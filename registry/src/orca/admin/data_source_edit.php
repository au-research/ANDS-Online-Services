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
//require '../../_includes/init.php';
//ini_set('display_errors',1); 
//error_reporting(E_ALL);
//require '../orca_init.php';
require '../manage/process_registry_object.php';
// Page processing
// -----------------------------------------------------------------------------


$data_Source = getQueryValue('data_source_key');
$taskWaiting = '';
$taskWaiting = scheduledTaskCheck($data_Source);

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
	$harvestDate = $dataSource[0]['time_zone_value'];
}else{
	$harvestDate = str_replace("+10"," +10:00",str_replace("+11"," +11:00",$dataSource[0]['harvest_date']));
}
$harvestFrequency = $dataSource[0]['harvest_frequency'];
$institutionalPages = $dataSource[0]['institution_pages'];
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
$create_primary_relationships_old = $dataSource[0]['create_primary_relationships'];
$class_1 = $dataSource[0]['class_1'];
$class_1_old = $dataSource[0]['class_1'];
$primary_key_1 = $dataSource[0]['primary_key_1']; 
$service_rel_1 = $dataSource[0]['service_rel_1']; 
$activity_rel_1 = $dataSource[0]['activity_rel_1'];
$collection_rel_1 = $dataSource[0]['collection_rel_1']; 
$party_rel_1 = $dataSource[0]['party_rel_1']; 
$class_2 = $dataSource[0]['class_2'];
$class_2_old = $dataSource[0]['class_2'];
$primary_key_2 = $dataSource[0]['primary_key_2']; 
$service_rel_2 = $dataSource[0]['service_rel_2']; 
$activity_rel_2 = $dataSource[0]['activity_rel_2'];
$collection_rel_2 = $dataSource[0]['collection_rel_2']; 
$party_rel_2 = $dataSource[0]['party_rel_2']; 
$assessementNotificationEmailAddr = $dataSource[0]['assessement_notification_email_addr'];
$autoPublish = $dataSource[0]['auto_publish'];
$qaFlag = $dataSource[0]['qa_flag'];
$advancedHarvestingMode = $dataSource[0]['advanced_harvesting_mode'];

$post_code = $dataSource[0]['post_code'];
$address_line_1 = $dataSource[0]['address_line_1'];
$address_line_2 = $dataSource[0]['address_line_2'];
$city = $dataSource[0]['city'];
$state = $dataSource[0]['state'];


$errorMessages = '';
$dataSourceKeyLabelClass = '';
$titleLabelClass = '';
$uriLabelClass = '';
$providerTypeLabelClass = '';
$harvestMethodLabelClass = '';
$advancedHarvestingModeLabelClass = '';
$pushNLALabelClass = '';
$createPrimaryClass = '';
$institutionPagesClass = '';
$dateLabelClass = '';
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

	//Lets deal with the three possible scenarios for institutional pages and then clear all of the excess post variables so we don't muck up the data_source update function
	$pagesChoice = getPostedValue('institution_pages');
	$groups = getDataSourceGroups($dataSourceKey);	
	if($groups)
	{
		foreach($groups as $group)
		{
			$alreadyMapped[$group['object_group']] = getGroupPage($group['object_group']);
			$theResult = deleteInstitutionalPage($group['object_group'],$dataSourceKey);
		
		}	
	}
	switch($pagesChoice){
		case 1:
			foreach($groups as $group)
			{
				//first we need to check if this group's institutional page is being administered by someone else, and if so we just leave it alone.
				$pageInfo = getGroupPage($group['object_group']);
				if(!isset($pageInfo[0]['authoritive_data_source_key']))
				{
				
					//check if the automated institutional page registry object actually already exists;
					$key  = "Contributor:".$group['object_group'];
					$thePage = getRegistryObject($key, $overridePermissions = true);
					if(!$thePage)
					{

						$thePage = getDraftRegistryObject($key, $dataSourceKey);
						if(!$thePage)
						{					
						
							$rifcs = "  <registryObject group=\"".$group['object_group']."\">\n";
							$rifcs .= "    <key>".$key."</key>\n";	
							$rifcs .= "    <originatingSource>".$dataSourceKey."</originatingSource>\n";					
							$rifcs .= "    <party type=\"group\">\n";			
							$rifcs .= "	<name type=\"primary\">\n";		
        					$rifcs .= "		<namePart type=\"full\">".$group['object_group']."</namePart>\n";       
      						$rifcs .= "	</name>\n"; 		
							$rifcs .= "    </party>\n";		
							$rifcs .= "  </registryObject>\n";			

							$wrappedRifcs = wrapRegistryObjects($rifcs, false);
			 				$registryObjects = new DOMDocument();
			  				$registryObjects->loadXML($wrappedRifcs);
			  								
							$theInstitutionPage =  insertDraftRegistryObject('SYSTEM', $key, 'Party', $group['object_group'], 'group', $group['object_group'], $dataSourceKey, date('Y-m-d H:i:s'), date('Y-m-d H:i:s') , $wrappedRifcs, '', 0, 0, 'DRAFT');
							
							runQualityLevelCheckForDraftRegistryObject($key,$dataSourceKey);
							
							addDraftToSolrIndex($key,addDraftToSolrIndex);
														
							$mailBody	= 'http://'.$host.'/'.$orca_root.'/manage/add_party_registry_object.php?readOnly&data_source='.$dataSourceKey.'&key='.urlencode($key);
						
							send_email(eCONTACT_EMAIL,$key . " contributor page has been generated under data source ".$dataSourceKey,$mailBody);							
						}	
					}
					$theInstitutionalPage = insertInstitutionalPage($group['object_group'],$key,$dataSourceKey);
				}
			}			

			break;
		case 2:	

			for($i=1;$i<=count($groups);$i++)
			{
				$group = getPostedValue('group_'.$i);
	
				$institutional_key = getPostedValue('institution_key_'.$i);
				if($institutional_key!='')
				{
					//lets check we have a valid party group key for this datas source with the correct object group
					$theInstitution = getRegistryObject($institutional_key,$overridePermissions = true);
					$theContributorDraft =  getDraftRegistryObject($institutional_key, $dataSourceKey);

					if($theInstitution[0]['data_source_key']==$dataSourceKey&&$theInstitution[0]['object_group']==$group&&$theInstitution[0]['registry_object_class']=='Party'&&$theInstitution[0]['type']=='group')
					{
						//echo "The record is valid so add it to the db";
						$theInstitutionalPage = insertInstitutionalPage($group,$institutional_key,$dataSourceKey);
						$mailSubject = $theInstitution[0]['list_title'].' has been mapped as a contributor page for group '.$group.' under data source '.$dataSourceKey;						
						$mailBody = 'http://'.$host.'/'.$orca_root.'/view.php?key='.urlencode($institutional_key);			
					}
					elseif ($theContributorDraft[0]['registry_object_data_source']==$dataSourceKey&&$theContributorDraft[0]['registry_object_group']==$group&&$theContributorDraft[0]['class']=='Party'&&$theContributorDraft[0]['registry_object_type']=='group')
					{
						//echo "The record is valid so add it to the db";
						$theInstitutionalPage = insertInstitutionalPage($group,$institutional_key,$dataSourceKey);	
						$mailSubject = $theContributorDraft[0]['registry_object_title'].' has been mapped as a contributor page for group '.$group.' under data source '.$dataSourceKey;									
						$mailBody	= 'http://'.$host.'/'.$orca_root.'/manage/add_party_registry_object.php?readOnly&data_source='.$dataSourceKey.'&key='.urlencode($institutional_key);				
					}
					else
					{
						//echo "The record is not valid so show error";					
						$institutionPagesClass = gERROR_CLASS;
						$errorMessages .= "You have provided an invalid key for your Institutional page.<br />The assigned registry object must be a group party originating from this datasource and object group.<br />";
					}	

					if($alreadyMapped[$group][0]['registry_object_key']!=$institutional_key&&$alreadyMapped[$group][0]['registry_object_key']!='')
					{
						send_email(eCONTACT_EMAIL,$mailSubject,$mailBody);	
					}
					
				}
				unset($_POST['group_'.$i]);
				unset($_POST['institution_key_'.$i]);
				unset($_POST['object_institution_key_'.$i.'_name']);
			}
			break;		
	}

	$title = getPostedValue('title');
	if( $title == '' )
	{ 
		$titleLabelClass = gERROR_CLASS;
		$errorMessages .= "Title is a mandatory field.<br />";
	}

	$post_code = getPostedValue('post_code');
	$address_line_1 = getPostedValue('address_line_1');
	$address_line_2 = getPostedValue('address_line_2');
	$city = getPostedValue('city');
	$state = getPostedValue('state');

	$uri = getPostedValue('uri');
	if( $uri == '' )
	{ 
		$uriLabelClass = gERROR_CLASS;
		$errorMessages .= "URI is a mandatory field.<br />";
	}	
	
	if(getPostedValue('uri')!='' && (!filter_var(getPostedValue('uri'), FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) || strpos(getPostedValue('uri'), "file://")===0))
  	{
		$uriLabelClass = gERROR_CLASS;
		$errorMessages .= "URI <em>".filter_var(getPostedValue('uri'))."</em> is not a valid URI.<br />";
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
	if(getPostedValue('institution_pages') == '')
	{
		$institutionPagesClass = gERROR_CLASS;
		$errorMessages .= "You must select one of the options for handling your institutional pages.<br />";
		$institutionalPages = '';		
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
	$create_primary_relationships = getPostedValue('create_primary_relationships');	
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
	if($harvestMethod!='DIRECT')
	{
		$pattern = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";
		if (getPostedValue('harvest_date')!='' && !preg_match( $pattern, getPostedValue('harvest_date') ) ) 
		{
			$dateLabelClass = gERROR_CLASS;
			$errorMessages .= "Harvester date format must be W3CDTF.<br />";
		}	
	}else{
		$_POST['harvest_date']='';
	}

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

	$advancedHarvestingMode = getPostedValue('advanced_harvesting_mode');

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
	
	
	// Check the advanced harvest mode compatibility
	if( $providerType && $harvestMethod && $advancedHarvestingMode == "INCREMENTAL" && $providerType != "OAI_RIF")
	{
		$advancedHarvestingModeLabelClass = gERROR_CLASS;
		$errorMessages .= 'This advanced harvesting mode is not compatible with your harvest type <br/>Note: Incremental harvesting only available in OAI-PMH providers.<br />'; 
	}
	if( $providerType && $harvestMethod && $advancedHarvestingMode == "REFRESH" && $harvestMethod == "DIRECT")
	{
		$advancedHarvestingModeLabelClass = gERROR_CLASS;
		$errorMessages .= 'This advanced harvesting mode is not compatible with your harvest method <br/>Note: Full Refresh harvesting only available in harvested feeds (consider Harvester DIRECT instead).<br />'; 
	}

	if( $errorMessages == '' )
	{
		// Update the record.

		$_POST['theZone'] = $_POST['harvest_date'];

		unset($_POST['object_relatedObject']);
		unset($_POST['object_primary_key_1_name']);			
		unset($_POST['object_primary_key_2_name']);
		unset($_POST['select_primary_key_1_class']);
		unset($_POST['select_primary_key_2_class']);	

		$errors = updateDataSource();
		$errors .= updateRecordsForDataSource($dataSourceKey, $autoPublish, $autoPublishOld , $qaFlag , $qaFlagOld,$create_primary_relationships, $create_primary_relationships_old,$class_1,$class_1_old,$class_2,$class_2_old);
		$errors .= updateAdvancedHarvestingModeForDataSource($dataSourceKey, $advancedHarvestingMode);
		$errors .= updatePostCodeForDataSource( $dataSourceKey, $post_code );
		$errors .= updateAddressForDataSource( $dataSourceKey, $address_line_1, $address_line_2, $city, $state );
		
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
<input type="hidden" id="dataSourceKey" value="<?php echo $dataSourceKey; ?>" />
<form id="data_source_edit" action="data_source_edit.php?data_source_key=<?php print(urlencode($dataSourceKey)); ?>" method="post" onSubmit="return checkModalId(this)">
<div  style="width:1000px;overflow:auto">
<table class="formTable" summary="Edit Data Source">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Edit Data Source.</td>
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
		<td colspan="2" style="border-bottom:2px solid black;"><span style="float:left;">Account Administration Information</span>
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

		<tr>
			<td></td>
			<td class="label" style="text-align:left;border-bottom:2px solid black;">Reference Address <span style="font-size:0.7em; color:#333;">Note: These optional fields are used to indicate your data source's origin in spatial reporting tools.</span></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<table>
					<tr>
						<td class="label">Address Line 1:</td>
						<td><input type="text" name="address_line_1" id="address_line_1" size="60" maxlength="128" value="<?php printSafe($address_line_1) ?>" /></td>
					</tr>

					<tr>
						<td class="label">Address Line 2:</td>
						<td><input type="text" name="address_line_2" id="address_line_2" size="60" maxlength="128" value="<?php printSafe($address_line_2) ?>" /></td>
					</tr>

					<tr>
						<td class="label">City:</td>
						<td><input type="text" name="city" id="city" size="15" maxlength="15" value="<?php printSafe($city) ?>" /></td>
					</tr>
					
					<tr>
						<td class="label">Post Code:</td>
						<td><input type="text" name="post_code" id="post_code" size="15" maxlength="6" value="<?php printSafe($post_code) ?>" /><br/></td>
					</tr>

					<tr>
						<td class="label">State:</td>
						<td>
							<?php
								$states = array(
									'ACT'=>'Australian Capital Territory',
									'NSW'=>'New South Wales',
									'NT'=>'Northern Territory',
									'QLD'=>'Queensland',
									'SA'=>'South Australia',
									'TAS'=>'Tasmania',
									'VIC'=>'Victoria',
									'WA'=>'Western Australia',
								);
							?>
							<select name="state" id="state">
								<option value=""></option>
								<?php 
									foreach($states as $sh=>$full){
										if($state==$sh){
											echo '<option value="'.$sh.'" selected=selected>'.$full.'</option>';
										}else{
											echo '<option value="'.$sh.'">'.$full.'</option>';
										}
									}
								?>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>

		
		<tr style="border-bottom:2px solid black;">
		<td colspan="2" style="border-bottom:2px solid black;"><span style="float:left;">Records Management Settings</span>
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
		<div align="left" style="width:320px;position:relative;float:left;">
		<table width="255">
		<tr><td>Class : </td><td><select name="class_1" id="select_1_class" onChange='setTheDialog(this,"select_primary_key_1_class");'>
			<option value="" <?php if($class_1=="") echo " selected"?>></option>			
			<option value="party" <?php if($class_1=="party") echo " selected"?>>Party</option>	
			<option value="service" <?php if($class_1=="service") echo " selected"?>>Service</option>	
			<option value="activity" <?php if($class_1=="activity") echo " selected"?>>Activity</option>									
			<option value="collection" <?php if($class_1=="collection") echo " selected"?>>Collection</option>		
			</select> </td></tr>
		<tr><td>	Key : </td>
		<td> <input type="text" name="primary_key_1" id="object_primary_key_1_value" size="25" maxlength="128" value="<?php printSafe($primary_key_1) ?>" />
			<img name="relatedImg" src="../_images/preview.png" onClick='showSearchModal("object_primary_key_1");' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" /> 
													 </td></tr>
			
			<tr><td colspan="2">Relationship From: </td></tr>
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
		<div align="left"  style="width:320px;position:relative;float:right;">
		<table width="255">
		<tr><td>Class : </td><td><select name="class_2"  id="select_2_class"   onChange='setTheDialog(this,"select_primary_key_2_class");'>
			<option value="" <?php if($class_2=="") echo " selected"?>></option>			
			<option value="party" <?php if($class_2=="party") echo " selected"?>>Party</option>	
			<option value="service" <?php if($class_2=="service") echo " selected"?>>Service</option>	
			<option value="activity" <?php if($class_2=="activity") echo " selected"?>>Activity</option>									
			<option value="collection" <?php if($class_2=="collection") echo " selected"?>>Collection</option>		
			</select> </td></tr>
		<tr><td>	Key : </td>
		<td> <input type="text" name="primary_key_2" id="object_primary_key_2_value" size="25" maxlength="128" value="<?php printSafe($primary_key_2) ?>" />
		<img name="relatedImg" src="../_images/preview.png" onClick='showSearchModal("object_primary_key_2");' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" /> </td></tr>
			
			<tr><td colspan="2">Relationship From: </td></tr>
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
			<td>
			 <?php if( userIsORCA_ADMIN() || userIsORCA_LIAISON()): ?>
				<input type="text" name="assessement_notification_email_addr" id="assessement_notification_email_addr" size="60" maxlength="128" value="<?php printSafe($assessementNotificationEmailAddr); ?>" />
			 <?php else: ?>
				<?php printSafe($assessementNotificationEmailAddr); ?>
				<input type="hidden" id="assessement_notification_email_addr" name="assessement_notification_email_addr" value="<?php printSafe($assessementNotificationEmailAddr); ?>" />
			 <?php endif; ?>
			</td>
		</tr>				
			<?php 
			$groups = '';
			$object_groups = getDataSourceGroups($data_Source); 

			if($object_groups)
			{
				foreach($object_groups as $group)
				{
					$groups .= ":::".$group['object_group'];
					$groupDataSources = getGroupDataSources($group['object_group']);
					$groupsDataSources[$group['object_group']] = '';
				//	foreach($groupDataSources as $groupDataSource)
				//	{
				//		$groups .= "|||".$groupDataSource['data_source_key'];
				//		$groupsDataSources[$group['object_group']] .= '<option value="'.$groupDataSource['data_source_key'].'">'.$groupDataSource['data_source_key'].'</option>';
				//	}
						$groups .= "|||".$dataSourceKey;
						$groupsDataSources[$group['object_group']] .= '<option value="'.$dataSourceKey.'">'.$dataSourceKey.'</option>';				
				}
				$groups = trim($groups,":::");
			}
			if($groups=='')
			{  
				$groupClass= ' style="display:none;"';
			} else {
				$groupClass= '';
			}
			?>
			<tr <?php echo $groupClass;?>>
			<td<?php print($institutionPagesClass); ?>>Contributor Pages:</td>		
			<td>			
			 	<input type="radio" name="institution_pages" value="0" <?php if($institutionalPages=="0") echo " checked"?> onChange="setInstitutionalPage(this,'<?php echo $groups;?>','<?php echo $data_Source?>');"> Do not have Contributor Pages<br />
				<input type="radio" name="institution_pages" value="1" <?php if($institutionalPages=="1") echo " checked"?> onChange="setInstitutionalPage(this,'<?php echo $groups;?>','<?php echo $data_Source?>');"> Auto generate Contributor Pages for all my groups<br /> 
				<input type="radio" name="institution_pages" value="2" <?php if($institutionalPages=="2") echo " checked"?> onChange="setInstitutionalPage(this,'<?php echo $groups;?>','<?php echo $data_Source?>');"> Manually manage my Contributor Pages and groups<br /> 
				<span id="currentPage" style="display:none"><?php echo $institutionalPages?></span>
				<?php if($groups!='')
				{?>
				<table id="institutionalPages" width="600" border="1">
				<tr><td style="width:200px"><b>Group </b> </td><td> <b> Contributor Page Key</b></td></tr>
			<?php 
				$i=1;
				$noAutoPage = "no";
				foreach($object_groups as $group)
				{ 
				$thePage = getGroupPage($group['object_group']);

				if($institutionalPages=='1'&&!$thePage)
				{
					$noAutoPage = "yes";
				}
				?>
				<tr><td id="group<? echo $i;?>name" width="200"><?php  echo $group['object_group'];?>
				<?php  if ($thePage[0]['authoritive_data_source_key'] != $data_Source && isset($thePage[0]['authoritive_data_source_key'])) 
				{ ?>
					<br /><span style="color:grey">Already managed by <?php echo $thePage[0]['authoritive_data_source_key']?></span><td><?php print($thePage[0]['registry_object_key'])?></td> 
					<?php  
				} else { ?>		
					<td id="group<?php echo $i;?>page">
					<?php  
					if($institutionalPages=="2") { 			
						$searchStr = '<div id="searchDialog_object_institution_key_'.$i.'" class="window" } \' >';
						$searchStr .= '<img src="../_images/error_icon.png" onClick=\'closeSearchModal("object_institution_key_'.$i.'");\' style="cursor:pointer; position:absolute; top:5px; right:5px; width:16px;" />';
						$searchStr .= '<ul id="guideNotes_relatedObject" class="guideNotes" style="display: block; ">';
						$searchStr .= '<li>The name search will only return the first 10 entries found in the database.<br/> To narrow down the returned results please ensure your text entries are as specific as possible.</li>';
						$searchStr .= '</ul>';
						$searchStr .= '<table class="rmdElementContainer" style="font-weight:normal;">';
						$searchStr .= '<tbody class="formFields andsorange">';
						$searchStr .= '<tr><td>Search by name:</td><td><input type="text" id="object_institution_key_'.($i).'_name" autocomplete="on" name="object_institution_key_'.($i).'_name" maxlength="512" size="30" /></td></tr>';
						$searchStr .= '<tr><td>Select object class:</td><td><span style="color:#666666">Party</span><input type="hidden" id="select_institution_key_'.($i).'_class" value = "Party"/></td></tr>';
						$searchStr .= '<tr><td>Data source:<input type="hidden" id="select_institution_key_'.($i).'_group" value="'.$group['object_group'].'"/><input type="hidden" id="select_institution_key_'.($i).'_dataSource" value="'.$data_Source.'"/></td><td>'.$data_Source.'</td></tr>';
						$searchStr .= '<tr><td><input type="button" value="Choose Selected" onClick=\'setRelatedId("object_institution_key_'.($i).'");\'/></td><td></td></tr>';
						$searchStr .= '</table>';				
						$searchStr .= '</div>'; 
						$searchStr .= '<div class="mask" onclick="closeSearchModal(\'object_institution_key_'.($i).'\')" id="mask_object_institution_key_'.($i).'"></div>';		
						$inputStr = $searchStr.'<input type="hidden" name="group_'.$i.'" value="'.$group['object_group'].'"/><input type="text" name="institution_key_'.$i.'" id="object_institution_key_'.$i.'_value" size="25" maxlength="128" value="'.$thePage[0]['registry_object_key'].'" />';
						$inputStr .='<img name="relatedImg" src="../_images/preview.png" onClick=\'showSearchModal("object_institution_key_'.$i.'"); \' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" />';
						echo $inputStr;?>
						<script>addRelatedObjectAutocomplete("object_institution_key_<?php echo $i?>_name");</script>
					<?php 		
					} else { 
						print($thePage[0]['registry_object_key']); 
					} 
					?>		
					</td> <input type="hidden" id="object_institution_key_<?php echo $i?>_current" value="<?php print($thePage[0]['registry_object_key'])?>"/>	<?php  
				} ?></tr>				
				<?php
				$i++; 
				}	
				if($noAutoPage=="yes")
				{
					?>
					<span id="noAutoPage" style="display:none">1</span>
					<?php 
				}
				?>			
				</table>	
			<?php 
			} 
			?>		
			</td>
		</tr>			
		<tr style="border-bottom:2px solid black;">
		<td colspan="2" style="border-bottom:2px solid black;"><span style="float:left;">Harvester Settings</span>
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
		
		<tr id="advanced_harvesting_options_row">
			<td <?php echo $advancedHarvestingModeLabelClass; ?>>Advanced Harvest Mode:</td>
			<td>
				<a onclick="javascript: $(this).hide(); $('#advanced_harvesting_options').show();">show advanced options</a>
				
				<div id="advanced_harvesting_options" class="hide">
					<input type="radio" name="advanced_harvesting_mode" value="STANDARD"<?=($advancedHarvestingMode=="STANDARD" ? ' checked="checked"' : '');?> /> Standard Mode<br/>
					<input type="radio" name="advanced_harvesting_mode" value="INCREMENTAL"<?=($advancedHarvestingMode=="INCREMENTAL" ? ' checked="checked"' : '');?> /> Incremental Mode<br/>
					<input type="radio" name="advanced_harvesting_mode" value="REFRESH"<?=($advancedHarvestingMode=="REFRESH" ? ' checked="checked"' : '');?> /> Full Refresh Mode
				</div>
				
				
			</td>
		</tr>

		<tr id="harvest_date_row">
			<td <?php echo $dateLabelClass; ?>>Harvest Date:</td>
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
		<!-- 			<td><?php drawDateTimeZoneInput('harvest_date', $harvestDate, eDCT_FORMAT_ISO8601_DATE_TIME."X") ?>
			<span id="gmtZone" class="inputFormat"><?php if(isset($theString)){ echo $theString;} else { echo $currentZone ;} ?> </span>
			<input name="theZone" id="theZone" type="hidden" value="<?php if(isset($newNum)){echo $newNum;}else { echo $currentNum ; }?>"/>
			</td> -->
			<td><?php drawDateTimeZoneInput('harvest_date', $harvestDate, eDCT_FORMAT_ISO8601_DATETIMESEC_UTC) ?></p>
			<input name="theZone" id="theZone" type="hidden" value=""/>


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
			<td width="175"></td>
			<td><input type="submit" name="action" value="Save" onClick="return nlaPushCheck();"/>&nbsp;&nbsp;<input type="submit" name="action" value="Cancel" />&nbsp;&nbsp;</td>
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
