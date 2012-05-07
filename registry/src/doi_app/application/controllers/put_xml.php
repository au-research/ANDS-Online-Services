<?php 
class Put_xml extends CI_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->putxml();
	}

}
?>