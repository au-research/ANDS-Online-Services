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
// Required files and application initialisation: order is important.
// -----------------------------------------------------------------------------

// Include environment settings.
require '_includes/init.php';
require '_functions/orca_constants.php';
require '_functions/orca_access_functions.php';
require '_functions/orca_cache_functions.php';
require '_functions/orca_data_functions.php';
require '_functions/orca_import_functions.php';
require '_functions/orca_export_functions.php';
require '_functions/orca_data_source_functions.php';
require '_functions/orca_presentation_functions.php';
require '_functions/orca_oai_functions.php';
require '_functions/pids_functions.php';
require '_functions/orca_solr_functions.php';
require '_functions/orca_taskmgr_functions.php';

define('gORCA_IMAGE_ROOT', eAPP_ROOT.'orca/_images/');

// Add the ORCA stylesheet to the header.
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/orca.css');
// Add the map control styles in case they're needed.
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/map_control.css');
// Import the stylesheet for this page
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/add_registry_object.css');
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/jquery-ui-1.8.9.custom.css');
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/jquery-ui-1.8.17.custom.css');
// Open a connection to the database.
// This will be closed automatically by the framework.
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);
?>
