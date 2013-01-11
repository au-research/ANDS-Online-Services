$(function() {

    var total_column = 0;
    var statuses = ['DRAFT', 'SUBMITTED_FOR_ASSESSMENT', 'APPROVED', 'MORE_WORK_REQUIRED', 'PUBLISHED'];
    
    var data_source_id = $('#data_source_id').val();
    $.getJSON(base_url+'registry_object/get_mmr_data/'+data_source_id, function(data) {
        var mmr_data = constructMMR(data);
        var template = $('#mmr_status_template').html();
        var output = Mustache.render(template, mmr_data);
        $('#mmr_hopper').html(output);
        bindSortables();
        sameHeight();
    });
});

function constructMMR(data){
    var mmr_data = [];
    mmr_data.status = [];
    console.log(data);
    total_column = data.total_statuses_count;
    $.each(data.statuses, function(){
        var status = {status:this.name, count:this.count, ro:this.ro, hasMore:this.hasMore};
        mmr_data.status.push(status);
    });
    mmr_data.span_count = 12 / total_column;
    return mmr_data;
}

function sameHeight(){
    var max_height = 0;
    $('.ro_content').each(function(){
        if($(this).height() > max_height) max_height = $(this).height();
    });
    $('.ro_content').height(max_height);
}

function bindSortables(){
    $( ".sortable" ).sortable({
      connectWith: ".connectedSortable",
      placeholder: "ui-state-highlight",
      receive:function(event, ui){
        //console.log(ui.item[0].id, $(this).attr('status'));
        var attributes = [
        {
            name:'status',
            value:$(this).attr('status')
        }];
        update(ui.item[0].id, attributes);
        sameHeight();
        //console.log(ui);
      },
      sort:function(event, ui){
        //console.log(this);
      }
    });
}

function update(id, postData){
    $.ajax({
        url:base_url+'registry_object/update/'+id, 
        type: 'POST',
        data: postData,
        success: function(data){
            console.info(data);
        }
    });
}