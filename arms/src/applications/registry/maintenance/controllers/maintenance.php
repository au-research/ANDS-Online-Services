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
		$data['totalCountDBPublished'] = $this->mm->getTotalRegistryObjectsCount('db', '*', 'PUBLISHED');
		$data['totalCountSOLR'] = $this->mm->getTotalRegistryObjectsCount('solr');
		$data['notIndexedArray'] = array_diff($this->mm->getAllIDs('db', 'PUBLISHED'), $this->mm->getAllIDs('solr'));
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
			$item['totalCountDBPUBLISHED'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id, 'PUBLISHED');
			//$item['totalCountSOLR'] = $this->mm->getTotalRegistryObjectsCount('solr', $ds->id); bad bad query
			if(isset($data_sources_indexed_count[$ds->id])){
				$item['totalCountSOLR'] = $data_sources_indexed_count[$ds->id];
			}else{
				$item['totalCountSOLR'] = 0;
			}
			$item['totalMissing'] =  $item['totalCountDBPUBLISHED'] - $item['totalCountSOLR'];
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		echo json_encode($data);
	}

	function enrichDS($data_source_id){//TODO: XXX
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');

		$ids = $this->ro->getIDsByDataSourceID($data_source_id);
		if($ids)
		{
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
	}

	/**
	 * web service for maintenance, this will index a data source
	 * @param  int $data_source_id 
	 * @return json result
	 */
	function indexDS($data_source_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$data = array();
		$data['data_source_id']=$data_source_id;
		$data['error']='';

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->library('solr');

		$ids = $this->ro->getIDsByDataSourceID($data_source_id, false, PUBLISHED);
		$i = 0;
		if($ids)
		{
			foreach($ids as $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($ro){
						$ro->enrich();//TODO: XXX
						$ro->update_quality_metadata(); // TODO: MOVE IT WHERE BELONGS!!!
						$solrXML = $ro->transformForSOLR();
						$result = $this->solr->addDoc($solrXML);
						$result = json_decode($result);
						$data['results'][$ro_id] = array(
							'result'=>$result->{'responseHeader'}->{'status'},
							'QTime'=>$result->{'responseHeader'}->{'QTime'}
						);
						if($result->{'responseHeader'}->{'status'}!=0){
							$data['results'][$ro_id]['msg'] = $result->{'error'}->{'msg'};
							$data['error'] .= $result->{'error'}->{'msg'};
						}else{
							//success
							$i++;
						}
					}else{
						$data['results'][$ro_id] = array(
							'result'=>'Not Found!',
							'QTime'=>0
						);
						$data['error'] .= 'RO not found: '. $ro_id.'<br/>';
					}
				}catch (Exception $e){
					$data['results'][$ro_id] = array(
						'result'=>"<pre>" . nl2br($e) . "</pre>",
						'QTime'=>0
					);
					$data['error'] .= nl2br($e);
				}
			}
			$this->solr->commit();
			$data['totalAdded'] = $i;
		}
		echo json_encode($data);
	}

	function clearDS($data_source_id){
		$this->load->library('solr');
		echo $this->solr->clear($data_source_id);
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