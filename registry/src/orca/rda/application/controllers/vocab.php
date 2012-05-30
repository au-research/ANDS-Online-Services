<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vocab extends CI_Controller {

	public function index(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['activity_name']='vocab';

		$this->load->model('vocabularies', 'vmodel');

		//$data['bigTree']=$this->vmodel->getBigTree($this->config->item('vocab_resolver_service'));
		$this->load->view('vocab/index', $data);
	}

	//ajax 
	function getConcept($num, $vocab){
		$this->load->model('vocabularies', 'vmodel');
		$tree = $this->vmodel->getConceptTree($this->config->item('vocab_resolver_service'), $num, $vocab);
		echo $tree;
	}

	function getConceptDetail($num, $vocab){
		$this->load->model('vocabularies', 'vmodel');
		$data['r'] = $this->vmodel->getConcept($this->config->item('vocab_resolver_service'), $num, $vocab);
		$data['notation'] = $num;
		$data['vocab'] = $vocab;

		//var_dump($data['r']);

		$this->load->model('solr');
		$fields = array(
			'q'=>'broader_subject_value_unresolved:("'.$num.'")','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>'json',
			'fl'=>'key', 'q.alt'=>'*:*'
		);
		$json = $this->solr->fireSearch($fields, '');
		$data['narrower_search_result'] = $json;

		$this->load->model('solr');
		$fields = array(
			'q'=>'subject_value_unresolved:("'.$num.'")','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>'json',
			'fl'=>'key', 'q.alt'=>'*:*'
		);
		$json = $this->solr->fireSearch($fields, '');
		$data['search_result'] = $json;

		$data['prefLabel'] = $data['r']->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
		$this->load->view('vocab/conceptDetail', $data);
	}

	function loadBigTree(){
		$this->load->model('vocabularies', 'vmodel');
		$data['bigTree']=$this->vmodel->getBigTree($this->config->item('vocab_resolver_service'));
		echo $data['bigTree'];
	}

}
?>
