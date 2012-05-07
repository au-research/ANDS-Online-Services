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
$registryObjects = null;

if( $searchString )
{
	$registryObjects = searchRegistry($searchString, '',  null, null, null, null);
}

$itemLinkBaseURL = eAPP_ROOT.'orca/view.php?key=';

$totalResults = 0;
if( $registryObjects )
{
	$totalResults = count($registryObjects);
}


// BEGIN: XML Response
// =============================================================================
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
print('<rss version="2.0" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">'."\n");
print('  <channel>'."\n");
print('    <title>'.esc(eINSTANCE_TITLE_SHORT.' '.eAPP_TITLE)." Collections Registry Search Results</title>\n");
print('    <link>'.eAPP_ROOT.'orca/search.php</link>'."\n");
print('    <description>Search results for '.esc(eINSTANCE_TITLE_SHORT.' '.eAPP_TITLE)." Collections Registry collection, service, party, and activity metadata</description>\n");
print('    <opensearch:totalResults>'.$totalResults.'</opensearch:totalResults>'."\n");
print('    <opensearch:Query role="request" searchTerms="'.esc($searchString).'" />'."\n");

if( $registryObjects )
{
	foreach( $registryObjects as $registryObject )
	{
		$registryObjectKey = $registryObject['registry_object_key'];
		$registryObjectName = getNameRSS($registryObjectKey);
		$registryObjectClass = $registryObject['registry_object_class'];
		$registryObjectType = $registryObject['type'];
		$registryObjectDescriptions = getDescriptionsRSS($registryObjectKey);
		
		print('    <item>'."\n");
		print('      <guid>'.esc($registryObjectKey).'</guid>'."\n");
		print('      <title>'.$registryObjectName.'</title>'."\n");
		print('      <link>'.$itemLinkBaseURL.esc($registryObjectKey).'</link>'."\n");
		print('      <category>'.esc("$registryObjectClass:$registryObjectType").'</category>'."\n");
		print('      <description>'.$registryObjectDescriptions.'</description>'."\n");
		print('    </item>'."\n");
	}
}
print('  </channel>'."\n");
print("</rss>\n");
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';


// Local presentation functions.
function getNameRSS($registryObjectKey)
{
	$rss = $registryObjectKey;
	$names = getNames($registryObjectKey);
	if( $names )
	{
		$rss = '';
		for( $i = 0; $i < count($names); $i++ )
		{
			{			
				if( $i != 0 )
				{
					$rss .= ' '.gCHAR_MIDDOT.' ';
				}
				$rss .= esc($names[$i]['value']);
			}
		}
	}
	return $rss;
}

function getDescriptionsRSS($registryObjectKey)
{
	$rss = '';
	$descriptions = getDescriptions($registryObjectKey);
	if( $descriptions )
	{
		for( $i = 0; $i < count($descriptions); $i++ )
		{
			$descr = $descriptions[$i]['value'];
			$rss .= strtoupper(esc($descriptions[$i]['type'])).": ";
			$rss .= esc($descr.'&nbsp;&nbsp;&nbsp;&nbsp;');
		}
	}
	return $rss;
}
?>
