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
//session_start();
//$_SESSION["timeout"] += 30000;

require '../../_includes/init.php';
require '../orca_init.php';
require '../_functions/assoc_array2xml.php';



$task = getQueryValue('task');
$keyValue = trim(getQueryValue('key'));
$dataSourceValue = urldecode(getQueryValue('data_source'));
$firstLoad = getQueryValue('firstLoad');
$defaultKeys = array('collection','party','activity','service');
if($task == 'get')
	{	
		include('_processes/get.php');
	}
else if($task == 'save')
	{
		include('_processes/save.php');
	}

	// AJAX responses to the manage my records screen
else if ($task == 'manage_my_records')
	{
		include('_processes/manage_my_records.php');		
	}
	
else if($task == 'validate')
	{
		include('_processes/validate.php');
	}

else if($task ==  'delete')
	{	
		include('_processes/delete.php');
	}
	
else if($task ==  'add')
	{
		include('_processes/add.php');
	}	
	
else if($task ==  'getvocab')
	{
		include('_processes/getvocab.php');
	}	
	
else if($task ==  'getSubjectVocab')
	{
		include('_processes/get_subject_vocab.php');

	}	
	
else if($task ==  'searchRelated')
	{
		include('_processes/search_related.php');
	}	
else if($task ==  'keepalive')
	{
		// Dummy method, session is refreshed when COSI init is called at the top of this script	
	}
else if($task ==  'getRelatedClass')
	{
		include('_processes/get_related_class.php');
	}
else if($task ==  'related_object_preview')
{
	include('_processes/related_object_preview.php');
}
else if($task ==  'checkKey')
	{	
		include('_processes/check_key.php');
	}
else if ($task == 'getGroups')
	{
		include('_processes/get_groups.php');	
		
			
	}	
else if($task ==  'fetch_record')
	{
		include('_processes/fetch_record.php');
		
	}
else if($task ==  'recover_record')
	{
		
		include('_processes/recover_record.php');

	}
else if($task ==  'flag_draft')
	{	
		include('_processes/flag_draft.php');
	}
else if($task ==  'flag_regobj')
	{	
		include('_processes/flag_regobj.php');
	}

if($task ==  'flag_draft' || $task ==  'recover_record' || $task == 'validate')
{
	$result = addDraftToSolrIndex($keyValue);
}
	
if($task ==  'delete' || $task ==  'add')
{
	$hash = sha1($keyValue.$dataSourceValue);
	$result = deleteSolrHashKey($hash);
}
	
require '../../_includes/finish.php';

function saveDraftRegistryObject($rifcs, $objectClass, $dataSource ,$keyValue, $title, $status='DRAFT', $maintainAttributes = false)
{
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($rifcs);
	$xs = 'rif';
	//var_dump($registryObjects);

	$gXPath = new DOMXpath($registryObjects);
	$defaultNamespace = $gXPath->evaluate('/*')->item(0)->namespaceURI;

	$gXPath->registerNamespace($xs, $defaultNamespace);
		
	$registryObject = $gXPath->evaluate("$xs:registryObject")->item(0);
			
	$draft_owner = getLoggedInUser();
	
	$draft_key = substr($gXPath->evaluate("$xs:key", $registryObject)->item(0)->nodeValue, 0, 512);
	
	if ($objectClass == null)
	{
		$objectClass = $gXPath->evaluate("$xs:collection|$xs:party|$xs:activity|$xs:service", $registryObject)->item(0)->nodeName;
	}

	$objectClass = strtolower(substr($objectClass, 0, 1)) . substr($objectClass, 1);
	
	$draft_class = strtoupper(substr($objectClass, 0, 1)) . substr($objectClass, 1);
	
	$draft_group = $registryObject->getAttribute("group");

	$draft_type = $gXPath->evaluate("$xs:$objectClass", $registryObject)->item(0)->getAttribute("type");
	
	$draft_data_source = $dataSource; //$gXPath->evaluate("$xs:originatingSource", $registryObject)->item(0)->nodeValue;
	
	$date_created = date('Y-m-d H:i:s'); 
	
	// If the object already exists, maintain creation and flagged details
	$flagged = false;
	if ($r = getRegistryObject($draft_key)) { $flagged = $r[0]['flag'] == 't'; $date_created = $r[0]['created_when']; } 
	if ($d = getDraftRegistryObject($draft_key,$draft_data_source)) { $flagged = $d[0]['flag'] == 't'; $date_created = $d[0]['date_created']; }

	// Last date modified is NOW!
	$date_modified = date('Y-m-d H:i:s'); 
	
	// Are we keeping all the pre-existing details?
	if ($maintainAttributes)
	{
		$d = getDraftRegistryObject($draft_key,$draft_data_source);
		if ($d)
		{
			$date_modified = $d[0]['date_modified'];
			$draft_owner = $d[0]['draft_owner'];
			$status = $d[0]['status'];
		}
	}
	
	
	$qualityTestResult = '';
	$errorCount = '0';                              
	$warningCount = '0';   
	insertDraftRegistryObject($draft_owner, $draft_key, $draft_class, $draft_group, $draft_type, $title, $draft_data_source, $date_created, $date_modified, $rifcs, $qualityTestResult, $errorCount, $warningCount, $status);
	runQualityLevelCheckForDraftRegistryObject($draft_key, $draft_data_source);
	// Maintain the flagged status
	if ($flagged) 
	{
		setDraftFlag($draft_key, $draft_data_source, true);
	}
	
	if($keyValue != $draft_key)
	{
		deleteDraftRegistryObject($dataSource, $keyValue);		
	}
    
}


function createPreview($rifcs, $objectClass, $dataSource, $dateCreated)
{
	//print("<pre>");
	//print_r($rifcs);
	//print("</pre>");
	//print($objectClass);
	$relatedXml = getRelatedXml($dataSource,$rifcs,$objectClass);		
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($relatedXml);
	$rifcs2preview = new DomDocument();
	$rifcs2preview->load('../_xsl/rifcs2preview.xsl');
	$proc = new XSLTProcessor();
	$proc->setParameter('', 'dataSource', $dataSource);
	$proc->setParameter('','dateCreated', $dateCreated);
	$proc->importStyleSheet($rifcs2preview);
	$preview = $proc->transformToXML($registryObjects);
	return $preview;	
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
}

function replace_unicode_escape_sequence2($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8');
}

function rmdGetName($registryObjectKey)
{
	$name = '';
	$names = getNames($registryObjectKey);
	if( $names )
	{
		for( $i = 0; $i < count($names); $i++ )
		{		
			if( $i != 0 )
			{
				$name .= ' '.gCHAR_MIDDOT.' ';
			}
			$name .= $names[$i]['value'];
		}
	}
	return $name;
}

function addDraftToSolrIndex($registryObjectKey, $commit=true)
{
	$allKeys = getDraftRegistryObject($registryObjectKey , null);
	$arraySize = sizeof($allKeys);
	$result = '';
	if($allKeys)
	{
		for($i = 0; $i < $arraySize ; $i++)
		{				
			$key = $allKeys[$i]['draft_key'];
			$dataSourceKey = $allKeys[$i]['registry_object_data_source'];
			$xml = "    <extRif:extendedMetadata key=\"".esc($key)."\">\n";			
			$hash = sha1($key.$dataSourceKey);
			if ($hash)
			{
				$xml .= "      <extRif:keyHash>".esc($hash)."</extRif:keyHash>\n";
			}
			$dataSource = getDataSources($dataSourceKey, null);
			$allow_reverse_internal_links = $dataSource[0]['allow_reverse_internal_links'];
			$allow_reverse_external_links = $dataSource[0]['allow_reverse_external_links'];
			$hash = sha1($dataSourceKey);
			if ($hash)
			{
				$xml .= "      <extRif:dataSourceKeyHash>".esc($hash)."</extRif:dataSourceKeyHash>\n";
			}
			$xml .= "      <extRif:status>".esc($allKeys[$i]['status'])."</extRif:status>\n";
			$xml .= "      <extRif:dataSourceKey>".esc($dataSourceKey)."</extRif:dataSourceKey>\n";		
			$reverseLinks = 'NONE';
	
			if($allow_reverse_internal_links == 't' && $allow_reverse_external_links == 't')
			{
				$reverseLinks = 'BOTH';
			}
			else if($allow_reverse_internal_links == 't')
			{
				$reverseLinks = 'INT';
				
			}
			else if($allow_reverse_external_links == 't')
			{
				$reverseLinks = 'EXT';
			}
			$xml .= "      <extRif:reverseLinks>".$reverseLinks."</extRif:reverseLinks>\n";
			
			
			// Get registry date modified			
			if (!($registryDateModified =  $allKeys[$i]['date_modified']))
			{
					$registryDateModified = time(); // default to now
			}
			$xml .= "      <extRif:registryDateModified>".$registryDateModified."</extRif:registryDateModified>\n";
	
	
	
			// displayTitle
			// -------------------------------------------------------------
			$xml .= '      <extRif:displayTitle>'.esc(trim($allKeys[$i]['registry_object_title'])).'</extRif:displayTitle>'."\n";
			
			
			// listTitle
			// -------------------------------------------------------------
			$xml .= '      <extRif:listTitle>'.esc(trim($allKeys[$i]['registry_object_title'])).'</extRif:listTitle>'."\n";
			$xml .= '      <extRif:flag>'.($allKeys[$i]['flag'] == 'f' ? '0' : '1').'</extRif:flag>'."\n";
			$xml .= '      <extRif:warning_count>'.esc(trim($allKeys[$i]['warning_count'])).'</extRif:warning_count>'."\n";
			$xml .= '      <extRif:error_count>'.esc(trim($allKeys[$i]['error_count'])).'</extRif:error_count>'."\n";
			//$xml .= '      <extRif:gold_status_flag>'.esc(trim($allKeys[$i]['gold_status_flag'])).'</extRif:gold_status_flag>'."\n";
			$xml .= '      <extRif:quality_level>'.esc(trim($allKeys[$i]['quality_level'])).'</extRif:quality_level>'."\n";
			$xml .= '      <extRif:feedType>'.($allKeys[$i]['draft_owner'] == 'SYSTEM' ? 'harvest' : 'manual').'</extRif:feedType>'."\n";
			$xml .= "    </extRif:extendedMetadata>\n";		
			$rifcsContent = unwrapRegistryObject($allKeys[$i]['rifcs']);	
			$rifcsContent .= $xml;
		}
		$rifcs = wrapRegistryObjects($rifcsContent);
		$solrrifcs = transformToSolr($rifcs);
		//echo $solrrifcs;
		if (strlen($solrrifcs) == 0)
		{
			echo "<font style='color:red'>".$rifcs."</font>";
		}				
		else
		{					
			$result = curl_post(gSOLR_UPDATE_URL, $solrrifcs);
			$result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
		}
	}
	return $result;
}

function deleteSolrHashKey($hashkey)
{
	return curl_post(gSOLR_UPDATE_URL.'?commit=true', '<delete><id>'.$hashkey.'</id></delete>');
}


function unwrapRegistryObject($rifcsString)
{
	$registryObjects = new DOMDocument();
	$result = $registryObjects->loadXML($rifcsString);
	if(!$result)
	{
	$error = error_get_last();
	echo "<font style='color:red'>".$error['message']."</font>";
	}
	$ro = $registryObjects->getElementsByTagName("registryObject")->item(0);
    return $registryObjects->saveXML($ro);
}

?>
