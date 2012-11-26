<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Maintenance Dashboard
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Maintenance extends MX_Controller {

	
	public function index(){
		$data['title'] = 'ARMS Maintenance';
		$data['small_title'] = '';
		$data['scripts'] = array('maintenance');
		$data['js_lib'] = array('core', 'prettyprint');

		$this->load->view("maintenance_index", $data);
	}

	function getSOLRstat(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('maintenance_stat', 'mm');
		$data['totalCountDB'] = $this->mm->getTotalRegistryObjectsCount('db');
		$data['totalCountSOLR'] = $this->mm->getTotalRegistryObjectsCount('solr');
		$data['notIndexedArray'] = array_diff($this->mm->getAllIDs('db'), $this->mm->getAllIDs('solr'));
		$data['notIndexed'] = sizeof($data['notIndexedArray']);
		echo json_encode($data);
	}

	function getDataSourcesStat(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model("data_source/data_sources","ds");
		$dataSources = $this->ds->getAll(0,0);//get everything

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		echo json_encode($data);
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