<?php
/*
Copyright 2008 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

********************************************************************************
       Object: /pids/index.php
   Written By: James Blanden
 Created Date: 27 October 2008
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================
 James Blanden        03/12/2008    List first property value.




*******************************************************************************/
// Include required files and initialisation.
require '../_includes/init.php';
require 'pids_init.php';

// Page processing
// -----------------------------------------------------------------------------

$handle = getPostedValue('handle');
$action = getPostedValue('action');

if( strtoupper($action) == "VIEW" )
{
	$serviceName = "getHandle";
	$parameters = "handle=".urlencode($handle);
	
	$response = pidsRequest($serviceName, $parameters);
	if( $response )
	{
		if( pidsGetResponseType($response) == gPIDS_RESPONSE_SUCCESS )
		{
			$handle = pidsGetHandleValue($response);
			responseRedirect('view.php?handle='.urlencode($handle));
		}
	}
}

$serviceName = "listHandles";
$response = pidsRequest($serviceName, '');

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<h3>My Identifiers</h3>
<form id="searchform" action="index.php" method="post" onsubmit="wcPleaseWait(true, 'Processing...')">
<div>
<input type="text" size="32" maxlength="255" name="handle" value="<?php printSafe(getQueryValue('handle')) ?>" />&nbsp;<input type="submit" name="action" value="View" /><br />
</div>
</form>
<?php 
if( $response )
{
	if( pidsGetResponseType($response) == gPIDS_RESPONSE_SUCCESS )
	{
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$identifiers = $responseDOMDoc->getElementsByTagName("identifier");

			if( !$identifiers )
			{
				print("<p>You have no identifers.</p>\n");
			}
			else
			{
				$listData = array();
				$i = 0;
				foreach( $identifiers as $identifier )
				{
					$listData[$i]['handle'] = $identifier->getAttribute("handle");
					$i++;
				}
				sort($listData);
				
				// Pagination settings.
				$uri = "index.php?";
				
				$itemsPerPage = 10;
				$pagesPerPage = 20;
				
				// Pagination calculations.
				$pageNumber = getPageNumber();
				$numItems = count($listData);
				$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);
		
				$startIndex = getStartIndex($pageNumber, $itemsPerPage);
				$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);
		
				$startPage = getStartPage($pageNumber, $pagesPerPage);
				$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
				?>
				<table summary="" class="rowNumbers">
				   <thead>
				      <tr>
				         <td style="border-bottom: 0px;"></td>
				         <td colspan="3"></td>
				      </tr>
				      <tr>
						<td style="border: 0px; background: transparent;"></td>
         				<td class="resultListHeader" style="border-right: 1px solid #dddddd;" colspan="3"><?php drawResultsInfo($startIndex, $endIndex, $numItems, ""); print('&nbsp;&nbsp;'); drawPagination($numPages, $pageNumber, $startPage, $endPage, $uri); ?></td>
				      </tr>
				   </thead>
				   <tbody>
				      <tr>
				         <th style="border-left: 0px;"></th>
				         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Handle</th>
				         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Resolver Link</th>
				         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">First Property Value</th>
				      </tr>
				      <?php
				      for( $i=$startIndex; $i<=$endIndex; $i++ )
				      {
				      	$num = $i + 1;
				      	$handle = $listData[$i]['handle'];
						$firstPropertyValue = pidsGetHandleListDescription($handle);

				      	print("<tr valign=\"top\">\n");
				      	print("  <td>".esc($num)."</td>\n");
				      	print('  <td align="left" style="padding-right: 5px;"><a href="view.php?handle='.esc($handle).'" title="View this identifier">'.esc($handle)."</a></td>\n");
				      	print('  <td align="left" style="padding-right: 5px;"><a href="'.esc(pidsGetHandleURI($handle)).'" class="external" title="Resolve this handle">'.esc(pidsGetHandleURI($handle)).'<img class="external" src="'.gPIDS_IMAGE_ROOT.'external_link.gif" alt="" /></a></td>'."\n");
				      	print('  <td align="left" style="padding-right: 5px;">'.esc($firstPropertyValue)."</td>\n");
				      	print("</tr>\n");
				      }
				      ?>
				   </tbody>
				</table>
				<?php
			}
		}
	}
	else
	{
		print('<p class="'.gERROR_CLASS.'">There was a problem with the request [2].</p>');
	}
}
else
{	
	print('<p class="'.gERROR_CLASS.'">There was an error with the service [1].</p>');
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
