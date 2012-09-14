<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class RIFCSMethod extends MethodHandler
{
	//var $params, $options, $formatter; 
   function handle()
   {
   		print_pre($this->formatter);
   }
}