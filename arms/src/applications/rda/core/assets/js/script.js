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

		$('.flexslider').flexslider();
	    $( "#range_slider" ).slider({
	            range: true,
	            min: 0,
	            max: 500,
	            values: [ 75, 300 ],
	            slide: function( event, ui ) {
	                $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
	            }
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
	    	return false;
	    }, function() {
	     	$(this).removeClass('exped');
	    	$('.advanced_search').slideUp('fast');
	    	return false;   	
	    });
	});