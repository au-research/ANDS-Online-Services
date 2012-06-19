#!/usr/bin/php
<?php
define('DEBUG', true);

echo "[SYSTEM] Starting\n";
// Initialise our worker with this environment's variables
require('init.php');

echo "[SYSTEM] Connecting to database" . NL;
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);

echo "[SYSTEM] Starting\n";
echo "[SYSTEM] Running any backlogged tasks..." . NL;
executeNextPendingTask();
echo "[SYSTEM] Normal operating mode initialised" . NL;

// Create our worker object.
$gmworker= new GearmanWorker();
$gmworker->addServer();
$gmworker->addFunction("executeNextPendingTask", "executeNextPendingTask");

while($gmworker->work())
{
	if ($gmworker->returnCode() != GEARMAN_SUCCESS)
	{
		echo "Gearman return_code: " . $gmworker->returnCode() . "\n";
		break;
	}
}

function executeNextPendingTask($job=null)
{
	global $gCNN_DBS_ORCA;

	if ($job)
	{
		echo "[SYSTEM] Received job: " . $job->handle() . "\n";
	}

	// Check database connection status
	// pg_connection_status doesn't seem to work as expected
	// (I suspect its status is only updated when a query is called)
	// while(pg_connection_status($gCNN_DBS_ORCA) === PGSQL_CONNECTION_BAD)
	while (@pg_query($gCNN_DBS_ORCA, "SELECT 'test';")===FALSE)
	{
		if (pg_connection_reset($gCNN_DBS_ORCA)) {
			echo "[SYSTEM] Reset connection to database...success!" . NL;
		} else {
			echo "[SYSTEM] Reset connection to database...FAILED" . NL;
			echo "[SYSTEM] Waiting 30 seconds to try again..." . NL;
			sleep(30);
		}
	}

	$nextTask = getNextWaitingTask();
	if (!$nextTask)
	{
		echo "[SYSTEM] No outstanding tasks to run. Nothing to do for this job." . NL;
		return true;
	}
	while ($nextTask = getNextWaitingTask())
	{
		$task = $nextTask[0];
		if(DEBUG){
			echo "[SYSTEM]    Running TaskID #" . $task['task_id'] . " (" . $task['method'] . ")" . NL;
			if ($task['data_source_key'])
			{
				echo "[SYSTEM]    Data Source: " . $task['data_source_key'] . NL;
			}
			if ($task['registry_object_keys'])
			{
				echo "[SYSTEM]    RegObj. Key(s): " . $task['registry_object_keys'] . NL;
			}
		}

		$method = strtolower($task['method']);
		setTaskStarted($task['task_id']);
		try
		{
			// Is our function already declared??
			if (!function_exists('task_' . $method))
			{
				echo "[SYSTEM] Loading Module: " . $method . NL;
				$module_file = 'orca/maintenance/_tasks/' . $method . '.php';
				if ($module_file)
				{
					include_once($module_file);
				}
				else
				{
					echo "[SYSTEM] No compatible module found: 'maintenance/_tasks/" . $method . ".php'" . NL;
				}
			}

			if (function_exists('task_' . $method))
			{
				// Dynamic languages rock, don't they??
				bench(2);
				ob_start();

				$method = 'task_' . $method;
				$return = $method($task);

				// Use OB buffer so that we output a TASKID next to the output
				$output = ob_get_contents(); ob_end_clean();

				if ($output)
				{
					$output = "[#".$task['task_id']."] " . preg_replace("/\n/", "\n" . "[#".$task['task_id']."] ", $output);
					echo $output . NL;
				}
				if ($return)
				{
					echo "[#".$task['task_id']."] " . preg_replace("/\n/", "\n" . "[#".$task['task_id']."] ", $return) . NL;
				}
				echo "[#".$task['task_id']."] Task terminated successfully after " . bench(2) . "seconds" . NL;

				setTaskCompleted($task['task_id'], $return);
			}
			else
			{
				throw new Exception('Function not defined/could not be found: ' . 'task_' . $method);
			}

		}
		catch (Exception $e)
		{
			echo "[SYSTEM] Task failed/exception thrown" . NL;
			echo $e->getMessage()  . NL;
			setTaskFailed($task['task_id'], $e->getMessage());
			//return false; dont return here, else other waiting tasks don't get executed!!
		}
		flush();
	}

	# Return what we want to send back to the client.
	return true;
}