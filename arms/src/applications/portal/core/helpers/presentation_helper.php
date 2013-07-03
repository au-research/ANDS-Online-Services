<?php
/*
 * escapeSolrValue
 * escaping sensitive items in a solr query
 */
function escapeSolrValue($string){
    //$string = urldecode($string);
    $match = array('\\','&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', ';', '/');
    $replace = array('\\\\','&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\*', '\\?', '\\:', '\\;', '\\/');
    $string = str_replace($match, $replace, $string);

    if(substr_count($string, '"') % 2 != 0){
    	$string = str_replace('"', '\\"', $string);
    }

    return $string;
}