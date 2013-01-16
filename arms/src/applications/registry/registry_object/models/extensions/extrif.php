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
		$rifNS = $xml->getNamespaces();
		// Cannot enrich already enriched RIFCS!!
		if(true)//!isset($rifNS[EXTRIF_NAMESPACE])) //! (string) $attributes['enriched'])//! (string) $attributes['enriched'])
		{
			$xml->addAttribute("extRif:enriched","true",EXTRIF_NAMESPACE);

			if(!isset($rifNS['']))
				$xml->addAttribute("xmlns",RIFCS_NAMESPACE);
			if (count($xml->key) == 1)
			{
				/* EXTENDED METADATA CONTAINER */
				$extendedMetadata = $xml->addChild("extRif:extendedMetadata", NULL, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:slug", $this->ro->slug, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:dataSourceKey", $ds->key, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:status", $this->ro->status, EXTRIF_NAMESPACE);				
				$extendedMetadata->addChild("extRif:id", $this->ro->id, EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:dataSourceTitle", $ds->title, EXTRIF_NAMESPACE);				
				$extendedMetadata->addChild("extRif:dataSourceID", $this->ro->data_source_id, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:updateTimestamp", $this->ro->updated, EXTRIF_NAMESPACE);					
	
				$extendedMetadata->addChild("extRif:displayTitle", $this->ro->title, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:listTitle", $this->ro->list_title, EXTRIF_NAMESPACE);
				if($xml->{$this->ro->class}->description)
				{
					foreach ($xml->{$this->ro->class}->description AS $description)
					{					
						$type = (string) $description['type'];
						$description_str = (string) $description;					
						$this->_CI->load->library('purifier');
						$clean_html = $this->_CI->purifier->purify_html($description_str);
						$extrifDescription = $extendedMetadata->addChild("extRif:description", $clean_html, EXTRIF_NAMESPACE);
						$extrifDescription->addAttribute("type", $type);
					}
				}

				$subjects = $extendedMetadata->addChild("extRif:subjects", NULL, EXTRIF_NAMESPACE);
				
				foreach ($this->ro->processSubjects() AS $subject)
				{
					$subjects->addChild("extRif:subject", $subject['value'], EXTRIF_NAMESPACE);
					$subjects->addChild("extRif:subject_type", $subject['type'], EXTRIF_NAMESPACE);
					$subjects->addChild("extRif:subject_resolved", $subject['resolved'], EXTRIF_NAMESPACE);
					$subjects->addChild("extRif:subject_uri", $subject['uri'], EXTRIF_NAMESPACE);
				}
				//$extendedMetadata->addChild("extRif:reverseLinks", $this->getReverseLinksStatusforEXTRIF($ds) , EXTRIF_NAMESPACE);
				
				//$extendedMetadata->addChild("extRif:flag", ($this->ro->flag === DB_TRUE ? '1' : '0'), EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:error_count", $this->ro->error_count, EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:warning_count", $this->ro->warning_count, EXTRIF_NAMESPACE);
				
				//$extendedMetadata->addChild("extRif:manually_assessed_flag", ($this->ro->manually_assessed_flag === DB_TRUE ? '1' : '0'), EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:gold_status_flag", ($this->ro->gold_status_flag === DB_TRUE ? '1' : '0'), EXTRIF_NAMESPACE);
				
				//$extendedMetadata->addChild("extRif:quality_level", $this->ro->quality_level, EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:feedType", ($this->ro->created_who == 'SYSTEM' ? 'harvest' : 'manual'), EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:lastModifiedBy", $this->ro->created_who, EXTRIF_NAMESPACE);
				
				// XXX: TODO: Search base score, displayLogo
				//$extendedMetadata->addChild("extRif:searchBaseScore", 100, EXTRIF_NAMESPACE);
				//$extendedMetadata->addChild("extRif:displayLogo", NULL, EXTRIF_NAMESPACE);
				
				// xxx: spatial extents (sanity checking?)
				$spatialLocations = $this->ro->getLocationAsLonLats();
				if($spatialLocations)
				{
					$spatialGeometry = $extendedMetadata->addChild("extRif:spatialGeometry", NULL, EXTRIF_NAMESPACE);
					$sumOfAllAreas = 0;
					foreach ($spatialLocations AS $lonLat)
					{
						//echo "enriching..." . $extent;
						$spatialGeometry->addChild("extRif:polygon", $lonLat, EXTRIF_NAMESPACE);
						$extents = $this->ro->calcExtent($lonLat);
						$spatialGeometry->addChild("extRif:extent", $extents['extent'], EXTRIF_NAMESPACE);
						$sumOfAllAreas += $extents['area'];
						$spatialGeometry->addChild("extRif:center", $extents['center'], EXTRIF_NAMESPACE);
					}
					$spatialGeometry->addChild("extRif:area", $sumOfAllAreas, EXTRIF_NAMESPACE);
				}

				$temporalCoverageList = $this->ro->processTemporal();
				if($temporalCoverageList)
				{
					$temporals = $extendedMetadata->addChild("extRif:temporal", NULL, EXTRIF_NAMESPACE);
					foreach ($temporalCoverageList AS $temporal)
					{
						if($temporal['type'] == 'dateFrom')
							$temporals->addChild("extRif:temporal_date_from", $temporal['value'], EXTRIF_NAMESPACE);
						if($temporal['type'] == 'dateTo')
							$temporals->addChild("extRif:temporal_date_to", $temporal['value'], EXTRIF_NAMESPACE);
					}
					$temporals->addChild("extRif:temporal_earliest_year", $this->ro->getEarliestAsYear(), EXTRIF_NAMESPACE);
					$temporals->addChild("extRif:temporal_latest_year", $this->ro->getLatestAsYear(), EXTRIF_NAMESPACE);
				}	

				foreach ($this->ro->getRelatedObjects() AS $relatedObject)
				{
					$relatedObj = $extendedMetadata->addChild("extRif:related_object", NULL, EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_key", $relatedObject['related_object_key'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_id", $relatedObject['related_id'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_class", $relatedObject['related_object_class'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_type", $relatedObject['related_object_type'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_display_title", $relatedObject['title'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_relation", $relatedObject['relation_type'], EXTRIF_NAMESPACE);
				}

				/* Names EXTRIF */
				//$descriptions = $xml->xpath('//'.$this->ro->class.'/description');
				
						
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


	function transformForQA($xml)
	{
		try{
			$xslt_processor = Transforms::get_qa_transformer();
			$dom = new DOMDocument();
			//$dom->loadXML($this->ro->getXML());
			$dom->loadXML($xml);
			$dataSource = 'a';
			$xslt_processor->setParameter('','dataSource',$dataSource);
			return $xslt_processor->transformToXML($dom);
		}catch (Exception $e)
		{
			echo "UNABLE TO TRANSFORM" . BR;	
			echo "<pre>" . nl2br($e->getMessage()) . "</pre>" . BR;
		}
	}
	
	function transformForHtml($revision='')
	{
		try{
			$xslt_processor = Transforms::get_extrif_to_html_transformer();
			$dom = new DOMDocument();
			$dataSource = $this->ro->data_source_key;
			if($revision=='') {
				$dom->loadXML($this->ro->getExtRif());
			}else $dom->loadXML($this->ro->getExtRifDataRecord($revision));
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
			$xslt_processor->setParameter('','base_url',base_url());
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