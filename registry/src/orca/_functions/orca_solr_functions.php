<?php
/*
Copyright 2012 The Australian National University
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

// SOLR operations on the ORCA database.

function solr($solr_url, $fields, $extras=""){
	//prep
	$fields_string='';
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	rtrim($fields_string,'&');
	if($extras!="") $fields_string .= $extras;

	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
	curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	$content = curl_exec($ch);//execute the curl
	return $content;
}
?>