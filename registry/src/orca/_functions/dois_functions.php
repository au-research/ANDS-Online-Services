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
	if (preg_match ("/^[a-z0-9][a-z0-9\-]+[a-z0-9](\.[a-z]{2,11})+$/i", $domain)) 
	{
   		return true;
	} else {
    	return false;
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