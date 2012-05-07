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
       Object: /pids/delete.php
   Written By: James Blanden
 Created Date: 29 October 2008
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================





*******************************************************************************/
// Include required files and initialisation.
require '../_includes/init.php';
require 'pids_init.php';
// Page processing
// -----------------------------------------------------------------------------

$handle = getQueryValue('handle');
$index = getQueryValue('index');

// Delete the property.
$serviceName = "deleteValueByIndex";
$parameters  = "handle=".urlencode($handle);
$parameters .= "&index=".urlencode($index);
$response = pidsRequest($serviceName, $parameters);

// Return to the identifier.
responseRedirect('view.php?handle='.urlencode($handle));

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================



// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
