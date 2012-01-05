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
       Object: /dois/dois_functions.php
   Written By: Liz Woods
 Created Date: 28 March 2011
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================
 

*******************************************************************************/

function doisGetDOIURI($doi)
{
	return 'http://dx.doi.org/'.$doi;
}

function doisGetResponseType($response)
{
	return $response;
}

function doisGetUserMessage($messageId, $doi_id)
{
	$userMessage = '';
	
	switch($messageId)
	{
		case "MT001":
			$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully minted.";
			break;
		case "MT002":
			$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully updated.";
			break;
		case "MT003":
			$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully inactivated.";
			break;
		case "MT004":
			$userMessage = "[".$messageId."] DOI ".$doi_id." was successfully activated.";		
			break;
		case "MT005":
			$userMessage = "[".$messageId."] The ANDS Cite My Data service is currently unavailable. Please try again at a later time. If you continue to experience problems please contact services@ands.org.au.";		
			break;
		case "MT006":
			$userMessage = "[".$messageId."] The metadata you have provided to mint a new DOI has failed the schema validation. 
			Metadata is validated against the latest version of the DataCite Metadata Schema. 
			For information about the schema and the latest version supported, 
			please visit the ANDS website http://ands.org.au. 
			Detailed information about the validation errors can be found below.<br />";		
			break;
		case "MT007":
			$userMessage = "[".$messageId."] The metadata you have provided to update DOI ".$doi_id." has failed the schema validation. 
			Metadata is validated against the DataCite Metadata Schema.
			For information about the schema and the latest version supported, 
			please visit the ANDS website http://ands.org.au. 
			Detailed information about the validation errors can be found below.<br />";		
			break;
		case "MT008":
			$userMessage = "[".$messageId."] You do not appear to be the owner of DOI ".$doi_id.". If you believe this to be incorrect please contact services@ands.org.au.";		
			break;								
		case "MT009":
			$userMessage = "[".$messageId."] You are not authorised to use this service. For more information or to request access to the service please contact services@ands.org.au.";
			break;
		case "MT010":
			$userMessage = "[".$messageId."] There has been an unexpected error processing your doi request. For more information please contact services@ands.org.au.";					
			break;
		case "MT011":
			$userMessage = "[".$messageId."] DOI ".$doi_id." does not exist in the ANDS Cite My Data service.";					
			break;	
		case "MT012":
			$userMessage = "[".$messageId."] No metadata exists in the Cite My Data service for DOI ".$doi_id;					
			break;						
		default:
			$userMessage = "There has been an unidentified error processing your doi request. For more information please contact services@ands.org.au.";
			break;									
	}
	return $userMessage;
}


function doisRequest($service, $doi, $url, $metadata,$client_id)
{
	$resultXML = '';
	//$mode ="?testMode=true";
	$mode='';
	$authstr = gDOIS_DATACENTRE_USERNAME.".CENTRE-".$client_id.":".gDOIS_DATACITE_PASSWORD;

	$ch = curl_init();
	
	$requestURI = gDOIS_SERVICE_BASE_URI;
	
	if($service=="mint")
	{
		$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
		$metadata="doi=".$doi."\nurl=".$url;
		$requestURI = gDOIS_SERVICE_BASE_URI."doi".$mode;
		curl_setopt($ch, CURLOPT_POST,1);		
	}
	elseif($service=="update")
	{	
		$context  = array('Content-Type:application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));			
		$requestURI = gDOIS_SERVICE_BASE_URI."metadata".$mode;
		curl_setopt($ch, CURLOPT_POST,1);	
	}
	elseif($service=="delete")
	{
		$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
		$requestURI = gDOIS_SERVICE_BASE_URI."metadata/".$doi;			
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");				
	}

	curl_setopt($ch, CURLOPT_URL, $requestURI);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
	curl_setopt($ch, CURLOPT_HTTPHEADER,$context);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$metadata);
	$result = curl_exec($ch);

	$curlinfo = curl_getinfo($ch);

	curl_close($ch);

	if($result)
	{
		$resultXML = $result;
	}
	return $resultXML;
}

function doisAddDatacentre($symbol,$client_name,$client_contact_name,$client_contact_email,$datacite_prefix,$domains)
{
	$resultXML = '';
	//$mode ="&testmode=true";
	$mode='';
	$domainList='';
	for($i=0;$i<count($domains);$i++)
	{
		$domainList .= $domains[$i].",";
	}
	$domainList = trim($domainList,",");

	$outxml = '<?xml version="1.0" encoding="UTF-8"?>
	<datacentre><name>'.$client_name.'</name>
	<symbol>'.$symbol.'</symbol>
	<domains>'.$domainList.'</domains>
	<isActive>true</isActive>
	<prefixes><prefix>'.trim($datacite_prefix,"/").'</prefix></prefixes>
	<contactName>'.$client_contact_name.'</contactName>
	<contactEmail>'.$client_contact_email.'</contactEmail>
	</datacentre>';

	$authstr = gDOIS_DATACENTRE_USERNAME.":".gDOIS_DATACENTRE_PASSWORD;

	$context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));		
	$requestURI = gDOIS_DATACENTRE_BASE_URI;	

	$newch = curl_init();
	curl_setopt($newch, CURLOPT_URL, $requestURI);
	curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($newch, CURLOPT_CUSTOMREQUEST, "PUT");			
	curl_setopt($newch, CURLOPT_HTTPHEADER,$context);
	curl_setopt($newch, CURLOPT_POSTFIELDS,$outxml);
	
	$result = curl_exec($newch);

	$curlinfo = curl_getinfo($newch);

	curl_close($newch);

	if( $result )
	{
		$resultXML = $result;
	}
	
}

function doisValidDomain($domain)
{
	if (preg_match ("/^[a-z0-9][a-z0-9\-]+[a-z0-9](\.[a-z]{2,4})+$/i", $domain)) 
	{
   		return true;
	} else {
    	return true;
	}
}
function doisValidIp($ip) { 
    return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" . 
            "(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip); 
} 

function doisValidEmail($email) {
  if (preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i", $email))
	{
   		return true;
	} else {
    	return false;
	}  
}
    
function doisDomainAvailible($domain)
{

    //initialize curl
    $curlInit = curl_init($domain);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

    //get answer
    $response = curl_exec($curlInit);  
    $curlInfo = curl_getinfo($curlInit);
    curl_close($curlInit);
    //check http status code
    if ($curlInfo["http_code"]<400) return true;

    return false;
}

?>