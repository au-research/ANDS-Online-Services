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
					default: logErrorOnScreen('this functionality is currently being worked on');break;
				}
				$('#data_source_view_container').attr('data_source_id', words[1]);
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
	$(window).hashchange();//initial hashchange event

	//switch view button binding
	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});

	load_more(1);//init the load_more function | load the first page

	//load_more button binding, once clicked will increment the page value
	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		load_more(page);
		$(this).attr('page', page++);
	});


	//item button binding
	$('.item-control .btn').live({
		click: function(e){
			e.preventDefault();
			var data_source_id = $(this).attr('data_source_id');
			if($(this).hasClass('view')){
				changeHashTo('view/'+data_source_id);
			}else if($(this).hasClass('edit')){
				changeHashTo('edit/'+data_source_id);
			}else if($(this).hasClass('delete')){
				changeHashTo('delete/'+data_source_id);
			}
		}
	});

	//data source chooser event
	$('#datasource-chooser').live({
		change: function(e){
			changeHashTo('view/'+$(this).val());
		}
	});

	//closing box header will go back in history
	$('.box-header .close').live({
		click: function(e){
			//changeHashTo('browse/'+currentView);
			window.history.back();
		}
	});

	$('.exportRecord').live({
		click: function(e){
		data_source_id = $('#data_source_view_container').attr('data_source_id');
		type = $(this).attr('type');
		var data = {};
		//let's construct the array using the form
		var form_data  = $('#data_source_export_form').serializeArray();
		form_data.push({name:"as",value:type});
		data = JSON.stringify(form_data);
		window.open(base_url+'data_source/exportDataSource/'+data_source_id+'?data='+data, '_blank');
		}
	})


	$('.dataSourceReport').live({
		click: function(e){
		data_source_id = $('#data_source_view_container').attr('data_source_id');
		type = $(this).attr('type');
		var data = {};
		//let's construct the array using the form
		var form_data  = $('#data_source_report_form').serializeArray();
		form_data.push({name:"as",value:type});
		data = JSON.stringify(form_data);
		window.open(base_url+'data_source/getDataSourceReport/'+data_source_id+'?data='+data, '_blank');
		}
	})
});

/*
 * Initialize the View
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [string] view ENUM thumbnails|lists
 * @returns: [void]
 */
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

/*
 * Initialize the view
 * This load the view for the next page, append that to the main #items container
 * @TODO: remove the next page div when there is no_more
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [int] page value
 * @returns: [void]
 */
function load_more(page){
	$.ajax({
		url: 'data_source/getDataSources/'+page,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#items').append(output);
		}
	});
}

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
		url: 'data_source/getDataSource/'+data_source_id,
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

			//bind the data source action button
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

			//draw the charts
			drawCharts(data_source_id);
			loadDataSourceLogs(data_source_id);
			loadContributorPages(data_source_id);

			//button toggling at edit level
			$('#view-datasource  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:false
				});
			});

			//list on the right hand side will take you to the manage my records screen with pre-filled filters
			$('.ro-list li').click(function(){
				var type = $(this).attr('type');
				var name = $(this).attr('name');
				var url_to = base_url+'registry_object/manage/'+data_source_id+'/'+suffix+'browse/thumbnails/'+type+'='+name;
				window.location = url_to;
			});
		}
	});
	return false;
}

/**
 * load the data source log
 * @param  {[type]} data_source_id [description]
 * @param  {[type]} offset         [description]
 * @param  {[type]} count          [description]
 * @return {[type]}                [description]
 */
function loadDataSourceLogs(data_source_id, offset, count)
{
	offset = typeof offset !== 'undefined' ? offset : 0;
	count = typeof count !== 'undefined' ? count : 10;
	// $('#data_source_log_container').html('Loading + + +');
	//$('#data_source_log_container').slideUp(500);

	$.ajax({
		url: 'data_source/getDataSourceLogs/',
		data: {id:data_source_id, offset:offset, count:count},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			var logsTemplate = $('#data_source_logs_template').html();
			var output = Mustache.render(logsTemplate, data);
			$('#data_source_log_container').append(output);

			$('#data_source_log_container').fadeIn(500);
			$('#log_summary').html('viewing ' + data.next_offset + ' of ' + data.log_size + ' log entries');
			//$('#log_summary_bottom').html('viewing ' + data.next_offset + ' of ' + data.log_size + ' log entries');
			$('#show_more_log').attr('next_offset', data.next_offset);
			if(data.next_offset=='all'){
				$('#show_more_log').remove();
			}
			var bottom_offset = $('#data_source_log_container').offset().top + $('#data_source_log_container').height();
			$('body').animate({"scrollTop": bottom_offset}, 100);
		}
	});

	$('#show_more_log').die().live({
		click:function(){
			var next_offset = $(this).attr('next_offset');
			loadDataSourceLogs(data_source_id, next_offset, count);
		}
	});

	return false;

}

function loadHarvestLogs(data_source_id, logid){
	console.log("logID: " + logid);
	$.ajax({
		url: 'data_source/getDataSourceLogs/',
		data: {id:data_source_id, logid:logid},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			var logsTemplate = "<table class='table table-hover'>"+
			"<thead><tr><th>#</th><th>DATE</th><th>TYPE</th><th>LOG</th></tr></thead>" +
			"<tbody>" +
			"{{#items}}" +
				"<tr class='{{type}}'><td>{{id}}</td><td>{{date_modified}}</td><td>{{type}}</td><td>{{log}}</td></tr>" +
			"{{/items}}" +
			"</tbody></table>";
			var output = Mustache.render(logsTemplate, data);
			$('#test_harvest_activity_log .modal-body').html(output);
			$.each(data.items, function(i, v) {
			    if (v.log.indexOf("TEST HARVEST COMPLETED") >= 0) {
			        return false;
			    }
			    setTimeout(function(){loadHarvestLogs(data_source_id, logid)}, 2000);
			});
			
			
		},
		error: function(data){
		console.log(data);
		}
	});
	

	
	return false;

}

function loadContributorPages(data_source_id)
{

	$.ajax({
		url: 'data_source/getContributorGroups/',
		data: {id:data_source_id},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			//console.log(data.contributorPages)
			var contributorsTemplate = "<p>"+data.contributorPages+"</p>"+
			"<table class='table table-hover'>"+
			"<thead><tr><th align='left'>GROUP</th><th>Contributor Page</th></tr></thead>" +
			"<tbody>" +
			"{{#items}}" +
				"<tr ><td>{{group}}</td><td>{{contributor_page}}</td></tr>" +
			"{{/items}}" +
			"</tbody></table>";
			var output = Mustache.render(contributorsTemplate, data);
			$('#contributor_groups').html(output);				
		},
		error: function(data){
		console.log(data);
		}
	});
	
	return false;
}

function loadContributorPagesEdit(data_source_id)
{

	$.ajax({
		url: 'data_source/getContributorGroups/',
		data: {id:data_source_id},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			//console.log(data.contributorPages)
			var thePageFields = "{{contributor_page}}"
			var contributorsTemplate = "<table class='table table-hover'>"+
			"<thead><tr><th align='left'>GROUP</th><th>Contributor Page</th></tr></thead>" +
			"<tbody>" +
			"{{#items}}" +
				"<tr ><td>{{group}}</td><td><input type='text' name='{{group}}' value='"+thePageFields+"'/></td></tr>" +
			"{{/items}}" +
			"</tbody></table>";
			var output = Mustache.render(contributorsTemplate, data);
			$('#contributor_groups').html(output);	
			$('#contributor_groups2').html(output);				
		},
		error: function(data){
		console.log(data);
		}
	});
	
	return false;

}

function drawCharts(data_source_id){
	
	/* Draw registry object progression chart */
	$.ajax({
		url: 'charts/getRegistryObjectProgression/' + data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);

			$('#ro-progression').height('350').html('');

			var options = {
				axes:{
			        xaxis:{
			              renderer:$.jqplot.DateAxisRenderer, 
				          tickOptions:{formatString:data.formatString},
			        },
			    },
			    seriesDefaults: {renderer:$.jqplot.BezierCurveRenderer},
			    series:[{lineWidth:4, rendererOptions: {
                    smooth: true
                }}],
			    highlighter: {
			        show: true,
			        sizeAdjust: 7.5,
			        yvalues: 1,
				    yvalues: 3,
    				formatString:'%s: %s registry objects'
			    },
			    cursor: {
			        show: false
			    }
			};

			$.jqplot('ro-progression', [data.table] , options);

		},
		failure: function(data){
			$('#ro-progression').height('350').html('Unable to load chart...');
		}
	});
}

/*
 * Validate the fields and values 
 * @params jsonData
 * @return string
 */
function validateFields(jsonData){

	var errorStr = '';

	if(included(jsonData,'create_primary_relationships') && (!included(jsonData,'class_1')||!included(jsonData,'primary_key_1')||!included(jsonData,'service_rel_1')||!included(jsonData,'activity_rel_1')||!included(jsonData,'party_rel_1')||!included(jsonData,'collection_rel_1')))
	{
		errorStr = errorStr + "You must provide a class ,registered key and all relationship types for the primary relationship.<br /><br />";

	
		if(included(jsonData,'class_2') && (!included(jsonData,'primary_key_2')||!included(jsonData,'service_rel_2')||!included(jsonData,'activity_rel_2')||!included(jsonData,'collection_rel_2')||!included(jsonData,'party_rel_2')))
		{
			errorStr = errorStr +  "You must provide a registered key and all relationship types for the 2nd primary relationship.<br />";	
		}

	}

	if(included(jsonData,'push_to_nla') && !included(jsonData,'isil_value'))
	{
		errorStr = errorStr + "If you select 'Party records to NLA' you must provide an ISIL value <br />";

	}	

	return  errorStr;
}


function included(arr, obj) {
    for(var i=0; i<arr.length; i++) {
        if (arr[i]['name'] == obj) 
        { 
        	if(arr[i]['name']=='create_primary_relationships' && !arr[i]['value'])
        	{
        		return false;
        	}
        	if(!arr[i]['value'])
        	{
        		return false;
        	}
        	return true;
        }
    }
}

/*
 * Load a datasource edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return [void]
 */
function load_datasource_edit(data_source_id, active_tab){
	$('#edit-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$('#view-datasources').slideUp(500);
	$.ajax({
		url: 'data_source/getDataSource/'+data_source_id,
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
			
			$('#edit-datasource  .normal-toggle-button').each(function(){

				if($(this).hasClass('create-primary')){
						if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
							$(this).find('input').attr('checked', 'checked');
							$('#primary-relationship-form').show();
						}
						$(this).toggleButtons({
							width:75,enable:true,
							onChange:function(){
								$(this).find('input').attr('checked', 'checked');
								$('#primary-relationship-form').toggle();
							}
						});	
				}else if ($(this).hasClass('push_to_nla')){
					if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
						$(this).find('input').attr('checked', 'checked');
						$('#nla-push-div').toggle();
					}
					$(this).toggleButtons({
						width:75,enable:true,
						onChange:function(){
							$(this).find('input').attr('checked', 'checked');
							$('#nla-push-div').toggle();
						}
					});
				}else{
					if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
						$(this).find('input').attr('checked', 'checked');
					}
					$(this).toggleButtons({
						width:75,enable:true,
						onChange:function(){		
							$(this).find('input').attr('checked', 'checked');
						}
					});				
				}
				

			});

			$('#edit-datasource  .contributor-page').each(function(){
				if($('#institution_pages').val()=='') {$('#institution_pages').val('0'); }
				if($(this).attr('value')== $('#institution_pages').val() ){
					$(this).attr('checked', 'checked');
				}
			});

			$('#edit-datasource  .contributor-page').live().change(function(){
				$(this).attr('checked', 'checked');
				$('#institution_pages').val($(this).val());				
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
			
			//initalize the datepicker, format is optional
			$('#edit-datasource  .datepicker').datepicker({
				format: 'yyyy-mm-dd'
			});
			//triggering the datepicker by focusing on it
			$('.triggerDatePicker').die().live({
				click: function(e){
				$(this).parent().children('input').focus();
				}
			});

		}
	});

	loadContributorPagesEdit(data_source_id);
	return false;
}


$('#save-edit-form').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');
		
		jsonData.push({name:'data_source_id', value:$('#data_source_view_container').attr('data_source_id')});
		
		$('#edit-datasource #edit-form input, #edit-datasource #edit-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();

			if($(this).attr('type')=='checkbox'){
				var label = $(this).attr('for');
				var value = $(this).is(':checked');
			}

			if($(this).attr('type')!='radio'){
			//if(value!='' && value){
				//console.log(label + " will be set to " + value)
				jsonData.push({name:label, value:value});
			//}
			}
		});


		$('#edit-datasource #edit-form select').each(function(){
			label = $(this).attr('for');
			jsonData.push({name:label, value:$(this).val()});
		});



		//var validationErrors = validateFields(jsonData);

		var form = $('#edit-form');
		var valid = Core_checkValidForm(form);
		//console.log(valid);
		
		$.ajax({
			url:'data_source/updateDataSource', 
			type: 'POST',
			data: jsonData,
			success: function(data){
				if (!data.status == "OK"){
					$('#myModal').modal();
					logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
					$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
				}else{
					changeHashTo('view/'+$('#data_source_view_container').attr('data_source_id'));
					createGrowl("Your Data Source was successfully updated");
					updateGrowls();
				}
			},
			error: function(){
				$('#myModal').modal();
				logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
				$('#myModal .modal-body').append("<br/><pre>Could't communicate with server</pre>");
			}
		});
		
		/*var jsonString = ""+JSON.stringify(jsonData);
		$('#myModal .modal-body').html('<pre>'+jsonString+'</pre>');
		$('#myModal').modal();*/
		$(this).button('reset');
	}
});


$('#importFromHarvesterLink').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];

		jsonData.push({name:'data_source_id', value:$('#data_source_view_container').attr('data_source_id')});

		$.ajax({
			url:'data_source/triggerHarvest', 
			type: 'POST',
			data: jsonData,
			dataType: 'json',
			success: function(data){
				if (data.status == "OK")
				{
					changeHashTo('view/'+$('#data_source_view_container').attr('data_source_id'));
					createGrowl("Your harvest was successfully queued for harvest. Check the Activity Log below.");
					updateGrowls();
				}
			},
			error: function()
			{
				logErrorOnScreen("An error occured whilst testing your harvest!");
			}
		});

	}
});

$('#test-harvest').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('Running...');
		
		jsonData.push({name:'data_source_id', value:$('#data_source_view_container').attr('data_source_id')});
		
		$('#edit-datasource #edit-form input, #edit-datasource #edit-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();
			if($(this).attr('type')=='checkbox'){
				var label = $(this).attr('for');
				var value = $(this).is(':checked');
			}
			if(value!='' && value){
				jsonData.push({name:label, value:value});
			}
		});

		$.ajax({
			url:'data_source/testHarvest', 
			type: 'POST',
			data: jsonData,
			success: function(data){	
				console.log(data);
					if (data.status == "OK")
					{
						$('#test_harvest_activity_log').modal();
						loadHarvestLogs($('#data_source_view_container').attr('data_source_id'), data.logid);
					}
					else
					{
						$('#test_harvest_activity_log').modal();
						logErrorOnScreen("An error occured whilst testing your harvest!", $('#test_harvest_activity_log .modal-body'));
					}
			},
			error: function()
			{
				$('#test_harvest_activity_log').modal();
				logErrorOnScreen("An error occured whilst testing your harvest!", $('#test_harvest_activity_log .modal-body'));
			}
			
		});

		$(this).button('reset');
	}
});

$('#importRecordsFromURLModal .doImportRecords').live({
	click: function(e){
		var thisForm = $('#importRecordsFromURLModal');
		$('#remoteSourceURLDisplay', thisForm).html($('form input[name="url"]').val());
		/* fire off the ajax request */
		$.ajax({
			url:'importFromURLtoDataSource', 
			type: 'POST',
			data:	{ 
				url: $('form input[name="url"]').val(), 
				data_source_id: $('#data_source_view_container').attr('data_source_id') 
			}, 
			success: function(data)
					{
						var thisForm = $('#importRecordsFromURLModal');
						thisForm.attr('loaded','true');
						var output = '';
				
						if(data.response == "success")
						{
							output = Mustache.render($('#import-screen-success-report-template').html(), data);
							$('.modal-body', thisForm).hide();
							$('div[name=resultScreen]', thisForm).html(output).fadeIn();
						}
						else
						{
							$('.modal-body', thisForm).hide();
							logErrorOnScreen(data.message, $('div[name=resultScreen]', thisForm));
							$('div[name=resultScreen]', thisForm).append("<pre>" + data.log + "</pre>");
							$('div[name=resultScreen]', thisForm).fadeIn();
						}
						$('.modal-footer a').toggle();

					}, 
			error: function(data)
					{
						$('.modal-body', thisForm).hide();
						logErrorOnScreen(data.responseText, $('div[name=resultScreen]', thisForm));
						$('div[name=resultScreen]', thisForm).fadeIn();
						$('.modal-footer a').toggle();
					},
			dataType: 'json'
		});
		
		$(this).button('loading');
		
		if (thisForm.attr('loaded') != 'true')
		{
			$('.modal-body', thisForm).hide();
			$('div[name=loadingScreen]', thisForm).fadeIn();
		}
		
		$('#importRecordsFromURLModal').on('hide',
			function()
			{
				load_datasource($('#data_source_view_container').attr('data_source_id'));
				thisForm.attr('loaded','false');
				$('.modal-footer a').hide();
				$('#importRecordsFromURLModal .doImportRecords').button('reset').show();
				$('.modal-body', thisForm).hide();
				$('div[name=selectionScreen]', thisForm).show();
			}
				
		);
						
		
	}
});

$('#importRecordsFromXMLModal .doImportRecords').live({
	click: function(e){
		var thisForm = $('#importRecordsFromXMLModal');
		/* fire off the ajax request */
		$.ajax({
			url:'importFromXMLPasteToDataSource', 	//?XDEBUG_TRACE=1', //XDEBUG_PROFILE=1&
			type: 'POST',
			data:	{ 
				xml: $('#xml_paste').val(), 
				data_source_id: $('#data_source_view_container').attr('data_source_id') 
			}, 
			success: function(data)
					{
						var thisForm = $('#importRecordsFromXMLModal');
						thisForm.attr('loaded','true');
						var output = '';
				
						if(data.response == "success")
						{
							output = Mustache.render($('#import-screen-success-report-template').html(), data);
							$('.modal-body', thisForm).hide();
							$('div[name=resultScreen]', thisForm).html(output).fadeIn();
						}
						else
						{
								$('.modal-body', thisForm).hide();
								logErrorOnScreen(data.message, $('div[name=resultScreen]', thisForm));
								$('div[name=resultScreen]', thisForm).append("<pre>" + data.log + "</pre>");
								$('div[name=resultScreen]', thisForm).fadeIn();
						}
						$('.modal-footer a').toggle();

					}, 
			error: function(data)
					{
						$('.modal-body', thisForm).hide();
						logErrorOnScreen(data.responseText, $('div[name=resultScreen]', thisForm));
						$('div[name=resultScreen]', thisForm).fadeIn();
						$('.modal-footer a').toggle();
					},
			dataType: 'json'
		});
		
		$(this).button('loading');
		
		if (thisForm.attr('loaded') != 'true')
		{
			$('.modal-body', thisForm).hide();
			$('div[name=loadingScreen]', thisForm).fadeIn();
		}
		
		$('#importRecordsFromXMLModal').on('hide',
			function()
			{
				load_datasource($('#data_source_view_container').attr('data_source_id'));
				thisForm.attr('loaded','false');
				$('.modal-footer a').hide();
				$('#importRecordsFromXMLModal .doImportRecords').button('reset').show();
				$('.modal-body', thisForm).hide();
				$('div[name=selectionScreen]', thisForm).show();
			}
				
		);
						
		
	}
});