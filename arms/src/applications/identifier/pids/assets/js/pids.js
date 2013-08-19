var params = {};
$(function(){
	initView();
});

$(document).on('click', '#mint_confirm', function(){
	$(this).button('loading');
	var url = $('#mint_form input[name=url]').val();
	var desc = $('#mint_form input[name=desc]').val();
	$.ajax({
		url: base_url+'pids/mint',
		type: 'POST',
		data: {url:url, desc:desc},
		success: function(data){
			// $(this).button('reset');
			// $('#mint_modal').modal('hide');
			location.reload();
		}
	});
}).on('click', '.load_more', function(){
	params['offset'] = $(this).attr('next_offset');
	var button = $(this);
	$.ajax({
		url: base_url+'pids/list_pids', 
		type: 'POST',
		data: {params:params},
		success: function(data){
			var template = $('#pids-more-template').html();
			var output = Mustache.render(template, data);
			button.after(output);
			button.remove();
		}
	});
}).on('submit', '.form-search', function(e){
	e.preventDefault();
	params['offset']=0;
	params['searchText'] = $('#search_query').val();
	listPIDs(params);
}).on('change', '#pid_chooser', function(){
	window.location = "?identifier="+$(this).val();
});

function initView(){
	params['offset'] = 0;
	listPIDs(params);
}

function listPIDs(params) {
	$('#pids').html('');
	params['identifier'] = $('#identifier').val();
	$('#pid_chooser').val(params['identifier']).trigger('liszt:updated');
	if(params['identifier']!='My Identifiers'){
		params['authDomain'] = 'researchdata.ands.org.au';
	}else{
		delete params['identifier'];
		delete params['authDomain'];
	}
	$.ajax({
		url: base_url+'pids/list_pids', 
		type: 'POST',
		data: {params:params},
		success: function(data){
			var template = $('#pids-list-template').html();
			var output = Mustache.render(template, data);
			$('#pids').html(output);
		}
	});
}

