<?php
/*
Copyright 2008 The Australian National University
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
       Object: /pids/pids_init.php
   Written By: James Blanden
 Created Date: 28 October 2008
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================
 James Blanden        15/05/2009    Service configuration.

*******************************************************************************/
// PIDS environment settings.
// -----------------------------------------------------------------------------

// Service configuration.
define('gPIDS_SERVICE_BASE_URI', $pids_url);

define('gPIDS_APP_ID', $pids_app_id);


$gPIDS_PROPERTY_TYPES = array( 
							     'URL'  => 'URL',
							     'DESC' => 'Description'
							 );
							 
define('gPIDS_IMAGE_ROOT', eAPP_ROOT.'pids/_images/');

// Add the PIDS stylesheet to the header.
importApplicationStylesheet(eAPP_ROOT.'pids/_styles/pids.css');

// Required files and application initialisation: order is important.
// -----------------------------------------------------------------------------
// Include environment settings.
require '_functions/pids_functions.php';
?>
