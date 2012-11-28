/**
 * Core Maintenance Javascript
 */

$(function(){
	initView();
	
});

function initView(){

	updateStat();

	//get Datasources stat
	$.getJSON(base_url+'maintenance/getDataSourcesStat', function(data) {
		var template = $('#ds-template').html();
		var output = Mustache.render(template, data);
		$('#ds').html(output);
		$('#dataSourceSelect').chosen();
		$('.data-table').dataTable({
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>'
		});
	});
}

function updateStat() {

	//get Stat
	$('#stat').css('opacity', '0.5');
	$.getJSON(base_url+'maintenance/getStat', function(data) {
		var template = $('#stat-template').html();
		var output = Mustache.render(template, data);
		$('#stat').html(output);
		$('#stat').css('opacity', '1');
		//$('.updateSOLRstat').click(updateSOLRstat);
	});
}