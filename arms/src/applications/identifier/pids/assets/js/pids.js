

$(function(){
	initView();
});

function initView(){
	//listTrustedClients();
	listPIDs();
}

function listPIDs() {

	$.getJSON(base_url+'pids/list_pids', function(data) {
		console.log(data);
		var template = $('#pids-list-template').html();
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

function listTrustedClients() {

	$.getJSON(base_url+'pids/list_trusted_clients/', function(data) {
		console.log(data);
		var template = $('#trusted_clients-template').html();
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