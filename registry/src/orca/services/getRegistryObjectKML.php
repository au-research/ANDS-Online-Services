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
$registryObject = getRegistryObject(getQueryValue('key'), true);

// Set the Content-Type header.
header("Content-Type: application/kml; charset=UTF-8", true);
header("Content-Disposition: inline; filename=registryObject.kml", true);

// BEGIN: XML Response
// =============================================================================
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
print('<kml xmlns="http://earth.google.com/kml/2.1"'."\n");
print('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n");
print('xsi:schemaLocation="http://earth.google.com/kml/2.1  http://code.google.com/apis/kml/schema/kml21.xsd">'."\n");
print('<Document>'."\n");
print('	<name>Registry Object Coverage</name>'."\n");
print('	<open>1</open>'."\n");
print(getKMLStyles());
if( $registryObject )
{
	print(getRegistryObjectKML($registryObject[0]['registry_object_key']));
}
print('</Document>'."\n");
print('</kml>');
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';
?>