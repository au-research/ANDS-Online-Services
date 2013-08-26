$(function(){
	$('#reassign').hide();
});

$(document).on('click', '#update_confirm', function(){
	var jsonData = {};
	jsonData['handle'] = $(this).attr('handle');
	$(this).button('loading');
	jsonData['values'] = [];
	$('#edit_form input[changed=true]').each(function(){
		var type = $(this).attr('name');

		jsonData['values'].push({
			type:type,
			idx:($(this).attr('idx')?$(this).attr('idx'):-1),
			value:$(this).val()
		});
	});
	console.log(jsonData);
	$.ajax({
		url:base_url+'pids/update/', 
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			location.reload();
		}
	});
}).on('change', "#edit_modal input", function(){
	$(this).attr('changed', 'true');
}).on('click', '#reassign_toggle', function(){
	$(this).hide();
	$('#reassign').show();
}).on('click', '#confirm_reassign', function(){
	var this_handle = $(this).attr('handle');
	var new_handle = $('#reassign_value').val();
	var jsonData = {};
	jsonData['current'] = this_handle;
	jsonData['reassign'] = new_handle;
	$.ajax({
		url:base_url+'pids/update_ownership/', 
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			window.location = base_url+'pids';
		}
	});
}).on('click', '.add_new', function(){
	var type = $(this).attr('add-type');
	if(type=='desc'){
		var new_dom = $('#new_desc').clone().insertBefore($('#separate_line')).removeClass('hide');
	}else if(type=='url'){
		var new_dom = $('#new_url').clone().insertBefore($('#separate_line')).removeClass('hide');
	}
});