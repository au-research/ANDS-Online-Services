var searchData = {};
var searchUrl = base_url+'search/filter';
var searchBox = null;
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

	

		var latlng = new google.maps.LatLng(-25.397, 133.644);
		
	    var myOptions = {
	      zoom: 4,
	      center: latlng,
	      disableDefaultUI: true,
	      panControl: true,
	      zoomControl: true,
	      mapTypeControl: true,
	      scaleControl: true,
	      streetViewControl: false,
	      overviewMapControl: false,
	      mapTypeId: google.maps.MapTypeId.ROADMAP
	    };
	    var map = new google.maps.Map(document.getElementById("searchmap"),myOptions);
	    
	    
	    var drawingManager = new google.maps.drawing.DrawingManager({
	        drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
	        drawingControl: true,
	        drawingControlOptions: {
	          position: google.maps.ControlPosition.TOP_CENTER,
	          drawingModes: [
	           // google.maps.drawing.OverlayType.MARKER,
	           // google.maps.drawing.OverlayType.CIRCLE,
	            google.maps.drawing.OverlayType.RECTANGLE
	          ]
	        },
	        rectangleOptions:{
	        	fillColor: '#FF0000'
	        }
	      });
	      drawingManager.setMap(map);
	      var rectangleOptions = drawingManager.get('rectangleOptions');
	      rectangleOptions.fillColor= '#FF0000';
	      rectangleOptions.strokeColor= "#FF0000";
	      rectangleOptions.fillOpacity= 0.1;
	      rectangleOptions.strokeOpacity= 0.8;
	      rectangleOptions.strokeWeight= 2;
	      rectangleOptions.clickable= false;
	      rectangleOptions.editable= true;
	      rectangleOptions.zIndex= 1;     
	      
	      drawingManager.set('rectangleOptions', rectangleOptions);
	    
	     google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
	         if (e.type == google.maps.drawing.OverlayType.RECTANGLE) {
	          // Switch back to non-drawing mode after drawing a shape.
	        if(searchBox != null)
	        {
	        	searchBox.setMap(null);
	        }
	        drawingManager.setDrawingMode(null);
	        var geoCodeRectangle = e.overlay;
	        searchBox = geoCodeRectangle;
	        var bnds = geoCodeRectangle.getBounds();
	        var n = bnds.getNorthEast().lat().toFixed(6);
        	var e = bnds.getNorthEast().lng().toFixed(6);
        	var s = bnds.getSouthWest().lat().toFixed(6);
        	var w = bnds.getSouthWest().lng().toFixed(6);
	        searchData['spatial'] = w + ' ' + s + ' ' + e + ' ' + n;
	        changeHashTo(formatSearch());
	        }

	       });
	
});

function executeSearch(searchData, searchUrl){
	$.ajax({
		url:searchUrl, 
		type: 'POST',
		data: {filters:searchData},
		dataType:'json',
		success: function(data){
			//console.log(data);

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

			var docs = data.result.docs;
			$(docs).each(function(){
				//console.log(this);
				console.log(this.id, this.spatial);
			});

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
		changeHashTo(formatSearch());
	});
}


function formatSearch()
{
	var query_string = '#!/';
	$.each(searchData, function(i, v){
		query_string += i + '=' + v + '/';
	})
	return query_string;
}

function changeHashTo(location){
	window.location.hash = location;
}