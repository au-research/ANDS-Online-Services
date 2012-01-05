<?php
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
// Menu Globals
// -----------------------------------------------------------------------------
// Array to hold menu objects.
$gMenus = array();

// Special root menu container id.
define("gROOT_MENU_ID", "ROOT_MENU");

// Object type constants.
define("gTYPE_MENU", 1);
define("gTYPE_ACTIVITY", 2);

// Presentation constants.
define("gMENU_PATH_COLOUR", "#aaaaaa");

// Activity Globals
// -----------------------------------------------------------------------------
// Array to hold activity objects
$gActivities = array();

// Path Globals
// -----------------------------------------------------------------------------
// Flag to support drawing of the path to the current activity.
$gDrawPath = false;

// Menu and Activity Functions
// -----------------------------------------------------------------------------
function addMenu($menu)
{
	global $gMenus;
	$gMenus[$menu->id] = $menu;
}

function addActivity($activity)
{
	global $gActivities;
	$gActivities[$activity->id] = $activity;
}

function drawMenus()
{
	global $gAppMarginClass;
	global $gMenus;
	global $gActivities;
	
	// Hide the menuContainer until we're ready.
	// The call to setMenuState in DHTML.js will make visible again.
	//print '<script type="text/javascript">document.getElementById(\'menuContainer\').style.visibility = \'hidden\'</script>';
	
	// Update any titles as required.
	if( getSessionVar(sROLE_ID) )
	{
		$gActivities['aCOSI_LOGIN']->title = 'Logout';
		$gActivities['aCOSI_LOGIN']->path = $gActivities['aCOSI_LOGIN']->path.'?logout=logout';	
	}
	
	// Draw the activities and menus for the special root menu container
	// then recursively draw all sub menus and activities.
	drawRootMenuGroupBegin($gAppMarginClass);
	foreach( $gActivities as $aKey => $aValue )
	{
		if( $aValue->menu_id == gROOT_MENU_ID && hasActivity($aValue->id) )
		{
			drawLink($aValue->id, gTYPE_ACTIVITY);
		}
	}
	drawRootMenuGroupEnd();
	
	foreach( $gMenus as $mKey => $mValue )
	{
		if( $mValue->parent_id == gROOT_MENU_ID && hasMenu($mValue->id) )
		{
			drawRootMenuGroupBegin($mValue->margin_class);
				drawLink($mValue->id, gTYPE_MENU);
				drawMenuItemsBegin($mValue->id);
					drawMenu($mValue->id);
				drawMenuItemsEnd();
			drawRootMenuGroupEnd();
		}
	}
}

function drawMenu($id){
	global $gMenus;
	global $gActivities;
	
	// Iterate activities for $id
	// and draw each activity found.
	foreach( $gActivities as $aKey => $aValue )
	{
		if( $aValue->menu_id == $id && hasActivity($aValue->id) )
		{
			drawLink($aValue->id, gTYPE_ACTIVITY);
		}
	}

	// Iterate menus for $id.
	foreach( $gMenus as $mKey => $mValue )
	{
		if( $mValue->parent_id == $id && hasMenu($mValue->id) )
		{
			// Recursively iterate sub activities and menus
			drawLink($mValue->id, gTYPE_MENU);
			drawMenuItemsBegin($mValue->id);
				drawMenu($mValue->id);
			drawMenuItemsEnd();
		}
	}
}

function drawLink($id, $type)
{
	global $gMenus;
	global $gActivities;
	global $gDrawPath;
	
	$class = '';
	$href = '';
	$title = '';
	$elementid = '';
	$onclick = '';
	$style = '';
	
	$parentIsRootMenu = false;
	$parentIsSelected = false;
	$isSelected = false;
	$show = true;
	$link = true;
	
	if( $type == gTYPE_MENU )
	{
		$class = 'menuLinkOpen';
		$text = $gMenus[$id]->title;
		//$title = $text;
		$elementid = " id=\"$id\"";
		$onclick = " onclick=\"toggleMenu('$id')\"";

		if( $gMenus[$id]->parent_id == gROOT_MENU_ID )
		{
			$parentIsRootMenu = true;
		}
		if( checkMenuSelected($gMenus[$id]->parent_id) )
		{
			$parentIsSelected = true;
		}
		if( checkMenuSelected($id) )
		{	// We're in the container path of the currently selected activity so...
			$class .= 'Selected';
			$style = ' style="margin-left: 0px; padding-left: 16px;"';
			$isSelected = true;
		}
		if( $parentIsRootMenu && $isSelected )
		{
			$class .= 'Root';
			// This is the starting point of the path to the current activity so...
			$gDrawPath = true;
		}
		if( !$parentIsRootMenu && $parentIsSelected && !$isSelected && $gDrawPath )
		{
			$style = ' style="margin-left: 0px; border-left: 1px solid '.gMENU_PATH_COLOUR.'"';
		}
		
	}
	
	if( $type == gTYPE_ACTIVITY )
	{
		$class = 'activityLink';
		$text = $gActivities[$id]->title;
		//$title = $text;
		$uri = $gActivities[$id]->path;
		$show = !$gActivities[$id]->only_show_if_active;
		if( !$show ){ $link = false; }

		if( $gActivities[$id]->menu_id == gROOT_MENU_ID )
		{
			$parentIsRootMenu = true;
		}	
		if( checkMenuSelected($gActivities[$id]->menu_id) )
		{
			$parentIsSelected = true;
		}		
		
		if( pathIsRelative($uri) )
		{	// Link to a local activity
			$uri = eAPP_ROOT.$uri;
		}
		else
		{	// Link to an external activity
			$class .= 'External';
			$title = 'External Link: '.$text;
		}

		if( checkActivitySelected($id) )
		{	// We're currently at this uri so...
			$class .= 'Selected';
			$style = ' style="margin-left: 0px; padding-left: 16px;"';
			$isSelected = true;
			// We're done drawing the path to this activity.
			$gDrawPath = false;
			$show = true;
		}

		$href = " href=\"$uri\"";
		
		if( $parentIsRootMenu && $isSelected )
		{
			$class .= 'Root';
		}
	
		if( !$parentIsRootMenu && $parentIsSelected && $gDrawPath )
		{
			$style = ' style="margin-left: 0px; border-left: 1px solid '.gMENU_PATH_COLOUR.';"';
		}
	}
	if( $show && $link )
	{
		print "<a class=\"$class\"$style$elementid$onclick$href title=\"".esc($title)."\">".esc($text)."</a>\n";
	}
	elseif( $show )
	{
		print "<span class=\"$class\"$style$elementid$onclick title=\"".esc($title)."\">".esc($text)."</span>\n";
	}
}

function pathIsRelative($path)
{
	$isRelative = true;
	if( strpos(strtoupper($path), 'HTTP') === 0 )
	{
		$isRelative = false;
	}
	return $isRelative;
}

function checkMenuSelected($menuid)
{
	$selected = false;
	
	// Check for a selected activity in this menu and its submenus.
	checkMenuActivitySelected($menuid, $selected);

	return $selected;
}

function checkMenuActivitySelected($menuid, &$selected)
{
	global $gActivities;
	global $gMenus;
	
	// Check for a selected activity in this menu.
	foreach( $gActivities as $aKey => $aValue )
	{
		if( $aValue->menu_id == $menuid && checkActivitySelected($aValue->id) )
		{
			$selected = true;
		}
	}
	
	if( !$selected )
	{
		// Recursively check this menu's submenus.
		foreach( $gMenus as $mKey => $mValue )
		{
			if( $mValue->parent_id == $menuid )
			{
				checkMenuActivitySelected($mValue->id, $selected);
			}
		}
	}
}

function checkActivitySelected($activityid)
{
	global $gActivities;
	$selected = false;
	
	// Check if we're currently at this uri.
	if( strpos(eAPP_ROOT.$gActivities[$activityid]->path, getCurrentPath()) )
	{
		$selected = true;
	}
	return $selected;
}

function drawRootMenuGroupBegin($marginClass)
{
	print <<<EOT
		<!-- BEGIN: Root Menu Group -->
		<div class="{$marginClass}">
			<div class="rootMenu">

EOT;
}

function drawRootMenuGroupEnd()
{
	print <<<EOT
			</div>
		</div>
		<!-- END: Root Menu Group -->

EOT;
}

function drawMenuItemsBegin($id)
{
	global $gMenus;
	global $gDrawPath;
	
	$style = '';
	
	$parentIsRootMenu = false;
	$parentIsSelected = false;
	$isSelected = false;
	
	if( $gMenus[$id]->parent_id == gROOT_MENU_ID )
	{
		$parentIsRootMenu = true;
	}
	if( checkMenuSelected($gMenus[$id]->parent_id) )
	{
		$parentIsSelected = true;
	}
	if( checkMenuSelected($id) )
	{
		$isSelected = true;
	}
	if( !$parentIsRootMenu && $parentIsSelected && !$isSelected && $gDrawPath )
	{
		$style .= 'margin-left: 0px; border-left: 1px solid '.gMENU_PATH_COLOUR.';';
	}
	
	print "<script type=\"text/javascript\">registerMenu('$id',".$gMenus[$id]->default_state.");</script>";
	print "<div class=\"menuItems\" style=\"$style\" id=\"MENUITEMS_$id\">\n";
}

function drawMenuItemsEnd()
{
	print "</div>\n";
}

function checkApplicationConfig()
{
	global $gMenus;
	global $gActivities;
	global $eDisplayErrors;
	
	$activityErrors = false;
	$menuErrors = false;
	$errors = false;
	$errorMessage = '';
	$errorMessagePrefix = '';
	
	// Activity Checks
	// -------------------------------------------------------------------------
	foreach( $gActivities as $aKey => $aValue )
	{
		// Check the Menu reference.
		if(  $aValue->menu_id != '' && $aValue->menu_id !== gROOT_MENU_ID && !isset($gMenus[$aValue->menu_id]) )
		{
			$menuErrors = true;
			$errorMessage .= 'Activity '.$aValue->id.' references a non-existent menu '.$aValue->id.'->menu_id='.$aValue->menu_id.'; ';
		}
		// Check for non-unique paths.
		foreach( $gActivities as $aSubKey => $aSubValue )
		{
			if( $aKey != $aSubKey && $aValue->path == $aSubValue->path )
			{
				$activityErrors = true;
				$errorMessage .= 'non-unique Activity paths '.$aValue->id.'->path=='.$aSubValue->id.'->path; ';
			}
		}
	}
	if( $activityErrors )
	{
		$errors = true;
		$errorMessagePrefix = "CONFIGURATION ERRORS (application_config.php)--bad Activity settings: ";
		error_log($errorMessagePrefix.$errorMessage);
		if( $eDisplayErrors )
		{
			printSafe($errorMessagePrefix.$errorMessage);
		}
		$errorMessage = '';
	}
	

	// Menu Checks
	// -------------------------------------------------------------------------
	foreach( $gMenus as $mKey => $mValue )
	{
		// Check the parent_ids of all its parents.
		checkMenuSettings($mKey, $mKey, $menuErrors, $errorMessage);
	}
	if( $menuErrors )
	{
		$errors = true;
		$errorMessagePrefix = "CONFIGURATION ERRORS (application_config.php)--bad Menu references: ";
		error_log($errorMessagePrefix.$errorMessage);
		if( $eDisplayErrors )
		{
			printSafe($errorMessagePrefix.$errorMessage);
		}
	}

	if( $errors )
	{
		print "CONFIGURATION ERROR";
		exit;
	}
}

function checkMenuSettings($id, $testID, &$menuErrors, &$errorMessage)
{
	global $gMenus;
	
	$recurse = true;
	
	
	$parentID = $gMenus[$testID]->parent_id;
	
	// Check the parent Menu reference.
	if( !isset($gMenus[$parentID]) && $parentID !== gROOT_MENU_ID )
	{
		$menuErrors = true;
		$errorMessage .= $id.' references a non-existent menu '.$testID.'->parent_id='.$parentID.'; ';
	}
	if( $id === $parentID )
	{
		$menuErrors = true;
		$errorMessage .= $id.' is its own parent where '.$testID.'->parent_id='.$parentID.'; ';
		// If we recurse in this case we will be in an infinite loop, so...
		$recurse = false;
	}
	if( $parentID !== gROOT_MENU_ID && isset($gMenus[$parentID]) && $recurse )
	{
		checkMenuSettings($id, $parentID, $menuErrors, $errorMessage);
	}
}
?>
