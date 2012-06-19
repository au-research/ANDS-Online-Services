<?php
/*
 Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
if (!IN_ORCA) die('No direct access to this file is permitted.');

$task_subset = getQueryValue('subset');
$page = getPostedValue('page');
if (!$page) $page = 1;
$rows_per_page = getPostedValue('rp');
if (!$rows_per_page) $rows_per_page  = 15;

$total = 0;
$output = array();
if ($task_subset == "completed")
{
	$tasks = getTask(null, "COMPLETED");
	if (!$tasks) $tasks = array();
	$total = count($tasks);
	$tasks = array_slice($tasks,(($page-1)*$rows_per_page), $rows_per_page);
	$result = array();
	foreach($tasks as $task)
	{
		$result[] = array ( "id" => $task['task_id'],
							"cell" =>
								array(
										"task_id" => $task['task_id'],
										"prereq_task" => $task['prerequisite_task'],
										"method" => $task['method'],
										"executed" => time_elapsed_string(strtotime($task['completed']))." ago",
										//"started" => date('H:i:s', strtotime($task['started'])),
										//"completed" => date('H:i:s', strtotime($task['completed'])),
										"duration" => (strtotime($task['completed']) - strtotime($task['started']))." seconds",
										"status" => $task['status'],
										"data_source_key" => $task['data_source_key'],
										"reg_obj_keys" => $task['registry_object_keys'],
										"log_msg" => $task['log_msg']
								)
					);

	}
	$output = array("total"=>$total, "page"=>$page, "rows"=>$result);
}
else if ($task_subset == "pending")
{
	$tasks = getPendingTasks();
	if (!$tasks) $tasks = array();
	$total = count($tasks);
	$tasks = array_slice($tasks,(($page-1)*$rows_per_page), $rows_per_page);
	$result = array();
	foreach($tasks as $task)
	{
		$result[] = array ( "id" => $task['task_id'],
							"cell" =>
								array(
										"opts" => '<a href="javascript:deleteTask(\''.$task['task_id'].'\');" class="smallIcon icon100s left"><span></span></a><a href="javascript:rescheduleTask(\''.$task['task_id'].'\');" class="smallIcon iconRefreshs right"><span></span></a>',
										"task_id" => $task['task_id'],
										"prereq_task" => $task['prerequisite_task'],
										"method" => $task['method'],
										"added" => date('d M Y H:i:s', strtotime($task['added'])),
										"time_waiting" => time_elapsed_string(strtotime($task['added'])),
										"scheduled_for" => ($task['scheduled_for'] ? time_elapsed_string(strtotime($task['scheduled_for']) - time(), false) : ''),
										"status" => $task['status'],
										"data_source_key" => $task['data_source_key'],
										"reg_obj_keys" => $task['registry_object_keys'],
										"log_msg" => $task['log_msg']
								)
					);

	}
	$output = array("total"=>$total, "page"=>$page, "rows"=>$result);
}
else if ($task_subset == "failed")
{
	$tasks = getFailedTasks();
	if (!$tasks) $tasks = array();
	$total = count($tasks);
	$tasks = array_slice($tasks,(($page-1)*$rows_per_page), $rows_per_page);
	$result = array();
	foreach($tasks as $task)
	{
		$result[] = array ( "id" => $task['task_id'],
				"cell" =>
				array(
						"opts" => '<a href="javascript:deleteTask(\''.$task['task_id'].'\');" class="smallIcon icon100s left"><span></span></a><a href="javascript:rescheduleTask(\''.$task['task_id'].'\');" class="smallIcon iconRefreshs right"><span></span></a>',
						"task_id" => $task['task_id'],
						"prereq_task" => $task['prerequisite_task'],
						"method" => $task['method'],
						"added" => time_elapsed_string(strtotime($task['added']))." ago",
						"started" => date('H:i:s', strtotime($task['started'])),
						"completed" => date('H:i:s', strtotime($task['completed'])),
						"duration" => (strtotime($task['completed']) - strtotime($task['started']))." seconds",
						"status" => $task['status'],
						"data_source_key" => $task['data_source_key'],
						"reg_obj_keys" => $task['registry_object_keys'],
						"log_msg" => $task['log_msg']
				)
		);

	}
	$output = array("total"=>$total, "page"=>$page, "rows"=>$result);
}
else if ($task_subset == "taskAdd")
{
	$method = getQueryValue('method');
	$data_source_key = getQueryValue('data_source_key');
	$registry_object_keys = getQueryValue('registry_object_keys');
	$schedule_for = getQueryValue('schedule_for');

	if (!$method)
	{
		$output = array("fail"=>"fail");
	}
	else
	{
		var_dump(array($method, "Queued from task manager at " .  date('d M Y H:i:s'),
					rawurldecode($registry_object_keys), rawurldecode($data_source_key), null, ($schedule_for ? rawurldecode($schedule_for) : null)));
		var_dump(addNewTask($method, "Queued from task manager at " .  date('d M Y H:i:s'),
					rawurldecode($registry_object_keys), rawurldecode($data_source_key), null, ($schedule_for ? rawurldecode($schedule_for) : null)));
		$output = array("success"=>"success");
	}
}
else if ($task_subset == "flushCompleted")
{
	deleteCompletedTasksBefore("1 day");
	$output = array("success"=>"success");
}
else if ($task_subset == "flushFailed")
{
	deleteFailedTasks();
	$output = array("success"=>"success");
}
else if ($task_subset == "triggerWorker")
{
	triggerAsyncTasks();
	$output = array("success"=>"success");
}
else if ($task_subset == "deleteTask")
{

	$task_id = getQueryValue('task_id');
	if ($task_id)
	{
		deleteTask($task_id);
		triggerAsyncTasks();
	}
	$output = array("success"=>"success");
}
else if ($task_subset == "rescheduleTask")
{

	$task_id = getQueryValue('task_id');
	if ($task_id)
	{
		$t = array_pop(getTask($task_id, null));
		deleteTask($task_id);
		addNewTask($t['method'], "Requeued failed task: #" . $task_id, $t['registry_object_keys'], $t['data_source_key'], null, null);
		triggerAsyncTasks();
	}
	$output = array("success"=>"success");
}
else
{
	$output = array("fail"=>"fail");
}

print json_encode($output);
