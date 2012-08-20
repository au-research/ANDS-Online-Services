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
$dataSource = getQueryValue('ds');
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
	$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
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
else if($type=='download')
{

		// Set the Content-Type header.
		
		header("Cache-Control: public"); 
		header('Pragma: public');
	//	header("Content-Type: text/xml; charset=UTF-8", true);
		header("Content-Type: application/force-download"); 
		header("Content-Type: application/octet-stream"); 
		header("Content-Type: application/download"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Transfer-Encoding: binary");
		header("Content-Description: File Transfer"); 
		header('Content-Disposition: attachment; filename='.$registryObject[0]['url_slug'].'-rifcs-download.xml');
		//header('Expires: 0');
		
		
		$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		
		// BEGIN: XML Response
		// =============================================================================
		$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		if( $registryObject )
		{
			$rifcs .= getRegistryObjectXML($registryObject[0]['registry_object_key']);
		}
		$rifcs .= "</registryObjects>\n";
		// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
		// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!

		$rifcs = transformToRif2XML($rifcs);
		
		header('Content-Length: ' . strlen($rifcs));
		
		// END: XML Response
		// =============================================================================
		require '../../_includes/finish.php';
}
elseif($type=='plain')
{
	
	if(!$dataSource)
    {
		// BEGIN: XML Response
		// =============================================================================
		$rifcs ='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
		$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
		$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
		if( $registryObject )
		{
			$rifcs .= getRegistryObjectXML($registryObject[0]['registry_object_key']);
		}
		$rifcs .= "</registryObjects>\n";
		// TODO : this is needed untill we stop having rifcs 1.0 elements in the database!!!
		// so delete it once the green and orange is imp[lemented + all data is migrated to rifcs 1.2 placeholders!!
		$rifc2 = transformToRif2XML($rifcs);
		print $rifc2;
	}
	else
	{
		header("Content-Type: text/xml; charset=UTF-8", true);
		$registryObject = getDraftRegistryObject(getQueryValue('key'), getQueryValue('ds'));
		if (getQueryValue('stripped'))
		{
			echo transformToStripFormData($registryObject[0]['rifcs']);
		}
		else
		{
			echo $registryObject[0]['rifcs'];
		}
	}
}
?>
