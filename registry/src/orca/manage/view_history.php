<?php
define('aCOSI_RELATED_ACTIVITY','aORCA_VIEW_HISTORY');
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

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';

echo '<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/orca_dhtml.js"></script>';
echo '<h1>Data Source History Tool</h1>';

$action = getQueryValue('action');
$data_source_key = urldecode(getQueryValue('data_source_key'));
$key = urldecode(getQueryValue('key'));
$this_url = eAPP_ROOT . "orca/manage/view_history.php?";
$errors = array();

if ($action == "data_source_view")
{
	// Get the record from the database.
	$dataSource = getDataSources($data_source_key, null);
	if( !$dataSource )
	{
		responseRedirect(eAPP_ROOT ."orca/admin/data_source_list.php");
	}
	
	// Check is Data Source ADMIN or ORCA ADMIN
	if( !(userIsDataSourceRecordOwner($dataSource[0]['record_owner']) || userIsORCA_ADMIN()) )
	{
		responseRedirect(eAPP_ROOT ."orca/admin/data_source_list.php");
	}

	if ($dataSource === false)
	{
		echo "<br/><span class='errorText'>Invalid Data Source Key / No History Found.</span>";
	}
	else 
	{
		// Data Table filter and configuration!
		echo '<script type="text/javascript">'
			.'$(document).ready(function() {'
			.'$("#data_source_history_table").dataTable( {'
			.'  "iDisplayLength": 25,'
			.'	"aaSorting": [[ 4, "desc" ]],'
			.'"aoColumnDefs": [{ "bSortable": false, "aTargets": [ 5 ] }], '
			.'});'
			.'} );'
			.'</script>';
		
		echo '<h4>Viewing History for <a href="'.eAPP_ROOT.'orca/admin/data_source_view.php?data_source_key='.rawurlencode($data_source_key).'">';
		echo $dataSource[0]['title'];
		echo '</a></h4>'; 
		
		
		echo '<div style="width:1000px; overflow:auto; clear:both; padding:6px;">'
			.'<table cellspacing=0 cellpadding=0 id="data_source_history_table" class="display" style="float:left; border:1px solid #000; margin:10px; width:980px; border-collapse:separate;">'
			.'	<thead>'
			.'	<tr>'
			.'		<th width="150px">Record Key</th>'
			.'		<th>Current Title</th>'
			.'		<th width="70px" align="center">Status</th>'
			.'		<th width="50px">Versions</th>'
			.'		<th width="60px">Last Updated</th>'
			.'		<th width="30px" align="center">View Record History</th>'
			.'	</tr>'
			.'	</thead>'
			.'	<tbody>';
			
		$rawRecords = getRawRecords(NULL, $data_source_key, NULL);
		$recordList = array();
		if ($rawRecords && count($rawRecords) > 0)
		{
			foreach ($rawRecords AS $record)
			{
				if (!isset($recordList[$record['registry_object_key']]))
				{
					$creationDate = strtotime($record['created_when']);
					$recordList[$record['registry_object_key']] = array("registry_object_key" => $record['registry_object_key'],
																		"title" => "",
																		"status" => "",
																		"versions" => 1,
																		"last_updated" => $creationDate);
					
				} 
				else
				{
					$creationDate = strtotime($record['created_when']);			
					if ($creationDate > $recordList[$record['registry_object_key']]['last_updated'])
					{
						$recordList[$record['registry_object_key']] = array("registry_object_key" => $record['registry_object_key'],
																		"title" => "",
																		"status" => "",
																		"versions" => $recordList[$record['registry_object_key']]['versions'] + 1,
																		"last_updated" => $creationDate);
					}
				}	
	
				$ro = getRegistryObject($record['registry_object_key']);
				if (!$ro) 
				{ 
					$recordList[$record['registry_object_key']]['status'] = "DELETED"; 
				}
				else
				{
					$recordList[$record['registry_object_key']]['status'] = $ro[0]['status'];
					$recordList[$record['registry_object_key']]['title'] = $ro[0]['list_title'];
				}
			}
			
			sort($recordList);
			
			foreach ($recordList AS $record)
					
				echo '	<tr style="cursor:pointer;" onclick="document.location.href=\''.$this_url.'action=record_view&key='.rawurlencode($record['registry_object_key']).'&data_source_key='.rawurlencode($data_source_key).'\';">'
					.'		<td><div style="overflow:hidden;white-space: nowrap; width:145px;"><a title="'.$record['registry_object_key'].'">'.$record['registry_object_key'].'</a></div></td>'
					.'		<td>'.$record['title'].'</td>'
					.'		<td align="center">'.getRegistryObjectStatusSpan($record['status']).'</td>'
					.'		<td align="center">'.$record['versions'].'</td>'
					.'		<td>'. date("Y-m-j g:i a",$record['last_updated']).'</td>'
					.'		<td><a href="'.$this_url.'action=record_view&key='.rawurlencode($record['registry_object_key']).'&data_source_key='.rawurlencode($data_source_key).'"><img src="'.eAPP_ROOT.'orca/_images/magnifyglass.png" /></a></td>'
					.'	</tr>';
		}	
			
		echo '	</tbody>';
		echo '</table>';
		echo '</div>';
			
			
	}
	
}

if ($action == "record_view")
{
	// Get the record from the database.
	$dataSource = getDataSources($data_source_key, null);
	if( !$dataSource )
	{
		responseRedirect(eAPP_ROOT ."orca/admin/data_source_list.php");
	}
	
	// Check is Data Source ADMIN or ORCA ADMIN
	if( !(userIsDataSourceRecordOwner($dataSource[0]['record_owner']) || userIsORCA_ADMIN()) )
	{
		responseRedirect(eAPP_ROOT ."orca/admin/data_source_list.php");
	}
	
	
	$recordHistory = getRawRecords($key, $data_source_key);
	
	if (!$recordHistory || count($recordHistory) < 1)
	{
		echo "<br/><span class='errorText'>No History Found for key: $key.</span>";
	}
	else
	{
		$registryObject = getRegistryObject($key);
			
		// Data Table filter and configuration!
		echo '<script type="text/javascript">'
			.'$(document).ready(function() {'
			.'$("#record_history_table").dataTable( {'
			.'  "iDisplayLength": 25,'
			.'	"aaSorting": [[ 0, "desc" ]],'
			.'"aoColumnDefs": [{ "bSortable": false, "aTargets": [ 2, 3 ] }], '
			.'});'
			.'} );'
			.'</script>';
		
		echo '<h4>Viewing Record History for '.($registryObject ? 
						'<a href="'.eAPP_ROOT.'orca/view.php?key='.rawurlencode($key).'">' . $registryObject[0]['registry_object_key'] . '</a>'
						: 
						$key);					 
		echo '</h4>'; 
		
		echo '<div style="width:980px; clear:both; position:relative;"><div style="width:50%; padding:6px; float:left; position:relative;">'
			.'<table cellspacing=0 cellpadding=0 id="record_history_table" class="display" style="float:left; border:1px solid #000; margin:10px; width:480px; border-collapse:separate;">'
			.'	<thead>'
			.'	<tr>'
			.'		<th>Update Time</th>'
			.'		<th>Updated By</th>'
			.'		<th width="30px" align="center">Recover Record</th>'
			.'		<th width="30px" align="center">Get RIFCS</th>'
			.'	</tr>'
			.'	</thead>'
			.'	<tbody>';
			
		$rawRecords = getRawRecords($key, $data_source_key, NULL);
		if ($rawRecords && count($rawRecords) > 0)
		{
			foreach ($rawRecords AS $record)
			{
					echo '<tr style="cursor:pointer;">'
					.'		<td onclick="$(\'#rifcs_display_block\').hide(); getRIFCSHistory(this, \''.$data_source_key.'\',\''.$key.'\',\''.strtotime($record['created_when']).'\');">'.date("Y-m-j g:i a",strtotime($record['created_when'])).'</td>'
					.'		<td onclick="$(\'#rifcs_display_block\').hide(); getRIFCSHistory(this, \''.$data_source_key.'\',\''.$key.'\',\''.strtotime($record['created_when']).'\');">'.$record['created_who'].'</td>'
					.'		<td><img src="'.eAPP_ROOT.'orca/_images/lifesaver.png" onclick="recoverRIFCS(this, \''.$data_source_key.'\',\''.$key.'\',\''.strtotime($record['created_when']).'\');"/></td>'
					.'		<td><img src="'.eAPP_ROOT.'orca/_images/arrow_top_right.png" style="cursor:pointer;" onclick="$(\'#rifcs_display_block\').hide(); getRIFCSHistory(this, \''.$data_source_key.'\',\''.$key.'\',\''.strtotime($record['created_when']).'\');" /></a></td>'
					.'	</tr>';
			}
		}
		
		echo '	</tbody>';
		echo '</table>';
		echo '</div>';
		
		echo '<div id="rifcs_display_block" style="width:45%; margin-left:8px; float:left; position:relative; border:1px solid black;background-color:#ccc; ">' 
				.'<div style="background-color:#555; color:white; font-size:10px; font-weight:bold; padding:4px;">RIFCS View of Record</div>'
				.'<div id="rifcs_display_container" style="background-color:#ccc; color:white; font-size:10px; font-weight:bold; padding:4px;">'
				.'<textarea id="rifcs_display_content" readonly="readonly" cols="82" rows="25" style="width:99%"></textarea>'
				.'<div style="float:right;">'
					.'<input type="button" value="select all" onclick="$(\'#rifcs_display_content\').focus().select();" /> <input type="button" value="hide" onclick="$(\'#rifcs_display_content\').value(" ");$(\'#rifcs_display_block\').hide();" />'
				.'</div>';
			
		echo '</div>';
		echo '</div>';
	}
}

/*
 * No action selected. Display the form to select a data source to view. 
 */
if ($action == NULL)
{
	
	// Check is ORCA Administrator
	if( !userIsORCA_ADMIN() )
	{
		responseRedirect(eAPP_ROOT ."orca/admin/data_source_list.php");
	}
	
	// Execute the search.
	$rawResults = getDataSources(null, null);
	$searchResults = array();
	
	// Check the record owners.
	if( $rawResults )
	{
		foreach( $rawResults as $dataSource )
		{
			if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_ADMIN()) )
			{
				$searchResults[count($searchResults)] = $dataSource;
			}		
		}
	}

	?>
	<p>This tool allows you to review the status of records created within your Data Source and restore modified/deleted records. </p> 
	<form id="data_source_history_form" name="data_source_history_form" action="view_history.php" method="get">
	
	<table class="formTable"> 
		<?php if (sizeof($errors) > 0) { ?>
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
		<?php }?>
	
		<tbody class="formFields"> 
			<tr> 
				<td>Data Source:</td> 
				<td>
					<input type="hidden" name="action" value="data_source_view" />
					<select name="data_source_key" id="data_source_key" style="width:300px;" onchange="this.form.submit();">
					<option value=""></option>
					<?php
						
						// Present the results.
						for( $i=0; $i < count($searchResults); $i++ )
						{
							$dataSourceKey = $searchResults[$i]['data_source_key'];
							$dataSourceTitle = $searchResults[$i]['title'];
							$numRegistryObjects = getRegistryObjectCount($dataSourceKey);		
							print("<option value=\"".rawurlencode($dataSourceKey)."\">".esc($dataSourceTitle)." (".esc($numRegistryObjects).")</option>\n");
						}
					
					}// end if search results
					?>
					</select>
				</td> 
			</tr> 
		</tbody>
	</table>
	
	</form>

<?php 


?>
