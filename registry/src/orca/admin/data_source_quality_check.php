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


$dataSourceKey = getQueryValue('dataSourceKey');
$itemurl = getQueryValue('item-url');

if($dataSourceKey != '' && $itemurl != '')
{	

	$transformResult = runQualityResultsforDataSource($dataSourceKey,$itemurl);
	print($transformResult);	
}
else
{
// Execute the search.
$rawResults = getDataSources(null, null);
$searchResults = array();

// Check the record owners.
if( $rawResults )
{
	foreach( $rawResults as $dataSource )
	{
		if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_ADMIN()) )
		{
			$searchResults[count($searchResults)] = $dataSource;
			//echo count($searchResults)."<br />";
		}		
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================

if( !$searchResults )
{
	print("<p>No Data sources were returned.</p>\n");
}
else
{
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<h1>Data Source Quality Check</h1>
<p><a href="http://www.ands.org.au/partner/data-source-quality-check.html">Limitations</a> of the Data Quality Check</p>
<h3>Data Sources</h3>
<div>
<select name="data_source_key" id="data_source_key">
<?php
	
	// Present the results.
	for( $i=0; $i < count($searchResults); $i++ )
	{
		$dataSourceKey = $searchResults[$i]['data_source_key'];
		$dataSourceTitle = $searchResults[$i]['title'];
		$numRegistryObjects = getRegistryObjectCount($dataSourceKey);		
		print("<option value=\"".urlencode($dataSourceKey)."\">".esc($dataSourceTitle)."(".esc($numRegistryObjects).")</option>\n");
	}

print("</select>\n");
}// end if search results
print("<input type=\"button\" width=\"23px\" value=\"Check for Quality\" onclick=\"javascript:runQualityCheck();\">\n");
print("<input type=\"hidden\" id=\"data-url\" value=\"".urlencode(eAPP_ROOT."orca/services/getRegistryObjects.php?&activities=
activity&parties=party&collections=collection&services=service&source_key=")."\"/>\n");
print("<input type=\"hidden\" id=\"item-url\" value=\"".urlencode(eAPP_ROOT."orca/view.php?key=")."\"/>\n");
print("<input type=\"hidden\" id=\"qTestURL\" value=\"".esc(eAPP_ROOT)."orca/admin/data_source_quality_check.php\"/>\n");
print("<div id=\"qualityCheckresult\">&nbsp;</div>\n");
print("</div>");

	



// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
}
require '../../_includes/finish.php';
?>