<?php
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

class User_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Return an array containing the success/failure of authentication
     * using the parameters below. If a valid combination is supplied, also 
     * supplied a list of activities, functional and organisational roles
     * which are associated with that user as well as other details specific to the
     * authentication method (such as LDAP information, name, token, etc).  
     * 
     * @param $username Username to authenticate
     * @param $password Plaintext password to use to authenticate
     * @param $method Authentication method to use (built-in/ldap/shib...etc)
     */
    function authenticate($username, $password, $method)
    {
    	/*
    	 * Using the built-in account system
    	 */
    	if ($method === gCOSI_AUTH_METHOD_BUILT_IN)
		{
			if ($username == '')
			{
				return array(
					'result'=>0,
					'message'=>'Authentication Failed (0)'
				);
			}
				
			if ($password == '')
			{
				return array(
					'result'=>0,
					'message'=>'Authentication Failed (1)'
				);
			}
			
    		$result = $this->db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",
      													"authentication_service_id"=>gCOSI_AUTH_METHOD_BUILT_IN,	
    													"enabled"=>'t'
    												));
    												
    		if ($result->num_rows() > 0)
    		{
    			$valid_users = $this->db->get_where("dba.tbl_authentication_built_in",
    													array(
    														"role_id"=>$username,
    														"passphrase_sha1"=>sha1($password)	
    													));	
    			if ($valid_users->num_rows() > 0)
    			{
    				$user_results = $this->getRolesAndActivitiesByRoleID ($valid_users->row(1)->role_id);
    				
					return array(	
									'result'=>1,
    								'message'=>'Success',
									'role'=>$username,
					    			'name'=>$result->row(1)->name,
    								'last_login'=>$result->row(1)->last_login,
    								'activities'=>$user_results['activities'],
    								'organisational_roles'=>$user_results['organisational_roles'],
    								'functional_roles'=>$user_results['functional_roles']
    							);
    			}
	    		else
	    		{
	    			// Invalid password
					return array(
									'result'=>0,
									'message'=>'Authentication Failed (2)'
								);
	    		}
    		}
    		else
    		{
    			// No such user/disabled
				return array(
					'result'=>0,
					'message'=>'Authentication Failed (3)'
				);
    		}
    		
    	}
    	/*
    	 * LDAP Authentication Methods
    	 */
    	elseif ($method === gCOSI_AUTH_METHOD_LDAP)
		{
			$this->load->helper('authentication');
			if ($username == '')
			{
				return array(
					'result'=>0,
					'message'=>'Authentication Failed (0)'
				);
			}
				
			if ($password == '')
			{
				return array(
					'result'=>0,
					'message'=>'Authentication Failed (1)'
				);
			}
			
    		$result = $this->db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",
      													"authentication_service_id"=>gCOSI_AUTH_METHOD_LDAP,	
    													"enabled"=>'t'
    												));
    												
			if ($result->num_rows() > 0)
    		{
    			$LDAPAttributes = array();
    			$LDAPMessage = "";
    			$successful = authenticateWithLDAP($username, $password, $LDAPAttributes, $LDAPMessage);

    			
    			if (count($LDAPAttributes) > 0)
    			{
    				$user_results = $this->getRolesAndActivitiesByRoleID ($username);
    				
					return array(	
									'result'=>1,
    								'message'=>'Success',
									'role'=>$username,
					    			'name'=>(isset($LDAPAttributes['cn'][0]) ? $LDAPAttributes['cn'][0] : $result->row(1)->name), // implementation specific
    								'last_login'=>$result->row(1)->last_login,
    								'activities'=>$user_results['activities'],
    								'organisational_roles'=>$user_results['organisational_roles'],
    								'functional_roles'=>$user_results['functional_roles']
    							);
    			}
	    		else
	    		{
	    			// LDAP ERROR (Could not bind)
	    			// You may wish to debug by appending $LDAPMessage to this response
					return array(
									'result'=>0,
									'message'=>'Authentication Failed (4)'
								);
	    		}
    		}
    		else
    		{
    			// No such user/disabled
				return array(
					'result'=>0,
					'message'=>'Authentication Failed (5)'
				);
    		}
    		
    		
		}
		elseif ($method == gCOSI_AUTH_METHOD_SHIBBOLETH)
		{
			
			$result = $this->db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",
      													"authentication_service_id"=>gCOSI_AUTH_METHOD_SHIBBOLETH,	
    													"enabled"=>'t'
    												));
    												
			if ($result->num_rows() > 0)
    		{
    			
    			if ($password == $this->config->item('gCOSI_SHIBBOLETH_SHARED_KEY'))
    			{
    				$user_results = $this->getRolesAndActivitiesByRoleID ($username);
    				
					return array(	
									'result'=>1,
    								'message'=>'Success',
									'role'=>$username,
					    			'name'=>$result->row(1)->name, 
    								'last_login'=>$result->row(1)->last_login,
    								'activities'=>$user_results['activities'],
    								'organisational_roles'=>$user_results['organisational_roles'],
    								'functional_roles'=>$user_results['functional_roles']
    							);
    			}
	    		else
	    		{
	    			// Invalid shared key
					return array(
						'result'=>0,
						'message'=>'Authentication Failed (6)'
					);
	    		}
    		}
    		else
    		{
    			// No such user/disabled
				return array(
					'result'=>0,
					'message'=>'This Shibboleth User does not exist in our database (6)'
				);
    		}
    		
		}
		/* No such authentication method */
    	else
    	{
    		// Invalid authentication method
    		return array(
					'result'=>0,
					'message'=>'Authentication Failed (-1)'
			);
    	}
    }
    
    
    
    
    public function getRolesAndActivitiesByRoleID ($role_id)
    {
    	$ret = array('organisational_roles'=>array(), 'functional_roles'=>array(), 'activities'=>array());

    	$roles = $this->getChildRoles($role_id);
    	foreach ($roles AS $role)
   		{
    		if (trim($role['role_type_id']) == gCOSI_AUTH_ROLE_ORGANISATIONAL)
    		{
    			$ret['organisational_roles'][] = $role['role_id'];
    		}
    		else if (trim($role['role_type_id']) == gCOSI_AUTH_ROLE_FUNCTIONAL)
    		{
    			$ret['functional_roles'][] = $role['role_id'];
    			$ret['activities'] = array_merge($ret['activities'], $this->getChildActivities($role['role_id']));
    		}
    					
    	}
    	
    	return $ret;
    				
    }
    
    
    
    private function getChildRoles($role_id)
    {
    	$roles = array();
    	
    	$related_roles = $this->db->query("SELECT rr.parent_role_id, r.role_type_id
 											FROM dba.tbl_role_relations rr	
 											JOIN dba.tbl_roles r ON r.role_id = rr.parent_role_id								
 											WHERE rr.child_role_id = '" . $role_id . "'
 											  AND r.enabled='t'");
    	
    	foreach($related_roles->result() AS $row)
    	{
    		$roles[] = array("role_id" => $row->parent_role_id, "role_type_id" => $row->role_type_id);
    		$roles = array_merge($roles, $this->getChildRoles($row->parent_role_id));
    	}
    	
    	return $roles;
    }
    
    
    
    private function getChildActivities($role_id)
    {
    	$activities = array();
    	
    	$results = $this->db->get_where("dba.tbl_role_activities", array("role_id"=>$role_id));
    	foreach($results->result() AS $row)
    	{
    		$activities[] = $row->activity_id;
    	}
    	
    	return $activities;
    }
    

}