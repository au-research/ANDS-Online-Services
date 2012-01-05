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
// Write the XML declaration.
print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">	
<!-- 
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
-->
<head>
	<title><?php if( $eDeploymentStatus != 'PROD' ){ print "$eDeploymentStatus&mdash;"; }
	print $pageTitle ?></title>
	<style type="text/css">
		@import url("<?php print eAPP_ROOT ?>_styles/general.css");
		@import url("<?php print eAPP_ROOT ?>_styles/layout.css");
		@import url("<?php print eAPP_ROOT ?>_styles/menus.css");
	</style>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/general.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/dhtml.js"></script>
</head>
<body onload="setPageHeight('pageContainer')">
<div id="outerContentContainer" class="marginLeftGrey">
	<div id="pageContainer"><a id="contentStart" name="contentStart"></a>
		<div id="pageContent">	
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-weight: normal; font-style: normal;">
&nbsp;&nbsp;<b>Help</b>
<?php
$page = getQueryValue('page');
if( $page != '' )
{
	//print("&nbsp;|&nbsp;&nbsp;<a href=\"".esc($page)."\">back to ".esc($activity->title)."</a>");
}
?>
</p>
<!-- BEGIN: Page Content -->