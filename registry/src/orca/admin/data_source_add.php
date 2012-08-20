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
	
$errorMessages = '';
$dataSourceKeyLabelClass = '';
$titleLabelClass = '';
$uriLabelClass = '';
$providerTypeLabelClass = '';
$harvestMethodLabelClass = '';
$primaryRelationshipClass = '';
$createPrimaryClass = '';
$pushNLALabelClass ='';
$dateLabelClass='';

if(getPostedValue('action'))
{	
	$allow_reverse_internal_links = getPostedValue('allow_reverse_internal_links');
	//print("allow_reverse_internal_links " . $allow_reverse_internal_links);
	if($allow_reverse_internal_links == '1')
	$allow_reverse_internal_links = 1;
	else
	$allow_reverse_internal_links = 0;

}
else {
	$allow_reverse_internal_links = 1;

}

$allow_reverse_external_links = getPostedValue('allow_reverse_external_links');
if($allow_reverse_external_links == '1')
$allow_reverse_external_links = 1;
else 
$allow_reverse_external_links = 0;

if ( strtoupper(getPostedValue('action')) == "SAVE" )
{
	//we need to set up the values to reset the time zone variables and display to be the selected values
	if(getPostedValue('harvest_date')&&getPostedValue('harvest_method')!='DIRECT'&&getPostedValue('harvest_method')!=''){
		$newNum = getPostedValue('theZone');
		$pattern = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";
		if ( preg_match( $pattern, getPostedValue('harvest_date') ) ) 
		{
			$formatOK = true;
		}else{
			$dateLabelClass = gERROR_CLASS;
			$errorMessages .= "Date format must be W3CDTF.<br />";
		}		
		if($newNum>0)
		{
			$theString = '&nbsp;&nbsp;&nbsp;(GMT +'.str_replace(".",":",number_format($newNum,2)).')';
			$theNum = "+".$newNum;
		}else{
			$theString = '&nbsp;&nbsp;&nbsp;(GMT '.str_replace(".",":",number_format($newNum,2)).')';
			$theNum=$newNum;			
		}
		$newDateTimeZone = str_replace("Z",$theNum,getPostedValue('harvest_date'));
	}
	if(getPostedValue('harvest_method')=='DIRECT')
	{
		$newDateTimeZone ='';
	}
	if( getPostedValue('data_source_key') == '' )
	{ 
		$dataSourceKeyLabelClass = gERROR_CLASS;
		$errorMessages .= "Key is a mandatory field.<br />";
	}
	else
	{
		// Check that a record with this key doesn't already exist.
		$DataSource = getDataSources(getPostedValue('data_source_key'), null);
		if( $DataSource )
		{
			$dataSourceKeyLabelClass = gERROR_CLASS;
			$errorMessages .= 'A Data Source with this Key already exists.<br />';
		}
	}

	if( getPostedValue('title') == '' )
	{ 
		$titleLabelClass = gERROR_CLASS;
		$errorMessages .= "Title is a mandatory field.<br />";
	}

	if( getPostedValue('uri') == '' )
	{ 
		$uriLabelClass = gERROR_CLASS;
		$errorMessages .= "URI is a mandatory field.<br />";
	}	
	
	if(getPostedValue('uri')!='' && (!filter_var(getPostedValue('uri'), FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) || strpos(getPostedValue('uri'), "file://")===0))
  	{
		$uriLabelClass = gERROR_CLASS;
		$errorMessages .= "URI <em>".filter_var(getPostedValue('uri'))."</em> is not a valid URI.<br />";
  	}
	if( getPostedValue('provider_type') == '' )
	{ 
		$providerTypeLabelClass = gERROR_CLASS;
		$errorMessages .= "Provider Type is a mandatory field.<br />";
	}

	if( getPostedValue('harvest_method') == '' )
	{ 
		$harvestMethodLabelClass = gERROR_CLASS;
		$errorMessages .= "Harvest Method is a mandatory field.<br />";
	}
	
	// Check the harvest method against the provider type.
	if( getPostedValue('provider_type') && getPostedValue('harvest_method') && !in_array(getPostedValue('provider_type'), $gORCA_HARVEST_PROVIDER_SETS[getPostedValue('harvest_method')], true) )
	{
		$providerTypeLabelClass = gERROR_CLASS;
		$harvestMethodLabelClass = gERROR_CLASS;
		$errorMessages .= 'This Provider Type is not supported by this Harvest Method.<br />'; 
		$errorMessages .= 'This Harvest Method does not support this Provider Type.<br />';
	}
	
	if( trim(getPostedValue('isil_value')) == '' && getPostedValue('push_to_nla'))
	{ 
		$pushNLALabelClass = gERROR_CLASS;
		$errorMessages .= "You must provide an ISIL if you wish to push party records to NLA.<br />";
	}
	if( getPostedValue('create_primary_relationships') && (trim(getPostedValue('class_1'))==''||trim(getPostedValue('party_key_1'))==''||trim(getPostedValue('service_rel_1'))==''||trim(getPostedValue('activity_rel_1'))==''||trim(getPostedValue('collection_rel_1'))==''||trim(getPostedValue('party_rel_1'))==''))
	{ 
		$createPrimaryClass = gERROR_CLASS;
		$errorMessages .= "You must provide a registered key, class and relationship types for the primary relationship.<br />";		
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
	//print("ERRRRRRRR" . $allow_reverse_internal_links . "   " . $allow_reverse_external_links);
	if( !$errorMessages )
	{
		// Insert the new record.
		// we need to reset the harvest_date variable to remove the new datetimezone value set up to indicate it has a time zone. Add the seconds and then add the requested time zone
		if(isset($newDateTimeZone))
		{
			$_POST['harvest_date'] = $newDateTimeZone;
		}
		$_POST['theZone'] = $_POST['harvest_date'];
		//unset($_POST['theZone']); //we need to remove this variable from the POST array as it does not get used in the actual insert.
		unset($_POST['object_relatedObject']);

		$errors = insertDataSource();
		if( $errors == "" )
		{
			responseRedirect('data_source_view.php?data_source_key='.urlencode(getPostedValue('data_source_key')));
		}
		else
		{
			$errorMessages = $errors;
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
<form id="data_source_add" action="data_source_add.php" method="post">
<table class="formTable" summary="Add Data Source">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Add Data Source</td>
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
			<td<?php print($dataSourceKeyLabelClass); ?>>* Key:</td>
			<td><input type="text" name="data_source_key" id="data_source_key" size="60" maxlength="255" value="<?php printSafe(getPostedValue('data_source_key')) ?>" /></td>
		</tr>
		<tr>
			<td<?php print($titleLabelClass); ?>>* Title:</td>
			<td><input type="text" name="title" id="title" size="60" maxlength="255" value="<?php printSafe(getPostedValue('title')) ?>" /></td>
		</tr>
		<tr>
			<td class="">Record Owner:</td>
			<td>
				<select name="record_owner" id="record_owner">
					<?php
					setChosen('record_owner', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$owners = getOrganisationalRoles();
					if( $owners )
					{
						foreach( $owners as $role )
						{
							setChosen('record_owner', $role['role_id'], gITEM_SELECT);
							print("<option value=\"".esc($role['role_id'])."\"$gChosen>".esc($role['role_name'])."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="">Contact Name:</td>
			<td><input type="text" name="contact_name" id="contact_name" size="60" maxlength="128" value="<?php printSafe(getPostedValue('contact_name')) ?>" /></td>
		</tr>
		<tr>
			<td class="">Contact E-mail:</td>
			<td><input type="text" name="contact_email" id="contact_email" size="60" maxlength="128" value="<?php printSafe(getPostedValue('contact_email')) ?>" /></td>
		</tr>
	
		<tr>
			<td class="">Notes:</td>
			<td><textarea name="notes" id="notes" cols="50" rows="5"><?php printSafe(getPostedValue('notes')) ?></textarea></td>
		</tr>
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;">Records Management Settings</span>
		</td>
		</tr>
		<tr>
			<td>Reverse Links:</td>
			<td><input type="hidden" id="allow_reverse_internal_links" name="allow_reverse_internal_links" value="<?php if($allow_reverse_internal_links === 0){print("0");}else{print("1");}?>"/> <img id="allow_reverse_internal_links_image" src="../_images/gray_<?php if($allow_reverse_internal_links === 0){print("un");}?>checked.png" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>&nbsp;<label style="cursor:pointer" onclick="toggle_checkbox('allow_reverse_internal_links_image');">Automatically create reverse links within this data source</label></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="hidden" id="allow_reverse_external_links" name="allow_reverse_external_links" value="<?php if($allow_reverse_external_links === 0){print("0");}else{print("1");}?>"/> <img id="allow_reverse_external_links_image" src="../_images/gray_<?php if($allow_reverse_external_links === 0){print("un");}?>checked.png" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>&nbsp;<label style="cursor:pointer" onclick="toggle_checkbox('allow_reverse_external_links_image');">Automatically create reverse links from external data sources</label></td>
		</tr>
		<!--  It has been decided that this functionality is not required for the add data source screen, however the datadase insert function now requires 34 paramteres. -->		
		<tr style='display:none'>
			<td <?php print($createPrimaryClass); ?>>Create primary relationships?:</td>
			<td>
			<input type="hidden" name="create_primary_relationships" id="create_primary_relationships" value="<?php if(getPostedValue('create_primary_relationships')=="t") { echo "1";}else{echo "0";}?>">
			<img id="create_primary_relationships_image" src="../_images/gray_<?php if(getPostedValue('create_primary_relationships')=="1"||getPostedValue('create_primary_relationships')=="f"){ echo "checked.png";}else{echo "unchecked.png";}?>" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>
		</tr>	
		<tr id="key_value_row_1" <?php if(getPostedValue('create_primary_relationships')=="t" || getPostedValue('create_primary_relationships')=="1") {} else{ echo "style='display:none'";} ?>>
		<td></td>
		<td>
	
										
		<div align="left" style="width:350px;position:relative;float:left">
		<table width="255">
		<tr><td>Class : </td><td><select name="class_1" id="class_1" >
			<option value="" <?php if(strtolower(getPostedValue('class_1'))=="") echo " selected"?>></option>			
			<option value="party" <?php if(strtolower(getPostedValue('class_1'))=="party") echo " selected"?>>Party</option>	
			<option value="service" <?php if(strtolower(getPostedValue('class_1'))=="service") echo " selected"?>>Service</option>	
			<option value="activity" <?php if(strtolower(getPostedValue('class_1'))=="activity") echo " selected"?>>Activity</option>									
			<option value="collection" <?php if(strtolower(getPostedValue('class_1'))=="collection") echo " selected"?>>Collection</option>		
			</select> </td></tr>
		<tr><td>	Key : </td>
		<td> <input type="text" name="primary_key_1" id="primary_key_1" size="30" maxlength="128" value="<?php printSafe(getPostedValue('primary_key_1')) ?>" />
			<img name="relatedImg" src="../_images/preview.png" onClick='showSearchModal("primary_key_2");' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" /> 
													 </td></tr>
			
			<tr><td colspan="2">Relationship To: </td></tr>
			<tr><td>Collection: </td><td><input type="text" name="collection_rel_1" id="object_collection_rel_1" size="20" maxlength="20" value="<?php printSafe(getPostedValue('collection_rel_1')) ?>" /> 
			<img id="button_collection_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_collection_rel_1','RIFCS'+ 'Collection' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>
			<tr><td>Service:  </td><td><input type="text" name="service_rel_1" id="object_service_rel_1" size="20" maxlength="20" value="<?php printSafe(getPostedValue('service_rel_1')) ?>" /> 
			<img id="button_service_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_service_rel_1','RIFCS'+ 'Service' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>	
			<tr><td>Activity:  </td><td><input type="text" name="activity_rel_1" id="object_activity_rel_1" size="20" maxlength="20" value="<?php printSafe(getPostedValue('activity_rel_1')) ?>" />
			<img id="button_activity_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_activity_rel_1','RIFCS'+ 'Activity' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>	
			<tr><td>Party: </td><td><input type="text" name="party_rel_1" id="object_party_rel_1" size="20" maxlength="20" value="<?php printSafe(getPostedValue('party_rel_1')) ?>" />
			<img id="button_party_rel_1" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_party_rel_1','RIFCS'+ 'Party' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>
			
		</table>	
		</div>
		<div align="left"  style="width:320px;position:relative;float:right">
		<table width="255">
		<tr><td>Class : </td><td><select name="class_2" id="class_2" >
			<option value="" <?php if(strtolower(getPostedValue('class_2'))=="") echo " selected"?>></option>			
			<option value="party" <?php if(strtolower(getPostedValue('class_2'))=="party") echo " selected"?>>Party</option>	
			<option value="service" <?php if(strtolower(getPostedValue('class_2'))=="service") echo " selected"?>>Service</option>	
			<option value="activity" <?php if(strtolower(getPostedValue('class_2'))=="activity") echo " selected"?>>Activity</option>									
			<option value="collection" <?php if(strtolower(getPostedValue('class_2'))=="collection") echo " selected"?>>Collection</option>		
			</select> </td></tr>
		<tr><td>	Key : </td><td> <input type="text" name="primary_key_2" id="primary_key_2" size="30" maxlength="128" value="<?php printSafe(getPostedValue('primary_key_2')) ?>" />
		<img name="relatedImg" src="../_images/preview.png" onClick='showSearchModal("primary_key_2");' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" /> </td></tr>
			
			<tr><td colspan="2">Relationship To: </td></tr>
			<tr><td>Collection: </td><td><input type="text" name="collection_rel_2" id="object_collection_rel_2" size="20" maxlength="20" value="<?php printSafe(getPostedValue('collection_rel_2')) ?>" /> 
			<img id="button_collection_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_collection_rel_2','RIFCS'+ 'Collection' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>
			<tr><td>Service:  </td><td><input type="text" name="service_rel_2" id="object_service_rel_2" size="20" maxlength="20" value="<?php printSafe(getPostedValue('service_rel_2')) ?>" /> 
			<img id="button_service_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_service_rel_2','RIFCS'+ 'Service' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>	
			<tr><td>Activity:  </td><td><input type="text" name="activity_rel_2" id="object_activity_rel_2" size="20" maxlength="20" value="<?php printSafe(getPostedValue('activity_rel_1')) ?>" />
			<img id="button_activity_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_activity_rel_2','RIFCS'+ 'Activity' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>	
			<tr><td>Party: </td><td><input type="text" name="party_rel_2" id="object_party_rel_2" size="20" maxlength="20" value="<?php printSafe(getPostedValue('party_rel_1')) ?>" />
			<img id="button_party_rel_2" src="../_images/buttons/dropdown_in.png" onClick="addVocabComplete('object_party_rel_2','RIFCS'+ 'Party' +'RelationType');toggleDropdown(this.id);" class='cursorimg' style="vertical-align:bottom; height:16px; width:16px;" />
			</td></tr>
		</table>			

		
		</div>	
		
		</td>	</tr>	

		<tr>
			<td <?php print($pushNLALabelClass); ?>>Party records to NLA?:</td>
			<td>
			<input type="hidden" name="push_to_nla" id="push_to_nla" value="<?php if(getPostedValue('push_to_nla')=="t") { echo "1";}else{echo "0";}?>">
			<img id="push_to_nla_image" src="../_images/gray_<?php if(getPostedValue('push_to_nla')=="1"||getPostedValue('push_to_nla')=="f"){ echo "checked.png";}else{echo "unchecked.png";}?>" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/>
		</tr>			
		<tr id="isil_value_row" <?php if(getPostedValue('push_to_nla')=="t" || getPostedValue('push_to_nla')=="1") {} else{ echo "style='display:none'";} ?> >
			<td>ISIL:</td>		
			<td><input type="text" name="isil_value" id="isil_value" size="60" maxlength="128" value="<?php printSafe(getPostedValue('isil_value')) ?>" /> 	<a href="http://ands.org.au/guides/ardc-party-infrastructure-awareness.html" target="blank">What's this?</a></td>	
		</tr>
		<tr>
			<td>Manually Publish records?:</td>
			<td><input type="hidden" id="auto_publish" name="auto_publish" value="<?php if(getPostedValue('auto_publish') == 't' || getPostedValue('auto_publish') == '1'){print("1");}else{print("0");}?>"/> <img id="auto_publish_image" src="../_images/gray_<?php if(getPostedValue('auto_publish') == 't' || getPostedValue('auto_publish') == '1'){print("checked.png");} else {print("unchecked.png");}?>" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/></td>
		</tr>
		<tr>
			<td>Quality Assessment Required?</td>
			<td><input type="hidden" id="qa_flag" name="qa_flag" value="<?php if(getPostedValue('qa_flag') == 'f' || getPostedValue('qa_flag') == '0'){print("0");}else{print("1");}?>"/> <img id="qa_flag_image" src="../_images/gray_<?php if(getPostedValue('qa_flag') == 'f' || getPostedValue('qa_flag') == '0'){print("un");}?>checked.png" style="cursor:pointer" onclick="toggle_checkbox(this.id);"/></td>
		</tr>
		<tr>
			<td>Assessment Notification Email:</td>		
			<td><input type="text" name="assessement_notification_email_addr" id="assessement_notification_email_addr" size="60" maxlength="128" value="<?php printSafe(getPostedValue('assessement_notification_email_addr')) ?>" /></td>	
		</tr>	
		<tr style="border-bottom:2px solid black;">
		<td colspan="2"><span style="float:left;">Harvester Settings</span>
		</td>
		</tr>			
		<tr>
			<td<?php print($uriLabelClass); ?>>* URI:</td>
			<td><input type="text" name="uri" id="uri" size="60" maxlength="255" value="<?php printSafe(getPostedValue('uri')) ?>" /></td>
		</tr>
		<tr>
			<td<?php print($providerTypeLabelClass); ?>>* Provider Type:</td>
			<td>
				<select name="provider_type" id="provider_type">
					<?php
					setChosen('provider_type', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$providerTypes = getDataProviderTypes();
					if( $providerTypes )
					{
						foreach( $providerTypes as $key => $description )
						{
							setChosen('provider_type', $key, gITEM_SELECT);
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
					setChosen('harvest_method', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$harvestMethods = getHarvestMethods();
					if( $harvestMethods )
					{
						foreach( $harvestMethods as $key => $description )
						{
							setChosen('harvest_method', $key, gITEM_SELECT);
							print("<option value=\"".esc($key)."\"$gChosen>".esc($description)."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="oai_set_row">
			<td class="">OAI Set:</td>
			<td><input type="text" name="oai_set" id="oai_set" size="30" maxlength="128" value="<?php printSafe(getPostedValue('oai_set')) ?>" /></td>
		</tr>
		<tr id="harvest_date_row">
			<td <?php print($dateLabelClass); ?>>Harvest Date:</td>
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
			?>			
		<!-- <td><?php drawDateTimeZoneInput('harvest_date', getPostedValue('harvest_date'), eDCT_FORMAT_ISO8601_DATE_TIME."X") ?> -->	
				<td ><?php drawDateTimeZoneInput('harvest_date', getPostedValue('harvest_date'), eDCT_FORMAT_ISO8601_DATETIMESEC_UTC) ?></p>		
			<span id="gmtZone" class="inputFormat"><?php if(isset($theString)){ echo $theString;} else { echo $currentZone; } ?></span>
			<input name="theZone" id="theZone" type="hidden" value="<?php if(isset($newNum)){echo $newNum;}else { echo $currentNum; }?>"/>
			</td>
		</tr>
		<tr id="harvest_frequency_row">
			<td class="">Harvest Frequency:</td>
			<td>
				<select name="harvest_frequency" id="harvest_frequency">
					<?php
					setChosen('harvest_frequency', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$harvestFrequencies = getHarvesterFrequencies();
					if( $harvestFrequencies )
					{
						foreach( $harvestFrequencies as $frequency )
						{
							setChosen('harvest_frequency', $frequency, gITEM_SELECT);
							print("<option value=\"".esc($frequency)."\"$gChosen>".esc($frequency)."</option>\n");
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td width="128"></td>
			<td><input type="submit" name="action" value="Save"   onClick="return nlaPushCheck();"/>&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory.<br />
			</td>
		</tr>
	</tbody>
</table>
</form>

<script type="text/javascript">setHarvestMethodDependents();</script>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
