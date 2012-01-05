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
if( userIsORCA_ADMIN() )
{
	$numRegistryObjects = getRegistryObjectCount(null, null, null, null);
}
else
{
	$numRegistryObjects = getRegistryObjectCount(null, null, null, PUBLISHED);
}

$searchString = getQueryValue('search');
$dataSourceKey = '';
$objectGroup = '';
$status = '';
$collections = 'collection';
$services = 'service';
$parties = 'party';
$activities = 'activity';

if( isset($_GET['search']) )
{
	$dataSourceKey = getQueryValue('source_key');
	$objectGroup = getQueryValue('object_group');
	$status = trim(getQueryValue('status'));
	$collections = getQueryValue('collections');
	$services = getQueryValue('services');
	$parties = getQueryValue('parties');
	$activities = getQueryValue('activities');
}
$action = getQueryValue('action');

// Buffer output for this page so that the 
// wcPleaseWait dialog remains active for the duration of the search.
ob_start();

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
$searchTitle = 'Search the Collections Registry ('.$numRegistryObjects.' records)';
if( userIsORCA_SOURCE_ADMIN() || userIsORCA_ADMIN() )
{
	$searchTitle = 'Administrative '.$searchTitle;
}
?>
<h3><?php printSafe($searchTitle)?></h3>


<div>
<?php

if( userIsORCA_SOURCE_ADMIN() || userIsORCA_ADMIN() )
{
	$dataSources = getDataSources(null, null);
	print('<select id= "source_key" name="source_key" style="margin: 2px; margin-left: 0px;">'."\n");
	print('  <option value="All">All Sources</option>'."\n");
	if( $dataSources )
	{
		foreach( $dataSources as $source )
		{
			$selected = "";
			if( $source['data_source_key'] == $dataSourceKey ){ $selected = ' selected="selected"'; }
			print('  <option value="'.esc($source['data_source_key']).'"'.$selected.'>'.esc($source['title']).'</option>'."\n");
		}
	}
	print('</select><br />'."\n");
}

$objectGroups = getObjectGroups();
print('<select id ="object_group" name="object_group" style="margin: 2px; margin-left: 0px;">'."\n");
print('  <option value="All">All Groups</option>'."\n");
if( $objectGroups )
{
	foreach( $objectGroups as $group )
	{
		$selected = "";
		if( $group['object_group'] == $objectGroup ){ $selected = ' selected="selected"'; }
		print('  <option value="'.esc($group['object_group']).'"'.$selected.'>'.esc($group['object_group']).'</option>'."\n");
	}
}
print('</select><br />'."\n");

if( userIsORCA_ADMIN() )
{
	$statuses = getStatuses();
	print('<select id="status" name="status" style="margin: 2px; margin-left: 0px;">'."\n");
	print('  <option value="All">All Statuses</option>'."\n");
	if( $statuses )
	{
		foreach( $statuses as $row)
		{
			$thisStatus = trim($row['status']);
			$selected = "";
			if( $thisStatus == $status ){ $selected = ' selected="selected"'; }
			print('  <option value="'.esc($thisStatus).'"'.$selected.'>'.esc($thisStatus).'</option>'."\n");
		}
	}
	print('</select><br />'."\n");
}

?>
<?php setChosenFromValue($collections, 'collection', gITEM_CHECK) ?>
<input type="checkbox" id="collections" name="collections" value="collection;"<?php print $gChosen ?> /><label for="collections">Collections</label>&nbsp;&nbsp;
<?php setChosenFromValue($services, 'service', gITEM_CHECK) ?>
<input type="checkbox" id="services" name="services" value="service;"<?php print $gChosen ?> /><label for="services">Services</label>&nbsp;&nbsp;
<?php setChosenFromValue($parties, 'party', gITEM_CHECK) ?>
<input type="checkbox" id="parties" name="parties" value="party;"<?php print $gChosen ?> /><label for="parties">Parties</label>&nbsp;&nbsp;
<?php setChosenFromValue($activities, 'activity', gITEM_CHECK) ?>
<input type="checkbox" id="activities" name="activities" value="activity;"<?php print $gChosen ?> /><label for="activities">Activities</label><br />
<input type="text" size="45" maxlength="255" name="search" value="<?php printSafe(getQueryValue('search')) ?>" id="search"/>&nbsp;<input type="button" value="Search" id="solr-input"/>
<input type="hidden" id="solrUrl" value="rda/search/service"/>
<input type="hidden" value="1" id="page"/>
<br />
</div>



<div id="search-result"></div>
<?php 
if( strtoupper($action) == "SEARCH" || $searchString != '' )
{
	// Execute the search.
	$classes = "$collections@@$services@@$parties@@$activities";
	$startTime = microtime(true);

	$dskey = '';
	if( userIsORCA_SOURCE_ADMIN() || userIsORCA_ADMIN() )
	{
		$dskey = $dataSourceKey;
	}
	if( $dskey == '' ){ $dskey = null; }
	
	$gp = $objectGroup;
	if( $gp == '' ){ $gp = null; }
	
	$st = PUBLISHED;
	if( userIsORCA_ADMIN() )
	{
		$st = $status;
	}
	if( $st == '' ){ $st = null; }
	
	//http://devl.ands.org.au/workareas/minh/rda/search/service
	
	
	print("<script>doSolrSearch();</script>");
	
	/*
	 * Old ORCA Search, Uncomment if needed
	 */
	
	/*
	$searchResults = searchRegistry($searchString, $classes, $dskey, $gp, null, null, $st);
	
	
	$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);

	if( !$searchResults )
	{
		print("<p>There are no results for these search criteria.</p>\n");
	}
	else
	{
		//print("<script>doSolrSearch();</script>");
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
		$args = "search=".urlencode($searchString);
		$args .= "&source_key=".urlencode($dataSourceKey);
		$args .= "&object_group=".urlencode($objectGroup);
		$args .= "&collections=".urlencode($collections);
		$args .= "&services=".urlencode($services);
		$args .= "&parties=".urlencode($parties);
		$args .= "&activities=".urlencode($activities);
		$args .= "&status=".urlencode($status);
		
		$plural = "s";
		if( $numItems == 1 ){ $plural = ""; }
		
		print('<div class="resultListHeader" style="padding: 2px 4px 2px 4px;">');
		drawResultsInfo($startIndex, $endIndex, $numItems, " ($timeTaken seconds)");
		print("&nbsp;&nbsp;&nbsp; <a style=\"padding: 0px; margin: 0px;\" href=\"services/getRegistryObjects.php?".esc($args)."\"><img title=\"Get RIF-CS XML for the ".$numItems." record".$plural." in this result set\" style=\" vertical-align: -0.6em;\" src=\"".gORCA_IMAGE_ROOT."rifcs.gif\" alt=\"\"/></a>");
		print("<a style=\"padding: 0px; margin: 0px;\" href=\"http://".eHOST."/".eROOT_DIR."/orca/services/getRegistryObjectsKML.php?".esc($args)."\"><img title=\"Get any KML that can be derived from coverage information in the ".$numItems." record".$plural." in this result set\" style=\" vertical-align: -0.6em;\" src=\"".gORCA_IMAGE_ROOT."kml.gif\" alt=\"\" /></a>");
		print('</div>');
		
		// Present the results.
		for( $i=$startIndex; $i <= $endIndex; $i++ )
		{
			$registryObjectKey = $searchResults[$i]['registry_object_key'];
			$registryObjectName = getNameHTML($registryObjectKey, $searchString);
			if( trim($registryObjectName) == '' )
			{
				$registryObjectName = esc($registryObjectKey);
			}
			$objectGroupName = $searchResults[$i]['object_group'];
			$registryObjectClass = $searchResults[$i]['registry_object_class'];
			$registryObjectType = $searchResults[$i]['type'];
			$dataSourceTitle = $searchResults[$i]['data_source_title'];
			$registryObjectIdentifiers = getIdentifiersHTML($registryObjectKey, gORCA_HTML_LIST, $searchString);
			$registryObjectRelations = getRelationsHTML($registryObjectKey, gORCA_HTML_LIST);
			$registryObjectSubjects = getSubjectsHTML($registryObjectKey, gORCA_HTML_LIST, $searchString);
			$registryObjectDescriptions = getDescriptionsHTML($registryObjectKey, gORCA_HTML_LIST, $searchString);

			print("<!-- Registry Object -->\n");
			print("<p class=\"resultListItem\">\n");
			print("<a href=\"view.php?key=".urlencode($registryObjectKey)."\" title=\"View details\">".$registryObjectName."</a>\n");
			if( userIsORCA_ADMIN() )
			{
				$statusSpan = getRegistryObjectStatusSpan($searchResults[$i]['status']);
				print("<br /><span class=\"resultListItemLabel\">Status:</span> ".$statusSpan."\n");
			}
			
			print("<br /><span class=\"resultListItemLabel\">".$registryObjectClass.gCHAR_EMDASH."Type:</span> ".esc($registryObjectType)."\n");
			print("<br /><span class=\"resultListItemLabel\">".$registryObjectClass.gCHAR_EMDASH."Key:</span> ".highlightSearchTerm(esc($registryObjectKey), esc($searchString), gORCA_HIGHLIGHT_STARTS_WITH)."\n");
			if( userIsORCA_SOURCE_ADMIN() || userIsORCA_ADMIN() )
			{
				print("<br /><span class=\"resultListItemLabel\">Source:</span> ".esc($dataSourceTitle)."\n");
			}
			print("&nbsp;&nbsp;<span class=\"resultListItemLabel\">Group:</span> ".esc($objectGroupName)."\n");
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
		$uri = "search.php?";
		$uri .= "search=".urlencode($searchString);
		$uri .= "&source_key=".urlencode($dataSourceKey);
		$uri .= "&object_group=".urlencode($objectGroup);
		$uri .= "&action=".urlencode($action);
		$uri .= "&collections=".urlencode($collections);
		$uri .= "&services=".urlencode($services);
		$uri .= "&parties=".urlencode($parties);
		$uri .= "&activities=".urlencode($activities);
		$uri .= "&status=".urlencode($status);
		
		// Present the pagination.
		drawResultsFooter($numPages, $pageNumber, $startPage, $endPage, $uri); 

	}// end if search results
*/
	

}// end if do the search


// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';

// Send the ouput from the buffer, and end buffering.
ob_end_flush();
?>
