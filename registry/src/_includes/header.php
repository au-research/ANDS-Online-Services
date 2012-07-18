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
// Set the Content-Type header.
header("Content-Type: text/html; charset=UTF-8", true);
// Write the XML declaration.
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
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
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<title><?php if( $eDeploymentStatus != 'PROD' ){ print "$eDeploymentStatus&mdash;"; }
    print($pageTitle) ?></title>

	<style type="text/css">
		@import url("<?php print eAPP_ROOT ?>_styles/general.css");
		@import url("<?php print eAPP_ROOT ?>_styles/layout.css");
		@import url("<?php print eAPP_ROOT ?>_styles/menus.css");
		@import url("<?php print eAPP_ROOT ?>_styles/datetime_control.css");
		@import url("<?php print eAPP_ROOT ?>_styles/wait_control.css");
		@import url("<?php print eAPP_ROOT ?>_styles/demo_table.css");
		<?php printApplicationStyleSheets(); ?>

	</style>

	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/general.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/dhtml.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/datetime_control.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/wait_control.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/jquery.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/jquery-ui-1.8.17.custom.min.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/jquery-ui-timepicker-addon-amended.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>

	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/jquery.cookie.js"></script>

	

</head>
<body onload="initDHTML('pageContainerWithMenu'); wcDisposeWait();">



<script type="text/javascript">
	dctInit('<?php print eIMAGE_ROOT ?>_controls/_datetime_control/');
	wcInit('<?php print eIMAGE_ROOT ?>_controls/_wait_control/');
	setRootPath('<?php print eAPP_ROOT ?>');
</script>
<?php

$eDeploymentName = strtok($eDeploymentStatus, " ");


// Display deployment, debug and error status as required.
if( $eDeploymentName != 'PROD' )
{
	switch( $eDeploymentName )
	{
		case 'DEVEL':
			print "<div style=\"color: #ffffff; background: #ff4aaa; border: solid #be3790; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\">&nbsp;".esc($eDeploymentStatus)."&nbsp;</div>";
			break;

		case 'TEST':
			print "<div style=\"color: #ffffff; background: #00d8ff; border: solid #0096c7; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\">&nbsp;".esc($eDeploymentStatus)."&nbsp;</div>";
			break;

		case 'DEMO':
			print "<div style=\"color: #ffffff; background: #000099; border: solid #000033; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\">&nbsp;".esc($eDeploymentStatus)."&nbsp;</div>";
			break;

		default:
			print "<div style=\"color: #ffffff; background: #9800df; border: solid #6700d1; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\">&nbsp;".esc($eDeploymentStatus)."&nbsp;</div>";
			break;
	}
}   


if( $eDebugOnStatus )
{
	print "<div style=\"color: #ffffff; background: #a61d1d; border: solid #7f2016; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\">&nbsp;DEBUG&nbsp;</div>";
}
if( $eDisplayErrors )
{
	print "<div style=\"color: #ffffff; background: #e20000; border: solid #be0400; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\">&nbsp;ERRORS&nbsp;</div>";
}
print "<div style=\"color: #ffffff; background: #e20000; border: solid #be0400; border-width: 0px 0px 0px 20px; font-weight: bold; padding: 4px;\" id=\"dataSourceStatus\" class=\"hide\"></div>";
// The link to Research Data Australia
$rdaLink = '';
$rdaLink = '<a href="http://'.$host.'/'.$rda_root.'">Research Data Australia</a>';
?>
<div class="<?php print $gAppMarginClass ?>">
	<div id="topNav"><script type="text/javascript">writeMenuControls()</script><a href="#menuStart">Skip to Menus</a><a href="#contentStart">Skip to Content</a><?php print $rdaLink ?><?php //print getActivityLink('aCOSI_ABOUT') ?></div>
</div>
<div id="bannerMargin" class="<?php print $gAppMarginClass ?>">
	<div id="banner" style="color: <?php print $gAppTitleTextColour ?>">
		<a href="<?php print eAPP_ROOT ?>" title="<?php printSafe(eINSTANCE_TITLE.' '.eAPP_TITLE) ?>"><img id="bannerImage" src="<?php print $gAppLogoImagePath ?>" alt="<?php printSafe(eINSTANCE_TITLE.' '.eAPP_TITLE) ?>" /></a>
		&nbsp;
		<?php printSafe(eAPP_TITLE) ?>
	</div>
</div>
<div id="outerContentContainer" class="marginLeftGrey">
	<div id="menuContainer"><a id="menuStart" name="menuStart"></a>
<?php drawMenus() ?>
	</div>
	<div id="pageContainerWithMenu">
		<div id="pageActions">
			<?php if( getSessionVar(sROLE_ID) ){ ?>
			<i>Logged in as:</i> <?php printSafe(getSessionVar(sNAME))?>&nbsp;(<?php printSafe(getSessionVar(sROLE_ID)) ?>)&nbsp;&nbsp;
			<?php } ?>
			<?php if( $thisHelpURI && hasActivity('aCOSI_HELP') ){ ?>
			<?php if( strpos(strtoupper($thisHelpURI), 'HTTP') === 0){ ?>
			<a rel="help" href="<?php print($thisHelpURI); ?>" title="Help for this page (new window)">Help</a>
			<?php } else {?>
			<a rel="help" href="<?php print eAPP_ROOT.'help.php?id='.$gThisActivityID.'&amp;page='.urlencode($_SERVER['REQUEST_URI']).'#'.urlencode($thisHelpFragmentId) ?>" title="Help for this page (new window)">Help</a>
			<?php } }?>
		</div>
		<div id="pageContent"><a id="contentStart" name="contentStart"></a>
<!-- BEGIN: Page Content -->
