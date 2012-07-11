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
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Topic extends CI_Controller {

    public function __construct()
    {
         parent::__construct();
    }


	public function _remap($method, $params = array())
	{
	    return call_user_func_array(array($this, "index"), array($method));
	}


	public function index($topic="")
	{

		// yuck!
		$this->load->model('topics','t');
		$this->topics = $this->t->getTopics();

		parse_str($_SERVER['QUERY_STRING'], $_GET);


		if (!$topic || $topic == "topic" || $topic == "index")
		{
			// needed to load page
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();
			$data['topics'] = $this->topics;
			$data['content'] = $this->load->view('topic-list',$data,true);
			$this->load->view('xml-view', $data);
			return;
		}
		else if (!$this->topics[$topic])
		{
			show_404("");
		}

		$data['activity_name'] = 'topic';

		// improve? dynamic?
		$data['topic_name'] = $this->topics[$topic]['name'];
		$data['topic_boxes'] = $this->topics[$topic]['auto_boxes'];
		$data['manual_boxes'] = $this->topics[$topic]['manual_boxes'];

		// XXX; error checking?
		$data['html'] = $this->load->view('topics/' . $topic,array(),true);

		// needed to load page
		$this->load->library('user_agent');
		$data['user_agent']=$this->agent->browser();
		$data['content'] = "";
		$data['content'] .= $this->load->view('topic-view',$data,true);

		$this->load->view('xml-view', $data);
	}


	private $topics = array();
}
?>