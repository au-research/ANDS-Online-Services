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

// Constants for activity checks.
define("aORCA_DATA_SOURCE_DELETE", "aORCA_DATA_SOURCE_DELETE");
define("aORCA_REGISTRY_OBJECT_EDIT", "aORCA_REGISTRY_OBJECT_EDIT");
define("aORCA_REGISTRY_OBJECT_DELETE", "aORCA_REGISTRY_OBJECT_DELETE");

// Record Owners
define("SYSTEM", "SYSTEM");

// Statuses
define("APPROVED", "APPROVED");
define("PENDING", "PENDING");
define("ASSESSMENT_IN_PROGRESS","ASSESSMENT_IN_PROGRESS");
define("DELETED","DELETED");
define("DRAFT", "DRAFT");
define("MORE_WORK_REQUIRED","MORE_WORK_REQUIRED");
define("PUBLISHED", "PUBLISHED");
define("SUBMITTED_FOR_ASSESSMENT" , "SUBMITTED_FOR_ASSESSMENT");


// Ownership checks.
function userIsDataSourceRecordOwner($data_source_record_owner)
{
	if (userIsORCA_QA() || userIsORCA_ADMIN())
	{
		return true;
	}
	else 
	{
		return hasRole($data_source_record_owner);
	}
}

function userIsRegistryObjectRecordOwner($registry_object_record_owner)
{
	$isOWner = false;
	if( getThisOrcaUserIdentity() == $registry_object_record_owner )
	{
		$isOWner = true;
	}
	return $isOWner;
}

// Functional role checks.
function userIsORCA_USER()
{
	return hasRole('ORCA_USER');
}

function userIsORCA_QA()
{
	return hasRole('ORCA_QUALITY_ASSESSOR');
}

function userIsORCA_LIAISON()
{
	return hasRole('ORCA_CLIENT_LIAISON');
}

function userIsORCA_SOURCE_ADMIN()
{
	return hasRole('ORCA_SOURCE_ADMIN'); 
}

function userIsORCA_ADMIN()
{
	return hasRole('ORCA_ADMIN');
}

function userIsLoggedIn()
{
	return (getThisOrcaUserIdentity() !== '');
}

// Identity for registry object record ownership.
function getThisOrcaUserIdentity()
{
	$userIdentity = '';
	// temp solution for expired certificate!!!!
	if( getSessionVar(sROLE_ID) && getSessionVar(sAUTH_DOMAIN) )
	{
		$tempFix = str_replace ('http://', 'https://', getSessionVar(sAUTH_DOMAIN));
		$userIdentity = getSessionVar(sROLE_ID).'::'.$tempFix;
	}
	return $userIdentity;
}
?>