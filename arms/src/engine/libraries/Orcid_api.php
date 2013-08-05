<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * ORCID class for use globally
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Orcid_api {

	private $api_uri = null;
    private $service_uri = null;
    private $client_id = null;
    private $client_secret = null;
    private $redirect_uri = null;
    private $access_token = null;
    private $orcid_id = null;

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->init();
    }

    function init(){
        $this->service_uri = $this->CI->config->item('gORCID_SERVICE_BASE_URI');
        $this->api_uri = $this->CI->config->item('gORCID_API_URI');
        $this->client_id = $this->CI->config->item('gORCID_CLIENT_ID');
        $this->client_secret = $this->CI->config->item('gORCID_CLIENT_SECRET');
        $this->redirect_uri = registry_url('orcid/auth');
    }

    /**
     * Authenticate with the API service using oauth
     * @param  string $code auth_code
     * @return data       
     */
    function oauth($code){
        $post_array = array(
            'client_id'=>$this->client_id,
            'client_secret'=>$this->client_secret,
            'grant_type'=>'authorization_code',
            'code'=>$code,
            'redirect_uri'=>$this->redirect_uri
        );
        $post_string = http_build_query($post_array);
        $url = $this->api_uri.'oauth/token';
        $data = curl_post($url, $post_string, array('Accept: application/json'));
        return $data;
    }

    function set_orcid_id($id){
        $this->orcid_id = $id;
        $this->CI->session->set_userdata('orcid_id', $id);
    }

    function get_orcid_id(){
        if($this->orcid_id){
            return $this->orcid_id;
        }else{
            if($this->CI->session->userdata('orcid_id')){
                return $this->CI->session->userdata('orcid_id');
            }else{
                return false;
            }
        }
        return false;
    }

    function set_access_token($token){
        $this->access_token = $token;
        $this->CI->session->set_userdata('access_token', $token);
    }

    function get_access_token(){
        if($this->access_token){
            return $this->access_token;
        }else{
            if($this->CI->session->userdata('access_token')){
                return $this->CI->session->userdata('access_token');
            }else{
                return false;
            }
        }
        return false;
    }

    /**
     * Get orcid XML of orcid id, if access_token is not set, it will return public information
     * @return object_xml         
     */
    function get_full(){
        $opts = array(
            'http'=>array(
                'method'=>'GET',
                'header'=>'Accept: application/orcid+json'
            )
        );
        if(!$this->get_orcid_id() && !$this->get_access_token()){
            return false;
        }else{
            $url = $this->api_uri.$this->get_orcid_id().'/orcid-profile/';
            $context = stream_context_create($opts);
            if($this->get_access_token()) $url.='?access_token='.$this->get_access_token();
            $result = file_get_contents($url, true, $context);
            return $result;
        }
    }

    
}