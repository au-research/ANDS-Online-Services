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

$ip_address = trim($_SERVER['REMOTE_ADDR']);

$app_id = trim(getQueryValue('app_id')); 	//passed as a parameter

$doiValue = trim(getQueryValue('doi'));

	//first up, lets check that this client is permitted to update this doi.

	$client_id = checkDoisValidClient($ip_address,$app_id);
	
	if(!$client_id)
	{
		$errorMessages .= doisGetUserMessage("MT009", $doi_id = NULL);
		header("HTTP/1.0 415 Authentication Error");	
		
	}else{
		
		if(!checkDoisClientDoi($doiValue,$client_id))
		{
			$errorMessages .= doisGetUserMessage("MT008", $doiValue);
			header("HTTP/1.0 415 Authentication Error");
		} 	
	}	

	if(getDoiStatus($doiValue)!="INACTIVE")
	{
		$errorMessages .= "DOI ".$doiValue." is not set to inactive so cannot activate it.<br />";	
		header("HTTP/1.0 500 Internal Server Error");	
	}

	if( $errorMessages == '' )
	{
		// Update doi information
		$status = "ACTIVE";
		$activateResult = setDoiStatus($doiValue,$status);
		$xml = file_get_contents("http://".eHOST.eROOT_DIR."doi_xml.php?doi=".$doiValue);
	
		if(!$activateResult){	
		// Activate the DOI.

			$response = doisRequest("update",$doiValue,$urlValue = NULL ,$xml, $client_id );
			echo $response." is the activate error<br />";
			if($response)
			{
				if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )
				{
					// We have successfully activated the doi through datacite.
					$notifyMessage .= doisGetUserMessage("MT004", $doiValue);
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
				
			$errorMessages .= '<br />'.$activateResult;	
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
		insertDoiActivity("ACTIVATE",$doiValue,"FAILURE",$client_id,$errorMessages);		

	}
	
	if($notifyMessage)
	{
		//We need to log this activity as successful
		insertDoiActivity("ACTIVATE",$doiValue,"SUCCESS",$client_id,$notifyMessage);	

		$outstr = $notifyMessage;
	}
	
	//we now need to return the result back to the calling program.
	header('Content-type: text/html');
	echo $outstr;
?>
