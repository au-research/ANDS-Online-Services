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

class Preview extends CI_Controller {

	public function index()
	{
		parse_str($_SERVER['QUERY_STRING'], $_GET);
	
		// $this->load->view('xml-view', $data);		
		if(isset($_GET['key'])){
			$key = urlencode($_GET['key']);
			$ds = urlencode($_GET['ds']);
			$this->load->model('RegistryObjects', 'ro');
	       	$content = $this->ro->get($key, $ds);
	       	$data['key']= $key;
			$data['content'] = $this->transform($content, 'rifcs2Preview.xsl', $ds);	
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();	
			$data['activity_name'] = 'preview';
			$this->load->view('xml-view', $data);
		}else{
			show_404('Preview Draft');
		}
	}
	
	private function transform($registryObjectsXML, $xslt, $ds){
		$qtestxsl = new DomDocument();
		$registryObjects = new DomDocument();
		$registryObjects->loadXML($registryObjectsXML);
		$qtestxsl->load('_xsl/'.$xslt);
		$proc = new XSLTProcessor();
		$proc->importStyleSheet($qtestxsl);
		$proc->setParameter('','base_url',base_url());
		$proc->setParameter('','orca_home',$this->config->item('orca_url'));
		$proc->setParameter('','dataSource',$ds);
		$transformResult = $proc->transformToXML($registryObjects);	
		return $transformResult;
	}
}
?>
