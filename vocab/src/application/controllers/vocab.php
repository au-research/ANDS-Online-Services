<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
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
*******************************************************************************/

class Vocab extends CI_Controller {

	public function _remap($method, $params = array())
	{
	    if (method_exists($this, $method))
	    {
	        return call_user_func_array(array($this, $method), $params);
	    }
	    else 
	    {
	    	// prepend the method to the params array 
	    	// this is a purl partial redirect
	    	array_unshift($params, $method);
	    	return call_user_func_array(array($this, "getConcepts"), $params);
	    }
	    show_404();
	}
	
	
	public function index()
	{
		$this->load->view('vocab/landing_page');
	}
	

	public function getConceptSchemes($filter='')
	{
		if ($this->input->get('filter'))	$filter = $this->input->get('filter',true);
						
		// Get the concept schemes ("vocabularies")
		$this->load->model('Concept_model');
		$concept_schemes = $this->Concept_model->getAllConceptSchemes($filter);
		
		$this->load->view('vocab/concept_scheme_view', array('concept_schemes'=>$concept_schemes));
	}
	
	public function getConcepts($vocab_id='', $vocab_version='', $identifier='', $depth_param=false)
	{
		//$this->output->cache(5); xxx 
		
		if ($this->input->get('depthParam'))	$depth_param = $this->input->get('depthParam',true);
		
		// Get the concept(s) ("terms")
		$this->load->model('Concept_model');
		$schemes = array();
		
		$depth_configuration = array();
		// If no identifier specified, then get ALL related concepts
		if (!$identifier)
		{
			// Get all related concepts
			$depth_configuration['b'] = 99; 
			$depth_configuration['n'] = 99; 
			$depth_configuration['m'] = 99;
			
			if ($depth_param)
			{
				$depth_configuration =  $this->generateDepthConfig($depth_param);
			}
			
			// Get the top level Concept Scheme
			$schemes = $this->Concept_model->getAllConceptSchemes($vocab_id, $vocab_version);
		}

		//if ($depth_param)
		//{
			$depth_configuration =  $this->generateDepthConfig($depth_param);
		//}
		
				
		$concepts = $this->Concept_model->getConcepts($vocab_id, $vocab_version, $identifier, $depth_configuration);	
		
		$this->load->view('vocab/concept_view', array('concepts'=>$concepts, 'concept_schemes'=>$schemes));
	}
	
	
	public function purlRequest($param1 = '', $param2 = '', $param3 = '', $param4 = '')
	{
		$depth_param = false;
		if ($this->input->get('depthParam')) $depth_param = $this->input->get('depthParam',true);

		//$depth_configuration = $this->generateDepthConfig($depth_param);
		$this->getConcepts($param1, $param2, $param3);
	}
	
	public function getMenus($vocabulary, $version, $term='')
	{
		$this->load->view('vocab/default_menu_view', array('root'=>$term));
	}	
	
	public function getConceptByIdentifier($identifier='')
	{
		if ($this->input->get('identifier'))	$identifier = $this->input->get('identifier',true);
										
		// Get the concept(s) ("terms")
		$this->load->model('Concept_model');
		$concepts = $this->Concept_model->getConceptsByPURL($identifier);
		
		$this->load->view('vocab/concept_view', array('concepts'=>$concepts));
		
	}

	
	private function generateDepthConfig($depth_param=false)
	{
		// Calculate how "far" from the specified concept to visit based on the depth parameter
		// depth_param is of the form    b:0:n:0:m:0     
		$depth_configuration = array();
		$depth_configuration['b'] = 0; // broader relationships
		$depth_configuration['n'] = 0; // narrower relationships
		$depth_configuration['m'] = 0; // other "match" relationships

		if ($depth_param)
		{
			if (preg_match_all("/[[a-z]{1}:[0-9]{1,2}:{0,1}]{0,3}/", $depth_param, $regex_match_array))
			{
				
				while ($match = array_pop($regex_match_array[0]))
				{
					$match = explode(":", $match);
					if (isset($depth_configuration[$match[0]]))
					{
						$depth_configuration[$match[0]] = (int) $match[1];
					}
				}
			}
		}
		
		return $depth_configuration;
	}
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */