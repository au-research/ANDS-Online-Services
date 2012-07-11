<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
**/ 
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {
	public function index(){
		$url = $_SERVER['REQUEST_URI'];
		$query=explode('api?', $url);
		$query=$query[1];
		//echo $query;

		$ch = curl_init();
    	$solr_url = $this->config->item('solr_url');
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
		//curl_setopt($ch,CURLOPT_POST,5);//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$query);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl
    	
    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl
		echo $content;	

	}
}
?>