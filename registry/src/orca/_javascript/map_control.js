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

var mctMarkers = new Array();
var mctMarkerListeners = new Array();
var mctPolygons = new Array();
var mctTempPolygons = new Array();
var mctGeocoder = null;
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
	
	try
	{
		if( mapInputField && GBrowserIsCompatible() )
		{		
			// Set the reference to the input field.
			mctInputFields[controlDivId] = mapInputField;
			
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
			if( !mctEditPushpin )
			{
				mctEditPushpin = new GIcon(G_DEFAULT_ICON, mctImagesRootPath + 'orange-pushpin.png');
				mctEditPushpin.shadow = mctImagesRootPath + 'pushpin-shadow.png';
				mctEditPushpin.iconSize = new GSize(24, 32);
				mctEditPushpin.shadowSize = new GSize(32, 32);
				mctEditPushpin.iconAnchor = new GPoint(5, 32);	
			}
			
			if( !mctPushpin )
			{
				mctPushpin = new GIcon(G_DEFAULT_ICON, mctImagesRootPath + 'blue-pushpin.png');
				mctPushpin.shadow = mctImagesRootPath + 'pushpin-shadow.png';
				mctPushpin.iconSize = new GSize(24, 32);
				mctPushpin.shadowSize = new GSize(32, 32);
				mctPushpin.iconAnchor = new GPoint(5, 32);	
			}
		
			// Initialise this maps marker and region references.
			mctMarkers[controlDivId] = null;
			mctMarkerListeners[controlDivId] = null;
			mctPolygons[controlDivId] = null;
			mctTempPolygons[controlDivId] = null;
		
			var mapCanvas = getObject(mapCanvasId);	
			
			map = new GMap2(mapCanvas);
			mctMaps[controlDivId] = map;
			// Set the map from any existing values when it's completed loading.
			GEvent.addListener(map, "load", function()
			{
				mctSetMapFromInputField(controlDivId, true);
			});
				
		    map.setCenter(new GLatLng(-27, 133), 3);
		    map.addControl(new GLargeMapControl3D());
		    map.addControl(new GHierarchicalMapTypeControl());
		    map.addMapType(G_PHYSICAL_MAP);
		    map.setMapType(G_PHYSICAL_MAP);
			// Set the default cursors.
			map.getDragObject().setDraggableCursor("default");
			map.getDragObject().setDraggingCursor("move");
			
			// Get a geocoder ready for the search.
			mctGeocoder = new GClientGeocoder();
		}
	}
	catch(e)
	{
		// The Google Maps API probably didn't load. Not much we can do.
		if( controlDiv )
		{
			controlDiv.className = "mct_loaderror";
			controlDiv.innerHTML = 'The mapping tool has failed to load. Your browser must allow non-HTTPS content to load on this page in order to use this tool.';
		}
	}
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
	mctMaps[controlDivId].getDragObject().setDraggableCursor("default");
	mctMaps[controlDivId].getDragObject().setDraggingCursor("move");
	
	// Remove any listeners from the map.
	if( (markerListener = mctMarkerListeners[controlDivId]) != null )
	{
		GEvent.removeListener(markerListener);
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
	
	if( lonlatText != "" && mctValidateLonLatText(lonlatText))
	{
		var coordsStr = lonlatText.split(' ');
   		for( var i=0; i < coordsStr.length; i++ )
		{
			// Fill the array with GLatLngs.
			coordsPair = coordsStr[i].split(",");
			coords[i] = new GLatLng(coordsPair[1],coordsPair[0]);
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
	
	// Remove the Temporary polygon from the map.
	mctRemoveTempPolygon(controlDivId);
}

function mctCentreMap(controlDivId)
{
	//Check for a polygon to centre on.
	if( (polygon = mctPolygons[controlDivId]) != null )
	{
		var bounds = polygon.getBounds();
        mctMaps[controlDivId].setCenter(bounds.getCenter());
        mctMaps[controlDivId].setZoom(mctMaps[controlDivId].getBoundsZoomLevel(bounds));
	}
	
	// Check for a marker to centre on.
	if( (marker = mctMarkers[controlDivId]) != null )
	{
		mctMaps[controlDivId].setCenter(marker.getLatLng());
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
	mctMaps[controlDivId].getDragObject().setDraggableCursor("crosshair");
	
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
		mctMarkerListeners[controlDivId] = GEvent.addListener(mctMaps[controlDivId], "click", function(overlay, latlng) 
		{
		    if( latlng ) 
		    {
		    	// Set the input field and reset the control.
				mctSetInputFieldValue(controlDivId, latlng.lng().toFixed(6) + "," + latlng.lat().toFixed(6)); 
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
	
	var marker = null;
    if( editable )
	{
		// Draw a new editable marker. 
	    marker = new GMarker(latlng, {icon: mctEditPushpin, draggable: true});
	    mctMaps[controlDivId].addOverlay(marker);
	    mctMarkers[controlDivId] = marker;
	    
		// Add a listener with an anonymous function for updating after dragging an editable marker.
		GEvent.addListener(marker, "dragend", function() 
		{
			// Set the input field and reset the control.
			var latlng = marker.getLatLng();
			mctSetInputFieldValue(controlDivId, latlng.lng().toFixed(6) + "," + latlng.lat().toFixed(6));
	    });
    }
    else
	{
		// Draw a new marker. 
	    marker = new GMarker(latlng, {icon: mctPushpin, draggable: false});
	    mctMarkers[controlDivId] = marker;
	    mctMaps[controlDivId].addOverlay(marker);
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
		mctMaps[controlDivId].removeOverlay(marker);
		mctMarkers[controlDivId] = null;
	}
}

// ===========================================================================
// REGION
// ===========================================================================
function mctStartRegion(tool, controlDivId)
{
	var active = mctGetToolActive(tool);
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
		}

		mctBeginDrawing(controlDivId);	
		mctCentreMap(controlDivId);	
	}	
}

function mctBeginDrawing(controlDivId)
{
	// Remove any existing temporary polygon.
	mctRemoveTempPolygon(controlDivId);
	
	// Create a new temporary and empty polygon.
	var coords = new Array();
	var tempPolygon = new GPolygon(coords, MCT_OPEN_POLY_COLOUR, 2, 0.7, MCT_OPEN_POLY_COLOUR, 0.2);
	mctMaps[controlDivId].addOverlay(tempPolygon); 	
	mctTempPolygons[controlDivId] = tempPolygon;
	
	// Enable drawing for the new polygon.
	tempPolygon.enableDrawing();
	
	// Add a listener with an anonymous function for saving the coordinates and resetting the tools/map when the polygon is completed.
	GEvent.addListener(tempPolygon, "endline", function()
	{		
	    mctSavePolygonString(tempPolygon, controlDivId);
		mctResetTools(controlDivId);
	});	
	
	// Add a listener with an anonymous function for removing any existing markers or polygons and emptying 
	// the input field in case of the user not completing the polygon.
	GEvent.addListener(tempPolygon, "lineupdated", function()
	{
		mctSetInputFieldValue(controlDivId, "");
		mctRemoveMarker(controlDivId);
		mctRemovePolygon(controlDivId);
	});
}

function mctCreatePolygon(coords, controlDivId, editable)
{
	// Remove any previous markers or regions.
	mctClearMap(controlDivId);
	
	var polygon = null;
	if(editable)
	{
	    // Create a new editable polygon.
		polygon = new GPolygon(coords, MCT_EDIT_POLY_COLOUR, 2, 0.7, MCT_EDIT_POLY_COLOUR, 0.2);
		mctPolygons[controlDivId] = polygon;
	    mctMaps[controlDivId].addOverlay(polygon);
		polygon.enableEditing();
		
		// Add a listener with an anonymous function for saving the coordinates when the polygon is completed.
		GEvent.addListener(polygon, "lineupdated", function()
		{
			// Update the input field coordinates.
			mctSavePolygonString(polygon, controlDivId);
			// Continue to allow editing this polygon or drawing a new polygon.
			mctBeginDrawing(controlDivId);
		});
		
		// Add a listener with an anonymous function for deleting a vertex.
		GEvent.addListener(polygon, "click", function(latlng, index)
		{
		    if( typeof index == "number" )
			{
				// Delete the vertex.
				polygon.deleteVertex(index);
				// Update the input field coordinates.
				mctSavePolygonString(polygon, controlDivId);
				// Continue to allow editing this polygon or drawing a new polygon.
				mctBeginDrawing(controlDivId);
		    }
		}); 
		
	}
	else
	{   
		// Create a non-editable, non-clickable region.
		polygon = new GPolygon(coords, MCT_POLY_COLOUR, 2, 0.7, MCT_POLY_COLOUR, 0.2,{"clickable":false});
		mctPolygons[controlDivId] = polygon;
	   	mctMaps[controlDivId].addOverlay(polygon);
		polygon.disableEditing();
	}
}

function mctSavePolygonString(polygon, controlDivId)
{	
	// Get the coordinates of the polygon vertices.
	var polyString = "";
	for( var i=0; i<polygon.getVertexCount(); i++ )
	{
 		var pLat = polygon.getVertex(i).lat();
 		var pLng = polygon.getVertex(i).lng();
 		if(i == 0)
 		{
			polyString =  pLng.toFixed(6) + "," + pLat.toFixed(6);
 		}
		else
		{
			polyString = polyString + " " + pLng.toFixed(6) + "," + pLat.toFixed(6);
		}           	
	} 
	// Set the input field value.
	mctSetInputFieldValue(controlDivId, polyString);
}

function mctRemovePolygon(controlDivId)
{
    // Check that we have a reference to the polygon.
	if( (polygon = mctPolygons[controlDivId]) != null )
	{
		// Disable editing (even though it is probably already disabled) to enable removal to work.
		polygon.disableEditing();
		// Remove the polygon overlay.
		mctMaps[controlDivId].removeOverlay(polygon);
		// Remove the reference.
		mctPolygons[controlDivId] = null;
	}
}

function mctRemoveTempPolygon(controlDivId)
{
	// Check that we have a reference to the polygon.
    if( (tempPolygon = mctTempPolygons[controlDivId]) != null )
	{
		// Disable editing to enable removal to work.
		tempPolygon.disableEditing();
		// Remove the temp polygon overlay.
		mctMaps[controlDivId].removeOverlay(tempPolygon);
		// Remove the reference.
		mctTempPolygons[controlDivId] = null;
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
  		mctGeocoder.getLocations(searchText, mctAddAddressToMap);	
	}
}

function mctAddAddressToMap(response)
{
    var markerBullet = '';
	var resultText = "";
	var coordString = "";
	if( !response || response.Status.code != 200 ) 
	{
		resultText = "No locations found";
	} 
	else 
	{	
		 // Loop through the results
		for( var i=0; i < response.Placemark.length; i++ ) 
		{
			var accuracy = response.Placemark[i].AddressDetails.Accuracy;
			/*		
			0 	Unknown accuracy.
			1 	Country level accuracy.
			2 	Region (state, province, prefecture, etc.) level accuracy.
			3 	Sub-region (county, municipality, etc.) level accuracy.
			4 	Town (city, village) level accuracy.
			5 	Post code (zip code) level accuracy.
			6 	Street level accuracy.
			7 	Intersection level accuracy.
			8 	Address level accuracy.
			9 	Premise (building name, property name, shopping center, etc.) level accuracy.
			*/		
			if( response.Placemark[i].ExtendedData.LatLonBox != "undefined" && accuracy < 7 )
			{
				var latLonBox = response.Placemark[i].ExtendedData.LatLonBox;
				coordString = latLonBox.east +","+latLonBox.north+" ";
				coordString	+= latLonBox.west +","+latLonBox.north+" ";
				coordString	+= latLonBox.west +","+latLonBox.south+" ";
				coordString	+= latLonBox.east +","+latLonBox.south+" ";
				coordString	+= latLonBox.east +","+latLonBox.north;
				markerBullet = '&#9633;';
			}
			else
			{
				coordString = response.Placemark[i].Point.coordinates[0] +","+response.Placemark[i].Point.coordinates[1];
				markerBullet = '&#8226;';
			}
			resultText  += "<div class='mct_search_result' onclick='mctSetMapFromSearchResult(\""+coordString+"\",\""+mctCurrentMapId+"\");' title=\"Set the map with this search result\">" + markerBullet + "&nbsp;"+ response.Placemark[i].address+"</div>";
		}
	}
  	var searchResultsDivId = MCT_ADDRESS_SEARCH_RESULTS_ID_PREFIX + mctCurrentMapId;
	var searchResultsDiv = getObject(searchResultsDivId);	
	searchResultsDiv.innerHTML = resultText; 
}


function mctSetMapFromSearchResult(coordString, controlDivId)
{
	// Update the input field coordinates.
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
