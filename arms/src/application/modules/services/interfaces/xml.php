<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class XMLInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload)
	{
		
	}
    
	function error($message)
	{
		
	}
	
	function output_mimetype()
	{
		return 'application/xml';
	}
}