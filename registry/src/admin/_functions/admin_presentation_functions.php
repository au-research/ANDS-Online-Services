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

function formatCreatedInfo($who, $when)
{
	return '&nbsp;&nbsp;<span class="inputFormat">Created:&nbsp;'.esc(formatDateTime($when, gDATETIME)).' '.esc($who).'</span>';
}

function drawRoleActivitiesForm($roleId)
{
	print("<form id=\"updateActivities\" action=\"role_view.php?role_id=".urlencode($roleId)."\" method=\"post\"><div>\n");
	print("<input type=\"hidden\" name=\"action\" value=\"UPDATE_ACTIVITY\" />\n");
	$activities = getRoleActivities($roleId);
	if( $activities )
	{
		foreach( $activities as $row )
		{
			print('<input type="submit" class="buttonSmall" name="'.esc(encodeName($row['activity_id'])).'" value="remove" />&nbsp;');
			print(esc($row['activity_id']).formatCreatedInfo($row['created_who'], $row['created_when'])."<br />\n");
		}
	}
	drawActivitySelectList($roleId);
	print("</div>\n</form>\n");
}

function drawRoleRelationsForm($roleId, $parentRoleTypeId)
{
	print("<form id=\"update".esc($parentRoleTypeId)."\" action=\"role_view.php?role_id=".urlencode($roleId)."\" method=\"post\"><div>\n");
	print("<input type=\"hidden\" name=\"action\" value=\"UPDATE_ROLE_RELATION\" />\n");
	
	$roleRelations = getRoleRelations($roleId, $parentRoleTypeId);
	if( $roleRelations )
	{
		foreach( $roleRelations as $row )
		{
			print('<input type="submit" class="buttonSmall" name="'.esc(encodeName($row['parent_role_id'])).'" value="remove" />&nbsp;');
			print("<a href=\"role_view.php?role_id=".urlencode($row['parent_role_id'])."\">".esc($row['parent_role_name'])."</a>".formatCreatedInfo($row['created_who'], $row['created_when'])."<br />\n");
		}
	}
	drawRoleSelectList($roleId, $parentRoleTypeId);
	print("</div>\n</form>\n");
}

function drawRoleSelectList($roleId, $roleTypeId)
{
	// Get every role of $roleTypeId of which $roleId is not a member
	// excluding any descendants of $roleId (to prevent a circular reference),
	// and inherited roles (as these are redundant).
	$relatedRoles = getRelatedRoleIDs($roleId);
	
	$roles = getRoleRelationAddList($roleId, $roleTypeId);
	$roleList = array();
	// Sort by role name, not value
	usort($roles, function ($a,$b) {
          return strtolower($a['name'])>strtolower($b['name']);
    });

	if( $roles )
	{
		foreach( $roles as $role )
		{
			$includeInList = true;
			if( $relatedRoles )
			{
				foreach( $relatedRoles as $relatedRole )
				{
					if( $relatedRole == $role['role_id'] )
					{
						$includeInList = false;
					}
				}
			}
			if( $includeInList )
			{
				$roleList[count($roleList)] = $role;
			}
		}		
	}

	if( $roleList )
	{
		print('<input type="submit" class="buttonSmall" name="role_add" value="add" />'."\n");
		print("<select name=\"role_id\">\n");
		print("<option value=\"\"></option>\n");
		foreach( $roleList as $row )
		{
			print("<option value=\"".esc($row['role_id'])."\">".esc($row['name'])."</option>\n");
		}
		print("</select>\n");	
	}
}

function drawActivitySelectList($roleId)
{
	// Get every activity that $roleId doesn't already have.
	$activities = getRoleActivityAddList($roleId);
	if( $activities )
	{
		print('<input type="submit" class="buttonSmall" name="activity_add" value="add" />'."\n");
		print("<select name=\"activity_id\">\n");
		print("<option value=\"\"></option>\n");
		foreach( $activities as $row )
		{
			print("<option value=\"".esc($row['activity_id'])."\">".esc($row['activity_id'])."</option>\n");
		}
		print("</select>\n");
	}
}

function drawDescendants($roleId)
{
	$descendants = getChildRoleIDs($roleId);
	if( $descendants )
	{
		print("<div style=\"margin-left: 1em;\">");
		foreach( $descendants as $child )
		{
			$childRoleId = $child['role_id'];
			print(gCHAR_MIDDOT."&nbsp;<a href=\"role_view.php?role_id=".urlencode($childRoleId)."\">".esc(getRoleName($childRoleId))."</a><br />\n");
			drawDescendants($childRoleId);
		}
		print("</div>\n");
	}
}

function encodeName($name)
{
	$encodedName = base64_encode($name);
	$encodedName = str_replace("=", "_", $encodedName);
	
	return $encodedName;
}

function decodeName($encodedName)
{
	$name = str_replace("_", "=", $encodedName);
	$name = base64_decode($name);
	
	return $name;
}

?>