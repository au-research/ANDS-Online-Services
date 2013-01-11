<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Vocab resolving using sissvoc to use globally
 * @author : <leo.monus@ands.org.au>
 */
class Vocab {

	private $CI;
	private $resolvingServices;
    private $resolvedArray;

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
		$this->init();
    }

    /**
     * Initialize the solr class ready for call
     * @return [type] [description]
     */
    function init(){
        $this->resolvingServices = $this->CI->config->item('vocab_resolving_services');
    	$this->resolvedArray = array();
        $this->broaderArray = array();
    	return true;
    }

	function resolveSubject($term, $vocabType){
		
        if($vocabType != '' && array_key_exists($vocabType, $this->resolvingServices))
        {
            $resolvingService = $this->resolvingServices[$vocabType]['resolvingService'];
            $uriprefix = $this->resolvingServices[$vocabType]['uriprefix'];

            if(isset($this->resolvedArray[$uriprefix][$term]))
            {
                return $this->resolvedArray[$uriprefix][$term];
            }
            else
            {
                $content = $this->post($this->constructResorceUriString($resolvingService, $uriprefix, $term));
    		    $json = json_decode($content, false);
        		if($json){
        			$this->result = $json;
                    
                    $subject['uriprefix'] = $uriprefix;
                    $subject['notation'] = $term;
                    $subject['value'] = $json->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
                    $subject['about'] = $json->{'result'}->{'primaryTopic'}->{'_about'};
                    $this->resolvedArray[$uriprefix][$term] = $subject;
                    $this->resolvedArray[$uriprefix][$term]['broaderTerms'] = array();
                    $this->setBroaderSubjects($resolvingService, $uriprefix, $term, $vocabType);
        			return  $subject;
        		}else{
        			$subject['uriprefix'] = $uriprefix;
                    $subject['notation'] = $term;
                    $subject['value'] = $term;
                    $subject['about'] = '';
                    $this->resolvedArray[$uriprefix][$term] = $subject;
                    return $subject;
        		}
            }
        }
        elseif(isset($this->resolvedArray['non-resolvable'][$term]))
        {
            return $this->resolvedArray['non-resolvable'][$term];
        }
        else
        {
            $subject['uriprefix'] = 'non-resolvable';
            $subject['notation'] = $term;
            $subject['value'] = $term;
            $subject['about'] = '';
            $this->resolvedArray['non-resolvable'][$term] = $subject;
            return $subject;
        }
	}

    function post($queryStr){
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL,$queryStr);//post to SOLR
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
        $content = curl_exec($ch);//execute the curl
        curl_close($ch);//close the curl
        return $content;
    }

    function constructResorceUriString($resolvingService, $uriprefix, $term){
        $resourceQueryComp = 'resource.json?uri=';
        $uri = $resolvingService.$resourceQueryComp.urlencode($uriprefix.$term);
        return $uri;
    }

    function constructBroaderUriString($resolvingService, $uriprefix, $term){
        $broaderQueryComp = 'allBroader.json?uri=';
        $uri = $resolvingService.$broaderQueryComp.urlencode($uriprefix.$term);
        return $uri;
    }


    function setBroaderSubjects($resolvingService, $uriprefix, $term, $vocabType)
    {
        $content = $this->post($this->constructBroaderUriString($resolvingService, $uriprefix, $term));
        $json = json_decode($content, false);
        if($json){
            $this->result = $json;
            foreach($json->{'result'}->{'items'} as $item)
            {               
                if(isset($item->{'broader'}))
                {
                    
                    $notation = $item->{'broader'}->{'notation'};
                    $subject['notation'] = $notation;
                    $subject['uriprefix'] = $uriprefix;
                    $subject['value'] = $item->{'broader'}->{'prefLabel'}->{'_value'};
                    $subject['about'] = $item->{'broader'}->{'_about'};
                    $this->resolvedArray[$uriprefix][$term]['broaderTerms'][] = $notation;
                    if(!isset($this->resolvedArray[$uriprefix][$notation]))
                    {
                        $this->resolvedArray[$uriprefix][$notation] = $subject;
                        $this->resolvedArray[$uriprefix][$notation]['broaderTerms'] = array();
                    }
                }
                if(isset($item->{'notation'}))
                {
                    $notation = $item->{'notation'};
                    $subject['notation'] = $notation;
                    $subject['uriprefix'] = $uriprefix;
                    $subject['value'] = $item->{'prefLabel'}->{'_value'};
                    $subject['about'] = $item->{'_about'};
                    $this->resolvedArray[$uriprefix][$term]['broaderTerms'][] = $notation;
                    if(!isset($this->resolvedArray[$uriprefix][$notation]))
                    {
                        $this->resolvedArray[$uriprefix][$notation] = $subject;
                        $this->resolvedArray[$uriprefix][$notation]['broaderTerms'] = array();
                    }
                } 
                          
            }
        }
    }

    function getBroaderSubjects($uriprefix, $term)
    {
        $result = array();
        //echo $uriprefix."----".$term;
        if( isset($this->resolvedArray[$uriprefix][$term]) && isset($this->resolvedArray[$uriprefix][$term]['broaderTerms']))
        {
            $broaderTerms = $this->resolvedArray[$uriprefix][$term]['broaderTerms'];
            //var_dump($broaderTerms);
            foreach($broaderTerms as $broaderTerm)
            {
                if(isset($this->resolvedArray[$uriprefix][$broaderTerm]))
                {
                    $broader = $this->resolvedArray[$uriprefix][$broaderTerm];
                    $result[$broaderTerm] = $this->resolvedArray[$uriprefix][$broaderTerm];
                }
            }
        }
        return $result;
    }

}