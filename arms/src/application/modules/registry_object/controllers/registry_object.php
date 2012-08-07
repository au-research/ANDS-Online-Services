<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Registry Object controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/registryobject
 * 
 */
class Registry_object extends MX_Controller {

	public function manage($data_source_id = false){
		$data['title'] = 'Manage My Records';

		if($data_source_id){
			$this->load->model('data_source/data_sources', 'ds');
			$data_source = $this->ds->getByID($data_source_id);
			if(!$data_source) show_error("Unable to retrieve data source id = ".$data_source_id, 404);
			$data['data_source'] = $data_source;
			$data['scripts'] = array('registry_objects');
			$this->load->view("registry_object_index", $data);
		}else{
			show_error('No Data Source ID provided. use all data source view for relevant roles');
		}
	}

	//AJAX function for MMR to search
	public function get_records(){
		$fields = $this->input->post('fields');
		$page = $this->input->post('page');


		$q = '';$i = 0;//counter
		foreach($fields as $field=>$val){
			if($i!=0)$q.=' AND ';
			
			if($field=='list_title'){
			$q .=$field.':(*'.$val.'*)';
			}else{
				$q .=$field.':('.$val.')';
			}
			$i++;
		}


		$start = 0; $row = 15;
		if($page!=1) $start = ($page - 1) * $row;

		

		$this->load->model('solr');
		$fields = array(
			'q'=>$q,'start'=>$start,'indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>$row
		);
		$facets = 'facet=true&facet.field=class';
		$solr_search_result = $this->solr->fireSearch($fields, $facets);

		$solr_header = $solr_search_result->{'responseHeader'};
		$solr_result = $solr_search_result->{'response'}->{'docs'};
		$solr_facet = $solr_search_result->{'facet_counts'}->{'facet_fields'};

		//echo '<pre>';
		$items = array();
		foreach($solr_result as $doc){
			$item = array();

			//get all stuffs in there so that we don't miss anything
			foreach($doc as $key=>$attrib){
				$item[$key] = $attrib;
			}

			//fix multi-valued description
			//LOGIC: only if there's a description if there's a brief, use it, if there's none, use first one
			if(isset($doc->{'description_value'})){
				foreach($doc->{'description_type'} as $key=>$type){
					if($type=='brief'){//use it
						$item['description'] = $doc->{'description_value'}[$key];
					}
				}
				if(!isset($item['description'])){
					$item['description'] = $doc->{'description_value'}[0];
				}
			}
			if(!isset($item['description'])){
				$item['description'] = '';
			}
			array_push($items, $item);
		}
		//var_dump($items);

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$jsonData['q'] = $solr_header;
		$jsonData['items'] = $items;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
		
	}

	public function browse($data_source_id, $page){
		//default values
		$start = 0; $row = 15;
		if($page!=1) $start = ($page - 1) * $row;

		$this->load->model('solr');
		$fields = array(
			'q'=>'+data_source_id:'.$data_source_id,'version'=>'2.2','start'=>$start,'indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>$row
		);
		$facets = 'facet=true&facet.field=class';
		$solr_search_result = $this->solr->fireSearch($fields, $facets);

		$solr_header = $solr_search_result->{'responseHeader'};
		$solr_result = $solr_search_result->{'response'}->{'docs'};
		$solr_facet = $solr_search_result->{'facet_counts'}->{'facet_fields'};

		//echo '<pre>';
		$items = array();
		foreach($solr_result as $doc){
			$item = array();

			//get all stuffs in there so that we don't miss anything
			foreach($doc as $key=>$attrib){
				$item[$key] = $attrib;
			}

			//fix multi-valued description
			//LOGIC: only if there's a description if there's a brief, use it, if there's none, use first one
			if(isset($doc->{'description_value'})){
				foreach($doc->{'description_type'} as $key=>$type){
					if($type=='brief'){//use it
						$item['description'] = $doc->{'description_value'}[$key];
					}
				}
				if(!isset($item['description'])){
					$item['description'] = $doc->{'description_value'}[0];
				}
			}
			if(!isset($item['description'])){
				$item['description'] = '';
			}
			array_push($items, $item);
		}
		//var_dump($items);

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$jsonData['items'] = $items;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
}	