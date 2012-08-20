<?php
/*
Copyright 2008 The Australian National University
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
       Object: /pids/pids_functions.php
   Written By: James Blanden
 Created Date: 28 October 2008
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================
 James Blanden        03/12/2008    pidsGetHandleListDescription($handle).

*******************************************************************************/
define('gPIDS_RESPONSE_FAILURE', 'FAILURE');
define('gPIDS_RESPONSE_SUCCESS', 'SUCCESS');

define('gPIDS_MESSAGE_SYSTEM', 'SYSTEM');
define('gPIDS_MESSAGE_USER',   'USER');

function pidsGetHandleURI($handle)
{
	return 'http://hdl.handle.net/'.$handle;
}

function pidsGetResponseType($response)
{
	$responseType = gPIDS_RESPONSE_FAILURE;
	$responseDOMDoc = new DOMDocument();
	$result = $responseDOMDoc->loadXML($response);
	if( $result )
	{
		$responseType = strtoupper($responseDOMDoc->getElementsByTagName("response")->item(0)->getAttribute("type"));
	}
	return $responseType;
}

function pidsGetUserMessage($response)
{
	$userMessage = '';
	$responseDOMDoc = new DOMDocument();
	$result = $responseDOMDoc->loadXML($response);
	if( $result )
	{
		$messageType = strtoupper($responseDOMDoc->getElementsByTagName("message")->item(0)->getAttribute("type"));
		if( $messageType == gPIDS_MESSAGE_USER )
		{
			$userMessage = esc($responseDOMDoc->getElementsByTagName("message")->item(0)->nodeValue);
		}
	}
	return $userMessage;
}

function pidsGetHandleValue($response)
{
	$handleValue = '';
	$responseDOMDoc = new DOMDocument();
	$result = $responseDOMDoc->loadXML($response);
	if( $result )
	{
		$handleValue = $responseDOMDoc->getElementsByTagName("identifier")->item(0)->getAttribute("handle");
	}
	return $handleValue;
}

function pidsRequest($serviceName, $parameters)
{
	$resultXML = '';
	
	$requestURI = gPIDS_SERVICE_BASE_URI.$serviceName."?".$parameters;
	
	$requestBody  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$requestBody .= '<request name="'.$serviceName.'">'."\n";
	$requestBody .= '  <properties>'."\n";
	$requestBody .= '    <property name="appId" value="'.gPIDS_APP_ID.'" />'."\n";
	$requestBody .= '    <property name="identifier" value="'.getSessionVar(sROLE_ID).'" />'."\n";
	$requestBody .= '    <property name="authDomain" value="'.getSessionVar(sAUTH_DOMAIN).'" />'."\n";
	$requestBody .= '  </properties>'."\n";
	$requestBody .= '</request>';
	
	$context  = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: text/plain', 'content' => $requestBody)));
	//$result = file_get_contents($requestURI, false, $context);
	// create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $requestURI);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//VERY IMPORTANT, skip SSL

	// $output contains the output string
	$result = curl_exec($ch);
	//var_dump($output);
	if( $result )
	{
		$resultXML = $result;
	}
	return $resultXML;
}

function pidsGetHandleListDescription($handle)
{
	$listDescription = '';
			      	
    // Get the handle to display the first property
	$serviceName = "getHandle";
	$parameters = "handle=".urlencode($handle);
	$response = pidsRequest($serviceName, $parameters);
	
	if( $response )
	{
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		
		if( $result )
		{
			// Get the value of the first property.
			if( $responseDOMDoc->getElementsByTagName("property")->item(0) )
			{
				$firstPropertyValue = $responseDOMDoc->getElementsByTagName("property")->item(0)->getAttribute("value");
				if( $firstPropertyValue )
				{
					$listDescription = $firstPropertyValue;
				}
			}

		}
	}
	
	return $listDescription;
}
?>