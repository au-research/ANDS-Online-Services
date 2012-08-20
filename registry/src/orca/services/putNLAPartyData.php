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

require '/var/www/htdocs/registry/global_config.php';

define('gRIF_SCHEMA_PATH', eAPPLICATION_ROOT.'/orca/schemata/registryObjects.xsd');
define('gRIF_SCHEMA_URI', 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');
define('gCURRENT_SCHEMA_VERSION', '1.3');
define('gDATA_SOURCE','NLA_PARTY');
define('gNLA_SRU_URI','http://www.nla.gov.au/apps/srw/search/peopleaustralia');
define('gSOLR_UPDATE_URL' , $solr_url . "update");

require '/var/www/htdocs/registry/_includes/_environment/database_env.php';
require '/var/www/htdocs/registry/_includes/_functions/database_functions.php';
require '/var/www/htdocs/registry/_includes/_functions/general_functions.php';
require '/var/www/htdocs/registry/_includes/_functions/access_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_data_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_data_source_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_export_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_access_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_import_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_cache_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_presentation_functions.php';
require '/var/www/htdocs/registry/orca/_functions/orca_constants.php';

chdir("/var/www/htdocs/registry/orca/_includes");
function htmlNumericCharRefs($unsafeString)
{
        $safeString = str_replace("&", "&#38;", $unsafeString);
        $safeString = str_replace('"', "&#34;", $safeString);
        $safeString = str_replace("'", "&#39;", $safeString);
        $safeString = str_replace("<", "&#60;", $safeString);
        $safeString = str_replace(">", "&#62;", $safeString);
        return $safeString;
}
function esc($unsafeString, $forJavascript=false)
{
        $safeString = $unsafeString;
        if( $forJavascript )
        {
                $safeString = str_replace('\\', '\\\\', $safeString);
                $safeString = str_replace("'", "\\'", $safeString);
        }
        $safeString = htmlNumericCharRefs($safeString);
        $safeString = str_replace("\r", "", $safeString);
        $safeString = str_replace("\n", "&#xA;", $safeString);
        return $safeString;
}

// Open a connection to the database.
// This will be closed automatically by the framework.
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);


// Open a connection to the database.
// This will be closed automatically by the framework.
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);

$services = $argv[1];
$actions ='';
$partyIdentifiers = array();
$setIdentifiers = array();

// Get the required NLA party identifiers or the nlaset local registry_object_keys from the database.
if($services=="relatedNLA"){
	//returns a list of all nla identifiers that are related objects to collections, services or activities and are not registry objects
	$partyIdentifiers = getPartyIdentifiers();
}
elseif($services=="partyNLA"){
	//returns a list of all nla identifiers that are party identifiers 	 and are not registry objects
	$partyIdentifiers = getPartyNLAIdentifiers();
}
elseif($services=="setNLA"){
	//returns a list of all parties from the nla party set for harvest
	$setIdentifiers = getSpecialObjectSet("nlaSet","Party");
}

if($partyIdentifiers)
{
		$responseType = 'success';
		$runErrors = null;
		$actions = "";
		$errors = null;
		$startTime = microtime(true);

	foreach($partyIdentifiers as $partyIdentifier){

		$partyId = trim(str_replace("http://nla.gov.au/","",$partyIdentifier["partyIdentifier"]));

		$requestURI =  gNLA_SRU_URI."?query=rec.identifier=%22".$partyId."%22&version=1.1&operation=searchRetrieve&recordSchema=http%3A%2F%2Fands.org.au%2Fstandards%2Frif-cs%2FregistryObjects";

		$get = curl_init();
		curl_setopt($get, CURLOPT_URL, $requestURI);
		curl_setopt($get, CURLOPT_RETURNTRANSFER, true);
		$ch = curl_exec($get);
		$curlinfo = curl_getinfo($get);
		curl_close($get);



		// Get the xml data.
		$registryObjects = new DOMDocument();

		$domObjects = explode("recordData>",$ch);
		if(isset($domObjects[1])){
		$result = $registryObjects->loadXML(str_replace("</registryObjects></","</registryObjects>",($domObjects[1])));
		$registryObjects->xinclude();

		$errors = error_get_last();
		if( $errors )
		{
			$runErrors = "Document Load Error: ".$errors['message']."\n";
		}

		if( !$runErrors )
		{
		// run an XSLT transformation
			$registryObjects = transformToRif2($registryObjects);
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

			// Validate it against the orca schema.
			$result = $registryObjects->schemaValidate(gRIF_SCHEMA_PATH);

			$errors = error_get_last();
			if( $errors )
			{
				$runErrors .= "Document Validation Error: ".$errors['message']."\n";
			}
		}

		if( !$runErrors )
		{
			// Import the data.
			$runErrors = importRegistryObjects($registryObjects, gDATA_SOURCE, $runResultMessage,'SYSTEM','PUBLISHED');

			if(!$runErrors)
			{
				$actions .= ">>SUCCESS nla party imported with key ".$partyId."\n";
				
			}
		}


		if( $runErrors )
		{
			$actions .= ">>ERRORS\n";
			$actions .= $runErrors;
		}

	}
	$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);
	$actions  .= "Time Taken: $timeTaken seconds\n";
	}
	//echo $actions;
}
elseif($setIdentifiers)
{
	$startTime = microtime(true);
	$responseType = 'success';
	$runErrors = '';
	$runResultMessage = "";
	$actions = "";
	$errors = null;

	foreach($setIdentifiers as $identifiers)
	{
		//this time we are querying NLA to see if a record has been matched. ie we are looking for an NLA record that has our local key as an identifier
		$requestURI =  gNLA_SRU_URI."?query=cql.anywhere+%3D+%22".urlencode($identifiers["registry_object_key"])."%22&version=1.1&operation=searchRetrieve&recordSchema=http%3A%2F%2Fands.org.au%2Fstandards%2Frif-cs%2FregistryObjects";

		$get = curl_init();
		curl_setopt($get, CURLOPT_URL, $requestURI);
		curl_setopt($get, CURLOPT_RETURNTRANSFER, true);
		$ch = curl_exec($get);
		$curlinfo = curl_getinfo($get);
		curl_close($get);

		$numrecords = explode("numberOfRecords",$ch);
		$recordNum = str_replace("</","",str_replace(">","",$numrecords[1]));
		// Lets find out if there is a match made
		if($recordNum!="0"){
			// "we have found an NLA record with our local identifier we now need to see of it exists as a party record in our registry with the nla identifier as the key<br />";



			$returnObject = new DOMDocument();
			$object = $returnObject->loadXML($ch);

			// Get the xml data.
			$registryObjects = new DOMDocument();

			$domObjects = explode("recordData>",$ch);

			$result = $registryObjects->loadXML(str_replace("</registryObjects></","</registryObjects>",($domObjects[1])));

			$errors = error_get_last();
			if( $errors )
			{
				$runErrors .= "Document Load Error: ".$errors['message']."\n";
			}

			if( !$runErrors )
			{
			// run an XSLT transformation
				$registryObjects = transformToRif2($registryObjects);
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
			// Validate it against the orca schema.
				$result = $registryObjects->schemaValidate(gRIF_SCHEMA_PATH);
				$errors = error_get_last();
				if( $errors )
				{
					$runErrors .= "Document Validation Error: ".$errors['message']."\n";
				}
			}

			if( !$runErrors )
			{

				$key = $registryObjects->getElementsByTagName("key")->item(0)->nodeValue;
				//check if this nla identifier is already imported as a part record
				$isthere = getRegistryObject($key);
				//if its not there already then lets import it
				if(!$isthere){
					$runErrors = importRegistryObjects($registryObjects, 'NLA', $runResultMessage,'SYSTEM','PUBLISHED');
					if(!$runErrors)
					{
						$actions .= ">>SUCCESS nla party imported with key".$key."\n";
					}
				}
			}

			if( $runErrors )
			{
				$actions .= ">>ERRORS\n";
				$actions .= $runErrors;
			}

		}
	}
	$timeTaken = substr((string)(microtime(true) - $startTime), 0, 5);
	$actions  .= "Time Taken: $timeTaken seconds\n";
}
else
{
	$actions = "No ".str_replace("NLA"," NLA",$services)." Party identifiers to insert \n";
}
date_default_timezone_set('Antarctica/Macquarie');
$actions .= date("d/m/Y h:m:s")."\n";
queueSyncDataSource('NLA_PARTY');
mail("lizwoods.ands@gmail.com","NLA Party imports",$actions);
//echo $actions;
// END: XML Response
// =============================================================================
//require '../../_includes/finish.php';
?>