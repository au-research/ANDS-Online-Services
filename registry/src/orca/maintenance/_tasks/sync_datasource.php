<?php
function task_sync_datasource($task)
{
	$taskId = $task['task_id'];
	$dataSourceKey = $task['data_source_key'];
	$message = '';

	if ($dataSourceKey)
	{
		addNewTask('RUN_QUALITY_CHECK', "Requeued task from task run: " . $taskId, '', $task['data_source_key'], null);
		addNewTask('GENERATE_CACHE', "Requeued task from task run: " . $taskId, '', $task['data_source_key'], null);
		addNewTask('INDEX_RECORDS', "Requeued task from task run: " . $taskId, '', $task['data_source_key'], null);
	}
	else
	{
		// Sync all data sources
		addNewTask('RUN_QUALITY_CHECK', "Requeued task from task run: " . $taskId, '', '', null);
		addNewTask('GENERATE_CACHE', "Requeued task from task run: " . $taskId, '', '', null);
		addNewTask('INDEX_RECORDS', "Requeued task from task run: " . $taskId, '', '', null);
	}

    return $message;
}