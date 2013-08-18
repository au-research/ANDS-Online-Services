$(function(){
	
});

$(document).on('click', '#update_confirm', function(){
	var jsonData = {};
	jsonData['handle'] = $(this).attr('handle');
	$(this).button('loading');
	$('#edit_modal input[changed=true]').each(function(){
		jsonData[$(this).attr('name')] = $(this).val();
		jsonData[$(this).attr('name')+'_index'] = $(this).attr('idx');
	});
	// console.log(jsonData);
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
});