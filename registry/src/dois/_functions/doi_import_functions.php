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



function importDoiObject($doiObjects, $url, $client_id, $created_who='SYSTEM', $status='REQUESTED')
{

	$runErrors = '';
	$errors = null;	

		
	// Doi Object
	// =========================================================================
	$doiObjectList = $doiObjects->getElementsByTagName("resource");
	
	// Doi Object
	// =====================================================================
	$doiObject = $doiObjectList->item(0);

	// Doi Identifier
	// =====================================================================
	
	$doiIdentifier = $doiObject->getElementsByTagName("identifier")->item(0)->nodeValue;
			
	if( $doiIdentifier)
	{			
		//Doi publisher
		// =====================================================================		
		$publisher = $doiObject->getElementsByTagName("publisher")->item(0)->nodeValue;
		
		//Doi publish year
		// =====================================================================
		$publish_year = $doiObject->getElementsByTagName("publicationYear")->item(0)->nodeValue;			
		
		// Doi language
		// =====================================================================
		$languageValue = $doiObject->getElementsByTagName("language")->item(0)->nodeValue;						
		
		// Doi version
		// =====================================================================		
		$versionValue = $doiObject->getElementsByTagName("version")->item(0)->nodeValue;	
				
		// Doi rights
		// =====================================================================		
		$rightsValue = $doiObject->getElementsByTagName("rights")->item(0)->nodeValue;	
											
		
		$runErrors .= insertDoiObject($doiIdentifier,$publisher,$publish_year,$client_id,$created_who,"REQUESTED",$languageValue,$versionValue,"DOI",$rightsValue,$url);
		
		$runErrors .= importCreators($doiIdentifier,$doiObject);
		
		$runErrors .= importTitles($doiIdentifier,$doiObject);
		
		$runErrors .= importSubjects($doiIdentifier,$doiObject);
		
		$runErrors .= importContributors($doiIdentifier,$doiObject);

		$runErrors .= importDates($doiIdentifier,$doiObject);

		$runErrors .= importResourceTypes($doiIdentifier,$doiObject);

		$runErrors .= importAlternateIdentifiers($doiIdentifier,$doiObject);
		
		$runErrors .= importRelatedIdentifiers($doiIdentifier,$doiObject);

		$runErrors .= importSizes($doiIdentifier,$doiObject);

		$runErrors .= importFormats($doiIdentifier,$doiObject);						
		
		$runErrors .= importDescriptions($doiIdentifier,$doiObject);		
	}
	else
	{
			$runErrors .= "Couldn't create DOI without identifier.</br>";
	}// DoiIdentifier
		

	echo $runErrors;
		return $runErrors;
}
function updateDoiObject($doi_id, $doiObjects, $url)
{	
	$runErrors = '';
	$errors = null;	

	if($url){
		$runErrors .= updateDoiUrl($doi_id,$url);
	}
	
	if($doiObjects)
	{
		// Doi Object
		// =========================================================================
		$doiObjectList = $doiObjects->getElementsByTagName("resource");
	
		// Doi Object
		// =====================================================================
		$doiObject = $doiObjectList->item(0);

		// Doi Identifier
		// =====================================================================	
		$doiIdentifier = $doiObject->getElementsByTagName("identifier")->item(0)->nodeValue;
		
		if( $doiIdentifier )
		{			
			//Doi publisher
			// =====================================================================		
			$publisher = $doiObject->getElementsByTagName("publisher")->item(0)->nodeValue;
		
			//Doi publish year
			// =====================================================================
			$publish_year = $doiObject->getElementsByTagName("publicationYear")->item(0)->nodeValue;			
		
			// Doi language
			// =====================================================================
			$languageValue = $doiObject->getElementsByTagName("language")->item(0)->nodeValue;						
		
			// Doi version
			// =====================================================================		
			$versionValue = $doiObject->getElementsByTagName("version")->item(0)->nodeValue;	
				
			// Doi rights
			// =====================================================================		
			$rightsValue = $doiObject->getElementsByTagName("rights")->item(0)->nodeValue;	
												
			$runErrors .= deleteDoiObjectXml($doiIdentifier);
		
			$runErrors .= updateDoiObjectAttributes($doiIdentifier,$publisher,$publish_year,$languageValue,$versionValue,$rightsValue);
		
			$runErrors .= importCreators($doiIdentifier,$doiObject);
		
			$runErrors .= importTitles($doiIdentifier,$doiObject);
		
			$runErrors .= importSubjects($doiIdentifier,$doiObject);
		
			$runErrors .= importContributors($doiIdentifier,$doiObject);

			$runErrors .= importDates($doiIdentifier,$doiObject);

			$runErrors .= importResourceTypes($doiIdentifier,$doiObject);

			$runErrors .= importAlternateIdentifiers($doiIdentifier,$doiObject);
		
			$runErrors .= importRelatedIdentifiers($doiIdentifier,$doiObject);

			$runErrors .= importSizes($doiIdentifier,$doiObject);

			$runErrors .= importFormats($doiIdentifier,$doiObject);						
		
			$runErrors .= importDescriptions($doiIdentifier,$doiObject);		
		}
		else
		{
			$runErrors .= "Couldn't update DOI without identifier.</br>";
		}// DoiIdentifier
	}	
	return $runErrors;
}
// Datatype handlers
// =============================================================================
	
// Doi creator/s
// =====================================================================
function importCreators($doi_id,$doiObject)
{
	$insertError = '';
	$creatorList = $doiObject->getElementsByTagName("creator");
		
	for($i=0;$i<$creatorList->length;$i++)
	{		
		$nameIdentifier='';
		$nameIdentifierScheme='';					
		$creator = $creatorList->item($i);
		$nodes = $creator->childNodes;			
		for($j=0;$j< $creator->childNodes->length;$j++){
			if($nodes->item($j)->nodeName=="nameIdentifier"){
				$nameIdentifier = $nodes->item($j)->nodeValue;
				$nameIdentifierScheme = $nodes->item($j)->getAttribute('nameIdentifierScheme');
			}
		}

		$creatorName = $doiObject->getElementsByTagName("creatorName")->item($i)->nodeValue;
		$insertError = insertDoiCreators($doi_id,$creatorName,$nameIdentifier,$nameIdentifierScheme);
	}
	return $insertError;
}

// Doi title/s
// =====================================================================
function importTitles($doi_id,$doiObject)
{
	$insertError = '';

	$titleList = $doiObject->getElementsByTagName("title");	
	for($i=0;$i<$titleList->length;$i++)
	{
		$titleValue =  $titleList->item($i)->nodeValue;
		$titleType =  $titleList->item($i)->getAttribute('titleType');	
		$insertError = insertDoiTitles($doi_id,$titleValue,$titleType);		
	}
	return $insertError;							
}
	
// Doi subjects/s
// =====================================================================		
function importSubjects($doi_id,$doiObject)
{
	$insertError = '';

	$subjectList = $doiObject->getElementsByTagName("subject");		
	for($i=0;$i<$subjectList->length;$i++)
	{
		$subjectScheme='';
		$subjectValue = $subjectList->item($i)->nodeValue;
		$subjectScheme = $subjectList->item($i)->getAttribute('subjectScheme');				
		$insertError = insertDoiSubject($doi_id,$subjectValue,$subjectScheme);
	}
	return $insertError;				
}

// Doi contributors/s
// =====================================================================
function importContributors($doi_id,$doiObject)
{
	$insertError = '';
	$contributorList = $doiObject->getElementsByTagName("contributor");			
	for($i=0;$i<$contributorList->length;$i++)
	{		
		$contributorName='';
		$contributorType='';
		$nameIdentifier='';
		$nameIdentifierScheme='';						
		$contributor = $contributorList->item($i);
		$nodes = $contributor->childNodes;
			
		for($j=0;$j< $contributor->childNodes->length;$j++){
			if($nodes->item($j)->nodeName=="nameIdentifier"){
				$nameIdentifier = $nodes->item($j)->nodeValue;
				$nameIdentifierScheme = $nodes->item($j)->getAttribute('nameIdentifierScheme');
			}
		}		
		$contributorName = $doiObject->getElementsByTagName("contributorName")->item($i)->nodeValue;
		$contributorType = $contributorList->item($i)->getAttribute('contributorType');
		$insertError = insertDoiContributor($doi_id,$contributorName,$contributorType,$nameIdentifier,$nameIdentifierScheme);
	}
	return $insertError;
}

// Doi date/s
// =====================================================================
function importDates($doi_id,$doiObject)
{
	$insertError = '';

	$dateList = $doiObject->getElementsByTagName("date");				
	for($i=0;$i<$dateList->length;$i++)
	{
		$dateValue = $dateList->item($i)->nodeValue;
		$dateType = $dateList->item($i)->getAttribute('dateType');					
		$insertError = insertDoiDate($doi_id,$dateValue,$dateType);
	}
	return $insertError;	
}
				
// Doi resourceType/s
// =====================================================================
function importResourceTypes($doi_id,$doiObject)
{
	$insertError = '';

	$resourceTypeList = $doiObject->getElementsByTagName("resourceType");		
	$resourceTypeDescription = '';
	$resourceTypeGeneral = '';
	for($i=0;$i<$resourceTypeList->length;$i++)
	{
		$resourceTypeValue = $resourceTypeList->item($i)->nodeValue;
		$resourceTypeGeneral = $resourceTypeList->item($i)->getAttribute('resourceTypeGeneral');
		$resourceTypeDescription = $resourceTypeList->item($i)->getAttribute('resourceTypeDescription');			
		$insertError = insertDoiResourceType($doi_id,$resourceTypeGeneral,$resourceTypeValue,$resourceTypeDescription);
	}
	return $insertError;	
}

// Doi alternateIdentifiers/s
// =====================================================================
function importAlternateIdentifiers($doi_id,$doiObject)	
{
	$insertError = '';
	$alternateIdentifierList = $doiObject->getElementsByTagName("alternateIdentifier");		
	for($i=0;$i<$alternateIdentifierList->length;$i++)
	{
		$alternateIdentifierValue = $alternateIdentifierList->item($i)->nodeValue;
		$alternateIdentifierType = $alternateIdentifierList->item($i)->getAttribute('alternateIdentifierType');
		$insertError = insertDoiAlternateIdentifier($doi_id,$alternateIdentifierValue,$alternateIdentifierType);
	}
	return $insertError;
}					

// Doi relatedIdentifiers/s
// =====================================================================	
function importRelatedIdentifiers($doi_id,$doiObject)
{
	$insertError = '';

	$relatedIdentifierList = $doiObject->getElementsByTagName("relatedIdentifier");			
	for($i=0;$i<$relatedIdentifierList->length;$i++)
	{
		$relatedIdentifierValue = $relatedIdentifierList->item($i)->nodeValue;
		$relatedIdentifierType = $relatedIdentifierList->item($i)->getAttribute('relatedIdentifierType');
		$relationType = $relatedIdentifierList->item($i)->getAttribute('relationType');
		$insertError = insertDoiRelatedIdentifier($doi_id,$relatedIdentifierValue, $relatedIdentifierType,$relationType);
	}
	return $insertError;
}

// Doi size/s
// =====================================================================	
function importSizes($doi_id,$doiObject)
{		
	$insertError = '';

	$sizeList = $doiObject->getElementsByTagName("size");		
	for($i=0;$i<$sizeList->length;$i++)
	{
		$sizeValue = $sizeList->item($i)->nodeValue;		
		$insertError = insertDoiSize($doi_id,$sizeValue);
	}
	return $insertError;
}	

// Doi format/s
// =====================================================================
function importFormats($doi_id,$doiObject)
{ 
	$insertError = '';

	$formatList = $doiObject->getElementsByTagName("format");	
	for($i=0;$i<$formatList->length;$i++)
	{
		$formatValue = $formatList->item($i)->nodeValue;							
		$insertError = insertDoiFormat($doi_id,$formatValue);
	}
	return $insertError;						
}

// Doi descriptions/s
// =====================================================================
function importDescriptions($doi_id,$doiObject)
{
	$insertError = '';

	$descriptionList = $doiObject->getElementsByTagName("description");		
	for($i=0;$i<$descriptionList->length;$i++)
	{
		$descriptionValue = $descriptionList->item($i)->nodeValue;	
		$descriptionType = $descriptionList->item($i)->getAttribute('descriptionType');								
		$insertError = insertDoiDescription($doi_id,$descriptionValue,$descriptionType);
	}	
	return $insertError;							
}
?>