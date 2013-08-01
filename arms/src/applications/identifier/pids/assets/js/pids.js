

$(function(){
	initView();
});

function initView(){
	listPIDs();
}

function listPIDs() {

	$.getJSON(base_url+'pids/list_pids/', function(data) {
		console.log(data);
		var template = $('#pids-template').html();
		var output = Mustache.render(template, data);
		$('#pids').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 10
		});
		//$('.updateSOLRstat').click(updateSOLRstat);
	});
}