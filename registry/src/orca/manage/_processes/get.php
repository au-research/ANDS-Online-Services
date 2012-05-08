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
if (!IN_ORCA) die('No direct access to this file is permitted.');

header("Content-Type: text/xml; charset=UTF-8", true);
		
if(in_array($keyValue,$defaultKeys))
{
	$blankRifcs = "<?xml version='1.0' encoding='UTF-8'?>\n";			
	$blankRifcs .= "<registryObjects>\n";
	$blankRifcs .= "   <registryObject>\n";
	$blankRifcs .= "        <key></key>\n";
	$blankRifcs .= "        <originatingSource></originatingSource>\n";
	$blankRifcs .= "        <".esc($keyValue).">\n"; 
	$blankRifcs .= "            <name></name>\n";     
	$blankRifcs .= "            <identifier></identifier>\n";  
	$blankRifcs .= "            <location></location>\n";
	$blankRifcs .= "            <relatedObject></relatedObject>\n";
	$blankRifcs .= "            <subject></subject>\n";
	$blankRifcs .= "            <description></description>\n";
	$blankRifcs .= "            <rights></rights>\n";
	$blankRifcs .= "            <coverage></coverage>\n";
	$blankRifcs .= "            <citationInfo></citationInfo>\n";
	$blankRifcs .= "            <relatedInfo></relatedInfo>\n";
	($keyValue == "service" ? "            <accessPolicy></accessPolicy>\n" : "");
	$blankRifcs .= "        </".esc($keyValue).">\n";
	$blankRifcs .= "    </registryObject>\n";
	$blankRifcs .= "</registryObjects>\n";			
	print ($blankRifcs);				
}
else if($registryObject = getDraftRegistryObject($keyValue , $dataSourceValue))
{
	print($registryObject[0]['rifcs']);
}
else if($registryObject = getRegistryObject($keyValue))
{
	print(getRegistryObjectXML($keyValue ));	
}
else
{
	print('<p>ERROR: Invalid key specified</p>');
}