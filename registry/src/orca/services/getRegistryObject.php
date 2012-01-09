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


// Get the record from the database.
$registryObject = getRegistryObject(getQueryValue('key'));
$type = getQueryValue('type');
if($type=='')$type='xml';

if($type=='xml'){
	// Set the Content-Type header.
	header("Content-Type: text/xml; charset=UTF-8", true);
	$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	
	// BEGIN: XML Response
	// =============================================================================
	$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
	$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
	$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF2_SCHEMA_URI.'">'."\n";
	if( $registryObject )
	{
		$rifcs .= getRegistryObjectXML($registryObject[0]['registry_object_key']);
	}
	$rifcs .= "</registryObjects>\n";
	// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
	// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!
	$rifc2 = transformToRif2XML($rifcs);
	print $rifc2;
	
	// END: XML Response
	// =============================================================================
	require '../../_includes/finish.php';
	
}elseif($type=='plain'){
	// BEGIN: XML Response
	// =============================================================================
	$rifcs ='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
	$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
	$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF2_SCHEMA_URI.'">'."\n";
	if( $registryObject )
	{
		$rifcs .= getRegistryObjectXML($registryObject[0]['registry_object_key']);
	}
	$rifcs .= "</registryObjects>\n";
	// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
	// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!
	$rifc2 = transformToRif2XML($rifcs);
	print $rifc2;
}elseif($type=='download'){
	// Set the Content-Type header.
	header("Content-Type: text/xml; charset=UTF-8", true);
	header('Content-Disposition: attachment; filename='.$registryObject[0]['registry_object_key'].'-rifcs-download.xml');
	$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	
	// BEGIN: XML Response
	// =============================================================================
	$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
	$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
	$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF2_SCHEMA_URI.'">'."\n";
	if( $registryObject )
	{
		$rifcs .= getRegistryObjectXML($registryObject[0]['registry_object_key']);
	}
	$rifcs .= "</registryObjects>\n";
	// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
	// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!
	$rifc2 = transformToRif2XML($rifcs);
	print $rifc2;
	
	// END: XML Response
	// =============================================================================
	require '../../_includes/finish.php';
}


?>