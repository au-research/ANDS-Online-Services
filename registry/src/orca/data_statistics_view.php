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

$action = getQueryValue('action');

// Buffer output for this page so that the 
// wcPleaseWait dialog remains active for the duration of the search.
ob_start();

$statisticsTitle = 'Statistics of the Collections Registry.';
if( userIsORCA_SOURCE_ADMIN() || userIsORCA_ADMIN() )
{
	$statisticsTitle = 'Administrative '.$statisticsTitle;
}

// Buffer output for this page so that the 
// wcPleaseWait dialog remains active for the duration of the search.
ob_start();

// =============================================================================
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
getAnalyticsTrackingCode(eGOOGLE_ANALYTICS_TRACKING_CODE_ORCA);

// BEGIN: Page Content
// =============================================================================
?>
<form id="statisticsform" action="data_statistics_view.php" method="get" onsubmit="wcPleaseWait(true, 'Retrieving...')">
<fieldset>
<legend><?php printSafe($statisticsTitle)?></legend>

<label for="dateFrom" class="stat">Date From</label> <?php drawMonthYearInput('monthFrom', getQueryValue('monthFrom'),'yearFrom', getQueryValue('yearFrom') ) ?> <br /><br />
<label for="dateTo" class="stat">Date To</label> <?php drawMonthYearInput('monthTo', getQueryValue('monthTo'),'yearTo', getQueryValue('yearTo') ) ?> <br /><br />
<label for="typeStat" class="stat">Registry Types</label> <input name="typeStat" id="typeStat" type="checkbox" value="1" <?php if(getQueryValue('typeStat')) echo " checked";?>/> <br /><br />


	
	<label for="submit" class="stat">&nbsp;</label><input type="submit" name="action" value="Get Statistics" />	
	
</fieldset>
</form>

	<?php
if( strtoupper($action) == "GET STATISTICS")
{ ?>

		<p>&nbsp;</p>
	<fieldset>
	<legend>
		Registry Statistics
		&nbsp;&nbsp;&nbsp; <a style="padding: 0px; margin: 0px;" href="services/data_statistics_xls.php?monthFrom=<?php echo getQueryValue('monthFrom');?>&yearFrom=<?php echo getQueryValue('yearFrom');?>&monthTo=<?php echo getQueryValue('monthTo');?>&yearTo=<?php echo getQueryValue('yearTo');?>&typeStat=<?php echo getQueryValue('typeStat');?>"><img title="Get xls file for the statistics" style=" vertical-align: -0.6em;" src="<?php echo gORCA_IMAGE_ROOT;?>xls.gif" alt=""/></a>	
		</legend>
		<?php 

	drawStatTable(getQueryValue('typeStat'));
	?>
	</fieldset>
	<?php 


}

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';

// Send the ouput from the buffer, and end buffering.
ob_end_flush();


?>
