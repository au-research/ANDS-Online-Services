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



$gXPath = null; // An XPATH object to use for parsing the XML.
$xs = 'rif';    // The default namespace prefix to register for use by XPATH.
$dataSourceKey = '';
function importRegistryObjects($registryObjects, $dataSourceKey, &$runResultMessage, $created_who=SYSTEM, $status=PUBLISHED, $record_owner=SYSTEM, $xPath=NULL, $override_qa=false)
{
	global $gXPath;
	global $xs;
	global $relatedObjectClassesStr;
	// If using a custom XPath pointer, save the gXPath
	if ($xPath !== NULL)
	{
		$tempXPath = $gXPath;
		$gXPath = $xPath;
	}
	
	$totalElements = 0;
	$totalRegistryObjectElements = 0;

	$recordsCached = 0;
	$totalRegistryObjectInserts = 0;
	$totalRegistryObjectDeletes = 0;
	$totalRegistryObjectChanges = 0;
	$totalAttemptedInserts = 0;
	$totalInserts = 0;
	
	$runErrors = '';
	$errors = null;
	if($dataSourceKey == 'PUBLISH_MY_DATA')
	{
		$qaFlag = 't';
		$manuallyPublish = 'f';
	}
	else
	{
		$dataSource = getDataSources($dataSourceKey, null);
		$manuallyPublish = $dataSource[0]['auto_publish'];
		$qaFlag = $dataSource[0]['qa_flag'];
	}
		
	if ($override_qa)
	{ 
		$qaFlag = 'f';
	}
	if($manuallyPublish == 'f')
	{
		$status = PUBLISHED;
	}
	else
	{
		$status = APPROVED;
	}
	
	// Get an xpath object to use for parsing the XML.
	$gXPath = new DOMXpath($registryObjects);
	// Get the default namespace of the registryObjects object.
	$defaultNamespace = $gXPath->evaluate('/*')->item(0)->namespaceURI;
	// Register a prefix for the default namespace so that we can actually use the xpath object.
	$gXPath->registerNamespace($xs, $defaultNamespace);
	
	$totalElements = $gXPath->evaluate("//*")->length;

	// Registry Objects
	// =========================================================================
	$registryObjectList = $gXPath->evaluate("$xs:registryObject");
	$totalRegistryObjectElements = $registryObjectList->length;
	for( $i=0; $i < $registryObjectList->length; $i++ )
	{
		// Registry Object
		// =====================================================================

		$registryObject = $registryObjectList->item($i);
		$deleted = true;
		// Registry Object Key
		// =====================================================================
		$registryObjectKey = substr($gXPath->evaluate("$xs:key", $registryObject)->item(0)->nodeValue, 0, 512);
		
		if( $registryObjectKey )
		{
			$currentUrlSlug = getRegistryObjectURLSlug($registryObjectKey);
	
			// Check if this object exists already, and delete it if it does.

			if( $oldRegistryObject = getRegistryObject($registryObjectKey) )
			{
				// Delete this object and all associated records from the registry (if qaflag is true, don't delete existing one

				if($dataSourceKey == $oldRegistryObject[0]['data_source_key'])
				{
					if ($qaFlag != 't')
					{
						$errors = deleteRegistryObject($registryObjectKey);
						if( !$errors ) 
						{ 
							$totalRegistryObjectDeletes++;         						
						} 
						else 
						{ 
							$runErrors .= "Failed to delete Registry Object with key $registryObjectKey\n"; 
						}
					}
				}
				else
				{ 
					$deleted = false;
					$runErrors .= "Registry Object with key $registryObjectKey already exists in a different datasource\n"; 
				}
				
				/*
				 * Check for previous revisions, compare equality and add a new revision if appropriate
				 */

				$previousRegistryObjects = getRawRecords($registryObjectKey,$dataSourceKey, NULL);

				//$previousRegistryObjects = null;
				//return $previousRegistryObjects;
				// Check if this object exists already, and delete it if it does.
				if( $previousRegistryObjects && count ($previousRegistryObjects) > 0)
				{
					// Check if the object has changed since its last import
					$currentRecordFragment = $registryObject->ownerDocument->saveXML($registryObject);
					//print $currentRecordFragment;die();
					// Get the most recent record fragment
					$previousRecordFragment = @array_pop($previousRegistryObjects);
					if ($previousRecordFragment === NULL)
					{
						$runErrors .= "Failed to find comparable Raw Record Fragment for $registryObjectKey\n"; 
					} 
					else 
					{
						
						// Wrap registryObject in the XML wrappers (this will cause records to mismatch if gRIF_SCHEMA_URI is changed
						// in orca/_includes/init.php
						if (!compareLooseXMLEquivalent($currentRecordFragment, $previousRecordFragment['rifcs_fragment']))
						{
							insertRawRecord($registryObjectKey, $dataSourceKey, date('Y-m-d H:i:s'), $created_who, $currentRecordFragment);
							$totalRegistryObjectChanges++;
						}
					}
				}
			}
			else 
			{
				$currentRecordFragment = $registryObject->ownerDocument->saveXML($registryObject);
				insertRawRecord($registryObjectKey, $dataSourceKey, date('Y-m-d H:i:s'), $created_who, $currentRecordFragment);
			}
			
			// Registry Object Originating Source
			// =====================================================================
			$originatingSource = $gXPath->evaluate("$xs:originatingSource", $registryObject)->item(0);
			$originatingSourceValue = $originatingSource->nodeValue;
			$originatingSourceType = $originatingSource->getAttribute("type");
			
			
			
			// We're all set to insert the new/replacement registry object.
			// Registry Object
			// =====================================================================
			$object_group = $registryObject->getAttribute("group");
			
		
			// Activity
			// =====================================================================
			
			if($qaFlag == 't')
			{
						
				if($activity = $gXPath->evaluate("$xs:activity", $registryObject)->item(0))
				{
				 	$draft_type = $activity->getAttribute("type");
				 	//$date_modified = $activity->getAttribute("dateModified");
				 	$eClass = 'activity';
				 	$draft_class = 'Activity';
				}
				else if($collection = $gXPath->evaluate("$xs:collection", $registryObject)->item(0))
				{
					$draft_type= $collection->getAttribute("type");
					//$date_modified = $collection->getAttribute("dateModified");	
					$eClass = 'collection';
					$draft_class = 'Collection';
				}
				else if($party = $gXPath->evaluate("$xs:party", $registryObject)->item(0))
				{
					$draft_type = $party->getAttribute("type");
					//$date_modified = $party->getAttribute("dateModified");
					$eClass = 'party';
					$draft_class = 'Party';
				}
				else if($service = $gXPath->evaluate("$xs:service", $registryObject)->item(0))
				{
					$draft_type = $service->getAttribute("type");
					//$date_modified = $service->getAttribute("dateModified");
					$eClass = 'service';
					$draft_class = 'Service';
				}
				$date_modified = date('Y-m-d H:i:s');
				$title = '';
				$possibleNames = null;
				$possibleNames = $gXPath->evaluate("$xs:$eClass/$xs:name[@type='primary']", $registryObject);
				if ($possibleNames->length > 0)
				{
	
					$parts = $gXPath->evaluate("$xs:$eClass/$xs:name[@type='primary']/$xs:namePart", $registryObject);
					if ($parts->length > 0)
					{
						$title = "";
						for($k=0; $k<$parts->length; $k++)
						{
							$title .= $parts->item($k)->nodeValue . " ";
						}
						$title = trim($title);
					}
				}
				else 
				{
					$possibleNames = $gXPath->evaluate("$xs:$eClass/$xs:name/$xs:namePart", $registryObject);
					
					if ($possibleNames->length > 0)
					{ 
						$title = "";
						for($k=0; $k<$possibleNames->length; $k++)
						{
							$title .= $possibleNames->item($k)->nodeValue . " ";
						}
						$title = trim($title);
					}
				}
				
				if (strlen($title) === 0)
				{
					$title = '(no name/title)';
				}
				
				$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
				$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
				$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
				$rifcs .= $registryObjects->saveXML($registryObject);
				$rifcs .= '</registryObjects>';

				if ($dataSourceKey != 'PUBLISH_MY_DATA' && getDraftCountByStatus($dataSourceKey, SUBMITTED_FOR_ASSESSMENT) == 0)
				{
					send_email(
						$dataSource[0]['assessement_notification_email_addr'],
						"Records from " . $dataSource[0]['title'] . " are ready for your assessment",
						$dataSource[0]['title'] . " has submitted " . count($totalRegistryObjectElements) . " record(s) for your assessment by Harvest. \n\n" .
						"Your action is required to review these records by visiting the Manage My Records screen or accessing the Data Source directly by the following link:\n" .
						eHTTP_APP_ROOT . "orca/manage/my_records.php?data_source=" . $dataSourceKey . "\n\n"
					);
				}
				$runResultMessage .=  insertDraftRegistryObject(($dataSourceKey == 'PUBLISH_MY_DATA' ? $record_owner : $created_who), $registryObjectKey, $draft_class, $object_group, $draft_type, $title, $dataSourceKey, date('Y-m-d H:i:s'), $date_modified , $rifcs, '', 0, 0, SUBMITTED_FOR_ASSESSMENT);
				$runResultMessage  .= "\nRegistry Object with key $registryObjectKey is SUBMITTED_FOR_ASSESSMENT"; 
			}
			else
			{
				if($deleted && !$errors && $activity = $gXPath->evaluate("$xs:activity", $registryObject)->item(0) )
				{
					$activityType = $activity->getAttribute("type");
					$date_modified = $activity->getAttribute("dateModified");
					
					$errors = insertRegistryObject($registryObjectKey, 'Activity', $activityType, $originatingSourceValue, $originatingSourceType, $dataSourceKey, $object_group, null, $date_modified, $created_who, $status, $record_owner);
					$totalAttemptedInserts++;				
					if( !$errors ) { $totalRegistryObjectInserts++; $totalInserts++; } else { $runErrors .= "Failed to insert Activity with key $registryObjectKey\n"; }
					
					// identifier
					// -----------------------------------------------------------------
					importIdentifierTypes($registryObjectKey, $activity, "identifier", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// name
					// -----------------------------------------------------------------
					importComplexNameTypes($registryObjectKey, $activity, "name", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// location
					// -----------------------------------------------------------------
					importLocations($registryObjectKey, $activity, "location", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedObject
					// -----------------------------------------------------------------
					importRelatedObjectTypes($registryObjectKey, $activity, "relatedObject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// subject
					// -----------------------------------------------------------------
					importSubjectTypes($registryObjectKey, $activity, "subject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// description
					// -----------------------------------------------------------------
					importDescriptionTypes($registryObjectKey, $activity, "description", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	
					// coverage
					// -----------------------------------------------------------------
					importCoverage($registryObjectKey, $activity, "coverage", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	
					// citationInfo
					// -----------------------------------------------------------------
					importCitationInfo($registryObjectKey, $activity, "citationInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// rights
					// -----------------------------------------------------------------
					importRights($registryObjectKey, $activity, "rights", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// existenceDates
					// -----------------------------------------------------------------
					importExistenceDates($registryObjectKey, $activity, "existenceDates", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedInfo
					// -----------------------------------------------------------------
					importRelatedInfo($registryObjectKey, $activity, "relatedInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
				} // Activity
				
				// Collection
				// =====================================================================
				if($deleted && !$errors && $collection = $gXPath->evaluate("$xs:collection", $registryObject)->item(0) )
				{
					$collectionType = $collection->getAttribute("type");
					
					$date_accessioned = $collection->getAttribute("dateAccessioned");
					$date_modified = $collection->getAttribute("dateModified");
					
					$errors = insertRegistryObject($registryObjectKey, 'Collection', $collectionType, $originatingSourceValue, $originatingSourceType, $dataSourceKey, $object_group, $date_accessioned, $date_modified, $created_who, $status, $record_owner);
					$totalAttemptedInserts++;				
					if( !$errors ) { $totalRegistryObjectInserts++; $totalInserts++; } else { $runErrors .= "Failed to insert Collection with key $registryObjectKey\n(aaa".$errors; }
					
					// identifier
					// -----------------------------------------------------------------
					importIdentifierTypes($registryObjectKey, $collection, "identifier", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// name
					// -----------------------------------------------------------------
					importComplexNameTypes($registryObjectKey, $collection, "name", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// location
					// -----------------------------------------------------------------
					importLocations($registryObjectKey, $collection, "location", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedObject
					// -----------------------------------------------------------------
					importRelatedObjectTypes($registryObjectKey, $collection, "relatedObject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// subject
					// -----------------------------------------------------------------
					importSubjectTypes($registryObjectKey, $collection, "subject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// description
					// -----------------------------------------------------------------
					importDescriptionTypes($registryObjectKey, $collection, "description", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	
					// coverage
					// -----------------------------------------------------------------
					importCoverage($registryObjectKey, $collection, "coverage", &$runErrors, &$totalAttemptedInserts, &$totalInserts);	
					
					// citationInfo
					// -----------------------------------------------------------------
					importCitationInfo($registryObjectKey, $collection, "citationInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// rights
					// -----------------------------------------------------------------
					importRights($registryObjectKey, $collection, "rights", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// existenceDates
					// -----------------------------------------------------------------
					importExistenceDates($registryObjectKey, $collection, "existenceDates", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					
					// relatedInfo
					// -----------------------------------------------------------------
					importRelatedInfo($registryObjectKey, $collection, "relatedInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
				} // Collection
				
				// Party
				// =====================================================================
				if($deleted && !$errors && $party = $gXPath->evaluate("$xs:party", $registryObject)->item(0) )
				{
					$partyType = $party->getAttribute("type");
					$date_modified = $party->getAttribute("dateModified");
					
					//echo $registryObjectKey.' Party '.$partyType.' '.$originatingSourceValue.' '.$originatingSourceType.' '.$dataSourceKey.' '.$object_group.' '.$date_modified.' '.$created_who.' '.$status.' '.$record_owner;
					$errors = insertRegistryObject($registryObjectKey, 'Party', $partyType, $originatingSourceValue, $originatingSourceType, $dataSourceKey, $object_group, null, $date_modified, $created_who, $status, $record_owner);
					//echo $errors;
					//exit;
					$totalAttemptedInserts++;				
					if( !$errors ) { $totalRegistryObjectInserts++; $totalInserts++; } else { $runErrors .= "Failed to insert Party with key $registryObjectKey\n"; }
					
					// identifier
					// -----------------------------------------------------------------
					importIdentifierTypes($registryObjectKey, $party, "identifier", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// name
					// -----------------------------------------------------------------
					importComplexNameTypes($registryObjectKey, $party, "name", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// location
					// -----------------------------------------------------------------
					importLocations($registryObjectKey, $party, "location", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedObject
					// -----------------------------------------------------------------
					importRelatedObjectTypes($registryObjectKey, $party, "relatedObject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// subject
					// -----------------------------------------------------------------
					importSubjectTypes($registryObjectKey, $party, "subject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// description
					// -----------------------------------------------------------------
					importDescriptionTypes($registryObjectKey, $party, "description", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	
					// coverage
					// -----------------------------------------------------------------
					importCoverage($registryObjectKey, $party, "coverage", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// citationInfo
					// -----------------------------------------------------------------
					importCitationInfo($registryObjectKey, $party, "citationInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);

					// rights
					// -----------------------------------------------------------------
					importRights($registryObjectKey, $party, "rights", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// existenceDates
					// -----------------------------------------------------------------
					importExistenceDates($registryObjectKey, $party, "existenceDates", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedInfo
					// -----------------------------------------------------------------
					importRelatedInfo($registryObjectKey, $party, "relatedInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
				} // Party
				
				// Service
				// =====================================================================
				if($deleted && !$errors && $service = $gXPath->evaluate("$xs:service", $registryObject)->item(0) )
				{
					$serviceType = $service->getAttribute("type");
					$date_modified = $service->getAttribute("dateModified");
					
					$errors = insertRegistryObject($registryObjectKey, 'Service', $serviceType, $originatingSourceValue, $originatingSourceType, $dataSourceKey, $object_group, null, $date_modified, $created_who, $status, $record_owner);
					$totalAttemptedInserts++;				
					if( !$errors ) { $totalRegistryObjectInserts++; $totalInserts++; } else { $runErrors .= "Failed to insert Service with key $registryObjectKey\n"; }
					
					// identifier
					// -----------------------------------------------------------------
					importIdentifierTypes($registryObjectKey, $service, "identifier", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// name
					// -----------------------------------------------------------------
					importComplexNameTypes($registryObjectKey, $service, "name", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// location
					// -----------------------------------------------------------------
					importLocations($registryObjectKey, $service, "location", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedObject
					// -----------------------------------------------------------------
					importRelatedObjectTypes($registryObjectKey, $service, "relatedObject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// subject
					// -----------------------------------------------------------------
					importSubjectTypes($registryObjectKey, $service, "subject", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// description
					// -----------------------------------------------------------------
					importDescriptionTypes($registryObjectKey, $service, "description", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	
					// coverage
					// -----------------------------------------------------------------
					importCoverage($registryObjectKey, $service, "coverage", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
				
					// citationInfo
					// -----------------------------------------------------------------
					importCitationInfo($registryObjectKey, $service, "citationInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// accessPolicy
					// -----------------------------------------------------------------
					importAccessPolicy($registryObjectKey, $service, "accessPolicy", &$runErrors, &$totalAttemptedInserts, &$totalInserts);

					// rights
					// -----------------------------------------------------------------
					importRights($registryObjectKey, $service, "rights", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// existenceDates
					// -----------------------------------------------------------------
					importExistenceDates($registryObjectKey, $service, "existenceDates", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
					// relatedInfo
					// -----------------------------------------------------------------
					importRelatedInfo($registryObjectKey, $service, "relatedInfo", &$runErrors, &$totalAttemptedInserts, &$totalInserts);
					
				} // Service
			
				// Add a default and list title for the registry object
				$display_title = getOrderedNames($registryObjectKey, (isset($party) && $party), true);
				$list_title = getOrderedNames($registryObjectKey, (isset($party) && $party), false);
				updateRegistryObjectTitles ($registryObjectKey, $display_title, $list_title);	
				
				$hash = generateRegistryObjectHashForKey($registryObjectKey);
				updateRegistryObjectHash($registryObjectKey, $hash);
				
				updateRegistryObjectSLUG($registryObjectKey, $display_title, $currentUrlSlug);

				// A new record has been inserted? Update the cache


				$extRif = generateExtendedRIFCS($registryObjectKey);
				if (!writeCache($dataSourceKey, $registryObjectKey, $extRif))

				{
					$runErrors .= "Could not writeCache() for key: " . $registryObjectKey ."\n";
				} 
				else
				{
					$recordsCached++;
				}
			}			
		}
		else
		{
			$runErrors .= "Couldn't create Registry Object without key.\n";
		}// registryObjectKey

	} // Next registryObject.
	// Useful result information.
	$runResultMessage .= "  SOURCE DATA\n";
	$runResultMessage .= "    $totalRegistryObjectElements registryObject element/s.\n";
	$runResultMessage .= "    $totalElements elements.\n";
	$runResultMessage .= "  ACTIONS\n";
	$runResultMessage .= "    $totalRegistryObjectDeletes Registry Object/s deleted.\n";
	$runResultMessage .= "    $totalRegistryObjectInserts Registry Object/s inserted.\n";
	$runResultMessage .= "    $recordsCached records added to cache.\n";
	$runResultMessage .= "    $totalAttemptedInserts attempted inserts.\n";
	$runResultMessage .= "    $totalInserts inserts.\n";


	// Reset the old xPath variable
	if ($xPath !== NULL)
	{
		$gXPath = $tempXPath;
	}
	
	return $runErrors;
}

// Datatype handlers
// =============================================================================

function importIdentifierTypes($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;

	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_identifiers.identifier_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		
		
		/** ACTIVITIES PULLBACK FROM SANDBOX - QUICKFIX FOR MONICA **/
		// If this related object is an ARC/NHMRC grant, we should grab it from sandbox (if we haven't already)
		if (defined("eAU_RESEARCH_GRANTS_PREFIX") && eAU_RESEARCH_GRANTS_PREFIX != "" && strpos($value, eAU_RESEARCH_GRANTS_PREFIX) === 0) 
		{
			if (!getRegistryObject($value, true))
			{
				
				$errors = "";
				$remoteRIFCS = file_get_contents(eAU_RESEARCH_GRANTS_HARVEST_POINT . "?search=".rawurlencode($value)."&activities=activity");
				if (strpos($remoteRIFCS, "<key>" . $value . "</key>") === FALSE) 
				{
					//$runErrors .= "Failed to fetch related Activity record for: $relatedRegistryObjectKey\n"; 
					//$runErrors .= "Related Object with this key could not be found in Data Source.\n";
				}
				else 
				{
					$relatedObjectDOM = new DOMDocument();
					$result = $relatedObjectDOM->loadXML($remoteRIFCS);
					$errors = error_get_last();
					if( $errors )
					{
						//$runErrors .= "Failed to fetch related Activity record for: $relatedRegistryObjectKey\n"; 
						//$runErrors .= "Document Load Error: ".$errors['message']."\n";
					}
					// Get an xpath object to use for parsing the XML.
					$localXPath = new DOMXpath($relatedObjectDOM);
					
					$defaultNamespace = $localXPath->evaluate('/*')->item(0)->namespaceURI;
					$localXPath->registerNamespace($xs, $defaultNamespace);
					importRegistryObjects($relatedObjectDOM, eAU_RESEARCH_GRANTS_DATA_SOURCE, $errors, SYSTEM, PUBLISHED, SYSTEM, $localXPath);

				}

			}
			
		}
		
		
		
		
		$errors = insertIdentifier($id, $registryObjectKey, $value, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert identifier for key $registryObjectKey\n"; }
	}
}

function importComplexNameTypes($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_complex_names.complex_name_id');
		$type = $list->item($j)->getAttribute("type");
		$date_from = $list->item($j)->getAttribute("dateFrom");
		$date_to = $list->item($j)->getAttribute("dateTo");
		$lang = $list->item($j)->getAttribute("xml:lang");
		
		$errors = insertComplexName($id, $registryObjectKey, $type, $date_from, $date_to, $lang);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert complexName for key $registryObjectKey\n"; }

		importNameParts($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importNameParts($complex_name_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:namePart", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_name_parts.name_part_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		
		$errors = insertNamePart($id, $complex_name_id, $value, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert namePart for complexName $complex_name_id\n"; }
	}	
}

function importLocations($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_locations.location_id');
		$date_from = $list->item($j)->getAttribute("dateFrom");
		$date_to = $list->item($j)->getAttribute("dateTo");
		$type = $list->item($j)->getAttribute("type");
		
		$errors = insertLocation($id, $registryObjectKey, $date_from, $date_to, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert location for key $registryObjectKey\n"; }

		importSpatialTypes($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts, $registryObjectKey);
		importAddresses($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importAddresses($location_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;

	$list = $gXPath->evaluate("$xs:address", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_address_locations.address_id');
		
		$errors = insertAddressLocation($id, $location_id);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert address for location $location_id\n"; }
	
		importElectronicAddressTypes($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
		importPhysicalAddressTypes($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importElectronicAddressTypes($address_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:electronic", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_electronic_addresses.electronic_address_id');
		$value = $list->item($j)->getElementsByTagName("value")->item(0)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		
		$errors = insertElectronicAddress($id, $address_id, $value, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert electronicAddress for address $address_id\n"; }
		
		importElectronicAddressArgs($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importElectronicAddressArgs($electronic_address_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:arg", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_electronic_address_args.electronic_address_arg_id');
		$name = $list->item($j)->nodeValue;
		$required = $list->item($j)->getAttribute("required");
		$type = $list->item($j)->getAttribute("type");
		$use = $list->item($j)->getAttribute("use");
		
		$errors = insertElectronicAddressArg($id, $electronic_address_id, $name, $required, $type, $use);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert arg for electronicAddress $physical_address_id\n"; }
	}	
}

function importPhysicalAddressTypes($address_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:physical", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_physical_addresses.physical_address_id');
		$type = $list->item($j)->getAttribute("type");
		$lang = $list->item($j)->getAttribute("xml:lang");
		
		$errors = insertPhysicalAddress($id, $address_id, $type, $lang);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert physicalAddress for address $address_id\n"; }
		
		importAddressParts($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importAddressParts($physical_address_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:addressPart", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_address_parts.address_part_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		
		$errors = insertAddressPart($id, $physical_address_id, $value, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert addressPart for physicalAddress $physical_address_id\n"; }
	}	
}

function importSpatialTypes($location_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts, $registryObjectKey)
{
	global $gXPath;
	global $xs;
    
	$list = $gXPath->evaluate("$xs:spatial", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_spatial_locations.spatial_location_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		$lang = $list->item($j)->getAttribute("xml:lang");
		
		$errors = insertSpatialLocation($id, $location_id, $value, $type, $lang);
		if(!$errors && ($type == 'gmlKmlPolyCoords' || $type == 'kmlPolyCoords' || $type == 'iso19139dcmiBox'))
		{
		$errors = importSpatialExtent($id, $value, $type, $registryObjectKey);				
		}		
		$totalAttemptedInserts++;
		//if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert spatial for location $location_id\n"; }
	}
}

function importSpatialExtent($id, $value, $type, $registryObjectKey)
{
	$north = -90;
	$south = 90;
	$west  = 180;
	$east  = -180;
	//$msg = '';

	if($type == 'kmlPolyCoords' || $type == 'gmlKmlPolyCoords')
	{
		$tok = strtok($value, " ");
		while ($tok !== FALSE)
		{
			$keyValue = explode(",", $tok);
			//$msg = $msg.'<br/>lat ' .$keyValue[1]. ' long '.$keyValue[0];
			if(is_numeric($keyValue[1]) && is_numeric($keyValue[0]))
				{
				
				$lng = floatval($keyValue[0]);
				$lat = floatval($keyValue[1]);
				//$msg = $msg.'<br/>lat ' .$lat. ' long '.$lng;
				if ($lat > $north)
				{
				 $north = $lat;
				}
				if($lat < $south)
				{
				 $south = $lat;
				}
				if($lng < $west)
				{
				 $west = $lng;
				}
				if($lng > $east)
				{
				 $east = $lng;
				}
			}
			$tok = strtok(" ");	
		}	
		
	}
	if($type == 'iso19139dcmiBox')
	{
	//northlimit=-23.02; southlimit=-25.98; westlimit=166.03; eastLimit=176.1; projection=WGS84
	$north = null;
	$south = null;
	$west  = null;
	$east  = null;
		$tok = strtok($value, ";");
		while ($tok !== FALSE)
		{
			$keyValue = explode("=",$tok);
			if(strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1]))
			{
			  $north = floatval($keyValue[1]);			
			} 
			if(strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1]))
			{
			  $south = floatval($keyValue[1]);				
			}
			if(strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1]))
			{
			  $west = floatval($keyValue[1]);				
			}
			if(strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1]))
			{
			  $east = floatval($keyValue[1]);				
			}
		  	$tok = strtok(";");
		}		
	}
	//$msg = $msg.'<br/> north:'.$north.' south:'.$south.' west:'.$west.' east:'.$east;

	return insertSpatialExtent($id, $id, $registryObjectKey, $north, $south, $west, $east);
	//print($msg);
}

function importRelatedObjectTypes($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_related_objects.relation_id');
		$relatedRegistryObjectKey = $gXPath->evaluate("$xs:key", $list->item($j))->item(0)->nodeValue;
		
		/** ACTIVITIES PULLBACK FROM SANDBOX - QUICKFIX FOR MONICA **/
		// If this related object is an ARC/NHMRC grant, we should grab it from sandbox (if we haven't already)
		if (defined("eAU_RESEARCH_GRANTS_PREFIX") && eAU_RESEARCH_GRANTS_PREFIX != "" && strpos($relatedRegistryObjectKey, eAU_RESEARCH_GRANTS_PREFIX) === 0) 
		{
			if (!getRegistryObject($relatedRegistryObjectKey, true))
			{
				
				$errors = "";
				$remoteRIFCS = file_get_contents(eAU_RESEARCH_GRANTS_HARVEST_POINT . "?search=".rawurlencode($relatedRegistryObjectKey)."&activities=activity");
				if (strpos($remoteRIFCS, "<key>" . $relatedRegistryObjectKey . "</key>") === FALSE) 
				{
					//$runErrors .= "Failed to fetch related Activity record for: $relatedRegistryObjectKey\n"; 
					//$runErrors .= "Related Object with this key could not be found in Data Source.\n";
				}
				else 
				{
					$relatedObjectDOM = new DOMDocument();
					$result = $relatedObjectDOM->loadXML($remoteRIFCS);
					$errors = error_get_last();
					if( $errors )
					{
						//$runErrors .= "Failed to fetch related Activity record for: $relatedRegistryObjectKey\n"; 
						//$runErrors .= "Document Load Error: ".$errors['message']."\n";
					}
					// Get an xpath object to use for parsing the XML.
					$localXPath = new DOMXpath($relatedObjectDOM);
					
					$defaultNamespace = $localXPath->evaluate('/*')->item(0)->namespaceURI;
					$localXPath->registerNamespace($xs, $defaultNamespace);
					importRegistryObjects($relatedObjectDOM, eAU_RESEARCH_GRANTS_DATA_SOURCE, $errors, SYSTEM, PUBLISHED, SYSTEM, $localXPath);

				}

			}
			
		}
		
		$errors = insertRelatedObject($id, $registryObjectKey, $relatedRegistryObjectKey);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert relatedObject for key $registryObjectKey\n"; }
		importRelationDescriptions($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importRelationDescriptions($relation_id, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:relation", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_relation_description_id.relation_description_id');
		$type = $list->item($j)->getAttribute("type");
		
		$description = null;
		$lang = null;
		
		if( $descrElement = $gXPath->evaluate("$xs:description", $list->item($j))->item(0) )
		{
			$description = $descrElement->nodeValue;
			$lang = $descrElement->getAttribute("xml:lang");
		}
	
		$url = '';
		if( $gXPath->evaluate("$xs:url", $list->item($j))->item(0) )
		{
			$url = $gXPath->evaluate("$xs:url", $list->item($j))->item(0)->nodeValue;
		}
		$errors = insertRelationDescription($id, $relation_id, $description, $type, $lang, $url);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert description for relation $relation_id\n"; }
	}	
}

function importSubjectTypes($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_subjects.subject_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		$lang = $list->item($j)->getAttribute("xml:lang");
		$termIdentifier = $list->item($j)->getAttribute("termIdentifier");
	
		$errors = insertSubject($id, $registryObjectKey, $value, $type, $termIdentifier, $lang);
		$totalAttemptedInserts++;

		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert subject for key $registryObjectKey\n"; }
	}	
}

function importDescriptionTypes($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_descriptions.description_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		$lang = $list->item($j)->getAttribute("xml:lang");
		
		$errors = insertDescription($id, $registryObjectKey, $value, $type, $lang);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert description for key $registryObjectKey\n"; }
	}
}

/* 
 * OLD v1.0 version Related Info ingest
function importRelatedInfo($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_related_info.related_info_id');
		$value = $list->item($j)->nodeValue;
		
		$errors = insertRelatedInfo($id, $registryObjectKey, $value);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert relatedInfo for key $registryObjectKey\n"; }
	}
}
*/

function importRelatedInfo($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_related_info.related_info_id');
		$type = $list->item($j)->getAttribute("type");
		$identifier = '';
		$identifier_type = '';
		$title = '';
		$notes = '';
		
		if( $identifierElement = $gXPath->evaluate("$xs:identifier", $list->item($j))->item(0) )
		{
			$identifier = $identifierElement->nodeValue;
			$identifier_type = $identifierElement->getAttribute("type");
		}
		
		if( $titleElement = $gXPath->evaluate("$xs:title", $list->item($j))->item(0) )
		{
			$title = $titleElement->nodeValue;
		}
		
		if( $notesElement = $gXPath->evaluate("$xs:notes", $list->item($j))->item(0) )
		{
			$notes = $notesElement->nodeValue;
		}
		if(!$identifier)
		{// old rifcs probably :-(
			$value = $list->item($j)->nodeValue;		
			$errors = insertRelatedInfoOld($id, $registryObjectKey, $value);		
		}
		else
		{
			$errors = insertRelatedInfo($id, $registryObjectKey, $type, $identifier, $identifier_type, $title, $notes);
		}
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert relatedInfo for key $registryObjectKey\n"; }
	}
}


function importRights($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_rights.rights_id');
		$rights_statement = '';
		$rights_statement_uri = '';
		$licence = '';
		$licence_uri = '';
		$licence_type = '';
		$access_rights = '';
		$access_rights_uri = '';
		$access_rights_type = '';		
		
		if( $rightsStatement = $gXPath->evaluate("$xs:rightsStatement", $list->item($j))->item(0) )
		{
			$rights_statement = $rightsStatement->nodeValue;
			$rights_statement_uri = $rightsStatement->getAttribute("rightsUri");
		}
		
		if( $licenceElement = $gXPath->evaluate("$xs:licence", $list->item($j))->item(0) )
		{
			$licence = $licenceElement->nodeValue;
			$licence_uri = $licenceElement->getAttribute("rightsUri");
			$licence_type = $licenceElement->getAttribute("type");
		}
		
		if( $accessRights = $gXPath->evaluate("$xs:accessRights", $list->item($j))->item(0) )
		{
			$access_rights = $accessRights->nodeValue;
			$access_rights_uri = $accessRights->getAttribute("rightsUri");
			$access_rights_type = $accessRights->getAttribute("type");
		}
		
		$errors = insertRights($id, $registryObjectKey, $rights_statement, $rights_statement_uri, $licence, $licence_uri, $access_rights, $access_rights_uri);
		
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert rights for key $registryObjectKey\n"; }
	}
}

function importExistenceDates($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_existence_dates.existence_dates_id');
		$start_date = '';
		$start_date_format = '';
		$end_date = '';
		$end_date_format = '';
		
		if( $startDate = $gXPath->evaluate("$xs:startDate", $list->item($j))->item(0) )
		{
			$start_date = $startDate->nodeValue;
			$start_date_format = $startDate->getAttribute("dateFormat");
		}
		
		if( $endDate = $gXPath->evaluate("$xs:endDate", $list->item($j))->item(0) )
		{
			$end_date = $endDate->nodeValue;
			$end_date_format = $endDate->getAttribute("dateFormat");
		}
		
		$errors = insertExistenceDates($id, $registryObjectKey, $start_date, $start_date_format, $end_date, $end_date_format);
		
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert existence dates for key $registryObjectKey\n"; }
	}
}

function importCoverage($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		
		// Add the coverage
		$coverageId = getIdForColumn('dba.tbl_coverage.coverage_id');
		
		$errors = insertCoverage($coverageId, $registryObjectKey);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert coverage for key $registryObjectKey\n"; }
		
		
		// Spatial coverages
		$spatialCoverageElements = $gXPath->evaluate("$xs:spatial", $list->item($j));
		for( $k=0; $k < $spatialCoverageElements->length; $k++ ) {
			
			$id = getIdForColumn('dba.tbl_spatial_locations.spatial_location_id');
			
			$value = $spatialCoverageElements->item($k)->nodeValue;
			$type = $spatialCoverageElements->item($k)->getAttribute("type");
			$lang = $spatialCoverageElements->item($k)->getAttribute("xml:lang");
			
			$errors = insertSpatialCoverage($id, $coverageId, $value, $type, $lang);
			if(!$errors && ($type == 'gmlKmlPolyCoords' || $type == 'kmlPolyCoords' || $type == 'iso19139dcmiBox'))
			{
			$errors = importSpatialExtent($id, $value, $type, $registryObjectKey);				
			}		
			$totalAttemptedInserts++;
			//if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert spatial for coverage $coverageId\n"; }
				
		}
		
		// Temporal coverages
		$temporalCoverageElements = $gXPath->evaluate("$xs:temporal", $list->item($j));
		for( $k=0; $k < $temporalCoverageElements->length; $k++ ) {
			
			$temporalCoverageId = getIdForColumn('dba.tbl_temporal_coverage.temporal_coverage_id');
			
			$errors = insertTemporalCoverage($temporalCoverageId, $coverageId);
			$totalAttemptedInserts++;
			if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert temporal coverage for key $registryObjectKey\n"; }
		
			importTemporalCoverageDates($temporalCoverageId, $temporalCoverageElements->item($k), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
			importTemporalCoverageText($temporalCoverageId, $temporalCoverageElements->item($k), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
		}

	}
}


function importTemporalCoverageText($temporalCoverageId, $node, $runErrors, $totalAttemptedInserts, $totalInserts) 
{
	global $gXPath;
	global $xs;
	
	$textElements = $gXPath->evaluate("$xs:text", $node);
	for( $k=0; $k < $textElements->length; $k++ ) {
	
		$temporalTextId = getIdForColumn('dba.tbl_temporal_coverage_text.coverage_text_id');
		$value = $textElements->item($k)->nodeValue;
		
		$errors = insertTemporalCoverageText($temporalTextId, $temporalCoverageId, $value);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert temporal coverage text for key $registryObjectKey\n"; }
		
		
	}
	
}

function importTemporalCoverageDates($temporalCoverageId, $node, $runErrors, $totalAttemptedInserts, $totalInserts) 
{
	global $gXPath;
	global $xs;
	
	$dateElements = $gXPath->evaluate("$xs:date", $node);
	for( $k=0; $k < $dateElements->length; $k++ ) {
	
		$temporalDateId = getIdForColumn('dba.tbl_temporal_coverage_dates.coverage_date_id');
		$value = $dateElements->item($k)->nodeValue;
		$type = $dateElements->item($k)->getAttribute("type");
		$dateFormat = $dateElements->item($k)->getAttribute("dateFormat");
		$timestamp = null; /// XXX: TODO: Parse value in dateFormat into timestamp
		
		$errors = insertTemporalCoverageDate($temporalDateId, $temporalCoverageId, $type, $dateFormat, $value, $timestamp);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert temporal coverage date for key $registryObjectKey\n"; }
		
		
	}
	
}



function importCitationInfo($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	// First enter fullCitation
	$list = $gXPath->evaluate("$xs:$elementName/$xs:fullCitation", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_citation_information.citation_info_id');
		$value = $list->item($j)->nodeValue;
		$style = $list->item($j)->getAttribute("style");
		
		$errors = insertFullCitationInformation($id, $registryObjectKey, $style, $value);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert fullCitation for key $registryObjectKey\n"; }
	}
	
	// Now handle citationMetadata
	$list = $gXPath->evaluate("$xs:$elementName/$xs:citationMetadata", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_citation_information.citation_info_id');

		$identifier = '';
		$identifier_type = '';
		$title = '';
		$edition = '';
		$publisher = '';
		$placePublished = '';
		$url = '';
		$context = '';
		
		if( $identiferElement = $gXPath->evaluate("$xs:identifier", $list->item($j))->item(0) )
		{
			$identifier = $identiferElement->nodeValue;
			$identifier_type = $identiferElement->getAttribute("type");
		}
		
		if( $titleElement = $gXPath->evaluate("$xs:title", $list->item($j))->item(0) )
		{
			$title = $titleElement->nodeValue;
		}
		
		if( $editionElement = $gXPath->evaluate("$xs:edition", $list->item($j))->item(0) )
		{
			$edition = $editionElement->nodeValue;
		}
		
		if( $publisherElement = $gXPath->evaluate("$xs:publisher", $list->item($j))->item(0) )
		{
			$publisher = $publisherElement->nodeValue;
		}
		
		if( $placeElement = $gXPath->evaluate("$xs:placePublished", $list->item($j))->item(0) )
		{
			$placePublished = $placeElement->nodeValue;
		}
	
		if( $urlElement = $gXPath->evaluate("$xs:url", $list->item($j))->item(0) )
		{
			$url = $urlElement->nodeValue;
		}
		
		if( $contextElement = $gXPath->evaluate("$xs:context", $list->item($j))->item(0) )
		{
			$context = $contextElement->nodeValue;
		}
		
		$errors = insertCitationMetadata($id, $registryObjectKey, $identifier, $identifier_type, $title, $edition, $placePublished, $url, $context, $publisher);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert citationMetadata for key $registryObjectKey\n"; }

		importCitationDates($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
		importCitationContributors($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importCitationDates($citationInfoId, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:date", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_citation_dates.metadata_date_id');
		$value = $list->item($j)->nodeValue;
		$type = $list->item($j)->getAttribute("type");
		
		$errors = insertCitationDate($id, $citationInfoId, $value, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert citationMetadata date for key $registryObjectKey\n"; }
	}
}


function importCitationContributors($citationInfoId, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs" . ":contributor", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$seq = '';
		$id = getIdForColumn('dba.tbl_citation_contributors.citation_contributor_id');
		
		$seq = $list->item($j)->getAttribute("seq");
		
		$errors = insertCitationContributor($id, $citationInfoId, $seq);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert citationMetadata contributor for key $citationInfoId\n"; }
		
		importCitationContributorNameParts($id, $list->item($j), &$runErrors, &$totalAttemptedInserts, &$totalInserts);
	}
}

function importCitationContributorNameParts($citationContributorId, $node, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs" . ":namePart", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$type = '';
		$value = '';
		$id = getIdForColumn('dba.tbl_name_parts.name_part_id');
		
		$type = $list->item($j)->getAttribute("type");
		if($type == 'unknown')
		{
			$type = '';
		}
		$value = $list->item($j)->nodeValue;
		
		$errors = insertCitationContributorNamePart($id, $citationContributorId, $value, $type);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert citationMetadata contributor namePart for key $citationContributorId\n"; }
	}
}





function importAccessPolicy($registryObjectKey, $node, $elementName, $runErrors, $totalAttemptedInserts, $totalInserts)
{
	global $gXPath;
	global $xs;
	
	$list = $gXPath->evaluate("$xs:$elementName", $node);
	for( $j=0; $j < $list->length; $j++ )
	{
		$id = getIdForColumn('dba.tbl_access_policies.access_policy_id');
		$value = $list->item($j)->nodeValue;
		
		$errors = insertAccessPolicy($id, $registryObjectKey, $value);
		$totalAttemptedInserts++;
		if( !$errors ) { $totalInserts++; } else { $runErrors .= "Failed to insert accessPolicy for key $registryObjectKey\n"; }
	}
}

function getUserPartyObject()
{
	// Get the owner handle. (This will create a new owner handle if one doesn't already exist.)
	$partyObjectKey = pidsGetOwnerHandle();
	
	// Check to see if we have a party object already.
	$partyObject = getRegistryObject($partyObjectKey);
	
	if( !$partyObject )
	{
		$dataSourceKey = 'PUBLISH_MY_DATA';
		$objectGroup = 'Publish My Data';
		
		// Create a party object in the registry.
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
		$rifcs .= '    <key>'.esc($partyObjectKey).'</key>'."\n";
		// ---------------------------------------------------------------------		
		// originatingSource
		$rifcs .= '    <originatingSource>'.esc(eAPP_ROOT.'orca').'</originatingSource>'."\n";
		// ---------------------------------------------------------------------		
		// party
		$rifcs .= '    <party type="publisher">'."\n";
		// ---------------------------------------------------------------------
		// name
		$rifcs .= '      <name>'."\n";
		$rifcs .= '        <namePart>'.esc(getSessionVar(sNAME)).'</namePart>'."\n";
		$rifcs .= '      </name>'."\n";
		// ---------------------------------------------------------------------
		// party
		$rifcs .= '    </party>'."\n";
		// ---------------------------------------------------------------------
		// registryObject
		$rifcs .= '  </registryObject>'."\n";
		// ---------------------------------------------------------------------
		$rifcs .= "</registryObjects>\n";
		
		// Check the xml.
		// =====================================================================
		$errorMessages = '';
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
			$result = $registryObjects->schemaValidate(gRIF2_SCHEMA_URI); //xxx
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
				
				// Log the datasource activity.
				insertDataSourceEvent($dataSourceKey, "ADD REGISTRY OBJECT\nKey: ".getPostedValue('key')."\n".$resultMessage);
			}
		}
		
		// Get the pary object so we can display it.
		$partyObject = getRegistryObject($partyObjectKey);
	}
	return $partyObject;
}

function updateUserPartyObject($name, $email=null)
{
	// Get the owner handle. (This will create a new owner handle if one doesn't already exist.)
	$partyObjectKey = pidsGetOwnerHandle();
	
	// Check to see if we have a party object already.
	$partyObject = getRegistryObject($partyObjectKey);
	
	if( $partyObject )
	{
		$dataSourceKey = 'PUBLISH_MY_DATA';
		$objectGroup = 'Publish My Data';
		
		// Create a party object in the registry.
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
		$rifcs .= '    <key>'.esc($partyObjectKey).'</key>'."\n";
		// ---------------------------------------------------------------------		
		// originatingSource
		$rifcs .= '    <originatingSource>'.esc(eAPP_ROOT.'orca').'</originatingSource>'."\n";
		// ---------------------------------------------------------------------		
		// party
		$rifcs .= '    <party type="person">'."\n";
		// ---------------------------------------------------------------------
		// name
		$rifcs .= '      <name>'."\n";
		$rifcs .= '        <namePart>'.esc($name).'</namePart>'."\n";
		$rifcs .= '      </name>'."\n";
		// ---------------------------------------------------------------------
		// email
		if( $email )
		{
			$rifcs .= '      <location>'."\n";
			$rifcs .= '        <address>'."\n";
			$rifcs .= '          <electronic type="email">'."\n";
			$rifcs .= '            <value>'.esc($email).'</value>'."\n";
			$rifcs .= '          </electronic>'."\n";
			$rifcs .= '        </address>'."\n";
			$rifcs .= '      </location>'."\n";
		}
		
			
		// ---------------------------------------------------------------------
		// related objects
		$rifcs .= getRelatedObjectTypesXML($partyObjectKey, 'relatedObject');
		
		// ---------------------------------------------------------------------
		// party
		$rifcs .= '    </party>'."\n";
		// ---------------------------------------------------------------------
		// registryObject
		$rifcs .= '  </registryObject>'."\n";
		// ---------------------------------------------------------------------
		$rifcs .= "</registryObjects>\n";
		
		// Check the xml.
		// =====================================================================
		$errorMessages = '';
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
			$result = $registryObjects->schemaValidate(gRIF2_SCHEMA_URI); //xxx
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
				
				// Log the datasource activity.
				insertDataSourceEvent($dataSourceKey, "ADD REGISTRY OBJECT\nKey: ".getPostedValue('key')."\n".$resultMessage);
			}
		}
		
		// Get the pary object so we can display it.
		$partyObject = getRegistryObject($partyObjectKey);
	}
	return $partyObject;
}

function addCollectionRelationToUserParty($partyObjectKey, $collectionKey)
{
	$relationId = getIdForColumn('dba.tbl_related_objects.relation_id');
	$relationDescriptionId = getIdForColumn('dba.tbl_relation_description_id.relation_description_id');
	
	insertRelatedObject($relationId, $partyObjectKey, $collectionKey);
	insertRelationDescription($relationDescriptionId, $relationId, null, 'hasAssociationWith', null, null);
}

function removeCollectionRelationFromUserParty($collectionKey)
{
	$partyObjectKey = pidsGetOwnerHandle();	
	deleteRelatedObject($partyObjectKey, $collectionKey);
}

function getRelatedObjectClass($relatedRegistryObjectKey, $dataSourceKey)
{
	$classStr = "";
		if($relatedRegistryObject = getRegistryObject($relatedRegistryObjectKey,true))
		{	
			$classStr = "###".$relatedRegistryObject[0]['registry_object_class'];			
		} 
		else if($relatedRegistryObject = getDraftRegistryObject($relatedRegistryObjectKey,null))
		{	
			$classStr = "###".$relatedRegistryObject[0]['class'];							
		}
	return $classStr;
}


function getAllRelatedObjectClass($RegistryObject, $dataSourceKey)
{
	
	$list = $RegistryObject->getElementsByTagName("key");
	$j=0;
	$relatedObjectClassesStr = "";
	for( $j=0; $j < $list->length; $j++ )
	{
		$relatedRegistryObjectKey = $list->item($j)->nodeValue;
		$relatedObjectClassesStr .= getRelatedObjectClass($relatedRegistryObjectKey, $dataSourceKey);			
	}
	
	$dataSourceInfo = getDataSources($dataSourceKey, $filter=null);
	
	if(isset($dataSourceInfo[0]['class_1']))
		$relatedObjectClassesStr .= '###'.$dataSourceInfo[0]['class_1'];
	if(isset($dataSourceInfo[0]['class_2']))
		$relatedObjectClassesStr .= '###'.$dataSourceInfo[0]['class_2'];	
	
	return $relatedObjectClassesStr;
}

function getRelatedXml($dataSource,$rifcs,$objectClass){

	$objectClass = strtolower($objectClass);
	$newrifcs = '';
	$dataSourceInfo = getDataSources($dataSource, $filter=null);

	$rifObject = new DomDocument();
	$rifObject->loadXML($rifcs);
	$theclasses = $rifObject->getElementsByTagName($objectClass);
	if($theclasses->length<1) 	$theclasses = $rifObject->getElementsByTagName($objectClass = strtolower($objectClass));
	$therelations = $rifObject->getElementsByTagName('relatedObject');	
	$theKey = $rifObject->getElementsByTagName('key');	
	$therealkey = $theKey->item(0)->firstChild->nodeValue;
	$relCount = $therelations->length;
	$theclass = $theclasses->item(0);
	$theRels = array();
	for($i=0;$i<$relCount;$i++)
	{	
		$theRels[$therelations->item($i)->firstChild->nodeValue] = $therelations->item($i)->firstChild->nodeValue;
	}

	if(isset($dataSourceInfo[0]['primary_key_1'])&& $dataSourceInfo[0]['primary_key_1']!= $therealkey &&(!array_key_exists($dataSourceInfo[0]['primary_key_1'],$theRels)))
	{
		$relCount++;
		$newrelatedObject = $rifObject->createElement('relatedObject');
		$newnode = $theclass->appendChild($newrelatedObject);
		//$newrelatedObject->setAttribute('field_id',"relatedObject_".$relCount);			
		//$newrelatedObject->setAttribute('tab_id',"relatedObject");	

				
		$newRelatedKey = $rifObject->createElement('key',$dataSourceInfo[0]['primary_key_1']);
		$newRelatedKey->setAttribute('roclass',ucwords($dataSourceInfo[0]['class_1']));		
		//$newRelatedKey->setAttribute('field_id',"relatedObject_".$relCount."_key_1");				
		//$newRelatedKey->setAttribute('tab_id',"relatedObject");				
		$newrelatedObject->appendChild($newRelatedKey);
		
		$newRelatedRelation = $rifObject->createElement('relation');
		$newRelatedRelation->setAttribute('type',$dataSourceInfo[0][strtolower($objectClass)."_rel_1"]);
		//$newRelatedRelation->setAttribute('field_id',"relatedObject_".$relCount."_relation_1");						
		//$newRelatedRelation->setAttribute('tab_id',"relatedObject");	
				
		$newrelatedObject->appendChild($newRelatedRelation);	
		$newRelationUrl = $rifObject->createElement('url');
		$newRelatedRelation->appendChild($newRelationUrl);							

	}
	if(isset($dataSourceInfo[0]['primary_key_2'])&& $dataSourceInfo[0]['primary_key_2']!= $therealkey &&(!array_key_exists($dataSourceInfo[0]['primary_key_2'],$theRels)))
	{
		$relCount++;
		$newrelatedObject = $rifObject->createElement('relatedObject');
		$newnode = $theclass->appendChild($newrelatedObject);
		//$newrelatedObject->setAttribute('field_id',"relatedObject_".$relCount);			
		//$newrelatedObject->setAttribute('tab_id',"relatedObject");	

				
		$newRelatedKey = $rifObject->createElement('key',$dataSourceInfo[0]['primary_key_2']);
		$newRelatedKey->setAttribute('roclass',ucwords($dataSourceInfo[0]['class_2']));		
		//$newRelatedKey->setAttribute('field_id',"relatedObject_".$relCount."_key_2");				
		//$newRelatedKey->setAttribute('tab_id',"relatedObject");				
		$newrelatedObject->appendChild($newRelatedKey);
		
		$newRelatedRelation = $rifObject->createElement('relation');
		$newRelatedRelation->setAttribute('type',$dataSourceInfo[0][strtolower($objectClass)."_rel_2"]);
		//$newRelatedRelation->setAttribute('field_id',"relatedObject_".$relCount."_relation_1");						
		//$newRelatedRelation->setAttribute('tab_id',"relatedObject");	
				
		$newrelatedObject->appendChild($newRelatedRelation);	
		$newRelationUrl = $rifObject->createElement('url');
		$newRelatedRelation->appendChild($newRelationUrl);							
								
	}

		$newrifcs = $rifObject->saveXML();
		return $newrifcs;
} 

function runQualityCheck($rifcs, $objectClass, $dataSource, $output, $relatedObjectClassesStr='')
{
	$relRifcs = getRelatedXml($dataSource,$rifcs,$objectClass);
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($relRifcs);
	$rmdQualityTest = new DomDocument();
	$rmdQualityTest->load('../_xsl/rmd_quality_test.xsl');
	$proc = new XSLTProcessor();
	$proc->setParameter('', 'dataSource', $dataSource);
	$proc->setParameter('', 'output', $output);
	$proc->setParameter('', 'relatedObjectClassesStr', $relatedObjectClassesStr);
	$proc->importStyleSheet($rmdQualityTest);
	$result = $proc->transformToXML($registryObjects);
	return $result;		
}


function runQualityCheckonDom($registryObjects, $dataSource, $output, $relatedObjectClassesStr)
{

	$rmdQualityTest = new DomDocument();
	$rmdQualityTest->load('../_xsl/rmd_quality_test.xsl');
	$proc = new XSLTProcessor();
	$proc->setParameter('', 'dataSource', $dataSource);
	$proc->setParameter('', 'output', $output);
	$proc->setParameter('', 'relatedObjectClassesStr', $relatedObjectClassesStr);
	$proc->importStyleSheet($rmdQualityTest);
	$result = $proc->transformToXML($registryObjects);
	return $result;		
}



function runQualityResultsforDataSource($dataSourceKey,$itemurl)
{
	$dataSource = getDataSources($dataSourceKey, null);
	$qaFlag = $dataSource[0]['qa_flag'];
	$message = 'Quality check run for '.$dataSourceKey."<br />";
	$message .= '<p><span class="error-info">(i) element or content is required</span><br/>
		<span class="recommended-info">(ii) element or content is strongly recommended if at all possible</span></p>';
	if($registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey))
	{
		for( $i=0; $i < count($registryObjectKeys); $i++ )
		{
			if($registryObjectKeys[$i]['quality_test_result']!=""&&($registryObjectKeys[$i]['error_count']>0||$registryObjectKeys[$i]['warning_count']>0))
			{
				$message .= $registryObjectKeys[$i]['registry_object_class'].": " .$registryObjectKeys[$i]['list_title']." : <a href=\"".$itemurl.$registryObjectKeys[$i]['registry_object_key']."\">".$registryObjectKeys[$i]['registry_object_key']."</a> (".$registryObjectKeys[$i]['status'].")<br/>";
				$message .= str_replace("</span>","</span><br />",$registryObjectKeys[$i]['quality_test_result']);
				$message = str_replace('class="info"','class="recommended-info"',$message);
				$message = str_replace('class="warning"','class="error-info"',$message);	
				$message = str_replace('class="error"','class="hidden"',$message);								
			}
		}
	}

	if($draftObjectKeys = getDraftRegistryObject(null, $dataSourceKey))
	{			
		for( $i=0; $i < count($draftObjectKeys); $i++ )
		{
			if($draftObjectKeys[$i]['quality_test_result']!="")
			{
				
				$class = "/orca/manage/add_".strtolower($draftObjectKeys[$i]['class'])."_registry_object.php?readOnly&data_source=".$dataSourceKey."&key=";
				$itemurlDraft = str_replace("/orca/view.php?key=",$class,$itemurl);				
				$message .= $draftObjectKeys[$i]['class'].": ".$draftObjectKeys[$i]['registry_object_title']." : <a href=\"".$itemurlDraft.$draftObjectKeys[$i]['draft_key']."\">".$draftObjectKeys[$i]['draft_key']."</a> (".$draftObjectKeys[$i]['status'].")<br/>";
				$message .= str_replace("</span>","</span><br />",$draftObjectKeys[$i]['quality_test_result']);				
				$message = str_replace('class="info"','class="recommended-info"',$message);
				$message = str_replace('class="warning"','class="error-info"',$message);
				$message = str_replace('class="error"','class="hidden"',$message);											
			}
		}
	}
	
	return $message;	
}



function runQualityCheckforDataSource($dataSourceKey)
{
	$dataSource = getDataSources($dataSourceKey, null);
	$message = 'Quality check run for '.$dataSourceKey."\n";

	if($registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey))
	{
		for( $i=0; $i < count($registryObjectKeys); $i++ )
		{
			$message .= runQualityLevelCheckForRegistryObject($registryObjectKeys[$i]['registry_object_key'], $dataSourceKey)."\n";
		}
	}
	if($draftRegistryObjectKeys = getDraftRegistryObject(null, $dataSourceKey))
	{
		for( $i=0; $i < count($draftRegistryObjectKeys); $i++ )
		{
			$message .= runQualityLevelCheckForDraftRegistryObject($draftRegistryObjectKeys[$i]['draft_key'], $dataSourceKey)."\n";
		}
	}

	return $message;	
}

function runQualityLevelCheckforDataSource($dataSourceKey)
{
	$dataSource = getDataSources($dataSourceKey, null);
	$qaFlag = $dataSource[0]['qa_flag'];
	$message = 'Quality check run for '.$dataSourceKey."\n";

	if($registryObjectKeys = getRegistryObjectKeysForDataSource($dataSourceKey))
	{
		for( $i=0; $i < count($registryObjectKeys); $i++ )
		{
			$message .= runQualityLevelCheckForRegistryObject($registryObjectKeys[$i]['registry_object_key'], $dataSourceKey)."\n";
		}
	}
	if($draftRegistryObjectKeys = getDraftRegistryObject(null, $dataSourceKey))
	{
		for( $i=0; $i < count($draftRegistryObjectKeys); $i++ )
		{
			$message .= runQualityLevelCheckForDraftRegistryObject($draftRegistryObjectKeys[$i]['draft_key'], $dataSourceKey)."\n";
		}
	}

	return $message;	
}



function runQualityCheckForRegistryObject($registryObjectKey, $dataSourceKey)
{
	
		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		$rifcs .= getRegistryObjectXML($registryObjectKey);
		$rifcs .= '</registryObjects>'; 
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";			
		}
		elseif(str_replace("<Service","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";			
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";			
		}	
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";			
		}
	
		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);  

		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		$relatedObjectClassesStr = '';
		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		$qualityTestResult = runQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
	    $errorCount = substr_count($qualityTestResult, 'class="error"'); 
		$warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');                              
        $result = updateRegistryObjectQualityTestResult($registryObjectKey, $qualityTestResult, $errorCount, $warningCount);                            
		//return $result;
}


function runQualityLevelCheckForRegistryObject($registryObjectKey, $dataSourceKey)
{
	
		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		$rifcs .= getRegistryObjectXML($registryObjectKey);
		$rifcs .= '</registryObjects>'; 
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";			
		}
		elseif(str_replace("<Servive","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";			
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";			
		}	
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";			
		}
	
		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);  

		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		$level = 1;

		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		$qualityTestResult = runQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
	    $errorCount = substr_count($qualityTestResult, 'class="error"'); 
		$warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');                              
        $result = updateRegistryObjectQualityTestResult($registryObjectKey, $qualityTestResult, $errorCount, $warningCount);                            
		$qa_result = getQualityLevel($RegistryObjects,$objectClass,$relatedObjectClassesStr,&$level);
		$result = updateRegistryObjectQualityLevelResult($registryObjectKey, $level, $qa_result);
		//print $qa_result;	                         
		return $result;
}

function runQualityLevelCheckForDraftRegistryObject($registryObjectKey, $dataSourceKey)
{
		$registryObject = getDraftRegistryObject($registryObjectKey,$dataSourceKey);
		$relatedObjectClassesStr = '';
		$rifcs = $registryObject[0]['rifcs'];	
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";			
		}
		elseif(str_replace("<Servive","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";			
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";			
		}	
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";			
		}	
		
		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);				
				
		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		
		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		$qualityTestResult = runQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
		$errorCount = substr_count($qualityTestResult, 'class="error"');                              
	    $warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');                              
        $result = updateDraftRegistryObjectQualityTestResult($registryObjectKey, $dataSourceKey, $qualityTestResult, $errorCount, $warningCount);             
		$level = 1;
		$qa_result = getQualityLevel($RegistryObjects,$objectClass,$relatedObjectClassesStr,&$level);
		$result = updateDraftRegistryObjectQualityLevelResult($registryObjectKey, $dataSourceKey, $level, $qa_result);
		//print $qa_result;	                         
		return $result;
}

function getQualityLevel($RegistryObjects,$objectClass,$relatedObjectClassesStr,$level)
{
	
	$response = "<span class='qa_ok' level='1'>registry objects</span>\n";
	$key;	
	if($objectClass == 'Collection')
	{
		//level 1 requirements
		if($eRegistryObject = $RegistryObjects->getElementsByTagName('registryObject')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>registry object</span>\n";
			$level = 1;
		}
		else
		{
			$response .= "<span class='qa_req' level='1'>registry object</span>\n";
			$level = 0;
		}
		if($eOriginatingSource = $RegistryObjects->getElementsByTagName('originatingSource')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>originating source</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>originating source</span>\n";
			$level = 0;
		}
		if($aRegistryObjectGroup = $eRegistryObject->getAttribute("group"))
		{
			$response .= "<span class='qa_ok' level='1'>group</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>group</span>\n";
			$level = 0;
		}
		if($eKey = $eRegistryObject->getElementsByTagName('key')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>key</span>\n";
			$key = $eKey->nodeValue;
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>key</span>\n";
			$level = 0;
		}
		if($eCollection = $eRegistryObject->getElementsByTagName('collection')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>class type (collection)</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>class type (collection)</span>\n";
			$level = 0;
		}
		if($aCollectionType = $eCollection->getAttribute("type"))
		{
			$response .= "<span class='qa_ok' level='1'>collection type</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>collection type</span>\n";
			$level = 0;
		}
		//level 2
		if($eName = $eCollection->getElementsByTagName('name')->item(0))
		{
			$response .= "<span class='qa_ok' level='2'>name</span>\n";
			$level = ($level == 1 ? 2 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='2'>name</span>\n";
		}
		if(strpos($relatedObjectClassesStr, 'party'))
		{
			$response .= "<span class='qa_ok' level='2'>related object (party)</span>\n";
			$level = ($level == 2 ? 2 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='2'>related object (party)</span>\n";
			$level = ($level == 2 ? 1 : $level);
		}
		if($eIdentifier = $eCollection->getElementsByTagName('identifier')->item(0))
		{
			$response .= "<span class='qa_ok' level='2'>identifier</span>\n";
			$level = ($level == 2 ? 2 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='2'>identifier</span>\n";
			$level = ($level == 2 ? 1 : $level);
		}
		//level 3
		if($eDescription = $eCollection->getElementsByTagName('description')->item(0))
		{
			$response .= "<span class='qa_ok' level='3'>description</span>\n";
			$level = ($level == 2 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>description</span>\n";
		}
		if($eLocation = $eCollection->getElementsByTagName('location')->item(0))
		{
			$response .= "<span class='qa_ok' level='3'>location/address</span>\n";
			$level = ($level == 3 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>location/address</span>\n";
			$level = ($level == 3 ? 2 : $level);
		}
		if($bRelatedActivity = strpos($relatedObjectClassesStr, 'activity'))
		{
			$response .= "<span class='qa_ok' level='3'>related object (activity)</span>\n";
			$level = ($level == 3 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>related object (activity)</span>\n";
			$level = ($level == 3 ? 2 : $level);
		}
		if($eRights = $eCollection->getElementsByTagName('rights')->item(0))
		{
			$response .= "<span class='qa_ok' level='3'>rights</span>\n";
			$level = ($level == 3 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>rights</span>\n";
			$level = ($level == 3 ? 2 : $level);
		}
		//level 4
		if($eSubject = $eCollection->getElementsByTagName('subject')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>subject</span>\n";
			$level = ($level == 3 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>subject</span>\n";
		}
		if($eCoverage = $eCollection->getElementsByTagName('coverage')->item(0))
		{
			if($eSpatial = $eCoverage->getElementsByTagName('spatial')->item(0))
			{
				$response .= "<span class='qa_ok' level='4'>spatial coverage</span>\n";
				$level = ($level == 4 ? 4 : $level);
			}
			else 
			{
				$response .= "<span class='qa_rec' level='4'>spatial coverage</span>\n";
				$level = ($level == 4 ? 3 : $level);
			}
			if($eTemporal = $eCoverage->getElementsByTagName('temporal')->item(0))
			{
				$response .= "<span class='qa_ok' level='4'>temporal coverage</span>\n";
				$level = ($level == 4 ? 4 : $level);
			}
			else 
			{
				$response .= "<span class='qa_rec' level='4'>temporal coverage</span>\n";
				$level = ($level == 4 ? 3 : $level);
			}
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>spatial coverage</span>\n";
			$response .= "<span class='qa_rec' level='4'>temporal coverage</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}		
		if($eCitation = $eCollection->getElementsByTagName('citationInfo')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>citation</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>citation</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}	
	}
	elseif($objectClass == 'Party')
	{
		//level 1 requirements
		if($eRegistryObject = $RegistryObjects->getElementsByTagName('registryObject')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>registry object</span>\n";
			$level = 1;
		}
		else
		{
			$response .= "<span class='qa_req' level='1'>registry object</span>\n";
			$level = 0;
		}
		if($eOriginatingSource = $RegistryObjects->getElementsByTagName('originatingSource')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>originating source</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>originating source</span>\n";
			$level = 0;
		}
		if($aRegistryObjectGroup = $eRegistryObject->getAttribute("group"))
		{
			$response .= "<span class='qa_ok' level='1'>group</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>group</span>\n";
			$level = 0;
		}
		if($eKey = $eRegistryObject->getElementsByTagName('key')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>key</span>\n";
			$key = $eKey->nodeValue;
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>key</span>\n";
			$level = 0;
		}
		if($eParty = $eRegistryObject->getElementsByTagName('party')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>class type (party)</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>class type (party)</span>\n";
			$level = 0;
		}
		if($aPartyType = $eParty->getAttribute("type"))
		{
			$response .= "<span class='qa_ok' level='1'>party type</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>party type</span>\n";
			$level = 0;
		}
		//level 2
		if($eName = $eParty->getElementsByTagName('name')->item(0))
		{
			$response .= "<span class='qa_ok' level='2'>name</span>\n";
			$level = ($level == 1 ? 2 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='2'>name</span>\n";
		}
		//level 3
		if($bRelatedCollection = strpos($relatedObjectClassesStr, 'collection'))
		{
			$response .= "<span class='qa_ok' level='3'>related object (collection)</span>\n";
			$level = ($level == 2 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>related object (collection)</span>\n";
		}
		//level 4
		if($eIdentifier = $eParty->getElementsByTagName('identifier')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>identifier</span>\n";
			$level = ($level == 3 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>identifier</span>\n";
		}
		if($eLocation = $eParty->getElementsByTagName('location')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>location address</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>location address</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($bRelatedActivity = strpos($relatedObjectClassesStr, 'activity'))
		{
			$response .= "<span class='qa_ok' level='4'>related object (activity)</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>related object (activity)</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($eDescription = $eParty->getElementsByTagName('description')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>description</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>description</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($eSubject = $eParty->getElementsByTagName('subject')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>subject</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>subject</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($eExistenceDates = $eParty->getElementsByTagName('existencedates')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>existencedates</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>existencedates</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
	}
	elseif($objectClass == 'Activity')
	{
		//level 1 requirements
		if($eRegistryObject = $RegistryObjects->getElementsByTagName('registryObject')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>registry object</span>\n";
			$level = 1;
		}
		else
		{
			$response .= "<span class='qa_req' level='1'>registry object</span>\n";
			$level = 0;
		}
		if($eOriginatingSource = $RegistryObjects->getElementsByTagName('originatingSource')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>originating source</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>originating source</span>\n";
			$level = 0;
		}
		if($aRegistryObjectGroup = $eRegistryObject->getAttribute("group"))
		{
			$response .= "<span class='qa_ok' level='1'>group</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>group</span>\n";
			$level = 0;
		}
		if($eKey = $eRegistryObject->getElementsByTagName('key')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>key</span>\n";
			$key = $eKey->nodeValue;
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>key</span>\n";
			$level = 0;
		}
		if($eActivity = $eRegistryObject->getElementsByTagName('activity')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>class type (activity)</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>class type (activity)</span>\n";
			$level = 0;
		}
		if($aActivityType = $eActivity->getAttribute("type"))
		{
			$response .= "<span class='qa_ok' level='1'>activity type</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>activity type</span>\n";
			$level = 0;
		}
		//level 2
		if($eName = $eActivity->getElementsByTagName('name')->item(0))
		{
			$response .= "<span class='qa_ok' level='2'>name</span>\n";
			$level = ($level == 1 ? 2 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='2'>name</span>\n";
		}
		//level 3
		if($eDescription = $eActivity->getElementsByTagName('description')->item(0))
		{
			$response .= "<span class='qa_ok' level='3'>description</span>\n";
			$level = ($level == 2 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='3'>description</span>\n";
		}
		if($bRelatedParty = strpos($relatedObjectClassesStr, 'party'))
		{
			$response .= "<span class='qa_ok' level='3'>related object (party)</span>\n";
			$level = ($level == 3 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>related object (party)</span>\n";
			$level = ($level == 3 ? 2 : $level);
		}
		//level 4
		if($eLocation = $eActivity->getElementsByTagName('location')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>location address</span>\n";
			$level = ($level == 3 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>location address</span>\n";
		}
		if($bRelatedCollection = strpos($relatedObjectClassesStr, 'collection'))
		{
			$response .= "<span class='qa_ok' level='4'>related object (collection)</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='4'>related object (collection)</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($eSubject = $eActivity->getElementsByTagName('subject')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>subject</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>subject</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($eExistenceDates = $eActivity->getElementsByTagName('existencedates')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>existencedates</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>existencedates</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
	
	}
	elseif($objectClass == 'Service')
	{
		//level 1 requirements
		if($eRegistryObject = $RegistryObjects->getElementsByTagName('registryObject')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>registry object</span>\n";
			$level = 1;
		}
		else
		{
			$response .= "<span class='qa_req' level='1'>registry object</span>\n";
			$level = 0;
		}
		if($eOriginatingSource = $RegistryObjects->getElementsByTagName('originatingSource')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>originating source</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>originating source</span>\n";
			$level = 0;
		}
		if($aRegistryObjectGroup = $eRegistryObject->getAttribute("group"))
		{
			$response .= "<span class='qa_ok' level='1'>group</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>group</span>\n";
			$level = 0;
		}
		if($eKey = $eRegistryObject->getElementsByTagName('key')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>key</span>\n";
			$key = $eKey->nodeValue;
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>key</span>\n";
			$level = 0;
		}
		if($eService = $eRegistryObject->getElementsByTagName('service')->item(0))
		{
			$response .= "<span class='qa_ok' level='1'>class type (service)</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>class type (service)</span>\n";
			$level = 0;
		}
		if($aServiceType = $eService->getAttribute("type"))
		{
			$response .= "<span class='qa_ok' level='1'>service type</span>\n";
			$level = ($level == 1 ? 1 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='1'>service type</span>\n";
			$level = 0;
		}
		//level 2
		if($eName = $eService->getElementsByTagName('name')->item(0))
		{
			$response .= "<span class='qa_ok' level='2'>name</span>\n";
			$level = ($level == 1 ? 2 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='2'>name</span>\n";
		}
		//level 3
		if($eLocation = $eService->getElementsByTagName('location')->item(0))
		{
			$response .= "<span class='qa_ok' level='3'>location address</span>\n";
			$level = ($level == 2 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>location address</span>\n";
		}
		if($bRelatedCollection = strpos($relatedObjectClassesStr, 'collection'))
		{
			$response .= "<span class='qa_ok' level='3'>related object (collection)</span>\n";
			$level = ($level == 3 ? 3 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='3'>related object (collection)</span>\n";
			$level = ($level == 3 ? 2 : $level);
		}
		//level 4
		if($bRelatedParty = strpos($relatedObjectClassesStr, 'party'))
		{
			$response .= "<span class='qa_ok' level='4'>related object (party)</span>\n";
			$level = ($level == 3 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_req' level='4'>related object (party)</span>\n";
		}
		if($eDescription = $eService->getElementsByTagName('description')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>description</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>description</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
		if($eAccessPolicy = $eService->getElementsByTagName('accessPolicy')->item(0))
		{
			$response .= "<span class='qa_ok' level='4'>access policy</span>\n";
			$level = ($level == 4 ? 4 : $level);
		}
		else 
		{
			$response .= "<span class='qa_rec' level='4'>access policy</span>\n";
			$level = ($level == 4 ? 3 : $level);
		}
	
	}
	$response = "<div id='quality_level_text' ro_class='".$objectClass."' ro_key='".$key."'>\n<span class='qa_level' level='".$level."'>".$level."</span>\n".$response."</div>\n";	
	return $response;	
}




function runQuagmireCheckForRegistryObject($registryObjectKey, $dataSourceKey)
{
	
		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rifcs .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .= '                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .= '                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		$rifcs .= getRegistryObjectXML($registryObjectKey);
		$rifcs .= '</registryObjects>'; 
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";			
		}
		elseif(str_replace("<Service","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";			
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";			
		}	
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";			
		}
	
		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);  

		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		$relatedObjectClassesStr = '';
		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		$qualityTestResult = runQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
	  //  $errorCount = substr_count($qualityTestResult, 'class="error"'); 
		//$warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');                              
      //  $result = updateRegistryObjectQualityTestResult($registryObjectKey, $qualityTestResult, $errorCount, $warningCount);                            
		return $qualityTestResult;
}
function runQualityCheckForDraftRegistryObject($registryObjectKey, $dataSourceKey)
{
		$registryObject = getDraftRegistryObject($registryObjectKey,$dataSourceKey);
		$relatedObjectClassesStr = '';
		$rifcs = $registryObject[0]['rifcs'];	
		$objectClass = "";
		if(str_replace("<Collection","",$rifcs)!=$rifcs||str_replace("<collection","",$rifcs)!=$rifcs)
		{
			$objectClass = "Collection";			
		}
		elseif(str_replace("<Servive","",$rifcs)!=$rifcs||str_replace("<service","",$rifcs)!=$rifcs)
		{
			$objectClass = "Service";			
		}
		elseif(str_replace("<Activity","",$rifcs)!=$rifcs||str_replace("<activity","",$rifcs)!=$rifcs)
		{
			$objectClass = "Activity";			
		}	
		elseif(str_replace("<Party","",$rifcs)!=$rifcs||str_replace("<party","",$rifcs)!=$rifcs)
		{
			$objectClass = "Party";			
		}	
		
		$relRifcs = getRelatedXml($dataSourceKey,$rifcs,$objectClass);				
				
		$RegistryObjects = new DOMDocument();
		$RegistryObjects->loadXML($relRifcs);
		$relatedObjectClassesStr = getAllRelatedObjectClass($RegistryObjects, $dataSourceKey);
		$qualityTestResult = runQualityCheckonDom($RegistryObjects, $dataSourceKey, 'html', $relatedObjectClassesStr);
		$errorCount = substr_count($qualityTestResult, 'class="error"');                              
	    $warningCount = substr_count($qualityTestResult, 'class="warning"') + substr_count($qualityTestResult, 'class="info"');                              
        $result = updateDraftRegistryObjectQualityTestResult($registryObjectKey, $dataSourceKey, $qualityTestResult, $errorCount, $warningCount);     
        //return $result;// $registryObjectKey.','.$dataSourceKey.','.$qualityTestResult.','.$errorCount.','.$warningCount.'<br/>';                    		
}

?>
