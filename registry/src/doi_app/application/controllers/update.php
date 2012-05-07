<?php 
class Update extends CI_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->update();
	}

}
?>