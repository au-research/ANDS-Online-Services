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
if($json)
{

	$a2xml = new assoc_array2xml();
	$rifcs = new DomDocument();
	$json2rif_xsl = new DomDocument();
	
	$test_array = json_decode($json, true);

	$objectClass = $test_array['objectClass'];
	$test_array['mandatoryInformation']['key'] = trim(urldecode($test_array['mandatoryInformation']['key']));
	$objectDataSource = urldecode($test_array['mandatoryInformation']['dataSource']);
	$dateCreated = urldecode($test_array['mandatoryInformation']['dateAccessioned']);
	$dateCreated = str_replace ('__THEPLUSSIGN__', '+', $dateCreated);
	
	$xml__text = $a2xml->array2xml($test_array);
//	print_r($xml__text);
//	exit();
	$xml__text = str_replace ('__THEPLUSSIGN__', '+', $xml__text);
	$xml__text = str_replace ('%26', '&amp;', $xml__text);

	$rifcs->loadXML(trim($xml__text));

	$json2rif_xsl->load('../_xsl/json2rifcs.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($json2rif_xsl);
	$rifcs = $proc->transformToDoc($rifcs);
	$transformResult = $rifcs->saveXML($rifcs);
	
	$registryObjects = new DomDocument();
	$registryObjects->loadXML($transformResult);
	$xs = 'rif';

	$gXPath = new DOMXpath($registryObjects);
	$defaultNamespace = $gXPath->evaluate('/*')->item(0)->namespaceURI;

	$gXPath->registerNamespace($xs, $defaultNamespace);
		
	$title = '';
	$possibleNames = $gXPath->evaluate("//$xs:name[@type='primary']");
	if ($possibleNames->length > 0)
	{

		$parts = $gXPath->evaluate("//$xs:name[@type='primary']/$xs:namePart");
		if ($parts->length > 0)
		{
			$title = "";
			for($i=0; $i<$parts->length; $i++)
			{
				$title .= $parts->item($i)->nodeValue . " ";
			}
			$title = trim($title);
		}
	}
	else 
	{
		$possibleNames = $gXPath->evaluate("//$xs:name/$xs:namePart");
		
		if ($possibleNames->length > 0)
		{ 
			$title = "";
			for($i=0; $i<$possibleNames->length; $i++)
			{
				$title .= $possibleNames->item($i)->nodeValue . " ";
			}
			$title = trim($title);
		}
	}
	
	if (strlen($title) === 0)
	{
		$title = '(no name/title)';
	}

	if (getQueryValue('userMode') != 'readOnly')
	{
		saveDraftRegistryObject($transformResult , $objectClass, $objectDataSource,$keyValue, $title);
	}
	else 
	{
		// Keep the status and modification details, just resave (possibly to add field_id information?)
		saveDraftRegistryObject($transformResult , $objectClass, $objectDataSource,$keyValue, $title, null, true);
		//syncDraftKey($keyValue, $objectDataSource);
	}

	$preview = createPreview($transformResult, $objectClass, $objectDataSource, $dateCreated);
	$removeSpace = '<script type="text/javascript">
	document.getElementById("object_mandatoryInformation_key").value = "'.$test_array['mandatoryInformation']['key'].'"
	</script>
	';
	echo $removeSpace;
	echo $preview;

}
else
{
	print("<script>alert('Could not save - Error: No JSON could be parsed.');</script>");	
}