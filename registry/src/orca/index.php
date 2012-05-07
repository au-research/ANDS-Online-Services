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
require '../_includes/init.php';
require 'orca_init.php';
// Page processing
// -----------------------------------------------------------------------------

responseRedirect('search.php');

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
// BEGIN: Page Content
// =============================================================================

/* BROKEN DATE TIME STAMP
echo "<h3>Date Time text cases</h3><br/>";
$case = "2006-01-01"; $fmt = "YYYY";
echo "<pre><b>".$case."</b> (".$fmt.")</pre>";
$dt = new DateTime($case);
echo "<pre>-&gt; ". date("F j, Y, g:i a", $dt->format("U")) ." (".($dt->format("U")).")</pre><br/>";
$case = "2006-09-21"; $fmt = "YYYY-MM-DD";
echo "<pre><b>".$case."</b> (".$fmt.")</pre>";
$dt = new DateTime($case);
echo "<pre>-&gt; ". date("F j, Y, g:i a", $dt->format("U")) ." (".($dt->format("U")).")</pre>";
*/

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';
?>
