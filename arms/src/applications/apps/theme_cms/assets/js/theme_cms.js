var blobs = {
	'title': 'Something',
	'slug': 'slug-slug-something',
	'left':[
		{
			title: 'Test Title 1',
			content: 'Test Content 1'
		},
		{
			title: 'Test Title 2',
			content: 'Test Content 1'
		},
		{
			title: 'Test Title 1',
			content: 'Test Content 1'
		},
		{
			title: 'Test Title 1',
			content: 'Test Content 1'
		},
	],
	'right':[
		{
			title: 'Test Title 1',
			content: 'Test Content 1'
		},
		{
			title: 'Test Title 2',
			content: 'Test Content 1'
		},
		{
			title: 'Test Title 1',
			content: 'Test Content 1'
		},
		{
			title: 'Test Title 1',
			content: 'Test Content 1'
		},
	]
};

$(function(){
	var template = $('#blob-template').html();
	var output = Mustache.render(template, blobs.left);
	$('#region_left').html(output);
	var output = Mustache.render(template, blobs.right);
	$('#region_right').html(output);
	$('.region').sortable({
		handle:'.handle',
		connectWith: '.region'
	});

	tinymce.init({
	    selector: "textarea.editor",
	    theme: "modern",
	    plugins: [
	        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
	        "searchreplace wordcount visualblocks visualchars code fullscreen",
	        "insertdatetime media nonbreaking save table contextmenu directionality",
	        "emoticons template paste"
	    ],
	    height:"250px",
	    width:"100%",
	    entity_encoding : "raw",
	    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
	});
});

$(document).on('click', '#add_confirm', function(){
	tinyMCE.triggerSave();
	var data = {
		title:$('#add_blob_form input[name=title]').val(), 
		content:$('#add_blob_form textarea[name=content]').val()
	};
	// console.log(data);
	blobs.left.push(data);
	var template = $('#blob-template').html();
	var output = Mustache.render(template, blobs.left);
	$('#region_left').html(output);
	$('#add_blob_modal').modal('hide');
});