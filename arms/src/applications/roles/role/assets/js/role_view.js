$(document).on('click','.remove_relation',function(){
	var parent = $(this).attr('parent');
	var child = $(this).attr('child');
	var elem = this;
	$.ajax({
		url:base_url+'role/remove_relation/', 
		type: 'POST',
		data: {parent:parent,child:child},
		success: function(data){
			// $(elem).parent().fadeOut();
			location.reload();
		}
	});
}).on('click', '.add_role', function(){
	var parent = $(this).prev('select').val();
	var child = $(this).attr('child');
	$.ajax({
		url:base_url+'role/add_relation/', 
		type: 'POST',
		data: {parent:parent,child:child},
		success: function(data){
			location.reload();
		}
	});
}).on('click', '#delete_role', function(){
	if(confirm('Are you sure you want to delete this role and all role relations related to this role? This action is irreversible')){
		var role_id = $(this).attr('role_id');
		$.ajax({
			url:base_url+'role/delete/', 
			type: 'POST',
			data: {role_id:role_id},
			success: function(data){
				window.location.href=base_url+'role/';
			}
		});
	}
});