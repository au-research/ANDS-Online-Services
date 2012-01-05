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

class View extends CI_Controller {


	public function index()
	{
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		
		if(isset($_GET['key'])){
			$key = ($_GET['key']);
			//echo $key;
			$this->load->model('RegistryObjects', 'ro');
			$this->load->model('solr');
	       	$content = $this->ro->get($key);
	       	$data['key']= $key;
			$data['content'] = $this->transform($content, 'rifcs2View.xsl',urlencode($key));	
			
			$obj = $this->solr->getByKey($key);
			$numFound = $obj->{'response'}->{'numFound'};
			$doc = ($obj->{'response'}->{'docs'}[0]);
			//echo $numFound;
			
			$data['title'] = $doc->{'displayTitle'};
			
			if(isset($doc->{'description_value'}[0]))$data['description']=htmlentities($doc->{'description_value'}[0]);
			$data['doc'] = $doc;
			
			
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();
			
			
			if($numFound>0){
				$this->load->view('xml-view', $data);
			}else show_404('page');
			
		}else{
			show_404('page');
		}
	}

	public function viewitem($key){
		redirect('view/?key='.$key);
	}
	
	public function printview(){
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		
		if(isset($_GET['key'])){
			$key = $_GET['key'];
			$this->load->model('RegistryObjects', 'ro');
	       	$content = $this->ro->get($key);
	       	$data['key']= $key;  	
			$data['content'] = $this->transform($content, 'rifcs2View.xsl',$key);	
			
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();
			
			$this->load->view('print-view', $data);
		}else{
			show_404('page');
		}
		
	}	
	
	private function transform($registryObjectsXML, $xslt,$key){
		$qtestxsl = new DomDocument();
		$registryObjects = new DomDocument();
		$registryObjects->loadXML($registryObjectsXML);
		$qtestxsl->load('_xsl/'.$xslt);
		$proc = new XSLTProcessor();
		$proc->importStyleSheet($qtestxsl);
		$proc->setParameter('','base_url',base_url());
		$orca_view = view_url();
		$proc->setParameter('','orca_view',$orca_view);
		$proc->setParameter('','key',$key);
		$transformResult = $proc->transformToXML($registryObjects);	
		return $transformResult;
	}
}
?>