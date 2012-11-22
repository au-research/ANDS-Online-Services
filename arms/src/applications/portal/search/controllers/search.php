<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia - Search';
		
		//$data['scripts'] = array('home_page');
		$this->load->view('search_layout', $data);
	}
}