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
// Page processing
// -----------------------------------------------------------------------------
// Increase the execution timeout for the import and clear operations, as they
// may have to deal with a large amount of data.
set_time_limit(0);
$executionTimeoutSeconds = 20*60;
$taskWaiting = '';
$taskWaiting = scheduledTaskCheck(getQueryValue('data_source_key'));

// Get the record from the database.
$dataSource = getDataSources(getQueryValue('data_source_key'), null);
if( !$dataSource )
{
	responseRedirect("data_source_list.php");
}

// Check the record owner.
if( !(userIsDataSourceRecordOwner($dataSource[0]['record_owner']) || userIsORCA_ADMIN()) )
{
	responseRedirect("data_source_list.php");
}

$dataSourceKey = $dataSource[0]['data_source_key'];

// Output the data source log
if( getQueryValue('getlog') == '1' )
{
	// Set the Content-Type header.
	header("Content-Type: text/plain; charset=UTF-8", true);
	print(getDataSourceLogText($dataSourceKey));
	exit;
}

// Get any action that may have been posted.
$action = strtoupper(getPostedValue('action'));

// Action the action.
switch( $action )
{
	case 'EDIT DATA SOURCE':
		responseRedirect('data_source_edit.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'DELETE DATA SOURCE':
		responseRedirect('data_source_delete.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'CLEAR LOG':
		// Clear the log.
		deleteDataSourceLog($dataSourceKey);
		// Log the fact that the log was cleared.
		insertDataSourceEvent($dataSourceKey, "LOG CLEARED");
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'TEST HARVEST':
		// Give the server more time to process the data.
		ini_set("max_execution_time", "$executionTimeoutSeconds");
		// Test the data from this source.
		runImport($dataSource, true);
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'IMPORT RECORDS':
		// Give the server more time to process the data.
		ini_set("max_execution_time", "$executionTimeoutSeconds");
		// Import the data from this source.
		runImport($dataSource, false);
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'DELETE ALL RECORDS':
		// Give the server more time to process the data.
		ini_set("max_execution_time", "$executionTimeoutSeconds");
		// Delete all Registry Objects imported from this source.
		runClear($dataSource, $action);
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'DELETE HARVESTED RECORDS':
		// Give the server more time to process the data.
		ini_set("max_execution_time", "$executionTimeoutSeconds");
		// Delete all Registry Objects imported from this source.
		runClear($dataSource, $action);
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'DELETE MANUALLY ENTERED RECORDS':
		// Give the server more time to process the data.
		ini_set("max_execution_time", "$executionTimeoutSeconds");
		// Delete all Registry Objects imported from this source.
		runClear($dataSource, $action);
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;		
	case 'CANCEL':
		// Get the id of the harvest request we're to cancel.
		$harvestRequestId = getPostedValue('harvest_request_id');
		cancelHarvestRequest($harvestRequestId, $dataSourceKey);
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	case 'REFRESH':
		// Redirect back here via a GET request to avoid problems with repost of form data.
		responseRedirect('data_source_view.php?data_source_key='.urlencode(getQueryValue('data_source_key')));
		break;
	default;
		break;
}

// Update the statuses of all harvest requests.
$harvestRequests = getHarvestRequests(null, $dataSourceKey);
if( $harvestRequests )
{
	foreach( $harvestRequests as $harvestRequest )
	{
		getHarvestRequestStatus($harvestRequest['harvest_request_id'], $dataSourceKey);
	}
}
// Get the updated data.
$harvestRequests = getHarvestRequests(null, $dataSourceKey);

// Get the event log.
$dataSourceLogHTML = getDataSourceLogHTML($dataSourceKey);


$numRegistryObjects = getRegistryObjectCount($dataSourceKey);
$numRegistryObjectsApproved = getRegistryObjectCount($dataSourceKey, null, null, APPROVED);

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>

<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<script type="text/javascript">
		checkDataSourceScheduleTask();
  	setInterval(checkDataSourceScheduleTask, 5000);
</script>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/jquery-ui-1.8.9.custom.min.js"></script>	
<input type="hidden" id="dataSourceKey" value="<?php echo $dataSource[0]['data_source_key']; ?>" />
<form id="datasourceFrom" action="data_source_view.php?data_source_key=<?php printSafe(urlencode(getQueryValue('data_source_key'))); ?>" method="post">
<table class="recordTable" summary="Data Source">
	<thead>
		<tr>
			<td></td>
			<td>Data Source</td>
		</tr>
	
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Records From Source:</td>
			<td>
				<?php 
					$statuses = array();
					foreach (getRecordCountsByStatusForDataSource($dataSourceKey) AS $status => $count)
					{
						$status = getRegistryObjectStatusInfo($status);
						$statuses[] = $status['display'] . ": ($count)";	
					}
					echo implode($statuses, ", ");
			//if($numRegistryObjects > 0) printSafe('Published: ('.$numRegistryObjects.')');  if($numRegistryObjectsApproved > 0) printSafe(' Approved: ('.$numRegistryObjectsApproved.')');
				 print(' <a href="../manage/my_records.php?data_source='.esc(urlencode($dataSourceKey)).'">Manage Records</a>');
				 ?>
			 </td>
		</tr>
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;"><h3>Account Administration Information</h3></span>
		<span style="text-align:right;">
				<input type="submit" name="action" value="Edit Data Source" title="Edit the settings for this Data Source" />&nbsp;
				<?php if( hasActivity(aORCA_DATA_SOURCE_DELETE) ) { ?><input type="submit" name="action" value="Delete Data Source" title="Delete this Data Source" />&nbsp;<?php } ?>
		</span>
		</td>
		</tr>
		<tr>
			<td>Key:</td>
			<td><?php printSafe($dataSource[0]['data_source_key']) ?></td>
		</tr>
		<tr>
			<td>Title:</td>
			<td><?php printSafe($dataSource[0]['title']) ?></td>
		</tr>
		<tr>
			<td>Record Owner:</td>
			<td><?php 
			if( $dataSource[0]['record_owner'] )
			{
				printSafe(getRoleName($dataSource[0]['record_owner'])." (".$dataSource[0]['record_owner'].")");
			}
			?></td>
		</tr>
		<tr>
			<td>Contact Name:</td>
			<td><?php printSafe($dataSource[0]['contact_name']) ?></td>
		</tr>
		<tr>
			<td>Contact E-mail:</td>
			<td><?php printSafe($dataSource[0]['contact_email']) ?></td>
		</tr>
		<tr>
			<td>Notes:</td>
			<td><?php printSafeWithBreaks($dataSource[0]['notes']) ?></td>
		</tr>
		<?php if (isset($dataSource[0]['address_line_1']) && $dataSource[0]['address_line_1'] != ''): ?>
			<tr>
				<td>Address Line 1:</td>
				<td><?php printSafeWithBreaks($dataSource[0]['address_line_1']) ?></td>
			</tr>
		<?php endif; ?>
		<?php if (isset($dataSource[0]['address_line_2']) && $dataSource[0]['address_line_2'] != ''): ?>
			<tr>
				<td>Address Line 2:</td>
				<td><?php printSafeWithBreaks($dataSource[0]['address_line_2']) ?></td>
			</tr>
		<?php endif; ?>
		<?php if (isset($dataSource[0]['city']) && $dataSource[0]['city'] != ''): ?>
			<tr>
				<td>City:</td>
				<td><?php printSafeWithBreaks($dataSource[0]['city']) ?></td>
			</tr>
		<?php endif; ?>
		<?php if (isset($dataSource[0]['post_code']) && $dataSource[0]['post_code'] != ''): ?>
			<tr>
				<td>Post Code:</td>
				<td><?php printSafeWithBreaks($dataSource[0]['post_code']) ?></td>
			</tr>
		<?php endif; ?>
		<?php if (isset($dataSource[0]['state']) && $dataSource[0]['state'] != ''): ?>
			<tr>
				<td>State:</td>
				<td><?php printSafeWithBreaks($dataSource[0]['state']) ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td>Created When:</td>
			<td><?php printSafe(formatDateTime($dataSource[0]['created_when'], gDATETIME)) ?></td>
		</tr>
		<tr>
			<td>Created Who:</td>
			<td><?php printSafe($dataSource[0]['created_who']) ?></td>
		</tr>
		<tr>
			<td>Modified When:</td>
			<td><?php printSafe(formatDateTime($dataSource[0]['modified_when'], gDATETIME)) ?></td>
		</tr>
		<tr>
			<td>Modified Who:</td>
			<td><?php printSafe($dataSource[0]['modified_who']) ?></td>
		</tr>
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;"><h3>Records Management Settings</h3></span>
		<span style="text-align:right;">
					<input type="button" onclick="window.location='<?php print eAPP_ROOT ?>orca/manage/view_history.php?action=data_source_view&data_source_key=<?php print urlencode($dataSourceKey); ?>'" value="View History"></input>
				<?php if( userIsORCA_ADMIN() ) { ?>	<input type="button" value="Delete Records" title="Delete Registry Objects from this source" onclick="showDeleteModal();"/><?php } ?>
				<input type="hidden" name="delete_flag" id="delete_flag" value="ALL"/>
		</span>
		</td>
		</tr>		
		<tr>
			<td>Reverse Links:</td>
			<td><img id="allow_reverse_internal_links_image" src="../_images/gray_<?php if($dataSource[0]['allow_reverse_internal_links'] == 'f'){print("un");}?>checked.png"/>&nbsp;<label>Automatically create reverse links within this data source</label></td>
		</tr>
		<tr>
			<td></td>
			<td><img id="allow_reverse_internal_links_image" src="../_images/gray_<?php if($dataSource[0]['allow_reverse_external_links'] == 'f'){print("un");}?>checked.png"/>&nbsp;<label>Automatically create reverse links from external data sources</label></td>
		</tr>

		<tr>
			<td>Create primary relationships:</td>
			<td><?php if((string)$dataSource[0]['create_primary_relationships']=="t") {echo "Yes: ";}else{echo "No";} ?>
			<?php if((string)$dataSource[0]['create_primary_relationships']=="t") { ?>

				<?php  $primary1 = getRegistryObject($dataSource[0]['primary_key_1'] );
				echo "<em>'".$primary1[0]['display_title']."'</em>";

			if($dataSource[0]['primary_key_2']!=''){
				 $primary2 = getRegistryObject($dataSource[0]['primary_key_2'] );
				echo " and <em>'".$primary2[0]['display_title']."'</em>";
			}
			}
			?>

			</td>
		</tr>


		<tr>
			<td>Push to NLA:</td>
			<td><?php if((string)$dataSource[0]['push_to_nla']=="t") {echo "Yes";}else{echo "No";} ?></td>
		</tr>	
		<?php if((string)$dataSource[0]['push_to_nla']=="t") {?>	
		<tr>
			<td>ISIL:</td>
			<td><?php printSafeWithBreaks($dataSource[0]['isil_value']) ?></td>
		</tr>	
		<?php }?>
		<tr>
			<td>Manually Publish Records?:</td>
			<td><?php if((string)$dataSource[0]['auto_publish']=="t") {echo "Yes";}else{echo "No";} ?></td>
		</tr>
		<tr>
			<td>Quality Assessment Required?:</td>
			<td><?php if((string)$dataSource[0]['qa_flag']=="t") {echo "Yes";}else{echo "No";} ?></td>
		</tr>	
		<tr>
			<td>Assessment Notification Email:</td>
			<td><?php printSafeWithBreaks($dataSource[0]['assessement_notification_email_addr']) ?></td>
		</tr>	
		<tr>
			<td>Contributor Pages:</td>
			<td>
						<?php 
			switch($dataSource[0]['institution_pages']){
				case 0:
					?>
					Contributor pages are not managed.
					<?php  
				
				break;
				case 1:
					?>
					Contributor pages are automatically managed.
					<?php  
				
				break;	
				case 2:
					?>
					Contributor pages are manually managed.
					<?php  
				
				break;							
			} 
			if($dataSource[0]['institution_pages']!=0){?>
			<table border="1" width="100%">

			<tr><td style="width:200px"><b>Group </b> </td><td> <b> Contributor Page Key</b></td></tr>
			<?php 
		$object_groups = getDataSourceGroups($dataSourceKey); 

		if($object_groups)
		{
			foreach($object_groups as $group)
			{ 
				$thePage = getGroupPage($group['object_group']);
				?>
				<tr><td id="group<? echo $i;?>name" width="200"><?php  echo $group['object_group'];?>
				<?php  if ($thePage[0]['authoritive_data_source_key'] != $dataSourceKey && isset($thePage[0]['authoritive_data_source_key'])) 
				{ ?>
					<br /><span style="color:grey">Managed by <?php echo $thePage[0]['authoritive_data_source_key']?></span></td><td><?php print($thePage[0]['registry_object_key']); ?></td> 
					<?php  
				} else { ?>		
					</td><td >
	<?php 			if($dataSource[0]['institution_pages']=="1"||$dataSource[0]['institution_pages']=="2")	
					{
						if(getRegistryObject($thePage[0]['registry_object_key'], $overridePermissions = true))
						{
						?>	<a href="../view.php?key=<?php print(($thePage[0]['registry_object_key'])); ?>"><?php print($thePage[0]['registry_object_key']);?></a><?php 
						}else{
						?>
							<a href="../manage/add_party_registry_object.php?readOnly&data_source=<?php echo $dataSourceKey;?>&key=<?php echo $thePage[0]['registry_object_key']; ?>"><?php print($thePage[0]['registry_object_key']);?></a><?php 
						}				
					}else{
						print($thePage[0]['registry_object_key']); 
					}
			?>
					</td> <?php  
				} ?></tr>				
				<?php
			}
		}	?>
			</table> <?php }?>
			</td>
		</tr>		
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;"><h3>Harvester Settings</h3></span>
		<span style="text-align:right;">
				<input type="submit" name="action" value="Test Harvest" title="Check the data at the Data Source URI" onclick="wcPleaseWait(true, 'Testing...')" />&nbsp;
				<input type="submit" name="action" value="Import Records" title="Import the data from the Data Source URI" onclick="wcPleaseWait(true, 'Importing...')" />&nbsp;
		</span>
		</td>
		</tr>					
		<tr>
			<td>URI:</td>
			<td><?php printSafe($dataSource[0]['uri']) ?></td>
		</tr>
		<tr>
			<td>Provider Type:</td>
			<td><?php printSafe($gORCA_PROVIDER_TYPES[$dataSource[0]['provider_type']]) ?></td>
		</tr>
		<tr>
			<td>Harvest Method:</td>
			<td><?php 
			
					printSafe($gORCA_HARVEST_METHODS[$dataSource[0]['harvest_method']]);
				
					if ($dataSource[0]['advanced_harvesting_mode'] != 'STANDARD') {
						echo " (" . $dataSource[0]['advanced_harvesting_mode'] . ")";	
					}
				
				?></td>
		</tr>
		<tr>
			<td>Harvest Date:</td>
			<?php $dateTime = new DateTime($dataSource[0]['harvest_date']) ;?>
			<td><?php printSafe(formatDateTimeWithMask($dataSource[0]['harvest_date'], eDCT_FORMAT_ISO8601_DATE_TIME)); echo "  (GMT ".timezone_name_get(date_timezone_get($dateTime)).")"; ?> <span class="inputFormat"><?php  if($dataSource[0]['time_zone_value']!=''&& $dataSource[0]['time_zone_value']!=$dataSource[0]['harvest_date']){echo $dataSource[0]['time_zone_value'];} ?></span></td>
		</tr>		
		<tr>
			<td>OAI-PMH Set:</td>
			<td><?php printSafe($dataSource[0]['oai_set']) ?></td>
		</tr>				
		<?php
		if( $harvestRequests )
		{
			print("    <tr>\n");
			print("      <td>Harvest Requests:</td>\n");
			print("      <td>\n");
			print('  <table class="subtable">'."\n");
			foreach( $harvestRequests as $key => $harvestRequest )
			{
				$style = '';
				if( $key < count($harvestRequests)-1 )
				{
					$style = ' style="border-bottom: 1px solid #dddddd; padding-bottom: 2px;"';
				}
				print('<tbody><tr><td class="attribute" style="white-space: nowrap;">');
				print('<a title="Show details" id="R'.esc($harvestRequest['harvest_request_id']).'_icon" class="menuLink" style="display: block; cursor: pointer; text-align: right; padding-left: 16px; color: #000000;" onclick="showHideTableRowGroup(\'R'.esc($harvestRequest['harvest_request_id']).'\')"> Status:</a>');
				print('</td><td>'.esc($harvestRequest['status']).'</td></tr></tbody>'."\n");
				
				print('<tbody id="R'.esc($harvestRequest['harvest_request_id']).'" style="display: none;">');
				print('<tr><td class="attribute">Last Status Update:</td><td>'.esc(formatDateTimeWithMask($harvestRequest['modified_when'], eDCT_FORMAT_ISO8601_DATE_TIMESEC)." ".$harvestRequest['modified_who']).'</td></tr>'."\n");
				print('<tr><td class="attribute">ID:</td><td>'.esc($harvestRequest['harvest_request_id']).'</td></tr>'."\n");
				print('<tr><td class="attribute">Submitted:</td><td>'.esc(formatDateTimeWithMask($harvestRequest['created_when'], eDCT_FORMAT_ISO8601_DATE_TIMESEC)).'</td></tr>'."\n");
				print('<tr><td class="attribute">Harvester Base URI:</td><td>'.esc($harvestRequest['harvester_base_uri']).'</td></tr>'."\n");
				print('<tr><td class="attribute">Harvester IP:</td><td>'.esc($harvestRequest['harvester_ip']).'</td></tr>'."\n");
				print('<tr><td class="attribute">Response Target URL:</td><td>'.esc($harvestRequest['response_target_uri']).'</td></tr>'."\n");
				print('<tr><td class="attribute">Source URL:</td><td>'.esc($harvestRequest['source_uri']).'</td></tr>'."\n");
				print('<tr><td class="attribute">Method:</td><td>'.esc($harvestRequest['method']).'</td></tr>'."\n");
				if( $harvestRequest['oai_set'] )
				{
					print('<tr><td class="attribute">OAI Set:</td><td>'.esc($harvestRequest['oai_set']).'</td></tr>'."\n");
				}
				if( $harvestRequest['harvest_date'] )
				{
					print('<tr><td class="attribute">Harvest Date:</td><td>'.esc(formatDateTimeWithMask($harvestRequest['harvest_date'], eDCT_FORMAT_ISO8601_DATETIMESEC_UTC)).'</td></tr>'."\n");
				}
				if( $harvestRequest['harvest_frequency'] )
				{
					print('<tr><td class="attribute">Harvest Frequency:</td><td>'.esc($harvestRequest['harvest_frequency']).'</td></tr>'."\n");
				}
				print('<tr><td class="attribute">Mode:</td><td>'.esc($harvestRequest['mode']).'</td></tr>'."\n");
				print('</tbody>');
				print('<tbody><tr><td'.$style.'></td><td'.$style.'><form style="margin: 0px; margin-bottom: 2px; padding: 0px;" action="data_source_view.php?data_source_key='.esc(urlencode(getQueryValue('data_source_key'))).'" method="post"><div>'."\n");
				print('<input type="hidden" name="harvest_request_id" value="'.esc($harvestRequest['harvest_request_id']).'" />'."\n");
				print('<input type="submit" class="buttonSmall" name="action" value="refresh" title="Refresh the data on this page" />&nbsp;'."\n");
				print('<input type="submit" class="buttonSmall" name="action" value="cancel" title="Cancel this harvest request" />&nbsp;<br />'."\n");
				print("</div></form></td></tr></tbody>\n");
			}
			print("  </table>\n");
			print("       </td>\n");
			print("    </tr>\n");
		}
		?>
		<tr>
			<td>Activity Log:<br /><br /><a href="<?php print('data_source_view.php?getlog=1&amp;data_source_key='.urlencode($dataSourceKey)) ?>" title="Plain text Activity Log" style="margin-right: 8px;"><img src="<?php print(eIMAGE_ROOT.'_icons/text_file.gif') ?>" alt="Plain text Activity Log" /></a></td>
			<td><div class="readonly" style="height: 220px; width: 600px; padding: 0px; overflow: scroll; font-family: courier new, courier, monospace; font-size: 9pt;"><?php print($dataSourceLogHTML) ?></div></td>
		</tr>
	</tbody>
	<tbody class="recordFields">
		<tr>
			<td colspan="2">
			    <span style="float:left;"></span>
				<span style="text-align:right;">
				<input type="submit" name="action" value="Refresh" title="Refresh the data on this page" />&nbsp;
				<input type="submit" name="action" value="Clear Log" title="Clear the Data Source activity log" />&nbsp;
				</span>				
			</td>
		</tr>
	</tbody>
</table>
<div id="delete_warning_box" class="window">
				<img src="<?php print(eAPP_ROOT) ?>orca/_images/error_icon.png" onClick='closeDeleteModal();' style="cursor:pointer; position:absolute; top:5px; right:5px; width:16px;" />
				<table>
				<tbody>
					<tr>
						<td>
							<b>**WARNING**</b><br/>
							Continuing with this action will result in all the records from this Data Source being permanently deleted.
						</td>
					</tr>
					<tr>
					<td>
					<input type="submit" name="action" value="Delete All Records" title="Delete all of the Registry Objects imported from this source" onclick="showDeleteModal();"/>&nbsp;
					<input type="submit" name="action" value="Delete Harvested Records" title="Delete all of the Registry Objects imported from this source" onclick="showDeleteModal();"/>&nbsp;
					<input type="submit" name="action" value="Delete Manually Entered Records" title="Delete all of the Registry Objects imported from this source" onclick="showDeleteModal();"/>&nbsp;
					</td>
					</tr>
					<tr>
						<td>							
						</td>
					</tr>
					<tr>
						<td>
							<input type="button" value="Cancel" onclick="closeDeleteModal()"/>
						</td>
					</tr>
					</tbody>				
				</table>				
			</div>
</form>
			
		<div class="mask" onclick="closeDeleteModal()" id="mask"></div>

<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
