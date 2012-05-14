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

********************************************************************************
$Date: 2011-11-25 14:26:15 +1100 (Fri, 25 Nov 2011) $
$Revision: 1633 $
*******************************************************************************/

function getRegistryObjectXML($registryObjectKey, $forSOLR = false, $includeRelated = false)
{


	if (!eCACHE_ENABLED)
	{
		return getRegistryObjectXMLFromDB($registryObjectKey, $forSOLR, $includeRelated);
	}



	$data_source_key = getRegistryObjectDataSourceKey($registryObjectKey);
	return getRegistryObjectXMLFromDB($registryObjectKey, $forSOLR, $includeRelated);
	// Registry key probably doesn't exist?
	if (!$data_source_key) return FALSE;
	
	// Get the result from cache as extended rifcs (hopefully!)
	$result = getCacheItems($data_source_key, $registryObjectKey, eCACHE_CURRENT_NAME, $forSOLR);
	
	// No luck? Well, lets try and index it then...
	if ($result === FALSE)
	{
		writeCache($data_source_key, $registryObjectKey, generateExtendedRIFCS($registryObjectKey));
	}
	$result = getCacheItems($data_source_key, $registryObjectKey, eCACHE_CURRENT_NAME, $forSOLR);

	// Still no luck? Fall back to getRegistryObjectXMLforSOLR and build from DB XXX: Temporary
	if ($result !== FALSE)
	{
		return $result;
	}
	else
	{
		// go ahead and rebuild by hand (fallback)
		return getRegistryObjectXMLFromDB($registryObjectKey, $forSOLR, $includeRelated);
	}
}

function getRegistryObjectXMLFromDB($registryObjectKey, $forSOLR = false, $includeRelated = false)
{
	// go ahead and rebuild by hand (fallback)
	$xml = '';	
	$registryObject = getRegistryObject($registryObjectKey);
	
	if ($forSOLR)
	{
		$dataSourceKey = $registryObject[0]["data_source_key"];
		$registryObjectStatus = $registryObject[0]["status"];
		$dataSource = getDataSources($dataSourceKey, null);
		$allow_reverse_internal_links = $dataSource[0]['allow_reverse_internal_links'];
		$allow_reverse_external_links = $dataSource[0]['allow_reverse_external_links'];
	}
	
	if( $registryObject )
	{
		// Registry Object
		// =====================================================================
		$group= esc($registryObject[0]['object_group']);
		$xml .= "  <registryObject group=\"$group\">\n";
		
		// Registry Object Key
		// =====================================================================
		$xml .= "    <key>".esc($registryObjectKey)."</key>\n";
		
		
		if ($forSOLR)
		{
			$xml .= "    <extRif:extendedMetadata>\n";

			// url_slug
			// -------------------------------------------------------------
			$xml .= '      <extRif:urlSlug>'.esc(trim(getRegistryObjectURLSlug($registryObjectKey))).'</extRif:urlSlug>'."\n";

			$hash = getRegistryObjectHash($registryObjectKey);
			if ($hash)
			{
				$xml .= "      <extRif:keyHash>".esc($hash)."</extRif:keyHash>\n";
			}

			$hash = getDataSourceHash($dataSourceKey);
			if ($hash)
			{
				$xml .= "      <extRif:dataSourceKeyHash>".esc($hash)."</extRif:dataSourceKeyHash>\n";
			}
			$xml .= "      <extRif:status>".esc($registryObjectStatus)."</extRif:status>\n";
			$xml .= "      <extRif:dataSourceKey>".esc($dataSourceKey)."</extRif:dataSourceKey>\n";		
			$reverseLinks = 'NONE';
			$allow_reverse_internal_links = $dataSource[0]['allow_reverse_internal_links'];
			$allow_reverse_external_links = $dataSource[0]['allow_reverse_external_links'];
			if($allow_reverse_internal_links == 't' && $allow_reverse_external_links == 't')
			{
				$reverseLinks = 'BOTH';
			}
			else if($allow_reverse_internal_links == 't')
			{
				$reverseLinks = 'INT';
				
			}
			else if($allow_reverse_external_links == 't')
			{
				$reverseLinks = 'EXT';
			}
			$xml .= "      <extRif:reverseLinks>".$reverseLinks."</extRif:reverseLinks>\n";
			
			
			// Get registry date modified			
			if (!($registryDateModified =  getRegistryObjectStatusModified($registryObjectKey)))
			{
					$registryDateModified = time(); // default to now
			}
			$xml .= "      <extRif:registryDateModified>".$registryDateModified."</extRif:registryDateModified>\n";



			// displayTitle
			// -------------------------------------------------------------
			$xml .= '      <extRif:displayTitle>'.esc(trim($registryObject[0]['display_title'])).'</extRif:displayTitle>'."\n";
			$logo = '';
			$logoStr = getDescriptionLogo($registryObjectKey);
			if ($logoStr !== false)
			{
				$xml .= '      <extRif:displayLogo>'.$logoStr.'</extRif:displayLogo>'."\n";
			}	
			
			// listTitle
			// -------------------------------------------------------------
			$xml .= '      <extRif:listTitle>'.esc(trim($registryObject[0]['list_title'])).'</extRif:listTitle>'."\n";

			// searchBaseScore (base "boost" used to adjust search rankings)
			$baseScore = eBOOST_DEFAULT_BASE;
			
			// number of related objects:
			$number_of_related_objects = getRelatedObjectCount($registryObjectKey);
			$baseScore += eBOOST_RELATED_OBJECT_ADJUSTMENT * (int) $number_of_related_objects;
			
			// number of INCOMING related objects (objects that relate to us):
			$number_of_related_objects = getIncomingRelatedObjectCount($registryObjectKey);
			$baseScore += eBOOST_INCOMING_RELATED_OBJECT_ADJUSTMENT * (int) $number_of_related_objects;
			

			$xml .= "      <extRif:searchBaseScore>$baseScore</extRif:searchBaseScore>\n";			
			$xml .= '      <extRif:flag>'.($registryObject[0]['flag'] == 'f' ? '0' : '1').'</extRif:flag>'."\n";
			$xml .= '      <extRif:warning_count>'.esc(trim($registryObject[0]['warning_count'])).'</extRif:warning_count>'."\n";
			$xml .= '      <extRif:error_count>'.esc(trim($registryObject[0]['error_count'])).'</extRif:error_count>'."\n";
			$xml .= '      <extRif:url_slug>'.esc(trim($registryObject[0]['url_slug'])).'</extRif:url_slug>'."\n";
			$xml .= '      <extRif:manually_assessed_flag>'.esc(trim($registryObject[0]['manually_assessed_flag'])).'</extRif:manually_assessed_flag>'."\n";
			$xml .= '      <extRif:gold_status_flag>'.esc(trim($registryObject[0]['gold_status_flag'])).'</extRif:gold_status_flag>'."\n";
			$xml .= '      <extRif:quality_level>'.esc(trim($registryObject[0]['quality_level'])).'</extRif:quality_level>'."\n";	
			$owner = ($registryObject[0]['created_who'] == 'SYSTEM' ? 'harvest' : 'manual');
			$xml .= '      <extRif:feedType>'.$owner.'</extRif:feedType>'."\n";	

			$xml .= "    </extRif:extendedMetadata>\n";
		}
		
		// Registry Object Class
		// =====================================================================
		$registryObjectClass = strtolower($registryObject[0]['registry_object_class']);
		$dataSource = $registryObject[0]['data_source_key'];
		$type = esc(strtolower($registryObject[0]['type']));
		
		$dateAccessioned = '';
		if( $registryObject[0]['date_accessioned'] && $registryObjectClass == 'collection' )
		{
			$dateAccessioned = ' dateAccessioned="'.esc(getXMLDateTime($registryObject[0]['date_accessioned'])).'"';
		}
		
		$dateModified = '';
		if( $registryObject[0]['date_modified'] )
		{
			$dateModified = ' dateModified="'.esc(getXMLDateTime($registryObject[0]['date_modified'])).'"';
		}	
			
		
		// Registry Object Originating Source
		// =====================================================================
		$originatingSource = esc($registryObject[0]['originating_source']);
		$originatingSourceType = '';
		if( $registryObject[0]['originating_source_type'] )
		{
			$originatingSourceType = ' type="'.esc($registryObject[0]['originating_source_type']).'"';
		}
		
		$xml .= "    <originatingSource$originatingSourceType>$originatingSource</originatingSource>\n";
		
		
		// To prevent empty XML elements, we append to blank string and check that it actually
		// contains data
		$internalxml = "";
		
		// identifier
		// -------------------------------------------------------------
		$internalxml .= getIdentifierTypesXML($registryObjectKey, 'identifier');
		
		// name
		// -------------------------------------------------------------
		$internalxml .= getComplexNameTypesXML($registryObjectKey, 'name', $registryObjectClass, $forSOLR);
		
		// location
		// -------------------------------------------------------------
		$internalxml .= getLocationTypesXML($registryObjectKey, 'location', $forSOLR);

		if($forSOLR && $includeRelated)
		{
			// relatedObject
			// -------------------------------------------------------------
			$internalxml .= getRelatedObjectTypesXML($registryObjectKey, $dataSourceKey, $registryObjectClass, 'relatedObject', $forSOLR);
		}
		else if (!$forSOLR)
		{
			// relatedObject
			// -------------------------------------------------------------
			$internalxml .= getRelatedObjectTypesXML($registryObjectKey, $dataSource, $registryObjectClass,'relatedObject', $forSOLR);
		}
		
		// subject
		// -------------------------------------------------------------
		$internalxml .= getSubjectTypesXML($registryObjectKey, 'subject', $forSOLR);
		
		// description
		// -------------------------------------------------------------
		$internalxml .= getDescriptionTypesXML($registryObjectKey, 'description', $forSOLR);

		// coverage
		// -------------------------------------------------------------
		$internalxml .= getCoverageTypesXML($registryObjectKey, 'coverage', $forSOLR);		
		
		
		// relatedInfo
		// -------------------------------------------------------------
		$internalxml .= getRelatedInfoTypesXML($registryObjectKey, 'relatedInfo');
		
		// rights 
		// -------------------------------------------------------------
		$internalxml .= getRightsTypesXML($registryObjectKey, 'rights', $forSOLR);			
		
		// existenceDates
		// -------------------------------------------------------------
		$internalxml .= getExistenceDateTypesXML($registryObjectKey, 'existenceDates');	

		//citationInfo
		// -------------------------------------------------------------
		$internalxml .= getCitationInformationTypeXML($registryObjectKey, 'citationInfo');

				
		if($registryObjectClass  == 'service')
		{				
			// accessPolicy
			// -------------------------------------------------------------
			$internalxml .= getAccessPolicyTypesXML($registryObjectKey, 'accessPolicy');
		}	
		
		if (strlen($internalxml) > 0)
		{
			$xml .= "    <$registryObjectClass type=\"$type\"".$dateAccessioned.$dateModified.">\n";
			$xml .= $internalxml;
			$xml .= "    </$registryObjectClass>\n";
		} else {
			$xml .= "    <$registryObjectClass type=\"$type\"".$dateAccessioned.$dateModified."/>\n";
		}
		
		$xml .= "  </registryObject>\n";
	}

	return $xml;
}


function getRegistryObjectXMLforSOLR($registryObjectKey,$includeRelated=false)
{
	return getRegistryObjectXML($registryObjectKey, true, $includeRelated);
	/*
	$xml = '';
	$registryObject = getRegistryObject($registryObjectKey);
	$dataSourceKey = $registryObject[0]["data_source_key"];
	$registryObjectStatus = $registryObject[0]["status"];
	$dataSource = getDataSources($dataSourceKey, null);
	$allow_reverse_internal_links = $dataSource[0]['allow_reverse_internal_links'];
	$allow_reverse_external_links = $dataSource[0]['allow_reverse_external_links'];
	
	if( $registryObject )
	{
		// Registry Object
		// =====================================================================
		$group= esc($registryObject[0]['object_group']);
		$xml .= "  <registryObject group=\"$group\">\n";
		
		// Registry Object Key
		// =====================================================================
		$xml .= "    <key>".esc($registryObjectKey)."</key>\n";
		$xml .= "    <extRif:status>".esc($registryObjectStatus)."</extRif:status>\n";
		$xml .= "    <extRif:dataSourceKey>".esc($dataSourceKey)."</extRif:dataSourceKey>\n";		
		$reverseLinks = 'NONE';
		$allow_reverse_internal_links = $dataSource[0]['allow_reverse_internal_links'];
		$allow_reverse_external_links = $dataSource[0]['allow_reverse_external_links'];
		if($allow_reverse_internal_links == 't' && $allow_reverse_external_links == 't')
		{
			$reverseLinks = 'BOTH';
		}
		else if($allow_reverse_internal_links == 't')
		{
			$reverseLinks = 'INT';
			
		}
		else if($allow_reverse_external_links == 't')
		{
			$reverseLinks = 'EXT';
		}
		$xml .= "    <extRif:reverseLinks>".$reverseLinks."</extRif:reverseLinks>\n";
		// Registry Object Originating Source
		// =====================================================================
		$originatingSource = esc($registryObject[0]['originating_source']);
		$originatingSourceType = '';
		if( $registryObject[0]['originating_source_type'] )
		{
			$originatingSourceType = ' type="'.esc($registryObject[0]['originating_source_type']).'"';
		}
		
		$xml .= "    <originatingSource$originatingSourceType>$originatingSource</originatingSource>\n";
		
		// Registry Object Class
		// =====================================================================
		$registryObjectClass = strtolower($registryObject[0]['registry_object_class']);
		$type = esc(strtolower($registryObject[0]['type']));
		
		$dateAccessioned = '';
		if( $registryObject[0]['date_accessioned'] && $registryObjectClass == 'collection' )
		{
			$dateAccessioned = ' dateAccessioned="'.esc(getXMLDateTime($registryObject[0]['date_accessioned'])).'"';
		}
		
		$dateModified = '';
		if( $registryObject[0]['date_modified'] )
		{
			$dateModified = ' dateModified="'.esc(getXMLDateTime($registryObject[0]['date_modified'])).'"';
		}	
			
		// To prevent empty XML elements, we append to blank string and check that it actually
		// contains data
		$internalxml = "";
		
		// identifier
		// -------------------------------------------------------------
		$internalxml .= getIdentifierTypesXML($registryObjectKey, 'identifier');
		
		// displayTitle
		// -------------------------------------------------------------
		$internalxml .= '<extRif:displayTitle>'.esc(trim($registryObject[0]['display_title'])).'</extRif:displayTitle>';
				$logo = '';
	//if ($registryObjectClass == 'Party')
	//{
		$logoStr = getDescriptionLogo($registryObjectKey);
		if ($logoStr !== false)
		{
			
			$internalxml .= '<extRif:displayLogo>'.$logoStr.'</extRif:displayLogo>';
			$logo = <<<HTML
					<span style="position:relative;float:right;"><img id="party_logo" style="right:0; top:0;position:absolute; float:right;" src="{$logoStr}"/></span>
					<script type="text/javascript">
					testLogo('party_logo', '{$logoStr}');
					</script>
HTML; 
			
		} 
	//}
		
		// listTitle
		// -------------------------------------------------------------
		$internalxml .= '<extRif:listTitle>'.esc(trim($registryObject[0]['list_title'])).'</extRif:listTitle>';
		
		// name
		// -------------------------------------------------------------
		$internalxml .= getComplexNameTypesXMLforSOLR($registryObjectKey, 'name', $registryObjectClass);
		
		// location
		// -------------------------------------------------------------
		$internalxml .= getLocationTypesXMLforSOLR($registryObjectKey, 'location');

		// coverage
		// -------------------------------------------------------------
		$internalxml .= getCoverageTypesXMLforSOLR($registryObjectKey, 'coverage');	
			
		if($includeRelated){
			// relatedObject
			// -------------------------------------------------------------
			$internalxml .= getRelatedObjectTypesXMLforSolr($registryObjectKey, $registryObjectClass,$dataSourceKey,'relatedObject');		
	
		}
		// subject
		// -------------------------------------------------------------
		$internalxml .= getSubjectTypesXMLforSOLR($registryObjectKey, 'subject');
		
		// description
		// -------------------------------------------------------------
		$internalxml .= getDescriptionTypesXMLforSOLR($registryObjectKey, 'description');
			
		if($registryObjectClass  == 'service')
		{				
			// accessPolicy
			// -------------------------------------------------------------
			$internalxml .= getAccessPolicyTypesXML($registryObjectKey, 'accessPolicy');
		}		
		// relatedInfo
		// -------------------------------------------------------------
		$internalxml .= getRelatedInfoTypesXML($registryObjectKey, 'relatedInfo');
		// existenceDates
		// -------------------------------------------------------------
		$internalxml .= getExistenceDateTypesXMLSolr($registryObjectKey, 'existenceDates');
		// rights
		// -------------------------------------------------------------
		$internalxml .= getRightsTypesXMLforSOLR($registryObjectKey, 'rights');
		
		//citationInfo
		// -------------------------------------------------------------
		$internalxml .= getCitationInformationTypeXML($registryObjectKey, 'citationInfo');
		
		if (strlen($internalxml) > 0)
		{
			$xml .= "    <$registryObjectClass type=\"$type\"".$dateAccessioned.$dateModified.">\n";
			$xml .= $internalxml;
			$xml .= "    </$registryObjectClass>\n";
		} else {
			$xml .= "    <$registryObjectClass type=\"$type\"".$dateAccessioned.$dateModified."/>\n";
		}
		
		$xml .= "  </registryObject>\n";
	}
	
	return $xml;
	*/
}


// Datatype handlers
// =============================================================================
function getXMLDateTime($datetime)
{
	return formatDateTimeWithMask($datetime, eDCT_FORMAT_ISO8601_DATETIMESEC_UTC);
}

function getIdentifierTypesXML($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getIdentifiers($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			$value = esc($element['value']);
			$xml .= "      <$elementName$type>$value</$elementName>\n";
		}
	}
	return $xml;
}



function getComplexNameTypesXML($registryObjectKey, $elementName, $registryObjectClass, $forSOLR = false)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getComplexNames($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $dateFrom = $element['date_from'] )
			{
				$dateFrom = ' dateFrom="'.getXMLDateTime($dateFrom).'"';
			}
			if( $dateTo = $element['date_to'] )
			{
				$dateTo = ' dateTo="'.getXMLDateTime($dateTo).'"';
			}
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			if ($forSOLR)
			{
				if($element['type'] == 'alternative')
				{
					$xml .= "      <extRif:$elementName$type>\n";
					$xml .= getNamePartsXMLforSOLR($element['complex_name_id'], $registryObjectClass);
					$xml .= "      </extRif:$elementName>\n";
				}
			}
			
			$xml .= "      <$elementName$dateFrom$dateTo$type$lang>\n";
			$xml .= getNamePartsXML($element['complex_name_id']);
			$xml .= "      </$elementName>\n";
			
		}
	}
	return $xml;
}

function getNamePartsXMLforSOLR($complex_name_id,$registryObjectClass)
{
	$display_title = '';
	$list_title = '';

	$nameParts = getNameParts($complex_name_id);		
		if (!is_array($nameParts) || count($nameParts) == 0)
		{
			$display_title = "(no name/title)";
			$list_title = "(no name/title)";
		}
		else if(count($nameParts) == 1)
		{
			$display_title = trim($nameParts[0]['value']);
			$list_title = trim($nameParts[0]['value']);
		}
		else 
		{
			if ($registryObjectClass == 'party')
			{
				$partyNameParts = array();
				$partyNameParts['title'] = array();
				$partyNameParts['suffix'] = array();
				$partyNameParts['initial'] = array();
				$partyNameParts['given'] = array();
				$partyNameParts['family'] = array();
				$partyNameParts['user_specified_type'] = array();

				foreach ($nameParts AS $namePart)
				{
					if (in_array(strtolower($namePart['type']), array_keys($partyNameParts)))
					{
						$partyNameParts[strtolower($namePart['type'])][] = trim($namePart['value']);
					} 
					else 
					{
						$partyNameParts['user_specified_type'][] = trim($namePart['value']);
					}
				}
					$display_title = 	(count($partyNameParts['title']) > 0 ? implode(" ", $partyNameParts['title']) . " " : "") . 
										(count($partyNameParts['given']) > 0 ? implode(" ", $partyNameParts['given']) . " " : "") . 
										(count($partyNameParts['initial']) > 0 ? implode(" ", $partyNameParts['initial']) . " " : "") . 
										(count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) . " " : "") . 
										(count($partyNameParts['suffix']) > 0 ? implode(" ", $partyNameParts['suffix']) . " " : "") . 
										(count($partyNameParts['user_specified_type']) > 0 ? implode(" ", $partyNameParts['user_specified_type']) . " " : ""); 

					foreach ($partyNameParts['given'] AS &$givenName)
					{
						$givenName = (strlen($givenName) == 1 ? $givenName . "." : $givenName);
					}

					foreach ($partyNameParts['initial'] AS &$initial)
					{
						$initial = $initial . ".";
					}

					$list_title = 	(count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) : "") .
										(count($partyNameParts['given']) > 0 ? ", " . implode(" ", $partyNameParts['given']) : "") . 
										(count($partyNameParts['initial']) > 0 ? " " . implode(" ", $partyNameParts['initial']) : "") . 
										(count($partyNameParts['title']) > 0 ? ", " . implode(" ", $partyNameParts['title']) : "") . 
										(count($partyNameParts['suffix']) > 0 ? ", " . implode(" ", $partyNameParts['suffix']) : "") . 
										(count($partyNameParts['user_specified_type']) > 0 ? " " . implode(" ", $partyNameParts['user_specified_type']) . " " : ""); 

			}
			else
			{
				$np = array();
				foreach ($nameParts as $namePart)
				{
					$np[] = trim($namePart['value']);
				}

				$display_title = implode(" ", $np);
				$list_title = implode(" ", $np);
			}
		}

	if($list_title) {$names = "<extRif:listTitle>".esc($list_title)."</extRif:listTitle>\n";}else{$names='';}	
	$names .= "<extRif:displayTitle>".esc($display_title)."</extRif:displayTitle>\n";
	return $names;
}

function getNamePartsXML($complex_name_id)
{
	$xml = '';
	$list = getNameParts($complex_name_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			$value = esc($element['value']);
			$xml .= "        <namePart$type>$value</namePart>\n";
		}
	}
	return $xml;
}

function getLocationTypesXML($registryObjectKey, $elementName, $forSOLR)

{
	$xml = '';
	$elementName = esc($elementName);
	$list = getLocations($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			$dateFrom='';
			$dateTo='';
			if( $dateFromValue = $element['date_from'] )

			{
				$dateFrom = ' dateFrom="'.getXMLDateTime($dateFromValue).'"';
				if ($forSOLR)
				{
					$dateFrom .= ' extRif:dateFrom="'.formatDateTime($dateFromValue, gDATE).'"';
				}
			}
			if( $dateToValue = $element['date_to'] )
			{
				$dateTo = ' dateTo="'.getXMLDateTime($dateToValue).'"';
				if ($forSOLR)
				{
					$dateTo .= ' extRif:dateTo="'.formatDateTime($dateToValue, gDATE).'"';
				}
				
			}
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			$xml .= "      <$elementName$dateFrom$dateTo$type>\n";
			$xml .= getAddressXML($element['location_id'], $forSOLR);
			$xml .= getSpatialTypesXML($element['location_id'], $forSOLR);
			$xml .= "      </$elementName>\n";
		}
	}
	return $xml;
}

function getCoverageTypesXML($registryObjectKey, $elementName, $forSOLR)

{
	$xml = '';
	$elementName = esc($elementName);
	$list = getCoverage($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{

			$xml .= "      <$elementName>\n";
			$xml .= getSpatialCoverageXML($element['coverage_id'], $forSOLR);
			$xml .= getTemporalCoverageXML($element['coverage_id'], $forSOLR);
			$xml .= "      </$elementName>\n";
		}
	}
	return $xml;
}


function getSpatialCoverageXML($coverage_id, $forSOLR)
{
	$xml = '';
	$list = getSpatialCoverage($coverage_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			$value = esc($element['value']);
			$xml .= "        <spatial$type$lang>$value</spatial>\n";
			
			if ($forSOLR)
			{
				
				$xml .= "        <extRif:spatial$type$lang>\n";
				$centre = '';
				if($element['type'] == 'iso19139dcmiBox')
				{
					$valueString =  strtolower(esc($element['value'])).';';
					$matches = array();
					preg_match('/northlimit=([^;]*);/i', $valueString, $matches);
					$north = (float)$matches[1];
					preg_match('/southlimit=([^;]*);/i', $valueString, $matches);
					$south = (float)$matches[1];
					preg_match('/westlimit=([^;]*);/i', $valueString, $matches);
					$west = (float)$matches[1];
					preg_match('/eastlimit=([^;]*);/i', $valueString, $matches);
					$east = (float)$matches[1];	
					$coordinates = "$west,$north $east,$north $east,$south $west,$south $west,$north";		
					$centre = (($east+$west)/2).','.(($north+$south)/2);
					$xml .= "          <extRif:coords>$west,$north $east,$north $east,$south $west,$south $west,$north</extRif:coords>\n";
				}
				else if($element['type'] ==  'gmlKmlPolyCoords' || $element['type'] == 'kmlPolyCoords')
				{
					$coordinates = trim(esc($element['value']));
					$coordinates = preg_replace("/\s+/", " ", $coordinates);
					
					if( validKmlPolyCoords($coordinates) )
					{
						// Build the coordinates string for the centre.
						$points = explode(' ', $coordinates);
						if( count($points) > 0 )
						{
							$north = -90.0;
							$south = 90.0;
							$west = 180.0;
							$east = -180.0;
							foreach( $points as $point )
							{
								$P = explode(',', $point); // lon,lat
								if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
								if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
								if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
								if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
							}
						}
						$centre = (($east+$west)/2).','.(($north+$south)/2);
						$xml .= "          <extRif:coords>$coordinates</extRif:coords>\n";
					}
				}
		        if($centre != '')
		        {
		        	$xml .= "          <extRif:center>$centre</extRif:center>\n";
		        }			
				$xml .= "        </extRif:spatial>\n";
								
			}
			

		}
	}
	return $xml;
}



function getTemporalCoverageXML($coverage_id, $forSOLR)

{
	$xml = '';
	$list = getTemporalCoverage($coverage_id);

	if($list)
	{
	$xml .= "        <temporal>\n";
		foreach( $list as $element )
		{

			$textArray = getTemporalCoverageText($element['temporal_coverage_id']);
			$dateArray = getTemporalCoverageDate($element['temporal_coverage_id']);
			if($textArray)
			{
				asort($textArray);
				foreach( $textArray  as $row )
				{
					if($value = $row['value'])
					{
					$xml .= "          <text>".esc($value)."</text>\n";
					}
				}	
			}
			if($dateArray)
			{
				asort($dateArray);
				foreach( $dateArray as $row )
				{
					$type = ' type="'.esc($row['type']).'"';	
					$dateFormat = ' dateFormat="'.esc($row['date_format']).'"';
					$value = esc($row['value']);
					$xml .= "          <date$type$dateFormat>$value</date>\n";
					
					if ($forSOLR && preg_match('/\b\d{4}\b/', $value, $matches))
					{
						$year = $matches[0];
						$xml .= "          <extRif:date$type$dateFormat>$year</extRif:date>\n";
					}
				}	
			}
		}

	$xml .= "        </temporal>\n";	
	}
	return $xml;
}


function getCitationInformationTypeXML($registryObjectKey, $elementName)
{
		
	$xml = '';
	$citationInfo = '';
	if($array = getCitationInformation($registryObjectKey))
	{
		foreach( $array as $row )
		{
			$xml .= "	<".esc($elementName).">\n";
			$xml .= drawCitationInfoXML($row['citation_info_id'], $row);
			$xml .= "	</".esc($elementName).">\n";
		}
	}
	return $xml;
}


function drawCitationInfoXML($citation_info_id, $row)
{
	$xml = '';
	if($row['full_citation'] != '' || $row['style'] != '')
	{
		$style = ' style="'.esc($row['style']).'"';	
		$value = esc($row['full_citation']);
		$xml .= "	<fullCitation$style>$value</fullCitation>\n";
	}
	else if($row['metadata_identifier'] != '')
	{
		$xml .= "	<citationMetadata>\n";
		$xml .= "		<identifier type=\"".esc($row['metadata_type'])."\">".esc($row['metadata_identifier'])."</identifier>\n";		
		$xml .= getCitationContributorsXML($citation_info_id);		
		$xml .= "		<title>".esc($row['metadata_title'])."</title>\n";
		$xml .= "		<edition>".esc($row['metadata_edition'])."</edition>\n";
		if($row['metadata_publisher']){$xml .= "		<publisher>".esc($row['metadata_publisher'])."</publisher>\n";}				
		$xml .= "		<placePublished>".esc($row['metadata_place_published'])."</placePublished>\n";
		$xml .= getCitationDatesXML($citation_info_id);		
		$xml .= "		<url>".esc($row['metadata_url'])."</url>\n";
		$xml .= "		<context>".esc($row['metadata_context'])."</context>\n";
		$xml .= "	</citationMetadata>\n";	
	}
	return $xml;
		
}	


function getCitationContributorsXML($citation_info_id)
{
	$xml = '';
	if($array = getCitationContributors($citation_info_id))
	{
		foreach( $array as $row )
		{
			$seq = '';
		    if( $seq = $row['seq'] )
			{
				$seq = ' seq="'.esc($seq).'"';
			}			
			$xml .= "		<contributor$seq>\n";
			$xml .=	getContributorNamePartsXML($row['citation_contributor_id'], $row);
			$xml .= "		</contributor>\n";
		}
	}
	return $xml;
}

function getContributorNamePartsXML($id, $row=null)
{
	$xml = '';
	if($array = getCitationContributorNameParts($id))
	{
	foreach( $array as $row )
		{
			if( $type = $row['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			$value = esc($row['value']);
			$xml .= "			<namePart$type>$value</namePart>\n";
		}
	}
	return $xml;
}

function getCitationDatesXML($citation_info_id)
{
	$xml = '';
	if($array = getCitationDates($citation_info_id))
	{	
		foreach( $array as $row )
		{
			$type = ' type="'.esc($row['type']).'"';
			$dateValue = esc($row['date']);
			$xml .= "		<date$type>$dateValue</date>\n";
		}
	}
	return $xml;
}


function getAddressXML($location_id, $forSOLR)
{
	$xml = '';
	$list = getAddressLocations($location_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			$xml .= "        <address>\n";
			$xml .= getElectronicAddressTypesXML($element['address_id']);
			$xml .= getPhysicalAddressTypesXML($element['address_id'], $forSOLR);
			$xml .= "        </address>\n";
		}
	}
	return $xml;
}

function getElectronicAddressTypesXML($address_id)
{
	$xml = '';
	$list = getElectronicAddresses($address_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $value = $element['value'] )
			{
				$value = "            <value>".esc(trim($value))."</value>\n";
			}			
			$xml .= "          <electronic$type>\n$value";
			$xml .= getElectronicAddressArgsXML($element['electronic_address_id']);
			$xml .= "          </electronic>\n";
		}		
	}
	return $xml;
}

function getElectronicAddressArgsXML($electronic_address_id)
{
	$xml = '';
	$list = getElectronicAddressArgs($electronic_address_id);
	if( $list )
	{
		foreach( $list as $element )
		{			
			$required = "false";
			if( pgsqlBool($element['required']) )
			{
				$required = "true";
			}
			$required = ' required="'.esc($required).'"';
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $use = $element['use'] )
			{
				$use = ' use="'.esc($use).'"';
			}
			$value = esc($element['name']);
			$xml .= "            <arg$required$type$use>$value</arg>\n";
		}		
	}
	return $xml;
}

function getPhysicalAddressTypesXML($address_id, $forSOLR)
{
	$xml = '';
	$list = getPhysicalAddresses($address_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			$xml .= "          <physical$type$lang>\n";
			$xml .= getAddressPartsXML($element['physical_address_id'], $forSOLR);	
			$xml .= "          </physical>\n";
		}	
	}
	return $xml;
}


function getAddressPartsXML($physical_address_id, $forSOLR)
{
	$xml = '';
	$list = getAddressParts($physical_address_id);
	if( $list )
	{
		foreach( $list as $element )
		{			
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			$value = esc($element['value']);
			$xml .= "            <addressPart$type>$value</addressPart>\n";
			
			if ($forSOLR)
			{
				$value = htmlspecialchars_decode($value);
				$value = purify($value);
				$value = htmlspecialchars($value);
				$xml .= "            <extRif:addressPart$type>$value</extRif:addressPart>\n";
			}
		}		
	}
	return $xml;
}


function getSpatialTypesXML($location_id, $forSOLR)
{
	$xml = '';
	$list = getSpatialLocations($location_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			$value = esc($element['value']);
			$xml .= "        <spatial$type$lang>$value</spatial>\n";
				
			if ($forSOLR)
			{
				$centre = '';
				$xml .= "        <extRif:spatial>";
				if($element['type'] == 'iso19139dcmiBox')
				{
					$valueString = strtolower(esc($element['value'])).';';
					$matches = array();
					preg_match('/northlimit=([^;]*);/i', $valueString, $matches);
					$north = (float)$matches[1];
					preg_match('/southlimit=([^;]*);/i', $valueString, $matches);
					$south = (float)$matches[1];
					preg_match('/westlimit=([^;]*);/i', $valueString, $matches);
					$west = (float)$matches[1];
					preg_match('/eastlimit=([^;]*);/i', $valueString, $matches);
					$east = (float)$matches[1];	
					$coordinates = "$west,$north $east,$north $east,$south $west,$south $west,$north";		
					$centre = (($east+$west)/2).','.(($north+$south)/2);
					$xml .= "          <extRif:coords>$west,$north $east,$north $east,$south $west,$south $west,$north</extRif:coords>\n";
					

				}
				else if($element['type'] ==  'gmlKmlPolyCoords' || $element['type'] == 'kmlPolyCoords')
				{
					$coordinates = trim(esc($element['value']));
					$coordinates = preg_replace("/\s+/", " ", $coordinates);
					$xml .= "          <extRif:isValidCoords>".validKmlPolyCoords($coordinates)."---".$coordinates."</extRif:isValidCoords>\n";
					//if( validKmlPolyCoords($coordinates) )
					//{
						// Build the coordinates string for the centre.
						$points = explode(' ', $coordinates);
						if( count($points) > 0 )
						{
							$north = -90.0;
							$south = 90.0;
							$west = 180.0;
							$east = -180.0;
							foreach( $points as $point )
							{
								$P = explode(',', $point); // lon,lat
								if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
								if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
								if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
								if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
							}
						}
						$centre = (($east+$west)/2).','.(($north+$south)/2);
					    $xml .= "          <extRif:coords>$coordinates</extRif:coords>\n";
						
					//}
				}

				
				
		        if($centre != '')
		        {
		        	$xml .= "          <extRif:center>$centre</extRif:center>\n";
	
		        }		
		        $xml .= "        </extRif:spatial>";	
				
			}
			
		}
	}
	return $xml;
}

function getRelatedObjectTypesXML($registryObjectKey, $dataSourceKey, $registryObjectClass, $elementName, $forSOLR = false)
{
	global $typeArray;
	
	
	$xml = '';
	$elementName = esc($elementName);
	
	//we need to check if this has related primary keys
	$dataSource = getDataSources($dataSourceKey, null);	
	$create_primary_relationships = $dataSource[0]['create_primary_relationships'];
	
	$pkey1 = '';
	$pkey2 = '';
	
	//we do not want to add the related primary objects if we are pasing the rifcs to te manual entry screens
	$caller = explode('/',$_SERVER['PHP_SELF']);
	$thecaller = $caller[count($caller)-1];
	
	if(($dataSource[0]['create_primary_relationships']=='t'||$dataSource[0]['create_primary_relationships']=='1') && $thecaller != 'process_registry_object.php')
	{
		$primary_key_1 =  $dataSource[0]['primary_key_1'];
		$primary_key_2 =  $dataSource[0]['primary_key_2'];
		
		if(trim($dataSource[0]['primary_key_1'])!='' && trim($dataSource[0]['primary_key_1'])!=$registryObjectKey)
		{
			
			$pkey1 = esc($dataSource[0]["primary_key_1"]);
			$relatedObject = getRegistryObject($pkey1,true);
			$relatedclass= strtolower($relatedObject[0]['registry_object_class']);	
			$relation_logo = false;
			
			if($typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1']])
			{
				$type = ' type="'.$typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1']].'"';
			}else{
				$type = ' type="'.$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1'].'"';
			}
			if (isset($row) &&	$relatedObject[0]['registry_object_class'] == 'Party' && strtolower($relatedObject[0]['type']) != 'person') 
			{
				$relation_logo = getDescriptionLogo($key);
			}
			
			$type = ' type="'.$dataSource[0][$registryObjectClass.'_rel_1'].'"';
			$xml .= "      <$elementName>\n";
			$xml .= "        <key>".$pkey1."</key>\n";
			$xml .= "        <relation$type></relation>\n";		
			if ($forSOLR)
			{
				$xml .= "        <extRif:relatedObjectClass>".strtolower($relatedObject[0]['registry_object_class'])."</extRif:relatedObjectClass>\n";
				$xml .= "        <extRif:relatedObjectType>".strtolower($relatedObject[0]['type'])."</extRif:relatedObjectType>\n";
				$xml .= "        <extRif:relatedObjectListTitle>".esc($relatedObject[0]['list_title'])."</extRif:relatedObjectListTitle>\n";
				$xml .= "        <extRif:relatedObjectDisplayTitle>".esc($relatedObject[0]['display_title'])."</extRif:relatedObjectDisplayTitle>\n";
				if($relation_logo) 
					$xml .= "        <extRif:relatedObjectLogo>".esc($relation_logo)."</extRif:relatedObjectLogo>\n";										
			}
			$xml .= "      </$elementName>\n";			
		}
		if(trim($dataSource[0]['primary_key_2'])!='' && trim($dataSource[0]['primary_key_2'])!=$registryObjectKey)
		{
			$pkey2 = esc($dataSource[0]["primary_key_2"]);
			$relatedObject = getRegistryObject($pkey2,true);
			$relatedclass= strtolower($relatedObject[0]['registry_object_class']);	
			$relation_logo = false;
			
			if($typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_2']])
			{
				$type = ' type="'.$typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_2']].'"';
			}else{
				$type = ' type="'.$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_2'].'"';
			}
			if (isset($row) &&	$relatedObject[0]['registry_object_class'] == 'Party' && strtolower($relatedObject[0]['type']) != 'person') 
			{
				$relation_logo = getDescriptionLogo($key);
			}
			
			$type = ' type="'.$dataSource[0][$registryObjectClass.'_rel_2'].'"';
			$xml .= "      <$elementName>\n";
			$xml .= "        <key>".$pkey2."</key>\n";
			$xml .= "        <relation$type></relation>\n";		
			if ($forSOLR)
			{
				$xml .= "        <extRif:relatedObjectClass>".strtolower($relatedObject[0]['registry_object_class'])."</extRif:relatedObjectClass>\n";
				$xml .= "        <extRif:relatedObjectType>".strtolower($relatedObject[0]['type'])."</extRif:relatedObjectType>\n";
				$xml .= "        <extRif:relatedObjectListTitle>".esc($relatedObject[0]['list_title'])."</extRif:relatedObjectListTitle>\n";
				$xml .= "        <extRif:relatedObjectDisplayTitle>".esc($relatedObject[0]['display_title'])."</extRif:relatedObjectDisplayTitle>\n";
				if($relation_logo) 
					$xml .= "        <extRif:relatedObjectLogo>".esc($relation_logo)."</extRif:relatedObjectLogo>\n";										
			}
			$xml .= "      </$elementName>\n";					
		}		
	}		
	$list = getRelatedObjects($registryObjectKey);
	if( $list )
	{
	foreach( $list as $element )
		{
			$key = esc($element['related_registry_object_key']);
			if($key!=$pkey1 && $key!=$pkey2)
			{
				$relatedObject = getRegistryObject($element['related_registry_object_key'],true);
				$relation_logo = false;
				$relationType = getRelationType($element['relation_id']);
				if (isset($element) &&	$relatedObject[0]['registry_object_class'] == 'Party' && strtolower($relatedObject[0]['type']) != 'person' ) 
				{
					$relation_logo = getDescriptionLogo($key);
				}		
				$relatedclass= strtolower($relatedObject[0]['registry_object_class']);
	
				$xml .= "      <$elementName>\n";
				$xml .= "        <key>$key</key>\n";
				//$xml .= getRelationsXML($element['relation_id'],$typeArray[$registryObjectClass], $forSOLR);
				$xml .= getRelationsXML($element['relation_id'], $forSOLR);
				if($forSOLR)
				{
					$xml .= "        <extRif:relatedObjectClass>".strtolower($relatedObject[0]['registry_object_class'])."</extRif:relatedObjectClass>\n";
					$xml .= "        <extRif:relatedObjectType>".strtolower($relatedObject[0]['type'])."</extRif:relatedObjectType>\n";
					$xml .= "        <extRif:relatedObjectListTitle>".esc($relatedObject[0]['list_title'])."</extRif:relatedObjectListTitle>\n";
					$xml .= "        <extRif:relatedObjectDisplayTitle>".esc($relatedObject[0]['display_title'])."</extRif:relatedObjectDisplayTitle>\n";
					if($relation_logo) $xml .= "        <extRif:relatedObjectLogo>".esc($relation_logo)."</extRif:relatedObjectLogo>\n";
				}
				$xml .= "      </$elementName>\n";
			}
		}
	}


	return $xml;
}

function getRelationType($relation_id)
{
	$list = getRelationDescriptions($relation_id);
	$type = '';
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				return $type;	
			}

		}
	}
	return $type;	
}
function getRelationsXML($relation_id, $forSOLR = false)
{
	global $typeArray;
	$xml = '';


	$list = getRelationDescriptions($relation_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			$type ='';
			if( $etype = $element['type'] )
			{

				if ($forSOLR)
				{
					if( array_key_exists($etype, $typeArray) )
					{
						$type .= ' extRif:type="'.$typeArray[$etype].'"';
					}
					else
					{
						$type .= ' extRif:type="'.changeFromCamelCase($etype).'"';
					}
				}
				
				$type .= ' type="'.esc($etype).'"';
				
			}
			if( $description = $element['description'] )
			{
				if( $lang = $element['lang'] )
				{
					$lang = ' xml:lang="'.esc($lang).'"';
				}
				$description = "          <description$lang>".esc($element['description'])."</description>\n";
			}
			if( $url = $element['url'] )
			{
				$url = "          <url>".esc($element['url'])."</url>\n";
			}
			$xml .= "        <relation$type>$description$url</relation>\n";
		}
	}
	return $xml;
}

function getSubjectTypesXML($registryObjectKey, $elementName, $forSOLR=false)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getSubjects($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			if( $termId = $element['termIdentifier'] )
			{
				$termId = ' termIdentifier="'.esc($termId).'"';
			}	
			$rawvalue = trim(esc($element['value']));
			$value = $rawvalue;
			$term = '';
			if ($forSOLR)
			{
				$resolvedName = '';
				if(($value != '') && (strlen($value) < 7) && is_numeric($value))
				{
					$valueLength = strlen($value);
					if($valueLength < 6){
						for($i = 0; $i < (6 - $valueLength) ; $i++){
							$value .= '0';
						}				
					}
					$resolvedName = getTermsForVocabByIdentifier(null, $value);
				}
				if($resolvedName && $resolvedName[0]['name'] != '')
				{

					$term = " extRif:resolvedValue=\"" . $resolvedName[0]['name'] . "\"";
				}
				else 
				{
					$term = " extRif:resolvedValue=\"" . $value . "\"";
				}
			}

			$xml .= "      <$elementName$type$lang$term>$rawvalue</$elementName>\n";
		}
	}
	return $xml;
}

function purify($dirty_html){
	require_once "../htmlpurifier/library/HTMLPurifier.auto.php";
	
	// Allowed Elements in HTML
	$HTML_Allowed_Elms = 'a, abbr, acronym, b, blockquote, br, caption, cite, code, dd, del, dfn, div, dl, dt, em, h1, h2, h3, h4, h5, h6, i, img, ins, kbd, li, ol, p, pre, s, span, strike, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var';

	// Allowed Element Attributes in HTML, element must also be allowed in Allowed Elements for these attributes to work.
	$HTML_Allowed_Attr = 'a.href, a.rev, a.title, a.target, a.rel, abbr.title, acronym.title, blockquote.cite, div.align, div.class, div.id, img.src, img.alt, img.title, img.class, img.align, span.class, span.id, table.class, table.id, table.border, table.cellpadding, table.cellspacing, table.width, td.abbr, td.align, td.class, td.id, td.colspan, td.rowspan, td.valign, tr.align, tr.class, tr.id, tr.valign, th.abbr, th.align, th.class, th.id, th.colspan, th.rowspan, th.valign, img.width, img.height, img.style';
	
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
	$config->set('HTML.Doctype', 'XHTML 1.0 Transitional'); // replace with your doctype
	//$config->set('Cache.SerializerPath', '/tmp/htmlfilter/');
	$config->set('HTML.AllowedElements', $HTML_Allowed_Elms); // sets allowed html elements that can be used.
	$config->set('HTML.AllowedAttributes', $HTML_Allowed_Attr); // sets allowed html attributes that can be used.
	
	
	
	//$def->removeAttribute('p','font');
    $purifier = new HTMLPurifier($config);
    $clean_html = $purifier->purify($dirty_html);
    return $clean_html;
}
function getDescriptionTypesXML($registryObjectKey, $elementName, $forSOLR)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getDescriptions($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			$value = trim($element['value']);
			$xml .= "      <$elementName$type$lang>".esc($value)."</$elementName>\n";
			
			if ($forSOLR)
			{
				$value = htmlspecialchars_decode($value);
				$value = purify($value);
				$value = htmlspecialchars($value);
				
				$xml .= "      <extRif:$elementName$type$lang>$value</extRif:$elementName>\n";
			}
		}
	}
	return $xml;
}
function getAccessPolicyTypesXML($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getAccessPolicies($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			$value = esc($element['value']);
			$xml .= "      <$elementName>$value</$elementName>\n";
		}
	}
	return $xml;
}


function getRelatedInfoTypesXML($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getRelatedInfo($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if($value = esc($element['value']))
			{
				$xml .= "	<$elementName>\n";
				$xml .= "		<identifier type=\"uri\">$value</identifier>\n";
				$xml .= "	</$elementName>\n";				
			}
			else
			{
				if($type = $element['info_type'])
				{
				$type = ' type="'.esc($type).'" ';
				}
				$xml .= "<$elementName$type>\n";
				$value = esc($element['identifier']);
				$xml .= "		<identifier type=\"".esc($element['identifier_type'])."\">$value</identifier>\n";
				if($notes = $element['title'])
				{
				$xml .= "		<title>".esc($notes)."</title>\n";
				}
				if($notes = $element['notes'])
				{
				$xml .= "		<notes>".esc($notes)."</notes>\n";
				}
				$xml .= "</$elementName>\n";
			}
		}
	}
	return $xml;
}

function getRightsTypesXML($registryObjectKey, $elementName, $forSOLR)
{
	$xml = '';
	$elementName = esc($elementName);

	if ($forSOLR)
	{
		$list = getDescriptions($registryObjectKey);
		if( $list )
		{
			foreach( $list as $element )
			{
				if( $type = $element['type'] && ($element['type']=='rights' || $element['type']=='accessRights'))
				{
					$type = ' type="'.esc($element['type']).'"';
			
				$test = $element['type'];
				$value = esc($element['value']);
				$xml .= "      <extRif:$elementName$type>$value</extRif:$elementName>\n";
				$type = '';
				}
			}
		}
	}
	
	$list = getRights($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			$xml .= "      <$elementName>";			
			if( $type = $element['access_rights'] || $type = $element['access_rights_uri'])
			{
				$subType = 'accessRights';
				if($uri = $element['access_rights_uri'])
				{
					$uri = ' rightsUri = "'.esc($uri).'"';
				}
				$value = esc($element['access_rights']);
				$xml .= "      <$subType$uri>$value</$subType>\n";				
			}
			
			if( $type = $element['rights_statement'] || $type = $element['rights_statement_uri'])
			{
				$subType = 'rightsStatement';
				if($uri = $element['rights_statement_uri'])
				{
					$uri = ' rightsUri = "'.esc($uri).'"';
				}
				$value = esc($element['rights_statement']);
				$xml .= "      <$subType$uri>$value</$subType>\n";								
			}
			
			if( $type = $element['licence'] || $type = $element['licence_uri'])
			{
				$subType = 'licence';
				if($uri = $element['licence_uri'])
				{
					$uri = ' rightsUri = "'.esc($uri).'"';
				}
				$value = esc($element['licence']);
				$xml .= "      <$subType$uri>$value</$subType>\n";																
			}
			$xml .= "      </$elementName>\n";		

			
			if ($forSOLR)
			{
				$elementName = "extRif:rights";
				if( $list )
				{
					foreach( $list as $element )
					{
						if( $type = $element['access_rights'] || $type = $element['access_rights_uri'])
						{
							$type = ' type="accessRights"';
							if($uri = $element['access_rights_uri'])
							{
								$uri = ' rightsUri = "'.esc($uri).'"';
							}
							$value = esc($element['access_rights']);
							$xml .= "      <$elementName$type$uri>$value</$elementName>\n";				
						}
						
						if( $type = $element['rights_statement'] || $type = $element['rights_statement_uri'])
						{
							$type = ' type="rights"';
							if($uri = $element['rights_statement_uri'])
							{
								$uri = ' rightsUri = "'.esc($uri).'"';
							}
							$value = esc($element['rights_statement']);
							$xml .= "      <$elementName$type$uri>$value</$elementName>\n";								
						}
						
						if( $type = $element['licence'] || $type = $element['licence_uri'])
						{
							$type = ' type="licence"';
							if($uri = $element['licence_uri'])
							{
								$uri = ' rightsUri = "'.esc($uri).'"';
							}
							$value = esc($element['licence']);
							$xml .= "      <$elementName$type$uri>$value</$elementName>\n";										
						}			
					}
				}	
			}
		}	
	}	
	return $xml;			
}

function getExistenceDateTypesXML($registryObjectKey, $elementName)
{
	$xml = '';
	$startdate = '';
	$enddate = '';
	$elementName = esc($elementName);
	$list = getExistenceDate($registryObjectKey);
	if( $list )
	{	
		foreach( $list as $element )
		{
			$xml .=	"		<$elementName>";
			if($startdate = $element['start_date'])
			{
				if($startDateFormat = $element['start_date_format'])
				{
					$dateFormat = ' dateFormat="'.$startDateFormat.'"';
				}
				$xml .= "			<startDate$dateFormat>$startdate</startDate>";
			}
			if($enddate = $element['end_date'])
			{
				if($startDateFormat = $element['end_date_format'])
				{
					$dateFormat = ' dateFormat="'.$startDateFormat.'"';
				}
				$xml .= "			<endDate$dateFormat>$enddate</endDate>";
			}			
			$xml .= "      </$elementName>\n";
		}
	}
	return $xml;
}

function getRegistryObjectKML($registryObjectKey)
{
	$kml = "";
	$locations = getLocations($registryObjectKey);
	$spatialLocations = null;
	$placemarks = array();
	
	if( $locations )
	{
		foreach( $locations as $location )
		{
			$locationId = $location['location_id'];
			$locationType = $location['type'];
			$locationDateFrom = $location['date_from'];
			$locationDateTo = $location['date_to'];
			//switch( $locationType ) 
			//{
				// coverage
			//	case 'coverage':
					if( $spatialLocations = getSpatialLocations($locationId) )
					{
						foreach( $spatialLocations as $spatialLocation )
						{
							$spatialLocationId = $spatialLocation['spatial_location_id'];
							$spatialLocationType = $spatialLocation['type'];
							switch( $spatialLocationType ) 
							{
								// -----------------------------------------------------
								// iso19139dcmiBox
								// -----------------------------------------------------
								case 'iso19139dcmiBox':
									// Parse the value and turn it into the KML coordinates string.
									//northlimit=28; southlimit=-70; westlimit=20; eastLimit=127;
									$valueString = $spatialLocation['value'];
									$matches = array();
									preg_match('/northlimit=([^;]*);/i', $valueString, $matches);
									$north = (float)$matches[1];
									preg_match('/southlimit=([^;]*);/i', $valueString, $matches);
									$south = (float)$matches[1];
									preg_match('/westlimit=([^;]*);/i', $valueString, $matches);
									$west = (float)$matches[1];
									preg_match('/eastlimit=([^;]*);/i', $valueString, $matches);
									$east = (float)$matches[1];	
									// Build the coordinates string.
									$coordinates = "$west,$north $east,$north $east,$south $west,$south $west,$north";		
								
									// Build the coordinates string for the centre.
									$centre = (($east+$west)/2).','.(($north+$south)/2);
								
									//TODO: Set the style for the marker. regionMarkerStyle_2 
									$markerStyle = 'regionMarkerStyle';
									if( $north === $south && $east === $west )
									{
										$markerStyle = 'pointMarkerStyle';
									}
								
									// Put the entry in the placemarks list.
									$placemarks[] = array( 'name'         => 'Location: '.$locationType.gCHAR_EMDASH.$spatialLocationType,
														   'coordinates'  => $coordinates,
														   'centre'       => $centre,
														   'marker_style' => $markerStyle,
														   'begin'        => $locationDateFrom,
														   'end'          => $locationDateTo
														 );
									break;
								
								// -----------------------------------------------------
								// gmlKmlPolyCoords
								// -----------------------------------------------------
								case 'gmlKmlPolyCoords':
									// Build the coordinates string.
									$coordinates = trim($spatialLocation['value']);
								
									// Rationalise whitespace to spaces for the explode in the next step.
									$coordinates = preg_replace("/\s+/", " ", $coordinates);
									
									if( validKmlPolyCoords($coordinates) )
									{
										// Build the coordinates string for the centre.
										$points = explode(' ', $coordinates);
										if( count($points) > 0 )
										{
											$north = -90.0;
											$south = 90.0;
											$west = 180.0;
											$east = -180.0;
											foreach( $points as $point )
											{
												$P = explode(',', $point); // lon,lat
												if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
												if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
												if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
												if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
											}
										}
										$centre = (($east+$west)/2).','.(($north+$south)/2);
									
										// Set the style for the marker.
										$markerStyle = 'regionMarkerStyle';
										if( $north === $south && $east === $west )
										{
											$markerStyle = 'pointMarkerStyle';
										}
									
										// Put the entry in the placemarks list.
										$placemarks[] = array( 'name'         => 'Location: '.$locationType.gCHAR_EMDASH.$spatialLocationType,
															   'coordinates'  => $coordinates,
															   'centre'       => $centre,
															   'marker_style' => $markerStyle,
															   'begin'        => $locationDateFrom,
															   'end'          => $locationDateTo
															 );
									}
									break;
								
								// -----------------------------------------------------
								// kmlPolyCoords
								// -----------------------------------------------------
								case 'kmlPolyCoords':
									// Build the coordinates string.
									$coordinates = trim($spatialLocation['value']);
								
									// Rationalise whitespace to spaces for the explode in the next step.
									$coordinates = preg_replace("/\s+/", " ", trim($coordinates));
								
									if( validKmlPolyCoords($coordinates) )
									{
										// Build the coordinates string for the centre.
										$points = explode(' ', $coordinates);
										if( count($points) > 0 )
										{
											$north = -90.0;
											$south = 90.0;
											$west = 180.0;
											$east = -180.0;
											foreach( $points as $point )
											{
												$P = explode(',', $point); // lon,lat
												if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
												if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
												if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
												if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
											}
										}
										$centre = (($east+$west)/2).','.(($north+$south)/2);
									
										// Set the style for the marker.
										$markerStyle = 'regionMarkerStyle';
										if( $north === $south && $east === $west )
										{
											$markerStyle = 'pointMarkerStyle';
										}
									
										// Put the entry in the placemarks list.
										$placemarks[] = array( 'name'         => 'Location: '.$locationType.gCHAR_EMDASH.$spatialLocationType,
															   'coordinates'  => $coordinates,
															   'centre'       => $centre,
															   'marker_style' => $markerStyle,
															   'begin'        => $locationDateFrom,
															   'end'          => $locationDateTo
															 );
									}			 
									break;
								
								// -----------------------------------------------------
								default:
									break;
							}
						}
			//		break;
			//		}
			//	default:
			//		break;
			}
		}
	}
	
	$coverageList = getCoverage($registryObjectKey);
	if( $coverageList )
	{
		foreach( $coverageList  as $coverage )
		{
			$spatialList = getSpatialCoverage($coverage['coverage_id']);
			$temporaLlist = getTemporalCoverage($coverage['coverage_id']);
			$locationDateFrom = '';
			$locationDateTo = '';
			$locationType = 'Coverage';
			//get the dateFrom and dateTo for this spatial coverage
			
			if($temporaLlist)
			{
				foreach( $temporaLlist as $element )
				{
					$dateArray = getTemporalCoverageDate($element['temporal_coverage_id']);
					if($dateArray)
					{
						foreach( $dateArray as $row )
						{
							if($row['type'] == 'dateFrom')
							{
								$locationDateFrom = esc($row['value']);
							}
							if($row['type'] == 'dateTo')
							{
								$locationDateTo = esc($row['value']);						
							}
						}	
					}
				}	
			}
			if($spatialList)
			{			
			foreach( $spatialList as $spatialLocation )
					{
						$spatialLocationId = $spatialLocation['spatial_location_id'];
						$spatialLocationType = $spatialLocation['type'];
						switch( $spatialLocationType ) 
						{
							// -----------------------------------------------------
							// iso19139dcmiBox
							// -----------------------------------------------------
							case 'iso19139dcmiBox':
								// Parse the value and turn it into the KML coordinates string.
								//northlimit=28; southlimit=-70; westlimit=20; eastLimit=127;
								$valueString = $spatialLocation['value'];
								$matches = array();
								preg_match('/northlimit=([^;]*);/i', $valueString, $matches);
								$north = (float)$matches[1];
								preg_match('/southlimit=([^;]*);/i', $valueString, $matches);
								$south = (float)$matches[1];
								preg_match('/westlimit=([^;]*);/i', $valueString, $matches);
								$west = (float)$matches[1];
								preg_match('/eastlimit=([^;]*);/i', $valueString, $matches);
								$east = (float)$matches[1];	
								// Build the coordinates string.
								$coordinates = "$west,$north $east,$north $east,$south $west,$south $west,$north";		
							
								// Build the coordinates string for the centre.
								$centre = (($east+$west)/2).','.(($north+$south)/2);
							
								// Set the style for the marker.
								$markerStyle = 'regionMarkerStyle';
								if( $north === $south && $east === $west )
								{
									$markerStyle = 'pointMarkerStyle';
								}
							
								// Put the entry in the placemarks list.
								$placemarks[] = array( 'name'         => 'Coverage: '.$locationType.gCHAR_EMDASH.$spatialLocationType,
													   'coordinates'  => $coordinates,
													   'centre'       => $centre,
													   'marker_style' => $markerStyle,
													   'begin'        => $locationDateFrom,
													   'end'          => $locationDateTo
													 );
								break;
							
							// -----------------------------------------------------
							// gmlKmlPolyCoords
							// -----------------------------------------------------
							case 'gmlKmlPolyCoords':
								// Build the coordinates string.
								$coordinates = trim($spatialLocation['value']);
							
								// Rationalise whitespace to spaces for the explode in the next step.
								$coordinates = preg_replace("/\s+/", " ", $coordinates);
								
								if( validKmlPolyCoords($coordinates) )
								{
									// Build the coordinates string for the centre.
									$points = explode(' ', $coordinates);
									if( count($points) > 0 )
									{
										$north = -90.0;
										$south = 90.0;
										$west = 180.0;
										$east = -180.0;
										foreach( $points as $point )
										{
											$P = explode(',', $point); // lon,lat
											if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
											if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
											if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
											if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
										}
									}
									$centre = (($east+$west)/2).','.(($north+$south)/2);
								
									// Set the style for the marker.
									$markerStyle = 'regionMarkerStyle';
									if( $north === $south && $east === $west )
									{
										$markerStyle = 'pointMarkerStyle';
									}
								
									// Put the entry in the placemarks list.
									$placemarks[] = array( 'name'         => 'Coverage: '.$locationType.gCHAR_EMDASH.$spatialLocationType,
														   'coordinates'  => $coordinates,
														   'centre'       => $centre,
														   'marker_style' => $markerStyle,
														   'begin'        => $locationDateFrom,
														   'end'          => $locationDateTo
														 );
								}
								break;
							
							// -----------------------------------------------------
							// kmlPolyCoords
							// -----------------------------------------------------
							case 'kmlPolyCoords':
								// Build the coordinates string.
								$coordinates = trim($spatialLocation['value']);
							
								// Rationalise whitespace to spaces for the explode in the next step.
								$coordinates = preg_replace("/\s+/", " ", trim($coordinates));
							
								if( validKmlPolyCoords($coordinates) )
								{
									// Build the coordinates string for the centre.
									$points = explode(' ', $coordinates);
									if( count($points) > 0 )
									{
										$north = -90.0;
										$south = 90.0;
										$west = 180.0;
										$east = -180.0;
										foreach( $points as $point )
										{
											$P = explode(',', $point); // lon,lat
											if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
											if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
											if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
											if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
										}
									}
									$centre = (($east+$west)/2).','.(($north+$south)/2);
								
									// Set the style for the marker.
									$markerStyle = 'regionMarkerStyle';
									if( $north === $south && $east === $west )
									{
										$markerStyle = 'pointMarkerStyle';
									}
								
									// Put the entry in the placemarks list.
									$placemarks[] = array( 'name'         => 'Coverage: '.$locationType.gCHAR_EMDASH.$spatialLocationType,
														   'coordinates'  => $coordinates,
														   'centre'       => $centre,
														   'marker_style' => $markerStyle,
														   'begin'        => $locationDateFrom,
														   'end'          => $locationDateTo
														 );
								}			 
								break;
							
							// -----------------------------------------------------
							default:
								break;
						}
					}//END FOR EACH COVERAGE	
				}		
			}
	}
		
	if( count($placemarks) > 0 )
	{
		$name = getNameHTML($registryObjectKey);
		if( $name == '' )
		{
			$name = $registryObjectKey;
		}
		$kml .= '	<Folder>'."\n";
		$kml .= '		<name>'.esc($name).'</name>'."\n";
		$kml .= '		<open>1</open>'."\n";
		foreach( $placemarks as $placemark )
		{
			$kml .= '		<Folder>'."\n";
			$kml .= '			<name>'.esc($placemark['name']).'</name>'."\n";
			$kml .= '			<open>0</open>'."\n";
			if( $placemark['centre'] != '' )
			{
				$kml .= '			<Placemark>'."\n";
				$kml .= '				<name>'.esc($name.' ['.$placemark['name'].']').'</name>'."\n";
				$kml .= '				<styleUrl>#'.esc($placemark['marker_style']).'</styleUrl>'."\n";
				if( $placemark['begin'] || $placemark['end'] )
				{
					$kml .= '				<TimeSpan>'."\n";
					if( $placemark['begin'] )
					{
						$kml .= '				  <begin>'.esc(getXMLDateTime($placemark['begin'])).'</begin>'."\n";
					}
					if( $placemark['end'] )
					{
						$kml .= '				  <end>'.esc(getXMLDateTime($placemark['end'])).'</end>'."\n";
					}
					$kml .= '				</TimeSpan>'."\n";
				}
				$kml .= '				<Point>'."\n";
	        	$kml .= '					<coordinates>'.esc($placemark['centre']).'</coordinates>'."\n";
				$kml .= '				</Point>'."\n";
				$kml .= '				<description><![CDATA[';
				$kml .= '<p>'.esc($placemark['name']).': '.esc($placemark['coordinates']).'</p>'."\n";
				$kml .= '<p>'.getDescriptionsHTML($registryObjectKey, gORCA_HTML_TABLE).'</p>'."\n";
				$kml .= '<p><a href="'.eAPP_ROOT.'orca/view.php?key='.esc(urlencode($registryObjectKey)).'">View the complete record in the ANDS Collection Registry.</a></p>'."\n";
				$kml .= ']]></description>'."\n";
				$kml .= '			</Placemark>'."\n";
			}
			if( $placemark['coordinates'] != '' )
			{
				$kml .= '			<Placemark>'."\n";
				if($placemark['marker_style'] == 'regionMarkerStyle_2')
				{
					$regionStyle = 'regionStyle_2';				
				}
				else
				{
					$regionStyle = 'regionStyle';						
				}
				//$kml .= '				<name>'.esc($placemark['name']).'</name>'."\n";
				$kml .= '				<styleUrl>#'.$regionStyle.'</styleUrl>'."\n";
				if( $placemark['begin'] || $placemark['end'] )
				{
					$kml .= '				<TimeSpan>'."\n";
					if( $placemark['begin'] )
					{
						$kml .= '			 	 <begin>'.esc(getXMLDateTime($placemark['begin'])).'</begin>'."\n";
					}
					if( $placemark['end'] )
					{
						$kml .= '				  <end>'.esc(getXMLDateTime($placemark['end'])).'</end>'."\n";
					}
					$kml .= '				</TimeSpan>'."\n";
				}
				$kml .= '				<Polygon>'."\n";
				$kml .= '					<outerBoundaryIs>'."\n";
				$kml .= '						<LinearRing>'."\n";
				$kml .= '							<tessellate>1</tessellate>'."\n";
				$kml .= '							<extrude>1</extrude>'."\n";
				$kml .= '							<coordinates>'.esc($placemark['coordinates']).'</coordinates>'."\n";
				$kml .= '						</LinearRing>'."\n";
				$kml .= '					</outerBoundaryIs>'."\n";
				$kml .= '				</Polygon>'."\n";
				$kml .= '			</Placemark>'."\n";
			}
		$kml .= '		</Folder>'."\n";
		}
		$kml .= '	</Folder>'."\n";
	}
	return $kml;
}

function validKmlPolyCoords($coords)
{
	$valid = false;
	$coordinates = preg_replace("/\s+/", " ", trim($coords));
	if( preg_match('/^(\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?)( (\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?))*$/', $coordinates) )
	{
		$valid = true;
	}
	return $valid;
}

function getKMLStyles()
{
	$kml = '';
	$kml .= '	<Style id="regionMarkerStyle">'."\n";
	$kml .= '		<IconStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<Icon><href>'.esc('http://'.eHOST.'/'.eROOT_DIR.'/orca/_images/region_marker.png').'</href></Icon>'."\n";
	$kml .= '			<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/>'."\n";
	$kml .= '		</IconStyle>'."\n";
	$kml .= '		<LabelStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<color>66FFFFFF</color>'."\n";
	$kml .= '			<colorMode>normal</colorMode>'."\n";
	$kml .= '		</LabelStyle>'."\n";	
	$kml .= '	</Style>'."\n";
	$kml .= '	<Style id="pointMarkerStyle">'."\n";
	$kml .= '		<IconStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<Icon><href>'.esc('http://'.eHOST.'/'.eROOT_DIR.'/orca/_images/point_marker.png').'</href></Icon>'."\n";
	$kml .= '			<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/>'."\n";
	$kml .= '		</IconStyle>'."\n";	
	$kml .= '		<LabelStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<color>66FFFFFF</color>'."\n";
	$kml .= '			<colorMode>normal</colorMode>'."\n";
	$kml .= '		</LabelStyle>'."\n";	
	$kml .= '	</Style>'."\n";
	$kml .= '	<Style id="regionStyle">'."\n";	
	$kml .= '		<LineStyle>'."\n";
	$kml .= '			<color>AA3B51FF</color>'."\n";
	$kml .= '			<width>1</width>'."\n";
	$kml .= '		</LineStyle>'."\n";	
	$kml .= '		<PolyStyle>'."\n";
	$kml .= '			<color>2F3B51FF</color>'."\n";
	$kml .= '			<colorMode>normal</colorMode>'."\n";
	$kml .= '		</PolyStyle>'."\n";
	$kml .= '	</Style>'."\n";
	$kml .= '	<Style id="regionMarkerStyle_2">'."\n";
	$kml .= '		<IconStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<Icon><href>'.esc('http://'.eHOST.'/'.eROOT_DIR.'/orca/_images/region_marker_2.png').'</href></Icon>'."\n";
	$kml .= '			<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/>'."\n";
	$kml .= '		</IconStyle>'."\n";
	$kml .= '		<LabelStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<color>66FFFFFF</color>'."\n";
	$kml .= '			<colorMode>normal</colorMode>'."\n";
	$kml .= '		</LabelStyle>'."\n";	
	$kml .= '	</Style>'."\n";
	$kml .= '	<Style id="pointMarkerStyle_2">'."\n";
	$kml .= '		<IconStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<Icon><href>'.esc('http://'.eHOST.'/'.eROOT_DIR.'/orca/_images/point_marker_2.png').'</href></Icon>'."\n";
	$kml .= '			<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/>'."\n";
	$kml .= '		</IconStyle>'."\n";	
	$kml .= '		<LabelStyle>'."\n";
	$kml .= '			<scale>1.0</scale>'."\n";
	$kml .= '			<color>66FFFFFF</color>'."\n";
	$kml .= '			<colorMode>normal</colorMode>'."\n";
	$kml .= '		</LabelStyle>'."\n";	
	$kml .= '	</Style>'."\n";
	$kml .= '	<Style id="regionStyle_2">'."\n";	
	$kml .= '		<LineStyle>'."\n";
	$kml .= '			<color>FF782878</color>'."\n";
	$kml .= '			<width>1</width>'."\n";
	$kml .= '		</LineStyle>'."\n";	
	$kml .= '		<PolyStyle>'."\n";
	$kml .= '			<color>2D782878</color>'."\n";
	$kml .= '			<colorMode>normal</colorMode>'."\n";
	$kml .= '		</PolyStyle>'."\n";
	$kml .= '	</Style>'."\n";
	return $kml;
}

function hasSpatialKMLData($registryObjectKey , $forType)
{
	$mappedCoverageTypes = array('iso19139dcmiBox', 'gmlKmlPolyCoords', 'kmlPolyCoords');
	if($forType == 'location')
	{
	$locationArray = getLocations($registryObjectKey);
	foreach( $locationArray as $location  )
		{
			if( $spatialArray = getSpatialLocations($location['location_id']) )
			{
				foreach( $spatialArray as $row )
				{					
					if( in_array($row['type'], $mappedCoverageTypes) )
					{
						return true;
					}
				}
			}
		}
	}	
	if($forType == 'coverage')
	{
	$coverageList = getCoverage($registryObjectKey);
	
	if( $coverageList )
	{
		foreach( $coverageList  as $coverage )
		{
			$spatialList = getSpatialCoverage($coverage['coverage_id']);
			if($spatialList)
			{			
				foreach( $spatialList as $spatialLocation )
				{
					if( in_array($spatialLocation['type'], $mappedCoverageTypes) )
					{
						return true;
					}
				}
			}
		}
	}
	}
	return false;
	
}

function addSolrIndex($registryObjectKey, $commit=true)
{
		$result = '';
		$rifcsContent = getRegistryObjectXMLforSOLR($registryObjectKey,true);

		$rifcsContent = wrapRegistryObjects($rifcsContent);
		$rifcs = transformToSolr($rifcsContent);									
		$result .= curl_post(gSOLR_UPDATE_URL, $rifcs);
		if($commit)
		{
			$result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
			$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
		}
		return $result;	
}

function addKeysToSolrIndex($keys, $commit=true)
{
		$result = '';
		$rifcs = '';

		foreach ($keys as $registryObjectKey)
		{
			$rifcs .= getRegistryObjectXMLforSOLR(rawurldecode($registryObjectKey),true);
		}			
		//print $rifcs;die();		
		$rifcs = wrapRegistryObjects($rifcs);
		$rifcs = transformToSolr($rifcs);		
		//print $rifcs;								
		$result .= curl_post(gSOLR_UPDATE_URL, $rifcs);
		if($commit)
		{
			$result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
			$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
		}
		return $result;	
}




function optimiseSolrIndex()
{
	$result = curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	return $result;	
}

function deleteSolrIndex($registryObjectkey)
{
	$result = curl_post(gSOLR_UPDATE_URL.'?commit=true', '<delete><id>'.$registryObjectkey.'</id></delete>');
	$result .= optimiseSolrIndex();
	return $result;		
}

function clearSolrIndex()
{
	$result = curl_post(gSOLR_UPDATE_URL.'?commit=true', '<delete><query>*:*</query></delete>');	
	$result .= curl_post(gSOLR_UPDATE_URL.'?commit=true', '<commit waitFlush="false" waitSearcher="false"/>');
	$result .= curl_post(gSOLR_UPDATE_URL.'?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
	return $result;	
}

function curl_post($url, $post) 
{ 

        $header = array("Content-type:text/xml; charset=utf-8");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $data = curl_exec($ch);
		return $data;
		/*
        if (curl_errno($ch)) {
           print "curl_error:" . curl_error($ch);
        } else {
           curl_close($ch);
           print "curl exited okay\n";
           echo "Data returned...\n";
           echo "------------------------------------\n";
           echo $data;
           echo "------------------------------------\n";
        } */
} 
function changeFromCamelCase($camelCaseString)
{
	$output = '';
	
	$output = preg_replace('/([A-Z])/', ' $1', $camelCaseString);
	$output = strtolower($output);
	$output = substr_replace($output, substr(strtoupper($output), 0, 1), 0, 1);
	
	return $output;
}
function send_email($to, $subject, $message, $headers='')
{
	$headers .= 'From: "ANDS Services" <services@ands.org.au>' . "\r\n" .
	    'Reply-To: "ANDS Services" <services@ands.org.au>' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();
	
	@mail($to, $subject, $message, $headers);
}

/*
function DIEDIEDIEgetSpatialTypesXMLforSOLR($location_id)
{
	$xml = '';
	$list = getSpatialLocations($location_id);
	$centre = '';
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}

			if($element['type'] == 'iso19139dcmiBox')
			{
				$valueString = strtolower(esc($element['value'])).';';
				$matches = array();
				preg_match('/northlimit=([^;]*);/i', $valueString, $matches);
				$north = (float)$matches[1];
				preg_match('/southlimit=([^;]*);/i', $valueString, $matches);
				$south = (float)$matches[1];
				preg_match('/westlimit=([^;]*);/i', $valueString, $matches);
				$west = (float)$matches[1];
				preg_match('/eastlimit=([^;]*);/i', $valueString, $matches);
				$east = (float)$matches[1];	
				$coordinates = "$west,$north $east,$north $east,$south $west,$south $west,$north";		
				$centre = (($east+$west)/2).','.(($north+$south)/2);
				$xml .= "        <spatial>$west,$north $east,$north $east,$south $west,$south $west,$north</spatial>\n";
				
			}
			else if($element['type'] ==  'gmlKmlPolyCoords' || $element['type'] == 'kmlPolyCoords')
			{
				$coordinates = trim(esc($element['value']));
				$coordinates = preg_replace("/\s+/", " ", $coordinates);
				
				if( validKmlPolyCoords($coordinates) )
				{
					// Build the coordinates string for the centre.
					$points = explode(' ', $coordinates);
					if( count($points) > 0 )
					{
						$north = -90.0;
						$south = 90.0;
						$west = 180.0;
						$east = -180.0;
						foreach( $points as $point )
						{
							$P = explode(',', $point); // lon,lat
							if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
							if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
							if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
							if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
						}
					}
					$centre = (($east+$west)/2).','.(($north+$south)/2);
				    $xml .= "        <spatial>$coordinates</spatial>\n";
					
				}
			}
	        if($centre != '')
	        {
	        	$xml .= "        <center>$centre</center>\n";

	        }			
			
			
			
		}
	}
	return $xml;
}*/

/*
function DIEDIEDIEgetComplexNameTypesXMLforSOLR($registryObjectKey, $elementName, $registryObjectClass)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getComplexNames($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if($element['type'] == 'alternative')
			{
				$xml .= "      <$elementName$type>\n";
				$xml .= getNamePartsXMLforSOLR($element['complex_name_id'], $registryObjectClass);
				$xml .= "      </$elementName>\n";
			}
		}
	}
	return $xml;
}*/


/*
function getLocationTypesXMLforSOLR($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getLocations($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $dateFrom = $element['date_from'] )
			{
				$dateFrom = ' dateFrom="'.formatDateTime($dateFrom, gDATE).'"';
			}
			if( $dateTo = $element['date_to'] )
			{
				$dateTo = ' dateTo="'.formatDateTime($dateTo, gDATE).'"';
			}
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			$xml .= "      <$elementName$dateFrom$dateTo$type>\n";
			$xml .= getAddressXMLforSOLR($element['location_id']);
			$xml .= getSpatialTypesXMLforSOLR($element['location_id']);
			$xml .= "      </$elementName>\n";
		}
	}
	return $xml;
}*/


/*
function getCoverageTypesXMLforSOLR($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getCoverage($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			$xml .= "      <$elementName>\n";
			$xml .= getSpatialCoverageXMLforSOLR($element['coverage_id']);
			$xml .= getTemporalCoverageXMLforSOLR($element['coverage_id']);
			$xml .= "      </$elementName>\n";
		}
	}
	return $xml;
}
*/


/*
function getSpatialCoverageXMLforSOLR($coverage_id)
{
	$xml = '';
	$centre = '';
	$list = getSpatialCoverage($coverage_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
						
			if($element['type'] == 'iso19139dcmiBox')
			{
				$valueString =  strtolower(esc($element['value'])).';';
				$matches = array();
				preg_match('/northlimit=([^;]*);/i', $valueString, $matches);
				$north = (float)$matches[1];
				preg_match('/southlimit=([^;]*);/i', $valueString, $matches);
				$south = (float)$matches[1];
				preg_match('/westlimit=([^;]*);/i', $valueString, $matches);
				$west = (float)$matches[1];
				preg_match('/eastlimit=([^;]*);/i', $valueString, $matches);
				$east = (float)$matches[1];	
				$coordinates = "$west,$north $east,$north $east,$south $west,$south $west,$north";		
				$centre = (($east+$west)/2).','.(($north+$south)/2);
				$xml .= "        <spatial>$west,$north $east,$north $east,$south $west,$south $west,$north</spatial>\n";
			}
			else if($element['type'] ==  'gmlKmlPolyCoords' || $element['type'] == 'kmlPolyCoords')
			{
				$coordinates = trim(esc($element['value']));
				$coordinates = preg_replace("/\s+/", " ", $coordinates);
				
				if( validKmlPolyCoords($coordinates) )
				{
					// Build the coordinates string for the centre.
					$points = explode(' ', $coordinates);
					if( count($points) > 0 )
					{
						$north = -90.0;
						$south = 90.0;
						$west = 180.0;
						$east = -180.0;
						foreach( $points as $point )
						{
							$P = explode(',', $point); // lon,lat
							if( (float)$P[0] >= $east ){ $east = (float)$P[0]; }
							if( (float)$P[0] <= $west ){ $west = (float)$P[0]; }
							if( (float)$P[1] >= $north ){ $north = (float)$P[1]; }
							if( (float)$P[1] <= $south ){ $south = (float)$P[1]; }
						}
					}
					$centre = (($east+$west)/2).','.(($north+$south)/2);
					$xml .= "        <spatial>$coordinates</spatial>\n";
				}
			}
	        if($centre != '')
	        {
	        	$xml .= "        <center>$centre</center>\n";
	        }			
		}
	}
	return $xml;
}*/


/*
function getTemporalCoverageXMLforSOLR($coverage_id)
{
	$xml = '';
	$list = getTemporalCoverage($coverage_id);
	

	if($list)
	{
		foreach( $list as $element )
		{
			$dateArray = getTemporalCoverageDate($element['temporal_coverage_id']);
			if($dateArray)
			{
				$xml .= '<temporal>';
				asort($dateArray);
				foreach( $dateArray as $row )
				{
					$type = ' type="'.esc($row['type']).'"';	
					$dateFormat = ' dateFormat="'.esc($row['date_format']).'"';
					$value = FormatDateTime(esc($row['value']), gDATE);
					$xml .= "            <date$type$dateFormat>$value</date>\n";
				}
				$xml .= '</temporal>';	
			}
		}	
	}
	return $xml;
}
*/


/*function getAddressXMLforSOLR($location_id)
{
	$xml = '';
	$list = getAddressLocations($location_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			$xml .= "        <address>\n";
			$xml .= getElectronicAddressTypesXML($element['address_id']);
			$xml .= getPhysicalAddressTypesXMLforSOLR($element['address_id']);
			$xml .= "        </address>\n";
		}
	}
	return $xml;
}*/


/*function getPhysicalAddressTypesXMLforSOLR($address_id)
{
	$xml = '';
	$list = getPhysicalAddresses($address_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			$xml .= "          <physical$type$lang>\n";
			$xml .= getAddressPartsXMLforSOLR($element['physical_address_id']);	
			$xml .= "          </physical>\n";
		}	
	}
	return $xml;
}
*/
/*function DIEDIEDIEgetAddressPartsXMLforSOLR($physical_address_id)
{
	$xml = '';
	$list = getAddressParts($physical_address_id);
	if( $list )
	{
		asort($list);
		foreach( $list as $element )
		{			
			if( $type = $element['type'] )
			{
				$type = ' type="'.strtolower(esc($type)).'"';
			}
			$value = ($element['value']);
			$value = htmlspecialchars_decode($value);
			$value = purify($value);
			$value = htmlspecialchars($value);
			$xml .= "            <addressPart$type>$value</addressPart>\n";
		}		
	}
	return $xml;
}*/



/*function DIEDIEDIEgetRelatedObjectTypesXMLforSolr($registryObjectKey,$registryObjectClass, $dataSourceKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$datasource = null;
	$dataSource = getDataSources($dataSourceKey, null);	
	$create_primary_relationships = $dataSource[0]['create_primary_relationships'];
	$typeArray['collection'] = array(
		"describes" => "Describes",
		"hasAssociationWith" => "Associated with",
		"hasCollector" => "Aggregated by",
		"hasPart" => "Contains",
		"isDescribedBy" => "Described by",
		"isLocatedIn" => "Located in",
		"isLocationFor" => "Location for",
		"isManagedBy" => "Managed by",
		"isOutputOf" => "Output of",
		"isOwnedBy" => "Owned by",
		"isPartOf" => "Part of",
		"supports" => "Supports"
	);
	$typeArray['party'] = array(
		"hasAssociationWith" => "Associated with",
		"hasMember" => "Has member",
		"hasPart" => "Has part",
		"isCollectorOf" => "Collector of",
		"isFundedBy" => "Funded by",
		"isFunderOf" => "Funds",
		"isManagedBy" => "Managed by",
		"isManagerOf" => "Manages",
		"isMemberOf" => "Member of",
		"isOwnedBy" => "Owned by",
		"isOwnerOf" => "Owner of",
		"isParticipantIn" => "Participant in",
		"isPartOf" => "Part of",
	);
	$typeArray['service'] = array(
		"hasAssociationWith" => "Associated with",
		"hasPart" => "Includes",
		"isManagedBy" => "Managed by",
		"isOwnedBy" => "Owned by",
		"isPartOf" => "Part of",
		"isSupportedBy" => "Supported by",
	);
	$typeArray['activity'] = array(
		"hasAssociationWith" => "Associated with",
		"hasOutput" => "Produces",
		"hasPart" => "Includes",
		"hasParticipant" => "Undertaken by",
		"isFundedBy" => "Funded by",
		"isManagedBy" => "Managed by",
		"isOwnedBy" => "Owned by",
		"isPartOf" => "Part of",
	);	

	//we need to check if this datasource has primary relationships set up.
	$pkey1 = '';
	$pkey2 = '';
	if($create_primary_relationships == 't'||$create_primary_relationships == '1')
		{
			$primary_key_1 =  $dataSource[0]['primary_key_1'];
			$primary_key_2 =  $dataSource[0]['primary_key_2'];
			if($primary_key_1!='' && $primary_key_1!=$registryObjectKey)
			{

				$pkey1 = esc($primary_key_1);
				$relatedObject = getRegistryObject($pkey1,true);
				$relatedclass= strtolower($relatedObject[0]['registry_object_class']);	
			
				$relation_logo = false;
				if($typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1']])
				{
					$type = ' type="'.$typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1']].'"';
				}else{
					$type = ' type="'.$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_1'].'"';
				}
				if (isset($row) &&	$relatedObject[0]['registry_object_class'] == 'Party' && strtolower($relatedObject[0]['type']) != 'person') 
				{
					$relation_logo = getDescriptionLogo($key);
				}		
				
				$xml .= "      <$elementName>\n";
				$xml .= "        <key>$pkey1</key>\n";				
				$xml .= "		 <relatedObjectClass>".strtolower($relatedObject[0]['registry_object_class'])."</relatedObjectClass>";
				$xml .= "		 <relatedObjectType>".strtolower($relatedObject[0]['type'])."</relatedObjectType>";
				$xml .= "		 <relatedObjectListTitle>".esc($relatedObject[0]['list_title'])."</relatedObjectListTitle>";
				$xml .= "		 <relatedObjectDisplayTitle>".esc($relatedObject[0]['display_title'])."</relatedObjectDisplayTitle>";
				if($relation_logo) $xml .= "		 <relatedObjectLogo>".esc($relation_logo)."</relatedObjectLogo>";					
				$xml .=   "<relation$type>\n</relation>";
				$xml .= "      </$elementName>\n";			
					
			}
			if($primary_key_2!='' && $primary_key_2!=$registryObjectKey)
			{

				$pkey2 = esc($primary_key_2);
				$relatedObject = getRegistryObject($pkey2,true);
				$relatedclass= strtolower($relatedObject[0]['registry_object_class']);	
			
				$relation_logo = false;
				if($typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_2']])
				{
					$type = ' type="'.$typeArray[$relatedclass][$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_2']].'"';
				}else{
					$type = ' type="'.$dataSource[0][strtolower($relatedObject[0]['registry_object_class']).'_rel_2'].'"';
				}
				if ($relatedObject[0]['registry_object_class'] == 'Party' && strtolower($relatedObject[0]['type']) != 'person') 
				{
					$relation_logo = getDescriptionLogo($key);
				}		
				
				$xml .= "      <$elementName>\n";
				$xml .= "        <key>$pkey2</key>\n";				
				$xml .= "		 <relatedObjectClass>".strtolower($relatedObject[0]['registry_object_class'])."</relatedObjectClass>";
				$xml .= "		 <relatedObjectType>".strtolower($relatedObject[0]['type'])."</relatedObjectType>";
				$xml .= "		 <relatedObjectListTitle>".esc($relatedObject[0]['list_title'])."</relatedObjectListTitle>";
				$xml .= "		 <relatedObjectDisplayTitle>".esc($relatedObject[0]['display_title'])."</relatedObjectDisplayTitle>";
				if($relation_logo) $xml .= "		 <relatedObjectLogo>".esc($relation_logo)."</relatedObjectLogo>";					
				$xml .=   "<relation$type>\n</relation>";
				$xml .= "      </$elementName>\n";								
			}			
			
		}	
	$list = getRelatedObjects($registryObjectKey);

	if( $list )
	{
		foreach( $list as $element )
		{
			$key = esc($element['related_registry_object_key']);
			if($key!=$pkey1 && $key!=$pkey2)
			{
				$relatedObject = getRegistryObject($element['related_registry_object_key'],true);
				$relation_logo = false;
				$relationType = getRelationType($element['relation_id']);
				if (isset($element) &&	$relatedObject[0]['registry_object_class'] == 'Party' && strtolower($relatedObject[0]['type']) != 'person' ) 
				{
					$relation_logo = getDescriptionLogo($key);
				}		
				$relatedclass= strtolower($relatedObject[0]['registry_object_class']);
	
				$xml .= "      <$elementName>\n";
				$xml .= "        <key>$key</key>\n";
				$xml .= "		 <relatedObjectClass>".strtolower($relatedObject[0]['registry_object_class'])."</relatedObjectClass>";
				$xml .= "		 <relatedObjectType>".strtolower($relatedObject[0]['type'])."</relatedObjectType>";
				$xml .= "		 <relatedObjectListTitle>".esc($relatedObject[0]['list_title'])."</relatedObjectListTitle>";
				$xml .= "		 <relatedObjectDisplayTitle>".esc($relatedObject[0]['display_title'])."</relatedObjectDisplayTitle>";
				if($relation_logo) $xml .= "		 <relatedObjectLogo>".esc($relation_logo)."</relatedObjectLogo>";
				$xml .= getRelationsXMLSOLR($element['relation_id'],$typeArray[$registryObjectClass]);
				$xml .= "      </$elementName>\n";
			}
		}
	}
	return $xml;
	
	
}
*/


/*
function DIEDIEDIEgetRelationsXMLSOLR($relation_id,$typeArray)
{
	$xml = '';
	$list = getRelationDescriptions($relation_id);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				if( array_key_exists($type, $typeArray) )
				{
					$type = ' type="'.$typeArray[$type].'"';
				}
				else
				{
					$type = ' type="'.changeFromCamelCase($type).'"';
				}
				
			}
			if( $description = $element['description'] )
			{
				if( $lang = $element['lang'] )
				{
					$lang = ' xml:lang="'.esc($lang).'"';
				}
				$description = "          <description$lang>".esc($element['description'])."</description>\n";
			}
			if( $url = $element['url'] )
			{
				$url = "          <url>".esc($element['url'])."</url>\n";
			}
			$xml .= "        <relation$type>\n$description$url        </relation>\n";
		}
	}
	return $xml;
}*/

/*function DIEDIEDIEgetSubjectTypesXMLforSOLR($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$resolvedName = '';
	$list = getSubjects($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			//var_dump($element['type']);
			$value = esc(trim($element['value']));
			$resolvedName = '';
			if(($value != '') && (strlen($value) < 7) && is_numeric($value))
			{
				$valueLength = strlen($value);
				if($valueLength < 6){
					for($i = 0; $i < (6 - $valueLength) ; $i++){
						$value .= '0';
					}				
				}
				$resolvedName = getTermsForVocabByIdentifier(null, $value);
			}
			if($resolvedName && $resolvedName[0]['name'] != '')
			{
				$term = $resolvedName[0]['name'];
			}
			else 
			{
				$term = $value;
			}
			$type = ' type="'.esc($element['type']).'"';
			$xml .= "      <$elementName$type>$term</$elementName>\n";
		}
	}
	return $xml;
}*/
/*
function getDescriptionTypesXMLforSOLR($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getDescriptions($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] )
			{
				$type = ' type="'.esc($type).'"';
			}
			if( $lang = $element['lang'] )
			{
				$lang = ' xml:lang="'.esc($lang).'"';
			}
			$value = (trim($element['value']));
			
			$value = htmlspecialchars_decode($value);
			$value = purify($value);
			$value = htmlspecialchars($value);
			
			$xml .= "      <$elementName$type$lang>$value</$elementName>\n";
		}
	}
	return $xml;
}
*/

/*
function getRightsTypesXMLforSOLR($registryObjectKey, $elementName)
{
	$xml = '';
	$elementName = esc($elementName);
	$list = getDescriptions($registryObjectKey);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['type'] && ($element['type']=='rights' || $element['type']=='accessRights'))
			{
				$type = ' type="'.esc($element['type']).'"';
		
			$test = $element['type'];
			$value = esc($element['value']);
			$xml .= "      <$elementName$type>$value</$elementName>\n";
			$type = '';
			}
		}
	}
	$list = getRights($registryObjectKey);
	//echo $registryObjectKey;
	//print_r($list);
	if( $list )
	{
		foreach( $list as $element )
		{
			if( $type = $element['access_rights'] || $type = $element['access_rights_uri'])
			{
				$type = ' type="accessRights"';
				if($uri = $element['access_rights_uri'])
				{
					$uri = ' rightsUri = "'.esc($uri).'"';
				}
				$value = esc($element['access_rights']);
				$xml .= "      <$elementName$type$uri>$value</$elementName>\n";				
			}
			
			if( $type = $element['rights_statement'] || $type = $element['rights_statement_uri'])
			{
				$type = ' type="rights"';
				if($uri = $element['rights_statement_uri'])
				{
					$uri = ' rightsUri = "'.esc($uri).'"';
				}
				$value = esc($element['rights_statement']);
				$xml .= "      <$elementName$type$uri>$value</$elementName>\n";								
			}
			
			if( $type = $element['licence'] || $type = $element['licence_uri'])
			{
				$type = ' type="licence"';
				if($uri = $element['licence_uri'])
				{
					$uri = ' rightsUri = "'.esc($uri).'"';
				}
				$value = esc($element['licence']);
				$xml .= "      <$elementName$type$uri>$value</$elementName>\n";										
			}			
		}
	}	
	return $xml;			
}
*/

/*function DIEDIEDIEgetExistenceDateTypesXMLSolr($registryObjectKey, $elementName)
{
	$xml = '';
	$startdate = '';
	$enddate = '';
	$elementName = esc($elementName);
	$list = getExistenceDate($registryObjectKey);
	if( $list )
	{	
		foreach( $list as $element )
		{
			$xml .=	"		<$elementName>";
			if($startdate = $element['start_date'])
			{
				$startdate1 = FormatDateTime(esc($startdate), gDATE);
				//echo $startdate;
				if($startDateFormat = $element['start_date_format'])
				{
					$dateFormat = ' dateFormat="'.$startDateFormat.'"';
				}
				$xml .= "			<startDate$dateFormat>$startdate1</startDate>";
			}
			if($enddate = $element['end_date'])
			{
				$enddate1 = FormatDateTime(esc($enddate), gDATE);				
				if($startDateFormat = $element['end_date_format'])
				{
					$dateFormat = ' dateFormat="'.$startDateFormat.'"';
				}
				$xml .= "			<endDate$dateFormat>$enddate1</endDate>";
			}			
			$xml .= "      </$elementName>\n";
		}
	}
	return $xml;
}*/


?>
