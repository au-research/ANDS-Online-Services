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


//OLD - use Search Draft By Name
		$searchText = getQueryValue("sText");
		$objectClass = getQueryValue("oClass");
		$dataSourcekey = getQueryValue("dSourceKey");
		$registryObjects = array();
		$names = array();
		
		$limit = 100;

		$names = searchDraftByName($searchText, $objectClass , $dataSourcekey, $limit);

		if (isset($names) && $names) 
		{ 		
			foreach ($names as $i => $value) 
			{   	
				$values[] = array (	"value" => $value['registry_object_key'], "desc" => $value['display_title']." (".$value['status'].")");       		    	
			}			
		} else {
			//$values[] = array (	"value" => "", "desc" => "Sorry - No Registry Object found!");
		}

		//print json_encode($values);


//NEW - use SOLR
		$objectClass = strtolower($objectClass);
		$q = '+displayTitle:('.$searchText.') +class:('.$objectClass.')';
		if($dataSourcekey!='') $q.=' +ds_key:('.$dataSourcekey.')';
		$fields = array(
			'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>$limit, 'wt'=>'json',
			'fl'=>'key, displayTitle, description_value, description_type, status'
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
	
		foreach($decoded->response->docs as $d){
			$values[] = array (	"value" => $d->{'key'}, "desc" => $d->{'displayTitle'}.' ('.$d->{'status'}.')');
		}
	
		echo json_encode($values);
		
