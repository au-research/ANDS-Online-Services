<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  PIDs primary controller
 *  @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Pids extends MX_Controller {

	/**
	 * Default function for pids, list all pids
	 * @return view 
	 */
	function index(){
		$data['title'] = 'List PIDs';
		$data['scripts'] = array('pids');
		$data['js_lib'] = array('core', 'dataTables');
		$this->load->view('pids_index', $data);
	}

	/**
	 * list all pids web service for the pids dashboard
	 * @return json 
	 */
	function list_pids(){
		$pids = array(
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
			array('id'=>1,'stuff'=>2),
			array('id'=>2,'stuff'=>3),
			array('id'=>3,'stuff'=>4),
			array('id'=>6,'stuff'=>5),
		);
		echo json_encode($pids);
	}
}
	