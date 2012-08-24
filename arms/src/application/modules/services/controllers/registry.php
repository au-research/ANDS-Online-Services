<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Registry Objects Services controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/services/registry
 * 
 */
class Registry extends MX_Controller {

	//formatResponse is a helper function in engine/helper/presentation_function


	/*
	 * get_registry_object
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	registry_object_id identifier
	 *			format: xml/json/raw/raw-xml
	 * prints out the requested rifcs of the object
	 */
	public function get_registry_object($id=null, $format='xml'){
		if($id){
			try{
				$this->load->model('registry_object/registry_objects', 'ro');
				$ro = $this->ro->getByID($id);
				$response = array();
				$response['status']='OK';
				$response['message']=$ro->getXML();
				formatResponse($response, $format);
			}catch (Exception $e){
				$response['status']='ERROR';
				$response['message']=$e->getMessage;
				formatResponse($response, $format);
			}
		}else{
			$response['status']='WARNING';
			$response['message']='Missing ID identifier for Registry Object';
			formatResponse($response, $format);
		}
	}

	/*
	 * get_vocab
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	vocab identifier
	 * prints out the requested json fragment for the autocomplete
	 */
	public function get_vocab($vocab){
		$vocab_results = array();
		if($vocab=='type'){
			$vocab_results = array('collection', 'party', 'some long name', 'project');
		}
		$vocab_results = json_encode($vocab_results);
		echo $vocab_results;
	}
}	