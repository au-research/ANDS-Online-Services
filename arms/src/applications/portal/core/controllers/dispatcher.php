<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The dispatcher catches all requests that pass through the (:any) filter 
 * in the routes.php configuration for this application. 
 *
 * It's purpose is to map any requests that fail to match a specific module
 * and/or controller to the default view controller. This effectively means
 * that we can treat any "unknown" request as if it were a request to a SLUG
 * (so http://myapp/my_random_record_slug is treated as a view request).
 *
 * Note: Only a PUBLISHED record with a matching SLUG will be returned. 
 * 
 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
 */
class Dispatcher extends MX_Controller {

	public function __construct()
    {
         parent::__construct();
    }

	public function _remap($method, $params = array())
	{
		// Put the method back together and try and locate a matching controller
		array_unshift($params, $method);
		$requested_controller = CI::$APP->router->locate($params);
		if(!is_null($requested_controller))
		{
			echo Modules::run(implode("/",$params));
			return;
		}
		else
		{
			// If no match, assume it is a SLUG view request
			$_GET['slug'] = array_pop($params);
			$params = array("view");
			echo Modules::run(implode("/",$params));
			return;
		}
	}

}