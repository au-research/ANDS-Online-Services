<?php

class Transforms_Extension extends ExtensionBase
{

	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		


	function transformForSOLR($add_tags = true)
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_solr_transformer();
			$dom = new DOMDocument();

			$dom->loadXML($this->ro->getExtRif());
			if ($add_tags)
			{
				return "<add>" . $xslt_processor->transformToXML($dom) . "</add>";
			}
			else
			{
				return  $xslt_processor->transformToXML($dom);
			}
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}


	function transformForQA($xml, $data_source_key = null)
	{
		try{
			$xslt_processor = Transforms::get_qa_transformer();
			$dom = new DOMDocument();
			$dom->loadXML($xml);
			$xslt_processor->setParameter('','dataSource', $data_source_key ?: $this->ro->data_source_key );
			$xslt_processor->setParameter('','relatedObjectClassesStr',$this->ro->getRelatedClassesString());
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}
	
	function transformForHtml($revision='', $data_source_key = null)
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_html_transformer();
			$dom = new DOMDocument();
			$dataSource = $this->ro->data_source_key;
			if($revision=='') {
				$dom->loadXML(wrapRegistryObjects($this->ro->getRif()));
			}else $dom->loadXML(wrapRegistryObjects($this->ro->getRif($revision)));
			$xslt_processor->setParameter('','dataSource', $data_source_key ?: $this->ro->data_source_key );
			return html_entity_decode($xslt_processor->transformToXML($dom));
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}
	
	
	
	function transformToDC()
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_dc_transformer();
			$dom = new DOMDocument();
			$dom->loadXML($this->ro->getExtRif());
			$xslt_processor->setParameter('','base_url',portal_url());
			return html_entity_decode($xslt_processor->transformToXML($dom));
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}


	function transformToDCI()
	{
		$this->_CI->load->helper('normalisation');

		try{
			$xslt_processor = Transforms::get_extrif_to_dci_transformer();
			$dom = new DOMDocument();
			$dom->loadXML($this->ro->getExtRif());
			$xslt_processor->setParameter('','dateProvided', date("Y-m-d"));
			$xml_output = $xslt_processor->transformToXML($dom);

			$dom = new DOMDocument;
			$dom->loadXML($xml_output);
			$sxml = simplexml_import_dom($dom);

			// Post-process the AuthorRole element
			$roles = $sxml->xpath('//Author[@postproc="1"]');
			foreach ($roles AS $i => $role)
			{
				// Remove the "to-process" marker

				unset($roles[$i]["postproc"]);
				// Change the value of the relation to be human-readable
				$role->AuthorRole[0] = format_relationship("collection",(string)$role->AuthorRole[0],'EXPLICIT');
				
				// Include identifiers and addresses for this author (if they exist in the registry)
				$researcher_object = $this->_CI->ro->getPublishedByKey((string)$role->ResearcherID[0]);
				if ($researcher_object && $researcher_sxml = $researcher_object->getSimpleXML())
				{
					// Handle the researcher IDs (using the normalisation_helper.php)
					$researcher_ids = $researcher_sxml->xpath('//ro:identifier');
					if (is_array($researcher_ids))
					{	
						$role->ResearcherID[0] = implode("\n", array_map('normaliseIdentifier', $researcher_ids));
						if ((string) $role->ResearcherID[0] == "")
						{
							unset($roles[$i]->ResearcherID[0]);
						}
					}
					else
					{
						unset($roles[$i]->ResearcherID[0]);
					}

					try
					{
						// Do we have an address? (using the normalisation_helper.php)
						$researcher_addresses = $researcher_sxml->xpath('//ro:location/ro:address');
						$address_string = "";
						if (is_array($researcher_addresses))
						{
							foreach($researcher_addresses AS $_addr)
							{
								if ($_addr->physical)
								{
									$address_string .= normalisePhysicalAddress($_addr->physical);
								}
								else if ($_addr->electronic)
								{
									$address_string .= (string) $_addr->electronic->value;
								}
							}
						}
						if ($address_string)
						{
							$role->AuthorAddress = $address_string;
						}
					}
					catch (Exception $e)
					{
						// ignore sloppy coding errors...SimpleXML is awful
					}
				}
				else
				{
					unset($roles[$i]->ResearcherID[0]);
				}
			}


			/* Post-process the Citations element
			$citations = $sxml->xpath('//CitationList[@postproc="1"]');
			foreach ($citations AS $i => $citations)
			{
				// Remove the "to-process" marker
				unset($citations[$i]["postproc"]);

				$role->ResearcherID[0] = implode("\n", array_map('normaliseIdentifier', $researcher_ids));
				if ((string) $role->ResearcherID[0] == "")
				{
					unset($roles[$i]->ResearcherID[0]);
				}
			}*/


			return trim(removeXMLDeclaration($sxml->asXML())) . NL;

		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}

	function transformToORCID()
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_orcid_transformer();
			$dom = new DOMDocument();
			$dom->loadXML($this->ro->getExtRif());
			$xslt_processor->setParameter('','dateProvided', date("Y-m-d"));
			$xslt_processor->setParameter('','rda_url', portal_url($this->ro->slug));
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}

	function transformCustomForFORM($rifcs){
		try{
			$xslt_processor = Transforms::get_extrif_to_form_transformer();
			$dom = new DOMDocument();
			$dom->loadXML($rifcs);
			$xslt_processor->setParameter('','base_url',base_url());
			return html_entity_decode($xslt_processor->transformToXML($dom));
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}

	function cleanRIFCSofEmptyTags($rifcs, $removeFormAttributes='true'){
		try{
			$xslt_processor = Transforms::get_form_to_cleanrif_transformer();
			$dom = new DOMDocument();
			//$dom->loadXML($this->ro->getXML());
			$dom->loadXML($rifcs);
			//$dom->loadXML($rifcs);
			$xslt_processor->setParameter('','removeFormAttributes',$removeFormAttributes);
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}

}
	