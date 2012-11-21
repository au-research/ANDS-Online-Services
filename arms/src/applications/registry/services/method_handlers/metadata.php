<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class MetadataMethod extends MethodHandler
{
	
	private $default_params = array(
		'q' => '*:*',
		'fl' => 'id,key,slug,display_title,status',
        'wt' => 'json',
        'indent' => 'on',
        'rows' => 20
    );
	
	
	//var $params, $options, $formatter; 
   function handle()
   {
   		// Get and handle a comma-seperated list of valid params which we will forward to the indexer
   		$permitted_forwarding_params = explode(',',$this->options['valid_solr_params']);
   		$forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
		
		$fields = array();
		foreach ($forwarded_params AS $param_name => $_)
		{
			$fields[$param_name] = $this->params[$param_name];
		}
		
		if (isset($this->params['debugAttributes']))
		{
			unset($this->default_params['fl']);
		}
		
		$fields = array_merge($this->default_params, $fields);
		
		$CI =& get_instance();
		$CI->load->library('solr');

		foreach($fields AS $key => $field)
		{
			$CI->solr->setOpt($key, $field);
		}

		$result = $CI->solr->executeSearch(true);
		
		if (!isset($this->params['debugQuery']))
		{
			$result = $result['response'];
		}
		
		echo json_encode($result);
   }
   
}