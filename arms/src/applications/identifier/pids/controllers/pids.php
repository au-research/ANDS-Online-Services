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

	function mint()
	{
		$this->load->model('_pids', 'pids');
		$responseArray = array();
		$serviceName = "mint";
		$parameters  = "type=".'DESC';
		$parameters .= "&value=".'HELLO%20PIDS';
		$response = $this->pids->pidsRequest($serviceName, $parameters);
		if( $response )
		{
			if( $this->pids->pidsGetResponseType($response) == 'SUCCESS' )
			{
				$responseArray['handle'] = $this->pids->pidsGetHandleValue($response);
			}
			else
			{
				$responseArray['error'] = $this->pids->pidsGetUserMessage($response);
			}
		}
		else
		{	
			$responseArray['error'] = 'There was an error communicating with the pids service.';
		}

		echo json_encode($responseArray);
	}

	/**
	 * list all pids web service for the pids dashboard
	 * @return json 
	 */
	function list_pids(){
		$this->load->model('_pids', 'pids');
		$this->load->model('cosi_authentication', 'cosi');
		$handles = array();
		$pidsDetails = array();
		$response = array();
		

		$params = $this->input->post('params');

		$offset = (isset($params['offset'])? $params['offset']: 0);
		$limit = (isset($params['limit'])? $params['limit']: 10);
		$searchText = (isset($params['searchText '])? $params['searchText ']: null);
		$authDomain = (isset($params['authDomain'])? $params['authDomain']: $this->user->authDomain());
		$identifier = (isset($params['identifier'])? $params['identifier']: $this->user->localIdentifier());

		$ownerHandle = $this->pids->getOwnerHandle($identifier,$authDomain);
		if($ownerHandle)
		{
			$handles = $this->pids->getHandles($ownerHandle, $searchText);
			$response['result_count'] = sizeof($handles);
			$response['owner_handle'] = $ownerHandle;
			$response['pids'] = $this->pids->getHandlesDetails(array_slice($handles, $offset, $limit));
		}

		echo json_encode($response);
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

	function get_handler($handler)
	{
		$this->load->model('_pids', 'pids');
		$serviceName = "getHandle";
		$parameters = "handle=".urlencode($handler);
		$response = $this->pids->pidsRequest($serviceName, $parameters);
		echo $response;

	}
	//function updateBy

}
	