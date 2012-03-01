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

		
$rawRecords = getRawRecords(urldecode(getQueryValue("key")), urldecode(getQueryValue("data_source")), date("r", urldecode(getQueryValue("version")-1)));

if(!$rawRecords)
{
	die("Error: Record could not be loaded.");
}

$rifcs = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$rifcs .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
$rifcs .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
$rifcs .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";
echo $rifcs;
echo $rawRecords[0]["rifcs_fragment"];
echo "\n</registryObjects>\n";