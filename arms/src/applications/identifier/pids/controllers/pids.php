<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  PIDs primary controller
 *  @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Pids extends MX_Controller {

	/**
	 * Default function for pids, list all pids
	 * @return view 
	 */
	function index(){
		$data['title'] = 'My Identifiers';
		$data['scripts'] = array('pids');
		$data['js_lib'] = array('core', 'dataTables');

		$this->load->model('cosi_authentication', 'cosi');
		// var_dump($this->user->identifier());
		// var_dump($this->cosi->getRolesAndActivitiesByRoleID($this->user->localIdentifier()));

		$this->load->view('pids_index', $data);
	}

	function list_trusted_clients(){
		$this->load->model('_pids', 'pids');
		$trusted_clients = $this->pids->getTrustedClients();
		echo json_encode($trusted_clients);
	}

	/**
	 * list all pids web service for the pids dashboard
	 * @return json 
	 */
	function list_pids(){
		$this->load->model('_pids', 'pids');
		$this->load->model('cosi_authentication', 'cosi');
	 	//var_dump($this->user->identifier());
		//var_dump($this->cosi->getRolesAndActivitiesByRoleID($this->user->localIdentifier()));
		//$ownerHandle = $this->pids->getOwnerHandle($this->user->localIdentifier(), 'ldaps://ldap.anu.edu.au::http://services.ands.org.au/home/orca/user/');
		$ownerHandle = $this->pids->getOwnerHandle('wron-repository','csiro.au');
		if($ownerHandle)
		$pids = $this->pids->getHandles($ownerHandle);
		echo json_encode($pids);
	}

	function get_pids_details(){
		$this->load->model('_pids', 'pids');
		$this->load->model('cosi_authentication', 'cosi');
	 	//var_dump($this->user->identifier());
		//var_dump($this->cosi->getRolesAndActivitiesByRoleID($this->user->localIdentifier()));
		//$ownerHandle = $this->pids->getOwnerHandle($this->user->localIdentifier(), 'ldaps://ldap.anu.edu.au::http://services.ands.org.au/home/orca/user/');
		$handleArray = array('102.100.100/55','102.100.100/100','102.100.100/5356','102.100.100/5686');
		$pidsDetails = $this->pids->getHandlesDetails($handleArray);
		echo json_encode($pidsDetails);
	}

	function updateBy

}
	