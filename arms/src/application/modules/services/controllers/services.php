<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('services');

define('SERVICES_MODULE_PATH', 'application/modules/services/');
/**
 * Services controller
 * 
 * Abstract services controller allows for easy extension of the
 * services module and logging and access management of requests
 * via the API key system. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services
 * 
 */
class Services extends MX_Controller {
	
	var $reserved_pages = array('register');
	
	public function _remap($api_key, $params = array())
	{
		$this->config->load('services');
		$service_mapping = parse_ini_file(SERVICES_MODULE_PATH . "config.ini", true);
		log_message('debug', 'Services request received from ' . $_SERVER["REMOTE_ADDR"]);
		log_message('debug', 'Request URI: ' . $_SERVER["REQUEST_URI"]);
		
		// If no parameters supplied, display the services landing page!
		if ($api_key == "index")
		{
			$this->service_list();
			return;
		}
		else if (in_array($api_key, $this->reserved_pages) && method_exists($this, $api_key))
		{
			// Some pre-canned pages (such as the registration module will have methods defined in this class)
			$this->{$api_key}();
			return;
		}
		
		// Method i.e. "getRIFCS", Format i.e. "xml"
		list($method, $format, $options) = $this->parse_request_params($params);
		
		
		
		$formatter = $this->getFormatter($format);		
		
		if (!$this->check_compatibility($method, $format, $service_mapping))
		{
			$formatter->error("Your requested method does not support this format: " . $format);
			return;
		}
		
		// TODO: Check that the API key is valid
		
		// TODO: Increment the request counter
		
		// TODO: If debug mode...
		
		$options = $service_mapping[$method];
		$handler = $this->getMethodHandler($service_mapping[$method]['method_handler']);
		$handler->initialise($options, $_GET, $formatter);
		$this->output->set_content_type($formatter->output_mimetype());
		
		// All the setup is finished! Palm off the handling of the request...
		$handler->handle();
	}

	private function service_list()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Web Services';
		$this->load->view('service_list', $data);
	}
	
	private function register()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Web Services';
		$this->load->view('register_api_key', $data);
	}
	
	private function check_compatibility($method, $format, array $service_mapping)
	{
		if (array_key_exists($method, $service_mapping)
			&& is_array($service_mapping[$method]['supports']) 
			&& in_array($format, $service_mapping[$method]['supports']))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}
	
		
		
	private function authenticate_api_key($api_key)
	{
		// Do the API key checking here!
		if (strlen($api_key) != 32)
		{
			// TODO: Check that the API key is valid
			//
		}
		
	}
	
	private function parse_request_params(array $params)
	{

		// Get the default values (partially malformed requests)
		$method = $this->config->item('services_default_method');
		$format = $this->config->item('services_default_format');

		// Grab the values from the parameter array
		// The syntax should be: <method>.<format>/?<query params>
		if (($called_method = array_shift($params)) != NULL)
		{
			$called_method = explode(".",$called_method);
			
			if ($called_method[0])
			{
				$method = $called_method[0];
			}
			if (isset($called_method[1]) && $called_method[1])
			{
				$format = $called_method[1];
			}
		}
		// The remaining params get passed along to the query
		$query_params = array_shift($params);
		
		return array($method, $format, $query_params);
	}
	
	
	private function getFormatter($format)
	{
		$formatter = null;
		
		if ($format && ctype_alnum($format))
		{
			
			$path = SERVICES_MODULE_PATH . '/interfaces/' . strtolower($format) . '.php';
			if (file_exists($path))
			{
				require_once($path);
				$classname = $format . "interface";
				$formatter = new $classname;
			}
			else
			{
				throw new Exception("Invalid format. Could not load the formatting parser for: '" . $format . "'");
			}
		}
		else
		{
			throw new Exception("Invalid Formatter -- cannot continue");
		}
		
		return $formatter;
	}

	
	private function getMethodHandler($method)
	{
		$handler = null;
		
		if ($method && ctype_alnum($method))
		{
			
			$path = SERVICES_MODULE_PATH . '/method_handlers/' . strtolower($method) . '.php';
			if (file_exists($path))
			{
				require_once($path);
				$classname = $method . "method";
				$handler = new $classname;
			}
			else
			{
				throw new Exception("Invalid handler. Could not load the method handler for: '" . $method . "'");
			}
		}
		else
		{
			throw new Exception("Invalid Method handler -- cannot continue");
		}
		
		return $handler;
	}
	
	
}	