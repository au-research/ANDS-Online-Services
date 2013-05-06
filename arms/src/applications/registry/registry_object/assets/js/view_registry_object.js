/**
 */
$(function(){
	$('#exportRIFCS').click(function(){
		$.getJSON(base_url+'registry_object/get_record/'+$('#ro_id').val(), function(data){
            $('#myModal .modal-header h3').html('<h3>RIFCS:</h3>');
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(data.ro.xml)) + '</code></pre>');
			prettyPrint();
			$('#myModal').modal();
		});
	});

	$('#exportNative').click(function(){
		$.getJSON(base_url+'registry_object/get_native_record/'+$('#ro_id').val(), function(data){
            $('#myModal .modal-header h3').html('<h3>Native Metadata:</h3>');
			$('#myModal .modal-body').html('<textarea style="width:95%;height:300px;margin:0 auto;">' + data.txt + '</textarea>');
			$('#myModal').modal();
		});
	});

    $('.status_change_action').on('click', function(e){
        e.preventDefault();
        url = base_url+'registry_object/update/';
        data = {affected_ids:[$('#ro_id').val()], attributes:[{name:'status',value:$(this).attr('to')}], data_source_id:$('#data_source_id').val()};
        $.ajax({
            url:url, 
            type: 'POST',
            data: data,
            dataType: 'JSON',
            success: function(data){
                if(data.status=='success')
                {
                    if(data.error_count != '0')
                    {
                        $("html, body").animate({ scrollTop: 0 });
                        logErrorOnScreen('ERROR WHILST CHANGING RECORD STATUS: ' + data.error_message);
                    }
                    else{
                        if (typeof(data.new_ro_id) !== 'undefined')
                        {
                            window.location = base_url + 'registry_object/view/' + data.new_ro_id;
                        }
                        else
                        {
                            window.location.reload();
                        }
                    }
                }
                else
                {
                    $("html, body").animate({ scrollTop: 0 });
                    logErrorOnScreen('ERROR WHILST CHANGING RECORD STATUS: ' + data.message);
                }
            }
        });
    });

    $('#delete_record_button').on('click', function(e){
        e.preventDefault();
        if (confirm('Are you sure you want to delete this record?' + "\n" 
            + "NOTE: Non-PUBLISHED records cannot be recovered once deleted.") !== true) 
        { 
            return; 
        }


        var data =  {affected_ids:[$('#ro_id').val()], data_source_id:$('#data_source_id').val()};
        $.ajax({
            url: base_url+'registry_object/delete/', 
            data: data,
            type: 'POST',
            success: function(data){
                if (!data.status == "success")
                {
                    alert(data.message);
                }
                else
                {
                     window.location = base_url + 'data_source/manage_records/' + $('#data_source_id').val();
                }
            }
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
            var li_item = $(this);
            $.ajax({
                url:base_url+'registry_object/tag/remove', 
                type: 'POST',
                data: {ro_id:ro_id,tag:text},
                success: function(data){
                    li_item.remove();
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
            $(this).html('Quality Level 2 - Required Metadata Content Requirements.' );
        else if($(this).parent().attr('qld') == 3)
             $(this).html('Quality Level 3 - Recommended Metadata Content Requirements.' );
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
             var maxRelated = 0
             var showRelated = 0;
             var moreToShow = '';
            if(data.connections.length>20)
            {
                maxRelated = 20;
 
            }else{
                 maxRelated = data.connections.length;
            }

            $.each(data.connections, function(){
       
                var id = this.registry_object_id;
                var title = this.title;
                var key = this.key;
                var ro_class = this['class'];
                var status = this.status;
                var origin = this.origin;
                var relationship = this.relation_type

                var revStr = '';
                if(id)
                {
                    var linkTitle = '<a href="' + base_url + 'registry_object/view/'+id+'">'+title+'</a> <span class="muted">(' + ro_class + ')</span>'; 
                    title = linkTitle;
                }
                if(origin == 'REVERSE_EXT'|| origin == 'REVERSE_INT')
                {
                    revStr = "<em> (Automatically generated reverse link) </em>"
                }

                if (origin=='EXPLICIT') {
                     $('#rorow').show();
                     showRelated++;
                     $('.resolvedRelated[key_value="'+key+'"]').html(title );
                }
                else 
                    {
                    if(showRelated < maxRelated){
                        showRelated++;     
                        $('#rorow').show();
                        var keyFound = false;
                        $('.resolvable_key').each(function(){
                            if($(this).attr('key_value')==key){
                                    keyFound=true;
                            }
                        });
                        if(!keyFound)
                        {
                             var newRow = '<table class="subtable">' +                                      
                                            '<tr><td><table class="subtable1">'+
                                            '<tr><td>Title:</td><td class="resolvedRelated" >'+title+'</td></tr>'+
                                            '<tr><td class="attribute">Key</td>' +
                                            '<td class="valueAttribute resolvable_key" key_value="'+ key +'">'+key+'</td>' +
                                            '</tr>' +
                                            '<tr><td class="attribute">Relation:</td>' +
                                            '<td class="valueAttribute"><table class="subtable1"><tr><td>type:</td><td>'+
                                            relationship+revStr+'</td></tr></table></td>' +
                                            '</tr>' +
                                            '</table></tr></td></table>';
                            $('#related_objects_table').last().append(newRow)  
                        } 
                     }
                 }  
 
            });

            if(data.connections.length > showRelated)
            {
                numToShow = data.connections.length - showRelated;
                moreToShow = '<table class="subtable">' +                                      
                            '<tr><td><table class="subtable1">'+
                            '<tr><td></td><td class="resolvedRelated" > There are '+numToShow+' more related objects</td></tr>'+                                     
                            '</table></tr></td></table>';
                $('#related_objects_table').last().append(moreToShow)     
            }
                              
        }
                      
    });

}
