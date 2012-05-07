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
require 'orca_init.php';
// Page processing
// -----------------------------------------------------------------------------
// Get the selected values.
$letter = getQueryValue('indexletter');
$class = getQueryValue('class');


$numRegistryObjects = getRegistryObjectCount(null);

$classNames = array( 'Collection', 'Service','Party', 'Activity' );

$indexLetters = array();
if( $class )
{
	for( $i = 0x41; $i < 0x41 + 26; $i++ )
	{
		$indexLetters[] = chr($i);
	}
	$indexLetters[] = 'ZZ';
}

$startsWith = null;
if( in_array($letter, $indexLetters) )
{
	$startsWith = $letter;
}


$searchResults = null;
if( $startsWith && $class )
{
	// Begin retrieving and filtering.
	$startTime = microtime(true);
	$rawResults = filterRegistry($startsWith, $class);
	
	if( $startsWith != 'ZZ' )
	{
		// Remove any untitled records.
		$searchResults = array();
		foreach( $rawResults as $row )
		{
			if( getNameHTML($row['registry_object_key']) )
			{
				$searchResults[] = $row;
			}
		}
	}
	else
	{
		$searchResults = $rawResults;
	}

	$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);
}

// Buffer output for this page so that the 
// wcPleaseWait dialog remains active for the duration of the search.
ob_start();
// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<table summary="layout table" style="width: 100%; margin: 0px;">
<tr><td style="width: 80%; padding: 0px; border: 0px; vertical-align: top;">
<!-- BEGIN: Results -->
<?php
print("<h3>Collections Registry Index by Class ($numRegistryObjects records)</h3>");
if( !$searchResults )
{
	if( $startsWith && $class )
	{
		print("<p>There are no results for these filter criteria.</p>\n");
	}
	else
	{
		print("<p>Select a Class and Index Letter.</p>\n");
	}
}
else
{
	// Pagination settings.
	$itemsPerPage = 10;
	$pagesPerPage = 20;
	
	// Pagination calculations.
	$pageNumber = getPageNumber();
	$numItems = count($searchResults);
	$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);

	$startIndex = getStartIndex($pageNumber, $itemsPerPage);
	$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);

	$startPage = getStartPage($pageNumber, $pagesPerPage);
	$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
	
	// Present the results information.
	drawResultsHeader($startIndex, $endIndex, $numItems, "");
	
	// Present the results.
	for( $i=$startIndex; $i <= $endIndex; $i++ )
	{
		$registryObjectKey = $searchResults[$i]['registry_object_key'];
		$registryObjectName = getNameHTML($registryObjectKey);
		if( trim($registryObjectName) == '' )
		{
			$registryObjectName = '<i>Untitled</i> ('.esc($registryObjectKey).')';
		}
			
		$objectGroupName = $searchResults[$i]['object_group'];
		$registryObjectClass = $searchResults[$i]['registry_object_class'];
		$registryObjectType = $searchResults[$i]['type'];
		$registryObjectIdentifiers = getIdentifiersHTML($registryObjectKey, gORCA_HTML_LIST);	
		$registryObjectRelations = getRelationsHTML($registryObjectKey, gORCA_HTML_LIST);
		$registryObjectSubjects = getSubjectsHTML($registryObjectKey, gORCA_HTML_LIST);
		$registryObjectDescriptions = getDescriptionsHTML($registryObjectKey, gORCA_HTML_LIST);
	
		print("<!-- Registry Object -->\n");
		print("<p class=\"resultListItem\">\n");
	
		print("<a href=\"view.php?key=".urlencode($registryObjectKey)."\" title=\"View details\">".$registryObjectName."</a>\n");
		print("<br /><span class=\"resultListItemLabel\">".$registryObjectClass.gCHAR_EMDASH."Type:</span> ".esc($registryObjectType)."\n");
		print("<br /><span class=\"resultListItemLabel\">".$registryObjectClass.gCHAR_EMDASH."Key:</span> ".esc($registryObjectKey)."\n");
		print("<br /><span class=\"resultListItemLabel\">Group:</span> ".esc($objectGroupName)."\n");
	
		if( $registryObjectIdentifiers )
		{
			print("<br /><span class=\"resultListItemLabel\">Identifiers:</span> ".$registryObjectIdentifiers."\n");
		}
		if( $registryObjectRelations )
		{
			print("<br /><span class=\"resultListItemLabel\">Relations:</span> $registryObjectRelations\n");
		}
		if( $registryObjectSubjects )
		{
			print("<br /><span class=\"resultListItemLabel\">Subjects:</span> ".$registryObjectSubjects."\n");
		}
		if( $registryObjectDescriptions )
		{
			print("<br /><span class=\"resultListItemLabel\">Description:</span> ".$registryObjectDescriptions."\n");
		}
		print("</p>\n");	
	}
	
	// Pagination data.
	$queryString = $_SERVER['QUERY_STRING'];
	$queryString = str_replace('&page='.$pageNumber, '', $queryString);
	
	$uri = "registry_index.php?";
	$uri .= $queryString;
	
	// Present the pagination.
	drawResultsFooter($numPages, $pageNumber, $startPage, $endPage, $uri);

}// end if search results
?>
<!-- END: Results -->
</td><td style=" border: 0px; vertical-align: top; font-size: 0.8em;">
<form id="browseform" action="registry_index.php" method="get">
<!-- BEGIN: Filters -->
<table summary="layout table">
<?php 
drawBrowseClasses($classNames); 
drawBrowseLetters($indexLetters, $class);
?>
</table>
<!-- END: Filters -->
</form>
</td></tr>
</table>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/analytics.js"></script>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';

// Send the ouput from the buffer, and end buffering.
ob_end_flush();

function drawBrowseClasses($classNames)
{
	global $gChosen;
	
	if( $classNames )
	{
		drawBrowseHeader("Class");
		$id = 0;
		foreach( $classNames as $className )
		{
			$name = 'class';
			$key = $className;
			$title = $className;
			$num = getRegistryObjectCount(null, null, $className);
			drawBrowseItem($id, $name, $key, $title, $num);
			$id++;
		}
	}
}

function drawBrowseLetters($indexLetters, $className)
{
	global $gChosen;

	if( $indexLetters )
	{
		drawBrowseHeader("AND Index Letter");
		$id = 0;
		foreach( $indexLetters as $indexLetter )
		{
			$name = 'indexletter';
			$key = $indexLetter;
			$title = $indexLetter;
			if( $key == 'ZZ'){ $title = 'Other'; }
			$num = getRegistryObjectFilterCount($className, $indexLetter);
			drawBrowseItem($id, $name, $key, $title, $num);
			$id++;
		}
	}
}

function drawBrowseHeader($name)
{
	print("<tr>\n");
	print('<td colspan="2" style="padding-left: 0px; padding-top: 6px; border: 0px;"><b>'.esc($name)."</b></td>\n");
	print("</tr>\n");
}

function drawBrowseItem($id, $name, $key, $title, $num)
{
	global $gChosen;
	setChosen($name, $key, gITEM_CHECK);
	
	print("<tr>\n");
	print('<td style="padding: 2px; width: 10px; border: 0px; vertical-align: top;">');
	print('<input id="'.$name.'_'.$id.'" ');
	print('onclick="wcPleaseWait(true, \'Retrieving...\'); this.form.submit();" ');
	print('type="radio" name="'.$name.'" value="'.esc($key).'" style="margin: 0px;"'.$gChosen.' />');
	print("</td>\n");
	print('<td style="padding: 3px; border: 0px; vertical-align: middle;">');
	print('<label for="'.$name.'_'.$id.'">'.esc($title));
	if( $num )
	{
		print('&nbsp;<span style="color: #AAAAAA;">('.$num.')</span>');
	}
	print('</label>');
	print("</td>\n");	
	print("</tr>\n");	
}
?>
