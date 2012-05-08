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
require 'pids_init.php';
// Page processing
// -----------------------------------------------------------------------------

$errorMessages = '';
$propertyTypeLabelClass = '';
$propertyValueLabelClass = '';

$agreedToTerms = false;
if( getPostedValue('agreed') ){ $agreedToTerms = true; }
if( strtoupper(getPostedValue('verb')) == "CANCEL" )
{
	responseRedirect('index.php');
}
if( strtoupper(getPostedValue('action')) == "SUBMIT" )
{
	$propertyType = getPostedValue('property_type');
	if( $propertyType == '' )
	{ 
		$propertyTypeLabelClass = gERROR_CLASS;
		$errorMessages .= "Property Type is a mandatory field.<br />";
	}
	
	$propertyValue = getPostedValue('property_value');
	if( $propertyValue == '' )
	{ 
		$propertyValueLabelClass = gERROR_CLASS;
		$errorMessages .= "Property Value is a mandatory field.<br />";
	}	
	
	if( $errorMessages == '' )
	{
		// Mint the identifier.
		$serviceName = "mint";
		$parameters  = "type=".urlencode($propertyType);
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
if( !$agreedToTerms )
{
?>
<div style="margin-bottom: 2em; width: 800px;">
<p>You have asked to mint a persistent identifier through ANDS <i>Identify
My Data</i> self-service. This means that you will enter location and/or
description information relating to the object you wish to identify and
ANDS will provide you with a persistent identifier for that object.</p>
<p>
In using ANDS <i>Identify My Data</i> self-service you agree that:
</p>
<ul>
	<li>You are part of the higher education, public research or cultural
	collections sector and that at least some of the objects you are
	identifying are publicly available or will eventually become publicly
	available.</li>
	<li>You are authorised and entitled to mint and manage persistent
	identifiers for the objects you intend to identify.</li>
	<li>You will endeavour to keep up-to-date the location and
	description fields for the persistent identifiers you mint.</li>
	<li>You understand that this location and description information
	will be available to the general public and that confidential material
	should not be entered into these fields.</li>
	<li>You will take responsibility for liaison with any party who has
	queries regarding persistent identifiers that you mint. (ANDS does not
	provide link-rot checking or help-desk services for end-users of
	persistent identifiers.)</li>
</ul>

<p>
You understand that:
</p>
<ul>
	<li>ANDS provides the <i>Identify My Data</i> product on an ‘as is’ and
	‘as available’ basis. ANDS hereby exclude any warranty either express
	or implied as to the merchantability, fitness for purpose, accuracy,
	currency or comprehensiveness of this product. To the fullest extent
	permitted by law, the liability of ANDS under any condition or warranty
	which cannot be excluded legally is limited, at the option of ANDS to
	supplying the services again or paying the cost of having the services
	supplied again.</li>
	
	<li>ANDS does not manage persistent identifiers; ANDS only provides
	the infrastructure that allows minting, resolution and updating of
	identifiers. Processes and policies need to be put in place by those
	utilising <i>Identify My Data</i> to ensure that appropriate maintenance
	practices are put in place to underpin persistence.</li>
	<li>ANDS will endeavour to persist ANDS Identifiers for a minimum of
	twenty years.</li>
	<li>The allocation of a persistent identifier to an object does not
	include any transfer or assignment of ownership of any Intellectual
	Property right (IPR) with regard to that content.</li>
	<li>ANDS will endeavour to provide a high availability service.
	However, ANDS <i>Identify My Data</i> is underpinned and reliant on the <a href="http://www.handle.net/">Handle
	services</a> provided by the <a href="http://cnri.reston.va.us/">Corporation for National Research Initiatives</a>
	(CNRI), in particular the Global Handle Registry. ANDS cannot warrant
	the longevity or reliability of the Handle system or the CNRI.</li>
</ul>

</div>
<script type="text/javascript">
	function setContinueButton()
	{
		var objAgreeCheck = getObject('agreeCheck');
		var objContinueButton = getObject('continueButton');
		if( objAgreeCheck.checked )
		{
			objContinueButton.style.visibility = "visible";
		}
		else
		{
			objContinueButton.style.visibility = "hidden";
		}
	}
</script>
<form id="agreement" action="create.php" method="post">
	<div>
		<input id="agreeCheck" type="checkbox" name="agreed[]" value="true" onclick="setContinueButton()" /><label for="agreeCheck">I AGREE</label><br /><br />
		<input id="url" type="hidden" name="url" value="<?php printSafe($url)?>" />
		<input type="submit" name="verb" value="Cancel"/>&nbsp;&nbsp;
		<input id="continueButton" style="visibility: hidden;" type="submit" name="verb" value="Continue" />&nbsp;&nbsp;
	</div>
</form>
<?php
}
else
{
?>
<form id="create_id" action="create.php" method="post" onsubmit="wcPleaseWait(false, 'Processing...')">
<table class="formTable" summary="Create Identifier">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>
				Create Identifier
				<input id="agreeCheck" type="hidden" name="agreed" value="<?php printSafe($agreedToTerms)?>" />
			</td>
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
			<td<?php print($propertyTypeLabelClass); ?>>* Property Type:</td>
			<td>
				<select name="property_type" id="property_type">
				<?php
					setChosen('property_type', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					foreach( $gPIDS_PROPERTY_TYPES as $key => $descr )
					{
						setChosen('property_type', $key, gITEM_SELECT);
						print("<option value=\"".esc($key)."\"$gChosen>".esc($descr)."</option>\n");
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td<?php print($propertyValueLabelClass); ?>>* Property Value:</td>
			<td><input type="text" name="property_value" id="property_value" size="32" maxlength="255" value="<?php printSafe(getPostedValue('property_value')) ?>" /></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Submit" />&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory.</td>
		</tr>
	</tbody>
</table>
</form>
<?php 
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
