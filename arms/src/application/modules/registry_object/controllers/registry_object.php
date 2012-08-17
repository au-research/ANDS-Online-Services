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
			$data['data_source_title'] = $data_source->title;
			$data['data_source_id'] = $data_source->id;
			$data['scripts'] = array('registry_objects');
			$this->load->view("registry_object_index", $data);
		}else{
			//showing all registry objects for all datasource
			$data['data_source_title'] = 'Viewing All Registry Objects';
			$data['data_source_id'] = 0;
			$data['scripts'] = array('registry_objects');
			$data['js_lib'] = array('core');
			$this->load->view("registry_object_index", $data);
			//show_error('No Data Source ID provided. use all data source view for relevant roles');
		}
	}

	//AJAX function for MMR to search
	public function get_records(){
		$fields = $this->input->post('fields');
		$sorts = $this->input->post('sorts');
		$page = $this->input->post('page');

		
		$q = '';$i = 0;//counter
		if($fields){
			foreach($fields as $field=>$val){
				if($i!=0)$q.=' AND ';
				
				if($field=='list_title'){
					$q .=$field.':(*'.$val.'*)';
				}else{
					$q .=$field.':('.$val.')';
				}
				$i++;
			}
		}
		if($q=='')$q='*:*';


		$start = 0; $row = 15;
		if($page!=1) $start = ($page - 1) * $row;

		$this->load->model('solr');
		$fields = array(
			'q'=>$q,'start'=>$start,'indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>$row
		);
		if($sorts && $sorts!=''){
			$fields['sort']=$sorts;
		}
		$facets = '&facet=true&facet.sort=index&facet.mincount=1&facet.field=class&facet.field=status&facet.field=quality_level';
		$solr_search_result = $this->solr->fireSearch($fields, $facets);

		$solr_header = $solr_search_result->{'responseHeader'};
		$solr_response = $solr_search_result->{'response'};
		$num_found = $solr_response->{'numFound'};
		$facet_fields = $solr_search_result->{'facet_counts'}->{'facet_fields'};


		$jsonData = array();

		$items = array();
		if($num_found>0){
			$jsonData['no_more'] = false;
			$solr_result = $solr_response->{'docs'};
			//echo '<pre>';
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
		}else{
			$jsonData['no_more'] = true;
		}

		$facets = array();
		foreach($facet_fields as $field=>$array){
			for($i=0;$i<sizeof($array)-1;$i=$i+2){
				$field_name = $array[$i];
				$value = $array[$i+1];
				$facets[$field][$field_name] = $value;
			}
		}
		

		
		$jsonData['status'] = 'OK';
		$jsonData['q'] = $solr_header;
		$jsonData['items'] = $items;
		$jsonData['num_found'] = $num_found;
		$jsonData['facets'] = $facets;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
		
	}
}	