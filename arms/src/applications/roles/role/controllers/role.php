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
		$data['roles'] = $this->cosi->getRolesAndActivitiesByRoleID($role_id,false); //only get explicit
		$data['recursiveRoles'] = $this->cosi->getRolesAndActivitiesByRoleID($role_id);

		$allFunctionalRoles = array();
		foreach($this->roles->list_roles('ROLE_FUNCTIONAL') as $f){
			array_push($allFunctionalRoles, $f->role_id);
		}
		$data['missingFunctionalRoles'] = array_diff($allFunctionalRoles, $data['recursiveRoles']['functional_roles']);

		$data['title'] = 'View Role - '.$data['role']->name;
		$data['js_lib'] = array('core');
		$this->load->view('role_view', $data);
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
				'type' => $role->role_type_id,
				'enabled' => $role->enabled,
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