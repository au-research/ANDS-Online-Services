var searchData = {};
var searchUrl = base_url+'search/filter';

$(document).ready(function() {

	/*GET HASH TAG*/
	$(window).hashchange(function(){
		//var hash = window.location.hash;
		
		var hash = location.href.substr(location.href.indexOf("#"));
		var query = hash.substring(3, hash.length);
		var words = query.split('/');
		$('#search_box, #selected_group, #selected_subject').empty();
		searchData = {};
		$.each(words, function(){
			var string = this.split('=');
			var term = string[0];
			var value = string[1];
			if(term && value) {
				searchData[term] = value;
				switch(term){
					case 'q': 
						$('#search_box').val(value);
						break;
					case 'group': 
						$('#selected_group').html(decodeURIComponent(value));
						break;
					case 'tab':
						$('.tabs a').removeClass('current');
						$('.tabs a[facet_value='+value+']').addClass('current');
						break;
				}
			}
			/**
			 * term could be: q, p, tab, group, type, subject, vocabUriFilter, licence, temporal, n, e, s, w, spatial
			 * resultSort, limitRows, researchGroupSort, subjectSort, typeSort, licenseSort
			 */
		});

		executeSearch(searchData, searchUrl);
	});
	$(window).hashchange(); //do the hashchange on page load
});

function executeSearch(searchData, searchUrl){
	$.ajax({
		url:searchUrl, 
		type: 'POST',
		data: {filters:searchData},
		dataType:'json',
		success: function(data){
			console.log(data);

			$('#search-result, .pagination, #facet-result').empty();

			//search result
			var template = $('#search-result-template').html();
			var output = Mustache.render(template, data.result);
			$('#search-result').html(output);

			//pagination
			var template = $('#pagination-template').html();
			var output = Mustache.render(template, data);
			$('.pagination').html(output);

			//facet
			var template = $('#facet-template').html();
			var output = Mustache.render(template, data);
			$('#facet-result').html(output);

			initSearchPage();
		},
		error: function(data){
			//$('body').prepend(data.responseText);
			console.error(data.responseText);
		}
	});
}

function initSearchPage(){
	//bind the facets
	$('.filter').click(function(){
		searchData[$(this).attr('filter_type')] = encodeURIComponent($(this).attr('filter_value'));
		//searchData.push({label:$(this).attr('facet_type'),value:encodeURIComponent($(this).attr('facet_value'))});
		var query_string = '#!/';
		$.each(searchData, function(i, v){
			query_string += i + '=' + v + '/';
		})
		window.location.hash = query_string;
	});
}