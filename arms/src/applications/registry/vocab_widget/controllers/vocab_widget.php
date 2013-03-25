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
		$solr_base = $this->config->item('solr_url');
		$sissvoc_base = str_replace('/solr1',
					    '/sissvoc/api',
					    $solr_base);
		$solr_base .= "collection1/";
		$this->load->view("proxy", array('solr_base' => $solr_base,
						 'sissvoc_base' => $sissvoc_base));
	}
}
