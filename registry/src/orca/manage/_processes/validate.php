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

$json = file_get_contents("php://input");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if (($keyValue && $firstLoad) && $draft = getDraftRegistryObject($keyValue, $dataSourceValue)) 
{
	
	$jQueryMessages = '';
	$reverseLinks = 'true';
	
	$thisDataSource = getDataSources($dataSourceValue, null);
	if ($thisDataSource[0]['qa_flag'] == "t")
	{
		$jQueryMessages .= "<script>qaRequired = true;</script>";		
	}
	else
	{
		$jQueryMessages .= "<script>qaRequired = false;</script>";
	}
	
	$allow_reverse_internal_links = $thisDataSource[0]['allow_reverse_internal_links'];
	$allow_reverse_external_links = $thisDataSource[0]['allow_reverse_external_links'];
	if($allow_reverse_internal_links!='t' && $allow_reverse_external_links!='t') $reverseLinks='false';	

	$jQueryMessages .= runQualityCheck($draft[0]['rifcs'], $draft[0]['class'],$draft[0]['registry_object_data_source'], 'script',$reverseLinks);
	
	// Enable/disable editing FOR UNPRIVILEGED users in readOnly states
	if (!userIsORCA_LIAISON() && (in_array($draft[0]['status'], array(SUBMITTED_FOR_ASSESSMENT, ASSESSMENT_IN_PROGRESS))) )
	{
		echo "<script>$(\"#enableBtn\").attr(\"disabled\",\"disabled\");</script>";
	}
	
	// Generate Action buttons
	$buttons = array();
	switch ($draft[0]['status'])
	{
		case DRAFT:
			
		//	$buttons[] = "<input type='submit' name='SUBMIT_FOR_ASSESSMENT' value='Submit for Assessment' disabled='disabled' />";
			$buttons[] = "<input type='submit' name='DELETE_DRAFT' value='Delete' class='mmr_action' onclick='if (!confirm(\'Deleting this record may be irreversible. Are you sure you wish to continue?\')) { return; }' />";
			
		break;
		
		case SUBMITTED_FOR_ASSESSMENT:
			
			if (userIsORCA_QA()) 
			{
				$buttons[] = "<input type='submit' name='START_ASSESSMENT' value='Start Assessment' class='mmr_action' />";
			}
			if (userIsORCA_LIAISON()) 
			{
				$buttons[] = "<input type='submit' name='BACK_TO_DRAFT' value='Revert to Draft' class='mmr_action' />";
			}
			
		break;
		
		case ASSESSMENT_IN_PROGRESS:
			
			if (userIsORCA_QA())
			{
				$buttons[] = "<input type='submit' name='APPROVE' value='Approve' class='mmr_action' />";
				$buttons[] = "<input type='submit' name='MORE_WORK_REQUIRED' value='More Work Required' class='mmr_action' />";
			}
			
		break;
		
		
	}
		

	
	if(getQueryValue('userMode') == 'readOnly' && (userIsORCA_LIAISON() || in_array($draft[0]['status'], array(DRAFT, MORE_WORK_REQUIRED))))
	{
		$jQueryMessages .= "<script>";
		$jQueryMessages .= "setButtonBar(\"".implode("",$buttons)."\"); activeTab = '#preview'; activateTab(activeTab);";
		$jQueryMessages .= "</script>";
	}
	$jQueryMessages .= "<script>setStatusSpan('" . getRegistryObjectStatusSpan($draft[0]['status']) . "'); </script>";	
	
	
	print($jQueryMessages);	
	
} 
else if ($keyValue && $draft = getDraftRegistryObject($keyValue, $dataSourceValue)) 
{
	$jQueryMessages = '';
	$reverseLinks='true';		
	$thisDataSource = getDataSources($dataSourceValue, null);

	if ($thisDataSource[0]['qa_flag'] == "t")
	{
		$jQueryMessages .= "<script>qaRequired = true;</script>";		
	}
	else
	{
		$jQueryMessages .= "<script>qaRequired = false;</script>";
	}
	
	$allow_reverse_internal_links = $thisDataSource[0]['allow_reverse_internal_links'];
	$allow_reverse_external_links = $thisDataSource[0]['allow_reverse_external_links'];
	if($allow_reverse_internal_links!='t' && $allow_reverse_external_links!='t') $reverseLinks='false';		
	
	$jQueryMessages .= runQualityCheck($draft[0]['rifcs'], $draft[0]['class'], $draft[0]['registry_object_data_source'], 'script',$reverseLinks);
	runQualityLevelCheckForDraftRegistryObject($keyValue, $dataSourceValue);
	$draft = getDraftRegistryObject($keyValue, $dataSourceValue);
	$jQueryMessages .= "<script>qualityLevel = ". $draft[0]['quality_level']. ";</script>";
	$jQueryMessages .= "<script>setStatusSpan('" . getRegistryObjectStatusSpan($draft[0]['status']) . "'); </script>";
	$jQueryMessages .= "<script>qualityLevel = ". $draft[0]['quality_level'] .";</script>";
	print($jQueryMessages);	
	
}
else if($json)
{
	
	$a2xml = new assoc_array2xml();
	$rifcs = new DomDocument();
	$json2rif_xsl = new DomDocument();	
	//print($json);	
	$jQueryMessages = '';	
	$test_array = json_decode($json, true);
	$objectClass = $test_array['objectClass'];
	$objectDataSource = urldecode($test_array['mandatoryInformation']['dataSource']);
	//echo $objectClass;
	//var_dump($test_array);
	$xml__text = $a2xml->array2xml($test_array);
	$xml__text = str_replace ('%26', '&amp;', $xml__text);
	$xml__text = str_replace ('%', '\\', $xml__text);	
	$xml__text = preg_replace_callback('/\\\\u([0-9a-f]{4})/i','replace_unicode_escape_sequence', $xml__text );					
	$xml__text = preg_replace_callback('/\\\\([A-F0-9]{2})/i','replace_unicode_escape_sequence2', $xml__text );
	$rifcs->loadXML(trim($xml__text));
	$json2rif_xsl->load('../_xsl/json2rifcs.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($json2rif_xsl);
	$transformResult = $proc->transformToXML($rifcs);
	
	$thisDataSource = getDataSources($objectDataSource, null);
	$reverseLinks = 'true';
	$jQueryMessages = "<script>qualityLevel = 999;</script>";
	if ($thisDataSource[0]['qa_flag'] == "t")
	{
		$jQueryMessages .= "<script>qaRequired = true;</script>";		
	}
	else
	{
		$jQueryMessages .= "<script>qaRequired = false;</script>";
	}
	$allow_reverse_internal_links = $thisDataSource[0]['allow_reverse_internal_links'];
	$allow_reverse_external_links = $thisDataSource[0]['allow_reverse_external_links'];
	if($allow_reverse_internal_links!='t' && $allow_reverse_external_links!='t') $reverseLinks='false';	
		
	$jQueryMessages .= runQualityCheck($transformResult, $objectClass, $objectDataSource, 'script', $reverseLinks);
	$jQueryMessages .= "<script>setStatusSpan('" . getRegistryObjectStatusSpan('DRAFT') . " (unsaved)'); </script>";
	print($jQueryMessages);				
} 
else 
{
	print("<script>alert('Could not save - Error: No XML to validate.');</script>");	
}
