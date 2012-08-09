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
var filters;
var sorts = {};
$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */
	ds_id = $('#ds_id').val();
	if(ds_id!=0) fields.data_source_id = ds_id;

	

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' : browse(words[1], words[2]);break;
					case 'view': load_ro(words[1]);break;
					case 'edit': load_ro_edit(words[1], words[2]);break;
					case 'delete': load_ro_delete(words[1]);break;
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
			$(this).toggleClass('selected');
			updateSelected();
		}
	});

	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});


	$('#select_all').click(function(){
		if($(this).attr('name')=='select_all'){
			$(this).attr('name', 'deselect_all');
			$(this).text('Deselect all');
			$('.item').addClass('selected');
		}else{
			$(this).attr('name', 'select_all');
			$(this).text('Select All');
			$('.item').removeClass('selected');
		}
		updateSelected();
	});

	$('#filter').live({
		click: function(){
			$('#filter_container').slideToggle();
		}
	});

	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		getRecords(fields, sorts, page);
		$(this).attr('page', page++);
	});

	$('#search-records').submit(function(e){
		e.preventDefault();
		var query = $('input', this).val();
		var name = $('input', this).attr('name');
		if(query!==""){
			fields[name] = query;
		}else{
			delete fields[name];
		}
		clearItems();

		/*var jsonString = ""+JSON.stringify(fields);
		$('#myModal .modal-body').html('<pre>'+jsonString+'</pre>');
		$('#myModal').modal();*/
		getRecords(fields);
	});

	$('.filter').live({
		click: function(e){
			e.preventDefault();
			var filtername = $(this).attr('name');
			var filtervalue = $(this).attr('value');
			fields[filtername] = filtervalue;
			filters = constructFilters(fields);
			changeHashTo('browse/'+currentView+'/'+filters);
		}
	});
	$('.remove_filter').live({
		click: function(e){
			e.preventDefault();
			var field = $(this).attr('name');
			delete fields[field];
			filters = constructFilters(fields);
			changeHashTo('browse/'+currentView+'/'+filters);
		}
	});

	$('.sort').live({
		click: function(){
			var field = $(this).attr('name');
			var span = $(this).next('span');
			var direction = $(this).attr('direction');

			$('.sort').attr('direction', '');
			$('.sort').next('span').removeClass();
			
			if(direction==''){
				direction = 'asc';
				$(this).attr('direction', 'asc');
				$(span).removeClass().addClass('icon-chevron-up');
			}else{//there is already a direction
				if(direction=='asc'){
					direction = 'desc';
					$(this).attr('direction', 'desc');
					$(span).removeClass().addClass('icon-chevron-down');
				}else{
					direction = 'asc';
					$(this).attr('direction', 'asc');
					$(span).removeClass().addClass('icon-chevron-up');
				}
			}
			sorts = field + ' '+ direction;
			clearItems();
			getRecords(fields, sorts, 1);
		}
	});

	$('#items')
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
	//bind the drag and drop
	
	
});

function browse(view, filter){
	clearItems();
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-ro').fadeIn();
		if(filter){
			filter = filter.split('&');
			$.each(filter, function(){
				if(this!=''){
					var words = this.split('=');
					var filtername = words[0];
					var filtervalue = words[1];
					fields[filtername] = filtervalue;
				}
			});
		}else{
			clearFilters(fields);
		}
		getRecords(fields);
	}else{
		logErrorOnScreen('invalid View Argument');
	}
}

function getRecords(fields, sorts, page){
	if(!page) page = 1;
	$.ajax({
		type: 'POST',
		url: base_url+'registry_object/get_records/',
		data: {fields:fields, sorts:sorts, page:page},
		dataType: 'json',
		success: function(data){
			console.log(data);
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#items').append(output);

			//deal with facets
			$.each(data.facets, function(facet, array){
				var facetDom = $('.facets[name='+facet+'] ul');
				var html = '';
				$.each(array, function(field, value){
					//console.log(field, value);
					html +='<li><a href="javascript:;" class="filter" name="'+facet+'" value="'+field+'">'+field+' ('+value+')'+'</a></li>';
				});
				$(facetDom).html(html);
			});

			//applied filters
			$('#applied_filters').html('');
			$.each(fields, function(field, value){
				//on dedicated div for applied filters
				var html;
				html = '<a href="javascript:;" class="tag remove_filter" name="'+field+'">'+value+'<i class="icon-remove"></i></a>';
				$('#applied_filters').append(html);

				//on facet view
				$('.filter[name='+field+']').removeClass('filter').addClass('remove_filter').append(' <i class="icon-remove"></i>');
			});

			//bind the drag and drop select
			$('#items .item')
				.drop("start",function(){
					//$( this ).addClass("active");
				})
				.drop(function( ev, dd ){
					$( this ).addClass("selected");
					updateSelected();
				})
				.drop("end",function(){
					$( this ).removeClass("active");
				});
			$.drop({ multi: true });

			updateSelected();
		}
	});
}

function clearItems(){
	$('#items').html('');
}

function clearFilters(fields){
	$.each(fields, function(key, value){
		if(key!='data_source_id'){
			delete fields[value];
		}
	});
}

function constructFilters(fields){
	var inc = 0;
	var filters = '';
	$.each(fields, function(field, value){
		if(inc>0){
			filters+='&';
		}
		if(field!='data_source_id'){
			filters +=field+'='+value;
		}
		inc++;
	});
	return filters;
}

function updateSelected(){
	var totalSelected = $('#items .selected').length;
	if(totalSelected > 0){
		//$('#items_info').slideDown();
		var message = '<b>'+totalSelected + '</b> registry objects has been selected';
		$('#items_info').html(message);
	}else{
		//$('#items_info').slideUp();
	}
}