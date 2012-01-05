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

// Get the record from the database.
$role = getRoles(getQueryValue('role_id'), null);
if( !$role )
{
	responseRedirect("role_list.php");
}

$initialBuiltinPassphrase = "abc123"; // For any User Roles changed to use the
                                      // built-in authentication service, that
									  // did not already have a built-in record.

$roleId = $role[0]['role_id'];
$roleName = $role[0]['role_name'];
$roleTypeId = trim($role[0]['role_type_id']);
$roleTypeName = $role[0]['role_type_name'];
$authServiceId = trim($role[0]['authentication_service_id']);
$enabled = $role[0]['role_enabled'];

$roleTypes = getRoleTypes();
$authenticationServices = getAuthenticationServices();

$errorMessages = '';
$roleNameLabelClass = '';
$authServiceIdLabelClass = '';

if( strtoupper(getPostedValue('action')) == "CANCEL" )
{
	responseRedirect("role_view.php?role_id=".urlencode($roleId));
}

if( strtoupper(getPostedValue('action')) == "SAVE" )
{
	$roleName = getPostedValue('role_name');
	if( $roleName == '' )
	{ 
		$roleNameLabelClass = gERROR_CLASS;
		$errorMessages .= "Name is a mandatory field.<br />";
	}
	
	$enabled = 't';
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
		
		// Update the role.
		$errors .= updateRole($roleId, $roleName, $authServiceId, $enabled);
		
		if( $errors == "" && $roleTypeId == gROLE_USER && $authServiceId == gAUTHENTICATION_BUILT_IN && !hasBuiltInAuthentication($roleId) )
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
<form id="role_edit" action="role_edit.php?role_id=<?php print(urlencode($roleId)); ?>" method="post">
<table class="formTable" summary="Edit Role">
	<thead>
		<tr>
			<td></td>
			<td>Edit Role</td>
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
			<td>ID:</td>
			<td><?php printSafe($roleId); ?></td>
		</tr>
		<tr>
			<td<?php print($roleNameLabelClass); ?>>* Name:</td>
			<td><input type="text" size="50" maxlength="255" name="role_name" value="<?php printSafe($roleName) ?>" /></td>
		</tr>
		<tr>
			<td>Type:</td>
			<td><?php printSafe($roleTypeName); ?></td>
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
						setChosenFromValue($authServiceId, '', gITEM_SELECT);
						print("<option value=\"\"$gChosen></option>\n");
						foreach( $authenticationServices as $service )
						{
							if( pgsqlBool($service['enabled']) )
							{
								setChosenFromValue($authServiceId, trim($service['authentication_service_id']), gITEM_SELECT);
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
			<td>
				<input type="submit" name="action" value="Cancel"/>&nbsp;
				<input type="submit" name="action" value="Save" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">
			Fields marked * are mandatory.<br /><br />
			1. Authentication Service is required for Roles of Type 'User'.<br />
			User Roles changed to use the Built-in Authentication Service<br />
			that have not previously been set to use this service will have<br />
			a built-in account created with the initial passphrase '<?php printSafe($initialBuiltinPassphrase) ?>'<br />
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