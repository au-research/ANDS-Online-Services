<?php
class Solr extends CI_Model {
	function __construct(){
        parent::__construct();
    }

    /*
     * Fire a search, given an array of fields and a string of facets
     */
	function fireSearch($fields, $facet='',$as_array=false){
		/*prep*/
		$fields_string='';
		//foreach($fields as $key=>$value) { $fields_string .= $key.'='.str_replace("+","%2B",$value).'&'; }//build the string
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}//build the string
    	$fields_string .= $facet;//add the facet bits

    	$ch = curl_init();
    	$solr_url = $this->config->item('solr_url');
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
		curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl

    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl

		$json = json_decode($content,$as_array);
		if($json){
			return $json;
		}else{
			throw new Exception('SOLR Query failed....ERROR:'.$content.'<br/> QUERY: '.$fields_string);
		}
    }
}