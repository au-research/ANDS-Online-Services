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
	function getConcept(){
		$this->load->model('vocabularies', 'vmodel');
		$uri = strtolower($_GET["uri"]);
		$vocab = strtolower($_GET["vocab"]);
		$tree = $this->vmodel->getConceptTree($this->config->item('vocab_resolver_service'), $uri, $vocab);
		echo $tree;
	}


	function getConceptDetail(){
		$uri = strtolower($_GET["uri"]);
		$vocab = strtolower($_GET["vocab"]);
		$this->load->model('vocabularies', 'vmodel');
		$data['r'] = $this->vmodel->getConcept($this->config->item('vocab_resolver_service'), $uri, $vocab);
		$data['notation'] = $data['prefLabel'] = $data['r']->{'result'}->{'primaryTopic'}->{'notation'};
		$data['vocab'] = $vocab;

		$data['prefLabel'] = $data['r']->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
		$data['uri']=$data['r']->{'result'}->{'primaryTopic'}->{'_about'};
		$this->load->view('vocab/conceptDetail', $data);
	}

	function loadBigTree(){
		$this->load->model('vocabularies', 'vmodel');
		$selected['uri'] = strtolower($_GET["selected_node"]);
		$selected['vocab'] = strtolower($_GET["selected_vocab"]);
		if($selected['uri']!='root'){
			$broaders = $this->getBroader($selected);
			$data['bigTree']=$this->vmodel->getBigTree($this->config->item('vocab_resolver_service'),$broaders);
		}else{
			$data['bigTree']=$this->vmodel->getBigTree($this->config->item('vocab_resolver_service'));
		}
		
		echo $data['bigTree'];
	}

	function vocabSearchResult($type, $page=1){
		$q = '';
		$row = 5;$start=0;
		if($page!=1) $start = ($page - 1) * $row;
		$uri = $this->input->post('uri');
		//echo $uri;
		if($type=='exact'){
			$q = 'subject_vocab_uri:("'.$uri.'")';
		}elseif($type=='narrower'){
			$q = 'broader_subject_vocab_uri:("'.$uri.'")';
		}
		$q.=' +class:collection';
		$this->load->model('solr');
		$fields = array(
			'q'=>$q,'version'=>'2.2','start'=>$start,'rows'=>$row,'indent'=>'on', 'wt'=>'json',
			'fl'=>'key, list_title, url_slug, description_value', 'q.alt'=>'*:*'
		);
		//var_dump($fields);
		$json = $this->solr->fireSearch($fields, '');
		$data['search_result'] = $json;
		$data['type']= $type;
		$data['page']= $page;
		$this->load->view('vocab/minisearch', $data);
	}

	function vocabAutoComplete(){
		$term = strtolower($_GET["term"]);
		$this->load->model('vocabularies', 'vmodel');
		$data['result']=$this->vmodel->labelContain($this->config->item('vocab_resolver_service'), $term);

		$json_result = array();
		foreach($data['result'] as $key=>$vocab_result){
			foreach($vocab_result as $item){
				array_push($json_result, array('label'=>$item['prefLabel'], 'uri'=>$item['uri'], 'vocab'=>$key));
			}
		}
		echo array_to_json($json_result);
	}

	function getBroader($selected){
		$this->load->model('vocabularies', 'vmodel');
		$parents = array();
		array_push($parents, $selected['uri']);
		$parents = $this->vmodel->getBroader($this->config->item('vocab_resolver_service'), $selected['uri'], $selected['vocab'], $parents);
		return $parents;
	}

	function reloadTree(){
		$selected['uri'] = strtolower($_GET["selected_uri"]);
		$selected['vocab'] = strtolower($_GET["selected_vocab"]);
		$this->load->model('vocabularies', 'vmodel');
		$broaders = $this->getBroader($selected);
		$data['tree'] = $this->vmodel->sloadTree($this->config->item('vocab_resolver_service'), $selected, $broaders);
		echo $data['tree'];
	}

}
?>
