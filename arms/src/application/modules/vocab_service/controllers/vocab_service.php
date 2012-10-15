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
		$currentVersion ='';
		if($versions)
		{
			foreach($versions as $version){
			
				
				if($currentVersion!=$version->version_id)
				{
					if($currentVersion!='')
					{
						array_push($items, $item);
					}
					$currentVersion = $version->version_id;
					$item = array();
					$item['formats'] = array();									
					$item['status'] = $version->status;
					$item['id'] = $version->version_id;
					$item['title'] = $version->title;
					
				} 
					
				$formats['format'][] = $version->format;
				$formats['type'][] = $version->type;
				$formats['value'][] = $version->value;
				array_push($item['formats'], $formats);
				unset($formats);
			
			}
			array_push($items, $item);
		}
		$jsonData['items'] = $items;
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
	public function getVocabVersion($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("vocab_services","vocab");

		$versions= $this->vocab->getVersionByID($id);
		$items = array();
		$currentVersion ='';
		if($versions)
		{
			foreach($versions as $version){
					//print_r($version);
				
				if($currentVersion!=$version->version_id)
				{
					if($currentVersion!='')
					{
						array_push($items, $item);
					}
					$currentVersion = $version->version_id;
					$item = array();
					$item['formats'] = array();									
					$item['status'] = $version->status;
					$item['id'] = $version->version_id;
					$item['title'] = $version->title;
					
				} 
					
				$formats['format'][] = $version->format;
				$formats['format_id'] = $version->id;
				$formats['type'][] = $version->type;
				$formats['value'][] = $version->value;
				array_push($item['formats'], $formats);
				unset($formats);
			
			}
			array_push($items, $item);
		}

		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}	

		
	/**
	 * delete a format from a vocab version 
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param [INT] version format ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return NIL
	 */	
	
	public function deleteFormat($format_id)
	{
		$this->load->model("vocab_services","vocab");

		$changes= $this->vocab->deleteFormat($format_id);	
	
	}	
	
	/**
	 * add a format to a vocab version 
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param [INT] version ID
	 * @todo ACL on which vocabs you have access to, error handling
	 * @return NIL
	 */	
	
	public function addFormat($version_id)
	{
		$POST = $this->input->post();

		$format = $this->input->post('versionFormat');	

		$type = $this->input->post('versionFormatType');
	
		$value = $this->input->post('versionFormatValue');	
		print_r($value);				

		$this->load->model("vocab_services","vocab");

		$format= $this->vocab->addFormat($version_id,$format,$type,$value);	
	
	}	
	
	/**
	 * uploadf a format file 
	 * 
	 * 
	 * @author Liz Woods <liz.woods@ands.org.au>
	 * @param NIL
	 * @todo ACL
	 * @return NIL
	 */	
	public function uploadFile()
	{

		print_r($_FILES);
		echo getcwd();
	  if (file_exists("../vocab_files/" . $_FILES["theFile"]["name"]))
      {
      	echo $_FILES["theFile"]["name"] . " already exists. ";
      }
    else
      {
      	move_uploaded_file($_FILES["theFile"]["tmp_name"],
      	"/var/www/htdocs/workareas/liz/ands/ands/arms/src/application/modules/vocab_service/vocab_files/" . $_FILES["theFile"]["name"]);      }	
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
		
		$this->load->model("vocab_services","vocab");

		$vocabid = $this->vocab->create();
		
		$jsonData['id'] = $vocabid->id;
		
		$vocab = $this->vocab->getByID($vocabid->id);
			
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
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}

/* End of file vocab_service.php */
/* Location: ./application/models/vocab_services/controllers/vocab_service.php */