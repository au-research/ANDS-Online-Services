<?php

class Theme_cms extends MX_Controller {
	private $directory = './assets/theme_pages/';

	function index(){
		$this->checkWritable();
		$data['title']='Theme CMS';
		$data['scripts'] = array('theme_cms_app');
		$data['js_lib'] = array('core', 'tinymce', 'angular');
		$this->load->view('theme_cms_index', $data);
	}

	function checkWritable(){
		// Check the upload directory exists/is writeable
		if (!is_dir($this->directory) || !is_writeable($this->directory)){
			throw new Exception("Uploads directory has not been created or cannot be read/written to.<br/><br/> Please create the directory: " . $this->directory);
		}
	}

	public function get($slug){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$file = read_file($this->directory.$slug.'.json');
		echo $file;
	}

	public function new_page(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = file_get_contents("php://input");
		$array = json_decode($data);
		if($this->write($array->slug, $data)){
			echo 1;
		}else echo 0;
	}

	public function delete_page(){
		unlink($this->directory.$this->input->post('slug').'.json');
	}

	public function save_page(){
		$data = file_get_contents("php://input");
		$array = json_decode(file_get_contents("php://input"));
		echo json_encode($array);
		if($this->write($array->slug, $data)){
			echo 1;
		}else echo 0;
	}

	public function write($slug, $content){
		if(!write_file($this->directory.$slug.'.json', $content, 'w+')){
			return false;
		}else return true;
	}

	public function view($slug=''){
		$data['title'] = 'Theme CMS';
		$data['scripts'] = array('theme_cms');
		$data['js_lib'] = array('core', 'tinymce');
		$this->load->view('theme_cms_view', $data);
	}

	public function list_pages(){
		$root = scandir($this->directory, 1);
		$result = array();
		foreach($root as $value){
			if($value === '.' || $value === '..') {continue;} 
			$pieces = explode(".", $value);
			if(is_file("$this->directory/$value")) {
				$result[]=$pieces[0];
				continue;
			} 
			foreach(find_all_files("$dir/$value") as $value){ 
				$result[]=$pieces[0]; 
			}
		}
		echo json_encode($result);
	}
	
	// Initialise
	function __construct(){
		parent::__construct();
		acl_enforce('PORTAL_STAFF');
		$this->load->helper('file');
	}
}