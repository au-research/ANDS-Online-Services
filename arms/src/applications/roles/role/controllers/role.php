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
class Role extends MX_Controller {
	
	public function index(){
		// var_dump($this->user->functions());
		$data['title'] = 'List Roles';
		$data['scripts'] = array('roles');
		$data['js_lib'] = array('core', 'dataTables');
		$this->load->view('roles_index', $data);
	}

	public function view($role_id){
		
		$data['role'] = $this->roles->get_role(rawurldecode($role_id));

		$this->load->model('cosi_authentication', 'cosi');
		$data['childs'] = $this->roles->list_childs($role_id); //only get explicit
		$data['recursiveRoles'] = $this->cosi->getRolesAndActivitiesByRoleID($role_id);

		$allFunctionalRoles = array();
		$allOrgRoles = array();
		foreach($this->roles->list_roles('ROLE_FUNCTIONAL') as $f){
			array_push($allFunctionalRoles, $f->role_id);
		}
		foreach($this->roles->list_roles('ROLE_ORGANISATIONAL') as $o){
			array_push($allOrgRoles, $o->role_id);
		}
		$data['missingFunctionalRoles'] = array_diff($allFunctionalRoles, $data['recursiveRoles']['functional_roles']);
		$data['missingOrgRoles'] = array_diff($allOrgRoles, $data['recursiveRoles']['organisational_roles']);

		if(trim($data['role']->role_type_id)=='ROLE_ORGANISATIONAL' || trim($data['role']->role_type_id)=='ROLE_FUNCTIONAL'){
			$data['users'] = $this->roles->descendants(rawurldecode($role_id));
		}

		$data['title'] = 'View Role - '.$data['role']->name;
		$data['scripts'] = array('role_view');
		$data['js_lib'] = array('core');
		$this->load->view('role_view', $data);
	}

	public function add(){
		if($this->input->get('posted')){
			$post = $this->input->post();
			if(trim($post['authentication_service_id'])=='') unset($post['authentication_service_id']);
			$this->roles->add_role($post);
			$this->index();
		}else{
			$data['title'] = 'Add New Role';
			$data['js_lib'] = array('core');
			$this->load->view('role_add', $data);
		}
	}

	public function edit($role_id){
		$role_id = rawurldecode($role_id);
		if($this->input->get('posted')){
			$post = $this->input->post();
			if(!isset($post['enabled'])) $post['enabled']='f';
			$this->roles->edit_role($role_id, $post);
		}
		$data['role'] = $this->roles->get_role($role_id);
		$data['title'] = 'Edit - '.$data['role']->name;
		$data['js_lib'] = array('core');
		$this->load->view('role_edit', $data);
	}

	public function delete(){
		$this->roles->delete_role($this->input->post('role_id'));
	}

	public function add_relation(){
		$this->roles->add_relation($this->input->post('parent'), $this->input->post('child'));
	}

	public function remove_relation(){
		$this->roles->remove_relation($this->input->post('parent'), $this->input->post('child'));
	}

	public function test(){
		// $this->roles->add_relation('REGISTRY_SUPERUSER', 'u4297901');
		// echo json_encode($this->roles->descendants('AuScope'));
		
		// $this->roles->migrate_from_cosi();
		// echo 'Done';

		// $this->db->query('use dbs_registry');
	}

	public function migrate_from_cosi(){
		$this->roles->migrate_from_cosi();
		echo 'Done';
	}



	public function all_roles(){
		echo json_encode($this->roles->all_roles());
	}

	public function list_roles($role_type_id = false){
		if(!$role_type_id) $role_type_id = false;
		$roles = array();
		foreach($this->roles->list_roles($role_type_id) as $role){
			$role = array(
				'role_id' => rawurlencode($role->role_id),
				'name' => $role->name,
				'type' => readable($role->role_type_id),
				'enabled' => readable($role->enabled),
				'last_modified' => $role->modified_when,
				'auth_service' => $role->authentication_service_id
			);
			array_push($roles, $role);
		}
		echo json_encode($roles);
	}


	public function __construct(){
		parent::__construct();
		acl_enforce('REGISTRY_SUPERUSER');
		$this->load->model('roles');
	}
}