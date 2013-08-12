<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class _pids extends CI_Model
{

	private $pid_db = null;

	function __construct(){
		parent::__construct();
		$this->pid_db = $this->load->database('pids', TRUE);
	}

	function getTrustedClients(){
		$result = $this->pid_db->get('public.trusted_client');
		return $result->result_array();
	}

	function getOwnerHandle($userIdentifier, $userDomain)
	{
		$identifierStr = $userIdentifier.'####'.$userDomain;
		$query = $this->pid_db->get_where("public.handles", array("type"=>'DESC', 'data'=>$identifierStr));
		if($query->num_rows()>0){
			$array = $query->result_array();
			return $array[0]['handle'];
		}
	}

	function getHandles($ownerHandle)
	{
		$query = $this->pid_db->get_where("public.handles", array("type"=>'AGENTID', 'data'=>$ownerHandle));
		if($query->num_rows()>0){
			return $query->result_array();
		}
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
				$userMessage = esc($responseDOMDoc->getElementsByTagName("message")->item(0)->nodeValue);
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
		$resultXML = '';
		USER_DOMAIN
		$requestURI = gPIDS_SERVICE_BASE_URI.$serviceName."?".$parameters;
		
		$requestBody  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$requestBody .= '<request name="'.$serviceName.'">'."\n";
		$requestBody .= '  <properties>'."\n";
		$requestBody .= '    <property name="appId" value="'.gPIDS_APP_ID.'" />'."\n";
		$requestBody .= '    <property name="identifier" value="'.getSessionVar(sROLE_ID).'" />'."\n";
		$requestBody .= '    <property name="authDomain" value="'.getSessionVar(sAUTH_DOMAIN).'" />'."\n";
		$requestBody .= '  </properties>'."\n";
		$requestBody .= '</request>';
		
		$context  = stream_context_create(array('http' => array('method' => 'POST', 'header' => 'Content-Type: text/plain', 'content' => $requestBody)));
		//$result = file_get_contents($requestURI, false, $context);
		// create curl resource
		$ch = curl_init();

		// set url
		curl_setopt($ch, CURLOPT_URL, $requestURI);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//VERY IMPORTANT, skip SSL

		// $output contains the output string
		$result = curl_exec($ch);
		//var_dump($output);
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
