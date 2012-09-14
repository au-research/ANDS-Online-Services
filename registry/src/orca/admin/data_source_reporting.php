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
require '../../_includes/init.php';
require '../orca_init.php';
require '../../gapi-1.3/gapi.class.php';
// Page processing
// -----------------------------------------------------------------------------
$report_type  = '';
$errorMessages = '';
$reportObjectLabelClass = '';
$reportdsLabelClass = '';
$ds_report = getPostedValue('ds_report');
$org_report = getPostedValue('org_report');
$dataSourceKey = getPostedValue('source_key');
$objectGroup = getPostedValue('object_group');

$dayFrom = getPostedValue('dayFrom');
$monthFrom = getPostedValue('monthFrom');
$yearFrom = getPostedValue('yearFrom');
$dateFrom = date("Y-m-d",strtotime($dayFrom."-".$monthFrom."-".$yearFrom));
$dateFromDisplay = date("d M Y",strtotime($dayFrom."-".$monthFrom."-".$yearFrom));

$dayTo = getPostedValue('dayTo');
$monthTo = getPostedValue('monthTo');
$yearTo = getPostedValue('yearTo');
$dateTo = date("Y-m-d",strtotime($yearTo."-".$monthTo."-".$dayTo));
$dateToDisplay = date("d M Y",strtotime($dayTo."-".$monthTo."-".$yearTo));

$dateString = $dateFromDisplay." - ".$dateToDisplay;

if(getPostedValue('org_report')=='Generate Report') { $report_type = "group"; $groupingType = "group"; $groupingValue = $objectGroup;}
if(getPostedValue('ds_report')=='Generate Report')  { $report_type = "datasource";$groupingType = "data_source";  $groupingValue = $dataSourceKey;}

if($report_type!='')
{

//we need to check we have the appropriate data passed and then display the report - calling individual stats functions
	if($report_type == 'group' && $objectGroup == '')
	{
		$reportObjectLabelClass = "style='color:#FF0000'";
		$errorMessages = "To generate an organisational report you must select the organisational group.";
	//	echo $errorMessages."<br />";
	}
	if($report_type == 'datasource' && $dataSourceKey == '')
	{
		$reportdsLabelClass = "style='color:#FF0000'";
		$errorMessages = "To generate a datasource report you must select the datasource.";
		//		echo $errorMessages."<br />";	
	}
	
	if($errorMessages=='')
	{
	//We want to run all our queries to obtain the stats;
	
		$totalRecords = array();
	
		if($report_type=='group')
		{
			$totalRecords = getRegistryObjectKeysForGroup($objectGroup);
			$collectionRecords = getRegistryObjectCount(null, $group=$objectGroup, $className='Collection', $status = null);
			$partyRecords = getRegistryObjectCount(null, $group=$objectGroup, $className='Party', $status = null);		
			$activityRecords = getRegistryObjectCount(null, $group=$objectGroup, $className='Activity', $status = null);	
			$serviceRecords = getRegistryObjectCount(null, $group=$objectGroup, $className='Service', $status = null);					

		}else if ($report_type=='datasource')
		{
			$totalRecords = getRegistryObjectKeysForDataSource($dataSourceKey);
			$collectionRecords = getRegistryObjectCount($dataSourceKey, $group = null, $className='Collection', $status = null);
			$partyRecords = getRegistryObjectCount($dataSourceKey, $group = null, $className='Party', $status = null);		
			$activityRecords = getRegistryObjectCount($dataSourceKey, $group = null, $className='Activity', $status = null);	
			$serviceRecords = getRegistryObjectCount($dataSourceKey, $group = null, $className='Service', $status = null);				
		}	

		if(isset($totalRecords[0]))	{
			$totalCount = count($totalRecords);
		}else {
			$totalCount = 0;
		}
		
		//Let's get the records which have been viewed	for the given time frame
		$pageViewCount = 0;
		$totalcollectionViews = 0;			
		$partyViewCount = 0;
		$totalpartyViews = 0;			
		$activityViewCount = 0;
		$totalactivityViews = 0;			
		$serviceViewCount = 0;
		$totalserviceViews = 0;
		$totalPageViews = 0;			
		$sortOrder = "page_views";
		
		$class = "collection";
		$page_views_stats = getCollectionsViewed($groupingType,$groupingValue,$dateFrom,$dateTo,$sortOrder,$class);			
		if($page_views_stats)
		{
			$pageViewCount = count($page_views_stats);
			foreach($page_views_stats as $pageStats)
			{
				$totalcollectionViews = $totalcollectionViews + $pageStats['page_views'];
			}
		}	
				
		$class = "party";
		$party_views_stats = getCollectionsViewed($groupingType,$groupingValue,$dateFrom,$dateTo,$sortOrder,$class);			
		if($party_views_stats)
		{
			$partyViewCount = count($party_views_stats);
			foreach($party_views_stats as $pageStats)
			{
				$totalpartyViews = $totalpartyViews + $pageStats['page_views'];
			}					
		}	
				
		$class = "activity";
		$activity_views_stats = getCollectionsViewed($groupingType,$groupingValue,$dateFrom,$dateTo,$sortOrder,$class);	
		if($activity_views_stats)
		{
			$activityViewCount =  count($activity_views_stats);	
			foreach($activity_views_stats as $pageStats)
			{
				$totalactivityViews = $totalactivityViews + $pageStats['page_views'];
			}	
		}
				
		$class = "service";
		$service_views_stats = getCollectionsViewed($groupingType,$groupingValue,$dateFrom,$dateTo,$sortOrder,$class);		
		if($service_views_stats)
		{
			$serviceViewCount =  count($service_views_stats);
			foreach($service_views_stats as $pageStats)
			{
				$totalserviceViews = $totalserviceViews + $pageStats['page_views'];
			}	
		}		

		$totalRecordsViewed = $pageViewCount + $partyViewCount + $activityViewCount + $serviceViewCount;
		$totalPageViews = $totalcollectionViews + $totalpartyViews + $totalactivityViews + $totalserviceViews;
		$filterViewCount = $pageViewCount;
		if($pageViewCount > 3) $pageViewCount = 3;
		$filter='';
		$ga = new gapi(ga_email,ga_password);

		$i = 0;
		$j = 0;	
		$array['countries'] = array();
		$array['regions'] =  array();	
		$array['sources'] =  array();
		$array['search'] =  array();	
					
		while($i<$filterViewCount)
		{
			if($j>0) $filter .= " || ";
			$filter .= 'pagePath == /'.$page_views_stats[$i]['slug'];	
			$j++;
			if($j == 10){
				$ga->requestReportData(ga_profile_id,array('pagePath','country','region','source'),array('pageviews','uniquePageviews'),null,$filter,$dateFrom,$dateTo,1,100);		
				foreach($ga->getResults() as $results){ 
					if(isset($array['countries'][$results->getCountry()]))
						$array['countries'][$results->getCountry()]++;
					else{
						$array['countries'][$results->getCountry()] = 1;
					}
					if(isset($array['regions'][$results->getRegion()]))
						$array['regions'][$results->getRegion()]++;
					else{
						$array['regions'][$results->getRegion()] = 1;
					}
					if(isset($array['sources'][$results->getSource()]))
						$array['sources'][$results->getSource()]++;
					else{
						$array['sources'][$results->getSource()] = 1;
					}										
				}				
				$j = 0;
				$filter = '';
			}
			$i++;	
		}
		if(count($page_views_stats[0])>0)
		{
		foreach($page_views_stats as $page)
		{
			
		 	
			$search_terms = getSearchTermForPageView($page['slug'],$dateFrom,$dateTo);
			if($search_terms)
			{
				foreach($search_terms as $theTerm)
				{
					if(!isset($array['search'][$theTerm['search_term']][$theTerm['slug']]))	
					{
						$array['search'][$theTerm['search_term']][$theTerm['slug']]=$theTerm['slug']=1;
					}else{
						$array['search'][$theTerm['search_term']][$theTerm['slug']]++;
					}
				}

			}
		}
		}
		arsort($array['countries']);
		arsort($array['sources']);
		arsort($array['search']);		
	
		$sortOrder = "unique_page_views";
		$class= 'collection';
		$unique_views_stats = getCollectionsViewed($groupingType,$groupingValue,$dateFrom,$dateTo,$sortOrder,$class);	
		$uniqueViewCount = 0;
		$uniqueViewCount = count($unique_views_stats);
		if($uniqueViewCount > 3) $uniqueViewCount = 3;	

		$noResults = getNoResultSearches($dateFrom,$dateTo);
	

	}
	
}

// Buffer output for this page so that the 
// wcPleaseWait dialog remains active for the duration of the search.
ob_start();

$reportTitle = 'Organisational & Data Source Reporting.';
if( userIsORCA_SOURCE_ADMIN() || userIsORCA_ADMIN() )
{
	//$statisticsTitle = 'Administrative '.$statisticsTitle;
}

// Buffer output for this page so that the 
// wcPleaseWait dialog remains active for the duration of the search.
ob_start();

// =============================================================================
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
getAnalyticsTrackingCode(eGOOGLE_ANALYTICS_TRACKING_CODE_ORCA);

// BEGIN: Page Content
// =============================================================================

if(( strtoupper($org_report) != "GENERATE REPORT" &&  strtoupper($ds_report) != "GENERATE REPORT") || $errorMessages!='')
{ ?>
<form id="statisticsform" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" onsubmit="wcPleaseWait(true, 'Retrieving...')">
<fieldset>
<legend><?php printSafe($reportTitle)?></legend>
	<?php if( $errorMessages ) { ?>
	<table><tbody>
		<tr>
			<td></td>
			<td class="errorText"><p><?php print($errorMessages); ?></p></td>
		</tr>
	</tbody></table>
	<?php } ?>
<label class="stat">Reporting Date:</label><br /><br />
<label for="dateFrom" class="stat ">Date From</label> <?php drawDayMonthYearInput('dayFrom', getPostedValue('dayFrom'),'monthFrom', getPostedValue('monthFrom'),'yearFrom', getPostedValue('yearFrom') ) ?> <br /><br />
<label for="dateTo" class="stat">Date To</label> <?php drawDayMonthYearInput('dayTo', getPostedValue('dayTo'),'monthTo', getPostedValue('monthTo'),'yearTo', getPostedValue('yearTo') ) ?> <br /><br />
<label class="stat"  <?php print($reportdsLabelClass); ?>>Data Sources:</label><br />
<?php  

$rawResults = getDataSources(null, null);
$searchResults = array();

// Check the record owners.
if( $rawResults )
{
	foreach( $rawResults as $dataSource )
	{
		if( (userIsDataSourceRecordOwner($dataSource['record_owner'])) )
		{
			$searchResults[count($searchResults)] = $dataSource;
		}		
	}

	print('<select id= "source_key" name="source_key" style="margin: 2px; margin-left: 0px; width:400px;">'."\n");
	print('  <option value="">{Data Source}</option>'."\n");
	if( $searchResults )
	{
		foreach( $searchResults as $source )
		{
			$selected = "";
			if( $source['data_source_key'] == $dataSourceKey ){ $selected = ' selected="selected"'; }
			print('  <option value="'.esc($source['data_source_key']).'"'.$selected.'>'.esc($source['title']).'</option>'."\n");
		}
	}
	print('</select> <input type="submit" name="ds_report" value="Generate Report" />	<br /><br />'."\n");
}?>
<label class="stat"  <?php print($reportObjectLabelClass); ?>>Organisations:</label><br />
<?php  

//we might need to only get the groups for the above datasources ??
//work still to do !!!!!
$objectGroups = getObjectGroups();
print('<select id ="object_group" name="object_group" style="margin: 2px; margin-left: 0px; width:400px;">'."\n");
print('  <option value="">{Organisations}</option>'."\n");
if( $objectGroups )
{
	foreach( $objectGroups as $group )
	{
		$selected = "";
		if( $group['object_group'] == $objectGroup ){ $selected = ' selected="selected"'; }
		print('  <option value="'.esc($group['object_group']).'"'.$selected.'>'.esc($group['object_group']).'</option>'."\n");
	}
}
print('</select> <input type="submit" name="org_report" value="Generate Report" /><br /><br />'."\n");
?>
</fieldset>
</form>
<?php 
} 

if( (strtoupper($ds_report) == "GENERATE REPORT" ||  strtoupper($org_report) == "GENERATE REPORT")&& $errorMessages=='')
{ 
	if($report_type =='group')
	{
		$title = $objectGroup;
		$grouping = "Organisation";
	}
	if($report_type == "datasource")
	{
		$data_source = getDataSources($dataSourceKey,null);
		$title = $data_source[0]['title'];
		$grouping = "Datasource";		
	}
	?>
	<p>&nbsp;</p>
	<fieldset>
	<legend><?php echo $title; ?></legend>
	<p><strong>Reporting Date:</strong>&nbsp;&nbsp;<span class="reportDate"> <?php echo $dateString;?></span><br /></br /></p>
	<p class="reportText">Statistics for <?php echo $title;?> </p>
	<p class="reportText"><?php echo $grouping;?> Summary </p>
	<div class="reportDiv">
		<table class="reportTable" width="800">
			<tbody>
				<tr style="border:0px;">
					<td width="450">&nbsp;</td>
					<td width="75" align="center"  class="reportHeaderCell">Collections</td>
					<td width="75" align="center"  class="reportHeaderCell">Parties</td>
					<td width="75" align="center"  class="reportHeaderCell">Activities</td>
					<td width="75" align="center"  class="reportHeaderCell">Services</td>
					<td width="75" align="center"></td>
				</tr>
				<tr>
					<td class="reportGrey">Number of Records within your <?php echo $grouping;?>:</td>
					<td class="reportResultCell" ><?php echo $collectionRecords;?></td>
					<td class="reportResultCell" ><?php echo $partyRecords;?></td>
					<td class="reportResultCell" ><?php echo $activityRecords;?></td>
					<td class="reportResultCell" ><?php echo $serviceRecords;?></td>
					<td class="reportResultCell"><?php echo $totalCount;?></td>
				</tr>
				<tr>
					<td class="reportGrey">Number of your records being viewed:</td>
					<td class="reportResultCell" ><?php echo $filterViewCount;?></td>
					<td class="reportResultCell" ><?php echo $partyViewCount;?></td>
					<td class="reportResultCell" ><?php echo $activityViewCount;?></td>
					<td class="reportResultCell" ><?php echo $serviceViewCount;?></td>
					<td class="reportResultCell"><?php echo $totalRecordsViewed;?></td>
				</tr>		
				<tr>
					<td class="reportGrey">Number of times your records have been accessed:</td>
					<td class="reportResultCell" ><?php  echo $totalcollectionViews;?></td>
					<td class="reportResultCell" ><?php  echo $totalpartyViews;?></td>
					<td class="reportResultCell" ><?php  echo $totalactivityViews;?></td>
					<td class="reportResultCell" ><?php  echo $totalserviceViews;?></td>
					<td class="reportResultCell"><?php echo $totalPageViews;?></td>		
				</tr>						
			</tbody>
		</table>
		
			<br />
			<br />
		<table class="reportTable" width="800">
			<tbody>
				<tr style="border:0px">
				<td  width="450" >&nbsp;</td>
		<?php	$count=0;
				foreach($array['sources'] as $key => $value)
				{
					if($count<5){?>
					<td align="center"   class="reportHeaderCell"><?php echo $key;?></td>					
		<?php		}
					$count++;
				}		?>				
				</tr>
				<tr>
				<td class="reportGrey" >How users are finding your Records:</td>				
		<?php 	$count=0;
				foreach($array['sources'] as $key => $value)
				{ 	

					if($count<5){?>
					<td class="reportResultCell" width="75"><?php echo $value;?></td>					
		<?php 		}
					$count++;
				} 		?>							

				</tr>
			</tbody>
		</table>	
		<br />	
</div>
		<p class="reportText">RDA Search Summary: </p>
		<div class="reportDiv">
		<table class="reportTable" style="vertical-align: top" width="800">
		<tr><td style="vertical-align:top">
		<table class="reportTable" style="vertical-align:top">
			<tbody>
				<tr><td width="300" colspan="2" class="reportGrey" >Keyword users are using when searching RDA successfully for your records</td></tr>
				<?php 
				$outPutcount = 0;
				foreach($array['search'] as $key => $search)
				{
					if($outPutcount<3)
					{ ?>
						<tr><td width="20"></td><td class="reportResultCell" width="250"><?php echo $key;?> (<?php echo count($search);?>)</td></tr>			
	<?php 			}
					$outPutcount++;
				}
				?>
			
			</tbody>
		</table>
		</td>
		<td>		
		<table class="reportTable" style="vertical-align:top">
			<tbody>
				<tr><td width="300" colspan="2" class="reportGrey" >Keywords users are searching RDA which produce zero search results</td></tr>
				<?php 
				if(isset($noResults)&&count($noResults[0])>0)
				{
					
					foreach($noResults as $noResult)
					{
	?>
					<tr><td width="20"></td><td class="reportResultCell" width="250"><?php echo $noResult['search_term'];?>  (<?php echo $noResult['thecount'];?>) </td></tr>
	<?php					
					}
				}
				?>
			
			</tbody>
		</table></td></tr>
		</table>
</div>

		<p class="reportText">Collection Summary:</p>
		<div class="reportDiv">
		<table width="800">
		<tr style="vertical-align: top"><td>
		<table class="reportTable">
			<tbody>
				<tr><td width="300" colspan="2" class="reportGrey" >Collections viewed most</td><td></td></tr>
				<?php 
				if($pageViewCount > 0)
				{
					for($i=0;$i<$pageViewCount;$i++)
					{
					?>
						<tr><td width="20"></td><td class="reportResultCell" width="250"><?php if($page_views_stats[$i]['display_title']!=''){echo $page_views_stats[$i]['display_title'];} ?> </td><td class="reportResultCell"><?php echo $page_views_stats[$i]['page_views'] ?></td></tr>
					<?php 
					}
				}else{
				?>
						<tr><td width="20"></td><td class="reportResultCell" width="250">No pages viewed within the given timeframe</td></tr>				
		<?php 	}
				?>			
			</tbody>
		</table>
		</td>
		<td>		
		<table class="reportTable">
			<tbody>
				<tr><td width="300" colspan="2" class="reportGrey" >Collections viewed most by distinct users</td><td></td></tr>
				<?php 
				if($uniqueViewCount > 0)
				{
					for($i=0;$i<$uniqueViewCount;$i++)
					{
					?>
						<tr><td width="20"></td><td class="reportResultCell" width="250"><?php if($unique_views_stats[$i]['display_title']!=''){echo $unique_views_stats[$i]['display_title'];}else{echo $unique_views_stats[$i]['slug'];} ?> </td><td class="reportResultCell"><?php echo $unique_views_stats[$i]['unique_page_views'] ?></td></tr>
					<?php 
					}
				}
				?>				
			</tbody>
		</table></td></tr>
		</table>	
		<br />
				<p class="reportText">Presence:</p>		
		<div class="reportDiv">
<p class="reportText">Nationally:</p>		
		<br />
		<table class="reportTable">
			<tbody>
				<tr style="border:0px;align:center"><td width="200"></td>
				<td class="reportHeaderCell" width="50" align="center" >ACT</td>
				<td class="reportHeaderCell" width="50" align="center" >NSW</td>
				<td class="reportHeaderCell" width="50" align="center" >QLD</td>
				<td class="reportHeaderCell" width="50" align="center" >NT</td>
				<td class="reportHeaderCell" width="50" align="center" >WA</td>
				<td class="reportHeaderCell" width="50" align="center" >SA</td>
				<td class="reportHeaderCell" width="50" align="center" >VIC</td>
				<td class="reportHeaderCell" width="50" align="center" >TAS</td>	
				<td></td>			
				</tr>
				<tr><td class="reportGrey" width="200">State Presence:</td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['Australian Capital Territory'])) {$act = $array['regions']['Australian Capital Territory'];} else {$act = 0;} echo $act;?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['New South Wales'])) {$nsw= $array['regions']['New South Wales'];} else {$nsw = 0;} echo $nsw;?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['Queensland'])) {$qld =  $array['regions']['Queensland'];} else {$qld = 0;} echo $qld?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['Northern Territory'])) {$nt = $array['regions']['Northern Territory'];} else {$nt = 0;} echo $nt;?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['Western Australia'])) {$wa =  $array['regions']['Western Australia'];} else {$wa=0;} echo $wa;?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['South Australia'])) {$sa = $array['regions']['South Australia'];} else {$sa = 0;} echo $sa;?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['Victoria'])) {$vic = $array['regions']['Victoria'];} else {$vic = 0;} echo $vic;?></td>
				<td class="reportResultCell" width="50"><?php if(isset($array['regions']['Tasmania'])) {$tas = $array['regions']['Tasmania'];} else {$tas = 0;} echo $tas;?></td>
				<?php $totalNational = $act + $nsw + $qld + $nt + $wa + $sa + $vic + $tas; ?>
				<td class="reportResultCell" width="50"><?php echo $totalNational;?></td>				
				</tr>
			</tbody>
		</table>	
		<br />
		<p class="reportText">Internationally:</p>	
		<table class="reportTable">
			<tbody>
				<tr style="border:0px"><td width="200"></td>
		<?php 	$count = 0;
				foreach($array['countries'] as $key => $value)
				{ 	
					if($count<5) {?>
					<td class="reportHeaderCell" width="100" align="center" ><?php echo $key;?></td>					
		<?php 		}
					$count++;
				}	?>
				
				</tr>
				<tr><td class="reportGrey" width="200">Global Presence:</td>
		<?php 	$count = 0;
				foreach($array['countries'] as $key => $value)
				{		
					if($count<5) {?>
					<td class="reportResultCell" width="100"><?php echo $value;?></td>					
		<?php 		}
					$count++;
				} 		?>
				
				</tr>
			</tbody>
		</table>	
		</div>			
	</div>
	
	</fieldset>
	<?php 
}

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';

// Send the ouput from the buffer, and end buffering.
ob_end_flush();


?>
