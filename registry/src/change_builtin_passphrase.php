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
require '_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------
$formErrors = false;
$formErrorMessage = '';

$changedMessage = '';
$changed = false;

$name = '';

if( $_POST )
{
	$role_id = getSessionVar(sROLE_ID);
	$passphrase = getPostedValue('passphrase');
	$new_passphrase = getPostedValue('new_passphrase');
	$confirm_passphrase = getPostedValue('confirm_passphrase');
	
	if( $confirm_passphrase == '' || $new_passphrase == '' || $passphrase == '' )
	{
		$formErrors = true;
		$formErrorMessage .= "All of the form fields are required.\n";
	}
	elseif( $confirm_passphrase != $new_passphrase )
	{
		$formErrors = true;
		$formErrorMessage .= "The New Passphrase was not the same as Confirmation Passphrase.\n";
	}
	else
	{
		// Authenticate with the current passphrase.
		$authDomain = '';
		$authenticated = authenticate($role_id, $passphrase, $changedMessage, $name, $authDomain);
		if( !$authenticated )
		{
			$formErrorMessage = "Invalid Current Passphrase.\n";
		}
		else
		{
			$changed = changeBuiltinPassphrase($role_id, $new_passphrase);
		}
		if( $authenticated && !$changed )
		{
			$formErrorMessage = "Save failed.\n";
		}
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>

<form id="change_form" action="change_builtin_passphrase.php" method="post">
<table class="formTable" summary="Change Built-in Passphrase Form">
	<thead>
		<tr>
			<td></td>
			<td>
				Change Built-in Passphrase for<br />
				<?php printSafe(getSessionVar(sNAME))?>&nbsp;(<?php printSafe(getSessionVar(sROLE_ID)) ?>)
			</td>
		</tr>
	</thead>
	<?php if( $formErrorMessage != '' ){ ?>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText"><?php printSafeWithBreaks($formErrorMessage) ?></td>
		</tr>
	</tbody>
	<?php } ?>
	<?php if( !$changed ){ ?>
	<tbody class="formFields">
		<tr>
			<td>Current Passphrase:</td>
			<td><input type="password" name="passphrase" size="25" maxlength="255" value="" /></td>
		</tr>
		<tr>
			<td>New Passphrase:</td>
			<td><input type="password" name="new_passphrase" size="25" maxlength="255" value="" /></td>
		</tr>
		<tr>
			<td>Confirm New Passphrase:</td>
			<td><input type="password" name="confirm_passphrase" size="25" maxlength="255" value="" /></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Save" /></td>
		</tr>
	</tbody>
	<?php } else { ?>
	<tbody>
		<tr>
			<td></td>
			<td>Your built-in passphrase was sucessfully changed.</td>
		</tr>
	</tbody>	
	<?php } ?>
</table>
</form>


<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '_includes/footer.php';
require '_includes/finish.php';
?>
