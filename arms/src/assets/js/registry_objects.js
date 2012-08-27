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
					case 'view': load_ro(words[1], 'view');break;
					case 'preview': load_ro(words[1], 'preview');break;
					case 'edit': load_ro(words[1], 'edit', words[2]);break;
					case 'delete': load_ro_delete(words[1]);break;
					default: logErrorOnScreen('Invalid Operation: '+hash);break;
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

	$('.toggleFilter').live({
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
	

	//bind viewing screen
	$('.tab-view-list li a').live({
		click:function(e){
			e.preventDefault();
			var view = $(this).attr('name');
			var id = $(this).attr('ro_id');
			changeHashTo(view+'/'+id);
		}
	});
	
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

function load_ro(ro_id, view, active_tab){
	$.ajax({
		type: 'GET',
		url: base_url+'registry_object/get_record/'+ro_id,
		dataType: 'json',
		success: function(data){
			console.log(data);
			var itemsTemplate = $('#item-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#view-ro').html(output);


			//tab binding
			$('#view-ro .tab-content').hide();
			$('#view-ro .tab-view-list a').removeClass('active');
			$('#view-ro .tab-view-list a[name='+view+']').addClass('active');
			$('#view-ro .tab-content[name='+view+']').fadeIn();

			if(view=='view'){
				//magic?
				$('#view-ro .html-view').html(data.ro.view);

				var revisions = '';
				$.each(data.ro.revisions, function(time, id){
					revisions += '<li>'+time+'</li>';
				});
				$('#view-ro #ro-revisions').html(revisions);
			}else if(view=='edit'){
				//set the active tab
				//console.log(data.ro.xml);
				/*var content = $('#view-ro .tab-content[name=edit]');
				var xmlDoc = $.parseXML(data.ro.xml);
				console.log($(xmlDoc).find('*').length);
				console.log($(xmlDoc).length);
				$(xmlDoc).children('key').each(function(){
 					console.log($(this).text());
				});*/
				$.ajax({
					type: 'GET',
					url: base_url+'registry_object/get_edit_form/'+ro_id,
					success:function(data){
						$('#view-ro .tab-content[name=edit]').html(data);
						if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
							$('.nav-tabs li a[href=#'+active_tab+']').click();
						}

						initEditForm();

						$('#edit-form .toggle').live({
							click: function(e){
								e.preventDefault();
								$('i', this).toggleClass('icon-plus').toggleClass('icon-minus');
								$(this).parent().parent().children('.aro_box_part, button.addNew').toggle();
							}
						});

						$('#edit-form button').live({
							click: function(e){
								e.preventDefault();
							}
						});

					}
				});
			}

			$('#view-ro').show();
		}
	});
}

function initEditForm(){

	$('.input-large').typeahead({
		source: function(typeahead,query){
			$.ajax({
				type: 'GET',
				dataType : 'json',
				url: base_url+'services/registry/get_vocab/RIFCSCollectionType',
				success:function(data){
					return typeahead.process(data);
				}
			});
		},
		minLength:0
	});

	$('.triggerTypeAhead').live({
		click: function(e){
			$(this).parent().children('input').focus()
		}
	});

	$('#generate_random_key').live({
		click:function(e){
			e.preventDefault();
			var input = $(this).prev('input');
			$.ajax({
				type: 'GET',
				url: base_url+'services/registry/get_random_key/',
				success:function(data){
					$(input).val(data)
				}
			});
		}
	});

	var selected_data_source = $('#data_source_id_value').val();
	$.ajax({
		type: 'GET',
		dataType : 'json',
		url: base_url+'services/registry/get_datasources_list/',
		success:function(data){
			var data_sources = data.items;
			$('#data_sources_select').append('<option value="0"></option>');
			$.each(data.items, function(e){
				var id = this.id;
				var title = this.title;
				var selected = '';
				if(id==selected_data_source){
					selected='selected=selected';
				}
				$('#data_sources_select').append('<option value="'+id+'" '+selected+'>'+title+'</option>');
			});
			//284 is the default width for input-xlarge + padding
			$('#data_sources_select').width('284').chosen();
		}
	});


	$('.datepicker').datepicker({
		format: 'yyyy-mm-dd'
	});

	$('.triggerDatePicker').live({
		click: function(e){
			$(this).parent().children('input').dblclick();
		}
	});

	$('.remove').live({
		click:function(){
			var target = $(this).parents('.aro_box_part');
			if($(target).length==0) target = $(this).parents('.aro_box');
			//console.log(target);
			$(target).remove();
		}
	});

	$('.addNew').live({
		click:function(e){
			e.preventDefault();
			var what = $(this).attr('type');
			//var templates = $('#templates');
			var template = $('.template[type='+what+']');
			var where = $(this).prevAll('.separate_line');
			$(template).clone().removeClass('template').insertBefore(where).hide().slideDown();
			if(what=='description' || what=='rights'){
				$('#edit-form textarea').addClass('editor');
				initEditor();
			}
		}
	});

	$('.export_xml').live({
		click: function(e){
			e.preventDefault();
			tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
			var currentTab = $(this).parents('.tab-pane');
			var xml = getRIFCSforTab(currentTab);
			$('#myModal .modal-body').html('<pre class="prettyprint"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
			prettyPrint();
			$('#myModal').modal();
		}
	});

	$('#master_export_xml').live({
		click: function(e){
			e.preventDefault();
			tinyMCE.triggerSave();
			var allTabs = $('.tab-pane');
			var xml = '';
			$.each(allTabs, function(){
				xml += getRIFCSforTab(this);
			});
			$('#myModal .modal-body').html('<pre class="prettyprint"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
			prettyPrint();
			$('#myModal').modal();
		}
	});

	initNames();
	initDescriptions();
}

function initNames(){
	var names = $('#names .aro_box[type=name]');
	$.each(names, function(){
		if(!$(this).hasClass('template')){
			initName(this);
		}
	});

	$('#names input').live({
		blur:function(e){
			var thisName = $(this).parents('.aro_box[type=name]');
			initName(thisName);
		}
	});
}



function initName(name){
	var display = $(name).children('.aro_box_display').find('h1');
	var type = $(name).children('.aro_box_display').find('input[name=type]').val();
	var parts = $(name).children('.aro_box_part');
	var display_name = '';
	var temp_name = '';
	$.each(parts, function(){
		var thisPart = [];
		var type = $(name).find('input[name=type]').val();
		var value = $(name).find('input[name=value]').val();
		//logic here
		temp_name = value;
		if(type=='primary'){
			display_name = value;
		}
	});
	if(display_name=='') display_name=temp_name;
	$(display).html(display_name);
}

function initDescriptions(){
	initEditor();

}

function initEditor(){
	if(editor=='tinymce'){
		tinyMCE.init({
		    theme : "advanced",
		    mode : "specific_textareas",
		    editor_selector : "editor",
		    theme_advanced_toolbar_location : "top",
		    theme_advanced_buttons1 : "bold,italic,underline,separator,link,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,separator,undo,redo,code",
		    theme_advanced_buttons2 : "",
		    theme_advanced_buttons3 : "",
		    height:"250px",
		    width:"600px"
		});
	}
}

function getRIFCSforTab(tab){
	var currentTab = $(tab);
	var boxes = $('.aro_box', currentTab);
	var xml = '';
	$.each(boxes, function(){
		if(!$(this).hasClass('template')){
			var fragment ='';
			var fragment_type = '';
			if($('.aro_box_display input', this).length>0){
				fragment_type = $('.aro_box_display input', this).val();
			}else{
				fragment_type = $('input[name=type]', this).val();
			}
			fragment +='<'+$(this).attr('type')+' type="'+fragment_type+'">';
			var parts = $('.aro_box_part', this);
			if(parts.length > 0){//if there is a part
				$.each(parts, function(){
					fragment += '<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'">'+$('input[name=value]', this).val()+'</'+$(this).attr('type')+'>';
				});
			}else{//there is no part, the data is right at this level
				//check if there's a text area
				if($('textarea', this).length>0){
					fragment += htmlEntities($('textarea', this).val());
				}else{
					//there's no textarea, just normal input
				}
			}
			fragment +='</'+$(this).attr('type')+'>';
			xml += fragment;
		}
	});
	return xml;
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
		$('#items_info').slideUp();
	}
}