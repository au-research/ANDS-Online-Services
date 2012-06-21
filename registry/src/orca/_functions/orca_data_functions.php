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

// Data operations on the ORCA database.
function getIdForColumn($column_identifier)
{
	global $gCNN_DBS_ORCA;

	$id = null;
	$strQuery = 'SELECT dba.udf_get_id($1) AS id';
	$params = array($column_identifier);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if( $resultSet )
	{
		$id = $resultSet[0]['id'];
	}
	return $id;
}

function getDraftCountByStatus($data_source_key, $status=DRAFT)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$strQuery = 'SELECT dba.udf_get_draft_count_by_status($1, $2) AS count';
	$params = array($data_source_key, $status);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}
	return $count;
}


function getDataProviderTypes()
{
	global $gORCA_PROVIDER_TYPES;

	return $gORCA_PROVIDER_TYPES;
}

function getHarvestMethods()
{
	global $gORCA_HARVEST_METHODS;

	return $gORCA_HARVEST_METHODS;
}

function getHarvesterFrequencies()
{
	global $gORCA_HARVESTER_FREQUENCIES;

	return $gORCA_HARVESTER_FREQUENCIES;
}

function insertDataSource()
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_data_source($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29,$30,$31,$32,$33,$34)';
	$params = getParams(array(getLoggedInUser()), $_POST, 34);
	//	print("<pre>");
	//print_r($_POST);
	//var_dump($params);
	//print("</pre>");
	//exit();
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}

	getDataSourceHashForKey($params[1]);

	return $errors;
}

function updateDataSource()
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_update_data_source($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29,$30,$31,$32,$33,$34,$35)';
	$params = getParams(array(getLoggedInUser()), $_POST, 35);
	//print("<pre>");
	//print_r($_POST);
	//var_dump($params);
	//print("</pre>");
	//exit();
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}

	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to update the record.";
	}

	getDataSourceHashForKey($params[1]);

	return $errors;
}
function deleteDataSource($key)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_data_source($1)';
	$params = array($key);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the record.";
	}
	return $errors;
}

function insertHarvestRequest($harvest_request_id, $data_source_key, $harvester_base_uri, $harvester_ip, $response_target_uri, $source_uri, $method, $OAISet, $harvestDate, $harvestFrequency, $mode)
{
	global $gCNN_DBS_ORCA;

	$created_who = getLoggedInUser();
	if( !$created_who )
	{
		$created_who = getUserAgent();
	}
	$status = 'PENDING';

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_harvest_request($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)';
	$params = array($harvest_request_id, $data_source_key, $harvester_base_uri, $harvester_ip, $response_target_uri, $source_uri, $method, $OAISet, $harvestDate, $harvestFrequency, $mode, $created_who, $status);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function updateHarvestRequest($harvest_request_id, $modified_who, $status)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_update_harvest_request($1, $2, $3)';
	$params = array($harvest_request_id, $modified_who, $status);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to update the record.";
	}
	return $errors;
}

function deleteHarvestRequest($harvest_request_id)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_harvest_request($1)';
	$params = array($harvest_request_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the record.";
	}
	return $errors;
}

function getHarvestRequests($harvest_request_id, $data_source_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_harvest_requests($1, $2)';
	$params = array($harvest_request_id, $data_source_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function insertDataSourceEvent($data_source_key, $event_description, $log_type="INFO")
{
	global $gCNN_DBS_ORCA;

	$event_id = getIdForColumn('dba.tbl_data_source_logs.event_id');
	$created_who = getLoggedInUser();
	if( !$created_who )
	{
		$created_who = getUserAgent();
	}
	$request_ip = getRemoteAddress();

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_data_source_event($1, $2, $3, $4, $5, $6)';
	$params = array($event_id, $data_source_key, $created_who, $request_ip, substr($event_description, 0, 2000), $log_type);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function getDataSourceLog($key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_data_source_log($1)';
	$params = array($key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function deleteDataSourceLog($key)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_data_source_log($1)';
	$params = array($key);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the record.";
	}
	return $errors;
}

function getDataSources($key, $filter)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_data_sources($1, $2)';
	$params = array($key, $filter);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

// By Name (basically the wordy vocabulary title)
function getTermsForVocab($vocabName, $term = "")
{

	global $gCNN_DBS_ORCA;
	if ($term == "*") { $term = ""; }
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_terms_in_vocabs($1,$2)';
	$params = array($vocabName,$term);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

// By identifier (the term identifier itself)
function getTermsForVocabByIdentifier($vocabName, $term = "")
{
	global $gCNN_DBS_ORCA;
	if ($term == "*") { $term = ""; }
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_terms_in_vocabs_by_identifier($1,$2)';
	$params = array($vocabName,$term);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function getChildTerms($parent_id, $term = "")
{
	global $gCNN_DBS_ORCA;
	if ($term == "*") { $term = ""; }
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_children_in_vocabs($1,$2)';
	$params = array($parent_id,$term);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getRegistryObjectCount($data_source_key, $group=null, $className=null, $status='PUBLISHED')
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_registry_object_count($1, $2, $3, $4) AS count';
	$params = array($data_source_key, $group, $className, $status);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}

	return $count;
}

function getRegistryObjectFilterCount($className, $indexLetter)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_filter_registry_count($1, $2) AS count';
	$params = array($indexLetter, $className);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}

	return $count;
}

function getRegistryObjectKeysForDataSource($key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_registry_objects_for_data_source($1)';
	$params = array($key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function deleteRegistryObject($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	//deleteSolrIndex($registryObjectkey);
	//$result = deleteSolrIndex($registry_object_key);
	// Unreference the SLUG (set the mapping to a null registry object)
	$slug = getRegistryObjectURLSlug($registry_object_key);
	deleteSLUGMapping($slug);
	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_registry_object($1)';
	$params = array($registry_object_key);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to delete the record.";
	}
	return $errors;
}

function insertRegistryObject($registry_object_key, $registry_object_class, $type, $originating_source, $originating_source_type, $data_source_key, $object_group, $date_accessioned, $date_modified, $created_who, $status, $record_owner, $schema_version = gCURRENT_SCHEMA_VERSION)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_registry_object($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)';
	$params = array($registry_object_key, $registry_object_class, substr($type, 0, 32), substr($originating_source, 0, 512), substr($originating_source_type, 0, 512), $data_source_key, substr($object_group, 0, 512), $date_accessioned, $date_modified, $created_who, $status, $record_owner, $schema_version);
	$z = 0;
	$b=999;

	foreach($params as &$param)
	{
		$z++;
		if( $param === '' )
		{
			$b=$z;
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$e = print_r($params, true);
		$errors = "An error occurred when trying to insert the record. >>>> ".$e;
	}
	return $errors;
}

function updateRegistryObjectStatus($registry_object_key, $status)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_update_registry_object_status($1, $2, $3)';
	$params = array($registry_object_key, $status, getLoggedInUser());

	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to update the record.";
	}
	return $errors;
}

function insertIdentifier($identifier_id, $registry_object_key, $value, $type)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_identifier($1, $2, $3, $4)';
	$params = array($identifier_id, $registry_object_key, substr($value, 0, 512), substr($type, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertComplexName($complex_name_id, $registry_object_key, $type, $date_from, $date_to, $lang)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_complex_name($1, $2, $3, $4, $5, $6)';
	$params = array($complex_name_id, $registry_object_key, substr($type, 0, 512), $date_from, $date_to, substr($lang, 0, 64));
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertNamePart($name_part_id, $complex_name_id, $value, $type)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_name_part($1, $2, $3, $4)';
	$params = array($name_part_id, $complex_name_id, substr($value, 0, 512), substr($type, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertLocation($location_id, $registry_object_key, $date_from, $date_to, $type=null)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_location($1, $2, $3, $4, $5)';
	$params = array($location_id, $registry_object_key, $date_from, $date_to, $type);
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertAddressLocation($address_id, $location_id)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_address_location($1, $2)';
	$params = array($address_id, $location_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertElectronicAddress($electronic_address_id, $address_id, $value, $type)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_electronic_address($1, $2, $3, $4)';
	$params = array($electronic_address_id, $address_id, substr($value, 0, 512), substr($type, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertElectronicAddressArg($electronic_address_arg_id, $electronic_address_id, $name, $required, $type, $use)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_electronic_address_arg($1, $2, $3, $4, $5, $6)';
	$params = array($electronic_address_arg_id, $electronic_address_id, substr($name, 0, 512), $required, substr($type, 0, 512), substr($use, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertPhysicalAddress($physical_address_id, $address_id, $type, $lang)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_physical_address($1, $2, $3, $4)';
	$params = array($physical_address_id, $address_id, substr($type, 0, 512), substr($lang, 0, 64));
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertAddressPart($address_part_id, $physical_address_id, $value, $type)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_address_part($1, $2, $3, $4)';
	$params = array($address_part_id, $physical_address_id, substr($value, 0, 512), substr($type, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertSpatialLocation($spatial_location_id, $location_id, $value, $type, $lang)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_spatial_location($1, $2, $3, $4, $5)';
	$params = array($spatial_location_id, $location_id, substr($value, 0, 512), substr($type, 0, 512), $lang);
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertSpatialCoverage($spatial_coverage_id, $coverage_id, $value, $type, $lang)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_spatial_coverage($1, $2, $3, $4, $5)';
	$params = array($spatial_coverage_id, $coverage_id, substr($value, 0, 512), substr($type, 0, 512), $lang);
	foreach($params as &$param)
	{
		if( $param == '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertSpatialExtent($spatial_location_id, $location_id, $registryObjectKey, $north, $south, $west, $east)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	if($north !== null && $south !== null && $west !== null && $east !== null)
	{
		$strQuery = 'SELECT dba.udf_insert_spatial_extent($1, $2, $3, $4, $5, $6, $7)';
		$params = array($spatial_location_id, $location_id, $registryObjectKey, $north, $south, $west, $east);

		foreach($params as &$param)
		{
			if( $param === '' )
			{
				$param = null;
			}
		}
		$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

		if( !$resultSet )
		{
			$errors = "An error occurred when trying to insert the record.";
		}
	}
	else
	{
		$errors = "SpatialExtent wasn't calculated for ".$registryObjectKey ;

	}
	return $errors;
}


function insertRelatedObject($relation_id, $registry_object_key, $related_registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_related_object($1, $2, $3)';
	$params = array($relation_id, $registry_object_key, $related_registry_object_key);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertFullCitationInformation($id, $registry_object_key, $style, $value)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_citation_information($1, $2, $3, $4)';
	$params = array($id, $registry_object_key, $style, $value);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertCitationMetadata($id, $registry_object_key, $identifier, $identifier_type, $title, $edition, $placePublished, $url, $context, $publisher)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_citation_information($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)';
	$params = array($id, $registry_object_key, $identifier, $identifier_type, $title, $edition, $placePublished, $url, $context, $publisher);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertExistenceDates($dates_id, $registryObjectKey, $start_date, $start_date_format, $end_date, $end_date_format)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_existence_dates($1, $2, $3, $4, $5, $6)';
	$params = array($dates_id, $registryObjectKey, $start_date, $start_date_format, $end_date, $end_date_format);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function getExistenceDates($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_existence_dates($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function insertRights($rights_id, $registryObjectKey, $rights_statement, $rights_statement_uri, $licence, $licence_uri, $access_rights, $access_rights_uri, $licence_type='', $access_rights_type='')
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_rights($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)';
	$params = array($rights_id, $registryObjectKey, $rights_statement, $rights_statement_uri, $licence, $licence_uri, $access_rights, $access_rights_uri, $licence_type, $access_rights_type);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertCitationDate($id, $citation_info_id, $value, $type)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_citation_date($1, $2, $3, $4)';
	$params = array( $id, $citation_info_id, $value, $type );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertCitationContributor($id, $citation_info_id, $seq)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	if($seq == '')
	$seq = null;
	$strQuery = 'SELECT dba.udf_insert_citation_contributor($1, $2, $3)';
	$params = array( $id, $citation_info_id, $seq );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}


function insertCoverage($id, $registry_object_key)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_coverage($1, $2)';
	$params = array( $id, $registry_object_key );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertTemporalCoverage($id, $coverage_id)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_temporal_coverage($1, $2)';
	$params = array( $id, $coverage_id );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertTemporalCoverageText($id, $temporal_coverage_id, $value)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_temporal_coverage_text($1, $2, $3)';
	$params = array( $id, $temporal_coverage_id, $value );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertTemporalCoverageDate($id, $temporal_coverage_id, $value, $type, $date_format, $timestamp)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_temporal_coverage_date($1, $2, $3, $4, $5, $6)';
	$params = array( $id, $temporal_coverage_id, $value, $type, $date_format, $timestamp );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;


}

function insertCitationContributorNamePart ($id, $citation_contributor_id, $value, $type)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_contributor_name_part($1, $2, $3, $4)';
	$params = array( $id,$citation_contributor_id, $value, $type );
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}

function insertRelationDescription($relation_description_id, $relation_id, $description, $type, $lang, $url)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_relation_description($1, $2, $3, $4, $5, $6)';
	$params = array($relation_description_id, $relation_id, substr($description, 0, 512), substr($type, 0, 512), substr($lang, 0, 64), substr($url, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertSubject($subject_id, $registry_object_key, $value, $type, $termIdentifier, $lang)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_subject($1, $2, $3, $4, $5, $6)';
	$params = array($subject_id, $registry_object_key, substr($value, 0, 512), substr($type, 0, 512), substr($termIdentifier, 0, 512), substr($lang, 0, 64));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertDescription($description_id, $registry_object_key, $value, $type, $lang)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_description($1, $2, $3, $4, $5)';
	$params = array($description_id, $registry_object_key, substr($value, 0, 4000), substr($type, 0, 512), substr($lang, 0, 64));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertAccessPolicy($access_policy_id, $registry_object_key, $value)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_access_policy($1, $2, $3)';
	$params = array($access_policy_id, $registry_object_key, substr($value, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}


function insertRelatedInfoOld($related_info_id, $registry_object_key, $value)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_related_info_old($1, $2, $3)';
	$params = array($related_info_id, $registry_object_key, substr($value, 0, 512));
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}


function insertRelatedInfo($related_info_id, $registry_object_key, $info_type, $identifier = null, $identifier_type = null, $title = null, $notes = null)
{
	global $gCNN_DBS_ORCA;

	// v1.0 to v1.2 (for backwards compatibility)
	if ($identifier === null) {
		$identifier = $info_type;
		$info_type = 'website';
		$identifier_type = 'uri';
		$title = null;
		$notes = null;
	}

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_related_info($1, $2, $3, $4, $5, $6, $7)';
	$params = array($related_info_id, $registry_object_key, $info_type, substr($identifier, 0, 512), $identifier_type, $title, $notes);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}


function getRegistryObject($registry_object_key, $overridePermissions = false)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.tbl_registry_objects where registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if( $resultSet )
	{
		// Get the values that we'll need to check for conditional display and access.
		$registryObjectRecordOwner = $resultSet[0]['record_owner'];
		$registryObjectStatus = trim($resultSet[0]['status']);

		// Check access.
		if( !(in_array($registryObjectStatus, array(PUBLISHED, APPROVED)) || userIsORCA_ADMIN() || userIsRegistryObjectRecordOwner($registryObjectRecordOwner)) && !$overridePermissions )
		{
			$resultSet = null;
		}
	}

	return $resultSet;
}


function getDraftRegistryObject($draft_key, $data_source)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_draft_registry_object($1, $2)';
	$params = array($draft_key, $data_source);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}



function getObjectGroups()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_object_groups() AS object_group';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getStatuses()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_statuses() AS status';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getDistinctDataSourceKeyObjectGroups()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT DISTINCT data_source_key, object_group FROM dba.tbl_registry_objects ORDER BY object_group ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getRegistryObjectClasses()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT DISTINCT registry_object_class FROM dba.tbl_registry_objects';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getDistinctDataSourceKeyObjectGroupRegistryObjectClasses()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT DISTINCT data_source_key, object_group, registry_object_class FROM dba.tbl_registry_objects ORDER BY registry_object_class ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getRegistryObjectTypes()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT DISTINCT type FROM dba.tbl_registry_objects ORDER BY type ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getDistinctDataSourceKeyObjectGroupRegistryObjectClassTypes()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT DISTINCT data_source_key, object_group, registry_object_class, type FROM dba.tbl_registry_objects ORDER BY registry_object_class, type ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getSubjectTypes()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT DISTINCT type FROM dba.tbl_subjects ORDER BY type ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getAllSubjects()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.tbl_subjects ORDER BY registry_object_key ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getRegistryObjects()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.vw_registry_objects ORDER BY registry_object_key ASC';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function getAllRegistryObjectKey()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'select registry_object_key from dba.tbl_registry_objects';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, null);

	return $resultSet;
}

function searchRegistry($search_string, $classes, $data_source_key, $object_group, $created_before_equals, $created_after_equals, $status=PUBLISHED, $record_owner=null)
{
	global $gCNN_DBS_ORCA;

	$search_string = str_replace("%", "\%", $search_string);

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_registry($1, $2, $3, $4, $5, $6, $7, $8)';
	$params = array($search_string, $classes, $data_source_key, $object_group, $created_before_equals, $created_after_equals, $status, $record_owner);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getSpecialObjectSet($specialSetFlag, $class)
{
	global $gCNN_DBS_ORCA;


	$resultSet = null;
	if($specialSetFlag=="nlaSet")
	{
		$strQuery = 'SELECT * FROM dba.udf_get_nla_set($1)';
		$params = array($class);
	}

	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function getDraftsByDataSource($data_source)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_draft_registry_object(NULL, $1)';
	$params = array($data_source);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return ($resultSet == null ? array() : $resultSet);
}

function getDraftsByKey($draft_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_draft_registry_object($1, NULL)';
	$params = array($draft_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return ($resultSet == null ? array() : $resultSet);
}

function insertDraftRegistryObject($draft_owner, $draft_key, $draft_class, $draft_group, $draft_type, $draft_title, $draft_data_source,  $date_created, $date_modified, $rifcs, $quality_test_result, $error_count, $warning_count, $status='DRAFT')
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_draft_registry_object($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)';
	$params = array($draft_owner, $draft_key, $draft_class, $draft_group, $draft_type, $draft_title, $draft_data_source,  $date_created, $date_modified, $rifcs, $quality_test_result, $error_count, $warning_count, $status);
	foreach($params as &$param)
	{
		if( $param === '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}


function insertRawRecord($registry_object_key, $data_source, $created_when, $created_who, $rifcs)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_raw_record($1, $2, $3, $4, $5)';
	$params = array($registry_object_key, $data_source, $created_when, $created_who, $rifcs);
	foreach($params as &$param)
	{
		if( $param === '' )
		{
			$param = null;
		}
	}
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the raw record.";
	}
	return $errors;
}


function getRawRecords($registry_object_key, $data_source_key, $created_when = NULL)
{

	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_raw_records($1, $2, $3)';
	$params = array($registry_object_key, $data_source_key, $created_when);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;

}


function deleteDraftRegistryObject($data_source, $draft_key)
{
	global $gCNN_DBS_ORCA;
	$errors = "";
	$strQuery = 'SELECT dba.udf_delete_draft_registry_object($1, $2)';
	$params = array($data_source, $draft_key);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return;
}


function updateDraftRegistryObjectStatus($draft_key, $data_source, $status)
{
	global $gCNN_DBS_ORCA;
	$errors = "";
	$strQuery = 'SELECT dba.udf_update_draft_registry_object_status($1, $2, $3)';
	$params = array($draft_key, $data_source, $status);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return;
}

function getRegistryObjectsInBound($north, $south, $west, $east)
{
	global $gCNN_DBS_ORCA;
	$resultSet = null;
	if(floatval($west) < -90 && floatval($east) > 90)
	{ // over the meridian
	$strQuery = 'SELECT * FROM dba.udf_get_registry_objects_inbound_two($1, $2, $3, $4, $5, $6, $7, $8)';
	$params = array($north, $south, $west, -180, $north, $south, 180, $east);
	}
	else
	{
	$strQuery = 'SELECT * FROM dba.udf_get_registry_objects_inbound($1, $2, $3, $4)';
	$params = array($north, $south, $west, $east);
	}

	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getGroups ($restrictions="", $limit = 10) {
	global $gCNN_DBS_ORCA;

	$strQuery = 'SELECT DISTINCT object_group FROM dba.tbl_registry_objects ' . $restrictions .' LIMIT ' . $limit;
	return executeQuery($gCNN_DBS_ORCA, $strQuery);
}

function searchRegistryObjectsInBound($north, $south, $west, $east, $search_string, $classes)
{
	global $gCNN_DBS_ORCA;
	$resultSet = null;
	if(floatval($west) < -90 && floatval($east) > 90)
	{ // over the meridian
	$strQuery = 'SELECT * FROM dba.udf_search_registry_objects_inbound_two($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)';
	$params = array($north, $south, $west, -180, $north, $south, 180, $east, $search_string, $classes);
	}
	else
	{
	$strQuery = 'SELECT * FROM dba.udf_search_registry_objects_inbound($1, $2, $3, $4, $5, $6)';
	$params = array($north, $south, $west, $east, $search_string, $classes);
	}
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function filterRegistry($filter_string, $classes, $group=null)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_filter_registry($1, $2, $3)';
	$params = array($filter_string, $classes, $group);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getNames($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_registry_object_names($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getNLAPartyIdentifier($registry_object_key)
{
	global $NLAPartyTypeKey;
	$NLAPartyKey = null;

	$identifiers = getIdentifiers($registry_object_key);
	if ($identifiers) {
		foreach ($identifiers AS $identifier) {
			if (in_array(strtoupper($identifier['type']), $NLAPartyTypeKey))
				$NLAPartyKey = $identifier['value'];
		}
	}

	return $NLAPartyKey;
}

function getSimpleNames($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_simple_names($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getComplexNames($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_complex_names($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getNameParts($complex_name_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_name_parts($1)';
	$params = array($complex_name_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getIdentifiers($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_identifiers($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getSubjects($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_subjects($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getDescriptions($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_descriptions($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getRights($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_rights($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}

/*
 * Get the value of the registry object's description[@type='logo'] (if any)
 * If multiple are returned, will either choose randomly ($random=true) or
 * pop the last logo off the end (working under the assumption that getDescriptions
 * returns descriptions in ascending order of ids)
 */
function getDescriptionLogo($registry_object_key, $random=true)
{
	$all_descriptions = getDescriptions($registry_object_key);

	$logos = array();
	if (!$all_descriptions) { return false; }
	foreach ($all_descriptions AS $description)
	{
		if (strtolower($description['type']) == "logo")
		{
			$logos[] = $description['value'];
		}
	}

	if (count($logos) > 0)
	{
		if ($random)
		{
			return strip_tags($logos[array_rand($logos)]);
		}
		else
		{
			return strip_tags(array_pop($logos));
		}
	}
	else
	{
		return false;
	}
}

function getRelatedObjects($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_related_objects($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getInternalReverseRelatedObjects($registry_object_key, $data_source_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_internal_reverse_related_objects($1,$2)';
	$params = array($registry_object_key, $data_source_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getExternalReverseRelatedObjects($registry_object_key, $data_source_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_external_reverse_related_objects($1,$2)';
	$params = array($registry_object_key, $data_source_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function getRelationDescriptions($relation_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_relation_descriptions($1)';
	$params = array($relation_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getAccessPolicies($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_access_policies($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getExistenceDate($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_existence_dates($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getRelatedInfo($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_related_info($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getLocations($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_locations($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getAddressLocations($location_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_address_locations($1)';
	$params = array($location_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getSpatialLocations($location_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_spatial_locations($1)';
	$params = array($location_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getRegistryObjectSpatialLocations($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_registry_object_spatial_locations($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getPhysicalAddresses($address_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_physical_addresses($1)';
	$params = array($address_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getRegistryObjectPhysicalAddresses($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_registry_object_physical_addresses($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getAddressParts($physical_address_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_address_parts($1)';
	$params = array($physical_address_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getElectronicAddresses($address_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_electronic_addresses($1)';
	$params = array($address_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getRegistryObjectElectronicAddresses($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_registry_object_electronic_addresses($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getElectronicAddressArgs($electronic_address_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_electronic_address_args($1)';
	$params = array($electronic_address_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getMinCreatedWhen()
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_earliest_created_when() AS min_created_when';
	$params = array();
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	$minCreatedWhen = '';
	if( $resultSet )
	{
		$minCreatedWhen = $resultSet[0]['min_created_when'];
	}

	return $minCreatedWhen;
}

function getResumptionToken($resumption_token_id, $complete_list_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_resumption_token($1, $2)';
	$params = array($resumption_token_id, $complete_list_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getIncompleteList($complete_list_id, $first_record_number)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_incomplete_list($1, $2, $3)';
	$params = array($complete_list_id, $first_record_number, OAI_LIST_SIZE);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function cleanupCompleteLists()
{
	global $gCNN_DBS_ORCA;

	$strQuery = 'SELECT dba.udf_cleanup_complete_lists()';
	$params = array();
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
}

function insertCompleteList()
{
	global $gCNN_DBS_ORCA;

	$complete_list_id = getIdForColumn('dba.tbl_oai_rt_complete_lists.complete_list_id');

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_complete_list($1)';
	$params = array($complete_list_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$complete_list_id = null;
	}

	return $complete_list_id;
}

function insertCompleteListRecord($complete_list_id, $record_number, $registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_complete_list_record($1, $2, $3)';
	$params = array($complete_list_id, $record_number, $registry_object_key);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function insertResumptionToken($complete_list_id, $first_record_number, $complete_list_size, $metadata_prefix)
{
	global $gCNN_DBS_ORCA;

	$resumption_token_id = strtoupper(sha1($complete_list_id.':'.microtime(false)));
	$status              = OAI_RT_LATEST;
	$expiration_date     = date('c', strtotime("now") + (OAI_RT_EXPIRES_MINUTES*60));

	$errors = "";
	$strQuery = 'SELECT dba.udf_insert_resumption_token($1, $2, $3, $4, $5, $6, $7)';
	$params = array($resumption_token_id, $complete_list_id, $status, $first_record_number, $complete_list_size, $expiration_date, $metadata_prefix);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function updateResumptionTokens($complete_list_id)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$strQuery = 'SELECT dba.udf_update_resumption_tokens($1)';
	$params = array($complete_list_id);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;
}

function updateRegistryObjectTitles ($registry_object_key, $display_title='', $list_title='')
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_update_registry_object_titles($1, $2, $3);';
	$params = array($registry_object_key, $display_title, $list_title);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( !$resultSet )
	{
		$errors = "An error occurred when trying to insert the record.";
	}
	return $errors;

}


function updateRegistryObjectQualityTestResult($registry_object_key, $quality_test_result, $error_count, $warning_count)
{

	global $gCNN_DBS_ORCA;

	$errors = "";
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_update_registry_quality_test_result($1, $2, $3, $4);';
	$params = array($registry_object_key, $quality_test_result, $error_count, $warning_count);
	$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if(!$resultSet){
		$errors = 'An Error Occurred When Updating Quality Test Result';
	}
	return $errors;

}



function setRegistryObjectGoldStandardFlag($registry_object_key, $gs_flag)
{
	if (in_array($gs_flag, array(0,1)))
	{
		global $gCNN_DBS_ORCA;
        $strQuery = 'UPDATE dba.tbl_registry_objects SET gold_status_flag = $2 WHERE registry_object_key = $1';
        $params = array($registry_object_key, $gs_flag);
        $resultSet = @executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	}
}

function setRegistryObjectManuallyAssessedFlag($registry_object_key)
{
		global $gCNN_DBS_ORCA;
        $strQuery = 'UPDATE dba.tbl_registry_objects SET manually_assessed_flag = 1 WHERE registry_object_key = $1';
        $params = array($registry_object_key);
        $resultSet = @executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
}

function updateRegistryObjectQualityLevelResult($registry_object_key, $quality_level,  $quality_level_result)
{

	if (in_array($quality_level, array(0,1,2,3,4)))
	{
		global $gCNN_DBS_ORCA;
		$errors = "";
		$resultSet = null;
		$strQuery = 'UPDATE dba.tbl_registry_objects SET quality_level = $2, quality_level_result = $3 WHERE registry_object_key = $1';
		$params = array($registry_object_key, $quality_level, $quality_level_result);
		$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
		return $result;
	}
}


function updateDraftRegistryObjectQualityLevelResult($draft_key, $registry_object_data_source, $quality_level,  $quality_level_result)
{
	if (in_array($quality_level, array(0,1,2,3,4)))
	{
		global $gCNN_DBS_ORCA;
		$errors = "";
		$resultSet = null;
		$strQuery = 'UPDATE dba.tbl_draft_registry_objects SET quality_level = $3, quality_level_result = $4 WHERE draft_key = $1 AND registry_object_data_source = $2';
		$params = array($draft_key, $registry_object_data_source, $quality_level, $quality_level_result);
		$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
		return $result;
	}
}


function updateDraftRegistryObjectQualityTestResult($registryObjectKey, $dataSourceKey, $qualityTestResult, $errorCount, $warningCount)
{
	global $gCNN_DBS_ORCA;

	$errors = "";
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_update_draft_quality_test_result($1, $2, $3, $4, $5);';
	$params = array($registryObjectKey, $dataSourceKey, $qualityTestResult, $errorCount, $warningCount);
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;

}


function getHighlightedQueryText($text, $queryText)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_highlighted_querytext($1, $2) AS text';
	$params = array($text, $queryText);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	$markedText = '';

	if( $resultSet )
	{
		$markedText = $resultSet[0]['text'];
	}

	return $markedText;
}

function deleteRelatedObject($registryObjectKey, $relatedRegistryObjectKey)
{
	global $gCNN_DBS_ORCA;

	if( $relatedObjects = getRelatedObjects($registryObjectKey) )
	{
		foreach( $relatedObjects as $relatedObject )
		{
			if( $relatedObject['related_registry_object_key'] == $relatedRegistryObjectKey )
			{
				$relationId = $relatedObject['relation_id'];
				$params = array();
				$strQuery = 'DELETE FROM dba.tbl_relation_descriptions WHERE relation_id='.$relationId;
				$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
				$strQuery = 'DELETE FROM dba.tbl_related_objects WHERE relation_id='.$relationId;
				$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
				break;
			}
		}
	}
}

function getCoverage($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_coverage($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getSpatialCoverage($coverage_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_spatial_coverage($1)';
	$params = array($coverage_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getTemporalCoverage($coverage_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_temporal_coverage($1)';
	$params = array($coverage_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function getTemporalCoverageText($temporal_coverage_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_temporal_coverage_text($1)';
	$params = array($temporal_coverage_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getTemporalCoverageDate($temporal_coverage_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_temporal_coverage_dates($1)';
	$params = array($temporal_coverage_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getCitationInformation($registry_object_key)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_citation_information($1)';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getCitationDates($citation_info_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_citation_dates($1)';
	$params = array($citation_info_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function getCitationContributors($citation_info_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_citation_contributors($1)';
	$params = array($citation_info_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getCitationContributorNameParts($citation_contributor_id)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_contributor_name_parts($1)';
	$params = array($citation_contributor_id);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function searchForNameParts($searchText, $objectClass, $dataSourceKey, $limit)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_names($1,$2,$3,$4)';
	$params = array($searchText, $objectClass, $dataSourceKey, $limit);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	$values = array();
	if(isset($resultSet ) && $resultSet )
	{
		while ($registryObjectKey = current($resultSet))
		{
	        $values[] = $registryObjectKey['udf_search_names'];
	    	next($resultSet);
		}
	}


	return $values;
}

function getQualityLevel($registry_object_key, $dataSourceKey, $status)
{
	// getLevelsResult uses quality_level_result, otherwise use quality_test_result
	$field = 'quality_level';

	if($status!='PUBLISHED' && $status!='APPROVED'){
		//it is a draft
		$strQuery = 'SELECT * FROM dba.tbl_draft_registry_objects WHERE draft_key = $1 AND registry_object_data_source = $2';
		$params = array($registry_object_key, $dataSourceKey);
	}else{
		//is not a draft
		$strQuery = 'SELECT * FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
		$params = array($registry_object_key);
	}

	global $gCNN_DBS_ORCA;


	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);


	if( $resultSet ){

		//if(isset($resultSet[0]['gold_status_flag']) && $resultSet[0]['gold_status_flag']==1){
		//	return  'This record is marked as gold standard';
		//}

		if($resultSet[0][$field]){
			$result = $resultSet[0][$field];
		}else{
			$result= null;
		}
	}else{
		$result = null;
	}

	return $result;
}


function getQualityTestResult($registry_object_key, $dataSourceKey, $status, $getLevelsResult = true){

	// getLevelsResult uses quality_level_result, otherwise use quality_test_result
	$field = ($getLevelsResult ? 'quality_level_result' : 'quality_test_result');

	if($status!='PUBLISHED' && $status!='APPROVED'){
		//it is a draft
		$strQuery = 'SELECT * FROM dba.tbl_draft_registry_objects WHERE draft_key = $1 AND registry_object_data_source = $2';
		$params = array($registry_object_key, $dataSourceKey);
	}else{
		//is not a draft
		$strQuery = 'SELECT * FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
		$params = array($registry_object_key);
	}

	global $gCNN_DBS_ORCA;


	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);


	if( $resultSet ){

		//if(isset($resultSet[0]['gold_status_flag']) && $resultSet[0]['gold_status_flag']==1){
		//	return  'This record is marked as gold standard';
		//}

		if($resultSet[0][$field]){
			$result = $resultSet[0][$field];
		}else{
			$result= "No QA for key $registry_object_key in Data Source $dataSourceKey";
		}
	}else{
		$result = "No QA for key $registry_object_key in Data Source $dataSourceKey";
	}

	return $result;
}




function searchByName($searchText, $objectClass, $dataSourceKey, $limit)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_by_name($1,$2,$3,$4)';
	$params = array($searchText, $objectClass, $dataSourceKey, $limit);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}

function searchDraftByName($searchText, $objectClass, $dataSourceKey, $limit)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_search_draft_by_name($1,$2,$3,$4)';
	$params = array($searchText, $objectClass, $dataSourceKey, $limit);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}




// functions defind to obtain registry statistics
//------------------------------------------------------------
function getDataSourceCount($created_when=null)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_data_source_count($1) AS count';
	$params = array($created_when);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}

	return $count;
}

function getPublishMyDataCount($created_when=null)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_publish_my_data_object_count($1) AS count';
	$params = array($created_when);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}

	return $count;
}

function getHarvestMethodCount($created_when=null,$harvest_method)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_harvest_method_count($1,$2) AS count';
	$params = array($created_when,$harvest_method);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}

	return $count;
}

function getRegistryObjectStatCount($created_when=null,$registry_object_class)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_registry_object_stat_count($1,$2) AS count';
	$params = array($created_when,$registry_object_class);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0]['count'];
	}

	return $count;
}

function getStoredStatCount($created_when=null,$table_column)
{
	global $gCNN_DBS_ORCA;

	$count = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_stored_stat_count($1,$2)';
	$params = array($created_when,$table_column);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if( $resultSet )
	{
		$count = $resultSet[0][$table_column];
	}

	return $count;
}

function getRegistryObjectTypeCount($created_when=null,$registry_object_class=null,$object_type=null)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_registry_object_type_count($1,$2,$3) AS count';
	$params = array($created_when,$registry_object_class,$object_type);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getDescriptionTypeCount($created_when=null)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_descriptions_type_count($1) AS count';
	$params = array($created_when);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}
function getRelatedInfoTypeCount($created_when=null)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT dba.udf_get_related_info_type_count($1) AS count';
	$params = array($created_when);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function getRelatedObjectCount($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT COUNT(*) AS "count" FROM dba.tbl_related_objects WHERE registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return 0;
	else
		return $resultSet[0]['count'];
}

function getIncomingRelatedObjectCount($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT COUNT(*) AS "count" FROM dba.tbl_related_objects WHERE related_registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return 0;
	else
		return $resultSet[0]['count'];
}

// function defined to obtain real subject values for dc xml
//------------------------------------------------------------
function getSubjectValue($identifier=NULL)
{
 	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$value = null;
	$strQuery = 'SELECT * FROM dba.udf_get_subject_value($1)';

	$params = array($identifier);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet[0]["name"] ;
}
// functions defined to obtain nla identifier keys
//------------------------------------------------------------
function getPartyIdentifiers()
{
 	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_nla_nonlinked_related_objects() AS "partyIdentifier"';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery,null);

	return $resultSet ;
}

function getPartyNLAIdentifiers()
{
 	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.udf_get_party_nla_identifiers() AS "partyIdentifier"';
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery,null);

	return $resultSet ;
}

// functions to format name parts
function getOrderedNames($registry_object_key, $isParty=false, $asDisplayTitle=true, $chooseRandom=true)
{
	$display_title = "";
	// Get the primary name (if available)
	$complexNames = getComplexNames($registry_object_key);
	$primaryNames = array();

	if (!is_array($complexNames) || count($complexNames) == 0)
	{
		$display_title = "(no name/title)";
	}
	else
	{
		$primary_name_id = null;

		// Get primary names
		foreach ($complexNames AS $complexName)
		{
			if ($complexName['type'] == "primary")
			{
				$primaryNames[] = $complexName['complex_name_id'];
			}
		}

		// Pick a primary name
		if (count($primaryNames) == 0)
		{
			$primary_name_id = $complexNames[array_rand($complexNames)];
			$primary_name_id = $primary_name_id['complex_name_id'];
		}
		else
		{
			$primary_name_id = $primaryNames[array_rand($primaryNames)];
		}

		$nameParts = getNameParts($primary_name_id);

		if (!is_array($nameParts) || count($nameParts) == 0)
		{
			$display_title = "(no name/title)";
		}
		else if(count($nameParts) == 1)
		{
			$display_title = $nameParts[0]['value'];
		}
		else
		{
			if ($isParty)
			{
				$partyNameParts = array();
				$partyNameParts['title'] = array();
				$partyNameParts['suffix'] = array();
				$partyNameParts['initial'] = array();
				$partyNameParts['given'] = array();
				$partyNameParts['family'] = array();
				$partyNameParts['user_specified_type'] = array();

				foreach ($nameParts AS $namePart)
				{
					if (in_array(strtolower($namePart['type']), array_keys($partyNameParts)))
					{
						$partyNameParts[strtolower($namePart['type'])][] = trim($namePart['value']);
					}
					else
					{
						$partyNameParts['user_specified_type'][] = trim($namePart['value']);
					}
				}

				if ($asDisplayTitle)
				{
					$display_title = 	(count($partyNameParts['title']) > 0 ? implode(" ", $partyNameParts['title']) . " " : "") .
										(count($partyNameParts['given']) > 0 ? implode(" ", $partyNameParts['given']) . " " : "") .
										(count($partyNameParts['initial']) > 0 ? implode(" ", $partyNameParts['initial']) . " " : "") .
										(count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) . " " : "") .
										(count($partyNameParts['suffix']) > 0 ? implode(" ", $partyNameParts['suffix']) . " " : "") .
										(count($partyNameParts['user_specified_type']) > 0 ? implode(" ", $partyNameParts['user_specified_type']) . " " : "");
				}
				else
				{
					foreach ($partyNameParts['given'] AS &$givenName)
					{
						$givenName = (strlen($givenName) == 1 ? $givenName . "." : $givenName);
					}

					foreach ($partyNameParts['initial'] AS &$initial)
					{
						$initial = $initial . ".";
					}

					$display_title = 	(count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) : "") .
										(count($partyNameParts['given']) > 0 ? ", " . implode(" ", $partyNameParts['given']) : "") .
										(count($partyNameParts['initial']) > 0 ? " " . implode(" ", $partyNameParts['initial']) : "") .
										(count($partyNameParts['title']) > 0 ? ", " . implode(" ", $partyNameParts['title']) : "") .
										(count($partyNameParts['suffix']) > 0 ? ", " . implode(" ", $partyNameParts['suffix']) : "") .
										(count($partyNameParts['user_specified_type']) > 0 ? " " . implode(" ", $partyNameParts['user_specified_type']) . " " : "");
				}

			}
			else
			{
				$np = array();
				foreach ($nameParts as $namePart)
				{
					$np[] = trim($namePart['value']);
				}

				$display_title = implode(" ", $np);
			}
		}

	}

	return $display_title;
}


function setDraftFlag($draft_key, $data_source, $flag)
{
	if (in_array($flag, array("true","false")))
	{
		global $gCNN_DBS_ORCA;
        $strQuery = 'UPDATE dba.tbl_draft_registry_objects SET flag = $1 WHERE draft_key = $2 AND registry_object_data_source = $3';
        $params = array($flag, $draft_key, $data_source);
        $resultSet = @executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	}
}

function setGoldFlag($key)
{
	global $gCNN_DBS_ORCA;
    $strQuery = 'UPDATE dba.tbl_registry_objects SET gold_status_flag = 1 , quality_level = 5 WHERE registry_object_key = $1';
    $params = array($key);
    $resultSet = @executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
}


function getGoldFlag($key)
{
	global $gCNN_DBS_ORCA;
	$r='';
	$strQuery = 'SELECT gold_status_flag FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
	$params = array($key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if( $resultSet )
	{
		$r = $resultSet[0]['gold_status_flag'];
	}
	return $r;
}



function insertTaskRequest($task_type, $created_by, $param_1='', $param_2='', $param_3='', $trigger_time)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'INSERT INTO dba.tbl_queued_tasks (task_type, created_by, param_1, param_2, param_3, queue_time, trigger_time) '.
				'VALUES ($1, $2, $3, $4, $5, $6, $7)';
	$params = array($task_type, $created_by, $param_1, $param_2, $param_3, time(), $trigger_time);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function updateTaskRequest($task_id, $start_time, $finish_time)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'UPDATE dba.tbl_queued_tasks SET start_time = $2 , finish_time = $3 WHERE task_id = $1';
	$params = array($task_id, $start_time, $finish_time);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

// Get all outstanding task requests (tasks that haven't yet been started)
// which have a trigger time in the past ([arbitrary] default: before 1/1/2020)
function getTaskRequests($trigger_time=1577862061)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT * FROM dba.tbl_queued_tasks WHERE trigger_time <= $1 AND start_time = 0 AND finish_time = 0';
	$params = array($trigger_time);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}


function updateDataSourceHash($data_source_key, $hash)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'UPDATE dba.tbl_data_sources SET key_hash = $2 WHERE data_source_key = $1';
	$params = array($data_source_key, $hash);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getDataSourceHash($data_source_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT key_hash FROM dba.tbl_data_sources WHERE data_source_key = $1';
	$params = array($data_source_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['key_hash'];
}

function getDataSourceByHash($hash)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.tbl_data_sources WHERE key_hash = $1';
	$params = array($hash);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getRegistryObjectByHash($hash)
{
	global $gCNN_DBS_ORCA;

	$resultSet = null;
	$strQuery = 'SELECT * FROM dba.tbl_registry_objects WHERE key_hash = $1';
	$params = array($hash);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function getRegistryObjectHash($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT key_hash FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['key_hash'];
}


function updateRegistryObjectHash($registry_object_key, $hash)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'UPDATE dba.tbl_registry_objects SET key_hash = $2 WHERE registry_object_key = $1';
	$params = array($registry_object_key, $hash);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}

function getRegistryObjectDataSourceKey($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT data_source_key FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['data_source_key'];
}

function getRegistryObjectRegistryDateModified($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT registry_date_modified FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['registry_date_modified'];
}

function getRegistryObjectStatusModified($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT status_modified_when FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['status_modified_when'];
}


function getRegistryObjectURLSlug($registry_object_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT url_slug FROM dba.tbl_registry_objects WHERE registry_object_key = $1';
	$params = array($registry_object_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['url_slug'];

}


function updateRegistryObjectSLUG ($registry_object_key, $new_display_title, $current_slug = '')
{
	global $gCNN_DBS_ORCA;

	if ($current_slug != '')
	{
		// Update the previous mapping (in case the key has been dereferenced?)
		updateSLUGMapping($current_slug, $registry_object_key, $new_display_title);

		// Now we should check whether the new title warrants a new SLUG
		// if so, we should maintain a new SLUG for it too...
		$updated_slug = generateUniqueSlug($new_display_title, $registry_object_key);

		// title has changed, use the updated slug as primary
		if ($current_slug != $updated_slug)
		{
			// If we're going *back* to a SLUG we used previously
			if (slugExist($updated_slug))
			{
				updateSLUGMapping($updated_slug, $registry_object_key, $new_display_title);
			}
			else
			{
				insertSLUGMapping($updated_slug, $registry_object_key, $new_display_title);
			}
		}

		// update the registry object to point to the latest slug (which will be the same as $current slug, if unchanged)
		$strQuery = 'UPDATE dba.tbl_registry_objects SET url_slug = $2 WHERE registry_object_key = $1';
		$params = array($registry_object_key, $updated_slug);
		$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

		return;
	}
	else
	{
		$updated_slug = generateUniqueSlug($new_display_title, $registry_object_key);

		//Add the new SLUG mapping
		insertSLUGMapping($updated_slug, $registry_object_key, $new_display_title);

		//update the slug for the registry object
		$strQuery = 'UPDATE dba.tbl_registry_objects SET url_slug = $2 WHERE registry_object_key = $1';
		$params = array($registry_object_key, $updated_slug);
		$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
		return;
	}
}

function slugExist($slug){
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT * FROM dba.tbl_url_mappings where url_fragment = $1';
	$params = array($slug);
	$existingSlug = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if($existingSlug){
		return true;
	}else{
		return false;
	}
}

function insertSLUGMapping($slug, $key, $current_title)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'INSERT INTO dba.tbl_url_mappings VALUES ($1, $2, $3, $4, $5)';
	$params = array($slug, $key, time(), 0, $current_title);
	//var_dump($params);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	return $resultSet;
}

function updateSLUGMapping($slug, $key, $current_title)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'UPDATE dba.tbl_url_mappings SET registry_object_key = $2, search_title = $3, date_modified = $4 WHERE url_fragment = $1';
	$params = array($slug, $key, $current_title, time());
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}


function deleteSLUGMapping($slug)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'UPDATE dba.tbl_url_mappings SET registry_object_key = \'\', date_modified = $2 WHERE url_fragment = $1';
	$params = array($slug, time());
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}


function countOtherSLUGMappings($slug, $key)
{
	global $gCNN_DBS_ORCA;
	// if the slug doesn't point to our key count it
	$strQuery = 'SELECT COUNT(*) as count FROM dba.tbl_url_mappings WHERE registry_object_key != $1 AND url_fragment = $2';
	$params = array($key, $slug);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return 0;
	else
		return (int) $resultSet[0]['count'];
}


function getDataSourceGroups($data_source_key)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT DISTINCT object_group FROM dba.tbl_registry_objects WHERE data_source_key = $1 ORDER BY object_group ASC ';
	$params = array($data_source_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet;

}
function getGroupPage($group)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT * FROM dba.tbl_institution_pages WHERE object_group = $1';
	$params = array($group);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet;

}

function deleteInstitutionalPage($group,$dataSourceKey)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'DELETE FROM dba.tbl_institution_pages WHERE object_group = $1 and authoritive_data_source_key = $2';
	$params = array($group,$dataSourceKey);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet;

}
function insertInstitutionalPage($group,$institutionalRegistryObjectKey,$dataSourceKey)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'INSERT INTO  dba.tbl_institution_pages (object_group , registry_object_key ,authoritive_data_source_key) VALUES ($1, $2, $3)';
	$params = array($group,$institutionalRegistryObjectKey,$dataSourceKey);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet;

}
function getGroupDataSources($group)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT DISTINCT(data_source_key) FROM dba.tbl_registry_objects WHERE object_group = $1';
	$params = array($group);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);

	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet;

}

function isContributorPage($page)
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT object_group FROM dba.tbl_institution_pages WHERE registry_object_key = $1';
	$params = array($page);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0];

}

//background tasks
function getNextWaitingTask()
{
	global $gCNN_DBS_ORCA;
	$resultSet = null;
	$strQuery = 'select * from dba.tbl_background_tasks bgt
						 where
								 bgt.status = $1
								 and (bgt.scheduled_for is null or bgt.scheduled_for < now())
								 and (bgt.prerequisite_task is null or ((select bg.status from dba.tbl_background_tasks bg where bg.task_id = bgt.prerequisite_task) = $2)) ORDER BY added ASC LIMIT 1';
	$params = array('WAITING','COMPLETED');
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}

function getTask($taskId, $status)
{
	global $gCNN_DBS_ORCA;
	$resultSet = null;
	if($taskId != null)
	{
		$strQuery = 'SELECT * FROM dba.tbl_background_tasks where task_id = CAST( $1 AS numeric )';
	    $params = array($taskId);
	}
	else if($status != null)
	{
		$strQuery = 'SELECT * FROM dba.tbl_background_tasks where status = $1 ORDER BY completed DESC';
	    $params = array($status);
	}
	else
	{
		$strQuery = 'SELECT * FROM dba.tbl_background_tasks order by added ASC';
	    $params = array();
	}
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}


function getPendingTasks()
{
	global $gCNN_DBS_ORCA;
	$resultSet = null;

	$strQuery = 'SELECT * FROM dba.tbl_background_tasks WHERE status <> \'COMPLETED\' and status <> \'FAILED\' order by scheduled_for, added, completed DESC';
	$params = array();

	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}

function getFailedTasks()
{
	global $gCNN_DBS_ORCA;
	$resultSet = null;

	$strQuery = 'SELECT * FROM dba.tbl_background_tasks WHERE status = \'FAILED\' order by completed DESC';
	$params = array();

	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;
}

function addNewTask($method, $log_msg = '', $ro_key = '', $ds_key = '', $prerequisite_task = null, $scheduled_for = null)
{
	// scheduled_for must be a valid interval (i.e. "30 seconds" or "1 day"
	if (is_null($scheduled_for)) { $scheduled_for = '0 seconds'; }

	$taskId = null;
	global $gCNN_DBS_ORCA;
	if($ds_key != '')//if a task already exist for the datasource just return the task id of the existing task
	{
		$strQuery = 'SELECT * FROM dba.tbl_background_tasks where method = $1 AND data_source_key = $2 AND status = $3 ORDER BY added ASC LIMIT 1';
		$params = array($method, $ds_key, 'WAITING');
		$existingTask = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
		if($existingTask)
		{
			return $existingTask[0]['task_id'];
		}
		else{
			$strQuery = 'INSERT INTO  dba.tbl_background_tasks (method, log_msg, registry_object_keys, data_source_key, prerequisite_task, scheduled_for) VALUES ($1, $2, $3, $4, $5, (NOW() + interval \''.$scheduled_for.'\'))';
			$params = array($method, $log_msg, $ro_key, $ds_key, $prerequisite_task);
			$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
			$strQuery = 'SELECT CURRVAL(pg_get_serial_sequence($1,$2))';
			$params = array('dba.tbl_background_tasks', 'task_id');
			$taskId = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
			return $taskId[0]['currval'];
		}
	}
	else
	{
		$strQuery = 'INSERT INTO  dba.tbl_background_tasks (method, log_msg, registry_object_keys, data_source_key, prerequisite_task, scheduled_for) VALUES ($1, $2, $3, $4, $5, (NOW() + interval \''.$scheduled_for.'\'))';
		$params = array($method, $log_msg, $ro_key, $ds_key, $prerequisite_task);
		$resultSet = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
		$strQuery = 'SELECT CURRVAL(pg_get_serial_sequence($1,$2))';
		$params = array('dba.tbl_background_tasks', 'task_id');
		$taskId = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
		return $taskId[0]['currval'];
	}
}

/*
 *
  task_id bigserial NOT NULL,
  method character varying(64),
  started timestamp without time zone,
  added timestamp without time zone NOT NULL DEFAULT now(),
  completed timestamp without time zone,
  prerequisite_task bigint,
  log_msg character varying(1024),
  registry_object_keys text,
  data_source_key text,
  status character varying(32) NOT NULL DEFAULT 'WAITING'::character
 */

function setTaskStarted($taskId)
{
	global $gCNN_DBS_ORCA;
    $strQuery = 'update dba.tbl_background_tasks set status = $2, started = now() where task_id = $1';
	$params = array($taskId, 'STARTED');
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;
}

function setTaskCompleted($taskId, $log_msg='')
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'update dba.tbl_background_tasks set status = $2, completed = now(), log_msg = $3 where task_id = $1';
	$params = array($taskId, 'COMPLETED', $log_msg);
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;
}

function setTaskFailed($taskId, $log_msg='')
{
	global $gCNN_DBS_ORCA;
	$strQuery = 'update dba.tbl_background_tasks set status = $2, completed = now(), log_msg = $3 where task_id = $1';
	$params = array($taskId, 'FAILED', $log_msg);
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;
}

function deleteTask($task_id)
{
	global $gCNN_DBS_ORCA;
	$strQuery = "DELETE FROM dba.tbl_background_tasks WHERE task_id = $1;";
	$params = array($task_id);
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;
}

function deleteCompletedTasksBefore($interval="1 day")
{
	global $gCNN_DBS_ORCA;
	$strQuery = "DELETE FROM dba.tbl_background_tasks WHERE status='COMPLETED' and completed < (now() - interval '".$interval."');";
	$params = array();
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;
}

function deleteFailedTasks()
{
	global $gCNN_DBS_ORCA;
	$strQuery = "DELETE FROM dba.tbl_background_tasks WHERE status='FAILED';";
	$params = array();
	$result = executeUpdateQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $result;
}

function getParentType($licence_type){
	global $gCNN_DBS_ORCA;
	$strQuery = 'SELECT parent_term_identifier FROM dba.tbl_terms WHERE identifier = $1';
	$params = array($licence_type);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	if (!isset($resultSet[0]))
		return false;
	else
		return $resultSet[0]['parent_term_identifier'];
}

function getDataSourceStats($data_source_key = '')
{

	global $gCNN_DBS_ORCA;
	$resultSet = null;
	$strQuery = "SELECT data_source_key as ds_key, registry_object_class as ro_class, status, quality_level as qa_level, count(quality_level)
				FROM dba.tbl_registry_objects where $1 = '' OR data_source_key = $1
				GROUP BY data_source_key, registry_object_class, quality_level,status
				UNION
				SELECT registry_object_data_source as ds_key, class as ro_class, status, quality_level as qa_level, count(quality_level)
				FROM dba.tbl_draft_registry_objects  where $1 = '' OR registry_object_data_source = $1
				GROUP BY registry_object_data_source, class, quality_level,status
				ORDER BY 1,2,3,4";
	$params = array($data_source_key);
	$resultSet = executeQuery($gCNN_DBS_ORCA, $strQuery, $params);
	return $resultSet;

}

?>
