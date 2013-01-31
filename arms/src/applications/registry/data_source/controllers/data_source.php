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
		
		$data['title'] = 'Manage My Datasources....';
		$data['small_title'] = '';

		$this->load->model("data_sources","ds");
		// $dataSources = $this->ds->getAll(0,0);//get everything  XXX: getOwnedDataSources
		$dataSources = $this->ds->getOwnedDataSources();
		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		$data['scripts'] = array('data_sources');
		$data['js_lib'] = array('core', 'graph', 'datepicker');

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

		//$dataSources = $this->ds->getAll($limit, $offset);
		$dataSources = $this->ds->getOwnedDataSources();

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
		foreach ($this->ro->valid_status AS $status)
		{
			// Hide some fields if there are no registry objects for that status
			if ($dataSource->getAttribute("count_$status") != 0 OR in_array($status, array(DRAFT, PUBLISHED))){
				array_push($jsonData['item']['statuscounts'], array('status' => $status, 'count' =>$dataSource->getAttribute("count_$status")));
			}
		}

		$jsonData['item']['qlcounts'] = array();
		foreach ($this->ro->valid_levels AS $level){
			array_push($jsonData['item']['qlcounts'], array('level' => $level, 'count' =>$dataSource->getAttribute("count_level_$level")));
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	public function getContributorGroups()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		date_default_timezone_set('Australia/Canberra');

		$POST = $this->input->post();
		$items = array();
		
		if (isset($POST['id'])){
			$id = (int) $this->input->post('id');
		}	
		$this->load->model("data_sources","ds");
		$dataSource = $this->ds->getByID($id);
		//print($dataSource->attributes['institution_pages']->value);
		if(isset($dataSource->attributes['institution_pages']->value))
		{
			$contributorPages = $dataSource->attributes['institution_pages']->value;
		} else {
			$contributorPages = 0;			
		}

		switch($contributorPages)
		{
			case 0:
				$jsonData['contributorPages'] = "Pages are not managed";	
				break;
			case 1:
				$jsonData['contributorPages'] = "Pages are automatically managed";	
				break;
			case 2:
				$jsonData['contributorPages'] = "Pages are manually managed";;	
				break;
		}

		$dataSourceGroups = $dataSource->get_groups();
		if(sizeof($dataSourceGroups) > 0){
			foreach($dataSourceGroups as $group){
				$item = array();
				$item['group'] = $group;
				$item['contributor_page'] = $dataSource->get_group_contributor($group);
				array_push($items, $item);
			}
			$jsonData['status'] = 'OK';
			$jsonData['items'] = $items;
		}		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}


	/**
	 * getDataSourceLogs
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] data_source_id [POST] offset [POST] count [POST] log_id
	 * 
	 * @return [json] [logs for the data source]
	 */
	public function getDataSourceLogs(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		// date_default_timezone_set('Australia/Canberra');//???

		$this->load->model('data_sources', 'ds');

		$post = $this->input->post();

		$id = isset($post['id']) ? $post['id'] : 0; //data source id
		if($id==0) {
			throw new Exception('Datasource ID must be provided');
			exit();
		}
		$offset = isset($post['offset']) ? (int) $post['offset'] : 10;
		$count = isset($post['count']) ? (int) $post['count'] : 0;
		$logid = isset($post['logid']) ? (int) $post['logid'] : null;

		$jsonData = array();
		$dataSource = $this->ds->getByID($id);
		$dataSourceLogs = $dataSource->get_logs($offset, $count, $logid);
		$jsonData['log_size'] = $dataSource->get_log_size();

		if($jsonData['log_size'] > ($offset + $count)){
			$jsonData['next_offset'] = $offset + $count;
			$jsonData['hasMore'] = true;
		}else{
			$jsonData['next_offset'] = 'all';
			$jsonData['hasMore'] = false;
		}

		$items = array();
		if(sizeof($dataSourceLogs) > 0){
			foreach($dataSourceLogs as $log){
				$item = array();
				$item['type'] = $log['type'];
				$item['log'] = $log['log'];
				$item['id'] = $log['id'];
				$item['date_modified'] = timeAgo($log['date_modified']);
				array_push($items, $item);
			}
		}
		$jsonData['count'] = $count;
		$jsonData['items'] = $items;

		echo json_encode($jsonData);
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
		//print("<pre>");
		//print_r($POST);
		//print("</pre>");

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
	    $primaryRelationship = array('class_1','class_2','primary_key_1','primary_key_2','collection_rel_1','collection_rel_2','activity_rel_1','activity_rel_2','party_rel_1','party_rel_2','service_rel_1','service_rel_2');
		$institutionPages = array('institution_pages');
		$resetHarvest = false;

		// XXX: This doesn't handle "new" attribute creation? Probably need a whilelist to allow new values to be posted. //**whitelist**//
		if ($dataSource)
		{
			$valid_attributes = array_merge(array_keys($dataSource->attributes()), $harvesterParams);

			$valid_attributes = array_merge($valid_attributes, $primaryRelationship);
			$valid_attributes = array_merge($valid_attributes, $institutionPages);
			$valid_attributes =array_unique($valid_attributes);

			foreach($valid_attributes as $attrib){	
				$new_value = null;

				if (isset($POST[$attrib])){					

					$new_value = trim($this->input->post($attrib));

				}
				else if(in_array($attrib, $harvesterParams))
				{
					$new_value = '';	
				}
				else if(in_array($attrib, $primaryRelationship)){
					$new_value = '';				
				}	
				if($this->input->post('create_primary_relationships')=='false')
				{
					switch($attrib){
						case 'class_1':
						case 'primary_key_1':
						case 'service_rel_1':
						case 'activity_rel_1':
						case 'collection_rel_1':
						case 'party_rel_1':
						case 'class_2':
						case 'primary_key_2':
						case 'service_rel_2':
						case 'activity_rel_2':
						case 'collection_rel_2':
						case 'party_rel_2':		
							$new_value = '';
							break;
						default:
							break;
					}
				
				}


			/*	this push to nla functionality has been removed as NLA aren't using it and the ds admins were getting confused

				if($this->input->post('push_to_nla')=='false')
				{
					switch($attrib){
						case 'isil_value':
							$new_value = '';
							break;
						default:
							break;	
					}				
				} 

			*/

				if($new_value=='true') $new_value=DB_TRUE;
				if($new_value=='false'){$new_value=DB_FALSE;} 

				if($new_value != $dataSource->{$attrib} && in_array($attrib, $harvesterParams))
				{
				   $resetHarvest = true;
				} 

				if (!is_null($new_value))
				{
					$dataSource->{$attrib} = $new_value;


					if($new_value == '' && $new_value != $dataSource->{$attrib} && in_array($attrib, $harvesterParams))
					{
						$dataSource->unsetAttribute($attrib);
					}
					else{
						$dataSource->setAttribute($attrib, $new_value);
					}

					if($attrib=='institution_pages')
					{
						$dataSource->setContributorPages($new_value,$POST);
					}

				}
				$dataSource->updateStats();	
			}		
	
			$dataSource->save();

			if($resetHarvest)
			{
				$dataSource->requestHarvest();	
			}
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Trigger harvest
	 */
	function triggerHarvest()
	{
		$jsonData = array("status"=>"ERROR");

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		if ($this->input->post('data_source_id')){

			$id = (int) $this->input->post('data_source_id');
	
			if ($id == 0) {
				 $jsonData['message'] = "ERROR: Invalid data source ID"; 
			}
			else 
			{
				$dataSource = $this->ds->getByID($id);
				$dataSource->requestHarvest();
				$jsonData['status'] = "OK";
			}
		}
		
		echo json_encode($jsonData);
	}
	
	/**
	 * Importing (Ben's import from URL)
	 * 
	 * 
	 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
	 * @param [POST] URL to the source
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] result of the saving [VOID] 
	 */
	function importFromURLtoDataSource()
	{
		 $this->load->library('importer');
		
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

			$this->load->model('data_source/data_sources', 'ds');
			$data_source = $this->ds->getByID($this->input->post('data_source_id'));
			$this->importer->setXML($xml);

			if ($data_source->provider_type != RIFCS_SCHEME)
			{
				$this->importer->setCrosswalk($data_source->provider_type);
			}

			$this->importer->setDatasource($data_source);
			$this->importer->commit();


			if ($error_log = $this->importer->getErrors())
			{
				$log .= "ERRORS DURING IMPORT" . NL;
				$log .= "====================" . NL ;
				$log .= $error_log . NL;
			}

			$log .= "IMPORT COMPLETED" . NL;
			$log .= "====================" . NL;
			$log .= $this->importer->getMessages();


			// XXX: data source log append...
		}
		catch (Exception $e)
		{
			
			$log .= "CRITICAL IMPORT ERROR [HARVEST COULD NOT CONTINUE]" . NL;
			$log .= $e->getMessage();
			
			echo json_encode(array("response"=>"failure", "message"=>"An error occured whilst importing from this URL", "log"=>substr($log,0, 1000)));
			return;	
		}	
	
		echo json_encode(array("response"=>"success", "message"=>"Import completed successfully!", "log"=>$log));	
			
	}

	/**
	 * Importing (Ben's import from XML Paste)
	 * 
	 * 
	 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
	 * @param [POST] xml A blob of XML data to parse and import
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] result of the saving [VOID] 
	 */
	function importFromXMLPasteToDataSource()
	{
		$this->load->library('importer');
		

		$xml = $this->input->post('xml');

		$log = 'IMPORT LOG' . NL;
		$log .= 'Harvest Method: Direct import from text posted' . NL;
		$log .= strlen($xml) . ' characters received...' . NL;

		if (strlen($xml) == 0)
		{
			echo json_encode(array("response"=>"failure", "message"=>"Unable to retrieve any content from the specified XML"));
			return;	
		}
		

		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID($this->input->post('data_source_id'));

		$xml=stripXMLHeader($xml);
		if ($data_source->provider_type != RIFCS_SCHEME)
		{
			$this->importer->setCrosswalk($data_source->provider_type);
		}
		else if (strpos(trim($xml), "<registryObjects") === FALSE)
		{
			$xml = wrapRegistryObjects($xml);
		}

		try
		{ 

			$this->importer->setXML($xml);

			$this->importer->setDatasource($data_source);
			$this->importer->commit();


			if ($error_log = $this->importer->getErrors())
			{
				$log .= NL . "ERRORS DURING IMPORT" . NL;
				$log .= "====================" . NL ;
				$log .= $error_log;
			}

			$log .= "IMPORT COMPLETED" . NL;
			$log .= "====================" . NL;
			$log .= $this->importer->getMessages();

			// XXX: data source log append...
		}
		catch (Exception $e)
		{
			
			$log .= "CRITICAL IMPORT ERROR [HARVEST COULD NOT CONTINUE]" . NL;
			$log .= $e->getMessage();
			
			echo json_encode(array("response"=>"failure", "message"=>"An error occured whilst importing from the specified XML", "log"=>$log));
			return;	
		}	
		
	
		echo json_encode(array("response"=>"success", "message"=>"Import completed successfully!", "log"=>$log));	
			
	}


	public function testHarvest()
	{
		header('Content-type: application/json');
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
			$dataSourceURI = $this->input->post("uri");
			$providerType = $this->input->post("provider_type");
			$OAISet = $this->input->post("oai_set");
			$harvestMethod = $this->input->post("harvest_method");
			$harvestDate = $this->input->post("harvest_date");
			$harvestFrequency = $this->input->post("harvest_frequency");
			$advancedHarvestingMethod = $this->input->post("advanced_harvesting_mode");	
			$nextHarvest = $harvestDate;
			$jsonData['logid'] = $dataSource->requestHarvest('','',$dataSourceURI, $providerType, $OAISet, $harvestMethod, $harvestDate, $harvestFrequency, $advancedHarvestingMethod, $nextHarvest, true);				
		}

		$jsonData = json_encode($jsonData);
		echo $jsonData;			
	}
	

	public function putHarvestData()
	{
		$POST = $this->input->post();
		$done = false;
		$mode = false;
		header("Content-Type: text/xml; charset=UTF-8", true);
		date_default_timezone_set('Australia/Canberra');
		$responseType = 'error';
		$message = 'THANK YOU';
		$harvestId = false;
		if (isset($POST['harvestid'])){
			$harvestId = (int) $this->input->post('harvestid');
		}
		if($harvestId)
		{
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
			if (isset($POST['mode'])){
				$mode =  strtoupper($this->input->post('mode'));
			}
			$this->load->model("data_sources","ds");
			$dataSource = $this->ds->getByHarvestID($harvestId);

			if($errmsg)
			{
				$dataSource->append_log("HARVESTER RESPONDED UNEXPECTEDLY: ".$errmsg, HARVEST_ERROR);
			}
			else
			{	
	
				$this->load->library('importer');	

				$this->load->model('data_source/data_sources', 'ds');

				// xxx: this won't work with crosswalk!
				$rifcsXml = $this->importer->extractRIFCSFromOAI($data);

				if (strpos($rifcsXml, 'registryObject ') === FALSE)
				{
					$dataSource->append_log("CRITICAL ERROR: Could not extract data from OAI feed. Check your provider.", HARVEST_ERROR);
					$dataSource->append_log($rifcsXml, HARVEST_ERROR);	
				}
				else
				{

					$this->importer->setXML($rifcsXml);

					if ($dataSource->provider_type != RIFCS_SCHEME)
					{
						$this->importer->setCrosswalk($dataSource->provider_type);
					}

					$this->importer->setHarvestID($harvestId);
					$this->importer->setDatasource($dataSource);

					if ($done != HARVEST_COMPLETE)
					{
						$this->importer->setPartialCommitOnly(TRUE);
					}


					if ($mode != HARVEST_TEST_MODE)
					{
						try
						{
							$this->importer->commit();

							if($this->importer->getErrors())
							{
								$dataSource->append_log($this->importer->getErrors(), HARVEST_WARNING);	
							}

							if($this->importer->getMessages())
							{
								$dataSource->append_log($this->importer->getMessages(), HARVEST_INFO);	
							}
							
							$dataSource->updateStats();
							$responseType = 'success';
						}
						catch (Exception $e)
						{
							$dataSource->append_log("CRITICAL ERROR: " . NL . $e->getMessages() . NL . $this->importer->getErrors(), HARVEST_ERROR);	
						}
					}	
				}
			}

			if(!$nextHarvestDate && $done == HARVEST_COMPLETE || $mode == HARVEST_TEST_MODE)
			{
				$dataSource->deleteHarvestRequest($harvestId);
			}
			
		}
		else
		{
			$message = "Missing harvestid param";
		}


		print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
		print('<response type="'.$responseType.'">'."\n");
		print('<timestamp>'.date("Y-m-d H:i:s").'</timestamp>'."\n");
		print("<message>".$message."</message>\n");
		print("</response>");
	}



	function getContributorPages()
	{
		$POST = $this->input->post();
		print_r($POST);
				print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
		print('<response type="">'."\n");
		print('<timestamp>'.date("Y-m-d H:i:s").'</timestamp>'."\n");
		print("<message> we need to get the contibutor groups and the pages if required</message>\n");
		print("</response>");
		return " we need to get the contibutor groups and the pages if required";

	}

	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}
