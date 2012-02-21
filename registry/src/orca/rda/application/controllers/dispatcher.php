<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dispatcher extends CI_Controller {

	public function _remap($method, $params = array())
	{

		if (file_exists(APPPATH.'controllers/'.$method.EXT))
		{
			include(APPPATH.'controllers/'.$method.EXT);
			$controller = new $method();
			
		    if (count($params) > 0 && method_exists($controller, $params[0]))
		    {
				$method = array_shift($params);
				call_user_func_array(array($controller, $method), $params);
				return;
			}
			else
			{
				call_user_func_array(array($controller, 'index'), $params);
				return;
			}
		}
		else
		{
			include('./application/controllers/view.php');
			$view_controller = new View();
			array_unshift($params, $method);
			
			call_user_func_array(array($view_controller, 'index'), array($params));
			return;
		}
	    show_404();
	}

}
?>