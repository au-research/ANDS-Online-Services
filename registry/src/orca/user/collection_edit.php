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
$registryObject = getRegistryObject(getQueryValue('key'));
$registryObjectKey = null;
$urlPropertyIndex = 0;

if( !$registryObject )
{
	responseRedirect('index.php');
}
else
{
	$registryObjectKey = $registryObject[0]['registry_object_key'];

	// Get the values that we'll need to check for conditional display and access.
	$registryObjectRecordOwner = $registryObject[0]['record_owner'];
	
	// Check access.
	if( !userIsRegistryObjectRecordOwner($registryObjectRecordOwner) )
	{
		responseRedirect('index.php');
	}
}

$errorMessages = '';
$classTypeLabelClass = '';
$titleLabelClass = '';
$urlLabelClass = '';
$descriptionLabelClass = '';
$contributorsLabelClass = '';
$anzsrcLabelClass = '';
$subjectLabelClass = '';
$coverageLabelClass = '';
$accessRightsLabelClass = '';
$citationLabelClass = '';
$citationStyleLabelClass = '';

if( strtoupper(getPostedValue('verb')) == "CANCEL" )
{
	responseRedirect('collection_view.php?key='.urlencode($registryObjectKey));
}
if( strtoupper(getPostedValue('verb')) == "SAVE" )
{
	$url = getPostedValue('url');
	
	if( getPostedValue('class_type') == '' )
	{ 
		$classTypeLabelClass = gERROR_CLASS;
		$errorMessages .= "Type is a mandatory field.<br />";
	}
	if( strlen(getPostedValue('class_type')) > 32 )
	{
		$classTypeLabelClass = gERROR_CLASS;
		$errorMessages .= "Type cannot be longer than 32 single-byte characters.<br />";
	}
	
	if( getPostedValue('title') == '' )
	{ 
		$titleLabelClass = gERROR_CLASS;
		$errorMessages .= "Title is a mandatory field.<br />";
	}
	if( strlen(getPostedValue('title')) > 512 )
	{
		$titleLabelClass = gERROR_CLASS;
		$errorMessages .= "Title cannot be longer than 512 single-byte characters.<br />";
	}
	
	if( trim(getPostedValue('url')) == '' )
	{ 
		$urlLabelClass = gERROR_CLASS;
		$errorMessages .= "URL is a mandatory field.<br />";
	}
	if( strlen(getPostedValue('url')) > 512 )
	{
		$urlLabelClass = gERROR_CLASS;
		$errorMessages .= "URL cannot be longer than 512 single-byte characters.<br />";
	}
	
	if( getPostedValue('description') == '' )
	{ 
		$descriptionLabelClass = gERROR_CLASS;
		$errorMessages .= "Description is a mandatory field.<br />";
	}
	if( strlen(getPostedValue('description')) > 4000 )
	{
		$descriptionLabelClass = gERROR_CLASS;
		$errorMessages .= "Description cannot be longer than 4000 single-byte characters.<br />";
	}
	if( strlen(getPostedValue('contributors')) > 4000 )
	{
		$contributorsLabelClass = gERROR_CLASS;
		$errorMessages .= "Contributors cannot be longer than 4000 single-byte characters.<br />";
	}
	
	if( getPostedValue('anzsrcfors') )
	{
		foreach( getPostedValue('anzsrcfors') as $id )
		{
			if( strlen(getPostedValue('anzsrcfor_value_'.$id)) > 512 )
			{
				$anzsrcLabelClass = gERROR_CLASS;
				$errorMessages .= "ANZSRC Fields of Research cannot be longer than 512 single-byte characters.<br />";
				break;
			}
		}
	}

	if( getPostedValue('keywords') )
	{
		foreach( getPostedValue('keywords') as $id )
		{
			if( strlen(getPostedValue('keyword_value_'.$id)) > 512 )
			{
				$subjectLabelClass = gERROR_CLASS;
				$errorMessages .= "Subject Keywords cannot be longer than 512 single-byte characters.<br />";
				break;
			}
		}
	}
	
	if( getPostedValue('coverage') )
	{ 
		if( strlen(getPostedValue('coverage')) > 4000 )
		{
			$coverageLabelClass = gERROR_CLASS;
			$errorMessages .= "Spatial Coverage cannot be longer than 4000 single-byte characters.<br />";
		}
		if( !validKmlPolyCoords(getPostedValue('coverage')) )
		{
			$coverageLabelClass = gERROR_CLASS;
			$errorMessages .= "Spatial Coverage must be described with valid KML coordinates (refer to the help for more information).<br />";
		}
	}

	if( trim(getPostedValue('citation')) != '' )
	{
		if (strlen(trim(getPostedValue('citation'))) > 512) 
		{
			$citationLabelClass = gERROR_CLASS;
			$errorMessages .= "Citation must not exceed 512 characters.<br />";	
		}		
	}	
	
	if( strlen(getPostedValue('accessrights')) > 4000 )
	{
		$accessRightsLabelClass = gERROR_CLASS;
		$errorMessages .= "Access Rights cannot be longer than 4000 single-byte characters.<br />";
	}
	
	if( !$errorMessages )
	{
		$dataSourceKey = 'PUBLISH_MY_DATA';
		$objectGroup = 'Publish My Data';
		$handle = $registryObjectKey;		
		
		// Get the party object.
		$partyObject = getUserPartyObject();
		if(isset($partyObject[0]['registry_object_key']))
				$partyObjectKey = $partyObject[0]['registry_object_key'];
		else
				$partyObjectKey = $partyObject[0]['draft_key'];

		// Build the RIF-CS from the posted data.
		// =====================================================================
		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		// ---------------------------------------------------------------------
		// registryObject
		$rifcs .= '  <registryObject group="'.esc($objectGroup).'">'."\n";
		// ---------------------------------------------------------------------		
		// key
		$rifcs .= '    <key>'.esc($registryObjectKey).'</key>'."\n";
		// ---------------------------------------------------------------------		
		// originatingSource
		$rifcs .= '    <originatingSource>'.esc(eORIGSOURCE_PUBLISH_MY_DATA).'</originatingSource>'."\n";
		// ---------------------------------------------------------------------		
		// collection
		$rifcs .= '    <collection type="'.esc(getPostedValue('class_type')).'">'."\n";
		// ---------------------------------------------------------------------
		// title
		$rifcs .= '      <name>'."\n";
		$rifcs .= '        <namePart>'.esc(getPostedValue('title')).'</namePart>'."\n";
		$rifcs .= '      </name>'."\n";
		
		// ---------------------------------------------------------------------
		// uri
		$rifcs .= '      <location>'."\n";
		$rifcs .= '        <address>'."\n";
		$rifcs .= '          <electronic type="url">'."\n";
		$rifcs .= '            <value>'.esc(pidsGetHandleURI($handle)).'</value>'."\n";
		$rifcs .= '          </electronic>'."\n";
		$rifcs .= '        </address>'."\n";
		$rifcs .= '      </location>'."\n";
			
		// ---------------------------------------------------------------------
		// coverage
		if( $coverage = getPostedValue('coverage') )
		{
			$rifcs .= '      <coverage>'."\n";
			$rifcs .= '        <spatial type="kmlPolyCoords">'.esc($coverage).'</spatial>'."\n";
			$rifcs .= '      </coverage>'."\n";
		}
			
		if ( strlen(trim(getPostedValue('citation'))) > 0)
		{
		// ---------------------------------------------------------------------
		// citationInfo
			$rifcs .= '      <citationInfo>'."\n";
			$rifcs .= '        <fullCitation>'.esc(getPostedValue('citation')).'</fullCitation>'."\n";
			$rifcs .= '      </citationInfo>'."\n";
		}
		// ---------------------------------------------------------------------
		// related object
		$rifcs .= '      <relatedObject>'."\n";
		$rifcs .= '        <key>'.esc($partyObjectKey).'</key>'."\n";
		$rifcs .= '        <relation type="hasAssociationWith" />'."\n";
		$rifcs .= '      </relatedObject>'."\n";

		// ---------------------------------------------------------------------
		// anzsrcfors
		if( getPostedValue('anzsrcfors') )
		{
			foreach( getPostedValue('anzsrcfors') as $id )
			{
				if( getPostedValue('anzsrcfor_value_'.$id) )
				{
					$rifcs .= '      <subject type="anzsrc-for">'.esc(getPostedValue('anzsrcfor_value_'.$id)).'</subject>'."\n";
				}
			}
		}
		
		// ---------------------------------------------------------------------
		// keywords
		if( getPostedValue('keywords') )
		{
			foreach( getPostedValue('keywords') as $id )
			{
				if( getPostedValue('keyword_value_'.$id) )
				{
					$rifcs .= '      <subject type="local">'.esc(getPostedValue('keyword_value_'.$id)).'</subject>'."\n";
				}
			}
		}
		
		// ---------------------------------------------------------------------
		// description
		$rifcs .= '      <description type="brief">'.esc(getPostedValue('description')).'</description>'."\n";	
		// ---------------------------------------------------------------------
		// contributors
		if( $contributors = getPostedValue('contributors') )
		{
			$rifcs .= '      <description type="contributors">'.esc($contributors).'</description>'."\n";	
		}
		// ---------------------------------------------------------------------
		// access rights
		if( $accessrights = getPostedValue('accessrights') )
		{
			$rifcs .= '      <description type="rights">'.esc($accessrights).'</description>'."\n";	
		}
		// ---------------------------------------------------------------------		
		// collection
		$rifcs .= '    </collection>'."\n";
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
			// XXX: libxml2.6 workaround (Save to local filesystem before validating)
				  
			// Create temporary file and save manually created DOMDocument.
			$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
			$registryObjects->save($tempFile);
				 
			// Create temporary DOMDocument and re-load content from file.
			$registryObjects = new DOMDocument();
			$registryObjects->load($tempFile);
				  
			// Delete temporary file.
			if (is_file($tempFile))
			{
			  unlink($tempFile);
			}			
			$result = $registryObjects->schemaValidate(gRIF_SCHEMA_PATH); //xxx
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
				$runErrors = importRegistryObjects($registryObjects, $dataSourceKey, $resultMessage, getLoggedInUser(), PENDING, getThisOrcaUserIdentity());
				if( $runErrors )
				{
					$errorMessages .= "Import Errors";
				}
				syncDraftKey($registryObjectKey, 'PUBLISH_MY_DATA');
				queueSyncDataSource('PUBLISH_MY_DATA');
				// Log the datasource activity.
				insertDataSourceEvent($dataSourceKey, "ADD REGISTRY OBJECT\nKey: ".$registryObjectKey."\n".$resultMessage);
			}
		}

		$urlPropertyIndex = getPostedValue('url_property_index');
		if( !$errorMessages )
		{
			if( $urlPropertyIndex )
			{
				// Update the URL for the handle.
				$errorMessages = pidsUpdatePropertyValue($registryObjectKey, $urlPropertyIndex, $url);
			}
			else
			{
				// Add a URL property to this handle.
				$errorMessages = pidsAddURLProperty($registryObjectKey, $url);
			}
		}
		if( !$errorMessages )
		{
			responseRedirect('collection_view.php?key='.urlencode($registryObjectKey));
		}
	}
}
else
{
	// Get the URL from the handle service.
	$url = '';
	$urlPropertyIndex = '';
	$urlProperty = pidsGetFirstURLProperty($registryObjectKey);
	if( $urlProperty )
	{
		$urlPropertyIndex = $urlProperty->getAttribute("index");
		$url = $urlProperty->getAttribute("value");
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<script type="text/javascript" src="<?php print ePROTOCOL ?>://maps.google.com/maps/api/js?sensor=false&libraries=drawing"></script>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/map_control.js"></script>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<script type="text/javascript">mctInit('<?php print gORCA_IMAGE_ROOT ?>_controls/_map_control/')</script>
<script type="text/javascript">vcSetImagePath('<?php print gORCA_IMAGE_ROOT ?>_controls/_vocab_control/')</script>
<form id="collection_edit" action="collection_edit.php?key=<?php printSafe(urlencode($registryObjectKey)) ?>" method="post">
<table class="formTable" summary="Edit Collection">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Edit Collection</td>
			<td></td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td></td>
			<td colspan="2" class="errorText"><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } ?>
	<tbody class="formFields">
		<tr>
			<td colspan="3" class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;">
				Help for this application is available from the <b><a rel="help" href="<?php print eAPP_ROOT.'help.php?id='.$gThisActivityID.'&amp;page='.urlencode($_SERVER['REQUEST_URI']).'#edit' ?>" title="Help for this page (new window)">Help</a></b> link at the top right hand corner of the page.<br />
				Fields marked * are mandatory.<br />
				The modified collection will require approval.
			</td>
		</tr>
		<!-- KEY -->
		<tr>
			<td>* Key:</td>
			<td>
				<?php printSafe($registryObjectKey); ?>
				<input type="hidden" name="key" value="<?php printSafe($registryObjectKey) ?>" />
			</td>
			<td></td>
		</tr>
		<!-- TYPE -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="border-top: 1px dotted #888888; height: 2.8em; vertical-align: bottom; white-space: normal;">
			What type of collection is this?</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($classTypeLabelClass); ?>>* Type:</td>
			<td>
				<input type="text" name="class_type" id="class_type" size="32" maxlength="32" value="<?php printSafe(getRegistryFormValue('class_type', $registryObject[0]['type'])) ?>" />
				<?php drawVocabControl('class_type', 'RIFCSCollectionType'); ?>
			</td>
			<td class="formNotes" style="width: 300px; white-space: normal;">You can select a type by clicking
			on the vocabulary widget located at the right of the entry box or if
			none of these apply you can enter your own. See <b><a rel="help" href="<?php print eAPP_ROOT.'help.php?id='.$gThisActivityID.'&amp;page='.urlencode($_SERVER['REQUEST_URI']).'#edit' ?>" title="Help for this page (new window)">Help</a></b> for explanations
			of each of the types listed by the widget.</td>
		</tr>
		
		<!-- TITLE -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
				What is the name of your collection? 
			</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($titleLabelClass); ?>>* Title:</td>
			<td><input type="text" name="title" id="title" size="40" maxlength="512" value="<?php printSafe(getRegistryFormValue('title', getNameHTML($registryObjectKey))) ?>" /></td>
			<td class="formNotes" style="width: 300px; white-space: normal;">You should ensure that this name will be meaningful to researchers outside your discipline area.</td>
		</tr>

		<!-- URI  -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
				Where is your collection located? 
			</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($urlLabelClass); ?>>* URL:</td>
			<td>
				<input type="text" name="url" id="url" size="40" maxlength="512" value="<?php printSafe($url) ?>" />
				<input type="hidden" name="url_property_index" id="url_property_index" value="<?php printSafe($urlPropertyIndex) ?>" />
			</td>
			<td class="formNotes" style="width: 300px; white-space: normal;">You should enter this location in the form http://www.example.com/</td>
		</tr>

		<!-- DESCRIPTION  -->
		<tr>
			<td style="border-top: 1px dotted #888888;"<?php print($descriptionLabelClass); ?>>* Description:</td>
			<td style="border-top: 1px dotted #888888;"><textarea name="description" id="description" rows="6" cols="38" style="width: 318px; height: 100px"><?php printSafe(getRegistryFormValue('description', getUserCollectionDescription($registryObjectKey, 'brief'))) ?></textarea></td>
			<td class="formNotes" style="width: 300px; white-space: normal; border-top: 1px dotted #888888;">
			Provide a short
			description of your collection. You should ensure that this
			description will be meaningful to researchers outside your discipline
			area. Try to include key information (who, what, when, where, how)
			that will help other researchers to discover your collection via text
			searching.</td>
		</tr>
		
		
		<tr>
			<td colspan="3" class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;">
				<b>Completing the following fields is optional, but will add value to your collection record.</b>
			</td>
		</tr>
		

		<!-- CONTRIBUTORS  -->
		<tr>
			<td></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal;">
			Were there any other contributors to this collection?
			</td>
			<td></td>
		</tr>
		<tr>
			<td<?php print($contributorsLabelClass); ?>>Contributors:</td>
			<td><textarea name="contributors" id="contributors" rows="6" cols="38" style="width: 318px; height: 100px"><?php printSafe(getRegistryFormValue('contributors', getUserCollectionDescription($registryObjectKey, 'contributors'))) ?></textarea></td>
			<td class="formNotes" style="width: 300px; white-space: normal;"> You should enter their name and role in the form: 
			Dr John Smith, Research Assistant. You can include institutions or organisations in this section. e.g. Australian National University.</td>		
		</tr>
		
		<!-- ANZSRCFOR  -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
			What fields of research does your collection relate to?
			</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($anzsrcLabelClass); ?> id="anzsrcfors">ANZSRC Fields of Research:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('anzsrcfors') )
				{
					$nextId = 0;
					foreach( getPostedValue('anzsrcfors') as $id )
					{
						if( !getPostedValue('remove_anzsrcfor_'.$id) )
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
							drawANZSRCFORFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				elseif( !getPostedValue('key') && getSubjects($registryObjectKey) )
				{
					$array = getSubjects($registryObjectKey);
					asort($array);
					foreach( $array as $row )
					{
						if( $row['type'] == "anzsrc-for" )
						{
							drawANZSRCFORFieldGroup($row['subject_id'], $row);
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="anzsrcfors[]" onclick="setFormView(this.form, 'anzsrcfor_<?php printSafe($thisId) ?>')" value="add" title="Add an ANZSRC Field of Research" /></div>
			</td>
			<td class="formNotes" style="width: 300px; white-space: normal;"> Choose a field of research from the vocabulary widget. 
			You can find descriptive information about the ANZSRC tags on the ABS website.
			Click on the add button to add additional field of research tags for your collection.</td>
		</tr>
		
		<!-- SUBJECTS -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
			What are the key subject terms relating to this collection?
			</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($subjectLabelClass); ?> id="keywords">Subject Keywords:</td>
			<td>
				<?php 
				$thisId = 0;
				if( getPostedValue('keywords') )
				{
					$nextId = 0;
					foreach( getPostedValue('keywords') as $id )
					{
						if( !getPostedValue('remove_keyword_'.$id) )
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
							drawKeywordFieldGroup($thisId);
							$thisId++;
						}
					}
				}
				elseif( !getPostedValue('key') && getSubjects($registryObjectKey) )
				{
					$array = getSubjects($registryObjectKey);
					asort($array);
					foreach( $array as $row )
					{
						if( $row['type'] == "local" )
						{
							drawKeywordFieldGroup($row['subject_id'], $row);
						}
					}
				}
				?>
				<div style="margin: 0px; margin-left: 6px; margin-top: 2px;"><input type="submit" class="buttonSmall" name="keywords[]" onclick="setFormView(this.form, 'keyword_<?php printSafe($thisId) ?>')" value="add" title="Add a Subject Keyword" /></div>
			<td class="formNotes" style="width: 300px; white-space: normal;">Click on the add button to add additional subject terms for your collection.</td>
		</tr>

		<!-- SPATIAL COVERAGE  -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
			What is the spatial coverage of your collection?</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($coverageLabelClass); ?>>Spatial Coverage:</td>
			<td>
				<input name="coverage" id="coverage" type="hidden" value="<?php printSafe(getRegistryFormValue('coverage', getUserSpatialCoverage($registryObjectKey))) ?>" />
				<script type="text/javascript">mctSetMapControl("coverage");</script>
			</td>
			<td class="formNotes" style="width: 300px; white-space: normal;">Choose a method for marking coverage from the options in the black bar above the map.</td>
		</tr>
		
		<!-- CITATIONS -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
			How should this dataset be cited?</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<?php $citation = getUserCitation($registryObjectKey); ?>
		<tr>
			<td<?php print($citationLabelClass); ?>>Citation:</td>
			<td>
				<textarea name="citation" id="citation" rows="6" cols="38" style="width: 318px; height: 100px"><?php printSafe(getRegistryFormValue('citation', $citation['value'])) ?></textarea>
			</td>
			<td class="formNotes" style="width: 300px; white-space: normal;">Use this field to provide a full citation for your collection.</td>
		</tr>
			
		<!-- ACCESS RIGHTS  -->
		<tr>
			<td style="border-top: 1px dotted #888888;"></td>
			<td class="formNotes" style="height: 2.8em; vertical-align: bottom; white-space: normal; border-top: 1px dotted #888888;">
			What are the access rights relating to your collection?</td>
			<td style="border-top: 1px dotted #888888;"></td>
		</tr>
		<tr>
			<td<?php print($accessRightsLabelClass); ?>>Access Rights:</td>
			<td><textarea name="accessrights" id="accessrights" rows="6" cols="38" style="width: 318px; height: 100px"><?php printSafe(getRegistryFormValue('accessrights', getUserCollectionDescription($registryObjectKey, 'rights'))) ?></textarea></td>
			<td class="formNotes" style="width: 300px; white-space: normal;"> Is the data
			publicly available or are there security, privacy or ethics
			constraints in place?</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td>
				<input type="submit" name="verb" value="Cancel"/>&nbsp;&nbsp;
				<input type="submit" name="verb" value="Save" onclick="wcPleaseWait(true, 'Processing...')" />&nbsp;&nbsp;
			</td>
			<td></td>
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

function getRegistryFormValue($formField, $objectValue)
{
	$value = $objectValue;
	if( isset($_POST[$formField]) )
	{
		$value = getPostedValue($formField);
	}
	return $value;
}

function getUserCitation($registryObjectKey)
{
	$ret = array('value'=>'', 'style'=>''); 
	
	$value = getCitationInformation($registryObjectKey);
	if (isset($value[0]['full_citation']) && $value[0]['full_citation'] != null) {
		$ret['value'] = $value[0]['full_citation'];
		$ret['style'] = $value[0]['style'];
	}

	return $ret;
}

function getUserSpatialCoverage($registryObjectKey)
{
	$coverage = '';
	if( $coverage = getCoverage($registryObjectKey) )
	{
		foreach( $coverage as $row )
		{
			if( $spatialCoverage = getSpatialCoverage($row['coverage_id']) )
			{
				foreach ( $spatialCoverage as $spatial )
				{
					$coverage = $spatial['value'];
				}
			}
		}
	}
	return $coverage;
}

function getUserCollectionDescription($registryObjectKey, $type)
{
	$description = '';
	if( $descrs = getDescriptions($registryObjectKey) )
	{	
		foreach( $descrs as $descr )
		{
			if( $descr['type'] == $type )
			{
				$description = $descr['value'];
				break;
			}
		}
	}
	return $description;
}

function drawANZSRCFORFieldGroup($id, $row=null)
{
	print("\n<!-- ANZSRCFOR -->\n");
	print('<input type="hidden" name="anzsrcfors[]" value="'.esc($id).'" />'."\n");
	print('<table id="anzsrcfor_'.esc($id).'" class="subtable" style="width: 320px;">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	$value = getPostedValue('anzsrcfor_value_'.esc($id));
	if( !getPostedValue('key') )
	{
		$value = $row['value'];
	}
	print('			<td><input type="text" name="anzsrcfor_value_'.esc($id).'" id="anzsrcfor_value_'.esc($id).'" size="26" maxlength="512" value="'.esc($value).'" /> ');
	drawVocabControl('anzsrcfor_value_'.$id,'1297.0','FOR');
	print('</td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_anzsrcfor_'.esc($id).'" onclick="setFormView(this.form, \'anzsrcfors\')" value="remove" title="Remove this ANZSRC Field of Research" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}

function drawKeywordFieldGroup($id, $row=null)
{
	print("\n<!-- KEYWORDS -->\n");
	print('<input type="hidden" name="keywords[]" value="'.esc($id).'" />'."\n");
	print('<table id="keyword_'.esc($id).'" class="subtable" style="width: 320px;">'."\n");
	print('		<tr>'."\n");
	print('			<td class="attribute">* Value:</td>'."\n");
	$value = getPostedValue('keyword_value_'.esc($id));
	if( !getPostedValue('key') )
	{
		$value = $row['value'];
	}
	print('			<td><input type="text" name="keyword_value_'.esc($id).'" id="keyword_value_'.esc($id).'" size="26" maxlength="512" value="'.esc($value).'" /></td>'."\n");
	print('		</tr>'."\n");
	print('		<tr>'."\n");
	print('			<td><input type="submit" class="buttonSmall" name="remove_keyword_'.esc($id).'" onclick="setFormView(this.form, \'keywords\')" value="remove" title="Remove this Subject Keyword" /></td>'."\n");
	print('			<td></td>'."\n");
	print('		</tr>'."\n");
	print('	</table>'."\n");
}
?>
