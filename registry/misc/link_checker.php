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

$cosi_root = "/var/www/htdocs/home/";
set_include_path(get_include_path() . PATH_SEPARATOR . $cosi_root);

include 'global_config.php';
include 'orca/_functions/orca_data_functions.php';
include '_includes/_functions/database_functions.php';
include '_includes/_functions/data_functions.php';
include '_includes/_environment/database_env.php';


// Set the default context stream to emulate firefox (avoid being blitted by over-protective sysadmins)
$default_opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.20) Gecko/20081217 Firefox/2.0.0.20 (.NET CLR 3.5.30729)",
  )
);
stream_context_set_default($default_opts);


//chdir($deployementDir."orca/admin");
// Connect to the database.
// ----------------------------------------------------------------------------------------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);
$eDebugOnStatus = false;
$dataSources = getDataSources(null, null);
$searchResults = array();
$validationResults = Array();
$i = 0;

$dateString = date('d-m-y',time());
$subject = "Link Check Result for: ".$orca_db_name." on: ".$dateString;
$fileContent = "<html><body><h2>".$subject."</h2>\n";
$totalErrors = 0;
$linkChecked = 0;
$emalOnErrorOnly = true;
if($dataSources)
{
	foreach( $dataSources as $dataSource )
	{
		$registryObjects = getRegistryObjectKeysForDataSource($dataSource['data_source_key']);
		if($registryObjects)
		{
			foreach($registryObjects as $registryObject)
			{
				$relatedInfos = getRelatedInfo($registryObject['registry_object_key']);
				if($relatedInfos)
				{
					foreach($relatedInfos as $relatedInfo)
					{
						if($relatedInfo['identifier_type'] == 'uri')
						{
							$linkChecked++;
							$headers = null;
							if($headers = get_headers($relatedInfo['identifier']))
							{
	    						$httpCode = substr($headers[0], 9, 1);
								if($httpCode == 4 || $httpCode == 5)
								{
									$validationResults[$i++] = Array("identifier" => $relatedInfo['identifier'],"registry_object_key" => $registryObject['registry_object_key'], "response_code" => $headers[0]);
								}
							}
							else
							{
								$validationResults[$i++] = Array("identifier" => $relatedInfo['identifier'],"registry_object_key" => $registryObject['registry_object_key'], "response_code" => 'request timed out');
							}
						}
					}
				}
			}

			if($i > 0)
			{
				$fileContent .= "<h3>Datasource :" .$dataSource['data_source_key']." has ". $i . " invalid link(s)</h3>\n";
				$fileContent .= "<ul>\n";
				for($j=0; $j < sizeof($validationResults) ; $j++)
				{
					$fileContent .="<li>uri: ".$validationResults[$j]['identifier']." for registry_object_key: ".$validationResults[$j]['registry_object_key']." response code: ".$validationResults[$j]['response_code']."</li>\n";
				}
				$fileContent .="</ul><br/>\n";
			}
		}
		$validationResults = Array();
		$registryObjects = null;
		$relatedInfos = null;
		$totalErrors += $i;
		$i = 0;

	}
}
if($totalErrors > 0 || !$emalOnErrorOnly)
{
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$footer = "<p>links checked: ".$linkChecked."<br/>number of bad links: ".$totalErrors."</body></html>";
	mail(eCONTACT_EMAIL, $subject, $fileContent.$footer, $headers);
}


require '_includes/finish.php';
?>