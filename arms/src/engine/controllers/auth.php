<?php
class Auth extends CI_Controller {

	public function login(){
		$data['title'] = 'Login';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		
		
		if ($this->input->post('inputUsername') || $this->input->post('inputPassword') && !$this->user->loggedIn())
		{
			try 
			{
				if($this->user->authChallenge($this->input->post('inputUsername'), $this->input->post('inputPassword')))
				{
					redirect('/');
				}
			}
			catch (Exception $e)
			{
				$data['error_message'] = "Unable to login. Please check your credentials are accurate.";
			}
		}
		
		$this->load->view('login', $data);
	}
	
	public function logout(){
		// Logs the user out and redirects them to the homepage/logout confirmation screen
		$this->user->logout(); 		
	}

	public function registerAffiliation(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$orgRole = $this->input->post('orgRole');
		$thisRole = $this->input->post('thisRole');
		$jsonData = array();
		$this->load->model('cosi_authentication', 'cosi');
		if($this->cosi->registerAffiliation($thisRole, $orgRole)){
			$jsonData['status']='OK';
			$jsonData['message']='registering success';
		}else{
			$jsonData['status']='ERROR';
			$jsonData['message']='problem encountered while registering affiliation';
		}
		$jsonData['message'].=$thisRole. ' affiliates with '.$orgRole;
		echo json_encode($jsonData);
	}
	
	public function dashboard()
	{
		$data['title'] = 'My Dashboard';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		
		if(sizeof($this->user->affiliations())>0){
			$data['hasAffiliation']=true;
		}else $data['hasAffiliation']=false;
		
		if (mod_enabled('data_source'))
		{
			$this->load->model('data_source/data_sources','ds');
			$data['my_datasources'] = $this->ds->getOwnedDataSources();
		}
		
		
		if (mod_enabled('vocab_service'))
		{
			$this->load->model('vocab_service/vocab_services','vocab');
			$data['my_vocabs'] = $this->vocab->getOwnedVocabs();

			$this->load->model('cosi_authentication', 'cosi');
			$data['available_organisations'] = $this->cosi->getAllOrganisationalRoles();
		}
		
		
		
		if($this->user->loggedIn()) 
		{
			$this->load->view('dashboard', $data);
		}
		else 
		{
			redirect('auth/login');
		}
	}
	

}