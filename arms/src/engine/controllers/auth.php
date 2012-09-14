<?php
class Auth extends CI_Controller {

	public function login(){
		$data['title'] = 'Login';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		
		
		if ($this->input->post('inputUsername') || $this->input->post('inputPassword'))
		{
			try 
			{
				$this->load->model('authentication');
				$login_object = $this->authentication->authenticate($this->input->post('inputUsername'),$this->input->post('inputPassword'), gCOSI_AUTH_METHOD_BUILT_IN);	

				if ($login_object['result'] == 1)
				{
					appendRoles(array_merge(array('AUTHENTICATED_USER'),$login_object['functional_roles']));
					$this->session->set_userdata(array('AUTH_USER_ID' => $login_object['role'] . "::",
													'AUTH_USER_NAME' => $login_object['name']));
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

		$this->session->sess_destroy(); //??
		redirect('/');
		
	}
	
	public function dashboard()
	{
		$data['title'] = 'My Dashboard';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		if(hasRole('AUTHENTICATED_USER')) 
		{
			$this->load->view('dashboard', $data);
		}
		else 
		{
			redirect('auth/login');
		}
	}
	

}