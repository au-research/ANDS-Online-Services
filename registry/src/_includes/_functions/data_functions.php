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

function validBuiltInCredentials($role_id, $passphrase, &$userMessage)
{
	global $gCNN_DBS_COSI;
	
	$validCredentials = false;

	$strQuery = 'SELECT dba.udf_authenticate_with_built_in($1, $2) AS valid_credentials';
	$params = array($role_id, sha1($passphrase));
	
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	if( $resultSet )
	{
		$validCredentials = pgsqlBool($resultSet[0]['valid_credentials']);
	}
	if( !$validCredentials )
	{
		$userMessage = "Login Failed: Invalid User ID/Password [20].\n";
	}
	return $validCredentials;
}

function isAuthenticationServiceEnabled($authentication_service_id)
{
	global $gCNN_DBS_COSI;
	$enabled = false;
	$strQuery = 'SELECT enabled FROM dba.tbl_authentication_services WHERE authentication_service_id = $1';
	$params = array($authentication_service_id);
	
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	if( $resultSet )
	{
		$enabled = pgsqlBool($resultSet[0]['enabled']);
	}
	return $enabled;
}

function getUserAuthenticationService($role_id)
{
	global $gCNN_DBS_COSI;
	$authentication_service_id = '';
	$strQuery = 'SELECT dba.udf_get_role_auth_service_id($1) AS auth_service_id';
	$params = array($role_id);

	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	if( $resultSet )
	{
		$authentication_service_id = trim($resultSet[0]['auth_service_id']);
	}
	return $authentication_service_id;
}

function getRoleName($role_id)
{
	global $gCNN_DBS_COSI;
	$userName = '';
	$strQuery = 'SELECT dba.udf_get_role_name($1) AS name';
	$params = array($role_id);
	
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	if( $resultSet )
	{
		$userName = trim($resultSet[0]['name']);
	}
	return $userName;
}

function getActivityRoleIDs($activity_id)
{
	global $gCNN_DBS_COSI;

	$strQuery = 'SELECT role_id FROM dba.udf_get_activity_role_ids($1)';
	$params = array($activity_id);
	
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);

	return $resultSet;
}

function getParentRoleIDs($role_id)
{
	global $gCNN_DBS_COSI;

	$strQuery = 'SELECT parent_role_id AS role_id FROM dba.udf_get_parent_role_ids($1)';
	$params = array($role_id);
	
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);

	return $resultSet;
}

function getChildRoleIDs($role_id)
{
	global $gCNN_DBS_COSI;

	$strQuery = 'SELECT child_role_id AS role_id FROM dba.udf_get_child_role_ids($1)';
	$params = array($role_id);
	
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);

	return $resultSet;
}

function changeBuiltinPassphrase($role_id, $new_passphrase)
{
	global $gCNN_DBS_COSI;

	$strQuery = 'SELECT dba.udf_update_built_in_passphrase($1, $2)';
	$params = array($role_id, sha1($new_passphrase));
	
	$result = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);

	return $result;
}

function updateLastLogin($role_id)
{
	global $gCNN_DBS_COSI;
	
	$strQuery = 'SELECT * FROM dba.udf_update_last_login($1)';
	$params = array($role_id);
	$result = executeUpdateQuery($gCNN_DBS_COSI, $strQuery, $params);

	return $result;
}

function isRoleEnabled($role_id)
{
	global $gCNN_DBS_COSI;
	
	$enabled = false;
	
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_role_enabled($1) AS enabled';
	$params = array($role_id);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( $resultSet )
	{
		if( pgsqlBool($resultSet[0]['enabled']) )
		{
			$enabled = true;
		}
	}
	
	return $enabled;
}

function getRoleRelations($role_id, $parent_role_type_id)
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_role_relations($1, $2)';
	$params = array($role_id, $parent_role_type_id);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	return $resultSet;
}

function getOrganisationalRoles()
{
	global $gCNN_DBS_COSI;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_organisational_roles()';
	$params = array();
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	return $resultSet;
}

// Statistics  function
//---------------------------------------------
function getDataSorceAdminCount($date_filter)
{
	global $gCNN_DBS_COSI;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_data_source_admin_count($1) AS count';
	$params = array($date_filter);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}

function getOrganisationCount($created_when=null)
{
	global $gCNN_DBS_COSI;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_organisation_count($1) AS count';
	$params = array($created_when);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}
function getUserCount($created_when=null)
{
	global $gCNN_DBS_COSI;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_user_count($1) AS count';
	$params = array($created_when);
	$resultSet = executeQuery($gCNN_DBS_COSI, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}
function getPidsCount($date_filter)
{
	global $gCNN_DBS_PIDS;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.udf_get_pids_count($1) AS count';
	$params = array($date_filter);
	$resultSet = executeQuery($gCNN_DBS_PIDS, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}
function getM2MCount($date_filter)
{
	global $gCNN_DBS_PIDS;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.udf_get_m2m_agreements_count($1) AS count';
	$params = array($date_filter);
	$resultSet = executeQuery($gCNN_DBS_PIDS, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}


function getPageAccessCounts($date_filter)
{
	$month_string = date('mY',strtotime($date_filter."-1 month"));
	//echo $month_string." is the month";
	$tmpdir = '/var/lib/awstats/';
	$currentPath = dirname($_SERVER['SCRIPT_NAME']);

	$pagehits = 0;

	if (file_exists($tmpdir)) {
   	   	
    	// open directory and walk through the filenames
    	$handler = opendir($tmpdir);
   		//change to the log directory
    	chdir($tmpdir);   

     	while ($file = readdir($handler)) 
    	{
      	// if file isn't this directory or its parent, add it to the results
      		if ($file != "." && $file != "..") 
      		{
      			//if we have found one of the access logs then unzip it and read the rda/view.php accesses for the given month  	
     			if(substr($file,0,13)=="awstats".$month_string)
     			{    							

					$execute = 'cat '.$file.'  | grep -e "^/home/orca/rda/view.php " | cut -d " " -f 4';	
					exec($execute,$retval,$retvar);	

					for($i=0;$i<count($retval);$i++)
					{
						if($retval[$i])				
						{		
    						$pagehits += $retval[$i];
						}
     				}
    				unset($retval);
 
     			}
      		}
    	}
 	// we need to go back to the executing directory
   chdir("/var/www/htdocs".$currentPath); 
    // tidy up: close the handler
   closedir($handler);
   print($pagehits);

	}	
}
function getVisitorCounts($date_filter)
{
	$month_string = date('mY',strtotime($date_filter."-1 month"));
	//echo $month_string." is the month";
	$tmpdir = '/var/lib/awstats/';
	$currentPath = dirname($_SERVER['SCRIPT_NAME']);

	$visits = 0;

	if (file_exists($tmpdir)) {
   	   	
    	// open directory and walk through the filenames
    	$handler = opendir($tmpdir);
   		//change to the log directory
    	chdir($tmpdir);   

     	while ($file = readdir($handler)) 
    	{
      	// if file isn't this directory or its parent, add it to the results
      		if ($file != "." && $file != "..") 
      		{
      			//if we have found one of the access logs then unzip it and read the rda/view.php accesses for the given month  	
     			if(substr($file,0,13)=="awstats".$month_string)
     			{    							

					$execute = 'cat '.$file.' | grep "TotalVisits" | grep -v "#" | cut -f 2 -d " "';	
					exec($execute,$retval,$retvar);	
    				$visits += $retval[0];
 
     			}
      		}
    	}
 	// we need to go back to the executing directory
   chdir("/var/www/htdocs".$currentPath); 
    // tidy up: close the handler
   closedir($handler);
   print($visits);

	}	
}
function getDoiClientCount($date_filter)
{
	global $gCNN_DBS_DOIS;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_clients_count($1) AS count';
	$params = array($date_filter);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}
function getDoisCount($date_filter)
{
	global $gCNN_DBS_DOIS;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_dois_count($1) AS count';
	$params = array($date_filter);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}
function getDoiMintFailCount($date_filter)
{
	global $gCNN_DBS_DOIS;
	
	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_mint_fail_count($1) AS count';
	$params = array($date_filter);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	
	return $count;
}
?>