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
    }
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
            case 'to_moreworkrequired':
                var attributes = [{
                    name:'status',
                    value:'MORE_WORK_REQUIRED'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'delete':
                if($(this).attr('ro_id')){
                    if(confirm('Are you sure you want to delete this Registry Objects?')){
                        deleting = [$(this).attr('ro_id')];
                        delete_ro(deleting);
                    }
                }else{
                    if(confirm('Are you sure you want to delete '+selected_ids.length+' Registry Objects?')){
                     delete_ro(selected_ids);
                    }
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
            case 'view':
                if($(this).attr('ro_id')){
                    window.location = base_url+'registry_object/view/'+$(this).attr('ro_id');
                }else window.location = base_url+'registry_object/view/'+selected_ids[0];
                break;
            case 'edit':
                if($(this).attr('ro_id')){
                    window.location = base_url+'registry_object/edit/'+$(this).attr('ro_id')+'#!/advanced/admin';
                }else window.location = base_url+'registry_object/edit/'+selected_ids[0]+'#!/advanced/admin';
                break;
            case 'advance_status':
                var status_to = $(this).attr('to');
                var attributes = [{
                    name:'status',
                    value:status_to
                  }];
                var updating = [$(this).attr('ro_id')];
                  update(updating, attributes);
                break;
        }
    });

});

function init(filters){

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

            $.each(selected_ids, function(){
                $('#'+this).addClass('active');
                $('#'+this).removeClass('active', 3000);
            });
            selected_ids = [];
            selecting_status = '';
            select_all = false;

            bindSortables();
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
                initLayout();
            }
        });
    });
}


function initLayout(){

    // var spare = [];
    // var remain = 95;
    // $('.block:visible').each(function(){
    //     if($(this).attr('count')==0){
    //         $(this).width('20%');
    //         remain = remain - 20;
    //     }else{
    //         spare.push(this);
    //     }
    // });

    // $(spare).each(function(){
    //     var percentage = Math.ceil(remain / spare.length);
    //     $(this).width(percentage+'%');
    // });




    var numBlock = $('.block:visible').length;
    var percentage = Math.ceil(95 / numBlock);
    $('.block').width(percentage+'%');

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
        position: {viewport: $(window), my:'left center'},
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

   

    $('.tipQA').live('mouseover', function(){
        $(this).qtip({
            content: {
                text: 'Loading...', // The text to use whilst the AJAX request is loading
                ajax: {
                    url: base_url+'registry_object/get_quality_view/', 
                    type: 'POST',
                    data: {ro_id: $(this).attr('ro_id')},
                    loading:false,
                    success: function(data, status) {
                        this.set('content.text', data);
                        formatTip(this);
                    }
                }
            },
            position: {viewport: $(window), my:'left center'},
            show: {
                //event: 'click',
                ready: true,
                solo:true,
                effect: function(offset) {
                    $(this).show(); // "this" refers to the tooltip
                }
            },
            hide: {
                fixed:true,
                delay: 800
            },
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'},
            overwrite: false
        });
    });

}

function formatTip(tt){
    var tooltip = $('#ui-tooltip-'+tt.id+'-content');
    
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
        $(this).parent().children('.qa_ok, .qa_error').slideToggle('fast', function(){
            tt.reposition();//fix the positioning
        });
    });
    $('.qa_ok').addClass('success');
    $('.qa_error').addClass('warning');
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
}

function update_selected_list(status){
    selected_ids = [];
    selecting_status = status;
    $('.ro_selected').each(function(){
        selected_ids.push($(this).attr('id'));
    });

    var num = selected_ids.length;
    if(select_all) num = parseInt($('#'+status+' .count').html());
    // console.log(num);
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
    // console.log(selected_ids);
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
                var list = $(this).parents('.status_field');
                if(select_all){
                    total = $('.count', list).text();
                }else total = selected_ids.length;
                return $( "<span class='label label-info helper'>"+total+"</span>" );
            },
            connectToSortable: target
        });
        $('li', this)
        .bind('contextmenu', function(){return false;})
        .bind('mouseover',function(){
            $('.right-menu', this).show();
            $('.toolbar', this).css({opacity:1.0});
        })
        .bind('mouseout',function(){
            $('.right-menu', this).hide();
            $('.toolbar', this).css({opacity:0.2});
        });
        $('.contextmenu').unbind('click').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            if($(this).closest('li').length==1) {
                click_ro($(this).closest('li'),'select');
            }
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
                position: {viewport: $(window), my:'left center', at:'right center'},
                show:{ready:true,effect:false,event:'click'},
                hide:{event:'unfocus'},
                style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
            });
        });

        $('.tipTag').unbind('click').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            var ro_id = $(this).attr('ro_id');
            if($(this).closest('li').length==1) {
                click_ro($(this).closest('li'),'select');
            }
            $(this).qtip({
                content: {
                    text: 'Loading...',
                    ajax:{
                        url: base_url+'registry_object/get_tag_menu',
                        type: 'POST',
                        data: {ro_id:ro_id},
                        success: function(data, status) {
                            this.set('content.text', data);
                            var tooltip = $('#ui-tooltip-'+this.id+'-content');
                            $('.tag_form').submit(function(e){
                                e.preventDefault();
                                e.stopPropagation();
                                var ro_id = $(this).attr('ro_id');
                                var tag = $('input', this).val();
                                var tag_html = '<li>'+tag+'<span class="hide"><i class="icon icon-remove"></i></span></li>';
                                $('.tags', tooltip).append(tag_html);
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
                                    // console.log('remove tag: '+$(this).text()+' from id:'+$(this).parent().attr('ro_id'));
                                    $.ajax({
                                        url:base_url+'registry_object/tag/remove', 
                                        type: 'POST',
                                        data: {ro_id:ro_id,tag:$(this).text()},
                                        success: function(data){
                                            // console.log(data);
                                            // $('#status_message').html(data.msg);
                                        }
                                    });
                                    $(this).remove();                                    
                                }
                            });
                        }
                    }
                },
                position: {viewport: $(window), my:'left center'},
                show:{ready:true,effect:false,event:'click'},
                hide:{event:'unfocus'},
                style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
            });
        });


        $(target).parents('.status_field').droppable({
            accept: from,
            hoverClass:"droppable",
            drop: function( event, ui ) {
                if(selecting_status==status){
                    var attributes = [{
                        name:'status',
                        value:connect_to
                    }];
                    update(selected_ids, attributes);
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
        total = parseInt($('#'+select_all+' .count').html());
    }else{
        url = base_url+'registry_object/update/'
        data = {affected_ids:ids, attributes:attributes};
        total = selected_ids.length
    }

    var text = total+' registry objects updating...';
    $('#status_message').html(text);
    $.ajax({
        url:url, 
        type: 'POST',
        data: data,
        success: function(data){
            init(filters);
            // $('#status_message').html(data.msg);
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