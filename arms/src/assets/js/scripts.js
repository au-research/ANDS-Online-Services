$('button.show_nav').click(function(){
	$('#main-nav').slideToggle();
});


$('.btn-group').live({
	dblclick: function(e){
		e.stopPropagation();
	}
});


function changeHashTo(hash){
	window.location.hash = suffix+hash;
}

function checkResponse(data)
{
	if (data.status == "ERROR")
	{
		logErrorOnScreen(data.message);
	}
}

$("#dashboard-datasource-chooser").chosen();
$('#dashboard-datasource-chooser').live({
	change: function(e){
		window.location = base_url + 'data_source/manage#!/view/'+$(this).val();
	}
});


function logErrorOnScreen(error, target){
	var template = $('#error-template').html();
	var output = Mustache.render(template, $("<div/>").html(error).html());
	if (!target){
		$('#content').prepend(output);
	}
	else{
		target.html(output);
	}
}

$(document).ready(function(){
	$('#main-nav-user-account').qtip({
		content: {
			text: $('#user-account-info').html()
		},
		position:{
			my:'top right',
			at: 'left bottom',
			target: 'mouse',
			adjust: { mouse: false }
		},
		show: {
			event: 'click'
		},
		hide: {
			event: 'unfocus'
		},
		style: {
			classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'
		}
	});

	$('.closeTip').live('click', function(){
		$('div.qtip:visible').qtip('hide');
	});

	$('.chosen').chosen();


	//catch all tip, bad practice, but useful nonetheless
	$('*[tip]').live('mouseover', function(event) {
		// Bind the qTip within the event handler
		var my = $(this).attr('my');
		var at = $(this).attr('at');
		if(!my){
			my = 'bottom center';
		}
		if(!at){
			at = 'top center';
		}
		$(this).qtip({
			overwrite: false, // Make sure the tooltip won't be overridden once created
			content: $(this).attr('tip'),
			show: {
				event: event.type, // Use the same show event as the one that triggered the event handler
				ready: true // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
			},
			position: {
				my: my, // Use the corner...
				at: at,
				viewport: $(window)
			},
			style: {
				classes: 'ui-tooltip-bootstrap ui-tooltip-shadow'
			}
		}, event); // Pass through our original event to qTip
	});

	$('#main-nav-search').click(function(e){
		e.preventDefault();
		$('#navbar-search-form').toggle('slide',{direction:'right'});
		$('#navbar-search-form input').focus();
	});

	$('.loginSelector').click(function(e){
		e.preventDefault();
		var auth = $(this).attr('id');
		$('.loginForm').hide();
		$('#'+auth+'_LoginForm').fadeIn();
	});

	$('#navbar-search-form input').keypress(function(e){
		if(e.which==13){//enter
			var query = $(this).val();
			$(this).qtip({
				content:{
					text: 'Loading...', // The text to use whilst the AJAX request is loading},
					ajax: {
						url: base_url+'services/registry/search?query='+encodeURIComponent(query), // URL to the local file
						type: 'GET', // POST or GET
						data: {}, // Data to pass along with your request
						success: function(data,status){
							
							if(data.length>0){
								var html = '<div class="dropdown"><ul class="nav nav-list">';
								$.each(data, function(){
									var h ='<li class=""><a href="'+base_url+'registry_object/view/'+this.subtext+'">'+this.value+'</a></li>';
									html +=h
								});
								html+='</ul></div>';
								this.set('content.text', html);
							}else{
								this.set('content.text', 'No Result!');
							}
						}
					}
				},
				show:{ready:true},
				position:{my:'top center',at:'bottom center'},
				hide:{fixed:true,event:'unfocus'},
				style:{classes:'ui-tooltip-bootstrap ui-tooltip-shadow'}
			});
		}
	});

	$('#navbar-search-form').submit(function() {return false});
	


	//jgrowl
	window.createGrowl = function(wrapAlert,text_data, target) {
		if (wrapAlert)
		{
			text_data = "<div class='alert alert-info' style='margin-bottom:0px;'>" + text_data + "</div>";
		}
        // Use the last visible jGrowl qtip as our positioning target
        if (!target)
        {
        	var target = $('.qtip.jgrowl:visible:last');
        }

        // Create your jGrowl qTip...
        $(document.body).qtip({
            // Any content config you want here really.... go wild!
            content: {
                text: text_data
            },
            position: {
                my: 'top right',
                // Not really important...
                at: (target.length ? 'bottom' : 'top') + ' right',
                // If target is window use 'top right' instead of 'bottom right'
                target: target.length ? target : $(window),
                // Use our target declared above
                adjust: { y: 5 },
                effect: function(api, newPos) {
                    // Animate as usual if the window element is the target
                    $(this).animate(newPos, {
                        duration: 200,
                        queue: false
                    });

                    // Store the final animate position
                    api.cache.finalPos = newPos; 
                }
            },
            show: {
                event: false,
                // Don't show it on a regular event
                ready: true,
                // Show it when ready (rendered)
                effect: function() {
                    $(this).stop(0, 1).fadeIn(400);
                },
                // Matches the hide effect
                delay: 0,
                // Needed to prevent positioning issues
                // Custom option for use with the .get()/.set() API, awesome!
                persistent: false
            },
            hide: {
                event: 'unfocus click',
                // Don't hide it on a regular event
                effect: function(api) {
                    // Do a regular fadeOut, but add some spice!
                    $(this).stop(0, 1).fadeOut(400).queue(function() {
                        // Destroy this tooltip after fading out
                        api.destroy();

                        // Update positions
                        updateGrowls();
                    })
                }
            },
            style: {
                classes: 'jgrowl ui-tooltip-bootstrap ui-tooltip-rounded',
                width: 360,
                // Some nice visual classes
                tip: false // No tips for this one (optional ofcourse)
            },
            events: {
                render: function(event, api) {
                    // Trigger the timer (below) on render
                    timer.call(api.elements.tooltip, event);
                }
            }
        }).removeData('qtip');
    };

    // Make it a window property see we can call it outside via updateGrowls() at any point
    window.updateGrowls = function() {
        // Loop over each jGrowl qTip
        var each = $('.qtip.jgrowl'),
            width = each.outerWidth(),
            height = each.outerHeight(),
            gap = each.eq(0).qtip('option', 'position.adjust.y'),
            pos;

        each.each(function(i) {
            var api = $(this).data('qtip');

            // Set target to window for first or calculate manually for subsequent growls
            api.options.position.target = !i ? $(window) : [
                pos.left + width, pos.top + (height * i) + Math.abs(gap * (i-1))
            ];
            api.set('position.at', 'top right');
            
            // If this is the first element, store its finak animation position
            // so we can calculate the position of subsequent growls above
            if(!i) { pos = api.cache.finalPos; }
        });
    };

    if (typeof(displayGrowl) !== 'undefined' && displayGrowl)
    {
    	createGrowl(true, displayGrowl, $('#user-nav'));
    }

    // Setup our timer function
    function timer(event) {
        var api = $(this).data('qtip'),
            lifespan = 5000; // 5 second lifespan
        
        // If persistent is set to true, don't do anything.
        if (api.get('show.persistent') === true) { return; }

        // Otherwise, start/clear the timer depending on event type
        clearTimeout(api.timer);
        if (event.type !== 'mouseover') {
            api.timer = setTimeout(api.hide, lifespan);
        }
    }

    // Utilise delegate so we don't have to rebind for every qTip!
    $(document).delegate('.qtip.jgrowl', 'mouseover mouseout', timer);
	/*createGrowl(false);
	createGrowl(false);
	updateGrowls();*/
	


	//cosi main login screen 
	$('#affiliation_signup').click(function(e){
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			var orgRole = $('#organisational_roles').val();
			var thisRole = $(this).attr('localIdentifier');
			//console.log('registering '+thisRole+' to have this role '+orgRole);

			var jsonData = [];
			jsonData.push({name:'orgRole', value:orgRole});
			jsonData.push({name:'thisRole', value:thisRole});
			$.ajax({
				url: base_url+'auth/registerAffiliation/',
				type: 'POST',
				data: {orgRole:orgRole,thisRole:thisRole},
				success: function(data){
					if(data.status=='OK'){
						window.location.href = base_url+"vocab_service"+"/addVocabulary";
						//$('#myModal .modal-body').html('You have to logout and log back in for the changes to take effect <a href="'+base_url+'auth/logout">Logout</a>');
						//$('#myModal').modal();
					}else if(data.status=='WARNING'){
						alert(data.message);
					}else{
						//console.error(data);
					}
				}
			});
		}
	});

	$('#openAddOrganisation').click(function(){
		var html = $('#addOrgHTML').html();
		$('#myModal .modal-body').html(html);
		$('#myModal').modal();
		Core_bindFormValidation($('#myModal .addOrgForm'));
	});

	$('#confirmAddOrganisation').live({
		click:function(e){
			e.preventDefault();
			var orgRole = $("#myModal input.orgName").val();
			var thisRole = $('#myModal input.orgName').attr('localIdentifier');
			if(Core_checkValidForm($('#myModal .addOrgForm'))){
				$('#myModal').modal('hide');
				$.ajax({
					url: base_url+'auth/registerAffiliation/true',
					type: 'POST',
					data: {orgRole:orgRole,thisRole:thisRole},
					success: function(data){
						if(data.status=='OK'){
							
							$('#myModal .modal-body').html('You have to logout and log back in for the changes to take effect <a href="'+base_url+'auth/logout">Logout</a>');
							$('#myModal').modal();
						}else if(data.status=='WARNING'){
							alert(data.message);
						}else{
							console.error(data);
						}
					}
				});
			}
		}
	});

	$('#showBuiltInLoginForm').qtip({
		content:{
			text:$('#BuiltInLoginForm')
		},
		position:{
			my:'top right',
			at: 'bottom center'
		},
		show: {event: 'click'},
		hide: {event: 'unfocus'},
		events: {},
		style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
	});

	$("#organisational_roles").chosen().change(function(){
		var value = $(this).val();
		if(value){
			$('#affiliation_signup').removeClass('disabled');
		}else{
			$('#affiliation_signup').addClass('disabled');
		}
	});


});

jQuery.fn.extend({
  slideRightShow: function(duration) {
    return this.each(function() {
        $(this).show('slide', {direction: 'right'}, duration);
    });
  },
  slideLeftHide: function(duration) {
    return this.each(function() {
      $(this).hide('slide', {direction: 'left'}, duration);
    });
  },
  slideRightHide: function(duration) {
    return this.each(function() {
      $(this).hide('slide', {direction: 'right'}, duration);
    });
  },
  slideLeftShow: function(duration) {
    return this.each(function() {
      $(this).show('slide', {direction: 'left'}, duration);
    });
  }
});


$.ajaxSetup({
	dataType: 'json',
	error: function(data)
	{
		try
		{
			data = $.parseJSON(data.responseText);
			checkResponse(data);
			return;
		}
		catch (e)
		{
			logErrorOnScreen("An unknown error occured whilst communicating with the server.");
		}
	}
});

// implement JSON.stringify serialization
//var JSON = {}
if (typeof JSON !== 'undefined')
{
	JSON.stringify = JSON.stringify || function (obj) {
	    var t = typeof (obj);
	    if (t != "object" || obj === null) {
	        // simple data type
	        if (t == "string") obj = '"'+obj+'"';
	        return String(obj);
	    }
	    else {
	        // recurse array or object
	        var n, v, json = [], arr = (obj && obj.constructor == Array);
	        for (n in obj) {
	            v = obj[n]; t = typeof(v);
	            if (t == "string") v = '"'+v+'"';
	            else if (t == "object" && v !== null) v = JSON.stringify(v);
	            json.push((arr ? "" : '"' + n + '":') + String(v));
	        }
	        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
	    }
	};
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatXml(xml) {
    var formatted = '';
    var reg = /(>)(<)(\/*)/g;
    xml = xml.replace(reg, '$1\r\n$2$3');
    var pad = 0;
    jQuery.each(xml.split('\r\n'), function(index, node) {
        var indent = 0;
        if (node.match( /.+<\/\w[^>]*>$/ )) {
            indent = 0;
        } else if (node.match( /^<\/\w/ )) {
            if (pad != 0) {
                pad -= 1;
            }
        } else if (node.match( /^<\w[^>]*[^\/]>.*$/ )) {
            indent = 1;
        } else {
            indent = 0;
        }

        var padding = '';
        for (var i = 0; i < pad; i++) {
            padding += '  ';
        }

        formatted += padding + node + '\r\n';
        pad += indent;
    });

    return formatted;
}


function Core_bindFormValidation(form){
	$(form).attr('valid', false);
	$('input,textarea', form).each(function(){
		Core_checkValidField(form, this);
		Core_checkValidForm(form);
		$(this).off().on({
			blur: function(){
				Core_checkValidField(form, this);
				Core_checkValidForm(form);
			},
			keyup: function(){
				Core_checkValidField(form, this);
				Core_checkValidForm(form);
			}
		});
	});
}

function markRequired(form){
	$(form).attr('valid', false);
	$('input,textarea', form).each(function(){
		if(this.required){
			var label = $(this).parent().find('.control-label');
			if(label.length === 0)
				label = $(this).parent().parent().find('.control-label');
			if(label.length === 0)
				label = $(this).parent().parent().parent().find('.control-label');
			if($(label).parent().find('.required').length === 0)
				$(label).prepend('<span class="muted required">* </span>');
		}
	});
}

function Core_checkValidField(form, field){
	var valid = true;
	var warning = false;
	if(field.required || $(field).attr('valid-type'))
	{
		if(field.required && $(field).val().length==0){
			valid = false;
		}
		else if($(field).attr('valid-type') && $(field).val().length > 0)
		{
			if($(field).attr('type')=='email'){//email validation
				valid = validateEmail($(field).val());

			}
			else if($(field).attr('valid-type')=='date'){//email validation
				valid = true;//validateDate($(field).val());
			}
			else if($(field).attr('valid-type')=='url'){//email validation
				if(validateUrl($(field).val())){
					valid = true;
				}else{
					warning = true;
				}
			}
		}

		if(valid){
			// $(field).closest('div.control-group').removeClass('error').addClass('success');
			$(field).removeClass('error').addClass('success');
			if($(field).parent().find('.validation').length > 0)
			{
				$(field).parent().find('.validation').remove();
			}
			if(warning)
			{
				$(field).closest('div.control-group').removeClass('success').addClass('warning');
				$(field).removeClass('success').addClass('warning');
				if($(field).parent().find('.validation').length === 0)
				{
					$(field).parent().append('<div class="alert alert-warning validation">Field should be a valid '+$(field).attr('valid-type')+'</div>');				
				}
			}
			return true;
		}else{
			$(form).attr('valid', false);
			$(field).closest('div.control-group').removeClass('success').addClass('error');
			$(field).removeClass('success').addClass('error');
			if($(field).parent().find('.validation').length === 0)
			{
				if($(field).attr('valid-type'))
					$(field).parent().append('<div class="alert alert-error validation">Field must be a valid '+$(field).attr('valid-type')+'</div>');
				else
					$(field).parent().append('<div class="alert alert-error validation">Field value must be entered</div>');			
			}
			return false;
		}
	}
	//never gonna get here for field needing validation
	return valid;
}

function Core_checkValidForm(form){
	var valid = true;
	$('input,textarea',form).each(function(){
		if(!Core_checkValidField(form, this)){
			valid = false;
		}
	});
	if(valid){
		$(form).attr('valid', true);
	}else{
		$(form).attr('valid', false);
	}


	return valid;
}





function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 

function validateDate(dateText) { 
	//log(dateText);
	var re = /^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T|\ ]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/;
	return re.test(dateText);
}

function validateUrl(url) { 
    var re = /(ftp|http):\/\/([_a-z\d\-]+(\.[_a-z\d\-]+)+)(([_a-z\d\-\\\.\/]+[_a-z\d\-\\\/])+)*/;
    return re.test(url);
}

Number.prototype.pad = function (len) {
    return (new Array(len+1).join("0") + this).slice(-len);
}

// usage: log('inside coolFunc',this,arguments);
// http://paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console){
    console.log( Array.prototype.slice.call(arguments) );
  }
};