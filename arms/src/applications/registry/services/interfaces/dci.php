<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class DCIInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload)
	{
		
		$dciDoc = '<?xml version="1.0" encoding="UTF-8"?>'.NL;
		$dciDoc .= '<DigitalContentData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="28May2013_DCI_schema_providers_V4.xsd">'.NL;
        $dciDoc .= implode($payload);
        $dciDoc .= '</DigitalContentData>';
        echo $dciDoc;
	}
    
	function error($message)
	{
		$dciDoc = '<DigitalContentData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="28May2013_DCI_schema_providers_V4.xsd">'.NL;
        $dciDoc .= $message;
        $dciDoc .= '</DigitalContentData>';
        echo $dciDoc;
	}
	
	function output_mimetype()
	{
		return 'application/xml';
	}
}