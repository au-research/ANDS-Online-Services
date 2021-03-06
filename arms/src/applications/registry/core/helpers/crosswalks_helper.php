<?php
global $ENV; $ENV['crosswalks'] = array();

function getCrossWalks()
{
	// Prevent multiple class generations
	global $ENV;
	if (count($ENV['crosswalks']) > 0) return $ENV['crosswalks'];

	// Include the interface
	require_once(APP_PATH . "core/crosswalks/_crosswalk.php");

	// List our crosswalks
	$CI =& get_instance();
	$CI->load->helper('directory');
	$crosswalks = directory_map(APP_PATH . "core/crosswalks");

	$cw_objects = array();

	foreach ($crosswalks AS $cw_class)
	{
		// Ignore classes with _filename.php
		if (substr($cw_class,0,1) == "_") continue;

		// Construct the crosswalk object
		$class_name = str_replace(".php","",$cw_class);
		try
		{
			require_once(APP_PATH . "core/crosswalks/" . $cw_class);
			// Add it to our objects array
			$cw_objects[$class_name] = new $class_name();
		}
		catch (Exception $e)
		{
			// No need to fatal error here...just print an error
			echo 'ERROR: Unable to load crosswalk: ' . $cw_class . '<br/>';
		}

	}

	return $cw_objects;

}