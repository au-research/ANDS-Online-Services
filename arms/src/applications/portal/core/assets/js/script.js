$(document).ready(function() {
	initTips();

	$('#search_box').typeahead({
		name:'Search Suggestion',
		remote: base_url+'search/suggest/?q=%QUERY'
	}).on('typeahead:selected', function(){
		window.location = base_url+'search/#!/q='+encodeURIComponent($('#search_box').val());
	});
	$('.twitter-typeahead').attr('style', '');

	if ($.browser.msie && $.browser.version <= 9.0) {
		$('#who_contributes li').css({
			float:'left',
			width:'310px',
			listStyleType:'none'
		});
		$('#who_contributes').addClass('clearfix');
	}

	$('.text_select').each(function() {
		var $this = $(this),
			$input = $this.find('input'),
			$ul = $this.find('ul'),
			$span = $this.find('.default_value');
		$input.val('');	
		var emptyValue = $span.text();
		$('<li />').text(emptyValue).prependTo($ul);
		$this.click(function() {
			$ul.slideDown();
			$this.addClass('current');				
		});
		$this.mouseleave(function() {
			$ul.slideUp('fast');
			$this.removeClass('current');				
		});			
		$ul.find('li').click(function() {
			var value = $(this).text();
			if(value!=emptyValue) {
				$input.val($(this).text());
				$span.hide();
			} else {
				$input.val('');
				$span.show();				
			}
		});
	});


    $('#clear_search').click(function() {
    	var $form = $(this).parents('form');
    	$form.find('input[type="text"]').val('');
    	$form.find('input[type="checkbox"]').removeAttr('checked');
    	$form.find('option').attr('selected', false);
    	$form.find('select').find('option').first().attr('selected', true);
		$("#slider").editRangeSlider("min",1544);
		$("#slider").editRangeSlider("max",2012);
		$("#slider").hide();
    	return false;
    });

    $('#ad_st').toggle(function() {
	//don't init slider until we show the advanced search slidedown
	$("#slider").editRangeSlider({
    	    scales: [
		// Primary scale
		{
		    first: function(val){ return val; },
		    next: function(val){ return val + 50; },
		    stop: function(val){ return false; },
		    label: function(val){ return val; }
		}],
    	    bounds:{min: 1544, max: 2012},
    	    defaultValues:{min: 1544, max: 2012},
    	    valueLabels:"change",
    	    type:"number",
    	    arrows:false,
    	    delayOut:200
	});

        $('a.adv_note').qtip({
          content: {
	    title: 'Search notes',
	    text: $('#adv_note_content')
	  },
          show: 'mouseover',
          hide: 'mouseout',
          style: {
            classes: 'ui-tooltip-light ui-tooltip-shadow'
          }
	});

    	$(this).addClass('exped');
    	$('.advanced_search').slideDown();
    	$("#slider").editRangeSlider("valueLabels","hide");
    	$("#slider").editRangeSlider("resize");
     	return false;
    }, function() {
     	$(this).removeClass('exped');
    	$('.advanced_search').slideUp('fast');
    	return false;
    });

    $('a.adv_note').on('click', function(e) { e.preventDefault(); });

    $('.ad_close > a').on('click', function(e){ e.preventDefault(); $('#ad_st').click(); });

    $('#searchTrigger').on('click', function(){
    	window.location = base_url+'search/#!/q='+encodeURIComponent($('#search_box').val());
    });
    $('#search_box').keypress(function(e){
		if(e.which==13){//press enter
			window.location = base_url+'search/#!/q='+encodeURIComponent($(this).val());
		}
	});

	$('#search_map_toggle').click(function(e){
		window.location = base_url+'search/#!/map=show';
	});

	$('#adv_start_search').click(function(e){
		e.preventDefault();
		var q = '';
		var all = $('.adv_all').val();
		var input = $('.adv_input').val();
		var nots = $('.adv_not');
		var not = '';
		$.each(nots, function(e){
			var v = $(this).val();
			if(v!='')not +='-'+v+' ';
		});
		if(all!='') q +='"'+all+'" ';
		q += input+ ' '+not;
		var tab = $('#record_tab').val();
		
		var url = base_url+'search/#!/q='+q+'/tab='+tab;
		if($('#rst_range').prop('checked')){
			var temporal = $("#slider").editRangeSlider("values");
			url += '/temporal='+Math.round(temporal.min)+'-'+Math.round(temporal.max);
		}
		window.location = url;
	});

	$('#slider').hide();
	$('#rst_range').on('change',function(){
		$('#slider').toggle();
		$('#slider').editRangeSlider('resize');
	});

	$(document).on('click', '.ro_preview_header', function(e){
    	e.preventDefault();
    	$(this).next('.ro_preview_description').slideToggle();
	});


	$('#contact-send-button').live({

		click: function(e){
			clear = true;
			$.each($('#contact-us-form input, #contact-content'), function(){
				if($(this).val()=='') {
					clear=false;
					 $(this).qtip({
        				content:$(this).attr('title'),
        				style: {classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',width: '250px'},
						show:{ready:'true'},
						hide:{event:'focus'},
    				}); 
				}else{
					$(this).qtip("disable");
				}
				
			});
			if($('#contact-email').val()!='')
			{
				if($('#contact-email').val()!='' && !validateEmail($('#contact-email').val()))
				{
				 	clear=false;
				 	$('#contact-email').qtip({
        			content:"The provided email address was not valid",
        			style: {classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',width: '250px'},
					show:{ready:'true'},
					hide:{event:'focus'},
    				}); 
    			}else{
    				$('#contact-email').qtip("disable");
				
    			}					
			}	

			if(clear){ 
		 	$.ajax({
		  		type:"POST",
		  		url: base_url+"/home/send/",
		  		data:"name="+$('#contact-name').val()+"&email="+$('#contact-email').val()+"&content="+$('#contact-content').val(),   
		  			success:function(msg){
		  				$('#contact-us-form').html(msg);
		  			},
		  			error:function(msg){
		  			}
	  			});
			}
		}
	});


$(document).on('click', '.sharing_widget', function(){
	addthis.init();
	$(this).remove();
});

function validateEmail(email) 
{
    var re = /\S+@\S+\.\S+/;


     return re.test(email);	


    
}
	function getURLParameter(name) 
	{
	    return unescape(
	        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	    );
	}

	function recurseGetText() { 
		if (this.nodeType == 3)
		{
			return this.nodeValue;
		}
		else if (this.nodeType == 1 && this.nodeName.toLowerCase() == "br")
		{
			return '<br/>';
		}
		else
		{
			if (typeof $(this).contents == 'function' && $(this).contents().length > 0)
			{
				return $(this).contents().map(recurseGetText).get().join(' ');
			}
		}
		return this.nodeType == 3 ? this.nodeValue : undefined;
	}

	// get any text inside the element $(this).directText()
	$.fn.directText=function(delim) {
	  if (!delim) delim = ' ';
	  return this.contents().map(recurseGetText).get().join(delim);
	};
	//setTimeout(function(){alert("Hello")},3000)
});
// usage: log('inside coolFunc',this,arguments);
// http://paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console && deployment_state!== undefined && deployment_state=='development'){
    console.log( Array.prototype.slice.call(arguments) );
  }
};

function initTips(selector)
{
	var qSelector = $('*[tip]');
	if (selector)
	{
		qSelector = $(selector);
	}
	qSelector.qtip({
		content: {
			text: function(api) {
				// Retrieve content from custom attribute of the $('.selector') elements.
				return $(this).attr('tip');
			}
		},
		position:{my:'left center', at:'right center', viewport: $(window)},
		style: {
	        classes: 'ui-tooltip-light'
	    }
	});

}

/* Not used currently, but would be better than scattered strings... :-( 
function initExplanations()
{
	var explanations = {}
	explanations["collection"] = "Research dataset or collection of research materials.";
	explanations["party"] = "Researcher or research organisation that creates or maintains research datasets or collections.";
	explanations["services"] = "Service that supports the creation or use of research datasets or collections.";
	explanations["activities"] = "Project or program that creates research datasets or collections.";
}*/

// decode htmlentities()
function htmlDecode(value) {
	return (typeof value === 'undefined') ? '' : $('<div/>').html(value).text();
}