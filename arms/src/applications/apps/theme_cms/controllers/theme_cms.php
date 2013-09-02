<?php

class Theme_cms extends MX_Controller {

	function index(){
		$data['title'] = 'Theme CMS';
		$data['scripts'] = array('theme_cms');
		$data['js_lib'] = array('core', 'tinymce');
		$this->load->view('theme_cms_index', $data);
	}

	public function view($slug=''){
		$data['title'] = 'Theme CMS';
		$data['scripts'] = array('theme_cms');
		$data['js_lib'] = array('core', 'tinymce');
		$this->load->view('theme_cms_index', $data);
	}
	
	// Initialise
	function __construct(){
		parent::__construct();
		acl_enforce('PORTAL_STAFF');
	}

}