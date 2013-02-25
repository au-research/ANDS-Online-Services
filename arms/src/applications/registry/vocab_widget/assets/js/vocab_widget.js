/*
  Copyright 2009 The Australian National University
  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
*******************************************************************************/
;(function($) {
    $.fn.ands_vocab_widget = function(options) {
	var VocabHandler = Class.extend({
	    init: function(container, settings) {
		this._container = container;
		this.settings = settings;
	    },

	    /**
	     * Implemented by subclasses; prep widget
	     * for user interaction
	     */
	    ready: function() {
		return false;
	    },

	    /**
	     * Implemented by subclasses; disable
	     * widget's user interaction
	     */
	    detach: function() {
		return false;
	    },

	    /**
	     * basic error handler
	     */
	    _err: function(xhr) {
		if (typeof(this.settings['error_msg']) === 'boolean' &&
		    this.settings['error_msg'] === false) {
		}
		else {
		    alert(this.settings['error_msg'] + "\r\n\r\n"
			  + xhr.responseText +
			  "\r\n(id:" + this._container.attr('id') + ")");
		}
		this._container.blur();
		return false;
	    },

	    _makelist: function(persist) {
		if (typeof(persist) === 'undefined') {
		    persist = false;
		}
		this._list = $('<ul />')
		    .attr('id', this._container.attr('id') + this.settings._wid)
		    .addClass(this.settings.list_class)
		    .addClass(this.settings.repository)
		    .addClass(this.mode)
		    .data('persist', persist)
		    .hide();
		this._list.insertAfter(this._container);
		this._container.attr('autocomplete', 'off');
	    },

	    /**
	     * silly wrapper to provide input buffering.
	     * `lookup` makes the ajax call
	     */
	    vocab_lookup: function (event) {
		//this._reset();
		if (this._container.data('ands_vocab_timer')) {
		    clearTimeout(this._container.data('ands_vocab_timer'));
		}
		this._container.data('ands_vocab_timer',
				     setTimeout(this.lookup(),
						this.settings.delay));
	    },

	    /**
	     * reset the list
	     */
	    _reset: function() {
		if (!this._list.data('persist')) {
		    this._list.empty();
		}
		this._list.hide();
	    },

	    /**
	     * generate the display for a vocab item (i.e. list item)
	     */
	    vocab_item: function(data) {
		var item = $('<li role="vocab_item" />');
		item.data(WIDGET_DATA, data);
		$.each(this.settings.fields, function(idx, field) {
		    if (typeof(data[field]) !== 'undefined') {
			item.append('<span role="' + field + '">' +
				    data[field] +
				    '</span>');
		    }
		});
		return item;
	    },

	    /**
	     * once a selection has been made, we need to do something with it
	     */
	    handle_selection: function(event) {
		var data = $(event.target).parent().data(WIDGET_DATA);
		if (typeof(data[this.settings.target_field]) !== 'undefined') {
		    this._container.val(data[this.settings.target_field]);
		    this._reset();
		}
		else {
		    this._err({status: 404,
			       responseText: 'item is missing target field (' +
			       this.settings.target_field + ')'});
		}
	    }
	});

	var SearchHandler = VocabHandler.extend({
	    init: function(container, settings) {
		this._super(container, settings);
		this._makelist();

		if (!this._container.is("input[type='text']")) {
		    // we only like being attached to input elements when searching
		    this._err({status: 500,
			       responseText: "must be attached to a text " +
			       "input element when mode is 'search'"});
		    return false;
		}
		//disable autocomplete; interferes with our autocomplete
		this._container.attr("autocomplete", "off");
	    },

	    ready: function() {
		var handler = this;
		this._container.bind("keydown", function(e) {
		    if (e.which == '27') {
			handler._reset();
			handler._container.val('');
		    }
		    else {
			handler.vocab_lookup(e);
		    }
		});
	    },

	    detach: function() {
		this._container.unbind("keydown");
	    },

	    /**
	     * let's do something with the provided data
	     */
	    process: function(data) {
		var handler = this;

		if (data.status !== "OK") {
		    this._err({status: 500, responseText: data.message});
		}
		else {
		    this._reset();
		    if (data.count === 0 &&
			typeof(this.settings['nohits_msg']) !== 'boolean' &&
			typeof(this.settings['nohits_msg']) !== false) {
			this._list.append('<li role="vocab_error">' +
					  this.settings['nohits_msg'] +
					  '</li>');
		    }
		    else if (data.count === 0 &&
			     typeof(this.settings['nohits_msg']) === 'boolean' &&
			     this.settings['nohits_msg'] === false) {
			this._list.empty();
		    }
		    else if (data.count > 0) {
			$.each(data.items, function(idx, item) {
			    handler._list.append(handler.vocab_item(item));
			});
		    }
		    else {
			this._list.append('<li role="vocab_error">' +
					  'Hmmm... something went wrong here.' +
					  ' Try again?</li>');
		    }
		    this._list.show()
			.children('li[role="vocab_item"]')
			.bind('click', function(event) { handler.handle_selection(event)});
		}
	    },

	    /**
	     * make the ajax call using the plugin settings + input value.
	     * calls `process` on success, `_err` on error
	     */
	    lookup: function() {
		if (this._container.val().length >= this.settings.min_chars) {
		    var handler = this;
		    var url = this.settings.endpoint +
			"?action=" + this.settings.mode +
			"&repository=" + this.settings.repository +
			"&limit=" + this.settings.max_results +
			"&lookfor=" + this._container.val();
		    $.ajax({
			url: url,
			cache: this.settings.cache,
			dataType: "jsonp",
			success: function(data) { handler.process(data) },
			error: function(xhr) { handler._err(xhr) },
		    });
		}
	    }
	});

	var NarrowHandler = VocabHandler.extend({
	    init: function(container, settings) {
		this._super(container, settings);
		this._ctype = this._container.get(0).tagName;
		if (this._container.is("select")) {
		    if (this.settings.fields.length > 1) {
			this._err({status:500,
				   responseText:"'fields' setting must be a " +
				   "single element array when mode isn't " +
				   "'search'"});
		    }

		    this._container.empty()
			.append('<option value=""></option>');

		}
		else if (this._container.is("input")) {
		    this._makelist(true);
		    this._preplist();
		}
		else {
		    this._err({status:500,
			       responseText: "in 'narrow' mode, the plugin " +
			       "must be attached to a select " +
			       "or input element"});
		}

	    },

	    _preplist: function() {
		var handler = this;
		$.ajax({
		    url: this._url(),
		    cache: this.settings.cache,
		    dataType:"jsonp",
		    success: function(data) {
			if (data.status === "OK") {
			    $.each(data.items, function(idx, item) {
				handler._list.append(handler.vocab_item(item));
			    });
			}
			else {
			    handler._err({status:500,
					  responseText:data.message});
			}
			handler._container.bind("keyup", function(e) {
			    handler.vocab_lookup(e);
			});
			handler._list
			    .children('li[role="vocab_item"]')
			    .bind('click', function(event) { handler.handle_selection(event)});
		    },
		    error: function(xhr) { handler._err(xhr) }
		});

	    },

	    lookup: function() {
		var handler = this;
		var lookfor = this._container.val().toLowerCase();
		if (lookfor.length) {
		    this._list.children('li').hide();
		    this._list.show();
		    var matches = $.grep(this._list.children('li[role="vocab_item"]'),
					 function(e,i) {
					     var item = $(e);
					     var data = item.data(WIDGET_DATA);
					     for (var fi in handler.settings.fields) {
						 var field = handler.settings.fields[fi];
						 if ((typeof(data[field]) !== 'undefined') &&
						     data[field].substring(0,lookfor.length).toLowerCase() === lookfor) {
						     return true;
						 }
					     }
					     return false;
					 });
		    $(matches).show();
		}
	    },

	    _url: function() {
		return this.settings.endpoint +
		    "?action=" + this.settings.mode +
		    "&repository=" + this.settings.repository +
		    "&limit=" + this.settings.max_results +
		    "&lookfor=" + this.settings.mode_params;
	    },

	    ready: function() {
		var handler = this;
		if (this._ctype === 'SELECT') {
		    var url = this._url();
		    $.ajax({
			url: url,
			cache: this.settings.cache,
			dataType: "jsonp",
			success: function(data) { handler.process(data) },
			error: function(xhr) { handler._err(xhr) },
		    });
		}
		else {

		    this._container.on("keydown", function(e) {
			if (e.which == '40') {
			    handler._list.show();
			}
			else if (e.which == '27') {
			    handler._container.val('');
			    handler._list.hide();
			}
		    });
		}
	    },

	    process: function(data) {
		var handler = this;
		if (data.status === "OK") {
		    $.each(data.items, function(idx, item) {
			var val = item[handler.settings.target_field];
			var label = item[handler.settings.fields[0]];
			handler._container.append('<option value="' + val + '">' +
						  label + '</option>');
		    });
		}
		else {
		    handler._err({status:500,
			       responseText:data.message});
		}
	    },

	    detach: function() {
		if (this._ctype === 'INPUT') {
		    this._container.unbind("keyup");
		}
	    }
	});

	var WIDGET_NAME = "ANDS Vocabulary Widget service";
	var WIDGET_ID = "_vocab_widget_list";
	var WIDGET_DATA = "ands_vocab_data";
	var defaults = {
	    //location (absolute URL) of the jsonp proxy
	    endpoint: 'http://ands3.anu.edu.au/workareas/smcphill/ands-online-services/arms/src/registry/vocab_widget/proxy/',

	    //sisvoc repository to query. (proxy defaults to 'anzsrc-for' if none supplied)
	    repository: 'anzsrc-for',

	    //currently, 'search' and 'narrow' are available. in future,
	    //'broad', 'concepts' modes will be added
	    mode: "search",

	    //search doesn't require any parameters, but narrow does (and broaden will)
	    //in the latter case, the parameter is the URI to narrow/broaden on
	    mode_params: "",

	    //at most, how many results should be returned?
	    max_results: 100,

	    //search mode: how many characters are required before we send a query?
	    min_chars: 3,

	    //search mode: how long should we wait (after initial user input) before
	    //firing the search? provide in milliseconds
	    delay: 500,

	    //should we cache results? yes by default
	    cache: true,

	    //search mode: what to show when no hits? set to boolean(false) to supress
	    nohits_msg: "No matches found",

	    //what to show when there's some weird error? set to boolean(false)
	    //to supress
	    error_msg: WIDGET_NAME + " error.",

	    //provide CSS 'class' references. Separate multiple classes by spaces
	    list_class: "ands_vocab_list",

	    //which fields do you want to display? check the repository for available fields
	    //nb:
	    //  - anzsrc-for uses [label, notation, about]
	    //  - rifcs uses [label, definition, about]
	    //
	    //nb: in browse mode, this should be a single element array
	    fields: ['label', 'notation', 'about'],

	    //what data field should be stored upon selection?
	    //in narrow mode, this is the option's value attribute
	    target_field: "label",

	},
	settings = $.extend({}, defaults, options);

	//do some quick and nasty fixes
	settings.list_class = typeof(settings.list_class) === 'undefined' ?
	    "" :
	    settings.list_class;

	settings._wname = WIDGET_NAME;
	settings._wid = WIDGET_ID;

	/**
	 * simplistic throwable template for input validation
	 */
	function _throwing(val, rule) {
	    throw "'" + val + "' must be " + rule + " (was: " + settings[val] + ")";
	}

	/**
	 * some very simple input validation
	 */
	function valid_input() {
	    var is_valid = true;
	    var container = $(this);
	    var validation_rules = [
		{
		    fields: ["min_chars", "max_results", "delay"],
		    description: "a positive integer",
		    test: function(val) { return typeof(val) === 'number' && val === ~~Number(val) && val >= 0; }
		},
		{
		    fields: ["cache"],
		    description: "a boolean",
		    test: function(val) { return typeof(val) === 'boolean'; }
		},
		{
		    fields: ["mode"],
		    description: "one of <search,narrow>",
		    test: function(val) { return val === 'search' || val === 'narrow'; }
		},
		{
		    fields: ["endpoint"],
		    description: "a URL",
		    test: function(val) { var urlre = new RegExp("^(http|https)\://.*$"); return urlre.test(val); }
		},
		{
		    fields: ["list_class", "repository"],
		    description: "a string",
		    test: function(val) { return typeof(val) === 'undefined' || typeof(val) === 'string'; }
		},
		{
		    fields: ["fields"],
		    description: "an array of strings",
		    test: function(val) { return Object.prototype.toString.call(val) === "[object Array]"; }
		},
		{
		    fields: ["mode_params"],
		    description: "non-search mode parameters",
		    test: function(val) { return ((typeof(val) !== 'undefined' && settings['mode'] !== 'search') || settings['mode'] === 'search'); }
		},
	    ];

	    $.each(validation_rules, function(ridx, rule) {
		$.each(rule.fields, function(fidx, field) {
		    try {
			is_valid = is_valid && rule.test(settings[field]);
		    }
		    catch (e) {
			is_valid = false;
		    }
		    if (!is_valid) {
			_throwing(field, rule.description);
			return false;
		    }
		});
		if (!is_valid) {
		    return false;
		}
	    });

	    return is_valid;
	}

	var is_ok = false;
	try {
	    is_ok = valid_input();
	}
	catch (e) { }

	var handler;
	if (is_ok) {
	    return this.each(function() {
		var $this = $(this);
		switch(settings.mode) {
		case 'search':
		    handler = new SearchHandler($this, settings);
		    break;
		case 'narrow':
		    handler = new NarrowHandler($this, settings);
		}

		handler.ready();
	    });
	}
	else {
	    try {
		//we know this will fail...
		valid_input();
	    }
	    catch (err) {
		alert(WIDGET_NAME + ': \r\n' + err + '\r\n(reload the page before retrying)');
	    }
	    //lose focus and unbind to avoid continuous errors
	    try {
		handler.detach();
	    }
	    catch (e) {}
	    $(this).blur();
	    return false;
	}
    };

    /* Simple JavaScript Inheritance
     * By John Resig http://ejohn.org/
     * MIT Licensed.
     */
    // Inspired by base2 and Prototype
    (function(){
	var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;

	// The base Class implementation (does nothing)
	this.Class = function(){};

	// Create a new Class that inherits from this class
	Class.extend = function(prop) {
	    var _super = this.prototype;

	    // Instantiate a base class (but only create the instance,
	    // don't run the init constructor)
	    initializing = true;
	    var prototype = new this();
	    initializing = false;

	    // Copy the properties over onto the new prototype
	    for (var name in prop) {
		// Check if we're overwriting an existing function
		prototype[name] = typeof prop[name] == "function" &&
		    typeof _super[name] == "function" && fnTest.test(prop[name]) ?
		    (function(name, fn){
			return function() {
			    var tmp = this._super;

			    // Add a new ._super() method that is the same method
			    // but on the super-class
			    this._super = _super[name];

			    // The method only need to be bound temporarily, so we
			    // remove it when we're done executing
			    var ret = fn.apply(this, arguments);
			    this._super = tmp;

			    return ret;
			};
		    })(name, prop[name]) :
		prop[name];
	    }

	    // The dummy class constructor
	    function Class() {
		// All construction is actually done in the init method
		if ( !initializing && this.init )
		    this.init.apply(this, arguments);
	    }

	    // Populate our constructed prototype object
	    Class.prototype = prototype;

	    // Enforce the constructor to be what we expect
	    Class.prototype.constructor = Class;

	    // And make this class extendable
	    Class.extend = arguments.callee;

	    return Class;
	};
    })();
})( jQuery );
