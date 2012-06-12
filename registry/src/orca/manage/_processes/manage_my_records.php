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
if (!IN_ORCA) die('No direct access to this file is permitted.');


// Default response
$response = array("responsecode" => 0, "response" => "No Response");
$data_source_key = getPostedValue('dataSourceKey');
$keys = getPostedValue('keys');

// Check if user is currently logged in
if (!userIsLoggedIn())
{
	$response['response'] = "Not logged in";
	echo json_encode($response);
	die();
}
	
// Check that data source is valid and user has access to it
$rawResults = getDataSources($data_source_key, null);
if ($data_source_key == 'PUBLISH_MY_DATA' && userIsORCA_ADMIN())
{
	$rawResults[] = array('data_source_key'=>'PUBLISH_MY_DATA');
}
if( $rawResults )
{
	
	foreach( $rawResults as $dataSource )
	{
		if( !(userIsORCA_QA() || userIsDataSourceRecordOwner($dataSource['record_owner'])) )
		{
			$response['response'] = "Not logged in";
			echo json_encode($response);
			die();
		}		
	}
}
else
{
	$response['response'] = "Invalid Datasource";
	echo json_encode($response);
	die();
}

// Check we actually have keys to act on
if (count($keys) == 0)
{
	$response['response'] = "Invalid Key Set";
	echo json_encode($response);
	die();
}

// Handle the action
switch(getQueryValue('action'))
{
	
	case "SUBMIT_FOR_ASSESSMENT":
		
		$send_email = true;
		if (getDraftCountByStatus($data_source_key, SUBMITTED_FOR_ASSESSMENT) !== 0)
		{
			$send_email = false;
		}
		/*foreach(getDraftRegistryObject(null, $data_source_key) AS $draft)
		{
			if ($draft['status'] == SUBMITTED_FOR_ASSESSMENT)
			{
				$send_email = false;
			}
		}*/
		
		foreach($keys AS $key)
		{
			$response['response']=updateDraftRegistryObjectStatus(rawurldecode($key), $data_source_key, SUBMITTED_FOR_ASSESSMENT);
			syncDraftKey(rawurldecode($key), $data_source_key);
		}

		$target_data_source = getDataSources($data_source_key, null);
		
		$response['responsecode'] = "MT008";

		if ($send_email)
		{			
			if (isset($target_data_source[0]['assessement_notification_email_addr']) && $target_data_source[0]['assessement_notification_email_addr'] != "")
			{
				
				$this_user = $_SESSION['name'];
				
				send_email(
					$target_data_source[0]['assessement_notification_email_addr'],
					"Records from " . $target_data_source[0]['title'] . " are ready for your assessment",
					$target_data_source[0]['title'] . " has submitted " . count($keys) . " record(s) for your assessment" . ($_SESSION['name'] != "" ? " by " .$_SESSION['name'] : ".") . " \n\n" .
					"Your action is required to review these records by visiting the Manage My Records screen or accessing the Data Source directly by the following link:\n" .
					eHTTP_APP_ROOT . "orca/manage/my_records.php?data_source=" . $data_source_key . "\n\n"
				);
				
				$response['responsecode'] = "MT014";
			}
		}

		echo json_encode($response);
		die();
			
	break;
	
	case "START_ASSESSMENT":
		
		foreach($keys AS $key)
		{
			updateDraftRegistryObjectStatus(rawurldecode($key), $data_source_key, ASSESSMENT_IN_PROGRESS);
			syncDraftKey(rawurldecode($key), $data_source_key);
		}
		
		$response['responsecode'] = "1";
		echo json_encode($response);
		die();
		
	break;
	
	case "MORE_WORK_REQUIRED":
		
		foreach($keys AS $key)
		{
			updateDraftRegistryObjectStatus(rawurldecode($key), $data_source_key, MORE_WORK_REQUIRED);
			syncDraftKey(rawurldecode($key), $data_source_key);
		}
		
		$response['responsecode'] = "1";
		echo json_encode($response);
		die();
		
	break;
	
	case "BACK_TO_DRAFT":
		
		foreach($keys AS $key)
		{
			updateDraftRegistryObjectStatus(rawurldecode($key), $data_source_key, DRAFT);
			syncDraftKey(rawurldecode($key), $data_source_key);
		}
		
		$response['responsecode'] = "1";
		echo json_encode($response);
		die();
		
	break;
	
	case "APPROVE":
		$returnErrors = "";
		foreach($keys AS $key)
		{
			$returnErrors .= approveDraft(rawurldecode($key), $data_source_key);
			deleteSolrHashKey(sha1($key.$data_source_key));//delete the draft
			syncKey(rawurldecode($key), $data_source_key);
		}
		$response['alert'] = $returnErrors;
		$response['responsecode'] = "1";
		echo json_encode($response);
		die();
		
	break;
	
	
	case "PUBLISH":
		foreach($keys AS $key){
			//is it a draft
			$isDraft = getDraftRegistryObject(rawurldecode($key), $data_source_key);
			if($isDraft){
				//is a draft, have to approve and do all the jazz with it first
				approveDraft($key, $data_source_key);
				updateRegistryObjectStatus(rawurldecode($key), PUBLISHED);
				syncDraftKey(rawurldecode($key), $data_source_key);
				$response['responsecode'] = "1";
				echo json_encode($response);
			}else{
				//is not draft
				updateRegistryObjectStatus(rawurldecode($key), PUBLISHED);
				syncKey(rawurldecode($key), $data_source_key);
				$response['responsecode'] = "1";
				echo json_encode($response);
			}
			
		}
		die();
		
	break;
	
	case "DELETE_RECORD":
		
		foreach($keys AS $key)
		{
			deleteSolrHashKey(sha1($key));//solr
			deleteCacheItem($data_source_key, $key);//cache
			deleteRegistryObject(rawurldecode($key));//db
			queueSyncDataSource($data_source_key);
		}
		
		$response['responsecode'] = "1";
		echo json_encode($response);
		die();
		
	break;
	
	case "DELETE_DRAFT":
		
		foreach($keys AS $key)
		{
			deleteDraftRegistryObject($data_source_key, rawurldecode($key));//delete from db
			deleteSolrDraft($key, $data_source_key);//delete from solr
			queueSyncDataSource($data_source_key);
		}
		
		$response['responsecode'] = "1";
		echo json_encode($response);
		die();
		
	break;
	
	case "FLAG_GOLD":
		foreach($keys as $key){
			setGoldFlag($key);
			syncKey($key, $data_source_key);
		}
		$response['responsecode']="1";
		echo json_encode($response);
		die();

	// if no action matches
	default:
		$response['response'] = "Invalid Action";
		echo json_encode($response);
		die();
}		



echo json_encode($response);
die();