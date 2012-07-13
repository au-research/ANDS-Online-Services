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

********************************************************************************
 PRODUCTION
*******************************************************************************/
// Web Server Environment
// -----------------------------------------------------------------------------

//require_once("/var/www/htdocs/workareas/minh/ands/registry/src/global_config.php");
require_once(dirname(__FILE__).'/../../global_config.php');

define("eHOST", $host);
define("eROOT_DIR", $cosi_root);
define("ePROTOCOL", $default_protocol);
define("eAPP_ROOT", ePROTOCOL.'://'.eHOST.'/'.eROOT_DIR.'/');
define("eHTTP_APP_ROOT",  'http://'.eHOST.'/'.eROOT_DIR.'/');

define("eIMAGE_ROOT", eAPP_ROOT.'_images/');

// Assume that the request root is set at the APP_ROOT
// init.php will set this to the actual request root.
$eRequestRoot = eAPP_ROOT;



// ORCA PIDS Service 
// -----------------------------------------------------------------------------
// URI of PIDS service (Server IP address should be added as 
//                      trusted admin on Tomcat PID service)
$ePIDS_RESOURCE_URI = $pids_url;


// Shibboleth Settings
// -----------------------------------------------------------------------------
// The Shibboleth Session Initiator '/Login?xxxx' | '/WAYF/{idp or WAYF location}' | '/DS?xxxx'
//$eShibbolethSessionInitiator = 'DS';
//$eShibbolethSessionInitiator = '';
// The domain over which the Shibboleth identity is unique.
$eShibbolethAuthDomain = 'aaf.edu.au';
//$eShibbolethAuthDomain = '';

// The Shibboleth header that uniquely identifies the user in the authentication domain (defined above).
define('eSHIBBOLETH_IDENTITY_HEADER', 'shib-shared-token');
// The Shibboleth header that provides the user's name.
define('eSHIBBOLETH_DISPLAYNAME_HEADER', 'displayName');

// Application Instance Settings
// -----------------------------------------------------------------------------
// Application deployment status 'DEVEL' | 'TEST' | '.....' | 'PROD'.
// A status of anything other than 'PROD' will be displayed in the user interface.
$eDeploymentStatus = $deploy_as;

// Custom debug information setting true | false.
// Make sure that this is set to false for any deployment other than DEVEL.
// A setting of true will display  in the user interface.
$eDebugOnStatus = $debug;

// Display errors true | false.
// Make sure that this is set to false for any deployment other than DEVEL.
$eDisplayErrors = $error;


if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
$ip=$_SERVER["REMOTE_ADDR"];
} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
$ip=$_SERVER["HTTP_CLIENT_IP"];
}


// Set the identity theme for this instance of the application.
$eThemes = array(   'EXAMPLE'	=> array( eIMAGE_ROOT.'_logos/logo_EXAMPLE.gif',
										'marginLeft_EXAMPLE',
										'#6f9318'
									  ),
					'ONE'		=> array( eIMAGE_ROOT.'_logos/logo_ONE.gif',
										'marginLeft_ONE_Magenta',
										'#713269'
									  ),
					'TWO'		=> array( eIMAGE_ROOT.'_logos/logo_TWO.gif',
										'marginLeft_TWO_Blue',
										'#333366'
									  ),
					'ANDS_DARK'	=> array( eIMAGE_ROOT.'_logos/logo_ANDS.gif',
										'marginLeft_ANDSGreen',
										'#283f09'
									  ),
					'ANDS_LIGHT'	=> array( eIMAGE_ROOT.'_logos/logo_ANDS.gif',
										'marginLeft_ANDSLightGreen',
										'#283f09'
									  )
				);

$eTheme = 'ANDS_LIGHT';

// Timezone Settings and Date/Time Format Constants
// -----------------------------------------------------------------------------
// See http://www.php.net/manual/en/timezones.php 
// for a list of supported timezones.
ini_set("date.timezone", "Australia/ACT");

// Format mask constants for use with date presentation functions formatDateTimeWithMask, 
// formatDateTime, and the datetime DHTML control (/_javascript/datetime_control.js).
// These strings will be treated in a case-sensitive way.
// The code assumes that all masks requiring a date will include 'YYYY', 'MM', and 'DD'.
// The code also assumes that all masks requiring a time will include 'hh' and 'mm'.
// There is no support for years before year 0.

define("eDCT_FORMAT_ISO8601_DATE"            , 'YYYY-MM-DD');                // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_DATE_TIME"       , 'YYYY-MM-DD hh:mm');          // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_DATE_TIMESEC"    , 'YYYY-MM-DD hh:mm:ss');
define("eDCT_FORMAT_ISO8601_DATE_TIME_OFF"   , 'YYYY-MM-DD hh:mmOOOO');
define("eDCT_FORMAT_ISO8601_DATE_TIMESEC_OFF", 'YYYY-MM-DD hh:mm:ssOOOO');
define("eDCT_FORMAT_ISO8601_DATE_TIME_UTC"   , 'YYYY-MM-DD hh:mmZ');         // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_TIME"            , 'hh:mm');                     // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_TIME_UTC"        , 'hh:mmZ');                    // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_DATETIME"        , 'YYYY-MM-DDThh:mm');          // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_DATETIMESEC"     , 'YYYY-MM-DDThh:mm:ss');
define("eDCT_FORMAT_ISO8601_DATETIME_OFF"    , 'YYYY-MM-DDThh:mmOOOO');
define("eDCT_FORMAT_ISO8601_DATETIMESEC_OFF" , 'YYYY-MM-DDThh:mm:ssOOOO');
define("eDCT_FORMAT_ISO8601_DATETIME_UTC"    , 'YYYY-MM-DDThh:mmZ');         // Supported by datetime control.
define("eDCT_FORMAT_ISO8601_DATETIMESEC_UTC" , 'YYYY-MM-DDThh:mm:ssZ');
define("eDCT_FORMAT_AU_DATE"                 , 'DD/MM/YYYY');                // Supported by datetime control.
define("eDCT_FORMAT_AU_DATETIME"             , 'DD/MM/YYYY hh:mm AM');       // Supported by datetime control.
define("eDCT_FORMAT_US_DATE"                 , 'MM/DD/YYYY');                // Supported by datetime control.
define("eDCT_FORMAT_US_DATETIME"             , 'MM/DD/YYYY hh:mm AM');       // Supported by datetime control.
define("eDCT_FORMAT_TIME"                    , 'hh:mm AM');                  // Supported by datetime control.

// Application wide setings for datetime presentation with formatDateTime.
$eDateFormat     = eDCT_FORMAT_ISO8601_DATE;
$eTimeFormat     = eDCT_FORMAT_ISO8601_TIME;
$eDateTimeFormat = eDCT_FORMAT_ISO8601_DATE_TIMESEC;

// PHP painful quotes string modification settings
// -----------------------------------------------------------------------------
ini_set("magic_quotes_runtime", "0");
ini_set("magic_quotes_sybase", "0");
// fixMagicQuotesGPC() is called in the init.php to sort that out.


// PHP Memory Settings
// -----------------------------------------------------------------------------
ini_set("memory_limit", "-1"); // -1 for unlimited.

// Error Handling
// -----------------------------------------------------------------------------
ini_set("display_errors", "0");
if( $eDisplayErrors )
{ 
	ini_set("display_errors", "1"); 
	error_reporting(E_ALL | E_STRICT);
}

// Set the path to the error_log.
// This file will need to be writable by the web server.
//ini_set("error_log", "/usr/local/php5/bin/error-log");

// Set the max size of the error log in bytes.
ini_set("log_errors_max_len", "8000000");

// Turn error logging on.
ini_set("log_errors", "1");

// Session Handling
// -----------------------------------------------------------------------------
//ini_set("session.save_path", "/tmp");
ini_set("session.hash_function", "1");    // Use SHA1 hashes.
ini_set("session.use_only_cookies", "1"); // Force cookies for session handling.
ini_set("session.cookie_httponly", "1");  // Don't allow access to the cookie
                                          // via anything other than HTTP eg Javascript.
                                          // Requires browser support, but can't hurt.

//ini_set("session.referer_check", eHOST); // This will interfere with deep-link redirects 
                                           // after a Shibboleth login.



// Create or resume a session.
// This needs to happen after the settings above, so it's better off here
// than in init.php.
$a = session_id();
if(empty($a))
{
	session_start();
}
?>
