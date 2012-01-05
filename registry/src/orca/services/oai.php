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

// Increase the execution timeout as we may have to deal with a large amount of data.
$executionTimeoutSeconds = 10*60;
ini_set("max_execution_time", "$executionTimeoutSeconds");

// Set the Content-Type header.
header("Content-Type: text/xml; charset=UTF-8", true);


// We must support both HTTP GET and POST.
$args = $_GET;
if( $_POST ) { $args = $_POST; }

$requestAttributes = '';
foreach( $args as $name => $value )
{
	$requestAttributes .= ' '.esc($name).'="'.esc($value).'"';
}

// Get the request verb.
$verb = getArgValue('verb', $args);


// BEGIN: XML Response
// =============================================================================
printOAIHeader();

// The six OAI-PMH verbs...
switch( $verb )
{
	case 'GetRecord':
		printOAIGetRecordXML($args, $requestAttributes);	
		break;
		
	case 'Identify':
		printOAIIdentifyXML($requestAttributes);
		break;
		
	case 'ListIdentifiers':
		printOAIListIdentifiersXML($args, $requestAttributes);
		break;
		
	case 'ListMetadataFormats':
		printOAIListMetadataFormatsXML($args, $requestAttributes);
		break;
		
	case 'ListRecords':
		printOAIListRecordsXML($args, $requestAttributes);
		break;
		
	case 'ListSets':
		printOAIListSetsXML($args, $requestAttributes);
		break;
		
	default:
		printOAIRequestAttributes("");
		print(getOAIErrorXML(OAIbadVerb, ''));
		break;
}

printOAIFooter();
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';
?>
