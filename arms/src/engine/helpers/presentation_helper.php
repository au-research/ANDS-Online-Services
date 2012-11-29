<?php
function print_pre($var)
{
	echo "<pre>";
		print_r($var);
	echo "</pre>";
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

function curl_post($url, $post)
{
    $header = array("Content-type:text/xml; charset=utf-8");

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

    $data = curl_exec($ch);
	
	
    /*if (curl_errno($ch)) {
       print "curl_error:" . curl_error($ch).'<br/>';
    } else {
       curl_close($ch);
       print "curl exited okay\n";
       echo "Data returned...\n";
       echo "------------------------------------\n";
       echo $data;
       echo "------------------------------------\n";
    } */
    return $data;
}

function url_suffix(){
	return '#!/';
}

function formatResponse($response, $format='xml'){
	header('Cache-Control: no-cache, must-revalidate');
	if($format=='xml'){
		header ("content-type: text/xml");
		$xml = new SimpleXMLELement('<root/>');
		$response = array_flip($response);
		array_walk_recursive($response, array ($xml, 'addChild'));
		print $xml->asXML();
	}elseif($format=='json'){
		header('Content-type: application/json');
		$response = json_encode($response);
		echo $response;
	}elseif($format=='raw'){
		print $response['message'];
	}elseif($format=='raw-xml'){
		header ("content-type: text/xml");
		print($response['message']);
	}
}