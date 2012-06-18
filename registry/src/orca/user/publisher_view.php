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

// Get the owner handle. (This will create a new owner handle if one doesn't already exist.)
$partyObjectKey = pidsGetOwnerHandle();

// Check to see if we have a party object already.
$registryObject = getRegistryObject($partyObjectKey);


// Get any action that may have been posted.
if( strtoupper(getPostedValue('action')) == 'EDIT' )
{
	responseRedirect('publisher_edit.php');
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
if( !$registryObject )
{
?>
<div style="margin-bottom: 2em; width: 800px;">
<h2>Welcome to ANDS <i>Publish My Data</i></h2>
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;">
	Help for this application is available from the <b><a href="<?php print eAPP_ROOT.'help.php?id='.$gThisActivityID.'&amp;page='.urlencode($_SERVER['REQUEST_URI']) ?>" title="Help for this page">Help</a></b> link at the top right hand corner of the page.<br />
</p>
<p>You do not have any Publisher Details recorded in the registry as you have not added any collections to the registry.
Once you have added a collection to the registry you will be able to view and edit your Publisher Details.
To add a collection and submit it for approval use the <i><a href="collection_add.php">Publish a Collection</a></i> link available from the menu at left.</p>
</div>
<?php
}
else
{
	$registryObjectKey = $registryObject[0]['registry_object_key'];
	$objectGroup = $registryObject[0]['object_group'];
	$registryObjectClass = $registryObject[0]['registry_object_class'];
	$registryObjectType = $registryObject[0]['type'];

	$originatingSourceHTML = esc($registryObject[0]['originating_source']);
	if( $registryObject[0]['originating_source_type'] )
	{
		$originatingSourceHTML = '<span class="attribute" title="originating source type">'.esc($registryObject[0]['originating_source_type']).':</span> '.$originatingSourceHTML;
	}

	$objectGroup = $registryObject[0]['object_group'];

	$createdWhen = formatDateTime($registryObject[0]['created_when']);
	$createdWho = $registryObject[0]['created_who'];

	print('<table class="recordTable" summary="Data Source">'."\n");
	print("	<thead>\n");

	$rdaLink = '';

	// The link to the RDA
	$rdaLinkPrefix = 'View';
	$rdaLink = '<br /><a style="font-size:0.8em; font-weight: normal;" rel="preview" href="http://'.$host.'/'.$rda_root . '/view.php?key='.esc(urlencode($registryObjectKey)).'">'.$rdaLinkPrefix.' this record in Research Data Australia (new window)</a>'."\n";

	drawRecordField("<a href=\"../services/getRegistryObject.php?key=".esc(urlencode($registryObjectKey))."\"><img title=\"Get RIF-CS XML for this record\" src=\"".gORCA_IMAGE_ROOT."rifcs.gif\" alt=\"\" /></a>", esc($registryObjectClass).$rdaLink);

	print("	</thead>\n");
	print('	<tbody class="recordFields">'."\n");

	drawRecordField("Type:",  esc($registryObjectType));
	drawRecordField("Key:",  esc($registryObjectKey));
	drawRecordField("Originating Source:", $originatingSourceHTML);
	drawRecordField("Group:", esc($objectGroup));

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

	if( $array = getLocations($registryObjectKey) )
	{
		print("\n<!-- LOCATIONS -->\n");
		print("		<tr>\n");
		print("			<td>Locations:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawLocation($row['location_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	if( $array = getRelatedObjects($registryObjectKey) )
	{
		print("\n<!-- RELATED OBJECTS -->\n");
		print("		<tr>\n");
		print("			<td>Related Objects:</td>\n");
		print("			<td>\n");
		asort($array);
		foreach( $array as $row )
		{
			drawRelatedObject($row['relation_id'], $row);
		}
		print("			</td>\n");
		print("		</tr>\n");
	}

	drawRecordField("Created When:",  $createdWhen);
	drawRecordField("Created Who:",  $createdWho);

	print("	</tbody>\n");

	print("	<tbody>\n");
	print("	  <tr>\n");
	print("	    <td></td>\n");
	print("	    <td>\n");
	print("	      <form action=\"publisher_view.php\" method=\"post\">\n");
	print("	      <div style=\"margin-bottom: 1em;\">\n");
	print("	      <input type=\"submit\" name=\"action\" value=\"Edit\" />&nbsp;\n");
	print("	      </div>\n");
	print("	      </form>\n");
	print("	    </td>\n");
	print("	  </tr>\n");
	print("	</tbody>\n");

	print("</table>\n");
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';



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
		$searchBaseURI = '../search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';
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
						$electronicAddress = '<a href="'.esc($electronicAddress).'" class="external" title="'.esc($electronicAddress).'">'.esc($value).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>';
					}
					else
					{
						$electronicAddress = esc($value);
					}
					break;

				case 'EMAIL':
					$electronicAddress = '<a href="mailto:'.esc($value).'" class="external" title="mailto:'.esc($value).'">'.esc($value).'</a>';
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
			if( trim($relatedObject[0]['status']) == PUBLISHED )
			{
				// The related object exists in the registry.
				$relationName = getNameHTML($relatedRegistryObjectKey);
				if( $relationName == '' )
				{
					$relationName = $relatedRegistryObjectKey;
				}
				$link = '<a href="collection_view.php?'.esc("key=".urlencode($relatedRegistryObjectKey)).'" title="'.esc($relatedRegistryObjectKey).'">';
				$link .= $relationName;
				$link .= "</a>";
			}
		}
		if( $link )
		{
			print('		<tr>'."\n");
			print('			<td></td><td>'.$link.'</td>'."\n");
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

?>
