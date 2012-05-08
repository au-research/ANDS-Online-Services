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

header("Location: " . eAPP_ROOT . "orca/manage/my_records.php");
exit();

// Page processing
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
define("eORIGSOURCE_REGISTER_MY_DATA_OLD", "/orca");

$draft_object_array = array();
$object_array = array();

$array = getDraftsByDataSource('ausstage');

foreach ($array AS $registry_object) 
{	

	$draft_object_array[esc($registry_object['draft_key'])] = 	array("key" => $registry_object['draft_key'],
							"title" => $registry_object['registry_object_title'],
							"status" => "DRAFT",
							"class" => $registry_object['class'],
							"group" => ucfirst(strtolower($registry_object['registry_object_group'])),
							"datasource" => $registry_object['registry_object_data_source'],
							"created" => $registry_object['date_created'],
							"modified" => $registry_object['date_modified']
						);	
						
}
usort($draft_object_array, "compareDraftsByCreated");


if ($array = searchRegistry('', '', null, null, null, null, null, getThisOrcaUserIdentity())) {
	
	usort($array, 'compareRegObjectsByCreated');
	$c = 0;
	foreach ($array AS $registry_object) 
	{		
		if ((stripos($registry_object['originating_source'], eORIGSOURCE_REGISTER_MY_DATA) !== FALSE ||
			stripos($registry_object['originating_source'], eORIGSOURCE_REGISTER_MY_DATA_OLD) !== FALSE) &&
			$c < 25) 
		{

			$title = "(no name/title)";
			if ($names = getComplexNames($registry_object['registry_object_key']))
			{
				foreach ($names as $name)
				{
					if ($name["type"] == "primary")
					{
						$parts = getNameParts($name["complex_name_id"]);
						if (count($parts) > 0)
						{
							$title = "";
							foreach ($parts as $part)
							{
								$title = $title . $part['value'] . " ";
							}
							$title = trim($title);
						}
					}
				}
				
				if ($title == "(no name/title)")
				{
					
					foreach ($names as $name)
					{
							$parts = getNameParts($name["complex_name_id"]);
							if (count($parts) > 0)
							{
								$title = "";
								foreach ($parts as $part)
								{
									$title = $title . $part['value'] . " ";
								}
								$title = trim($title);
							}
					}
					
				}
				
			}
			$object_array[esc($registry_object['registry_object_key'])] = 	
								array("key" => $registry_object['registry_object_key'],
									"title" => esc($title),
									"status" => trim($registry_object['status']),
									"class" => $registry_object['registry_object_class'],
									"group" => ucfirst(strtolower($registry_object['type'])),
									"datasource" => $registry_object['data_source_title'],
									"created" => $registry_object['created_when'],
									"modified" => null
								);	
								
			$c++;
							
		}
		
	}
}


if (count($object_array) == 0 && count($draft_object_array) == 0)
{
?>

<div style="margin-bottom: 2em; width: 800px;">
<h2>Manage My Records</h2>
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;">
	This tool allows you to view all records which you have created through the Register My Data interface including draft/unfinished records.<br/>
</p>
<p>You currently have no manually-entered records in the registry. To create a record use the <i>Add New Record</i> tool in the menu on the left.</p>	
</div>

<?php 
} else {
	
?>
<div style="margin-bottom: 2em; width: 800px;">
<h2>Manage My Records</h2>
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;">
	This tool allows you to view all records which you have created or modified through the Register My Data interface including drafts.<br/>
</p>
<table summary="My Draft Records" class="rowNumbers" width="100%">
	   <thead>
	      <tr>
	         <td style="border-bottom: 0px;"></td>
	         <td colspan="5"></td>
	      </tr>
	      <tr>
			<td style="border: 0px; background: transparent;"></td>
         	<td class="resultListHeader" style="border-right: 1px solid #dddddd;" colspan="6">Drafts (<?php drawListCount($draft_object_array); ?>)</td>
	      </tr>
	   </thead>
	   <tbody>
	      <tr>
	         <th style="border-left: 0px;"></th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left" width="55px">Record Key</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left" width="330px">Name/Title</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Status</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Class</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Created</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left" width="70px">Options</th>
	      </tr>
	      <?php
	      $num=0;
	      foreach ( $draft_object_array as $key => $registry_object ) 
	      {
	      	$num = $num + 1;
	      	
	      	$dateCreated = date("g:i a, j M y", strtotime($registry_object['created']));
	      	$dateModified = date("g:i a, j M y", strtotime($registry_object['modified']));
	      	if (!$dateModified) $dateModified = '-';
			$statusSpan = getRegistryObjectStatusSpan($registry_object['status']);
	      	$registryObjectKey = $registry_object['key'];
			$registryObjectType = $registry_object['class'];
			$registryObjectTitle = $registry_object['title'];

	      	print("<tr id=\"trow".$num."\" valign=\"middle\">\n");
	      	
	      	$cellAttrBase = ' onmouseover="recordOver(\'trow'.$num.'\', false)" onmouseout="recordOut(\'trow'.$num.'\', false)"';
	      	print("  <td".$cellAttrBase.">".$num."</td>\n");
	      	
	      	$cellAttributes = $cellAttrBase;
	      	$cellAttributes .= ($registry_object['status'] == "DRAFT" ? ' title="Edit this record"' : ' title="View this record in ORCA"');
	      	$cellAttributes .= ($registry_object['status'] == "DRAFT" ? ' onclick="window.location=\''.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?data_source='.esc(rawurlencode($registry_object['datasource']), true).'&key='.esc(rawurlencode($registryObjectKey), true).'\'"' 
	      															  : ' onclick="window.location=\''.eAPP_ROOT .'orca/view.php?key='.esc(rawurlencode($registryObjectKey), true).'\'"');
	      	$cellAttributes .= ' class="recordLink" style="font-size:0.8em;"';
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;"><div style="overflow:hidden;white-space: nowrap; width:65px;"><a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?data_source='.esc(rawurlencode($registry_object['datasource']), true).'&key='.esc(rawurlencode($registryObjectKey), true).'" title="'.esc($registryObjectKey).'" style="color:black;">'.esc($registryObjectKey)."</a></div></td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;"><div style="overflow:hidden;white-space: nowrap; width:330px;"><a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?data_source='.esc(rawurlencode($registry_object['datasource']), true).'&key='.esc(rawurlencode($registryObjectKey), true).'" title="'.esc($registryObjectTitle).'" style="color:black;">'.esc($registryObjectTitle)."</a></div></td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$statusSpan."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.ucfirst($registry_object['class'])."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$dateCreated."</td>\n");
	      	print('  <td'.$cellAttrBase.' align="left" style="padding-right: 5px;">');

	      	// DRAFT records cannot be viewed
	      	if ($registry_object['status'] == "DRAFT") {
	      		print ('    <a href="javascript:alert(\'Draft records cannot be viewed in ORCA until editing is completed. Choose Edit instead!\');" style="cursor:help;" ontitle="Draft records cannot be viewed in ORCA"><img src="'.(eAPP_ROOT . "orca/_images/preview_disabled.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	
	      	// PUBLISHED records can be viewed in ORCA (and consequently RDA)
	      	} else {
				print ('    <a href="'.eAPP_ROOT.'orca/view.php?key='.esc(rawurlencode($registryObjectKey)).'" title="View this record in ORCA"><img src="'.(eAPP_ROOT . "orca/_images/preview.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	}
	      	
	      	// PUBLISHED records which already have a DRAFT (i.e. have been edited before) cannot be edited directly
	      	if ($registry_object['status'] == PUBLISHED && isset($draft_object_array[esc($registryObjectKey)])) {
				print('    <a href="" title="An editable draft for this record already exists!" onclick="alert(\'An editable draft for this record already exists!\');"><img src="'.(eAPP_ROOT . "orca/_images/edit_disabled.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	// All other records can be edited
	      	} else {
	      		print('    <a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?data_source='.esc(rawurlencode($registry_object['datasource']), true).'&key='.esc(rawurlencode($registryObjectKey)).'" title="Modify this Record"><img src="'.(eAPP_ROOT . "orca/_images/edit.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	}
	      	
	      	// All types of records can be deleted!
	      	if ($registry_object['status'] == "DRAFT") {
	      		print('    <a onClick="if (confirm(\'Are you sure? This will irreversibly delete this draft and any changes will be lost!\')) { window.location.href=\''.eAPP_ROOT .'orca/manage/process_registry_object.php?task=delete&key='.esc(rawurlencode($registryObjectKey)).'\'; }" title="Delete this Draft" style="cursor:pointer;"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>');
	      	} else {
	      		print('    <a href="'.eAPP_ROOT .'orca/admin/registry_object_delete.php?key='.esc(rawurlencode($registryObjectKey)).'" title="Delete this Record"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>');
	      	}
	      	
	      	print("</td>\n");
	      	print("</tr>\n");
	      }
	      ?>
	   </tbody>
	</table>
	
	<table summary="My Records" class="rowNumbers" width="100%">
	   <thead>
	      <tr>
	         <td style="border-bottom: 0px;"></td>
	         <td colspan="5"></td>
	      </tr>
	      <tr>
			<td style="border: 0px; background: transparent;"></td>
         	<td class="resultListHeader" style="border-right: 1px solid #dddddd;" colspan="6">Recently modified records (<?php drawListCount($object_array); ?>)</td>
	      </tr>
	   </thead>
	   <tbody>
	      <tr>
	         <th style="border-left: 0px;"></th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left" width="55px">Record Key</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left" width="310px">Name/Title</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Status</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Class</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left">Created</th>
	         <th class="resultListHeader" style="border-bottom: 1px solid #bbbbbb;" align="left" width="75px">Options</th>
	      </tr>
	      <?php
	      $num=0;
	      foreach ( $object_array as $key => $registry_object ) 
	      {
	      	$num = $num + 1;
	      	
	      	$dateCreated = date("g:i a, j M y", strtotime($registry_object['created']));
	      	$dateModified = date("g:i a, j M y", strtotime($registry_object['modified']));
	      	if (!$dateModified) $dateModified = '-';
			$statusSpan = getRegistryObjectStatusSpan($registry_object['status']);
	      	$registryObjectKey = $registry_object['key'];
			$registryObjectType = $registry_object['class'];
			$registryObjectTitle = $registry_object['title'];

	      	print("<tr id=\"row".$num."\" valign=\"middle\">\n");
	      	
	      	$cellAttrBase = ' onmouseover="recordOver(\'row'.$num.'\', false)" onmouseout="recordOut(\'row'.$num.'\', false)"';
	      	print("  <td".$cellAttrBase.">".$num."</td>\n");
	      	
	      	$cellAttributes = $cellAttrBase;
	      	$cellAttributes .= ($registry_object['status'] == "DRAFT" ? ' title="Edit this record"' : ' title="View this record in ORCA"');
	      	$cellAttributes .= ($registry_object['status'] == "DRAFT" ? ' onclick="window.location=\''.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?key='.esc(rawurlencode($registryObjectKey), true).'\'"' 
	      															  : ' onclick="window.location=\''.eAPP_ROOT .'orca/view.php?key='.esc(rawurlencode($registryObjectKey), true).'\'"');
	      	$cellAttributes .= ' class="recordLink" style="font-size:0.8em;"';
			print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;"><div style="overflow:hidden;white-space: nowrap; width:65px;"><a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?key='.esc(rawurlencode($registryObjectKey), true).'" title="'.esc($registryObjectKey).'" style="color:black;">'.esc($registryObjectKey)."</a></div></td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;"><div style="overflow:hidden;white-space: nowrap; width:310px;"><a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?key='.esc(rawurlencode($registryObjectKey), true).'" title="'.esc($registryObjectTitle).'" style="color:black;">'.esc($registryObjectTitle)."</a></div></td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$statusSpan."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$registry_object['class']."</td>\n");
	      	print('  <td'.$cellAttributes.' align="left" style="padding-right: 5px;">'.$dateCreated."</td>\n");
	      	print('  <td'.$cellAttrBase.' align="left" style="padding-right: 5px;">');

	      	// DRAFT records cannot be viewed
	      	if ($registry_object['status'] == "DRAFT") {
	      		print ('    <a href="javascript:alert(\'Draft records cannot be viewed in ORCA until editing is completed. Choose Edit instead!\');" style="cursor:help;" ontitle="Draft records cannot be viewed in ORCA"><img src="'.(eAPP_ROOT . "orca/_images/preview_disabled.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	
	      	// ACTIVE/APPROVED records can be viewed in ORCA (and consequently RDA)
	      	} else {
				print ('    <a href="'.eAPP_ROOT.'orca/view.php?key='.esc(rawurlencode($registryObjectKey)).'" title="View this record in ORCA"><img src="'.(eAPP_ROOT . "orca/_images/preview.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	}
	      	
	      	// ACTIVE records which already have a DRAFT (i.e. have been edited before) cannot be edited directly
	      	if ($registry_object['status'] == PUBLISHED && isset($draft_object_array[esc($registryObjectKey)])) {
				print('    <a href="" title="A draft for this record already exists!" onclick="alert(\'An editable draft for this record already exists!\');"><img src="'.(eAPP_ROOT . "orca/_images/edit_disabled.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	// All other records can be edited
	      	} else {
	      		print('    <a href="'.eAPP_ROOT .'orca/manage/add_'.strtolower($registry_object['class']).'_registry_object.php?key='.esc(rawurlencode($registryObjectKey)).'" title="Modify this Record"><img src="'.(eAPP_ROOT . "orca/_images/edit.png").'" width="15px" height="15px" /></a>&nbsp;');
	      	}
	      	
	      	// All types of records can be deleted!
	      	if ($registry_object['status'] == "DRAFT") {
	      		print('    <a onClick="if (confirm(\'Are you sure? This will irreversibly delete this draft and any changes will be lost!\')) { window.location.href=\''.eAPP_ROOT .'orca/manage/process_registry_object.php?task=delete&key='.esc(rawurlencode($registryObjectKey)).'\'; }" title="Delete this Draft" style="cursor:pointer;"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>');
	      	} else {
	      		print('    <a href="'.eAPP_ROOT .'orca/admin/registry_object_delete.php?key='.esc(rawurlencode($registryObjectKey)).'" title="Delete this Record"><img src="'.(eAPP_ROOT . "orca/_images/bin.png").'" width="15px" height="15px" /></a>');
	      	}
	      	
	      	print("</td>\n");
	      	print("</tr>\n");
	      }
	      ?>
	   </tbody>
	</table>
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

	
</div>
<?php 
}

function drawListCount($array) 
{
	print count($array)." record" . (count($array) > 1 ? 's' : '') . " found";
}

function compareRegObjectsByCreated($x, $y)
{	
 if ( @strtotime($x['created_when']) == @strtotime($y['created_when']) )
  return 0;
 else if ( @strtotime($x['created_when']) < @strtotime($y['created_when']) )
  return 1;
 else
  return -1;
}
function compareDraftsByCreated($x, $y)
{	
 if ( strtotime($x['created']) == strtotime($y['created']) )
  return 0;
 else if ( strtotime($x['created']) < strtotime($y['created']) )
  return 1;
 else
  return -1;
}

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';