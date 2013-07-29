<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Role Controller
 * 
 * Base controller for role management for the Registry
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * 
 */
class Role extends MX_Controller {
	
	/**
	 * default controller, returns the role management dashboard
	 * @return view 
	 */
	public function index(){
		// var_dump($this->user->functions());
		$data['title'] = 'List Roles';
		$data['scripts'] = array('roles');
		$data['js_lib'] = array('core', 'dataTables');
		$this->load->view('roles_index', $data);
	}

	public function test(){
		echo json_encode($this->roles->descendants('AusStage'));
	}

	/**
	 * view controller, returns the view of a role with all of its roles, org and func roles included
	 * @param  string $role_id role_id is now in the url, so needs to be decoded correctly for usage
	 * @return view          
	 */
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

	/**
	 * Controller to handle adding new roles
	 * If a new role is posted, go back to the dashboard else return the default view
	 * @return view
	 */
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

	/**
	 * Controller to handle updating roles
	 * Of an edited role is posted, do the update
	 * @param  string $role_id encoded role_id
	 * @return view
	 */
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

	/**
	 * Delete a Role
	 * Invoke the delete role model function
	 * @return true
	 */
	public function delete(){
		$this->roles->delete_role($this->input->post('role_id'));
	}

	/**
	 * Adding relation
	 * Invoke the add relation model function
	 * @param parent_id
	 * @param child_id
	 * @return true
	 */
	public function add_relation(){
		$this->roles->add_relation($this->input->post('parent'), $this->input->post('child'));
	}

	/**
	 * Removing a relation
	 * @param parent_id
	 * @param child_id
	 * @return true
	 */
	public function remove_relation(){
		$this->roles->remove_relation($this->input->post('parent'), $this->input->post('child'));
	}

	/**
	 * Provide a way to migrate from old cosi
	 * @return true
	 */
	public function migrate_from_cosi(){
		$this->roles->migrate_from_cosi();
		echo 'Done';
	}

	/**
	 * Return All Roles in JSON form for Web applications
	 * @return json
	 */
	public function all_roles(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		echo json_encode($this->roles->all_roles());
	}

	/**
	 * List All Roles that fit in a role_type_id in JSON form
	 * @param  string $role_type_id
	 * @return json                
	 */
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

	/**
	 * Construct the controller
	 * Default to REGISTRY_SUPERUSER access
	 */
	public function __construct(){
		parent::__construct();
		// acl_enforce('REGISTRY_SUPERUSER');
		$this->load->model('roles');
	}
}