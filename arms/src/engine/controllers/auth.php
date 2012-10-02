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
	
	public function dashboard()
	{
		$data['title'] = 'My Dashboard';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		
		
		
		if (mod_enabled('data_source'))
		{
			$this->load->model('data_source/data_sources','ds');
			$data['my_datasources'] = $this->ds->getOwnedDataSources();
		}
		
		
		if (mod_enabled('vocab_service'))
		{
			$this->load->model('vocab_service/vocab_services','vocab');
			$data['my_vocabs'] = $this->vocab->getOwnedVocabs();
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