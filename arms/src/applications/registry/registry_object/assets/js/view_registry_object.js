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

    $('.tag_form').submit(function(e){
        e.preventDefault();
        e.stopPropagation();
        var ro_id = $(this).attr('ro_id');
        var tag = $('input', this).val();
        var tag_html = '<li>'+tag+'<span class="hide"><i class="icon icon-remove"></i></span></li>';
        $('.tags').append(tag_html);
        $('.notag').hide();
         $.ajax({
            url:base_url+'registry_object/tag/add', 
            type: 'POST',
            data: {ro_id:ro_id,tag:tag},
            success: function(data){
                // console.log(data);
                // $('#status_message').html(data.msg);
            }
        });
    });
    $('.tags li').die().live({
        mouseover: function(){
            $('span', this).show();
        },
        mouseout: function(){
            $('span', this).hide();
        },
        click: function(){
            var text = $(this).text();
            var ro_id = $(this).parent().attr('ro_id');
            $.ajax({
                url:base_url+'registry_object/tag/remove', 
                type: 'POST',
                data: {ro_id:ro_id,tag:text},
                success: function(data){
                    $(this).remove();
                }
            });                             
        }
    });

	formatTip($('#qa_level_results'));
    processRelatedObjects();
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
    // var showThisQA = $('.qa_error:first', tooltip).parent();
    // $(showThisQA).children().show();
    
    //coloring the qa that has error, the one that doesn't have error will be the default one
    $('.qa_container', tooltip).each(function(){
        if($(this).children('.qa_error').length>0){//has an error
            //$(this).children('.toggleQAtip').addClass('hasError');
            $(this).addClass('warning');
            $('.toggleQAtip', this).prepend('<span class="label label-important"><i class="icon-white icon-info-sign"></i></span> ');
        }else{
            $(this).addClass('success');
            $('.toggleQAtip', this).prepend('<span class="label label-success"><i class="icon-white icon-ok"></i></span> ');
        }
    });
    //bind the toggle header to open all the qa inside
    $('.toggleQAtip', tooltip).click(function(){
        $(this).parent().children('.qa_ok, .qa_error').slideToggle('fast');
    });
    $('.qa_ok').addClass('success');
    $('.qa_error').addClass('warning');
}

function processRelatedObjects()
{
    $.ajax({
        type: 'GET',
        url: base_url+'registry_object/getConnections/'+$('#registry_object_id').val(),
        dataType: 'json',
        success: function(data){
            $.each(data.connections, function(){
                var id = this.registry_object_id;
                var title = this.title;
                var key = this.key;
                var status = this.status;
                var origin = this.origin;
                //log("id:" + id + ", key:" + key + ", title:" + title + ", status:" + status + ", origin:" + origin);
                if(origin == 'EXPLICIT')
                {
                    $('.resolvable_key[key_value="'+key+'"] span.resolvedRelated').html(" <b>  TITLE: " + title + "</b>");
                }
                else if(origin == 'REVERSE_EXT'){
                    //<table id="related_objects_table">
                    // ADD REVERSE LINKS to the bottom of the table with EXTERNAL REVERSE DISPLAYED
                }
                else if(origin == 'REVERSE_INT'){
                    // ADD REVERSE LINKS to the bottom of the table with INTERNAL REVERSE DISPLAYED
                }
            });

        }
    });


}
