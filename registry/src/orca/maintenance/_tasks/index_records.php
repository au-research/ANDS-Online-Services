<?php
global $solr_url;
$solr_update_url = $solr_url.'update';
$totalCount = 0;
$chunkSize = 49;
function task_index_records($task)
{
	global $solr_url;
	$taskId = $task['task_id'];
	$message = '';
	$dataSourceKey = $task['data_source_key'];
	$registryObjectKeys = $task['registry_object_keys'];
	$totalCount = 0;
	$chunkSize = 49;
	$solr_update_url = $solr_url.'update';
	if($dataSourceKey != '' && $registryObjectKeys != '')
	{
		$registryObjectKeysArray = processList($registryObjectKeys);
		if($registryObjectKeysArray)
		{
			for( $i=0; $i < count($registryObjectKeysArray); $i++ )
			{
				$message .= runQualityLevelCheckForRegistryObject($registryObjectKeysArray[i], $dataSourceKey)."\n";
				$message .= runQualityLevelCheckForDraftRegistryObject($registryObjectKeysArray[i], $dataSourceKey)."\n";
			}
		}
	}
	else if($dataSourceKey != '')
	{

		$result =  clearDS($dataSourceKey);
		$message .= "clearing Datasource Index".$result."\n";
		$message .= addPublishedSolrIndexForDatasource($dataSourceKey);
		$message .= addDraftSolrIndexForDatasource($dataSourceKey);
	}


	$message .= "\ncompleted! update to ".$solr_update_url;
	return $message;
}


function clearDS($dataSourceKey){
	global $solr_update_url;
	echo "Clearing DS SOLR indexes: ".$dataSourceKey."  ".$solr_update_url;
	$result = curl_post($solr_update_url.'?commit=true', '<delete><query>data_source_key:("'.esc($dataSourceKey).'")</query></delete>');
	$result .= curl_post($solr_update_url.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	return $result;
}


function addPublishedToSolrIndex($registryObjectKey, $commit=true)
{
		global $solr_update_url;
		$rifcsContent = getRegistryObjectXMLforSOLR($registryObjectKey,true);
		$rifcsContent = wrapRegistryObjects($rifcsContent);
		$rifcs = transformToSolr($rifcsContent);
		$result = curl_post($solr_update_url, $rifcs);
		return $result;
}

function addDraftToSolrIndex($registryObjectKey, $commit=true)
{
	global $solr_update_url;
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
			$result = curl_post($solr_update_url, $solrrifcs);
		}
	}
	return $result;
}

function addPublishedSolrIndexForDatasource($dataSourceKey)
{
	global $solr_update_url;
	global $totalCount;
	global $chunkSize;
	$rifcsContent = '';
	$allKeys = getRegistryObjectKeysForDataSource($dataSourceKey);
    $message = '';
	if($allKeys)
	{
		$arraySize = sizeof($allKeys);
		$message .= "Reindexing Published ".$dataSourceKey.": Total: ".$arraySize;
		$totalCount += $arraySize;
		$result = '';
		for($i = 0; $i < $arraySize ; $i++)
		{
			$key = $allKeys[$i]['registry_object_key'];
			$rifcsContent .= getRegistryObjectXMLforSOLR($key, true);
			if(($i % $chunkSize == 0 && $i != 0) || $i == ($arraySize -1))
			{
					$rifcs = wrapRegistryObjects($rifcsContent);
					$solrrifcs = transformToSolr($rifcs);
					if (strlen($solrrifcs) != 0)
					{
						$result = curl_post($solr_update_url, $solrrifcs);
						$rifcsContent = '';
					}
			}
		}
		$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	}
	return $message;
}


function addDraftSolrIndexForDatasource($dataSourceKey)
{
	global $solr_update_url;
	global $totalCount;
	global $chunkSize;
	$message = '';
	$rifcsContent = '';
	$allKeys = getDraftRegistryObject(null , $dataSourceKey);
	if($allKeys)
	{
	$arraySize = sizeof($allKeys);
	$message  ="Reindexing Drafts ".$dataSourceKey.": Total: ".$arraySize;
	$totalCount = $totalCount + $arraySize;

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
					$rifcsContent = '';

				}
		}
	}
			$result = curl_post($solr_update_url.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	}
	return $message;
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