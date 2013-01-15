var selected_ids=[];
$(function() {
    init();
});

function init(){
    selected_ids = [];
    var data_source_id = $('#data_source_id').val();
    $.getJSON(base_url+'registry_object/get_mmr_data/'+data_source_id, function(data) {
        console.log(data);
        //var mmr_data = constructMMR(data);
        // var template = $('#mmr_status_template').html();
        // var output = Mustache.render(template, data);
        // $('#mmr_hopper').html(output);
        // 
        
        $.each(data.statuses, function(d){
            var template = $('#mmr_status_template').html();
            var output = Mustache.render(template, this);
            $('#'+d).html(output);
            $('#'+d).parent().show();
        });

        bindSortables();
        bindPreviews();
        bindShowMore();
        initLayout();
        $('.stick').sticky();
    });
}

function bindShowMore(){
    $('.show_more').click(function(){
        var ds_id = $(this).attr('ds_id');
        var offset = parseInt($(this).attr('offset'));
        var status = $(this).attr('status');
        var button = this;
        $.ajax({
            url:base_url+'registry_object/get_more_mmr_data/', 
            type: 'POST',
            data: {ds_id:ds_id,offset:offset,status:status},
            success: function(data){
                console.log(data);
                new_offset = offset+10;
                $(button).attr('offset', new_offset);

                var template = $('#mmr_data_more').html();
                var output = Mustache.render(template, data);
                $('ul[status='+status+']').append(output);

                if(!data.hasMore) $(button).remove();
                bindSortables();
                bindPreviews();
                initLayout();
            }
        });
    });
}

function constructMMR(data){
    var mmr_data = [];
    mmr_data.status = [];
    console.log(data);
    total_column = data.total_statuses_count;
    $.each(data.statuses, function(){
        var status = {status:this.name, count:this.count, ro:this.ro, hasMore:this.hasMore, offset:this.offset+10,connectTo:this.connectTo,sub_ro:this.sub_ro,hasSub:this.hasSub};
        mmr_data.status.push(status);
    });
    mmr_data.span_count = Math.floor(12 / total_column);
    mmr_data.ds_id = data.ds.data_source_id;
    return mmr_data;
}

function initLayout(){

    var numBlock = $('.block:visible').length;
    var percentage = Math.ceil(90 / numBlock);
    $('.block').width(percentage+'%');

    var max_height = 0;
    $('.block').height('auto');
    $('.block').each(function(){
        if($(this).height() > max_height) max_height = $(this).height();
    });
    // $('.ro_box[status=SUBMITTED_FOR_ASSESSMENT], .ro_box[status=APPROVED], .ro_box[status=ASSESSMENT_IN_PROGRESS],.ro_box[status=PUBLISHED]').height(max_height);
    // var draft_height = $('.ro_box[status=DRAFT]').height() + max_height - $('.ro_box[status=DRAFT]').parent('.block').height();
    // $('.ro_box[status=DRAFT]').height(draft_height);
}

function bindSortables(){
    $('.sortable li').unbind('click');
    $(".sortable li").click(function() {
        $(this).toggleClass("ro_selected",75);
        var id = $(this).attr('id');
        if($.inArray($(this).attr('id'), selected_ids)==-1){
            selected_ids.push($(this).attr('id'));
        }else{
            selected_ids.splice( $.inArray($(this).attr('id'), selected_ids), 1 );
        }
    });
    $('.sortable').sortable('destroy');
    $('.sortable').each(function(){
        var connect_to = $(this).attr('connect_to');
        // var target = $('.sortable[status='+connect_to+']');
        var target = $('.sortable');

        $(this).sortable({
            connectWith: target,
            placeholder: "ui-state-highlight",
            receive:function(event, ui){
              //console.log(selected_ids);
              var attributes = [{
                  name:'status',
                  value:$(this).attr('status')
              }];
              if(selected_ids.length==0) selected_ids.push(ui.item[0].id);
              $('li', target).animate({
                    opacity:1,
                    marginleft:'0'
                });
              update(selected_ids, attributes);
              //console.log(ui);
            },
            start: function(e, info) {
                info.item.after(info.item.siblings('li.ro_selected'));
                $('li', target).animate({
                    opacity:0.5,
                    backgroundColor:'#C1F4E7'
                });
                $(target).animate({
                    backgroundColor:'#C1F4E7'
                })
                //info.item.siblings("li.selected").appendTo(info.item);
            },
            out: function(e, info) {
                //info.item.after(info.item.find("li"))
                $('li', target).animate({
                    opacity:1,
                    backgroundColor:'white'
                });
                $(target).animate({
                    backgroundColor:'white'
                })
            },
            stop: function(e, info) {
                //info.item.after(info.item.find("li"))
                $('li', target).animate({
                    opacity:1,
                    backgroundColor:'white'
                });
                $(target).animate({
                    backgroundColor:'white'
                })
            },
            over: function(e, info){
                
            },
            sort:function(e, ui){
              $(ui.item.context).offset({top:e.pageY-10,left:e.pageX-10});
            }
        });
    });
    
}

function update(ids, attributes){
    $.ajax({
        url:base_url+'registry_object/update/', 
        type: 'POST',
        data: {affected_ids:ids, attributes:attributes},
        success: function(data){
            //console.info(data);
            init();
        }
    });
}

function bindPreviews(){
    $('.ro_preview').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        var ro_id = $(this).attr('ro_id');
         $(this).qtip({
            content: {
                text: 'Loading...',
                ajax: {
                    url: base_url+'registry_object/preview/'+ro_id+'/pane',
                    type: 'GET'
                }
            },
            position: {viewport: $('.pool')},
            show:{ready:true,effect:false,event:'click'},
            hide:{event:'unfocus'},
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
        });
    });

    $('.show_menu').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        var content = $(this).parent().next('.ro_menu');
        $(this).qtip({
            content: content,
            position: {my:'top right', at:'left bottom'},
            show:{ready:true,effect:false,event:'click'},
            hide:{event:'unfocus'},
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
        });
    });
}