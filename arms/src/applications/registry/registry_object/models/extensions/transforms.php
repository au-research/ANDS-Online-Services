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
			$roles = $sxml->xpath('//AuthorRole[@postproc="1"]');
			foreach ($roles AS $i => $role)
			{
				// Change the value of the relation to be human-readable
				$roles[$i][0] = format_relationship("collection",(string)$roles[$i],'EXPLICIT');
				// Remove the "to-process" marker
				unset($roles[$i]["postproc"]);
			}

			// Post-process the ResearcherID element
			$roles = $sxml->xpath('//ResearcherID[@postproc="1"]');
			foreach ($roles AS $i => $role)
			{
				//$this->_CI->load->model('data_source/data_sources','ds');
				$researcher_object = $this->_CI->ro->getPublishedByKey((string)$roles[$i][0]);
				if ($researcher_object && $researcher_sxml = $researcher_object->getSimpleXML())
				{
					$orcids = $researcher_sxml->xpath('//ro:identifier[@type="orcid"]');

					if (count($orcids))
					{
						$roles[$i][0] = "http://orcid.org/" . $orcids[0][0];
						unset($roles[$i]["postproc"]);
					}
					else
					{
						unset($roles[$i][0]);
					}
				}
				else
				{
					unset($roles[$i][0]);
				}
			}

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
	