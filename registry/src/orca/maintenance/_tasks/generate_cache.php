<?php
function task_generate_cache($task)
{
	$taskId = $task['task_id'];
	$dataSourceKey = $task['data_source_key'];
	$message = '';

	// No specific data source, cache the whole registry!
	if (!$dataSourceKey) {
		$registryObjectKeys = array();
		$ds = getDataSources(null, null);
		$ds[] = array('data_source_key'=>'PUBLISH_MY_DATA');
		foreach($ds AS $datasource)
		{

			$ro = getRegistryObjectKeysForDataSource($datasource['data_source_key']);
			$message .= "Caching of " . count($ro) . " records started for " . ($datasource['data_source_key']) . ": \n";
			if($ro)
			{
				foreach ($ro AS $registry_object)
				{
					$extendedRIFCS = generateExtendedRIFCS($registry_object['registry_object_key']);
					writeCache($datasource['data_source_key'], $registry_object['registry_object_key'], $extendedRIFCS);
				}
			}
			$message .= "completed!\n";
		}
	}
	else
	{
		$registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey);
		$message = "Caching of " . count($registryObjectKeys) . " records started for " . ($dataSourceKey) . ": \n";
		if($registryObjectKeys)
		{
			foreach ($registryObjectKeys AS $registry_object)
			{
				$extendedRIFCS = generateExtendedRIFCS($registry_object['registry_object_key']);
				writeCache($dataSourceKey, $registry_object['registry_object_key'], $extendedRIFCS);
			}
		}
		$message .= "\ncompleted!";
	}
    return $message;
}