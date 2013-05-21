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
		die('This module is not enabled. Check your configuration item: $ENV[ENGINE_ENABLED_MODULE_LIST]['.$module_name.'] (global_config.php)');
	}
}

function acl_enforce($function_name, $message = '')
{
	$_ci =& get_instance();
	if (!$_ci->user->isLoggedIn())
	{
		throw new Exception (($message ?: "Access to this function requires you to be logged in. Perhaps you have been automatically logged out?"));
	}
	else if (!$_ci->user->hasFunction($function_name))
	{
		throw new Exception (($message ?: "You do not have permission to use this function (".$function_name."). Perhaps you have been logged out?"));
	}
}

function ds_acl_enforce($ds_id, $message = ''){
	$_ci =& get_instance();
	$_ci->load->model('data_source/data_sources', 'ds');
	$ds = $_ci->ds->getByID($ds_id);
	if($ds){
		if (!$_ci->user->hasAffiliation($ds->record_owner)){
			throw new Exception (($message ?: "You do not have permission to access this data source: ".$ds->title." (".$ds->record_owner.")"));
		}
	}else{
		throw new Exception ("Datasource does not exists!");
	}
}


/* Error handling */

function default_error_handler($errno, $errstr, $errfile, $errline)
{
	// Ignore E_STRICT mode
	if ($errno == E_STRICT) { return true; }

	if (ENVIRONMENT == "development")
	{
		throw new Exception($errstr . NL . "on line " . $errline . " (" . $errfile .")");
	}
	else
	{
		throw new Exception("An unexpected system error has occured. Please try again or report this error to the system administrator.");
	}
	return true;   /* Don't execute PHP internal error handler */

}
set_error_handler("default_error_handler");

function default_exception_handler( $e ) {

    $_ci =& get_instance(); // CI super object to access load etc.
    
	$data['js_lib'] = array('core');
	$data['scripts'] = array();
	$data['title'] = 'An error occurred!';

    echo $_ci->load->view( 'header' , $data , true); 
    
   	echo $_ci->load->view( 'exception' , array("message" => $e->getMessage()) , true );
   
    echo $_ci->load->view( 'footer' , $data , true);
}
set_exception_handler('default_exception_handler');

function json_exception_handler( $e ) {
    echo json_encode(array("status"=>"ERROR", "message"=> $e->getMessage()));
}

function asset_url( $path, $loc = 'modules')
{
	$CI =& get_instance();

	if($loc == 'base'){
		return $CI->config->item('default_base_url').'assets/'.$path;
	}else if($loc == 'core'){
		return base_url( 'assets/core/' . $path );
	}else if($loc == 'modules'){
		if ($module_path = $CI->router->fetch_module()){
			return base_url( 'assets/' . $module_path . "/" . $path );
		}
		else{
			return base_url( 'assets/' . $path );
		}
	}else if($loc =='base_path'){
		return $CI->config->item('default_base_url').$path;
	}
}

function registry_url($suffix='')
{
	$CI =& get_instance();
	return $CI->config->item('default_base_url') . 'registry/' . $suffix;
}

function portal_url($suffix='')
{
	$CI =& get_instance();

	return $CI->config->item('default_base_url') . $suffix;
}

function current_protocol()
{
	$url = parse_url(site_url());
	return $url['scheme'].'://';
}

function host_url(){
	$url = parse_url(site_url());
	return $url['scheme'].'://'.$url['host'];
}

function secure_host_url(){
	$url = parse_url(site_url());
	$protocol = 'https://';
	$host = $url['host'];
	return $protocol.$host;
}

function secure_base_url(){
	$url = parse_url(site_url());
	$protocol = 'https://';
	return $protocol.$url['host'].$url['path'];
}

function url_suffix(){
	return '#!/';
}

function utc_timezone()
{
	date_default_timezone_set('UTC');
}

function reset_timezone()
{
	$CI =& get_instance();
	date_default_timezone_set($CI->config->item('default_tz'));
}

$cycles = 0;
function clean_cycles()
{
	global $cycles;
	$cycles++;
	if ($cycles > 100)
	{
		gc_collect_cycles();
		$cycles = 0;
	}
}

function urchin_for($account)
{
	if (isset($account) && !empty($account)) {
		$snippet = <<<URCHIN
var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '%s']);
	_gaq.push(['_trackPageview']);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();

URCHIN;
		return sprintf($snippet, $account);
	}
	else {
		return "<!-- this would be the code snippet for Google Analytics, " .
		"but the provided account details were empty... -->\n";
	}
}