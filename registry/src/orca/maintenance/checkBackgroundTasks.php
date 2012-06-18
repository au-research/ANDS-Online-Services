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
$runningInBackgroundTask = true;
$cosi_root = "/var/www/htdocs/workareas/leo/ands/registry/src/";
define('gRIF_SCHEMA_PATH', eAPPLICATION_ROOT.'/orca/schemata/registryObjects.xsd');
define('gRIF_SCHEMA_URI', 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');
define("eDCT_FORMAT_ISO8601_DATETIMESEC_UTC" , 'YYYY-MM-DDThh:mm:ssZ');
define("eIMAGE_ROOT", "");

include 'orca/_functions/orca_data_functions.php';
include '_includes/_functions/database_functions.php';
include '_includes/_functions/data_functions.php';
include '_includes/_functions/presentation_functions.php';
include '_includes/_environment/database_env.php';

//chdir($deployementDir."orca/admin");
// Connect to the database.
// ----------------------------------------------------------------------------------------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);

// Increase the execution timeout as we may have to deal with a large amount of data. date("Y:m:d H:i")
$executionTimeoutSeconds = 0;
ini_set("max_execution_time", "$executionTimeoutSeconds");

$nextTask = getNextWaitingTask();
if($nextTask)
{
	$method = $nextTask[0]['method'];
	echo "\n".$method." for  ".$nextTask[0]['data_source_key']." ".date("d-m-Y H:i")."\n";
	switch ($method){
		case 'RUN_QUALITY_CHECK':
			include('orca/_functions/orca_access_functions.php');
			include('orca/_functions/orca_import_functions.php');
			include('orca/_functions/orca_export_functions.php');
			include('orca/maintenance/_tasks/run_quality_test.php');
			break;
		case 'GENERATE_HASH':
			include('orca/maintenance/_tasks/generate_hashes.php');
			break;
		case 'INDEX_RECORDS':
			include('orca/_functions/orca_access_functions.php');
			include('orca/_functions/orca_data_source_functions.php');
			include('orca/_functions/orca_export_functions.php');
			include('orca/_functions/orca_cache_functions.php');
			include('orca/_functions/orca_constants.php');
			include('orca/maintenance/_tasks/index_records.php');
			break;
		case 'GENERATE_CACHE':
			include('orca/_functions/orca_access_functions.php');
			include('orca/_functions/orca_export_functions.php');
			include('orca/_functions/orca_cache_functions.php');
			include('orca/_functions/orca_constants.php');
			include('orca/maintenance/_tasks/generate_cache.php');
			break;
		case 'RUN_HARVEST':
			break;
	}
}
//else{
//	echo "\nNO TASK TO RUN";
//}


/*
echo "\naddNewTask('RUN_QUALITY_CHECK', '', '', 'ansto.gov.au')\n";
$result = addNewTask('RUN_QUALITY_CHECK', '', '', 'ansto.gov.au');

echo "\naddNewTask('GENERATE_CACHE', '', '', 'ansto.gov.au',".$result.")\n";
$result = addNewTask('GENERATE_CACHE', '', '', 'ansto.gov.au',$result);

echo "\naddNewTask('INDEX_RECORDS', '', '', 'ansto.gov.au',".$result.")\n";
$result = addNewTask('INDEX_RECORDS', '', '', 'ansto.gov.au',$result);
<<<<<<< Updated upstream
*/


require '_includes/finish.php';



?>