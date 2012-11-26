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
	var WIDGET_NAME = "ANDS Vocabulary Widget service"
	var WIDGET_ID = "ands_vocab_widget_list";
	var defaults = {
	    //location (absolute URL) of the jsonp proxy
	    endpoint: 'http://ands3.anu.edu.au/workareas/smcphill/ands-online-services/arms/src/registry/vocab_widget/proxy/',

	    //currently, only search is available. in future, 'narrow',
	    //'broad', 'concepts' modes will be added
	    mode: "search",

	    //at most, how many results should be returned?
	    max_results: 100,

	    // how many characters are required before we send a query?
	    min_chars: 3,

	    //how long should we wait (after initial user input) before
	    //firing the search? provide in milliseconds
	    delay: 500,

	    //should we cache results? yes by default
	    cache: true,

	    //what to show when no hits? set to boolean(false) to supress
	    nohits_msg: "No matches found",

	    //what to show when there's some weird error? set to boolean(false)
	    //to supress
	    error_msg: WIDGET_NAME + " error.",

	    //provide CSS 'class' references. Separate multiple classes by spaces
	    list_class: "ands_vocab_list",

	    //which fields do you want to display? currently,
	    //('label', 'notation', 'about') are available
	    fields: ['label', 'notation', 'about'],

	    //what data field should be stored upon selection?
	    target_field: "notation",

	},
	settings = $.extend({}, defaults, options);

	//do some quick and nasty fixes
	settings.list_class = typeof(settings.list_class) == 'undefined' ?
	    "" :
	    settings.list_class;

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

	    var validation_rules = [
		{
		    fields: ["min_chars", "max_results", "delay"],
		    description: "a positive integer",
		    test: function(val) { return typeof(val) == 'number' && String(val) == ~~Number(val) && val >= 0; }
		},
		{
		    fields: ["cache"],
		    description: "a boolean",
		    test: function(val) { return typeof(val) == 'boolean'; }
		},
		{
		    fields: ["mode"],
		    description: "one of <search,>",
		    test: function(val) { return val == 'search'; }
		},
		{
		    fields: ["endpoint"],
		    description: "a URL",
		    test: function(val) { var urlre = new RegExp("^(http|https)\://.*$"); return urlre.test(val); }
		},
		{
		    fields: ["list_class"],
		    description: "a string",
		    test: function(val) { return typeof(val) == 'undefined' || typeof(val) == 'string'; }
		},
		{
		    fields: ["fields"],
		    description: "an array of strings",
		    test: function(val) { return Object.prototype.toString.call(val) == "[object Array]"; }
		},
	    ];

	    $.each(validation_rules, function(ridx, rule) {
		$.each(rule.fields, function(fidx, field) {
		    is_valid = is_valid && rule.test(settings[field]);
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
	catch (e) {}

	if (is_ok) {
	    return this.each(function() {
		var $this = $(this);
		$('<ul id="' + WIDGET_ID + '"' +
		  'class="' + settings.list_class +
		  '" />').insertAfter($this);
		var $list = $("ul#" + WIDGET_ID);
		$list.hide();

		if (settings.mode == 'search') {
		    if (!$this.is("input[type='text']")) {
			// we only like being attached to input elements
			alert(WIDGET_NAME + ': must be attached to a text input element!');
			return false;
		    }
		    //disable autocomplete; interferes with our autocomplete
		    $this.attr("autocomplete", "off");
		}

		/**
		 * silly wrapper to provide input buffering.
		 * _lookup makes the ajax call
		 */
		function vocab_lookup(event) {
		    _reset();
		    if ($this.data('ands_vocab_timer')) {
			window.clearTimeout($this.data('ands_vocab_timer'));
		    }
		    $this.data('ands_vocab_timer',
			       window.setTimeout(_lookup, settings.delay));
		}

		/**
		 * make the ajax call using the plugin settings + input value.
		 * calls `process_lookup` on success, `_err` on error
		 */
		function _lookup() {
		    if ($this.val().length >= settings.min_chars) {
			var url = settings.endpoint + "?action=" + settings.mode +
			    "&limit=" + settings.max_results + "&lookfor=" +
			    $this.val();
			$.ajax({
			    url: url,
			    cache: settings.cache,
			    dataType: "jsonp",
			    success: process_lookup,
			    error: _err
			});
		    }
		}

		/**
		 * basic error handler
		 */
		function _err(xhr) {
		    if (typeof(settings['error_msg']) == 'boolean' &&
			settings['error_msg'] == false) {
		    }
		    else {
			alert(settings['error_msg'] + "\r\n\r\n"
			       + xhr.status + ": " + xhr.statusText);
		    }
		    $this.blur();
		}

		/**
		 * reset the list
		 */
		function _reset() {
		    $list.empty();
		    $list.hide();
		}

		/**
		 * generate the display for a voab item (i.e. list item)
		 */
		function vocab_item(data) {
		    var item = $('<li role="vocab_item" />');
		    item.data('ands_vocab_data', data);
		    $.each(settings.fields, function(idx, field) {
			if (typeof(data[field]) != 'undefined') {
			    item.append('<span role="' + field + '">' +
					data[field] +
					'</span>');
			}
		    });
		    return item;
		}

		/**
		 * once a selection has been made, we need to do something with it
		 */
		function handle_selection(event) {
		    var data = $(this).data('ands_vocab_data');
		    if (typeof(data[settings.target_field]) != 'undefined') {
			$this.val(data[settings.target_field]);
			_reset();
		    }
		    else {
			_err({status: 404,
			      statusText: 'item is missing target field (' +
			      settings.target_field + ')'});
		    }

		}

		/**
		 * finally! let's do something with the provided data
		 */
		function process_lookup(data) {
		    if (data.status !== "OK") {
			_err({status: 500, statusText: data.message});
		    }
		    else {
			_reset();
			if (data.count == 0 &&
			    typeof(settings['nohits_msg']) !== 'boolean' &&
			    typeof(settings['nohits_msg']) !== false) {
			    $list.append('<li role="vocab_error">' +
					 settings['nohits_msg'] + '</li>');
			}
			else if (data.count == 0 &&
				 typeof(settings['nohits_msg']) == 'boolean' &&
				 settings['nohits_msg'] == false) {
			    $list.empty();
			}
			else if (data.count > 0) {
			    $.each(data.items, function(idx, item) {
				$list.append(vocab_item(item));
			    });
			}
			else {
			    $list.append('<li role="vocab_error">' +
					'Hmmm... something went wrong here. ' +
					'Try again?</li>');
			}
			$list.show();
			$('ul#' + WIDGET_ID + ' > li[role="vocab_item"]')
			    .bind('click', handle_selection);
		    }
		}

		$this.bind("keydown", vocab_lookup);
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
	    $(this).blur();
	    $(this).unbind("keydown");
	    return false;
	}
    };
})( jQuery );
