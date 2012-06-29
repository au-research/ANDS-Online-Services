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


// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

var currentView = 'statusview';//can be status or quality
var dsKey;
var dsName;
 
$(document).ready(function() {

	$(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
	var dsKey = $('#dataSourceKey').val();
	var dsName = $('#dataSourceName').val();


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
	var MANUAL_PUBLISH = false;
	if($('#MANUAL_PUBLISH').text()=='yes'){
		MANUAL_PUBLISH = true;
	}


	if($.cookie('currentView')){
		currentView = $.cookie('currentView');
	}else{
		currentView = 'statusview';
		$.cookie('currentView', 'statusview');
	}

	if($.cookie('dsKey')){
		dsKey = $.cookie('dsKey');
		dsName = $.cookie('dsName');
		$('#last_ds').html('Last accessed data source: <a href="my_records.php?data_source='+dsKey+'">'+dsName+'</a>');
		if($('#dataSourceKey').val()){
			dsKey = $('#dataSourceKey').val();
			dsName = $('#dataSourceName').val();
			$.cookie('dsKey', dsKey);
			$.cookie('dsName', dsKey);
		}
		reloadData();
	}else{
		if($('#dataSourceKey').val()){
			dsKey = $('#dataSourceKey').val();
			dsName = $('#dataSourceName').val();
			$.cookie('dsKey', dsKey);
			$.cookie('dsName', dsKey);
			reloadData();
		}else{
			//console.log('Select a data source');
		}
	}


	$('#mmr_datasource_information').hide();

	//MMR Tables
	var dsKey = $('#dataSourceKey').val();
	var dsName = $('#dataSourceName').val();


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
	var MANUAL_PUBLISH = false;
	if($('#MANUAL_PUBLISH').text()=='yes'){
		MANUAL_PUBLISH = true;
	}

	view(currentView, 'All');

	function view(type, status){
		//console.log('type='+type+' status='+status);
		$('.tab-content').hide();
		$('#toggleSummaryTable, #toggleDetailTables').text('-');
		if(type=='statusview'){
			$('.qaview').hide();
			$('.viewswitch').removeClass('pressed');
			$('.viewswitch[name=statusview]').addClass('pressed');
			if(status=='All'){//open all statuses except for more work required (if there is none)
				$('.statusview').show();
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
			if(dsKey) google.setOnLoadCallback(drawBarChart(status, dsKey));
			//drawBarChart(status, dsKey);
			if(status=='All'){
				$('.qaview[id=All_qaview]').show();
				$('.as_qa_table').parents('.tab-content').show();
			}else{//is a specific status
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


	function drawPieChart(status, ds) {
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

    function drawBarChart(status, ds){
    	var chartData = new google.visualization.DataTable();
    	//var get_view = 'get_view.php?view=StatusAllQA&status='+status+'&ds='+ds;
       var get_view = 'get_view.php?view=getAllStat&status='+status+'&ds='+ds;
       //console.log(get_view);
    	/*console.log(get_view);
    	$.ajax({
    		url: get_view,
    		method: 'get',
    		cache: false, // don't cache the result
    		success: function(data) {
    			console.log(data);
    		}
    	});*/

        var colNumber = [];
        var rowCount = 0;
		$.ajax({
    		url: get_view,
    		method: 'get',
    		cache: false, // don't cache the result
    		success: function(data) {

    			//console.log(data);
  				var chartData = new google.visualization.DataTable();
  				
  				var first = true;
  				$.each(data.columns, function(i, item){
  					//console.log(item);
  					if(first){
  						chartData.addColumn("string", item);
  						first = false;
  					}else{
  						chartData.addColumn("number", item);
  						colNumber.push(item);
  					}
  				});
  				
  				var sum = [];
  				$.each(data.rows, function(i, item){
  					rowCount++;
  					var miniSum=0;
  					chartData.addRow(item);
  					$.each(item, function(j, qa_i){
  						if(!isNaN(qa_i)) miniSum = miniSum+ qa_i;
  					});
  					sum[i] = miniSum;
  				});
  				//console.log('sum='+sum);
  				
  				
  				

  				//console.log(chartData);
    			// Create and draw the visualization.
    			var barsVisualization = new google.visualization.BarChart(document.getElementById(status+'_qaview'));
    			//var formatter = new google.visualization.BarFormat({showValue: true});
    			//formatter.format(realData, 0);
			  	
    			var optionTitle = 'Class';
    			var optionHeight = (rowCount * 50) + 100;
    			if(dsKey=='ALL_DS_ORCA'){
    				optionTitle = 'Data Source Key';
        			
    			}
    			
    			var optionPercent = {title:status.replace(/_/g," ") + " Records",
		        	width:1000, height:optionHeight,
		        	vAxis: {title:optionTitle},
		        	isStacked:true,
		        	colors:['#F06533','#F2CE3B', '#6DA539', '#4491AB'],
		        	animation:{
    					duration: 1000,
    					easing: 'out'
  					},
  					sliceVisibilityThreshold:0,
		        	hAxis: {title: "Quality Levels Percentage",format:'##%'}};
				

		        var view = new google.visualization.DataView(chartData);

		        var theRest = [];
		        theRest.push(0);
		        var colLength = colNumber.length;
		       // console.log('colNumber='+colNumber);
		        $.each(colNumber, function(i, item){
  					var labelValue = 'Gold Standard';
  					if(item < 4)
  					labelValue = 'Quality Level '+item;
  					//if(i!=0){
  						var theThing = {
  	  				        	label:labelValue,
  	  				        	type:'number',
  	  				        	calc:function(dt,row){
  	  				        		var value = dt.getValue(row, i+1);
  	  				        		//console.log('at '+i+' = '+value/sum[row]);
  	  				        		return {v: value/sum[row],f:value.toString()};
  	  				        	}
  	  				        };
  	  					theRest.push(theThing);
  					//}
  					
  				});
		       // console.log(theRest);
		        
		        view.setColumns(theRest);
		      //  console.log(view);
		        

			  	function drawThisChart(dataToDraw,optionToDraw){
			  		barsVisualization.draw(dataToDraw,optionToDraw);
			  	}

			  	var dataToDraw = view;		
			  	var optionToDraw = optionPercent;	  	
			  	$('#switchChartType').live('click', function(){
			  		if(dataToDraw==chartDataPercent){
			  			dataToDraw=view;
			  			optionToDraw=option;
			  		}else{
			  			dataToDraw=chartDataPercent;
			  			optionToDraw=optionPercent;
			  		}
			  		drawThisChart(dataToDraw, optionToDraw);
			  	});
			  	drawThisChart(dataToDraw, optionToDraw);
        	}
        });
    }

    $('#summaryTable').flexigrid({
    	striped:true,
    	title:'Summary',
    	tableTitle: 'Registry Content Summary for '+dsName,
    	resizable:false,
    	showTableToggleBtn: true,
    	height:'auto',
    	width:'auto'

    });
	$('.mmr_table').each(function(){
		var status = $(this).attr('status');
		var ql = $(this).attr('ql');
		var count = $(this).attr('count');
		var buttons = [];

		var table_type='status_table';
		if($(this).hasClass('as_qa_table')) table_type='as_qa_table';
		if($(this).hasClass('qa_table')) table_type='qa_table';


		var tableTitle ='';
		if(table_type=='status_table'){
			theTableTitle=status.replace(/_/g," ");
		}else if(table_type=='as_qa_table' || table_type=='qa_table'){
			theTableTitle='Quality Level '+ql;
		}

		var tClass = status+'_table';
		if(table_type=='as_qa_table' || table_type=='qa_table'){
			tClass='';
		}

		if(table_type=='status_table'){
			if(status!='MORE_WORK_REQUIRED') buttons.push({name: 'Select All', bclass: 'button', onpress : selectAll});
		}

		if(status=="DRAFT"){
			if(!DS_QA_flag){
				if(MANUAL_PUBLISH){//is manual
					buttons.push({name: 'Approve', bclass: 'approve', onpress : doCommand});
				}else{//auto publish
					buttons.push({name: 'Publish', bclass: 'publish', onpress : doCommand});
				}
			}else{//required assessment
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
				if(MANUAL_PUBLISH){
					buttons.push({name: 'Approve', bclass: 'approve', onpress : doCommand});
				}else{//auto publish
					buttons.push({name: 'Publish', bclass: 'publish', onpress : doCommand});
				}
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

		if(dsKey=='ALL_DS_ORCA') buttons = [];

		var table_type='status_table';
		if($(this).hasClass('as_qa_table')) table_type='as_qa_table';
		if($(this).hasClass('qa_table')) table_type='qa_table';


		var tableTitle ='';
		if(table_type=='status_table'){
			theTableTitle=status.replace(/_/g," ");;
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
			{display: 'recordKey', name:'key', width:120, sortable: true, align:'left'},
                {display: 'Name/Title', name : 'list_title', width : 350, sortable : true, align: 'left'},
                {display: 'Last Modified', name : 'date_modified', width : 150, sortable : true, align: 'left'},
                {display: 'Class', name : 'class', width : 70, sortable : true, align: 'left'},
                {display: 'Errors', name : 'error_count', width : 30, sortable : true, align: 'left'},
                {display: 'Quality Level', name : 'quality_level', width : 70, sortable : true, align: 'left'},
                {display: 'Flag', name : 'flag', width : 30, sortable : true, align: 'left'},
                {display: 'Options', name : 'buttons', width : 100, sortable : false, align: 'left'},
                {display: 'Status', name : 'status', width : 200, sortable : true, align: 'left'},
                {display: 'Manually Assessed', name : 'manually_assessed_flag', width : 50, sortable : true, align: 'left', hide:true}
            ],
            sortname:'date_modified',
            sortorder:'desc',
            buttons:buttons,
            resizable:true,
            useRp: true,
			rp: 10,
			pagestat: 'Displaying {from} to {to} of {total} records',
			nomsg: 'No records found',

            height:'200px',
            additionalClass:tClass,
            tableTitle:theTableTitle,
            searchitems : [
            		{display: 'Name/Title', name : 'list_title'}
            ],
            onSuccess: formatTable,
            cookies: true,
            tableId:theTableTitle
		});
	});


	function formatTable(com, grid){
		//hide info
		$('.infoDiv', grid).hide();
		$('td[abbr=status]', grid).each(function(){
			$(this).addClass($(this).text()+'_status');
		});

		if($('#MORE_WORK_REQUIRED').find('.ftitle').attr('count')=='0'){
			$('#MORE_WORK_REQUIRED').hide();
		}
	}


	
	/**
	BUTTONS
	**/
	function doCommand(com, grid) {
		//setup the keys
		var targetKeys = [];
		var hasError = false;
		if($(grid).attr('selectall')=='no'){
			$('.trSelected', grid).each(function() {
				var id = $(this).attr('id');
				id = id.substring(id.lastIndexOf('row')+3);
				
				if(id){
					var numError = $('td[abbr=error_count]', this).text();
					if(numError!='0'){
						//console.log(numError);
						hasError = true;
					}
					targetKeys.push(id);
				}
			});
			fireZaLaserz(com, targetKeys, hasError);
		}else if($(grid).attr('selectall')=='yes'){
			var status = $(grid).attr('status');
			$.ajax({
				url: 'get_view.php?view=allKeys&status='+status+'&ds='+dsKey,
				dataType: 'json',
				success: function(data) {
					docs = data.response.docs;
					$(docs).each(function(){
						targetKeys.push(this.key);
						if(this.error_count!=0) hasError = true;
					});
					fireZaLaserz(com, targetKeys, hasError);
				}
			});
		}else{
			alert('No command to be executed');
			return false;
		}

		//Reindex all the target Keys
	}

	function fireZaLaserz(com, targetKeys, hasError){
		
		var numKeys = (targetKeys).length;
		var dataSourceKey = $('#dataSourceKey').val();

		//if there is none
		if(numKeys==0){
			alert('Please select record');
			release();
			return false;
		}

		//setup actions
		var AllSystemGo = true;
		if (com == 'Edit') {
			
		}else if (com == 'Delete Record') {
			if(confirm('You are about to delete '+numKeys+' records')){
				action = 'DELETE_RECORD';
			}else {
				release();
				AllSystemGo = false;
			}
		}else if(com=='Delete Draft'){
			if(confirm('You are about to delete '+numKeys+' drafts. This draft will be permanently deleted and cannot be restored. Do you want to continue?')){
				action = 'DELETE_DRAFT';
			}else {
				release();
				AllSystemGo = false;
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

		if(hasError){
			alert('Selected Record(s) contains record with error');
			//release();
			AllSystemGo = false;
		}

		//POST the stuff over to Manage My Records
		if(AllSystemGo && !hasError){
			loading();
			var req_url = $("#elementSourceURL").val() + "task=manage_my_records&action=" + action;
			$.ajax({
				url:req_url,
				data:{'keys[]' : targetKeys,'dataSourceKey' : dataSourceKey},
				dataType:'json',
				type:'POST',
				success:function(data){
					if(data.responsecode=='0'){
						alert('Error Occured: Access Denied');
					}else if(data.responsecode=='MT008'){
						alert('Your records have been sent to ANDS for assessment and approval. You should contact your ANDS Client Liaison Officer to notify them that these records have been submitted.');
					}else if(data.responsecode=='MT014'){
						alert('An ANDS Quality Assessor has been notified of your submitted record(s)');
					}else{
						//if(data.alert) alert(data.alert);
					}
					release();
					$('.mmr_table').each(function(){
						$(this).flexReload();
					});
					reloadData();
				},
				error:function(data){
					//console.log(data);
					release();
					$('.mmr_table').each(function(){
						$(this).flexReload();
					});
					reloadData();
				}
			});
		}
	}

	function loading(){
		//$('.tab-content').css('opacity', 0.5);
		$.blockUI({
			message: '<img src="../_images/ui-anim_basic_16x16.gif"/>',
			css: { width:'auto'}
		});
	}

	function release(){
		$.unblockUI();
	}

	function reloadData(){
		var viewURL = 'get_view.php?view=summary&ds='+dsKey+'&ds_qa_flag='+DS_QA_flag+'&manual_publish='+MANUAL_PUBLISH;
		//console.log(viewURL);
		var columns = [];
		columns.push({display:'', name:'', width:120, sortable:false,align:'left'});

		//Sort it by this order
    	//var order = array('MORE_WORK_REQUIRED', 'DRAFT','SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS', 'APPROVED', 'PUBLISHED');
    	//var order = ['PUBLISHED', 'APPROVED', 'SUBMITTED_FOR_ASSESSMENT', , 'ASSESSMENT_IN_PROGRESS''DRAFT', 'MORE_WORK_REQUIRED'];
    	var order = [];
    	if(DS_QA_flag){
			if(MANUAL_PUBLISH){
    			var order = ['PUBLISHED', 'APPROVED', 'SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS', 'DRAFT', 'MORE_WORK_REQUIRED'];
    		}else{//auto publish
				var order = ['PUBLISHED', 'SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS', 'DRAFT', 'MORE_WORK_REQUIRED'];
    		}
    	}else{
    		if(MANUAL_PUBLISH){
    			var order = ['PUBLISHED', 'APPROVED', 'DRAFT'];
    		}else{//auto publish
				var order = ['PUBLISHED', 'DRAFT'];
    		}
    	}
    	$.each(order, function(pos, i){
    		columns.push({display:i.replace(/_/g," "), name:i, width:120, sortable:false, align:'left'});
    	});

		$.ajax({
    		url: 'get_view.php?view=AllStatus&ds='+dsKey+'&ds_qa_flag='+DS_QA_flag+'&manual_publish='+MANUAL_PUBLISH,
    		method: 'get',
    		dataType:'json',
    		contentType: "application/json", //tell the server we're looking for json
    		success: function(data) {
    			$('.tab').remove();
    			$('.tab-list').append('<li><a href="javascript:void(0);" title="All" class="tab active-tab" name="All">All Records</a></li>');
    			$.each(data, function(i, num){
    				//columns.push({display:i.replace(/_/g," "), name:i, width:120, sortable:false, align:'left'});
    				if(num>0){
    					$('.tab-list').append('<li><a href="javascript:void(0);" title="'+num+' Records" class="tab tip" name="'+i+'">'+i.replace(/_/g," ")+'</a><li>');
    				}else{
    					$('.tab-list').append('<li><a href="javascript:void(0);" title="'+num+' Records" class="tab tip inactive" name="'+i+'">'+i.replace(/_/g," ")+'</a><li>');
    				}
    				
    			});
    			//console.log(columns);
    			$('.summary_table').flexReload();
    			
				//console.log(viewURL);
        	}
       	});

       	$('.summary_table').flexigrid({
			striped:true,
			title:status,
			showTableToggleBtn: true,
			showToggleBtn: true,
	        url: viewURL,
			dataType: 'json',
			//usepager: true,
			colModel :columns,
	        resizable:true,
	        //useRp: true,
			rp: 10,
			pagestat: 'Displaying {from} to {to} of {total} records',
			nomsg: 'No records found',

	        height:'200px',
	        //additionalClass:tClass,
	        tableTitle:'Registry Content Summary for '+dsName
		});
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
			

			flaggedRecords = $(grid).find('.icon59sOn');
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
		var flaggedRecords = $(grid).find('.icon59sOn');
		$('tr', grid).removeClass('trSelected');
		$.each(flaggedRecords, function(index){
			$(this).parents('tr').addClass('trSelected');
		});
		$('.infoDiv', grid).html('All '+flaggedRecords.length+' flagged records selected');
	});

	//delete record button
	$('.deleteConfirm').live('click', function(){
		var row = $(this).parents('tr');
		var targetKeys = [];
		var id = $(row).attr('id');
		id = id.substring(id.lastIndexOf('row')+3);
		if(id) targetKeys.push(id);

		var draftStatus = $(this).attr('draftStatus');
		var com = 'Delete Record';
		if(draftStatus=='draft'){
			var com = 'Delete Draft';
		}

		//console.log(com);
		//console.log(targetKeys);
		fireZaLaserz(com, targetKeys, false);
		
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

	$('a.tipQA').live('mouseover', function(){
		$(this).qtip({
			content: {
				text: 'Loading...', // The text to use whilst the AJAX request is loading
				ajax: {
					url: 'get_view.php', // URL to the local file
					type: 'GET', // POST or GET
					data: {
						view: 'tipQA',
						ql: $(this).attr('level'),
						key: $(this).attr('key'),
						status: $(this).attr('status'),
						ds: $(this).attr('dsKey')
					}, // Data to pass along with your request
					loading:false,
					success: function(data, status) {
						this.set('content.text', data);
						formatTip(this);
					}
				},
				title: {
					text: 'Quality Level'
					//button: true
				}
			},
			position: {
				my: 'right center', // Use the corner...
				at: 'left center' // ...and opposite corner
			},
			show: {
				//event: 'click',
				ready: true,
				solo:true,
         		effect: function(offset) {
					$(this).show(); // "this" refers to the tooltip
				}
      		},
      		hide: {
      			fixed:true,
				delay: 1500
			},
			style: {
				classes: 'ui-tooltip-shadow ui-tooltip-light'
			},
      		overwrite: false
		});
	});

	function formatTip(tt){
		var tooltip = $('#ui-tooltip-'+tt.id+'-content');
		
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
				$(this).text('Quality Level 1 - Mandatory RIF-CS Elements');
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
			}else{
				$(this).addClass('success');
			}
		});
		//bind the toggle header to open all the qa inside
		$('.toggleQAtip', tooltip).click(function(){
			$(this).parent().children('.qa_ok, .qa_error').slideToggle('fast', function(){
				tt.reposition();//fix the positioning
			});
		});
		$('.qa_ok').addClass('success');
		$('.qa_error').addClass('warning');
	}

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
		}else{
			$.get($("#elementSourceURL").val() + "task=flag_draft&data_source=" + encodeURIComponent($("#dataSourceKey").val()) + "&key=" + encodeURIComponent(key) + "&flag=" + flag);	
		}
	});

});//end





	/**
	OLD
	**/


	// Help lower resolution screens by expanding the outermost page containers to the fixed-width size of the MMR table
	$('body > div').css('min-width','1235px');