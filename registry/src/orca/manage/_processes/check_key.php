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

$keyValue = rawurldecode($keyValue);

if ($keyValue)
{
	if(getRegistryObject($keyValue,true))
	{
		print ("<script>$('[name=\"warnings_mandatoryInformation_key\"]').html('');</script>");
		print ("<script>$('[name=\"warnings_mandatoryInformation_key\"]').hide();</script>");				
		print ("<script>$('[name=\"errors_mandatoryInformation_key\"]').html('');</script>");
		print ("<script>$('[name=\"errors_mandatoryInformation_key\"]').hide();</script>");				
		print ("<script>isUniqueKey = false;</script>");		
		print ("<script>SetErrors('errors_mandatoryInformation_key','A record with this Key already exists in the ANDS Registry');</script>");
		print ("<script>setTabs();</script>");					
	} 
	else if(getDraftRegistryObject($keyValue,$dataSourceValue))
	{

		print ("<script>$('[name=\"warnings_mandatoryInformation_key\"]').html('');</script>");
		print ("<script>$('[name=\"warnings_mandatoryInformation_key\"]').hide();</script>");				
		print ("<script>$('[name=\"errors_mandatoryInformation_key\"]').html('');</script>");
		print ("<script>$('[name=\"errors_mandatoryInformation_key\"]').hide();</script>");				
		print ("<script>isUniqueKey = false;</script>");		
		print ("<script>SetErrors('errors_mandatoryInformation_key','A draft record with this Key already exists');</script>");
		print ("<script>setTabs();</script>");						
	}
	else
	{


		print ("<script>$('[name=\"warnings_mandatoryInformation_key\"]').html('');</script>");
		print ("<script>$('[name=\"warnings_mandatoryInformation_key\"]').hide();</script>");				
		print ("<script>$('[name=\"errors_mandatoryInformation_key\"]').html('');</script>");
		print ("<script>$('[name=\"errors_mandatoryInformation_key\"]').hide();</script>");				
		print ("<script>isUniqueKey = true;</script>");
		print ("<script>$('#mandatoryInformation_tab').removeClass('error');</script>");
		print ("<script>setTabs();</script>");		
	}
	
}		