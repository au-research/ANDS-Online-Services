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

********************************************************************************
$Date: 2009-08-11 12:57:09 +1000 (Tue, 11 Aug 2009) $
$Revision: 32 $
*******************************************************************************/

class Cosi_authentication extends CI_Model {

	private $cosi_db = null;
	
    function __construct()
    {
        parent::__construct();
		$this->cosi_db = $this->load->database('cosi', TRUE);
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
    function authenticate($username, $password, $method=gCOSI_AUTH_METHOD_BUILT_IN)
    {
    	/*
    	 * Using the built-in account system
    	 */
    	$result = $this->cosi_db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",	
    													"enabled"=>'t'
    												));
		if($result->num_rows() > 0){
			$method = trim($result->row(1)->authentication_service_id);
		}
    												
    	//return array('result'=>0,'message'=>json_encode($result));												
    	if ($method === 'AUTHENTICATION_BUILT_IN')
		{
			if ($username == '')
			{
				throw new Exception('Authentication Failed (0)');
			}
				
			if ($password == '')
			{
				throw new Exception('Authentication Failed (1)');
			}
			
    		$result = $this->cosi_db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",
      													"authentication_service_id"=>gCOSI_AUTH_METHOD_BUILT_IN,	
    													"enabled"=>'t'
    												));
    												
    		if ($result->num_rows() > 0)
    		{
    			$valid_users = $this->cosi_db->get_where("dba.tbl_authentication_built_in",
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
									'user_identifier'=>$username,
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
					throw new Exception('Authentication Failed (2)');
	    		}
    		}
    		
		}
		else if ($method === 'AUTHENTICATION_SHIBBOLETH')
		{
			
			$result = $this->cosi_db->get_where("dba.tbl_roles",	
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
									'user_identifier'=>$username,
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
					throw new Exception('Authentication Failed (11)');
	    		}
    		}
    		else
    		{
    			// No such user/disabled
				throw new Exception('Authentication Failed (12)');
    		}
    		
		}
		else if($method === 'AUTHENTICATION_LDAP')
		{
			/*
			 * Try using the LDAP Authentication Methods
			 */
			
			$this->load->helper('ldap');
			if ($username == '')
			{
				throw new Exception('Authentication Failed (00)');
			}
				
			if ($password == '')
			{
				throw new Exception('Authentication Failed (01)');
			}
			
			$result = $this->cosi_db->get_where("dba.tbl_roles",	
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
				$successful = $this->authenticateWithLDAP($username, $password, $LDAPAttributes, $LDAPMessage);
				if (count($LDAPAttributes) > 0)
				{
					$user_results = $this->getRolesAndActivitiesByRoleID ($username);
					
					return array(	
									'result'=>1,
									'message'=>'Success',
									'user_identifier'=>$username,
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
					throw new Exception('Authentication Failed (02)');
	    		}
			}
			else
			{
				// No such user/disabled
				throw new Exception('Authentication Failed (03)');
			}
		}
    	else
		{
		return array('result'=>0,'message'=>json_encode($result));	
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

    public function getRolesInAffiliate($affiliate){
        $roles = array();
        $user_role = $this->cosi_db->query("SELECT child_role_id FROM dba.tbl_role_relations WHERE parent_role_id = '".$affiliate."'");
        foreach($user_role->result() as $r){
            $roles[] = $r->child_role_id;
        }
        return $roles;
    }

    public function getAllOrganisationalRoles(){
        $roles = array();
        $org_roles = $this->cosi_db->query("SELECT * FROM dba.tbl_roles WHERE role_type_id='ROLE_ORGANISATIONAL' AND enabled='t' ORDER BY name ASC");
        foreach($org_roles->result() as $r){
            $roles[] = array("role_id"=>$r->role_id, "name"=>$r->name);
        }
        return $roles;
    }
    
    public function registerAffiliation($thisRole, $orgRole){
        $insertQry = 'INSERT INTO dba.tbl_role_relations (parent_role_id,child_role_id,created_who) VALUES (\''.$orgRole.'\',\''.$thisRole.'\',\''.$thisRole.'\');';
        $query = $this->cosi_db->query($insertQry);
        if($query){
            return true;
        }else{
            return false;
        }
    }

    public function createOrganisationalRole($orgRole, $thisRole){
        $insertQry = 'INSERT INTO dba.tbl_roles (role_id,role_type_id,name,enabled,created_who) VALUES (\''.$orgRole.'\',\'ROLE_ORGANISATIONAL\',\''.$orgRole.'\',\'TRUE\',\''.$thisRole.'\');';
        $query = $this->cosi_db->query($insertQry);
        if($query){
            return true;
        }else return false;
    }
    
    
    private function getChildRoles($role_id)
    {
    	$roles = array();
    	
    	$related_roles = $this->cosi_db->query("SELECT rr.parent_role_id, r.role_type_id
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
    
	private function authenticateWithLDAP($role_id, $passphrase, &$LDAPAttributes, &$userMessage)
	{
$eLDAPHost = "ldap://ldap.anu.edu.au";
$eLDAPPort = 389; //636 | 389
// The resource distinguished name.
// The string @@ROLE_ID@@ will be replace with the user role_id, and escaped
// for LDAP reserved characters before the bind is attempted.
$eLDAPBaseDN = "ou=People, o=anu.edu.au";
$eLDAPuid = "uid=@@ROLE_ID@@";
$eLDAPDN = "$eLDAPuid, $eLDAPBaseDN";

		
		$validCredentials = false;
		
		if( $eLDAPBaseDN && $eLDAPuid )
		{
			$ldapDN = str_replace("@@ROLE_ID@@", escLDAPChars($role_id), $eLDAPDN);
			$ldapconn = ldap_connect($eLDAPHost, $eLDAPPort);
		
			if( $ldapconn && $passphrase != '' )
			{
				$ldapbind = ldap_bind($ldapconn, $ldapDN, $passphrase);
				if( $ldapbind )
				{
					$validCredentials = true;
				
					// Put this user's LDAP attributes into session to make them available
					// for use with authorisations and stuff.
					$ldapUserDN = str_replace("@@ROLE_ID@@", escLDAPChars($role_id), $eLDAPuid);
					$searchResult = ldap_search($ldapconn, $eLDAPBaseDN, $ldapUserDN);
					if( $searchResult && ldap_count_entries($ldapconn, $searchResult) === 1 )
					{
						$entry = ldap_first_entry($ldapconn, $searchResult);
						$LDAPAttributes = ldap_get_attributes($ldapconn, $entry);
					}
				
					ldap_unbind($ldapconn);
				}
				else
				{
					$ldapErrorNumber = ldap_errno($ldapconn);
					if( $ldapErrorNumber === 49 ) // 0x31 = 49 is the LDAP error number for invalid credentials.
					{
						$userMessage = "LOGIN FAILED\nInvalid user ID/password [31,49].\n";
					}
					else
					{
						$userMessage = "LOGIN FAILED\nAuthentication service error [32,$ldapErrorNumber].\n";
					}
					/* 
					LDAP error numbers have the same meaning across implementations, though the messages vary.
	 
					A list of implementation specific error messages can be obtained using:
					 	for ($i=-1; $i<100; $i++) {
					 		printf("Error $i: %s<br />\n", ldap_err2str($i));
					 	}
	
					Error numbers and messages are for troubleshooting, and should not be displayed to users.
	
					Example results:
						Error -1: Can't contact LDAP server
						Error 0: Success
						Error 1: Operations error
						Error 2: Protocol error
						Error 3: Time limit exceeded
						Error 4: Size limit exceeded
						Error 5: Compare False
						Error 6: Compare True
						Error 7: Authentication method not supported
						Error 8: Strong(er) authentication required
						Error 9: Partial results and referral received
						Error 10: Referral
						Error 11: Administrative limit exceeded
						Error 12: Critical extension is unavailable
						Error 13: Confidentiality required
						Error 14: SASL bind in progress
						Error 16: No such attribute
						Error 17: Undefined attribute type
						Error 18: Inappropriate matching
						Error 19: Constraint violation
						Error 20: Type or value exists
						Error 21: Invalid syntax
						Error 32: No such object
						Error 33: Alias problem
						Error 34: Invalid DN syntax
						Error 35: Entry is a leaf
						Error 36: Alias dereferencing problem
						Error 47: Proxy Authorization Failure
						Error 48: Inappropriate authentication
						Error 49: Invalid credentials
						Error 50: Insufficient access
						Error 51: Server is busy
						Error 52: Server is unavailable
						Error 53: Server is unwilling to perform
						Error 54: Loop detected
						Error 64: Naming violation
						Error 65: Object class violation
						Error 66: Operation not allowed on non-leaf
						Error 67: Operation not allowed on RDN
						Error 68: Already exists
						Error 69: Cannot modify object class
						Error 70: Results too large
						Error 71: Operation affects multiple DSAs
						Error 80: Internal (implementation specific) error
					 */
				}
			}
			else
			{
				$userMessage = "LOGIN FAILED\nAuthentication service error [30].\n";
			}
		}
		else
		{
			$userMessage = "LOGIN FAILED\nAuthentication service error [31].\n";
		}
		
		return $validCredentials;
	}
	    
    private function getChildActivities($role_id)
    {
    	$activities = array();
    	
    	$results = $this->cosi_db->get_where("dba.tbl_role_activities", array("role_id"=>$role_id));
    	foreach($results->result() AS $row)
    	{
    		$activities[] = $row->activity_id;
    	}
    	
    	return $activities;
    }
    

}