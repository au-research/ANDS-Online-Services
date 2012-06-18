<?php

function task_run_quality_check($task)
{
	$taskId = $task['task_id'];
	$message = '';
	$dataSourceKey = $task['data_source_key'];
	$registryObjectKeys = $task['registry_object_keys'];


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
		$message .= runQualityLevelCheckforDataSource($dataSourceKey);
	}


	$message .= "\ncompleted!";
	return $message;
}
