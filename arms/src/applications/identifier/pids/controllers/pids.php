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
		acl_enforce('PID_USER');
		$data['title'] = 'My Identifiers';
		$data['scripts'] = array('pids');
		$data['js_lib'] = array('core');

		$this->load->model('cosi_authentication', 'cosi');
		// var_dump($this->user->identifier());
		// var_dump($this->cosi->getRolesAndActivitiesByRoleID($this->user->localIdentifier()));

		$this->load->view('pids_index', $data);
	}

	public function view(){

		$this->load->library('debugbar');
		$this->debugbar->addMsg('viewing a pid');
		$this->debugbar->addMsg('SOME STUFFF');
		$data['debugbarRenderer']=$this->debugbar->debugbarRenderer();

		acl_enforce('PID_USER');
		$this->load->model('_pids', 'pids');
		$handle = $this->input->get('handle');
		if($handle){
			$handle = $this->pids->getHandlesDetails(array($handle));
			// echo json_encode($handle);
			$pid = array();
			foreach($handle as $h){
				$pid['handle'] = $h['handle'];
				if($h['type']=='DESC') {
					$pid['desc'] = $h['data'];
					$pid['desc_index'] = $h['idx'];
				}
				if($h['type']=='URL') {
					$pid['url'] = $h['data'];
					$pid['url_index'] = $h['idx'];
				}
			}
			$data['pid'] = $pid;
			$data['title'] = 'View Handle: '.$pid['handle'];
			$data['scripts'] = array('pid');
			$data['js_lib'] = array('core');
			$this->load->view('pid_view', $data);
		}else{
			$this->index();
		}
	}

	function list_trusted_clients(){
		$this->load->model('_pids', 'pids');
		$trusted_clients = $this->pids->getTrustedClients();
		echo json_encode($trusted_clients);
	}

	function add_trusted_client()
	{
		$this->load->model('_pids', 'pids');
		$ip = trim(urlencode($this->input->post('ip')));
		$desc = trim(urlencode($this->input->post('desc')));
		$appId = trim(urlencode($this->input->post('appId')));
		$response = $this->pids->addTrustedClient($ip, $desc, $appId);
		echo json_encode($response);
	}

	function mint(){
		$this->load->model('_pids', 'pids');

		$url = urlencode($this->input->post('url'));
		$desc = urlencode($this->input->post('desc'));

		if($url && $desc){
			//do desc -> update with url
			$response = $this->pids->pidsRequest('mint', 'type=DESC&value='.$desc);
			if($response){
				$responseArray['handle'] = $this->pids->pidsGetHandleValue($response);
				$updateResponse = $this->pids->pidsRequest('addValue', 'type=URL&value='.$url.'&handle='.$responseArray['handle']);
				if($updateResponse){
					$responseArray['result']='ok';
					$responseArray['message']='success';
				}else{
					$responseArray['result']='error';
					$responseArray['message'] = 'There was an error communicating with the pids service. update failed.';
				}
				echo json_encode($responseArray);
			}else{
				$responseArray['result']='error';
				$responseArray['message'] = 'There was an error communicating with the pids service.';
				echo json_encode($responseArray);
			}
		}else if($url){
			//do url only
			$response = $this->pids->pidsRequest('mint', 'type=URL&value='.$url);
			$this->handleResponse($response);
		}else if($desc){
			//do desc
			$response = $this->pids->pidsRequest('mint', 'type=DESC&value='.$desc);
			$this->handleResponse($response);
		}else{
			$responseArray['result']='error';
			$responseArray['message']='Either URL or DESC must be specified';
			echo json_encode($responseArray);
		}
	}

	function update(){
		$this->load->model('_pids', 'pids');
		$post = $this->input->post('jsonData');
		$handle = $post['handle'];
		$response = array();
		if(isset($post['url'])){
			if($post['url']!=''){
				if($post['url_index']!=''){
					$response['url'] = $this->pids->modify_value_by_index($handle, $post['url'], $post['url_index']);
				}else{
					$response['url'] = $this->pids->pidsRequest('addValue', 'type=URL&value='.urlencode($post['url']).'&handle='.urlencode($handle));
				}
			}else{
				$response['url'] = $this->pids->delete_value_by_index($handle, $post['url_index']);
			}
		}
		if(isset($post['desc'])){
			if($post['desc']!=''){
				if($post['desc_index']!=''){
					$response['desc'] = $this->pids->modify_value_by_index($handle, $post['desc'], $post['desc_index']);
				}else{
					$response['url'] = $this->pids->pidsRequest('addValue', 'type=DESC&value='.urlencode($post['desc']).'&handle='.urlencode($handle));
				}
			}else{
				$response['desc'] = $this->pids->delete_value_by_index($handle, $post['desc_index']);
			}
		}
		echo json_encode($response);
	}



	function handleResponse($response){
		$responseArray = array();
		if($response){
			if($this->pids->pidsGetResponseType($response) == 'SUCCESS'){
				$responseArray['handle'] = $this->pids->pidsGetHandleValue($response);
			}else{
				$responseArray['error'] = $this->pids->pidsGetUserMessage($response);
			}
		}else{	
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
		$searchText = (isset($params['searchText'])? $params['searchText']: null);
		$authDomain = (isset($params['authDomain'])? $params['authDomain']: $this->user->authDomain());
		$identifier = (isset($params['identifier'])? $params['identifier']: $this->user->localIdentifier());

		$ownerHandle = $this->pids->getOwnerHandle($identifier,$authDomain);

		if($ownerHandle)
		{
			$handles = $this->pids->getHandles($ownerHandle, $searchText);
			$response['result_count'] = sizeof($handles);
			$response['owner_handle'] = $ownerHandle;
			if($response['result_count'] > 0){
				$result = $this->pids->getHandlesDetails(array_slice($handles, $offset, $limit));
				foreach($result as $r)
				{
					if($r['type'] == 'DESC' || $r['type'] == 'URL')
					{
						// $pidsDetails[] = array(
						// 	'handle'=>$r['handle'],
						// 	$r['type']=>$r['data']
						// );
						$pidsDetails[$r['handle']]['handle'] = $r['handle'];
						// $pidsDetails[$r['handle']][$r['type']] = array($r['idx']=>$r['data']);
						$pidsDetails[$r['handle']][$r['type']] = $r['data'];

					}
				}
				$result = array();
				foreach($pidsDetails as $r){
					array_push($result, $r);
				}

				$response['pids'] = $result;
			}else{
				$response['no_result'] = true;
			}
		}

		if(($offset + $limit) < $response['result_count']){
			$response['hasMore'] = true;
			$response['next_offset'] = $offset + $limit;
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
	