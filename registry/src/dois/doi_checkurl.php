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
// Include required files and initialisation.

chdir("/var/www/htdocs/home/dois");

require 'dois_init.php';

// Page processing
// -----------------------------------------------------------------------------

$unavailableCount = 0;
$message = '';
$subject = "Cite My Data DOI url availability check";
$recipient = "services@ands.org.au";
$recipient = "lizwoods.ands@gmail.com";
$notifyMessage = '';
$lastupdate = '';
$doiList = getDoiList();

if($doiList)
{
	foreach($doiList as $doi)
	{
		//we want to check if the url is available
		if(!doisDomainAvailible($doi["url"]))
		{
			$lastupdate = $doi["updated_when"];
			if(!$lastupdate) $lastupdate = $doi["created_when"];
			$clientDetails = getDoisClientDetails($doi["client_id"]);
			$notifyMessage .= $doi["doi_id"]." ".$doi["url"]." ".$clientDetails[0]["client_name"]." ".$lastupdate."\n";
			$unavailableCount++;
		}
	}	
}

date_default_timezone_set('Antarctica/Macquarie');
$message .= "There are ".$unavailableCount." doi urls unavailable on ".date("d/m/Y h:m:s")."\n"; 
$message .= $notifyMessage;
mail($recipient,$subject,$message);
?>
