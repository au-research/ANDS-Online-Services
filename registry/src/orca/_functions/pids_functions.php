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
define('gPIDS_RESPONSE_FAILURE', 'FAILURE');
define('gPIDS_RESPONSE_SUCCESS', 'SUCCESS');

define('gPIDS_MESSAGE_SYSTEM', 'SYSTEM');
define('gPIDS_MESSAGE_USER',   'USER');

function pidsGetOwnerHandle()
{
	$handleValue = null;
	
	$serviceName = "getOwnerHandle";
	$response = pidsRequest($serviceName, '');
	
	if( pidsGetResponseType($response) == gPIDS_RESPONSE_SUCCESS )
	{
		$handleValue = pidsGetHandleValue($response);
	}
	
	return $handleValue;
}

function pidsGetFirstURLProperty($handle)
{
	$prop = null;
	
	$serviceName = "getHandle";
	$parameters = "handle=".urlencode($handle);
	$response = pidsRequest($serviceName, $parameters);
	
	$responseDOMDoc = new DOMDocument();
	$result = $responseDOMDoc->loadXML($response);
	if( $result )
	{
		$properties = $responseDOMDoc->getElementsByTagName("property");
		if( $properties )
		{	
			foreach( $properties as $property )
			{
				$propertyIndex = $property->getAttribute("index");
				$propertyType = $property->getAttribute("type");
				$propertyValue = $property->getAttribute("value");
				
				if( strtoupper($propertyType) == "URL" )
				{
					$prop = $property;
					break;
				}
			}
		}	
	}
	return $prop;
}

function pidsUpdatePropertyValue($handle, $index, $propertyValue)
{
	$errorMessages = '';
	
	// Update the property value.
	$serviceName = "modifyValueByIndex";
	$parameters  = "handle=".urlencode($handle);
	$parameters .= "&index=".urlencode($index);
	$parameters .= "&value=".urlencode($propertyValue);
	$response = pidsRequest($serviceName, $parameters);
	if( $response )
	{
		if( pidsGetResponseType($response) != gPIDS_RESPONSE_SUCCESS )
		{
			$errorMessages = pidsGetUserMessage($response);
			if( !$errorMessages )
			{
				$errorMessages = 'There was a problem with the request [2].';
			}
		}
	}
	else
	{	
		$errorMessages = 'There was an error with the service [1].';
	}
	return $errorMessages;
}

function pidsAddURLProperty($handle, $url)
{
	$errorMessages = '';
	
	// Update the property value.
	$serviceName = "addValue";
	$parameters = "handle=".urlencode($handle);
	$parameters .= "&type=URL";
	$parameters .= "&value=".urlencode($url);
	$response = pidsRequest($serviceName, $parameters);
	if( $response )
	{
		if( pidsGetResponseType($response) != gPIDS_RESPONSE_SUCCESS )
		{
			$errorMessages = pidsGetUserMessage($response);
			if( !$errorMessages )
			{
				$errorMessages = 'There was a problem with the request [2].';
			}
		}
	}
	else
	{	
		$errorMessages = 'There was an error with the service [1].';
	}
	return $errorMessages;
}

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
	$requestBody .= '    <property name="authDomain" value="'.getSessionVar(sAUTH_DOMAIN).'::'.esc(eAPP_ROOT).'orca/user/'.'" />'."\n";
	$requestBody .= '  </properties>'."\n";
	$requestBody .= '</request>';
	
	$context  = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: text/plain', 'content' => $requestBody)));
	$result = file_get_contents($requestURI, false, $context);
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