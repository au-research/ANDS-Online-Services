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

// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 10*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");

// Set the Content-Type header.
header("Content-Type: text/xml; charset=UTF-8", true);

$dataSources = getDataSources(null, null);
$instanceRoot = esc(eAPP_ROOT).'orca';

// BEGIN: XML Response
// =============================================================================
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
print('<dataSources instanceRootURI="'.esc($instanceRoot).'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="'.gORCA_DATA_SOURCE_LIST_SCHEMA_URI.'">'."\n");
if( $dataSources )
{
	foreach( $dataSources as $dataSource )
	{
		print("\n  <dataSource>\n");
		print("    <key>".esc($dataSource['data_source_key'])."</key>\n");
		print("    <title>".esc($dataSource['title'])."</title>\n");
		print("    <providerType>".esc($dataSource['provider_type'])."</providerType>\n");
		print("    <sourceDataURI>".esc($dataSource['uri'])."</sourceDataURI>\n");
		print("  </dataSource>\n");
	}
}
print("\n</dataSources>\n");
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';
?>
