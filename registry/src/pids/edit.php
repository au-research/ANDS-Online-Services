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
       Object: /pids/edit.php
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
$index = getQueryValue('index');
$propertyType = '';
$propertyValue = '';


if( strtoupper(getPostedValue('action')) == "CANCEL" )
{
	responseRedirect('view.php?handle='.urlencode($handle));
}
	
// Get the handle
$serviceName = "getHandle";
$parameters  = "handle=".urlencode($handle);
$response = pidsRequest($serviceName, $parameters);
if( $response )
{
	if( pidsGetResponseType($response) == gPIDS_RESPONSE_SUCCESS )
	{
		// Get the data.
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		$properties = $responseDOMDoc->getElementsByTagName("property");
		if( $properties )
		{
			foreach( $properties as $property )
			{
				if( $property->getAttribute("index") == $index )
				{
					$propertyType = $property->getAttribute("type");
					$propertyValue = $property->getAttribute("value");
					break;
				}
			}
		}
	}
	else
	{
		$errorMessages = pidsGetUserMessage($response);
		if( !$errorMessages )
		{
			$errorMessages = 'There was a problem with the request [2].';
		}
	}
}
else
{	
	$errorMessages = 'There was an error with the service [1].';
}	

$errorMessages = '';
$propertyValueLabelClass = '';

if( strtoupper(getPostedValue('action')) == "SUBMIT" )
{
	$propertyValue = getPostedValue('property_value');
	if( $propertyValue == '' )
	{ 
		$propertyValueLabelClass = gERROR_CLASS;
		$errorMessages .= "Property Value is a mandatory field.<br />";
	}	
	
	if( $errorMessages == '' )
	{
		// Update the property value.
		$serviceName = "modifyValueByIndex";
		$parameters  = "handle=".urlencode($handle);
		$parameters .= "&index=".urlencode($index);
		$parameters .= "&value=".urlencode($propertyValue);
		$response = pidsRequest($serviceName, $parameters);
		if( $response )
		{
			if( pidsGetResponseType($response) == gPIDS_RESPONSE_SUCCESS )
			{
				// Display the identifier.
				$handle = pidsGetHandleValue($response);
				responseRedirect('view.php?handle='.urlencode($handle));
			}
			else
			{
				$errorMessages = pidsGetUserMessage($response);
				if( !$errorMessages )
				{
					$errorMessages = 'There was a problem with the request [2].';
				}
			}
		}
		else
		{	
			$errorMessages = 'There was an error with the service [1].';
		}
	}
}
// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<form id="edit_property" action="edit.php?handle=<?php printSafe(urlencode($handle))?>&amp;index=<?php printSafe(urlencode($index))?>" method="post" onsubmit="wcPleaseWait(false, 'Processing...')">
<table class="formTable" summary="Edit Identifier Property">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Edit Identifier Property</td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td>&nbsp;</td>
			<td class="errorText" style="white-space: normal"><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } ?>
	<tbody class="formFields">
		<tr>
			<td>Handle:</td>
			<td><?php printSafe($handle)?></td>
		</tr>
		<tr>
			<td>Index:</td>
			<td><?php printSafe($index)?></td>
		</tr>
		<tr>
			<td>Property Type:</td>
			<td><?php printSafe($propertyType)?></td>
		</tr>
		<tr>
			<td<?php print($propertyValueLabelClass); ?>>* Property Value:</td>
			<td><input type="text" name="property_value" id="property_value" size="32" maxlength="255" value="<?php printSafe($propertyValue) ?>" /></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Cancel" />&nbsp;&nbsp;<input type="submit" name="action" value="Submit" /></td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory.</td>
		</tr>
	</tbody>
</table>
</form>
<?php 

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
