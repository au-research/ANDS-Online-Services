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

********************************************************************************
$Date: 2011-01-27 16:06:41 +1100 (Thu, 27 Jan 2011) $
$Revision: 678 $
*******************************************************************************/
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);
// Include required files and initialisation.
//require '../_includes/init.php';
//require 'orca_init.php';
// Page processing
// -----------------------------------------------------------------------------
chdir("/var/www/home/orca");
include('../global_config.php');
include '_functions/orca_data_functions.php';
include '../_includes/_functions/database_functions.php';
include '../_includes/_functions/data_functions.php';
include '../_includes/_environment/database_env.php';
// Connect to the database.
// -----------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_COSI, eCNN_DBS_COSI);
// Connect to the database.
// -----------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);
// Connect to  the PIDS database.
// -----------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_PIDS, eCNN_DBS_PIDS);

// Connect to  the DOIS database.
// -----------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_DOIS, eCNN_DBS_DOIS);

// Authorisation and Access.
// -----------------------------------------------------------------------------
	global $gCNN_DBS_ORCA;
	
	$theMonth = date("2012-01-01");
	$object_count = getRegistryObjectStatCount($theMonth,$registryObjectClass=null);
	$collection_count = getRegistryObjectStatCount($theMonth,$registryObjectClass='Collection');
	$party_count = getRegistryObjectStatCount($theMonth,$registryObjectClass='Party');
	$activity_count = getRegistryObjectStatCount($theMonth,$registryObjectClass='Activity');
	$service_count = getRegistryObjectStatCount($theMonth,$registryObjectClass='Service');
	$trusted_sw_agreement_count = getM2MCount(strtotime($theMonth));
	
	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_stored_stat_count($1, $2, $3, $4, $5, $6, $7)';
	$params = array($theMonth, $object_count, $collection_count, $party_count, $activity_count, $service_count, $trusted_sw_agreement_count);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	
	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
?>
