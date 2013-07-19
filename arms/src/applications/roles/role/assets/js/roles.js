/**
 * Core Maintenance Javascript
 */

$(function(){
	initView();
	// setInterval(function(){
	// 	updateStat();
	// }, 2000);

	 // setInterval(function(){
	 // 	updateStat();
	 // }, 10000);
});

function initView(){
	listRoles();
}

function listRoles() {

	//get Stat
	// $('#stat').css('opacity', '0.5');
	$.getJSON(base_url+'role/list_roles/', function(data) {
		console.log(data);
		var template = $('#roles-template').html();
		var output = Mustache.render(template, data);
		$('#roles').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 30
		});
		//$('.updateSOLRstat').click(updateSOLRstat);
	});
}


$(document).on('click','button.task',function(){
	$(this).button('loading');
	var op = $(this).attr('op');
	var ds_id = $(this).attr('ds_id');
	var url;
	switch(op){
		case 'index_ds':url = base_url+'maintenance/indexDS/'+ds_id;break;
		case 'enrich_ds':url = base_url+'maintenance/enrichDS/'+ds_id;break;
		case 'clear_ds':url = base_url+'maintenance/clearDS/'+ds_id;break;
		case 'enrich_all': url= base_url+'maintenance/enrichAll/';break;
		case 'enrich_missing': url= base_url+'maintenance/enrichMissing/';break;
		case 'index_all': url= base_url+'maintenance/indexAll/';break;
		case 'index_missing': url= base_url+'maintenance/indexMissing/';break;
		case 'cleanNotExist': url= base_url+'maintenance/cleanNotExist/';break;
	}
	$.getJSON(url, function(data) {
		updateDataSourcesStat();
	});
}).on('click','#refresh', function(){
	initView();
});