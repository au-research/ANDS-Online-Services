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

define('gORCA_HARVEST_REQUEST_STATUS_PROCESSING', 'PROCESSING HARVESTER PUT REQUEST');

$rif2solrXSL = new DomDocument();
$rif2solrXSL->load(eAPPLICATION_ROOT.'/orca/_xsl/rif2solr.xsl');
$solrXSLTProc = new XSLTProcessor();
$solrXSLTProc->importStyleSheet($rif2solrXSL);

$qtestxsl = new DomDocument();
$qtestxsl->load(eAPPLICATION_ROOT.'orca/_xsl/extRif2solr.xsl');
$extRif2solrProc = new XSLTProcessor();
$extRif2solrProc->importStyleSheet($qtestxsl);


function runImport($dataSource, $testOnly)
{
	$dataSourceProviderType  = $dataSource[0]['provider_type'];
	$dataSourceHarvestMethod = $dataSource[0]['harvest_method'];
	$dataSourceURI = $dataSource[0]['uri'];
	$dataSourceKey = $dataSource[0]['data_source_key'];
	$runErrors = "";
	$transformErrors = "";
	$runResultMessage = "";
	$actions = "";
	$errors = null;
	$log_type = "INFO";
	$startTime = microtime(true);
	$mode = 'harvest'; if( $testOnly ){ $mode = 'test'; }
	
	// DIRECT HARVEST
	// =========================================================================
	if( $dataSourceHarvestMethod == gORCA_HARVEST_METHOD_DIRECT )
	{
		// Get the xml data.
		$registryObjects = new DOMDocument();
		$result = $registryObjects->load($dataSourceURI);
		$errors = error_get_last();
		if( $errors )
		{
			$runErrors .= "Document Load Error: ".$errors['message']."\n";
			$log_type = "DOCUMENT_LOAD_ERROR";
		}
		if( !$runErrors )
		{
			// run an XSLT transformation 			
			$registryObjects = transformToRif2($registryObjects);
			if($registryObjects == null)
			{
				$transformErrors .= "There was an error transforming the document to RIF-CS v1.2";
				$log_type = "DOCUMENT_LOAD_ERROR";				
			}
		}		
		if( !$runErrors && !$transformErrors)
		{
			  // Validate it against the orca schema.
			  // XXX: libxml2.6 workaround (Save to local filesystem before validating)
			  
			  // Create temporary file and save manually created DOMDocument.
			  $tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
			  $registryObjects->save($tempFile);
			 
			  // Create temporary DOMDocument and re-load content from file.
			  $registryObjects = new DOMDocument();
			  $registryObjects->load($tempFile);
			  
			  // Delete temporary file.
			  if (is_file($tempFile))
			  {
			    unlink($tempFile);
			  }
			  $result = $registryObjects->schemaValidate(gRIF_SCHEMA_PATH); //xxx
			//print($registryObjects->saveXML());
			//exit;
			$errors = error_get_last();
			if( $errors )
			{
				$runErrors .= "Document Validation Error: ".$errors['message']."\n";
				$log_type = "DOCUMENT_VALIDATION_ERROR";
			}
		}
		
		$actions  = "IMPORT\n";
		$actions .= "URI: $dataSourceURI\n";
		$actions .= "Provider Type: $dataSourceProviderType\n";
		$actions .= "Harvest Method: $dataSourceHarvestMethod\n";
		
		if( !$testOnly )
		{
			if( !$runErrors )
			{	
				// Import the data.
				$runErrors = importRegistryObjects($registryObjects, $dataSourceKey, $runResultMessage);
				$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);
				$actions  .= "Time Taken: $timeTaken seconds\n";
				
			}


			if( $runErrors )
			{
				$actions .= ">>ERRORS\n".$runResultMessage."\n";
				$actions .= $runErrors;
			}
			else
			{
				$actions .= ">>SUCCESS\n".$runResultMessage."\n";
			}
			queueSyncDataSource($dataSourceKey);
		}
		else
		{
			$actions .= "## TEST ##\n";
			
			// Check for errors in the data.
			if( $runErrors )
			{
				$actions .= ">>ERRORS\n";
				$actions .= $runErrors;
			}
			else
			{	
				$actions .= ">>SUCCESS\n";
				
				// Get some information about the data.
				$actions .= "  SOURCE DATA\n";
				$actions .= '    '.$registryObjects->getElementsByTagName("registryObject")->length." registryObject element/s.\n";
				$actions .= '    '.$registryObjects->getElementsByTagName("*")->length." elements.\n";
			}
		}
			
		// Log the activity.
		insertDataSourceEvent($dataSourceKey, $actions, $log_type);
		
	}
	
	// HARVESTER HARVEST
	// =========================================================================
	else
	{
		$harvestRequestId = strtoupper(sha1($dataSourceKey.microtime(false)));
		
		$actions  = "SUBMIT HARVEST REQUEST\n";
		$actions .= "Harvest Request ID: $harvestRequestId\n";
		$actions .= "URI: $dataSourceURI\n";
		$actions .= "Provider Type: $dataSourceProviderType\n";
		
		// Check the harvester configuration.
		if( !gORCA_HARVESTER_BASE_URI || !gORCA_HARVESTER_IP )
		{
			$actions .= ">>ERRORS\nNo harvester is configured.\n";
			$log_type = "HARVESTER_ERROR";
			insertDataSourceEvent($dataSourceKey, $actions, $log_type);
		}
		else
		{
			
			global $gActivities;
			$responseTargetURI = getObject($gActivities, 'aORCA_SERVICE_PUT_HARVEST_DATA')->path;
			
			$OAISet = '';
			if( isset($dataSource[0]['oai_set']) )
			{
				$OAISet = $dataSource[0]['oai_set'];
			}
			
			$harvestDate = null;
			if( isset($dataSource[0]['harvest_date']) )
			{
				$harvestDate = formatDateTimeWithMask($dataSource[0]['harvest_date'], eDCT_FORMAT_ISO8601_DATETIMESEC_UTC);
			}
			
			$harvestFrequency = '';
			if( isset($dataSource[0]['harvest_frequency']) )
			{
				$harvestFrequency = $dataSource[0]['harvest_frequency'];
			}

			// Build and submit the harvest request.
			$actions .= "Harvester Base URI: ".gORCA_HARVESTER_BASE_URI."\n";
			$actions .= "Harvester IP: ".gORCA_HARVESTER_IP."\n";
			$actions .= "Response Target URL: $responseTargetURI\n";
			$actions .= "Source URL: $dataSourceURI\n";
			$actions .= "Method: $dataSourceHarvestMethod\n";
			if( $OAISet )
			{
				$actions .= "OAI Set: $OAISet\n";
			}
			$actions .= "Harvest Date: $harvestDate\n";
			$actions .= "Harvest Frequency: $harvestFrequency\n";
			$actions .= "Mode: $mode\n";
			
			$harvestRequest  = 'requestHarvest?';
			$harvestRequest .= 'responsetargeturl='.urlencode($responseTargetURI);
			$harvestRequest .= '&harvestid='.urlencode($harvestRequestId);
			$harvestRequest .= '&sourceurl='.urlencode($dataSourceURI);
			$harvestRequest .= '&method='.urlencode($dataSourceHarvestMethod);
			if( $OAISet )
			{
				$harvestRequest .= '&set='.urlencode($OAISet);
			}
			$harvestRequest .= '&date='.urlencode($harvestDate);
			$harvestRequest .= '&frequency='.urlencode($harvestFrequency);
			$harvestRequest .= '&mode='.urlencode($mode);
			
			// Submit the request.
			$runErrors = submitHarvestRequest(gORCA_HARVESTER_BASE_URI.$harvestRequest);
			
			if( $runErrors )
			{
				$actions .= ">>ERRORS\n";
				$log_type = "HARVESTER_ERROR";
				$actions .= $runErrors;
			}
			else
			{
				$actions .= ">>SUCCESS\n";
				// Create an entry to track the request.
				$errors = insertHarvestRequest($harvestRequestId, $dataSourceKey, gORCA_HARVESTER_BASE_URI, gORCA_HARVESTER_IP, $responseTargetURI, $dataSourceURI, $dataSourceHarvestMethod, $OAISet, $harvestDate, $harvestFrequency, $mode);
				if( $errors )
				{
					$actions .= $errors;
				}
			}
			// Log the activity.
			insertDataSourceEvent($dataSourceKey, $actions, $log_type);
		}
	}
}

function getRecordCountsByStatusForDataSource($data_source_key)
{
	global $solr_url;
	$statuses = array();
	$statuses["PUBLISHED"] = 0;
	
	$result = json_decode(file_get_contents($solr_url."select/?wt=json&q=data_source_key:(\"".rawurlencode($data_source_key)."\")&facet=true&facet.field=status&facet.mincount=1&rows=0"), true);

	if (isset($result['facet_counts']['facet_fields']['status']))
	{
		
		for ( $i = 0; $i<count($result['facet_counts']['facet_fields']['status']); $i+=2)
		{
			$statuses[$result['facet_counts']['facet_fields']['status'][$i]] = $result['facet_counts']['facet_fields']['status'][$i+1];
		}
	}

	
	return $statuses;
	
}


function runClear($dataSource, $action)
{
	$actions = "";
	$runResultMessage = "";
	$dataSourceKey = $dataSource[0]['data_source_key'];
	$startTime = microtime(true);
	$log_type = "INFO";
	// Delete all the related Registry Objects associated with this data source.
	$runErrors = deleteDataSourceRegistryObjects($dataSourceKey, $runResultMessage, $action);
	
	$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);
	
	$actions  = "$action ($timeTaken seconds)\n";
	
	if( $runErrors )
	{
		$actions .= ">>ERRORS\n".$runResultMessage."\n";
		$log_type = "DATABASE_IMPORT_ERROR";
		$actions .= $runErrors;
	}
	else
	{
		$actions .= ">>SUCCESS\n".$runResultMessage."\n";
	}
	
	// Log the activity.
	queueSyncDataSource($dataSourceKey);
	insertDataSourceEvent($dataSourceKey, $actions, $log_type);

}

function deleteDataSourceRegistryObjects($dataSourceKey, &$resultMessage, $action)
{
	$errors = '';
	$registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey);
	$registryObjectCount = 0;
	$successfulDeletes = 0;
	if( $registryObjectKeys )
	{
		$registryObjectCount = count($registryObjectKeys);
		for( $i=0; $i < count($registryObjectKeys); $i++ )
		{
			$registryObjectKey = $registryObjectKeys[$i]['registry_object_key'];
			$originatingSource = $registryObjectKeys[$i]['originating_source'];
			if($action == 'DELETE ALL RECORDS')
			{
				$result = deleteRegistryObject($registryObjectKey);
				if( $result == '' )
				{
					$successfulDeletes++;
				}
				else
				{
					$errors .= $result."\n";
				}
			}
			else if($action == 'DELETE HARVESTED RECORDS' && (strpos($originatingSource ,eORIGSOURCE_RMD_SUFFIX) === false || strpos($originatingSource ,eORIGSOURCE_RMD_SUFFIX) === false))
			{
				$result = deleteRegistryObject($registryObjectKey);
				if( $result == '' )
				{
					$successfulDeletes++;
				}
				else
				{
					$errors .= $result."\n";
				}
			}
			else if($action == 'DELETE MANUALLY ENTERED RECORDS' && (strpos($originatingSource ,eORIGSOURCE_RMD_SUFFIX) > 0 || strpos($originatingSource ,eORIGSOURCE_RMD_SUFFIX) > 0))
			{
				$result = deleteRegistryObject($registryObjectKey);
				if( $result == '' )
				{
					$successfulDeletes++;
				}
				else
				{
					$errors .= $result."\n";
				}
			}

		}
	}
	$resultMessage .= "  REGISTRY DATA\n";
	$resultMessage .= "    $registryObjectCount Registry Object/s from this source.\n";
	$resultMessage .= "  ACTIONS\n";
	$resultMessage .= "    $successfulDeletes Registry Object/s deleted.\n";
	queueSyncDataSource($dataSourceKey);
	return $errors;
}

function getDataSourceLogHTML($dataSourceKey)
{
	$html = '';
	$dataSourceLog = getDataSourceLog($dataSourceKey);
	if( $dataSourceLog )
	{
		$row = count($dataSourceLog);
		$html .= '<table style="margin: 0px; width: 100%; border-collapse: separate;" cellspacing="0">'."\n";

		foreach( $dataSourceLog as $event )
		{
			$description = $event['event_description'];
			if($event['log_type'] == "HARVESTER_ERROR")
			{
				$logTypeColour = "#AA0011";

			}
			elseif($event['log_type'] == "DOCUMENT_LOAD_ERROR")
			{
				$logTypeColour = "#AA0011";
			}
			elseif($event['log_type'] == "DOCUMENT_VALIDATION_ERROR")
			{
				$logTypeColour = "#AA0011";
				$description = str_replace("{http://ands.org.au/standards/rif-cs/registryObjects}","",$description);
			}
			elseif($event['log_type'] == "DATABASE_IMPORT_ERROR")
			{
				$logTypeColour = "#AA0011";
			}
			else 
			{
				$logTypeColour = "#66D207";
			}
					
			$html .= "<tr>\n";
			$html .= "<td id='".$row."' style=\"background: ".$logTypeColour."; border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa; border-right: 1px solid #c8c5c3; text-align: right; font-weight: bold; font-style: italic; width: 2%;color:white; border:none; \">[$row]&nbsp;</td>\n";
			$html .= "<td style=\"background: #dfddda URL('".eIMAGE_ROOT."_layout/bg_dark.gif') no-repeat;  border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa; border-right: 1px solid #c8c5c3; text-align: left; width: 7%; white-space: nowrap;\">".esc(formatDateTimeWithMask($event['created_when'], eDCT_FORMAT_ISO8601_DATE_TIMESEC))."</td>\n";
			$html .= "<td id='".$row."_type' style=\"background: #dfddda URL('".eIMAGE_ROOT."_layout/bg_dark.gif') no-repeat;  border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa; border-right: 1px solid #c8c5c3; text-align: center; width: 5%; white-space: nowrap; font-weight:bold;\">".esc($event['log_type'])."</td>\n";
			$html .= "<td style=\"background: #dfddda URL('".eIMAGE_ROOT."_layout/bg_dark.gif') no-repeat;  border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa; border-right: 1px solid #c8c5c3; text-align: center; width: 6%; white-space: nowrap;\">".esc($event['request_ip'])."</td>\n";
			$html .= "<td style=\"background: #dfddda URL('".eIMAGE_ROOT."_layout/bg_dark.gif') no-repeat;  border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa;\">".esc($event['created_who'])."</td>\n";
			$html .= "</tr>\n<tr>\n";
			if($event['log_type'] == "INFO")
			{
				$html .= "<td style=\"background: ".$logTypeColour.";  border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa; border-right: 1px solid #c8c5c3; text-align: center; width: 5%; white-space: nowrap;\"></td>\n";
			}
			else
			{
				$html .= "<td style=\"background: ".$logTypeColour.";  border-top: 1px solid #ffffff; border-bottom: 1px solid #aaaaaa; border-right: 1px solid #c8c5c3; align: center; width: 5%; white-space: nowrap;\"><a href='javascript:void(0);' class='infoIcon' id='".$row."_info'><img src='../_images/Question-mark-icon.png' title='More Info' alt=''></a></td>\n";
			}
			$html .= "<td id='".$row."_desc' style=\"background-color: #f5f2f0; border-top: 1px solid #fbf8f5; border-bottom: 1px solid #999999;\" colspan=\"4\">".escWithBreaks($description)."</td>\n";
			$html .= "</tr>\n";

			$row--;
		}
		$html .= '</table>'."\n";
	}
	return $html;
}

function getDataSourceLogText($dataSourceKey)
{
	$text = '';
	$dataSourceLog = getDataSourceLog($dataSourceKey);
	if( $dataSourceLog )
	{
		$row = count($dataSourceLog);
		foreach( $dataSourceLog as $event )
		{
			$text .= "[$row]\n";
			$text .= formatDateTimeWithMask($event['created_when'], eDCT_FORMAT_ISO8601_DATE_TIMESEC)."\t";
			$text .= $event['log_type']."\t";
			$text .= $event['request_ip']."\t";
			$text .= $event['created_who']."\n";
			$text .= $event['event_description']."\n";

			$row--;
		}
	}
	return $text;
}

function submitHarvestRequest($harvestRequest)
{
	$runErrors = '';
	
	// Submit the harvestRequest to the harvester.
	$resultMessage = new DOMDocument();
	$result = $resultMessage->load($harvestRequest);
	$errors = error_get_last();
	if( $errors )
	{
		$runErrors = "harvestRequest Error[1]: ".$errors['message']."\n";
	}
	else
	{
		$responseType = strtoupper($resultMessage->getElementsByTagName("response")->item(0)->getAttribute("type"));
		$message = $resultMessage->getElementsByTagName("message")->item(0)->nodeValue;
		
		if( $responseType != 'SUCCESS' )
		{
			$runErrors = "harvestRequest Error[2]: $message";
		}	
	}
	return $runErrors;
}

function getHarvestRequestStatus($harvestRequestId, $dataSourceKey)
{
	$runErrors = '';
	
	// Get the harvest request.
	$harvestRequest = getHarvestRequests($harvestRequestId, null);
	
	$actions  = "GET HARVEST REQUEST STATUS\n";
	$actions .= "Harvest Request ID: $harvestRequestId\n";

	if( $harvestRequest )
	{
		$harvesterBaseURI = $harvestRequest[0]['harvester_base_uri'];
		$status = $harvestRequest[0]['status'];
		
		if( $status != gORCA_HARVEST_REQUEST_STATUS_PROCESSING )
		{
			// Submit a getHarvestStatus to the harvester.
			$request = $harvesterBaseURI."getHarvestStatus?harvestid=".esc($harvestRequestId);
		
			// Submit the request.
			$runErrors = '';
			$resultMessage = new DOMDocument();
			$result = $resultMessage->load($request);
			$errors = error_get_last();
			if( $errors )
			{
				$runErrors = "getHarvestStatus Error[1]: ".$errors['message']."\n";
			}
			else
			{
				$responseType = strtoupper($resultMessage->getElementsByTagName("response")->item(0)->getAttribute("type"));
				$message = $resultMessage->getElementsByTagName("message")->item(0)->nodeValue;
				
				if( $responseType == 'SUCCESS' )
				{
					$status = $message;
					$errors = updateHarvestRequest($harvestRequestId, 'HARVESTER', $status);
					if( $errors )
					{
						$runErrors = $errors;
					}	
				}
				else
				{
					$runErrors = "getHarvestStatus Error[2]: $message";
				}
			}
		}
	}
	else
	{
		$runErrors = 'The harvest request does not exist.';
	}	
	
	if( $runErrors )
	{
		$actions .= ">>ERRORS\n";
		$actions .= $runErrors;
		
		// Log the problem.
		insertDataSourceEvent($dataSourceKey, $actions);
	}
}

function cancelHarvestRequest($harvestRequestId, $dataSourceKey)
{

	// Get the harvest request.
	$harvestRequest = getHarvestRequests($harvestRequestId, null);
	
	$actions  = "DELETE HARVEST REQUEST\n";
	$actions .= "Harvest Request ID: $harvestRequestId\n";
	$actions .= "Submitted: ".formatDateTimeWithMask($harvestRequest[0]['created_when'], eDCT_FORMAT_ISO8601_DATE_TIMESEC)."\n";
	$actions .= "Last Status Update: ".formatDateTimeWithMask($harvestRequest[0]['modified_when'], eDCT_FORMAT_ISO8601_DATE_TIMESEC)."\n";
	$actions .= "Status: ".$harvestRequest[0]['status']."\n";
	
	
	if( $harvestRequest )
	{
		$harvesterBaseURI = $harvestRequest[0]['harvester_base_uri'];
		
		// Submit a deleteHarvestRequest to the harvester.
		$request = $harvesterBaseURI."deleteHarvestRequest?harvestid=".esc($harvestRequestId);
		
		// Submit the request.
		$runErrors = '';
		$resultMessage = new DOMDocument();
		$result = $resultMessage->load($request);
		$errors = error_get_last();
		if( $errors )
		{
			$runErrors = "deleteHarvestRequest Error[1]: ".$errors['message']."\n";
		}
		else
		{
			$responseType = strtoupper($resultMessage->getElementsByTagName("response")->item(0)->getAttribute("type"));
			$message = $resultMessage->getElementsByTagName("message")->item(0)->nodeValue;
			
			if( $responseType != 'SUCCESS' )
			{
				$runErrors = "deleteHarvestRequest Error[2]: $message";
			}
		}
		
		if( $runErrors )
		{
			$actions .= ">>ERRORS\n";
			$actions .= $runErrors;
		}
		else
		{
			$actions .= ">>SUCCESS\n";
			// Remove the entry.
			$errors = deleteHarvestRequest($harvestRequestId);
			if( $errors )
			{
				$actions .= $errors;
			}
		}
	}
	else
	{
		$actions .= ">>ERRORS\n";
		$actions .= 'The harvest request does not exist.';
	}

	// Log the activity.
	insertDataSourceEvent($dataSourceKey, $actions);
}

function transformToRif2($registryObjects)
{
$qtestxsl = new DomDocument();
$qtestxsl->load('../_xsl/rif1Torif2.xsl');
$proc = new XSLTProcessor();
$proc->importStyleSheet($qtestxsl);
$transformResult = $proc->transformToDoc($registryObjects);	
return $transformResult;
}

function transformToRif2XML($registryObjectsXML)
{
$qtestxsl = new DomDocument();
$registryObjects = new DomDocument();
$registryObjects->loadXML($registryObjectsXML);
$qtestxsl->load('../_xsl/rif1Torif2.xsl');
$proc = new XSLTProcessor();
$proc->importStyleSheet($qtestxsl);
$transformResult = $proc->transformToXML($registryObjects);	
return $transformResult;
}

function transformToStripFormData($registryObjectsXML)
{
$qtestxsl = new DomDocument();
$registryObjects = new DomDocument();
$registryObjects->loadXML($registryObjectsXML);
$qtestxsl->load('../_xsl/stripFormData.xsl');
$proc = new XSLTProcessor();
$proc->importStyleSheet($qtestxsl);
$transformResult = $proc->transformToXML($registryObjects);	
return $transformResult;
}

function transformToSolr($registryObjectsXML)
{	
	global $extRif2solrProc;
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($registryObjectsXML);
	$transformResult = $extRif2solrProc->transformToXML($registryObjects);	
	return $transformResult;
}

function runSolrIndexForDatasource($dataSourceKey)
{
	$rifcsContent = '';
	$allKeys = getRegistryObjectKeysForDataSource($dataSourceKey);
	$arraySize = sizeof($allKeys);
	$result ='';
	$publishedRecords = 0;
	for($i = 0; $i < $arraySize ; $i++)
	{				
		//if($allKeys[$i]['status'] == PUBLISHED)
		//{
		$key = $allKeys[$i]['registry_object_key'];	
		$publishedRecords++;	
		$rifcsContent = getRegistryObjectXMLforSOLR($key, true);
		$rifcs = wrapRegistryObjects($rifcsContent);
		$rifcs = transformToSolr($rifcs);		
		//	echo $key . '<br/>';						
		$result .= curl_post(gSOLR_UPDATE_URL, $rifcs);					
		$result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
		//echo $result;
		$result ='';flush(); ob_flush();	
	//	$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	
		//}	
	}
		$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	/*if($publishedRecords > 0)
	{
		$rifcs = wrapRegistryObjects($rifcsContent);
		$rifcs = transformToSolr($rifcs);									
		$result .= curl_post(gSOLR_UPDATE_URL, $rifcs);					
		$result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
		$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');	
	}*/
    return $result;	
}

function updateRecordsForDataSource($dataSourceKey, $manuallyPublish,$manuallyPublishOld, $qaFlag, $qaFlagOld,$createPrimary,$oldCreatePrimary,$class_1,$class_1_old,$class_2,$class_2_old)
{

	$actions = '';
	if($createPrimary=='0') $createPrimary='f';
	
	if(($manuallyPublish == 0 || $manuallyPublish == 'f') && $manuallyPublishOld == 't')
	{
		// set Approved to Published for all registry Objects
		$actions .= 'Changed Status to PUBLISHED';
		if($registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey))
		{
			for( $i=0; $i < count($registryObjectKeys); $i++ )
			{
				updateRegistryObjectStatus($registryObjectKeys[$i]['registry_object_key'], PUBLISHED);
				//$actions .= $registryObjectKeys[$i]['registry_object_key'].' changed status to PUBLISHED';
			}
		}
		
		insertDataSourceEvent($dataSourceKey, $actions);
	}
	//echo $createPrimary." = new :: ".$oldCreatePrimary." = old ";
	//if($createPrimary!=$oldCreatePrimary||$class_1!=$class_1_old||$class_2!=$class_2_old)
	//{
		
	//}
	if(($qaFlag == 0 || $qaFlag == 'f') && $qaFlagOld == 't')
	{
			
		if($manuallyPublish == 1 || $manuallyPublish == 't')
		{
			$status = APPROVED;
		}
		else 
		{
			$status = PUBLISHED;
		}
		
		// change status in draft MORE_WORK => DRAFT
		
		// move ASSESSMENT_IN_PROGRESS AND SUBMITTED_FOR_ASSESSMENT into either APPROVED or PUBLISHED (depending on $autoPublish flag)
		if($registryObjectKeys = getDraftRegistryObject(null, $dataSourceKey))
		{
			for( $i=0; $i < count($registryObjectKeys); $i++ )
			{
				
				if($registryObjectKeys[$i]['status'] == MORE_WORK_REQUIRED)
				{
					$actions .= $registryObjectKeys[$i]['draft_key']." Changed Status to DRAFT\n";
					updateDraftRegistryObjectStatus($registryObjectKeys[$i]['draft_key'],$dataSourceKey,DRAFT);
				}
				else if($registryObjectKeys[$i]['status'] == ASSESSMENT_IN_PROGRESS || $registryObjectKeys[$i]['status'] == SUBMITTED_FOR_ASSESSMENT)
				{
					$actions .= "PUBLISHING records\n";
					$rifcs = new DomDocument();
					$rifcs->loadXML($registryObjectKeys[$i]['rifcs']);
					$stripFromData = new DomDocument();
					$stripFromData->load('../_xsl/stripFormData.xsl');
					$proc = new XSLTProcessor();
					$proc->importStyleSheet($stripFromData);
					$registryObject = $proc->transformToDoc($rifcs);
					//print_pre($draft);
					$registryObjectKey = $registryObjectKeys[$i]['draft_key'];
					$owner = $registryObjectKeys[$i]['draft_owner'];
			        $error = error_get_last();
				    $tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
				    $registryObject->save($tempFile);
				 
				    // Create temporary DOMDocument and re-load content from file.
				    $registryObject = new DOMDocument();
				    $registryObject->load($tempFile);
				    
				    // Delete temporary file.
				    if (is_file($tempFile))
				    {
				      unlink($tempFile);
				    }
				  
					$registryObject->schemaValidate(gRIF_SCHEMA_PATH); //xxx

					$importErrors = importRegistryObjects($registryObject,$dataSourceKey, $resultMessage, getLoggedInUser(), $status, $owner, null, true);       
					if( !$importErrors )
					{
						$deleteErrors = deleteDraftRegistryObject($dataSourceKey,$registryObjectKey);
						$actions .= $registryObjectKeys[$i]['draft_key'].' Imported to Registry as '.$status."\n";
					}
										
				}
			}
						
		}

		insertDataSourceEvent($dataSourceKey, $actions);	
	}
	queueSyncDataSource($dataSourceKey);
	
}


function deleteDataSourceDrafts($dataSourceKey , $message)
{	
	$errors = '';
	$drafts = getDraftsByDataSource($dataSourceKey);
	if( $drafts )
	{
		for( $i=0; $i < count($drafts); $i++ )
		{
			$draft_key  = $drafts[$i]['draft_key'];
			$errors .= deleteDraftRegistryObject($dataSourceKey, $draft_key);
		}
	}
	$message = "DELETED ".count($drafts)." DARFTS\n";
	queueSyncDataSource($dataSourceKey);
	return $errors;	
}

?>
