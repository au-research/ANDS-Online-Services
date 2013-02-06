var searchData = {};
var searchUrl = base_url+'search/filter';
var searchBox = null;
var map = null;
var pushPin = null;
var resultPolygons = new Array();
var markersArray = new Array();
var polygonsArray = new Array();
var markerClusterer = null;
var rectangleOptions = null;
var infowindow = null;
$(document).ready(function() {

	/*GET HASH TAG*/
	$(window).hashchange(function(){
		var hash = window.location.hash;

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
						$('.tabs a[filter_value='+value+']').addClass('current');
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
	initMap();
});

function executeSearch(searchData, searchUrl){
	resultPolygons = new Array();
	clearOverlays();
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

			//populate spatial result polygons
			var docs = data.result.docs;
			$(docs).each(function(){
			 	if(this.spatial_coverage_polygons)
			 	{
			 		// console.log(this.spatial_coverage_polygons);
			 	resultPolygons[this.id] = new Array(this.display_title, this.spatial_coverage_polygons[0], this.spatial_coverage_centres[0]);
			 	}

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

	//see if we need to init the map
	if(searchData['map']){
		$('#searchmap').show();
		 processPolygons();
		 resetZoom();
		 $('.post').hover(function(){
		 	//console.log($(this).attr('ro_id'));
		 	clearPolygons();
			polygonsArray[$(this).attr('ro_id')].setMap(map);
		 },function(){
		 	clearPolygons();
		 });
	}

	$('#search_map_toggle').unbind('click');
	$('#search_map_toggle').click(function(e){
		e.preventDefault();
		if(searchData['map']){
			//already map, hide map
			$('#searchmap').hide();
			delete searchData['map'];
		}else{
			//no map, show map
			searchData['map']='show';
			processPolygons();
			resetZoom();
		}
		changeHashTo(formatSearch());
	});

	//add another bind for advanced search specifically for this page
	$('#adv_start_search').click(function(e){
		e.preventDefault();
		e.stopPropagation();
		$('#ad_st').removeClass('exped');
    	$('.advanced_search').slideUp('fast');
	});

	//populate the advanced search field, BLACK MAGIC, not exactly, just some bad code
	if(searchData['q']){
		var q = searchData['q'];
		if(q.indexOf('"')!='-1'){
			all = q.match(/"([^"]+)"/)[1];//anything inside quote
			rest = q.split(q.match(/"([^"]+)"/)[0]).join('');
		}else {
			all ='';
			rest = q;
		}
		$('.adv_all').val(all);
		rest_split = rest.split(" ");
		var nots = [];
		var inputs = '';
		$.each(rest_split, function(){
			if(this.indexOf('-')==0){//anything starts with - is nots
				nots.push(this.substring(1,this.length));
			}else{
				inputs += this;//anything else is normal
			}
		});
		$('.adv_input').val(inputs);
		$('.adv_not').each(function(e,k){//populate the nots
			$(this).val(nots[e]);
		});
	}
}

function initMap(){
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
    map = new google.maps.Map(document.getElementById("searchmap"),myOptions);
	infowindow = new google.maps.InfoWindow();
    pushPin = new google.maps.MarkerImage('http://maps.google.com/intl/en_us/mapfiles/ms/micons/blue.png',
					      new google.maps.Size(32,32),
					      new google.maps.Point(0,0),
					      new google.maps.Point(16,32)
					     );

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
      rectangleOptions = drawingManager.get('rectangleOptions');
      rectangleOptions.fillColor= '#FF0000';
      rectangleOptions.strokeColor= "#FF0000";
      rectangleOptions.fillOpacity= 0.1;
      rectangleOptions.strokeOpacity= 0.8;
      rectangleOptions.strokeWeight= 2;
      rectangleOptions.clickable= false;
      // rectangleOptions.editable= true;
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
     markerClusterer = new  MarkerClusterer(map, null, {zoomOnClick:false});
}


function processPolygons(){
	if(resultPolygons.length)
	{
		for(p in resultPolygons)
		{

			id = p.toString();
			title = resultPolygons[p][0];
			polygons = resultPolygons[p][1];
			centers = resultPolygons[p][2];
			// console.log(id);
			createResultPolygonWithMarker(polygons, centers, title, id);
		}
	}
}

function createResultPolygonWithMarker(polygons, centers, title, id)
{

	//var coords = getCoordsFromInputField(polygons);
	var centerCoords = getPointFromString(centers);
	var coords = getCoordsFromString(polygons);
	createMarker(centerCoords, id);	
    createPolygon(coords, id);
}

function getCoordsFromString(lonLatStr)
{
	var coords = new Array();
		var coordsStr = lonLatStr.split(' ');
		
   		for( var i=0; i < coordsStr.length; i++ )
		{
			coordsPair = coordsStr[i].split(",");
			coords[i] = new google.maps.LatLng(coordsPair[1],coordsPair[0]);
		}	
	//}
	return coords;
}

function getPointFromString(lonLatStr)
{
	lonLatStr = lonLatStr + " ";
	var coordsStr = lonLatStr.split(' ');
	coords = new google.maps.LatLng(coordsStr[1],coordsStr[0]);
	return coords;
}

function createPolygon(coords, id)
{

	polygon = new google.maps.Polygon({
			    paths: coords,
			    map : map,
			    strokeColor: '#008dce',
			    strokeOpacity: 0.7,
			    strokeWeight: 2,
			    fillColor: '#008dce',
			    fillOpacity: 0.2,
			    editable : false
			  });
	polygon.setMap(null);
	polygonsArray[id] = polygon;

}

function createMarker(latlng, id)
{
	var marker = new google.maps.Marker({
	          position: latlng,
	          map: map,
	          icon : pushPin,
	        });
	marker.set("id", id);
	google.maps.event.addListener(marker,"mouseover",function(){
		clearPolygons();
		polygonsArray[marker.id].setMap(map);
	});
	google.maps.event.addListener(marker,"click",function(){
		showPreviewWindowConent(marker);
	});
	google.maps.event.addListener(marker,"mouseout",function(){
		clearPolygons();
	});
	markerClusterer.addMarker(marker);
	markersArray[id] = marker;
}


function clearOverlays() 
{
	if(markerClusterer)
		markerClusterer.clearMarkers();
	clearMarkers();
	clearPolygons();
}

function clearMarkers()
{
  for (m in markersArray) 
  {
    markersArray[m].setMap(null);
  }
}

function showPreviewWindowConent(mOverlay)
{
	roIds = [];
	// either a marker is passed or a marker_cluster
    if(typeof mOverlay.id != 'undefined')
    {
    	roIds.push(mOverlay.id);
    	infowindow.setPosition(mOverlay.position);
    }
    else if(typeof mOverlay.markers_ != 'undefined')
    {
    	$(mOverlay.markers_).each(function(){
    	roIds.push(this.id);
    	});
    	infowindow.setPosition(mOverlay.center_)
    }

	$.ajax({
		url:base_url+'view/preview', 
		data : {roIds:roIds},
		type: 'POST',
		dataType:'json',
		success: function(data){
			infowindow.setContent(data.html);
			infowindow.open(map);
		},
		error: function(data){
			//$('body').prepend(data.responseText);
			console.error("ERROR" + data.responseText);
			return null;
		}
	});
	
}

function clearPolygons()
{
  for (p in polygonsArray) 
  {
    polygonsArray[p].setMap(null);
  }
}

function resetZoom(){
	google.maps.event.trigger(map, 'resize');
	if(searchBox)
	{
		map.setCenter(searchBox.getBounds().getCenter());
		map.fitBounds(searchBox.getBounds());
	}
	else if(searchData['spatial']){
		//harvester https support test
		var spatialBounds = searchData['spatial'];
		spatialBounds = decodeURI(spatialBounds);
		var wsenArray = spatialBounds.split(' ');
		var sw = new google.maps.LatLng(wsenArray[1],wsenArray[0]);
		var ne = new google.maps.LatLng(wsenArray[3],wsenArray[2]);
		//148.359375 -32.546813 152.578125 -28.998532
		//LatLngBounds(sw?:LatLng, ne?:LatLng)
		var rBounds = new google.maps.LatLngBounds(sw,ne);
		//var rectangleOptions = new google.maps.RectangleOptions();
	  	rectangleOptions.fillColor= '#FF0000';
	  	rectangleOptions.strokeColor= "#FF0000";
	  	rectangleOptions.fillOpacity= 0.1;
	  	rectangleOptions.strokeOpacity= 0.8;
	  	rectangleOptions.strokeWeight= 2;
	  	rectangleOptions.clickable= false;
	  	rectangleOptions.bounds = rBounds;

	  	var geoCodeRectangle = new google.maps.Rectangle(rectangleOptions);
		geoCodeRectangle.setMap(map);
	  	searchBox = geoCodeRectangle;
	 	map.setCenter(searchBox.getBounds().getCenter());
		map.fitBounds(searchBox.getBounds());
	}

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

/**
 * Triggers the clusterclick event and zoom's if the option is set.
 */
ClusterIcon.prototype.triggerClusterClick = function() {
  var markerClusterer = this.cluster_.getMarkerClusterer();

  // Trigger the clusterclick event.
  google.maps.event.trigger(markerClusterer, 'clusterclick', this.cluster_);

  if (markerClusterer.isZoomOnClick() || this.cluster_.markers_.length > 9) 
  {
    // Zoom into the cluster.
    this.map_.fitBounds(this.cluster_.getBounds());
  }
  else if(this.cluster_.markers_.length > 9)
  {
	this.map_.fitBounds(this.cluster_.getBounds());
  }
  else{
	showPreviewWindowConent(this.cluster_);
  }

};
