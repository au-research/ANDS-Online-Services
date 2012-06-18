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
$executionTimeoutSeconds = 1000 * 60;
ini_set("max_execution_time", "$executionTimeoutSeconds");
ini_set("memory_limit", "768M");
ini_set('display_errors', 0);
error_reporting(E_ALL|E_STRICT);

$cosi_root = "/var/www/htdocs/workareas/leo-git/ANDS-Online-Services/registry/src/";
set_include_path(get_include_path() . PATH_SEPARATOR . $cosi_root);

include 'global_config.php';
include 'orca/_functions/orca_data_functions.php';
include '_includes/_functions/database_functions.php';
include '_includes/_functions/data_functions.php';
include '_includes/_environment/database_env.php';

//chdir($deployementDir."orca/admin");
// Connect to the database.
// ----------------------------------------------------------------------------------------------------------------------------------------------------------
openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);
$eDebugOnStatus = false;
//$dataSources = getDataSources(null, null);
$dataSources = array(array("data_source_key"=>"deakin.edu.au","data_source_name"=>"data1"),array("data_source_key"=>"ecu.edu.eu","data_source_name"=>"ecu"));
$searchResults = array();
$validationResults = Array();
$i = 0;

$dateString = date('d-m-y',time());
$subject = "Link Check Result for: ".$orca_db_name." on: ".$dateString;
$fileContent = "<html><body><h2>".$subject."</h2>\n";
$totalErrors = 0;
$linkChecked = 0;
$emalOnErrorOnly = true;
// CSV Format:    yyyy-mm-dd hh:mm:ss, DataSource, URI, Registery Object Key, Response Code
$csv_report = "DateTime,Data Source, URI, Registery Object Key, Response Code";
if($dataSources)
{
	foreach( $dataSources as $dataSource )
	{
        echo $dataSource['data_source_key'];

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
					$csv_report .= "\n".date("Y-m-d H:i:s").",".$dataSource['data_source_key'].",".$validationResults[$j]['identifier'].",".$validationResults[$j]['registry_object_key'].",".$validationResults[$j]['response_code'];
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
 	send_email($csv_report, "mahmoud.sadeghi@ands.org.au");
}

// send_email($csv_report, "mahmoud.ands@gmail.com");


function mail_attachment($content, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$filename = "briken_link.csv";
	$header = "From: ".$from_name." <".$from_mail.">\r\n";
	$header .= "Reply-To: ".$replyto."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$header .= $message."\r\n\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-Type: text/csv; name=\"".$filename."\"\r\n"; // use different content types here
	$header .= "Content-Transfer-Encoding: base64\r\n";
	$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
	$header .= $content."\r\n\r\n";
	$header .= "--".$uid."--";
	if (mail($mailto, $subject, "", $header)) {
		echo "mail send ... OK"; // or use booleans here
	} else {
		echo "mail send ... ERROR!";
	}
}


function send_email($csv_report, $to) {

	$lines = explode("\n", $csv_report);
	$line_count = count($lines) -1 ;
	$my_name = "ORCA Link Checker";
	$my_mail = "services@ands.org.au";
	$my_replyto = "services@ands.org.au";
	$my_subject = "Broken Links Report (Count=".$line_count.")";
	$my_message = "Hi,\nYou can find the broken links in the attached file.\n BR";
	mail_attachment($csv_report, $to, $my_mail, $my_name, $my_replyto, $my_subject, $my_message);


}

