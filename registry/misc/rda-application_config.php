<?php


// BEGIN - RDA #################################################################
/*******************************************************************************
$Date: 2010-05-17 15:26:46 +1000 (Mon, 17 May 2010) $
$Revision: 368 $
*******************************************************************************/
// =============================================================================
// RDA Home
$activity = new activity('aRDA_HOME', 'Research Data Australia', 'orca/rda/index.php');
$activity->no_check_ssl= true;
addActivity($activity);
	
// =============================================================================
// RDA About
$activity = new activity('aRDA_ABOUT', 'About', 'orca/rda/about.php');
$activity->no_check_ssl= true;
addActivity($activity);
	
// =============================================================================
// RDA Disclaimer
$activity = new activity('aRDA_DISCLAIMER', 'Disclaimer', 'orca/rda/disclaimer.php');
$activity->no_check_ssl= true;
addActivity($activity);
	
// =============================================================================
// RDA Help
$activity = new activity('aRDA_HELP', 'Help', 'orca/rda/help.php');
$activity->no_check_ssl= true;
addActivity($activity);
	
// =============================================================================
// RDA View
$activity = new activity('aRDA_VIEW', 'View', 'orca/rda/view.php');
$activity->no_check_ssl= true;
addActivity($activity);
	
// =============================================================================
// RDA List
$activity = new activity('aRDA_LIST', 'View', 'orca/rda/list.php');
$activity->no_check_ssl= true;
addActivity($activity);

// END - RDA ###################################################################



?>