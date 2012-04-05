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

function updateDoisClient($client_name,$client_contact_name,$client_contact_email,$ip_address,$datacite_prefix,$app_id,$client_id)
{
	global $gCNN_DBS_DOIS;

	$resultSet = null;
	$strQuery = 'UPDATE public.doi_client SET client_name = $1 ,client_contact_name = $2 ,client_contact_email = $3 ,ip_address = $4 ,datacite_prefix = $5 ,app_id = $6 WHERE client_id = $7';
	$params = array($client_name,$client_contact_name,$client_contact_email,$ip_address,$datacite_prefix,$app_id,$client_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);
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

function deleteClientDomainList($client_id)
{
	global $gCNN_DBS_DOIS;
	//echo "deleting the domain list for ".$client_id."<br />";
	$resultSet = null;
	$strQuery = 'DELETE FROM public.doi_client_domains WHERE client_id = $1';
	$params = array($client_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

}
function getDoisClientDomains($client_id)
{
	global $gCNN_DBS_DOIS;

	$resultSet = null;
	$strQuery = 'SELECT client_domain FROM public.doi_client_domains WHERE client_id = $1';

	$params = array($client_id);
	$resultSet = executeQuery($gCNN_DBS_DOIS, $strQuery, $params);

	return $resultSet;
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
?>
