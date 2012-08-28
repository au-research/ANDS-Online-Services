<?php
class Login extends CI_Controller {

	public function index(){
		$data['title'] = 'Login';
		$data['js_lib'] = array('core');
		$data['scripts'] = array('');
		$this->load->view('login', $data);
	}
}
?>