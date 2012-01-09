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
***************************************************************************
*
**/ 
?>
<?php

/*
 * displayFacet
 * function prints out HTML
 * used in facet view
 */
function displayFacet($facet_name, $facetFilter, $json, $ro_class){
	
	$clear ='';$name = '';$class='';
	//echo $ro_class;
	switch($facet_name){
		case "type":$clear = 'clearType';
			if($ro_class!='All'){
				$name=ucfirst($ro_class).' Types';
			}else $name = 'Types';				
			$class="typeFilter";break;
		case "group":$clear = 'clearGroup';$name='Research Groups';$class="groupFilter";break;
		case "subject_value":$clear = 'clearSubjects';$name="Subjects";$class="subjectFilter";break;
	}
	

	
	echo '<div class="right-box shadow">';
	
	
	echo '<h2>'.$name;
	/*echo '<span class="toggle-facet-field">
			<img src="'.base_url().'img/sort-alpha.png" id="'.$facet_name.'-facetSort" class="toggle-facet-sort"/>
			</span>';*/
	echo '</h2>';
	echo '<div class="facet-content">';

	echo '<ul class="more" id="'.$facet_name.'-facet">';
	$object_type = $json->{'facet_counts'}->{'facet_fields'}->{$facet_name};
	
	//print the others
	for($i=0;$i< sizeof($object_type)-1 ;$i=$i+2){
		if($object_type[$i+1]>0){
			if($object_type[$i]!=$facetFilter){
				echo '<li class="limit">
					<a href="javascript:void(0);" 
						title="'.$object_type[$i].' ('.number_format($object_type[$i+1]).''.' results)" 
						class="'.$class.'" id="'.$object_type[$i].'">'.
						''.$object_type[$i].' ('.number_format($object_type[$i+1]).')'.'
						</a></li>';
			} 
		}
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>';
}

/*
 * displayCustomiseOptions
 * Used in the display customise dialog box
 */ 
function displayCustomiseOptions($cookie){
	$CI =& get_instance();
	if($CI->input->cookie($cookie)!=''){
		if($CI->input->cookie('show_subjects')=='yes'){
			echo '<img id="'.$cookie.'" class="customise-option" src="'.base_url().'img/yes.png"';
		}else{
			echo '<img id="'.$cookie.'" class="customise-option" src="'.base_url().'img/no.png"';
		}
	}else{
		echo '<img id="'.$cookie.'" class="customise-option" src="'.base_url().'img/no.png"';
	}
}

/*
 * displaySelectedFacet
 * Used in facet view
 */ 
function displaySelectedFacet($facet_name, $facetFilter, $json){
	$clear ='';$name = '';$class='';
	switch($facet_name){
		case "type":$clear = 'clearType';$name='Types';$class="typeFilter";break;
		case "group":$clear = 'clearGroup';$name='Research Groups';$class="groupFilter";break;
		case "subject_value":$clear = 'clearSubjects';$name="Subjects";$class="subjectFilter";break;
	}
	$object_type = $json->{'facet_counts'}->{'facet_fields'}->{$facet_name};
	//print the selected
	for($i=0;$i< sizeof($object_type)-1 ;$i=$i+2){
		if($object_type[$i+1]>0){
			if($object_type[$i]==$facetFilter){
				echo '<li class="limit">
					<a href="javascript:void(0);" 
						title="'.$object_type[$i].' ('.number_format($object_type[$i+1]).''.' results)" 
						class="clearFilter '.$clear.'" id="'.$object_type[$i].'">'.
						''.$object_type[$i].' ('.number_format($object_type[$i+1]).')'.'
						</a></li>';
			}
		}
	}
}

/*
 * Construct a SOLR based filter query
 * Used in SOLR model
 */ 
function constructFilterQuery($class, $groups){
	$str='';
	switch($class){
		case 'class':$str='+class:(';break;
		case 'type':$str='+type:(';break;
		case 'group':$str='+group:(';break;
		case 'subject_value':$str='+subject_value:(';break;
		case 'status':$str='+status:(';break;
	}
	
	$classes = explode(';',$groups);
	$first = true;
	foreach($classes as $c){
		if(!$first){
			$str.=' OR "'.escapeSolrValue($c).'"';
		}else{
			$str.= '"'.escapeSolrValue($c).'"';
			$first = false;
		}
	}
	$str .=')';
    return $str;
}

/*
 * escapeSolrValue
 * escaping sensitive items in a solr query
 * encode afterwards (need check)
 */ 
function escapeSolrValue($string){
	//$string = urldecode($string);
    $match = array('\\','&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', '"', ';');
    $replace = array('\\\\','&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\*', '\\?', '\\:', '\\"', '\\;');
    $string = str_replace($match, $replace, $string);
    return urlencode($string);
}

/*
 * getDidYouMean
 * given a term, spits out didyoumean term
 * Used when no search result is being returned
 */ 
function getDidYouMean($term){
	$CI =& get_instance();
	$CI->load->model('Registryobjects', 'ro');
	return $CI->ro->didYouMean($term);
}

/*
 * array_to_json
 * Spits out a json object given a php array
 * Used in search suggestion
 */ 
/* following function obtained from http://jqueryui.com */
function  array_to_json( $array ){
  if( !is_array( $array ) ){
      return false;
  }
  $associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
  if( $associative ){
        $construct = array();
        foreach( $array as $key => $value ){
            // We first copy each key/value pair into a staging array,
            // formatting each key and value properly as we go.
            // Format the key:
            if( is_numeric($key) ){
                $key = "key_$key";
            }
            $key = "\"".addslashes($key)."\"";
            // Format the value:
            if( is_array( $value )){
                $value = array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = "\"".addslashes($value)."\"";
            }
            // Add to staging array:
            $construct[] = "$key: $value";
        }
        // Then we collapse the staging array into the JSON form:
        $result = "{ " . implode( ", ", $construct ) . " }";
    } else { // If the array is a vector (not associative):
        $construct = array();
        foreach( $array as $value ){
            // Format the value:
            if( is_array( $value )){
                $value = array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = "'".addslashes($value)."'";
            }
            // Add to staging array:
            $construct[] = $value;
        }
        // Then we collapse the staging array into the JSON form:
        $result = "[ " . implode( ", ", $construct ) . " ]";
    }
    return $result;
}


/*
 * getHTTPs
 * takes in a URL and spits out https form
 * Basically replace http with https
 */
function getHTTPs($uri){
	return str_replace('http', 'https', $uri);
}
/*
 * service_url
 * gives the ORCA service url that view page will use to get extended RIFCS
 * Basically returns a string
 */
function service_url(){
	$ci =& get_instance();
	$orca = $ci->config->item('orca_url');
	$orca_service = $ci->config->item('orca_service_point');
	return getHTTPs($orca).$orca_service;
}

/*
 * view_url
 * gives the ORCA view url that the view page will link to
 * Basically returns a string
 */
function view_url(){
	$ci =& get_instance();
	return getHTTPs($ci->config->item('orca_url')).$ci->config->item('orca_view_point');
}


/*Get response from a http request*/
function get_http_response_code($url) {
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}
?>