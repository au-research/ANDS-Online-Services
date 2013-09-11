<?php

class Theme_cms extends MX_Controller {
	private $directory = './assets/shared/theme_pages/';
	private $index_file = 'theme_cms_index.json';

	function index(){
		$this->checkWritable();
		$data['title']='Theme CMS';
		$data['scripts'] = array('theme_cms_app');
		$data['js_lib'] = array('core', 'tinymce', 'angular', 'rosearch_widget', 'colorbox');
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
		$this->build_index();
	}

	public function delete_page(){
		$data = file_get_contents('php://input');
		$array = json_decode($data);
		unlink($this->directory.$array->slug.'.json');
		$this->build_index();
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
		$index = read_file($this->directory.$this->index_file);
		echo $index;
	}

	public function build_index(){
		$root = scandir($this->directory, 1);
		$result = array();
		$result = array();
		foreach($root as $value){
			if($value === '.' || $value === '..') {continue;} 
			$pieces = explode(".", $value);
			if(is_file("$this->directory/$value")) {
				if($pieces[0].'.json'!=$this->index_file){
					$file = json_decode(read_file($this->directory.$pieces[0].'.json'), true);
					$result[] = array(
						'title' => (isset($file['title'])?$file['title']:'No Title'),
						'slug' => (isset($file['slug'])?$file['slug']:$pieces[0]),
						'status' => (isset($file['status'])?$file['status']:'')
					);
				}
			} 
		}
		if(!write_file($this->directory.$this->index_file, json_encode($result), 'w+')){
			return false;
		} else {
			return true;
		}
	}
	
	// Initialise
	function __construct(){
		parent::__construct();
		acl_enforce('PORTAL_STAFF');
		$this->load->helper('file');
	}
}