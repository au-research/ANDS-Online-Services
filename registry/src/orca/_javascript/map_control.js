/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/

// Globals and constants
var MCT_TOOL_POINT_PREFIX  = 'mct_tool_point';
var MCT_TOOL_REGION_PREFIX = 'mct_tool_region';
var MCT_TOOL_TEXT_PREFIX   = 'mct_tool_text';
var MCT_TOOL_SEARCH_PREFIX = 'mct_tool_search';

var MCT_CONTROL_ID_PREFIX                  = 'mct_control_';
var MCT_CANVAS_ID_PREFIX                   = 'mct_canvas_';
var MCT_ADDRESS_SEARCH_DIALOG_ID_PREFIX    = 'mct_search_';
var MCT_ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX = 'mct_search_textfield';
var MCT_ADDRESS_SEARCH_RESULTS_ID_PREFIX   = 'mct_search_results';
var MCT_LONLAT_DIALOG_ID_PREFIX            = 'mct_lonlat_';
var MCT_LONLAT_TEXTAREA_ID_PREFIX          = 'mct_lonlat_textarea';

var MCT_POLY_COLOUR      = '#008dce';
var MCT_EDIT_POLY_COLOUR = '#ff5b00';
var MCT_OPEN_POLY_COLOUR = '#ff0000';

var mctImagesRootPath = null;
var mctInputFields = new Array();
var mctMaps = new Array();
var mctTools = new Array();
var mctOriginalInputFieldValues = new Array();

var mctPushpin = null;
var mctEditPushpin = null;
var mctShadow = null;

var mctMarkers = new Array();
var mctMarkerListeners = new Array();
var mctPolygons = new Array();
var mctTempPolygons = new Array();
var mctGeocoder = null;
var mctDrawingManagers = new Array(); // probably not useed
var mctErrorMessage = "";

var mctCurrentMapId = "";

function mctInit(imagePath)
{
	mctImagesRootPath = imagePath;	
}

function mctGetErrorMessage()
{
	return mctErrorMessage;
}

function mctSetErrorMessage(message)
{
	mctErrorMessage = message;
	//console.log(mctErrorMessage);
}

function mctGetOriginalInputFieldValue(controlDivId)
{
	return mctOriginalInputFieldValues[controlDivId];
}

function mctSetOriginalInputFieldValue(controlDivId, value)
{
	mctOriginalInputFieldValues[controlDivId] = value;
}

function mctSetInputFieldValue(controlDivId, newValue)
{
	//console.log("mctSetInputFieldValue-------- controlDivId: "+ controlDivId + " newValue: " + newValue);
	if( mctInputFields[controlDivId] )
	{
		mctInputFields[controlDivId].value = newValue;	
	}
}

function mctGetInputFieldValue(controlDivId)
{
	var value = '';
	if( mctInputFields[controlDivId] )
	{
		value = mctInputFields[controlDivId].value;	
	}
	
	return value;
}

function mctGetOriginalInputFieldValue(controlDivId)
{
	return mctOriginalInputFieldValues[controlDivId];
}

function mctSetMapControl(mapInputFieldId)
{
	// Because IE6 and 7 have problems if with the map if we put it on the page before
	// the required bits of the DOM have loaded we need to delay loading the maps.
	// We do this by appending the mctGetMapControl calls to the onload handler.
	// This window.onload stuff is not defined in a public standard but Safari, Firefox, and IE
	// all exhibit the same behaviour.
	
	// Build the map control div.
	var controlDivId = MCT_CONTROL_ID_PREFIX + mapInputFieldId;
	document.write('<div id="' + controlDivId + '" class="mct_control"></div>');
	
	// Add this map to the onload queue.
	var onloadQueue = window.onload;
	if( typeof onloadQueue == 'function' )
	{
		window.onload = function()
		{
			onloadQueue();
			mctGetMapControl(mapInputFieldId);
		};
	}
	else
	{
		window.onload = function()
		{
			mctGetMapControl(mapInputFieldId);
		};		
	}
}

function mctSetNoDelayMapControl(mapInputFieldId)
{
	// Because IE6 and 7 have problems if with the map if we put it on the page before
	// the required bits of the DOM have loaded we need to delay loading the maps.
	// We do this by appending the mctGetMapControl calls to the onload handler.
	// This window.onload stuff is not defined in a public standard but Safari, Firefox, and IE
	// all exhibit the same behaviour.
	
	// Build the map control div.
	var controlDivId = MCT_CONTROL_ID_PREFIX + mapInputFieldId;
	mctGetMapControl(mapInputFieldId);

	
}


function mctGetMapControl(mapInputFieldId)
{
	var map = null;
	var mapInputField = getObject(mapInputFieldId);
	
	var controlDivId = MCT_CONTROL_ID_PREFIX + mapInputFieldId;
	var controlDiv = getObject(controlDivId);
	
	//try
	//{
		if( mapInputField)
		{		
			// Set the reference to the input field.
			mctInputFields[controlDivId] = mapInputField;
			//console.log("mapInputFieldId: "+ mapInputFieldId);
			// Set up for reset map.
			mctSetOriginalInputFieldValue(controlDivId, mapInputField.value);
			
			// Initialise this instance.
			var mapCanvasId = MCT_CANVAS_ID_PREFIX + controlDivId;
			
			var mapToolBarHTML       = mctGetToolBarHTML(controlDivId);
			var mapAddressSearchHTML = mctGetAddressSearchDialogHTML(controlDivId);
			var mapLonLatDialogHTML  = mctGetLonLatDialogHTML(controlDivId);
			var mapCanvasHTML        = '<div class="mct_canvas" id="' + mapCanvasId + '"></div>';
					
			controlDiv.innerHTML = mapToolBarHTML + mapAddressSearchHTML + mapLonLatDialogHTML + mapCanvasHTML;	
			
			// Build any icons we might need.
			if( !mctEditPushpin)
			{
				mctEditPushpin = new google.maps.MarkerImage(mctImagesRootPath + 'orange-pushpin.png',
					      new google.maps.Size(24,32),
					      new google.maps.Point(0,0),
					      new google.maps.Point(4,32));
			}
			
			if( !mctPushpin)
			{
				mctPushpin = new google.maps.MarkerImage(mctImagesRootPath + 'blue-pushpin.png',
					      new google.maps.Size(24,32),
					      new google.maps.Point(0,0),
					      new google.maps.Point(4,32));			
			}

		    mctShadow = new google.maps.MarkerImage(mctImagesRootPath + 'pushpin-shadow.png',
		    		new google.maps.Size(37,32),
				      new google.maps.Point(0,0),
				      new google.maps.Point(4,32));

			
			// Initialise this maps marker and region references.
			mctMarkers[controlDivId] = null;
			mctMarkerListeners[controlDivId] = null;
			mctPolygons[controlDivId] = null;
			mctTempPolygons[controlDivId] = null;
		
			var mapCanvas = getObject(mapCanvasId);				
			var myOptions = {
				      zoom: 3,disableDefaultUI: true,center:new google.maps.LatLng(-27, 133),panControl: true,zoomControl: true,mapTypeControl: true,scaleControl: true,
				      streetViewControl: false,overviewMapControl: true,mapTypeId: google.maps.MapTypeId.TERRAIN
				    };
			map = new google.maps.Map(mapCanvas,myOptions);
			var bounds = new google.maps.LatLngBounds();

			mctMaps[controlDivId] = map;
			// Set the map from any existing values when it's completed loading.
			//google.maps.event.addListener(map, "load", function()
			//{
			mctSetMapFromInputField(controlDivId, true);
			//});
			// Set the default cursors.
			map.setOptions({draggableCursor:"default"});
			map.setOptions({raggingCursor:"move"});
			
			// Get a geocoder ready for the search.
			mctGeocoder = new google.maps.Geocoder();
		}
	//}
	//catch(e)
	//{
		// The Google Maps API probably didn't load. Not much we can do.
	//	if( controlDiv )
	//	{
	//		controlDiv.className = "mct_loaderror";
	//		controlDiv.innerHTML = 'The mapping tool has failed to load. Your browser must allow non-HTTPS content to load on this page in order to use this tool.';
	//	}
	//}
}

function mctGetToolBarHTML(controlDivId)
{
	var id = null;
	html = '<div class="mct_toolbar">';
	
	// point
	id = MCT_TOOL_POINT_PREFIX + controlDivId;
	mctTools[id] = false;
	html += '<span class="mct_tool" id="' + id + '" onclick="mctStartPoint(this, \'' + controlDivId + '\');" title="Mark a point on the map">point</span>';
	
	// region
	id = MCT_TOOL_REGION_PREFIX + controlDivId;
	mctTools[id] = false;
	html += '<span class="mct_tool" id="' + id + '" onclick="mctStartRegion(this, \'' + controlDivId + '\');" title="Draw a polygon on the map to mark a region">region</span>';
	
	// search...
	id = MCT_TOOL_SEARCH_PREFIX + controlDivId;
	mctTools[id] = false;
	html += '<span class="mct_tool" id="' + id + '" onclick="mctShowAddressSearchDialog(this, \'' + controlDivId + '\')" title="Search for a place or region to mark on the map">search...</span>';
	
	// coordinates...
	id = MCT_TOOL_TEXT_PREFIX + controlDivId;
	mctTools[id] = false;
	html += '<span class="mct_tool" id="' + id + '" onclick="mctShowLonLatDialog(this, \'' + controlDivId + '\')" title="Enter longitude,latitude pairs to mark a point or a region on the map">coordinates...</span>';

	// clear
	html += '<span class="mct_special_tool" onclick="mctEmptyMap(\'' + controlDivId + '\')" title="Clear the marker/region data">clear</span>';
	
	// reset
	if( mctGetOriginalInputFieldValue(controlDivId) )
	{
		html += '<span class="mct_special_tool" onclick="mctResetMap(\'' + controlDivId + '\')" title="Reset the map to its initial state">reset</span>';	
	}
	
	html += '</div>';

	return html;
}

function mctSetToolActive(tool, active)
{
	mctTools[tool.id] = active;
	if( active )
	{
		tool.className = 'mct_tool_active';
	}
	else
	{
		tool.className = 'mct_tool';
	}
}

function mctGetToolActive(tool)
{
	return mctTools[tool.id];
}

function mctResetTools(controlDivId)
{
	var id = null;
	var object = null;

	// =======================================================================
	// TIDY UP THE TOOLS
	// =======================================================================
	// POINT
	// -----------------------------------------------------------------------
	// Set the tool state.
	id = MCT_TOOL_POINT_PREFIX + controlDivId;
	object = getObject(id);
	mctSetToolActive(object, false);
	
	// REGION
	// -----------------------------------------------------------------------
	// Set the tool state.
	id = MCT_TOOL_REGION_PREFIX + controlDivId;
	object = getObject(id);
	mctSetToolActive(object, false);
	
	// TEXT
	// -----------------------------------------------------------------------
	// Set the tool state.
	id = MCT_TOOL_TEXT_PREFIX + controlDivId;
	object = getObject(id);
	mctSetToolActive(object, false);
	
	// Hide the dialog.
	id = MCT_LONLAT_DIALOG_ID_PREFIX + controlDivId;
	object = getObject(id);
	object.style.display = "none";
	
	// Clear the text.
	id = MCT_LONLAT_TEXTAREA_ID_PREFIX + controlDivId;
	object = getObject(id);
	object.value = '';		
	
	// SEARCH
	// -----------------------------------------------------------------------
	// Set the tool state.
	id = MCT_TOOL_SEARCH_PREFIX + controlDivId;
	object = getObject(id);
	mctSetToolActive(object, false);
	
	// Hide the dialog leaving it how it is.
	id = MCT_ADDRESS_SEARCH_DIALOG_ID_PREFIX + controlDivId;
	object = getObject(id);
	object.style.display = "none";
	
	// =======================================================================
	// TIDY UP THE MAP
	// =======================================================================
	// Set the cursors back to the default settings.
	mctMaps[controlDivId].setOptions({draggableCursor:"default"});
	mctMaps[controlDivId].setOptions({draggingCursor:"move"});
	
	// Remove any listeners from the map.
	if( (markerListener = mctMarkerListeners[controlDivId]) != null )
	{
		google.maps.event.removeListener(markerListener);
	}
	
	if( (drawingManager = mctDrawingManagers[controlDivId]) != null )
	{
		drawingManager.setMap(null);
	}
	
	// Redraw the map.
	mctSetMapFromInputField(controlDivId, false);
}

function mctResetMap(controlDivId)
{
	mctSetInputFieldValue(controlDivId, mctGetOriginalInputFieldValue(controlDivId));
	mctResetTools(controlDivId);
	mctCentreMap(controlDivId);
}

function mctEmptyMap(controlDivId)
{
	mctSetInputFieldValue(controlDivId, '');
	mctResetTools(controlDivId);
	mctCentreMap(controlDivId);
}

function mctTidyLonLatText(lonlatText)
{
	var cleanLonLatText = lonlatText;
	
	if( cleanLonLatText != "" )
	{
		// Remove white space from between latitude and longitude.
		cleanLonLatText = cleanLonLatText.replace(new RegExp('\\s*,\\s*', "g"), ',');
		// Convert all white space between pairs to spaces.
		cleanLonLatText = cleanLonLatText.replace(new RegExp('\\s+',"g"),' ');
		// Remove any leading and/or trailing spaces.
		cleanLonLatText = cleanLonLatText.replace(new RegExp('^ '),'');
		cleanLonLatText = cleanLonLatText.replace(new RegExp(' $'),'');	
	}
	
	return cleanLonLatText;
}

function mctGetCoordsFromInputField(controlDivId)
{
	var coords = new Array();
	
	var lonlatText = mctTidyLonLatText(mctGetInputFieldValue(controlDivId));
	//console.log("mctGetCoordsFromInputField lonlatText:" + lonlatText + "mctValidateLonLatText: " + mctValidateLonLatText(lonlatText));
	if( lonlatText != "" && mctValidateLonLatText(lonlatText))
	{
		var coordsStr = lonlatText.split(' ');
   		for( var i=0; i < coordsStr.length; i++ )
		{
			// Fill the array with GLatLngs.

			coordsPair = coordsStr[i].split(",");
			coords[i] = new google.maps.LatLng(coordsPair[1],coordsPair[0]);
		}	
	}
		
	return coords;
}

function mctValidateLonLatText(lonlatText)
{
	var valid = true;
	
	if( lonlatText != "" )
	{
		var coords = lonlatText.split(' ');
		var lat = null;
		var lon = null;
		var coordsPair = null;
   		
		// Test for a two point line.
		if( coords.length == 2 )
		{
			mctSetErrorMessage("The coordinates don't represent a point or a region.");
			valid = false;	
		}

		for( var i=0; i < coords.length && valid; i++ )
		{
			// Get the lat and lon.
			coordsPair = coords[i].split(",");
			lat = coordsPair[1];
			lon = coordsPair[0];
			
			// Test for numbers.
			if( isNaN(lat) || isNaN(lon) )
			{
				mctSetErrorMessage('Some coordinates are not numbers.');
				valid = false;
				break;
			}
			// Test the limits.
			if( Math.abs(lat) > 90 || Math.abs(lon) > 180 )
			{
				//console.log("lat: " + lat + " " + Math.abs(lat) + " lon: " + lon + " " + Math.abs(lon))
				mctSetErrorMessage('Some coordinates have invalid values.');
				valid = false;
				break;
			}
		
			// Test for an open region.
			if( i == coords.length-1 )
			{
				if( coords[0] != coords[i] )
				{
					mctSetErrorMessage("The coordinates don't represent a point or a region. To define a region the last point needs to be the same as the first.");
					valid = false;	
				}
			}	
		}
	}
	return valid;
}

function mctSetMapFromInputField(controlDivId, centred)
{
	// Clear the map.
	mctClearMap(controlDivId);
	// Redraw the map with values from the input field.
	var coords = mctGetCoordsFromInputField(controlDivId);
	//console.log("mctSetMapFromInputField: " + coords);
	if( coords.length == 1 )
	{
		mctCreateMarker(coords[0], controlDivId , false);	
	}	
	else if( coords.length > 2 )
	{  
        mctCreatePolygon(coords, controlDivId, false);
	}		
	if( centred )
	{
		mctCentreMap(controlDivId);
	}
}

function mctClearMap(controlDivId)
{
	// Remove any marker from the map.
	mctRemoveMarker(controlDivId);
	
	// Remove any polygon from the map.
	mctRemovePolygon(controlDivId);
	
	if( (drawingManager = mctDrawingManagers[controlDivId]) != null )
	{
		drawingManager.setMap(null);
	}
}

function mctCentreMap(controlDivId)
{
	//Check for a polygon to centre on.
	if( (polygon = mctPolygons[controlDivId]) != null )
	{
		var bounds = new google.maps.LatLngBounds();
		var i;

		// The Bermuda Triangle
		var polygonCoords = polygon.getPath().getArray();
		//console.log(polygonCoords);
		for (i = 0; i < polygonCoords.length; i++) {
			//console.log(polygonCoords[i]);
		  bounds.extend(polygonCoords[i]);
		}
        //resetZoom();//google map api bug fix
		mctMaps[controlDivId].fitBounds(bounds);
	}
	
	// Check for a marker to centre on.
	if( (marker = mctMarkers[controlDivId]) != null )
	{
		mctMaps[controlDivId].setCenter(marker.getPosition());
	}
}


// ===========================================================================
// POINT
// ===========================================================================
function mctStartPoint(tool, controlDivId)
{
	var active = mctGetToolActive(tool);
	mctResetTools(controlDivId);
	
	// Set the cursor for dropping a marker.
	mctMaps[controlDivId].setOptions({draggableCursor:"crosshair"});
	
	if( !active )
	{
		mctSetToolActive(tool, true);
		
		// Get coords from the the input field.
		var coords = mctGetCoordsFromInputField(controlDivId);
		
		// Check to see if it represents a point.
		if( coords.length == 1 )
		{
		    // Show an editable marker on the map.
			mctCreateMarker(coords[0], controlDivId, true);
		}
		
		// Add a listener with an anonymous function for dropping a new marker on the map.
		
		mctMarkerListeners[controlDivId] = google.maps.event.addListener(mctMaps[controlDivId], "click", function(e) 
		{
		    if( e.latLng) 
		    {
		    	// Set the input field and reset the control.
				mctSetInputFieldValue(controlDivId, e.latLng.lng().toFixed(6) + "," + e.latLng.lat().toFixed(6)); 
				mctResetTools(controlDivId);
		    }
   		});		
   		// Center the map on this marker.
		mctCentreMap(controlDivId);		
	}	
}

function mctCreateMarker(latlng, controlDivId, editable)
{
	// Remove any previous markers or regions.
	mctClearMap(controlDivId);
	//console.log("creating Marker");
	var marker = null;
    if( editable )
	{
		// Draw a new editable marker. 
	   // marker = new GMarker(latlng, {icon: mctEditPushpin, draggable: true});
	    marker = new google.maps.Marker({
	          position: latlng,
	          map: mctMaps[controlDivId],
	          icon : mctEditPushpin,
	          draggable : true
	        });
	    mctMarkers[controlDivId] = marker;	    
		// Add a listener with an anonymous function for updating after dragging an editable marker.
	    google.maps.event.addListener(marker, "dragend", function() 
		{
			// Set the input field and reset the control.
			var latlng = marker.getPosition();
			mctSetInputFieldValue(controlDivId, latlng.lng().toFixed(6) + "," + latlng.lat().toFixed(6));
	    });
    }
    else
	{
		// Draw a new marker. 
    	marker = new google.maps.Marker({
	          position: latlng,
	          map: mctMaps[controlDivId],
	          icon : mctPushpin 
	        });
	    mctMarkers[controlDivId] = marker;
    }

	// Set the input field value to the marker location.
	mctSetInputFieldValue(controlDivId, latlng.lng().toFixed(6) + "," + latlng.lat().toFixed(6)); 
}

function mctRemoveMarker(controlDivId)
{
	// Check for a marker.
    if( (marker = mctMarkers[controlDivId]) != null )
	{	
		// Remove the marker overlay.
		marker.setMap(null);
		mctMarkers[controlDivId] = null;
	}
}

// ===========================================================================
// REGION
// ===========================================================================
function mctStartRegion(tool, controlDivId)
{
	var active = mctGetToolActive(tool);
	mctCurrentMapId = controlDivId;
	mctResetTools(controlDivId);	
	if( !active )
	{
		mctSetToolActive(tool, true);
		
		// Get coords from the input field value.
		var coords = mctGetCoordsFromInputField(controlDivId);
		
		// Check to see if it represents a region.
		if( coords.length > 2 )
		{  
			mctCreatePolygon(coords, controlDivId, true);
			mctMarkerListeners[controlDivId] = google.maps.event.addListener(mctMaps[controlDivId], "click", function(e) 
					{
					    if( e.latLng) 
					    {
					    	mctClearMap(mctCurrentMapId);
					    	mctBeginDrawing(mctCurrentMapId, e.latLng);	
					    }
			   		});	
			mctCentreMap(controlDivId);	
		}
		else
			{
			mctBeginDrawing(controlDivId,'');
			}


	}	
}

function mctBeginDrawing(controlDivId , latLng)
{
	// Remove any existing temporary polygon.

	mctCurrentMapId = controlDivId;
	
	if(mctMarkerListeners[controlDivId] != null)
		{
		google.maps.event.removeListener(mctMarkerListeners[controlDivId]);
		}
	
	var mctDrawingManager = new google.maps.drawing.DrawingManager({
		  drawingMode: google.maps.drawing.OverlayType.POLYGON,
		  drawingControl: false,
		  polygonOptions: {
		    fillColor: MCT_OPEN_POLY_COLOUR,
		    paths : [latLng],
		    fillOpacity: 0.2,
		    strokeColor: MCT_OPEN_POLY_COLOUR,
		    strokeWeight: 2,
		    clickable: true,
		    zIndex: 1,
		    editable: true
		  }
		});
	mctDrawingManagers[controlDivId] = mctDrawingManager;

	mctDrawingManager.setMap(mctMaps[controlDivId]);
	
	google.maps.event.addListener(mctDrawingManager, 'polygoncomplete', function(polygon) {
		    mctSavePolygonString(polygon.getPath(), mctCurrentMapId);
		    polygon.setMap(null);
		    mctDrawingManager.setMap(null);
		    mctDrawingManagers[mctCurrentMapId] = null;
		    mctResetTools(mctCurrentMapId);
		});
	
}

function mctCreatePolygon(coords, controlDivId, editable)
{
	// Remove any previous markers or regions.
	mctClearMap(controlDivId);
	mctCurrentMapId = controlDivId;
	//console.log("creating Polygon: " + coords);
	var polygon = null;
	if(editable)
	{
		polygon = new google.maps.Polygon({
		    paths: coords,
		    map : mctMaps[controlDivId],
		    strokeColor: MCT_OPEN_POLY_COLOUR,
		    strokeOpacity: 0.7,
		    strokeWeight: 2,
		    fillColor: MCT_OPEN_POLY_COLOUR,
		    fillOpacity: 0.2,
		    editable : true,
		    clickable : true
		  });

		google.maps.event.addListener(polygon, 'click', function(e) {
		    mctSavePolygonString(polygon.getPath(), mctCurrentMapId);
		});
		
		google.maps.event.addListener(polygon, 'mouseup', function(e) {
		    mctSavePolygonString(polygon.getPath(), mctCurrentMapId);
		});
		
		google.maps.event.addListener(polygon, 'mouseout', function(e) {
		    mctSavePolygonString(polygon.getPath(), mctCurrentMapId);
		});
	}
	else
	{   
		// Create a non-editable, non-clickable region.
		polygon = new google.maps.Polygon({
			    paths: coords,
			    map : mctMaps[controlDivId],
			    strokeColor: MCT_POLY_COLOUR,
			    strokeOpacity: 0.7,
			    strokeWeight: 2,
			    fillColor: MCT_POLY_COLOUR,
			    fillOpacity: 0.2,
			    editable : false
			  });
	}
	mctPolygons[controlDivId] = polygon;
}

function mctSavePolygonString(path, controlDivId)
{	
	// Get the coordinates of the polygon vertices.

	var coords = path.getArray();
	//console.log(coords);
	var polyString = "";
	for( var i=0; i < coords.length; i++ )
	{
 		var pLat = coords[i].lat();
 		var pLng = coords[i].lng();
 		if(i == 0)
 		{
			polyString =  pLng.toFixed(6) + "," + pLat.toFixed(6);
 		}
		else
		{
			polyString = polyString + " " + pLng.toFixed(6) + "," + pLat.toFixed(6);
		}           	
	} 
	polyString = polyString + " " + coords[0].lng().toFixed(6) + "," + coords[0].lat().toFixed(6);
	// Set the input field value.
	//console.log("polyString: " + polyString);
	mctSetInputFieldValue(controlDivId, polyString);
}

function mctRemovePolygon(controlDivId)
{
    // Check that we have a reference to the polygon.
	if( (polygon = mctPolygons[controlDivId]) != null )
	{
		// Disable editing (even though it is probably already disabled) to enable removal to work.
		polygon.setMap(null);
		// Remove the reference.
		mctPolygons[controlDivId] = null;
	}
}


// ===========================================================================
// SEARCH
// ===========================================================================
 function mctGetAddressSearchDialogHTML(controlDivId)
{
	var mapDialogId = MCT_ADDRESS_SEARCH_DIALOG_ID_PREFIX + controlDivId;
	html = '<div class="mct_dialog_container" id="' + mapDialogId + '">';
	html += '<img class="mct_dialog_back" src="' + mctImagesRootPath + 'mct_dialog_bg.png" alt="" />';
	html += '<div class="mct_dialog_outer">';
	html += '<div class="mct_dialog_inner">';
	html += '<div class="mct_dialog_content" style="overflow: hidden;">';
	
	var searchResultsDivId = MCT_ADDRESS_SEARCH_RESULTS_ID_PREFIX + controlDivId;
	var searchResultsTextfieldId = MCT_ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX + controlDivId;
	html += '<div class="mct_dialog_text"><i>Search for a region or place to mark on the map</i></div>';
	html += '<div class="mct_dialog_text"><input type="text" id="' + searchResultsTextfieldId + '" style="margin: 0px 0px 0px 0px; width: 210px;" onkeypress="return mctCheckSearchEvent(event, \'' + controlDivId + '\');" />';
	html += '&nbsp;<button type="button" class="mct_button" onclick="mctDoSearch(\'' + controlDivId + '\')">search</button></div>';
	html += '<div id="' + searchResultsDivId + '" style="padding: 0px 0px 0px 8px; margin: 0px 0px 0px 0px; height: 158px; white-space: nowrap; overflow: auto;">';
	html += '</div>';
	
	html += '</div>';
	html += '<div class="mct_buttonbar" style="text-align: left;">';
	html += '<button type="button" class="mct_button" onclick="mctResetTools(\'' + controlDivId + '\')">cancel</button>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</div>';

	return html;
}
 
function mctCheckSearchEvent(event, controlDivId)
{
	result = true;
	if( event.keyCode == 13 )
	{
		result = false;
		mctDoSearch(controlDivId);
	}
	return result;
}

function mctShowAddressSearchDialog(tool, controlDivId)
{
	var active = mctGetToolActive(tool);
	mctResetTools(controlDivId);

	if( !active )
	{
		mctSetToolActive(tool, true);
		var id = MCT_ADDRESS_SEARCH_DIALOG_ID_PREFIX + controlDivId;
		var dialog = getObject(id);
		dialog.style.display = "block";	
		// Set the focus and select the text.
		var searchResultsTextfieldId = MCT_ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX + controlDivId;
		var searchResultsTextfield = getObject(searchResultsTextfieldId);
		searchResultsTextfield.focus();
		searchResultsTextfield.select();
	}
}

function mctDoSearch(controlDivId)
{
	var searchResultsTextfieldId = MCT_ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX + controlDivId;
	var searchResultsTextfield = getObject(searchResultsTextfieldId);
	var searchText = searchResultsTextfield.value;

	var searchResultsDivId = MCT_ADDRESS_SEARCH_RESULTS_ID_PREFIX + controlDivId;
	var searchResultsDiv = getObject(searchResultsDivId);	
	if( searchText != '' )
	{		
		searchResultsDiv.innerHTML = 'Searching...';
  		mctCurrentMapId = controlDivId;
  		mctGeocoder.geocode({ 'address': searchText}, function(results, status) {
  	    	mctAddAddressToMap(results, status);
  	    });
	}
}

function mctAddAddressToMap(results, status)
{
    var markerBullet = '';
	var resultText = "";
	var coordString = "";
	//console.log(status);
	if(status != google.maps.GeocoderStatus.OK) 
	{
		resultText = "No locations found";
	} 
	else 
	{	
		 // Loop through the results		
		for( var i=0; i < results.length; i++ ) 
		{
			var accuracy = results[i].geometry.location_type;
			//console.log(accuracy);
			if(accuracy == 'ROOFTOP')
			{
				coordString = results[i].geometry.location.lng().toFixed(6) +","+ results[i].geometry.location.lat().toFixed(6) ;
				markerBullet = '&#8226;';				
			}			
			else
			{
				var nE = results[i].geometry.bounds.getNorthEast();
				var sW = results[i].geometry.bounds.getSouthWest();
				coordString = nE.lng().toFixed(6) +","+ nE.lat().toFixed(6)+" ";
				coordString += sW.lng().toFixed(6) +","+ nE.lat().toFixed(6)+" ";
				coordString += sW.lng().toFixed(6) +","+ sW.lat().toFixed(6)+" ";
				coordString += nE.lng().toFixed(6) +","+ sW.lat().toFixed(6)+" ";
				coordString += nE.lng().toFixed(6) +","+ nE.lat().toFixed(6);
				markerBullet = '&#9633;';
			}
			//console.log(coordString);
			resultText  += "<div class='mct_search_result' onclick='mctSetMapFromSearchResult(\""+coordString+"\",\""+mctCurrentMapId+"\");' title=\"Set the map with this search result\">" + markerBullet + "&nbsp;"+ results[i].formatted_address+"</div>";
		}
	}
  	var searchResultsDivId = MCT_ADDRESS_SEARCH_RESULTS_ID_PREFIX + mctCurrentMapId;
	var searchResultsDiv = getObject(searchResultsDivId);	
	searchResultsDiv.innerHTML = resultText; 
}


function mctSetMapFromSearchResult(coordString, controlDivId)
{
	// Update the input field coordinates.
	//console.log("mctSetMapFromSearchResult: " + coordString + "  controlDivId: " + controlDivId);
	mctSetInputFieldValue(controlDivId, coordString); 
	// Reset the tools and redraw the map.
	mctResetTools(controlDivId);
	// Center the map on this region.
	mctCentreMap(controlDivId);	
}


// ===========================================================================
// COORDINATES
// ===========================================================================
function mctGetLonLatDialogHTML(controlDivId)
{
	var mapDialogId = MCT_LONLAT_DIALOG_ID_PREFIX + controlDivId;
	html = '<div class="mct_dialog_container" id="' + mapDialogId + '">';
	html += '<img class="mct_dialog_back" src="' + mctImagesRootPath + 'mct_dialog_bg.png" alt="" />';
	html += '<div class="mct_dialog_outer">';
	html += '<div class="mct_dialog_inner">';
	html += '<div class="mct_dialog_content">';

	var lonlatTextareaId = MCT_LONLAT_TEXTAREA_ID_PREFIX + controlDivId;
	html += '<div class="mct_dialog_text"><i>Enter space delimited longitude,latitude pairs</i></div>';
	html += '<textarea id="' + lonlatTextareaId + '" style="display: block; margin: auto; width: 278px; height: 174px;"></textarea>';

	html += '</div>';
	html += '<div class="mct_buttonbar" style="text-align: left;">';
	html += '<button type="button" class="mct_button" onclick="mctResetTools(\'' + controlDivId + '\')">cancel</button>';
	html += '&nbsp;<button type="button" class="mct_button" onclick="mctSetMapFromText(\'' + controlDivId + '\')" title="Set the map to show this point or region">set</button>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</div>';

	return html;	
}

function mctShowLonLatDialog(tool, controlDivId)
{	
	var active = mctGetToolActive(tool);
	mctResetTools(controlDivId);	

	if( !active )
	{
		mctSetToolActive(tool, true);
		var lonlatTextareaId = MCT_LONLAT_TEXTAREA_ID_PREFIX + controlDivId;
	    var lonlatTextarea = getObject(lonlatTextareaId);
		lonlatTextarea.value = mctGetInputFieldValue(controlDivId);
		
		var id = MCT_LONLAT_DIALOG_ID_PREFIX + controlDivId;
		var dialog = getObject(id);
		dialog.style.display = "block";	
		// Set the focus.
		var lonlatTextareaId = MCT_LONLAT_TEXTAREA_ID_PREFIX + controlDivId;
		var lonlatTextarea = getObject(lonlatTextareaId);
		lonlatTextarea.focus();
	}
}

function mctSetMapFromText(controlDivId)
{
	var lonlatTextareaId = MCT_LONLAT_TEXTAREA_ID_PREFIX + controlDivId;
	var lonlatTextarea = getObject(lonlatTextareaId);
	var lonlatText = mctTidyLonLatText(lonlatTextarea.value);
	
	if( mctValidateLonLatText(lonlatText) )
	{
		mctSetInputFieldValue(controlDivId, lonlatText);	
		mctResetTools(controlDivId);	
		mctSetMapFromInputField(controlDivId, true);		
	}
	else
	{
		var errors = "THERE ARE PROBLEMS WITH THE COORDINATE TEXT\n\n" + mctGetErrorMessage() + "\n\nCancel or correct the error and set.";
		alert(errors);
	}
}
