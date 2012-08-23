<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Registry Objects Services controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/services/registry_object
 * 
 */
class Registry_objects extends MX_Controller {


	public function index(){
		echo 'Hello World aa';
	}

	public function get_registry_object($id=null, $format='xml'){
		
		if($id){
			try{
				//$this->load->model('data_source/data_sources', 'ds');
				//$data_source = $this->ds->getByID($id);
				$this->load->model('registry_object/registry_objects', 'ro');
				$ro = $this->ro->getByID($id);

				$response = array();
				$response['status']='OK';
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
		}
	}
	
}	