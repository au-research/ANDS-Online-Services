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
	});