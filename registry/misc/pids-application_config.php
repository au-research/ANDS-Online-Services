<?php


// BEGIN - PIDS-SELFSERVICE ####################################################
/*******************************************************************************
$Date: 2010-06-22 15:53:28 +1000 (Tue, 22 Jun 2010) $
$Revision: 435 $
*******************************************************************************/
// =============================================================================
// Persistent Identifiers
$menu = new menu('mPIDS_CONTAINER', 'Identify My Data', gROOT_MENU_ID);
$menu->margin_class = 'marginLeftLightBlue';
addMenu($menu);

	// =============================================================================
	// List
	$activity = new activity('aPIDS_LIST', 'List My Identifiers', 'pids/index.php');
	$activity->menu_id = 'mPIDS_CONTAINER';
	$activity->help_content_uri = eAPP_ROOT.'pids/_helpcontent/hc_pids.php';
	$activity->help_content_fragment_id = 'list';
	addActivity($activity);

	// =============================================================================
	// Create
	$activity = new activity('aPIDS_CREATE', 'Create Identifier', 'pids/create.php');
	$activity->menu_id = 'mPIDS_CONTAINER';
	$activity->help_content_uri = eAPP_ROOT.'pids/_helpcontent/hc_pids.php';
	$activity->help_content_fragment_id = 'create';
	addActivity($activity);

	// =============================================================================
	// View
	$activity = new activity('aPIDS_VIEW', 'View Identifier', 'pids/view.php');
	$activity->menu_id = 'mPIDS_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Add
	$activity = new activity('aPIDS_ADD', 'Add Identifier Property', 'pids/add.php');
	$activity->menu_id = 'mPIDS_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Edit
	$activity = new activity('aPIDS_EDIT', 'Edit Identifier Property', 'pids/edit.php');
	$activity->menu_id = 'mPIDS_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Delete
	$activity = new activity('aPIDS_DELETE', '', 'pids/delete.php');
	addActivity($activity);

// END - PIDS-SELFSERVICE ######################################################


?>