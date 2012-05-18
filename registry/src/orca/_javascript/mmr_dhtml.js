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

var currentView = 'statusview';//can be status or quality

     
$(document).ready(function() {

	$(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
	if($.cookie('currentView')){
		currentView = $.cookie('currentView');
	}else{
		currentView = 'statusview';
		$.cookie('currentView', 'statusview');
	}
	//console.log($.cookie('currentView'));

	$('#mmr_datasource_information').hide();

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
	var DS_QA_flag = false;
	if($('#DS_QA_flag').text()=='yes'){
		DS_QA_flag = true;
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
			
			$('.statusview').hide();
			$('.viewswitch').removeClass('pressed');
			$('.viewswitch[name=qaview]').addClass('pressed');
			if(status=='All'){
				google.setOnLoadCallback(drawChartAllStatus(dsKey));
				$('.qaview[id=All_qaview]').show();
				$('.as_qa_table').parents('.tab-content').show();
			}else{//is a specific status
				google.setOnLoadCallback(drawChart(status, dsKey));
				$('.qaview[id='+status+'_qaview]').show();
				$('.qa_table[status='+status+']').parents('.tab-content').show();
			}
		}
	}

	$('.tab').live('click', function(){
		if(!$(this).hasClass('inactive')){//only for tab that is active
			$('.tab').removeClass('active-tab');
			$(this).addClass('active-tab');//make this tab active and other tab not active (doesn't mean inactive)
			var name = $(this).attr('name');
			view($.cookie('currentView'), name);
		}
	});


	$('.viewswitch').live('click', function(){
		$('.viewswitch').removeClass('pressed');
		$(this).addClass('pressed');
		var name = $(this).attr('name');
		$.cookie('currentView', name);
		$('.'+name).show();
		view(name, $('.tab-list li a.active-tab').attr('name'));
	});


	function drawChart(status, ds) {
        // Create the data table.
        var chartData = new google.visualization.DataTable();

        //console.log('hgere');
        $.ajax({
    		url: 'get_view.php?view=statusCount&status='+status+'&ds='+ds,
    		method: 'get',
    		dataType:'json',
    		contentType: "application/json", //tell the server we're looking for json
    		cache: false, // don't cache the result
    		success: function(data) {
    			//console.log(data);
    			var qualityLevels = data.facet_counts.facet_fields.quality_level;    		
    			//console.log(qualityLevels);
    			chartData.addColumn('string', 'QA Level');
    			chartData.addColumn('number', 'level');

    			var resultArray = new Array();
    			for (var i = 0; i < qualityLevels.length - 1; i=i+2) {
        			//console.log(qualityLevels[i]);
        			var result = [];
        			result.push('QA Level '+ qualityLevels[i], qualityLevels[i+1])
        			resultArray.push(result);
        			//chartData.addColumn('string', 'QA Level '+qualityLevels[i]);
        		};

        		//console.log(resultArray);
        		chartData.addRows(resultArray);

        		function selectHandler() {
			    	var selectedItem = chart.getSelection()[0];
				    if (selectedItem) {
				    	//console.log(chartData);
				      	var value = chartData.getValue(selectedItem.row, 0);
				      	//console.log(value);
				    }
			  	}

        		var options = {'title':status+' Records',
                       'width':400,
                       'height':300,
                   		backgroundColor: { fill:'transparent' },
                   		is3D:true,
                   		colors:['#dc3912', '#ff9900','#3366cc']
                   	};

				var chart = new google.visualization.PieChart(document.getElementById(status+'_qaview'));
				chart.draw(chartData, options);
    			google.visualization.events.addListener(chart, 'select', selectHandler);

        		//console.log('finish');
        	}
       	});
    }

	function drawChartAllStatus(dsKey){
		var chartData = new google.visualization.DataTable();
		$.ajax({
    		url: 'get_view.php?view=AllStatusAllQA&ds='+dsKey,
    		method: 'get',
    		dataType:'json',
    		contentType: "application/json", //tell the server we're looking for json
    		cache: false, // don't cache the result
    		success: function(data) {

    			//console.log(data);

    			var realData = [];
    			var classList = [];
    			var QAList = [];
    			classList.push('Quality Levels');
    			var num = 1;
    			$.each(data, function(i, item) {
    				classList.push('Quality level '+num);
    				var QA = [];
    				QA.push(item.label);
    				$.each(item.qa, function(i, qa_i) {
    					for(j=0;j<5;j++){
    						if(j!=i && !QA[j]){
    							QA[j]=0;
    							//console.log(j + ' > '+ '0');
    						}else{
    							QA[i]=((qa_i*100)/item.num)/100;
    						}
    					}
					});
					//console.log(QA);
					
					QAList.push(QA);

					num++;
					//console.log('====');
				});
				realData.push(classList);

				$.each(QAList, function(i, item) {
					realData.push(item);
				});

  				var realData = google.visualization.arrayToDataTable(realData);

  				console.log(realData);
    			// Create and draw the visualization.
    			var barsVisualization = new google.visualization.BarChart(document.getElementById('All_qaview'));
			  	barsVisualization.draw(realData,
			           {title:"All Registry Objects",
			            width:750, height:400,
			            vAxis: {title: "Status"},
			            isStacked:true,
			            'tooltip': {trigger: 'none'},
			            colors:['#dc3912', '#ff9900','#3366cc', 'green'],
			            hAxis: {title: "Quality Levels Percentage",format:'##%'}}
			      );
			      google.visualization.events.addListener(barsVisualization, 'onmouseover', over);
			      google.visualization.events.addListener(barsVisualization, 'onmouseout', over);
			      google.visualization.events.addListener(barsVisualization, 'click', clickBar);

			      function over(){}
			      function clickBar(e){
			      	var a = barsVisualization.getSelection([e]);
			      	//console.log(a);
			      	console.log(realData.getValue(a[0].row, a[0].column));
			      	console.log(realData.getColumnLabel(a[0].column));
			      }
        	}
        });
	}

	$('.mmr_table').each(function(){
		var status = $(this).attr('status');
		var ql = $(this).attr('ql');
		var count = $(this).attr('count');

		var buttons = [
			{name: 'Select All', bclass: 'button', onpress : selectAll}
		];
		if(status=="DRAFT"){
			if(!DS_QA_flag){
				buttons.push({name: 'Publish', bclass: 'publish', onpress : doCommand});
			}else{
				buttons.push({name: 'Submit for Assessment', bclass: 'submit_for_assessment', onpress : doCommand});
			}
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

		var table_type='status_table';
		if($(this).hasClass('as_qa_table')) table_type='as_qa_table';
		if($(this).hasClass('qa_table')) table_type='qa_table';


		var tableTitle ='';
		if(table_type=='status_table'){
			theTableTitle=status;
		}else if(table_type=='as_qa_table' || table_type=='qa_table'){
			theTableTitle='Quality Level '+ql;
		}

		var tClass = status+'_table';
		if(table_type=='as_qa_table' || table_type=='qa_table'){
			tClass='';
		}

		//service URL
		var viewURL = 'get_view.php?view='+table_type+'&status='+status+'&ds='+dsKey+'&ql='+ql;
		
		$(this).flexigrid({
			striped:true,
			title:status,
			showTableToggleBtn: true,

			showToggleBtn: true,
            url: viewURL,
			dataType: 'json',
			usepager: true,
			colModel : [
			{display: 'recordKey', name:'key', width:70, sortable: true, align:'left'},
                {display: 'Name/Title', name : 'list_title', width : 350, sortable : true, align: 'left'},
                {display: 'Last Modified', name : 'date_modified', width : 150, sortable : true, align: 'left'},
                {display: 'Class', name : 'class', width : 70, sortable : true, align: 'left'},
                {display: 'Errors', name : 'error_count', width : 30, sortable : true, align: 'left'},
                {display: 'Quality Level', name : 'quality_level', width : 70, sortable : true, align: 'left'},
                {display: 'Flag', name : 'flag', width : 30, sortable : true, align: 'left'},
                {display: 'Options', name : 'buttons', width : 100, sortable : false, align: 'left'},
                {display: 'Status', name : 'status', width : 50, sortable : true, align: 'left'},
                {display: 'Manually Assessed', name : 'manually_assessed_flag', width : 50, sortable : true, align: 'left', hide:true}
            ],
            buttons:buttons,
            resizable:true,
            useRp: true,
			rp: 10,
			pagestat: 'Displaying {from} to {to} of {total} records',
			nomsg: 'No records found',

            height:'auto',
            additionalClass:tClass,
            tableTitle:theTableTitle,
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
		}else if(com=='Mark as Gold Standard'){
			if(confirm('You are about to flag '+numKeys+' registry objects as gold standard. Do you want to continue?')){
				action = 'FLAG_GOLD';
			}
			
		}
		//alert($("#elementSourceURL").val());


		//POST the stuff over to Manage My Records
		$('.tab-content').css('opacity',0.5);
		$.post(
			$("#elementSourceURL").val() + "task=manage_my_records&action=" + action,
			{
				'keys[]' : targetKeys,
				'dataSourceKey' : dataSourceKey
			},
			function(data)
			{
				//console.log(data);
				if (data['responsecode'] == '0')
				{
					// Error occured
					alert("Error Occured: Access Denied.");
				}
				else if (data['responsecode'] == "MT008")
				{
					//$('#mmr_datasource_alert_msg').html('Your records have been sent to ANDS for assessment and approval. You should contact your ANDS Client Liaison Officer to notify them that these records have been submitted.');
				}
				else if (data['responsecode'] == "MT014")
				{
					//$('#mmr_datasource_alert_msg').html('An ANDS Quality Assessor has been notified of your submitted record(s)');
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
		$(this).toggleClass('icon59sOff');
		$(this).toggleClass('icon59sOn');
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
										//console.log(data);
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

});





	/**
	OLD
	**/


	// Help lower resolution screens by expanding the outermost page containers to the fixed-width size of the MMR table
	$('body > div').css('min-width','1235px');