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
// DHTML Globals
// -----------------------------------------------------------------------------
var RELATIVE_POSITION_BELOW_LEFT        = 0;
var RELATIVE_POSITION_BELOW_RIGHT       = 1;
var RELATIVE_POSITION_BELOW_LEFT_ALIGN  = 2;
var RELATIVE_POSITION_BELOW_RIGHT_ALIGN = 3;
var RELATIVE_POSITION_ABOVE_LEFT        = 4;
var RELATIVE_POSITION_ABOVE_LEFT_ALIGN  = 5;
var RELATIVE_POSITION_ABOVE_RIGHT       = 6;
var RELATIVE_POSITION_ABOVE_RIGHT_ALIGN = 7;

// Support for dynamic moving of objects.
var gDoMove = false;
var gMoveX = 0;
var gMoveY = 0;
var gMoveObject = null;

// DHTML Functions
// -----------------------------------------------------------------------------

// Layout tweaking
// -----------------------------------------------------------------------------
function initDHTML(containerID)
{
	setPageHeight(containerID);
	setMenuState();
	setLinksToNewWindows();
}


//Set the rel="help" and rel="preview" hyperlinks to open in a new window
function setLinksToNewWindows()
{  
	var anchors = document.getElementsByTagName("a");  
	for( var i=0; i < anchors.length; i++ )
	{  
		var anchor = anchors[i];  
		if( anchor.getAttribute("href") && (anchor.getAttribute("rel") == "help") || (anchor.getAttribute("rel") == "preview") )
		{
			anchor.target = "_blank";  
		}
	}  
}

function setPageHeight(containerID)
{
	// A hack to workaround IE6's lack of support for CSS2 min-height.
	// This will push the footer down to below the left-side menu on pages
	// without enough content for this to happen normally.
	// This will also mitigate against really long menu configuration from extending
	// beyond the arbitrary min-height in all browsers (if javascript is enabled).
	var minHeightPx = 380; // This should be set to the same value as 
	                       // pageContainerWithMenu.min-height in layout.css
	                       // to prevent pages that support min-height from 
	                       // un-necessarily re-rendering after onload.
	
	var objPageContainer = getObject(containerID);
	var objMenuContainer = getObject('menuContainer');
	
	// Note that there is no public standard for offsetHeight.
	var pageHeight = objPageContainer.offsetHeight;
	var menuHeight = 1;
	if( objMenuContainer )
	{
		menuHeight = objMenuContainer.offsetHeight;
	}
	
	if( menuHeight && pageHeight )
	{
		if( menuHeight >= minHeightPx )
		{
			objPageContainer.style.minHeight = (menuHeight+20) + "px";
		}
		if( browserHasWindowedSelectElements() )
		{
			// Then we're dealing with IE 5/6 and min-height is not supported so...	
			if( pageHeight < minHeightPx )
			{	// Not enough content to reach min-height so...
				objPageContainer.style.height = minHeightPx+"px";
				objPageContainer.style.overflow = "auto";
				pageHeight = minHeightPx;
			}
			if( menuHeight > pageHeight )
			{	// Menu still exceeds page height so...
				objPageContainer.style.height = (menuHeight+40) + "px";
				objPageContainer.style.overflow = "auto";
			}			
		}
	}
}

// Menu functionality
// -----------------------------------------------------------------------------
// This code uses a cookie to store menu state indefinitely (well until 2018),
// and provides for the opening and closing of menus.
// Note that if javascript is not enabled, then all menus will render as open
// to provide access to all available activites.
// Also note that if cookies are disabled then the menu state will not be stored,
// and session state will not be available to provide access to activities that
// require authorisations beyond public access.

// Closed menu list cookie globals.
var MENU_COOKIE_NAME = 'COSI_CLOSED_MENUS';
var MENUDEFAULTS_COOKIE_NAME = 'COSI_DEFAULTED_MENUS';
var MENU_COOKIE_TTL_DAYS = 365*5;
var CLOSED_MENUS_REMOVE = 1;
var CLOSED_MENUS_ADD = 2;
var MENU_OPEN = 0;
var MENU_CLOSED = 1;

// A global to hold the names of all the menus not in the special root menu.
var gAryMenus = new Array();
var gAryMenuState = new Array(); // default state of menu item

function writeMenuControls()
{
	var html = '<a onclick="setAllMenus(MENU_OPEN)" style="cursor: pointer;" title="Open all of the menus and sub-menus in the left navigation">Expand All Menus</a>';
	html += '<a onclick="setAllMenus(MENU_CLOSED)" style="cursor: pointer;" title="Close all of the menus and sub-menus in the left navigation">Collapse All Menus</a>';
	
	document.write(html);
}

function registerMenu(id)
{
	var index = gAryMenus.length;
	gAryMenus[index] = id;
}

function registerMenu(id, state)
{
	var index = gAryMenus.length;
	gAryMenus[index] = id;
	gAryMenuState[index] = state;
}

function isInArray(array, item) {
	var i = 0;
	for ( i=0; i<array.length; i++) {
		if (array[i] == item) {
			return true;
		} else {
			i++;
		}
	}
	return false;
}

function setMenuState()
{
	var i = 0;
	
	// Get the menu items which have already been set to their default state from the cookie.
	var defaultMenuState = '';
	if( (defaultMenuState = getCookie(MENUDEFAULTS_COOKIE_NAME)) == '' )
	{
		// The cookie doesn't exist, so create it for use by toggleMenu.
		setCookie(MENUDEFAULTS_COOKIE_NAME, '', MENU_COOKIE_TTL_DAYS);		
	}
		
	
	// If menu item hasn't already been set to it's default, set it now. 
	for( i=0; i < gAryMenus.length; i++ )
	{	
		if (defaultMenuState.indexOf(gAryMenus[i] + '#') >= 0) {
			setMenu(gAryMenus[i], MENU_OPEN, false);
		}	
		else
		{
			setMenu(gAryMenus[i], gAryMenuState[i], true);
			
			// ...add this menu to the list of closed menus.			
			var defaultedMenuList = getCookie(MENUDEFAULTS_COOKIE_NAME);
			defaultedMenuList += gAryMenus[i] + "#";
			setCookie(MENUDEFAULTS_COOKIE_NAME, defaultedMenuList, MENU_COOKIE_TTL_DAYS);
		}
	}
	
	// Get the menu state from the cookie.
	var closedMenuList = '';
	if( (closedMenuList = getCookie(MENU_COOKIE_NAME)) != '' )
	{
		
		// Check the list of closed menus.
		var aryClosedMenus = closedMenuList.split("#");
		for( i=0; i < aryClosedMenus.length-1; i++ )
		{
			// Close this menu without changing the state of the cookie.
			setMenu(aryClosedMenus[i], MENU_CLOSED, false);
		}
	}
	else
	{	// The cookie doesn't exist, so create it for use by toggleMenu.
		setCookie(MENU_COOKIE_NAME, '', MENU_COOKIE_TTL_DAYS);			
	}
	
	// Now that the menu state has been restored, make it visible.
	var objMenuContainer = getObject('menuContainer');
	if( objMenuContainer )
	{
		objMenuContainer.style.visibility = 'visible';
	}
}

function setMenu(menuID, menuState, updateCookie)
{
	var objMenu = getObject(menuID);
	var objMenuItems = getObject('MENUITEMS_'+menuID);
	var updateAction = CLOSED_MENUS_REMOVE;
	if( objMenu && objMenuItems )
	{
		var itemsDisplay = objMenuItems.style.display+'';
		var menuClass = objMenu.className;
		var selected = '';
		if( menuClass == 'menuLinkOpenSelected' || menuClass == 'menuLinkSelected' )
		{
			selected = 'Selected';
		}
		if( menuClass == 'menuLinkOpenSelectedRoot' || menuClass == 'menuLinkSelectedRoot' )
		{
			selected = 'SelectedRoot';
		}
		if( menuState == MENU_CLOSED )
		{	// Set the menu to closed.
			itemsDisplay = 'none';
			menuClass = 'menuLink'+selected;
			updateAction = CLOSED_MENUS_ADD;
		}
		else
		{	// Set the menu to open.
			itemsDisplay = 'block';
			menuClass = 'menuLinkOpen'+selected;
			updateAction = CLOSED_MENUS_REMOVE;
		}
		objMenu.className = menuClass;
		objMenuItems.style.display = itemsDisplay;
		if( updateCookie )
		{	// Update the menu state cookie.
			updateMenusCookie(menuID, updateAction);
		}
	}
}

function setAllMenus(menuState)
{
	for( var i=0; i < gAryMenus.length; i++ )
	{
		setMenu(gAryMenus[i], menuState, true);
	}
}

function toggleMenu(menuID)
{
	var objMenu = getObject(menuID);
	var objMenuItems = getObject('MENUITEMS_'+menuID);
	var updateAction = CLOSED_MENUS_REMOVE;
	if( objMenu && objMenuItems )
	{
		var itemsDisplay = objMenuItems.style.display+'';
		var menuClass = objMenu.className;
		var selected = '';
		if( itemsDisplay == 'none' )
		{	// Set the menu to open and update the cookie.
			setMenu(menuID, MENU_OPEN, true);
		}
		else
		{	// Set the menu to closed and update the cookie.
			setMenu(menuID, MENU_CLOSED, true);
		}
	}
}

function updateMenusCookie(menuID, action)
{
	var closedMenuList = getCookie(MENU_COOKIE_NAME);
	if( action == CLOSED_MENUS_ADD )
	{
		// If it isn't already there...
		if( closedMenuList.indexOf(menuID + "#") < 0 )
		{	// ...add this menu to the list of closed menus.
			closedMenuList += menuID + "#";
		}
	}
	
	if( action == CLOSED_MENUS_REMOVE )
	{	// Remove this menu from the list of closed menus.
		closedMenuList = closedMenuList.replace(menuID+"#", '');
	}
	setCookie(MENU_COOKIE_NAME, closedMenuList, MENU_COOKIE_TTL_DAYS);
}

function setCookie(cookieName, cookieValue, daysUntilExpiry)
{
	if( cookieName != '' )
	{
		var newCookie = cookieName + "=" + encodeURIComponent(cookieValue);	
		if( daysUntilExpiry )
		{
			var date = new Date();
			date.setTime(date.getTime()+(daysUntilExpiry*24*60*60*1000));
			newCookie += "; expires="+date.toGMTString();
		}
		newCookie += "; path=/";	
		document.cookie = newCookie;
	}
}

function getCookie(cookieName)
{
	var cookieContent = '';
	var cookieValue = '';
	var cookieNameEQ = cookieName+'=';
	if( document.cookie != '' )
	{
		var list = document.cookie.split("; ");
		for( var i=0; i < list.length; i++ )
		{
			var thisCookie = list[i];
			if( thisCookie.indexOf(cookieNameEQ) == 0 )
			{
				var valStart = cookieNameEQ.length;
				cookieValue = thisCookie.substring(valStart);
				break;
			}
		}
	}
	cookieValue = decodeURIComponent(cookieValue);
	return cookieValue;
}

// Functions for implementing dynamic DHTML controls.
// -----------------------------------------------------------------------------
function displayObjectNear(displayThisObj, nearThisObj, relPos)
{
    if( displayThisObj != null && nearThisObj != null )
	{
        displayThisObj.style.visibility = 'hidden';
        displayThisObj.style.display = 'block';
	    
		switch( relPos )
		{
			case RELATIVE_POSITION_ABOVE_RIGHT_ALIGN:
	        	displayThisObj.style.left=getX(nearThisObj) + nearThisObj.offsetWidth - displayThisObj.offsetWidth + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) - nearThisObj.offsetHeight - displayThisObj.offsetHeight + 'px';
			break;
			
			case RELATIVE_POSITION_ABOVE_RIGHT:
	        	displayThisObj.style.left=getX(nearThisObj) + nearThisObj.offsetWidth + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) - nearThisObj.offsetHeight - displayThisObj.offsetHeight + 'px';
		    break;
			
			case RELATIVE_POSITION_ABOVE_LEFT_ALIGN:
	        	displayThisObj.style.left=getX(nearThisObj) + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) - nearThisObj.offsetHeight - displayThisObj.offsetHeight + 'px';
		    break;
			
			case RELATIVE_POSITION_ABOVE_LEFT:
	        	displayThisObj.style.left=getX(nearThisObj) - displayThisObj.offsetWidth + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) - nearThisObj.offsetHeight - displayThisObj.offsetHeight + 'px';
		    break;
		
			case RELATIVE_POSITION_BELOW_RIGHT_ALIGN:
	        	displayThisObj.style.left=getX(nearThisObj) + nearThisObj.offsetWidth - displayThisObj.offsetWidth + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) + 'px';
			break;

			case RELATIVE_POSITION_BELOW_RIGHT:
	        	displayThisObj.style.left=getX(nearThisObj) + nearThisObj.offsetWidth + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) + 'px';
			break;
		    
			case RELATIVE_POSITION_BELOW_LEFT_ALIGN:
	        	displayThisObj.style.left=getX(nearThisObj) + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) + 'px';
			break;
			
			default: // RELATIVE_POSITION_BELOW_LEFT
	        	displayThisObj.style.left=getX(nearThisObj) - displayThisObj.offsetWidth + 'px';
	        	displayThisObj.style.top=getY(nearThisObj) + 'px';
			break;
		}
	    if( browserHasWindowedSelectElements() ){ hideObscuredSelects(displayThisObj, true); }
        displayThisObj.style.visibility = 'visible';
    }
}

function displayObjectAt(displayThisObj, X, Y)
{
    if( displayThisObj != null ){
        displayThisObj.style.visibility = 'hidden';
        displayThisObj.style.display = 'block';
        displayThisObj.style.left = X;
        displayThisObj.style.top = Y;
        if( browserHasWindowedSelectElements() ){ hideObscuredSelects(displayThisObj, true); }
        displayThisObj.style.visibility = 'visible';
    }
}

function isObjectDisplayed(obj)
{
	var isDisplayed = true;
	if( obj != null && obj.style.display == 'none' )
	{
        isDisplayed = false;
    }
	return isDisplayed;
}

function displayObject(obj, display)
{
    if( obj != null )
	{
        if( display == 'none' && browserHasWindowedSelectElements() )
		{
			// Show any selects that this object had hidden.
			hideObscuredSelects(obj, false);
		}
		obj.style.display = display;
    }
}

function getObject(elementId)
{
	return document.getElementById(elementId);
}

function getX(obj)
{
    // Returns the absolute left position of obj.
    var total = obj.offsetLeft;
	var parentObject = obj.offsetParent;
    
	while( parentObject && parentObject.tagName != null )
	{
		total += parentObject.offsetLeft;
		parentObject = parentObject.offsetParent;
	}
    
    return total;
}
    
function getY(obj)
{
    // Returns the BOTTOM position of obj,
    // NOT the top position like you'd expect!
    // This is generally more useful, and we don't want to rewrite isObscuring.
    var total = obj.offsetTop + obj.offsetHeight;
	var parentObject = obj.offsetParent;
    
	while( parentObject && parentObject.tagName != null )
	{
		total += parentObject.offsetTop;
		parentObject = parentObject.offsetParent;
	}    

    return total;
}

function updateImage(imageId, src, title)
{
	var obj = getObject(imageId);
	if( obj )
	{
		obj.alt = title;
		obj.title = title;
		obj.src = src;
	}
}

function startMove(event, object)
{
	gDoMove = true;
	gMoveObject = object;
	gMoveX = event.clientX;
	gMoveY = event.clientY;

	document.onmouseup = endMove;
	document.onmousemove = moveObject;
}

function endMove()
{
	gDoMove = false;
	gMoveObject = null;

	document.onmouseup = null;
	document.onmousemove = null;
}

function moveObject(evt)
{
	if( gDoMove )
	{
		// Workaround for differences in Mozilla and
		// IE event handling. Safari is happy either way.
		if( evt != null )
		{
			event = evt;
		}
		var deltaX = event.clientX - gMoveX;
		var deltaY = event.clientY - gMoveY;

		var X = parseInt(gMoveObject.style.left, 10);
		var Y = parseInt(gMoveObject.style.top, 10);

		X += deltaX;
		Y += deltaY;

		displayObjectAt(gMoveObject, X+'px', Y+'px');

		gMoveX = event.clientX;
		gMoveY = event.clientY;
	}
}


// Functions to workaround browser idiosyncracies.
// -----------------------------------------------------------------------------
function browserHasWindowedSelectElements()
{
	var browserHasWindowedSelects = false;
	var strUserAgent = navigator.userAgent;
	if( strUserAgent.indexOf("MSIE 5") > -1 || strUserAgent.indexOf("MSIE 6") > -1 )
	{
		browserHasWindowedSelects = true;
	}
	return browserHasWindowedSelects;
}

function browserSupportsTableRowCSS()
{
	var supported = true;
	var strUserAgent = navigator.userAgent;
	if( strUserAgent.indexOf("MSIE") > -1 )
	{
		supported = false;
	}	
	return supported;
}

function hideObscuredSelects(obj, hide)
{
    // MSIE 5/6 will show select lists through any DHTML objects that are on a layer
	// above them, so we hide them first, and display them again when we're done.
	// Note also that Firefox 2.x on Mac OS X will not hide the scrollbar on multiple select lists 
	// and it will show them through any objects on a higher layer.
	
	// Any selects actually within the object that you're displaying on the higher layer
	// will need to be given a style attribute specifying a z-index greater than 0
	// (preferrably the same as the control that contains it).
	var selects;
    var i;
	
    selects = document.getElementsByTagName("SELECT");
    if( !hide )
	{
        // Make visible every select on the page.
        if( selects != null )
		{
            for( i=0; i < selects.length; i++ )
			{
                selects[i].style.visibility = "visible";
            }
        }
    } 
	else  // hide
	{
        // Make hidden any selects that are obscured by obj.
        if( selects != null )
		{  
            for( i=0; i < selects.length; i++ )
			{    
                if( isObscuring(obj, selects[i]) && (selects[i].style.zIndex == '' || selects[i].style.zIndex <= 0) )
				{
                    selects[i].style.visibility = "hidden";
                }
				else
				{
					selects[i].style.visibility = "visible";
				}
            }
        }
    }
}

function isObscuring(O1, O2)
{
    // Is O1 obscuring O2?
    var obscured = false;
    var O1XL, O1XR, O1YU, O1YL, O2XL, O2XR, O2YU, O2YL;
    var iSmallestXR, iBiggestXL;
    var iSmallestYL, iBiggestYU;
    var Xoverlap = 0;
    var Yoverlap = 0;
    
    O1XL = getX(O1);
    O1XR = getX(O1) + O1.offsetWidth;
    O1YU = getY(O1) - O1.offsetHeight;
    O1YL = getY(O1);
    O2XL = getX(O2);
    O2XR = getX(O2) + O2.offsetWidth;
    O2YU = getY(O2) - O2.offsetHeight;
    O2YL = getY(O2);
    
    // Set the vars we need to calculate the overlap area.
    iSmallestXR = O1XR;
    if( O2XR < O1XR )
	{
        iSmallestXR = O2XR;
    }
    iBiggestXL = O1XL;
    if( O2XL > O1XL )
	{
        iBiggestXL = O2XL;
    }
    
    iSmallestYL = O1YL;
    if( O2YL < O1YL )
	{
        iSmallestYL = O2YL;
    }
    iBiggestYU = O1YU;
    if( O2YU > O1YU )
	{
        iBiggestYU = O2YU;
    }
    
    Xoverlap = iSmallestXR-iBiggestXL;
    Yoverlap = iSmallestYL-iBiggestYU;

    if( Xoverlap > 0 && Yoverlap > 0 )
	{
    	obscured = true;
    }

    return obscured;
}
