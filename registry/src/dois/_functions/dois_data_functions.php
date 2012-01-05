<?php

function addDoisClient($client_name,$client_contact_name,$client_contact_email,$ip_address,$datacite_prefix,$app_id)
{
	global $gCNN_DBS_DOIS;

	$client_id = 0;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_add_client($1,$2,$3,$4,$5,$6) AS client_id';
	$params = array($client_name,$client_contact_name,$client_contact_email,$ip_address,$datacite_prefix,$app_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	
	if( $resultSet )
	{
		$client_id = $resultSet[0]['client_id'];		
	}	
	return $client_id;
}

function listDoisClients()
{
	global $gCNN_DBS_DOIS;
	$clients = array();
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_list_clients() as clients';
	$params = array();
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	if( $resultSet )
	{
		$clients = $resultSet;		
	}
	
	return $clients;
}

function addDoisClientDomain($client_id,$client_domain)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_add_client_domain($1,$2)';
	$params = array($client_id,$client_domain);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

}

function getDoisClient($app_id)
{

	global $gCNN_DBS_DOIS;

	$client = null;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_client($1)' ;
	$params = array($app_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	if( $resultSet )
	{
		$client = $resultSet[0];		
	}
	
	return $client;
}
function getDoisClientDetails($client_id)
{
	global $gCNN_DBS_DOIS;
	$client = array();
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_client_details($1)' ;
	$params = array($client_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	if( $resultSet )
	{
		$client = $resultSet;		
	}
	
	return $client;
}

function checkDoisValidClient($ip_address,$app_id)
{
	global $gCNN_DBS_DOIS;
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_check_valid_client($1)' ;
	$params = array($app_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if( $resultSet )
	{
		$iprange = explode(",",$resultSet[0]['ip_address']);
		if(count($iprange)>1)
		{
			if($ip_address>=$iprange[0]&&$ip_address<=$iprange[1]) return $resultSet[0]['client_id'];			
		}
		else
		{
			return $resultSet[0]['client_id'];					
		}

	}else{
		return false;
	}
}

function checkDoisClientDoi($doi_id,$client_id)
{
	global $gCNN_DBS_DOIS;

	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_check_client_doi($1,$2)' ;
	$params = array($doi_id,$client_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	
	if( $resultSet )
	{
		return $resultSet[0]['client_id'];		
	}else{
		return false;
	}
}
function getDoiList()
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_list()';
	$params = array();
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	return $resultSet;
}

function getDoiObject($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_object($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	return $resultSet;
}

function getDoiStatus($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_status($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	if( $resultSet )
	{
		return $resultSet[0]['status'];		
	}else{
		return false;
	}
}

function insertDoiObject($doi_id,$publisher,$publicationYear,$client_id,$created_who,$status,$language,$version,$identifier_type,$rights,$url)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_object($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)';
	$params = array($doi_id,$publisher,$publicationYear,$client_id,$created_who,$status,$language,$version,$identifier_type,$rights,$url);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI record<br />";
	}		
}

function insertDoiCreators($doi_id,$creator_name,$name_identifier,$name_identifier_scheme)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_creators($1,$2,$3,$4)';
	$params = array($doi_id,$creator_name,$name_identifier,$name_identifier_scheme);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI creators<br />";
	}		
}
function insertDoiTitles($doi_id,$title,$title_type)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_title($1,$2,$3)';
	$params = array($doi_id,$title,$title_type);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI titles<br />";
	}		
}

function insertDoiSubject($doi_id,$subjectValue,$subjectScheme)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_subject($1,$2,$3)';
	$params = array($doi_id,$subjectValue,$subjectScheme);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI subjects<br />";
	}			
}

function insertDoiContributor($doi_id,$contributorName,$contributorType,$nameIdentifier,$nameIdentifierScheme)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_contributor($1,$2,$3,$4,$5)';
	$params = array($doi_id,$contributorName,$contributorType,$nameIdentifier,$nameIdentifierScheme);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI contributors<br />";
	}					
}

function insertDoiDate($doi_id,$dateValue,$dateType)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_date($1,$2,$3)';
	$params = array($doi_id,$dateValue,$dateType);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI dates<br />";
	}					
}

function insertDoiResourceType($doi_id,$resourceTypeGeneral,$resourceTypeValue,$resourceTypeDescription)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_resource_type($1,$2,$3,$4)';
	$params = array($doi_id,$resourceTypeGeneral,$resourceTypeValue,$resourceTypeDescription);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI resource types<br />";
	}			
}

function insertDoiAlternateIdentifier($doi_id,$alternateIdentifierValue,$alternateIdentifierType)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_alternate_identifier($1,$2,$3)';
	$params = array($doi_id,$alternateIdentifierValue,$alternateIdentifierType);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI alternate identifiers<br />";
	}				
}

function insertDoiRelatedIdentifier($doi_id,$relatedIdentifierValue, $relatedIdentifierType,$relationType)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_related_identifier($1,$2,$3,$4)';
	$params = array($doi_id,$relatedIdentifierValue, $relatedIdentifierType,$relationType);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI related identifiers<br />";
	}				
}

function insertDoiSize($doi_id,$sizeValue)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_size($1,$2)';
	$params = array($doi_id,$sizeValue);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI sizes<br />";
	}			
}

function insertDoiFormat($doi_id,$formatValue)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_format($1,$2)';
	$params = array($doi_id,$formatValue);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI formats<br />";
	}			
}

function insertDoiDescription($doi_id,$descriptionValue,$descriptionType)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_descrption($1,$2,$3)';
	$params = array($doi_id,$descriptionValue,$descriptionType);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI descriptions<br />";
	}			
}

function insertDoiActivity($activity,$doiValue,$result,$client_id,$message)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_insert_activity_log($1,$2,$3,$4,$5)';
	$params = array($activity,$doiValue,$result,$client_id,$message);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to insert the DOI activity<br />";
	}				
}

function setDoiStatus($doi_id, $status)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_set_status($1,$2)';
	$params = array($doi_id, $status);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to set the DOI status<br />";
	}					
}

function updateDoiUrl($doi_id,$url)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_update_url($1,$2)';
	$params = array($doi_id, $url);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to update the DOI url<br />";
	}			
}

function deleteDoiObjectXml($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_delete_object_xml($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to delete the DOI xml<br />";
	}			
}

function updateDoiObjectAttributes($doi_id,$publisher,$publicationYear,$language,$version,$rights)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_update_object_attributes($1,$2,$3,$4,$5,$6)';
	$params = array($doi_id,$publisher,$publicationYear,$language,$version,$rights);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to update the DOI attributes<br />";
	}		
}

function getDoiCreators($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_creators($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to get the DOI creators<br />";
	}
	else 
	{
		return $resultSet;
	}			
}

function getDoiTitles($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_titles($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;	
}
function getDoiSubjects($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_subjects($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;
}
function getDoiPublisher($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_object($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to get the DOI publisher<br />";
	}
	else 
	{
		return $resultSet[0]["publisher"];
	}			
}

function getDoiPublicationYear($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_object($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to get the DOI Publication Year<br />";
	}
	else 
	{
		return $resultSet[0]["publication_year"];
	}			
}

function getDoiContributors($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_contributors($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;		
}
function getDoiDates($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_dates($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;	
}
function getDoiLanguage($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_object($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to get the DOI titles<br />";
	}
	else 
	{
		return $resultSet[0]["language"];
	}			
}
function getDoiVersion($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_object($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	if(!$resultSet){
		return "An error occurred when attempting to get the DOI titles<br />";
	}
	else 
	{
		return $resultSet[0]["version"];
	}			
}
function getDoiRights($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_doi_object($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	if(!$resultSet){
		return "An error occurred when attempting to get the DOI titles<br />";
	}
	else 
	{
		return $resultSet[0]["rights"];
	}			
}
function getDoiResourceType($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_resource_types($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;
			
}

function getDoiAlternateIdentifiers($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_alternate_identifiers($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;		
}

function getDoiRelatedIdentifiers($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_related_identifiers($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;	
}
function getDoiSizes($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_sizes($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;		
}
function getDoiFormats($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_formats($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;		
}
function getDoiDescriptions($doi_id)
{
	global $gCNN_DBS_DOIS;
	
	$resultSet = null;
	$strQuery = 'SELECT * FROM public.dois_get_descriptions($1)';
	$params = array($doi_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
	return $resultSet;	
}
?>
