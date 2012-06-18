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
$BENCH_AVERAGE = array(array(),array(),array(),array(),array(),array(),array(),array(),array(),array(),array());
$cosi_root = "/var/www/htdocs/home/";
set_include_path(get_include_path() . PATH_SEPARATOR . $cosi_root);

include '../src/global_config.php';
include '../src/orca/_functions/orca_data_functions.php';
include '../src/orca/_functions/orca_export_functions.php';
include '../src/orca/_functions/orca_import_functions.php';
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
define("SCHEMA_URI" , 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');
$eDebugOnStatus = false;
$eDateTimeFormat = 'YYYY-MM-DDThh:mm:ss';
$dataSources = getDataSources(null, null);
echo "loaded All Datasources\n";

$i = 0;

$dateString = date('d-m-y',time());
$subject = "Quality check Benchmarking";
$fileContent = "<html><body><h2>".$subject."</h2>\n";
$totalRecords = 0;
$dataSources = array(array('data_source_key'=>'ansto.gov.au'), array('data_source_key'=> 'ausdata'));

	$rmdQualityTest = new DomDocument();
	$rmdQualityTest->load('../src/orca/_xsl/rmd_quality_test.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($rmdQualityTest);



if($dataSources)
{
	foreach( $dataSources as $dataSource )
	{
		bench(0);
		$dataSourceKey = $dataSource['data_source_key'];
		$fileContent = 'Quality check run for '.$dataSourceKey."\n";
		echo $fileContent;
		if($registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey))
		{
			for( $i=0; $i < count($registryObjectKeys); $i++ )
			{
				$totalRecords++;
				bench(1);
				$fileContent .= benchQualityCheckForRegistryObject($registryObjectKeys[$i]['registry_object_key'], $dataSourceKey);
				print ("QA:".bench(1)."\n");
			}
		}
		if($draftRegistryObjectKeys = getDraftRegistryObject(null, $dataSourceKey))
		{
			for( $i=0; $i < count($draftRegistryObjectKeys); $i++ )
			{
				$totalRecords++;
				bench(1);
				$fileContent .= benchQualityCheckForDraftRegistryObject($draftRegistryObjectKeys[$i]['draft_key'], $dataSourceKey);
				print ("QA:".bench(1)."\n");
			}
		}
		print ("total DS:".bench(0)."\n");
		//echo $fileContent;
	}

}
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$footer = "<p><br/>Records Tested: ".$totalRecords."</body></html>";
	mail("leo.monus@anu.edu.au", $subject, $fileContent.$footer, $headers);

function benchQualityCheckForRegistryObject($registryObjectKey, $dataSourceKey)
{

		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.SCHEMA_URI.'">'."\n";
		$rifcs .= getRegistryObjectXML($registryObjectKey);
		$rifcs .= '</registryObjects>';
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";
		}
		elseif(str_replace("<Servive","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";
		}
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";
		}

		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);

		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		$relatedObjectClassesStr = '';
		//bench(1);
		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		//print ("Related fetch (PUBLISHED):".bench(1)."\n");
		//bench(2);
		//$qualityTestResult = benchQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
		$qualityTestResult = benchQualityCheckonDomOld($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
		//print ("QA transform:".bench(2)."\n");
	    $errorCount = substr_count($qualityTestResult, 'class="error"');
		$warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');
        //$result = updateRegistryObjectQualityTestResult($registryObjectKey, $qualityTestResult, $errorCount, $warningCount);
		//return $result;
}


function benchQualityCheckForDraftRegistryObject($registryObjectKey, $dataSourceKey)
{
		$registryObject = getDraftRegistryObject($registryObjectKey,$dataSourceKey);
		$relatedObjectClassesStr = '';
		$rifcs = $registryObject[0]['rifcs'];
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";
		}
		elseif(str_replace("<Service","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";
		}
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";
		}

		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);

		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		//bench(1);
		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		//print ("Related fetch (Draft):".bench(1)."\n");
		//bench(2);
		//$qualityTestResult = benchQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
		$qualityTestResult = benchQualityCheckonDomOld($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
		$errorCount = substr_count($qualityTestResult, 'class="error"');
	    $warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');
	   // print ("QA transform:".bench(2)."\n");
        //$result = updateDraftRegistryObjectQualityTestResult($registryObjectKey, $dataSourceKey, $qualityTestResult, $errorCount, $warningCount);
        //return $result;// $registryObjectKey.','.$dataSourceKey.','.$qualityTestResult.','.$errorCount.','.$warningCount.'<br/>';
}



function benchQualityCheckonDom($registryObjects, $dataSource, $output, $relatedObjectClassesStr)
{

	global $proc;
	$proc->setParameter('', 'dataSource', $dataSource);
	$proc->setParameter('', 'output', $output);
	$proc->setParameter('', 'relatedObjectClassesStr', $relatedObjectClassesStr);
	$result = $proc->transformToXML($registryObjects);
	return $result;
}

function benchQualityCheckonDomOld($registryObjects, $dataSource, $output, $relatedObjectClassesStr)
{
	$rmdQualityTest = new DomDocument();
	$rmdQualityTest->load('../src/orca/_xsl/rmd_quality_test.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($rmdQualityTest);
	$proc->setParameter('', 'dataSource', $dataSource);
	$proc->setParameter('', 'output', $output);
	$proc->setParameter('', 'relatedObjectClassesStr', $relatedObjectClassesStr);
	$result = $proc->transformToXML($registryObjects);
	return $result;
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