<?php
/*
Copyright 2012 The Australian National University
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

// SOLR operations on the ORCA database.

function solr($solr_url, $fields, $extras=""){
	//prep
	$fields_string='';
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	rtrim($fields_string,'&');
	if($extras!="") $fields_string .= $extras;

	$ch = curl_init();

	//echo $fields_string;

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
	curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	$content = curl_exec($ch);//execute the curl
	return $content;
}

function addDraftToSolrIndex($registryObjectKey, $data_source_key, $commit=true)
{
	$allKeys = getDraftRegistryObject($registryObjectKey , $data_source_key);
	$arraySize = sizeof($allKeys);
	$result = '';
	$rifcs='';
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
			$rifcsContent = unwrapRegistryObject($allKeys[$i]['rifcs']);
			$rifcsContent .= $xml;
			$rifcs .=$rifcsContent;
		}
		$rifcs = wrapRegistryObjects($rifcs);
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


function addSetofDraftsToSolrIndex($registryObjectKeys, $data_source_key, $commit=true)
{

	$keySize = sizeof($registryObjectKeys);
	$result = '';
	$rifcs='';
	foreach($registryObjectKeys AS $registryObjectKey)
	{
		$allKeys = getDraftRegistryObject($registryObjectKey , $data_source_key);
		$arraySize = sizeof($allKeys);

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
				$rifcsContent = unwrapRegistryObject($allKeys[$i]['rifcs']);
				$rifcsContent .= $xml;
				$rifcs .=$rifcsContent;
			}
		}
	}
	$rifcs = wrapRegistryObjects($rifcs);
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
	return sizeof($keySize).$solrrifcs.$result;
}


function addPublishedToSolrIndex($registryObjectKey, $commit=true)
{
	//global $solr_update_url;
	$rifcsContent = getRegistryObjectXMLforSOLR($registryObjectKey,true);
	$rifcsContent = wrapRegistryObjects($rifcsContent);
	$rifcs = transformToSolr($rifcsContent);
	$result = curl_post(gSOLR_UPDATE_URL, $rifcs);
	if($commit) $result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	return $result;
}

function deleteSolrDraft($draft_key, $data_source_key){
	$hash = sha1($draft_key.$data_source_key);
	return deleteSolrHashKey($hash);
}


function deleteSetofSolrDrafts($draft_keys, $data_source_key){
	$hash = '';
	foreach($draft_keys AS $key)
	{
		$hash .= "<id>".sha1($key.$data_source_key)."</id>";
	}
	return deleteSolrHashKeys($hash);
}


function deleteSolrHashKey($hashkey)
{
	return curl_post(gSOLR_UPDATE_URL.'?commit=true', '<delete><id>'.$hashkey.'</id></delete>');
}

function deleteSolrHashKeys($hashkeys)
{
	return curl_post(gSOLR_UPDATE_URL.'?commit=true', '<delete>'.$hashkeys.'</delete>');
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

function syncDraftKey($draft_key, $data_source_key){
	deleteSolrDraft($draft_key, $data_source_key);
	runQualityLevelCheckForDraftRegistryObject($draft_key, $data_source_key);
	addDraftToSolrIndex($draft_key, $data_source_key);
}

function syncDraftKeys($draft_keys, $data_source_key){
	deleteSetofSolrDrafts($draft_keys, $data_source_key); //done
	foreach($draft_keys AS $key)
	{
		runQualityLevelCheckForDraftRegistryObject($key, $data_source_key);
	}
	addSetofDraftsToSolrIndex($draft_keys, $data_source_key); //done
}


function syncKey($key, $data_source_key){
	deleteSolrHashKey(sha1($key));

	//qa
	runQualityLevelCheckForRegistryObject($key, $data_source_key);

	//cache
	$extendedRIFCS = generateExtendedRIFCS($key);
	writeCache($data_source_key,$key, $extendedRIFCS);

	//index
	addPublishedToSolrIndex($key);
}

function queueSyncDataSource($data_source_key){
	$result = addNewTask('RUN_QUALITY_CHECK', '', '', $data_source_key);
	$result = addNewTask('GENERATE_CACHE', '', '', $data_source_key,$result);
	$result = addNewTask('INDEX_RECORDS', '', '', $data_source_key,$result);
	triggerAsyncTasks();
}

?>