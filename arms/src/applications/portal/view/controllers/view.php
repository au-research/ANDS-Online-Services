<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia';
		$data['js_lib'] = array('googleapi');

		if (!$this->input->get('slug') && !$this->input->get('id'))
		{
			throw new Exception("Registry object could not be located (no SLUG or ID specified!)");
		}

		$this->load->model('registry_fetch','registry');
		if ($this->input->get('slug'))
		{
			$extRif = $this->registry->fetchExtRifBySlug($this->input->get('slug'));
			$connections = $this->registry->fetchConnectionsBySlug($this->input->get('slug'));
		}
		else if ($this->input->get('id'))
		{
			$extRif = $this->registry->fetchExtRifByID($this->input->get('id'));
			$connections = $this->registry->fetchConnectionsByID($this->input->get('id'));
		}
		$data['connections_contents'] = $connections;
		$connDiv = $this->load->view('connections', $data, true);

		$data['registry_object_contents'] = $this->registry->transformExtrifToHTMLStandardRecord($extRif);
		$data['registry_object_contents'] = str_replace('%%%%CONNECTIONS%%%%', $connDiv, $data['registry_object_contents']);
		

		$this->load->view('view', $data);

	}

}