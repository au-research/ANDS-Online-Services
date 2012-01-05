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

// Get the record from the database.
$registryObject = getRegistryObject(getQueryValue('key'));
$registryObjectKey = null;
$dataSourceKey = null;
$registryObjectRecordOwner = null;
$registryObjectDataSourceRecordOwner = null;

if( !$registryObject )
{
	responseRedirect('index.php');
}
else
{
	$registryObjectKey = $registryObject[0]['registry_object_key'];
	$dataSourceKey = $registryObject[0]['data_source_key'];
	
	// Get the values that we'll need to check for conditional display and access.
	$registryObjectRecordOwner = $registryObject[0]['record_owner'];
	
	// Check access.
	if( !userIsRegistryObjectRecordOwner($registryObjectRecordOwner) )
	{
		responseRedirect('index.php');
	}
	
	if( strtoupper(getPostedValue('action')) == "CANCEL" )
	{
		responseRedirect("collection_view.php?key=".urlencode($registryObjectKey));
	}
	
	if( strtoupper(getPostedValue('action')) == "DELETE" )
	{
		$actions = '    1 Registry Object deleted.';
		$result = deleteRegistryObject($registryObjectKey);
		if( $result != '' )
		{
			$actions = '    '.$result;
		}
		
		removeCollectionRelationFromUserParty($registryObjectKey);
		
		// Log the datasource activity.
		insertDataSourceEvent($dataSourceKey, "DELETE REGISTRY OBJECT\nKey: ".$registryObjectKey."\n  ACTIONS\n".$actions);
		
		responseRedirect('index.php');
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
if( $registryObject )
{
	$registryObjectClass = $registryObject[0]['registry_object_class'];
	$registryObjectType = $registryObject[0]['type'];
?>
<form id="collection_delete" action="collection_delete.php?key=<?php printSafe(urlencode($registryObjectKey)) ?>" method="post">
  <table class="formTable" summary="Delete Registry Object">
    <thead>
      <tr>
        <td>&nbsp;</td>
        <td>Delete <?php printSafe($registryObjectClass); ?></td>
      </tr>
    </thead>
    <tbody class="formFields">
      <tr>
        <td class="">Type:</td>
        <td><?php printSafe($registryObjectType) ?></td>
      </tr>
      <tr>
        <td class="">Key:</td>
        <td><?php printSafe($registryObjectKey) ?><input type="hidden" name="key" id="key" value="<?php printSafe($registryObjectKey) ?>"/></td>
      </tr>
      <tr>
        <td class="">Title:</td>
        <td><?php print(getNameHTML($registryObjectKey)) ?></td>
      </tr>
    </tbody>
    <tbody>
      <tr>
        <td/>

        <td><input type="submit" name="action" value="Cancel"/>&nbsp;&nbsp;<input type="submit" name="action" value="Delete"/>&nbsp;&nbsp;</td>
      </tr>
    </tbody>
  </table>
</form>
<?php
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
