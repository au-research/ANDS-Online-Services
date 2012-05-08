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

$client = getQueryValue('client');
$searchString = getQueryValue('search');
$pNorthLimit = getQueryValue('north');
$registryObjectViewURL = getQueryValue('view');
$pFilters = getQueryValue('filters');
$labelArray = Array("type"=>"Type","object_group"=>"Providers","registry_object_class"=>"Class");

// BEGIN: Response
// =============================================================================

if($client == 'filters' && $pNorthLimit != '' && $searchString != '')
{

	$pNorthLimit = getQueryValue('north');
	$pSouthLimit = getQueryValue('south');
	$pWestLimit = getQueryValue('west');
	$pEastLimit = getQueryValue('east');
	$searchResults = searchRegistryObjectsInBound($pNorthLimit, $pSouthLimit, $pWestLimit, $pEastLimit, $searchString, '');
	if($searchResults)
	{
		print('<div class="heading" style="margin-bottom: 6px;">Results Filters</div>');
		getFilterFor('registry_object_class', $searchResults, $labelArray);
		getFilterFor('type', $searchResults, $labelArray);
    	getFilterFor('object_group', $searchResults, $labelArray);
	}
}
/*
else if($client == 'text' && $pNorthLimit == '' && $searchString == '')
{
	print('<div id="visible-infoControl">');
	print('		<table cellspacing="0" summary="Map legend layout">');
	print('			<tr>');
	print('				<td style="vertical-align: top;"><ul class="disc">To view more search options <li>enter text in the search box and click \'Search\' </li>or<li> use the Spatial Search option by drawing an area on the map above.</li></ul></td>');
	print('			</tr>');
	print('		</table>');
	print('</div>');
}
*/
else if($client == 'filters' && $pNorthLimit == '' && $searchString == '')
{
	print('<div id="visible-infoControl">');
	print('		<table cellspacing="0" summary="Map legend layout">');
	print('			<tr>');
	print('				<td style="vertical-align: top;">To view more search options <br/><img src="_images/list_item_disc.gif" alt=""/>&nbsp;&nbsp;enter text in the search box and click \'Search\' <br/>or<br/><img src="_images/list_item_disc.gif" alt=""/>&nbsp;&nbsp;use the Spatial Search option by drawing an area on the map above.<br/><br/>Note that the Search Box can be used to filter a Spatial Search by a term and the Spatial Search tool can be used to filter a term search by spatial coverage; however, not all objects in the registry have spatial coverage data.</td>');
	print('			</tr>');
	print('		</table>');
	print('</div>');
}
else if($client == 'filters' && $pNorthLimit != '')
{

	$pNorthLimit = getQueryValue('north');
	$pSouthLimit = getQueryValue('south');
	$pWestLimit = getQueryValue('west');
	$pEastLimit = getQueryValue('east');
	$searchResults = getRegistryObjectsInBound($pNorthLimit, $pSouthLimit, $pWestLimit, $pEastLimit);
	if($searchResults)
	{
		print('<div class="heading" style="margin-bottom: 6px;">Results Filters</div>');
		getFilterFor('registry_object_class', $searchResults, $labelArray);
		getFilterFor('type', $searchResults, $labelArray);
    	getFilterFor('object_group', $searchResults, $labelArray);
	}
}	
else if($client == 'filters' && $searchString != '')
{
	$classes = "collection@@service@@party@@activity";
	// TODO: remove all other classes to search only for collections! (when PUBLISHED!!)
	//$classes = "collection";
	$searchResults = searchRegistry($searchString, $classes, null, null, null, null);
	if($searchResults)
	{
		print('<div class="heading" style="margin-bottom: 6px;">Results Filters</div>');
		getFilterFor('registry_object_class', $searchResults, $labelArray, '');
		getFilterFor('type', $searchResults, $labelArray, '');
    	getFilterFor('object_group', $searchResults, $labelArray, '');
	}
}
else if($client == 'text' && $pNorthLimit != '' && $searchString != '')
{	
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$class = getFilters('registry_object_class', $pFilters);
	$classes = getFiltersAsString('registry_object_class', $pFilters);
    $pNorthLimit = getQueryValue('north');
	$pSouthLimit = getQueryValue('south');
	$pWestLimit = getQueryValue('west');
	$pEastLimit = getQueryValue('east');
	$searchResults = searchRegistryObjectsInBound($pNorthLimit, $pSouthLimit, $pWestLimit, $pEastLimit, $searchString, $classes);		
	$searchResults = filterSearchResultBy('type', $type, $searchResults);
	$searchResults = filterSearchResultBy('object_group', $objectGroup, $searchResults);				
	if( !$searchResults )
	{
		$message = "There are no results for this area";
		if( $pFilters )
		{
			$message = "There are no results for this area with these filters applied";
		}		
		print('<div id="rs-filter-headers" class="rs-filters">');
		print('<div class="rs-header" onclick="clearMap(true)" title="Clear Map Filter"><img src="_images/list_item_clear.gif" alt="" class="rs-clear" />&nbsp;&nbsp;Results restricted to selected spatial area</div>');
		print("<p class=\"rs-header\">".$message."</p>\n");
		print('</div>');		
	}		
	else
	{
		$itemsPerPage = 6;
		$pagesPerPage = 6;
		// Pagination calculations.
		$pageNumber = getPageNumber();
		$numItems = count($searchResults);
		$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);

		$startIndex = getStartIndex($pageNumber, $itemsPerPage);
		$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);

		$startPage = getStartPage($pageNumber, $pagesPerPage);
		$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
		
		$plural = "s";
		if( $numItems == 1 ){ $plural = ""; }
		print('<div id="rs-filter-headers" class="rs-filters">');
		print('<div class="rs-header" onclick="clearMap(true)" title="Clear Map Filter"><img src="_images/list_item_clear.gif" alt="" class="rs-clear" />&nbsp;&nbsp;Results restricted to selected spatial area</div>');
		if( $pFilters )
		{
			$printAnd = true;			
			printFilterHeaders('registry_object_class', $class, $labelArray, $printAnd);
			printFilterHeaders('type', $type, $labelArray, $printAnd);
			printFilterHeaders('object_group', $objectGroup, $labelArray, $printAnd);
			
		}
		print('</div>');
		print('<div class="rs-header">');
		drawRegistrySearchResultsInfo($startIndex, $endIndex, $numItems, "");
		print('</div>');
		
		// Present the results.
		for( $i=$startIndex; $i <= $endIndex; $i++ )
		{
			$registryObjectKey = $searchResults[$i]['registry_object_key'];
			$registryObjectName = getNameHTML($registryObjectKey);
			if( trim($registryObjectName) == '' )
			{
				$registryObjectName = '<i>Untitled</i> ('.esc($registryObjectKey).')';
			}
			$dataSourceTitle = $searchResults[$i]['data_source_title'];
			$registryObjectDescriptions = getRegistrySearchDescription($registryObjectKey);

			print("\n<!-- Registry Object -->\n");
			print("<div class=\"rs-item\">\n");
			print("<a href=\"".esc($registryObjectViewURL)."?key=".urlencode($registryObjectKey)."\" title=\"View details\" onclick=\"updateLayout(SHOW_CONTENT)\">".$registryObjectName."</a>\n");
			if( $registryObjectDescriptions )
			{
				print("<p class=\"rs-description\">".$registryObjectDescriptions."</p>\n");
			}
			print("</div>\n");
		}	
		// Present the pagination.
		drawRegistrySearchResultsFooter($numPages, $pageNumber, $startPage, $endPage); 
	}				
}
else if($client == 'text' && $searchString != '')
{
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$class = getFilters('registry_object_class', $pFilters);
	$classes = getFiltersAsString('registry_object_class', $pFilters);
	$searchResults = searchRegistry($searchString, $classes, null, null, null, null);		
	$searchResults = filterSearchResultBy('type', $type, $searchResults);
	$searchResults = filterSearchResultBy('object_group', $objectGroup, $searchResults);
	
	if( $pFilters )
	{
		print('<div id="rs-filter-headers" class="rs-filters">');
		$printAnd = true;
		printFilterHeaders('registry_object_class', $class, $labelArray, $printAnd);
		printFilterHeaders('type', $type, $labelArray, $printAnd);
		printFilterHeaders('object_group', $objectGroup, $labelArray, $printAnd);
		print('</div>');
	}
	
	if( !$searchResults )
	{
		$message = "There are no results for this text";
		if( $pFilters )
		{
			$message = "There are no results for this text with these filters applied";
		}
		print("<p class=\"rs-header\">".$message."</p>\n");
	}
	else
	{
		$itemsPerPage = 6;
		$pagesPerPage = 6;
		// Pagination calculations.
		$pageNumber = getPageNumber();
		$numItems = count($searchResults);
		$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);

		$startIndex = getStartIndex($pageNumber, $itemsPerPage);
		$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);

		$startPage = getStartPage($pageNumber, $pagesPerPage);
		$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
		
		$plural = "s";
		if( $numItems == 1 ){ $plural = ""; }
		
		print('<div class="rs-header">');
		drawRegistrySearchResultsInfo($startIndex, $endIndex, $numItems, "");
		print('</div>');
		
		// Present the results.
		for( $i=$startIndex; $i <= $endIndex; $i++ )
		{
			$registryObjectKey = $searchResults[$i]['registry_object_key'];
			$registryObjectName = getNameHTML($registryObjectKey);
			if( trim($registryObjectName) == '' )
			{
				$registryObjectName = '<i>Untitled</i> ('.esc($registryObjectKey).')';
			}
			$dataSourceTitle = $searchResults[$i]['data_source_title'];
			$registryObjectDescriptions = getRegistrySearchDescription($registryObjectKey);

			print("\n<!-- Registry Object -->\n");
			print("<div class=\"rs-item\">\n");
			print("<a href=\"".esc($registryObjectViewURL)."?key=".urlencode($registryObjectKey)."\" title=\"View details\" onclick=\"updateLayout(SHOW_CONTENT)\">".$registryObjectName."</a>\n");
			//print("<a href='#' id=\"".esc($registryObjectViewURL)."?key=".urlencode($registryObjectKey)."\" title=\"View details\" onclick=\"updateLayout(SHOW_CONTENT);getContent('".$registryObjectViewURL."?key=".urlencode($registryObjectKey)."&format=content')\">".$registryObjectName."</a>\n");
			if( $registryObjectDescriptions )
			{
				print("<p class=\"rs-description\">".$registryObjectDescriptions."</p>\n");
			}
			print("</div>\n");
		}	
		// Present the pagination.
		drawRegistrySearchResultsFooter($numPages, $pageNumber, $startPage, $endPage); 
	}
}// end if search results
else if($client == 'text' && $pNorthLimit != '')
{
	$objectGroup = getFilters('object_group',$pFilters);
	$type = getFilters('type', $pFilters);
	$pNorthLimit = getQueryValue('north');
	$pSouthLimit = getQueryValue('south');
	$pWestLimit = getQueryValue('west');
	$pEastLimit = getQueryValue('east');
	$class = getFilters('registry_object_class', $pFilters);
	$classes = getFiltersAsString('registry_object_class', $pFilters);
	$searchResults = getRegistryObjectsInBound($pNorthLimit, $pSouthLimit, $pWestLimit, $pEastLimit);		
	$searchResults = filterSearchResultBy('type', $type, $searchResults);
	$searchResults = filterSearchResultBy('object_group', $objectGroup, $searchResults);
	
	print('<div id="rs-filter-headers" class="rs-filters">');
	print('<div class="rs-header" onclick="clearMap(true)" title="Clear Map Filter"><img src="_images/list_item_clear.gif" alt="" class="rs-clear" />&nbsp;&nbsp;Results restricted to selected spatial area</div>');
	if( $pFilters )
	{
		$printAnd = true;			
		printFilterHeaders('registry_object_class', $class, $labelArray, $printAnd);
		printFilterHeaders('type', $type, $labelArray, $printAnd);
		printFilterHeaders('object_group', $objectGroup, $labelArray, $printAnd);
		
	}
	print('</div>');	
	if( !$searchResults )
	{
		$message = "There are no results in this area";
		if( $pFilters )
		{
			$message = "There are no results for this text with these filters applied";
		}
		print("<p class=\"rs-header\">".$message."</p>\n");

	}	
	else
	{
		$itemsPerPage = 6;
		$pagesPerPage = 6;
		// Pagination calculations.
		$pageNumber = getPageNumber();
		$numItems = count($searchResults);
		$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);

		$startIndex = getStartIndex($pageNumber, $itemsPerPage);
		$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);

		$startPage = getStartPage($pageNumber, $pagesPerPage);
		$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
		
		$plural = "s";
		if( $numItems == 1 ){ $plural = ""; }
		
		print('<div class="rs-header">');
		drawRegistrySearchResultsInfo($startIndex, $endIndex, $numItems, "");
		print('</div>');
		
		// Present the results.
		for( $i=$startIndex; $i <= $endIndex; $i++ )
		{
			$registryObjectKey = $searchResults[$i]['registry_object_key'];
			$registryObjectName = getNameHTML($registryObjectKey);
			if( trim($registryObjectName) == '' )
			{
				$registryObjectName = '<i>Untitled</i> ('.esc($registryObjectKey).')';
			}
			$dataSourceTitle = $searchResults[$i]['data_source_title'];
			$registryObjectDescriptions = getRegistrySearchDescription($registryObjectKey);

			print("\n<!-- Registry Object -->\n");
			print("<div class=\"rs-item\">\n");
			print("<a href=\"".esc($registryObjectViewURL)."?key=".urlencode($registryObjectKey)."\" title=\"View details\" onclick=\"updateLayout(SHOW_CONTENT)\">".$registryObjectName."</a>\n");
			//print("<a href='#' id=\"".esc($registryObjectViewURL)."?key=".urlencode($registryObjectKey)."\" title=\"View details\" onclick=\"updateLayout(SHOW_CONTENT);getContent('".$registryObjectViewURL."?key=".urlencode($registryObjectKey)."&format=content')\">".$registryObjectName."</a>\n");
			if( $registryObjectDescriptions )
			{
				print("<p class=\"rs-description\">".$registryObjectDescriptions."</p>\n");
			}
			print("</div>\n");
		}	
		// Present the pagination.
		drawRegistrySearchResultsFooter($numPages, $pageNumber, $startPage, $endPage); 
	}
}// end if search results

// END: Response
// =============================================================================
require '../../_includes/finish.php';

function printFilterHeaders($field, $filterArray, $labelArray, &$printAnd)
{
	if( count($filterArray) > 0 )
	{
		print('<div class="rs-header" onclick="clearFilterSet(\''.$field.'\')" title="Clear this filter set"><img src="_images/list_item_clear.gif" alt="" class="rs-clear" />&nbsp;&nbsp;');
		if( $printAnd )
		{
			print('AND ');
		}
		print($labelArray[$field]."</div>\n");
		print('<ul>');
		$printOr = false;
		while( key($filterArray) != NULL ) 
		{		
			print('<li id="'.esc($field).'@@'.esc(key($filterArray)).'" onclick="setFilter(\''.esc($field, true).'@@'.esc(key($filterArray), true).'\')" title="Remove this filter">');
			if( $printOr )
			{
				print('<i>OR</i>&nbsp;');
			}
			print(esc(key($filterArray))."</li>\n");
			$printOr = true;
			next($filterArray);
		}
		print('</ul>');
		$printAnd = true;
	}
}

function getRegistrySearchDescription($registryObjectKey, $suppressLogos = true)
{
	$truncateAt = 180;
	$html = '';
	$descriptions = getDescriptions($registryObjectKey);
	if( $descriptions )
	{
		asort($descriptions);
		foreach( $descriptions as $descr )
		{
			if ($suppressLogos && strtolower($descr['type']) == 'logo')
			{
				$html .= "";
			}
			else 
			{
				$html .= esc($descr['value'])." \n";
			}
		}
	}
	
	// Truncate the description.
	if( $html )
	{
		if( strlen($html) > $truncateAt )
		{
			$len = $truncateAt;
			if( strpos($html, " ", $truncateAt) !== false )
			{
				$len = strpos($html, " ", $truncateAt);
			}
			$html = substr($html, 0, $len);
			if( strlen($html) >= $len )
			{
				$html .= "...";
			}
		}
	}
	
	return $html;
}


function drawRegistrySearchResultsInfo($startIndex, $endIndex, $numItems, $additionalText)
{
	print("Results ".($startIndex+1)." to ".($endIndex+1)." of ".$numItems.$additionalText);
}

function drawRegistrySearchResultsFooter($numPages, $pageNumber, $startPage, $endPage)
{
	if( $numPages > 1 )
	{
		print("<p class=\"rs-footer\">");
		drawRegistrySearchPagination($numPages, $pageNumber, $startPage, $endPage);
		print("</p>\n");			
	}		
}

function drawRegistrySearchPagination($numPages, $pageNumber, $startPage, $endPage)
{

	if( $numPages > 1 )
	{
		print("&nbsp;");
		
		if( $pageNumber > 1 )
		{
			print("<span class=\"rs-page-pic\" onclick=\"runSearch(1)\" title=\"First page (1)\"><img src=\"".gPAG_CONTROL_PATH."first.gif\" alt=\"First page\" width=\"14\" height=\"12\" /></span>\n");
			print("<span class=\"rs-page-pic\" onclick=\"runSearch(".($pageNumber-1).")\" title=\"Previous page (".($pageNumber-1).")\"><img src=\"".gPAG_CONTROL_PATH."prev.gif\" alt=\"Previous page\" width=\"12\" height=\"12\" /></span>&nbsp;\n");
		}
		else
		{
			print("<span class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."first_disabled.gif\" alt=\"First page\" width=\"14\" height=\"12\" /></span>\n");
			print("<span class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."prev_disabled.gif\" alt=\"Previous page\" width=\"12\" height=\"12\" /></span>&nbsp;\n");
		}
		
		for( $i=$startPage; $i <= $endPage; $i++ )
		{
			if( $i == $pageNumber )
			{
				print("<span class=\"rs-current-page\">$pageNumber</span>\n");
			}
			else
			{
				print("<span class=\"rs-page-link\" onclick=\"runSearch(".$i.")\" title=\"Page $i\">$i</span>\n");
			}
			/*
			if( $i < $endPage )
			{
				print(" | ");
			}
			*/
		}
		
		if( $pageNumber < $numPages )
		{
			print("&nbsp;<span class=\"rs-page-pic\" onclick=\"runSearch(".($pageNumber+1).")\" title=\"Next page (".($pageNumber+1).")\"><img src=\"".gPAG_CONTROL_PATH."next.gif\" alt=\"Next page\" width=\"12\" height=\"12\" /></span>\n");
			print("<span class=\"rs-page-pic\" onclick=\"runSearch(".($numPages).")\" title=\"Last page ($numPages)\"><img src=\"".gPAG_CONTROL_PATH."last.gif\" alt=\"Last page\" width=\"14\" height=\"12\" /></span>\n");
		}
		else
		{
			print("&nbsp;<span class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."next_disabled.gif\" alt=\"Next page\" width=\"12\" height=\"12\" /></span>\n");
			print("<span class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."last_disabled.gif\" alt=\"Last page\" width=\"14\" height=\"12\" /></span>\n");
		}
		
		print("&nbsp;");
	}	
}


function getFilterFor($filterField, $searchResults, $labelArray)
{
	$filterArray = Array();
	$nSize = count($searchResults);
	for($i = 0 ; $i < $nSize; $i++)
    {
	    if(array_key_exists($searchResults[$i][$filterField],$filterArray))
	    {	
			$filterArray[$searchResults[$i][$filterField]]++;
	    }
	    else
	    {
	    	$filterArray[$searchResults[$i][$filterField]] = 1;    	
	    }
    }
    print('<div class="rs-header" onclick="clearFilterSet(\''.$filterField.'\')" title="Clear this filter set">');
    print('<img id="clear-'.$filterField.'" src="_images/list_item_clear_grey.gif" alt="" class="rs-clear" />&nbsp;&nbsp;');
    print($labelArray[$filterField]."</div>\n");
	print('<ul>');
	while( key($filterArray) != NULL ) 
	{
		print('<li id="'.esc($filterField).'@@'.esc(key($filterArray)).'" onclick="setFilter(\''.esc($filterField, true).'@@'.esc(key($filterArray), true).'\')" title="Add this filter">');
		print(esc(key($filterArray))."&nbsp;(".esc(current($filterArray)).")</li>");
		next($filterArray);
	}
	print('</ul>');

}

function getFilters($field, $pFilters)
{
	$filterArray = Array();
	$tok = strtok($pFilters, "||");
	while ($tok !== FALSE)
	{
		$keyValue = explode("@@",$tok);
		if($keyValue[0] == $field)
		{
		  $filterArray[$keyValue[1]] = 'set';			
		} 
	  	$tok = strtok("||");
	}	
	return $filterArray;
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
	if( count($filterByArray) > 0 )
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