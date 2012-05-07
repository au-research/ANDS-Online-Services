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
$dataSourceLabelClass = '';
$rifcs = '';

$registryObjectClass = strtolower(getPostedValue('class'));

if ( strtoupper(getPostedValue('verb')) == "SAVE" )
{

	if( getPostedValue('data_source_key') == '' )
	{ 
		$dataSourceLabelClass = gERROR_CLASS;
		$errorMessages .= "Data Source is a mandatory field.<br />";
	}

	if( !$errorMessages )
	{
		// Build the RIF-CS from the posted data.
		// =====================================================================
		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		// ---------------------------------------------------------------------
		// registryObject
		$rifcs .= '  <registryObject';
		if( getPostedValue('registry_object_group') )
		{
			// group
			$rifcs .= ' group="'.esc(getPostedValue('registry_object_group')).'"';
		}
		$rifcs .= '>'."\n";
		// ---------------------------------------------------------------------		
		// key
		if( getPostedValue('key') )
		{
			$rifcs .= '    <key>'.esc(getPostedValue('key')).'</key>'."\n";
		}
		// ---------------------------------------------------------------------		
		// originatingSource
		$rifcs .= '    <originatingSource>'.esc(eAPP_ROOT.'orca/register_my_data').'</originatingSource>'."\n";
		// ---------------------------------------------------------------------		
		// collection | service | party | activity
		if( getPostedValue('class') )
		{
			$rifcs .= '    <'.esc(getPostedValue('class'));
			if( getPostedValue('class_type') )
			{
				// type
				$rifcs .= ' type="'.esc(getPostedValue('class_type')).'"';
			}
			if( getPostedValue('registry_object_date_accessioned') )
			{
				// dateAccessioned
				$rifcs .= ' dateAccessioned="'.esc(getPostedValue('registry_object_date_accessioned')).'"';
			}
			if( getPostedValue('registry_object_date_modified') )
			{
				// dateModified
				$rifcs .= ' dateModified="'.esc(getPostedValue('registry_object_date_modified')).'"';
			}
			$rifcs .= '>'."\n";
		}
		// ---------------------------------------------------------------------
		// identifiers
		if( getPostedValue('identifiers') )
		{
			foreach( getPostedValue('identifiers') as $id )
			{
				if( getPostedValue('identifier_value_'.$id) )
				{
					$rifcs .= '      <identifier';
					if( getPostedValue('identifier_type_'.$id) )
					{
						$rifcs .= ' type="'.esc(getPostedValue('identifier_type_'.$id)).'"';
					}
					$rifcs .= '>'.esc(getPostedValue('identifier_value_'.$id)).'</identifier>'."\n";
				}
			}
		}
		
		// ---------------------------------------------------------------------
		// names
		if( getPostedValue('complex_names') )
		{
			foreach( getPostedValue('complex_names') as $id )
			{
				$rifcs .= '      <name';
				if( getPostedValue('complex_name_type_'.$id) )
				{
					$rifcs .= ' type="'.esc(getPostedValue('complex_name_type_'.$id)).'"';
				}
				if( getPostedValue('complex_name_lang_'.$id) )
				{
					$rifcs .= ' xml:lang="'.esc(getPostedValue('complex_name_lang_'.$id)).'"';
				}
				if( getPostedValue('complex_name_date_from_'.$id) )
				{
					$rifcs .= ' dateFrom="'.esc(getPostedValue('complex_name_date_from_'.$id)).'"';
				}
				if( getPostedValue('complex_name_date_to_'.$id) )
				{
					$rifcs .= ' dateTo="'.esc(getPostedValue('complex_name_date_to_'.$id)).'"';
				}
				$rifcs .= '>'."\n";
				// -------------------------------------------------------------
				// name parts
				$parent = 'complex_name_'.$id;
				if( getPostedValue($parent.'_name_parts') )
				{
					foreach( getPostedValue($parent.'_name_parts') as $subId )
					{
						if( getPostedValue($parent.'_name_part_value_'.$subId) )
						{
							$rifcs .= '        <namePart';
							if( getPostedValue($parent.'_name_part_type_'.$subId) )
							{
								$rifcs .= ' type="'.esc(getPostedValue($parent.'_name_part_type_'.$subId)).'"';
							}
							$rifcs .= '>'.esc(getPostedValue($parent.'_name_part_value_'.$subId)).'</namePart>'."\n";
						}
					}
				}
				$rifcs .= '      </name>'."\n";
			}
		}
		
		// ---------------------------------------------------------------------
		// locations
		if( getPostedValue('locations') )
		{
			foreach( getPostedValue('locations') as $id )
			{
				$parent = 'location_'.$id;
				$rifcs .= '      <location';
				if( getPostedValue('location_date_from_'.$id) )
				{
					$rifcs .= ' dateFrom="'.esc(getPostedValue('location_date_from_'.$id)).'"';
				}
				if( getPostedValue('location_date_to_'.$id) )
				{
					$rifcs .= ' dateTo="'.esc(getPostedValue('location_date_to_'.$id)).'"';
				}	
				if( getPostedValue('location_type_'.$id) )
				{
					$rifcs .= ' type="'.esc(getPostedValue('location_type_'.$id)).'"';
				}			
				$rifcs .= '>'."\n";
				// -------------------------------------------------------------
				// addresses
				if( getPostedValue($parent.'_addresses') )
				{
					foreach( getPostedValue($parent.'_addresses') as $subId )
					{
						$subParent = $parent.'_address_'.$subId.'_';
						$rifcs .= '        <address>'."\n";
						// electronic addresses
						if( getPostedValue($subParent.'electronic_addresses') )
						{
							foreach( getPostedValue($subParent.'electronic_addresses') as $subSubId )
							{
								if( getPostedValue($subParent.'electronic_address_value_'.$subSubId) )
								{
									$rifcs .= '          <electronic';
									if( getPostedValue($subParent.'electronic_address_type_'.$subSubId) )
									{
										$rifcs .= ' type="'.esc(getPostedValue($subParent.'electronic_address_type_'.$subSubId)).'"';
									}
									$rifcs .= '>'."\n";
									$rifcs .= '            <value>'.esc(getPostedValue($subParent.'electronic_address_value_'.$subSubId))."</value>\n";
									
									$subSubParent = $subParent.'electronic_address_'.$subSubId.'_';
									// arguments
									if( getPostedValue($subSubParent.'arguments') )
									{
										foreach( getPostedValue($subSubParent.'arguments') as $subSubSubId )
										{
											if( getPostedValue($subSubParent.'argument_name_'.$subSubSubId) )
											{
												$rifcs .= '            <arg';
												if( getPostedValue($subSubParent.'argument_required_'.$subSubSubId) )
												{
													$rifcs .= ' required="'.esc(getPostedValue($subSubParent.'argument_required_'.$subSubSubId)).'"';
												}
												if( getPostedValue($subSubParent.'argument_type_'.$subSubSubId) )
												{
													$rifcs .= ' type="'.esc(getPostedValue($subSubParent.'argument_type_'.$subSubSubId)).'"';
												}
												if( getPostedValue($subSubParent.'argument_use_'.$subSubSubId) )
												{
													$rifcs .= ' use="'.esc(getPostedValue($subSubParent.'argument_use_'.$subSubSubId)).'"';
												}
												$rifcs .= '>'.esc(getPostedValue($subSubParent.'argument_name_'.$subSubSubId))."</arg>\n";
											}
										}
									}
									$rifcs .= '          </electronic>'."\n";
								}
							}
						}
						// physical addresses
						if( getPostedValue($subParent.'physical_addresses') )
						{
							foreach( getPostedValue($subParent.'physical_addresses') as $subSubId )
							{
								$rifcs .= '          <physical';
								if( getPostedValue($subParent.'physical_address_type_'.$subSubId) )
								{
									$rifcs .= ' type="'.esc(getPostedValue($subParent.'physical_address_type_'.$subSubId)).'"';
								}
								if( getPostedValue($subParent.'physical_address_lang_'.$subSubId) )
								{
									$rifcs .= ' xml:lang="'.esc(getPostedValue($subParent.'physical_address_lang_'.$subSubId)).'"';
								}
								$rifcs .= '>'."\n";
								
								$subSubParent = $subParent.'physical_address_'.$subSubId.'_';
								// address parts
								if( getPostedValue($subSubParent.'address_parts') )
									{
										foreach( getPostedValue($subSubParent.'address_parts') as $subSubSubId )
										{
											if( getPostedValue($subSubParent.'address_part_value_'.$subSubSubId) )
											{
												$rifcs .= '            <addressPart';
												if( getPostedValue($subSubParent.'address_part_type_'.$subSubSubId) )
												{
													$rifcs .= ' type="'.esc(getPostedValue($subSubParent.'address_part_type_'.$subSubSubId)).'"';
												}
												$rifcs .= '>'.esc(getPostedValue($subSubParent.'address_part_value_'.$subSubSubId))."</addressPart>\n";
											}
										}
									}
								$rifcs .= '          </physical>'."\n";
							}
						}
						$rifcs .= '        </address>'."\n";
					}
				}
				// -------------------------------------------------------------
				// spatial locations
				if( getPostedValue($parent.'_spatial') )
				{
					foreach( getPostedValue($parent.'_spatial') as $subId )
					{
						if( getPostedValue($parent.'_spatial_value_'.$subId) )
						{
							$rifcs .= '        <spatial';
							if( getPostedValue($parent.'_spatial_type_'.$subId) )
							{
								$rifcs .= ' type="'.esc(getPostedValue($parent.'_spatial_type_'.$subId)).'"';
							}
							if( getPostedValue($parent.'_spatial_lang_'.$subId) )
							{
								$rifcs .= ' xml:lang="'.esc(getPostedValue($parent.'_spatial_lang_'.$subId)).'"';
							}
							$rifcs .= '>'.esc(getPostedValue($parent.'_spatial_value_'.$subId)).'</spatial>'."\n";
						}
					}
				}
				
				$rifcs .= '      </location>'."\n";
			}
		}

		
		// ---------------------------------------------------------------------
		// relatedObjects
		if( getPostedValue('related_objects') )
		{
			foreach( getPostedValue('related_objects') as $id )
			{
				$rifcs .= '      <relatedObject>'."\n";
				if( getPostedValue('related_registry_object_key_'.$id) )
				{
					$rifcs .= '        <key>'.esc(getPostedValue('related_registry_object_key_'.$id))."</key>\n";
				}
				// -------------------------------------------------------------
				// relations
				$parent = 'related_object_'.$id;
				if( getPostedValue($parent.'_relations') )
				{
					foreach( getPostedValue($parent.'_relations') as $subId )
					{
						$rifcs .= '        <relation';
						if( getPostedValue($parent.'_relation_type_'.$subId) )
						{
							$rifcs .= ' type="'.esc(getPostedValue($parent.'_relation_type_'.$subId)).'"';
						}
						$rifcs .= ">\n";
						// description
						if( getPostedValue($parent.'_relation_description_'.$subId) )
						{
							$rifcs .= '          <description';
							if( getPostedValue($parent.'_relation_description_lang_'.$subId) )
							{
								$rifcs .= ' xml:lang="'.esc(getPostedValue($parent.'_relation_description_lang_'.$subId)).'"';
							}
							$rifcs .= '>'.esc(getPostedValue($parent.'_relation_description_'.$subId))."</description>\n";
						}
						// url
						if( getPostedValue($parent.'_relation_url_'.$subId) )
						{
							$rifcs .= '          <url>'.esc(getPostedValue($parent.'_relation_url_'.$subId))."</url>\n";
						}
	
						$rifcs .= '        </relation>'."\n";
					}
				}
				$rifcs .= '      </relatedObject>'."\n";
			}
		}
		
		// ---------------------------------------------------------------------
		// subjects
		if( getPostedValue('subjects') )
		{
			foreach( getPostedValue('subjects') as $id )
			{
				if( getPostedValue('subject_value_'.$id) )
				{
					$rifcs .= '      <subject';
					if( getPostedValue('subject_type_'.$id) )
					{
						$rifcs .= ' type="'.esc(getPostedValue('subject_type_'.$id)).'"';
					}
					if( getPostedValue('subject_lang_'.$id) )
					{
						$rifcs .= ' xml:lang="'.esc(getPostedValue('subject_lang_'.$id)).'"';
					}
					$rifcs .= '>'.esc(getPostedValue('subject_value_'.$id)).'</subject>'."\n";
				}
			}
		}

		
		// ---------------------------------------------------------------------
		// descriptions
		if( getPostedValue('descriptions') )
		{
			foreach( getPostedValue('descriptions') as $id )
			{
				if( getPostedValue('description_value_'.$id) )
				{
					$rifcs .= '      <description';
					if( getPostedValue('description_type_'.$id) )
					{
						$rifcs .= ' type="'.esc(getPostedValue('description_type_'.$id)).'"';
					}
					if( getPostedValue('description_lang_'.$id) )
					{
						$rifcs .= ' xml:lang="'.esc(getPostedValue('description_lang_'.$id)).'"';
					}
					$rifcs .= '>'.esc(getPostedValue('description_value_'.$id)).'</description>'."\n";
				}
			}
		}

		
		// ---------------------------------------------------------------------
		// accessPolicy
		if( getPostedValue('access_policies') )
		{
			foreach( getPostedValue('access_policies') as $id )
			{
				if( getPostedValue('access_policy_value_'.$id) )
				{
					$rifcs .= '      <accessPolicy>'.esc(getPostedValue('access_policy_value_'.$id)).'</accessPolicy>'."\n";
				}
			}
		}

		
		// ---------------------------------------------------------------------
		// relatedInfo
		if( getPostedValue('related_info') )
		{
			foreach( getPostedValue('related_info') as $id )
			{
				if( getPostedValue('related_info_value_'.$id) )
				{
					$rifcs .= '      <relatedInfo>'.esc(getPostedValue('related_info_value_'.$id)).'</relatedInfo>'."\n";
				}
			}
		}

	
		
		// ---------------------------------------------------------------------		
		// collection | service | party | activity
		if( getPostedValue('class') )
		{
			$rifcs .= '    </'.esc(getPostedValue('class')).'>'."\n";
		}
		// ---------------------------------------------------------------------
		// registryObject
		$rifcs .= '  </registryObject>'."\n";
		// ---------------------------------------------------------------------
		$rifcs .= "</registryObjects>\n";
		
		// Check the xml.
		// =====================================================================
		$runErrors = '';
		$resultMessage = '';
		$registryObjects = new DOMDocument();
		$result = $registryObjects->loadXML($rifcs);
		$errors = error_get_last();
		if( $errors )
		{
			$errorMessages .= "Document Load Error";
			$errorMessages .= "<div class=\"readonly\" style=\"margin-top: 8px; color: #880000; height: 100px; width: 500px; padding: 0px; white-space: pre-wrap; overflow: auto; font-family: courier new, courier, monospace; font-size: 9pt;\">";
			$errorMessages .= esc($errors['message']);
			$errorMessages .= "</div>\n";
		}
	
		if( !$errorMessages )
		{
			// Validate it against the orca schema.
			$result = $registryObjects->schemaValidate(gRIF_SCHEMA_PATH);
			$errors = error_get_last();
			if( $errors )
			{
				$errorMessages .= "Document Validation Error";
				$errorMessages .= "<div class=\"readonly\" style=\"margin-top: 8px; color: #880000; height: 100px; width: 500px; padding: 0px; white-space: pre-wrap; overflow: auto; font-family: courier new, courier, monospace; font-size: 9pt;\">";
				$errorMessages .= esc($errors['message']);
				$errorMessages .= "</div>\n";
			}
			else
			{
				$runErrors = importRegistryObjects($registryObjects, getPostedValue('data_source_key'), $resultMessage, getLoggedInUser(), PUBLISHED, getThisOrcaUserIdentity());
				$errors = error_get_last();
				if( $runErrors )
				{
					$errorMessages .= "Import Errors";
					$errorMessages .= "<div class=\"readonly\" style=\"margin-top: 8px; color: #880000; height: 100px; width: 500px; padding: 0px; white-space: pre-wrap; overflow: auto; font-family: courier new, courier, monospace; font-size: 9pt;\">";
					$errorMessages .= esc($errors['message']);
					$errorMessages .= "</div>\n";
				}
				
				// Log the datasource activity.
				insertDataSourceEvent(getPostedValue('data_source_key'), "ADD REGISTRY OBJECT\nKey: ".getPostedValue('key')."\n".$resultMessage);
			}
		}
		
		if( !$errorMessages )
		{
			responseRedirect('../view.php?key='.urlencode(getPostedValue('key')));
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
<script type="text/javascript">vcSetImagePath('<?php print gORCA_IMAGE_ROOT ?>_controls/_vocab_control/')</script>
<form id="registry_object_add" action="registry_object_add.php" method="post">
<table class="formTable" summary="Add Registry Object">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Add Registry Object</td>
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
		<tr>
			<td<?php print($dataSourceLabelClass); ?>>* Data Source:</td>
			<td>
				<select name="data_source_key" id="data_source_key">
					<?php
					setChosen('data_source_key', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					$dataSources = getDataSources(null, null);
					if( $dataSources )
					{
						foreach( $dataSources as $source )
						{
							if( userIsDataSourceRecordOwner($source['record_owner']) || userIsORCA_ADMIN() )
							{
								setChosen('data_source_key', $source['data_source_key'], gITEM_SELECT);
								print('  <option value="'.esc($source['data_source_key']).'"'.$gChosen.'>'.esc($source['title']).'</option>'."\n");
							}
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>* Group:</td>
			<td><input type="text" name="registry_object_group" id="registry_object_group" size="40" maxlength="255" value="<?php printSafe(getPostedValue('registry_object_group')) ?>" /></td>
		</tr>
		<tr>
			<td>* Key:</td>
			<td><input type="text" name="key" id="key" size="40" maxlength="255" value="<?php printSafe(getPostedValue('key')) ?>" /></td>
		</tr>
		<tr>
			<td>* Class:</td>
			<td>
				<select name="class" id="class" onchange="this.form.submit()">
					<?php
					setChosen('class', '', gITEM_SELECT);
					print("<option value=\"\"$gChosen></option>\n");
					
					setChosen('class', 'collection', gITEM_SELECT);
					print("<option value=\"collection\"$gChosen>collection</option>\n");
					
					setChosen('class', 'service', gITEM_SELECT);
					print("<option value=\"service\"$gChosen>service</option>\n");
					
					setChosen('class', 'party', gITEM_SELECT);
					print("<option value=\"party\"$gChosen>party</option>\n");
					
					setChosen('class', 'activity', gITEM_SELECT);
					print("<option value=\"activity\"$gChosen>activity</option>\n");
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>* Type:</td>
			<td>
				<input type="text" name="class_type" id="class_type" size="40" maxlength="255" value="<?php printSafe(getPostedValue('class_type')) ?>" />
				<?php
					switch( $registryObjectClass )
					{
						case 'collection':
							drawVocabControl('class_type', 'RIFCSCollectionType');
							break;
						case 'service':
							drawVocabControl('class_type', 'RIFCSServiceType');
							break;
						case 'party':
							drawVocabControl('class_type', 'RIFCSPartyType');
							break;
						case 'activity':
							drawVocabControl('class_type', 'RIFCSActivityType');
							break;
					}
				?>
			</td>
		</tr>
		
		<?php if( $registryObjectClass == 'collection' ){ ?>
		
		<tr>
			<td>Date Accessioned:</td>
			<td><?php drawDateTimeInput('registry_object_date_accessioned', getPostedValue('registry_object_date_accessioned'), 'YYYY-MM-DDThh:mm:00Z') ?></td>
		</tr>
		
		<?php } ?>
		
		<tr>
			<td>Date Modified:</td>
			<td><?php drawDateTimeInput('registry_object_date_modified', getPostedValue('registry_object_date_modified'), 'YYYY-MM-DDThh:mm:00Z') ?></td>
		</tr>
		
		<!-- NAMES -->
		<tr>
			<td id="complex_names">Names:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('complex_names') )
				{
					$nextId = 0;
					foreach( getPostedValue('complex_names') as $id )
					{
						if( !getPostedValue('remove_complex_name_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawComplexNameFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="complex_names[]" onclick="setFormView(this.form, 'complex_name_<?php printSafe($thisId) ?>')" value="add" title="Add a Name" /></div>
			</td>
		</tr>
		
		<!-- IDENTIFIERS  -->
		<tr>
			<td id="identifiers">Identifiers:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('identifiers') )
				{
					$nextId = 0;
					foreach( getPostedValue('identifiers') as $id )
					{
						if( !getPostedValue('remove_identifier_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawIdentifierFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="identifiers[]" onclick="setFormView(this.form, 'identifier_<?php printSafe($thisId) ?>')" value="add" title="Add an Identifier" /></div>
			</td>
		</tr>
		
		<!-- LOCATIONS  -->
		<tr>
			<td id="locations">Locations:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('locations') )
				{
					$nextId = 0;
					foreach( getPostedValue('locations') as $id )
					{
						if( !getPostedValue('remove_location_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawLocationFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="locations[]" onclick="setFormView(this.form, 'location_<?php printSafe($thisId) ?>')" value="add" title="Add a Location" /></div>				
			</td>
		</tr>
		
		<!-- RELATED OBJECTS  -->
		<tr>
			<td id="related_objects">Related Objects:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('related_objects') )
				{
					$nextId = 0;
					foreach( getPostedValue('related_objects') as $id )
					{
						if( !getPostedValue('remove_related_object_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawRelatedObjectFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="related_objects[]" onclick="setFormView(this.form, 'related_object_<?php printSafe($thisId) ?>')" value="add" title="Add a Related Object" /></div>
			</td>
		</tr>
		
		<!-- SUBJECTS  -->
		<tr>
			<td id="subjects">Subjects:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('subjects') )
				{
					$nextId = 0;
					foreach( getPostedValue('subjects') as $id )
					{
						if( !getPostedValue('remove_subject_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawSubjectFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="subjects[]" onclick="setFormView(this.form, 'subject_<?php printSafe($thisId) ?>')" value="add" title="Add a Subject" /></div>
			</td>
		</tr>
		
		<!-- DESCRIPTIONS  -->
		<tr>
			<td id="descriptions">Descriptions:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('descriptions') )
				{
					$nextId = 0;
					foreach( getPostedValue('descriptions') as $id )
					{
						if( !getPostedValue('remove_description_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawDescriptionFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="descriptions[]" onclick="setFormView(this.form, 'description_<?php printSafe($thisId) ?>')" value="add" title="Add a Description" /></div>
			</td>
		</tr>
		
		<?php if( $registryObjectClass == 'service' ){ ?>
		
		<!-- ACCESS POLICIES  -->
		<tr>
			<td id="access_policies">Access Policies:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('access_policies') )
				{
					$nextId = 0;
					foreach( getPostedValue('access_policies') as $id )
					{
						if( !getPostedValue('remove_access_policy_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawAccessPolicyFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="access_policies[]" onclick="setFormView(this.form, 'access_policy_<?php printSafe($thisId) ?>')" value="add" title="Add an Access Policy" /></div>
			</td>
		</tr>
		<?php } ?>
		
		<!-- RELATED INFO  -->
		<tr>
			<td id="related_info">Related Info:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('related_info') )
				{
					$nextId = 0;
					foreach( getPostedValue('related_info') as $id )
					{
						if( !getPostedValue('remove_related_info_'.$id) )
						{
							if( $id != 'add' )
							{
								$thisId = $id;
								$nextId = $id+1;
							}
							else
							{
								$thisId = $nextId;
							}
							drawRelatedInfoFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="related_info[]" onclick="setFormView(this.form, 'related_info_<?php printSafe($thisId) ?>')" value="add" title="Add Related Info" /></div>
			</td>
		</tr>		
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="verb" value="Save" onclick="wcPleaseWait(true, 'Processing...')" />&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">
				Fields marked * are mandatory.<br />
				Any existing registryObject with this Key will be overwritten.<br />
				Data other than Data Source	will be validated againt the RIF-CS schema.<br />
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText">	<div style="color:white; padding: 2px 6px; background-color:#B30000; border: 1px solid #B30000;  float:left; font-size:0.8em;"><b>Note:</b></div><br/>
									<div style="color:black; float:left; border: 1px solid #B30000; background-color:#ccc; padding:6px; font-size:0.8em;">
											This system does not yet support the <a href="http://ands.org.au/resource/rifcs_updates.html">new RIF-CS v1.2</a> elements (such as citations and coverage).
									</div>
			</td>
		</tr>
	</tbody>
</table>
</form>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';


function drawComplexNameFieldGroup($id)
{
	$parent = 'complex_name_'.$id;
	print("\n<!-- COMPLEX NAME -->\n");
	print('<input type="hidden" name="complex_names[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Type:</td>'."\n");
	print('			<td><input type="text" name="complex_name_type_'.esc($id).'" id="complex_name_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('complex_name_type_'.esc($id))).'" /> ');
	drawVocabControl('complex_name_type_'.$id,'RIFCSNameType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Lang:</td>'."\n");
	print('			<td><input type="text" name="complex_name_lang_'.esc($id).'" id="complex_name_lang_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('complex_name_lang_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Date From:</td>'."\n");
	print('			<td>');
	drawDateTimeInput('complex_name_date_from_'.esc($id), getPostedValue('complex_name_date_from_'.esc($id)), 'YYYY-MM-DDThh:mm:00Z');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Date To:</td>'."\n");
	print('			<td>');
	drawDateTimeInput('complex_name_date_to_'.esc($id), getPostedValue('complex_name_date_to_'.esc($id)), 'YYYY-MM-DDThh:mm:00Z');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Name Parts:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($parent.'_name_parts') )
	{
		$nextId = 0;
		foreach( getPostedValue($parent.'_name_parts') as $subId )
		{
			if( !getPostedValue($parent.'_remove_name_part_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawNamePartFieldGroup($parent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($parent).'_name_parts[]" onclick="setFormView(this.form, \''.esc($parent).'_name_part_'.esc($thisId).'\')" value="add" title="Add a Name Part" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_complex_name_'.esc($id).'" onclick="setFormView(this.form, \'complex_names\')" value="remove" title="Remove this Name" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawNamePartFieldGroup($parent, $id)
{
	print("\n<!-- NAME PART -->\n");
	print('<input type="hidden" name="'.esc($parent).'_name_parts[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'_name_part_'.esc($id).'" class="subtable1">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_name_part_value_'.esc($id).'" id="'.esc($parent).'_name_part_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_name_part_value_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_name_part_type_'.esc($id).'" id="'.esc($parent).'_name_part_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_name_part_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_name_part_type_'.$id,'RIFCSNamePartType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_name_part_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_name_parts\')" value="remove" title="Remove this Name Part" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawIdentifierFieldGroup($id)
{
	print("\n<!-- IDENTIFIER -->\n");
	print('<input type="hidden" name="identifiers[]" value="'.esc($id).'" />'."\n");
	print('<table id="identifier_'.esc($id).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="identifier_value_'.esc($id).'" id="identifier_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('identifier_value_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="identifier_type_'.esc($id).'" id="identifier_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('identifier_type_'.esc($id))).'" /> ');
	drawVocabControl('identifier_type_'.$id,'RIFCSIdentifierType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_identifier_'.esc($id).'" onclick="setFormView(this.form, \'identifiers\')" value="remove" title="Remove this Identifier" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawLocationFieldGroup($id)
{
	$parent = 'location_'.$id;
	print("\n<!-- LOCATION -->\n");
	print('<input type="hidden" name="locations[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Type:</td>'."\n");
	print('			<td><input type="text" name="location_type_'.esc($id).'" id="location_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('location_type_'.esc($id))).'" /> ');
	drawVocabControl('location_type_'.$id,'RIFCSLocationType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Date From:</td>'."\n");
	print('			<td>');
	drawDateTimeInput('location_date_from_'.esc($id), getPostedValue('location_date_from_'.esc($id)), 'YYYY-MM-DDThh:mm:00Z');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Date To:</td>'."\n");
	print('			<td>');
	drawDateTimeInput('location_date_to_'.esc($id), getPostedValue('location_date_to_'.esc($id)), 'YYYY-MM-DDThh:mm:00Z');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($parent).'_addresses" class="attribute">Addresses:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($parent.'_addresses') )
	{
		$nextId = 0;
		foreach( getPostedValue($parent.'_addresses') as $subId )
		{
			if( !getPostedValue($parent.'_remove_address_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawAddressFieldGroup($parent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($parent).'_addresses[]" onclick="setFormView(this.form, \''.esc($parent).'_address_'.esc($thisId).'\')" value="add" title="Add an Address" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($parent).'_spatial" class="attribute">Spatial:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($parent.'_spatial') )
	{
		$nextId = 0;
		foreach( getPostedValue($parent.'_spatial') as $subId )
		{
			if( !getPostedValue($parent.'_remove_spatial_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawSpatialFieldGroup($parent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($parent).'_spatial[]" onclick="setFormView(this.form, \''.esc($parent).'_spatial_'.esc($thisId).'\')" value="add" title="Add a Spatial Location" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_location_'.esc($id).'" onclick="setFormView(this.form, \'locations\')" value="remove" title="Remove this Location" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawAddressFieldGroup($parent, $id)
{
	$thisParent = $parent.'_address_'.$id;
	print("\n<!-- ADDRESS -->\n");
	print('<input type="hidden" name="'.esc($parent).'_addresses[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($thisParent).'" class="subtable1">'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($thisParent).'_electronic_addresses" class="attribute">Electronic:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($thisParent.'_electronic_addresses') )
	{
		$nextId = 0;
		foreach( getPostedValue($thisParent.'_electronic_addresses') as $subId )
		{
			if( !getPostedValue($thisParent.'_remove_electronic_address_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawElectronicAddressFieldGroup($thisParent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($thisParent).'_electronic_addresses[]" onclick="setFormView(this.form, \''.esc($thisParent).'_electronic_address_'.esc($thisId).'\')" value="add" title="Add an Electronic Address" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($thisParent).'_physical_addresses" class="attribute">Physical:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($thisParent.'_physical_addresses') )
	{
		$nextId = 0;
		foreach( getPostedValue($thisParent.'_physical_addresses') as $subId )
		{
			if( !getPostedValue($thisParent.'_remove_physical_address_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawPhysicalAddressFieldGroup($thisParent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($thisParent).'_physical_addresses[]" onclick="setFormView(this.form, \''.esc($thisParent).'_physical_address_'.esc($thisId).'\')" value="add" title="Add a Physical Address" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_address_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_addresses\')" value="remove" title="Remove this Address" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawElectronicAddressFieldGroup($parent, $id)
{
	$thisParent = $parent.'_electronic_address_'.$id;
	print("\n<!-- ELECTRONIC ADDRESS -->\n");
	print('<input type="hidden" name="'.esc($parent).'_electronic_addresses[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($thisParent).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_electronic_address_value_'.esc($id).'" id="'.esc($parent).'_electronic_address_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_electronic_address_value_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_electronic_address_type_'.esc($id).'" id="'.esc($parent).'_electronic_address_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_electronic_address_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_electronic_address_type_'.$id,'RIFCSElectronicAddressType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($thisParent).'_arguments" class="attribute">Arguments:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($thisParent.'_arguments') )
	{
		$nextId = 0;
		foreach( getPostedValue($thisParent.'_arguments') as $subId )
		{
			if( !getPostedValue($thisParent.'_remove_argument_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawElectronicAddressArgumentFieldGroup($thisParent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($thisParent).'_arguments[]" onclick="setFormView(this.form, \''.esc($thisParent).'_argument_'.esc($thisId).'\')" value="add" title="Add an Argument" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_electronic_address_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_electronic_addresses\')" value="remove" title="Remove this Electronic Address" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");	
}

function drawElectronicAddressArgumentFieldGroup($parent, $id)
{
	global $gChosen;
	
	print("\n<!-- ELECTRONIC ADDRESS ARGUMENT -->\n");
	print('<input type="hidden" name="'.esc($parent).'_arguments[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'_argument_'.esc($id).'" class="subtable1">'."\n");	
	print('		<tr>'."\n");
	print('			<td class="attribute">* Name:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_argument_name_'.esc($id).'" id="'.esc($parent).'_argument_name_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_argument_name_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Required:</td>'."\n");
	print("			<td>\n");
	print('           <select name="'.esc($parent).'_argument_required_'.esc($id).'" id="'.esc($parent).'_argument_required_'.esc($id).'">');	
	setChosen($parent.'_argument_required_'.esc($id), '', gITEM_SELECT);
	print("<option value=\"\"$gChosen></option>\n");
	setChosen($parent.'_argument_required_'.esc($id), 'true', gITEM_SELECT);
	print("<option value=\"true\"$gChosen>true</option>\n");
	setChosen($parent.'_argument_required_'.esc($id), 'false', gITEM_SELECT);
	print("<option value=\"false\"$gChosen>false</option>\n");
	print('           </select>');
	print("         </td>\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_argument_type_'.esc($id).'" id="'.esc($parent).'_argument_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_argument_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_argument_type_'.$id,'RIFCSArgType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Use:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_argument_use_'.esc($id).'" id="'.esc($parent).'_argument_use_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_argument_use_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_argument_use_'.$id,'RIFCSArgUse');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_argument_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_arguments\')" value="remove" title="Remove this Argument" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");	
}

function drawPhysicalAddressFieldGroup($parent, $id)
{
	$thisParent = $parent.'_physical_address_'.$id;
	print("\n<!-- PHYSICAL ADDRESS -->\n");
	print('<input type="hidden" name="'.esc($parent).'_physical_addresses[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($thisParent).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_physical_address_type_'.esc($id).'" id="'.esc($parent).'_physical_address_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_physical_address_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_physical_address_type_'.$id,'RIFCSPhysicalAddressType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Lang:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_physical_address_lang_'.esc($id).'" id="'.esc($parent).'_physical_address_lang_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_physical_address_lang_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($thisParent).'_address_parts" class="attribute">* Address Parts:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($thisParent.'_address_parts') )
	{
		$nextId = 0;
		foreach( getPostedValue($thisParent.'_address_parts') as $subId )
		{
			if( !getPostedValue($thisParent.'_remove_address_part_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawPhysicalAddressPartFieldGroup($thisParent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($thisParent).'_address_parts[]" onclick="setFormView(this.form, \''.esc($thisParent).'_address_part_'.esc($thisId).'\')" value="add" title="Add an Address Part" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_physical_address_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_physical_addresses\')" value="remove" title="Remove this Physical Address" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawPhysicalAddressPartFieldGroup($parent, $id)
{
	print("\n<!-- PHYSICAL ADDRESS PART -->\n");
	print('<input type="hidden" name="'.esc($parent).'_address_parts[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'_address_part_'.esc($id).'" class="subtable1">'."\n");	
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><textarea name="'.esc($parent).'_address_part_value_'.esc($id).'" id="'.esc($parent).'_address_part_value_'.esc($id).'" rows="6" cols="38" style="width: 318px; height: 100px">'.esc(getPostedValue($parent.'_address_part_value_'.esc($id))).'</textarea></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_address_part_type_'.esc($id).'" id="'.esc($parent).'_address_part_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_address_part_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_address_part_type_'.$id,'RIFCSAddressPartType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_address_part_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_address_parts\')" value="remove" title="Remove this Address Part" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");	
}

function drawSpatialFieldGroup($parent, $id)
{
	print("\n<!-- SPATIAL LOCATION -->\n");
	print('<input type="hidden" name="'.esc($parent).'_spatial[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'_spatial_'.esc($id).'" class="subtable1">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_spatial_value_'.esc($id).'" id="'.esc($parent).'_spatial_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_spatial_value_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_spatial_type_'.esc($id).'" id="'.esc($parent).'_spatial_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_spatial_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_spatial_type_'.$id,'RIFCSSpatialType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Lang:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_spatial_lang_'.esc($id).'" id="'.esc($parent).'_spatial_lang_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_spatial_lang_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_spatial_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_spatial\')" value="remove" title="Remove this Spatial Location" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");	
}

function drawRelatedObjectFieldGroup($id)
{
	$parent = 'related_object_'.$id;
	print("\n<!-- RELATED OBJECT -->\n");
	print('<input type="hidden" name="related_objects[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Key:</td>'."\n");
	print('			<td><input type="text" name="related_registry_object_key_'.esc($id).'" id="related_registry_object_key_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('related_registry_object_key_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td id="'.esc($parent).'_relations" class="attribute">* Relations:</td>'."\n");
	print('			<td>'."\n");
	$thisId = 0;
	if( getPostedValue($parent.'_relations') )
	{
		$nextId = 0;
		foreach( getPostedValue($parent.'_relations') as $subId )
		{
			if( !getPostedValue($parent.'_remove_relation_'.$subId) )
			{
				if( $subId != 'add' )
				{
					$thisId = $subId;
					$nextId = $subId+1;
				}
				else
				{
					$thisId = $nextId;
				}
				drawRelationFieldGroup($parent, $thisId);
				$thisId++;
			}
		}
	}
	print('<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="'.esc($parent).'_relations[]" onclick="setFormView(this.form, \''.esc($parent).'_relation_'.esc($thisId).'\')" value="add" title="Add a Relation" /></div>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_related_object_'.esc($id).'" onclick="setFormView(this.form, \'related_objects\')" value="remove" title="Remove this Related Object" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawRelationFieldGroup($parent, $id)
{
	print("\n<!-- RELATION -->\n");
	print('<input type="hidden" name="'.esc($parent).'_relations[]" value="'.esc($id).'" />'."\n");
	print('<table id="'.esc($parent).'_relation_'.esc($id).'" class="subtable1">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_relation_type_'.esc($id).'" id="'.esc($parent).'_relation_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_relation_type_'.esc($id))).'" /> ');
	drawVocabControl($parent.'_relation_type_'.$id, 'RIFCSRelationType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Description:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_relation_description_'.esc($id).'" id="'.esc($parent).'_relation_description_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_relation_description_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Description Lang:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_relation_description_lang_'.esc($id).'" id="'.esc($parent).'_relation_description_lang_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_relation_description_lang_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">URL:</td>'."\n");
	print('			<td><input type="text" name="'.esc($parent).'_relation_url_'.esc($id).'" id="'.esc($parent).'_relation_url_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue($parent.'_relation_url_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="'.esc($parent).'_remove_relation_'.esc($id).'" onclick="setFormView(this.form, \''.esc($parent).'_relations\')" value="remove" title="Remove this Relation" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawSubjectFieldGroup($id)
{
	print("\n<!-- SUBJECT -->\n");
	print('<input type="hidden" name="subjects[]" value="'.esc($id).'" />'."\n");
	print('<table id="subject_'.esc($id).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="subject_value_'.esc($id).'" id="subject_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('subject_value_'.esc($id))).'" /> ');
	drawVocabControl('subject_value_'.$id,'1297.0');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="subject_type_'.esc($id).'" id="subject_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('subject_type_'.esc($id))).'" /> ');
	drawVocabControl('subject_type_'.$id,'RIFCSSubjectType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Lang:</td>'."\n");
	print('			<td><input type="text" name="subject_lang_'.esc($id).'" id="subject_lang_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('subject_lang_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_subject_'.esc($id).'" onclick="setFormView(this.form, \'subjects\')" value="remove" title="Remove this Subject" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawDescriptionFieldGroup($id)
{
	print("\n<!-- DESCRIPTION -->\n");
	print('<input type="hidden" name="descriptions[]" value="'.esc($id).'" />'."\n");
	print('<table id="description_'.esc($id).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><textarea name="description_value_'.esc($id).'" id="description_value_'.esc($id).'" rows="6" cols="38" style="width: 318px; height: 100px">'.esc(getPostedValue('description_value_'.esc($id))).'</textarea></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Type:</td>'."\n");
	print('			<td><input type="text" name="description_type_'.esc($id).'" id="description_type_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('description_type_'.esc($id))).'" /> ');
	drawVocabControl('description_type_'.$id, 'RIFCSDescriptionType');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">Lang:</td>'."\n");
	print('			<td><input type="text" name="description_lang_'.esc($id).'" id="description_lang_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('description_lang_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_description_'.esc($id).'" onclick="setFormView(this.form, \'descriptions\')" value="remove" title="Remove this Description" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawAccessPolicyFieldGroup($id)
{
	print("\n<!-- ACCESS POLICY -->\n");
	print('<input type="hidden" name="access_policies[]" value="'.esc($id).'" />'."\n");
	print('<table id="access_policy_'.esc($id).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="access_policy_value_'.esc($id).'" id="access_policy_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('access_policy_value_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_access_policy_'.esc($id).'" onclick="setFormView(this.form, \'access_policies\')" value="remove" title="Remove this Access Policy" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawRelatedInfoFieldGroup($id)
{
	print("\n<!-- RELATED INFO -->\n");
	print('<input type="hidden" name="related_info[]" value="'.esc($id).'" />'."\n");
	print('<table id="related_info_'.esc($id).'" class="subtable">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	print('			<td><input type="text" name="related_info_value_'.esc($id).'" id="related_info_value_'.esc($id).'" size="40" maxlength="255" value="'.esc(getPostedValue('related_info_value_'.esc($id))).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_related_info_'.esc($id).'" onclick="setFormView(this.form, \'related_info\')" value="remove" title="Remove this Related Info" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}
?>
