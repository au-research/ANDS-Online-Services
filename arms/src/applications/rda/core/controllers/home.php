<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia';
		$this->load->view('home', $data);
	}
}