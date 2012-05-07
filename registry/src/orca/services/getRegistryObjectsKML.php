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
header("Content-Type: application/kml; charset=UTF-8", true);
header("Content-Disposition: inline; filename=registryObjects.kml", true);
$client = getQueryValue('client');
$pSpatial = getQueryValue('spatial');
$searchString = getQueryValue('search');
$limit = getQueryValue('limit');
$pFilters = getQueryValue('filters');
$pNorthLimit = getQueryValue('north');
$pSouthLimit = getQueryValue('south');
$pWestLimit = getQueryValue('west');
$pEastLimit = getQueryValue('east');



if( $limit )
{
	$limit = (int)$limit;
	if( $limit < 1 )
	{
		$limit = 0;
	}
}
if($client == 'map' && $pNorthLimit != '' && $searchString != '')
{	
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$class = getFilters('registry_object_class', $pFilters);
	$classes = getFiltersAsString('registry_object_class', $pFilters);
	$registryObjects = searchRegistryObjectsInBound($pNorthLimit, $pSouthLimit, $pWestLimit, $pEastLimit, $searchString, $classes);		
	$registryObjects = filterSearchResultBy('registry_object_class', $class, $registryObjects);			
	$registryObjects = filterSearchResultBy('type', $type, $registryObjects);
	$registryObjects = filterSearchResultBy('object_group', $objectGroup, $registryObjects);		
}
else if($client == 'map' && $searchString != '')
{	
	$limit = 25;
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$class = getFilters('registry_object_class', $pFilters);
	$classes = getFiltersAsString('registry_object_class', $pFilters);
	$registryObjects = searchRegistry($searchString, $classes, null, null, null, null);
	$registryObjects = filterSearchResultBy('registry_object_class', $class, $registryObjects);			
	$registryObjects = filterSearchResultBy('type', $type, $registryObjects);
	$registryObjects = filterSearchResultBy('object_group', $objectGroup, $registryObjects);		
}
else if($client == 'map' && $pNorthLimit != '')
{	
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$class = getFilters('registry_object_class', $pFilters);
	$registryObjects = getRegistryObjectsInBound($pNorthLimit, $pSouthLimit, $pWestLimit, $pEastLimit);
	$registryObjects = filterSearchResultBy('registry_object_class', $class, $registryObjects);			
	$registryObjects = filterSearchResultBy('type', $type, $registryObjects);
	$registryObjects = filterSearchResultBy('object_group', $objectGroup, $registryObjects);		
}
else if($client == 'map' && $pNorthLimit == '' && $searchString == '')
{	
	$registryObjects = null;	
}
else
{
	$searchString = getQueryValue('search');
	$dataSourceKey = getQueryValue('source_key');
	$objectGroup = getQueryValue('object_group');
	$collections = getQueryValue('collections');
	$services = getQueryValue('services');
	$parties = getQueryValue('parties');
	$activities = getQueryValue('activities');
	$modifiedBeforeInclusive = getQueryValue('modified_before_equals');
	$modifiedAfterInclusive = getQueryValue('modified_after_equals');

	$pFilters = getQueryValue('filters');
	if( $dataSourceKey == '' ){ $dataSourceKey = null; }
	if( $objectGroup == '' ){ $objectGroup = null; }
	
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$classes = getFiltersAsString('registry_object_class', $pFilters);	
	$modifiedBeforeInclusive = getFormattedDatetimeWithMask($modifiedBeforeInclusive, eDCT_FORMAT_ISO8601_DATETIMESEC_UTC);
	$modifiedAfterInclusive = getFormattedDatetimeWithMask($modifiedAfterInclusive, eDCT_FORMAT_ISO8601_DATETIMESEC_UTC);	
	$registryObjects = searchRegistry($searchString, $classes, null, null, null, null);
	$registryObjects = filterSearchResultBy('type', $type, $registryObjects);
	$registryObjects = filterSearchResultBy('object_group', $objectGroup, $registryObjects);
	
}
// BEGIN: XML Response
// =============================================================================
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
print('<kml xmlns="http://earth.google.com/kml/2.1"'."\n");
print('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n");
print('xsi:schemaLocation="http://earth.google.com/kml/2.1  http://code.google.com/apis/kml/schema/kml21.xsd">'."\n");
print('<Document>'."\n");
print('	<name>Registry Objects Coverage</name>'."\n");
print('	<open>1</open>'."\n");
print(getKMLStyles());
if( $registryObjects )
{
	$pageNumber = getPageNumber();
	if( $limit )
	{
		$i = 0;
		$j = 0;
		while( $i < $limit && $j < count($registryObjects) )
		{
			if( $kml = getRegistryObjectKML($registryObjects[$j]['registry_object_key']) )
			{
				print($kml);
				$i++;
			}
			$j++;
		}
	}
	else if($pageNumber)
	{
			$itemsPerPage = 6;
			$numItems = count($registryObjects);
			$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);	
			$startIndex = getStartIndex($pageNumber, $itemsPerPage);
			$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);
			for( $i=$startIndex; $i <= $endIndex; $i++ )
			{
				print(getRegistryObjectKML($registryObjects[$i]['registry_object_key']));
			}
	}
}
print('</Document>'."\n");
print("</kml>\n");
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';


function getFilters($field, $pFilters)
{
	$facetArray = Array();
	$tok = strtok($pFilters, "||");
	while ($tok !== FALSE)
	{
		$keyValue = explode("@@",$tok);
		if($keyValue[0] == $field)
			{
			  $facetArray[$keyValue[1]] = 'set';			
			} 
	  	$tok = strtok("||");
	}	
	return $facetArray;
}

function getFiltersAsString($field, $pFilters)
{
	$filterString = '';
	$tok = strtok($pFilters, "||");
	while ($tok !== FALSE)
	{
		$keyValue = explode("@@",$tok);
		if($keyValue[0] == $field)
		{
		  	$filterString .= strtolower($keyValue[1]).'@@';						
		} 
	  	$tok = strtok("||");
	}
	return $filterString;
}

function filterSearchResultBy($field, $filterByArray, $searchResults)
{
	$altArray = Array();
	$j = 0;
	if(count($filterByArray) > 0)
	{
	for( $i=0; $i < count($searchResults); $i++ )
		{
		if(array_key_exists($searchResults[$i][$field],$filterByArray))
			{
			$altArray[$j++] = $searchResults[$i];
			}	
		}		
		return $altArray;
	}
	else
	{
		return $searchResults;
	}	
}
?>