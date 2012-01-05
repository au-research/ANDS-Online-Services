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
/* 
 * Note: This page is not intended to be used outside of the ANDS sandbox and
 *       should not be present in the distribution of ORCA. It should not be 
 *       used in production outside of the ANDS sandbox.
 *       
 *       To add this functionality to ORCA, add the following to your COSI
 *       application_config:
 *       
 *      // =============================================================================
 *		// Export from a Data Source 
 *		$activity = new activity('aORCA_DATA_SOURCE_EXPORT', 'Export from Data Source', 'orca/admin/data_source_export.php');
 *		$activity->menu_id = 'mORCA_ADMINISTRATION';
 *		addActivity($activity);
 *
 *		You will also need to create the aORCA_DATA_SOURCE_EXPORT activity in the database.
 *
 */

// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';

// Page processing
// -----------------------------------------------------------------------------

$form_action = getPostedValue('action');
$errors = array();
$BASE_REGISTRYGET_URL = eAPP_ROOT . "orca/services/getRegistryObjects.php";

$display_gui = true;// because of embedded html, exit() doesn't work
					// as desired, so using this status variable instead

// If action == fetch, use headers to force file to be downloaded
if (getQueryValue('action') == "fetch" && getQueryValue('url') != "") { 

	$xml_contents = file_get_contents(str_replace(" ","%20",urldecode(getQueryValue('url'))));
	
	// Set headers
    header("Content-Description: File Transfer");
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=data_source_export.xml");
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    //header('Content-Length: ' . filesize($xml_contents));
	echo $xml_contents;
    
    // Fetch from URL
    $display_gui = false;
}

// Check that the export request is legitimate and 
// if so, show links so the user can view/download RIF-CS XML
if($form_action == "Export Data Source")
{

	$data_source_key = getPostedValue('data_source_key');
	$object_group = getPostedValue('object_group');
	$object_types = getPostedValue('objectTypes');
	$records_before = getPostedValue('recordsBeforeDate');
	$records_after = getPostedValue('recordsAfterDate');
	$search_phrase = getPostedValue('searchPhrase');
	
	// Setup the URL to fetch from
	$url = $BASE_REGISTRYGET_URL . "?";
	
	// Check valid data source key/object group
	if ($data_source_key == "" && $object_group == "") {
		$errors[] = "Must specify either a Data Source Key or a specific Object Group";
	} else {
		if ($data_source_key != "") {
			$url .= "&source_key=" . $data_source_key;
		}
		if ($object_group != "") {
			$url .= "&object_group=" . $object_group;
		}
	}

	// Return requested object types only
	if ($object_types == "") { 
		$errors[] = "At least one Registry Object Type must be specified";
	} else {
		foreach ($object_types AS $object_type) {
			$url .= "&" . $object_type;
		}
	}
	
	// Check record dates
	if ($records_before != "") {
		if (!strtotime($records_before)) {
			$errors[] = "Invalid date format specified for \"Only records created before\" field.";
		} else {
			$url .= "&created_before_equals=" . $records_before;
		}
	}
	
	if ($records_after != "") {
		if (!strtotime($records_after)) {
			$errors[] = "Invalid date format specified for \"Only records created after\" field.";
		} else {
			$url .= "&created_after_equals=" . $records_after;
		}
	}
	
	// Search phrase
	if ($search_phrase != "") {
		if (strlen($search_phrase) > 255) {
			$errors[] = "Search phrase is too long";
		} else {
			$url .= "&search=" . $search_phrase;
		}
	}

	if (sizeof($errors) == 0) {
		
		// Display landing page
		require '../../_includes/header.php';
		
		echo "<h1>Data Source Export Tool</h1>";
		
		echo "<p>";
		echo "Export from this Data Source is available:<br/>";
		echo "<a href=\"".$url."\">View as XML</a> or <a href=\"". eAPP_ROOT . "orca/admin/data_source_export.php?
					action=fetch&url=".urlencode($url)."\">Download as XML</a>";
		echo "</p>";

		echo "<p>";
		echo "<b>Note:</b> Data Sources with a large number of Registry Objects may take a while to download.";
		echo "</p>";
		
		require '../../_includes/footer.php';
		require '../../_includes/finish.php';
	
		$display_gui = false;
		
	}
	
	
} 


// Else display the GUI form
if ($display_gui) {

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

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================

if( !$searchResults )
{
	print("<p>No Data sources are available.</p>\n");
}
else
{
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<h1>Data Source Export Tool</h1>
<p>This tool allows you to export RIF-CS from a data source to your local machine.</p> 
<form id="data_source_export_form" name="data_source_export_form" action="data_source_export.php" method="post">

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
			<td>* Data Source:</td> 
			<td>
				<select name="data_source_key" id="data_source_key" style="width:300px;">
				<option value=""></option>
				<?php
					
					// Present the results.
					for( $i=0; $i < count($searchResults); $i++ )
					{
						$dataSourceKey = $searchResults[$i]['data_source_key'];
						$dataSourceTitle = $searchResults[$i]['title'];
						$numRegistryObjects = getRegistryObjectCount($dataSourceKey);		
						print("<option value=\"".urlencode($dataSourceKey)."\">".esc($dataSourceTitle)." (".esc($numRegistryObjects).")</option>\n");
					}
				
				}// end if search results
				?>
				</select>
			</td> 
		</tr> 
		
		<tr> 
			<td>* Data Group:</td> 
			<td>
				<?php
					$objectGroups = getObjectGroups();
					print('<select name="object_group" style="margin: 2px; margin-left: 0px; width:300px;">'."\n");
					print('  <option value="">All Groups from Data Source</option>'."\n");
					if( $objectGroups )
					{
						foreach( $objectGroups as $group )
						{
							$selected = "";
							//if( $group['object_group'] == $objectGroup ){ $selected = ' selected="selected"'; }
							print('  <option value="'.esc($group['object_group']).'"'.$selected.'>'.esc($group['object_group']).'</option>'."\n");
						}
					}
					print('</select><br />'."\n");
				?>
			</td> 
		</tr> 		
		<tr> 
			<td>* Registry Object Types:</td> 
			<td> 
								<input type="checkbox" name="objectTypes[]" value="activities=activity" checked="checked" />Activities<br/>
								<input type="checkbox" name="objectTypes[]" value="collections=collection" checked="checked" />Collections<br/>
								<input type="checkbox" name="objectTypes[]" value="parties=party" checked="checked" />Parties<br/>
								<input type="checkbox" name="objectTypes[]" value="services=service" checked="checked" />Services<br/>
			</td> 
		</tr> 
		<tr> 
			<td>Only records created before: </td> 
			<td><input type="text" size="20" maxlength="20" name="recordsBeforeDate" id="recordsBeforeDate" value="" /> 
			<script type="text/javascript">dctGetDateTimeControl('recordsBeforeDate', 'YYYY-MM-DDThh:mm:00Z')</script>&nbsp;<span class="inputFormat">YYYY-MM-DDThh:mm:ssZ</span></td> 
		</tr> 
		<tr> 
			<td>Only records created after: </td> 
			<td><input type="text" size="20" maxlength="20" name="recordsAfterDate" id="recordsAfterDate" value="" /> 
<script type="text/javascript">dctGetDateTimeControl('recordsAfterDate', 'YYYY-MM-DDThh:mm:00Z');</script>&nbsp;<span class="inputFormat">YYYY-MM-DDThh:mm:ssZ</span></td> 
		</tr> 
		<tr> 
			<td>Search Phrase:</td> 
			<td><input type="text" size="30" maxlength="255" name="searchPhrase" value="" /></td> 
		</tr> 
	</tbody> 
	<tbody> 
		<tr> 
			<td></td> 
			<td><input type="submit" name="action" value="Export Data Source" /></td> 
		</tr> 
		<tr>
			<td></td>
			<td class="formNotes">Fields marked * are mandatory. <br/>Either a Data Source or Object Group MUST be specified.<br /> 
			</td> 
		</tr>
	</tbody> 
</table> 

<?php
print("</form>\n");

	



// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
} // end if $display_gui
?>