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
// Include required files and initialisation.
require '_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------

$helpActivityID = false;

// Get the activity id from the querystring.
if( isset($_GET['id']) )
{
	$helpActivityID = $_GET['id'];
}

// Check that the activity for which help is requested exists, 
// and that this user role has access to it.
checkActivityAccess($helpActivityID);

// Get the activity object.
$activity = getObject($gActivities, $helpActivityID);

// Get the help content for this activity.
// Set the http referer, so that the help content page can check it.
$context  = stream_context_create(array('http' => array('header' => 'Referer: '.eAPP_ROOT.'help.php')));
// Get the content.
$help_content = file_get_contents($activity->help_content_uri, false, $context);

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '_includes/help_header.php';
// BEGIN: Page Content
// =============================================================================

print $help_content;

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '_includes/help_footer.php';
require '_includes/finish.php';
?>
