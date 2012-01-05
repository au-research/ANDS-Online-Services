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
**/ 
?>
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index(){
		$this->load->model('Solr');
		$data['json'] = $this->Solr->getNCRISPartners();
		
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		//echo $data['user_agent'];
		
		$this->load->view('home_page', $data);
	}
	
	public function about(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$this->load->view('content/about', $data);
	}
	
	public function disclaimer(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$this->load->view('content/disclaimer', $data);
	}
	
	public function help(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$this->load->view('content/help', $data);
	}
	
	public function contact(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$this->load->view('content/contact_form', $data);
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
		
		echo '<b>Thank you for your response. Your message has been delivered successfully</b>';
	}

	public function homepage(){
		$this->load->model('Solr');
		$data['json'] = $this->Solr->getNCRISPartners();
		$this->load->view('home_page', $data);
	}
	
	public function notfound(){
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['message']='Page not found!';
		$this->load->view('layout',$data);
	}
}