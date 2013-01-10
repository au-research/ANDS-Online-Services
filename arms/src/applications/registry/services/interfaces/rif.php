<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class RIFInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload)
	{
		echo "<?xml version=\"1.0\"?>".NL;
		echo wrapRegistryObjects(implode($payload,"\n"));
	}
    
	function error($message)
	{
		echo '<?xml version="1.0" ?>'.NL;
		echo wrapRegistryObjects('');
	}
	
	function output_mimetype()
	{
		return 'application/xml';
	}
}