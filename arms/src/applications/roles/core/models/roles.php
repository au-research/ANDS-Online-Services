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

    

    function add_relation($parent_role_id, $child_role_id){
        $result = $this->cosi_db->insert('dba.tbl_role_relations', 
            array(
                'parent_role_id'=>$parent_role_id,
                'child_role_id'=>$child_role_id,
                'created_who'=>$this->user->identifier()
            )
        );
    }

    function remove_relation($parent_role_id, $child_role_id){
        $result = $this->cosi_db->delete('dba.tbl_role_relations',
            array(
                'parent_role_id'=>$parent_role_id,
                'child_role_id'=>$child_role_id
            )
        );
    }

    function list_childs($role_id){
        $res = array();
        // $role = $this->get_role($role_id);

        
        $result = $this->cosi_db->query("SELECT rr.parent_role_id, r.role_type_id, r.name, r.role_id
                                            FROM dba.tbl_role_relations rr  
                                            JOIN dba.tbl_roles r ON r.role_id = rr.parent_role_id                               
                                            WHERE rr.child_role_id = '" . $role_id . "'
                                              AND r.enabled='t'");
        

        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                $res[] = $r;
                $childs = $this->list_childs($r->parent_role_id);
                if(sizeof($childs) > 0){
                    $r->childs = $childs;
                }else{
                    $r->childs = false;
                }
            }
        }
        return $res;
    }

    function descendants($role_id){
        $res = array();
        $result = $this->cosi_db->query("SELECT rr.parent_role_id, r.role_type_id, r.name, r.role_id
                                            FROM dba.tbl_role_relations rr  
                                            JOIN dba.tbl_roles r ON r.role_id = rr.child_role_id                               
                                            WHERE rr.parent_role_id = '" . $role_id . "'
                                              AND r.enabled='t'");
        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                $res[] = $r;
                $childs = $this->descendants($r->role_id);
                if(sizeof($childs) > 0){
                    $r->childs = $childs;
                }else $r->childs = false;
            }
        }
        return $res;
        return $result->result();
    }

    function add_role($post){
        $result = $this->cosi_db->insert('dba.tbl_roles', 
            array(
                'role_id'=>$post['role_id'],
                'name'=>$post['name'],
                'role_type_id'=>$post['role_type_id'],
                'enabled'=> ($post['enabled']=='on' ? 't' : 'f'),
                'authentication_service_id'=> trim($post['authentication_service_id']),
                'created_who'=>$this->user->identifier()
            )
        );
    }

    function edit_role($role_id, $post){
        $this->cosi_db->where('role_id', $role_id);
        $this->cosi_db->update('dba.tbl_roles', 
            array(
                'name'=> $post['name'],
                'enabled'=>($post['enabled']=='on' ? 't' : 'f')
            )
        ); 
    }

    function delete_role($role_id){
        $this->cosi_db->delete('dba.tbl_role_relations', array('parent_role_id' => $role_id));
        $this->cosi_db->delete('dba.tbl_role_relations', array('child_role_id' => $role_id));
        $this->cosi_db->delete('dba.tbl_roles', array('role_id' => $role_id));
    }
}