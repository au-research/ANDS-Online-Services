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

class View_part extends CI_Controller {

	public function index()
	{
		
	}

	public function homepageStat($sort = 'index'){
		$this->load->model('solr');
		$data['groups']=$this->solr->getStat($sort, 'collection');
		$data['classes']=$this->solr->getStat($sort);
		$this->load->view('stat-view', $data);
	}
	public function cannedText(){
		$sort = $this->input->get('sort');
		$group = $this->input->get('group');	
		$key = $this->input->get('key');		
		$this->load->model('solr');
		$data['content']=$this->solr->getCannedContent($sort,$group,$key);
		$data['group']=$group;			
		$this->load->view('cannedText-view', $data);
	}	
	public function contentStat(){
		$sort = $this->input->get('sort');
		$group = $this->input->get('group');	
		$key = $this->input->get('key');	
		$this->load->model('solr');
		$data['content']=$this->solr->getContent($sort,$group,$key);
		//print_r($data['content']);
		$data['group']=$group;			
		$this->load->view('content-view', $data);
	}
	public function subjectStat(){
		$sort = $this->input->get('sort');
		$group = $this->input->get('group');	
		$this->load->model('solr');
		$data['content']=$this->solr->getSubjects($sort,$group);
		$data['group']=$group;			
		$this->load->view('subject-view', $data);
	}		
	public function collectionStat(){
		$sort = $this->input->get('sort');
		$group = $this->input->get('group');	
		$this->load->model('solr');
		$data['collections']=$this->solr->getCollection($sort, 'collection',$group);
		$data['group']=$group;			
		$this->load->view('collection-view', $data);
	}
	public function groupStat(){
		$sort = $this->input->get('sort');
		$group = $this->input->get('group');	
		$key = $this->input->get('key');	
		$this->load->model('solr');
		$data['collections']=$this->solr->getGroups($sort, $group, $key);
		$data['group']=$group;			
		$this->load->view('group-view', $data);
	}	
	public function getDictionaryTerms(){
		$q = strtolower($_GET["term"]);
		$this->load->model('Registryobjects', 'ro');
		$result = array();
		$terms = $this->ro->getSearchHistory();
		foreach($terms->result() as $t){
			$pos = strrpos($t->search_term, ":");
			if ($pos === false) {//is not a field term
				if (strpos(strtolower($t->search_term), $q) == 0&&strpos(strtolower($t->search_term), $q)!==false) {
			    	array_push($result, array("occurrence"=>$t->occurrence, "label"=>$t->search_term, "value" => strip_tags($t->search_term)));
				}
			}
		}
		echo array_to_json($result);
	}
	

	public function getView()
	{
		$key = $this->input->post('key');
		$this->load->model('Registryobjects', 'ro');
		$content = $this->ro->get($key);
		$data['contentTop'] = $this->transform($content, 'rifcs2MenuHtml.xsl');
		$data['contentLeft'] = $this->transform($content, 'rifcs2View.xsl');
		$data['contentRight'] = $this->transform($content, 'rifcs2ViewRight.xsl');
		$data['class'] = $this->transform($content, 'returnclass.xsl');
		$this->load->view('xml-view2', $data);
	}


	private function transform($registryObjectsXML, $xslt){
		$qtestxsl = new DomDocument();
		$registryObjects = new DomDocument();
		$registryObjects->loadXML($registryObjectsXML);
		$qtestxsl->load('_xsl/'.$xslt);
		$proc = new XSLTProcessor();
		$proc->importStyleSheet($qtestxsl);
		$proc->setParameter('','base_url',base_url());
		$transformResult = $proc->transformToXML($registryObjects);	
		return $transformResult;
	}
	

}

?>