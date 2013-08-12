<?php

class Uploader extends MX_Controller {

	private $map = array();
	private $directory = './assets/uploads/';

	function __construct()
	{
		parent::__construct();

		$config['upload_path'] = $this->directory;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['max_size']	= '4096'; //in KB
		$config['max_width']  = '0';
		$config['max_height']  = '0';
		$config['max_filename']  = '64';
		$this->load->library('upload', $config);

		$this->load->helper('directory');

		if (!is_dir($config['upload_path']) || !is_writeable($config['upload_path']))
		{
			throw new Exception("Uploads directory has not been created or cannot be read/written to.<br/><br/> Please create the directory: " . $config['upload_path']);
		}
		$this->map = directory_map($config['upload_path']);
		$this->load->helper(array('form', 'url'));
	}


	function index()
	{
		$data['recent_uploads'] = $this->getRecentUploads();
		$data['title'] = "Image Uploader";

		$this->load->view('uploader', $data);
	}


	function upload()
	{
		if ( ! $this->upload->do_upload('new_file') )
		{
			$data['error_message'] = $this->upload->display_errors();
			$data['recent_uploads'] = $this->getRecentUploads();
			$data['title'] = "Image Uploader";

			$this->load->view('uploader', $data);
		}
		else
		{
			// Create the compressed copy
			$upload_result = $this->upload->data();
			$image = new Imagick(  $this->directory . $upload_result['file_name'] );

			// Create the optimised image
			// "bestfit" param will ensure that the image is downscaled proportionally if needed
			$image->resizeImage(350,200, Imagick::FILTER_LANCZOS, 1, true);
			$image->setImageCompression(Imagick::COMPRESSION_JPEG);
			$image->setCompression(Imagick::COMPRESSION_JPEG);
			$image->setCompressionQuality(80); 
			$image->writeImage($this->directory . 'img_' . md5($upload_result['file_name']) . ".jpg");
			$image->clear();
			$image->destroy();

			$data['success_message'] = "File successfully uploaded!";
			$data['recent_uploads'] = $this->getRecentUploads();
			$data['title'] = "Image Uploader";

			$this->load->view('uploader', $data);
		}
	}

	private function getRecentUploads()
	{
		$this->map = directory_map($this->directory);
		$return_map = array();
		foreach ($this->map AS &$file)
		{
			if (!is_array($file) && strpos($file, 'img_') !== 0)
			{
				$return_map[] = array(	'date_modified' => filemtime($this->directory . $file),
										'filename' => $file,
										'optimised_filename' => 'img_' . md5($file) . ".jpg");
			}
		}
		// Sort by date order and take the most recent 30 files
		usort($return_map, array($this, 'rsort_by_date_cmp'));
		$return_map = array_slice($return_map, 0, 30);
		return $return_map;
	}


	private function rsort_by_date_cmp($a, $b)
	{
		return ($a['date_modified'] < $b['date_modified']) ? +1 : -1;
	}

}