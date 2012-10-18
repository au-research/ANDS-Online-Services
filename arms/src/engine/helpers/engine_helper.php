<?php

function mod_enabled($module_name)
{
	$CI =& get_instance();
	return in_array($module_name, $CI->config->item(ENGINE_ENABLED_MODULE_LIST));
}

function mod_enforce($module_name)
{
	$CI =& get_instance();
	if(!in_array($module_name, $CI->config->item(ENGINE_ENABLED_MODULE_LIST)))
	{
		die('This module is not enabled. Check your configuration item: $config[ENGINE_ENABLED_MODULE_LIST][$module_name] (config.php)');
	}
}

function my_exception_handler( $e ) {

    $_ci =& get_instance(); // CI super object to access load etc.
    
	$data['js_lib'] = array('core');
	$data['scripts'] = array();
	$data['title'] = 'An error occurred!';

    echo $_ci->load->view( 'header' , $data , true); 
    
   	echo $_ci->load->view( 'exception' , array("message" => $e->getMessage()) , true );
   
    echo $_ci->load->view( 'footer' , $data , true);
}
set_exception_handler('my_exception_handler');