<?php
$start_execution = microtime(true);
set_time_limit(0);
// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 1000*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");
ini_set("memory_limit","768M");
ini_set('display_errors',0);
//error_reporting(E_ALL|E_STRICT);

$cosi_root = "/var/www/htdocs/workareas/leo/ands/registry/src/";
set_include_path(get_include_path() . PATH_SEPARATOR . $cosi_root);
$eDebugOnStatus = false;
include 'global_config.php';
include 'orca/_functions/orca_data_functions.php';
include '_includes/_functions/database_functions.php';
include '_includes/_functions/data_functions.php';
include '_includes/_environment/database_env.php';

//chdir($deployementDir."orca/admin");
// Connect to the database.
// ----------------------------------------------------------------------------------------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);

// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 0;
ini_set("max_execution_time", "$executionTimeoutSeconds");

$nextTask = getNextTask('WAITING');
if($nextTask)
{
	$method = $nextTask[0]['method'];	
	switch ($method){
		case 'RUN_QUALITY_CHECK':
			echo $method."\n";
			include('_tasks/run_quality_test.php');
			break;
		case 'GENERATE_HASH':
			echo $method."\n";
			include('_tasks/generate_hashes.php');
			break;
		case 'INDEX_RECORDS':
			echo $method."\n";
			include('_tasks/index_records.php');
			break;
		case 'GENERATE_CACHE':
			echo $method."\n";
			include('_tasks/generate_cache.php');
			break;
		case 'RUN_HARVEST':
			echo $method."\n";
			break;
	}
}



echo "\naddNewTask('RUN_QUALITY_CHECK', '', '', 'anu.edu.au')";
$result = addNewTask('RUN_QUALITY_CHECK', '', '', 'anu.edu.au');
var_dump($result);

echo "\ngetNextTask('WAITING')";
$result = getNextTask('WAITING');
var_dump($result);

echo "\naddNewTask('RUN_QUALITY_CHECK', '', '', 'anu.edu.au', '1')";
$result = addNewTask('RUN_QUALITY_CHECK', '', '', 'anu.edu.au', '1');
var_dump($result);

echo "\ngetNextTask(null, '20')";
$result = getNextTask(null, '20');
var_dump($result);

echo "\ngetTask('3')";
$result = getTask('3', null);
var_dump($result);

echo "\ngetNextTask('WAITING', '1')";
$result = getNextTask('WAITING', '1');
var_dump($result);


echo "\ngetTask(null,'WAITING')";
$result = getTask(null,'WAITING');
var_dump($result);

require '_includes/finish.php';

?>