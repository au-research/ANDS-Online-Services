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

// Get the party object. (This will create a new party object if one doesn't already exist.)
$registryObject = getUserPartyObject();
$registryObjectKey = null;

$errorMessages = '';
$nameLabelClass = '';

if( $registryObject )
{
	$registryObjectKey = $registryObject[0]['registry_object_key'];
	$name = getNameHTML($registryObjectKey);
	$email = getPublisherEmailAddress($registryObjectKey);
}

if( strtoupper(getPostedValue('verb')) == "CANCEL" )
{
	responseRedirect('publisher_view.php');
}
if( strtoupper(getPostedValue('verb')) == "SAVE" )
{
	
	$name = getPostedValue('name');
	$email = getPostedValue('email');
	
	if( $name == '' )
	{ 
		$nameLabelClass = gERROR_CLASS;
		$errorMessages .= "Name is a mandatory field.<br />";
	}

	if( !$errorMessages )
	{
		updateUserPartyObject($name, $email);
		responseRedirect('publisher_view.php');
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
if( $registryObject )
{
?>
<form id="publisher_edit" action="publisher_edit.php" method="post">
<table class="formTable" summary="View/Update My Details">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>View/Update My Details</td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText"><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } ?>
	<tbody class="formFields">
		<!-- KEY -->
		<tr>
			<td>* Key:</td>
			<td><?php printSafe($registryObjectKey); ?></td>
		</tr>
		
		<!-- NAME -->
		<tr>
			<td<?php print($nameLabelClass); ?>>* Name:</td>
			<td><input type="text" name="name" id="name" size="40" maxlength="255" value="<?php printSafe($name) ?>" /></td>
		</tr>

		<!-- EMAIL  -->
		<tr>
			<td>Email Address:</td>
			<td>
				<input type="text" name="email" id="email" size="40" maxlength="255" value="<?php printSafe($email) ?>" />
			</td>
		</tr>

	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td>
				<input type="submit" name="verb" value="Cancel"/>&nbsp;&nbsp;
				<input type="submit" name="verb" value="Save" onclick="wcPleaseWait(true, 'Processing...')" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">
				Fields marked * are mandatory.
			</td>
		</tr>
	</tbody>
</table>	
</form>	

<?php
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';

function getPublisherEmailAddress($registryObjectKey)
{
	$email = '';
	$locations = getLocations($registryObjectKey);
	if( $locations )
	{
		$locationId = $locations[0]['location_id'];
		$addresses = getAddressLocations($locationId);
		$addressId = $addresses[0]['address_id'];
		$electronicAddresses = getElectronicAddresses($addressId);
		$email = $electronicAddresses[0]['value'];
	}
	return $email;
}
?>
