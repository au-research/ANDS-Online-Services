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

require 'dois_init.php';
// Page processing
// -----------------------------------------------------------------------------

$errorMessages = '';
$notifyMessage = '';
$logMessage = '';
$outstr = '';
$doiObjects = null;
$response1 = "OK";
$response2 = "OK";
$ip_address = trim($_SERVER['REMOTE_ADDR']);
$xml = '';

$app_id = trim(getQueryValue('app_id')); 	//passed as a parameter
 
$urlValue = trim(getQueryValue('url'));		//passed as a parameter

$doiValue = trim(getQueryValue('doi'));    //passed as a parameter

$doi =getDoiObject($doiValue); // check if doi is a valid doi and get the information about it.

if(!$doi[0]["doi_id"])
{
	$errorMessages .= doisGetUserMessage("MT011", $doi_id=$doiValue);
}
				
if($_POST)
{
	if(str_replace("<?xml version=","",implode($_POST))==implode($_POST))
	{
		$xml = "<?xml version=".implode($_POST); 	// passed as posted content
	}
	else 
	{
		$xml = implode($_POST);				// passed as posted content
	}
}

	//first up, lets check that this client is permitted to update this doi.
	$client_id = checkDoisValidClient($ip_address,$app_id);
	
	if(!$client_id)
	{
		$errorMessages .= doisGetUserMessage("MT009", $doi_id=NULL);
		header("HTTP/1.0 415 Authentication Error");
	}else{
		
		if(!checkDoisClientDoi($doiValue,$client_id))
		{
			$errorMessages .= doisGetUserMessage("MT008", $doiValue);
			header("HTTP/1.0 415 Authentication Error");
		} 
		
	}	
	if($xml) // if the client has posted xml to be updated
	{
		$doiObjects = new DOMDocument();
				
		$result = $doiObjects->loadXML($xml);

		$errors = error_get_last();
	
		if( $errors )
		{
			$errorMessages .= "Document Load Error: ".$errors['message']."\n";
			header("HTTP/1.0 500 Internal Server Error");
		}
		else 
		{
			// Validate it against the datacite schema.
			error_reporting(0);
			// Create temporary file and save manually created DOMDocument.
			$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
				  
			$doiObjects->save($tempFile);
			 
			// Create temporary DOMDocument and re-load content from file.
			$doiObjects = new DOMDocument();
			$doiObjects->load($tempFile);
			  
			 //Delete temporary file.
			if (is_file($tempFile))
			{
				unlink($tempFile);
			}
  
			$result = $doiObjects->schemaValidate(gCMD_SCHEMA_URI);
			$xml = $doiObjects->saveXML();

			$errors = error_get_last();
			if( $errors )
			{
				$errorMessages .= doisGetUserMessage("MT007", $doi_id=NULL);
				$errorMessages .= "Document Validation Error: ".$errors['message']."\n";
				header("HTTP/1.0 500 Internal Server Error");
			}				
		}	
	}										
	if( $errorMessages == '' )
	{
		// Update doi information
		$updateResult = updateDoiObject($doiValue,$doiObjects,$urlValue);
		if(!$updateResult){	
		// Update the DOI.
			if($urlValue)
			{
				$response1 = doisRequest("mint",$doiValue, $urlValue, $xml,$client_id);				
			}
			
			if($doiObjects)
			{
				$response2 = doisRequest("update",$doiValue, $urlValue, $xml,$client_id);
			}
			
			if( $response1 && $response2 )
			{
				if( doisGetResponseType($response1) == gDOIS_RESPONSE_SUCCESS && doisGetResponseType($response2) == gDOIS_RESPONSE_SUCCESS)
				{
					// We have successfully updated the doi through datacite.
					$notifyMessage = doisGetUserMessage("MT002", $doiValue);
					header("HTTP/1.0 200 OK");
				}
				else
				{
					$errorMessages .= doisGetUserMessage("MT010", $doi=NULL);
					$logMessage = "MT010 ".$response;
					header("HTTP/1.0 500 Internal Server Error");
				}
			}
			else
			{	
				$errorMessages .= doisGetUserMessage("MT005", $doi=NULL);
				header("HTTP/1.0 500 Internal Server Error");
			}
		}else{
				
			$errorMessages .= '<br />'.$updateResult;
			header("HTTP/1.0 500 Internal Server Error");		
		}
	}

	if($errorMessages)
	{
		$outstr =  $errorMessages;			
		//We need to log this activity as errorred
		if($logMessage)
		{
			$errorMessages .= $logMessage;
		}
		insertDoiActivity("UPDATE",$doiValue,"FAILURE",$client_id,$errorMessages);
	}
	
	if($notifyMessage)
	{
		//We need to log this activity
		insertDoiActivity("UPDATE",$doiValue,"SUCCESS",$client_id,$notifyMessage);
		$outstr = $notifyMessage;
	}
	
	//we now need to return the result back to the clling program.
	header('Content-type: text/html');
	echo $outstr;
?>
