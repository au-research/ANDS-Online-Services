<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Location_capture_widget extends MX_Controller {

	function index()
	{
		$data['js_lib'] = array('core','prettyprint');
		$data['scripts'] = array();
		$data['title'] = 'Location Capture Widget';
		$this->load->view('documentation', $data);
		
	}
	
	function demo()
	{
		$this->load->view('demo');
	}
}
	