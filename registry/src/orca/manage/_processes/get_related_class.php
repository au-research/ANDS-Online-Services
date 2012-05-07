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

if (!isset($_POST['relations']))
{
	// key value, field name
	$relations = array(array($keyValue, str_Replace("_roclass","",urldecode(getQueryValue("fieldId")))));
}
else
{
	$relations = $_POST['relations'];
}

if ($relations) 
{

	foreach($relations AS $relation)
	{
		$keyValue = $relation[0];
		$fieldId = $relation[1] . "_roclass";

		if ($keyValue != "")
		{
			if($registryObject = getRegistryObject($keyValue,true))
			{	
				print ("<script>$('#".$fieldId."').val('".$registryObject[0]['registry_object_class']."');</script>");			
			} 
			else if($registryObject = getDraftRegistryObject($keyValue,$dataSourceValue))
			{	
				print ("<script>$('#".$fieldId."').val('".$registryObject[0]['class']."');</script>");			
				
			}
			else if($registryObject = getDraftRegistryObject($keyValue,null))
			{	
				print ("<script>$('#".$fieldId."').val('".$registryObject[0]['class']."');</script>");			
				
			}
			else 
			{
				print ("<script>$('#".$fieldId."').val('');</script>");
			}
		}
	}
}