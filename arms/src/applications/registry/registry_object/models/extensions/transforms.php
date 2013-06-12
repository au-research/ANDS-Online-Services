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
	