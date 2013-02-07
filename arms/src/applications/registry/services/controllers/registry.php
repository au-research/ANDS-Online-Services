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
	public function get_vocab($vocabIdentifier){
		$this->load->database();
		$this->db->select('vocabpath, name, identifier, description, id');
		$this->db->from('tbl_terms');
		$this->db->where(array('vocabulary_identifier'=>$vocabIdentifier));
		$query = $this->db->get();

		$vocab_results = array();
		foreach($query->result() as $row){
			$description = $row->vocabpath;
			/*if($row->description){
				$description = $row->description;
			} else{
				$description = $row->name;
			}*/
			$item = array('value'=>$row->identifier, 'subtext'=>$description);
			array_push($vocab_results, $item);
		}

		$vocab_results = json_encode($vocab_results);
		echo $vocab_results;
	}

	/*
	 * get_random_key
	 * 
	 * @author 	Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	length of the key
	 * prints out a random key that is unique
	 */
	public function get_random_key($length=52){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
		$str='';
		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}

		//@TODO: need some db checking
		echo $str;
	}

	/**
	 * check a key to see uniqueness
	 * @param  string $type data_source_key || registry_object_key
	 * @param  string $key  
	 * @return [total]
	 */
	public function check_unique($type){
		$this->load->database();
		$key = $this->input->post('key');
		if($type=='data_source_key'){
			$this->db->select('key');
			$this->db->from('data_sources');
			$this->db->where('key', $key);
			$total =  $this->db->count_all_results();
		}else if($type=='registry_object_key'){
			$this->db->select('key');
			$this->db->from('registry_objects');
			$this->db->where('key', $key);
			$total =  $this->db->count_all_results();
		}
		echo $total;
	}

	/*
	 * get_datasources_list
	 * 
	 * @author 	Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	
	 * prints out the list of datasources the user has access to @TODO: needs ACL
	 */
	public function get_datasources_list(){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("data_source/data_sources","ds");
		$dataSources = $this->ds->getAll(0, 0);//get All

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
}	