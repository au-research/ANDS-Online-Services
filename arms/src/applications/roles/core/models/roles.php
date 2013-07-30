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

/**
 * Base Model for Roles Management
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Roles extends CI_Model {

	private $cosi_db = null;
	
    function __construct(){
        parent::__construct();
		$this->cosi_db = $this->load->database('roles', TRUE);
    }

    /**
     * function to return all of the available roles, enabled or not
     * @return array_object
     */
    function all_roles(){
        $result = $this->cosi_db->get("roles");
        return $result->result();
    }

    /**
     * returns a list of role based on the role_type_id
     * @param  string $role_type_id if not provided, return all roles
     * @return array_object               
     */
    function list_roles($role_type_id){
        if($role_type_id){
            $result = $this->cosi_db->get_where("roles",    
                                                    array(
                                                        "role_type_id"=>$role_type_id                                                        
                                                    ));
        }else{
            $result = $this->cosi_db->get("roles");
        }
        return $result->result();
    }

    /**
     * retrieve a single role
     * @param  string $role_id the role_id identifier
     * @return object          
     */
    function get_role($role_id){
        $result = $this->cosi_db->get_where("roles",    
                                                    array(
                                                        "role_id"=>$role_id
                                                    ));
        foreach($result->result() as $r){
            return $r;
        }
    }

    /**
     * add a relation between roles, this adds an entry into the role_relations table
     * @param string $parent_role_id    
     * @param string $child_role_id 
     */
    function add_relation($parent_role_id, $child_role_id){
        $result = $this->cosi_db->insert('role_relations', 
            array(
                'parent_role_id'=>$parent_role_id,
                'child_role_id'=>$child_role_id,
                'created_who'=>$this->user->localIdentifier()
            )
        );
        return $result;
    }

    /**
     * this function remove a relation between 2 roles, explicit parent and child must be provided
     * @param  string $parent_role_id
     * @param  string $child_role_id 
     * @return result                
     */
    function remove_relation($parent_role_id, $child_role_id){
        $result = $this->cosi_db->delete('role_relations',
            array(
                'parent_role_id'=>$parent_role_id,
                'child_role_id'=>$child_role_id
            )
        );
        return $result;
    }

    /**
     * recursive function that goes through and collect all of the (parents) of a role
     * @param  string $role_id
     * @return array_object if an object has a child, object->childs will be a list of the child objects
     */
    function list_childs($role_id){
        $res = array();
        // $role = $this->get_role($role_id);
        
        $result = $this->cosi_db
                ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.child_role_id', $role_id)
                ->where('enabled', 't')
                ->where('role_relations.parent_role_id !=', $role_id)
                ->get();

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

    /**
     * basically reverse of the list_childs function, search for all (childs) of a role
     * @param  string $role_id
     * @return array_object
     */
    function descendants($role_id){
        $res = array();
    
        $result = $this->cosi_db
                    ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                    ->from('role_relations')
                    ->join('roles', 'roles.role_id = role_relations.child_role_id')
                    ->where('role_relations.parent_role_id', $role_id)
                    ->where('enabled', 't')
                    ->where('role_relations.child_role_id !=', $role_id)
                    ->get();

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
    }

    /**
     * Register a Role in the roles table, if the role has authentication built in, then create another entry in the relevant table
     * @param [type] $post [description]
     */
    function add_role($post){
        $this->cosi_db->insert('roles', 
            array(
                'role_id'=>$post['role_id'],
                'name'=>$post['name'],
                'role_type_id'=>$post['role_type_id'],
                'enabled'=> ($post['enabled']=='on' ? DB_TRUE : DB_FALSE),
                'authentication_service_id'=> trim($post['authentication_service_id']),
                'created_who'=>$this->user->localIdentifier()
            )
        );
        if($post['authentication_service_id']=='AUTHENTICATION_BUILT_IN'){
            $this->cosi_db->insert('authentication_built_in',
                array(
                    'role_id'=>$post['role_id'],
                    'passphrase_sha1'=>sha1('abc123'),
                    'created_who'=>$this->user->localIdentifier(),
                    'modified_who'=>$this->user->localIdentifier()
                )
            );
        }
    }

    /**
     * Update a role name and enable status
     * @param  string $role_id
     * @param  array $post    
     * @return true          
     */
    function edit_role($role_id, $post){
        $this->cosi_db->where('role_id', $role_id);
        $this->cosi_db->update('roles', 
            array(
                'name'=> $post['name'],
                'enabled'=>($post['enabled']=='on' ? DB_TRUE : DB_FALSE)
            )
        ); 
    }

    /**
     * Remove a role and all relations associated with it
     * @param  string $role_id
     * @return true
     */
    function delete_role($role_id){
        $this->cosi_db->delete('role_relations', array('parent_role_id' => $role_id));
        $this->cosi_db->delete('role_relations', array('child_role_id' => $role_id));
        $this->cosi_db->delete('roles', array('role_id' => $role_id));
    }

    /**
     * Migrate from old PGSQL COSI database to the new one, recommended to run before the usage of roles management
     * @return true
     */
    function migrate_from_cosi(){
        $this->load->dbforge();

        $this->old_cosi_db = $this->load->database('cosi', true);
        $this->new_cosi_db = $this->load->database('roles', true);
        $this->dbforge->set_database($this->new_cosi_db);

        //Removes the existing table
        $this->dbforge->drop_table('roles');
        $this->dbforge->drop_table('role_relations');
        $this->dbforge->drop_table('authentication_built_in');

        //roles table schema
        $fields = array(
            'role_id' => array(
                'type'=>'VARCHAR','constraint'=> 255
            ),
            'role_type_id'=>array(
                'type'=>'VARCHAR','constraint'=> 20
            ),
            'name'=>array(
                'type'=>'VARCHAR','constraint'=> 255
            ),
            'authentication_service_id'=>array(
                'type'=>'VARCHAR','constraint'=> 32
            ),
            'enabled'=>array(
                'type'=>'VARCHAR', 'constraint'=>1, 'default'=>'t'
            ),
            'created_when'=>array(
                'type'=>'timestamp'
            ),
            'created_who'=>array(
                'type'=>'VARCHAR', 'constraint'=>255,'default'=>'SYSTEM'
            ),
            'modified_who'=>array(
                'type'=>'VARCHAR', 'constraint'=>255,'default'=>'SYSTEM'
            ),
            'modified_when'=>array(
                'type'=>'timestamp'
            ),
            'last_login'=>array(
                'type'=>'timestamp'
            ),
        );
        $this->dbforge->add_field('id');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('role_id', true);
        $this->dbforge->create_table('roles', true);

        $all_roles = $this->old_cosi_db->get('dba.tbl_roles');
        foreach($all_roles->result() as $r){
            $this->db->insert('roles',
                array(
                    'role_id'=>$r->role_id,
                    'role_type_id'=>$r->role_type_id,
                    'name'=>$r->name,
                    'authentication_service_id'=>($r->authentication_service_id ? trim($r->authentication_service_id) : ''),
                    'enabled'=>($r->enabled==DB_TRUE ? DB_TRUE: DB_FALSE),
                    'created_when'=>$r->created_when,
                    'created_who'=>$r->created_who,
                    'modified_who'=>$r->modified_who,
                    'modified_when'=>$r->modified_when,
                    'last_login'=>$r->last_login
                )
            );
        }

        //role relations
        $fields = array(
            'parent_role_id'=>array(
                'type'=>'VARCHAR','constraint'=>255
            ),
            'child_role_id'=>array(
                'type'=>'VARCHAR','constraint'=>255
            ),
            'created_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            ),
            'created_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            ),
        );
        $this->dbforge->add_field('id');
        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('role_relations', true);
        $all_relations = $this->old_cosi_db->get('dba.tbl_role_relations');
        foreach($all_relations->result() as $r){
            $this->db->insert('role_relations',
                array(
                    'parent_role_id'=>$r->parent_role_id,
                    'child_role_id'=>$r->child_role_id,
                    'created_who'=>$r->created_who,
                    'created_when'=>$r->created_when,
                    'modified_when'=>$r->modified_when,
                    'modified_who'=>$r->modified_who
                )
            );
        }

        //authentication_built_in
        $fields = array(
            'role_id'=>array(
                'type'=>'VARCHAR', 'constraint'=>255
            ),
            'passphrase_sha1'=>array(
                'type'=>'VARCHAR', 'constraint'=>40
            ),
            'created_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            ),
            'created_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            )
        );
        $this->dbforge->add_field('id');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('role_id', true);
        $this->dbforge->create_table('authentication_built_in', true);
        $all_built_in = $this->old_cosi_db->get('dba.tbl_authentication_built_in');
        foreach($all_built_in->result() as $r){
            $this->db->insert('authentication_built_in', 
                array(
                    'role_id'=>$r->role_id,
                    'passphrase_sha1'=>$r->passphrase_sha1,
                    'created_who'=>$r->created_who,
                    'created_when'=>$r->created_when,
                    'modified_when'=>$r->modified_when,
                    'modified_who'=>$r->modified_who
                )
            );
        }

    }
}