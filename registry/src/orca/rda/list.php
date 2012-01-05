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
// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 10*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");

$group = getQueryValue('group');
$class = getQueryValue('class');

$searchGroup = null;
$searchClass = null;

$haveGroup = false;
$haveClass = false;

$contentTitle = '';
$className = '';

$classNames = array('Collection' => 'Collections',
                    'Party'      => 'Parties',
                    'Service'    => 'Services',
                    'Activity'   => 'Activities');

// Check and validate the class string.
if( isset($classNames[$class]) )
{
	// Set the title for this class.
	$className = $classNames[$class];
	$searchClass = $class;
	$haveClass = true;
}

// Check and validate the group string.
if( $group )
{
	$objectGroups = getObjectGroups();
	if( $objectGroups )
	{
		foreach( $objectGroups as $objectGroup  )
		{
			if( $group == $objectGroup['object_group'] )
			{
				$searchGroup = $group;
				$haveGroup = true;
				break;
			}
		}
	}
}

$path = '<a href="index.php">Home</a> &gt;'."\n";

// ------------------------------------------------
// Group and Class
// ------------------------------------------------
if( $haveGroup && $haveClass )
{
	$contentTitle = esc($group).'&mdash;'.$className;
	$path .= '<a href="list.php?group='.esc(urlencode($group)).'">'.esc($group).'</a> &gt;'."\n";	
}
// ------------------------------------------------
// Class only
// ------------------------------------------------
if( !$haveGroup && $haveClass )
{
	$contentTitle = $className;	
}
// ------------------------------------------------
// Group only
// ------------------------------------------------
if( $haveGroup && !$haveClass )
{
	$contentTitle = esc($group);
	$searchClass = "collection@@service@@party@@activity";
}
// ------------------------------------------------

$pageTitle = 'Research Data Australia&mdash;'.$contentTitle;

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '_includes/header.php';
// BEGIN: Page Content
// =============================================================================

print('<h1>'.$contentTitle.'</h1>');

// ------------------------------------------------
// Group only
// ------------------------------------------------
if( $haveGroup && !$haveClass )
{
	// List by class.
	print("<ul>\n");
	foreach( $classNames as $key => $value )
	{
		$num = getRegistryObjectCount(null, $searchGroup, $key);
		if( $num > 0 )
		{
			print('<li><a href="list.php?group='.esc(urlencode($searchGroup)).'&amp;class='.esc(urlencode($key)).'">'.$value.' ('.$num.')</a>&nbsp;</li>'."\n");
		}
	}
	print("</ul>\n");	
}
// ------------------------------------------------
// Class or Group and Class
// ------------------------------------------------
else
{
	// List the records.
	$results = filterRegistry('', $searchClass, $searchGroup);
	if( $results )
	{
		// Pagination settings.
		$itemsPerPage = 12;
		$pagesPerPage = 12;
		
		// Pagination calculations.
		$pageNumber = getPageNumber();
		$numItems = count($results);
		$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);

		$startIndex = getStartIndex($pageNumber, $itemsPerPage);
		$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);

		$startPage = getStartPage($pageNumber, $pagesPerPage);
		$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
		
		$plural = "s";
		if( $numItems == 1 ){ $plural = ""; }
		
		print('<div class="rs-header" style="margin: 6px 0px 0px 0px;">');
		drawRDAListInfo($startIndex, $endIndex, $numItems, "");
		print('</div>');	
		
		for( $i=$startIndex; $i <= $endIndex; $i++ )
		{
			$registryObject = $results[$i];
			$registryObjectKey = $registryObject['registry_object_key'];
			$registryObjectName = getNameHTML($registryObjectKey, '');
			if( !$registryObjectName )
			{
				$registryObjectName = '<i>Untitled</i>';
			}
			$registryObjectDescriptions = getRDAListDescription($registryObjectKey);
		
		
			print("\n<!-- Registry Object -->\n");
			print("<div class=\"rs-item\">\n");
			print('<a href="view.php?key='.esc(urlencode($registryObjectKey)).'" title="View details">'.$registryObjectName.'</a>&nbsp;');
			if( $registryObjectDescriptions )
			{
				print("<p class=\"description\">".$registryObjectDescriptions."</p>\n");
			}
			print("</div>\n");

		}	
		
		// Pagination data.
		$uri = "list.php?";
		$uri .= "group=".urlencode($group);
		$uri .= "&class=".urlencode($class);
		
		// Present the pagination.
		drawRDAListFooter($numPages, $pageNumber, $startPage, $endPage, $uri); 
	}	
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '_includes/footer.php';
require '../../_includes/finish.php';


function getRDAListDescription($registryObjectKey, $suppressLogos = true)
{
	$truncateAt = 400;
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
			else if  (strtolower($descr['type']) == 'full'||strtolower($descr['type']) == 'brief')
			{
				$html .= esc($descr['value'])." \n";
			}
		}
		foreach( $descriptions as $descr )
		{
			if ($suppressLogos && strtolower($descr['type']) == 'logo')
			{
				$html .= "";
			}
			else if  (strtolower($descr['type']) == 'rights'||strtolower($descr['type']) == 'accessRights')
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

function drawRDAListInfo($startIndex, $endIndex, $numItems, $additionalText)
{
	print("Records ".($startIndex+1)." to ".($endIndex+1)." of ".$numItems.$additionalText.".");
}

function drawRDAListFooter($numPages, $pageNumber, $startPage, $endPage, $uri)
{
	if( $numPages > 1 )
	{
		print("<p class=\"rs-footer\">");
		drawRDAListPagination($numPages, $pageNumber, $startPage, $endPage, $uri);
		print("</p>\n");			
	}		
}

function drawRDAListPagination($numPages, $pageNumber, $startPage, $endPage, $uri)
{
	if( $numPages > 1 )
	{
		print("&nbsp;");
		
		if( $pageNumber > 1 )
		{
			print("<a class=\"rs-page-pic\" href=\"".esc("$uri&page=1")."\" title=\"First page (1)\"><img src=\"".gPAG_CONTROL_PATH."first.gif\" alt=\"First page\" width=\"14\" height=\"12\" /></a>\n");
			print("<a class=\"rs-page-pic\" href=\"".esc("$uri&page=".($pageNumber-1))."\" title=\"Previous page (".($pageNumber-1).")\"><img src=\"".gPAG_CONTROL_PATH."prev.gif\" alt=\"Previous page\" width=\"12\" height=\"12\" /></a>&nbsp;\n");
		}
		else
		{
			print("<a class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."first_disabled.gif\" alt=\"First page\" width=\"14\" height=\"12\" /></a>\n");
			print("<a class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."prev_disabled.gif\" alt=\"Previous page\" width=\"12\" height=\"12\" /></a>&nbsp;\n");
		}
		
		for( $i=$startPage; $i <= $endPage; $i++ )
		{
			if( $i == $pageNumber )
			{
				print("<a class=\"rs-current-page\">$pageNumber</a>\n");
			}
			else
			{
				print("<a class=\"rs-page-link\" href=\"".esc("$uri&page=".$i)."\" title=\"Page $i\">$i</a>\n");
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
			print("&nbsp;<a class=\"rs-page-pic\" href=\"".esc("$uri&page=".($pageNumber+1))."\" title=\"Next page (".($pageNumber+1).")\"><img src=\"".gPAG_CONTROL_PATH."next.gif\" alt=\"Next page\" width=\"12\" height=\"12\" /></a>\n");
			print("<a class=\"rs-page-pic\" href=\"".esc("$uri&page=".($numPages))."\" title=\"Last page ($numPages)\"><img src=\"".gPAG_CONTROL_PATH."last.gif\" alt=\"Last page\" width=\"14\" height=\"12\" /></a>\n");
		}
		else
		{
			print("&nbsp;<a class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."next_disabled.gif\" alt=\"Next page\" width=\"12\" height=\"12\" /></a>\n");
			print("<a class=\"rs-page-pic\"><img src=\"".gPAG_CONTROL_PATH."last_disabled.gif\" alt=\"Last page\" width=\"14\" height=\"12\" /></a>\n");
		}
		
		print("&nbsp;");
	}	
}
?>