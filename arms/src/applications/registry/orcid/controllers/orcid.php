<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 * ORCID base controller for the orcid integration process
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au> 
 */
class Orcid extends MX_Controller {

	/**
	 * Base Method, requires the user to login
	 * @return view 
	 */
	function index(){
		$data['title'] = 'Login to ORCID';
		$data['js_lib'] = array('core');
		$data['link'] = $this->config->item('gORCID_SERVICE_BASE_URI').'oauth/authorize?client_id='.$this->config->item('gORCID_CLIENT_ID').'&response_type=code&scope=/orcid-profile/read-limited /orcid-works/create&redirect_uri=';
		$data['link'].=registry_url('orcid/auth');
		// $data['link'].='http://devl.ands.org.au/workareas/minh/ands/arms/src/registry/orcid/auth';
		// $data['link'].='https://developers.google.com/oauthplayground';
		$this->load->view('login_orcid', $data);
	}

	/**
	 * REDIRECT URI set to this method, process the user and provide the relevant view
	 * @return view 
	 */
	function auth(){
		$this->load->library('Orcid_api', 'orcid');
		if($this->input->get('code')){
			$code = $this->input->get('code');
			$data = json_decode($this->orcid_api->oauth($code),true);
			
			
			if(isset($data['access_token'])){
				// var_dump($data);
				$this->orcid_api->set_access_token($data['access_token']);
				$this->orcid_api->set_orcid_id($data['orcid']);
				$bio = $this->orcid_api->get_full();
				$bio = json_decode($bio, true);
				$this->wiz($bio);
			}else{

				// var_dump($data);
				// redirect(registry_url('orcid'));
				if($access_token = $this->orcid_api->get_access_token()){
					// var_dump($this->orcid_api->get_orcid_id());
					$bio = $this->orcid_api->get_full();
					$bio = json_decode($bio, true);
					$this->wiz($bio);
				}else{
					redirect(registry_url('orcid'));
				}
			}
		}else{
			if($access_token = $this->orcid_api->get_access_token()){
				$bio = $this->orcid_api->get_full();
				$bio = json_decode($bio, true);
				$this->wiz($bio);
			}else{
				redirect(registry_url('orcid'));
			}
		}
	}

	/**
	 * returns the orcid xml
	 * @param array(ro_id)
	 * @return xml 
	 */
	function get_orcid_xml(){
		$ro_ids = $this->input->post('ro_ids');
		$this->load->model('registry_object/registry_objects', 'ro');
		$xml = '';
		foreach($ro_ids as $id){
			$ro = $this->ro->getByID($id);
			if($ro){
				$xml .= $ro->transformToORCID();
			}
		}
		$msg =  '<?xml version="1.0" encoding="UTF-8"?><orcid-message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.orcid.org/ns/orcid http://orcid.github.com/ORCID-Parent/schemas/orcid-message/1.0.7/orcid-message-1.0.7.xsd" xmlns="http://www.orcid.org/ns/orcid"><message-version>1.0.7</message-version><orcid-profile><orcid-activites><orcid-works visibility="public">'.$xml.'</orcid-works></orcid-activites></orcid-profile></orcid-message>';
	echo '<?xml version="1.0" encoding="UTF-8"?>
<orcid-message
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.orcid.org/ns/orcid http://orcid.github.com/ORCID-Parent/schemas/orcid-message/1.0.7/orcid-message-1.0.7.xsd"
    xmlns="http://www.orcid.org/ns/orcid">
<message-version>1.0.7</message-version>
<orcid-profile>
  <orcid-activities>
    <orcid-works> 
      <orcid-work>
        <work-title>
          <title>Minhgy again</title>
          <subtitle>My Subtitle</subtitle>
        </work-title>
        <short-description>My Abstract</short-description>
        <work-citation>
          <work-citation-type>formatted-apa</work-citation-type>
          <citation>My correctly formatted citation</citation>
        </work-citation>
        <work-type>journal-article</work-type>
        <publication-date>
          <year>2010</year>
          <month>11</month>
        </publication-date>
        <work-external-identifiers>
          <work-external-identifier>
            <work-external-identifier-type>other-id</work-external-identifier-type>
            <work-external-identifier-id>1234</work-external-identifier-id>
          </work-external-identifier>
        </work-external-identifiers>
        <url>www.orcid.org</url>
        <work-contributors>
          <contributor>
            <credit-name>LastName, FirstName</credit-name>
            <contributor-attributes>
              <contributor-sequence>first</contributor-sequence>
              <contributor-role>author</contributor-role>
            </contributor-attributes>
          </contributor>
        </work-contributors>
      </orcid-work>
    </orcid-works>
  </orcid-activities>
</orcid-profile>
</orcid-message>';
// echo $msg;
	}

	/**
	 * Push an orcid xml to append to the orcid works profile
	 * @return true 
	 */
	function push_orcid_xml(){
		$this->load->library('Orcid_api');
		$xml = $this->input->post('xml');
		$result = $this->orcid_api->append_works($xml);
		if($result){
			echo '1';
		}else{
			echo json_encode($result);
		}
	}

	/**
	 * The wizard?
	 * @return view 
	 */
	function wiz($bio){
		$data['bio'] = $bio['orcid-profile'];
		$data['title']='Import Your Work';
		$data['scripts']=array('orcid_wiz');
		$data['js_lib']=array('core','prettyprint');

		// echo json_encode($data['bio']);
		$orcid_id = $data['bio']['orcid']['value'];
		$first_name = $data['bio']['orcid-bio']['personal-details']['given-names']['value'];
		$last_name = $data['bio']['orcid-bio']['personal-details']['family-name']['value'];
		$name = $first_name.' '.$last_name;

		$suggested_collections = array();

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->library('solr');

		//find parties of similar names
		$this->solr->setOpt('fq', '+class:party');
		$this->solr->setOpt('fq', '+display_title:('.$name.')');
		$this->solr->executeSearch();

		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			// echo json_encode($result);
			foreach($result->{'docs'} as $d){
				$ro = $this->ro->getByID($d->{'id'});
				$connections = $ro->getConnections(true,'collection');
				// var_dump($connections[0]['collection']);
				if(sizeof($connections[0]['collection']) > 0) {
					$suggested_collections=array_merge($suggested_collections, $connections[0]['collection']);
				}
				unset($ro);
			}
		}

		// echo json_encode($suggested_collections);

		//find parties that have the same orcid_id
		$this->solr->clearOpt('fq');
		$this->solr->setOpt('fq', '+class:party');
		$this->solr->setOpt('fq', '+identifier_value:("'.$orcid_id.'")');
		$this->solr->executeSearch();
		if($this->solr->getNumFound() > 0){
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d){
				$ro = $this->ro->getByID($d->{'id'});
				$connections = $ro->getConnections(true,'collection');
				if(sizeof($connections[0]['collection']) > 0) {
					$suggested_collections=array_merge($suggested_collections, $connections[0]['collection']);
				}
				unset($ro);
			}
		}
		
		// echo sizeof($suggested_collections);
		
		$data['name'] = $name;
		$data['orcid_id'] = $orcid_id;
		$data['suggested_collections'] = $suggested_collections;
		$this->load->view('orcid_wiz', $data);
	}
}
	