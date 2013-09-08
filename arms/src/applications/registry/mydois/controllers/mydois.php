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
		acl_enforce('DOI_USER');

		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'My DOIs List';
		$data['associated_app_ids'] = array();

		if($this->user->loggedIn())
		{

			if (count($this->user->affiliations()))
			{
				$roles_db = $this->load->database('roles', TRUE);
				$roles_db->distinct()->select('parent_role_id')
						->where_in('child_role_id', $this->user->affiliations())
						->where('role_type_id', 'ROLE_DOI_APPID      ', 'after')
						->join('roles', 'role_id = parent_role_id')
						->from('role_relations');
				$query = $roles_db->get();

				if ($query->num_rows() > 0)
				{
					foreach ($query->result() AS $result)
					{
						$data['associated_app_ids'][] = $result->parent_role_id;
					}
				}
			}
			
			$this->load->view('input_app_id', $data);
		}else{
			$this->load->view('login_required', $data);
		}
	}
	
	function show()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'DOI Query Tool';
		
		// Validate the appId
		$appId = $this->input->get_post('app_id');
		$doi_update = $this->input->get_post('doi_update');
		$error = $this->input->get_post('error');
		if (!$appId)
		{
			$appId = $this->input->get_post('app_id_select');
		}
		$doiStatus = $this->input->get_post('doi_status');
		$data['doi_appids'] = $this->user->doiappids();
		if($doi_update)
		{
				$data['doi_update'] = $doi_update;	
		}
		if($error)
		{
				$data['error'] = $error;	
		}
		if (!$appId) throw new Exception ('Invalid App ID');  
		
		if(!in_array($appId, $data['doi_appids'] ))
		{
			throw new Exception ('You do not have authorisation to view dois associated with application id '.$appId);  
		}


		
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');  
		$client_obj = array_pop($client_obj);
		
		// Store the recently used app id in the client cookie
		$this->input->set_cookie('last_used_doi_appid', $appId, 9999999);
		//087391e742ee920e4428aa6e4ca548b190138b89

		$query = $doi_db->order_by('updated_when', 'desc')->order_by('created_when', 'desc')->where('client_id',$client_obj->client_id)->where('status !=','REQUESTED')->select('*')->get('doi_objects');

		
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
		acl_enforce('DOI_USER');
		
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
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the doi_id
		$doi_id = rawurldecode($this->input->get_post('doi_id'));
		if (!$doi_id) throw new Exception ('Invalid DOI ID');  
		
		$query = $doi_db->where('doi_id',$doi_id)->select('doi_id, datacite_xml')->get('doi_objects');
		if (!$doi_obj = $query->result_array()) throw new Exception ('Invalid DOI ID');  
		$doi_obj = array_pop($doi_obj);

		$this->load->view('view_datacite_xml',$doi_obj);
		
	}
	
	function updateDoi()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);
		
		// Validate the doi_id
		$doi_id = rawurldecode($this->input->get_post('doi_id'));

		if (!$doi_id) throw new Exception ('Invalid DOI ID');  
		
		$query = $doi_db->where('doi_id',$doi_id)->select('doi_id, url,client_id')->get('doi_objects');
		if (!$doi_obj = $query->result_array()) throw new Exception ('Invalid DOI ID');  
		$doi_obj = array_pop($doi_obj);
		$this->load->view('update_doi',$doi_obj);
		
	}
	function updateDoiUrl()
	{
		acl_enforce('DOI_USER');
		
		$doi_db = $this->load->database('dois', TRUE);

		// Validate the url
		$new_url = rawurldecode($this->input->get_post('new_url'));
		$old_url = rawurldecode($this->input->get_post('old_url'));
		$doi_id = rawurldecode($this->input->get_post('doi_id'));
		$client_id = rawurldecode($this->input->get_post('client_id'));
		


		if (!$client_id || !$old_url || !$doi_id)
		{
			throw new Exception("Unable to update DOI. Not all parameters were given");
		}

		$query = $doi_db->where('client_id',$client_id)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid Client ID');  
		$client_obj = array_pop($client_obj);
		
		$doi_appids = $this->user->doiappids();
		if(!in_array($client_obj->app_id, $doi_appids))
		{
			throw new Exception ('You do not have authorisation to update DOI  '.$doi_id);  
		}
		$query = $doi_db->where('client_id',$client_id)->select('client_domain')->get('doi_client_domains');
		if(!$new_url)
		{
			$message = "param 'url' required";
			$client = str_replace("-","0",$client_id);
			$logdata = array(
               'timestamp' =>  "NOW()",
               'client_id' => $client,
               'activity' => "UPDATE", 
               'doi_id'  => $doi_id, 
               'result'    => "FAILURE",  
               'message'    => $message,      
            	);
			$doi_db->insert('activity_log', $logdata); 
			redirect('/mydois/show/?app_id='.$client_obj->app_id.'&doi_update='.urlencode($message)."&error=yes", 'location');
		}
		
		$validDomain = false;
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() AS $result)
			{
				if(str_replace($result->client_domain,"",$new_url)==$result->client_domain)
				{
					$validDomain = $result->client_domain;
				}
			}
		}

		if(!$validDomain)
		{
			$message = "Invalid top level domain provided in url ";
			$client = str_replace("-","0",$client_id);
			$logdata = array(
               'timestamp' =>  "NOW()",
               'client_id' => $client,
               'activity' => "UPDATE", 
               'doi_id'  => $doi_id, 
               'result'    => "FAILURE",  
               'message'    => $message,      
            	);
			$doi_db->insert('activity_log', $logdata); 
			redirect('/mydois/show/?app_id='.$client_obj->app_id.'&doi_update='.urlencode($message)."&error=yes", 'location');				
		} 

		if($client_id<10) $client_id = '-'.$client_id;	

		$requestURI =  $this->config->item('gDOIS_SERVICE_BASE_URI');
		$authstr = $this->config->item('gDOIS_DATACENTRE_NAME_PREFIX').".".$this->config->item('gDOIS_DATACENTRE_NAME_MIDDLE').$client_id.":".$this->config->item('gDOIS_DATACITE_PASSWORD');	

		$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
		$metadata="url=".$new_url."\ndoi=".$doi_id;
		$requestURI = $this->config->item('gDOIS_SERVICE_BASE_URI')."doi";
		$result = '';
		$extrainfo = '';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_URL, $requestURI);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
		curl_setopt($ch, CURLOPT_HTTPHEADER,$context);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$metadata);
		$result = curl_exec($ch);
	
		$curlinfo = curl_getinfo($ch);
	
		curl_close($ch);
	
		if($result == $this->config->item('gDOIS_RESPONSE_SUCCESS'))
		{
			//if its all Ok - update the database and return to the doi listing
			$data = array(
               'url' => $new_url,
               'updated_when' => "NOW()",
            	);
			$doi_db->where('doi_id', $doi_id);
			$doi_db->update('doi_objects', $data); 
			$message =  "DOI ".$doi_id." was successfully update to url '".$new_url."' with ".$validDomain;
			$client = str_replace("-","0",$client_id);
			$logdata = array(
               'timestamp' =>  "NOW()",
               'client_id' => $client,
               'activity' => "UPDATE", 
               'doi_id'  => $doi_id, 
               'result'    => "SUCCESS",  
               'message'    => $message,      
            	);
			$doi_db->insert('activity_log', $logdata); 
			redirect('/mydois/show/?app_id='.$client_obj->app_id.'&doi_update='.$message, 'location');
		}else{
			//we got an error back or nothing so we need to tell the user something went wrong
			if($result) $extrainfo = "The following error message was returned : ".$result;
			throw new Exception ('Update of the doi was unsuccessful. ' . $extrainfo);  
		}
	
	}
	function getAppIDConfig()
	{
		acl_enforce('DOI_USER');
		
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
	