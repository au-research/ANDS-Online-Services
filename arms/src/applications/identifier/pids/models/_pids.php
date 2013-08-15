<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class _pids extends CI_Model
{

	private $_CI; 
	private $pid_db = null;
	private $PIDS_SERVICE_BASE_URI = null;
	private $PIDS_APP_ID = null;

	function __construct(){
		parent::__construct();
		$this->_CI =& get_instance();
		$this->pid_db = $this->load->database('pids', TRUE);
		$this->PIDS_APP_ID = $this->_CI->config->item('pids_server_app_id');
		$this->PIDS_SERVICE_BASE_URI = $this->_CI->config->item('pids_server_base_url');
	}

	function getTrustedClients(){
		$result = $this->pid_db->get('public.trusted_client');
		return $result->result_array();
	}

	function getOwnerHandle($userIdentifier, $userDomain)
	{
		$this->_CI->session->set_userdata(PIDS_USER_IDENTIFIER, $userIdentifier);
		$this->_CI->session->set_userdata(PIDS_USER_DOMAIN, $userDomain);
		$identifierStr = $userIdentifier.'####'.$userDomain;
		$query = $this->pid_db->get_where("public.handles", array("type"=>'DESC', 'data'=>$identifierStr));
		if($query->num_rows()>0){
			$array = $query->result_array();
			return $array[0]['handle'];
		}
	}

	function getHandles($ownerHandle, $searchText = null)
	{
		$aHandles = array();
		$query = $this->pid_db->select('handle')->from('public.handles')->where('handle !=',$ownerHandle)->where("type",'AGENTID')->where('data',$ownerHandle)->get();
		if($query->num_rows()>0 && $searchText)
		{
			$handles = $query->result_array();
			$query = $this->pid_db->select('handle')->from("public.handles")->like('DESC',$searchText)->where_in("handle",$handles)->get();
		}
		if($query->num_rows()>0){
			foreach($query->result_array() as $r)
				{
					$aHandles[] = $r['handle'];
				}
		}
		return $aHandles;
	}


	function getHandlesDetails($handles)
	{
		$query = $this->pid_db->select('*')->from("public.handles")->where_in("handle",$handles)->get();
		if($query->num_rows()>0){
			return $query->result_array();
		}
	}


	function pidsGetHandleURI($handle)
	{
		return 'http://hdl.handle.net/'.$handle;
	}

	function pidsGetResponseType($response)
	{
		$responseType = 'FAILURE';
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$responseType = strtoupper($responseDOMDoc->getElementsByTagName("response")->item(0)->getAttribute("type"));
		}
		return $responseType;
	}

	function pidsGetUserMessage($response)
	{
		$userMessage = '';
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$messageType = strtoupper($responseDOMDoc->getElementsByTagName("message")->item(0)->getAttribute("type"));
			if( $messageType == 'USER' )
			{
				$userMessage = $responseDOMDoc->getElementsByTagName("message")->item(0)->nodeValue;
			}
		}
		return $userMessage;
	}

	function pidsGetHandleValue($response)
	{
		$handleValue = '';
		$responseDOMDoc = new DOMDocument();
		$result = $responseDOMDoc->loadXML($response);
		if( $result )
		{
			$handleValue = $responseDOMDoc->getElementsByTagName("identifier")->item(0)->getAttribute("handle");
		}
		return $handleValue;
	}

	function pidsRequest($serviceName, $parameters)
	{
		
		$userIdentifier = $this->_CI->session->userdata(PIDS_USER_IDENTIFIER);
		$userDomain = $this->_CI->session->userdata(PIDS_USER_DOMAIN);

		$resultXML = '';		
		$requestURI = $this->PIDS_SERVICE_BASE_URI.$serviceName."?".$parameters;
		$requestBody  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$requestBody .= '<request name="'.$serviceName.'">'."\n";
		$requestBody .= '  <properties>'."\n";
		$requestBody .= '    <property name="appId" value="'.$this->PIDS_APP_ID.'" />'."\n";
		$requestBody .= '    <property name="identifier" value="'.$userIdentifier.'" />'."\n";
		$requestBody .= '    <property name="authDomain" value="'.$userDomain.'" />'."\n";
		$requestBody .= '  </properties>'."\n";
		$requestBody .= '</request>';
		$result = curl_post($requestURI, $requestBody, array("Content-Type: text/plain"));
		if( $result )
		{
			$resultXML = $result;
		}
		return $resultXML;
		
	}

	function pidsGetHandleListDescription($handle)
	{
		$listDescription = '';
				      	
	    // Get the handle to display the first property
		$serviceName = "getHandle";
		$parameters = "handle=".urlencode($handle);
		$response = pidsRequest($serviceName, $parameters);
		
		if( $response )
		{
			$responseDOMDoc = new DOMDocument();
			$result = $responseDOMDoc->loadXML($response);
			
			if( $result )
			{
				// Get the value of the first property.
				if( $responseDOMDoc->getElementsByTagName("property")->item(0) )
				{
					$firstPropertyValue = $responseDOMDoc->getElementsByTagName("property")->item(0)->getAttribute("value");
					if( $firstPropertyValue )
					{
						$listDescription = $firstPropertyValue;
					}
				}

			}
		}
		
		return $listDescription;
	}

}
