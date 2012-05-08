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
$roleId = getQueryValue('role_id');
$role = getRoles($roleId, null);
if( !$role )
{
	responseRedirect("role_list.php");
}


// Get any action that may have been posted.
$action = strtoupper(getPostedValue('action'));

$errors = '';

// Action the action.
switch( $action )
{
	case 'EDIT':
		responseRedirect('role_edit.php?role_id='.urlencode(getQueryValue('role_id')));
		break;
	case 'DELETE':
		responseRedirect('role_delete.php?role_id='.urlencode(getQueryValue('role_id')));
		break;
	
	case 'UPDATE_ACTIVITY':
		if( getPostedValue('activity_add') == 'add' && getPostedValue('activity_id') != '' )
		{
			$errors = insertRoleActivity($roleId, getPostedValue('activity_id'));
		}
		
		$postKeys = array_keys($_POST);
		if( $postKeys )
		{
			foreach( $postKeys as $postKey )
			{
				if( getPostedValue($postKey) == 'remove' )
				{
					$removeActivityId = decodeName($postKey);
					$errors = deleteRoleActivity($roleId, $removeActivityId);
				}
			}
		}

		break;
	case 'UPDATE_ROLE_RELATION':
		if( getPostedValue('role_add') == 'add' && getPostedValue('role_id') != '' )
		{
			$errors = insertRoleRelation($roleId, getPostedValue('role_id'));
		}
		
		$postKeys = array_keys($_POST);
		if( $postKeys )
		{
			foreach( $postKeys as $postKey )
			{
				if( getPostedValue($postKey) == 'remove' )
				{
					$removeRoleId = decodeName($postKey);
					$errors = deleteRoleRelation($roleId, $removeRoleId);
				}
			}
		}
		break;
	default;
		break;
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<table class="recordTable" summary="Role">
	<thead>
		<tr>
			<td></td>
			<td>Role</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>ID:</td>
			<td><?php printSafe($role[0]['role_id']) ?></td>
		</tr>
		<tr>
			<td>Name:</td>
			<td><?php printSafe($role[0]['role_name']) ?></td>
		</tr>
		<tr>
			<td>Type:</td>
			<td><?php printSafe($role[0]['role_type_name']) ?></td>
		</tr>
		<tr>
			<td>Enabled:</td>
			<td><?php
				$enabled = 'YES';
				if( !pgsqlBool($role[0]['role_enabled']) ) { $enabled = 'NO'; }
			 	printSafe($enabled); ?></td>
		</tr>
		<tr>
			<td>Authentication Service:</td>
			<td><?php printSafe($role[0]['authentication_service_name']) ?></td>
		</tr>
		<tr>
			<td>Last Login:</td>
			<td><?php printSafeWithBreaks(formatDateTime($role[0]['last_login'])) ?></td>
		</tr>
		<tr>
			<td>Created When:</td>
			<td><?php printSafe(formatDateTime($role[0]['created_when'], gDATETIME)) ?></td>
		</tr>
		<tr>
			<td>Created Who:</td>
			<td><?php printSafe($role[0]['created_who']) ?></td>
		</tr>
		<tr>
			<td>Modified When:</td>
			<td><?php printSafe(formatDateTime($role[0]['modified_when'], gDATETIME)) ?></td>
		</tr>
		<tr>
			<td>Modified Who:</td>
			<td><?php printSafe($role[0]['modified_who']) ?></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td>
				<form action="role_view.php?role_id=<?php printSafe(urlencode(getQueryValue('role_id'))); ?>" method="post">
				<div style="margin-bottom: 1em;">
				<input type="submit" name="action" value="Edit" />&nbsp;
				<input type="submit" name="action" value="Delete" />&nbsp;
				</div>
				</form>
			</td>
		</tr>
	</tbody>
<?php
$roleId = $role[0]['role_id'];
$roleTypeId = trim($role[0]['role_type_id']);
if( $errors != '' )
{
	print("<tbody><tr><td></td><td class=\"errorText\">".esc($errors)."</td></tr></tbody>\n");
}
switch( $roleTypeId )
{
	case gROLE_USER:
		print("<tbody class=\"recordFields\">\n");
		print("<tr><td>Functional Roles:</td><td>\n");
		drawRoleRelationsForm($roleId, gROLE_FUNCTIONAL);
		print("</td></tr>\n");
		print("<tr><td>Organisational Roles:</td><td>\n");
		drawRoleRelationsForm($roleId, gROLE_ORGANISATIONAL);
		print("</td></tr>\n");
		print("</tbody>\n");
		break;
		
	case gROLE_FUNCTIONAL:
		print("<tbody class=\"recordFields\">\n");
		print("<tr><td>Activities:</td><td>\n");			
		drawRoleActivitiesForm($roleId);
		print("</td></tr>\n");
		print("<tr><td>Functional Roles:</td><td>\n");
		drawRoleRelationsForm($roleId, gROLE_FUNCTIONAL);
		print("</td></tr>\n");
		print("<tr><td>Descendants:</td><td>\n");
		drawDescendants($roleId);
		print("</td></tr>\n");
		print("</tbody>\n");
		break;
		
	case gROLE_ORGANISATIONAL:
		print("<tbody class=\"recordFields\">\n");
		print("<tr><td>Organisational Roles:</td><td>\n");
		drawRoleRelationsForm($roleId, gROLE_ORGANISATIONAL);
		print("</td></tr>\n");
		print("<tr><td>Descendants:</td><td>\n");
		drawDescendants($roleId);
		print("</td></tr>\n");
		print("</tbody>\n");
		break;
}
?>
</table>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
