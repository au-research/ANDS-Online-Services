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
if (!IN_ORCA) die('No direct access to this file is permitted.');

if (isset($_GET['key']) && $draft = getDraftRegistryObject($_GET['key'], $dataSourceValue))
{

	// Get data sources which we have access to
	$rawResults = getDataSources(null, null);
	$dataSources = array();
	
	if( $rawResults )
	{
		foreach( $rawResults as $dataSource )
		{
			if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_ADMIN()) )
			{
				$dataSources[$dataSource['data_source_key']] = $dataSource;
			}		
		}
	}
	if(array_key_exists($dataSourceValue, $dataSources)) {
		
		deleteDraftRegistryObject($dataSourceValue, $_GET['key']);
		
		header("Location: " .  eAPP_ROOT."orca/manage/my_records.php?data_source=" . rawurlencode($dataSourceValue));
						
	} else {
		// invalid user, cannot delete draft you do not own!
		print ("Could not delete - Error: Cannot delete a record which you do not own. ");
	}
	
} else {
	
	// 	no registry object
	print ("Could not delete - Error: Invalid key, perhaps this draft has already been deleted? ");
}