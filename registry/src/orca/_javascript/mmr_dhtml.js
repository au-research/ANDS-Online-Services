/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/

var STATUS_COOKIE_NAME = 'ORCA_MMR_STATUS';
var STATUS_COOKIE_TTL_DAYS = 365*5;

var MMR_datasource_info_visible = true;

// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

var currentView;//can be status or quality

     
$(document).ready(function() {

	$(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
	if($.cookie('currentView')){
		currentView = $.cookie('currentView');
	}else{
		currentView = 'statusview';
		$.cookie('currentView', 'statusview');
	}
	//console.log($.cookie('currentView'));


	//MMR Tables
	var dsKey = $('#dataSourceKey').val();


	var orcaQA = false;
	if($('#orcaQA').text()=='yes'){
		orcaQA = true;
	}
	var orcaLIASON = false;
	if($('#orcaLIASON').text()=='yes'){
		orcaLIASON = true;
	}

	view(currentView, 'All');


	function view(type, status){
		//console.log('type='+type+' status='+status);
		$('.tab-content').hide();
		if(type=='statusview'){
			$('.qaview').hide();
			$('.viewswitch').removeClass('pressed');
			$('.viewswitch[name=statusview]').addClass('pressed');
			if(status=='All'){//open all statuses except for more work required (if there is none)
				$('.statusview').show();
				if($('.MORE_WORK_REQUIRED_table').find('.ftitle').attr('count')==0){
					$('.MORE_WORK_REQUIRED_table').hide();
					//console.log('hiding');
				}
			}else{//is a specific status
				$('.statusview').each(function(){
					if($(this).attr('id')==status){
						$(this).show();
					}
				});
			}
		}else if(type=='qaview'){
			google.setOnLoadCallback(drawChart(status, dsKey));
			$('.statusview').hide();
			$('.viewswitch').removeClass('pressed');
			$('.viewswitch[name=qaview]').addClass('pressed');
			if(status=='All'){
				$('.qaview[id=All_qaview]').show();
			}else{//is a specific status
				$('.qaview[id='+status+'_qaview]').show();
			}
		}
	}

	$('.tab').live('click', function(){
		if(!$(this).hasClass('inactive')){//only for tab that is active
			$('.tab').removeClass('active-tab');
			$(this).addClass('active-tab');//make this tab active and other tab not active (doesn't mean inactive)
			var name = $(this).attr('name');
			view(currentView, name);
		}
	});


	$('.viewswitch').live('click', function(){
		$('.viewswitch').removeClass('pressed');
		$(this).addClass('pressed');
		var name = $(this).attr('name');
		$.cookie('currentView', name);
		$('.'+name).show();
		view(name, 'All');
	});


	function drawChart(status, ds) {
        // Create the data table.
        var chartData = new google.visualization.DataTable();

        $.ajax({
    		url: 'get_view.php?view=statusCount&status='+status+'&ds='+ds,
    		method: 'get',
    		data: {},
    		dataType:'json',
    		success: function(data) {
    			var qualityLevels = data.facet_counts.facet_fields.quality_level;    		
    			//console.log(qualityLevels);

    			chartData.addColumn('string', 'QA Level');
    			chartData.addColumn('number', 'level');

    			var resultArray = [];
    			for (var i = qualityLevels.length - 2; i >= 0; i=i-2) {
        			//console.log(qualityLevels[i]);
        			var result = [];
        			result.push('QA Level '+ qualityLevels[i], qualityLevels[i+1])
        			resultArray.push(result);
        			//chartData.addColumn('string', 'QA Level '+qualityLevels[i]);
        		};

        		
        		
        		//console.log(resultArray);
        		chartData.addRows(resultArray);

        		var options = {'title':status+' Records',
                       'width':400,
                       'height':300,
                   		backgroundColor: { fill:'transparent' }};
                // Instantiate and draw our chart, passing in some options.
        		var chart = new google.visualization.PieChart(document.getElementById(status+'_qaview'));
        		chart.draw(chartData, options);
        	}
        });
        

        /*data.addColumn('string', 'Status');
        data.addColumn('number', 'QA Level 1');
        data.addColumn('number', 'QA Level 2');
        data.addRows([
          ['PUBLISHED', 3,26],
          ['DRAFT', 1,15]
        ]);

        // Set chart options
        var options = {'title':'All Records',
                       'width':400,
                       'height':300,
                   backgroundColor: { fill:'transparent' }};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById(id));
        chart.draw(data, options);*/
      }

	$('.mmr_table').each(function(){
		var status = $(this).attr('name');
		var count = $(this).attr('count');

		var buttons = [
			{name: 'Select All', bclass: 'button', onpress : selectAll}
		];
		if(status=="DRAFT"){
			buttons.push({name: 'Submit for Assessment', bclass: 'submit_for_assessment', onpress : doCommand});
			buttons.push({name: 'Delete Draft', bclass: 'delete', onpress : doCommand});
		}else if(status=="SUBMITTED_FOR_ASSESSMENT"){
			if(orcaQA){
				buttons.push({name: 'Start Assessment', bclass: 'start_assessment', onpress : doCommand});
			}
			if(orcaLIASON){
				buttons.push({name: 'Revert to Draft', bclass: 'revert_to_draft', onpress : doCommand});
			}
		}else if(status=="ASSESSMENT_IN_PROGRESS"){
			if(orcaQA){
				buttons.push({name: 'Approve', bclass: 'approve', onpress : doCommand});
				buttons.push({name: 'More Work Required', bclass: 'more_work_required', onpress : doCommand});
			}
		}else if(status=="APPROVED"){
			buttons.push({name: 'Publish', bclass: 'publish', onpress : doCommand});
			buttons.push({name: 'Delete Record', bclass: 'delete', onpress : doCommand});
		}else if(status=="PUBLISHED"){

			if(orcaQA){
				buttons.push({name: 'Mark as Gold Standard', bclass: 'mark_gold_standard', onpress : doCommand});
			}

			buttons.push({name: 'Delete Record', bclass: 'delete', onpress : doCommand});
		}
		//buttons.push({separator:true});
		
		$(this).flexigrid({
			striped:true,
			title:status,
			showTableToggleBtn: true,

			showToggleBtn: true,
            url: 'get_view.php?view=status&status='+status+'&ds='+dsKey,
			dataType: 'json',
			usepager: true,
			colModel : [
			{display: 'recordKey', name:'key', width:70, sortable: true, align:'left'},
                {display: 'Name/Title', name : 'list_title', width : 350, sortable : true, align: 'left'},
                {display: 'Last Modified', name : 'date_modified', width : 100, sortable : true, align: 'left'},
                {display: 'Class', name : 'class', width : 50, sortable : true, align: 'left'},
                {display: 'Errors', name : 'error_count', width : 50, sortable : true, align: 'left'},
                {display: 'Quality Level', name : 'quality_level', width : 50, sortable : true, align: 'left'},
                {display: 'Flag', name : 'flag', width : 50, sortable : false, align: 'left'},
                {display: 'Options', name : 'buttons', width : 150, sortable : false, align: 'left'},
                {display: 'Status', name : 'status', width : 150, sortable : true, align: 'left'},
                {display: 'Manually Assessed', name : 'manually_assessed_flag', width : 150, sortable : true, align: 'left', hide:true}
            ],
            buttons:buttons,
            resizable:true,
            useRp: true,
			rp: 10,
			pagestat: 'Displaying {from} to {to} of {total} records',
			nomsg: 'No records found',

            height:'auto',
            additionalClass:status+'_table',
            searchitems : [
                        {display: 'Name/Title', name : 'list_title'}
                        ],
            onSuccess: hideInfo
		});
		if(count=='0'){
			$(this).parent().parent().find('.ptogtitle').click();
			//console.log(count);
		}
	});


	function hideInfo(com, grid){
		$('.infoDiv', grid).hide();
		$('td[abbr=status]').each(function(){
			$(this).addClass($(this).text()+'_status');
		});

	}


	
	/**
	BUTTONS
	**/
	function doCommand(com, grid) {

		//setup the keys
		var targetKeys = [];
		if($(grid).attr('selectall')=='no'){
			$('.trSelected', grid).each(function() {
				var id = $(this).attr('id');
				id = id.substring(id.lastIndexOf('row')+3);
				//alert("Edit row " + id);
				targetKeys.push(id);
			});
			fireZaLaserz(com, targetKeys);
		}else if($(grid).attr('selectall')=='yes'){
			var status = $(grid).attr('status');
			$.ajax({
				url: 'get_view.php?view=allKeys&status='+status+'&ds='+dsKey,
				dataType: 'json',
				success: function(data) {
					docs = data.response.docs;
					$(docs).each(function(){
						targetKeys.push(this.key);
					});
				fireZaLaserz(com, targetKeys);
				}
			});
		}else{
			alert('No command to be executed');
			return false;
		}

		//Reindex all the target Keys
	}

	function fireZaLaserz(com, targetKeys){
		var numKeys = (targetKeys).length;
		var dataSourceKey = $('#dataSourceKey').val();

		//if there is none
		if(numKeys==0){
			alert('Please select');
			return false;
		}

		//setup actions
		if (com == 'Edit') {
			
		}else if (com == 'Delete Record') {
			if(confirm('You are about to delete '+numKeys+' records')){
				action = 'DELETE_RECORD';
			}
		}else if(com=='Delete Draft'){
			if(confirm('You are about to delete '+numKeys+' drafts. This draft will be permanently deleted and cannot be restored. Do you want to continue?')){
				action = 'DELETE_DRAFT';
			}
		}else if(com=='Submit for Assessment'){
			action = 'SUBMIT_FOR_ASSESSMENT';
		}else if(com=='Revert to Draft'){
			action = 'BACK_TO_DRAFT';
		}else if(com=='Start Assessment'){
			action = 'START_ASSESSMENT';
		}else if(com=='More Work Required'){
			action = 'MORE_WORK_REQUIRED';
			$('#MORE_WORK_REQUIRED').show();
		}else if(com=='Approve'){
			action = 'APPROVE';
		}else if(com=='Publish'){
			action = 'PUBLISH';
		}
		//alert($("#elementSourceURL").val());


		//POST the stuff over to Manage My Records
		var isPreApproval = false;
		$.post(
			$("#elementSourceURL").val() + "task=manage_my_records&action=" + action,
			{
				'keys[]' : targetKeys,
				'preapproval' : isPreApproval,
				'dataSourceKey' : dataSourceKey
			},
			function(data)
			{
				//console.log(data);
				if (data['responsecode'] === 0)
				{
					// Error occured
					alert("Error Occured: Access Denied.");
				}
				else if (data['responsecode'] == "MT008")
				{
					$('#mmr_datasource_alert_msg').html('Your records have been sent to ANDS for assessment and approval. You should contact your ANDS Client Liaison Officer to notify them that these records have been submitted.');
				}
				else if (data['responsecode'] == "MT014")
				{
					$('#mmr_datasource_alert_msg').html('An ANDS Quality Assessor has been notified of your submitted record(s)');
				}
				else
				{
					if (data['alert'])
					{
						console.log(data['alert']);
						$('#mmr_datasource_alert_msg').html(data['alert']);
					}
				}
				$('#indexDS').click();
			},
			'json'
		);
	}



	function selectAll(com, grid){
		if($(this).text()=='Select All'){
			$('tbody tr', grid).addClass('trSelected');

			//console.log($('.ftitle',grid));
			var total = parseInt($('.ftitle', grid).attr('count'));
			var rp = parseInt($('.ftitle', grid).attr('rp'));
			var message = '';var showInfo = false;
			if(rp < total){
				message += 'All '+rp+' records on this page are selected. <a href="javascript:void(0);" class="selectAll">Select All '+total+' records</a>. ';
				showInfo = true;
			}
			

			flaggedRecords = $(grid).find('.icon28sOn');
			if(flaggedRecords.length > 0){
				message +='<a href="javascript:void(0);" class="selectFlagged">Select only flagged record </a> on this page. '; 
				showInfo = true;
			}

			if(showInfo){
				$('.infoDiv', grid).html(message);
				$('.infoDiv', grid).show();
			}

			$(this).html('<a class="button smaller left">Deselect All</a>');
		}else{
			$(grid).attr('selectall', 'no');
			$('tbody tr', grid).removeClass('trSelected');
			$('.infoDiv', grid).hide();
			$(this).html('<a class="button smaller left">Select All</a>');
		}
	}

	$('.selectAll').live('click', function(){
		var grid = $(this).parent().parent().parent();
		$(grid).attr('selectall', 'yes');
		var total = parseInt($('.ftitle', grid).attr('count'));
		$(this).parent().html('All '+total+' records are selected');
	});

	$('.selectFlagged').live('click', function(){
		var grid = $(this).parent().parent().parent();
		var flaggedRecords = $(grid).find('.icon28sOn');
		$('tr', grid).removeClass('trSelected');
		$.each(flaggedRecords, function(index){
			$(this).parents('tr').addClass('trSelected');
		});
		$('.infoDiv', grid).html('All '+flaggedRecords.length+' flagged records selected');
	});

	//delete Confirm
	$('.deleteConfirm').live('click', function(){
		return confirm('You are about to delete 1 record. Do you want to continue?');
	});
	

	//Tooltip for the (more details)
	$('a.pop[title]').qtip({
		content:{
			text: $('#mmr_ds_moredetails'),
			title: {
				text: 'About Manage My Records'
				
			}
		},
		position: {
			my: 'top center', // Use the corner...
			at: 'bottom center' // ...and opposite corner
		},
		style: {
			classes: 'ui-tooltip-shadow ui-tooltip-jtools'
		},
		show: {
			event: 'click',
			effect: function(offset) {
				$(this).slideDown(100); // "this" refers to the tooltip
			}
		},
		hide: {
			fixed:true,
			delay: 1000
		}
	});

	//tooltip for everything that has class tip and has an attribute of tip

	$('a.tip').live('mouseover', function(){
		$(this).qtip({
			content:{
				text: $(this).attr('tip')
			},
			position: {
				my: 'bottom center', // Use the corner...
				at: 'top center' // ...and opposite corner
			},
			style: {
				classes: 'ui-tooltip-shadow ui-tooltip-dark'
			},
			show: {
         		ready: true // Needed to make it show on first mouseover event
      		},
      		overwrite: false
		});
	});

	//Flag Status button
	$('.flagToggle').live('click', function(e){

		var flag;
		if($(this).hasClass('icon28sOn')){
			flag = false;			
		}else{
			flag = true;
		}
		$(this).toggleClass('icon28sOff');
		$(this).toggleClass('icon28sOn');
		e.stopPropagation()
		var rowID = $(this).parent().parent().parent().attr('id');
		var key = rowID.substring(rowID.lastIndexOf('row')+3);
		var status = $('.flagToggle').parents('.flexigrid').attr('status');
		if(status=='APPROVED' || status=='PUBLISHED'){//is not draft
			$.get($("#elementSourceURL").val() + "task=flag_regobj&key=" + encodeURIComponent(key) + "&flag=" + flag);
			//console.log($("#elementSourceURL").val() + "task=flag_regobj&key=" + encodeURIComponent(key) + "&flag=" + flag);
		}else{
			$.get($("#elementSourceURL").val() + "task=flag_draft&data_source=" + encodeURIComponent($("#dataSourceKey").val()) + "&key=" + encodeURIComponent(key) + "&flag=" + flag);	
		}
		//$.get($("#elementSourceURL").val() + "task=flag_draft&data_source=" + encodeURIComponent($("#dataSourceKey").val()) + "&key=" + encodeURIComponent(key) + "&flag=" + flag);
		//$.get($("#elementSourceURL").val() + "task=flag_regobj&key=" + encodeURIComponent(key) + "&flag=" + flag);
		//console.log('setting '+rowID+' as gold standard');
	});


	


	



	$('#indexDS').live('click', function(){
		$('#indexDS').html('Quality Checking...');
		$('.tab-content').css('opacity',0.5);
		$.ajax({
			url:$('#checkQualityURL').val(),
			success:function(data){
				$('#indexDS').html('Clearing Index...')
				$.ajax({
					url:$('#clearIndexURL').val(),
					success:function(data){
						$('#indexDS').html('Generate Cache...')
						$.ajax({
							url:$('#generateCacheURL').val(),
							success:function(data){
								$('#indexDS').html('Reindexing....');
								$.ajax({
									url:$('#reindexURL').val(),
									success:function(data){
										$('#indexDS').html('<span></span>');
								    	$('.tab-content').css('opacity',1.0);
								    	//location.reload();
										$('.mmr_table').each(function(){
											$(this).flexReload();
										});
									}
								});
							}
						});
					}
				});			
			}
		});
	});







	/**
	OLD
	**/
























	// Help lower resolution screens by expanding the outermost page containers to the fixed-width size of the MMR table
	$('body > div').css('min-width','1235px');
	
	$.blockUI.defaults.css.width = '503px';
	
	MMR_initStatusCookie();
	
	if(MMR_datasource_info_visible == "false")
	{
		$('#mmr_datasource_information').hide();
		$('#mmr_information_show').show();
	}
	else
	{
		$('#mmr_datasource_information').show();
		$('#mmr_information_show').hide();
	}

	
	$('#mmr_information_hide').live('click', function(e){
		e.preventDefault();
		MMR_toggleInfoVisible();
	});
	
	$("#mmr_information_show").live("click", function (e) {
		e.preventDefault();
		MMR_toggleInfoVisible();
	});
	
	
	$(".mmr_expandable_table > tbody").each(function() {
		$("tr:gt(7)", this).hide(); 
	   	$("tr:nth-child(9)", this).after("<tr class='mmr_more_records_link'><td colspan='10'></td><td><a href='#'>Show more..</a></td></tr>");
	});
	$(".mmr_more_records_link").live("click", function() {
		var tr = $(this).parent();
	   	tr.children(".record_row").show();
	   	$(this).remove();
	   	$("tr:last", tr).after("<tr class='mmr_less_records_link'><td colspan='10'></td><td><a href='#'>Show less..</a></td></tr>");
	   	return false;
	});
	$(".mmr_less_records_link").live("click", function() {
		var tr = $(this).parent();
	   	$(this).remove();
	   	$("tr:gt(7)", tr).hide(); 
	   	$("tr:nth-child(9)",tr).after("<tr class='mmr_more_records_link'><td colspan='10'></td><td><a href='#'>Show more..</a></td></tr>");
	   	tr.animate({ scrollTop: 0 }, 'slow');
	   	return false;
	});
	
	$("tr.record_row > td:not(.rowSelector):not(.mmr_flag)").live("click", function() {
		
		var select = $(".mmr_select_box", $(this).parent());

		
		if (select.attr("checked") == false)
		{		
			//select.attr("checked", true);
			select.click();
			select.parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
		}
		else
		{
			//select.attr("checked",false);
			select.click();
			select.parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
		}

	});
	

	$(".mmr_flag > .not_flagged").live("click", function() {
		
		if ($(this).parent().hasClass("is_draft"))
		{
			$(this).parent().children().removeClass("hide");
			MMR_setDraftFlag($(this).parent().parent().attr("name"), "true");
			$(this).addClass("hide");
		}
		else
		{
			$(this).parent().children().removeClass("hide");
			MMR_setRegObjFlag($(this).parent().parent().attr("name"), "true");
			$(this).addClass("hide");
		}
	});

	$(".mmr_flag > .flagged").live("click", function(e) {
		if ($(this).parent().hasClass("is_draft"))
		{
			$(this).parent().children().removeClass("hide");
			MMR_setDraftFlag($(this).parent().parent().attr("name"), "false");
			$(this).addClass("hide");
		}
		else
		{
			$(this).parent().children().removeClass("hide");
			MMR_setRegObjFlag($(this).parent().parent().attr("name"), "false");
			$(this).addClass("hide");
		}
		
	});
	
	$(".mmr_select_all_button").live("click", function() {
		var tbl = $(this).parent().parent().parent();
		if ($(this).val()=="deselect all")
		{
			$(".mmr_select_banner", tbl).hide();
			var selectedRows = $(".mmr_select_box", tbl).filter(":checked").click().parent().parent();
			selectedRows.children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
			$(".mmr_button_row > input:enabled", $(this).parents("table.mmr_expandable_table")).attr("disabled","disabled");
			$(this).val("select all");
		}
		else
		{
			var flaggedRecordsExist = ($("tr", tbl).has("td.mmr_flag > .flagged:not(.hide)").length > 0);
			var unshownRecords = $("tr.record_row:not(:visible)", tbl).length;
			if (unshownRecords > 0)
			{
				$(".mmr_select_banner", tbl).children("td").html("There are " + unshownRecords + " more record(s) in this status category that are not visible. Do you want to <a class='mmr_select_all_link'>select these records too</a>?" + (flaggedRecordsExist ? " Alternatively, you could <a class='mmr_select_flagged_link'>only select flagged records</a>?" : ""));
				$(".mmr_select_banner", tbl).show();
			}
			else if (flaggedRecordsExist)
			{
				$(".mmr_select_banner", tbl).children("td").html("Do you want to <a class='mmr_select_flagged_link'>only select flagged records</a>?");
				$(".mmr_select_banner", tbl).show();
			}
			$(".mmr_select_box", $("tr:visible", tbl)).filter(":not(:checked)").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
			$(this).val("deselect all");
		}
		
	});
	
	
	
	$(".mmr_button_row > input").live("click", function() {
		
		if ($(this).attr('disabled')){return;}
		var targetKeys = new Array();
		var action = $(this).attr("name");
		var isPreApproval = $(this).parent().hasClass("preapproval");
		var dataSourceKey = $('#dataSourceKey').val();

		var canContinue = true;
		
		if (action == "DELETE_RECORD" || action == "DELETE_DRAFT")
		{
			$(".mmr_select_box:checked", $(this).parents("table.mmr_expandable_table")).parents(".record_row").each(function() {
				targetKeys.push($(this).attr("name"));
			});
			
			if (!confirm("You are about to delete " + targetKeys.length + " record(s). "+(action == "DELETE_DRAFT" ? "These records will be permanently deleted and cannot be restored. " : "") + "Do you want to continue?"))
			{
				return;
			}
		}
		else 
		{
		
			$(".mmr_select_box:checked", $(this).parents("table.mmr_expandable_table")).parents(".record_row").each(function() {
				targetKeys.push($(this).attr("name"));
	
				if ($(this).hasClass("erroneous"))
				{
					canContinue = false;
				}
			});
			
		}
		
		if (!canContinue && (action != 'BACK_TO_DRAFT'))
		{
			$('#mmr_datasource_alert_msg').html("One or more of the records selected contain errors. <br/><br/>Please correct these errors before continuing.");
			$.blockUI({ message: $('#mmr_datasource_alert') });
			return;
		}
		
		blockLoading();
		$.post(
			$("#elementSourceURL").val() + "task=manage_my_records&action=" + $(this).attr("name"),
			{ 	
				'keys[]' : targetKeys, 
				'preapproval' : isPreApproval,
				'dataSourceKey' : dataSourceKey
			},
			function(data)
			{					
				if (data['responsecode'] == 0)
				{
					// Error occured
					alert("Error Occured: Access Denied.");
					$.unblockUI(); 
				}
				else if (data['responsecode'] == "MT008")
				{
					$('#mmr_datasource_alert_msg').html('Your records have been sent to ANDS for assessment and approval. You should contact your ANDS Client Liaison Officer to notify them that these records have been submitted.');
				}
				else if (data['responsecode'] == "MT014")
				{
					$('#mmr_datasource_alert_msg').html('An ANDS Quality Assessor has been notified of your submitted record(s)');
				}
				else
				{
					if (data['alert'])
					{
						$('#mmr_datasource_alert_msg').html(data['alert']);
					}
					location.reload(); 
				}
			},
			'json'
		);
		
		
	});
	
	$(".mmr_select_all_link").live("click", function() {
		var tbl = $(this).parents("table.mmr_expandable_table");
		var checkBoxes = $(".mmr_select_box", $("tr", tbl));
		$(".mmr_select_banner", tbl).children("td").html("All " + checkBoxes.length + " records selected...").parent().delay(1000).fadeOut();
		checkBoxes.filter(":not(:checked)").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
	});
	
	$(".mmr_select_flagged_link").live("click", function() {
		var tbl = $(this).parents("table.mmr_expandable_table");
		
		// Clear all checkboxes
		var checkBoxes = $(".mmr_select_box", $("tr", tbl));
		checkBoxes.filter(":checked").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
		
		$(".mmr_more_records_link", tbl).click();
		
		var flaggedRows = $("tr", tbl).has("td.mmr_flag > .flagged:not(.hide)").has("td.rowSelector > input");
		$("td.rowSelector > input", flaggedRows).filter(":not(:checked)").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
		$(".mmr_select_banner", tbl).children("td").html(flaggedRows.length + " flagged record(s) selected...").parent().delay(1000).fadeOut();
		if (flaggedRows.length == 0)
		{
			$(".mmr_select_all_button", tbl).val("select all");
		}
	});
	
	
	$(".mmr_select_box").live("click", function(e) {

		if (e.originalEvent != undefined)
		{
			if ($(this).val() == "checked")
			{
				$(this).parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
			}
			else
			{
				$(this).parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
			}
		}
		
		if ($(this).val() == "checked")
		{
			$(this).val("unchecked");
		}
		else
		{
			$(this).val("checked");
		}
		
		if ($(".mmr_select_box[value=checked]", $(this).parents("table.mmr_expandable_table")).length > 0)
		{
			$(".mmr_button_row > input:disabled", $(this).parents("table.mmr_expandable_table")).removeAttr("disabled");
		}
		else
		{
			$(".mmr_button_row > input:enabled", $(this).parents("table.mmr_expandable_table")).attr("disabled","disabled");
		}
	});
	
	
	
});

function MMR_setDraftFlag(key, flag)
{
	$.get($("#elementSourceURL").val() + "task=flag_draft&data_source=" + encodeURIComponent($("#dataSourceKey").val()) + "&key=" + encodeURIComponent(key) + "&flag=" + flag);
}

function MMR_setRegObjFlag(key, flag)
{
	$.get($("#elementSourceURL").val() + "task=flag_regobj&key=" + encodeURIComponent(key) + "&flag=" + flag);
}



function MMR_toggleInfoVisible()
{
	if (MMR_datasource_info_visible == "true")
	{
		$('#mmr_datasource_information').hide();
		$('#mmr_information_show').show();
		MMR_setStatusCookie('info','false');
	}
	else
	{
		$('#mmr_datasource_information').show();
		$('#mmr_information_show').hide();
		MMR_setStatusCookie('info','true');
	}
}

function MMR_initStatusCookie()
{
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{
		// Status Cookie format:
		// bool||bool||bool
		//   ^     ^     ^
		// info    |     |
		//       unused  |
		//             unused
		
		setCookie(STATUS_COOKIE_NAME, "true||false||false", STATUS_COOKIE_TTL_DAYS);
		MMR_initStatusCookie();
	} else {
		MMR_datasource_info_visible = currentState.split("||")[0];
	}		
}

function MMR_setStatusCookie(field, value) {
	
	// Check cookie exists and get its current state
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{	
		RMD_initStatusCookie();
		currentState = getCookie(STATUS_COOKIE_NAME);
	} 
		
	// Update the appropriate value
	curVals = currentState.split("||");
		
	if (field == "info") {
		curVals[0] = value;
	} 
	
	MMR_datasource_info_visible = curVals[0];
		
	// Reset the cookie
	setCookie(	STATUS_COOKIE_NAME, 
				curVals.join("||"), 
				STATUS_COOKIE_TTL_DAYS);
	
}


function MMR_getFromStatusCookie(field) {
	
	// Check cookie exists and get its current state
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{	
		RMD_initStatusCookie();
		currentState = getCookie(STATUS_COOKIE_NAME);
	} 
		
	// Update the appropriate value
	curVals = currentState.split("||");
		
	if (field == "info") {
		return curVals[0];
	} 
	
}


function blockLoading()
{
	// change this is regmydata too
	$('#mmr_datasource_alert_msg').html("Sending message to server. Please wait...<br/><br/>" +
										"<div style='text-align:center'>" +
											"<img src='../../_images/_icons/ajax_loading.gif' />" +
										"</div>"); 
	$.blockUI({ message: $('#mmr_datasource_alert') });
}

function blockWithMessage(msg)
{
	$('#mmr_datasource_alert_msg').html(msg);
	$.blockUI({ message: $('#mmr_datasource_alert') });
}