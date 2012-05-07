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
       Object: /pids/view.php
   Written By: James Blanden
 Created Date: 27 October 2008
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================




*******************************************************************************/
// Include required files and initialisation.
require '../_includes/init.php';
require 'pids_init.php';
// Page processing
// -----------------------------------------------------------------------------

$handle = getQueryValue('handle');

// Get the handle.
$serviceName = "getHandle";
$parameters = "handle=".urlencode($handle);
$response = pidsRequest($serviceName, $parameters);

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
if( $response )
{
	if( pidsGetResponseType($response) == gPIDS_RESPONSE_SUCCESS )
	{
?>
<table class="recordTable" summary="Identifier">
	<thead>
		<tr>
			<td></td>
			<td>Identifier</td>
			<td></td>
			<td></td>
		</tr>
	</thead>
	<tbody class="recordFields">
	<?php
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			print("<tr>\n");
			print("  <td>Handle:</td>\n");
			print("  <td>".esc(pidsGetHandleValue($response))."</td>\n");
			print("  <td></td>\n");
			print("  <td></td>\n");
			print("</tr>\n");
			print("<tr>\n");
			print("  <td>Resolver Link:</td>\n");
			print("  <td>".'<a href="'.esc(pidsGetHandleURI($handle)).'" class="external" title="Resolve this handle">'.esc(pidsGetHandleURI($handle)).'<img class="external" src="'.gPIDS_IMAGE_ROOT.'external_link.gif" alt="" /></a>'."</td>\n");
			print("  <td></td>\n");
			print("  <td></td>\n");
			print("</tr>\n");

			$properties = $responseDOMDoc->getElementsByTagName("property");
			if( $properties )
			{	
				foreach( $properties as $property )
				{
					$propertyIndex = $property->getAttribute("index");
					$propertyType = $property->getAttribute("type");
					$propertyValue = $property->getAttribute("value");
					
					print("<tr>\n");
					print("  <td>".esc($propertyType).":</td>\n");
					print("  <td>".esc($propertyValue)."</td>\n");
					print('  <td style="vertical-align: middle;"><form action="edit.php?handle='.esc(urlencode($handle)).'&amp;index='.esc(urlencode($propertyIndex)).'" method="post"><div><input type="submit" class="buttonSmall" name="action" value="edit" title="Edit this property" /></div></form>'."</td>\n");
					print('  <td style="vertical-align: middle; padding-left: 0px;"><form action="delete.php?handle='.esc(urlencode($handle)).'&amp;index='.esc(urlencode($propertyIndex)).'" method="post" onsubmit="wcPleaseWait(false, \'Processing...\')"><div><input type="submit" class="buttonSmall" name="action" value="delete" title="Delete this property" /></div></form>'."</td>\n");
					print("</tr>\n");
				}
			}	
		}
	?>
	</tbody>
	<tbody>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td colspan="2"><form action="add.php?handle=<?php printSafe(urlencode($handle)); ?>" method="post"><div><input type="submit" class="buttonSmall" name="action" value="add" title="Add a property to this identifier" /></div></form></td>
		</tr>
	</tbody>
</table>
<?php 
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
