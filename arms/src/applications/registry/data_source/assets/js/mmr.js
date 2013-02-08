var selected_ids=[],selecting_status;
var filters = {};
$(function() {

    //check if there's any get variable
    var $_GET = {};
    document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
        function decode(s) {
            return decodeURIComponent(s.split("+").join(" "));
        }

        $_GET[decode(arguments[1])] = decode(arguments[2]);
    });
    //console.log($_GET["filters"]);

    //if filters are determined in the get variable, they will be json string, parse them and use them instead of default
    if($_GET['filters']){
        filters = jQuery.parseJSON($_GET['filters']);
    }else{
        var sort = {}; sort['updated'] = 'asc';
        filters['sort'] = sort;
        // var filter = {}; filter['status'] = 'PUBLISHED';
        // filters['filter'] = filter;
    }
    //console.log(JSON.stringify(filters, null, 2));
    init(filters);

    $(document).on("click", ".sortable li", function(e){
        if(e.button==2){
            if(!$(this).hasClass('ro_selected')) click_ro(this, 'select');
            var status = $(this).attr('status');
            var menu = $('#context-menu-'+status+' ul');
            console.log($('.unflag', menu).length);
            if($('span.flag', this).length){
                $('.unflag', menu).show();
            }else{
                $('.unflag', menu).hide();
            }
        }else{
            click_ro(this, 'toggle');
        }
    }).on('click', '.op', function(e){
        console.log("right-click");
        var action = $(this).attr('action');
        var status = $(this).attr('status');
        switch(action){
            case 'select_all':
                action_list(status, 'select_all');
                break;
            case 'to_draft':
                var attributes = [{
                    name:'status',
                    value:'DRAFT'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_submit':
                var attributes = [{
                    name:'status',
                    value:'SUBMITTED_FOR_ASSESSMENT'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_assess':
                var attributes = [{
                    name:'status',
                    value:'ASSESSMENT_IN_PROGRESS'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_approve':
                var attributes = [{
                    name:'status',
                    value:'APPROVED'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_publish':
                var attributes = [{
                    name:'status',
                    value:'PUBLISHED'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'delete':
                if(confirm('Are you sure you want to delete '+selected_ids.length+' Registry Objects?')){
                  delete_ro(selected_ids);
                }
                break;
            case 'flag':
                var attributes = [{
                    name:'flag',
                    value:'t'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'un_flag':
                var attributes = [{
                    name:'flag',
                    value:'f'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'set_gold_status_flag':
                var attributes = [{
                    name:'gold_status_flag',
                    value:'t'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'un_set_gold_status_flag':
                var attributes = [{
                    name:'gold_status_flag',
                    value:'f'
                  }];
                  update(selected_ids, attributes);
                break;
        }
    });

});

function init(filters){
    selected_ids = [];
    var data_source_id = $('#data_source_id').val();
    //$('.pool').hide();
     $('#active_filters').html('<em>Loading...</em>');
    $.ajax({
        url:base_url+'data_source/get_mmr_data/'+data_source_id, 
        type: 'POST',
        dataType:'JSON',
        data: {'filters':filters},
        success: function(data){
            console.log(data);
            
            $.each(data.statuses, function(d){
                // console.log(this);
                var template = $('#mmr_status_template').html();
                var output = Mustache.render(template, this);
                $('#'+d).html(output);
                var block = $('#'+d).parent();
                var num = parseInt($(block).attr('count'));
                if(!num){
                    $(block).attr('count', this.count);
                }else{
                    num = num + parseInt(this.count);
                    $(block).attr('count', num);
                }
                $('#'+d).parent().show();
            });
            $('.pool').show();

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
        var filter = JSON.stringify(filters, null, 2);
        console.log(filters);
        $.ajax({
            url:base_url+'data_source/get_more_mmr_data/', 
            type: 'POST',
            data: {ds_id:ds_id,offset:offset,filter:filter,status:status},
            success: function(data){
                if(data){
                    new_offset = offset+10;
                    $(button).attr('offset', new_offset);

                    var template = $('#mmr_data_more').html();
                    var output = Mustache.render(template, data);
                    $('ul[status='+status+']').append(output);

                    if(!data.hasMore) $(button).remove();
                }
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

    var spare = [];
    var remain = 90;
    $('.block:visible').each(function(){
        if($(this).attr('count')==0){
            $(this).width('15%');
            remain = remain - 15;
        }else{
            spare.push(this);
        }
    });

    $(spare).each(function(){
        var percentage = Math.ceil(remain / spare.length);
        $(this).width(percentage+'%');
    });


    // var numBlock = $('.block:visible').length;
    // var percentage = Math.ceil(90 / numBlock);
    // $('.block').width(percentage+'%');

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


    $('#search_form').unbind('submit').submit(function(e){
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

    $('#active_filters').html('');
    if(filters['filter'] && filters['filter'].length > 0){
        $('#active_filters').append('<em>Active Filters: </em>');
    }

    $(filters['filter']).each(function(){
        $.each(this, function(key, value){
            $('#active_filters').append('<span class="removeFilter tag" name="'+key+'"><a href="javascript:;">'+key+':'+value+' <i class="icon icon-remove"></i></a></span>');
        });
    });

    $('.removeFilter').unbind('click').click(function(){
        var name = $(this).attr('name');
        delete filters['filter'][name];
        init(filters);
    });

    $('.filter').unbind('click').click(function(e){
        if(!$(this).closest('li').hasClass('disabled')){
            var name = $(this).attr('name');
            var value = $(this).attr('value');
            var filter = {}; filter[name] = value;filters['filter'] = filter;
            console.log(filters);
            init(filters);
        }else{
            e.preventDefault();
            e.stopPropagation();
        }
    });

    $('.context').contextmenu();

    $('.select_all').unbind('click').click(function(){
        var status = $(this).attr('status');
        if($('i',this).hasClass('icon-ok-circle')){//deselect
            $('i',this).removeClass('icon-ok-circle').addClass('icon-ok-sign');
            action_list(status, 'select_all');
        }else if($('i',this).hasClass('icon-ok-sign')){//select all
            $('i',this).removeClass('icon-ok-sign').addClass('icon-ok-circle');
            action_list(status, 'deselect_all');
        }
    });
}

function action_list(status, action){
    var list = $('ul[status='+status+']');
    if(action=='select_all'){
        // console.log($('li.ro_item', list).length)
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
            selected_ids.push($(this).attr('id'));
        });
       
    }else if(action=='deselect_all'){
        $.each($('li.ro_item', list), function(index, val) {
            $(this).removeClass('ro_selected');
            selected_ids = [];
        });
    }
    selected_ids = $.unique(selected_ids);
    update_selected_list(status);
    console.log(selected_ids);
}

function update_selected_list(status){
    var num = selected_ids.length;
    var list = $('.ro_box[status='+status+']');
    var selected = $('div.selected_status', list);
    if(num>0){
        var text = num + ' registry objects selected.';
        selected.html(text);
        selected.slideDown();
    }else{
        selected.slideUp();
    }
}

function click_ro(ro_item, action){
    var ro_id = $(ro_item).attr('id');
    var status = $(ro_item).attr('status');

    if(status==selecting_status){
        if($.inArray(ro_id, selected_ids)==-1){
            selected_ids.push(ro_id);
        }else{
            selected_ids.splice( $.inArray(ro_id, selected_ids), 1 );
        }
    }else{
        $('.ro_item').removeClass('ro_selected');
        selecting_status=status;
        selected_ids=[];//empty
        selected_ids.push(ro_id);
    }
    
    if(action=='toggle'){
        $('#'+ro_id).toggleClass('ro_selected', 75);
    }else if(action=='select'){
        $('#'+ro_id).addClass('ro_selected', 75);
    }
    selected_ids = $.unique(selected_ids);
    update_selected_list(status)
    // console.log(selected_ids);
}


function bindSortables(){

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
            delay:100,
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
            // start: function(e, info) {
            //     info.item.after(info.item.siblings('li.ro_selected'));
            //     $('li', target).animate({
            //         opacity:0.5,
            //         backgroundColor:'#C1F4E7'
            //     });
            //     $(target).animate({
            //         backgroundColor:'#C1F4E7'
            //     })
            //     //info.item.siblings("li.selected").appendTo(info.item);
            // },
            
            // stop: function(e, info) {
            //     //info.item.after(info.item.find("li"))
            //     $('li', target).animate({
            //         opacity:1,
            //         backgroundColor:'white'
            //     });
            //     $(target).animate({
            //         backgroundColor:'white'
            //     });
            // },
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
            init(filters);
            //console.log(data);
        }
    });
}

function delete_ro(ids){
    $.ajax({
        url:base_url+'registry_object/delete/', 
        type: 'POST',
        data: {affected_ids:ids},
        success: function(data){
            init(filters);
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
            style: {
                width:750
            },
            position: {viewport: $(window)},
            show:{ready:true,effect:false,event:'click'},
            hide:{event:'unfocus'},
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
        });
    });
}