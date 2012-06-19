<?php
function task_do_something_hourly($task)
{
	$taskId = $task['task_id'];
	$dataSourceKey = $task['data_source_key'];
	$message = "DID SOMETHING HOURLY AT: " . date ('H:i:s');

	addNewTask($task['method'], "Requeued task from task run: " . $taskId, $task['registry_object_keys'], $task['data_source_key'], null, "1 hour");
	return $message;
}