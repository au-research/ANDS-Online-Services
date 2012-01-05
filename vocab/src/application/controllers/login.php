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

class Login extends CI_Controller {

	/**
	 * Authentication Controller for this application.
	 *
	 * Allows other applications to authenticate users against the COSI
	 * user database system and retrieve their associated roles. 
	 * 
	 */
	public function index()
	{
		$this->load->view('authentication_api_message');
	}
	
	public function authenticate($username='', $password='', $method = gCOSI_AUTH_METHOD_BUILT_IN) 
	{
		$authentication_response = array();
		
		// Grab the posted values
		if ($this->input->post('username'))	$username = $this->input->post('username',true);
		if ($this->input->post('password')) $password = $this->input->post('password',true);
		if ($this->input->post('method')) $method = $this->input->post('method',true);
		
		// Load the user model and authenticate according to the details received
		$this->load->model('User_model');
		$authentication_response['authentication'] = $this->User_model->authenticate($username, $password, strtoupper($method));

		if ($authentication_response['authentication']['result'] == 1)
		{			
			// Fetch the menu item array to match these permitted activities
			$this->load->model('Menu_model');
			$authentication_response['menu'] = $this->Menu_model->fetchMenu($authentication_response['authentication']['activities']);
			
			// Fetch an array containing messages from the relevant index and include the count of unread messages
			$this->load->model('Message_model');
			$authentication_response['messages'] = array(	'unreadmessagecount'=> $this->Message_model->fetchUnreadCount($authentication_response['authentication']['role']),
															'messageitems'=> $this->Message_model->fetchMessages($authentication_response['authentication']['role']));
			
		}
		
		// Output the authentication response as JSON
		json_output($authentication_response);
	}
	
	
	public function fetchMenu($role='PUBLIC')
	{
		// Grab posted values (if any)
		if ($this->input->post('role'))	$role = $this->input->post('role',true);
		
		$menu_items = array();
		
		// Get the permitted activities for this role
		$this->load->model('User_model');
		$permitted_roles = $this->User_model->getRolesAndActivitiesByRoleID($role);
		
		// Fetch the menu item array to match these permitted activities
		$this->load->model('Menu_model');
		$menu_items['menuitems'] = $this->Menu_model->fetchMenu($permitted_roles['activities']);
		
		// Output the menu item structure as JSON
		json_output($menu_items);
	}
	
	public function fetchMessages($role, $start_index, $count)
	{
		// Fetch an array containing messages from the relevant index and include the count of unread messages
		$this->load->model('Message_model');
		$messages = array(		'unreadmessagecount'=> $this->Message_model->fetchUnreadCount($role),
								'messages'=> $this->Message_model->fetchMessages($role, $start_index, $count));
		
		// Output the message structure as JSON
		json_output($messages);
	}
	
	
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */