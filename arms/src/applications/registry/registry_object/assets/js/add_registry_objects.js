/**
 */
$(function(){
	$('.addButton span').live({
		click:function(e){
			e.preventDefault();
			console.log($(this).attr('id'));
			var ro_class = $(this).attr('id');
			$('#AddNewDS_confirm').attr('ro_class', ro_class);
			$('#AddNewDS_confirm').html('Add New '+ro_class);
			$("#AddNewDS").modal('show');
		}
	});	

	$('#generate_random_key').live({
		click:function(e){
			e.preventDefault();
			var input = $(this).prev('input');
			$.ajax({
				type: 'GET',
				url: base_url+'services/registry/get_random_key/',
				success:function(data){
					console.log("random Key:" + data);
					$(input).val(data)
				}
			});
		}
	});
	$('#AddNewDS_confirm').die().live({
		click:function(e){
			e.preventDefault();
			registry_object_key = $('input[name=key]').val();
			ro_class = $(this).attr('ro_class');
			type = $('input[name=type]').val();
			group = $('input[name=group]').val();
			data_source_id = $('select[name=data_source_id]').val();
			originating_source = $('input[name=originatingSource]').val();

			var data = {data_source_id:data_source_id, registry_object_key:registry_object_key, ro_class:ro_class, type:type, group:group, originating_source:originating_source};

			$.ajax({
				type: 'POST',
				url: base_url+'registry_object/add_new',
				data:{data:data},
				dataType:'JSON',
				success:function(data){
					$("#AddNewDS").modal('hide');
					//console.log(data);
					if(data.success) window.location = base_url+'registry_object/edit/'+data.ro_id+'#!/advanced/admin';
				},
				error:function(data){
					console.log("error: " + data);
				}
			});
		}
	});


});