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

function first_line($string)
{
	return strtok($string, "\r\n");
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


function curl_file_get_contents($URL)
{
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
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

function  timeAgo($timestamp, $granularity=2, $format='Y-m-d H:i:s'){
        $difference = time() - $timestamp;
        if($difference < 0) return '0 seconds ago';
        elseif($difference < 864000){
                $periods = array('week' => 604800,'day' => 86400,'hr' => 3600,'min' => 60,'sec' => 1);
                $output = '';
                foreach($periods as $key => $value){
                        if($difference >= $value){
                                $time = round($difference / $value);
                                $difference %= $value;
                                $output .= ($output ? ' ' : '').$time.' ';
                                $output .= (($time > 1 && $key == 'day') ? $key.'s' : $key);
                                $granularity--;
                        }
                        if($granularity == 0) break;
                }
                return ($output ? $output : '0 seconds').' ago';
                 // return ($output ? $output : '0 seconds').'';
        }
        else return date($format, $timestamp); 
}


function ellipsis ($string, $length = 64)
{
	if (strlen($string) <= $length)
	{
		return $string;
	}
	else
	{
		return substr($string,0, $length-3) . "&hellip;";
	}
}

function readable($text){
	$text = strtolower($text);
	switch($text){
		case "draft": return 'Drafts';break;
		case "submitted_for_assessment": return 'Submitted for Assessment';break;
		case "assessment_in_progress": return 'Assessment In Progress';break;
		case "approved": return 'Approved Records';break;
		case "published": return 'Published Records';break;
		case "more_work_required": return 'More Work Required';break;
		case "collection": return 'Collections';break;
		case "party": return 'Parties';break;
		case "service": return 'Services';break;
		case "activity": return 'Activities';break;
	}
}

function array_to_TABCSV($data)
{
    $outstream = fopen("php://temp", 'r+');
    foreach($data AS $row)
    {
    	fputcsv($outstream, $row, "\t", '"');
    }
    rewind($outstream);
    $csv = '';
    while (($buffer = fgets($outstream, 4096)) !== false) {
    	$csv .= $buffer;
    }
    fclose($outstream);
    return $csv;
}
