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
require 'admin/_functions/admin_data_functions.php';
// Page processing
// -----------------------------------------------------------------------------
$formErrors = false;
$formErrorMessage = '';

$changedMessage = '';
$reset = false;

$name = '';
$builtInRoles = getRoles(null,'built-in');



if( $_POST )
{
	$role_id = getPostedValue('userID');
	$userID = getPostedValue('userID');
	$reset_passphrase = 'abc123';
	
	if( $userID == ''  )
	{
		$formErrors = true;
		$formErrorMessage .= "User ID is required.\n";
	}
	
	else
	{
		// Reset with the default passphrase.
		$authDomain = getUserAuthenticationService($role_id);
		if($authDomain=="AUTHENTICATION_BUILT_IN"){

			$reset = changeBuiltinPassphrase($role_id, $reset_passphrase);
			
		}else{
			
			$formErrorMessage = "The supplied user ID does not have built in authentication.\n";			
		}

	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>

<form id="change_form" action="reset_builtin_passphrase.php" method="post">
<table class="formTable" summary="Reset Built-in Passphrase Form">
	<thead>
		<tr>
			<td></td>
			<td>
				Reset Built-in Passphrase<br />
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
	<?php if( !$reset ){ ?>
	<tbody class="formFields">
		<tr>
			<td>User ID:</td>
			<td>
			<select name="userID">
			<?php 
			if($builtInRoles)
			{
				foreach($builtInRoles as $builtInRole)
				{ ?>
					<option value="<?php echo $builtInRole["role_id"];?>"><?php echo $builtInRole["role_id"];?></option>
			<?php 	}
			}
			?>
			</select>
			</td>
		</tr>

	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Reset" /></td>
		</tr>
	</tbody>
	<?php } else { ?>
	<tbody>
		<tr>
			<td></td>
			<td>The built-in passphrase was reset to <em>abc123</em> successfully for user <em><?=$role_id?></em>.</td>
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