/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

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
				$('section').hide();
				switch(action){
					case 'browse' : browse(words[1]);break;
					case 'view': load_datasource(words[1]);break;
					case 'edit': load_datasource_edit(words[1], words[2]);break;
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
			browse('thumbnails');
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
		logErrorOnScreen('invalid View Argument');
	}
	$("#datasource-chooser").chosen();
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
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			//console.log(output);
			//$('#items').append('<div id="loading">Loading...</div>')
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
		}
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

$('.item-control .btn').live({
	click: function(e){
		e.preventDefault();
		var data_source_id = $(this).parent().parent().attr('data_source_id');
		if($(this).hasClass('view')){
			changeHashTo('view/'+data_source_id);
		}else if($(this).hasClass('edit')){
			changeHashTo('edit/'+data_source_id);
		}else if($(this).hasClass('delete')){
			changeHashTo('delete/'+data_source_id);
		}
	}
});

$('#datasource-chooser').live({
	change: function(e){
		changeHashTo('view/'+$(this).val());
	}
});

$('.close').live({
	click: function(e){
		//changeHashTo('browse/'+currentView);
		window.history.back();
	}
});

/*
 * Load a datasource view
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return false
 */
function load_datasource(data_source_id){
	$('#view-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$.ajax({
		url: 'getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);
			var template = $('#data-source-view-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-datasource');
			$('#view-datasource').html(output);
			$('#view-datasource').fadeIn(500);

			$('.btn-group button', view).click(function(){
				var data_source_id = $(this).parent().attr('data_source_id');
				if($(this).hasClass('edit')){
					changeHashTo('edit/'+data_source_id);
				}else if($(this).hasClass('history')){
					changeHashTo('history/'+data_source_id);
				}else if($(this).hasClass('delete')){
					changeHashTo('deleteRecord/'+data_source_id);
				}
			});

		}
	});
	return false;
}

/*
 * Load a datasource edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return false
 */
function load_datasource_edit(data_source_id, active_tab){
	$('#edit-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$('#view-datasources').slideUp(500);
	$.ajax({
		url: 'getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);
			var template = $('#data-source-edit-template').html();
			var output = Mustache.render(template, data);
			$('#edit-datasource').html(output);
			$('#edit-datasource').fadeIn(500);
			if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
				$('.nav-tabs li a[href=#'+active_tab+']').click();
			}
			
			$('#edit-datasource .normal-toggle-button').toggleButtons({
				width: 75,
				onChange: function($el, status, e){
					$('input', $el).attr('value', status);
				}
			});
			$('#edit-datasource  .normal-toggle-button input[type=checkbox]').each(function(){
				var input = $('#'+$(this).attr('for'));
				if($(input).val()=='t'){
					$(this).parent().click();
				}else{
					//do nothing for now
				}
			});

			$("#edit-datasource .chzn-select").chosen().change(function(){
				var input = $('#'+$(this).attr('for'));
				$(input).val($(this).val());
			});
			$('#edit-datasource .chzn-select').each(function(){
				var input = $('#'+$(this).attr('for'));
				$(this).val($(input).val());
				$(this).chosen().trigger("liszt:updated");
			});
		}
	});
	return false;
}
$('#save-edit-form').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');

		$('#edit-datasource #edit-form input, #edit-datasource #edit-form textarea').each(function(){
			var label = $(this).attr('id');
			var value = $(this).val();
			if(value!=''){
				jsonData.push({name:label, value:value});
			}
		});

		var jsonString = ""+JSON.stringify(jsonData);
		$('#myModal .modal-body').html('<pre>'+jsonString+'</pre>');
		$('#myModal').modal();
		//$(this).button('reset');
	}
});