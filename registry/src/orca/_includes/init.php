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

require_once(dirname(__FILE__).'/../../global_config.php');


// If not running within COSI, include COSI's environment variables
// (needed for "lightweight" activities such as fetch_element)
if (!defined("eHOST")) { require '../_includes/_environment/application_env.php'; }
require_once(eAPPLICATION_ROOT.'/global_config.php');
// Use to prevent direct access to certain scripts
define('IN_ORCA', true);


// ORCA environment settings.
// -----------------------------------------------------------------------------
// The locations of the schemata.
// Data provided by data sources will be validated against this schema.
//define('gRIF_SCHEMA_URI', 'http://'.eHOST.'/'.eROOT_DIR.'/'.'orca/schemata/registryObjects.xsd');

// note: changing this will cause all reharvested records to have a "new record revision" created
define('gRIF_SCHEMA_PATH', eAPPLICATION_ROOT.'/orca/schemata/registryObjects.xsd'); 

define('gRIF_SCHEMA_URI', 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');

define('gCURRENT_SCHEMA_VERSION', '1.3');

// Party identifiers to be treated to special display (NLA-specific)
$NLAPartyTypeKey = array ("AU-ANL:PEAU");

// ARC/NHMRC grants (Activities project integration)
define("eAU_RESEARCH_GRANTS_PREFIX", "http://thisurlwillneverexistatall.ands.org.au/but/they/will/never/remember/qg13r7uy8qg23wrhbuy1tqhsdbuyh78dt6"); // "http://purl.org/au-research/grants/"
define("eAU_RESEARCH_GRANTS_HARVEST_POINT","https://services.ands.org.au/sandbox/orca/services/getRegistryObjects.php");
define("eAU_RESEARCH_GRANTS_DATA_SOURCE","AU_RESEARCH_GRANTS");

// New originatingSource identifiers for objects created using manual entry tools
define("eORIGSOURCE_RMD_SUFFIX", "orca/register_my_data");
define("eORIGSOURCE_PMD_SUFFIX", "orca/publish_my_data");

define("eORIGSOURCE_REGISTER_MY_DATA", 'http://'.eHOST.'/'.eROOT_DIR.'/'.eORIGSOURCE_RMD_SUFFIX);
define("eORIGSOURCE_PUBLISH_MY_DATA", 'http://'.eHOST.'/'.eROOT_DIR.'/'.eORIGSOURCE_PMD_SUFFIX);

// Google Analytics tracking code (UA-XXX-XXXX)                       [optional]
// Set to an empty string in order to disable analytics tracking
define("eGOOGLE_ANALYTICS_TRACKING_CODE_ORCA", "");
define("eGOOGLE_ANALYTICS_TRACKING_CODE_RDA", "");



define('gORCA_DATA_SOURCE_LIST_SCHEMA_URI', 'http://'.eHOST.'/'.eROOT_DIR.'/'.'orca/schemata/dataSourceList.xsd');
define('gORCA_GROUP_LIST_SCHEMA_URI', 'http://'.eHOST.'/'.eROOT_DIR.'/'.'orca/schemata/groupList.xsd');

// The registry administrator email address for the OAI-PMH Identify response.
define('gORCA_INSTANCE_ADMIN_EMAIL', eCONTACT_EMAIL);

// The vocabulary service settings.
// Use an empty string to use the internal vocabularies
// or provide the base uri to an appropriate service.
define('gORCA_VOCABS_BASE_URI', ""); 

// The harvester settings.
// Use empty strings for both values if there is no harvester available.
define('gORCA_HARVESTER_BASE_URI', $harvest_url);
define('gORCA_HARVESTER_IP', $harvest_ip);

// Harvest methods.
// These values are used in _javascript/data_source_forms.js
// and so any changes here will require corresponding changes there.
define('gORCA_HARVEST_METHOD_DIRECT', 'DIRECT');
define('gORCA_HARVEST_METHOD_HARVESTER_DIRECT', 'GET');
define('gORCA_HARVEST_METHOD_HARVESTER_OAIPMH', 'RIF');

$gORCA_HARVEST_METHODS = array( gORCA_HARVEST_METHOD_DIRECT => 'DIRECT'
							  );
							  
// If we have a harvester configured, then add the methods supported by the harvester.
if( gORCA_HARVESTER_BASE_URI )
{
	$gORCA_HARVEST_METHODS[gORCA_HARVEST_METHOD_HARVESTER_DIRECT] = 'Harvester DIRECT';
	$gORCA_HARVEST_METHODS[gORCA_HARVEST_METHOD_HARVESTER_OAIPMH] = 'Harvester OAI-PMH';
}

// Data provider types.
define('gORCA_PROVIDER_TYPE_RIF', 'RIF');
define('gORCA_PROVIDER_TYPE_OAI_RIF', 'OAI_RIF');

$gORCA_PROVIDER_TYPES = array( gORCA_PROVIDER_TYPE_RIF     => 'RIF',
							   gORCA_PROVIDER_TYPE_OAI_RIF => 'RIF OAI-PMH'
							 );
							 
// Supported provider types for harvest methods.
$gORCA_HARVEST_PROVIDER_SETS = array( gORCA_HARVEST_METHOD_DIRECT           => array(gORCA_PROVIDER_TYPE_RIF),
									  gORCA_HARVEST_METHOD_HARVESTER_DIRECT => array(gORCA_PROVIDER_TYPE_RIF),
									  gORCA_HARVEST_METHOD_HARVESTER_OAIPMH => array(gORCA_PROVIDER_TYPE_OAI_RIF)
									);
									
// Harvester harvest frequencies.
$gORCA_HARVESTER_FREQUENCIES = array('daily', 'weekly', 'fortnightly', 'monthly' );

//RDA SPECIFIC
$gRDA_RIGHTSURL_RIGHTSLOGO = array( 'https://df.arcs.org.au/ARCS/projects/PICCLOUD' => 'http://polarcommons.org/images/PIC_print_small.png');

$gORCA_STATUS_INFO = array (	
							"PUBLISHED"=>array("colour"=>"#32CD32", "span"=>"PUBLISHED", "short_span"=>"PUBLISHED", "display"=>"Published"),
							"APPROVED"=>array("colour"=>"#EDD155", "span"=>"APPROVED", "short_span"=>"APPROVED", "display"=>"Approved"),
							"ASSESSMENT_IN_PROGRESS"=>array("colour"=>"#0B2E59", "span"=>"ASSESSMENT IN PROGRESS", "short_span"=>"IN PROGRESS", "display"=>"Assessment in Progress"),
							"SUBMITTED_FOR_ASSESSMENT"=>array("colour"=>"#688EDE", "span"=>"SUBMITTED FOR ASSESSMENT", "short_span"=>"SUBMITTED","display"=>"Submitted for Assessment"),
							"MORE_WORK_REQUIRED"=>array("colour"=>"#6A4A3C", "span"=>"MORE WORK REQUIRED", "short_span"=>"WORK REQUIRED", "display"=>"More Work Required"),
							"DRAFT"=>array("colour"=>"#cc6600", "span"=>"DRAFT", "short_span"=> "DRAFT", "display"=>"Draft"),
							"DELETED"=>array("colour"=>"#D64040", "span"=>"DELETED", "short_span"=>"DELETED", "display"=>"Deleted"),
);







// PIDS environment settings.
// -----------------------------------------------------------------------------
// Service configuration.
define('gPIDS_SERVICE_BASE_URI', $pids_url);
define('gPIDS_APP_ID', $pids_app_id);
define('gSOLR_UPDATE_URL' , $solr_url . "update");
