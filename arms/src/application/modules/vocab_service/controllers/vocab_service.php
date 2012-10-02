<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Core Vocab controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Vocab_service extends MX_Controller {

	/**
	 * Manage My Vocabs
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param 
	 * @todo everything :)
	 * @return [HTML] output
	 */
	public function index(){
				$this->load->database('vocabs');
		$data['title'] = 'Classify My Data';
		$data['small_title'] = '';

		$this->load->model("vocab_services","vocab");
		$vocabs = $this->vocab->getAll(0,0);//get everything

		$items = array();
		foreach($vocabs as $vocab){
			$item = array();
			$item['title'] = $vocab->title;
			$item['id'] = $vocab->id;
			$item['description'] = $vocab->description;
			array_push($items, $item);
		}
		$data['vocabs'] = $items;
		$data['scripts'] = array('vocab_services');
		$data['js_lib'] = array('core', 'graph');
		$this->load->view("vocab_service_index", $data);
	}

	/**
	 * Same as index
	 */
	public function manage(){
		$this->index();
	}


	/**
	 * Get a list of data sources
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] page
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] results of the search
	 */
	public function getVocabs($page=1){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		//Limit and Offset calculated based on the page
		$limit = 16;
		$offset = ($page-1) * $limit;

		$vocabs = $this->vocab->getAll($limit, $offset);

		$items = array();
		foreach($vocabs as $vocab){
			$item = array();
			$item['title'] = $vocab->title;
			$item['id'] = $vocab->id;
			$item['description'] = $vocab->description;
			$item['counts'] = array();

			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	/**
	 * Get a single vocab
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocab you have access to, error handling
	 * @return [JSON] of a single vocab
	 */
	public function getVocab($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$vocab= $this->vocab->getByID($id);

		if($vocab)
		{
			foreach($vocab->attributes as $attrib=>$value){
				$jsonData['item'][$attrib] = $value->value;
			} 
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Get a set of vocab versions
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return [JSON] of a list of vocab versions
	 */	
	public function getVocabVersions($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$versions= $this->vocab->getVersionsByID($id);
		$items = array();
		if($versions)
		{
			foreach($versions as $version){
				$item = array();
				$item['vocab_status'] = $version->vocab_status;
				$item['id'] = $version->id;
				$item['vocab_format'] = $version->vocab_format;
				$item['vocabulary'] = $version->vocabulary;
	
				array_push($items, $item);
			}
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	
	/**
	 * Get a set of vocab change histrory
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] vocab ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return [JSON] of a list of vocab changes
	 */	
	public function getVocabChanges($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$changes= $this->vocab->getChangesByID($id);
		$items = array();
		if($changes)
		{
			foreach($changes as $change){
				$item = array();
				$item['change_date'] = $change->change_date;
				$item['id'] = $change->id;
				$item['description'] = $change->description;
	
				array_push($items, $item);
			}
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
			
	/**
	 * Save a vocab
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] Vocab ID [POST] attributes
	 * @todo ACL on which vocab you have access to, error handling, new attributes
	 * @return [JSON] result of the saving [VOID] 
	 */
	public function updateVocab(){
		
		$jsonData = array();
		$vocab = NULL;
		$id = NULL; 
		
		
		$jsonData['status'] = 'OK';
		$POST = $this->input->post();
		if (isset($POST['vocab_id'])){
			$id = (int) $this->input->post('vocab_id');
		}
		
		$this->load->model("vocab_services","vocab");

		
		if ($id == 0) {
			 $jsonData['status'] = "ERROR: Invalid vocab ID"; 
		}
		else 
		{
			$vocab = $this->vocab->getByID($id);
		}


		if ($vocab)
		{

			foreach($vocab->attributes() as $attrib=>$value){						
				if ($new_value = $this->input->post($attrib)) {
					if($new_value=='true') $new_value=DB_TRUE;
					if($new_value=='false') $new_value=DB_FALSE;
					$vocab->setAttribute($attrib, $new_value);
				}
			}
			$vocab->save();
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Create a vocab
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] Vocab ID [POST] attributes
	 * @todo ACL on which vocab you have access to, error handling, new attributes
	 * @return [JSON] result of the saving [VOID] 
	 */
	public function addVocab(){
		
		$jsonData = array();
		$vocab = NULL;
		$id = NULL; 
		
		
		$jsonData['status'] = 'OK';
		$POST = $this->input->post();

		print_r($POST);
		
		$this->load->model("vocab_services","vocab");


		$vocabid = $this->vocab->create();

		$vocab = $this->vocab->getByID($vocabid->id);
		print_r($vocab->attributes());
			
		if ($vocab)
		{

			foreach($vocab->attributes() as $attrib=>$value){						
				if ($new_value = $this->input->post($attrib)) {
					if($new_value=='true') $new_value=DB_TRUE;
					if($new_value=='false') $new_value=DB_FALSE;
					$vocab->setAttribute($attrib, $new_value);
					$jsonData['item'][$attrib] = $new_value;
				}
			}
			
			print_r($vocab->attributes());
			$vocab->save();
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	
	
	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}

/* End of file vocab_service.php */
/* Location: ./application/models/vocab_services/controllers/vocab_service.php */