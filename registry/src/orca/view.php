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
require '../_includes/init.php';
require 'orca_init.php';
// Page processing
// -----------------------------------------------------------------------------

// Get the record from the database.
$registryObject = getRegistryObject(getQueryValue('key'), true);
$errorMessages = '';
$existingRelatedArray = Array();
$registryObjectKey = null;
$dataSourceKey = null;
$registryObjectRecordOwner = null;
$registryObjectDataSourceRecordOwner = null;
$registryObjectStatus = null;
$dataSource = null;

if( !$registryObject )
{

	responseRedirect('search.php');
}
else
{
	$registryObjectKey = $registryObject[0]['registry_object_key'];
	$dataSourceKey = $registryObject[0]['data_source_key'];
	$dataSource = getDataSources($dataSourceKey, null);
	$existingRelatedArray = Array();
	// Get the values that we'll need to check for conditional display and access.
	$registryObjectRecordOwner = $registryObject[0]['record_owner'];
	$registryObjectDataSourceRecordOwner = $dataSource[0]['record_owner'];
	$registryObjectStatus = trim($registryObject[0]['status']);

	// Check access.
	if( !(in_array($registryObjectStatus, array(PUBLISHED, APPROVED)) || userIsORCA_ADMIN() || userIsORCA_LIAISON() || $registryObjectDataSourceRecordOwner == getThisOrcaUserIdentity() || $registryObjectRecordOwner == getThisOrcaUserIdentity()) )
	{
		responseRedirect('search.php');
	}

	// Get any action that may have been posted.
	$action = strtoupper(getPostedValue('action'));

	// Action the action.
	switch( $action )
	{
		case 'EDIT':
			responseRedirect("manage/add_" .strtolower(urlencode(getPostedValue('class'))). "_registry_object.php?key=".esc(urlencode(getQueryValue('key')))."&data_source=".esc(urlencode($dataSourceKey)));
			break;
		case 'DELETE':
			responseRedirect('admin/registry_object_delete.php?key='.urlencode(getQueryValue('key')));
			break;
		case 'UPDATESTATUS':
			if( userIsORCA_ADMIN() && $dataSourceKey == "PUBLISH_MY_DATA" ) // && $registryObjectRecordOwner != SYSTEM
			{
				// Update the registry object status.
				$errorMessages = updateRegistryObjectStatus($registryObjectKey, getPostedValue("status"));
				// Get the updated object.
				$registryObject = getRegistryObject($registryObjectKey);
				$registryObjectStatus = trim($registryObject[0]['status']);
			}
			break;
	}

	$registryObjectClass = $registryObject[0]['registry_object_class'];
	$registryObjectType = $registryObject[0]['type'];

	$registryObjectName = getNameHTML($registryObjectKey, '');
	if( trim($registryObjectName) == '' )
	{
		$registryObjectName = esc($registryObjectKey);
	}

	$pageTitle = $registryObjectClass.' ('.$registryObjectType.'): '.$registryObjectName;
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../_includes/header.php';
getAnalyticsTrackingCode(eGOOGLE_ANALYTICS_TRACKING_CODE_ORCA);
// BEGIN: Page Content
// =============================================================================

if( $registryObject )
{
	$objectGroup = $registryObject[0]['object_group'];
	$registryObjectClass = $registryObject[0]['registry_object_class'];
	$registryObjectType = $registryObject[0]['type'];
	$registryObjectSource = $registryObject[0]['display_title'];
	$originatingSourceHTML = esc($registryObject[0]['originating_source']);
	if( $registryObject[0]['originating_source_type'] )
	{
		$originatingSourceHTML = '<span class="attribute" title="originating source type">'.esc($registryObject[0]['originating_source_type']).':</span> '.$originatingSourceHTML;
	}

	$objectGroup = $registryObject[0]['object_group'];
	$dateAccessioned= getXMLDateTime($registryObject[0]['date_accessioned']);
	$dateModified = getXMLDateTime($registryObject[0]['date_modified']);

	$createdWhen = formatDateTime($registryObject[0]['created_when']);
	$createdWho = $registryObject[0]['created_who'];
	$url_slug = getRegistryObjectURLSlug($registryObjectKey);
	print('<table class="recordTable" summary="Data Source">'."\n");
	print("	<thead>\n");

	$rdaLink = '';

	// The link to the RDA
	$rdaLinkPrefix = 'View';
	if( $registryObjectStatus != PUBLISHED )
	{
		$rdaLinkPrefix = 'Preview';
	}
	if(isContributorPage($registryObjectKey)) {
		$rdaLink = '<br /><a style="font-size:0.8em; font-weight: normal;" href="http://'.$host.'/'.$rda_root . '/view/group/?group='.urlencode($registryObjectKey). '&groupName='.esc($objectGroup).'">'.$rdaLinkPrefix.' this record in Research Data Australia</a>'."\n";
	} else {
		$rdaLink = '<br /><a style="font-size:0.8em; font-weight: normal;" href="http://'.$host.'/'.$rda_root . '/' . $url_slug.'">'.$rdaLinkPrefix.' this record in Research Data Australia</a>'."\n";
	}

	$recordHistory = "";
	if( userIsDataSourceRecordOwner($registryObjectDataSourceRecordOwner) || userIsORCA_ADMIN() )
	{
		$recordHistory = "<span style='float:right;'><a style='font-size:0.8em; font-weight: normal;' href='".eAPP_ROOT."orca/manage/view_history.php?action=record_view&key=".urlencode($registryObjectKey)."&data_source_key=".urlencode($dataSourceKey)."'>view record history</a></span>";
	}

	//CC-47

	print('<span class="hide" id="key">'.$registryObjectKey.'</span>');
	$rifcs_button = '
		<div id="rifcs_container">
			<a href="#" id="rifcs_button"><img title="Get RIF-CS XML for this record" src="'.gORCA_IMAGE_ROOT.'rifcs.gif" alt="Get RIFCS"/></a>
			<div id="rifcs_popup">
				<ul>
					<li><a href="#" id="rifcs_view">View RIF-CS</a></li>
					<li><a href="'.eAPP_ROOT.'orca/services/getRegistryObject.php?key='.urlencode($registryObjectKey).'&type=download" id="rifcs_download">Download RIF-CS</a></li>
				</ul>
			</div>
			<div id="rifcs_plain" class="hide">
				<img src="'.gORCA_IMAGE_ROOT.'delete_16.png" style="float:right;" class="closeBlockUI"/>
				<textarea id="rifcs_plain_content"></textarea>
			</div>
		</div>
	';

	echo '';
	drawRecordField($rifcs_button, esc($registryObjectClass) . $recordHistory .$rdaLink);
	//drawRecordField("<a href=\"services/getRegistryObject.php?key=".esc(urlencode($registryObjectKey))."\"><img title=\"Get RIF-CS XML for this record\" src=\"".gORCA_IMAGE_ROOT."rifcs.gif\" alt=\"\" /></a>", esc($registryObjectClass) . $recordHistory .$rdaLink);


	print("	</thead>\n");
	print('	<tbody class="recordFields">'."\n");

	if( userIsORCA_ADMIN() || ($registryObjectRecordOwner == getThisOrcaUserIdentity() && $dataSourceKey == "PUBLISH_MY_DATA") )
	{
		drawRecordField("Status:",  getRegistryObjectStatusSpan($registryObjectStatus));
	}

	drawRecordField("Type:",  esc($registryObjectType));
	drawRecordField("Key:",  esc($registryObjectKey));
	if( userIsORCA_ADMIN() )
	{
		echo "<span color='#ccc'>";
		drawRecordField("Originating Source:",  esc($registryObjectSource));
		drawRecordField("URL \"SLUG\":", esc($url_slug));
		drawRecordField("DS Key:", $dataSourceKey);
		drawRecordField("DS Key Hash:", getDataSourceHashForKey($dataSourceKey));
		drawRecordField("Record Key Hash:", getRegistryObjectHashForKey($registryObjectKey));
		echo "</span>";
	}
	drawRecordField("Originating Source:", $originatingSourceHTML);
	drawRecordField("Group:", esc($objectGroup));

	if( $dateAccessioned )
	{
		drawRecordField("Date Accessioned:", esc($dateAccessioned));
	}

	if( $dateModified )
	{
		drawRecordField("Date Modified:", esc($dateModified));
	}
	if( $array = getExistenceDates($registryObjectKey) )
	{
		print("\n<!-- EXISTENCE DATES -->\n");
		print("		<tr>\n");
		print("			<td>Existence Dates:</td>\n");
		print("			<td><table class='subtable'>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawExistenceDates($row['existence_date_id'], $row);
		}
		print("			</table></td>\n");
		print("		</tr>\n");
	}
	if( $array = getComplexNames($registryObjectKey) )
	{
		print("\n<!-- NAMES -->\n");
		print("		<tr>\n");
		print("			<td>Names:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawName($row['complex_name_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getIdentifiers($registryObjectKey) )
	{
		print("\n<!-- IDENTIFIERS -->\n");
		print("		<tr>\n");
		print("			<td>Identifiers:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawIdentifier($row['identifier_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getLocations($registryObjectKey) )
	{
		print("\n<!-- LOCATIONS -->\n");
		print("		<tr>\n");
		print("			<td>Locations:");
		if( hasSpatialKMLData($registryObjectKey ,'location') )
		{
			print("<br /><a href=\"http://".eHOST."/".eROOT_DIR."/orca/services/getRegistryObjectKML.php?key=".esc(urlencode($registryObjectKey))."\"><img title=\"Get any KML that can be derived from coverage information in this record\" src=\"".gORCA_IMAGE_ROOT."kml.gif\" alt=\"\" /></a>");
		}
		print("</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawLocation($row['location_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getCoverage($registryObjectKey) )
	{
		print("\n<!-- COVERAGE-->\n");
		print("		<tr>\n");
		print("			<td>Coverage:");
		if( hasSpatialKMLData($registryObjectKey, 'coverage') )
		{
			print("<br /><a href=\"http://".eHOST."/".eROOT_DIR."/orca/services/getRegistryObjectKML.php?key=".esc(urlencode($registryObjectKey))."\"><img title=\"Get any KML that can be derived from coverage information in this record\" src=\"".gORCA_IMAGE_ROOT."kml.gif\" alt=\"\" /></a>");
		}
		print("</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawCoverage($row['coverage_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}
	$allow_reverse_internal_links = $dataSource[0]['allow_reverse_internal_links'];
	$allow_reverse_external_links = $dataSource[0]['allow_reverse_external_links'];
	$create_primary_relationships = $dataSource[0]['create_primary_relationships'];
	$reverseRelatedArrayExt = Array();
	$reverseRelatedArrayInt = Array();
	$relatedArray = Array();
	$existingRelatedArray = Array();
	//$relatedArray = getRelatedObjects($registryObjectKey);
	//$reverseRelatedArrayInt = getInternalReverseRelatedObjects($registryObjectKey, $dataSourceKey);
	if( $create_primary_relationships=='t' || $relatedArray = getRelatedObjects($registryObjectKey) || ($allow_reverse_internal_links == 't' && $reverseRelatedArrayInt = getInternalReverseRelatedObjects($registryObjectKey, $dataSourceKey)) || ($allow_reverse_external_links == 't' && $reverseRelatedArrayExt = getExternalReverseRelatedObjects($registryObjectKey, $dataSourceKey)))
	{
			//we need to check if this datasource has primary relationships set up.
			//	echo "we are here".$create_primary_relationships."::".$relatedArray."::".$allow_reverse_internal_links ."::".$reverseRelatedArrayInt."::".$allow_reverse_external_links."::".$reverseRelatedArrayExt."<br />";
//	print("...<pre>");
	//print_r($reverseRelatedArrayInt);
	//print("</pre>...");
	//print("...<pre>");
	//print_r($relatedArray);
//	print("</pre>...");
		$pkey1 = '';
		$pkey2 = '';
		if($create_primary_relationships == 't'||$create_primary_relationships == '1')
		{

			$pkey1 =  $dataSource[0]['primary_key_1'];
			$pkey2 =  $dataSource[0]['primary_key_2'];
		}

		print("\n<!-- RELATED OBJECTS -->\n");
		print("		<tr>\n");
		print("			<td>Related Objects:</td>\n");
		print("			<td>\n");
		if($pkey1!='' && $pkey1!=$registryObjectKey )
		{

			if( $relatedObject=getRegistryObject($pkey1,true) )
			{

			print("\n<!-- RELATED OBJECT -->\n");
			print('<table class="subtable">'."\n");
				$type = $dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1'];
				if( trim($relatedObject[0]['status']) == PUBLISHED || userIsORCA_ADMIN() )
				{


					// The related object exists in the registry.
					$relationName = getNameHTML($pkey1);
					if( $relationName == '' )
					{
						$relationName = $relatedObject[0]['registry_object_key'];
					}
					$link = '<a href="view.php?'.esc("key=".urlencode($pkey1)).'" title="View this record">';
					$link .= $relationName;
					$link .= "</a>";
				}
				if( $link )
				{
					print('		<tr>'."\n");
					print('			<td class="attribute"></td><td>'.$link.'</td>'."\n");
					print('		</tr>'."\n");
				}
				print('		<tr>'."\n");
				print('			<td class="attribute">Key:</td>'."\n");
				print('			<td class="value">'.esc($pkey1).'</td>'."\n");
				print('		</tr>'."\n");
				print('		<tr>'."\n");
				print('			<td class="attribute">Relations:</td>'."\n");
				print('			<td>'."\n");
				print('<table class="subtable1">'."\n");
				print('		<tr>'."\n");
				print('			<td class="attribute">Type:</td>'."\n");
				print('			<td class="valueAttribute">'.esc($type).'</td>'."\n");
				print('		</tr>'."\n");
				print('</table>'."\n");
				print('         </td>'."\n");
				print('		</tr>'."\n");
				print('	</table>'."\n");

			} else {
				print (" <p>Primary related object is not PUBLISHED </p>");
			}
		}
		if($pkey2!='' && $pkey2!=$registryObjectKey)
		{
			if( $relatedObject=getRegistryObject($pkey2,true) )
			{

							print("\n<!-- RELATED OBJECT -->\n");
			print('<table class="subtable">'."\n");$type = $dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1'];
				if( trim($relatedObject[0]['status']) == PUBLISHED || userIsORCA_ADMIN() )
				{
					// The related object exists in the registry.
					$relationName = getNameHTML($pkey2);
					if( $relationName == '' )
					{
						$relationName = $relatedRegistryObjectKey;
					}
					$link = '<a href="view.php?'.esc("key=".urlencode($pkey2)).'" title="View this record">';
					$link .= $relationName;
					$link .= "</a>";
				}

				if( $link )
				{
					print('		<tr>'."\n");
					print('			<td class="attribute"></td><td>'.$link.'</td>'."\n");
					print('		</tr>'."\n");
				}
				print('		<tr>'."\n");
				print('			<td class="attribute">Key:</td>'."\n");
				print('			<td class="value">'.esc($pkey2).'</td>'."\n");
				print('		</tr>'."\n");
				print('		<tr>'."\n");
				print('			<td class="attribute">Relations:</td>'."\n");
				print('			<td>'."\n");
				print('<table class="subtable1">'."\n");
				print('		<tr>'."\n");
				print('			<td class="attribute">Type:</td>'."\n");
				print('			<td class="valueAttribute">'.esc($type).'</td>'."\n");
				print('		</tr>'."\n");
				print('</table>'."\n");
				print('         </td>'."\n");
				print('		</tr>'."\n");
				print('	</table>'."\n");
			}else {
				print (" <p>Primary related object is not PUBLISHED</p> ");
			}
		}
		if($relatedArray = getRelatedObjects($registryObjectKey))
		{
			asort($relatedArray);
			foreach( $relatedArray as $row )
			{
				if($row['related_registry_object_key']!=$pkey1 && $row['related_registry_object_key']!=$pkey2)
				{
					$existingRelatedArray[$row['related_registry_object_key']] = 1;
					drawRelatedObject($row['relation_id'], $row);
				}
			}
		}
		if($allow_reverse_internal_links == 't' && $reverseRelatedArrayInt = getInternalReverseRelatedObjects($registryObjectKey, $dataSourceKey))
		{
			asort($reverseRelatedArrayInt);

			foreach( $reverseRelatedArrayInt as $row )
			{
			//print ($row['registry_object_key'])	;
			if(!array_key_exists($row['registry_object_key'],$existingRelatedArray))
				{
					drawReverseRelatedObject($row['relation_id'], $row);
				}
			}
		}
		if($allow_reverse_external_links == 't' && $reverseRelatedArrayExt = getExternalReverseRelatedObjects($registryObjectKey, $dataSourceKey))
		{
			asort($reverseRelatedArrayExt);
			foreach( $reverseRelatedArrayExt as $row )
			{
				if(!array_key_exists($row['registry_object_key'],$existingRelatedArray))
				{
					drawReverseRelatedObject($row['relation_id'], $row);
				}
			}
		}

		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getRights($registryObjectKey) )
	{
		print("\n<!-- RIGHTS -->\n");
		print("		<tr>\n");
		print("			<td>Rights:</td>\n");
		print("			<td><table class='subtable'>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawRights($row['rights_id'], $row);
		}
		print("			</table></td>\n");
		print("		</tr>\n");
	}

	if( $array = getSubjects($registryObjectKey) )
	{
		print("\n<!-- SUBJECTS -->\n");
		print("		<tr>\n");
		print("			<td>Subjects:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawSubject($row['subject_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getDescriptions($registryObjectKey) )
	{
		print("\n<!-- DESCRIPTIONS -->\n");
		print("		<tr>\n");
		print("			<td>Descriptions:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawDescription($row['description_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getAccessPolicies($registryObjectKey) )
	{
		print("\n<!-- ACCESS POLICIES -->\n");
		print("		<tr>\n");
		print("			<td>Access Policies:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawAccessPolicy($row['access_policy_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getRelatedInfo($registryObjectKey) )
	{
		print("\n<!-- RELATED INFO -->\n");
		print("		<tr>\n");
		print("			<td>Related Info:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawRelatedInfo($row['related_info_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getCitationInformation($registryObjectKey) )
	{
		print("\n<!-- CITATION INFO -->\n");
		print("		<tr>\n");
		print("			<td>Citation Info:</td>\n");
		asort($array);
		print("			<td>\n");
		foreach( $array as $row )
		{
			print('			<table class="subtable">'."\n");
			drawCitationInfo($row['citation_info_id'], $row);
			print('			</table>'."\n");
		}
		print("			</td>\n");
		print("		</tr>\n");
	}


	if( userIsDataSourceRecordOwner($registryObjectDataSourceRecordOwner) || userIsORCA_ADMIN() )
	{
		drawRecordField("Created When:",  $createdWhen);
		drawRecordField("Created Who:",  $createdWho);
	}

	if( userIsORCA_ADMIN() )
	{
		//if( $registryObjectRecordOwner != SYSTEM )
		if ($dataSourceKey == "PUBLISH_MY_DATA")
		{
			$statusForm = "<form action=\"view.php?key=".esc(urlencode(getQueryValue('key')))."\" method=\"post\">";
			$statusForm .= '<input type="hidden" name="action" value="UpdateStatus" />';
			$statusForm .= '<select name="status" id="status" onchange="this.form.submit()">';

			$statuses = getStatuses();
			if( $statuses )
			{
				foreach( $statuses as $stat )
				{
					setChosenFromValue(trim($registryObject[0]['status']), trim($stat['status']), gITEM_SELECT);
					$statusForm .= '<option value="'.esc(trim($stat['status'])).'"'.$gChosen.'>'.esc(trim($stat['status'])).'</option>';
				}
			}
			$statusForm .= '</select>';
			$statusForm .= "</form>";

			//drawRecordField("Set Status:",  $statusForm);
		}

		$statusWhen = formatDateTime($registryObject[0]['status_modified_when']);
		$statusWho = $registryObject[0]['status_modified_who'];

		drawRecordField("Status Set:",  $statusWhen);
		drawRecordField("Status Set By:",  $statusWho);
	}

	print("	</tbody>\n");

	if( userIsDataSourceRecordOwner($registryObjectDataSourceRecordOwner) || userIsORCA_ADMIN() )
	{
		print("	<tbody>\n");
		print("	  <tr>\n");
		print("	    <td></td>\n");
		print("	    <td>\n");
		print("	      <form action=\"\" method=\"post\">\n");
		print("		  <input type=\"hidden\" name=\"class\" value=\"".$registryObjectClass."\" />");
		print("	      <div style=\"margin-bottom: 1em;\">\n");
		if( hasActivity(aORCA_REGISTRY_OBJECT_EDIT) ) // && recordOwner != SYSTEM
		{
			print("	      <input type=\"submit\" name=\"action\" value=\"Edit\" />&nbsp;\n");
		}
		if( hasActivity(aORCA_REGISTRY_OBJECT_DELETE) )
		{
			//print("	      <input type=\"submit\" name=\"action\" value=\"Delete\" />&nbsp;\n");
			echo '<input type="submit" name="action" value="Delete" onclick="return confirmSubmit(\'You are about to delete 1 record. Do you want to continue?\')"/>&nbsp';
		}

		print("	      </div>\n");
		print("	      </form>\n");
		print("	    </td>\n");
		print("	  </tr>\n");
		print("	</tbody>\n");
	}

	print("</table>\n");
}

// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../_includes/footer.php';
require '../_includes/finish.php';


function drawName($id, $row=null)
{
	print("\n<!-- NAME -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['date_from'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Date From:</td>'."\n");
		print('			<td class="valueAttribute">'.esc(getXMLDateTime($value)).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['date_to'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Date To:</td>'."\n");
		print('			<td class="valueAttribute">'.esc(getXMLDateTime($value)).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getNameParts($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Name Parts:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawNamePart($row['name_part_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawNamePart($id, $row=null)
{
	print("\n<!-- NAME PART -->\n");
	print('<table class="subtable1">'."\n");
	if( $value = $row['value'] )
	{
		$searchBaseURI = 'search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';
		$link = '<a class="search" title="Search with this name part" href="'.$searchBaseURI.esc(urlencode($value)).'">'.escWithBreaks($value).'</a>';
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.$link.'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawIdentifier($id, $row=null)
{
	print("\n<!-- IDENTIFIER -->\n");
	print('<input type="hidden" name="identifiers[]" value="'.esc($id).'" />'."\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['value'] )
	{
		$searchBaseURI = 'search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';
		$link = '<a class="search" title="Search with this identifier" href="'.$searchBaseURI.esc(urlencode($value)).'">'.escWithBreaks($value).'</a>';
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.$link.'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawLocation($id, $row=null)
{
	print("\n<!-- LOCATION -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['date_from'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Date From:</td>'."\n");
		print('			<td class="valueAttribute">'.esc(getXMLDateTime($value)).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['date_to'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Date To:</td>'."\n");
		print('			<td class="valueAttribute">'.esc(getXMLDateTime($value)).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getAddressLocations($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Addresses:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawAddress($row['address_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getSpatialLocations($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Spatial:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawSpatial($row['spatial_location_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawAddress($id, $row=null)
{
	print("\n<!-- ADDRESS -->\n");
	print('<table class="subtable1">'."\n");
	if( $array = getElectronicAddresses($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Electronic:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawElectronicAddress($row['electronic_address_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getPhysicalAddresses($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Physical:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawPhysicalAddress($row['physical_address_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawSpatial($id, $row=null)
{
	print("\n<!-- SPATIAL LOCATION -->\n");
	print('<table class="subtable1">'."\n");
	if( $value = $row['value'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawElectronicAddress($id, $row=null)
{
	print("\n<!-- ELECTRONIC ADDRESS -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['value'] )
	{
		$electronicAddress = '';
		if( $electronicAddressType = $row['type'] )
		{
			// Do some custom value display for some known types.
			switch( strtoupper($electronicAddressType) )
			{
				case 'URL':
					if( !getElectronicAddressArgs($id) ) // Only display a hyperlink if the address doesn't require args.
					{
						// Fix relative URLs.
						$electronicAddress = $value;
						if( !preg_match('/^[a-zA-Z]{0,5}:\/\/.*/', $electronicAddress) )
						{
							$electronicAddress = 'http://'.$electronicAddress;
						}
						$electronicAddress = '<a href="'.esc($electronicAddress).'" class="external">'.esc($value).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>';
					}
					else
					{
						$electronicAddress = esc($value);
					}
					break;

				case 'EMAIL':
					$electronicAddress = '<a href="mailto:'.esc($value).'" class="external">'.esc($value).'</a>';
		    		break;

		    	default:
		    		$electronicAddress = esc($value);
		    		break;
			}
		}
		else
		{
			$electronicAddress = esc($value);
		}
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.$electronicAddress.'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getElectronicAddressArgs($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Arguments:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawElectronicAddressArgument($row['electronic_address_arg_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawElectronicAddressArgument($id, $row=null)
{
	print("\n<!-- ELECTRONIC ADDRESS ARGUMENT -->\n");
	print('<table class="subtable1">'."\n");
	if( $value = $row['name'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $row['required'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Required:</td>'."\n");
		print('			<td class="valueAttribute">');
		$value = 'false';
		if( pgsqlBool($row['required']) )
		{
			$value = 'true';
		}
		print("$value</td>\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['use'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Use:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawPhysicalAddress($id, $row=null)
{
	print("\n<!-- PHYSICAL ADDRESS -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getAddressParts($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Address Parts:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawPhysicalAddressPart($row['address_part_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawPhysicalAddressPart($id, $row=null)
{
	print("\n<!-- PHYSICAL ADDRESS PART -->\n");
	print('<table class="subtable1">'."\n");
	if( $value = $row['value'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.escWithBreaks($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawRelatedObject($id, $row=null)
{
	print("\n<!-- RELATED OBJECT -->\n");
	print('<table class="subtable">'."\n");
	if( $relatedRegistryObjectKey = $row['related_registry_object_key'] )
	{
		$link = '';
		if( $relatedObject=getRegistryObject($relatedRegistryObjectKey) )
		{
			if( trim($relatedObject[0]['status']) == PUBLISHED || userIsORCA_ADMIN() )
			{
				// The related object exists in the registry.
				$relationName = getNameHTML($relatedRegistryObjectKey);
				if( $relationName == '' )
				{
					$relationName = $relatedRegistryObjectKey;
				}
				$link = '<a href="view.php?'.esc("key=".urlencode($relatedRegistryObjectKey)).'" title="View this record">';
				$link .= $relationName;
				$link .= "</a>";
			}
		}
		if( $link )
		{
			print('		<tr>'."\n");
			print('			<td class="attribute"></td><td>'.$link.'</td>'."\n");
			print('		</tr>'."\n");
		}
		print('		<tr>'."\n");
		print('			<td class="attribute">Key:</td>'."\n");
		print('			<td class="value">'.esc($relatedRegistryObjectKey).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $array = getRelationDescriptions($id) )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Relations:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawRelation($row['relation_description_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}


function drawReverseRelatedObject($id, $row=null)
{

	print("\n<!-- RELATED OBJECT -->\n");
	print('<table class="subtable" style="border:2px">'."\n");
	if( $relatedRegistryObjectKey = $row['registry_object_key'] )
	{

			$link = '';
			if( $relatedObject=getRegistryObject($relatedRegistryObjectKey) )
			{
				if( trim($relatedObject[0]['status']) == PUBLISHED || userIsORCA_ADMIN() )
				{
					// The related object exists in the registry.
					$relationName = getNameHTML($relatedRegistryObjectKey);
					if( $relationName == '' )
					{
						$relationName = $relatedRegistryObjectKey;
					}
					$link = '<a href="view.php?'.esc("key=".urlencode($relatedRegistryObjectKey)).'" title="View this record">';
					$link .= $relationName;
					$link .= "</a>";
				}
			}

			if( $link )
			{
				print('		<tr>'."\n");
				print('			<td class="attribute"></td><td>'.$link.'</td>'."\n");
				print('		</tr>'."\n");
			}
			print('		<tr>'."\n");
			print('			<td class="attribute">Key:</td>'."\n");
			print('			<td class="value">'.esc($relatedRegistryObjectKey).'</td>'."\n");
			print('		</tr>'."\n");
		}
		if( $array = getRelationDescriptions($id) )
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Relations:</td>'."\n");
			print('			<td>'."\n");
			asort($array);
			foreach( $array as $row )
			{
				drawReverseRelation($row['relation_description_id'], $row);
			}
			print('         </td>'."\n");
			print('		</tr>'."\n");
		}
	print('	</table>'."\n");
}

function drawRelation($id, $row=null)
{
	print("\n<!-- RELATION -->\n");
	print('<table class="subtable1">'."\n");
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['description'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Description:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Description Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['url'] )
	{
		// Fix relative URLs.
		$href = $value;
		if( !preg_match('/^[a-zA-Z]{0,5}:\/\/.*/', $href) )
		{
			$href = 'http://'.$href;
		}
		$url = '<a href="'.esc($href).'" class="external" title="'.esc($href).'">'.esc($href).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>';
		print('		<tr>'."\n");
		print('			<td class="attribute">URL:</td>'."\n");
		print('			<td class="value">'.$url.'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}


function drawReverseRelation($id, $row=null)
{
	print("\n<!-- RELATION -->\n");
	print('<table class="subtable1">'."\n");
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).' (Automatically generated reverse link)</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['description'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Description:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Description Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['url'] )
	{
		// Fix relative URLs.
		$href = $value;
		if( !preg_match('/^[a-zA-Z]{0,5}:\/\/.*/', $href) )
		{
			$href = 'http://'.$href;
		}
		$url = '<a href="'.esc($href).'" class="external" title="'.esc($href).'">'.esc($href).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>';
		print('		<tr>'."\n");
		print('			<td class="attribute">URL:</td>'."\n");
		print('			<td class="value">'.$url.'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}




function drawSubject($id, $row=null)
{
	print("\n<!-- SUBJECT -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['value'] )
	{
		$searchBaseURI = 'search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';
		$link = '<a class="search" title="Search with this subject" href="'.$searchBaseURI.esc(urlencode($value)).'">'.escWithBreaks($value).'</a>';
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.$link.'</td>'."\n");
		print('		</tr>'."\n");
	}
	if($value = $row['termIdentifier'])
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">termIdentifier:</td>'."\n");
		print('			<td class="value">'.$value.'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawDescription($id, $row=null)
{
	print("\n<!-- DESCRIPTION -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['value'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.escWithBreaks($value).'</td>'."\n");
		print('		</tr>'."\n");
	}

	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}



function drawAccessPolicy($id, $row=null)
{
	print("\n<!-- ACCESS POLICY -->\n");
	print('<table class="subtable">'."\n");
	if( $value = $row['value'] )
	{
		$link = $value;
		if( !preg_match('/^[a-zA-Z]{0,5}:\/\/.*/', $link) )
		{
			$link = 'http://'.$link;
		}
		$link = '<a href="'.esc($link).'" class="external" title="'.esc($value).'">'.escWithBreaks($value).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>'."\n";
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.$link.'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawRelatedInfo($id, $row=null)
{
	print("\n<!-- RELATED INFO -->\n");
	print('<table class="subtable">'."\n");

	if($value = esc($row['value']))
	{
		print('			<tr>'."\n");
		print('				<td class="attribute">Identifier:</td>'."\n");
		print('				<td>'."\n");
		print('					<table class="subtable">'."\n");
		print('					<tr>'."\n");
		print('						<td class="attribute">Value:</td>'."\n");
		print('						<td class="value">'.$value.'</td>'."\n");
		print('					</tr>'."\n");
		print('					<tr>'."\n");
		print('						<td class="attribute">Type:</td>'."\n");
		print('						<td class="valueAttribute">uri</td>'."\n");
		print('					</tr>'."\n");
		print('	        		</table>'."\n");
		print('				</td>'."\n");
		print('			</tr>'."\n");
	}
	else
	{
		if( $value = $row['info_type'] )
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Type:</td>'."\n");
			print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
			print('		</tr>'."\n");
		}
		print('			<tr>'."\n");
		print('				<td class="attribute">Identifier:</td>'."\n");
		print('				<td>'."\n");
		print('					<table class="subtable">'."\n");
		print('					<tr>'."\n");
		print('						<td class="attribute">Value:</td>'."\n");
		print('						<td class="value">'.esc($row['identifier']).'</td>'."\n");
		print('					</tr>'."\n");
		print('					<tr>'."\n");
		print('						<td class="attribute">Type:</td>'."\n");
		print('						<td class="valueAttribute">'.esc($row['identifier_type']).'</td>'."\n");
		print('					</tr>'."\n");
		print('	        		</table>'."\n");
		print('				</td>'."\n");
		print('			</tr>'."\n");
		if( $value = $row['title'] )
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Title:</td>'."\n");
			print('			<td class="value">'.esc($value).'</td>'."\n");
			print('		</tr>'."\n");
		}
		if( $value = $row['notes'] )
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Notes:</td>'."\n");
			print('			<td class="value">'.esc($value).'</td>'."\n");
			print('		</tr>'."\n");
		}
	}
	print('	</table>'."\n");
}

function drawCoverage($id, $row=null)
{
	print("\n<!-- COVERAGE -->\n");
	print('<table class="subtable">'."\n");
	if($array = getSpatialCoverage($id))
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Spatial:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawSpatialCoverage($row['coverage_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	if($array = getTemporalCoverage($id))
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Temporal:</td>'."\n");
		print('			<td>'."\n");
		asort($array);
		foreach( $array as $row )
		{
			drawTemporalCoverage($row['temporal_coverage_id'], $row);
		}
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}
function drawSpatialCoverage($id, $row=null)
{
print('<table class="subtable1">'."\n");
	if( $value = $row['value'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['lang'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Lang:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('	</table>'."\n");
}

function drawTemporalCoverage($id, $row=null)
{
	print('<table class="subtable1">'."\n");
	if($array = getTemporalCoverageText($id))
	{
		asort($array);
		foreach( $array as $row )
		{
			drawTemporalCoverageText($row['coverage_text_id'], $row);
		}
	}
	if($array = getTemporalCoverageDate($id))
	{
		asort($array);
		foreach( $array as $row )
		{
			drawTemporalCoverageDate($row['coverage_date_id'], $row);
		}
	}
	print('	</table>'."\n");
}

function drawTemporalCoverageDate($id, $row=null)
{
	print('		<tr>'."\n");
	print('			<td class="attribute">Date:</td>'."\n");
	print('			<td>'."\n");
	print('		<table class="subtable1">'."\n");

	if( $value = $row['value'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['type'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Type:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['date_format'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">DateFormat:</td>'."\n");
		print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('		</table>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
}


function drawTemporalCoverageText($id, $row=null)
{
	print('		<tr>'."\n");
	print('			<td class="attribute">Text:</td>'."\n");
	print('			<td>'."\n");
	print('			<table class="subtable1">'."\n");
	if( $value = $row['value'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
	}
	print('			</table>'."\n");
	print('         </td>'."\n");
	print('		</tr>'."\n");
}
function drawExistenceDates($id, $row=null)
{

	if( $value = $row['start_date'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">startDate:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
		print('		<tr>'."\n");
		print('			<td class="attribute">format:</td>'."\n");
		print('			<td class="value">'.esc($row['start_date_format']).'</td>'."\n");
		print('		</tr>'."\n");
		print('			</table>'."\n");
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	if( $value = $row['end_date'] )
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">endDate:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($value).'</td>'."\n");
		print('		</tr>'."\n");
		print('		<tr>'."\n");
		print('			<td class="attribute">format:</td>'."\n");
		print('			<td class="value">'.esc($row['end_date_format']).'</td>'."\n");
		print('		</tr>'."\n");
		print('			</table>'."\n");
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
}
function drawRights($id, $row=null)
{

	if($row['rights_statement']!=''||$row['rights_statement_uri']!='')
	{

		print('		<tr>'."\n");
		print('			<td class="attribute">rightsStatement:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		if($row['rights_statement']!='')
		{
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($row['rights_statement']).'</td>'."\n");
		print('		</tr>'."\n");
		}
		if($row['rights_statement_uri']!='')
		{
		print('		<tr>'."\n");
		print('			<td class="attribute">rightsUri:</td>'."\n");
		print('			<td class="value">'.esc($row['rights_statement_uri']).'</td>'."\n");
		print('		</tr>'."\n");
		}
		print('			</table>'."\n");
		print('         </td>'."\n");
		print('		</tr>'."\n");

	}
	if($row['licence']!=''||$row['licence_uri']!=''||$row['licence_type']!='')
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">licence:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		if($row['licence']!='')
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Value:</td>'."\n");
			print('			<td class="value">'.esc($row['licence']).'</td>'."\n");
			print('		</tr>'."\n");
		}
		if($row['licence_uri']!='')
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">rightsUri:</td>'."\n");
			print('			<td class="value">'.esc($row['licence_uri']).'</td>'."\n");
			print('		</tr>'."\n");
		}
		if($row['licence_type']!='')
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">type:</td>'."\n");
			print('			<td class="value">'.esc($row['licence_type']).'</td>'."\n");
			print('		</tr>'."\n");
		}
		print('			</table>'."\n");
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
	if($row['access_rights']!=''||$row['access_rights_uri']!=''||$row['access_rights_type']!='')
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">accessRights:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		if($row['access_rights']!='')
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Value:</td>'."\n");
			print('			<td>'.esc($row['access_rights']).'</td>'."\n");
			print('		</tr>'."\n");
		}
		if($row['access_rights_uri']!='')
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">rightsUri:</td>'."\n");
			print('			<td class="value">'.esc($row['access_rights_uri']).'</td>'."\n");
			print('		</tr>'."\n");
		}
		if($row['access_rights_type']!='')
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">type:</td>'."\n");
			print('			<td class="value">'.esc($row['access_rights_type']).'</td>'."\n");
			print('		</tr>'."\n");
		}
		print('			</table>'."\n");
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
}
function drawCitationInfo($id, $row=null)
{

	if($row['full_citation'] != '' || $row['style'] != '')
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Full Citation:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		print('		<tr>'."\n");
		print('			<td class="attribute">Value:</td>'."\n");
		print('			<td class="value">'.esc($row['full_citation']).'</td>'."\n");
		print('		</tr>'."\n");
		if( $value = $row['style'] )
		{
			print('		<tr>'."\n");
			print('			<td class="attribute">Style:</td>'."\n");
			print('			<td class="valueAttribute">'.esc($value).'</td>'."\n");
			print('		</tr>'."\n");
		}
		print('		</table>'."\n");
		print('     </td>'."\n");
		print('		</tr>'."\n");

	}
	else if($row['metadata_identifier'] != '')
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">Citation Metadata:</td>'."\n");
		print('			<td>'."\n");
		print('			<table class="subtable1">'."\n");
		print('				<tr>'."\n");
		print('				<td class="attribute">Identifier:</td>'."\n");
		print('				<td>'."\n");
		print('					<table class="subtable">'."\n");
		print('						<tr>'."\n");
		print('							<td class="attribute">Value:</td>'."\n");
		print('							<td class="value">'.esc($row['metadata_identifier']).'</td>'."\n");
		print('						</tr>'."\n");
		print('						<tr>'."\n");
		print('							<td class="attribute">Type:</td>'."\n");
		print('							<td class="valueAttribute">'.esc($row['metadata_type']).'</td>'."\n");
		print('						</tr>'."\n");
		print('					</table>'."\n");
		print('     		</td>'."\n");
		print('				</tr>'."\n");
		print('		<tr>'."\n");
		print('			<td class="attribute">Contributor(s):</td>'."\n");
		print('			<td>'."\n");
		print('				<table class="subtable">'."\n");
		if($array2 = getCitationContributors($row['citation_info_id']))
		{
			foreach( $array2 as $row2 )
			{
				if( $seq = $row2['seq'] )
				{
					print('						<tr>'."\n");
					print('							<td class="attribute">Sequence No.:</td>'."\n");
					print('							<td class="valueAttribute">'.esc($seq).'</td>'."\n");
					print('						</tr>'."\n");
				}
				drawContributorNameParts($row2['citation_contributor_id'], $row2);
			}
		}
		print('				</table>'."\n");
		print('     		</td>'."\n");
		print('				<tr>'."\n");
		print('					<td class="attribute">Title:</td>'."\n");
		print('					<td class="value">'.esc($row['metadata_title']).'</td>'."\n");
		print('				</tr>'."\n");
		print('				<tr>'."\n");
		print('					<td class="attribute">Edition:</td>'."\n");
		print('					<td class="value">'.esc($row['metadata_edition']).'</td>'."\n");
		print('				</tr>'."\n");
		print('				<tr>'."\n");
		print('					<td class="attribute">URL:</td>'."\n");
		print('					<td class="value">'.esc($row['metadata_url']).'</td>'."\n");
		print('				</tr>'."\n");
		print('				<tr>'."\n");
		print('					<td class="attribute">Place Published:</td>'."\n");
		print('					<td class="value">'.esc($row['metadata_place_published']).'</td>'."\n");
		print('				</tr>'."\n");
		if($row['metadata_publisher']!='')
		{
			print('				<tr>'."\n");
			print('					<td class="attribute">Publisher:</td>'."\n");
			print('					<td class="value">'.esc($row['metadata_publisher']).'</td>'."\n");
			print('				</tr>'."\n");
		}
		print('				<tr>'."\n");
		print('					<td class="attribute">Context:</td>'."\n");
		print('					<td class="value">'.esc($row['metadata_context']).'</td>'."\n");
		print('				</tr>'."\n");

		if($array = getCitationDates($row['citation_info_id']))
		{
			print('				<tr>'."\n");
			print('				<td class="attribute">Date(s):</td>'."\n");
			print('				<td>'."\n");
			print('				<table class="subtable">'."\n");
			foreach( $array as $row )
			{
				print('				<tr>'."\n");
				print('					<td class="attribute">Value:</td>'."\n");
				print('					<td class="value">'.esc($row['date']).'</td>'."\n");
				print('				</tr>'."\n");
				print('				<tr>'."\n");
				print('					<td class="attribute">Type:</td>'."\n");
				print('					<td class="valueAttribute">'.esc($row['type']).'</td>'."\n");
				print('				</tr>'."\n");
			}
			print('				</table>'."\n");
			print('     		</td>'."\n");
			print('				</tr>'."\n");
		}

		print('			</table>'."\n");
		print('         </td>'."\n");
		print('		</tr>'."\n");
	}
}

function drawContributorNameParts($id, $row=null)
{
	if($array = getCitationContributorNameParts($id))
	{
		print('		<tr>'."\n");
		print('			<td class="attribute">NamePart(s):</td>'."\n");
		print('				<td>'."\n");
		print('					<table class="subtable1">'."\n");
		foreach( $array as $row )
				{
					print('				<tr>'."\n");
					print('					<td class="attribute">Value:</td>'."\n");
					print('					<td class="value">'.esc($row['value']).'</td>'."\n");
					print('				</tr>'."\n");
					if( $type = $row['type'] )
					{
						print('				<tr>'."\n");
						print('					<td class="attribute">Type:</td>'."\n");
						print('					<td class="valueAttribute">'.esc($type).'</td>'."\n");
						print('				</tr>'."\n");
				}
				}
		print('					</table>'."\n");
		print('     	</td>'."\n");
		print('		</tr>'."\n");
	}
}


?>
