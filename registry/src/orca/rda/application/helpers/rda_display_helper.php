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
		case "group":$clear = 'clearGroup';$name='Contributed by';$class="groupFilter";break;
		case "subject_value_resolved":$clear = 'clearSubjects';$name="Subjects";$class="subjectFilter";break;
		case "licence_group":$clear = 'clearLicence';$name="Licence&nbsp;<a href='http://www.ands.edu.au/guides/cpguide/cpgrights.html' target='_blank'><img src='".base_url()."img/question_mark.png' style='position:absolute;margin-top:7px;'/></a>";$class="licenceFilter";break;
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
	
	$order['licence_group'] = array("Open Licence", "Non-Commercial Licence", "Non-Derivative Licence", "Restrictive Licence", "No Licence", "Unknown");
	
	$result = array();
	for($i=0;$i< sizeof($object_type)-1 ;$i=$i+2){
		if($object_type[$i+1]>0){
			if($object_type[$i]!=$facetFilter){

				$result[$object_type[$i]] = '<li class="limit">

					<a href="javascript:void(0);"
						title="'.$object_type[$i].' ('.number_format($object_type[$i+1]).''.' results)"
						class="'.$class.'" id="'.$object_type[$i].'">'.
						''.$object_type[$i].' ('.number_format($object_type[$i+1]).')'.'
						</a></li>';
			}

		}
	}


	if($facet_name=='licence_group'){
		foreach($order[$facet_name] as $o){
			if(isset($result[$o])) echo $result[$o];
		}
	}else{
		foreach($result as $r){
			echo $r;

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
		case "subject_value_resolved":$clear = 'clearSubjects';$name="Subjects";$class="subjectFilter";break;
		case "subject_vocab_uri":$clear = 'clearSubjects';$name="Subjects";$class="subjectFilter";break;
		case "licence_group":$clear = 'clearLicence';$name="Licence";$class="licenceFilter";break;
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
		case 'subject_value_resolved':
			// ~ include narrower terms (drill-down mode)
			if (strpos($groups, "~") !== FALSE)
			{
				$str='+broader_subject_value_resolved:(';
			}
			else
			{
				$str='+subject_value_resolved:(';
			}
		break;
		case 'subject_vocab_uri':
			// ~ include narrower terms (drill-down mode)
			if (strpos($groups, "~") !== FALSE)
			{
				$str='+broader_subject_vocab_uri:(';
			}
			else
			{
				$str='+subject_vocab_uri:(';
			}
			break;
		case 'licence_group':$str='+licence_group:(';break;
		case 'status':$str='+status:(';break;
	}

	$classes = explode(';',$groups);
	$first = true;
	foreach($classes as $c){
		$c = str_replace("~","",$c);
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
	//return getHTTPs($orca).$orca_service;
	return $orca.$orca_service;
}

/*
 * view_url
 * gives the ORCA view url that the view page will link to
 * Basically returns a string
 */
function view_url(){
	$ci =& get_instance();
	return $ci->config->item('orca_url').$ci->config->item('orca_view_point');
}

function assets_url()
{
	$CI=&get_instance();
	return $CI->config->item('asset_url');
}


function solr_url()
{
	$CI=&get_instance();
	return $CI->config->item('solr_url');
}



/*Get response from a http request*/
function get_http_response_code($url) {
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}

/* Check to see if a given group has an assigned institutional page */
function getInstitutionPage($group)
{


	$CI =& get_instance();

	$CI->load->database();
	$query = $CI->db->get_where('dba.tbl_institution_pages',array('object_group'=>$group));
	if($query){
		foreach($query->result() as $row)
		{
			if($row->registry_object_key)
			{
				$query = $CI->db->get_where('dba.tbl_registry_objects',array('registry_object_key'=>$row->registry_object_key,'status'=>'PUBLISHED'));
				foreach($query->result() as $row)
				{
					return $row->registry_object_key;
				}
			}else{
				return false;
			}
			//return $row->registry_object_key;
		}

	}else{
		return false;
	}
}

function getPageLogo($key){
	$CI =& get_instance();
	$CI->load->database();
	$query = $CI->db->select("value")->get_where("dba.tbl_descriptions", array("registry_object_key" => $key,"type" => 'logo'));
	if ($query->num_rows() == 0)
	{
		return false;
	}else{
		$query = $query->row();
		$value  = $query->value;
		return strip_tags($value);

	}

}
function displaySubscriptions(){//for now we only want to set up the subscriptions for search resluts on the collections tab
	if($_POST['classFilter']=='collection')
	{
		return true;
	}else{
		return false;
	}

}

/*
 * Take a vocab term uri (http://purl.org/au-research/vocab/...)
 * and try to resolve it back to a prefLabel (& optional notation)
 * the mapped vocabulary services in global_config.php
 */
function resolveLabelFromVocabTermURI($vocabTermUri, $withNotation=true)
{
	global $gVOCAB_RESOLVER_SERVICE;
	$resolution_target = false;

	$return_string = rawurlencode($vocabTermUri); // if no results, return the URI

	foreach ($gVOCAB_RESOLVER_SERVICE AS $resolver)
	{
		if (strpos($vocabTermUri, $resolver['uriprefix']) === 0)
		{
			$resolution_target = $resolver['resolvingService'] . "resource.json?uri=" . rawurlencode($vocabTermUri);
		}
	}

	if ($resolution_target)
	{
		$contents = file_get_contents($resolution_target);
		if ($contents)
		{
			$contents = json_decode($contents,true);
			if ($contents)
			{
				if (isset($contents['result']['primaryTopic']['prefLabel']['_value']))
				{
					$return_string = $contents['result']['primaryTopic']['prefLabel']['_value'];
				}
				if (isset($contents['result']['primaryTopic']['notation']) && $withNotation)
				{
					$return_string .= " (" . $contents['result']['primaryTopic']['notation'] . ")";
				}
			}
		}
	}
	return $return_string;
}

function cmpTopLevelFacet($a, $b) {
    if ($a['prefLabel'] == $b['prefLabel']) {
        return 0;
    }
    return ($a['prefLabel'] < $b['prefLabel']) ? -1 : 1;
}

/*
 * Take a vocab term uri (http://purl.org/au-research/vocab/...)
 * and try to resolve it back to a prefLabel (& optional notation)
 * the mapped vocabulary services in global_config.php
 */
function resolveLabelFromVocabNotation($vocabNotation)
{
	$resolvedVocab = resolveFromVocabNotation($vocabNotation);
	if (isset($resolvedVocab['prefLabel']['_value']))
	{
		return $resolvedVocab['prefLabel']['_value'];
	}
	else
	{
		return false;
	}
}

function resolveFromVocabNotation($vocabNotation)
{
	global $gVOCAB_RESOLVER_SERVICE;

	foreach ($gVOCAB_RESOLVER_SERVICE AS $resolver)
	{
		$resolution_target = $resolver['resolvingService'] . "concept.json?notation=" . rawurlencode($vocabNotation);
		$contents = json_decode(file_get_contents($resolution_target),true);
		if ($contents)
		{
			if (isset($contents['result']['items']) && count($contents['result']['items']) > 0)
			{
				return $contents['result']['items'][0];
			}
		}
				
	}

	return false;
}

?>
