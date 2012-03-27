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

// Make sure valid user is accessing the content (session-based authentication)
//session_start();
//$_SESSION["timeout"] += 30;

define('gDEFAULT_CONTEXT_ERROR', "Invalid Context");
define('gDEFAULT_TAGNOTFOUND_ERROR', "Element not available");

if (isset($_GET['cache_set']))
{
	require '../_includes/init.php';
	require 'orca_init.php';

	// Context of remote element
	$context = (isset($_GET['context']) ? $_GET['context'] : '');

	// Tag of remote element
	$tag = (isset($_GET['tag']) ? $_GET['tag'] : '');

	// Check for a valid context
	if (!preg_match('/[a-z]{1,20}/', $context) 
		|| !@file_exists('_includes/' . $context . '_strings.php')) 
	{
		die(gDEFAULT_CONTEXT_ERROR);		
	}
	$has_fragment = "%%HASFRAGMENT%%";
	// Include the context file
	require '_includes/' . $context . '_strings.php';

	$cacheSet = array();
	foreach ($_strings AS $key => $element)
	{
		if (strpos($key, $tag) === 0 || strpos($key, "*") === 0)
		{
			$cacheSet[$key] = $element;
		}
	}

	echo json_encode( $cacheSet );
	exit();
}

// Only include full orca_init.php if needed
if (isset($_GET['tag']) && (strpos($_GET['tag'], "mandatoryInformation") !== FALSE || strpos($_GET['tag'], "relatedObject") !== FALSE)) {
	require '../_includes/init.php';
	require 'orca_init.php';
} else {
	require '_includes/init.php';
}


//==============================================================================
// 
// Context of remote element
$context = (isset($_GET['context']) ? $_GET['context'] : '');

// Tag of remote element
$tag = (isset($_GET['tag']) ? $_GET['tag'] : '');

// Sequence number for element
$seq_number = (isset($_GET['seq_num']) ? $_GET['seq_num'] : '');
$seq_number = ($seq_number === "" ? '0' : $seq_number);

// Check for a valid context
if (!preg_match('/[a-z]{1,20}/', $context) 
	|| !@file_exists('_includes/' . $context . '_strings.php')) 
{
	die(gDEFAULT_CONTEXT_ERROR);		
}

// Include the context file
require '_includes/' . $context . '_strings.php';

// See if there is a matching tag in the $_strings array
$element_content = '';
if (isset($_strings[$tag])) 
{
	$element_content = $_strings[$tag];	
} 
elseif (isset($_strings[preg_replace("/[a-z]+_/","*_",$tag,1)]))
{
	// else display the default tag for this context
	$element_content = $_strings[preg_replace("/[a-z]+_/","*_",$tag,1)];
} 
elseif (isset($_strings['*']))
{
	// else display the default tag for this context
	$element_content = $_strings['*'];
} 
else 
{	
	// else display an error
	$element_content = gDEFAULT_TAGNOTFOUND_ERROR;
}


// Make any replacements (based on context)
switch ($context) 
{
	// Add registry object elements (i.e. description_1)
	case "add_registry_object_element":
		
		$replacements = array();
		
		/*
		if (getQueryValue('registry_object_status') == "live") {
			
			$registryObject = getRegistryObject(getQueryValue('registry_object_key'));
			$registryObjectKey = null;
			$dataSourceKey = null;
			$registryObjectRecordOwner = null;
			$registryObjectDataSourceRecordOwner = null;
			$registryObjectStatus = null;
			
			if ( !$registryObject )
			{
				die("Registry object with key: " . getQueryValue('registry_object_key') . " was not found");
			}
			else 
			{	
				$registryObjectKey = $registryObject[0]['registry_object_key'];
				$dataSourceKey = $registryObject[0]['data_source_key'];
				$dataSource = getDataSources($dataSourceKey, null);
				
				// Get the values that we'll need to check for conditional display and access.
				$registryObjectRecordOwner = $registryObject[0]['record_owner'];
				$registryObjectDataSourceRecordOwner = $dataSource[0]['record_owner'];
				
				// Check access.
				if( !(userIsDataSourceRecordOwner($registryObjectDataSourceRecordOwner) || (userIsORCA_ADMIN() && $registryObjectRecordOwner == SYSTEM)) )
				{
					die("You do not have permission to access this registry object (". getQueryValue('registry_object_key').")");
				}	
				$registryObject = $registryObject[0];
			}
			
			
			// now do some stuff with $registryObject
			
		}
		*/	
			
		// Fill in the sequence number values
		$seq_number = explode(":",$seq_number);
		for ($idx=0; $idx<count($seq_number); $idx++) {
			$replacements["SEQNUM" . ($idx+1)] = (isset($seq_number[$idx]) ? $seq_number[$idx] : 0); 
		}
		
		foreach ($replacements AS $find => $replace) {
			$element_content = str_replace("%%" . $find . "%%", 
											$replace, $element_content);
		}
		
		echo json_encode( array("rawHTML" => $element_content, "cbc" => (isset($_GET['cbc'])?$_GET['cbc']:''))); // array("context" => ""),
			
	break;

	case "SOLR_get_stuff":echo "yeah";break;
	
	// By default, just print the element content "as is"
	case "":
	default:
		echo $element_content;
}
