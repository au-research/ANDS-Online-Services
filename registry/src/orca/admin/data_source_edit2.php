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

// Get the record from the database.

$data_Source = getQueryValue('data_source_key');


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
$title = $dataSource[0]['title'];
$ds_string = '<option value="'.$dataSourceKey.'" selected>'.$title.'</option>';
$uri = $dataSource[0]['uri'];
$providerType = $dataSource[0]['provider_type'];
$harvestMethod = $dataSource[0]['harvest_method'];
$oaiSet = $dataSource[0]['oai_set'];
if($dataSource[0]['time_zone_value']!='')
{
	$harvestDate = substr($dataSource[0]['time_zone_value'],0,16);
	$theZone = str_replace("+","",substr($dataSource[0]['time_zone_value'],20,strlen($dataSource[0]['time_zone_value'])));
}else{
	$harvestDate = formatDateTimeWithMask($dataSource[0]['harvest_date'], eDCT_FORMAT_ISO8601_DATE_TIME);
}
$harvestFrequency = $dataSource[0]['harvest_frequency'];
$contactName = $dataSource[0]['contact_name'];
$contactEmail = $dataSource[0]['contact_email'];
$notes = $dataSource[0]['notes'];
$recordOwner = $dataSource[0]['record_owner'];
$isil_value =  $dataSource[0]['isil_value'];
$push_to_nla = (string)$dataSource[0]['push_to_nla'];
$allowReverseInternalLinks = $dataSource[0]['allow_reverse_internal_links'];
$allowReverseExternalLinks = $dataSource[0]['allow_reverse_external_links'];
$newDateTimeZone = $dataSource[0]['time_zone_value'];
$create_primary_relationships = $dataSource[0]['create_primary_relationships'];
$class_1 = $dataSource[0]['class_1'];
$primary_key_1 = $dataSource[0]['primary_key_1']; 
$service_rel_1 = $dataSource[0]['service_rel_1']; 
$activity_rel_1 = $dataSource[0]['activity_rel_1'];
$collection_rel_1 = $dataSource[0]['collection_rel_1']; 
$party_rel_1 = $dataSource[0]['party_rel_1']; 
$class_2 = $dataSource[0]['class_2'];
$primary_key_2 = $dataSource[0]['primary_key_2']; 
$service_rel_2 = $dataSource[0]['service_rel_2']; 
$activity_rel_2 = $dataSource[0]['activity_rel_2'];
$collection_rel_2 = $dataSource[0]['collection_rel_2']; 
$party_rel_2 = $dataSource[0]['party_rel_2']; 
$assessementNotificationEmailAddr = $dataSource[0]['assessement_notification_email_addr'];
$autoPublish = $dataSource[0]['auto_publish'];
$qaFlag = $dataSource[0]['qa_flag'];

$errorMessages = '';
$dataSourceKeyLabelClass = '';
$titleLabelClass = '';
$uriLabelClass = '';
$providerTypeLabelClass = '';
$harvestMethodLabelClass = '';


$draft_array = getDraftRegistryObject(null, $dataSourceKey);
$draft_record_set = array(
						MORE_WORK_REQUIRED => 0,
						DRAFT => 0,
						SUBMITTED_FOR_ASSESSMENT => 0,
						ASSESSMENT_IN_PROGRESS => 0,
					);
if($draft_array)
{						
	foreach ($draft_array AS $record)
	{
		if (array_key_exists ( $record['status'] , $draft_record_set))
		{
			$draft_record_set[$record['status']] += 1;
		}	
	}
}
$numRegistryObjects = getRegistryObjectCount($dataSourceKey);
$numRegistryObjectsApproved = getRegistryObjectCount($dataSourceKey, null, null, APPROVED);


if( strtoupper(getPostedValue('action')) == "CANCEL" )
{
	responseRedirect("data_source_view.php?data_source_key=".urlencode($dataSourceKey));
}

if( strtoupper(getPostedValue('action')) == "SAVE" )
{
	//we need to set up the values to reset the time zone variables and display to be the selected values
	
	if(getPostedValue('harvest_date')){
		$newNum = str_replace(":",".",getPostedValue('theZone'));
		if($newNum>0)
		{
			$theString = '&nbsp;&nbsp;&nbsp;(GMT +'.str_replace(".",":",number_format($newNum,2)).')';
			$theNum = "+".$newNum;
		}else{
			$theString = '&nbsp;&nbsp;&nbsp;(GMT '.str_replace(".",":",number_format($newNum,2)).')';
			$theNum=$newNum;			
		}
		$newDateTimeZone = getPostedValue('harvest_date').":00 ".str_replace(".",":",$theNum);
	}
	
	$title = getPostedValue('title');
	if( $title == '' )
	{ 
		$titleLabelClass = gERROR_CLASS;
		$errorMessages .= "Title is a mandatory field.<br />";
	}
	
	$uri = getPostedValue('uri');
	if( $uri == '' )
	{ 
		$uriLabelClass = gERROR_CLASS;
		$errorMessages .= "URI is a mandatory field.<br />";
	}	

	$providerType = getPostedValue('provider_type');
	if( $providerType == '' )
	{ 
		$providerTypeLabelClass = gERROR_CLASS;
		$errorMessages .= "Provider Type is a mandatory field.<br />";
	}

	$harvestMethod = getPostedValue('harvest_method');
	if( $harvestMethod == '' )
	{ 
		$harvestMethodLabelClass = gERROR_CLASS;
		$errorMessages .= "Harvest Method is a mandatory field.<br />";
	}
	
	if( trim(getPostedValue('isil_value')) == '' && getPostedValue('push_to_nla'))
	{ 
		$pushNLALabelClass = gERROR_CLASS;
		$errorMessages .= "You must provide an ISIL if you wish to push party records to NLA.<br />";
	}
	$primary_key_1 = getPostedValue('primary_key_1');
	$class_1 = getPostedValue('class_1');
	$service_rel_1 = getPostedValue('service_rel_1');
	$activity_rel_1 = getPostedValue('activity_rel_1');
	$collection_rel_1 = getPostedValue('collection_rel_1');	
	$party_rel_1  = getPostedValue('party_rel_1');	
	$primary_key_2 = getPostedValue('primary_key_2');
	$class_2 = getPostedValue('class_2');
	$service_rel_2 = getPostedValue('service_rel_2');
	$activity_rel_2 = getPostedValue('activity_rel_2');
	$collection_rel_2 = getPostedValue('collection_rel_2');	
	$party_rel_2  = getPostedValue('party_rel_2');		
	if(trim(getPostedValue('class_1'))=='' && trim(getPostedValue('primary_key_1'))=='' && trim(getPostedValue('service_rel_1'))=='' && trim(getPostedValue('activity_rel_1'))=='' && trim(getPostedValue('collection_rel_1'))=='' && trim(getPostedValue('party_rel_1'))=='')
	{
		$_POST['create_primary_relationships'] = '0';
	}
	
	if( getPostedValue('create_primary_relationships') && (trim(getPostedValue('class_1'))==''||trim(getPostedValue('primary_key_1'))==''||trim(getPostedValue('service_rel_1'))==''||trim(getPostedValue('activity_rel_1'))==''||trim(getPostedValue('collection_rel_1'))==''||trim(getPostedValue('party_rel_1'))==''))
	{ 
		//echo getPostedValue('class_1')."::".getPostedValue('pprimary_key_1')."::".getPostedValue('service_rel_1')."::".getPostedValue('activity_rel_1')."::".getPostedValue('party_rel_1')."::".getPostedValue('collection_rel_1');
		$createPrimaryClass = gERROR_CLASS;
		$errorMessages .= "You must provide a class ,registered key and all relationship types for the primary relationship.<br />";		
	}	
	
	if(trim(getPostedValue('class_2'))=='' && (trim(getPostedValue('primary_key_2'))!=''||trim(getPostedValue('service_rel_2'))!=''||trim(getPostedValue('activity_rel_2'))!=''||trim(getPostedValue('collection_rel_2'))!=''||trim(getPostedValue('party_rel_2'))!=''))
	{
		$createPrimaryClass = gERROR_CLASS;		
		$errorMessages .= "You must provide a class ,registered key and all relationship types for the primary relationship.<br />";	
	}
	
	if( trim(getPostedValue('class_2'))!=''&&(trim(getPostedValue('primary_key_2'))==''||trim(getPostedValue('service_rel_2'))==''||trim(getPostedValue('activity_rel_2'))==''||trim(getPostedValue('collection_rel_2'))==''||trim(getPostedValue('party_rel_2'))==''))
	{ 
		//echo getPostedValue('class_2')."::".getPostedValue('pprimary_key_2')."::".getPostedValue('service_rel_2')."::".getPostedValue('activity_rel_2')."::".getPostedValue('party_rel_2')."::".getPostedValue('collection_rel_2');
		$createPrimaryClass = gERROR_CLASS;
		$errorMessages .= "You must provide a class ,registered key and all relationship types for the primary relationship.<br />";	
	}		
	if( !getPostedValue('create_primary_relationships')	)
	{

		//if user has changed their mind about primary relationships after entering data - we don't want to store their data
		$_POST['class_1']='';
		$_POST['primary_key_1']='';
		$_POST['service_rel_1']='';
		$_POST['party_rel_1']='';
		$_POST['activity_rel_1']='';
		$_POST['collection_rel_1']='';
		$_POST['class_2']='';
		$_POST['primary_key_2']='';
		$_POST['service_rel_2']='';
		$_POST['party_rel_2']='';
		$_POST['activity_rel_2']='';
		$_POST['collection_rel_2']='';								
	}


	if(trim(getPostedValue('primary_key_1'))&&getPostedValue('create_primary_relationships'))
	{
		$primarytest1 = getRegistryObject(trim(getPostedValue('primary_key_1')));

		if(!$primarytest1||$primarytest1[0]['data_source_key']!=$dataSourceKey||strtolower($primarytest1[0]['registry_object_class'])!=getPostedValue('class_1'))
		{
			$createPrimaryClass = gERROR_CLASS;
			$errorMessages .= "You must provide a published registry object key  (with correct class) from within this datasource for primary relationship 1<br />";					
		}
	}
	if(trim(getPostedValue('primary_key_2'))&&getPostedValue('create_primary_relationships'))
	{
		$primarytest2 = getRegistryObject(trim(getPostedValue('primary_key_2')));

		if(!$primarytest2||$primarytest2[0]['data_source_key']!=$dataSourceKey||strtolower($primarytest2[0]['registry_object_class'])!=getPostedValue('class_2'))
		{
			$createPrimaryClass = gERROR_CLASS;
			$errorMessages .= "You must provide a published registry object key (with correct class) from within this datasource for primary relationship 2<br />";					
		}
	}	
	$oaiSet = getPostedValue('oai_set');
	$harvestDate = getPostedValue('harvest_date');
	$harvestFrequency = getPostedValue('harvest_frequency');
	$contactName = getPostedValue('contact_name');
	$contactEmail = getPostedValue('contact_email');
	$notes = getPostedValue('notes');
	$isil_value = getPostedValue('isil_value');
	$push_to_nla = getPostedValue('push_to_nla');
	$allowReverseInternalLinks = getPostedValue('allow_reverse_internal_links');
	$allowReverseExternalLinks = getPostedValue('allow_reverse_external_links');
	$create_primary_relationships = getPostedValue('create_primary_relationships');
	
	$class_1 = getPostedValue('class_1');
	$service_rel_1 =  getPostedValue('service_rel_1');
	$activity_rel_1 =  getPostedValue('activity_rel_1');
	$party_rel_1 =  getPostedValue('party_rel_1');
	$collection_rel_1 =  getPostedValue('collection_rel_1');
	$class_2 = getPostedValue('class_2');
	$service_rel_2 =  getPostedValue('service_rel_2');
	$activity_rel_2 =  getPostedValue('activity_rel_2');
	$party_rel_2 =  getPostedValue('party_rel_2');
	$collection_rel_2 =  getPostedValue('collection_rel_2');	
	
	$assessementNotificationEmailAddr = getPostedValue('assessement_notification_email_addr');
	$autoPublishOld = $autoPublish;
	$qaFlagOld = $qaFlag;
	$autoPublish = getPostedValue('auto_publish');
	$qaFlag = getPostedValue('qa_flag');

	if( getPostedValue('record_owner') )
	{ 
		$recordOwner = getPostedValue('record_owner');
	}
	
	// Check the harvest method against the provider type.
	if( $providerType && $harvestMethod && !in_array($providerType, $gORCA_HARVEST_PROVIDER_SETS[$harvestMethod], true) )
	{
		$providerTypeLabelClass = gERROR_CLASS;
		$harvestMethodLabelClass = gERROR_CLASS;
		$errorMessages .= 'This Provider Type is not supported by this Harvest Method.<br />'; 
		$errorMessages .= 'This Harvest Method does not support this Provider Type.<br />';
	}
	
	if( $errorMessages == '' )
	{
		// Update the record.

		if(isset($newDateTimeZone))
		{
			$_POST['harvest_date'] = $newDateTimeZone;
		}
		$_POST['theZone'] = $_POST['harvest_date'];
		//unset($_POST['theZone']); //we need to remove this variable from the POST array as it does not get used in the actual insert.
		
		unset($_POST['object_relatedObject']);
		unset($_POST['object_primary_key_1_name']);			
		unset($_POST['object_primary_key_2_name']);
		unset($_POST['select_primary_key_1_class']);
		unset($_POST['select_primary_key_2_class']);		
		// we need to reset the harvest_date variable to remove the new datetimezone value set up to indicate it has a time zone. Add the seconds and then add the requested time zone
		//print("<pre>");
		//print_r($_POST);
		//print("</pre>");	
		//exit();
		$errors = updateDataSource();
		$errors .= updateRecordsForDataSource($dataSourceKey, $autoPublish, $autoPublishOld , $qaFlag , $qaFlagOld);

		if( $errors == "" )
		{
			responseRedirect('data_source_view.php?data_source_key='.urlencode($dataSourceKey));
		}
		else
		{
			$errorMessages .= $errors;
		}
	}
}
// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/data_source_functions.js"></script>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/jquery-ui-1.8.9.custom.min.js"></script>	
<form id="data_source_edit" action="data_source_edit.php?data_source_key=<?php print(urlencode($dataSourceKey)); ?>" method="post" onSubmit="return checkModalId(this)">
<table class="formTable" summary="Edit Data Source">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Edit Data Source</td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText"><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } ?>
	<tbody class="formFields">
	<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;">Account Administration Information</span>
		</td>
		</tr>
		<tr>
			<td>* Key:</td>
			<td><?php printSafe($dataSourceKey) ?>
			<input type="hidden" name="data_source_key" id="data_source_key" value="<?php printSafe($dataSourceKey) ?>" /></td>
		</tr>
		<tr>
			<td<?php print($titleLabelClass); ?>>* Title:</td>
			<td><input type="text" name="title" id="title" size="60" maxlength="255" value="<?php printSafe($title) ?>" /></td>
		</tr>
		<tr>
			<td class="">Record Owner:</td>
			<td>
				<?php if( userIsORCA_ADMIN() ) { ?>		
				<select name="record_owner" id="record_owner">
					<?php
					setChosenFromValue($recordOwner, '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$owners = getOrganisationalRoles();
					if( $owners )
					{
						foreach( $owners as $role )
						{
							setChosenFromValue($recordOwner, $role['role_id'], gITEM_SELECT);
							print("<option value=\"".esc($role['role_id'])."\"$gChosen>".esc($role['role_name'])."</option>\n");
						}
					}
					?>
				</select>
				<?php } else { 
						printSafe(getRoleName($recordOwner)." (".$recordOwner.")");
						print('<input type="hidden" name="record_owner" id="record_owner" value="'.esc($recordOwner).'">');
					  } ?>
			</td>
		</tr>
		<tr>
			<td class="">Contact Name:</td>
			<td><input type="text" name="contact_name" id="contact_name" size="60" maxlength="128" value="<?php printSafe($contactName) ?>" /></td>
		</tr>
		<tr>
			<td class="">Contact E-mail:</td>
			<td><input type="text" name="contact_email" id="contact_email" size="60" maxlength="128" value="<?php printSafe($contactEmail) ?>" /></td>
		</tr>
		<tr>
			<td class="">Notes:</td>
			<td><textarea name="notes" id="notes" cols="50" rows="5"><?php printSafe($notes) ?></textarea></td>
		</tr>
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;">Records Management Settings</span>
		</td>
		</tr>
		<tr>
			<td>Reverse Links:</td>
			<td><input type="hidden" id="allow_reverse_internal_links" name="allow_reverse_internal_links" value="<?php if($allowReverseInternalLinks == 'f'){print("0");}else{print("1");}?>"/> 
			<img id="allow_reverse_internal_links_image" src="../_images/gray_<?php if($allowReverseInternalLinks == 'f'){print("un");}?>checked.png" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>&nbsp;<label style="cursor:pointer" onclick="toggle_checkbox('allow_reverse_internal_links_image');">Automatically create reverse links within this data source</label></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="hidden" id="allow_reverse_external_links" name="allow_reverse_external_links" value="<?php if($allowReverseExternalLinks == 'f'){print("0");}else{print("1");}?>"/> <img id="allow_reverse_external_links_image" src="../_images/gray_<?php if($allowReverseExternalLinks== 'f'){print("un");}?>checked.png" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>&nbsp;<label style="cursor:pointer" onclick="toggle_checkbox('allow_reverse_external_links_image');">Automatically create reverse links from external data sources</label></td>
		</tr>
		<tr>
			<td <?php print($createPrimaryClass); ?>>Create primary relationships?:</td>
			<td>
			<input type="hidden" name="create_primary_relationships" id="create_primary_relationships" value="<?php if($create_primary_relationships=="t"||$create_primary_relationships=='1') { echo "1";}else{echo "0";}?>">
						<img id="create_primary_relationships_image" src="../_images/gray_<?php if($create_primary_relationships == 'f'){print("un");}?>checked.png" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>
		</tr>	
		<tr id="key_value_row_1" <?php if($create_primary_relationships=="t" || $create_primary_relationships=="1") {} else{ echo "style='display:none'";} ?>>
		<td></td>
		<td>
											<div id="searchDialog_object_primary_key_1" class="window" }' >
											<img src="../_images/error_icon.png" onClick='closeSearchModal("object_primary_key_1");' style="cursor:pointer; position:absolute; top:5px; right:5px; width:16px;" />
											<ul id="guideNotes_relatedObject" class="guideNotes" style="display: block; ">
											<li>The name search will only return the first 10 entries found in the database.<br/> To narrow down the returned results please ensure your text entries are as specific as possible.</li>
											</ul>
											<table class="rmdElementContainer" style="font-weight:normal;"> 
											<tbody class="formFields andsorange"> 			
												<tr>
												<td>
												Search by name:
												</td>
												<td>
													<input type="text" id="object_primary_key_1_name" autocomplete="on" name="object_primary_key_1_name" maxlength="512" size="30" />		
												</td>
												</tr>
												<tr>
												<td>
												Select object class:
												</td>
												<td>
												<select id="select_primary_key_1_class"  name="select_primary_key_1_class" >
												<option value="Collection" <?php if($class_1=="collection") echo " selected"?>>Collection</option>
												<option value="Party" <?php if($class_1=="party") echo " selected"?>>Party</option>
												<option value="Activity" <?php if($class_1=="activity") echo " selected"?>>Activity</option>
												<option value="Service" <?php if($class_1=="service") echo " selected"?>>Service</option>
												</select>
												</td>
												</tr>
												<tr>
												<td>
												Data source:
												</td>
												<td> <span style="color:#666666"><?php  echo $title; ?></span>
												<input type="hidden" id="select_primary_key_1_dataSource" value = "<?php  echo $dataSourceKey; ?>"/>
												
												</td>							
												</tr>
												<tr>
													<td>
														<input type="button" value="Choose Selected" onClick='setRelatedId("object_primary_key_1");'/>
													</td><td></td>
												</tr>
											</table>				
											</div>  
											<div class="mask" onclick="closeSearchModal('object_primary_key_1')" id="mask_object_primary_key_1"></div>
										<script>										
											//if ({$has_fragment} == false) {
											//	getElement('relation', [], 'object.relatedObject[%%SEQNUM1%%].', null, getNextSeq('relatedObject_%%SEQNUM1%%_relation'));
											//}
												addRelatedObjectAutocomplete('object_primary_key_1_name');
										</script>										
		<div align="left" style="width:350px;position:relative;float:left">
		<table width="255">
		<tr><td>Class : </td><td><select name="class_1" id="select_1_class" onChange='setTheDialog(this,"select_primary_key_1_class");'>
			<option value="" <?php if($class_1=="") echo " selected"?>></option>			
			<option value="party" <?php if($class_1=="party") echo " selected"?>>Party</option>	
			<option value="service" <?php if($class_1=="service") echo " selected"?>>Service</option>	
			<option value="activity" <?php if($class_1=="activity") echo " selected"?>>Activity</option>									
			<option value="collection" <?php if($class_1=="collection") echo " selected"?>>Collection</option>		
			</select> </td></tr>
		<tr><td>	Key : </td>
		<td> <input type="text" name="primary_key_1" id="object_primary_key_1_value" size="30" maxlength="128" value="<?php printSafe($primary_key_1) ?>" />
			<img name="relatedImg" src="../_images/preview.png" onClick='showSearchModal("object_primary_key_1");' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" /> 
													 </td></tr>
			
			<tr><td colspan="2">Relationship To: </td></tr>
			<tr><td>Collection: </td><td><input type="text" name="collection_rel_1" id="object_collection_rel_1" size="20" maxlength="20" value="<?php printSafe($collection_rel_1) ?>" /> 
		<img id="button_collection_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_collection_rel_1','RIFCS'+ 'Collection' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>
			<tr><td>Service:  </td><td><input type="text" name="service_rel_1" id="object_service_rel_1" size="20" maxlength="20" value="<?php printSafe($service_rel_1) ?>" /> 
			<img id="button_service_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_service_rel_1','RIFCS'+ 'Service' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>	
			<tr><td>Activity:  </td><td><input type="text" name="activity_rel_1" id="object_activity_rel_1" size="20" maxlength="20" value="<?php printSafe($activity_rel_1) ?>" /> 
			<img id="button_activity_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_activity_rel_1','RIFCS'+ 'Activity' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>	
			<tr><td>Party: </td><td><input type="text" name="party_rel_1" id="object_party_rel_1" size="20" maxlength="20" value="<?php printSafe($party_rel_1) ?>" />	 
			<img id="button_party_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_party_rel_1','RIFCS'+ 'Party' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>
		</table>	
		</div>
												<div id="searchDialog_object_primary_key_2" class="window">
											<img src="../_images/error_icon.png" onClick='closeSearchModal("object_primary_key_2");' style="cursor:pointer; position:absolute; top:5px; right:5px; width:16px;" />
											<ul id="guideNotes_relatedObject" class="guideNotes" style="display: block; ">
											<li>The name search will only return the first 10 entries found in the database.<br/> To narrow down the returned results please ensure your text entries are as specific as possible.</li>
											</ul>
											<table class="rmdElementContainer" style="font-weight:normal;" border="1"> 
											<tbody class="formFields andsorange"> 			
												<tr>
												<td>
												Search by name:
												</td>
												<td>
													<input type="text" id="object_primary_key_2_name" autocomplete="on" name="object_primary_key_2_name" maxlength="512" size="30" />		
												</td>
												</tr>
												<tr>
												<td>
												Select object class:
												</td>
												<td>
												<select id="select_primary_key_2_class"  name="select_primary_key_2_class" >
												<option value="Collection"<?php if($class_2=="collection") echo " selected"?>>Collection</option>
												<option value="Party"<?php if($class_2=="party") echo " selected"?>>Party</option>
												<option value="Activity"<?php if($class_2=="activity") echo " selected"?>>Activity</option>
												<option value="Service"<?php if($class_2=="service") echo " selected"?>>Service</option>
												</select>
												</td>
												</tr>
												<tr>
												<td>
												Data source:
												</td>
												<td><span style="color:#666666"><?php  echo $title; ?></span>
												<input type="hidden" id="select_primary_key_2_dataSource" value="<?php  echo $dataSourceKey; ?>"/>
												</select>
												</td>							
												</tr>
												<tr>
													<td>
														<input type="button" value="Choose Selected" onClick='setRelatedId("object_primary_key_2");'/>
													</td><td></td>
												</tr>
											</table>				
											</div>  
											<div class="mask" onclick="closeSearchModal('object_primary_key_2')" id="mask_object_primary_key_2"></div>
											
										<script>										
											//if ({$has_fragment} == false) {
											//	getElement('relation', [], 'object.relatedObject[%%SEQNUM1%%].', null, getNextSeq('relatedObject_%%SEQNUM1%%_relation'));
											//}
												addRelatedObjectAutocomplete('object_primary_key_2_name');
										</script>												
		<div align="left"  style="width:320px;position:relative;float:right">
		<table width="255">
		<tr><td>Class : </td><td><select name="class_2"  id="select_2_class"   onChange='setTheDialog(this,"select_primary_key_2_class");'>
			<option value="" <?php if($class_2=="") echo " selected"?>></option>			
			<option value="party" <?php if($class_2=="party") echo " selected"?>>Party</option>	
			<option value="service" <?php if($class_2=="service") echo " selected"?>>Service</option>	
			<option value="activity" <?php if($class_2=="activity") echo " selected"?>>Activity</option>									
			<option value="collection" <?php if($class_2=="collection") echo " selected"?>>Collection</option>		
			</select> </td></tr>
		<tr><td>	Key : </td>
		<td> <input type="text" name="primary_key_2" id="object_primary_key_2_value" size="30" maxlength="128" value="<?php printSafe($primary_key_2) ?>" />
		<img name="relatedImg" src="../_images/preview.png" onClick='showSearchModal("object_primary_key_2");' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" /> </td></tr>
			
			<tr><td colspan="2">Relationship To: </td></tr>
			<tr><td>Collection: </td><td><input type="text" name="collection_rel_2" id="object_collection_rel_2" size="20" maxlength="20" value="<?php printSafe($collection_rel_2) ?>" /> 
		<img id="button_collection_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_collection_rel_2','RIFCS'+ 'Collection' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>
			<tr><td>Service:  </td><td><input type="text" name="service_rel_2" id="object_service_rel_2" size="20" maxlength="20" value="<?php printSafe($service_rel_2) ?>" /> 
			<img id="button_service_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_service_rel_2','RIFCS'+ 'Service' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>	
			<tr><td>Activity:  </td><td><input type="text" name="activity_rel_2" id="object_activity_rel_2" size="20" maxlength="20" value="<?php printSafe($activity_rel_2) ?>" /> 
			<img id="button_activity_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_activity_rel_2','RIFCS'+ 'Activity' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>	
			<tr><td>Party: </td><td><input type="text" name="party_rel_2" id="object_party_rel_2" size="20" maxlength="20" value="<?php printSafe($party_rel_2) ?>" />	 
			<img id="button_party_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_party_rel_2','RIFCS'+ 'Party' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />			
			</td></tr>
		</table>			

		
		</div>	
		
		</td>	</tr>			
		<tr>
			<td <?php print($pushNLALabelClass); ?>>Party records to NLA?:</td>
			<td>
			<input type="hidden" name="push_to_nla" id="push_to_nla" value="<?php if($push_to_nla=="t" || $push_to_nla=="1") { echo "1";}else{echo "0";}?>">
			<img id="push_to_nla_image" src="../_images/gray_<?php if($push_to_nla=="1" || $push_to_nla=="t"){ echo "checked.png";}else{echo "unchecked.png";}?>" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>
		</tr>			
		<tr id="isil_value_row" <?php if($push_to_nla=="f" || $push_to_nla=="0") { echo "style='display:none'";} ?> >
			<td>ISIL:</td>		
			<td><input type="text" name="isil_value" id="isil_value" size="60" maxlength="128" value="<?php printSafe($isil_value) ?>" /> 	<a href="http://ands.org.au/guides/ardc-party-infrastructure-awareness.html" target="blank">What's this?</a></td>	
		</tr>
		<tr>
			<td>Manually Publish records?:</td>
			<td><input type="hidden" id="auto_publish" name="auto_publish" value="<?php if($autoPublish == 'f' || $autoPublish == '0'){print("0");}else{print("1");}?>"/> <img id="auto_publish_image" src="../_images/gray_<?php if($autoPublish == 'f' || $autoPublish == '0'){print("un");}?>checked.png" style="cursor:pointer" onclick="show_info(this.id);"/></td>
		</tr>
		<tr>
			<td><span <?php if( !(userIsORCA_ADMIN() || userIsORCA_LIAISON())) { print('style="color:#888"');}?>>Quality Assessment Required?</span></td>
			<td><input type="hidden" id="qa_flag" name="qa_flag" value="<?php if($qaFlag == 'f' || $qaFlag == '0'){print("0");}else{print("1");}?>"/> <img id="qa_flag_image" src="../_images/gray_<?php if($qaFlag == 'f' || $qaFlag == '0'){print("un");}?>checked.png" style="cursor:pointer" <?php if( (userIsORCA_ADMIN() || userIsORCA_LIAISON())) { print('onclick="show_info(this.id);"');}?>/></td>
		</tr>
		<tr>
			<td><span <?php if( !(userIsORCA_ADMIN() || userIsORCA_LIAISON())) { print('style="color:#888"');}?>>Assessment Notification Email:</span></td>		
			<td><input type="text" name="assessement_notification_email_addr" id="assessement_notification_email_addr" size="60" maxlength="128" value="<?php printSafe($assessementNotificationEmailAddr) ?>" <?php if( !(userIsORCA_ADMIN() || userIsORCA_LIAISON())) { print('disabled="disabled"');}?>/></td>	
		</tr>	
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;">Harvester Settings</span>
		</td>
		</tr>	
		
				
		<tr>
			<td<?php print($uriLabelClass); ?>>* URI:</td>
			<td><input type="text" name="uri" id="uri" size="60" maxlength="255" value="<?php printSafe($uri) ?>" /></td>
		</tr>
		<tr>
			<td<?php print($providerTypeLabelClass); ?>>* Provider Type:</td>
			<td>
				<select name="provider_type" id="provider_type">
					<?php
					setChosenFromValue($providerType, '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$providerTypes = getDataProviderTypes();
					if( $providerTypes )
					{
						foreach( $providerTypes as $key => $description )
						{
							setChosenFromValue($providerType, $key, gITEM_SELECT);
							print("<option value=\"".esc($key)."\"$gChosen>".esc($description)."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td<?php print($harvestMethodLabelClass); ?>>* Harvest Method:</td>
			<td>
				<select name="harvest_method" id="harvest_method" onchange="setHarvestMethodDependents()">
					<?php
					setChosenFromValue($harvestMethod, '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$harvestMethods = getHarvestMethods();
					if( $harvestMethods )
					{
						foreach( $harvestMethods as $key => $description )
						{
							setChosenFromValue($harvestMethod, $key, gITEM_SELECT);
							print("<option value=\"".esc($key)."\"$gChosen>".esc($description)."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="oai_set_row">
			<td class="">OAI Set:</td>
			<td><input type="text" name="oai_set" id="oai_set" size="30" maxlength="128" value="<?php printSafe($oaiSet) ?>" /></td>
		</tr>
		<tr id="harvest_date_row">
			<td class="">Harvest Date:</td>
			<?php 		
				$origin_dt = new DateTime(date("y-m-d h:s",time())) ;
			    $remote_dtz = new DateTimeZone('GMT');
			    $origin_dtz = new DateTimeZone(timezone_name_get(date_timezone_get($origin_dt)));			    
    			$remote_dt = new DateTime("now",$remote_dtz);
    			$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    			$current = $offset/60/60;	
    			if($current>0)$current = "+".$current;
				$currentZone = "&nbsp;&nbsp;(GMT ".$current.")";
				$currentNum = number_format($current);
				if(isset($theZone))
				{
					$currentNum = $theZone;
					if($theZone>0)$theZone = "+".$theZone;
					$currentZone = "&nbsp;&nbsp;(GMT ".$theZone.")"; 
				}
			?>			
			<td><?php drawDateTimeZoneInput('harvest_date', $harvestDate, eDCT_FORMAT_ISO8601_DATE_TIME."X") ?>
			<span id="gmtZone" class="inputFormat"><?php if(isset($theString)){ echo $theString;} else { echo $currentZone ;} ?> </span>
			<input name="theZone" id="theZone" type="hidden" value="<?php if(isset($newNum)){echo $newNum;}else { echo $currentNum ; }?>"/>
			</td>
		</tr>		

		<tr id="harvest_frequency_row">
			<td class="">Harvest Frequency:</td>
			<td>
				<select name="harvest_frequency" id="harvest_frequency">
					<?php
					setChosenFromValue($harvestFrequency, '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$harvestFrequencies = getHarvesterFrequencies();
					if( $harvestFrequencies )
					{
						foreach( $harvestFrequencies as $frequency )
						{
							setChosenFromValue($harvestFrequency, $frequency, gITEM_SELECT);
							print("<option value=\"".esc($frequency)."\"$gChosen>".esc($frequency)."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>


		

	</tbody></table>
	<table class="formTable" summary="Edit Data Source">
	<tbody>		

	</tbody>
	<tbody>
		<tr>
			<td width="128"></td>
			<td><input type="submit" name="action" value="Cancel" />&nbsp;&nbsp;<input type="submit" name="action" value="Save"  onClick="return nlaPushCheck();"/>&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory.<br />
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" id="auto_publish_old" name="auto_publish_old" value="<?php if($autoPublish == 'f' || $autoPublish == '0'){print("0");}else{print("1");}?>"/>
<input type="hidden" id="qa_flag_old" name="qa_flag_old" value="<?php if($qaFlag == 'f' || $qaFlag == '0'){print("0");}else{print("1");}?>"/> 
<input type="hidden" id="numRegistryObjects" name="numRegistryObjects" value="<?php echo $numRegistryObjects; ?>"/>
<input type="hidden" id="numRegistryObjectsApproved" name="numRegistryObjectsApproved" value="<?php echo $numRegistryObjectsApproved; ?>"/>					
<?php
foreach ($draft_record_set AS $key => $value)
{
 print('<input type="hidden" id="'.$key.'" name="'.$key.'" value="'.$value.'"/>'."\n");	
}
?>
</form>
<script type="text/javascript">setHarvestMethodDependents();</script>

<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
