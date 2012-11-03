<?php 
class Deactivate extends CI_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->deactivate();
	}

}
?>