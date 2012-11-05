<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 * Services controller
 * 
 * Abstract services controller allows for easy extension of the
 * services module and logging and access management of requests
 * via the API key system. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services
 * 
 */
class Mydois extends MX_Controller {

	function index()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'My DOIs List';
		$this->load->view('input_app_id', $data);
	}
	
	function show()
	{
		$doi_db = $this->load->database('dois', TRUE);
		
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'DOI Query Tool';
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		$doiStatus = $this->input->get_post('doi_status');
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		// Store the recently used app id in the client cookie
		$this->input->set_cookie('last_used_doi_appid', $appId, 9999999);
		//087391e742ee920e4428aa6e4ca548b190138b89

		$query = $doi_db->where('client_id',$client_obj->client_id)->where('status !=','REQUESTED')->select('*')->order_by('updated_when DESC, created_when DESC')->get('doi_objects');

		
		$data['dois'] = array();
		foreach ($query->result() AS $doi)
		{
			$data['dois'][] = $doi;
		}
		
		$data['client'] = $client_obj;
		$this->load->view('list_dois', $data);

	}

	function getActivityLog()
	{
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		$query = $doi_db->order_by('timestamp', 'desc')->where('client_id',$client_obj->client_id)->select('*')->limit(50)->get('activity_log');
		$this->load->view('view_activity_log',array("activities"=>$query->result()));
		

	}
	
	function getDoiXml()
	{
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the doi_id
		$doi_id = rawurldecode($this->input->get_post('doi_id'));
		if (!$doi_id) throw new Exception ('Invalid DOI ID');  
		
		$query = $doi_db->where('doi_id',$doi_id)->select('doi_id, datacite_xml')->get('doi_objects');
		if (!$doi_obj = $query->result_array()) throw new Exception ('Invalid DOI ID');  
		$doi_obj = array_pop($doi_obj);

		$this->load->view('view_datacite_xml',$doi_obj);
		
	}
	
	function getAppIDConfig()
	{
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result_array()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		$query = $doi_db->where('client_id',$client_obj['client_id'])->select('client_domain')->get('doi_client_domains');
		foreach ($query->result_array() AS $domain)
		{
			$client_obj['permitted_url_domains'][] = $domain['client_domain'];
		}

		$this->load->view('view_app_id_config', $client_obj);
		
		
	}
		
		
}
	