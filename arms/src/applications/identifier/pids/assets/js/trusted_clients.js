$(function(){
	init();
});
function init () {
	listTrustedClients();
}

$(document).on('click', '#add_confirm', function(){
	$(this).button('loading');
	var jsonData = {};
	$('#add_trusted_client_form input').each(function(){
		jsonData[$(this).attr('name')] = $(this).val();
	});
	$.ajax({
		url:base_url+'pids/add_trusted_client/', 
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			location.reload();
		}
	});
}).on('click', '#app_id_show', function(){
	$(this).hide();
	$('#app_id_field').show();
});

function listTrustedClients() {
	$.getJSON(base_url+'pids/list_trusted_clients/', function(data) {
		var template = $('#trusted_clients-template').html();
		var output = Mustache.render(template, data);
		$('#trusted_clients').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 10
		});
	});
}
