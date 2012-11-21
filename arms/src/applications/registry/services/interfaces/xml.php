<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class XMLInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload)
	{
		echo "<?xml version=\"1.0\"?>";
		echo "<response>".NL;
			echo $payload;
		echo "</response>".NL;
	}
    
	function error($message)
	{
		echo '<?xml version="1.0" ?>';
		echo '<response type="error">'.NL;
			echo htmlentities($message);
		echo '</response>'.NL;
	}
	
	function output_mimetype()
	{
		return 'application/xml';
	}
}