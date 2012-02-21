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

/**
Dispatcher class will receive all requests which do not match a higher
defined $route in config.php. The dispatcher acts as an emulator for the
CodeIgniter Reactor controller and dispatches requests to the appropriate
controller/method (where it exists) based on the URL parameters.

The purpose of the dispatcher is to provide logic for handling "psuedo"/
"friendly" URLs such as URLs containing record titles or root-level view
records (i.e. url.org.au/fish_in_the_antarctic_region).

Furthermore, this controller can be extended with any logic that is appropriate
to the request BEFORE it is handled by the controller specified in the URL. 
*/
class Dispatcher extends CI_Controller {
	
    public function __construct()
    {
         parent::__construct();
		 $this->load->helper('rda_url');
    }

	public function _remap($method, $params = array())
	{		

		if (file_exists(APPPATH.'controllers/'.$method.EXT))
		{
			include(APPPATH.'controllers/'.$method.EXT);
			$controller = new $method();
			
		    if (count($params) > 0 && method_exists($controller, $params[0]))
		    {
				$method = array_shift($params);
				call_user_func_array(array($controller, $method), $params);
				return;
			}
			else
			{
				call_user_func_array(array($controller, 'index'), $params);
				return;
			}
		}
		else
		{

			$record_hash = $this->_getMappingFor($method);

			if (!$record_hash)		
			{
				// check if it used to exist?
				// display soft error
			}
			else
			{
				include('./application/controllers/view.php');
				$view_controller = new View();
				$view_controller->db = $this->db;
				array_unshift($params, $method);
				array_unshift($params, $record_hash);
			
				call_user_func_array(array($view_controller, 'view_by_hash'), array($params));
				return;
			}

		}
	    show_404();
	}


	function _getMappingFor($uri_fragment)
	{
		$query = $this->db
						->select('key_hash')
						->join('dba.tbl_registry_objects ro', 'url.registry_object_key = ro.registry_object_key')
						->order_by('url.date_created', 'DESC')
						->get_where('dba.tbl_url_mappings url', array('url_fragment' => $uri_fragment), 1);

		if ($query->num_rows() > 0)
		{
			$result = $query->row();
			return $result->key_hash;
		}
		else
		{
			return false;
		}
		
	}
	
	function _generateInitialMappings()
	{
		$this->db->save_queries = false; 

		/*
		$query = $this->db->select('registry_object_key, display_title')->get('dba.tbl_registry_objects');
		foreach ($query->result() as $row)
		{
			
			$data = array(
			   'url_fragment' => $this->_generateUniqueSlug($row->display_title, $row->registry_object_key),
			   'registry_object_key' =>  $row->registry_object_key,
			   'date_created' => time(),
			   'search_title' => $row->display_title
			);
			$this->db->insert('dba.tbl_url_mappings', $data); 
		}

		$query = $this->db->select('registry_object_key')->get('dba.tbl_registry_objects');
		foreach ($query->result() as $row)
		{
			$data = array(
			   'url_slug' => getSlugForRecordByKey($row->registry_object_key)
		    );
		   
			$this->db->update('dba.tbl_registry_objects', $data, "registry_object_key = " . $row->registry_object_key);
		
		}
		*/
		
	}
	
	
	
	function _generateUniqueSlug($display_title, $key)
	{
		
		$slug = generateSlug($display_title);
		//no name/title
		if ($slug == self::NO_NAME_OR_TITLE_SLUG)
		{
			$slug = generateSlug($key);
		}

		$existing_mappings = $this->db
						        ->where('url_fragment', $slug)
						        ->where('registry_object_key != ', $key)
						        ->count_all_results('dba.tbl_url_mappings');

		if ($existing_mappings > 0)
		{
			$key_slug = generateSlug($key);
			if ($slug != $key_slug)
			{
				$slug .= "-" . $key_slug;
			}
		}
		
		while ($existing_mappings > 0)
		{
			$slug .= "-";
			$query = $this->db
					        ->where('url_fragment', $slug)
					        ->where('registry_object_key != ', $key)
							->count_all_results('dba.tbl_url_mappings');
		}
		
		
		return $slug; 
	}
	

}

?>