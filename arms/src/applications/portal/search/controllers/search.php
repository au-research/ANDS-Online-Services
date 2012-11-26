<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia - Search';
		$data['scripts'] = array('search');
		//$data['scripts'] = array('home_page');
		$this->load->view('search_layout', $data);
	}

	function filter(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		/**
		 * Getting the stuffs Ready
		 */
		$pp = 15;//per page
		$page = 1;
		$start = 0;
		$filters = $this->input->post('filters');
		$this->load->library('solr');

		/**
		 * Default Search Parameters for RDA Portal search
		 */
		$this->solr->setOpt('rows', $pp);
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('mm', '3');
		$this->solr->setOpt('q.alt', '*:*');
		$this->solr->setOpt('qf', 'id^100 display_title^50 list_title^50 fulltext^1.2');
		$facets = array(
			'class' => 'Class',
			'subject_value_resolved' => 'Subjects',
			'group' => 'Contributed By',
			'type' => 'Type'
		);
		foreach($facets as $facet=>$display){
			$this->solr->setFacetOpt('field', $facet);
		}
		$this->solr->setFacetOpt('mincount','1');

		/**
		 * Setting the SOLR OPTIONS based on the filters sent over AJAX
		 */
		if($filters){
			foreach($filters as $key=>$value){
				$value = urldecode($value);
				switch($key){
					case 'q': $this->solr->setOpt('q', $value);break;
					case 'p': 
						$page = (int)$value;
						if($page>1){
							$start = $pp * ($page-1);
						}
						$this->solr->setOpt('start', $start);
						break;
					case 'tab': 
						if($value!='all') $this->solr->setOpt('fq', 'class:("'.$value.'")');
						break;
					case 'group': 
						$this->solr->setOpt('fq', 'group:("'.$value.'")');
						break;
					case 'type': 
						$this->solr->setOpt('fq', 'type:'.$value);
						break;
					case 'subject': 
						$this->solr->setOpt('subject_value_resolved', $value);
						break;
				}
				
			}
		}

		/**
		 * Doing the search
		 */
		$data['solr_result'] = $this->solr->executeSearch();


		/**
		 * Getting the results back
		 */
		$data['solr_header'] = $this->solr->getHeader();
		$data['result'] = $this->solr->getResult();
		$data['numFound'] = $this->solr->getNumFound();
		$data['currentPage'] = $page;
		$data['totalPage'] = ceil($data['numFound'] / $pp);
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;

		/**
		 * House cleaning on the facet_results
		 */
		$data['facet_result'] = array();
		foreach($facets as $facet=>$display){
			$facet_values = array();
			$solr_facet_values = $this->solr->getFacetResult($facet);
			foreach($solr_facet_values AS $title => $count){
				$facet_values[] = array(
					'title' => $title,
					'count' => $count
				);
			}
			// little bit different with class being tab
			if($facet!='class'){
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
			}else{
				$data['selected_tab'] = $facet;
			}
		}

		/**
		 * Debugging
		 */
		$data['options'] = $this->solr->getOptions();
		$data['facet_counts'] = $this->solr->getFacet();
		$data['fieldstrings'] = $this->solr->constructFieldString();

		//return the result to the client
		echo json_encode($data);
	}
}