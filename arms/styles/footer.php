

  
    <!-- The javascripts Libraries
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.7.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/less-1.3.0.min.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js" type="text/javascript"></script>

    <script type="text/javascript">
    	$('.thumbnails .thumbnail').dblclick(function(e){
    		e.preventDefault();
    		if($(this).parent().hasClass('span3')){
    			$(this).parent().switchClass( "span3", "span12", 1000 );
    		}else{
    			$(this).parent().switchClass( "span12", "span3", 1000 );
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
    	});


    </script>

  </body>
</html>