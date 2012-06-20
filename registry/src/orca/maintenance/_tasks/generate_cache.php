<?php
function task_generate_cache($task)
{
	$taskId = $task['task_id'];
	$dataSourceKey = $task['data_source_key'];
	if (!$dataSourceKey) {
		$registryObjectKeys = array();
		$dataSources = getDataSources(null,null);
		foreach($dataSources AS $dataSource)
		{
			$ds_keys = getRegistryObjectKeysForDataSource($dataSource['data_source_key']);
			if (is_array($ds_keys)) {
				array_merge($registryObjectKeys, $ds_keys);
			}
		}
	}
	else
	{
		$registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey);
	}
	$message = "Caching of " . count($registryObjectKeys) . " records started for " . ($dataSourceKey ? $dataSourceKey : "all data sources") . ": ";
	if($registryObjectKeys)
	{

		foreach ($registryObjectKeys AS $registry_object)
		{
			$extendedRIFCS = generateExtendedRIFCS($registry_object['registry_object_key']);
			writeCache($dataSourceKey, $registry_object['registry_object_key'], $extendedRIFCS);
		}
	}
	$message .= "\ncompleted!";
    return $message;
}