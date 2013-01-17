var selected_ids=[];
var select_all='';
var exclude_ids=[];
var filters = {};
$(function() {
    //filters['search'] = '';
    var sort = {}; sort['updated'] = 'asc';
    filters['sort'] = sort;
    var filter = {}; filter['class'] = 'collection';
    filters['filter'] = filter;

    init(filters);
});

function init(filters){
    selected_ids = [];
    var data_source_id = $('#data_source_id').val();

    $.ajax({
        url:base_url+'registry_object/get_mmr_data/'+data_source_id, 
        type: 'POST',
        dataType:'JSON',
        data: {'filters':filters},
        success: function(data){
            console.log(data);
        
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
        }
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
    //$('.ro_box[status=SUBMITTED_FOR_ASSESSMENT], .ro_box[status=APPROVED], .ro_box[status=ASSESSMENT_IN_PROGRESS],.ro_box[status=PUBLISHED]').height(max_height);
    //var draft_height = $('.ro_box[status=DRAFT]').height() + max_height - $('.ro_box[status=DRAFT]').parent('.block').height();
   // $('.ro_box[status=DRAFT]').height(draft_height);
    // 
    // 
    $('span.icon').unbind('click').click(function(){
        var list = $(this).closest('.widget-box').find('.widget-content');
        $(list).slideToggle();
    });

    $('.op').unbind('click').click(function(){

        var op = $(this).attr('op');
        var ds_id = $(this).closest('.nav-list').attr('data_source_id');
        var status = $(this).closest('.nav-list').attr('status');
        switch(op){
            case 'select_all':
                select_all=status;
                $('.sortable[status='+status+'] li').addClass('ro_selected');
                break;
        }
    });

    $('#search_form').submit(function(e){
        e.preventDefault();
        e.stopPropagation();
        var search_term = $('input', this).val();
        filters['search']=search_term;
        init(filters);
    });

    //init filters
    $('.sort').find('span').removeClass('icon-chevron-down').removeClass('icon-chevron-up');
    $(filters['sort']).each(function(){
        $.each(this, function(key, value){
            var direction = '';
            if(value=='asc'){
                direction = 'up';
            }else if(value=='desc'){
                direction = 'down';
            }
            $('.sort[sort='+key+']').attr('value', value).find('span').addClass('icon-chevron-'+direction);
        });
    });

    $('.sort').unbind('click').click(function(){
        var value = $(this).attr('value');
        var sort = $(this).attr('sort');
        if(value=='desc'){
            value = 'asc';
        }else if(value=='asc'){
            value='desc';
        }else{
            value='desc';
        }
        var sorting = {};
        sorting[sort] = value;
        filters['sort'] = sorting;
        console.log(filters['sort']);
        init(filters);
    });
}

function bindSortables(){
    $('.sortable li').unbind('click');
    $(".sortable li").click(function() {

        $(this).toggleClass("ro_selected",75);
        var id = $(this).attr('id');
        if($(this).attr('status')==select_all){
            //exclude the item
            exclude_ids.push($(this).attr('id'));
        }else{
            //include the item
            
            if($.inArray($(this).attr('id'), selected_ids)==-1){
                selected_ids.push($(this).attr('id'));
            }else{
                selected_ids.splice( $.inArray($(this).attr('id'), selected_ids), 1 );
            }
        }
        console.log(selected_ids, exclude_ids);
    });
    $('.sortable').sortable('destroy');
    $('.sortable').each(function(){
        var connect_to = $(this).attr('connect_to');
        var target = $('.sortable[status='+connect_to+']');
        // var target = $('.sortable');

        $(this).sortable({
            connectWith: target,
            placeholder: "ui-state-highlight",
            scroll:false,
            revert:false,
            item:'li.ro_item',
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
            init();
        }
    });
}

function updateAll(data_source_id, exclude, attributes){

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
            style: {
                width:750
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
        var content = $(this).closest('.widget-title').find('.ro_menu');
        if(content){
            $(this).qtip({
                content: $(content),
                position: {my:'top right', at:'left bottom'},
                show:{ready:true,effect:false,event:'click'},
                hide:{event:'unfocus'},
                style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
            }); 
        }
    });
}