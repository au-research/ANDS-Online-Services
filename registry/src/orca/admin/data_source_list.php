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

$searchString = getQueryValue('search');
$action = getQueryValue('action');

// Execute the search.
$rawResults = getDataSources(null, $searchString);
$searchResults = array();

// Check the record owners.
if( $rawResults )
{
	foreach( $rawResults as $dataSource )
	{
		if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_ADMIN()) )
		{
			$searchResults[count($searchResults)] = $dataSource;
		}		
	}
}

// If there is only one entry then just go straight to the detailed view.
if( $searchResults && count($searchResults) === 1 )
{
	responseRedirect("data_source_view.php?data_source_key=".urlencode($searchResults[0]['data_source_key']));
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<h3>Data Sources</h3>
<form id="searchform" action="data_source_list.php" method="get">
<div>
<input type="text" size="45" maxlength="255" name="search" value="<?php printSafe(getQueryValue('search')) ?>" />&nbsp;<input type="submit" name="action" value="Filter" /><br />
</div>
</form>

<?php 
if( !$searchResults )
{
	print("<p>No Data sources were returned.</p>\n");
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
		$dataSourceKey = $searchResults[$i]['data_source_key'];
		$dataSourceTitle = $searchResults[$i]['title'];
		$uri = $searchResults[$i]['uri'];
		$providerType = $gORCA_PROVIDER_TYPES[$searchResults[$i]['provider_type']];
		$harvestMethod = $gORCA_HARVEST_METHODS[$searchResults[$i]['harvest_method']];
		
		$harvestRequests = getHarvestRequests(null, $dataSourceKey);
		$harvestStatuses = '';
		if( $harvestRequests )
		{
			foreach( $harvestRequests as $key => $harvestRequest )
			{
				$harvestStatuses .= $harvestRequest['status'];
				if( $key < (count($harvestRequests)-1) )
				{
					$harvestStatuses .= '; ';
				}
			}
		}
		
		$numRegistryObjects = getRegistryObjectCount($dataSourceKey);
		
		print("<!-- Data Source -->\n");
		print("<p class=\"resultListItem\">\n");
		print("<a href=\"data_source_view.php?data_source_key=".urlencode($dataSourceKey)."\" title=\"Click to view details\">".esc($dataSourceTitle).'</a>'."\n");
		print('<br />'."<span class=\"resultListItemLabel\">Published Records:</span> ".esc($numRegistryObjects).' <a href="../search.php?source_key='.esc(urlencode($dataSourceKey)).'&amp;collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;search=&amp;action=Search">List Records</a>'."\n");
		print('<br />'."<span class=\"resultListItemLabel\">Key:</span> ".esc($dataSourceKey)."\n");
		print('<br />'."<span class=\"resultListItemLabel\">URI:</span> ".esc($uri)."\n");
		print('<br />'."<span class=\"resultListItemLabel\">Provider Type:</span> ".esc($providerType)."\n");
		print('<br />'."<span class=\"resultListItemLabel\">Harvest Method:</span> ".esc($harvestMethod)."\n");
		if( $harvestStatuses )
		{
			print('<br />'."<span class=\"resultListItemLabel\">Harvest Statuses:</span> ".esc($harvestStatuses)."\n");
		}
		print("</p>\n");
	}
	
	// Pagination data.
	$uri = "data_source_list.php?";
	$uri .= "search=".urlencode($searchString);
	$uri .= "&action=".urlencode($action);
	
	// Present the pagination.
	drawResultsFooter($numPages, $pageNumber, $startPage, $endPage, $uri);

}// end if search results


// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>