<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
if (!IN_ORCA) die('No direct access to this file is permitted.');

		$values = array();

		
//OLD - use Search Draft By Name

		$searchText = rawurldecode(getQueryValue("sText"));
	//	echo json_encode($searchText);
	//	exit;

		$objectClass =  rawurldecode(getQueryValue("oClass"));
		$dataSourcekey =  rawurldecode(getQueryValue("dSourceKey"));
		$group = rawurldecode(getQueryValue("oGroup"));
		$registryObjects = array();
		$names = array();
	
		$limit = 100;
		
		$match = array(   '\\', '&', '|',   '!',    '(',   ')',   '{',   '}',   '[',   ']',   '^',   '-',   '~',    '*',   '?',   ':',   '"',  '"',   ';',   '#',   '%',   '@',    '_');
    	$replace = array('\\\\','\\&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\-', '\\~', '\\*', '\\?', '\\:', '"', '\\"',  '\\;',   '\\#', '\\%', '\\@', '\\_');
    	$searchText = str_replace($match, $replace, $searchText);

		if ($searchText == "\\*\\:\\*")
		{
			// search for all names (untransform SOLR query syntax)
			//$names = searchDraftByName("", $objectClass , $dataSourcekey, $limit);
		}
		else
		{
			//$names = searchDraftByName($searchText, $objectClass , $dataSourcekey, $limit);
		}
		
		if (isset($names) && $names) 
		{ 		
			foreach ($names as $i => $value) 
			{   	
				$values[] = array (	"value" => $value['registry_object_key'], "desc" => $value['display_title']." (".$value['status'].")");       		    	
			}			
		} else {

			//$values[] = array (	"value" => "", "desc" => "Sorry - No Registry Object found!");
		}

	//	print json_encode($values);
	//	exit;


//NEW - use SOLR
		$objectClass = strtolower($objectClass);
		$groupStr = '';
    	if($group)$groupStr =' +group:("'.$group.'")';

		$q = '(list_title:('.($searchText).'*) OR list_title:('.($searchText).') OR list_title:('.($searchText).'~0.3)) +class:('.$objectClass.')'.$groupStr;
		
		if($dataSourcekey!='') $q.=' +data_source_key:("'.$dataSourcekey.'")';
		$fields = array(
			'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>$limit, 'wt'=>'json',
			'fl'=>'key, display_title, description_value, description_type, status'
		);
	
		/*prep*/
		$fields_string='';
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
		rtrim($fields_string,'&');
	
		//echo $fields_string;
	
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
		curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
		$content = curl_exec($ch);//execute the curl
    	
		curl_close($ch);//close the curl
		
		//echo 'json received+<pre>'.$content.'</pre>';	
	
		$decoded = json_decode($content);
		//print_r($decoded);
	

		//$values[] = array('value'=>$searchText, "desc"=> $fields_string);
		if (isset($decoded->response->docs))
		{
			foreach($decoded->response->docs as $d){
				$values[] = array (	"value" => $d->{'key'}, "desc" => $d->{'display_title'}.' ('.$d->{'status'}.')');
			}
		}
		

		echo json_encode($values);