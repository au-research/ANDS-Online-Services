
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
require '../../_includes/init.php';
require '../orca_init.php';

// Increase the execution timeout as we may have to deal with a large amount of data.
set_time_limit(0);
$executionTimeoutSeconds = 10*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");

// Set the Content-Type header.
header("Content-Type: text/xml; charset=UTF-8", true);

$responseType = 'success';
$message = '';

$runErrors = '';
$runResultMessage = "";
$actions = "";
$errors = null;
$startTime = microtime(true);

$remoteAddress = getRemoteAddress();
$harvestRequestId = getPostedValue('harvestid');

// Get the harvest request.
$harvestRequest = getHarvestRequests($harvestRequestId, null);

$actions  = "PUT HARVEST DATA\n";
$actions .= "Request IP: $remoteAddress\n";
$actions .= "Harvest Request ID: $harvestRequestId\n";
$errmsg = getPostedValue('errmsg');
$log_type = "HARVESTER_ERROR";
if( !$harvestRequest )
{
	$responseType = 'failure';
	$message = 'Invalid put request [1]';
}
elseif($errmsg != '')
{
	$dataSourceKey = $harvestRequest[0]['data_source_key'];
	insertDataSourceEvent($dataSourceKey, $errmsg, $log_type );
}
else
{
	$dataSourceKey = $harvestRequest[0]['data_source_key'];
	$harvesterIP = $harvestRequest[0]['harvester_ip'];
	
	$actions .= "Harvester IP: $harvesterIP\n";
	
	if( $remoteAddress != $harvesterIP )
	{
		$responseType = 'failure';
		$runErrors = 'Invalid put request [2]';
		
		$actions .= ">>ERRORS\n";
		$actions .= $runErrors;
	}
	else
	{
		$mode = strtoupper(getPostedValue('mode'));
		$done = strtoupper(getPostedValue('done'));
		$nextHarvestDate = getPostedValue('date');
		$data = getPostedValue('content');
		
		$actions .= "Mode: $mode\n";
		$actions .= "Done: $done\n";
		
		updateHarvestRequest($harvestRequestId, 'ORCA', gORCA_HARVEST_REQUEST_STATUS_PROCESSING);
		
		// Get the xml data.
		$OAIPMHDocument = new DOMDocument();
		$result = $OAIPMHDocument->loadXML($data);
		$errors = error_get_last();
		if( $errors )
		{
			$runErrors .= "Document Load Error: ".$errors['message']."\n";
			$log_type = "DOCUMENT_LOAD_ERROR";
		}
		if( !$runErrors )
		{
			// run an XSLT transformation 	
			
			
			$registryObjects = transformToRif2($OAIPMHDocument);
			if($registryObjects == null)
			{
				$runErrors = "There was an error transforming the document to RIF-CS v1.2";				
			}
		}	
		if( !$runErrors )
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
			  
			  // Validate temporary DOMDocument.
			  $result = $registryObjects->schemaValidate(gRIF_SCHEMA_PATH); //xxx

			$errors = error_get_last();
			if( $errors )
			{
				$runErrors .= "Document Validation Error: ".$errors['message']."\n";
				$log_type = "DOCUMENT_VALIDATION_ERROR";
			}
			
		}
		
		if( $mode == 'HARVEST' )
		{				
			$deletedRegistryObjectCount = 0;
			if( !$runErrors )
			{	
				// Import the data.
				$deletedRegistryObjectCount = checkforOAIdeletes($OAIPMHDocument);
				$runErrors = importRegistryObjects($registryObjects, $dataSourceKey, $runResultMessage, $harvestRequestId, NULL,  $harvestRequestId);
			}

			$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);
			$actions  .= "Time Taken: $timeTaken seconds\n";
			
			if( $runErrors )
			{
				$actions .= ">>ERRORS\n".$runResultMessage."\n";
				if($deletedRegistryObjectCount > 0)
				{
					$actions .= '>>OAI DELETES: ' .$deletedRegistryObjectCount." registryObjects\n";
				}
				$log_type = "DATABASE_IMPORT_ERROR";
				$actions .= $runErrors;
			}
			else
			{
				$actions .= ">>SUCCESS\n".$runResultMessage."\n";

				$log_type = "INFO";

				if($deletedRegistryObjectCount > 0)
				{
					$actions .= '>>OAI DELETES: ' .$deletedRegistryObjectCount." registryObjects\n";
				}
			}
		}
		else
		{
			$actions = "## TEST ##\n".$actions;			
			// Check for errors in the data.
			if( $runErrors )
			{
				$actions .= ">>ERRORS\n";
				$actions .= $runErrors;
			}
			else
			{	
				$actions .= ">>SUCCESS\n";
				$log_type = "INFO";
				// Get some information about the data.
				$actions .= "  SOURCE DATA\n";
				$actions .= '    '.$registryObjects->getElementsByTagName("registryObject")->length." registryObject element/s.\n";
				$actions .= '    '.$registryObjects->getElementsByTagName("*")->length." elements.\n";
			}
		}
		
		// We're done processing the put request.
		updateHarvestRequest($harvestRequestId, 'ORCA', '');
		
		if( $done == 'TRUE' && !$nextHarvestDate )
		{
			// The harvester is done servicing this harvest request so...

			deleteHarvestRequest($harvestRequestId);
			$actions .= "deleting harvestRequest\n";
		}
		else
		{
			// Update the status
			getHarvestRequestStatus($harvestRequestId, $dataSourceKey);
		}
		if( $done == 'TRUE' && $mode == 'HARVEST')
		{
			//Delete all records that are in the database and have a different HarvestID to the current Harvest
			$actions .= purgeDataSource($dataSourceKey, $harvestRequestId);//checking for REFRESH is done inside this function as well
		    queueSyncDataSource($dataSourceKey);
		    $actions .= 'SYNCING data source. Please wait...';
		}
	}
	
	if( $runErrors )
	{
		$actions .= $runErrors;
		//insertDataSourceEvent($dataSourceKey, $runErrors, $log_type);
	}


	// Log the activity.
	insertDataSourceEvent($dataSourceKey, $actions, $log_type);

}

// BEGIN: XML Response
// =============================================================================
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
print('<response type="'.esc($responseType).'">'."\n");
print('<timestamp>'.esc(getXMLDateTime(date("Y-m-d H:i:s"))).'</timestamp>'."\n");
print("<message>".esc($message)."</message>");
print("</response>");
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';

function checkforOAIdeletes($registryObjects)
{
	$xs = 'oai';
	$totalRegistryObjectDeletes = 0;
	$gXPath = new DOMXpath($registryObjects);
	// Get the default namespace of the registryObjects object.
	$defaultNamespace = $gXPath->evaluate('/*')->item(0)->namespaceURI;
	// Register a prefix for the default namespace so that we can actually use the xpath object.
	$gXPath->registerNamespace($xs, $defaultNamespace);
	$deletedRegistryObjectList = $gXPath->evaluate("//$xs:header[@status='deleted']");
	$totalDeletedRegistryObjectElements = $deletedRegistryObjectList->length;
	for( $i=0; $i < $deletedRegistryObjectList->length; $i++ )
	{
			
		$deletedRegistryObject = $deletedRegistryObjectList->item($i);
		
		// Registry Object Key
		// =====================================================================
		$registryObjectKey = substr($gXPath->evaluate("$xs:identifier", $deletedRegistryObject)->item(0)->nodeValue, 0, 512);
		
		
		if( getRegistryObject($registryObjectKey) )
			{
				// Delete this object and all associated records from the registry.
				$errors = deleteRegistryObject($registryObjectKey);
				if( !$errors ) { $totalRegistryObjectDeletes++; } else { $runErrors .= "Failed to delete Registry Object with key $registryObjectKey\n"; }
			}
	}
	return $totalRegistryObjectDeletes;
}
?>
