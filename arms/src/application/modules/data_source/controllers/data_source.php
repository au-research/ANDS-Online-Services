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

	/**
	 * Manage My Datasources (MMR version for Data sources)
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 
	 * @todo ACL on which data source you have access to
	 * @return [HTML] output
	 */
	
	public function index(){
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
	public function getDataSources($page=1){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");

		//Limit and Offset calculated based on the page
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

	/**
	 * Get a single data source
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] Data Source ID
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] of a single data source
	 */
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
	
	public function getDataSourceLogs()
	{		
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		date_default_timezone_set('Australia/Canberra');
		$offset = 0;
		$count = 10;
		$POST = $this->input->post();
		if (isset($POST['id'])){
			$id = (int) $this->input->post('id');
		}
		if (isset($POST['offset'])){
			$offset = (int) $this->input->post('offset');
		}
			if (isset($POST['count'])){
			$count= (int) $this->input->post('count');
		}
				
		$jsonData = array();
		$items = array();
		$this->load->model("data_sources","ds");
		$dataSource = $this->ds->getByID($id);
		$dataSourceLogs = $dataSource->get_logs($offset, $count);
		$logSize = (int) $dataSource->get_log_size();
		$jsonData['log_size'] = $logSize;
		if($logSize > $offset + $count)
		{
			$jsonData['next_offset'] = $offset + $count;
		}
		else
		{
			$jsonData['next_offset'] = 'all';
		}
		$jsonData['count'] = $count;
		$jsonData['id'] = $id;



		if(sizeof($dataSourceLogs) > 0){
		foreach($dataSourceLogs as $log){
			$item = array();
			$item['type'] = $log['type'];
			$item['log'] = $log['log'];
			$item['id'] = $log['id'];
			$item['date_modified'] = date("Y-m-d H:i:s", $log['date_modified']);
			array_push($items, $item);
		}
		$jsonData['status'] = 'OK';
		$jsonData['items'] = $items;
		}		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Save a data source
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] Data Source ID [POST] attributes
	 * @todo ACL on which data source you have access to, error handling, new attributes
	 * @return [JSON] result of the saving [VOID] 
	 */
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
	    $harvesterParams = array('uri','provider_type','harvest_method','harvest_date','oai_set');
		$resetHarvest = false;
		// XXX: This doesn't handle "new" attribute creation? Probably need a whilelist to allow new values to be posted. //**whitelist**//
		if ($dataSource)
		{
			$valid_attributes = array_merge(array_keys($dataSource->attributes()), $harvesterParams);
			foreach($valid_attributes as $attrib){
				if ($new_value = $this->input->post($attrib)) {
					if($new_value=='true') $new_value=DB_TRUE;
					if($new_value=='false') $new_value=DB_FALSE;
					
					if($new_value != $dataSource->{$attrib} && in_array($attrib, $harvesterParams))
					{
					   $dataSource->append_log("new_value ".$new_value." ".$attrib, 'warning');
					   $resetHarvest = true;
					} 
					$dataSource->setAttribute($attrib, $new_value);

				}
			}
			
			$dataSource->save();
			if($resetHarvest)
			{
				$dataSource->resetHarvest();	
			}
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	
	/**
	 * Importing (Ben's import from URL)
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] URL to the source
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] result of the saving [VOID] 
	 */
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
	
	
	public function putHarvestData()
	{
		$POST = $this->input->post();
		$done = false;
		if (isset($POST['harvestid'])){
			$harvestId = (int) $this->input->post('harvestid');
		}
		if (isset($POST['content'])){
			$data =  $this->input->post('content');
		}
		if (isset($POST['errmsg'])){
			$errmsg =  $this->input->post('errmsg');
		}
		if (isset($POST['done'])){
			$done =  strtoupper($this->input->post('done'));
		}
		if (isset($POST['date'])){
			$nextHarvestDate =  $this->input->post('date');
		}
		$this->load->model("data_sources","ds");
		$dataSource = $this->ds->getByHarvestID($harvestId);		
		header("Content-Type: text/xml; charset=UTF-8", true);
		date_default_timezone_set('Australia/Canberra');
		$responseType = 'success';
		$message = 'THANK YOU';
		if($errmsg)
		{
			$dataSource->append_log("HARVESTER RESPONCE ".$errmsg, 'error');
		}
		else
		{	

			$this->load->model('data_source/import','importer');	
			$rifcsXml = $this->importer->getRifcsFromHarvest($data);
			
			if(strpos($rifcsXml, 'ERROR') === 0)
			{
				$dataSource->append_log("RIF EXTRACTION ERROR ".$rifcsXml, 'error');
				$responseType = 'error';
			}
			else
			{
				try
				{ 
					$log = $this->importer->importPayloadToDataSource($dataSource->getID(), $rifcsXml, $harvestId, false);
					if(strpos($log , 'DONE WITH ERRORS') > 0)
					{
						$dataSource->append_log("IMPORTING RECORDS ".$data, 'info');	
					}
					else{
						$dataSource->append_log("IMPORTING RECORDS ".$log.", finished Harvest: ".$done, 'info');	
					}	

				}
				catch (Exception $e)
				{					
					$log .= "ERRORS" . NL;
					$log .= $e->getMessage();
					$dataSource->append_log("IMPORTING ".$log, 'error');
				}	
			}
		}
		if(!$nextHarvestDate && $done == 'TRUE')
		{
			$dataSource->deleteHarvestRequest($harvestId);
			$dataSource->append_log("HARVEST COMPLETED ".$harvestId, 'info');
		}
		
		if($done == 'TRUE')
		{
			$dataSource->append_log("INDEXING RECORDS ...".date( 'Y-m-d\TH:i:s.uP', time()), 'info');
			$log = $this->importer->indexDS($dataSource->getID());
			$dataSource->append_log("INDEXING COMPLETED ".date( 'Y-m-d\TH:i:s.uP', time())." ".$log, 'info');			
		}

		print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
		print('<response type="'.$responseType.'">'."\n");
		print('<timestamp>'.date("Y-m-d H:i:s").'</timestamp>'."\n");
		print("<message>".$message."</message>");
		print("</response>");
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