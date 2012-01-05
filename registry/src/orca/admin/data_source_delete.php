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
require '../../_includes/init.php';
require '../orca_init.php';
// Page processing
// -----------------------------------------------------------------------------

// define local variables
$pageID      = "data_source_delete.php?data_source_key=".urlencode(getQueryValue('data_source_key')); // page that form will action on a save submit
$xmlData     = "";                                                               // path to xml form initial data
$gDataSource = gSOURCE_STRING;
$xmlForm     = eAPP_ROOT."orca/admin/data_source_delete_form.xml";      // path to xml form layout
$responseOK  = eAPP_ROOT."orca/admin/data_source_list.php";             // page that will be redirect to if form is validated
$pageCancel  = eAPP_ROOT."orca/admin/data_source_view.php".'?data_source_key='.urlencode(getQueryValue('data_source_key'));  // page that form will action on a cancel submit
$postError   = "";


// Get the data for this record.
$key = getQueryValue('data_source_key');

// Get the record from the database.
$DataSource = getDataSources($key, null);
if( !$DataSource )
{
	responseRedirect("data_source_list.php");
}

$errors = '';

if( getPostedValue('action') ) // The user has submitted the form.
{
	// Validate the posted data.
	$Result = doProcessForm($xmlForm, $xmlData, "Delete");
	if ( $Result )
	{
		$message = "";
		
		// Delete all the related Registry Objects.
		$errors .= deleteDataSourceRegistryObjects($key , $message, "DELETE ALL RECORDS");
		$errors .= deleteDataSourceDrafts($key , $message);

		// Delete the record.
		$errors .= deleteDataSource($key);
		
		if( $errors == "" )
		{
			responseRedirect($responseOK);
		}
		else
		{
			$postError = getErrorXML('', $errors);
		}
	}
}
else // The user has navigated here.
{
	// Get tha data ready for the form processor.
	$xmlData = arrayToDataXML($DataSource);

	// Build the edit form.
	$Result = doProcessForm($xmlForm, $xmlData);
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================

print(formTransform($xmlForm, $pageID, $postError));

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
