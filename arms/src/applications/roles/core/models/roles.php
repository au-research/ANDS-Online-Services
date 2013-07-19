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

class Roles extends CI_Model {

	private $cosi_db = null;
	
    function __construct()
    {
        parent::__construct();
		$this->cosi_db = $this->load->database('cosi', TRUE);
    }

    function all_roles(){
        $result = $this->cosi_db->get("dba.tbl_roles");
        return $result->result_array();
    }

    function list_roles($role_type_id){
        if($role_type_id){
            $result = $this->cosi_db->get_where("dba.tbl_roles",    
                                                    array(
                                                        "role_type_id"=>$role_type_id                                                        
                                                    ));
        }else{
            $result = $this->cosi_db->get("dba.tbl_roles");
        }
        return $result->result();
    }

    function get_role($role_id){
        $result = $this->cosi_db->get_where("dba.tbl_roles",    
                                                    array(
                                                        "role_id"=>$role_id
                                                    ));
        foreach($result->result() as $r){
            return $r;
        }
    }

    function list_childs($parent_role_id){
        $result = $this->cosi_db->get_where("dba.tbl_role_relations",    
                                                    array(
                                                        "parent_role_id"=>$parent_role_id
                                                    ));
        return $result->result();
    }
}