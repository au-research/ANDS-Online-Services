<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Orcid_widget extends MX_Controller {

    function index()
    {
            $data['js_lib'] = array('core','prettyprint');
            $data['scripts'] = array();
            $data['title'] = 'Orcid Widget - ANDS';
            $this->load->view('documentation', $data);

    }

   function proxy()
    {
	//$solr_base = $this->config->item('solr_url');
	//$sissvoc_base = $this->config->item('sissvoc_url');
	$this->load->view("proxy");
    } 

    function demo()
    {
	$data['title'] = "ANDS Orcid widget";
//	$data['scripts'] = array('vocab_widget_loader');
	$data['js_lib'] = array('core', 'orcid_widget');
	$this->load->view('demo', $data);
    }

}
