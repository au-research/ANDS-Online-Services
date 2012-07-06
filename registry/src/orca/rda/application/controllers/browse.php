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

class Browse extends CI_Controller {

	public function index(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['activity_name']='vocab';

		$this->load->model('vocabularies', 'vmodel');

		//$data['bigTree']=$this->vmodel->getBigTree($this->config->item('vocab_resolver_service'));
		$this->load->view('browse/index', $data);
	}

	//ajax
	function getConcept(){
		$this->load->model('vocabularies', 'vmodel');
		$uri = strtolower($this->input->post('uri'));
		$vocab = strtolower($this->input->post('vocab'));
		$params = $this->input->post('params');
		if($params) {
			$this->load->model('solr');
			$params = $this->solr->constructQuery($params);
		}
		$tree = $this->vmodel->getConceptTree($this->config->item('vocab_resolver_service'), $uri, $vocab, $params);
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
		$this->load->view('browse/conceptDetail', $data);
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
		$row = 15;$start=0;
		if($page!=1) $start = ($page - 1) * $row;
		$uri = $this->input->post('uri');
		//echo $uri;
		if($type=='exact'){
			$q = '+subject_vocab_uri:("'.$uri.'")';
		}elseif($type=='narrower'){
			$q = '+broader_subject_vocab_uri:("'.$uri.'")';
		}elseif($type=='both'){
			// exact matches to the top
			$q = '(+subject_vocab_uri:("'.$uri.'")^2000 OR +broader_subject_vocab_uri:("'.$uri.'"))';
		}
		$q.=' +class:collection +status:PUBLISHED';
		$this->load->model('solr');
		$fields = array(
			'q'=>$q,'version'=>'2.2','start'=>$start,'rows'=>$row,'indent'=>'on', 'wt'=>'json',
			'fl'=>'key, list_title, url_slug, description_value', 'q.alt'=>'*:*', 'sort'=>'date_modified desc',
		);

		//var_dump($fields);
		$json = $this->solr->fireSearch($fields, '');
		$data['search_result'] = $json;
		$data['type']= $type;
		$data['page']= $page;
		$data['vocab_uri']=$uri;
		$this->load->view('browse/minisearch', $data);
	}

	function vocabAutoComplete($where='anzsrcfor'){
		$term = $_GET["term"];
		$params = urldecode($this->input->get('params'));
		parse_str($params, $params);
		$this->load->model('vocabularies', 'vmodel');
		if($where=='anzsrcfor'){
			$data['result']=$this->vmodel->labelContain($this->config->item('vocab_resolver_service'), $term);
			$result = array();
			foreach($data['result'] as $key=>$vocab_result){
				foreach($vocab_result as $item){
					array_push($result, array('label'=>$item['prefLabel'], 'uri'=>$item['uri'], 'vocab'=>$key));
				}
			}
		}else{
			$data['result'] = $this->vocabKeywords($where, $term, $params);
			$result = array();
			$categories = $this->config->item('subjects_categories');
			foreach($data['result'] as $r){
				array_push($result, array('label'=>$r, 'vocab'=>$categories[$where]['display']));
			}
		}
		
		// no result? oh dear!
		if (count($result) == 0)
		{
			array_push($result, array('label'=>"No matches found", 'vocab'=>"", "nomatches"=>"true"));
		}
			
		echo array_to_json($result);
		
	}

	function vocabKeywords($subject_category, $term, $params){
		//default categories
    	$categories = $this->config->item('subjects_categories');
    	$set = $categories[$subject_category]['list'];

    	//add the restrictions on these subjects
    	$restrictions = '(';
    	foreach($set as $s){
    		$restrictions .= "type = '$s' ";
    		if($s!=end($set)) $restrictions .= ' OR ';
    	}
    	$restrictions .= ')';
    	//echo $restrictions;
    	$restrictions .= " AND value ILIKE '%$term%'";

    	$this->load->database();
    	$query = 'select distinct(value), count(value) from dba.tbl_subjects where '.$restrictions.' group by value order by value asc';
    	//echo $query;
		$all_subjects = $this->db->query($query);
		$result = array();
		foreach($all_subjects->result() as $s){
			array_push($result, $s->value);
		}
		//$result = array_slice($result, 0, 200);

		$this->load->model('solr');
		$params = $this->solr->constructQuery($params);
		$limit = 50;
		$real_result = array();
		foreach($result as $r){
			if($limit > 0){
				$count = $this->solr->getSOLRCountForSubjects($params, array($r));
				if($count>0){
					array_push($real_result, $r);
					$limit--;
				}
			}
			
		}
		return $real_result;
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
