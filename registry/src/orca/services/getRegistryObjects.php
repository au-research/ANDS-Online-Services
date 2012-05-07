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

$searchString = getQueryValue('search');
$dataSourceKey = getQueryValue('source_key');
$objectGroup = getQueryValue('object_group');
$collections = getQueryValue('collections');
$services = getQueryValue('services');
$parties = getQueryValue('parties');
$activities = getQueryValue('activities');
$createdBeforeInclusive = getQueryValue('modified_before_equals');
$createdAfterInclusive = getQueryValue('modified_after_equals');

if( $dataSourceKey == '' ){ $dataSourceKey = null; }
if( $objectGroup == '' ){ $objectGroup = null; }

$classes = "$collections@@$services@@$parties@@$activities";

$createdBeforeInclusive = getFormattedDatetimeWithMask($createdBeforeInclusive, eDCT_FORMAT_ISO8601_DATETIMESEC_UTC);
$createdAfterInclusive = getFormattedDatetimeWithMask($createdAfterInclusive, eDCT_FORMAT_ISO8601_DATETIMESEC_UTC);

$registryObjects = searchRegistry($searchString, $classes, $dataSourceKey, $objectGroup, $createdBeforeInclusive, $createdAfterInclusive);
$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
// BEGIN: XML Response
// =============================================================================
$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
if( $registryObjects )
{
	foreach( $registryObjects as $registryObject )
	{
		$rifcs .= getRegistryObjectXML($registryObject['registry_object_key']);
	
	}
}
$rifcs .= "</registryObjects>\n";
// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!
$rifc2 = transformToRif2XML($rifcs);
print $rifc2;
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';
?>