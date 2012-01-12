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

echo '<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/orca_dhtml.js"></script>
		<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/mmr_dhtml.js"></script>
		<input type="hidden" id="elementSourceURL" value="' . eAPP_ROOT . 'orca/manage/process_registry_object.php?" />';

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
		<div id="mmr_datasource_information">
		 <a href="" id="mmr_information_hide">Hide Information</a>
		 <a href="<?php echo eAPP_ROOT . "orca/admin/data_source_view.php?data_source_key=" . rawurlencode($data_source_key); ?>" id="mmr_manage_data_source">Manage this Data Source</a>
			<div>
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
		 */
		if (count($draft_record_set[MORE_WORK_REQUIRED]) > 0)
		{	
			displayMMRRecordTable(MORE_WORK_REQUIRED, $draft_record_set[MORE_WORK_REQUIRED], array("<span style='font-weight:normal;'>edit these records and resubmit them for assessment</span>"), true);
		}
		
		/*
		 * DRAFT Records
		 * - All users can delete/submit for review
		 */
		
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
		 */
		
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
		 */
		
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
		 */
		
		if (count($approved_array) > 0 || $dataSource['auto_publish'] == 't') // manually
		{
			$buttons = array();
			$buttons[] = "<input type='submit' name='PUBLISH' value='Publish' disabled='disabled' />";
			$buttons[] = "<input type='submit' name='DELETE_RECORD' value='Delete' disabled='disabled' />";
			
			displayMMRRecordTable(APPROVED, $approved_array, $buttons, false);
		}
		
		/*
		 * PUBLISHED Records (last 30 days)
		 */

		$buttons = array();
		$buttons[] = "<input type='submit' name='DELETE_RECORD' value='Delete' disabled='disabled' />";
			
		displayMMRRecordTable(PUBLISHED, $published_array, $buttons, false);
		
		
		
		displayMMRNewRecord();
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
			<td><div style="overflow:hidden;white-space: nowrap; width:100px;"><a <?php echo 'onclick="'.$onClickLink.'"'; ?> class="nodecor" title="<?php echo $record['key'];?>"><?php echo $record['key'];?></a></div></td>
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
				<?php echo $record['quality_test_result']; ?></span></td>
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
		      			print('    <a onClick="alert(\'This record has already been submitted for assessment and cannot be deleted." title="Delete this Draft" style="cursor:pointer;"><img src="'.(eAPP_ROOT . "orca/_images/bin_disabled.png").'" width="15px" height="15px" /></a>');

		      		
		      		
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

function displayMMRDataSourceSwitcher(array $dataSources = array(), $selected_key = '')
{
	if (userIsORCA_ADMIN())
	{
		$dataSources[] = array('data_source_key'=>'PUBLISH_MY_DATA', 'title'=>'Publish My Data (ORCA Admin View)');
	}
	
	?>
		
		
		<form id="data_source_history_form" name="data_source_history_form" action="my_records.php" method="get">

	<?php if ($selected_key == ''):?>
		Select the Data Source you wish to manage:
	<?php else:?>
		Managing My Records for:
	<?php endif;?>
			<select name="data_source" id="data_source" style="width:300px;" onchange="this.form.submit();">
			<option value=""></option>
			<?php
				
				// Present the results.
				for( $i=0; $i < count($dataSources); $i++ )
				{
					$dataSourceKey = $dataSources[$i]['data_source_key'];
					$dataSourceTitle = $dataSources[$i]['title'];	
					print("<option value=\"".$dataSourceKey."\"" . ($selected_key == $dataSourceKey ? " selected" : "").">".esc($dataSourceTitle)."</option>\n");
				}
			
			?>
			</select>

			<a href="" id="mmr_information_show">(more details)</a>
		
		</form>
		
		<?php 
}