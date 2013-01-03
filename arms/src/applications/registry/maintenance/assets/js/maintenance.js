/**
 * Core Maintenance Javascript
 */

$(function(){
	initView();
	
});

function initView(){

	updateStat();
	updateDataSourcesStat();
	
}

function updateStat() {

	//get Stat
	$('#stat').css('opacity', '0.5');
	$.getJSON(base_url+'maintenance/getStat', function(data) {
		console.log(data);
		var template = $('#stat-template').html();
		var output = Mustache.render(template, data);
		$('#stat').html(output);
		$('#stat').css('opacity', '1');
		//$('.updateSOLRstat').click(updateSOLRstat);
	});
}

function updateDataSourcesStat(){
	//get Datasources stat
	$('#ds').css('opacity','0.5');
	$.getJSON(base_url+'maintenance/getDataSourcesStat', function(data) {
		var template = $('#ds-template').html();
		var output = Mustache.render(template, data);
		$('#ds').css('opacity', '1.0');
		$('#ds').html(output);
		$('#dataSourceSelect').chosen();
		$('.data-table').dataTable({
			"aaSorting": [[ 5, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>'
		});
		$('button.reindex_ds').die().live({
			click:function(){
				$(this).button('loading');
				var ds_id = $(this).attr('ds_id');
				$.getJSON(base_url+'maintenance/indexDS/'+ds_id, function(data) {
					console.log(data);
					updateDataSourcesStat();
				});
			}
		});

		$('button.clearindex_ds').die().live({
			click:function(){
				$(this).button('loading');
				var ds_id = $(this).attr('ds_id');
				$.getJSON(base_url+'maintenance/clearDS/'+ds_id, function(data) {
					console.log(data);
					updateDataSourcesStat();
				});
			}
		});
	});
}