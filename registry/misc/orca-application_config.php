<?php

// BEGIN - ORCA ################################################################
/*******************************************************************************
$Date: 2010-09-24 15:33:17 +1000 (Fri, 24 Sep 2010) $
$Revision: 509 $
*******************************************************************************/
// =============================================================================
// Collections Registry menu item
$menu = new menu('mORCA_CONTAINER', 'Collections Registry', gROOT_MENU_ID);
$menu->margin_class = 'marginLeftLightYellow';
addMenu($menu);

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
	// Registry Index
	$activity = new activity('aORCA_REGISTRY_INDEX', 'Index', 'orca/registry_index.php');
	//$activity->menu_id = 'mORCA_CONTAINER';
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
		// services/putHarvestData
		$activity = new activity('aORCA_SERVICE_PUT_HARVEST_DATA', 'Put Harvest Data', 'http://'.eHOST.'/'.eROOT_DIR.'/orca/services/putHarvestData.php');
		$activity->no_check_ssl= true;
		addActivity($activity);
		
	// =============================================================================
	// Administration
	$menu = new menu('mORCA_ADMINISTRATION', 'Register My Data', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightYellow';
	addMenu($menu);

		// =============================================================================
		// Add Data Source
		$activity = new activity('aORCA_DATA_SOURCE_ADD', 'Add Data Source', 'orca/admin/data_source_add.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		addActivity($activity);


		// =============================================================================
		// List Data Sources
		$activity = new activity('aORCA_DATA_SOURCE_LIST', 'List My Data Sources', 'orca/admin/data_source_list.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_data_source_list.php';
		addActivity($activity);
		
		
		// =============================================================================
		// View Data Source
		$activity = new activity('aORCA_DATA_SOURCE_VIEW', 'List My Data Sources', 'orca/admin/data_source_view.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		$activity->only_show_if_active= true;
		$activity->help_content_uri = eAPP_ROOT.'orca/_helpcontent/hc_data_source_list.php';
		addActivity($activity);
		
		// =============================================================================
		// Edit Data Source
		$activity = new activity('aORCA_DATA_SOURCE_EDIT', 'Edit Data Source', 'orca/admin/data_source_edit.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		$activity->only_show_if_active= true;
		addActivity($activity);
		
		// =============================================================================
		// Delete Data Source
		$activity = new activity('aORCA_DATA_SOURCE_DELETE', 'Delete Data Source', 'orca/admin/data_source_delete.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		$activity->only_show_if_active= true;
		addActivity($activity);
		
		// =============================================================================
		// Add Registry Object
		$activity = new activity('aORCA_REGISTRY_OBJECT_ADD', 'Add Registry Object', 'orca/admin/registry_object_add.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		addActivity($activity);
		
		// =============================================================================
		// Edit Registry Object
		$activity = new activity('aORCA_REGISTRY_OBJECT_EDIT', 'Edit Registry Object', 'orca/admin/registry_object_edit.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		$activity->only_show_if_active= true;
		addActivity($activity);
		
		// =============================================================================
		// Delete Registry Object
		$activity = new activity('aORCA_REGISTRY_OBJECT_DELETE', 'Delete Registry Object', 'orca/admin/registry_object_delete.php');
		$activity->menu_id = 'mORCA_ADMINISTRATION';
		$activity->only_show_if_active= true;
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
		
	/*	
	// BEGIN - PIDS ADMINISTRATION #####################################################
	// =============================================================================
	// PIDS Administration
	$menu = new menu('mORCA_PIDS_ADMINISTRATION', 'PIDS Administration', gROOT_MENU_ID);
	$menu->margin_class = 'marginLeftLightYellow';
	addMenu($menu);

		// =============================================================================
		// Add Data Source
		$activity = new activity('mORCA_PIDS_IP_ADD', 'Add Trusted IP', 'orca/admin/add_trusted_pids_client.php');
		$activity->menu_id = 'mORCA_PIDS_ADMINISTRATION';
		addActivity($activity);
		
		// =============================================================================
		// List Data Sources
		$activity = new activity('mORCA_PIDS_IP_LIST', 'List Trusted IPs', 'orca/admin/list_trusted_pids_client.php');
		$activity->menu_id = 'mORCA_PIDS_ADMINISTRATION';
		addActivity($activity);
	
	// XXX: This section should be moved to cosi/_environment/application_env.php 
	// ORCA PIDS Service 
	// -----------------------------------------------------------------------------
	// URI of PIDS service (Server IP address should be added as 
	//                      trusted admin on Tomcat PID service)
	$ePIDS_RESOURCE_URI = "https://devl.ands.org.au:8443/pids/";	
	
	// END - PIDS ADMINISTRATION
	*/
	
	
// END - ORCA ##################################################################

?>