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
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/manage_my_records.css');
// Page processing
// -----------------------------------------------------------------------------

$data_source_key = urldecode(getQueryValue('data_source'));
$this_url = eAPP_ROOT . "orca/manage/my_records.php?";

$errors = array();

// Get data sources which we have access to
$rawResults = getDataSources(null, null);
$dataSources = array();

if( $rawResults )
{
	foreach( $rawResults as $dataSource )
	{
		if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_QA()) )
		{
			$dataSources[] = $dataSource;
		}		
	}
}

// Allow user to browse to the appropriate data source
if (!$data_source_key)
{
	if (count($dataSources) == 1)
	{
		header("Location: " . $this_url . "data_source=" . rawurlencode($dataSources[0]['data_source_key']));
		die();
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';

//google chart
echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';

echo '<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/orca_dhtml.js"></script>
		<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/mmr_dhtml.js"></script>
		<input type="hidden" id="elementSourceURL" value="' . eAPP_ROOT . 'orca/manage/process_registry_object.php?" />';

//CHOSEN Javascript library for choosing data sources
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_javascript/chosen/chosen.css" />
		<script src="'. eAPP_ROOT.'orca/_javascript/chosen/chosen.jquery.js" type="text/javascript"></script>';


//FLEXIGRID
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_javascript/flexigrid/css/flexigrid.css" />
		<script src="'. eAPP_ROOT.'orca/_javascript/flexigrid/js/flexigrid.js" type="text/javascript"></script>';

//QTIP at COSI level
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'/_javascript/qtip2/jquery.qtip.css" />
		<script src="'. eAPP_ROOT.'/_javascript/qtip2/jquery.qtip.js" type="text/javascript"></script>';

//Specific MMR Styles
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_styles/mmr.css" />';


echo '<h1>Manage My Records</h1>';


if (!$data_source_key)
{
		displayMMRDataSourceSwitcher($dataSources);
}
else
{
	$dataSource = getDataSources($data_source_key, null);
	if(!(userIsDataSourceRecordOwner($dataSource[0]['record_owner']) || userIsORCA_QA()) )
	{
		die("<font color='red'>Error: Access Denied for Datasource</font>");
	}
	if (($dataSource && count($dataSource) === 1) || $data_source_key == "PUBLISH_MY_DATA" )
	{
		if (!$dataSource)
		{
			$dataSource = array(
								'data_source_key' => 'PUBLISH_MY_DATA',
								'qa_flag' => 't',
								'auto_publish' => 'f',
			);
		}
		else 
		{
			$dataSource = array_pop($dataSource);	
		}
			
		displayMMRDataSourceSwitcher($dataSources, $data_source_key);
		?>
		<input type="hidden" id="dataSourceKey" value="<?php echo $data_source_key; ?>" />
		<input type="hidden" id="reindexURL" value="<?php echo eAPP_ROOT;?>orca/services/indexer.php?dataSourceKey=<?php echo $data_source_key?>&task=indexDSo"/>
		<input type="hidden" id="clearIndexURL" value="<?php echo eAPP_ROOT;?>orca/services/indexer.php?dataSourceKey=<?php echo $data_source_key?>&task=clearDS"/>
		<input type="hidden" id="generateCacheURL" value="<?php echo eAPP_ROOT;?>orca/maintenance/runTasks.php?data_source=<?php echo $data_source_key?>&task=generate_cache"/>
		<input type="hidden" id="checkQualityURL" value="<?php echo eAPP_ROOT;?>orca/services/indexer.php?dataSourceKey=<?php echo $data_source_key?>&task=checkQuality"/>
		
		<div id="mmr_datasource_alert" style="display:none;">
			<div id="mmr_datasource_alert_title" class="clearfix">
				<div style="float:left;"><img src="<?php echo eAPP_ROOT; ?>_images/_logos/logo_ANDS.gif" alt="Australian National Data Service Online Services"></div>
				<div style="margin-top:18px; margin-left:5px; float:left;">Message</div>
			</div>
			<div id="mmr_datasource_alert_msg">
			</div>
			<input type="button" onclick="location.reload();" value="Continue" style="vertical-align:bottom;"/>
		</div>
		
		<?php 
		

		/**
			NEW
		**/
		?>


		<?php
			$json = json_decode(getDataSourceStatuses($data_source_key));
			//We are now extracting the valuable data out from json and put them into an array
			//of form $status['DRAFT'] => 4 or something
			$i=0;$status=array();$placeholder = $json->{'facet_counts'}->{'facet_fields'}->{'status'};
			for($i=0;$i<sizeof($placeholder);$i+=2){
				//placeholder[$i]='status'
				$status_class_json = json_decode(getClassesByStatus($data_source_key, $placeholder[$i]));
				//var_dump($status_class_json);
				$status_class_facet = $status_class_json->{'facet_counts'}->{'facet_fields'}->{'class'};
				for($j=0;$j<sizeof($status_class_facet);$j+=2){
					$status[$placeholder[$i]][$status_class_facet[$j]] = $status_class_facet[$j+1];

				}
				//var_dump($status_class_facet);
				$status[$placeholder[$i]]['count'] = $placeholder[$i+1];
			}
			$i=0;$classes=array();$placeholder = $json->{'facet_counts'}->{'facet_fields'}->{'class'};
			for($i=0;$i<sizeof($placeholder);$i+=2){
				$classes[$placeholder[$i]] = $placeholder[$i+1];
			}
			//var_dump($classes);
			array_multisort($status,SORT_DESC);
			//var_dump($status);

			//doing the same on quality levels
			$json = json_decode(getQALevels($data_source_key));
			$i=0;$qa_levels=array();$placeholder = $json->{'facet_counts'}->{'facet_fields'}->{'quality_level'};
			for($i=0;$i<sizeof($placeholder);$i+=2){
				$qa_levels[$placeholder[$i]] = $placeholder[$i+1];
			}
			//array_multisort($qa_levels,SORT_DESC);
			//var_dump($qa_levels);
		?>

		<div id="tabs">
		    <ul class="tab-list">
		    	<li><a href="javascript:void(0);" title="All" class="tab active-tab" name="All">All Records</a></li>
		    	<?php
		    		foreach($status as $key=>$s){
		    			if($s['count']!=0){
		    				echo '<li><a href="javascript:void(0);" title="'.$s['count'].' Records" class="tab tip" name="'.$key.'">'.str_replace('_', ' ', $key).'</a><li>';
		    			}else{
		    				echo '<li><a href="javascript:void(0);" title="'.$s['count'].' Records" class="tab tip inactive" name="'.$key.'">'.str_replace('_', ' ', $key).'</a><li>';
		    			}
		    		}
		    	?>
		    	<li class="rightTab"><a href="javascript:void(0);" id="indexDS" class="smallIcon icon2s tip borderless" tip="ReIndex"><span></span></a></li>
		    </ul>

		    <?php
		    	/*
				 * Setting up variables for button configuration in JavaScript
				 */
		    	echo '<div class="hide" id="orcaQA">';
		    		if(userisORCA_QA()) echo 'yes'; else echo 'no';
		    	echo '</div>';
		    	echo '<div class="hide" id="orcaLIASON">';
		    		if(userIsORCA_LIAISON()) echo 'yes'; else echo 'no';
		    	echo '</div>';
		    	echo '<div class="hide" id="DS_QA_flag">';
		    		if($dataSource['qa_flag']=='t') echo 'yes'; else echo 'no';
		    	echo '</div>';


		    	//Sort it by this order
		    	$order = array('MORE_WORK_REQUIRED', 'DRAFT','SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS', 'APPROVED', 'PUBLISHED');
		    	$sorted = array();
		    	foreach($order as $o) $sorted[$o]=$status[$o];
				$status = $sorted;

				
				//summary
				//var_dump($status);
				$class_names = array('collection', 'party', 'activity', 'service');
				
				echo '<div class="tab-content statusview"><h3><button id="toggleSummaryTable">-</button> Summary</h3></div>';
				echo '<div id="All_statusview" class="tab-content statusview">';
				echo '<table id="summaryTable">';
				echo '<tr><td></td>';//empty
				foreach($status as $status_name=>$array){
					if($array['count']>0){
						echo '<td>'.str_replace('_', ' ', $status_name).'</td>';
					}
					
				}
				echo '</tr>';
				
				foreach($class_names as $class_name){
					echo '<tr>';
					echo '<td>'.$class_name.'</td>';
					foreach($status as $status_name=>$array){
						if($array['count']>0){
							echo '<td>'.$array[$class_name].'</td>';
						}
						
					}
					echo '</tr>';
				}

				echo '</table>';
				echo '</div>';


				echo '<div class="tab-content statusview"><h3><button id="toggleDetailTables">-</button> Details</h3></div>';
				echo '<div id="detailTables">';
				//display 2 tables and 1 graph for each of the status
		    	foreach($status as $status_name=>$array){
		    		$count = $status_name['count'];
		    		$tableClass = '';$displayTable=false;
		    		if($status_name=='MORE_WORK_REQUIRED'){//only visible if there are records of this status
		    			if($count==0) $tableClass='hide';
		    			$displayTable=true;
		    		}elseif($status_name=='DRAFT'){//all users can review their drafts
		    			$displayTable=true;
		    		}else if($status_name=='SUBMITTED_FOR_ASSESSMENT'){
		    			if($count>0 || $dataSource['qa_flag'] == 't'){$displayTable=true;}
		    		}elseif($status_name=='ASSESSMENT_IN_PROGRESS'){
		    			if($count>0 || $dataSource['qa_flag'] == 't'){$displayTable=true;}
		    		}elseif($status_name=='APPROVED'){
		    			if($count>0 || $dataSource['auto_publish'] == 't'){$displayTable=true;}
		    		}elseif($status_name=='PUBLISHED'){//anyone can see published records
		    			$displayTable=true;
		    		}

		    		if($displayTable){
			    		echo '	<div id="'.$status_name.'" class="tab-content '.$tableClass.' statusview">
									<table class="mmr_table" status="'.$status_name.'" count="'.$count.'"><tr><td>Loading Table...</td></tr></table>
								</div>';
						echo '<div id="'.$status_name.'_qaview" class="tab-content qaview">Loading Graph...</div>';
					}
					foreach($qa_levels as $key=>$ql){
		    			echo '	<div class="tab-content qaview">
								<table class="mmr_table qa_table" ql="'.$key.'" status="'.$status_name.'" count="'.$ql.'"><tr><td>Loading Table...</td></tr></table>
								</div>';
		    		}
		    	}
		    	echo '</div>';
				/*
				 * All of em
				 */
		    	echo '<div id="All_qaview" class="tab-content qaview"></div>';

		    	foreach($qa_levels as $key=>$l){
		    		echo '	<div class="tab-content qaview">
							<table class="mmr_table as_qa_table" ql="'.$key.'" status="All" count="'.$l.'"><tr><td>Loading Graph...</td></tr></table>
							</div>';
		    	}


		    ?>

		</div>

    <div class="clearfix"></div>

<?php
	displayMMRNewRecord();

		/**
			OLD
		**/

		//echo '<hr/>';
		/*
		$draft_array = getDraftRegistryObject(null, $data_source_key);
		$approved_array = searchRegistry('', '', $data_source_key, null, null, null, APPROVED, null);
		$approved_array = record2MMRRecordSet(($approved_array ? $approved_array : array()));
		
		$published_array = searchRegistry('', '', $data_source_key, null, null, date('Y-m-d H:i:s', time() - 7*24*60*60), PUBLISHED, null); // last 7 days
		$published_array = record2MMRRecordSet(($published_array ? $published_array : array()));
		$draft_record_set = array(
								MORE_WORK_REQUIRED => array(),
								DRAFT => array(),
								SUBMITTED_FOR_ASSESSMENT => array(),
								ASSESSMENT_IN_PROGRESS => array(),
							);
							
		if(is_array($draft_array))
		{
			$record_set = draft2MMRRecordSet($draft_array);
		}
		else
		{
			$record_set = draft2MMRRecordSet();
		}
				
		foreach ($record_set AS $record)
		{
			if (isset($draft_record_set[$record['status']]))
			{
				$draft_record_set[$record['status']][] = $record;
			}
			else
			{
				$draft_record_set['DRAFT'][] = $record;
			}	
		}
		
		
		/*
		 * More Work Required Records
		 * - only visible if there are records of this status
		 *
		if (count($draft_record_set[MORE_WORK_REQUIRED]) > 0)
		{	
			displayMMRRecordTable(MORE_WORK_REQUIRED, $draft_record_set[MORE_WORK_REQUIRED], array("<span style='font-weight:normal;'>edit these records and resubmit them for assessment</span>"), true);
		}
		
		/*
		 * DRAFT Records
		 * - All users can delete/submit for review
		 *
		
		$buttons = array();
		if ($dataSource['qa_flag'] == 't')
		{
			$buttons[] = "<input type='submit' name='SUBMIT_FOR_ASSESSMENT' value='Submit for Assessment' disabled='disabled' />";
		} 
		else
		{
			$buttons[] = "<input type='submit' name='APPROVE' value='Approve' disabled='disabled' />";
		}
		
		$buttons[] = "<input type='submit' name='DELETE_DRAFT' value='Delete' disabled='disabled' />";
		
		displayMMRRecordTable(DRAFT, $draft_record_set[DRAFT], $buttons, true);
		
		/*
		 * SUBMITTED FOR ASSESSMENT Records
		 *
		
		$buttons = array();
		// Minimum level of access for this action
		if (userIsORCA_QA()) 
		{
			$buttons[] = "<input type='submit' name='START_ASSESSMENT' value='Start Assessment' disabled='disabled' />";
		}
		
		if (userIsORCA_LIAISON())
		{
			$buttons[] = "<input type='submit' name='BACK_TO_DRAFT' value='Revert to Draft' disabled='disabled' />";
		}
				
		if (count($draft_record_set[SUBMITTED_FOR_ASSESSMENT]) > 0 || $dataSource['qa_flag'] == 't')
		{
			displayMMRRecordTable(SUBMITTED_FOR_ASSESSMENT, $draft_record_set[SUBMITTED_FOR_ASSESSMENT], $buttons, true);
		}
		
		/*
		 * ASSESSMENT IN PROGRESS Records
		 *
		
		$buttons = array();
		if (userIsORCA_QA())
		{
			$buttons[] = "<input type='submit' name='APPROVE' value='Approve' disabled='disabled' />";
			$buttons[] = "<input type='submit' name='MORE_WORK_REQUIRED' value='More Work Required' disabled='disabled' />";
		}
		if (count($draft_record_set[ASSESSMENT_IN_PROGRESS]) > 0 || $dataSource['qa_flag'] == 't')
		{
			displayMMRRecordTable(ASSESSMENT_IN_PROGRESS, $draft_record_set[ASSESSMENT_IN_PROGRESS], $buttons, true);
		}
		
		/*
		 * APPROVED Records
		 *
		
		if (count($approved_array) > 0 || $dataSource['auto_publish'] == 't') // manually
		{
			$buttons = array();
			$buttons[] = "<input type='submit' name='PUBLISH' value='Publish' disabled='disabled' />";
			$buttons[] = "<input type='submit' name='DELETE_RECORD' value='Delete' disabled='disabled' />";
			
			displayMMRRecordTable(APPROVED, $approved_array, $buttons, false);
		}
		
		/*
		 * PUBLISHED Records (last 30 days)
		 *

		$buttons = array();
		$buttons[] = "<input type='submit' name='DELETE_RECORD' value='Delete' disabled='disabled' />";
			
		displayMMRRecordTable(PUBLISHED, $published_array, $buttons, false);
		*/
		
		
		
		//displayMMRRecordTable("DRAFT", $record_set, array("buttons"), true);
		
	}
	else
	{
		$errors[] = "Unable to select the Data Source: " . $data_source_key	. ". Please go back and try again.";
		displayMMRErrors();
	}
	
}



function draft2MMRRecordSet(array $record_set = array())
{
	$return = array();
	
	usort($record_set, "compareByDateModified");
	
	foreach ($record_set AS $record)
	{
		$return[] = array(
						"key" => $record['draft_key'],
						"class" => ucfirst($record['class']),
						"title" => $record['registry_object_title'],
						"created" => date("g:i a, j M y", strtotime($record['date_modified'])),
						"last_changed_by" => ($record['draft_owner'] == "SYSTEM" ? "Harvester" : elipsesLimit($record['draft_owner'],15)),
						"feed_type" => ($record['draft_owner'] == "SYSTEM" ? "Harvest" : "Manual"),
						"error_count" => $record['error_count'],
						"quality_test_result" => $record['quality_test_result'],
						"warning_count" => $record['warning_count'],
						"flagged" => ($record['flag'] == 't' ? true : false),
						"status" => $record['status'],
					);		
	}
	
	return $return;
}

function record2MMRRecordSet(array $record_set = array())
{
	$return = array();
	
	usort($record_set, "compareByCreatedTime");
	
	foreach ($record_set AS $record)
	{
		$record = getRegistryObject($record['registry_object_key'], true);
		$record = $record[0];
		$return[] = array(
						"key" => $record['registry_object_key'],
						"class" => ucfirst($record['registry_object_class']),
						"title" => getOrderedNames($record['registry_object_key']),
						"created" => date("g:i a, j M y", strtotime($record['created_when'])),
						"created_time" => strtotime($record['created_when']),
						"last_changed_by" => "",
						"feed_type" => ($record['record_owner'] == "SYSTEM" ? "Harvest" : "Manual"),
						"error_count" => $record['error_count'],
						"quality_test_result" => $record['quality_test_result'],
						"warning_count" => $record['warning_count'],
						"flagged" => ($record['flag'] == 't' ? true : false),
						"status" => $record['status'],
					);		
	}


	return $return;
}

function compareByDateModified($x, $y)
{	
 if ( strtotime($x['date_modified']) == strtotime($y['date_modified']) )
 {
  return (strnatcasecmp($x['draft_key'],$y['draft_key']) < 0 ? 1 : -1);
 }
 else if ( strtotime($x['date_modified']) < strtotime($y['date_modified']) )
  return 1;
 else
  return -1;
}

function compareByCreatedTime($x, $y)
{	
 if ( strtotime($x['created_when']) == strtotime($y['created_when']) )
  return (strnatcasecmp($x['registry_object_key'],$y['registry_object_key']) < 0 ? 1 : -1);
 else if ( strtotime($x['created_when']) < strtotime($y['created_when']) )
  return 1;
 else
  return -1;
}


function dePluralise($word, $related_array)
{
	if (count($related_array) == 1 && substr($word, -1) == "s")
	{
		return substr($word, 0, -1);
	}
	else
	{
		return $word;
	}
}

function elipsesLimit($string, $maxlen)
{
	if (strlen($string) > $maxlen)
	{
		return substr($string, 0, ($maxlen-3)) . "...";
	}
	else
	{
		return $string;
	}
}



function displayMMRRecordTable($status, array $record_set = array(), array $button_set = array(), $in_draft = true)
{
	
	?>
	
	<table style="width:1050px;" class="mmr_expandable_table" id="mmr_record_table_<?php echo $status;?>">

		<tr>
			<td rowspan="2" style="background-color:<?php echo getRegistryObjectStatusColor($status);?>; padding:0; width:20px; margin:0;"></td>
			<td colspan="10" class="resultListHeader">
			
					<div style="float:left;">
					<?php $status_info = getRegistryObjectStatusInfo($status);
					
						echo $status_info['display'] . " (" . count($record_set) . " " . dePluralise("records", $record_set) . " found)";
						
					?>
					
					</div>
					<div style="float:right;" class="mmr_button_row">
					<?php 
						foreach ($button_set AS $button):
							echo $button;
						endforeach;
					?>
					</div>
			</td>
		</tr>
		<?php if (count($record_set) > 0): ?>
		<tr>
			<td class="resultListHeader" style="width:80px;"><input type="button" class="mmr_select_all_button" value="select all" /></td>
			<td class="resultListHeader" style="width:100px;">Record Key</td>
			<td class="resultListHeader" style="width:200px;">Name/Title</td>
			<td class="resultListHeader" style="width:125px;">Last Changed</td>
			<td class="resultListHeader" style="width:55px;">Class</td>
			<td class="resultListHeader" style="width:55px;">Errors / Warnings</td>
			<td class="resultListHeader center" style="width:70px;">Options</td>
			<td class="resultListHeader center" style="width:30px;">Flag</td>
			<td class="resultListHeader" style="width:55px;">Feed Type</td>
			<td class="resultListHeader" style="width:82px;">Status</td>
		</tr>
		<?php endif; ?>
		
		<tr style="display:none;" class="mmr_select_banner">
			<td colspan="11" class="mmr_select_message">
				There are more records in this category that are not visible. Do you want to select these records too?
			</td>
		</tr>
	
		<?php for ($x=0; $x < count ($record_set); $x++): ?>
		<?php $record = $record_set[$x]; ?>
		<?php if (in_array($status, array(DRAFT, MORE_WORK_REQUIRED, PUBLISHED, APPROVED))) { $readOnly = ""; } else { $readOnly = ($record['feed_type'] == 'Harvest' ? 'harvested=true&' : '') . "readOnly&"; } ?>
		<tr class="record_row<?php echo ($record['error_count']>0?' erroneous':'');?>" id="<?php echo $status . "_row_" . ($x+1);?>" name="<?php echo rawurlencode($record['key']);?>">
			<td class="rowNumbers"><?php echo $x+1;?></td>
			<td class="rowSelector"><input type="checkbox" class="mmr_select_box" /></td>
			<?php 
			if ($in_draft) 
			{
				if ($record['feed_type'] == 'Harvest')
				{
					$onClickLink = 'if (confirm(\'The record you have selected to edit has been entered into the ANDS Registry via a harvest. Editing this record will only change the record in the ANDS registry and not in the original harvested source. Do you still want to continue?\')) { window.location = \'' . eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?'.$readOnly.'data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])) . '\'; }';
				}
				else
				{
					$onClickLink = "window.location = '" . eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?'.$readOnly.'data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])) . "'";
				}
			}
			else 
			{
				$onClickLink = "window.location = '" . eAPP_ROOT .'orca/view.php?key='.esc(rawurlencode($record['key'])) . "'";
			}
			?>
			<td><div style="overflow:hidden;white-space: nowrap; width:100px;"><a <?php echo 'onclick="'.$onClickLink.'"'; ?> class="nodecor" title="<?php echo htmlentities($record['key']);?>"><?php echo htmlentities($record['key']);?></a></div></td>
			<td><div style="overflow:hidden; white-space: nowrap; width:200px;"><a <?php echo 'title="'.$record['title'].'" onclick="'.$onClickLink.'"'; ?> class="nodecor"><?php echo $record['title'];?></a></div></td>

			<td><?php echo $record['created']; if ($record['last_changed_by'] != '') { echo "<br/><span class='mmr_changed_by'>by: " . $record['last_changed_by'] . "</span>"; }?></td>
			<td><?php echo $record['class'];?></td>
			<td><span class="mmr_infoControl">
				<?php if ($record['warning_count'] > 0):?>
					<img src="<?php echo eAPP_ROOT . "orca/_images/required_icon.png"; ?>" /> 
				<?php endif; ?>
				<?php if ($record['error_count'] > 0):?>
					<img src="<?php echo eAPP_ROOT . "orca/_images/error_icon.png"; ?>" /> 
				<?php endif; ?>
				<?php echo str_replace(array("&lt;i&gt;","&lt;/i&gt;"),array("<i>","</i>"),$record['quality_test_result']); ?></span></td>
			<td>
				<?php 
	
				// data source administrator should not be able to delete records in the:
				// Submitted for Assessment or Assessment in Progress stages
		      	if ($in_draft) {
		      		
		      		if (in_array($status, array(DRAFT, MORE_WORK_REQUIRED)))
		      		{
		      			print('    <a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?'.($record['feed_type'] == 'Harvest' ? 'harvested=true&' : '') . 'readOnly&data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'" title="View this Record in Read Only mode"><img src="'.(eAPP_ROOT . "orca/_images/preview_disabled.png").'" width="15px" height="15px" /></a>&nbsp;');
		      			
		      			if ($record['feed_type'] == 'Harvest')
		      			{
		      				print('    <a onClick="if (confirm(\'The record you have selected to edit has been entered into the ANDS Registry via a harvest. Editing this record will only change the record in the ANDS registry and not in the original harvested source. Do you still want to continue?\')) { window.location = \''.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'\'; }" title="Modify this Record"><img src="'.(eAPP_ROOT . "orca/_images/edit.png").'" width="15px" height="15px" /></a>&nbsp;');
		      			}
		      			else
		      			{
		      				print('    <a onClick="window.location = \''.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'\';" title="Modify this Record"><img src="'.(eAPP_ROOT . "orca/_images/edit.png").'" width="15px" height="15px" /></a>&nbsp;');
		      			}
		      			
		      			print('    <a onClick="if (confirm(\'You are about to delete 1 record. This record will be permanently deleted and cannot be restored. Do you want to continue?\')) { window.location.href=\''.eAPP_ROOT .'orca/manage/process_registry_object.php?task=delete&data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'\'; }" title="Delete this Draft" style="cursor:pointer;"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>');
		      	
		      		}
		      		else
		      		{
		      			print('    <a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?'.($record['feed_type'] == 'Harvest' ? 'harvested=true&' : '') . 'readOnly&data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'" title="View this Record in Read Only mode"><img src="'.(eAPP_ROOT . "orca/_images/preview.png").'" width="15px" height="15px" /></a>&nbsp;');
		      			print('    <a onClick="if (confirm(\'Cannot edit a record that has already been submitted for assessment. Open in Read-Only Mode instead?\')) { window.location = \"'.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?'.($record['feed_type'] == 'Harvest' ? 'harvested=true&' : '') . 'readOnly&data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'\"; }" title="Already submitted! View this record in Read Only mode?"><img src="'.(eAPP_ROOT . "orca/_images/edit_disabled.png").'" width="15px" height="15px" /></a>&nbsp;');
						print('    <a onClick="alert(\'This record has already been submitted for assessment and cannot be deleted.\')" title="Delete this Draft" style="cursor:pointer;"><img src="'.(eAPP_ROOT . "orca/_images/bin_disabled.png").'" width="15px" height="15px" /></a>');
		      		
		      		}
		      		
		      	} else {

		      		print('    <a href="'.eAPP_ROOT.'orca/view.php?key='.esc(rawurlencode($record['key'])).'" title="View this record in ORCA"><img src="'.(eAPP_ROOT . "orca/_images/preview.png").'" width="15px" height="15px" /></a>&nbsp;');
		      		
		      		if ($record['feed_type'] == 'Harvest')
		      		{
		      			print('    <a onClick="if (confirm(\'The record you have selected to edit has been entered into the ANDS Registry via a harvest. Editing this record will only change the record in the ANDS registry and not in the original harvested source. Do you still want to continue?\')) { window.location = \''.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?data_source='.rawurlencode(getQueryValue('data_source')).'&key='.esc(rawurlencode($record['key'])).'\'; }" title="Modify this Record"><img src="'.(eAPP_ROOT . "orca/_images/edit.png").'" width="15px" height="15px" /></a>&nbsp;');
		      		}
		      		else 
		      		{
		      			print('    <a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($record['class']).'_registry_object.php?data_source='.rawurlencode(getQueryValue('data_source')).'&key='.rawurlencode($record['key']).'" title="Modify this Record"><img src="'.(eAPP_ROOT . "orca/_images/edit.png").'" width="15px" height="15px" /></a>&nbsp;');
		      		}
		      		//print('    <a href="'.eAPP_ROOT .'orca/admin/registry_object_delete.php?key='.esc(rawurlencode($record['key'])).'" title="Delete this Record"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>');
					echo'    <a href="'.eAPP_ROOT .'orca/admin/registry_object_delete.php?key='.esc(rawurlencode($record['key'])).'" title="Delete this Record" onClick="return confirmSubmit(\'You are about to delete 1 record. Do you want to continue?\')"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>';

		      	}
				
				?>		
			</td>
			<td class="mmr_flag center<?php if ($in_draft) { echo " is_draft"; } ?>">
						<img src="<?php echo eAPP_ROOT . "orca/_images/star_grey.png";?>" class="not_flagged <?php if ($record['flagged']) { echo "hide"; } ?>"/>
						<img src="<?php echo eAPP_ROOT . "orca/_images/star.png";?>" class="flagged <?php if (!$record['flagged']) { echo "hide"; } ?>"/>
			</td>

			<td class="mmr_feed_type"><?php echo $record['feed_type'];?></td>
			<td class="center mmr_nohighlight" style="background-color:<?php echo getRegistryObjectStatusColor($status); ?>; color:white;"><?php $text = getRegistryObjectStatusInfo($status); echo $text['display']; ?></td>
		</tr>
		<?php endfor; ?>
		
		</tbody>
	</table>
	
	
	
	<?php 
		
}




function displayMMRErrors()
{
	global $errors;
	
	if (sizeof($errors) > 0): ?>
			<table class="formTable"> 
			<tbody> 
				<tr> 
					<td></td> 
					<td class="errorText">
						<?php foreach ($errors AS $error) {
							print($error . "<br/>");
						}?>
						</td> 
				</tr> 
			</tbody> 
			</table>
	<?php 
	endif;
			
	$errors = array();
}

function displayMMRNewRecord()
{
	?>
	<div>	
		<label>Add New  </label>
		<select id="addNewSelect" onChange="$(window.location).attr('href',$('#addNewSelect').val())">
			<option value=""></option>
			<option value="add_collection_registry_object.php">Collection</option>
			<option value="add_party_registry_object.php">Party</option>
			<option value="add_activity_registry_object.php">Activity</option>
			<option value="add_service_registry_object.php">Service</option>
		</select>
		<label> record  </label>

	</div>
	<?php 
}

/*
 * function that I used (Minh)
 */
function getDataSourceStatuses($dataSourceKey){
	global $solr_url;
	$q = 'data_source_key:("'.$dataSourceKey.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key'
	);
	$extra = '&facet=true&facet.field=status&&facet.field=class&facet.limit=-1&facet.mincount=0';
	$content = solr($solr_url, $fields, $extra);
	return $content;
}

function getQALevels($dataSourceKey){
	global $solr_url;
	$q = 'data_source_key:("'.$dataSourceKey.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key'
	);
	$extra = '&facet=true&facet.field=quality_level&facet.limit=-1&facet.mincount=0';
	$content = solr($solr_url, $fields, $extra);
	return $content;
}




function getClassesByStatus($data_source_key, $status){
	global $solr_url;
	$q = '+data_source_key:("'.$data_source_key.'") +status:("'.$status.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key'
	);
	$extra = '&facet=true&facet.field=class&facet.limit=-1&facet.mincount=0';
	$content = solr($solr_url, $fields, $extra);
	return $content;
}
function displayMMRDataSourceSwitcher(array $dataSources = array(), $selected_key = '')
{
	if (userIsORCA_ADMIN())
	{
		$dataSources[] = array('data_source_key'=>'PUBLISH_MY_DATA', 'title'=>'Publish My Data (ORCA Admin View)');
	}
	
	?>

		
		
		<form id="data_source_history_form" name="data_source_history_form" action="my_records.php" method="get">
			<div id="select_ds_container">
				<?php if ($selected_key == ''):?>
					<div class="content_block">Select the Data Source you wish to manage:</div>
				<?php else:?>
					<div class="content_block">Managing My Records for:</div>
				<?php endif;?>
				<div class="content_block">
					<select data-placeholder="Choose a Datasource" name="data_source" id="data_source" style="width:300px;" onchange="this.form.submit();" class="chzn-select" tab-index="2">
					<option value=""></option>
					<?php
						// Present the results.
						for( $i=0; $i < count($dataSources); $i++ ){
							$dataSourceKey = $dataSources[$i]['data_source_key'];
							$dataSourceTitle = $dataSources[$i]['title'];	
							print("<option value=\"".$dataSourceKey."\"" . ($selected_key == $dataSourceKey ? " selected" : "").">".esc($dataSourceTitle)."</option>\n");
						}

					?>
					</select>
				</div>

				
				<div class="content_block">
					<div class="buttons">
						<a href="javascript:void(0);" class="button left pressed viewswitch" name="statusview">Status</a><a href="javascript:void(0);" class="button right viewswitch"name = "qaview">Quality</a>
					</div>
				</div>
				<div class="content_block">
					<a href="<?php echo eAPP_ROOT . "orca/admin/data_source_view.php?data_source_key=" . rawurlencode($selected_key); ?>">Manage this Data Source</a>
				</div>
				<div class="content_block">
					<a class="pop" href="#" title="This tool allows you to view and manage the records which you have recently created, edited or harvested.">(more details)</a>
					<!--a href="" id="mmr_information_show">(more details)</a-->
				</div>
			</div>

			<div class="clearfix"></div>


			<div id="mmr_datasource_information" class="hide">

			 <a href="" id="mmr_information_hide">Hide Information</a>
			 	<div id="mmr_ds_moredetails">
					<ul style="padding-left:40px;">
						<li>
							This tool allows you to view and manage the records which you have recently created, edited or harvested. 
						</li>
						<?php if (isset($dataSource['qa_flag']) && $dataSource['qa_flag'] == 't'):?>
						<li>
							Records entered into the ANDS registry under the data source '<?php echo (isset($dataSource['title']) ? $dataSource['title'] : $dataSource['data_source_key']); ?>' need to be assessed and approved by ANDS staff. 
							Please contact your ANDS client liaison officer for more information. 
						</li>
						<?php endif; ?>
						<?php if (isset($dataSource['auto_publish']) && $dataSource['auto_publish'] == 't'): // manually ?>
						<li>
							Your data source administrator currently has this data source set to 'Manually Publish Records'. Records will need to be manually published from this screen once approved by ANDS.  
						</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>

		</form>

		<?php 
}