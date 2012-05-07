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



// Buffer output for this page.
ob_start();

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================

$classes = "collection";
$searchResults = searchRegistry('', $classes, 'PUBLISH_MY_DATA', null, null, null, null, getThisOrcaUserIdentity());

if( !$searchResults )
{
	?>
<div style="margin-bottom: 2em; width: 800px;">
<h2>Welcome to ANDS <i>Publish My Data</i></h2>
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;">
	Help for this application is available from the <b><a href="<?php print eAPP_ROOT.'help.php?id='.$gThisActivityID.'&amp;page='.urlencode($_SERVER['REQUEST_URI']) ?>" title="Help for this page">Help</a></b> link at the top right hand corner of the page.<br />
</p>
<p>You have no collections in the registry. To add a collection and submit it for approval use the <i><a href="collection_add.php">Publish a Collection</a></i> link available from the menu at left.</p>	
</div>
	
	
	<?php
}
else
{
	// Pagination settings.
	$uri = "index.php?";
	
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
	
	
	
	?>
	<table summary="My Collections" class="rowNumbers">
	   <thead>
	      <tr>
	         <td style="border-bottom: 0px;"></td>
	         <td colspan="5"></td>
	      </tr>
	      <tr>
			<td style="border: 0px; background: transparent;"></td>
         	<td class="resultListHeader" style="border-right: 1px solid #dddddd;" colspan="5"><?php drawResultsInfo($startIndex, $endIndex, $numItems, ""); print('&nbsp;&nbsp;'); drawPagination($numPages, $pageNumber, $startPage, $endPage, $uri); ?></td>
	      </tr>
	   </thead>
	   <tbody>
	      <tr>
	         <th style="border-left: 0px;"></th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Created/Updated</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Status</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Key</th>
	         <!--<th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Type</th>-->
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Title</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">URL</th>
	      </tr>
	      <?php
	      for( $i=$startIndex; $i<=$endIndex; $i++ )
	      {
	      	$num = $i + 1;
	      	$createdWhen = formatDateTime($searchResults[$i]['created_when']);
			$statusSpan = getRegistryObjectStatusSpan($searchResults[$i]['status']);
	      	$registryObjectKey = $searchResults[$i]['registry_object_key'];
			$registryObjectType = $searchResults[$i]['type'];
			$registryObjectName = getNameHTML($registryObjectKey);
			if( trim($registryObjectName) == '' )
			{
				$registryObjectName = esc($registryObjectKey);
			}
			
			// Get the URL from the handle service.
			$url = '';
			$urlProperty = pidsGetFirstURLProperty($registryObjectKey);
			if( $urlProperty )
			{
				$url = '<a href="'.esc($urlProperty->getAttribute("value")).'" class="external" title="Navigate to this URL">'.esc($urlProperty->getAttribute("value")).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>';
			}

	      	print("<tr id=\"row".$num."\" valign=\"top\">\n");
	      	
	      	$cellAttributes = ' onmouseover="recordOver(\'row'.$num.'\', false)" onmouseout="recordOut(\'row'.$num.'\', false)"';
	      	print("  <td".$cellAttributes.">".$num."</td>\n");
	      	
	      	$cellAttributes = ' onmouseover="recordOver(\'row'.$num.'\', false)" onmouseout="recordOut(\'row'.$num.'\', false)"';
	      	$cellAttributes .= ' title="View this record"';
	      	$cellAttributes .= ' onclick="window.location=\'collection_view.php?key='.esc(urlencode($registryObjectKey), true).'\'"';
	      	$cellAttributes .= ' class="recordLink"';
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px; white-space: nowrap;">'.esc($createdWhen)."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$statusSpan."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;"><a href="collection_view.php?key='.esc(urlencode($registryObjectKey)).'" title="View this collection">'.esc($registryObjectKey)."</a></td>\n");
	      	//print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.esc($registryObjectType)."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$registryObjectName."</td>\n");
	      	print('  <td align="left" style="padding-right: 5px;">'.$url.'</td>'."\n");
	      	print("</tr>\n");
	      }
	      ?>
	   </tbody>
	</table>
	<?php
}// end if search results

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';

// Send the ouput from the buffer, and end buffering.
ob_end_flush();
?>
