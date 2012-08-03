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
// OAI_PMH Settings

// Set the size of result sets for ListRecords and ListIdentifiers that will be
// handled with resumptionTokens.
define("OAI_LIST_SIZE", 100);

define("OAI_RT_EXPIRES_MINUTES", 10);

define("OAI_RT_LATEST",   0);
define("OAI_RT_PREVIOUS", 1);

// OAI-PMH error codes.
define('OAIbadArgument'             , 'badArgument');
define('OAIbadResumptionToken'      , 'badResumptionToken');
define('OAIbadVerb'                 , 'badVerb');
define('OAIcannotDisseminateFormat' , 'cannotDisseminateFormat');
define('OAIidDoesNotExist'          , 'idDoesNotExist');
define('OAInoRecordsMatch'          , 'noRecordsMatch');
define('OAInoMetaDataFormats'       , 'noMetaDataFormats');
define('OAInoSetHierachy'           , 'noSetHierachy');

// Generic OAI-PMH error descriptions.
$aoiErrors = array(
	OAIbadArgument             => 'The request includes illegal arguments, is missing required arguments, or values for arguments have an illegal syntax.',
	OAIbadResumptionToken      => 'The value of the resumptionToken argument is invalid or expired.',
	OAIbadVerb                 => 'The value of the verb argument is not a legal OAI-PMH verb, or the verb argument is missing.',
	OAIcannotDisseminateFormat => 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.',
	OAIidDoesNotExist          => 'The value of the identifier argument is unknown or illegal in this repository.',
	OAInoRecordsMatch          => 'The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.',
	OAInoMetaDataFormats       => 'There are no metadata formats available for the specified item.',
	OAInoSetHierachy           => 'The repository does not support sets.'
);

// OAI-PMH metadata prefixes.
define('OAI_SCHEMA_URI', 0);
define('OAI_NAMESPACE', 1);

define('OAI_DC_METADATA_PREFIX', 'oai_dc');
define('OAI_RIF_METADATA_PREFIX', 'rif');

$gORCA_OAI_METADATA_PREFIXES = array(
	      OAI_DC_METADATA_PREFIX  => array( OAI_SCHEMA_URI => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
	                                        OAI_NAMESPACE  => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
	                                       ),
	      OAI_RIF_METADATA_PREFIX => array( OAI_SCHEMA_URI => gRIF_SCHEMA_URI,
	                                        OAI_NAMESPACE  => 'http://ands.org.au/standards/rif-cs/registryObjects'
	                                      )
                                    );

function getArgValue($argName, $args)
{
	$value = '';
	if( isset($args[$argName]) )
	{
		$value = $args[$argName];
	}
	return $value;
}

function getOAIBaseURL()
{
	global $gActivities;

	$activity = getObject($gActivities, 'aORCA_SERVICE_OAI_DATA_PROVIDER');

	return esc($activity->path);
}

function getEarliestDateStamp()
{
	$earliestDateStamp = getXMLDateTime(getMinCreatedWhen());
	if( !$earliestDateStamp )
	{
		// An earliestDatestamp is required by the protocol so if there are no records then...
		$earliestDateStamp = getXMLDateTime(date("Y-m-d H:i:s"));
	}
	return $earliestDateStamp;
}

function getOAIDateGranularityMask($datetime)
{
	$mask = eDCT_FORMAT_ISO8601_DATE;
	if( strpos($datetime, "T") )
	{
		$mask = eDCT_FORMAT_ISO8601_DATETIMESEC_UTC;
	}
	return $mask;
}

function printOAIHeader()
{
	// OAI-PMH Specification  3.2.1
	print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
	// OAI-PMH Specification  3.2.2
	print('<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"'."\n");
	print('         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n");
	print('         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/'."\n");
	print('         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">'."\n");
	// OAI-PMH Specification  3.2.3
	print('  <responseDate>'.getXMLDateTime(date("Y-m-d H:i:s")).'</responseDate>'."\n");
}

function printOAIFooter()
{
	print("</OAI-PMH>\n");
}

function printOAIRequestAttributes($requestAttributes)
{
	print("  <request$requestAttributes>".getOAIBaseURL().'</request>'."\n");
}

// OAI-PMH Specification 4.1 GetRecord
// =============================================================================
function printOAIGetRecordXML($args, $requestAttributes)
{
	global $gORCA_OAI_METADATA_PREFIXES;
	$errors = false;
	$xml = '';
	$registryObject = null;

	// Check for the required identifier argument.
	// -------------------------------------------------------------------------
	$identifier = getArgValue('identifier', $args);
	if( !$identifier )
	{
		$requestAttributes = "";
		$errors = true;
		$xml .= getOAIErrorXML(OAIbadArgument, "Missing argument 'identifier'");
	}
	else
	{
		// Check to see if we can get a record for this identifier.
		$registryObject = getRegistryObject($identifier);
		if( !$registryObject )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAIidDoesNotExist, "");
		}
	}

	// Check for the required metadataPrefix argument.
	// -------------------------------------------------------------------------
	$metadataPrefix = getArgValue('metadataPrefix', $args);
	if( !$metadataPrefix )
	{
		$requestAttributes = "";
		$errors = true;
		$xml .= getOAIErrorXML(OAIbadArgument, "Missing argument 'metadataPrefix'");
	}
	else
	{
		// Check that we support this metadata format.
		// Note that we don't need to check at the object level, because this
		// characteristic is the same across all objects in the ORCA Registry.
		if( !isset($gORCA_OAI_METADATA_PREFIXES[$metadataPrefix]) )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAIcannotDisseminateFormat, "");
		}
	}

	printOAIRequestAttributes($requestAttributes);
	print($xml);

	// Generate the ouput.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		$dateStamp = getXMLDateTime($registryObject[0]['created_when']);
		$class = 'class:'.strtolower($registryObject[0]['registry_object_class']);
		$group = 'group:'.encodeOAISetSpec($registryObject[0]['object_group']);
		$source = 'dataSource:'.encodeOAISetSpec($registryObject[0]['data_source_key']);

		print "  <GetRecord>\n";
		print "    <record>\n";
		print "      <header>\n";
		print "        <identifier>".esc($identifier)."</identifier>\n";
		print "        <datestamp>".esc($dateStamp)."</datestamp>\n";
		print "        <setSpec>".esc($class)."</setSpec>\n";
		print "        <setSpec>".esc($group)."</setSpec>\n";
		print "        <setSpec>".esc($source)."</setSpec>\n";
		print "      </header>\n";

		if( $metadataPrefix == OAI_RIF_METADATA_PREFIX )
		{
			print "      <metadata>\n";
			print '        <registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
			print '                         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
			print '                         xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
			print getRegistryObjectXML($identifier);
			print "        </registryObjects>\n";
			print "      </metadata>\n";
		}

		if( $metadataPrefix == OAI_DC_METADATA_PREFIX )
		{
			print "      <metadata>\n";
			print '        <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" '."\n";
			print '                   xmlns:dc="http://purl.org/dc/elements/1.1/" '."\n";
			print '                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
			print '                   xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">'."\n";
			print getRegistryObjectOAIDCXMLElements($identifier);
			print "        </oai_dc:dc>\n";
			print "      </metadata>\n";
		}

		print "    </record>\n";
		print "  </GetRecord>\n";
	}
}

// OAI-PMH Specification 4.2 Identify
// =============================================================================
function printOAIIdentifyXML($requestAttributes)
{
	printOAIRequestAttributes($requestAttributes);

	$xml  = "  <Identify>\n";
	$xml .= "    <repositoryName>".esc(eINSTANCE_TITLE.' '.eAPP_TITLE)."</repositoryName>\n";
	$xml .= "    <baseURL>".getOAIBaseURL()."</baseURL>\n";
	$xml .= "    <protocolVersion>2.0</protocolVersion>\n";

	$adminEmail = gORCA_INSTANCE_ADMIN_EMAIL;
	// An admin e-mail of some sort is required by the protocol so...
	if( $adminEmail == '' ){ $adminEmail = 'oai@example.com'; }
	$xml .= "    <adminEmail>".esc($adminEmail)."</adminEmail>\n";

	$earliestDateStamp = getEarliestDateStamp();
	$xml .= "    <earliestDatestamp>".esc($earliestDateStamp)."</earliestDatestamp>\n";

	$xml .= "    <deletedRecord>no</deletedRecord>\n";
	$xml .= "    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>\n";
	$xml .= "  </Identify>\n";
	print($xml);
}

// OAI-PMH Specification 4.3 ListIdentifiers
// =============================================================================
function printOAIListIdentifiersXML($args, $requestAttributes)
{
	global $gORCA_OAI_METADATA_PREFIXES;
	$errors = false;
	$xml = '';
	$resumptionTokenXML = '';
	$registryObjects = null;
	$classes = '';
	$dataSourceKey = null;
	$objectGroup = null;
	$createdAfterInclusive = null;
	$createdBeforeInclusive = null;
	$resumptionTokenId = null;

	// Check for the exclusive resumptionToken argument.
	// -------------------------------------------------------------------------
	if( isset($args['resumptionToken']) )
	{
		$resumptionTokenId = getArgValue('resumptionToken', $args);
		if( !getResumptionToken($resumptionTokenId, null) )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAIbadResumptionToken, '[1] The value of the resumptionToken argument is invalid or expired.');
		}

		// If there are other args then resumptionToken isn't exlusive so...
		if( count($args) > 2 )
		{
			$requestAttributes = "";
			$errors = true;
			$xml .= getOAIErrorXML(OAIbadArgument, "resumptionToken is an exclusive argument.");
		}
	}
	else
	{
		// Check for the required metadataPrefix argument.
		// -------------------------------------------------------------------------
		$metadataPrefix = getArgValue('metadataPrefix', $args);
		if( !$metadataPrefix )
		{
			$requestAttributes = "";
			$errors = true;
			$xml .= getOAIErrorXML(OAIbadArgument, "Missing argument 'metadataPrefix'");
		}
		else
		{
			// Check that we support this metadata format.
			if( !isset($gORCA_OAI_METADATA_PREFIXES[$metadataPrefix]) )
			{
				$errors = true;
				$xml .= getOAIErrorXML(OAIcannotDisseminateFormat, "");
			}
		}

		// Check for the optional from argument.
		// -------------------------------------------------------------------------
		$from = getArgValue('from', $args);
		$fromMask = '';
		if( $from )
		{
			if( strtotime($from) )
			{
				$fromMask = getOAIDateGranularityMask($from);
				$createdAfterInclusive = formatDateTimeWithMask($from, $fromMask);
				if( $fromMask == eDCT_FORMAT_ISO8601_DATE ){ $createdAfterInclusive .= "T00:00:00Z"; }
			}
			else
			{
				$requestAttributes = "";
				$errors = true;
				$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'from' is not a valid ISO8601 UTC date.");
			}
		}

		// Check for the optional until argument.
		// -------------------------------------------------------------------------
		$until = getArgValue('until', $args);
		$untilMask = '';
		if( $until )
		{
			if( strtotime($until) )
			{
				$untilMask = getOAIDateGranularityMask($until);
				$createdBeforeInclusive = formatDateTimeWithMask($until, $untilMask);

				if( $from && $fromMask != $untilMask )
				{
					$requestAttributes = "";
					$errors = true;
					$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'until' is not the same granularity as 'from'.");
				}

				if( $untilMask == eDCT_FORMAT_ISO8601_DATE ){ $createdBeforeInclusive .= "T23:59:59Z"; }

				$earliestDateStamp = formatDateTimeWithMask(getEarliestDateStamp(), $untilMask);
				if( strtotime($createdBeforeInclusive) < strtotime($earliestDateStamp) )
				{
					$requestAttributes = "";
					$errors = true;
					$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'until' is before the earliest datestamp.");
				}
			}
			else
			{
				$requestAttributes = "";
				$errors = true;
				$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'until' is not a valid ISO8601 UTC date.");
			}
		}

		// Check for the optional set argument.
		// -------------------------------------------------------------------------
		$set = getArgValue('set', $args);
		if( $set )
		{
			$setSpec = explode(":", $set);

			if( count($setSpec) == 2 )
			{
				$setKey = $setSpec[0];
				$setValue = $setSpec[1];

				switch( $setKey )
				{
					case 'class':
						switch( $setValue )
						{
							case 'activity':
								$classes = $setValue;
								break;

							case 'collection':
								$classes = $setValue;
								break;

							case 'party':
								$classes = $setValue;
								break;

							case 'service':
								$classes = $setValue;
								break;

							default:
								$errors = true;
								$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
								break;
						}
						break;

					case 'group':
						$objectGroup = decodeOAISetSpec($setValue);
						break;

					case 'dataSource':
						$dataSourceKey = decodeOAISetSpec($setValue);
						break;

					default:
						$errors = true;
						$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
						break;
				}
			}
			else
			{
				$errors = true;
				$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
			}
		}
	} // end no resumptionToken

	// Get the records that match the arguments.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		if( $resumptionTokenId )
		{
			// It's a request identifying an incomplete list so
			// get the incomplete list identified by this resumption token.
			$resumptionToken = getResumptionToken($resumptionTokenId, null);
			if( $resumptionToken )
			{
				$completeListId    = $resumptionToken[0]['complete_list_id'];
				$firstRecordNumber = $resumptionToken[0]['first_record_number'];
				$completeListSize  = $resumptionToken[0]['complete_list_size'];
				$status            = $resumptionToken[0]['status'];
				$metadataPrefix    = $resumptionToken[0]['metadata_prefix'];

				$registryObjects = getIncompleteList($completeListId, $firstRecordNumber);

				if( ($firstRecordNumber + OAI_LIST_SIZE - 1) < $completeListSize )
				{
					// This ISN'T the last incomplete list needed to service the request.
					if( $status == OAI_RT_LATEST )
					{
						// This is a request for the last issued resumptionToken.
						// Delete any existing OAI_RT_PREVIOUS resumptionToken and
						// set the status of this resumptionToken to OAI_RT_PREVIOUS.
						updateResumptionTokens($completeListId);

						// Create a new resumptionToken for the next incomplete list.
						insertResumptionToken($completeListId, $firstRecordNumber+OAI_LIST_SIZE, $completeListSize, $metadataPrefix);

					}
					// Get the resumptionTokenXML.
					$resumptionTokenXML = getResumptionTokenXML($completeListId);
				}
				else
				{
					// This IS the last incomplete list needed to service the request.
					// Issue an empty resumptionToken.
					$resumptionTokenXML = getResumptionTokenXML(null);
				}
			}
			else
			{
				$errors = true;
				$xml .= getOAIErrorXML(OAIbadResumptionToken, '[2] The value of the resumptionToken argument is invalid or expired.');
			}
		}
		else
		{
			// It's a new request.
			$registryObjects = searchRegistry('', $classes, $dataSourceKey, $objectGroup, $createdBeforeInclusive, $createdAfterInclusive);

			if( $registryObjects && count($registryObjects) > OAI_LIST_SIZE )
			{
				// The list is larger than the incomplete list size so...
				$completeListId = insertCompleteList();

				if( $completeListId )
				{
					// Create a new resumptionToken for the next incomplete list.
					$firstRecordNumber = 1;
					$completeListSize = count($registryObjects);

					$error = insertResumptionToken($completeListId, $firstRecordNumber+OAI_LIST_SIZE, $completeListSize, $metadataPrefix);

					if( !$error )
					{
						// Build the complete list.
						for( $i = 0; $i < $completeListSize; $i++ )
						{
							insertCompleteListRecord($completeListId, $i+1, $registryObjects[$i]['registry_object_key']);
						}

						// Get the first incomplete list.
						$registryObjects = getIncompleteList($completeListId, $firstRecordNumber);

						// Get the resumptionTokenXML.
						$resumptionTokenXML = getResumptionTokenXML($completeListId);
					}
					else
					{
						$errors = true;
						$xml .= getOAIErrorXML(OAInoRecordsMatch, "A server error resulted in no records being returned.");
					}
				}
				else
				{
					$errors = true;
					$xml .= getOAIErrorXML(OAInoRecordsMatch, "A server error resulted in no records being returned.");
				}
			}
		}
		if( !$registryObjects )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
		}
	}

	printOAIRequestAttributes($requestAttributes);
	print($xml);

	// Generate the ouput.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		print "  <ListIdentifiers>\n";
		foreach( $registryObjects as $registryObject )
		{
			$identifier = $registryObject['registry_object_key'];
			$dateStamp = getXMLDateTime($registryObject['created_when']);
			$class = 'class:'.strtolower($registryObject['registry_object_class']);
			$group = 'group:'.encodeOAISetSpec($registryObject['object_group']);
			$source = 'dataSource:'.encodeOAISetSpec($registryObject['data_source_key']);

			print "    <header>\n";
			print "      <identifier>".esc($identifier)."</identifier>\n";
			print "      <datestamp>".esc($dateStamp)."</datestamp>\n";
			print "      <setSpec>".esc($class)."</setSpec>\n";
			print "      <setSpec>".esc($group)."</setSpec>\n";
			print "      <setSpec>".esc($source)."</setSpec>\n";
			print "    </header>\n";
		}
		print "    $resumptionTokenXML\n";
		print "  </ListIdentifiers>\n";
	}
}

// OAI-PMH Specification 4.4 ListMetadataFormats
// =============================================================================
function printOAIListMetadataFormatsXML($args, $requestAttributes)
{
	global $gORCA_OAI_METADATA_PREFIXES;
	$errors = false;
	$xml = '';

	// Check for the optional identifier argument.
	// -------------------------------------------------------------------------
	$identifier = getArgValue('identifier', $args);
	if( $identifier )
	{
		$registryObject = getRegistryObject($identifier);
		if( !$registryObject )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAIidDoesNotExist, "");
		}
	}

	printOAIRequestAttributes($requestAttributes);
	print($xml);

	// Generate the ouput.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		print "  <ListMetadataFormats>\n";
		foreach( $gORCA_OAI_METADATA_PREFIXES as $prefix => $values)
		{
		    $metadataPrefix = $prefix;
		    $schema = $values[OAI_SCHEMA_URI];
		    $metadataNamespace = $values[OAI_NAMESPACE];

			print "    <metadataFormat>\n";
		    print "      <metadataPrefix>$metadataPrefix</metadataPrefix>\n";
		    print "      <schema>$schema</schema>\n";
		    print "      <metadataNamespace>$metadataNamespace</metadataNamespace>\n";
		    print "    </metadataFormat>\n";
		}
		print "  </ListMetadataFormats>\n";
	}
}

// OAI-PMH Specification 4.5 ListRecords
// =============================================================================
function printOAIListRecordsXML($args, $requestAttributes)
{
	global $gORCA_OAI_METADATA_PREFIXES;
	$errors = false;
	$xml = '';
	$resumptionTokenXML = '';
	$registryObjects = null;
	$classes = '';
	$dataSourceKey = null;
	$objectGroup = null;
	$createdAfterInclusive = null;
	$createdBeforeInclusive = null;
	$resumptionTokenId = null;
	$nlaSet = null;

	// Check for the exclusive resumptionToken argument.
	// -------------------------------------------------------------------------
	if( isset($args['resumptionToken']) )
	{
		$resumptionTokenId = getArgValue('resumptionToken', $args);
		if( !getResumptionToken($resumptionTokenId, null) )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAIbadResumptionToken, '[1] The value of the resumptionToken argument is invalid or expired.');
		}

		// If there are other args then resumptionToken isn't exlusive so...
		if( count($args) > 2 )
		{
			$requestAttributes = "";
			$errors = true;
			$xml .= getOAIErrorXML(OAIbadArgument, "resumptionToken is an exclusive argument.");
		}
	}
	else
	{
		// Check for the required metadataPrefix argument.
		// -------------------------------------------------------------------------
		$metadataPrefix = getArgValue('metadataPrefix', $args);
		if( !$metadataPrefix )
		{
			$requestAttributes = "";
			$errors = true;
			$xml .= getOAIErrorXML(OAIbadArgument, "Missing argument 'metadataPrefix'");
		}
		else
		{
			// Check that we support this metadata format.
			if( !isset($gORCA_OAI_METADATA_PREFIXES[$metadataPrefix]) )
			{
				$errors = true;
				$xml .= getOAIErrorXML(OAIcannotDisseminateFormat, "");
			}
		}

		// Check for the optional from argument.
		// -------------------------------------------------------------------------
		$from = getArgValue('from', $args);
		$fromMask = '';
		if( $from )
		{
			if( strtotime($from) )
			{
				$fromMask = getOAIDateGranularityMask($from);
				$createdAfterInclusive = formatDateTimeWithMask($from, $fromMask);
				if( $fromMask == eDCT_FORMAT_ISO8601_DATE ){ $createdAfterInclusive .= "T00:00:00Z"; }
			}
			else
			{
				$requestAttributes = "";
				$errors = true;
				$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'from' is not a valid ISO8601 UTC date.");
			}
		}

		// Check for the optional until argument.
		// -------------------------------------------------------------------------
		$until = getArgValue('until', $args);
		$untilMask = '';
		if( $until )
		{
			if( strtotime($until) )
			{
				$untilMask = getOAIDateGranularityMask($until);
				$createdBeforeInclusive = formatDateTimeWithMask($until, $untilMask);

				if( $from && $fromMask != $untilMask )
				{
					$requestAttributes = "";
					$errors = true;
					$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'until' is not the same granularity as 'from'.");
				}

				if( $untilMask == eDCT_FORMAT_ISO8601_DATE ){ $createdBeforeInclusive .= "T23:59:59Z"; }

				$earliestDateStamp = formatDateTimeWithMask(getEarliestDateStamp(), $untilMask);
				if( strtotime($createdBeforeInclusive) < strtotime($earliestDateStamp) )
				{
					$requestAttributes = "";
					$errors = true;
					$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'until' is before the earliest datestamp.");
				}
			}
			else
			{
				$requestAttributes = "";
				$errors = true;
				$xml .= getOAIErrorXML(OAIbadArgument, "Optional argument 'until' is not a valid ISO8601 UTC date.");
			}
		}

		// Check for the optional set argument.
		// -------------------------------------------------------------------------
		$set = getArgValue('set', $args);
		if( $set )
		{
			$setSpec = explode(":", $set);

			if( count($setSpec) == 2 )
			{
				$setKey = $setSpec[0];
				$setValue = $setSpec[1];

				switch( $setKey )
				{
					case 'class':
						switch( $setValue )
						{
							case 'activity':
								$classes = $setValue;
								break;

							case 'collection':
								$classes = $setValue;
								break;

							case 'party':
								$classes = $setValue;
								break;

							case 'service':
								$classes = $setValue;
								break;

							default:
								$errors = true;
								$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
								break;
						}
						break;

					case 'group':
						$objectGroup = decodeOAISetSpec($setValue);
						break;

					case 'dataSource':
						$dataSourceKey = decodeOAISetSpec($setValue);
						break;

					case 'nlaSet':
						$nlaSet = decodeOAISetSpec($setValue);
						break;

					default:
						$errors = true;
						$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
						break;
				}
			}
			else
			{
				$errors = true;
				$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
			}
		}
	} // end no resumptionToken

	// Get the records that match the arguments.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		if( $resumptionTokenId )
		{
			// It's a request identifying an incomplete list so
			// get the incomplete list identified by this resumption token.
			$resumptionToken = getResumptionToken($resumptionTokenId, null);
			if( $resumptionToken )
			{
				$completeListId    = $resumptionToken[0]['complete_list_id'];
				$firstRecordNumber = $resumptionToken[0]['first_record_number'];
				$completeListSize  = $resumptionToken[0]['complete_list_size'];
				$status            = $resumptionToken[0]['status'];
				$metadataPrefix    = $resumptionToken[0]['metadata_prefix'];


				//$registryObjects = 	getIncompleteListNLA($completeListId, $firstRecordNumber);
				$registryObjects = 	getIncompleteList($completeListId, $firstRecordNumber);

				if( ($firstRecordNumber + OAI_LIST_SIZE - 1) < $completeListSize )
				{
					// This ISN'T the last incomplete list needed to service the request.
					if( $status == OAI_RT_LATEST )
					{
						// This is a request for the last issued resumptionToken.
						// Delete any existing OAI_RT_PREVIOUS resumptionToken and
						// set the status of this resumptionToken to OAI_RT_PREVIOUS.
						updateResumptionTokens($completeListId);

						// Create a new resumptionToken for the next incomplete list.
						insertResumptionToken($completeListId, $firstRecordNumber+OAI_LIST_SIZE, $completeListSize, $metadataPrefix);

					}
					// Get the resumptionTokenXML.
					$resumptionTokenXML = getResumptionTokenXML($completeListId);
				}
				else
				{
					// This IS the last incomplete list needed to service the request.
					// Issue an empty resumptionToken.
					$resumptionTokenXML = getResumptionTokenXML(null);
				}
			}
			else
			{
				$errors = true;
				$xml .= getOAIErrorXML(OAIbadResumptionToken, '[2] The value of the resumptionToken argument is invalid or expired.');
			}
		}
		else
		{
			// It's a new request.
			if($nlaSet==null)
			{
				$registryObjects = searchRegistry('', $classes, $dataSourceKey, $objectGroup, $createdBeforeInclusive, $createdAfterInclusive);
			}
			else
			{
				$registryObjects = getSpecialObjectSet('nlaSet', $nlaSet);
			}

			if( $registryObjects && count($registryObjects) > OAI_LIST_SIZE )
			{
				// The list is larger than the incomplete list size so...
				$completeListId = insertCompleteList();

				if( $completeListId )
				{
					// Create a new resumptionToken for the next incomplete list.
					$firstRecordNumber = 1;
					$completeListSize = count($registryObjects);

					$error = insertResumptionToken($completeListId, $firstRecordNumber+OAI_LIST_SIZE, $completeListSize, $metadataPrefix);

					if( !$error )
					{
						// Build the complete list.
						for( $i = 0; $i < $completeListSize; $i++ )
						{
							insertCompleteListRecord($completeListId, $i+1, $registryObjects[$i]['registry_object_key']);
						}


						$registryObjects = getIncompleteList($completeListId, $firstRecordNumber);


						// Get the resumptionTokenXML.
						$resumptionTokenXML = getResumptionTokenXML($completeListId);
					}
					else
					{
						$errors = true;
						$xml .= getOAIErrorXML(OAInoRecordsMatch, "A server error resulted in no records being returned.");
					}
				}
				else
				{
					$errors = true;
					$xml .= getOAIErrorXML(OAInoRecordsMatch, "A server error resulted in no records being returned.");
				}
			}
		}
		if( !$registryObjects )
		{
			$errors = true;
			$xml .= getOAIErrorXML(OAInoRecordsMatch, "");
		}
	}

	printOAIRequestAttributes($requestAttributes);
	print($xml);

	// Generate the ouput.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		print "  <ListRecords>\n";
		foreach( $registryObjects as $registryObject )
		{
			$identifier = $registryObject['registry_object_key'];
			$dateStamp = getXMLDateTime($registryObject['created_when']);
			$class = 'class:'.strtolower($registryObject['registry_object_class']);
			$group = 'group:'.encodeOAISetSpec($registryObject['object_group']);
			$source = 'dataSource:'.encodeOAISetSpec($registryObject['data_source_key']);
			if($nlaSet)
			$isil = 'isil:'.$registryObject['isil_value'];

			print "    <record>\n";
			print "      <header>\n";
			print "        <identifier>".esc($identifier)."</identifier>\n";
			print "        <datestamp>".esc($dateStamp)."</datestamp>\n";
			print "        <setSpec>".esc($class)."</setSpec>\n";
			print "        <setSpec>".esc($group)."</setSpec>\n";
			print "        <setSpec>".esc($source)."</setSpec>\n";
			if($nlaSet){
			print "        <setSpec>".esc($isil)."</setSpec>\n";}
			print "      </header>\n";

			if( $metadataPrefix == OAI_RIF_METADATA_PREFIX )
			{
				print "      <metadata>\n";
				print '        <registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
				print '                         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
				print '                         xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
				print getRegistryObjectXML($identifier);
				print "        </registryObjects>\n";
				print "      </metadata>\n";
			}

			if( $metadataPrefix == OAI_DC_METADATA_PREFIX )
			{
				print "      <metadata>\n";
				print '        <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" '."\n";
				print '                   xmlns:dc="http://purl.org/dc/elements/1.1/" '."\n";
				print '                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
				print '                   xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">'."\n";
				print getRegistryObjectOAIDCXMLElements($identifier);
				print "        </oai_dc:dc>\n";
				print "      </metadata>\n";
			}
			print "    </record>\n";
		}
		print "    $resumptionTokenXML\n";
		print "  </ListRecords>\n";
	}
}

// OAI-PMH Specification 4.6 ListSets
// =============================================================================
function printOAIListSetsXML($args, $requestAttributes)
{
	global $gORCA_OAI_SET_SPECS;
	$errors = false;
	$xml = '';

	// We never issue a resumptionToken for ListSets so...
	// -------------------------------------------------------------------------
	if( isset($args['resumptionToken']) )
	{
		$errors = true;
		$xml .= getOAIErrorXML(OAIbadResumptionToken, '[1] The value of the resumptionToken argument is invalid or expired.');
	}

	printOAIRequestAttributes($requestAttributes);
	print($xml);

	// Generate the ouput.
	// -------------------------------------------------------------------------
	if( !$errors )
	{
		print "  <ListSets>\n";

		// The classes of registry object - class:
		print "    <set>\n";
	    print "      <setSpec>class:activity</setSpec>\n";
	    print "      <setName>Activities</setName>\n";
	    print "    </set>\n";
		print "    <set>\n";
	    print "      <setSpec>class:collection</setSpec>\n";
	    print "      <setName>Collections</setName>\n";
	    print "    </set>\n";
		print "    <set>\n";
	    print "      <setSpec>class:party</setSpec>\n";
	    print "      <setName>Parties</setName>\n";
	    print "    </set>\n";
		print "    <set>\n";
	    print "      <setSpec>class:service</setSpec>\n";
	    print "      <setName>Services</setName>\n";
	    print "    </set>\n";

	    // Registry Object Groups - group:
	    $groups = getObjectGroups();
	    if( $groups )
	    {
	    	foreach( $groups as $group )
			{
				print "    <set>\n";
			    print "      <setSpec>group:".esc(encodeOAISetSpec($group['object_group']))."</setSpec>\n";
			    print "      <setName>Registry objects in group '".esc($group['object_group'])."'</setName>\n";
			    print "    </set>\n";
			}
	    }

	    // Data Sources - dataSource:
	    $dataSources = getDataSources(null, null);
	    if( $dataSources )
	    {
	    	foreach( $dataSources as $dataSource )
			{
				print "    <set>\n";
			    print "      <setSpec>dataSource:".esc(encodeOAISetSpec($dataSource['data_source_key']))."</setSpec>\n";
			    print "      <setName>Registry objects from data source '".esc($dataSource['title'])."'</setName>\n";
			    print "    </set>\n";
			}
	    }

		print "  </ListSets>\n";
	}
}
// =============================================================================
function getResumptionTokenXML($completeListId)
{
	$xml = '<resumptionToken';
	if( $completeListId )
	{
		$resumptionToken = getResumptionToken(null, $completeListId);
		$resumptionTokenId = 'ErrorGettingToken';

		if( $resumptionToken )
		{
			$resumptionTokenId = $resumptionToken[0]['resumption_token_id'];
			$firstRecordNumber = $resumptionToken[0]['first_record_number'];
			$expirationDate   = $resumptionToken[0]['expiration_date'];
			$completeListSize  = $resumptionToken[0]['complete_list_size'];

			$cursor = $firstRecordNumber - OAI_LIST_SIZE - 1;

			$xml .= ' expirationDate="'.esc(getXMLDateTime($expirationDate)).'"';
			$xml .= ' completeListSize="'.esc($completeListSize).'"';
			$xml .= ' cursor="'.esc($cursor).'"';
		}
		$xml .= '>'.esc($resumptionTokenId);
	}
	else
	{
		$xml .= '>';
	}
	$xml .= '</resumptionToken>';

	cleanupCompleteLists();

	return $xml;
}

// =============================================================================
function encodeOAISetSpec($rawSpec)
{
	$encodedSpec = preg_replace('/%([0-9][0-9])/', '0x$1', rawurlencode($rawSpec));

	return $encodedSpec;
}

function decodeOAISetSpec($encodedSpec)
{
	$rawSpec = rawurldecode(preg_replace('/0x([0-9][0-9])/', '%$1', $encodedSpec));

	return $rawSpec;
}

// =============================================================================
function getOAIErrorXML($code, $description)
{
	global $aoiErrors;

	if( !$description )
	{
		// Get the generic description.
		$description = $aoiErrors[$code];
	}
	$xml = '  <error code="'.esc($code).'">'.esc($description).'</error>'."\n";
	return $xml;
}

// =============================================================================
function getRegistryObjectOAIDCXMLElements($registryObjectKey)
{
	global $host, $rda_root;
	$xml = '';
	$registryObject = getRegistryObject($registryObjectKey);

	if( $registryObject )
	{
		//<element ref="dc:title"/>
		$names = getComplexNames($registryObjectKey);
		if( $names )
		{
			$xml .= "          <dc:title>";
			foreach( $names as $name )
			{
				$nameParts = getNameParts($name['complex_name_id']);
				if($name["type"]=="primary"||$name["type"]=="abbreviated")
				{
					foreach( $nameParts as $namePart)
					{
						$xml .= esc($namePart['value']);
					}
				}
			}
			$xml .=	"</dc:title>\n";
		}


	    //<element ref="dc:identifier"/>
		$electronicAddresses = getRegistryObjectElectronicAddresses($registryObjectKey);
		if($electronicAddresses)
		{
			foreach($electronicAddresses as $electronicAddress)
			{
				// spec: collection/location/address/electronic[@type='url']/value
				if (strtolower($electronicAddress['type']) == "url")
				{
					$xml .= "          <dc:identifier>".esc($electronicAddress['value'])."</dc:identifier>\n";
				}

			}
		}
		$identifiers = getIdentifiers($registryObjectKey);
		if( $identifiers )
		{
			foreach( $identifiers as $identifier )
			{
				if($identifier['type']=="handle"){
					$xml .= "          <dc:identifier>".str_replace("hdl:","http://hdl.handle.net/102.100.100/14",esc($identifier['value']))."</dc:identifier>\n";
				}
				elseif($identifier['type']=="doi"){
					$xml .= "          <dc:identifier>http://dx.doi.org/".esc($identifier['value'])."</dc:identifier>\n";
				}
				elseif($identifier['type']=="url"||$identifier['type']=="uri"||$identifier['type']=="purl")
				{
					$xml .= "          <dc:identifier>".esc($identifier['value'])."</dc:identifier>\n";
				}
				else
				{
					$xml .= "          <dc:identifier>".esc($identifier['value'])." (".esc($identifier['type']).")</dc:identifier>\n";
				}
			}
		}

		$xml .= "          <dc:identifier>http://".$host.'/'.$rda_root . '/view.php?key='.esc(urlencode($registryObjectKey))."</dc:identifier>\n";

		//<element ref="dc:description"/>
	    //<element ref="dc:rights" />
		$descriptions = getDescriptions($registryObjectKey);
		if( $descriptions )
		{
			foreach( $descriptions as $description )
			{
				if(esc($description['type'])=='rights'||esc($description['type'])=='accessRights')
				{
					$xml .= "          <dc:rights>".esc($description['value'])."</dc:rights>\n";
				}
				else
				{
					$xml .= "          <dc:description>".esc($description['value'])."</dc:description>\n";
				}
			}
		}

		//<element ref="dc:subject"/>
		$subjects = getSubjects($registryObjectKey);
		if( $subjects )
		{
			foreach( $subjects as $subject )
			{
				if(strtoupper($subject["type"])=="ANZSRC-FOR"||strtoupper($subject["type"])=="ANZSRC-SEO"||strtoupper($subject["type"])=="ANZSRC-TOA"||strtoupper($subject["type"])=="RFCD")
				{
					switch( strtoupper($subject["type"]) )
					{
					// ---------------------------------------------
					// RFCD
					// ---------------------------------------------
					case 'RFCD':
						$value = getNameForVocabSubject('rfcd',  $subject["value"]);
						break;

					// ---------------------------------------------
					// ANZSRC
					// ---------------------------------------------
					case 'ANZSRC-FOR':
						$value = getNameForVocabSubject('ANZSRC-FOR', $subject["value"]);
						break;

					case 'ANZSRC-SEO':
						$value = getNameForVocabSubject('ANZSRC-SEO', $subject["value"]);
						break;

					case 'ANZSRC-TOA':
						$value = getNameForVocabSubject('ANZSRC-TOA', $subject["value"]);
						break;

		    		default:
		    			break;
					}

					if($value)
					{
						$xml .= "          <dc:subject>".esc($value)."</dc:subject>\n";
					}
					else
					{
						// Monica has asked for this to be removed if it doesn't match a valid code
						//$xml .= "          <dc:subject>".esc($subject["type"]).":".esc($subject["value"])."</dc:subject>\n";
					}
				}else{
					if($subject['value'])$xml .= "          <dc:subject>".esc($subject['value'])."</dc:subject>\n";
				}
			}
		}

		//<element ref="dc:type"/>
		if($registryObject[0]['type']){
				$xml .= "          <dc:type>".esc($registryObject[0]['type'])."</dc:type>\n";
		}

		//<element ref="dc:coverage"/>
		$coverages = getCoverage($registryObjectKey);
		if( $coverages )
		{
			foreach( $coverages as $coverage )
			{

				$spatialCoverages = getSpatialCoverage($coverage['coverage_id']);
				if($spatialCoverages)
				{
					foreach($spatialCoverages as $spatialCoverage)
					{
						$xml .= "          <dc:coverage>Spatial:".str_replace("text","",esc($spatialCoverage['type'])).":".esc($spatialCoverage['value'])."</dc:coverage>\n";
					}
				}

				$temporalCoverages = getTemporalCoverage($coverage['coverage_id']);
				if($temporalCoverages)
				{
					foreach($temporalCoverages as $temporalCoverage)
					{
							$temporalDates = getTemporalCoverageDate($temporalCoverage['temporal_coverage_id']);
							$coverageDates = '';
							foreach($temporalDates as $temporalDate)
							{

								if($temporalDate['type']=='dateFrom')
								{
									$coverageDates .= ' from '.str_replace("T00:00:00Z","",str_replace("T23:59:59Z","",esc($temporalDate['value'])));
								}

								if($temporalDate['type']=='dateTo')
								{
									$coverageDates .= ' to '.str_replace("T00:00:00Z","",str_replace("T23:59:59Z","",esc($temporalDate['value'])));
								}

							}
							$xml .= "          <dc:coverage>Temporal:".$coverageDates."</dc:coverage>\n";

							$temporalTexts = getTemporalCoverageText($temporalCoverage['temporal_coverage_id']);
							if($temporalTexts){
								foreach($temporalTexts as $temporalText)
								{
									$xml .= "          <dc:coverage>Temporal:".esc($temporalText['value'])."</dc:coverage>\n";
								}
							}
					}
				}

			}
		}

		//<element ref="dc:publisher"/>
		if($registryObject[0]['object_group']!='Publish My Data'){
			$xml .= "          <dc:publisher>".$registryObject[0]['object_group']."</dc:publisher>\n";
		}

		//<element ref="dc:contributor"/>
		$contributors = getRelatedObjects($registryObjectKey);
		if($contributors)
		{
			foreach($contributors as $contributor)
			{
				$relations = getRelationDescriptions(esc($contributor['relation_id']));

				$Names = getNames($contributor['related_registry_object_key']);

				if($Names)
				{
					$contributorName ='';
					foreach($Names as $Name)
					{
						$contributorName .= esc($Name['value']);
					}
				}
				if(trim($contributorName)){
					$xml .= "          <dc:contributor>".$contributorName." (".esc($relations[0]['type']) .")</dc:contributor>\n";
				}
				$contributorName ='';
			}
		}

		$relatedInfos = getRelatedInfo($registryObjectKey);
		if( $relatedInfos )
		{
			foreach( $relatedInfos as $relatedInfo )
			{
				if($relatedInfo['identifier_type']=="url"||$relatedInfo['identifier_type']=="uri"||$relatedInfo['identifier_type']=="purl"||$relatedInfo['identifier_type']=="handle")
				{
					$xml .= "          <dc:relation>".esc($relatedInfo['identifier'])."</dc:relation>\n";
				}
				else
				{
					if($relatedInfo['identifier_type']){
						$xml .= "          <dc:relation>".esc($relatedInfo['identifier_type']).":".esc($relatedInfo['identifier'])."</dc:relation>\n";
					}
					else {
						$xml .= "          <dc:relation>".($relatedInfo['value'])."</dc:relation>\n";
					}
				}
			}
		}


	}
	return $xml;
}
function getNameForVocabSubject($vocabId, $vocabTermId)
{
	$termName = '';
	$term = null;
	$term = getTermsForVocabByIdentifier($vocabId, $vocabTermId);
	if ($term != null)
	{
		$termName = $term[0]['name'];
	}

	return $termName;

}
?>
