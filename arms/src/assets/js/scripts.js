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

function updateItemsInfo(){
	var totalSelected = $('#items .selected').length;
	if(totalSelected > 1){
		$('#items_info').slideDown();
		var message = '<b>'+totalSelected + '</b> registry objects has been selected';
		$('#items_info').html(message);
	}else{
		$('#items_info').slideUp();
	}
}

function logErrorOnScreen(error){
	var template = $('#error-template').html();
	var output = Mustache.render(template, error);
	$('#main-content').prepend(output);
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


	//jgrowl
	window.createGrowl = function(persistent) {
	    // Use the last visible jGrowl qtip as our positioning target
	    var target = $('.qtip.jgrowl:visible:last');

	    // Create your jGrowl qTip...
	    $('#main-content').qtip({
	        // Any content config you want here really.... go wild!
	        content: {
	            text: 'Testing out our jGrowl implementation (persistent: ' + persistent + ')',
	            title: {
	                text: 'Attention!',
	                button: true
	            }
	        },
	        position: {
                my: 'top right',
                // Not really important...
                at: (target.length ? 'bottom' : 'top') + ' right',
                // If target is window use 'top right' instead of 'bottom right'
                target: target.length ? target : $('#main-content'),
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
	            persistent: persistent
	        },
	        hide: {
	            event: false,
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
	        api.options.position.target = !i ? $('#main-content') : [
	            pos.left + width, pos.top + (height * i) + Math.abs(gap * (i-1))
	        ];
	        api.set('position.at', 'top right');
	        
	        // If this is the first element, store its finak animation position
	        // so we can calculate the position of subsequent growls above
	        if(!i) { pos = api.cache.finalPos; }
	    });
	};

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
	/*createGrowl(false);
	createGrowl(false);
	createGrowl(false);
	createGrowl(false);
	createGrowl(false);
	updateGrowls();*/
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
    error: function(err) {
        //do stuff when things go wrong
        console.error(err);
        logErrorOnScreen(err.responseText);
    }
});

// implement JSON.stringify serialization
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


