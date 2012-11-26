/**
 * Core Maintenance Javascript
 */

$(function(){
	initView();
});

function initView(){

	//get SOLR stat
	$.getJSON(base_url+'maintenance/getSOLRstat', function(data) {
		var template = $('#solr-template').html();
		var output = Mustache.render(template, data);
		$('#solr').html(output);
	});

	//get Datasources stat
	$.getJSON(base_url+'maintenance/getDataSourcesStat', function(data) {
		var template = $('#ds-template').html();
		var output = Mustache.render(template, data);
		$('#ds').html(output);
		$('#dataSourceSelect').chosen();
	});
}