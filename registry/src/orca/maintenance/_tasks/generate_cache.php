<?php
function task_generate_cache($task)
{
	$taskId = $task['task_id'];
	$dataSourceKey = $task['data_source_key'];
	$registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey);
	$message = "Caching of " . count($registryObjectKeys) . " records started for " . $dataSourceKey . ": ";
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