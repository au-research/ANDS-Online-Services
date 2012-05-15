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
require '../../_includes/init.php';
require '../orca_init.php';
set_time_limit(0);
// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 1000*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");

// Set the Content-Type header.
//header("Content-Type: text/xml; charset=UTF-8", true);
$task = getQueryValue('task');
$key = getQueryValue('key');
$foo = getQueryValue('foo');
$solrUrl = getQueryValue('solrUrl');
$draftKey = getQueryValue('draftKey');
$dataSourceKey = getQueryValue('dataSourceKey');
$getRelated = getQueryValue('getRelated');
$subject = getQueryValue('subject');
$registry_object_hash = getQueryValue('registry_object_hash');
$vocab = getQueryValue('vocab');
$relatedKey = getQueryValue('relatedKey');

if($draftKey != '' && $dataSourceKey != '')
{
	
	$draftRegistryObjects = getDraftRegistryObject($draftKey, $dataSourceKey);
	$rifcs = $draftRegistryObjects[0]['rifcs'];
	$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$draftRegistryObjects[0]['class']);
	print $relRifcs;
	require '../../_includes/finish.php';
	exit();
	
}
if($task=='getTitle'){
	if($published = getRegistryObject($relatedKey)){
		echo '<record>'.$published[0]['list_title'].'</record>';
	}else if($draft = getDraftRegistryObject($relatedKey, null)){
		echo '<draft>';
		echo '<title>'.$draft[0]['registry_object_title'].'</title>';
		echo '<ds>'.$draft[0]['registry_object_data_source'].'</ds>';
		echo '</draft>';
	}else{
		echo '<norecord/>';
	}
	//print $draft[0]['registry_object_title'];
	require '../../_includes/finish.php';
	exit();
}
if($subject != '' && $vocab != '')
{
	$resolvedName = '';
	$value = esc($subject);
	$upperCase = strtoupper($vocab);	
	if($upperCase=='RFCD'){
		$resolvedName = getTermsForVocabByIdentifier('rfcd', $value);
	}elseif($upperCase=='ANZSRC-FOR'){
		$valueLength = strlen($value);
		if($valueLength < 6){
			for($i = 0; $i < (6 - $valueLength) ; $i++){
				$value .= '0';
			}				
		}
		$resolvedName = getTermsForVocabByIdentifier("ANZSRC-FOR", $value);
		$resolvedName = $resolvedName[0]['name'];
	}elseif($upperCase=='ANZSRC-SEO'){
						$valueLength = strlen($value);
		if($valueLength < 6){
			for($i = 0; $i < (6 - $valueLength) ; $i++){
				$value .= '0';
			}				
		}
		$resolvedName = getTermsForVocabByIdentifier('ANZSRC-SEO', $value);
		$resolvedName = $resolvedName[0]['name'];
	}elseif($upperCase=='ANZSRC-TOA'){
						$valueLength = strlen($value);
		if($valueLength < 6){
			for($i = 0; $i < (6 - $valueLength) ; $i++){
				$value .= '0';
			}				
		}
		$resolvedName = getTermsForVocabByIdentifier('ANZSRC-TOA', $value);
		$resolvedName = $resolvedName[0]['name'];
	}else{
		$resolvedName = $value;
	}
    if($resolvedName == '') $resolvedName = $subject;
/*
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<subject value="'.$subject.'" resolvedName="'.$resolvedName.'">'.$resolvedName."</subject>\n";*/
	print $resolvedName;
	require '../../_includes/finish.php';
	exit();
}

// comment this function out.....
if($task == 'clearAll')
{
	echo "Running...";
	$response = clearSolrIndex();
	print $response;
	require '../../_includes/finish.php';
	exit();
}
if($solrUrl)
{
	
	header("Content-Type: text/html; charset=UTF-8", true);
	$rifcsContent = '';
	if($key)
	{
		$result = addSolrIndex($key);	
		print('commit: '.$result. "<br/>");
	}
	elseif($dataSourceKey)
	{
		$result = runSolrIndexForDatasource($dataSourceKey);
		print('commit: '.$result. "<br/>");		
	}	
	else 
	{
		echo "<h2>Reindexing SOLR</h2>";
		bench(0);
		bench(1);
		$allKeys = getAllRegistryObjectKey();
		$arraySize = sizeof($allKeys);
		echo "Took " . bench(1) . "s to get all keys..." . $arraySize . "<br/><hr/><br/>";
		ob_flush();flush();
		$chunkSize = 500;
		$j = 1;
		$result = 'test';
		bench(1);
		for($i = 0; $i < $arraySize ; $i++)
		{				
			
			$key = $allKeys[$i]['registry_object_key'];		
			$rifcsContent .= getRegistryObjectXMLforSOLR($key, true);

			
			if(($i % $chunkSize == 0 && $i != 0) || $i == ($arraySize -1))
			{					


					$rifcs = wrapRegistryObjects($rifcsContent);
					$solrrifcs = transformToSolr($rifcs);
					printf("%-145s", "<i>Fetching extRif for records " . $i . " to " . ($i+$chunkSize) . "... [took ".bench(1)."s]</i>"); 
					bench(2);

					if (strlen($solrrifcs) == 0)
					{
						echo $rifcs;
					}				
					else
					{					
						$result = curl_post($solrUrl, $solrrifcs);
					}


	
					$rifcs .= wrapRegistryObjects($rifcsContent);

					$rifcs = transformToSolr($rifcs);									
					$result = curl_post($solrUrl, $rifcs);

					print($j.': ('.$i.')registryObjects is sent to solr ' . $result . "<br/>");					

					
					$result = curl_post($solrUrl.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
					printf("%-145s", "Sending " . $chunkSize ." records to SOLR... [took ".bench(2)."s]</i> (Commit Result: ".$result.")<br/>"); 
					ob_flush();flush();
					$rifcsContent = '';
					$j++;
					bench(1);
			}
		}
		$result = curl_post($solrUrl.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
		print ('optimise: '.$result. "<br/>");
		echo "<hr/>Took " . bench(0) ."s to reindex...";
	}

}


else if($key && $foo)
{
	$result =  getRegistryObjectXMLforSOLR($key);
	header("Content-Type: text/xml; charset=UTF-8", true);
	// BEGIN: XML Response


	$rifcs = wrapRegistryObjects($result);

	$rifcs = transformToSolr($rifcs);

	// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
	// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!

	print $rifcs;
}


else if($key)
{
	header("Content-Type: text/xml; charset=UTF-8", true);



	$rifcs = '';

	if($registryObject = getRegistryObject($key))
	{
		$rifcs .= getRegistryObjectXMLforSOLR($key, true);
		
	}
	$rifcs = wrapRegistryObjects($rifcs);
	// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
	// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!

	print $rifcs;
}

else if($registry_object_hash)
{

	header("Content-Type: text/xml; charset=UTF-8", true);
	$rifcs = '';
	if($registryObject = getRegistryObjectByHash($registry_object_hash))
	{
		$rifcs .= getRegistryObjectXMLforSOLR($registryObject[0]['registry_object_key'], true);	
	}
	$rifcs = wrapRegistryObjects($rifcs);
	// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
	// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!
	print $rifcs;
	

}
/*
elseif ($getRelated){
	header("Content-Type: text/xml; charset=UTF-8", true);
	$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	// BEGIN: XML Response
	// =============================================================================
	$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
	$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
	$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
	if($registryObject = getRegistryObject($getRelated))
	{
		$rifcs .= getRegistryObjectRelatedObjectsforSOLR($getRelated);		
	}
	$rifcs .= "</registryObjects>\n";	
	print $rifcs;	
}
*/

// END: XML Response
// =============================================================================
require '../../_includes/finish.php';
?>

