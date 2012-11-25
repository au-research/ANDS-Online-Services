<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Vocab_widget extends MX_Controller {

        function index()
        {
                $data['js_lib'] = array('core','prettyprint');
                $data['scripts'] = array();
                $data['title'] = 'Vocabulary Widget - ANDS';
                $this->load->view('documentation', $data);

        }

	function proxy()
	{
		$this->load->view("proxy");
	}
}
