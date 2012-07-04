<?php
// Set unlimited execution
set_time_limit(0);
ini_set('memory_limit', '1024M');

// Get our global config path
$APPLICATION_BASE = "CHANGEME";
$ip = '';

set_include_path(get_include_path() . PATH_SEPARATOR . $APPLICATION_BASE);
include 'global_config.php';

$runningInBackgroundTask = true; // what is this?!?
$cosi_root = $APPLICATION_BASE; //"/var/www/htdocs/workareas/ben/registry/src/";

// Random variables that need to be defined
$typeArray = array();
$vocabBroaderTerms = array();
if (!defined('NL')) {
	define('NL', "\n");
}
$chunkSize = 50;

define('gRIF_SCHEMA_PATH', eAPPLICATION_ROOT.'/orca/schemata/registryObjects.xsd');
define('gRIF_SCHEMA_URI', 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');
define("eDCT_FORMAT_ISO8601_DATETIMESEC_UTC" , 'YYYY-MM-DDThh:mm:ssZ');
define("eIMAGE_ROOT", "");


include '_includes/_functions/database_functions.php';
include '_includes/_functions/data_functions.php';
include '_includes/_functions/general_functions.php';
include '_includes/_functions/presentation_functions.php';
include '_includes/_environment/database_env.php';
include 'orca/_functions/orca_data_functions.php';
include 'orca/_functions/orca_taskmgr_functions.php';
require_once('orca/_functions/orca_access_functions.php');
require_once('orca/_functions/orca_constants.php');
require_once('orca/_functions/orca_import_functions.php');
require_once('orca/_functions/orca_export_functions.php');
require_once('orca/_functions/orca_presentation_functions.php');
require_once('orca/_functions/orca_data_source_functions.php');
require_once('orca/_functions/orca_cache_functions.php');
//require_once('orca/_functions/orca_solr_functions.php');
?>
