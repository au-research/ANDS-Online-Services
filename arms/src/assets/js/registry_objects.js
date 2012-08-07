/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
var ds_id;
var fields = {};
$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */
	ds_id = $('#ds_id').val();
	fields.data_source_id = ds_id;

	getRecords(fields);

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' : browse(words[1]);break;
					case 'view': load_ro(words[1]);break;
					case 'edit': load_ro_edit(words[1], words[2]);break;
					case 'delete': load_datasource_delete(words[1]);break;
					default: browse('thumbnails');break;
				}
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}else{//there is no hash suffix
			browse('lists');
		}
	});
	$(window).hashchange();

	$('.item').live({
		mouseenter: function(e){
			$('.btn-group', this).show();
		},
		mouseleave: function(e){
			$('.btn-group', this).hide();
		},
		dblclick: function(e){
			e.preventDefault();
			changeHashTo('view/'+$(this).attr('ro_id'));
		},
		click: function(){
			
		}
	});

	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});


	$('.select_all_btn').click(function(){
		if($(this).attr('name')=='select_all'){
			$(this).attr('name', 'deselect_all');
			$(this).text('Deselect all');
			$('.item').addClass('selected');
		}else{
			$(this).attr('name', 'select_all');
			$(this).text('Select All');
			$('.item').removeClass('selected');
		}
		updateItemsInfo();
	});

	$('#filter').live({
		click: function(){
			$('#filter_fields').slideToggle();
		}
	});

	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		getRecords(fields, page);
		$(this).attr('page', page++);
	});

	$('#search-records').submit(function(e){
		e.preventDefault();
		var query = $('input', this).val();
		if(query!==""){
			fields.list_title = query;
		}else{
			delete fields.list_title;
		}
		clearItems();

		/*var jsonString = ""+JSON.stringify(fields);
		$('#myModal .modal-body').html('<pre>'+jsonString+'</pre>');
		$('#myModal').modal();*/
		getRecords(fields);
	});

	/*$('#items')
		.drag("start",function( ev, dd ){
			return $('<div class="selection" />')
				.css('opacity', .65 )
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

	$.drop({ multi: true });
	$('#items').sortable({
		delay: 300,       
	    start: function(e, ui){
	        $(ui.placeholder).hide(300);
	    },
	    change: function (e,ui){
	        $(ui.placeholder).hide().show(300);
	    }
	});
	//bind the drag and drop
	$('#items .item')
		.drop("start",function(){
			//$( this ).addClass("active");
		})
		.drop(function( ev, dd ){
			$( this ).addClass("selected");
			updateItemsInfo();
		})
		.drop("end",function(){
			//$( this ).removeClass("active");
		});
	$.drop({ multi: true });
	*/


});

function browse(view){
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-ro').fadeIn();
	}else{
		logErrorOnScreen('invalid View Argument');
	}
}

function getRecords(fields, page){
	if(!page) page = 1;
	//console.log(fields, page);
	$.ajax({
		type: 'POST',
		url: base_url+'registry_object/get_records/',
		data: {fields:fields, page:page},
		dataType: 'json',
		success: function(data){
			console.log(data);
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#items').append(output);
		}
	});
}

function clearItems(){
	$('#items').html('');
}
