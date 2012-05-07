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
require '_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<h2>About The Applications</h2>

<h3>Versions</h3>
COSI 1.3.8<br />
<ul>
	<li>COSI Administration 1.0</li>
	<li>ORCA Registry 3.3.5</li>
	<li>Research Data Australia 1.2.1</li>
	<li>Persistent Identifiers Self Service 1.1.2</li>
</ul>

Using Harvester 1.0, Vocabulary Service 0.1, and Persistent Identifier Service 1.1. 

<h3>Settings</h3>
<table class="recordTable" summary="Record Title">
	<tbody class="recordFields">
		<tr>
			<td>Timezone:</td>
			<td><?php printSafe(date_default_timezone_get()); ?></td>
		</tr>
		<tr>
			<td>Date Format:</td>
			<td><?php printSafe($eDateFormat); ?></td>
		</tr>
		<tr>
			<td>Time Format:</td>
			<td><?php printSafe($eTimeFormat); ?></td>
		</tr>
		<tr>
			<td>DateTime Format:</td>
			<td><?php printSafe($eDateTimeFormat); ?></td>
		</tr>
	</tbody>
</table>

<h3>Requirements</h3>
<p>This web application has been tested with the following web browsers:</p>
<ul>
	<li>Firefox 2 on Mac OS X 10.4</li>
	<li>Safari 2 on Mac OS X 10.4</li>
	<li>Firefox 3 on Mac OS X 10.5</li>
	<li>Safari 3 on Mac OS X 10.5</li>
	<li>Firefox 2 on Windows XP SP2</li>
	<li>Firefox 3 on Windows XP SP3</li>
	<li>Internet Explorer 7 on Windows XP SP3</li>
</ul>

<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '_includes/footer.php';
require '_includes/finish.php';
?>
