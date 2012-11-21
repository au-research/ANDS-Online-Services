<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * SOLR class for use globally
 * Search functionality
 * Index functionality
 */
class Solr {

	private $CI;
	private $solr_url;
	private $result;
	private $options;

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->init();
    }

    /**
     * Initialize the solr class ready for call
     * @return [type] [description]
     */
    function init(){
    	$this->solr_url = $this->CI->config->item('solr_url');
    	$this->options = array('q'=>'*:*','start'=>'0','indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>'10');
    	return true;
    }

    /**
     * Manually set the option for solr search
     * @param string $field 
     * @param string $value
     */
    function setOpt($field, $value){
    	$this->options[$field] = $value;
    	$this->executeSearch();
    }

    /**
     * Manually set the solr url
     * @param string $value http link for solr url, defaults to be the value in the config
     */
    function setSolrUrl($value){
    	$this->solr_url = $value;
    }

    /**
     * return the total numFound of the search result
     * @return integer
     */
    function getNumFound(){
    	return (int) $this->result->{'response'}->{'numFound'};
    }

    /**
     * get SOLR result header
     * @return array 
     */
    function getHeader(){
    	return $this->result->{'responseHeader'};
    }

    /**
     * get SOLR result response
     * @return array 
     */
    function getResult(){
    	return $this->result->{'reponse'};
    }

    
    /**
     * Sample simple search
     * @param  string $term a full text search on this term
     * @return array
     */
	function search($term){
		$this->options['q']='fulltext:'.$term;
		return $this->executeSearch();
	}

	/**
	 * Execute the search based on the given options
	 * @return array results
	 */
	function executeSearch($as_array = false){
		$fields_string='';
		foreach($this->options as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}//build the string

    	$ch = curl_init();
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$this->solr_url.'select');//post to SOLR
		//curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl

    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl

		$json = json_decode($content, $as_array);
		if($json){
			$this->result = $json;
			return $this->result;
		}else{
			throw new Exception('SOLR Query failed....ERROR:'.$content.'<br/> QUERY: '.$fields_string);
		}
	}
}