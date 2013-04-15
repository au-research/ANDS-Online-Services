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
		$this->_CI->load->library('purifier');
		// Save ourselves some computation by avoiding creating the whole $ds object for 
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		//same as in relationships.php
		$xml = $this->ro->getSimpleXML();

		// Reset our namespace object (And go down one level from the wrapper if needed)
		$xml =  addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));

		$xml = simplexml_load_string($xml);

		// Clone across the namespace (if applicable)
		$namespaces = $xml->getNamespaces(true);
		if ( !in_array(RIFCS_NAMESPACE, $namespaces) )
		{    
			$xml->addAttribute("xmlns",RIFCS_NAMESPACE);
		}

		$xml = simplexml_load_string( addXMLDeclarationUTF8($xml->asXML()) );
		// Cannot enrich already enriched RIFCS!!
		if(true)//!isset($rifNS[EXTRIF_NAMESPACE])) //! (string) $attributes['enriched'])//! (string) $attributes['enriched'])
		{
			$xml->addAttribute("extRif:enriched","true",EXTRIF_NAMESPACE);
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
	
				$extendedMetadata->addChild("extRif:displayTitle", str_replace('&', '&amp;' , $this->ro->title), EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:listTitle", str_replace('&', '&amp;' , $this->ro->list_title), EXTRIF_NAMESPACE);
				$theDescription = '';
				$theDescriptionType = '';
				if($xml->{$this->ro->class}->description)
				{
					$logoAdded = false;
					foreach ($xml->{$this->ro->class}->description AS $description)
					{					
						$type = (string) $description['type'];
						$description_str = (string) $description;

						//add logo to the extrif
						if($type=='logo' && !$logoAdded){
							$logoAdded = true;
							$logoRef = $this->getLogoUrl($description);
							$extendedMetadata->addChild("extrif:logo", $logoRef, EXTRIF_NAMESPACE);
							$this->ro->set_metadata('the_logo', $logoRef);
						}

						// Clean the HTML with purifier, but decode entities first (else they wont be picked up in the first place)
						$clean_html = htmlentities(htmlentities($this->_CI->purifier->purify_html( html_entity_decode(html_entity_decode($description_str)) )));
						$encoded_html = '';

						// Check for <br/>'s
						if (strpos($description_str, "&lt;br") !== FALSE || strpos($description_str, "&lt;p") !== FALSE)
						{
							$encoded_html = $clean_html;
							$extrifDescription = $extendedMetadata->addChild("extRif:description", $encoded_html, EXTRIF_NAMESPACE);
						}
						else
						{
							$encoded_html = nl2br($clean_html);
							$extrifDescription = $extendedMetadata->addChild("extRif:description", $encoded_html, EXTRIF_NAMESPACE);
						}
						$extrifDescription->addAttribute("type", $type);

						if($type == 'brief' && $theDescriptionType != 'brief')
						{
							$theDescription = $encoded_html;
							$theDescriptionType = $type;
						}
						else if($type == 'full' && ($theDescriptionType != 'brief' || $theDescriptionType != 'full'))
						{
							$theDescription = $encoded_html;
							$theDescriptionType = $type;
						}
						else if($type != '' && $theDescriptionType == '')
						{
							$theDescription = $encoded_html;
							$theDescriptionType = $type;
						}
						else if($theDescription == '')
						{
							$theDescription = $encoded_html;
							$theDescriptionType = $type;
						}
					}
					$theDescription = strip_tags(html_entity_decode(html_entity_decode($theDescription)), '<p><br/><br />');
					$extrifTheDescription = $extendedMetadata->addChild("extRif:the_description", $theDescription, EXTRIF_NAMESPACE);
					$this->ro->set_metadata('the_description',$theDescription);

				}

				$subjects = $extendedMetadata->addChild("extRif:subjects", NULL, EXTRIF_NAMESPACE);
				
				foreach ($this->ro->processSubjects() AS $subject)
				{
					$subject_node = $subjects->addChild("extRif:subject", "", EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_value", $subject['value'], EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_type", $subject['type'], EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_resolved", $subject['resolved'], EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_uri", $subject['uri'], EXTRIF_NAMESPACE);
				}

	
				foreach ($this->ro->processLicence() AS $right)
				{
					$theright = $extendedMetadata->addChild("extRif:right", $right['value'], EXTRIF_NAMESPACE);
					$theright->addAttribute("type", $right['type']);	
					if(isset($right['rightsUri']))$theright->addAttribute("rightsUri", $right['rightsUri']);					
					if(isset($right['licence_type']))$theright->addAttribute("licence_type", $right['licence_type']);
					if(isset($right['licence_group']))$theright->addAttribute("licence_group", $right['licence_group']);					
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
					$relatedObj->addChild("extRif:related_object_class", $relatedObject['class'], EXTRIF_NAMESPACE);
					//$relatedObj->addChild("extRif:related_object_type", $relatedObject['related_object_type'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_display_title", $relatedObject['title'], EXTRIF_NAMESPACE);
					$relatedObj->addChild("extRif:related_object_relation", $relatedObject['relation_type'], EXTRIF_NAMESPACE);
					//$relatedObj->addChild("extRif:related_object_logo", $relatedObject['the_logo'], EXTRIF_NAMESPACE);
				}

				// Friendlify dates =)
				$xml = $this->ro->extractDatesForDisplay($xml);


				/* Names EXTRIF */
				//$descriptions = $xml->xpath('//'.$this->ro->class.'/description');
				
				//$ds->append_log(var_export($xml->asXML(), true));

				$this->ro->updateXML($xml->asXML(),TRUE,'extrif');
				//return $this;
			}
			else
			{
				throw new Exception ("Unable to enrich RIFCS. Not valid RIFCS XML");
			}
		}
	}
	
	function getLogoUrl($str)
	{
		$urlStr = '';
		if(preg_match('%(https?://[^\s^"^\'^&]+|[^\/\s^"^\'^&]+www\.[^\s^"^\'^&]+)%', $str, $url)) 
			$urlStr = $url[0];
		return $urlStr;    
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