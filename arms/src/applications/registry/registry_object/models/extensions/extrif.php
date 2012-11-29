<?php

class Extrif_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
		include_once(APP_PATH."registry_object/models/_transforms.php");
	}		
	
	/*
	 * 	Extrif
	 */
	function enrich()
	{
		$this->_CI->load->model('data_source/data_sources','ds');		
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);
		//same as in relationships.php
		$xml = $this->ro->getSimpleXML();
		$attributes = $xml->attributes(EXTRIF_NAMESPACE);

		// Cannot enrich already enriched RIFCS!!
		if(true)//! (string) $attributes['enriched'])
		{
			$xml->addAttribute("extRif:enriched","true",EXTRIF_NAMESPACE);
			$xml->addAttribute("xmlns",RIFCS_NAMESPACE);
			if (count($xml->key) == 1)
			{
				/* EXTENDED METADATA CONTAINER */
				$extendedMetadata = $xml->addChild("extRif:extendedMetadata", NULL, EXTRIF_NAMESPACE);
				
				$extendedMetadata->addChild("extRif:status", $this->ro->status, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:slug", $this->ro->slug, EXTRIF_NAMESPACE);
				
				$extendedMetadata->addChild("extRif:id", $this->ro->id, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:dataSourceTitle", $ds->title, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:dataSourceKey", $this->ro->data_source_key, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:dataSourceID", $this->ro->data_source_id, EXTRIF_NAMESPACE);
									
	
				$extendedMetadata->addChild("extRif:displayTitle", $this->ro->title, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:listTitle", $this->ro->list_title, EXTRIF_NAMESPACE);
				
				$extendedMetadata->addChild("extRif:reverseLinks", $this->getReverseLinksStatusforEXTRIF($ds) , EXTRIF_NAMESPACE);
				
				$extendedMetadata->addChild("extRif:flag", ($this->ro->flag === DB_TRUE ? '1' : '0'), EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:error_count", $this->ro->error_count, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:warning_count", $this->ro->warning_count, EXTRIF_NAMESPACE);
				
				$extendedMetadata->addChild("extRif:manually_assessed_flag", ($this->ro->manually_assessed_flag === DB_TRUE ? '1' : '0'), EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:gold_status_flag", ($this->ro->gold_status_flag === DB_TRUE ? '1' : '0'), EXTRIF_NAMESPACE);
				
				$extendedMetadata->addChild("extRif:quality_level", $this->ro->quality_level, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:feedType", ($this->ro->created_who == 'SYSTEM' ? 'harvest' : 'manual'), EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:lastModifiedBy", $this->ro->created_who, EXTRIF_NAMESPACE);
				
				// XXX: TODO: Search base score, displayLogo
				$extendedMetadata->addChild("extRif:searchBaseScore", 100, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:displayLogo", NULL, EXTRIF_NAMESPACE);
				
				// xxx: spatial extents (sanity checking?)
				$spatialGeometry = $extendedMetadata->addChild("extRif:spatialGeometry", NULL, EXTRIF_NAMESPACE);
				foreach ($this->ro->getLocationAsLonLats() AS $lonLat)
				{
					//echo "enriching..." . $extent;
					$spatialGeometry->addChild("extRif:geometry", $lonLat, EXTRIF_NAMESPACE);
					$spatialGeometry->addChild("extRif:extent", $this->ro->calcExtent($lonLat), EXTRIF_NAMESPACE);
				}
				
				
				/* Names EXTRIF */
				$names = $xml->xpath('//'.$this->ro->class.'/name');
				
				foreach ($names AS $name)
				{
					$extrifName = $extendedMetadata->addChild("extRif:name", NULL, EXTRIF_NAMESPACE);
					$extrifName->addAttribute("extRif:type", (string) $name['type'], EXTRIF_NAMESPACE);
					$titles =  $this->ro->getTitlesForFragment($name, $this->ro->class);
					$extrifName->addChild("extRif:listTitle", $titles['list_title'], EXTRIF_NAMESPACE);
					$extrifName->addChild("extRif:displayTitle", $titles['display_title'], EXTRIF_NAMESPACE);
				}	
				
				/* Names EXTRIF */
				//$descriptions = $xml->xpath('//'.$this->ro->class.'/description');
				
				foreach ($xml->{$this->ro->class}->description AS $description)
				{					
					$type = (string) $description['type'];
					$description_str = (string) $description;					
					$this->_CI->load->library('purifier');
					$clean_html = $this->_CI->purifier->purify_html($description_str);
					$extrifDescription = $xml->{$this->ro->class}->addChild("extRif:description", $clean_html, EXTRIF_NAMESPACE);
					$extrifDescription->addAttribute("type", $type);
				}						
				$this->ro->updateXML($xml->asXML(),TRUE,'extrif');
				return $this;
			}
			else
			{
				throw new Exception ("Unable to enrich RIFCS. Not valid RIFCS XML");
			}
		}
	}

	function transformForSOLR()
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_solr_transformer();
			$dom = new DOMDocument();
			//$dom->loadXML($this->ro->getXML());
			$dom->loadXML($this->ro->getExtRif());
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}
	
	function transformForHtml()
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_html_transformer();
			$dom = new DOMDocument();
			$dataSource = $this->ro->data_source_key;
			$this->ro->enrich();
			$dom->loadXML($this->ro->getExtRif());
			$xslt_processor->setParameter('','dataSource',$dataSource);
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}
	
	
	function transformForFORM()
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_form_transformer();
			$dom = new DOMDocument();
			//$dom->loadXML($this->ro->getXML());
			$dataSource = $this->ro->data_source_key;
			$this->ro->enrich();
			$dom->loadXML($this->ro->getExtRif());
			$xslt_processor->setParameter('','dataSource',$dataSource);
			return $xslt_processor->transformToXML($dom);
		}
		catch (Exception $e)
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
			$this->ro->enrich();
			$dom->loadXML($this->ro->getExtRif());
			//$dom->loadXML(str_replace('&','&amp;',$this->ro->getExtRif()));
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
			//$dom->loadXML($this->ro->getXML());
			$dom->loadXML($rifcs);
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}
	
	
	
	function getReverseLinksStatusforEXTRIF($ds) 
	{
		$reverseLinks = 'NONE';
		if($ds->allow_reverse_internal_links == DB_TRUE && $ds->allow_reverse_external_links == DB_TRUE)
		{
			$reverseLinks = 'BOTH';
		}
		else if($ds->allow_reverse_internal_links == DB_TRUE)
		{
			$reverseLinks = 'INT';

		}
		else if($ds->allow_reverse_external_links == DB_TRUE)
		{
			$reverseLinks = 'EXT';
		}
		return $reverseLinks;
	}

}