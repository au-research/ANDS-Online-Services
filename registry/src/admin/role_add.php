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
require 'admin_init.php';
// Page processing
// -----------------------------------------------------------------------------

$roleTypes = getRoleTypes();
$authenticationServices = getAuthenticationServices();
$enabled = 't';

$initialBuiltinPassphrase = "abc123"; // For any new User Roles using the
                                      // built-in authentication service.
$errorMessages = '';
$roleIdLabelClass = '';
$roleNameLabelClass = '';
$roleTypeLabelClass = '';
$authServiceIdLabelClass = '';

if( strtoupper(getPostedValue('action')) == "SAVE" )
{
	$roleId = getPostedValue('role_id');
	if( $roleId == '' )
	{ 
		$roleIdLabelClass = gERROR_CLASS;
		$errorMessages .= "ID is a mandatory field.<br />";
	}
	else
	{
		// Check that the role doesn't already exist.
		if( getRoles($roleId, null) )
		{
			$roleIdLabelClass = gERROR_CLASS;
			$errorMessages .= "A role with this ID already exists.<br />";
		}
	}
	
	$roleName = getPostedValue('role_name');
	if( $roleName == '' )
	{ 
		$roleNameLabelClass = gERROR_CLASS;
		$errorMessages .= "Name is a mandatory field.<br />";
	}
	
	$roleTypeId = getPostedValue('role_type_id');
	if( $roleTypeId == '' )
	{ 
		$roleTypeLabelClass = gERROR_CLASS;
		$errorMessages .= "Type is a mandatory field.<br />";
	}
	
	if( !getPostedValue('enabled') ){ $enabled = 'f'; }
	
	$authServiceId = null;
	if( $roleTypeId == gROLE_USER )
	{ 
		$authServiceId = getPostedValue('authentication_service_id');
		if( $authServiceId == '' )
		{
			$authServiceIdLabelClass = gERROR_CLASS;
			$errorMessages .= "Authentication Service is required for Roles of Type 'User'.<br />";
		}
	}
	
	if( $errorMessages == '' )
	{
		$errors = '';
		
		// Add the new role.
		$errors .= insertRole($roleId, $roleTypeId, $roleName, $authServiceId, $enabled);
		
		if( $errors == "" && $roleTypeId == gROLE_USER && $authServiceId == gAUTHENTICATION_BUILT_IN )
		{
			// Add a record to support the built-in authentication.
			$errors .= insertBuiltInAuthenticationUser($roleId, sha1($initialBuiltinPassphrase));
		}
		
		if( $errors == '' )
		{
			responseRedirect("role_view.php?role_id=".urlencode($roleId));
		}
		else
		{
			$errorMessages = $errors;
		}
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>

<form id="role_add" action="role_add.php" method="post">
<table class="formTable" summary="Add Role">
	<thead>
		<tr>
			<td></td>
			<td>Add Role</td>
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
		<tr>
			<td<?php print($roleIdLabelClass); ?>>* ID:</td>
			<td><input type="text" size="30" maxlength="255" name="role_id" value="<?php printSafe(getPostedValue('role_id')) ?>" /></td>
		</tr>
		<tr>
			<td<?php print($roleNameLabelClass); ?>>* Name:</td>
			<td><input type="text" size="50" maxlength="255" name="role_name" value="<?php printSafe(getPostedValue('role_name')) ?>" /></td>
		</tr>
		<tr>
			<td<?php print($roleTypeLabelClass); ?>>* Type:</td>
			<td>
				<select name="role_type_id">
					<?php 
					if( $roleTypes )
					{
						setChosen('role_type_id', '', gITEM_SELECT);
						print("<option value=\"\"$gChosen></option>\n");
						foreach( $roleTypes as $type )
						{
							setChosen('role_type_id', trim($type['role_type_id']), gITEM_SELECT);
							print("<option value=\"".esc(trim($type['role_type_id']))."\"$gChosen>".esc($type['name'])."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Enabled:</td>
			<td>
				<?php setChosenFromValue($enabled, 't', gITEM_CHECK) ?>
				<input type="checkbox" name="enabled[]" value="t"<?php print $gChosen ?> />
			</td>
		</tr>
		<tr>
			<td<?php print($authServiceIdLabelClass); ?>><sup>1</sup> Authentication Service:</td>
			<td>
				<select name="authentication_service_id">
					<?php 
					if( $authenticationServices )
					{
						setChosen('authentication_service_id', '', gITEM_SELECT);
						print("<option value=\"\"$gChosen></option>\n");
						foreach( $authenticationServices as $service )
						{
							if( pgsqlBool($service['enabled']) )
							{
								setChosen('authentication_service_id', trim($service['authentication_service_id']), gITEM_SELECT);
								print("<option value=\"".esc(trim($service['authentication_service_id']))."\"$gChosen>".esc($service['name'])."</option>\n");
							}
						}
					}
					?>
				</select>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Save" /></td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">
			Fields marked * are mandatory.<br /><br />
			1. Authentication Service is required for Roles of Type 'User'.<br />
			User Roles added with the Built-in Authentication Service will<br />
			have a built-in account created with the initial passphrase '<?php printSafe($initialBuiltinPassphrase) ?>'<br />
			(ignoring the quotes). Users of the Built-in Authentication Service<br />
			can change their passphrase by using the Change Built-in Passphrase<br />
			function after they login.
			</td>
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
