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
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);
require 'dois_init.php';
// Page processing
// -----------------------------------------------------------------------------

$errorMessages = '';
$notifyMessage = '';
$logMessage = '';
$outstr = '';
$xml = '';
$response = '';
$ip_address = trim($_SERVER['REMOTE_ADDR']);

$app_id = trim(getQueryValue('app_id')); 	//passed as a parameter

//$app_id = '7c37e1d139e65cc9280f961122b4ef719b9534fd';
//$ip_address = '150.203.59.199';

$client_id = checkDoisValidClient($ip_address,$app_id);


$clientDetail = getDoisClient($app_id );
if($clientDetail['client_id']<'10')
{
	$client_id2 = "0".$clientDetail['client_id'];
}else{
	$client_id2 = $clientDetail['client_id'];
}

$datacite_prefix = $clientDetail['datacite_prefix'];

$urlValue = trim(getQueryValue('url'));		//passed as a parameter

//$urlValue = 'http://www.genomics.csse.unimelb.edu.au/GSS/output.html';

$doiValue = strtoupper($datacite_prefix.$client_id2.'/'.uniqid());	//generate a unique suffix for this doi for this client 

if($_POST){
	if(str_replace("<?xml version=","",implode($_POST))==implode($_POST))
	{
		$xml = "<?xml version=".implode($_POST); 	// passed as posted content
	}
	else 
	{
		$xml = implode($_POST);				// passed as posted content
	}
}
/*if($xml=='')
{
	$xml ='<resource xmlns="http://datacite.org/schema/kernel-2.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-2.2 http://schema.datacite.org/meta/kernel-2.2/metadata.xsd">
	<identifier identifierType="DOI"></identifier>
	<creators>
		<creator>
			<creatorName>Abraham, G</creatorName>
		</creator>
		<creator>
			<creatorName>Kowalczyk, A</creatorName>
		</creator>
		<creator>
			<creatorName>Loi, S</creatorName>
		</creator>	
		<creator>
			<creatorName>Haviv, I</creatorName>
		</creator>	
		<creator>
			<creatorName>Zobel, J</creatorName>
		</creator>						
	</creators>
	<titles>
		<title>Prognostic gene set signatures derived from breast cancer microarray gene expression data</title>
	</titles>

	<publisher>CSSE Uni Melbourne</publisher>
	<publicationYear>2011</publicationYear>
</resource>
	';
}*/

	$doiObjects = new DOMDocument();
				
	$result = $doiObjects->loadXML($xml);

	$errors = error_get_last();
	
	// we need to insert the determined doi value into the xml string to be sent to datacite
	// so we create a new 'identifier' element, set the identifierType attribute to DOI and 
	// replace the current identifier element then  write out to the xml string that is passed
	$currentIdentifier=$doiObjects->getElementsByTagName('identifier');
	for($i=0;$i<$currentIdentifier->length;$i++){
		$doiObjects->getElementsByTagName('resource')->item(0)->removeChild($currentIdentifier->item($i));
	}
	$newdoi = $doiObjects->createElement('identifier',$doiValue);
	$newdoi->setAttribute('identifierType',"DOI");	
	$doiObjects->getElementsByTagName('resource')->item(0)->insertBefore($newdoi,$doiObjects->getElementsByTagName('resource')->item(0)->firstChild);

	//$xml = $doiObjects->saveXML();

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
			$errorMessages .= doisGetUserMessage("MT006", $doi_id=NULL);
			$errorMessages .= "Document Validation Error: ".$errors['message']."\n";
			header("HTTP/1.0 500 Internal Server Error");
		}			
		
	}					
	
	if(!$client_id)
	{
		$errorMessages .= doisGetUserMessage("MT009", $doi_id=NULL);
		header("HTTP/1.0 415 Authentication Error");
	}		
				
	if($urlValue=='')
	{
		$errorMessages .= "URL is a mandatory value to mint a doi.<br />";
		header("HTTP/1.0 500 Internal Server Error");		
	}			
	
	if( $errorMessages == '' )
	{
		// Insert doi information into the database
		$insertResult = importDoiObject($doiObjects,$urlValue, $client_id, $created_who='SYSTEM', $status='REQUESTED');
		if(!$insertResult){	
			// Mint the DOI.
			
			$response = doisRequest("mint",$doiValue, $urlValue, $xml,$client_id);
			
			if( $response )
			{
				if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )
				{
					// We have successfully minted the doi through datacite.
								
					$response = doisRequest("update",$doiValue, $urlValue, $xml,$client_id);
					
					if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )			
					{
						$notifyMessage = doisGetUserMessage("MT001", $doiValue);
						$status = "ACTIVE";
						$activateResult = setDoiStatus($doiValue,$status);
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
		}
		else 
		{
			$errorMessages .= '..<br />'.$insertResult;
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
		insertDoiActivity("MINT",$doiValue,"FAILURE",$client_id,$errorMessages);

	}
	
	if($notifyMessage)
	{
		//We need to log this activity
		insertDoiActivity("MINT",$doiValue,"SUCCESS",$client_id,$notifyMessage);

		$outstr = $notifyMessage;
	}
	
	//we now need to return the result back to the calling program.
	header('Content-type: text/html');
	echo $outstr;
?>
