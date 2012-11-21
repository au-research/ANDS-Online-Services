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

function json_to_xml($obj){
  $str = "";
  if(is_null($obj))
  {
    return "<null/>";
  }
  elseif(is_array($obj)) 
  {
      //a list is a hash with 'simple' incremental keys
    $is_list = array_keys($obj) == array_keys(array_values($obj));
    if(!$is_list) {
      foreach($obj as $k=>$v)
        $str.="<$k>".json_to_xml($v)."</$k>".NL;
    } else {
      $str.= "<list>";
      foreach($obj as $v)
        $str.="<item>".json_to_xml($v)."</item>".NL;
      $str .= "</list>";
    }
    return $str;
  }
  elseif(is_string($obj))
  {
    return htmlspecialchars($obj) != $obj ? "<![CDATA[$obj]]>" : $obj;
  } 
  elseif(is_scalar($obj))
    return $obj;
  else
    throw new Exception("Unsupported type $obj");
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