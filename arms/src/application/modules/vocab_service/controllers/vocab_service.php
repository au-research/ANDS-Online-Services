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
		$data['title'] = 'Browse Vocabularies';
		$data['small_title'] = '';

		$this->load->model("vocab_services","vocab");
		$vocabs = $this->vocab->getAll(0,0);//get everything

		$data['my_vocabs'] = $this->vocab->getOwnedVocabs();

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
		$data['js_lib'] = array('core');
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


		//get owned vocabs permission
		$ownedVocabs = $this->vocab->getOwnedVocabs();
		$ownedVocabsID = array();
		foreach($ownedVocabs as $v){
			array_push($ownedVocabsID, $v->id);
		}

		//Limit and Offset calculated based on the page
		$limit = 9;
		$offset = ($page-1) * $limit;

		$vocabs = $this->vocab->getAll($limit, $offset);
		if(sizeof($vocabs)<$limit){
			$jsonData['more'] = false;
		}else $jsonData['more'] = true;
		$items = array();
		foreach($vocabs as $vocab){
			$item = array();
			$item['title'] = $vocab->title;
			$item['id'] = $vocab->id;
			$item['description'] = $vocab->description;
			$item['counts'] = array();
			
			if(in_array($item['id'], $ownedVocabsID)){
				$item['owned'] = true;
			}

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
			//get owned vocabs permission
			$ownedVocabs = $this->vocab->getOwnedVocabs();
			$ownedVocabsID = array();
			foreach($ownedVocabs as $v){
				array_push($ownedVocabsID, $v->id);
			}

			foreach($vocab->attributes as $attrib=>$value){
				$jsonData['item'][$attrib] = $value->value;
			}

			if($vocab->contact_name || $vocab->contact_email || $vocab->contact_number) $jsonData['item']['contact']=true;
			//if($vocab->contact_email) $jsonData['item']['contact_email'] = mailto($vocab->contact_email);
			if(in_array($vocab->id, $ownedVocabsID)) $jsonData['item']['owned']=true;

			//vocab versions
			$versions= $this->vocab->getVersionsByID($id);
			$items = array();
			$currentVersion ='';
			if($versions)
			{
				$jsonData['item']['hasVersions']=true;
				foreach($versions as $version){
					$item=array();
					$item['status']=$version->status;
					$item['title']=$version->title;
					$item['id']=$version->id;
					array_push($items, $item);
				}
				$jsonData['item']['versions']=$items;
			}else{
				$jsonData['noVersions']=true;
			}

			//vocab formats
			$formats = $this->vocab->getAvailableFormatsByID($id);
			unset($items);
			$items = array();
			if($formats){
				$jsonData['item']['hasFormats']=true;
				foreach($formats as $m){
					array_push($items, $m->format);
				}
				$jsonData['item']['available_formats']=$items;
			}else{
				$jsonData['item']['noFormats']=true;
			}

			//vocab changes
			$changes= $this->vocab->getChangesByID($id);
			unset($items);
			$items = array();
			if($changes)
			{
				$jsonData['item']['hasChanges']=true;
				foreach($changes as $change){
					$item = array();
					$item['change_date'] = $change->change_date;
					$item['id'] = $change->id;
					$item['description'] = $change->description;
					array_push($items, $item);
				}
			}else{
				$jsonData['item']['noChanges']=true;
			}
			$jsonData['item']['changes']=$items;
		}else{
			$jsonData['status'] = 'ERROR';
			$jsonData['message'] = 'Non Existing Vocab Specified';
		}

		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function getDownloadableByFormat($vocab_id, $format){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("vocab_services","vocab");
		$downloadables = $this->vocab->getDownloadableByFormat($vocab_id, $format);
		$items = array();
		if($downloadables){
			$jsonData['hasItems']=true;
			foreach($downloadables as $d){
				$item = array();
				$item['title']=$d->title;
				$item['format']=$d->format;
				$item['type']=$d->type;
				$item['value']=$d->value;
				$item['version_id']=$d->version_id;
				$item['status']=$d->status;
				array_push($items, $item);
			}
			$jsonData['items']=$items;
		}else{
			$jsonData['noItems']=true;
			$jsonData['requestFor']=$vocab_id .' and '.$format;

		}
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function getFormatByVersion($version_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("vocab_services","vocab");
		$formats = $this->vocab->getFormatByVersion($version_id);
		$items = array();
		if($formats){
			$jsonData['hasItems']=true;
			foreach($formats as $f){
				$item = array();
				$item['id']=$f->id;
				$item['type']=$f->type;
				$item['value']=$f->value;
				$item['format']=$f->format;
				array_push($items, $item);
			}
			$jsonData['items']=$items;
		}else{
			$jsonData['noItems']=true;
			$jsonData['requestFor']='version_id = '. $version_id;
		}

		//get owned vocabs permission
		$version = $this->vocab->getVersionByID($version_id);
		$jsonData['id']=$version->id;
		$jsonData['title']=$version->title;
		$jsonData['vocab_id']=$version->vocab_id;
		if($version->status=='current'){
			$jsonData['current']=true;
		}else{
			$jsonData['notCurrent']=true;
		}
		$ownedVocabs = $this->vocab->getOwnedVocabs();
		$ownedVocabsID = array();
		foreach($ownedVocabs as $v) array_push($ownedVocabsID, $v->id);
		if(in_array($jsonData['vocab_id'], $ownedVocabsID)) $jsonData['owned']=true;

		$jsonData['id']=$version_id;

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
					if($currentVersion!=''){
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
		return $jsonData;
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

		$versions= $this->vocab->getVersionByID_old($id);
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
	
	public function deleteFormat($format_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model("vocab_services","vocab");
		$jsonData = array();
		if($this->vocab->deleteFormat($format_id)){
			$jsonData['status']='OK';
			$jsonData['message']='format id = '.$format_id.' deleted successfully';
		}else{
			$jsonData['status']='ERROR';
			$jsonData['message']='there is a problem deleting format id = '.$format_id;
		}
		echo json_encode($jsonData);
	}	

	public function deleteVersion(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$version_id = $this->input->post('version_id');
		$this->load->model("vocab_services","vocab");
		$this->vocab->deleteVersion($version_id);
	}

	public function test(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$config['upload_path'] = './assets/uploads/vocab_uploaded_files';
		$config['allowed_types'] = '*';
		$this->load->library('upload', $config);
		$jsonData = array();
		if ( ! $this->upload->do_upload()){
			$error = array('error' => $this->upload->display_errors());
			$jsonData['status']='ERROR'; 
			$jsonData['message']=$error['error'];
		}
		else{
			$data = array('upload_data' => $this->upload->data());
			$jsonData['status']='OK'; 
			$jsonData['message']='File uploaded successfully!';
		}
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function addFormat($version_id){
		$type = $this->input->post('type');
		$format = $this->input->post('format');
		$value = $this->input->post('value');
		
		$jsonData=array();
		$this->load->model("vocab_services","vocab");
		if($this->vocab->addFormat($version_id,$format,$type,$value)){
			$jsonData['status']='OK';
			$jsonData['message']='format added to the database';
		}else{
			$jsonData['status']='ERROR';
			$jsonData['message']='problem adding format to the database';
		}
		$jsonData=json_encode($jsonData);
		echo $jsonData;
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
	
	

	public function addVersion($vocab_id){
		$this->load->model('vocab_services', 'vocab');
		$version = array(
			'title'=>$this->input->post('title'),
		);
		if($this->input->post('makeCurrent')) {
			$version['makeCurrent']=true;
		}else $version['makeCurrent']=false;
		$this->vocab->addVersion($vocab_id, $version);
	}

	public function updateVersion(){
		$this->load->model('vocab_services', 'vocab');
		$version = array(
			'title'=>$this->input->post('title'),
			'id'=>$this->input->post('id')
		);
		if($this->input->post('makeCurrent')) {
			$version['makeCurrent']=true;
		}else $version['makeCurrent']=false;
		$this->vocab->updateVersion($version);
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
			$jsonData['hasChanges']=true;
			foreach($changes as $change){
				$item = array();
				$item['change_date'] = $change->change_date;
				$item['id'] = $change->id;
				$item['description'] = $change->description;
				array_push($items, $item);
			}
		}else{
			$jsonData['noChanges']=true;
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
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$vocab = NULL;
		$id = NULL; 
		
		
		$POST = $this->input->post();
		if (isset($POST['vocab_id'])){
			$id = (int) $this->input->post('vocab_id');
		}
		
		$this->load->model("vocab_services","vocab");

		
		if ($id == 0) {
			$jsonData['status']='ERROR';
			$jsonData['message'] = "ERROR: Invalid vocab ID"; 
		}
		else{
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
		
		$jsonData['status']='OK';
		$jsonData['message']='Your Vocabulary was successfully updated <a href="#!/view/'.$id.'">Go back to view</a>';
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