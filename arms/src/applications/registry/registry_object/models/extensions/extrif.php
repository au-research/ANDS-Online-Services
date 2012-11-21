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
		$xml = new SimpleXMLElement($this->ro->getRif()); // $this->ro->getSimpleXML();  XXX
		$attributes = $xml->attributes(EXTRIF_NAMESPACE);

		// Cannot enrich already enriched RIFCS!!
		if(! (string) $attributes['enriched'])
		{
			$xml->addAttribute("extRif:enriched","true",EXTRIF_NAMESPACE);
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
				
				echo "gnaa..";
				// xxx: spatial extents (sanity checking?)
				$spatialGeometry = $extendedMetadata->addChild("extRif:spatialGeometry", NULL, EXTRIF_NAMESPACE);
				foreach ($this->ro->getSpatialExtents() AS $extent)
				{
					echo "enriching..." . $extent;
					$spatialGeometry->addChild("extRif:geometry", $extent, EXTRIF_NAMESPACE);
				}
				
				
				/* Names EXTRIF */
				$names = $xml->xpath('//'.$this->ro->class.'/name');
				
				foreach ($names AS $name)
				{
					$extrifName = $xml->addChild("extRif:name", NULL, EXTRIF_NAMESPACE);
					$extrifName->addAttribute("extRif:type", (string) $name['type'], EXTRIF_NAMESPACE);
					$titles =  $this->ro->getTitlesForFragment($name, $this->ro->class);
					$extrifName->addChild("extRif:listTitle", $titles['list_title'], EXTRIF_NAMESPACE);
					$extrifName->addChild("extRif:displayTitle", $titles['display_title'], EXTRIF_NAMESPACE);
				}	
				
				/* Names EXTRIF */
				$descriptions = $xml->xpath('//'.$this->ro->class.'/description');
				
				foreach ($descriptions AS $description)
				{
					$extrifDescription = $xml->addChild("extRif:description", (string)$description, EXTRIF_NAMESPACE);
					$extrifDescription->addAttribute("type", (string) $description['type']);
					// XXX: TODO: CLEAN UP HTML (PURIFY)
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
			//$dom->loadXML($this->ro->getXML());
			$dom->loadXML($this->ro->getExtRif());
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
			$dom->loadXML($this->ro->getExtRif());
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