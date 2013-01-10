<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Administration controller
 * 
 * Base stub for administrative control of the registry
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services
 * 
 */
class Administration extends MX_Controller {
	
	public function index()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration';
		$this->load->view('admin_panel', $data);
	}

	public function api_log()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - API Log';

		$this->db->order_by('timestamp','DESC');
		$query = $this->db->get('api_requests', 100);
		$data['log_entries'] = $query;

		$this->load->view('api_log', $data);
	}

	public function api_keys()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Registry Administration - API Log';

		$this->db->order_by('created','DESC');
		$query = $this->db->get('api_keys', 100);

		$api_keys = array();

		foreach ($query->result_array() AS $result)
		{
			$this->db->where(array('api_key'=>$result['api_key'], 'timestamp >=' => (time()-ONE_MONTH)));
			$this->db->from('api_requests');
			$queries_this_month = $this->db->count_all_results();

			$this->db->where(array('api_key'=>$result['api_key']));
			$this->db->from('api_requests');
			$queries_ever = $this->db->count_all_results();

			$api_keys[] = array_merge($result, array(
				"queries_ever"=>$queries_ever,
				"queries_this_month"=>$queries_this_month 
			));
		}
		$data['api_keys'] = $api_keys;
		
		$this->load->view('api_keys', $data);
	}

}