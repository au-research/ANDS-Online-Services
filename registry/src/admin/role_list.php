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
require '../_includes/init.php';
require 'admin_init.php';
// Page processing
// -----------------------------------------------------------------------------

$searchString = getQueryValue('search');
$action = getQueryValue('action');

// Execute the search.
$searchResults = getRoles(null, $searchString);

// If there is only one entry then just go straight to the detailed view.
if( $searchResults && count($searchResults) === 1 )
{
	responseRedirect("role_view.php?role_id=".urlencode($searchResults[0]['role_id']));
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<h3>Roles</h3>
<form id="searchform" action="role_list.php" method="get">
<div>
<input type="text" size="45" maxlength="255" name="search" value="<?php printSafe(getQueryValue('search')) ?>" />&nbsp;<input type="submit" name="action" value="Filter" /><br />
</div>
</form>

<?php 
if( !$searchResults )
{
	print("<p>No roles were returned.</p>\n");
}
else
{
	// Reorder the results array
	$newColumns = array('role_id', 
	                    'role_name',
	                    'role_type_name',
	                    'role_enabled',
	                    'modified_when',
	                    'last_login',
	                    'authentication_service_name',
	                    'authentication_service_enabled'
						);
	$tableData = getArrayFromArray($newColumns, $searchResults);
	foreach( $tableData as &$row )
	{
		$enabled = 'YES';
		if( !pgsqlBool($row['role_enabled']) ) { $enabled = 'NO'; }
		$row['role_enabled'] = $enabled;
		
		if( isset($row['authentication_service_enabled']) )
		{
			$enabled = 'YES';
			if( !pgsqlBool($row['authentication_service_enabled']) ) { $enabled = 'NO'; }
			$row['authentication_service_enabled'] = $enabled;
		}

	}
		
	$xmlTable = eAPP_ROOT."admin/role_list_table.xml";
	$pageID   = "role_list.php";
	drawArray_to_Table($xmlTable, $tableData, $pageID);

}// end if search results

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
