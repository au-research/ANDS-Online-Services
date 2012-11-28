<?php

class Registry_fetch extends CI_Model
{

	function transformExtrifToHTMLStandardRecord($extrif)
	{
		$xsl_args = array(
			'base_url' => base_url(),
		);
		//print_pre(htmlentities($extrif));
		// XXX: Quick fix xmlns!!!
		//$extrif = str_replace("<registryObject ", "<registryObject xmlns=\"http://ands.org.au/standards/rif-cs/registryObjects\" ", $extrif);
		return $this->_transformByXSL($extrif, 'extRif2view.xsl', $xsl_args);
	}


	private function _transformByXSL ($XML, $xslt_filename, $args = array())
	{
		$xsl = new DomDocument();
		$document = new DomDocument();
		$document->loadXML($XML);
		$xsl->load(APP_PATH . 'view/_xsl/'. $xslt_filename);
		$proc = new XSLTProcessor();
		$proc->importStyleSheet($xsl);

		foreach ($args AS $arg_name => $arg)
		{
			$proc->setParameter('', $arg_name, $arg);
		}

		$transformResult = $proc->transformToXML($document);	
		return $transformResult;
	}

	


	function fetchExtrifBySlug($slug)
	{
		$url = $this->config->item('registry_endpoint') . "getRegistryObject/?slug=" . $slug;
		$contents = json_decode(file_get_contents($url), true);
		if (isset($contents['data']))
		{
			return $contents['data'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object: " . $contents['message']);
		}
	}

	function fetchExtrifByID($id)
	{
		$url = $this->config->item('registry_endpoint') . "getRegistryObject/?registry_object_id=" . $id;
		$contents = json_decode(file_get_contents($url), true);
		if (isset($contents['data']))
		{
			return $contents['data'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object: " . $contents['message']);
		}
	}

	function fetchConnectionsBySlug($slug)
	{
		$url = $this->config->item('registry_endpoint') . "getConnections/?slug=" . $slug;

		$contents = json_decode(file_get_contents($url), true);
		if (isset($contents['connections']))
		{
			return $contents['connections'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connections: " . $contents['message']);
		}
	}

	function fetchConnectionsByID($id)
	{
		$url = $this->config->item('registry_endpoint') . "getConnections/?registry_object_id=" . $id;
		$contents = json_decode(file_get_contents($url), true);
		if (isset($contents['connections']))
		{
			return $contents['connections'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connections: " . $contents['message']);
		}
	}

}