<?php
function print_pre($var)
{
	echo "<pre>";
		print_r($var);
	echo "</pre>";
} 

function wrap_xml($xml, $scheme = 'rif')
{
	
	$return = "";
	switch($scheme)
	{
		
		case "rif":
			$return .= '<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">' . NL; 
			$return .= $xml;
			$return .= '</registryObjects>' . NL;
				
		break;
	}
	return $return;		
}
	