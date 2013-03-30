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
var subjectType = 'anzsrc-for';
$(document).ready(function() {

	/*GET HASH TAG*/
	$(window).hashchange(function(){
		var hash = window.location.hash;
		var hash = location.href.substr(location.href.indexOf("#"));
		var query = hash.substring(3, hash.length);
		var words = query.split('/');
		// $('.tabs').hide();
		$('#search_box, #selected_group, #selected_subject').empty();
		searchData = {};
		fieldString = '';
		$.each(words, function(){
			var string = this.split('=');
			var term = string[0];
			var value = string[1];
			if(term && value) {
				value = decodeURIComponent(value);
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
			$.each(data.solr_result.response.docs, function(){
				// log(this.list_title, this.score);
			});
			var numFound = data.result.numFound;
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
			 	if(this.spatial_coverage_polygons){
			 		resultPolygons[this.id] = new Array(this.display_title, this.spatial_coverage_polygons[0], this.spatial_coverage_centres[0]);
			 	}
			});
			initSearchPage();
		},
		error: function(data){
			$('#search-result').html('There was a problem connecting to the server. Please try again in a little while...');
		}
	});
}

$(document).on('click', '.filter',function(e){
	searchData['p']=1;
	searchData[$(this).attr('filter_type')] = encodeURIComponent($(this).attr('filter_value'));
	if($(this).attr('filter_type')=='subject_vocab_uri'){
		searchData['subject_vocab_uri_display'] = $(this).text();
	}
	//searchData.push({label:$(this).attr('facet_type'),value:encodeURIComponent($(this).attr('facet_value'))});
	changeHashTo(formatSearch());
}).on('click', '.remove_facet', function(e){
	var filter_type = $(this).attr('filter_type');
	if($(this).attr('filter_type')=='subject_vocab_uri'){
		delete(searchData['subject_vocab_uri_display']);
	}
	delete(searchData[filter_type]);
	searchData['p']=1;
	changeHashTo(formatSearch());
}).on('click', '.toggle_sidebar', function(e){
	e.preventDefault();
	$('.sidebar').toggle();
	$('.main').toggleClass('full-width');
}).on('click', '#browse-more-subject', function(e){
	$(this).qtip({
		content: {
			text: 'Loading...',
			ajax: {
				url: base_url+'search/getsubjectfacet', 
				type: 'POST', // POST or GET
				data: {filters:searchData, subjectType:subjectType}, // Data to pass along with your request
				success: function(data, status) {
					this.set('content.text', data);
					loadSubjectBrowse(subjectType);
				}
			},
			title: {
				text: 'Subjects',
				button: true
			}
		},
		
		show:{solo:true,ready:true,event:'click'},
	    hide:false,
	    position:{my:'top right', at:'bottom left'},
	    style: {
	        classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',
	        width: 400,
	    }
	});
}).on('change', '#subjectfacet-select', function(){
	subjectType = $(this).val();
	loadSubjectBrowse($(this).val());
}).on('click', '.vocab_tree_standard ins', function(){
	$(this).siblings('ul').toggle();
	$(this).parent().toggleClass('tree_closed').toggleClass('tree_open');
}).on('click', '.tree_leaf', function(){
	if(!$(this).hasClass('tree_empty')){
		searchData['s_subject_value_resolved']=$(this).attr('vocab_value');
		searchData['p'] = 1;
		changeHashTo(formatSearch());
	}
});

function loadSubjectBrowse(val){
	if(val!='anzsrc-for'){
		$('#subjectfacet').html('Loading...');
		$.ajax({
			url:base_url+'search/getAllSubjects/'+val, 
			type: 'POST',
			data: {filters:searchData},
			success: function(data){
				$('#subjectfacet').html(data);
				$('#subjectfacet ul.vocab_tree_standard ul').each(function(){
					$('li:gt(10)', this).hide();
				});
				$('#subjectfacet ul.vocab_tree_standard ul').append('<li class="show_next_10" current="10">Show More..</li>');
				$('.show_next_10').click(function(){
					var current = $(this).attr('current');
					var next = parseInt(current) + 10;
					var theList = $(this).parent();
					$('li:lt('+next+')', theList).show();
					$(this).attr('current', next);
				});
			}
		});
	}else{
		$('#subjectfacet div').remove();
		$('#subjectfacet').append($('<div/>'));
		var sqc = '';
		if(searchData['q']) sqc += searchData['q'];
		if(searchData['tab']) sqc += '+class:("'+searchData['tab']+'")';
		if(searchData['group']) sqc += '+group:("'+searchData['group']+'")';
		if(searchData['type']) sqc += '+type:("'+searchData['type']+'")';
		sqc += '&defType=edismax';
		$('#subjectfacet div').vocab_widget({mode:'tree', repository:'anzsrc-for', sqc:sqc})
		.on('treeselect.vocab.ands', function(event) {
			var target = $(event.target);
			var data = target.data('vocab');
			searchData['subject_vocab_uri'] = encodeURIComponent(data.about);
			searchData['subject_vocab_uri_display'] = data.label;
			searchData['p'] = 1;
			changeHashTo(formatSearch());
	    });
	}
}


function initSearchPage(){
	getTopLevelFacet();

	$('.tabs').show();

	//see if we need to init the map
	if(searchData['map']){
		$('#searchmap').show();
		$('.container').css({margin:'0',width:'100%',padding:'0'});
		$('.main').css({width:'100%',padding:'0'});
		$('.sidebar').addClass('mapmode_sidebar');
		$('.facet_class').show();
		$('#search-result, .pagination, .page_title, .tabs').hide();
		 processPolygons();
		 resetZoom();
		 $('.post').hover(function(){
		 	//console.log($(this).attr('ro_id'));
		 	clearPolygons();
			polygonsArray[$(this).attr('ro_id')].setMap(map);
		 },function(){
		 	clearPolygons();
		 });
	}else{
		$('#searchmap').hide();
		$('.container').css({margin:'0 auto',width:'922px',padding:'10px 0 0 0'});
		$('.main').css({width:'633px',padding:'20px 0 0 0'});
		$('.facet_class').hide();
		$('.sidebar').removeClass('mapmode_sidebar');
		$('#search-result, .pagination, .page_title, .tabs').show();
	}


	$('#search_map_toggle').unbind('click');
	$('#search_map_toggle').click(function(e){
		e.preventDefault();
		if(searchData['map']){
			//already map, hide map
			$('#searchmap').hide();
			delete searchData['map'];
			delete searchData['spatial'];
			if(searchBox!=null){
				searchBox.setMap(null);
				searchBox = null;
			}
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
		q = decodeURIComponent(q);
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
				inputs += this+' ';//anything else is normal
			}
		});
		$('.adv_input').val($.trim(inputs));
		$('.adv_not').each(function(e,k){//populate the nots
			$(this).val(nots[e]);
		});
	}

	if(searchData['temporal']){
		$('#rst_range').attr('checked', 'checked');
		$('#slider').show();
		$('#slider').editRangeSlider('resize');
		var temporal = searchData['temporal'].split('-');
		$("#slider").editRangeSlider("min",parseInt(temporal[0]));
		$("#slider").editRangeSlider("max",parseInt(temporal[1]));
	}	

	$('#search_box').unbind('keypress').keypress(function(e){
		if(e.which==13){//press enter
			searchData['q']=$(this).val();
			changeHashTo(formatSearch());
		}
	});

	$('.excerpt').each(function(){
		// This will unencode the encoded entities, but also hide any random HTML'ed elements
		$(this).html(htmlDecode(htmlDecode(htmlDecode($(this).html()))));
		$(this).html($(this).directText());

		var thecontent = $(this).html();
		var newContent = ellipsis(thecontent, 200);
		if(thecontent!=newContent) 
		{ 
			newContent = '<div class="hide" fullExcerpt="true">'+thecontent+'</div>' + newContent + '';
		}

		$(this).html(newContent);
		}
	);

	$('.showmore_excerpt').click(function(){	
		$(this).parent().html($(this).parent().children(0).html());
	});

	
}

// decode htmlentities()
function htmlDecode(value) {
   return (typeof value === 'undefined') ? '' : $('<div/>').html(value).text();
}

function recurseGetText() { 
	if (this.nodeType == 3)
	{
		return this.nodeValue;
	}
	else
	{
		if (typeof $(this).contents == 'function' && $(this).contents().length > 0)
		{
			return $(this).contents().map(recurseGetText).get().join();
		}
	}
	return this.nodeType == 3 ? this.nodeValue : undefined;
}

// get any text inside the element $(this).directText()
$.fn.directText=function(delim) {
  if (!delim) delim = '';
  return this.contents().map(recurseGetText).get().join(delim);
};


function getTopLevelFacet(){
	$.ajax({
		url:base_url+'search/getTopLevel', 
		type: 'POST',
		data: {filters:searchData},
		success: function(data){
			var template = $('#top-level-template').html();
			var output = Mustache.render(template, data);
			$('#top_concepts').html(output);
		},
		complete:function(data){
			postSearch();
		}
	});
}

function postSearch(){
	$('.sidebar ul').each(function(){
		if($('li', this).length>6){
			$('li:gt(5)', this).hide();
			$(this).append('<li><a href="javascript:;" class="show-all-facet">Show More...</a></li>');
			$('.show-all-facet').click(function(){
				$(this).parent().siblings().show();
				$(this).parent().remove();
			});
		}
	});

	var selecting_facets = ['group','type','license_class'];
	$.each(selecting_facets,function(){
		if(searchData[this]){
			var facet_value = decodeURIComponent(searchData[this]);
			var facet_div = $('div.facet_'+this);
			$('.filter[filter_value="'+facet_value+'"]',facet_div).addClass('remove_facet').before('<img class="remove_facet" filter_type="'+this+'" src="'+base_url+'assets/core/images/delete.png"/>');
		}
	});

	if(searchData['s_subject_value_resolved']){
		var html = '<li><img src="'+base_url+'assets/core/images/delete.png" filter_type="s_subject_value_resolved" class="remove_facet"/><a href="javascript:;" class="filter remove_facet" filter_type="s_subject_value_resolved">'+searchData['s_subject_value_resolved']+'</a></li>';
		$('.facet_subjects ul').prepend(html);
	}

	if(searchData['subject_vocab_uri']){
		var html = '<li><img src="'+base_url+'assets/core/images/delete.png" filter_type="subject_vocab_uri" class="remove_facet"/><a href="javascript:;" class="filter remove_facet" filter_type="subject_vocab_uri">'+searchData['subject_vocab_uri_display']+'</a></li>';
		$('.facet_subjects ul').prepend(html);
	}

	if(searchData['temporal']){
		var temporal = searchData['temporal'].split('-');
		var html = '<li><img src="'+base_url+'assets/core/images/delete.png" filter_type="temporal" class="remove_facet"/><a href="javascript:;" class="filter remove_facet" filter_type="temporal">'+temporal[0]+'-'+temporal[1]+'</a></li>';
		$('.facet_subjects ul').prepend(html);
	}
}


function ellipsis (string, length)
{
	if (string.length <= length){
		return string;
	}else{
		var trimmedString = string.substr(0, length-3);
		trimmedString = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) + '&hellip;';
		return trimmedString + '<span class="showmore_excerpt"><br /><a href="javascript:void(0);">More &hellip;</a></span>';
	}
}

function SidebarToggle(controlDiv, map) {
	// Set CSS styles for the DIV containing the control
	// Setting padding to 5 px will offset the control
	// from the edge of the map.
	controlDiv.style.padding = '5px';

	// Set CSS for the control border.
	var controlUI = document.createElement('div');
	controlUI.style.backgroundColor = 'white';
	controlUI.style.borderStyle = 'solid';
	controlUI.style.borderWidth = '2px';
	controlUI.style.cursor = 'pointer';
	controlUI.style.textAlign = 'center';
	controlUI.title = 'Click to set the map to Home';
	controlDiv.appendChild(controlUI);

	// Set CSS for the control interior.
	var controlText = document.createElement('div');
	controlText.style.fontFamily = 'Arial,sans-serif';
	controlText.style.fontSize = '12px';
	controlText.style.paddingLeft = '4px';
	controlText.style.paddingRight = '4px';
	controlText.innerHTML = '<strong>Show/Hide Facet</strong>';
	controlUI.appendChild(controlText);

	// Setup the click event listeners: simply set the map to Chicago.
	google.maps.event.addDomListener(controlUI, 'click', function() {
		$('.sidebar').toggle();
	});
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

    var homeControlDiv = document.createElement('div');
  	var homeControl = new SidebarToggle(homeControlDiv, map);

  	homeControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);

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
          ],
        },
        rectangleOptions:{
        	fillColor: '#FF0000'
        }
      });
      drawingManager.setMap(map);
      drawingManager.setDrawingMode(null);
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
     google.maps.event.addListener(map, 'click', function(e) {
     	if(infowindow)
     	{
     		infowindow.close();
 		}
 	});
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
		//check if first point is the same as last... if not copy fist coords to the end.
		//if(coordsStr.length > 1 && coordsStr[0] !== coordsStr[i-1])
		//{
		//	coordsPair = coordsStr[0].split(",");
		//	coords[i] = new google.maps.LatLng(coordsPair[1],coordsPair[0]);
		//}
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
			infowindow.setOptions({maxWidth:500});
			infowindow.open(map);
			$('.ro_preview_header').unbind('click').click(function(){
				$(this).next('.ro_preview_description').slideToggle(200, function(){
					// console.log(infowindow);
					google.maps.event.trigger(infowindow, 'content_changed');
				});
			});
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
		query_string += i + '=' + encodeURIComponent(v) + '/';
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

  var identical = checkIdenticalMarkers(this.cluster_);
  if (!identical){
    // Zoom into the cluster.
    this.map_.fitBounds(this.cluster_.getBounds());
  }
  else{
	showPreviewWindowConent(this.cluster_);
  }

};

function checkIdenticalMarkers(cluster){
	var identical = true;
	$.each(cluster.markers_, function(){
		if(!this.position.equals(cluster.markers_[0].position)) {
			identical = false;
			return identical;
		}
	});
	return identical;
}
