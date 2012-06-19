<?php
function task_hourly_registry_maintenance($task)
{
	$taskId = $task['task_id'];

	// XXX: Check Harvester

	// XXX: Check PIDs

	// XXX: Check DOIs

	// XXX: Check system resources

	// XXX: Update stats

	// Clear completed tasks
	deleteCompletedTasksBefore("1 day");

	addNewTask($task['method'], "Requeued task from task run: " . $taskId, '', '', null, "1 hour");
	return $message;
}