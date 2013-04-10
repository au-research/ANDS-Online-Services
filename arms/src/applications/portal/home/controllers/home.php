<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MX_Controller {

	function index(){
		ini_set('xdebug.profiler_enable',1);
		$data['title']='Research Data Australia';

		//solr for counts
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		//$this->solr->setOpt('fq', 'status:PUBLISHED');
		$this->solr->setOpt('rows','0');
		$this->solr->setFacetOpt('field', 'class');
		$this->solr->setFacetOpt('field', 'group');
		$this->solr->executeSearch();

		//classes
		$classes = $this->solr->getFacetResult('class');
		$data = array('collection'=>0,'service'=>0,'activity'=>0,'party'=>0);
		foreach($classes as $class=>$num){
			$data[$class] = $num;
		}

		//groups
		$groups = $this->solr->getFacetResult('group');
		$data['groups'] = array();
		foreach($groups as $group=>$num){
			$data['groups'][$group] = $num;
		}

		$this->load->library('stats');
		$this->stats->registerPageView();
		//spotlights
		
		$data['scripts'] = array('home_page');
		$data['js_lib'] = array('qtip');
		$this->load->view('home', $data);
	}

	function contributors(){
		//solr for counts
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		//$this->solr->setOpt('fq', 'status:PUBLISHED');
		$this->solr->setOpt('rows','0');
		$this->solr->setFacetOpt('field', 'class');
		$this->solr->setFacetOpt('field', 'group');
		$this->solr->executeSearch();

		//groups
		$groups = $this->solr->getFacetResult('group');
		$data['groups'] = array();
		foreach($groups as $group=>$num){
			$data['groups'][$group] = $num;
		}

		$this->load->library('stats');
		$this->stats->registerPageView();

		$this->load->view('who_contributes', $data);
	}

	function about(){
		$data['title'] = 'Research Data Australia - About';
		$this->load->view('about', $data);
	}

	function contact(){
			//	$data['scripts'] = array('home_page');
		$data['title'] = 'Research Data Australia - Contact Us';
		$this->load->view('contact', $data);
	}

public function send(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$content = $this->input->post('content');

		$this->load->library('email');

		$this->email->from($email, $name);
		$this->email->to('services@ands.org.au');
		$this->email->subject('RDA Contact Us');
		$this->email->message($content);

		$this->email->send();

		echo '<p> </p><p>Thank you for your response. Your message has been delivered successfully</p><p> </p><p> </p><p> </p><p> </p><p> </p><p> </p><p> </p>';
	}
}