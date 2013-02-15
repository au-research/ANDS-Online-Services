var selected_ids=[],selecting_status,select_all=false;
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
        var sort = {}; sort['updated'] = 'desc';
        filters['sort'] = sort;
         // var filter = {}; filter['status'] = 'PUBLISHED';
         // var or_filter = {};or_filter['status'] = 'DRAFT';
         // filters['filter'] = filter;
         // filters['or_filter'] = or_filter;
    }
    //console.log(JSON.stringify(filters, null, 2));
    init(filters);

    $(document).on('mouseup', '.sortable li', function(e){
        if(e.which==3){
            e.preventDefault();
            if(!$(this).hasClass('ro_selected')) click_ro(this, 'select');
            $('.contextmenu',this).click();
        }
    }).on('dblclick', '.sortable li', function(e){
        window.location = base_url+'registry_object/view/'+$(this).attr('id');
    }).on('click','.sortable li',function(e){
        if(e.metaKey || e.ctrlKey){
            click_ro(this, 'select');
        }else if(e.shiftKey){
            click_ro(this, 'select_until');
        }else{
            if(!$(this).hasClass('ro_selected')){
                click_ro(this, 'select_1');    
            }else{
                click_ro(this, 'toggle');
            }
        }
    });

    $(document).on('click', '.op', function(e){

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
    $('#status_message').html('<em>Loading...</em>');
    $('#status_message').show();
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
            $('#status_message').hide();
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


function initLayout(){

    var spare = [];
    var remain = 95;
    $('.block:visible').each(function(){
        if($(this).attr('count')==0){
            $(this).width('10%');
            remain = remain - 10;
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
    $('.pool').height(max_height+50);
    //$('.ro_box[status=SUBMITTED_FOR_ASSESSMENT], .ro_box[status=APPROVED], .ro_box[status=ASSESSMENT_IN_PROGRESS],.ro_box[status=PUBLISHED]').height(max_height);
    //var draft_height = $('.ro_box[status=DRAFT]').height() + max_height - $('.ro_box[status=DRAFT]').parent('.block').height();
   // $('.ro_box[status=DRAFT]').height(draft_height);
    // 
    // 


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
            var filter = {}; filter[name] = value;
            if(filters['filter']){
                filters['filter'][name] = value;
            }else filters['filter'] = filter;
            console.log(filters);
            init(filters);
        }else{
            e.preventDefault();
            e.stopPropagation();
        }
    });

    $('.selector_menu').qtip({
        content:{
            text: function(){
                return $('.selecting_menu',this).html();
            }
        },
        position: {viewport: $(window)},
        show:{ready:false,effect:false,event:'click'},
        hide:{event:'unfocus'},
        style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
    });

    $('.selector_btn').die().live({
        click:function(){
            var status = $(this).attr('status');
            var list = $('.sortable[status='+status+']');
            if($(this).hasClass('select_all')){
                action_list(status, 'select_all');
            }else if($(this).hasClass('select_display')){
                action_list(status, 'select_display');
            }else if($(this).hasClass('select_none')){
                action_list(status, 'select_none');
            }else if($(this).hasClass('select_flagged')){
                action_list(status, 'select_flagged');
            }
        }
    });

    if(select_all){
        var list = $('.sortable[status='+select_all+']');
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
    }

    $('.status_field:visible').each(function(){
        var total = $('li.ro_item',this).length;
        $('.select_display span', this).html(total);
    });

    // $('.context').contextmenu();

}

function action_list(status, action){
    var list = $('ul[status='+status+']');
    selecting_status = status;
    if(action=='select_display'){
        select_all = false;
        // console.log($('li.ro_item', list).length)
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
       
    }else if(action=='select_none'){
        selecting_status = '';
        select_all = false;
        $('.ro_selected').removeClass('ro_selected');
    }else if(action=='select_all'){
        select_all = status;
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
    }
    selected_ids = $.unique(selected_ids);
    update_selected_list(status);
    // console.log(selected_ids);
}

function update_selected_list(status){
    selected_ids = [];
    selecting_status = status;
    $('.ro_selected').each(function(){
        selected_ids.push($(this).attr('id'));
    });

    var num = selected_ids.length;
    var list = $('.ro_box[status='+status+']');
    // var selected = $('div.selected_status', list);
    var selected = $('#status_message');
    if(num>0){
        var text = num + ' registry objects selected.';
        selected.html(text);
        selected.show();
    }else{
        selected.hide(50);
    }
}

function click_ro(ro_item, action){
    var ro_id = $(ro_item).attr('id');
    var status = $(ro_item).attr('status');
    
    if(action=='toggle'){
        $('#'+ro_id).toggleClass('ro_selected');
    }else if(action=='select'){
        $('#'+ro_id).addClass('ro_selected');
    }else if(action=='select_1'){
        $('.sortable li').removeClass('ro_selected');
        $('#'+ro_id).addClass('ro_selected');
    }else if(action=='select_until'){
        $('#'+ro_id).addClass('ro_selected');
        var prev = $('#'+ro_id).prevAll('.ro_selected').attr('id');
        $('#'+ro_id).prevUntil('#'+prev).addClass('ro_selected');
    }
    selected_ids = $.unique(selected_ids);
    update_selected_list(status);
    //console.log(selected_ids);
}


function bindSortables(){

    $('.sortable li').draggable('destroy');

    $('.sortable').each(function(){
        var status = $(this).attr('status');
        var from = '.sortable[status='+status+'] li';
        var connect_to = $(this).attr('connect_to');
        var target = $('.sortable[status='+connect_to+']');

        var ds_id = $('#data_source_id').val();

        $('li', this).draggable({
            cursor: "move",cursorAt:{top:-5,left:-5},scroll:true,
            helper: function(e){
                return $( "<span class='label label-info helper'>"+selected_ids.length+"</span>" );
            },
            connectToSortable: target
        });
        $('li', this).bind('contextmenu', function(){return false;})
        .bind('mouseover',function(){
            $('.contextmenu', this).show();
        })
        .bind('mouseout',function(){
            $('.contextmenu', this).hide();
        });
        $('.contextmenu').unbind('click').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            // if($(this).parent('li').length==1) click_ro($(this).parent('li'),'select');
            var context_status = $(this).attr('status');
            $(this).qtip({
                content: {
                    text: 'Loading...',
                    ajax:{
                        url: base_url+'data_source/get_mmr_menu',
                        type: 'POST',
                        data: {data_source_id:ds_id,status:context_status,affected_ids:selected_ids,selecting_status:selecting_status}
                    }
                },
                position: {viewport: $(window)},
                show:{ready:true,effect:false,event:'click'},
                hide:{event:'unfocus'},
                style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
            });
        });


        $(target).droppable({
            accept: from,
            hoverClass:"droppable",
            drop: function( event, ui ) {
                if(selecting_status==status){
                    var attributes = [{
                        name:'status',
                        value:connect_to
                    }];
                    update(selected_ids, attributes);
                    var text = selected_ids.length+' registry objects have been moved to '+connect_to;
                    $('#status_message').html(text);
                }
            }
        });
    });
}




function bindSortables_old(){

    $('.sortable').sortable('destroy');
    $('.sortable').each(function(){
        var connect_to = $(this).attr('connect_to');
        var target = $('.sortable[status='+connect_to+']');
        // var target = $('.sortable');

        $(this).sortable({
            connectWith: target,
            placeholder: "ui-state-highlight",
            scroll:false,
            revert:'invalid',
            delay:100,
            item:'li.ro_selected',
            helper: false,
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
            sort:function(e, ui){
                $(ui.item.context).offset({top:e.pageY-10,left:e.pageX-10});
            }
        });
    });
    
}


function update(ids, attributes){
    if(select_all){
        ids = select_all;
        url = base_url+'registry_object/update/all';
        data = {data_source_id:$('#data_source_id').val(),select_all:select_all, attributes:attributes};
    }else{
        url = base_url+'registry_object/update/'
        data = {affected_ids:ids, attributes:attributes};
    }
    $.ajax({
        url:url, 
        type: 'POST',
        data: data,
        success: function(data){
            init(filters);
            console.log(data);
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