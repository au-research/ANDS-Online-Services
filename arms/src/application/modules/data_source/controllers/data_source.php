<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Core Data Source controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Data_source extends MX_Controller {

	public function index()
	{
		$this->output->enable_profiler(TRUE);
		$this->load->model("data_sources","ds");

		echo "<pre>";
		//$ds = $this->ds->getBySlug('abctb');
		//$ds->append_log("This is a test log message...TESTING!", "debug");
		
		//$ds2 = $this->ds->getBySlug('abctb');
		
		
		
		//echo modules::run('test/test/index');
	//	$this->ds->
	}
	
	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();	
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */