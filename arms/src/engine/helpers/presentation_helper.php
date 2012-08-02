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
	
	
$BENCHMARK_TIME = array();
function bench($idx = 0)
{
	global $BENCHMARK_TIME;
	if (!isset($BENCHMARK_TIME[$idx])) { $BENCHMARK_TIME[$idx] = 0; }
	
	if ($BENCHMARK_TIME[$idx] == 0) 
	{
		$BENCHMARK_TIME[$idx] = microtime(true);
	}
	else
	{
		$diff = sprintf ("%.3f", (float) (microtime(true) - $BENCHMARK_TIME[$idx]));
		$BENCHMARK_TIME[$idx] = 0;
		return $diff;
	}
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