/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
$(".chzn-select").chosen();
$(".chzn-select-deselect").chosen({allow_single_deselect:true});


$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				switch(action){
					case 'browse' : browse(words[1]);break;
					case 'view': load_datasource(words[1]);break;
					case 'edit': load_datasource_edit(words[1]);break;
					case 'delete': load_datasource_delete(words[1]);break;
					default: browse('thumbnails');break;
				}
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}
	});
	$(window).hashchange();
});

function browse(view){
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-datasources').slideDown();
	}else{
		var template = $('#error-template').html();
		var output = Mustache.render(template, 'Invalid Argument');
		$('#main-content').append(output);
		$('section').hide();
	}
}

var currentView = 'thumbnails';
$('#switch_view a').click(function(){
	changeHashTo('browse/'+$(this).attr('name'));
	currentView = $(this).attr('name');
});

function load_more(page){
	$.ajax({
		url: 'data_source/getDataSources/'+page,
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
			}, 0);
		},
		error: function(data){}
	});
}
load_more(1);
$('#load_more').click(function(){
	var page = parseInt($(this).attr('page'));
	page++;
	load_more(page);
	$(this).attr('page', page++);
});

$('.item').live({
	mouseenter: function(e){
		$('.btn-group', this).show();
	},
	mouseleave: function(e){
		$('.btn-group', this).hide();
	},
	dblclick: function(e){
		e.preventDefault();
		changeHashTo('view/'+$(this).attr('data_source_id'));
	},
	click: function(){
		
	}
});

$('#datasource-chooser').live({
	change: function(e){
		load_datasource($(this).val());
	}
});

$('.close').live({
	click: function(e){
		changeHashTo('browse/'+currentView);
	}
});

function load_datasource(data_source_id){
	$('#view-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$.ajax({
		url: 'getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			console.log(data);
			var template = $('#data-source-view-template').html();
			var output = Mustache.render(template, data);
			$('#view-datasource').html(output);
			$('#view-datasource').fadeIn(500);
		},
		error: function(data){}
	});
}