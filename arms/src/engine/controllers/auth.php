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

	public function registerAffiliation($new = false){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$orgRole = $this->input->post('orgRole');
		$thisRole = $this->input->post('thisRole');
		$jsonData = array();
		$this->load->model('cosi_authentication', 'cosi');

		if($new){
			$this->cosi->createOrganisationalRole($orgRole, $thisRole);
		}

		if(in_array($orgRole, $this->user->affiliations())){
			$jsonData['status']='WARNING';
			$jsonData['message']='You are already affiliate with this organisation: '.$orgRole;
		}else{
			if($this->cosi->registerAffiliation($thisRole, $orgRole)){
				$jsonData['status']='OK';
				$jsonData['message']='registering success';
			}else{
				$jsonData['status']='ERROR';
				$jsonData['message']='problem encountered while registering affiliation';
			}
		}
		
		//$jsonData['message'].=$thisRole. ' affiliates with '.$orgRole;
		echo json_encode($jsonData);

		//sending email
		$this->load->library('email');
		$this->email->from('minh.nguyen@ands.org.au', 'Minh Duc Nguyen test');
		$this->email->to('minh.nguyen@ands.org.au'); 
		$this->email->subject('New user affiliation registered');
		$message = 'Registering user '.$thisRole. ' to affiliate with '.$orgRole;
		if($new) $message.='. User created '.$orgRole;
		$this->email->message($message);	
		$this->email->send();
	}
	
	public function dashboard()
	{
		$data['title'] = 'My Dashboard';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		
		
		if($this->user->loggedIn()) 
		{
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
				$data['group_vocabs']=$this->vocab->getGroupVocabs();
				$data['owned_vocabs']=$this->vocab->getOwnedVocabs(false);
				$this->load->model('cosi_authentication', 'cosi');
				$data['available_organisations'] = $this->cosi->getAllOrganisationalRoles();
				asort($data['available_organisations']);
			}

			$this->load->view('dashboard', $data);
		}
		else 
		{
			redirect('auth/login');
		}
	}
	

}