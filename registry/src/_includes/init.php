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
// Required files and application initialisation: order is important
// -----------------------------------------------------------------------------
// Include environment settings.
require '_environment/application_env.php';
require '_environment/database_env.php';

// Include classes.
require '_classes/activity.php';
require '_classes/menu.php';

// Include functions.
require '_functions/general_functions.php';
require '_functions/database_functions.php';
require '_functions/data_functions.php';
require '_functions/session_functions.php';
require '_functions/access_functions.php';
require '_functions/presentation_functions.php';
require '_functions/menu_functions.php';
require '_functions/form_functions.php';
require '_functions/table_functions.php';

// Include the application configuration.
require '_configuration/application_config.php';

// Prevent PHP 'magic quotes' data corruption.
fixMagicQuotesGPC();

// Check the application_config.php settings for circular menu references,
// references to non-existent menus, and activities with non-unique paths.
checkApplicationConfig();

// Get this activity id.
$gThisActivityID = getThisActivityID();

// Check the request for SSL against the specified web root.
checkSSL($gThisActivityID);

// Set the request root.
$eRequestRoot = 'http://'.eHOST.'/'.eROOT_DIR.'/';
if( isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) === 'ON' )
{
	$eRequestRoot = 'https://'.eHOST.'/'.eROOT_DIR.'/';
}

// UNDER MAINTENANCE

$UNDER_MAINTENANCE = $maintenance;

if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
$ip=$_SERVER["HTTP_CLIENT_IP"];
} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
$ip=$_SERVER["REMOTE_ADDR"];
}
$requestUri = $_SERVER["REQUEST_URI"];

//END OF UNDER MAINETANCE

// Check the session to ensure that it belongs to the session owner.
// ie bind the session to the user agent and remote address at login.
checkSession();

// Set the logo and colour scheme for this instance.
setTheme($eTheme);

// Connect to the database.
// -----------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_COSI, eCNN_DBS_COSI);
openDatabaseConnection($gCNN_DBS_PIDS, eCNN_DBS_PIDS);
openDatabaseConnection($gCNN_DBS_DOIS, eCNN_DBS_DOIS);
// Authorisation and Access.
// -----------------------------------------------------------------------------
checkActivityAccess($gThisActivityID);


// Get the help content uri.
$thisHelpURI = getActivityHelpContentURI($gThisActivityID);
$thisHelpFragmentId = getActivityHelpContentFragmentId($gThisActivityID);

// Set the default page title.
$titleActivity = getObject($gActivities, $gThisActivityID);

$pageTitle = $titleActivity->title;
getTitlePath($titleActivity->menu_id, $pageTitle);
$pageTitle = esc(eINSTANCE_TITLE_SHORT.' '.eAPP_TITLE.gCHAR_EMDASH.$pageTitle);
?>
