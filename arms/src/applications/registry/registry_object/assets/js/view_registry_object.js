/**
 */
$(function(){
	$('#exportRIFCS').click(function(){
		$.getJSON(base_url+'registry_object/get_record/'+$('#ro_id').val(), function(data){
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(data.ro.xml)) + '</code></pre>');
			prettyPrint();
			$('#myModal').modal();
		});
	});

	$('#exportNative').click(function(){
		$.getJSON(base_url+'registry_object/get_native_record/'+$('#ro_id').val(), function(data){
			$('#myModal .modal-body').html('<textarea style="width:95%;height:300px;margin:0 auto;">' + data.txt + '</textarea>');
			$('#myModal').modal();
		});
	});

	formatTip($('#qa_level_results'));
});

function formatTip(tt){
    var tooltip = tt;
    
    //wrap around the current tooltip with a div
    for(var i=1;i<=3;i++){
        $('*[level='+i+']', tooltip).wrapAll('<div class="qa_container" qld="'+i+'"></div>');
    }
    //add the toggle header
    $('.qa_container', tooltip).prepend('<div class="toggleQAtip"></div>');
    $('.toggleQAtip', tooltip).each(function(){
        if ($(this).parent().attr('qld') == 5)
            $(this).text('Gold Standard Record');
        else if($(this).parent().attr('qld') == 1)
            $(this).text('Quality Level 1 - Required RIF-CS Schema Elements');
        else if($(this).parent().attr('qld') == 2)
            $(this).html('Quality Level 2 - required Metadata Content Requirements.' );
        else if($(this).parent().attr('qld') == 3)
             $(this).html('Quality Level 3 - recommended Metadata Content Requirements.' );
    });
    //hide all qa
    $('.qa_container', tooltip).each(function(){
        $(this).children('.qa_ok, .qa_error').hide();
    });
    //show the first qa that has error
    var showThisQA = $('.qa_error:first', tooltip).parent();
    $(showThisQA).children().show();
    //coloring the qa that has error, the one that doesn't have error will be the default one
    $('.qa_container', tooltip).each(function(){
        if($(this).children('.qa_error').length>0){//has an error
            //$(this).children('.toggleQAtip').addClass('hasError');
            $(this).addClass('warning');
        }else{
            $(this).addClass('success');
        }
    });
    //bind the toggle header to open all the qa inside
    $('.toggleQAtip', tooltip).click(function(){
        $(this).parent().children('.qa_ok, .qa_error').slideToggle('fast');
    });
    $('.qa_ok').addClass('success');
    $('.qa_error').addClass('warning');
}
