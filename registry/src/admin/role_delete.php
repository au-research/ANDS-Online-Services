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

$roleId = $role[0]['role_id'];
$roleName = $role[0]['role_name'];
$roleTypeName = $role[0]['role_type_name'];

$errorMessages = '';

if( strtoupper(getPostedValue('action')) == "CANCEL" )
{
	responseRedirect("role_view.php?role_id=".urlencode($roleId));
}

if( strtoupper(getPostedValue('action')) == "DELETE" )
{
	$errors = '';

	// Delete the role.
	// This will also delete any associated role activities, role relations, and built-in authentication.
	$errors .= deleteRole($roleId);

	if( $errors == '' )
	{
		responseRedirect("role_list.php");
	}
	else
	{
		$errorMessages = $errors;
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<form id="role_delete" action="role_delete.php?role_id=<?php print(urlencode($roleId)); ?>" method="post">
<table class="formTable" summary="Delete Role">
	<thead>
		<tr>
			<td></td>
			<td>Delete Role</td>
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
			<td>Name:</td>
			<td><?php printSafe($roleName); ?></td>
		</tr>
		<tr>
			<td>Type:</td>
			<td><?php printSafe($roleTypeName); ?></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td>
				<input type="submit" name="action" value="Cancel"/>&nbsp;
				<input type="submit" name="action" value="Delete" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">
			Fields marked * are mandatory.<br /><br />

			Deleting this role will also delete any associated<br />
			Built-in Authentication records.
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
