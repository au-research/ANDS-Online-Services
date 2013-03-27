$(document).ready(function() {

	$("#anzsrc-vocab").vocab_widget({mode: 'search',cache: false,repository: 'anzsrc-for',target_field: 'label'});

	$("#vocab-tree").vocab_widget({mode:'tree',repository:'anzsrc-for'})
	.on('treeselect.vocab.ands', function(event) {
		var target = $(event.target);
		var data = target.data('vocab');
		// alert('You clicked ' + data.label + '\r\n<' + data.about + '>');
		loadVocabDetail(data.about);
	});
});


function loadVocabDetail(about){
	$.ajax({
		url: base_url+'browse/loadVocab', 
		type: 'POST',
		data: {url:about},
		success: function(data){
			$('#content').html(data);
			loadSearchResult(about, 0);
		}
	});
}

function loadSearchResult(about, start){
	$.ajax({
		url:base_url+'browse/search', 
		type: 'POST',
		data:{url:about, start:start},
		success: function(data){
			var template = $('#link_list_template').html();
			var output = Mustache.render(template, data);
			$('#vocab_search_result').html(output);

			$('.vocab-info-table tr:gt(1)').hide();
			$('#show_vocab_metadata_link').click(function(){
				$('.vocab-info-table tr:gt(1)').show();
				$(this).remove();
			});

			$('.suggestor_paging').click(function(){
				loadSearchResult(about, $(this).attr('offset'));
			});
		}
	});
}
