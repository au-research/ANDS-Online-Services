<?php

class Registry_fetch extends CI_Model
{

	function transformExtrifToHTMLStandardRecord($extrif)
	{
		$xsl_args = array(
			'base_url' => base_url(),
		);

		return $this->_transformByXSL($extrif, 'extRif2view.xsl', $xsl_args);
	}

	function transformExtrifToHTMLPreview($extrif)
	{
		$xsl_args = array(
			'base_url' => base_url(),
		);

		return $this->_transformByXSL($extrif, 'extRif2preview.xsl', $xsl_args);
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
		else if (isset($contents['previously_valid_title']))
		{
			// Should throw a soft 404...
			throw new SlugNoLongerValidException($contents['previously_valid_title']);
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
			throw new ErrorException("Error whilst fetching registry object: " . $contents['message']);
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

	function fetchAncestryGraphBySLUG($slug)
	{
		$url = $this->config->item('registry_endpoint') . "getAncestryGraph/?slug=" . $slug;
		$contents = json_decode(file_get_contents($url), true);
		if (isset($contents['trees']))
		{
			return $contents['trees'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connection graph: " . $contents['message']);
		}
	}

	function fetchAncestryGraphByID($id)
	{
		$url = $this->config->item('registry_endpoint') . "getAncestryGraph/?registry_object_id=" . $id;
		$contents = json_decode(file_get_contents($url), true);
		if (isset($contents['trees']))
		{
			return $contents['trees'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connection graph: " . $contents['message']);
		}
	}

}

class SlugNoLongerValidException extends Exception {}