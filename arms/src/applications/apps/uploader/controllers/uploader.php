<?php

class Uploader extends MX_Controller {

	// Immutable settings
	const COMPRESSION_PERCENTAGE = 80; // JPEG compression percentage
	const IMAGE_WIDTH = 470; //pixels, image will be proprtionally resized to fit (if needed)
	const IMAGE_HEIGHT= 90;
	const MAX_FILE_SIZE_KB = 4096;
	const MAX_FILE_NAME_LEN = 64;
	const FILE_PREFIX = "img_"; // settings for the compressed/optimised generated file, filename
	const FILE_SUFFIX = ".jpg";
	const RECENT_CUTOFF = 30; // number of items to list as "Recent"

	// Mutable settings
	private $map = array();

	// Directory where uploads should be stored
	private $directory = './assets/uploads/';



	// Default page, containing a list of recent uploads and a form for uploading new images
	function index()
	{
		$data['recent_uploads'] = $this->getRecentUploads();
		$data['title'] = "Image Uploader";
		$data['js_lib'] = array('core');
		$this->load->view('uploader', $data);
	}

	// Receives the POST operation of the upload form
	function upload()
	{
		// Default settings/restrictions
		$config['upload_path'] = $this->directory;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';	
		$config['max_size']	= self::MAX_FILE_SIZE_KB;
		$config['max_width']  = '0'; // no restriction (image will be resized anyway!)
		$config['max_height']  = '0';
		$config['max_filename']  = self::MAX_FILE_NAME_LEN;
		$this->load->library('upload', $config);

		// Try and upload the file using the settings from __construct()
		if ( ! $this->upload->do_upload('new_file') )
		{
			// Abort upload and display errors, if any
			$data['error_message'] = $this->upload->display_errors();
			$data['recent_uploads'] = $this->getRecentUploads();
			$data['title'] = "Image Uploader";
			$data['js_lib'] = array('core');

			$this->load->view('uploader', $data);
		}
		else
		{
			// Create the compressed copy of the image based on a hash of the uploaded filename
			$upload_result = $this->upload->data();
			$image = new Imagick(  $this->directory . $upload_result['file_name'] );

			// Create the optimised image
			// "bestfit" param will ensure that the image is downscaled proportionally if needed
			$image->resizeImage(self::IMAGE_WIDTH,self::IMAGE_HEIGHT, Imagick::FILTER_LANCZOS, 1, true);
			$image->setImageCompression(Imagick::COMPRESSION_JPEG);
			$image->setCompression(Imagick::COMPRESSION_JPEG);
			$image->setCompressionQuality(self::COMPRESSION_PERCENTAGE); 
			$image->writeImage($this->directory . 'img_' . md5($upload_result['file_name']) . ".jpg");
			$image->clear();
			$image->destroy();

			$data['success_message'] = "File successfully uploaded!";
			$data['recent_uploads'] = $this->getRecentUploads();
			$data['title'] = "Image Uploader";
			$data['js_lib'] = array('core');

			$this->load->view('uploader', $data);
		}
	}

	// Generates a list of recent uploads along with their associated optimised files, ordered by most recent
	private function getRecentUploads()
	{
		// Check the upload directory exists/is writeable
		if (!is_dir($this->directory) || !is_writeable($this->directory))
		{
			throw new Exception("Uploads directory has not been created or cannot be read/written to.<br/><br/> Please create the directory: " . $this->directory);
		}

		// Create the directory map for use in generating the recent list
		$this->load->helper('directory');
		$this->map = directory_map($this->directory);
		// 
		$return_map = array();
		foreach ($this->map AS &$file)
		{
			// Do not list optimised files (i.e. those starting with FILE_PREFIX)
			if (!is_array($file) && strpos($file, self::FILE_PREFIX) !== 0)
			{
				$return_map[] = array(	'date_modified' => filemtime($this->directory . $file),
										'filename' => $file,
										'optimised_filename' => self::FILE_PREFIX . md5($file) . self::FILE_SUFFIX);
			}
		}

		// Sort by date order and take the most recent 30 files
		usort($return_map, array($this, 'rsort_by_date_cmp'));

		// Slice to restrict the list
		$return_map = array_slice($return_map, 0, self::RECENT_CUTOFF);

		return $return_map;
	}


	// Helper sort function to sort list items based on descending modified date (i.e. most recent first)
	private function rsort_by_date_cmp($a, $b)
	{
		return ($a['date_modified'] < $b['date_modified']) ? +1 : -1;
	}


	// Initialise
	function __construct()
	{
		parent::__construct();
		acl_enforce('PORTAL_STAFF');
		$this->load->helper(array('form', 'url'));
	}


}