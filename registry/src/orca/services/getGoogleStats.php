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
$base_url = '/var/www/htdocs/registry/';
require $base_url.'global_config.php';
require $base_url.'_includes/_environment/database_env.php';
require $base_url.'_includes/_functions/database_functions.php';
require $base_url.'_includes/_functions/general_functions.php';
require $base_url.'_includes/_functions/access_functions.php';
require $base_url.'orca/_functions/orca_data_functions.php';
require $base_url.'orca/_functions/orca_data_source_functions.php';
require $base_url.'orca/_functions/orca_export_functions.php';
require $base_url.'orca/_functions/orca_access_functions.php';
require $base_url.'orca/_functions/orca_import_functions.php';
require $base_url.'orca/_functions/orca_cache_functions.php';
require $base_url.'orca/_functions/orca_presentation_functions.php';
require $base_url.'gapi-1.3/gapi.class.php';

chdir($base_url."orca/_includes");

//openDatabaseConnection($gCNN_DBS_ORCA_PROD, eCNN_DBS_ORCA_PROD);

openDatabaseConnection($gCNN_DBS_ORCA, eCNN_DBS_ORCA);
		$ga = new gapi(ga_email,ga_password);
		$year = date("Y",time());
		$month = date("m",time());
		$day = date("d",time());


		$yesterday = mktime(0,0,0,$month,$day-1,$year);
	//	$today = mktime(0,0,0,$month,$day,$year);
	//	while($yesterday<$today)	
	//	{
			//$tomorrow = mktime(0,0,0,$month,$day+1,$year);
			$date = date('Y-m-d',$yesterday);

			$ga->requestReportData(ga_profile_id,array('pagePath'),array('pageviews','uniquePageviews'),null,null,$date,$date,1,1000,null,ga_api_key);

			foreach($ga->getResults() as $result)
			{					
				$slug = trim($result->getPagePath(),'/');
				if($slug!='')
				{
					if($objectData = getDataFromSlug($slug))
					{	
						$dayPageId = insertDailyStats($slug, $objectData['registry_object_key'], $objectData['data_source_key'], $objectData['object_group'], $date, $result->getPageviews(),$result->getUniquePageviews(),$objectData['display_title'],$objectData['registry_object_class']);						

					}
				}

			}
			$day = $day + 1;

		
	//	}

?>