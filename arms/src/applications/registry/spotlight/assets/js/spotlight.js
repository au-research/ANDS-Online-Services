$(function(){

	//textarea
	if(editor=='tinymce'){
		tinyMCE.init({
		    theme : "advanced",
		    mode : "specific_textareas",
		    editor_selector : "editor",
		    theme_advanced_toolbar_location : "top",
		    theme_advanced_buttons1 : "bold,italic,underline,separator,link,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,separator,undo,redo,code",
		    theme_advanced_buttons2 : "",
		    theme_advanced_buttons3 : "",
		    height:"250px",
		    width:"100%"
		});
	}

	$('#item_list ul').sortable({
		items: "li:not(#new)",
		stop: function(ev, ui){
			var array = $(this).sortable('toArray');
			// console.log(array);
			$.ajax({
				url:base_url+'spotlight/saveOrder', 
				type: 'POST',
				data: {data:array},
				success: function(data){
					//console.log(data)
				}
			});
		}
	});

	$('#item_list a').click(function(){
		var id = $(this).parent().attr('id');
		$('.item-content, .flexslider').hide();
		$('#'+id+'-content, #'+id+'-preview').show();
		$('#item_list a').removeClass('active');
		$(this).addClass('active');
	});
	$('#item_list a:first').click();

	$('button.save').click(function(e){
		e.preventDefault();
		var id = $(this).attr('_id');
		var form = $('form[_id='+id+']');
		if(editor=='tinymce') tinyMCE.triggerSave();
		var jsonData = $(form).serializeArray();
		//console.log(jsonData);
		$.ajax({
			url:base_url+'spotlight/save/'+id, 
			type: 'POST',
			data: jsonData,
			success: function(data){
				if(data=='success') {
					document.location.reload(true);
				} else alert(data);
			}
		});
	});

	$('button.delete').click(function(e){
		var id = $(this).attr('_id');
		if(confirm('Are you sure you want to delete this record?')){
			$.ajax({
				url:base_url+'spotlight/delete/'+id, 
				type: 'POST',
				success: function(data){
					if(data=='success') {
					document.location.reload(true);
					} else alert(data);
				}
			});
		}
	});

	$('button.add').click(function(e){
		e.preventDefault();
		var form = $('form[_id=new]');
		var jsonData = $(form).serializeArray();
		if(editor=='tinymce') tinyMCE.triggerSave();
		//console.log(jsonData);
		$.ajax({
			url:base_url+'spotlight/add/', 
			type: 'POST',
			data: jsonData,
			success: function(data){
				if(data=='success') {
				document.location.reload(true);
				} else alert(data);
			}
		});
	})
	/*$.getJSON(base_url+'assets/spotlight/spotlight.json',function(data){
		var template = $('#spotlight-template').html();
		var output = Mustache.render(template, data);
		$('#item_list').html(output);

		$('#item_list ul').sortable({
			stop: function(ev, ui){
				var array = $(this).sortable('toArray');
				console.log(array);
			}
		});

		json = data;
		console.log(json);
		save(json);
	});*/
});

function save(jsonData){
	$.ajax({
		url:base_url+'spotlight/save', 
		type: 'POST',
		data: {data:jsonData},
		success: function(data){
			console.log(data)
		}
	});
}