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

function getRoles($role_id, $filter)
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_roles($1, $2)';
	$params = array($role_id, $filter);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	return $resultSet;
}

function getRoleTypes()
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_role_types()';
	$params = array();
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	return $resultSet;
}

function getAuthenticationServices()
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_authentication_services()';
	$params = array();
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	return $resultSet;
}

function insertRole($role_id, $role_type_id, $name, $authentication_service_id, $enabled)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_role($1, $2, $3, $4, $5, $6)';
	$params = array(getLoggedInUser(), $role_id, $role_type_id, $name, $authentication_service_id, $enabled);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the role.";
	}
	return $errors;
}

function updateRole($role_id, $name, $authentication_service_id, $enabled)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_update_role($1, $2, $3, $4, $5)';
	$params = array(getLoggedInUser(), $role_id, $name, $authentication_service_id, $enabled);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to update the role.";
	}
	return $errors;
}

function insertBuiltInAuthenticationUser($role_id, $passphrase_sha1)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_built_in_authentication_user($1, $2, $3)';
	$params = array(getLoggedInUser(), $role_id, $passphrase_sha1);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the built-in user.";
	}
	return $errors;
}

function hasBuiltInAuthentication($role_id)
{
	global $gCNN_DBS_COSI;
	
	$hasRecord = false;
	$strQuery = 'SELECT * FROM dba.udf_has_built_in_authentication($1) AS role_exists';
	$params = array($role_id);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( $resultSet[0]['role_exists'] == 1 )
	{
		$hasRecord = true;
	}
	return $hasRecord;
}
	
function deleteRole($role_id)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_role($1)';
	$params = array($role_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the role.";
	}
	return $errors;
}

function getRoleActivities($role_id)
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_role_activities($1)';
	$params = array($role_id);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	return $resultSet;
}

function getRoleActivityAddList($role_id)
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_role_activity_add_list($1)';
	$params = array($role_id);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	return $resultSet;
}

function getRoleRelationAddList($role_id, $parent_role_id)
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_role_relation_add_list($1, $2)';
	$params = array($role_id, $parent_role_id);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	return $resultSet;
}

function insertRoleActivity($role_id, $activity_id)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_role_activity($1, $2, $3)';
	$params = array(getLoggedInUser(), $role_id, $activity_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the role activity.";
	}
	return $errors;
}

function deleteRoleActivity($role_id, $activity_id)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_role_activity($1, $2)';
	$params = array($role_id, $activity_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the role activity.";
	}
	return $errors;
}

function insertRoleRelation($child_role_id, $parent_role_id)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_role_relation($1, $2, $3)';
	$params = array(getLoggedInUser(), $child_role_id, $parent_role_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the role relation.";
	}
	return $errors;
}

function deleteRoleRelation($child_role_id, $parent_role_id)
{
	global $gCNN_DBS_COSI;
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_role_relation($1, $2)';
	$params = array($child_role_id, $parent_role_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the role relation.";
	}
	return $errors;
}
?>