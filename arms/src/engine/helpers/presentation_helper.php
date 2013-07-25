<?php
function print_pre($var)
{
	echo "<pre>";
		print_r($var);
	echo "</pre>";
} 

function display_date($timestamp=0)
{
    if (!$timestamp)
    {
        $timestamp = time();
    }

    return date("j F Y, g:i a", $timestamp);
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

function readable($text, $singular = false){
	$text = trim(strtolower($text));
	switch($text){
        case "all": return 'All'; break;
		case "draft": return ($singular ? 'Draft' : 'Drafts');break;
		case "submitted_for_assessment": return 'Submitted for Assessment';break;
		case "assessment_in_progress": return 'Assessment In Progress';break;
		case "approved": return ($singular ? 'Approved' : 'Approved Records');break;
		case "published": return  ($singular ? 'Published' : 'Published Records');break;
		case "more_work_required": return 'More Work Required';break;
		case "collection": return 'Collections';break;
		case "party": return 'Parties';break;
		case "service": return 'Services';break;
		case "activity": return 'Activities';break;
        case "role_user": return 'User';break;
        case "role_organisational": return 'Organisation';break;
        case "role_functional": return 'Functional';break;
        case "role_doi_appid": return 'DOI Application Identifier';break;
        case "t": return "<i class='icon icon-ok'></i>";break;
        case "f": return "<i class='icon icon-remove'></i>";break;
        case "authentication_built_in": return "Built-in";break;
        case "authentication_ldap": return "LDAP";break;
        case "authentication_shibboleth": return "Shibboleth";break;
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
