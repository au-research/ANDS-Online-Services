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
//if (!IN_ORCA) die('No direct access to this file is permitted.');

// Execute the search.
$rawResults = getDataSources(null, null);
$dataSources = array();

// Check the record owners.
if( $rawResults )
{
	foreach( $rawResults as $dataSource )
	{
		if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_ADMIN()) )
		{
			$dataSources[] = esc($dataSource["data_source_key"]);
		}		
	}
}

$groups = getGroups("WHERE data_source_key IN ('" . implode("', '", $dataSources) . "')" .
					(getQueryValue('term') && getQueryValue('term') != "*" ? " AND UPPER(object_group) LIKE UPPER('%" . esc(getQueryValue('term')) . "%')" : ""), 15);

$values = array();

if ($groups) {
	foreach ($groups AS $grp)
	{
		$values[] = array(	"value" => $grp['object_group'] );
	}
}

if (count($values) == 0) {
	//$values[] = array (	"value" => "", "desc" => "Sorry - No Registry Object found!");
}

print json_encode($values);
