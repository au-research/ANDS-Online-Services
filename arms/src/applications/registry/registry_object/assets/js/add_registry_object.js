/**
 * Core Maintenance Javascript
 */
var aro_mode, active_tab;
var editor = 'tinymce';
var fieldID = 1;
var SIMPLE_MODE = 'simple';
var ADVANCED_MODE = 'advanced';

$(function(){
	$('body').css('background-color', '#454545');

	//mode
	aro_mode = 'advanced';
	$('.pane').hide();
	switchMode(aro_mode);
	$('#mode-switch button').click(function(){
		var to_mode = $(this).attr('aro-mode');
		aro_mode = to_mode;

		// Change tab to the first tab of this mode
		var tab = $('#'+aro_mode+'-menu a:first').attr('href');
		changeHashTo(aro_mode+'/'+tab.substring(1, tab.length));

		switchMode(aro_mode);
	});

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			aro_mode = words[0];
			active_tab = words[1];

			switchMode(aro_mode);
			if(aro_mode==ADVANCED_MODE){
				$('.pane').hide();
				$('#'+active_tab).show();
				$('#advanced-menu a').parent().removeClass('active');
				$('#advanced-menu a[href=#'+active_tab+']').parent().addClass('active');
			}
			else if(aro_mode==SIMPLE_MODE){
				$('.pane').hide();
				$('#'+active_tab).show();
				$('#simple-menu a').parent().removeClass('active');
				$('#simple-menu a[href=#'+active_tab+']').parent().addClass('active');
			}

		}else{//there is no hash suffix
			 location.hash = suffix + ADVANCED_MODE + "/" + "admin";
			 $(window).hashchange();
		}
	});
	$(window).hashchange();//initial hashchange event
	initEditForm();

	
	// === Sidebar navigation === //	
	$('.submenu > a').click(function(e){
		e.preventDefault();
		var submenu = $(this).siblings('ul');
		var li = $(this).parents('li');
		var submenus = $('#sidebar li.submenu ul');
		var submenus_parents = $('#sidebar li.submenu');
		if(li.hasClass('open')){
			if(($(window).width() > 768) || ($(window).width() < 479)) {
				submenu.slideUp();
			} else {
				submenu.fadeOut(250);
			}
			li.removeClass('open');
		}else{
			if(($(window).width() > 768) || ($(window).width() < 479)){
				submenus.slideUp();			
				submenu.slideDown();
			}else{
				submenus.fadeOut(250);			
				submenu.fadeIn(250);
			}
			submenus_parents.removeClass('open');		
			li.addClass('open');	
		}
	});
	
	$('#sidebar > a').click(function(e){
		e.preventDefault();
		var sidebar = $('#sidebar');
		if(sidebar.hasClass('open')){
			sidebar.removeClass('open');
			$('#sidebar > ul').slideUp(250);
		}else{
			sidebar.addClass('open');
			$('#sidebar > ul').slideDown(250);
		}
	});

	$('#advanced-menu a').click(function(e){
		var tab = $(this).attr('href');
		changeHashTo('advanced/'+tab.substring(1, tab.length));
	});

	$('#simple-menu a').click(function(e){
		var tab = $(this).attr('href');
		changeHashTo('simple/'+tab.substring(1, tab.length));
	});

	$('input').live('keypress',function(event){
		if (event.keyCode == 10 || event.keyCode == 13) {
        	event.preventDefault();
    	}
	});
	validate();


	$(document).on('click','.search_related_btn', function(){
		var target = $(this).prev('input');
		var qtipTarget = $(this);
		$(this).qtip({
			content: {
				text: 'Loading...', // The text to use whilst the AJAX request is loading
				ajax: {
					url: base_url+'registry_object/related_object_search_form', // URL to the local file
					type: 'GET',
					data: {},
					success: function(data, status) {
						this.set('content.text', data.html_data);
						bindSearchRelatedEvents(this, target);
					}
				}
			},
			show:{solo:true,ready:true,event:'click'},
		    hide:{delay:1500, fixed:true,event:'unfocus'},
		    position:{my:'left center', at:'right center',viewport:$(window)},
		    style: {
		        classes: 'ui-tooltip-light ui-tooltip-shadow'
		    }
		});
	});
});

function bindSearchRelatedEvents(tt, target){
	var tooltip = $('#ui-tooltip-'+tt.id+'-content');
	$('.input_search_related', tooltip).keypress(function(e) {
	    if(e.which == 13) {
	        $(this).next('.search_related').click();
	    }
	});
	$('.search_related', tooltip).click(function(){
		var term = $('input', tooltip).val();
		// data_source_id_value
		var ds_option = '';
		if($('#ds_option').attr('checked')=='checked'){
			ds_option = '/'+$('#data_source_id_value').val();
		}
		var published_option = '';
		if($('#published_option').attr('checked')=='checked'){
			published_option = '&onlyPublished=yes';
		}
		var class_option = $('#class_related_search_option').val();
		$.ajax({
			url:base_url+'registry_object_search/search/'+class_option+ds_option+'?field=title&term='+term+published_option, 
			type: 'GET',
			success: function(data){
				var template = $('#related_object_search_result').html();
				var output = Mustache.render(template, data);
				$('#result', tooltip).html(output);
				$('.select_related').click(function(){
					$(target).val($(this).attr('key'));
					tt.hide();
				});
			}
		});
	});
	$('.show_advanced_search_related', tooltip).click(function(){
		$('#advanced',tooltip).toggle();
	});
}

function switchMode(aro_mode){
	$('#sidebar ul').hide();
	$('#'+aro_mode+'-menu').show();
	$('#mode-switch button').removeClass('btn-primary');
	$('#mode-switch button[aro-mode='+aro_mode+']').addClass('btn-primary');

	// Reset the values of the inputs to their bound equivalents
	if (aro_mode==SIMPLE_MODE){
		initSimpleModeFields();
	}
}



/*
 * Initialize the edit form, ready to be use upon completion
 * @TODO: 
 * 
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initEditForm(){

	/*
	 * Toggle button
	 * toggle the plus and minus
	 * slidetoggle everything except div.aro_box_display
	 */
	$('#edit-form .toggle').die().live({
		click: function(e){
			e.preventDefault();
			$('i', this).toggleClass('icon-plus').toggleClass('icon-minus');
			var aro_box = $(this).parents('.aro_box');
			$(aro_box).children('*:not(.aro_box_display)').slideToggle();
		}
	});

	/*
	 * Prevents the form from submitting when hit any button
	 */
	$('#edit-form button').die().live({
		click: function(e){
			e.preventDefault();
		}
	});
	
	/*
	 * Enable typeahead on input of class.[something]
	 * Documentation of typeahead is on twitter bootstrap
	 * @TODO: 
	 		- write a service that takes in a vocab_type, vocab_class and vocab_scheme eg: RIFCSCollectionType
	 			-> returns a json array of results
	 *
	 */
	$('.input-largeXX').typeahead({
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

	/*
	 * icon-chevron-down button that triggers the typeahead by focusing into the input
	 */
	$('.triggerTypeAhead').die().live({
		click: function(e){
			$(this).parent().children('input').focus()
		}
	});

	/*
	 * Generate the random key based on the services/registry/get_random_key dynamically on the server
	 * @TODO: make sure the key is unique accross system, returns error message if fails
	 */
	$('#generate_random_key').die().live({
		click:function(e){
			e.preventDefault();
			var input = $(this).prev('input');
			$.ajax({
				type: 'GET',
				url: base_url+'services/registry/get_random_key/',
				success:function(data){
					$(input).val(data.key);
				}
			});
		}
	});

	/*
	 * Replace the data source text input field with a chosen() select
	 * @TODO: ACL on which data source is accessible on services/registry/get_datasources_list
	 */
	/*var selected_data_source = $('#data_source_id_value').val();
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

					// Update the header link
					$('.data_source_link').html(title);
					$('.data_source_link').attr("href",base_url + "data_source/manage_records/" + id);
					$('.data_source_link').fadeIn();
				}
				$('#data_sources_select').append('<option value="'+id+'" '+selected+'>'+title+'</option>');
			});
			//284 is the default width for input-xlarge + padding
			$('#data_sources_select').width('284').chosen().trigger("liszt:update");

		}
	});
	$('#data_sources_select').change(function(){
		var chosenvalue = $(":selected", this);
		$('.data_source_link').html(chosenvalue.html());
		$('.data_source_link').attr("href",base_url + "data_source/manage_records/" + chosenvalue.val());
//		$(".data_source_title")
	})*/

	$(document).on('mouseup', '.remove',function(e){
		/*
		 * Remove the parent element
		 * If a part is found, remove the part
		 * If no part is found, remove the box
		 * 
		 */
		var target = $(this).parent('.aro_box');
		if($(target).length==0) target = $(this).parents('.aro_box_part')[0];
		if($(target).length==0) target = $(this).parents('.aro_box')[0];
		$(target).fadeOut(500, function(){
			$(target).remove();
		});
	}).on('mouseup', '.addNew',function(e){
		/*
		 * Add a new Element
		 * find a div.separate_line among the parents previous divs
		 * template is a div.template[type=] where type is defined in the @type attribute of the button itself
		 */
		e.stopPropagation();
		e.preventDefault();
		var what = $(this).attr('add_new_type');
		var template = $('.template[type='+what+']')[0];
		var where = $(this).prevAll('.separate_line')[0];

		//FIND THE SEPARATE LINE!!!
		//@TODO: badly need an algorithm | refactor | or an easier way
		if(!where){//if there is no separate line found, go out 1 layer and find it
			where = $(this).parent().prevAll('.separate_line')[0];
			if(!where){
				where = $(this).parent().parent().prevAll('.separate_line')[0];
				if(!where){
					where = $(this).parent().parent().parent().prevAll('.separate_line')[0];
					if(!where){
						where= $(this).parent().parent().parent().parent().prevAll('.separate_line')[0];
					}
				}
			}
		}
		//found it, geez
		
		//add the DOM
		var new_dom = $(template).clone().removeClass('template').insertBefore(where).hide().slideDown();
		assignFieldID(new_dom);
		initVocabWidgets(new_dom);
		initMapWidget(new_dom);
		//log(new_dom);
		//@TODO: check if it's inside a tooltip and perform reposition


		/*
		 * Reason for this:
		 	- We don't want to init the editor onto hidden template element
		 	- We keep template element without the class editor
		 		And only add the class editor upon addition of the element
		 */
		if(what=='description' || what=='rights'){
			$('#descriptions_rights textarea').addClass('editor');
			initEditor();
		}
		if(what=='dates_date' || what=='dates' || what=='date' || what == 'location'){
			//initalize the datepicker, format is optional
			$('input.datepicker').datepicker({
				format: 'yyyy-mm-dd'
			});
			
			//triggering the datepicker by focusing on it
			$('.triggerDatePicker').die().live({
				click: function(e){
					$(this).parent().children('input').focus();
				}
			});
		}

		//bind the tooltip parts UI in case of adding a new element with show Parts Elements
		bindPartsTooltip();
	});
	

	//Export XML button for currentTab in pretty print and modal
	$('.export_xml').die().live({
		click: function(e){
			e.preventDefault();
			if(editor=='tinymce') tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
			var currentTab = $(this).parents('.pane');
			var xml = getRIFCSforTab(currentTab,false);
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
			prettyPrint();
			$('#myModal').modal();
		}
	});

	//Export XML button for ALL TABS in pretty print and modal
	$('#master_export_xml').die().live({
		click: function(e){
			e.preventDefault();
			if(editor=='tinymce') tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
			var allTabs = $('.pane');
			var xml = '';

			//admin tab
			var admin = $('#admin');
			var ro_class = $('#ro_class').val();//hidden value
			var ro_id = $('#ro_id').val();

			xml += '<registryObject group="'+$('input[name=group]',admin).val()+'">';
			xml += '<key>'+$('input[name=key]', admin).val()+'</key>';
			xml += '<originatingSource type="'+$('input[name=originatingSourceType]', admin).val()+'">'+$('input[name=originatingSource]',admin).val()+'</originatingSource>';
			xml += '<'+ro_class+' type="'+$('input[name=type]',admin).val()+'">';

			$.each(allTabs, function(){
				xml += getRIFCSforTab(this,true);
			});

			xml+='</'+ro_class+'></registryObject>';
			$('#myModal .modal-header h3').html('<h3>Save &amp; Validate Registry Object</h3>');
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
			$('#myModal .modal-footer').html('<button class="btn btn-primary">Download</button>');
			prettyPrint();
			$('#myModal').modal();
		}
	});

	$('#save').off().on({
		click: function(e){
			e.preventDefault();
			validate();
			if(editor=='tinymce') tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
			var allTabs = $('.pane');
			var xml = '';

			//admin tab
			var admin = $('#admin');
			var ro_class = $('#ro_class').val();//hidden value
			var ro_id = $('#ro_id').val();

			xml += '<registryObject group="'+$('input[name=group]',admin).val()+'">';
			xml += '<key>'+$('input[name=key]', admin).val()+'</key>';
			xml += '<originatingSource type="'+$('input[name=originatingSourceType]', admin).val()+'">'+$('input[name=originatingSource]',admin).val()+'</originatingSource>';
			xml += '<'+ro_class+' type="'+$('input[name=type]',admin).val()+'">';

			$.each(allTabs, function(){
				xml += getRIFCSforTab(this,false);
			});

			xml+='</'+ro_class+'></registryObject>';
			$('#myModal .modal-header h3').html('<h3>Save &amp; Validate Registry Object</h3>');
			$('#myModal .modal-body').html('<div style="width:100%; margin:both; text-align:center;">' + 
											'<img src="'+real_base_url+'assets/img/ajax-loader.gif" style="padding-top:25px;" />' +
											'<p></p><p><small>Saving your Registry Object...</small></p>' +
											'</div>');
			$('#myModal .modal-footer').html('');
			prettyPrint();
			$('#myModal').modal();

			//test validation
			// $.ajax({
			// 	url:base_url+'registry_object/validate/'+ro_id, 
			// 	type: 'POST',
			// 	data: {xml:xml},
			// 	success: function(data){
			// 		console.log(data);
			// 	}
			// });

			//saving
			//log(xml);
			$.ajax({
				url:base_url+'registry_object/save/'+ro_id, 
				type: 'POST',
				data: {xml:xml},
				success: function(data){
					if(data.status=='success'){
						var template = $('#save-record-template').html();
						var output = Mustache.render(template, data);
						//console.log($('.record_title'));
						$('.record_title').html(data.title);
						$('#myModal .modal-body').html(output);
						formatQA($('#myModal .qa'));
					}else{
						$('#myModal .modal-body').html(data.message);
					}
				},
				error: function(data){
					data = $.parseJSON(data.responseText);
					$('#myModal .modal-body').html(data.message);
				}
			});
		}
	});

	$('#validate').off().on({
		click: function(e){
			e.preventDefault();
			validate();
		}
	});


	//Load external XML modal dialog
	$('#load_xml').die().live({
		click: function(e){
			e.preventDefault();
			$('#myModal .modal-header h3').html('<h3>Paste RIFCS Here</h3>');
			$('#myModal .modal-body').html('<textarea id="load_xml_rifcs"></textarea>');
			$('#myModal .modal-footer').html('<button id="load_edit_xml" class="btn btn-primary">Load</button>');
			$('#myModal').modal();
		}
	});

	//This button stays inside the Load xml modal dialog
	//This will post the input rifcs to the server and replace the current edit form with the response
	$('#load_edit_xml').die().live({
		click: function(e){
			var rifcs = $('textarea#load_xml_rifcs').val();
			var ro_id = $('#ro_id').val();
			//console.log(ro_id);
			if(rifcs!=''){
				$('#view-ro .tab-content[name=edit]').html('Loading...');
				$.ajax({
					type: 'POST',
					data: {rifcs:rifcs},
					url: base_url+'registry_object/get_edit_form_custom/'+ro_id,
					success:function(data){
						$('#view-ro .tab-content[name=edit]').html(data);						
						initEditForm();
						$('#myModal').modal('hide');
					}
				});
			}
		}
	});

	//initalize the datepicker, format is optional
	$('input.datepicker').datepicker({
		format: 'yyyy-mm-dd'
	});

	//triggering the datepicker by focusing on it
	$('.triggerDatePicker').die().live({
		click: function(e){
			$(this).parent().children('input').focus();
		}
	});

	$('.triggerMapWidget').die().live({
		click: function(e){
			var typeInput = $(this).parent().parent().find('input[name=type]');
			$(typeInput).val('kmlPolyCoords');
			initMapWidget($(this).parent().parent());
		}
	});

	//Various calls to initialize different tabs
	/*
	 	@TODO: 
	 		- Related object resolving
			- Resolve subject with sissvoc
			- Resolve identifier (based on types)
			- short (1 line) for locations
			- short (1 line) for descriptions / rights
	 */
	initNames();
	initDescriptions();
	initRelatedInfos();
	initRelatedObjects();
	bindPartsTooltip();
	assignFieldID();
	initVocabWidgets($(document));
	initMapWidget($(document));


}

function validate(){
	if(editor=='tinymce') tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
	var allTabs = $('.pane');
	var xml = '';

	//admin tab
	var admin = $('#admin');
	var ro_class = $('#ro_class').val();//hidden value
	var ro_id = $('#ro_id').val();

	xml += '<registryObject group="'+$('input[name=group]',admin).val()+'">';
	xml += '<key>'+$('input[name=key]', admin).val()+'</key>';
	xml += '<originatingSource type="'+$('input[name=originatingSourceType]', admin).val()+'">'+$('input[name=originatingSource]',admin).val()+'</originatingSource>';
	xml += '<'+ro_class+' type="'+$('input[name=type]',admin).val()+'">';

    $('.error' ,allTabs).each(function(){
        $(this).removeClass('error');
    });

	$.each(allTabs, function(){		
		xml += getRIFCSforTab(this,true);
	});

	xml+='</'+ro_class+'></registryObject>';
	prettyPrint();

	//validate
	//log(xml);
	$.ajax({
		url:base_url+'registry_object/validate/'+ro_id, 
		type: 'POST',
		data: {xml:xml},
		success: function(data){
			//log(data);
			$('.alert:not(.persist)').remove();
			if(data.SetInfos) $.each(data.SetInfos, function(e,i){addValidationMessage(i, 'info');});
			if(data.SetErrors) $.each(data.SetErrors, function(e,i){addValidationMessage(i, 'error');});
			if(data.SetWarnings) $.each(data.SetWarnings, function(e,i){addValidationMessage(i, 'warning');});

			var allTabs = $('.pane');
			$('#advanced-menu .label').remove();
			$.each(allTabs, function(){
				var count_info = $('.alert-info', this).length;
				var count_error = $('.alert-error', this).length;
				var count_warning = $('.alert-warning', this).length;
				var id = $(this).attr('id');
				if(count_info > 0) addValidationTag(id, 'info', count_info);
				if(count_error > 0) addValidationTag(id, 'important', count_error);
				if(count_warning > 0) addValidationTag(id, 'warning', count_warning);
			});
		}
	});
}


function addValidationMessage(tt, type){
	var name = tt.field_id;
	var message = tt.message;
    var message = $('<div />').html(message).text();

	// log(name, message);
	if(name.match("^tab_")){
		var tab = name.replace('tab_','');
		$('#'+tab).prepend('<div class="alert alert-'+type+'">'+message+'</div>');
	}else{
		var field = $('*[field_id='+name+']');
		$(field).append('<div class="alert alert-'+type+'">'+message+'</div>');
		$(field).addClass('error');
	}
}

function addValidationTag(pane, type, num){
	var menu_item = $('a[href="#'+pane+'"]');
	$(menu_item).append('<span class="label label-'+type+'">'+num+'</span>')
}

function initSimpleModeFields()
{
	/* Show/hide full description field */
	if ($('#simpleFullDescription').length > 0)
	{
		$('#simpleFullDescription').parent().parent().show();
		$('#simpleFullDescriptionToggle').parent().hide();
	}

	$('#simpleAddMoreIdentifiers').live({
		click: function(e){
			changeHashTo(ADVANCED_MODE+'/identifiers');
		}
	})

}


function initVocabWidgets(container){
	var container_elem;
	if(container){
		container_elem = container;
	}else container_elem = $(document);
	$(".rifcs-type", container_elem).each(function(){
		//log(this, 'bind vocab widget');
		var elem = $(this);
		var widget = elem.vocab_widget({mode:'advanced'});
		var vocab = _getVocab(elem.attr('vocab'));
		elem.on('narrow.vocab.ands', function(event, data) {	
		var dataArray = Array();
			if(vocab == 'RIFCSSubjectType')
			{				
				$.each(data.items, function(idx, e) {
					dataArray.push({value:e.notation, subtext:e.definition});
				});
				$(elem).off().on("change",function(e){
					// $(elem).prev().val('');
					initSubjectWidget(elem);
				});
				
				initSubjectWidget(elem);
			}	
			else
			{
				$.each(data.items, function(idx, e) {
					dataArray.push({value:e.label, subtext:e.definition});
				});
			}
			elem.typeahead({source:dataArray});
		});

		elem.on('error.vocab.ands', function(event, xhr) {
			log(xhr);
		});
		widget.vocab_widget('repository', 'rifcs');
		widget.vocab_widget('narrow', "http://purl.org/au-research/vocabulary/RIFCS/1.4/" + vocab);		 
	});
}

function initRelatedObjects(){
	//var names = $('#names .aro_box[type=name]');
	
}

function _getVocab(vocab)
{
	vocab = vocab.replace("collection", "Collection");
	vocab = vocab.replace("party", "Party");
	vocab = vocab.replace("service", "Service");
	vocab = vocab.replace("activity", "Activity");
	return vocab;
}

function initSubjectWidget(elem){
	var vocab_type = elem;
	var vocab_value = $(elem).prev();

	var vocab = vocab_type.val();
	var vocab_term = $(vocab_value).val();
	var term = vocab_value.attr('vocab');

	var dataArray = Array();
	// WE MIGHT NEED A WHITE LIST HERE

	if(vocab == 'anzsrc-for' || vocab =='anzsrc-seo'){
		var widget = vocab_value.vocab_widget({mode:'advanced',cache: false, repository: vocab});
		vocab_value.one('search.vocab.ands', function(event, data) {	
			var dataArray = Array();
			$.each(data.items, function(idx, e) {
				dataArray.push({value:e.notation, subtext:e.label});
			});
			// log(dataArray);
			vocab_value.typeahead({source:dataArray});
			vocab_value.data('typeahead').source = dataArray;
		});
		widget.vocab_widget('search', '');	
	}
}

function initMapWidget(container){

	var container_elem;
	if(container){
		container_elem = container;
	}else container_elem = $(document);
	
	$(".spatial_value", container_elem).each(function(){

		var typeInput = $(this).parent().find('input[name=type]');
		typeInput.one({
			change: function(e){
				
				initMapWidget($(this).parent());
			}
		});
		var type = typeInput.val();
		var controls = $(this).closest('.controls');
		if(type === 'gmlKmlPolyCoords' || type === 'kmlPolyCoords'){
			var fieldId = $(this).attr('field_id');
			$(this).attr('id',fieldId+"_input");
			if ($("#"+fieldId+"_map").length === 0) {
				controls.append('<div id="'+fieldId+'_map" class="map_widget"></div>');
				$('#'+fieldId+'_map').ands_location_widget({
  					target:fieldId+"_input"
				});
			}
		}else{
			$('.map_widget', controls).remove();
		}
	});
}


function assignFieldID(chunk){
	var content;
	if (typeof(chunk) === 'undefined') {
		content = $('#content');
	}
	else {
		content = chunk;

	}

	$('div, input, .aro_box:not(.template), .aro_box_part:not(.template)', content).each(function(){
		if(!$(this).attr('field_id')) {
			fieldID++;
			$(this).attr('field_id', fieldID);
		}
	});

}


/*
 * Binds .showParts to display a qtip element
 * @TODO: #fix reposition issue when interacting with the DOM inside a tooltip (eg addNew, remove)
 * finds the next div.parts and use that as content
 * The content will be removed from the DOM and append to the body with the id of ui-tooltip-x
 * This target will be defined at the button level by the attribute aria-describedby
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function bindPartsTooltip(){
	$('.showParts').each(function(){
		var parts = $(this).next('.parts')[0];
		if(parts){
			var button = this;
			$(this).qtip({
				content:{text:$(parts)},
				position:{
					my:'center left',
					at: 'center right'
				},
				show: {event: 'click'},
				hide: {event: 'unfocus'},
				events: {
					show: function(event, api) {
						//console.log(api.id, button);
					}
				},
				style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
			});
		}
	});
}

/*
 * Initialize the names tab (aro_box_display)
 * the heading takes values from name Parts
 * @TODO: write a service that takes in a list of name part & class => spits out the display_title
 * Currently this function gives the primary name, or the first name part
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initNames(){
	var names = $('#names .aro_box[type=name]');
	$.each(names, function(){
		var name = this;
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
	});

	$('#names input').die().live({
		blur:function(e){
			var thisName = $(this).parents('.aro_box[type=name]');
			initNames();
		}
	});
}


/*
 * Initialize the descriptions tab (aro_box_display)
 * only init the editor for now (@see:editor)
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initDescriptions(){
	initEditor();
}

/*
 * Initialize all related Info heading (aro_box_display)
 * the heading takes values from title > notes and then identifier
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initRelatedInfos(){
	var relatedInfos = $('#relatedinfos .aro_box[type=relatedInfo]');
	$.each(relatedInfos, function(){
		var ri = this;//ri is the current related info
		var display = $('.aro_box_display h1', ri);
		var todisplay = $('input[name=title]', ri).val();
		if(!todisplay){//if there is none, grab the notes
			todisplay = $('input[name=notes]', ri).val();
		}
		if(!todisplay){//if there is none, grab the identifier
			todisplay = $('input[name=identifier]', ri).val();
		}
		$(display).html(todisplay);
	});

	$('input', relatedInfos).off().on('blur',function(){
		initRelatedInfos();
	});
}

/*
 * Initialize all the Editors on screen
 * Cater for multiple types of wysiwyg html editor
 * The editor value is set as tinymce and will be able to change dynamically
 * @see: registry_object/controllers/registry_object
 * @see: registry_object/views/registry_object_index
 * @see: engine/views/footer
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void] > affecting all textarea.editor on screen
 */

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
		    width:"600px",
		    entity_encoding : "raw",
		    forced_root_block : ''
		});
	}
}

/*
 * Minh's Black Magic
 * Getting the RIFCS fragment for the given tab
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [object] tab
 * @returns: [string] RIFCS fragment ready for validation
 */

function getRIFCSforTab(tab, hasField){
	var currentTab = $(tab);
	var boxes = $('.aro_box', currentTab);
	var xml = '';
	$.each(boxes, function(){
		var fragment ='';
		var fragment_type = '';

		/*
		 * Getting the fragment header
		 * In the form of <name> or <name type="sometype">
		 * The name => the "type" attribute of the box
		 * The type => the input[name=type] of the box display (heading)
		 */
		var this_fragment_type = $(this).attr('type');
		//log("FRAGMENT TYPE: " + this_fragment_type);
		fragment +='<'+this_fragment_type+'';
		if(hasField) fragment +=' field_id="' +$(this).attr('field_id')+'"';
		var valid_fragment_meta = ['type', 'dateFrom', 'dateTo', 'style', 'rightsURI'];//valid input type to be put as attributes
		var this_box = this;
		$.each(valid_fragment_meta, function(index, value){
			var fragment_meta = '';
			var input_field = $('input[name='+value+']', this_box);
			if($(input_field).length>0 && $(input_field).val()!=''){
				fragment_meta += ' '+value+'="'+$(input_field).val()+'"';
			}
			if(this_fragment_type!='citationMetadata' && this_fragment_type!='coverage' && this_fragment_type!='relatedObject') fragment +=fragment_meta;
		});
		fragment +='>';
		//finish fragment header

		//onto the body of the fragment
		var parts = $(this).children('.aro_box_part');
		var subbox = $('.aro_subbox', this);

		if(parts.length > 0 && subbox.length==0){//if there is a part, data is spread out in parts
			$.each(parts, function(){
				/*
				 * If there is a part
				 * Usually there will be a type attribute for these part
				 * Special cases are dealt with using else ifs
				 * Generic case have 2 outcome, a tag with type attribute and no type attribute
				 * If there is no type attribute for the part itself, it's an element <name>value</name> thing
				 */
				if($(this).attr('type')){//if type is there for this part

					//deal with the type
					var type = $(this).attr('type');
					if(type=='relation'){//special case for related object relation
						fragment += '<'+type+' type="'+$('input[name=type]',this).val()+'">';
						if($('input[name=description]', this).val()!=''){//if the relation has a description
							fragment += '<description>'+$('input[name=description]', this).val()+'</description>';
						}
						if($('input[name=url]', this).val()!=''){//if the relation has a url
							fragment += '<url>'+$('input[name=url]', this).val()+'</url>';
						}
						fragment += '</'+type+'>';
					}else if(type=='relatedInfo'){//special case for relatedInfo
						//identifier is required
						fragment += '<identifier field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=identifier_type]', this).val()+'">'+$('input[name=identifier]', this).val()+'</identifier>';
						//title and notes are not required, but useful nonetheless
						if($('input[name=title]', this).val()!=''){
							fragment += '<title field_id="' +$(this).attr('field_id')+'">'+$('input[name=title]', this).val()+'</title>';
						}
						if($('input[name=notes]', this).val()!=''){
							fragment += '<notes field_id="' +$(this).attr('field_id')+'">'+$('input[name=notes]', this).val()+'</notes>';
						}
					}else if(type=='date'){
						var dates = $('.aro_box_part[type=date]', this);
						$.each(dates, function(){
							fragment += '<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'">';
							fragment += $('input[name=value]', this).val();
							fragment +='</'+$(this).attr('type')+'>';
						});
					}else if(type=='rightStatement' || type=='licence' || type=='accessRights' ){
						 fragment += '<'+$(this).attr('type')+' rightsUri="'+$('input[name=rightsUri]', this).val()+'">'+$('input[name=value]', this).val()+'</'+$(this).attr('type')+'>';	
					}else if(type=='contributor'){
						var contributors = $('.aro_box_part[type=contributor]', this);//tooltip not init
						$.each(contributors, function(){
							fragment += '<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" seq="'+$('input[name=seq]', this).val()+'">';
							var contrib_name_part = $('.aro_box_part', this);
							$.each(contrib_name_part, function(){
								fragment += '<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'">';
								fragment += $('input[name=value]', this).val();
								fragment +='</'+$(this).attr('type')+'>';
							});
							fragment +='</'+$(this).attr('type')+'>';
						});
					}else if(type=='dates_date'){
						fragment += '<date field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'" dateFormat="W3CDTF">';
						fragment += $('input[name=value]', this).val();
						fragment +='</date>';
					}else if(type=='temporal'){
						fragment+='<temporal';
						if(hasField) fragment += ' field_id="' +$(this).attr('field_id')+'"';
						fragment+='>';
						var dates = $('.aro_box_part[type=coverage_date]', this);
						$.each(dates, function(){
							fragment += '<date';
							if(hasField) fragment += ' field_id="' +$(this).attr('field_id')+'"';
							fragment += ' type="'+$('input[name=type]', this).val()+'" dateFormat="'+$('input[name=dateFormat]', this).val()+'">'+$('input[name=value]', this).val()+'</date>';	
						});
						var texts = $('.aro_box_part[type=text]', this);
						$.each(texts, function(){
							fragment += '<text';
							if(hasField) fragment += ' field_id="' +$(this).attr('field_id')+'"';
							fragment += '>'+$('input[name=value]', this).val()+'</text>';	
						});
						fragment+='</temporal>';
					}else{//generic
						//check if there is an input[name="type"] in this box_part so that we can use as a type attribute
						var type = $('input[name=type]', this).val();
						if(type){
							fragment += '<'+$(this).attr('type')
							if(hasField) fragment += ' field_id="' +$(this).attr('field_id')+'"';
							fragment += ' type="'+$('input[name=type]', this).val()+'">'+htmlEntities($('input[name=value]', this).val())+'</'+$(this).attr('type')+'>';	
						}else{
							var type = $(this).attr('type');
							fragment += '<'+type+' field_id="' +$(this).attr('field_id')+'">'+$('input[name=value]', this).val()+'</'+type+'>';
						}
					}
				}else{//it's an element
					fragment += '<'+$('input', this).attr('name')+' field_id="' +$(this).attr('field_id')+'">'+htmlEntities($('input', this).val())+'</'+$('input', this).attr('name')+'>';
				}
			});
		}else if(subbox.length==0){//data is right at this level, grab it!
			//check if there's a text area
			if($('textarea', this).length>0){
				fragment += htmlEntities($('textarea', this).val());
			}else if($('input[name=value]', this).length>0){
				fragment += $('input[name=value]', this).val();//there's no textarea, just normal input
			}
		}
			
		//check if there is any subbox content, this is a special case for LOCATION
		var sub_content = $(this).children('.aro_subbox');
		if(sub_content.length >0){
			//there are subcontent, for location
			$.each(sub_content, function(){
				var subbox_type = $(this).attr('type');
				var subbox_fragment ='';
				if(subbox_type !== 'spatial')
					subbox_fragment +='<'+subbox_type+'>';

				var parts = $(this).children('.aro_box_part');
				if(parts.length>0){
					$.each(parts, function(){
						var this_fragment = '';
						//opening tag
						if($(this).attr('type')=='electronic'){
							this_fragment +='<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'" field_id="' +$(this).attr('field_id')+'">';
							this_fragment +='<value>'+$('input[name=value]',this).val()+'</value>';
							//deal with args here
							var args = $('.aro_box_part', this);
							$.each(args, function(){
								this_fragment += '<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'" required="'+$('input[name=required]', this).val()+'" use="'+$('input[name=use]', this).val()+'">';
								this_fragment += $('input[name=value]', this).val();
								this_fragment +='</'+$(this).attr('type')+'>';
							});
							this_fragment +='</'+$(this).attr('type')+'>';//closing tag
						}else if($(this).attr('type')=='physical'){
							//deal with address parts here
							var address_parts = $('.aro_box_part', this);
							this_fragment +='<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'">';
							$.each(address_parts, function(){
								this_fragment += '<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'">';
								this_fragment += $('input[name=value]', this).val();
								this_fragment +='</'+$(this).attr('type')+'>';
							});
							this_fragment +='</'+$(this).attr('type')+'>';//closing tag
						}
						else if($(this).attr('type')=='spatial'){
							this_fragment += '<'+$(this).attr('type')+' field_id="' +$(this).attr('field_id')+'" type="'+$('input[name=type]', this).val()+'">';
							this_fragment += $('input[name=value]', this).val();
							this_fragment += '</'+$(this).attr('type')+'>';
						}
						else{
							//duh, if the type of this fragment being neither physical nor electronic, IT IS NOTHING!
							//or SPATIAL!!
						}
						
						subbox_fragment+=this_fragment;
					});
				}else{
					//no parts found
				}
				if(subbox_type !== 'spatial')
					subbox_fragment +='</'+subbox_type+'>';//closing tag
				fragment+=subbox_fragment;//add the sub box fragments to the main fragment
			});
		}

		fragment +='</'+$(this).attr('type')+'>';

		//SCENARIO on Access Policies

		if($(this).attr('type')=='fullCitation' || $(this).attr('type')=='citationMetadata'){
			fragment = '<citationInfo>'+fragment+'</citationInfo>';
		}

		xml += fragment;
		
	});
	 // xml=xml.replace(/<[\^>]+><\/[\S]+>/gim, "");
	return xml;
}

function formatQA(container){
    var tooltip = container;
    
    //wrap around the current tooltip with a div
    for(var i=1;i<=3;i++){
        $('*[level='+i+']', tooltip).wrapAll('<div class="qa_container" qld="'+i+'"></div>');
    }
    //add the toggle header
    $('.qa_container', tooltip).prepend('<div class="toggleQAtip"></div>');
    $('.toggleQAtip', tooltip).each(function(){
        if ($(this).parent().attr('qld') == 5)
            $(this).text('Gold Standard Record');
        else if($(this).parent().attr('qld') == 1)
            $(this).text('Quality Level 1 - Required RIF-CS Schema Elements');
        else if($(this).parent().attr('qld') == 2)
            $(this).html('Quality Level 2 - required Metadata Content Requirements.' );
        else if($(this).parent().attr('qld') == 3)
             $(this).html('Quality Level 3 - recommended Metadata Content Requirements.' );
    });
    //hide all qa
    $('.qa_container', tooltip).each(function(){
        $(this).children('.qa_ok, .qa_error').hide();
    });
    //show the first qa that has error
    var showThisQA = $('.qa_error:first', tooltip).parent();
    $(showThisQA).children().show();
    //coloring the qa that has error, the one that doesn't have error will be the default one
    $('.qa_container', tooltip).each(function(){
        if($(this).children('.qa_error').length>0){//has an error
            //$(this).children('.toggleQAtip').addClass('hasError');
            $(this).addClass('warning');
            $('.toggleQAtip', this).prepend('<span class="label label-important"><i class="icon-white icon-info-sign"></i></span> ');
        }else{
            $(this).addClass('success');
            $('.toggleQAtip', this).prepend('<span class="label label-success"><i class="icon-white icon-ok"></i></span> ');
        }
    });
    //bind the toggle header to open all the qa inside
    $('.toggleQAtip', tooltip).click(function(){
        $(this).parent().children('.qa_ok, .qa_error').slideToggle('fast');
    });
    $('.qa_ok').addClass('success');
    $('.qa_error').addClass('warning');
}