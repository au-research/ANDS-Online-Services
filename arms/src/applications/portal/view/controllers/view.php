<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia';
		$data['js_lib'] = array('dynatree','qtip');

		$suggested_links = array();
		if (!$this->input->get('slug') && !$this->input->get('id'))
		{
			throw new Exception("Registry object could not be located (no SLUG or ID specified!)");
		}

		$this->load->model('registry_fetch','registry');
		if ($this->input->get('slug'))
		{
			try
			{
				$extRif = $this->registry->fetchExtRifBySlug($this->input->get('slug'));
			}
			catch (SlugNoLongerValidException $e)
			{
				$this->load->view('soft404', array('previously_valid_title'=>$e->getMessage()));
				return;
			}

			$connections = $this->registry->fetchConnectionsBySlug($this->input->get('slug'));
			$suggested_links['identifiers'] = $this->registry->fetchSuggestedLinksBySlug($this->input->get('slug'), "ands_identifiers",0 ,0);
			$suggested_links['subjects'] = $this->registry->fetchSuggestedLinksBySlug($this->input->get('slug'), "ands_subjects",0 ,0);
		}
		else if ($this->input->get('id'))
		{
			$extRif = $this->registry->fetchExtRifByID($this->input->get('id'));
			$connections = $this->registry->fetchConnectionsByID($this->input->get('id'));
			$suggested_links['identifiers'] = $this->registry->fetchSuggestedLinksByID($this->input->get('id'), "ands_identifiers");
			$suggested_links['subjects'] = $this->registry->fetchSuggestedLinksByID($this->input->get('id'), "ands_subjects");
		}

		$data['connections_contents'] = $connections;
		$connDiv = $this->load->view('connections', $data, true);

		$data['suggested_links_contents'] = $suggested_links;
		$suggestedLinksDiv = $this->load->view('suggested_links', $data, true);

		$data['registry_object_contents'] = $this->registry->transformExtrifToHTMLStandardRecord($extRif);
		$data['registry_object_contents'] = str_replace('%%%%CONNECTIONS%%%%', $connDiv, $data['registry_object_contents']);
		$data['registry_object_contents'] = str_replace('%%%%ANDS_SUGGESTED_LINKS%%%%', $suggestedLinksDiv, $data['registry_object_contents']);

		$this->load->view('view', $data);

	}

	/* This preview widget is embedded in qtips popups */
	/* Note: do not use exceptions as this will override screen
			 styles and produce an undesirable error effect */
	function preview(){
		$this->load->model('registry_fetch','registry');

		if ($this->input->get('slug'))
		{

			try
			{
				$extRif = $this->registry->fetchExtRifBySlug($this->input->get('slug'));
				$html = $this->registry->transformExtrifToHTMLPreview($extRif); 
			}
			catch (SlugNoLongerValidException $e)
			{
				die("Registry object could not be located (perhaps it no longer exists!)");
			}
		}
		else if ($this->input->get('registry_object_id')) {
			try
			{
				$extRif = $this->registry->fetchExtRifByID($this->input->get('registry_object_id'));
				$html = $this->registry->transformExtrifToHTMLPreview($extRif);
			}
			catch (SlugNoLongerValidException $e)
			{
				die("Registry object could not be located (perhaps it no longer exists!)");
			}
		}
		else if ($this->input->post('roIds')) {
			try
			{
				$html = '';
				foreach($this->input->post('roIds') as $roID)
				{
					$extRif = $this->registry->fetchExtRifByID($roID);
					$html .= $this->registry->transformExtrifToHTMLPreview($extRif);
				}
			}
			catch (SlugNoLongerValidException $e)
			{
				die("Registry object could not be located (perhaps it no longer exists!)");
			}
		}
		else 
		{
			die("Registry object could not be located (no SLUG or ID specified!)");
		}

		$response = array(
			"slug" => $this->input->get('slug'),
			"registry_object_id" => $this->input->get('registry_object_id'),
			"html" => $html
		);

		echo json_encode($response);
	}

	function connectionGraph()
	{
		$this->load->model('registry_fetch','registry');
		if ($this->input->get('slug'))
		{
			echo json_encode($this->registry->fetchAncestryGraphBySlug($this->input->get('slug')));
		}
		else if ($this->input->get('id'))
		{
			echo json_encode($this->registry->fetchAncestryGraphByID($this->input->get('id')));
		}
	}

	function getSuggestedLinks($suggestor, $start, $rows)
	{
		$this->load->model('registry_fetch','registry');

		try 
		{
			if ($this->input->get('slug'))
			{
				echo json_encode($this->registry->fetchSuggestedLinksBySlug($this->input->get('slug'), 
																			$suggestor, $start, $rows));
			}
			else if ($this->input->get('id'))
			{

				echo json_encode($this->registry->fetchSuggestedLinksByID($this->input->get('id'),
																		 $suggestor, $start, $rows));
			}
		} catch (Exception $e)
		{
			echo json_encode(array("status"=>"error","message"=>$e->getMessage()));
		}

	}

}