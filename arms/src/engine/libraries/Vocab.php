<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Vocab resolving using sissvoc to use globally
 * @author : <leo.monus@ands.org.au>
 */
class Vocab {

	private $CI;
	private $resolvingServices;
    private $resolvedArray;
    private $broaderArray;

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

            $content = $this->post($this->constructResorceUriString($resolvingService, $uriprefix, $term));
		    $json = json_decode($content, false);
    		if($json){
    			$this->result = $json;
                $this->setBroaderSubjects($resolvingService, $uriprefix, $term, $vocabType);
                $subject['value'] = $json->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
                $subject['about'] = $json->{'result'}->{'primaryTopic'}->{'_about'};
    			return  $subject;
    		}else{
    			$subject['value'] = $term;
                $subject['about'] = '';
                return $subject;
    		}
        }
        else{
            $subject['value'] = $term;
            $subject['about'] = '';
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
                    $prefLabel = $item->{'broader'}->{'prefLabel'}->{'_value'};
                    $about = $item->{'broader'}->{'_about'};
                    if(!array_key_exists($notation, $this->broaderArray))
                    {
                        $this->broaderArray[$notation] = array('type'=>$vocabType, 'value'=>$notation, 'resolved'=>$prefLabel , 'uri' => $about);
                    }
                }
                if(isset($item->{'notation'}))
                {
                    $notation = $item->{'notation'};
                    $prefLabel = $item->{'prefLabel'}->{'_value'};
                    $about = $item->{'_about'};
                    if(!array_key_exists($notation, $this->broaderArray))
                    {
                        $this->broaderArray[$notation] = array('type'=>$vocabType, 'value'=>$notation, 'resolved'=>$prefLabel, 'uri' => $about);
                    }

                } 
                          
            }
           // $this->getBroaderSubjects($resolvingService, $uriprefix, $term);
           // return $json->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
        }
    }

    function getBroaderSubjects()
    {
        return $this->broaderArray;
    }

}