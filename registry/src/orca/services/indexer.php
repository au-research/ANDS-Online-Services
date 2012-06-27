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
$status = getQueryValue('status');
if(!$status) $status = 'All';
$draftKey = getQueryValue('draftKey');
$dataSourceKey = getQueryValue('dataSourceKey');
$totalCount = 0;
$solr_update_url = $solr_url.'update';
//echo $solr_update_url;
switch($task){
	case "indexAll":	index($status);										break;
	case "indexDS": 	indexDS($dataSourceKey , $status); 					break;
	case "indexDSo":	indexDS($dataSourceKey, $status, false);			break;
	case "indexKey":	indexKey($key);										break;
	case "clearAll":	clearAll();											break;
	case "clearDS":		clearDS($dataSourceKey);							break;
	case "clearKey":	clearKey($key);										break;
	case "checkQuality":	checkQuality($key,$dataSourceKey);				break;
	default: print "no task defined"; 										break;
}

/*FUNCTIONS*/
function index($status = 'All')
{
	global $totalCount;
	global $solr_update_url;
	echo 'Reindexing SOLR: '.$solr_update_url.'<br/>';
	$dataSources = getDataSources(null, null);
	$arraySize = sizeof($dataSources);
	bench(0);
	for($i= 0 ; $i < $arraySize ; $i++)
	{
		indexDS($dataSources[$i]['data_source_key'], $status, false);
	}
	indexDS('PUBLISH_MY_DATA', $status);
	$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	print ('commit ALL: '.$result. "<br/>");
	$result = curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	print ('optimise: '.$result. "<br/>");
	echo "<hr/>Took " . bench(0) ."s to reindex...".$totalCount." records";
	
}

function clearAll(){
	global $solr_update_url;
	echo "Clearing All SOLR indexes: ".$solr_update_url;
	$response = clearAllSolrIndex();
	print $response;
}

function indexDS($dataSourceKey, $status = 'All', $optimise = true){
	global $solr_update_url;
	if($status == 'PUBLISHED' || $status == 'All')
	{
		addPublishedSolrIndexForDatasource($dataSourceKey);
	}
	if($status == 'DRAFT' || $status == 'All')
	{
		addDraftSolrIndexForDatasource($dataSourceKey);
	}
	print "<br/>done";
	ob_flush();flush();
	
	if($optimise)
	{
		print '...but<br/>now optimising<br/>';
		ob_flush();flush();
		$result = curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
		print $result;
	}
}

function clearDS($dataSourceKey){
	global $solr_update_url;
	echo "Clearing DS SOLR indexes: ".$dataSourceKey;
	$result = curl_post($solr_update_url.'?commit=true', '<delete><query>data_source_key:("'.esc($dataSourceKey).'")</query></delete>');	
	$result .= curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	return $result;	
	print $result;
}

function indexKey($key, $status = 'All'){
	global $solr_update_url;
	echo "Indexing RegistryObject: ".$key;
	$result = '';
	if($status == 'PUBLISHED' || $status == 'All')
	{
		$result .= addPublishedToSolrIndex($key, true);
	}
	if($status == 'DRAFT' || $status == 'All')
	{
		$result .= addDraftToSolrIndex($key, true);
	}
	print $result;
	$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	print $result;
	$result = curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	print $result;
}

function clearKey($key, $status = 'All'){
	global $solr_update_url;
	echo "Deleting Solr Index for: ".$key;
	$result = '';
	if($status == 'PUBLISHED' || $status == 'All')
	{
		if($registryObject = getRegistryObject($key, true))
		{
			$result = deleteSolrHashKey($registryObject[0]['key_hash']);
		}

	}	
	if($status == 'DRAFT' || $status == 'All')
	{
		if($allKeys = getDraftRegistryObject($key , null))
		{
			$arraySize = sizeof($allKeys);
			for($i = 0; $i < $arraySize ; $i++)
			{				
				$key = $allKeys[$i]['draft_key'];
				$dataSourceKey = $allKeys[$i]['registry_object_data_source'];	
				$hash = sha1($key.$dataSourceKey);
				$result .= deleteSolrHashKey($hash);
			}
			
		}
	}
	print $result;
	$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	print $result;
	$result = curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	print $result;
}



function clearAllSolrIndex()
{
	global $solr_update_url;
	$result = curl_post($solr_update_url.'?commit=true', '<delete><query>*:*</query></delete>');	
	$result .= curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	return $result;	
}


function addPublishedSolrIndexForDatasource($dataSourceKey)
{
	global $solr_update_url;
	global $totalCount;
	$rifcsContent = '';
	$allKeys = getRegistryObjectKeysForDataSource($dataSourceKey);

	if($allKeys)
	{
		$arraySize = sizeof($allKeys);
		echo "<hr/><h3>Reindexing Published ".$dataSourceKey.": Total: ".$arraySize."</h3>";
		$totalCount += $arraySize;
		bench(1);
		
		ob_flush();flush();
		$chunkSize = 49;
		$result = '';
		if($arraySize <= $chunkSize){
			$numDots = 100;
		}else{
			$numDots = ceil(100/ceil($arraySize/$chunkSize));
		}
		// all published Records
		for($i = 0; $i < $arraySize ; $i++)
		{				
			
			$key = $allKeys[$i]['registry_object_key'];		
			$rifcsContent .= getRegistryObjectXMLforSOLR($key, true);	
			//$totalCount++;	
			if(($i % $chunkSize == 0 && $i != 0) || $i == ($arraySize -1))
			{					
					$rifcs = wrapRegistryObjects($rifcsContent);
					$solrrifcs = transformToSolr($rifcs);

					if (strlen($solrrifcs) == 0)
					{
						echo $rifcs;
					}				
					else
					{			
						//echo $rifcs;		
						$result = curl_post($solr_update_url, $solrrifcs);
						$percent = round((($i+1)*100)/$arraySize, 2);
						//echo ($percent).'% completed.<br/>';
						echo $result.'published ';

						ob_flush();flush();
						$rifcsContent = '';
						for ($j=0;$j<$numDots;$j++) {
							echo '.';ob_flush();flush();
						}
					}
					
					
			}
		}
		$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
		print ('<br/>commit: '.$result."<br/>");
		echo "Took " . bench(1) ."s to reindex...";
	}
}


function addDraftSolrIndexForDatasource($dataSourceKey)
{
	global $solr_update_url;
	global $totalCount;
	$rifcsContent = '';
	$allKeys = getDraftRegistryObject(null , $dataSourceKey);
	//var_dump($allKeys);
	ob_flush();flush();
	$chunkSize = 49;
	$result = '';
	if($allKeys)
	{
	bench(2);
	$arraySize = sizeof($allKeys);	
	echo "<hr/><h3>Reindexing Drafts ".$dataSourceKey.": Total: ".$arraySize."</h3>";
	$totalCount = $totalCount + $arraySize;
	//echo 'up to :'.$totalCount.'<br/>';
		if($arraySize <= $chunkSize){
			$numDots = 100;
		}else{
			$numDots = ceil(100/ceil($arraySize/$chunkSize));
		}
		//$pCompleted = 100/ceil($arraySize/$chunkSize);
		//echo "pC:".$pCompleted;
	for($i = 0; $i < $arraySize ; $i++)
	{				
		$key = $allKeys[$i]['draft_key'];
		//$totalCount++;
		//echo $key;
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
			else 
			{
				$registryDateModified = strtotime($registryDateModified); // parse the SQL timestamp
			}
			// SOLR requires the date in ISO8601, restricted to zulu time (why, I don't know...)
			$xml .= "      <extRif:registryDateModified>".gmdate('Y-m-d\TH:i:s\Z',$registryDateModified)."</extRif:registryDateModified>\n";
			

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
			$xml .= '      <extRif:lastModifiedBy>'.$allKeys[$i]['draft_owner'].'</extRif:lastModifiedBy>'."\n";
			$xml .= "    </extRif:extendedMetadata>\n";

		
		$rifcsContent .= unwrapRegistryObject($allKeys[$i]['rifcs']);	
		$rifcsContent .= $xml;
		//print $rifcsContent."\n";
		if(($i % $chunkSize == 0 && $i != 0) || $i == ($arraySize -1))
		{					
				$rifcs = wrapRegistryObjects($rifcsContent);
				$solrrifcs = transformToSolr($rifcs);
				//echo $solrrifcs."\n";
				if (strlen($solrrifcs) == 0)
				{
					echo "<font color='red'>".$rifcs."</font>";
				}				
				else
				{					
					$result = curl_post($solr_update_url, $solrrifcs);
					//echo $result;
					
					$percent = round((($i+1)*100)/$arraySize, 0);
					//echo $result.'<br/>';
					ob_flush();flush();
					$rifcsContent = '';
					 
					for ($j=0;$j<$numDots;$j++) {
						echo '.';
						ob_flush();flush();
					}
					
				}
				
				
		}
	}
	$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	print ('<br/>commit: '.$result."<br/>");
	echo "<hr/>Took " . bench(2) ."s to reindex...";
	}
}

function checkQuality($key,$dataSourceKey)
{
	if($dataSourceKey && $key)
	{
		$message = runQualityLevelCheckForRegistryObject($key,$dataSourceKey);
	}
	elseif($dataSourceKey)
	{
		$message = runQualityLevelCheckforDataSource($dataSourceKey);
	}
	else 
	{
		$dataSources = getDataSources(null, null);
		$arraySize = sizeof($dataSources);
		bench(0);
		for($i= 0 ; $i < $arraySize ; $i++)
		{
			$message = runQualityLevelCheckforDataSource($dataSources[$i]['data_source_key']);
			print $message;
			ob_flush();flush();
		}
		$message = runQualityLevelCheckforDataSource('PUBLISH_MY_DATA');
		print $message;
	}
	print $message;	
	
}




require '../../_includes/finish.php';
?>