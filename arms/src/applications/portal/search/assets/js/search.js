var searchData = [];
var searchUrl = base_url+'search/filter';

$(document).ready(function() {

	/*GET HASH TAG*/
	$(window).hashchange(function(){
		//var hash = window.location.hash;
		var hash = location.href.substr(location.href.indexOf("#"));
		var query = hash.substring(3, hash.length);
		var words = query.split('/');
		$.each(words, function(){
			var string = this.split('=');
			var term = string[0];
			var value = string[1];
			//searchData[term] = value;
			searchData.push({label:term,value:value});
			$(jQuery.parseJSON(JSON.stringify(searchData))).each(function() {  
				if(this.label=='q'){
					$('#search_box').val(this.value);
				}
			});
			/**
			 * term could be: q, p, tab, group, type, subject, vocabUriFilter, licence, temporal, n, e, s, w
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

			//search result
			$('#search-result').html();
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
			console.error(data);
		}
	});
}

function initSearchPage(){

	//bind the facets
	$('.facet_select').click(function(){
		searchData.push({label:$(this).attr('facet_type'),value:$(this).attr('facet_value')});
		executeSearch(searchData, searchUrl);
	});
}