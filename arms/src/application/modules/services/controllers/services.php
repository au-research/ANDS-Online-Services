<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Services controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/services
 * 
 */
class Services extends MX_Controller {

	public function index(){
		echo 'Hello World';
	}

	public function get_registry_object($id=null, $format='xml'){
		
		if($id){
			try{
				$this->load->model('registry_object/registry_objects', 'ro');
				$ro = $this->ro->getByID($id);
				$response = array();
				$response['status']='OK';
				$response['message']=$ro->getXML();
				$this->formatResponse($response, $format);
			}catch (Exception $e){
				$response['status']='ERROR';
				$response['message']=$e->getMessage;
				$this->formatResponse($response, $format);
			}
		}else{
			$response['status']='WARNING';
			$response['message']='Missing ID identifier for Registry Object';
			$this->formatResponse($response, $format);
		}
	}

	public function formatResponse($response, $format='xml'){
		header('Cache-Control: no-cache, must-revalidate');
		if($format=='xml'){
			header ("content-type: text/xml");
			$xml = new SimpleXMLELement('<root/>');
			$response = array_flip($response);
			array_walk_recursive($response, array ($xml, 'addChild'));
			print $xml->asXML();
		}elseif($format=='json'){
			header('Content-type: application/json');
			$response = json_encode($response);
			echo $response;
		}elseif($format=='raw'){
			print $response['message'];
		}elseif($format=='raw-xml'){
			header ("content-type: text/xml");
			print($response['message']);
		}
	}
	
}	