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
		$data['js_lib'] = array('core', 'prettyprint', 'dataTables');

		$this->load->view("maintenance_index", $data);
	}

	function getStat(){
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
		$this->load->model('maintenance_stat', 'mm');
		$dataSources = $this->ds->getAll(0,0);//get everything

		//get all data_source_count
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		$this->solr->setFacetOpt('field', 'data_source_id');
		$this->solr->executeSearch();
		$data_sources_indexed_count = $this->solr->getFacetResult('data_source_id');

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			$item['totalCountDB'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id); //kinda bad but ok for now
			//$item['totalCountSOLR'] = $this->mm->getTotalRegistryObjectsCount('solr', $ds->id); bad bad query
			if(isset($data_sources_indexed_count[$ds->id])){
				$item['totalCountSOLR'] = $data_sources_indexed_count[$ds->id];
			}else{
				$item['totalCountSOLR'] = 0;
			}
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		echo json_encode($data);
	}

	function enrichDS($data_source_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');

		$ids = $this->ro->getIDsByDataSourceID($data_source_id);
		foreach($ids as $ro_id){
			try{
				$ro = $this->ro->getByID($ro_id);
				if($ro->getRif()){
					$ro->enrich();
				}
			}catch (Exception $e){
				echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
			}
		}
	}

	function indexDS($data_source_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->library('solr');

		//construct the
		$ids = $this->ro->getIDsByDataSourceID($data_source_id);
		$i = 1;
		foreach($ids as $ro_id){
			try{
				$ro = $this->ro->getByID($ro_id);
				$solrXML = $ro->transformForSOLR();
				echo $solrXML;
				echo $this->solr->post($solrXML, 'update');
				
			}catch (Exception $e){
				echo "UNABLE TO Index this registry object id = ".$ro_id . BR;	
				echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
			}
		}
	}
	
	/**
	 * @ignore
	 */
	public function __construct(){
		parent::__construct();
	}
	
}

/* End of file vocab_service.php */
/* Location: ./application/models/vocab_services/controllers/vocab_service.php */