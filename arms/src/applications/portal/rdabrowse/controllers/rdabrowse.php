<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rdabrowse extends MX_Controller {

	var $image_base_url;
	public function index()
	{
		$data['title']='Research Data Australia';

		$data['vocabularies'] = $this->getVocabTree();
		$data['image_base_url'] = $this->image_base_url;
		$this->renderRdaBrowsePage();
	}




	private function renderRdaBrowsePage()
	{	
		$data['title']='Research Data Australia';
		$data['js_lib'] = array('vocab_widget');
		$data['scripts'] = array('rdabrowse');

		$data['vocabs'] = $this->getVocabTree();

		if ($this->input->get('subject'))
		{
			$data['result_list'] = $this->registry->fetchResultsBySubject($this->input->get('subject'));

		}
				

		$data['resultsDiv'] = $this->load->view('list_results', $data, true);	
		$this->load->view('list_vocabs', $data);

	}


	private function getVocabTree()
	{
		$vocabs = array();
	//	$data_file = json_decode(file_get_contents($this->config->item('topics_datafile')), true);
	//	if ($data_file && isset($data_file['topics']))
	//	{
	//		$topics = $data_file['topics'];
	//		$this->image_base_url = $data_file['image_url'];
	//	}
	//	else
	//	{
	//		throw new Exception("No topics could be loaded from the topics datafile.");
	//	}

		return $vocabs;
	}

}