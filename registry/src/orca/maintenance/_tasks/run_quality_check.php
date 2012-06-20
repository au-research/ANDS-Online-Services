<?php

function task_run_quality_check($task)
{
	$taskId = $task['task_id'];
	$message = '';
	$dataSourceKey = $task['data_source_key'];
	$registryObjectKeys = $task['registry_object_keys'];


	// Specific registry object
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
	// All records in a single data source
	else if($dataSourceKey != '')
	{
		$message .= runQualityLevelCheckforDataSource($dataSourceKey);
	}
	// All records in the registry...
	else
	{
		$ds = getDataSources(null, null); //add publish my data
		$ds[] = array('data_source_key'=>'PUBLISH_MY_DATA');
		foreach($ds AS $datasource)
		{
			$message .= runQualityLevelCheckforDataSource($datasource['data_source_key']);
		}
	}


	$message .= "\ncompleted!";
	return $message;
}
