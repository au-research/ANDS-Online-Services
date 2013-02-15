/**
 */
$(function(){
	$('.viewrecord').click(function(){
		var recordKey = $(this).attr('record_key');
		$('#myModal .modal-body').html('');
		$('div[name=resultScreen] #myModal').html('');
		try{
			var rifcs = $("#"+recordKey).html();
			rifcs = rifcs.replace(/&lt;/g, '<');
			rifcs = rifcs.replace(/&gt;/g, '>');
			rifcs = rifcs.replace(/&gamp;/g, '&');
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(rifcs)) + '</code></pre>');
			prettyPrint();
		}
		catch(e)
		{
			$('#myModal .modal-body').html('The record data '+recordKey+' is missing');
		}
		$('.modal-footer .undelete_record').attr('record_key',recordKey); 
		$('#myModal').modal();
	});

	$('.undelete_record').live({
		click: function(e){
			var recordKey = $(this).attr('record_key');
			$('#myModal').modal();
			$('#myModal .modal-body').html('');
			$('div[name=resultScreen] #myModal').html('');
			/* fire off the ajax request */
			$.ajax({
				url: base_url + 'data_source/reinstateRecordforDataSource', 	//?XDEBUG_TRACE=1', //XDEBUG_PROFILE=1&
				type: 'POST',
				data:	{ 
					deleted_registry_object_id: recordKey,
					data_source_id: $('#data_source_id').val()
				}, 
				success: function(data)
						{		
							if(data.response == "success")
							{
								output = Mustache.render($('#import-screen-success-report-template').html(), data);
								$('#myModal .modal-body').html(output);
							}
							else
							{
								$('#myModal .modal-body').html("<pre>" + data.log + "</pre>");
							}
							$('.modal-footer a').toggle();
						}, 
				error: function(data)
						{
							$('#myModal .modal-body').html('data');
						},
				dataType: 'json'
			});
		}
	});


});