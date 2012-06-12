<?php

$taskId = $nextTask[0]['task_id'];
setTaskStarted($taskId);
$message = '';
$dataSourceKey = $nextTask[0]['data_source_key'];
$registryObjectKeys = $nextTask[0]['registry_object_keys'];

   
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
	$message .= runQualityCheckforDataSource($dataSourceKey);
}


$message .= "\ncompleted!";
setTaskCompleted($taskId, $message);

