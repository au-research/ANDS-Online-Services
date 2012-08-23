<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Core Data Source controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Data_source extends MX_Controller {

	public function index()
	{
		//$this->output->enable_profiler(TRUE);
		
		$data['title'] = 'Manage My Datasources';
		$data['small_title'] = '';

		$this->load->model("data_sources","ds");
		$dataSources = $this->ds->getAll(0,0);//get everything

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		$data['scripts'] = array('data_sources');
		$data['js_lib'] = array('core', 'graph');

		$this->load->view("data_source_index", $data);
	}

	public function manage(){
		$this->index();
	}


	public function getDataSources($page=1)
	{
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");
		$limit = 16;
		$offset = ($page-1) * $limit;

		$dataSources = $this->ds->getAll($limit, $offset);

		$this->load->model("registry_object/registry_objects", "ro");

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;

			$item['counts'] = array();
			foreach ($this->ro->valid_status AS $status){
				if($ds->getAttribute("count_$status")>0){
					array_push($item['counts'], array('status' => $status, 'count' =>$ds->getAttribute("count_$status")));
				}
			}
			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	public function getDataSource($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		$dataSource = $this->ds->getByID($id);

		foreach($dataSource->attributes as $attrib=>$value){
			$jsonData['item'][$attrib] = $value->value;
		}

		$jsonData['item']['statuscounts'] = array();
		foreach ($this->ro->valid_status AS $status){
			array_push($jsonData['item']['statuscounts'], array('status' => $status, 'count' =>$dataSource->getAttribute("count_$status")));
		}

		$jsonData['item']['qlcounts'] = array();
		foreach ($this->ro->valid_levels AS $level){
			array_push($jsonData['item']['qlcounts'], array('level' => $level, 'count' =>$dataSource->getAttribute("count_level_$level")));
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	public function updateDataSource(){
		
		$jsonData = array();
		$dataSource = NULL;
		$id = NULL; 
		
		
		$jsonData['status'] = 'OK';
		$POST = $this->input->post();
		if (isset($POST['data_source_id'])){
			$id = (int) $this->input->post('data_source_id');
		}
		
		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		
		if ($id == 0) {
			 $jsonData['status'] = "ERROR: Invalid data source ID"; 
		}
		else 
		{
			$dataSource = $this->ds->getByID($id);
		}

		// XXX: This doesn't handle "new" attribute creation? Probably need a whilelist to allow new values to be posted. //**whitelist**//
		if ($dataSource)
		{
			foreach($dataSource->attributes() as $attrib=>$value){
				if ($new_value = $this->input->post($attrib)) {
					if($new_value=='true') $new_value=DB_TRUE;
					if($new_value=='false') $new_value=DB_FALSE;
					$dataSource->setAttribute($attrib, $new_value);
				}
			}
			$dataSource->save();
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	
	function importFromURLtoDataSource()
	{
		$this->load->model('data_source/import','importer');
		
		$log = 'IMPORT LOG' . NL;
		//$log .= 'URI: ' . $this->input->post('url') . NL;
		$log .= 'Harvest Method: Direct import from URL' . NL;
		
		$url = $this->input->post('url');
		if (!preg_match("/^https?:\/\/.*/",$url))
		{
			echo json_encode(array("response"=>"failure", "message"=>"URL must be valid http:// or https:// resource. Please try again."));
			return;	
		}
		
		$xml = @file_get_contents($this->input->post('url'));
		if (strlen($xml) == 0)
		{
			echo json_encode(array("response"=>"failure", "message"=>"Unable to retrieve any content from the specified URL"));
			// todo: http error?
			return;	
		}
		
		try
		{ 
			$log .= $this->importer->importPayloadToDataSource($this->input->post('data_source_id'), $xml);
		}
		catch (Exception $e)
		{
			
			$log .= "ERRORS" . NL;
			$log .= $e->getMessage();
			
			echo json_encode(array("response"=>"failure", "message"=>"An error occured whilst importing from this URL", "log"=>$log));
			return;	
		}	
		
	
		echo json_encode(array("response"=>"success", "message"=>"Import completed successfully!", "log"=>$log));	
			
	}
	
	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */