

  
    <!-- The javascripts Libraries
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.7.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/less-1.3.0.min.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js" type="text/javascript"></script>
    <script src="js/jquery.event.drag-2.2.js"></script>
	<script src="js/jquery.event.drag.live-2.2.js"></script>
	<script src="js/jquery.event.drop-2.2.js"></script>
	<script src="js/jquery.event.drop.live-2.2.js"></script>

	<script src="js/mustache.js"></script>

    <script type="text/javascript">
    	$('.thumbnails .thumbnail').dblclick(function(e){
    		e.preventDefault();
    		if($(this).parent().hasClass('span3')){
    			$(this).parent().switchClass( "span3", "span12", 500 );
    		}else{
    			$(this).parent().switchClass( "span12", "span3", 500 );
    		}
    	});

    	$('.btn-group').dblclick(function(e){
    		e.stopPropagation();
    	});

    	$('.thumbnails .thumbnail').hover(function(){
    		$('.btn-group', this).show();
    	}, function(){
			$('.btn-group', this).hide();
    	});

    	$('#switch_view a').click(function(){
    		$('#items').removeClass().addClass($(this).attr('name'));
    		$("#switch_view a").removeClass('active');
    		$(this).addClass('active');
    	});

    	$(document)
			.drag("start",function( ev, dd ){
				return $('<div class="selection" />')
					.css('opacity', .55 )
					.appendTo( document.body );
			})
			.drag(function( ev, dd ){
				$( dd.proxy ).css({
					top: Math.min( ev.pageY, dd.startY ),
					left: Math.min( ev.pageX, dd.startX ),
					height: Math.abs( ev.pageY - dd.startY ),
					width: Math.abs( ev.pageX - dd.startX )
				});
			})
			.drag("end",function( ev, dd ){
				$( dd.proxy ).remove();
			});
		$('.thumbnail')
			.drop("start",function(){
				$(this).addClass("active");
			})
			.drop(function( ev, dd ){
				$(this).toggleClass("selected");
			})
			.drop("end",function(){
				$(this).removeClass("active");
			}).click(function(){
				$(this).toggleClass("selected");
			});
		$.drop({ multi: true });
		/*$('#items').sortable({       
		    start: function(e, ui){
		        $(ui.placeholder).hide(300);
		    },
		    change: function (e,ui){
		        $(ui.placeholder).hide().show(300);
		    }
		});*/

		function load_more(){
			$.ajax({
				url: 'getItems.php',
				type: 'GET',
				contentType: 'application/json; charset=utf-8',
				dataType: 'json',
				success: function(data){
					//console.log(data);
					var itemsTemplate = $('#items-template').html();
					var output = Mustache.render(itemsTemplate, data);
					//console.log(output);
					$('#items').append('<div id="loading">Loading...</div>')
					setTimeout(function(){
						$('#loading').remove();
						$('#items').append(output);
					}, 2000);
					


				},
				error: function(data){

				}
			});
		}
		load_more();
		$('#load_more').click(function(){
			load_more();
		});

    </script>

  </body>
</html>