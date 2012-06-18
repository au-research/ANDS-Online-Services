#!/usr/bin/php
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
set_time_limit(0);
// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 1000*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");
ini_set("memory_limit","768M");
ini_set('display_errors',0);
//error_reporting(E_ALL|E_STRICT);
$BENCHMARK_TIME = array(0,0,0,0,0,0,0,0,0,0);
$BENCH_AVERAGE = array(array(),array(),array(),array());
$cosi_root = "/var/www/htdocs/home/";
set_include_path(get_include_path() . PATH_SEPARATOR . $cosi_root);

include '../src/global_config.php';
include '../src/orca/_functions/orca_data_functions.php';
include '../src/orca/_functions/orca_export_functions.php';
include '../src/_includes/_functions/database_functions.php';
include '../src/orca/_functions/orca_access_functions.php';
include '../src/_includes/_functions/presentation_functions.php';
include '../src/_includes/_functions/data_functions.php';
include '../src/_includes/_environment/database_env.php';


//chdir($deployementDir."orca/admin");
// Connect to the database.
// ----------------------------------------------------------------------------------------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);
define("eDCT_FORMAT_ISO8601_DATETIMESEC_UTC" , 'YYYY-MM-DDThh:mm:ssZ');
$eDebugOnStatus = false;
$eDateTimeFormat = 'YYYY-MM-DDThh:mm:ss';
bench(0);
$dataSources = getDataSources(null, null);
echo "loaded All Datasources";
echo bench(0);
//$SCHEMA_URI = 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd';
$SCHEMA_URI = '/var/www/htdocs/rifcs-schema/registryObjects.xsd';
//$SCHEMA_URI = 'http://devl.ands.org.au/rifcs-schema/registryObjects.xsd';
$searchResults = array();
$validationResults = Array();
$i = 0;

$dateString = date('d-m-y',time());
$subject = "Validator Benchmarking";
$fileContent = "<html><body><h2>".$subject."</h2>\n";
$totalRecords = 0;
$numValidError = 0;
$dataSources = array(array('data_source_key'=>'ansto.gov.au'), array('data_source_key'=> 'ausdata'));

if($dataSources)
{
	foreach( $dataSources as $dataSource )
	{
		bench(1);
		$registryObjects = getRegistryObjectKeysForDataSource($dataSource['data_source_key']);
		$fileContent .= "<p> got all ".sizeof($registryObjects)." registry Objects for ".$dataSource['data_source_key'].bench(1)."</p>";
		if($registryObjects)
		{
			foreach($registryObjects as $registryObject)
			{
				$totalRecords++;
				bench(2);
				$rifCsXML = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$rifCsXML .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
				$rifCsXML .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
				$rifCsXML .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.$SCHEMA_URI.'">'."\n";
				$rifCsXML .= getRegistryObjectXML($registryObject['registry_object_key']);
				$rifCsXML .= "</registryObjects>\n";
				//if($registryObject['registry_object_key'] == '50d6cf48cf53aa790940bb8caee63230ccfa78ac')
				//{
				print($registryObject['registry_object_key']."\n");
				//}

				//print " finished generating ".$registryObject['registry_object_key']." time ".bench(2)."\n";
				$rObject = new DOMDocument();
				$rObject->loadXML($rifCsXML);
				//print($rifCsXML);
				//exit();
				$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
			    $rObject->save($tempFile);

			// Create temporary DOMDocument and re-load content from file.
			    $rObject = new DOMDocument();
			    $rObject->load($tempFile);

			// Delete temporary file.
				if (is_file($tempFile))
				{
				  unlink($tempFile);
				}




				bench(3);
			  	$result = $rObject->schemaValidate($SCHEMA_URI); //xxx
			  	if($result != 1)
			  	{
			  		$numValidError++;
			  		$errors = error_get_last();
			  		$fileContent .= " <p>finished validating <b>".$registryObject['registry_object_key']."</b> time ".bench(3)." <br/><font color='red'>result ".$errors['message']."</font></p>";
			  		//$fileContent .= "<p>".$rifCsXML."</p>";
			  	}
			  	else{bench(3);}
				//print " finished validating ".$registryObject['registry_object_key']." time ".bench(3)." result ".$result."\n";
			}
	}
}

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$footer = "<p><br/>Records Tested: ".$totalRecords." Erroneous Records: ".$numValidError."</body></html>";
	mail("leo.monus@anu.edu.au", $subject, $fileContent.$footer, $headers);


}



function bench($idx = 0)
{
	global $BENCHMARK_TIME;
	global $BENCH_AVERAGE;
	$total = 0;
	if ($BENCHMARK_TIME[$idx] == 0)
	{
		$BENCHMARK_TIME[$idx] = microtime(true);
	}
	else
	{
		$diff = sprintf ("%.3f", (float) (microtime(true) - $BENCHMARK_TIME[$idx]));
		array_push($BENCH_AVERAGE[$idx], $diff);
        //print ("size: ".sizeof($BENCH_AVERAGE[$idx]));
		for($i = 0 ; $i < sizeof($BENCH_AVERAGE[$idx]) ; $i++)
		{
		 $total += $BENCH_AVERAGE[$idx][$i];
		}
		$average = $total / sizeof($BENCH_AVERAGE[$idx]);
		$BENCHMARK_TIME[$idx] = 0;
		return $diff." average ". $average. " for ".sizeof($BENCH_AVERAGE[$idx]);
	}
}
?>