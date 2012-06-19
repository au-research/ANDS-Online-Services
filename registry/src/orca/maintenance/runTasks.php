<?php
$start_execution = microtime(true);
require '../../_includes/init.php';
require '../orca_init.php';

//////////
die ('This task is deprecated - use the task manager instead');
//////////


// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 0;
ini_set("max_execution_time", "$executionTimeoutSeconds");


$task = getQueryValue('task');

switch ($task)
{
	case "generate_cache":
		include "_tasks/generate_cache.php";
	break;
	case "check_slugs":
		include "_tasks/check_slugs.php";
		break;
	default:
		echo "Error: No task specified";
}




//header("Content-Type: text/xml; charset=UTF-8", true);

//deleteCacheItem("HeatherCSIRO", "ANZCW0306003143");
//writeCache("HeatherCSIRO", "ANZCW0306003143", generateExtendedRIFCS("ANZCW0306003143"));
//echo getCacheItems("HeatherCSIRO", "", eCACHE_CURRENT_NAME, true);
//echo microtime(true) - $start_execution;

/*
$ds = getDataSources(null, null); //add publish my data
foreach($ds AS $datasource)
{
	if ($datasource['data_source_key'] == 'ansto.gov.au')
	{
		$ro = getRegistryObjectKeysForDataSource($datasource['data_source_key']);

		foreach ($ro AS $registry_object)
		{

			writeCache($datasource['data_source_key'], $registry_object['registry_object_key'], generateExtendedRIFCS($registry_object['registry_object_key']));
		}

		echo "<br/>Cache update for ".$datasource['data_source_key']." complete!<br/>";
	}
}
*/

/*
// Insert for testing
insertTaskRequest("updateSolrIndexForDatabase", "SYSTEM", "","","",time()-10);


// Get all queued tasks with a trigger time greater than our current
$taskRequests = getTaskRequests(time());
$tasksLaunchCount = 0;

if ($taskRequests)
{
	// There are tasks to process!
	foreach ($taskRequests AS $task)
	{
		if (function_exists($task['task_type']))
		{
			$start_time = time();
			doLog("Beginning task run for ". $task['task_type']);
			updateTaskRequest($task['task_id'], $start_time, 0);
			$task['task_type']($task['param_1'],$task['param_2'],$task['param_3']);
			$end_time = time();
			doLog("Completed task run for ". $task['task_type'] . " (took " . ($end_time - $start_time) . "s)");
			updateTaskRequest($task['task_id'], $start_time, $end_time);
		}
		else
		{
			doLog("No such function exists to execute task type: '".$task['task_type']."'");
		}
	}
}
else
{
	doLog("No tasks queued to run!");
	$end_execution = microtime(true);
}

doLog("Took " . (microtime(true) - $start_execution) . "s to run...");


function updateSolrIndexForDatabase($param_1, $param_2, $param_3)
{
	$repeat_interval = 60*60*24;
	$datasources = getDataSources(null);
	if (!$datasources)
	{
		doLog("No datasources to index.");
		return;
	}

	foreach ($datasources AS $ds)
	{
		$output = runSolrIndexForDatasource($ds['data_source_key']);
		doLog($output);
	}
	// Schedule a recurring task
	insertTaskRequest("updateSolrIndexForDatabase", "SYSTEM", "", "", "", time() + $repeat_interval);
}


function updateQualityResultForDataSource($datasourcekey, $param_2, $param_3)
{
	$repeat_interval = 60*60*24;
	$allKeys = getAllRegistryObjectKey();
	$arraySize = sizeof($allKeys);
	$chunkSize = 666;
	$j = 1;
	$result = 'test';
	for($i = 0; $i < $arraySize ; $i++)
	{
		$key = $allKeys[$i]['registry_object_key'];
		$rifcsContent .= getRegistryObjectXMLforSOLR($key, true);

		if($i == ($chunkSize * $j) || $i == ($arraySize -1))
		{
				$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
				$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
				$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF2_SCHEMA_URI.'">'."\n";
				$rifcs .= $rifcsContent;
				$rifcs .= "</registryObjects>\n";
				$rifcs = transformToSolr($rifcs);
				$result = curl_post($solrUrl, $rifcs);
				doLog($j.': ('.$i.')registryObjects is sent to solr ' . $result . "<br/>");
				ob_flush();
				flush();
				$result = curl_post($solrUrl.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
				doLog($result);

				$rifcsContent = '';
				$j++;
		}
	}
	$result = curl_post($solrUrl.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	doLog('optimise: '.$result. "<br/>");

	// Schedule a recurring task
	insertTaskRequest("updateQualityResultForDataSource", "SYSTEM", "", "", "", time() + $repeat_interval);
}

function sendSystemStatusNotification($param_1, $param_2, $param_3)
{
	if (!$param_1)
	{
		doLog("No recipient specified");
		return;
	}
	doLog("Generating System Statistics for $param_1");
	//$repeat_interval = 60*60*24;
	$repeat_interval = 5;

	// Some logic
	$output = '';
	$hostname = trim(shell_exec("hostname"));
	$diskstats = trim(shell_exec("df -h | grep -v /boot | grep -v tmpfs | tr -s ' ' | cut -s -d' ' -f 2-5"));
	$memory = trim(shell_exec("free -m |  grep -v cache:"));
	$uptime = trim(shell_exec("uptime"));

	$httpd_cpu_raw = trim(shell_exec("ps waux | grep httpd | grep -v grep |  tr -s ' ' | cut -d' ' -f 3"));
	$httpd_cpu = sumPercentages($httpd_cpu_raw);
	$httpd_mem_raw = trim(shell_exec("ps waux | grep httpd | grep -v grep | tr -s ' ' | cut -d' ' -f 4"));
	$httpd_mem = sumPercentages($httpd_mem_raw);
	$httpd_threads = trim(shell_exec("ps waux | grep httpd | grep -v grep | wc -l"));
	if ($httpd_threads == 0) { $httpd_threads = "ERROR: NO PROCESS FOUND - NOT RUNNING?"; }

	$postgres_cpu_raw = trim(shell_exec("ps waux | grep postgres | grep -v grep | tr -s ' ' | cut -d' ' -f 3"));
	$postgres_cpu = sumPercentages($postgres_cpu_raw);
	$postgres_mem_raw = trim(shell_exec("ps waux | grep postgres | grep -v grep | tr -s ' ' | cut -d' ' -f 4"));
	$postgres_mem = sumPercentages($postgres_mem_raw);
	$postgres_threads = trim(shell_exec("ps waux | grep postgres | grep -v grep | wc -l"));
	if ($postgres_threads == 0) { $postgres_threads = "ERROR: NO PROCESS FOUND - NOT RUNNING?"; }

	$tomcat_cpu_raw = trim(shell_exec("ps waux | grep tomcat | grep -v grep | tr -s ' ' | cut -d' ' -f 3"));
	$tomcat_cpu = sumPercentages($tomcat_cpu_raw);
	$tomcat_mem_raw = trim(shell_exec("ps waux | grep tomcat | grep -v grep | tr -s ' ' | cut -d' ' -f 4"));
	$tomcat_mem = sumPercentages($tomcat_mem_raw);
	$tomcat_threads = trim(shell_exec("ps waux | grep tomcat | grep -v grep | wc -l"));
	if ($tomcat_threads == 0) { $tomcat_threads = "ERROR: NO PROCESS FOUND - NOT RUNNING?"; }


$output = <<<END
	<html><body><pre style="font-size:12px;">
----------
- Server Statistics ($hostname)
----------
$uptime

$memory

$diskstats

Web Server:
	Threads - $httpd_threads
	CPU - {$httpd_cpu} %
	Mem - {$httpd_mem} %

Database:
	Threads - {$postgres_threads}
	CPU - {$postgres_cpu} %
	Mem - {$postgres_mem} %

Tomcat:
	Threads - {$tomcat_threads}
	CPU - {$tomcat_cpu} %
	Mem - {$tomcat_mem} %
</pre></body></html>
END;

	// Deliver the output to our recipients!
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	$headers .= "From: \"ANDS Services <services@ands.org.au>\"";
	send_email($param_1, "System Report ($hostname)", $output, $headers);

	// Schedule a recurring task
	insertTaskRequest("sendSystemStatusNotification", "SYSTEM", $param_1, "", "", time() + $repeat_interval);
}

// redirect to appropriate log file
function doLog($str)
{
	echo $str . "<Br/>";
}

// Helper functions
function sumPercentages($raw_list)
{
	$perc = 0;
	foreach (explode("\n", $raw_list) AS $val)
	{
		$perc += (float) $val;
	}
	return $perc;
}

*/