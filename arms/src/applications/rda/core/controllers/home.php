<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia';

		//solr for counts
		$this->load->library('solr');
		$this->solr->setOpt('rows','0');
		$this->solr->setFacetOpt('field', 'class');
		$this->solr->setFacetOpt('field', 'type');
		$this->solr->executeSearch();
		$classes = $this->solr->getFacetResult('class');
		
		$data = array('collection'=>0,'service'=>0,'activity'=>0,'party'=>0);
		foreach($classes as $class=>$num){
			$data[$class] = $num;
		}

		$this->load->view('home', $data);
	}
}