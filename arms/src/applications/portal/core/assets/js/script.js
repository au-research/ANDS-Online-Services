$(document).ready(function() {

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
    $( "#range_slider" ).slider({
            range: true,
            min: 0,
            max: 500,
            values: [ 75, 300 ],
            slide: function( event, ui ) {
                $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            }
    });
    $("#slider").editRangeSlider({
    	bounds:{min: 1544, max: 2012},
    	defaultValues:{min: 1544, max: 2012},
    	valueLabels:"change",
    	type:"number",
    	arrows:false,
    	delayOut:400
	});

    $('#clear_search').click(function() {
    	var $form = $(this).parents('form');
    	$form.find('input[type="text"]').val('');
    	$form.find('input[type="checkbox"]').removeAttr('checked');
    	$form.find('option').attr('selected', false);
    	$form.find('select').find('option').first().attr('selected', true);
    	return false;
    });
    $('#ad_st').toggle(function() {
    	$(this).addClass('exped');
    	$('.advanced_search').slideDown();
    	$("#slider").editRangeSlider("resize");
    	return false;
    }, function() {
     	$(this).removeClass('exped');
    	$('.advanced_search').slideUp('fast');
    	$("#slider").editRangeSlider("resize");
    	return false;
    });

    $('#search_box').keypress(function(e){
		if(e.which==13){//press enter
			window.location = base_url+'search/#!/q='+$(this).val();
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
		var temporal = $("#slider").editRangeSlider("values");
		window.location = base_url+'search/#!/q='+q+'/tab='+tab+'/temporal='+Math.round(temporal.min)+'-'+Math.round(temporal.max);
	});


	function getURLParameter(name) 
	{
	    return unescape(
	        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	    );
	}


	//setTimeout(function(){alert("Hello")},3000)

});