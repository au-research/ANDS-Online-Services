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
/*
	Menu Margin Classes
	---------------------
	marginLeftDarkOrange
	marginLeftLightOrange
	marginLeftDarkYellow
	marginLeftLightYellow
	marginLeftDarkRed
	marginLeftLightRed
	marginLeftDarkGreen
	marginLeftLightGreen
	marginLeftDarkBlue
	marginLeftLightBlue
*/
// Built-in activities not in the left navigation/menuing.
// -----------------------------------------------------------------------------
// =============================================================================
// About
$activity = new activity('aCOSI_ABOUT', 'About '.eINSTANCE_TITLE_SHORT.' '.eAPP_TITLE, 'about.php');
addActivity($activity);

// =============================================================================
// Help
$activity = new activity('aCOSI_HELP', 'Help', 'help.php');
addActivity($activity);

// =============================================================================
// Index
$activity = new activity('aCOSI_INDEX', '', 'index.php');
addActivity($activity);

// =============================================================================
// Versions
$activity = new activity('aCOSI_VERSIONS', 'About The Applications', 'versions.php');
addActivity($activity);


// Menu and Activity Definition
// -----------------------------------------------------------------------------
// BEGIN - COSI ################################################################
// =============================================================================
// Login
$activity = new activity('aCOSI_LOGIN', 'Login', 'login.php');
$activity->menu_id = gROOT_MENU_ID;
$activity->help_content_uri = eAPP_ROOT.'_helpcontent/hc_login.php';
addActivity($activity);

// =============================================================================
// Change Built-in Passphrase
$activity = new activity('aCOSI_CHANGE_BUILT_IN_PASS', 'Change Built-in Passphrase', 'change_builtin_passphrase.php');
$activity->menu_id = gROOT_MENU_ID;
addActivity($activity);


// =============================================================================
// Instance Administration
$menu = new menu('mCOSI_ADMIN', 'Administration', gROOT_MENU_ID);
$menu->margin_class = $eThemes[$eTheme][1];
addMenu($menu);

	// =============================================================================
	// Role List
	$activity = new activity('aCOSI_ROLE_LIST', 'List Roles', 'admin/role_list.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	addActivity($activity);

	// =============================================================================
	// Reset Built-in Passphrase
	$activity = new activity('aCOSI_RESET_BUILT_IN_PASS', 'Reset Built-in Passphrase', 'reset_builtin_passphrase.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	addActivity($activity);

	// =============================================================================
	// Add Role
	$activity = new activity('aCOSI_ROLE_ADD', 'Add Roles', 'admin/role_add.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	addActivity($activity);

	// =============================================================================
	// View Role
	$activity = new activity('aCOSI_ROLE_VIEW', 'View Role', 'admin/role_view.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Edit Role
	$activity = new activity('aCOSI_ROLE_EDIT', 'Edit Role', 'admin/role_edit.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Delete Role
	$activity = new activity('aCOSI_ROLE_DELETE', 'Delete Role', 'admin/role_delete.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	$activity->only_show_if_active= true;
	addActivity($activity);

	$activity = new activity('aORCA_RUN_TASKS', 'Background Task Manager', 'orca/maintenance/show_tasks.php');
	$activity->menu_id = 'mCOSI_ADMIN';
	addActivity($activity);

	// =============================================================================
	// Documentation
	$menu = new menu('mCOSI_DOCUMENTATION', 'Styles Documentation', 'mCOSI_ADMIN');
	addMenu($menu);

		// =============================================================================
		// General Style Sampler
		$activity = new activity('aEXAMPLE_STYLE_SAMPLER', 'General Styles', 'admin/documentation/stylesampler.php');
		$activity->menu_id = 'mCOSI_DOCUMENTATION';
		addActivity($activity);

		// =============================================================================
		// Form Style Sampler
		$activity = new activity('aEXAMPLE_FORM_STYLE_SAMPLER', 'Form Styles', 'admin/documentation/formstylesampler.php');
		$activity->menu_id = 'mCOSI_DOCUMENTATION';
		addActivity($activity);

		// =============================================================================
		// Chart Sampler
		$activity = new activity('aEXAMPLE_CHART_SAMPLER', 'Charts', 'admin/documentation/chartsampler.php');
		//$activity->menu_id = 'mCOSI_DOCUMENTATION';
		addActivity($activity);

		// =============================================================================
		// Example Theme One
		//$activity = new activity('aEXAMPLE_THEME_ONE', 'Example Theme One', 'admin/documentation/themeexample_one.php');
		//$activity->menu_id = 'mCOSI_DOCUMENTATION';
		//addActivity($activity);

		// =============================================================================
		// Example Theme Two
		//$activity = new activity('aEXAMPLE_THEME_TWO', 'Example Theme Two', 'admin/documentation/themeexample_two.php');
		//$activity->menu_id = 'mCOSI_DOCUMENTATION';
		//addActivity($activity);

// END - COSI ##################################################################




// BEGIN - ORCA ################################################################
/*******************************************************************************
$Date: 2011-11-25 16:09:32 +1100 (Fri, 25 Nov 2011) $
$Revision: 1634 $
*******************************************************************************/
// =============================================================================
// Collections Registry menu item
$menu = new menu('mORCA_CONTAINER', 'Collections Registry', gROOT_MENU_ID);
$menu->margin_class = 'marginLeftLightYellow';
addMenu($menu);

	/*
	// =============================================================================
	// Maintenance
	$activity = new activity('aORCA_RUN_TASKS', '', 'orca/maintenance/runTasks.php');
	$activity->menu_id = 'mORCA_CONTAINER';
	addActivity($activity);
	$activity->only_show_if_active= true;
	*/

	// =============================================================================
	// Index
	$activity = new activity('aORCA_INDEX', '', 'orca/index.php');
	//$activity->menu_id = 'mORCA_CONTAINER';
	addActivity($activity);

	// =============================================================================
	// Search
	$activity = new activity('aORCA_SEARCH', 'Search', 'orca/search.php');
	$activity->menu_id = 'mORCA_CONTAINER';
	addActivity($activity);



	// =============================================================================
	// Gold Level Collections
	$activity = new activity('aORCA_GOLD_INDEX', 'Gold Standard Records', 'orca/show_gold_level_collections.php');
	$activity->menu_id = 'mORCA_CONTAINER';
	addActivity($activity);

	// =============================================================================
	// View
	$activity = new activity('aORCA_VIEW', 'View Registry Object', 'orca/view.php');
	//$activity->menu_id = 'mORCA_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Web Services
	$activity = new activity('aORCA_WEB_SERVICES', 'Web Services', 'orca/services/index.php');
	$activity->menu_id = 'mORCA_CONTAINER';
	addActivity($activity);

		// =============================================================================
		// services/OpenSearchDescription
		$activity = new activity('aORCA_SERVICE_OPENSEARCH_DESCRIPTION', 'OpenSearch Description', 'orca/services/OpenSearchDescription.php');
		addActivity($activity);

		// =============================================================================
		// services/OpenSearch
		$activity = new activity('aORCA_SERVICE_OPENSEARCH', 'OpenSearch', 'orca/services/OpenSearch.php');
		addActivity($activity);

		// =============================================================================
		// services/getRegistryObject
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECT', 'Get Registry Object', 'orca/services/getRegistryObject.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		// services/getRegistryObjectKML
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECT_KML', 'Get Registry Object KML', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectKML.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		// services/getRegistryObjects
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECTS', 'Get Registry Objects', 'orca/services/getRegistryObjects.php');
		addActivity($activity);

		// =============================================================================
		// services/getRegistryObjectsSOLR
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECTS_SOLR', 'Get Registry Objects', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectsSOLR.php');
		$activity->no_check_ssl= true;

		addActivity($activity);

		// services/getRegistryObjectsSOLR
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECTS_SOLR_2', 'Indexer', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/indexer.php');
		$activity->no_check_ssl= true;
		addActivity($activity);


		$activity = new activity('aORCA_SERVICE_AJAX', 'AJAX', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/manage/get_view.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		// services/getRegistryObjectsKML
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECTS_KML', 'Get Registry Objects KML', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectsKML.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		// services/getRegistrySearchXHTML
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_SEARCH_XHTML', 'Get Registry Search XHTML', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistrySearchXHTML.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		// services/getDataSources
		$activity = new activity('aORCA_SERVICE_GET_DATA_SOURCES', 'Get Data Sources', 'orca/services/getDataSources.php');
		addActivity($activity);

		// =============================================================================
		// services/getRegistryObjectGroups
		$activity = new activity('aORCA_SERVICE_GET_REGISTRY_OBJECT_GROUPS', 'Get Registry Object Groups', 'orca/services/getRegistryObjectGroups.php');
		addActivity($activity);

		// =============================================================================
		// services/oai
		// OAI-PMH is not supported over SSL.
		$activity = new activity('aORCA_SERVICE_OAI_DATA_PROVIDER', 'OAI Data Provider', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/oai.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		//http://svcs.services.ands.org.au/home/orca/services
		// services/putHarvestData
		$activity = new activity('aORCA_SERVICE_PUT_HARVEST_DATA', 'Put Harvest Data', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/putHarvestData.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

		// =============================================================================
		// services/putNLAData
		$activity = new activity('aORCA_SERVICE_PUT_NLA_DATA', 'Put NLA Party Data', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/putNLAPartyData.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

	// BEGIN - PUBLISH MY DATA #####################################################
	// =============================================================================
	// My Collections
	$menu = new menu('mPMD_CONTAINER', 'Publish My Data', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightOrange';
	addMenu($menu);

		// =============================================================================
		// List My Collections
		$activity = new activity('aORCA_USER_LIST_COLLECTIONS', 'List My Published Collections', 'orca/user/index.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'overview';
		addActivity($activity);

		// =============================================================================
		// View Collection
		$activity = new activity('aORCA_USER_VIEW_COLLECTION', 'View Collection', 'orca/user/collection_view.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'view';
		$activity->only_show_if_active= true;
		addActivity($activity);

		// =============================================================================
		// Add Collection
		$activity = new activity('aORCA_USER_ADD_COLLECTION', 'Publish a Collection', 'orca/user/collection_add.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'add';
		addActivity($activity);

		// =============================================================================
		// Edit Collection
		$activity = new activity('aORCA_USER_EDIT_COLLECTION', 'Edit Collection', 'orca/user/collection_edit.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'edit';
		$activity->only_show_if_active= true;
		addActivity($activity);

		// =============================================================================
		// Delete Collection
		$activity = new activity('aORCA_USER_DELETE_COLLECTION', 'Delete Collection', 'orca/user/collection_delete.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'delete';
		$activity->only_show_if_active= true;
		addActivity($activity);

		// =============================================================================
		// View Publisher
		$activity = new activity('aORCA_USER_VIEW_PUBLISHER', 'View/Update My Details', 'orca/user/publisher_view.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'view_publisher';
		addActivity($activity);

		// =============================================================================
		// Edit Publisher
		$activity = new activity('aORCA_USER_EDIT_PUBLISHER', 'Edit Publisher Details', 'orca/user/publisher_edit.php');
		$activity->menu_id = 'mPMD_CONTAINER';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_user.php';
		$activity->help_content_fragment_id = 'edit_publisher';
		$activity->only_show_if_active= true;
		addActivity($activity);

	// END - PUBLISH MY DATA #######################################################

// END - ORCA ##################################################################



// BEGIN - PIDS-SELFSERVICE ####################################################
/*******************************************************************************
$Date: 2011-11-25 16:09:32 +1100 (Fri, 25 Nov 2011) $
$Revision: 1634 $
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


// BEGIN - DOIS-SELFSERVICE ####################################################
/*******************************************************************************
$Date: 2011-02-17 14:31:29 +1100 (Thu, 17 Feb 2011) $
$Revision: 723 $
*******************************************************************************/
// =============================================================================
// DOIS
$menu = new menu('mDOIS_CONTAINER', 'Identify My Datasets', gROOT_MENU_ID);
$menu->margin_class = 'marginLeftLightBlue';
addMenu($menu);

	// =============================================================================
	// List
	$activity = new activity('aDOIS_LIST', 'List My DOIS', 'dois/index.php');
	$activity->menu_id = 'mDOIS_CONTAINER';
	$activity->help_content_uri = eAPP_ROOT.'dois/_helpcontent/hc_pids.php';
	$activity->help_content_fragment_id = 'list';
	addActivity($activity);

	// =============================================================================
	// Create
	$activity = new activity('aDOIS_CREATE', 'Create DOI', 'dois/create_test.php');
	$activity->menu_id = 'mDOIS_CONTAINER';
	$activity->help_content_uri = eAPP_ROOT.'dois/_helpcontent/hc_pids.php';
	$activity->help_content_fragment_id = 'create';
	addActivity($activity);

	// =============================================================================
	// View
	$activity = new activity('aDOIS_VIEW', 'View DOI', 'dois/view.php');
	$activity->menu_id = 'mDOIS_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Add
	$activity = new activity('aDOIS_ADD', 'Add DOI Property', 'dois/add.php');
	$activity->menu_id = 'mDOIS_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Edit
	$activity = new activity('aDOIS_EDIT', 'Edit DOI', 'dois/edit.php');
	$activity->menu_id = 'mDOIS_CONTAINER';
	$activity->only_show_if_active= true;
	addActivity($activity);

	// =============================================================================
	// Delete
	$activity = new activity('aDOIS_DELETE', '', 'dois/delete.php');
	addActivity($activity);

// END - PIDS-SELFSERVICE ######################################################




	// =============================================================================
	// Register My Data Administration
	$menu = new menu('mORCA_ADMINISTRATION', 'Register My Data', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightYellow';
	addMenu($menu);

		// =============================================================================
		// Fetch Element (accessed by Javascript to asynchronously fetch HTML
		// elements such as help text/tab content/etc.)
		$activity = new activity('aORCA_FETCH_ELEMENT', 'Fetch elements', 'orca/fetch_element.php');
		addActivity($activity);

		// =============================================================================
		// Registry Object Administration
		$menu = new menu('mORCA_REGISTRY_OBJECTS', 'My Registry Objects', 'mORCA_ADMINISTRATION');
		addMenu($menu);

			// =============================================================================
			// List All Records
			//$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_LIST', 'Show All Records', 'orca/manage/list_registry_objects.php');
			//$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
			//addActivity($activity);


			// =============================================================================
			// View Draft Records
			$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_VIEW_DRAFT', 'List My Records', 'orca/manage/list_registry_objects.php');
			$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
			$activity->only_show_if_active= true;
			addActivity($activity);

			// =============================================================================
			// Manage My Records
			$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_MY_RECORDS', 'Manage My Records', 'orca/manage/my_records.php');
			$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
			$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_manage_my_records.php';
			$activity->help_content_fragment_id = 'mmr';
			addActivity($activity);


			// =============================================================================
			// Add Record Submenu
			$menu = new menu('mORCA_REGISTRY_OBJECT_ADMIN_ADD', 'Add New Record', 'mORCA_REGISTRY_OBJECTS');
			$menu->default_state = 'MENU_CLOSED';
			addMenu($menu);

				// =============================================================================
				// Add Activity Record
				$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_ADD_ACTIVITY', 'Activity', 'orca/manage/add_activity_registry_object.php');
				$activity->menu_id = 'mORCA_REGISTRY_OBJECT_ADMIN_ADD';
				addActivity($activity);

				// =============================================================================
				// Add Collection Record
				$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_ADD_COLLECTION', 'Collection', 'orca/manage/add_collection_registry_object.php');
				$activity->menu_id = 'mORCA_REGISTRY_OBJECT_ADMIN_ADD';
				addActivity($activity);

				// =============================================================================
				// Add Party Record
				$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_ADD_PARTY', 'Party', 'orca/manage/add_party_registry_object.php');
				$activity->menu_id = 'mORCA_REGISTRY_OBJECT_ADMIN_ADD';
				addActivity($activity);

				// =============================================================================
				// Add Service Record
				$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_ADD_SERVICE', 'Service', 'orca/manage/add_service_registry_object.php');
				$activity->menu_id = 'mORCA_REGISTRY_OBJECT_ADMIN_ADD';
				addActivity($activity);


		// =============================================================================
		// Add Registry Object [OLD] (hidden)
		//$activity = new activity('aORCA_REGISTRY_OBJECT_ADD', 'Add Registry Object [OLD]', 'orca/admin/registry_object_add.php');
		//$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
		//$activity->only_show_if_active= true;
		//addActivity($activity);

		// Process Registry Object (hidden)
		$activity = new activity('aORCA_REGISTRY_OBJECT_ADMIN_MANAGE_RECORDS', '', 'orca/manage/process_registry_object.php');
		//$activity->menu_id = 'mORCA_REGISTRY_OBJECT_ADMIN_ADD';
		addActivity($activity);

		// =============================================================================
		// Edit Registry Object
		$activity = new activity('aORCA_REGISTRY_OBJECT_EDIT', 'Edit Registry Object', 'orca/admin/registry_object_edit.php');
		$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
		$activity->only_show_if_active= true;
		addActivity($activity);

		// =============================================================================
		// Data Source History tool
		$activity = new activity('aORCA_REGISTRY_OBJECT_HISTORY', 'View History', 'orca/manage/view_history.php');
		$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
		$activity->only_show_if_active= true;
		addActivity($activity);

		// =============================================================================
		// Delete Registry Object
		$activity = new activity('aORCA_REGISTRY_OBJECT_DELETE', 'Delete Registry Object', 'orca/admin/registry_object_delete.php');
		$activity->menu_id = 'mORCA_REGISTRY_OBJECTS';
		$activity->only_show_if_active= true;
		addActivity($activity);




		// =============================================================================
		// Data Source Administration
		$menu = new menu('mORCA_DATA_SOURCE_ADMIN', 'My Data Sources', 'mORCA_ADMINISTRATION');
		addMenu($menu);

			// =============================================================================
			// Add Data Source
			$activity = new activity('aORCA_DATA_SOURCE_ADD', 'Add Data Source', 'orca/admin/data_source_add.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			addActivity($activity);


			// =============================================================================
			// List Data Sources
			$activity = new activity('aORCA_DATA_SOURCE_LIST', 'List My Data Sources', 'orca/admin/data_source_list.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_data_source_list.php';
			addActivity($activity);


			// =============================================================================
			// View Data Source
			$activity = new activity('aORCA_DATA_SOURCE_VIEW', 'View Data Source', 'orca/admin/data_source_view.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			$activity->only_show_if_active= true;
			$activity->help_content_uri = 'http://ands.org.au/guides/cpguide/cpgdsaaccount.html';
			addActivity($activity);


			// =============================================================================
			// Edit Data Source
			$activity = new activity('aORCA_DATA_SOURCE_EDIT', 'Edit Data Source', 'orca/admin/data_source_edit.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			$activity->only_show_if_active= true;
			$activity->help_content_uri = 'http://ands.org.au/guides/cpguide/cpgdsaaccount.html';
			addActivity($activity);

			// =============================================================================
			// Delete Data Source
			$activity = new activity('aORCA_DATA_SOURCE_DELETE', 'Delete Data Source', 'orca/admin/data_source_delete.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			$activity->only_show_if_active= true;
			addActivity($activity);

			// =============================================================================
			// Data Source Quality Check
			$activity = new activity('aORCA_DATA_SOURCE_QUALITY_CHECK', 'Data Source Quality Check', 'orca/admin/data_source_quality_check.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			addActivity($activity);

			// =============================================================================
			// Data Source Report
			$activity = new activity('aORCA_DATA_SOURCE_REPORTS', 'Data Source Reports', 'orca/admin/data_source_report.php');
			addActivity($activity);


			// =============================================================================
			// Export from a Data Source
			$activity = new activity('aORCA_DATA_SOURCE_EXPORT', 'Export from Data Source', 'orca/admin/data_source_export.php');
			$activity->menu_id = 'mORCA_DATA_SOURCE_ADMIN';
			addActivity($activity);

	// =============================================================================
	// PIDS IP Administration
	$menu = new menu('mORCA_PIDS_ADMINISTRATION', 'PIDS IP Administration', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightYellow';
	addMenu($menu);

		// =============================================================================
		// Add Trusted IP
		$activity = new activity('aORCA_PIDS_IP_ADD', 'Add Trusted IP', 'orca/admin/add_trusted_pids_client.php');
		$activity->menu_id = 'mORCA_PIDS_ADMINISTRATION';
		addActivity($activity);

		// =============================================================================
		// List Trusted IPs
		$activity = new activity('aORCA_PIDS_IP_LIST', 'List Trusted IPs', 'orca/admin/list_trusted_pids_client.php');
		$activity->menu_id = 'mORCA_PIDS_ADMINISTRATION';
		addActivity($activity);


	// =============================================================================
	// DOIS Administration
	$menu = new menu('mORCA_DOIS_ADMINISTRATION', 'DOIS Administration', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightYellow';
	addMenu($menu);

		// =============================================================================
		// Add Trusted IP
		$activity = new activity('aORCA_DOIS_ADD', 'Add DOI client', 'orca/admin/add_trusted_dois_client.php');
		$activity->menu_id = 'mORCA_DOIS_ADMINISTRATION';
		addActivity($activity);

		// =============================================================================
		// List Trusted IPs
		$activity = new activity('aORCA_DOIS_LIST', 'List Trusted DOI client', 'orca/admin/list_trusted_dois_client.php');
		$activity->menu_id = 'mORCA_DOIS_ADMINISTRATION';
		addActivity($activity);

		// =============================================================================
	// Data Statistics

	$menu = new menu('mORCA_STATISTICS', 'Data Statistics', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightYellow';
	addMenu($menu);

		// =============================================================================
		// Fetch Statistics
		$activity = new activity('aORCA_STATISTICS', 'Fetch statistics', 'orca/data_statistics_view.php');
		$activity->menu_id = 'mORCA_STATISTICS';
		addActivity($activity);
		// services/getRegistryObjectStat
		$activity = new activity('aORCA_STATISTIC_VIEWS', 'Get Registry Object Statistics', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/data_statistics_xls.php');
		$activity->no_check_ssl= true;
		addActivity($activity);

// Tidy up
// -----------------------------------------------------------------------------
$menu = null;
$activity = null;
?>
