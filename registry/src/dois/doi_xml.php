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
$xml = '';

$doiValue = getQueryValue('doi');	

$doi =getDoiObject($doiValue);

if(!$doi[0]["doi_id"])
{
	$errorMessages .= doisGetUserMessage("MT011", $doi_id=$doiValue);
	header("HTTP/1.0 200 OK");
	header('Content-type: text/html');
}
elseif($doi[0]["status"]!="ACTIVE")
{
	echo $doi[0]["status"]." is the status <br />";
	$errorMessages .= doisGetUserMessage("MT012", $doi_id=$doiValue);
	header("HTTP/1.0 200 OK");
	header('Content-type: text/html');
}
if(!$errorMessages)
{
$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<resource xmlns="http://datacite.org/schema/kernel-2.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-2.2 http://schema.datacite.org/meta/kernel-2.2/metadata.xsd">';

$xml .='
<identifier identifierType="DOI">'.$doiValue.'</identifier>';
$xml .= exportDoiCreators($doiValue);
$xml .= exportDoiTitles($doiValue);
$xml .= exportDoiPublisher($doiValue);
$xml .= exportDoiPublicationYear($doiValue);
$xml .= exportDoiSubjects($doiValue);
$xml .= exportDoiContributors($doiValue);
$xml .= exportDoiDates($doiValue);
$xml .= exportDoiLanguage($doiValue);
$xml .= exportDoiResourceType($doiValue);
$xml .= exportDoiAlternateIdentifiers($doiValue);
$xml .= exportDoiRelatedIdentifiers($doiValue);
$xml .= exportDoiSizes($doiValue);
$xml .= exportDoiFormats($doiValue);
$xml .= exportDoiVersion($doiValue);
$xml .= exportDoiRights($doiValue);
$xml .= exportDoiDescriptions($doiValue);
$xml .='
</resource>';
	header('Content-type: text/xml');
}else{
	$xml = $errorMessages;
}
	echo $xml;
?>
