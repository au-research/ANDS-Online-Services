<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
include_once("applications/registry/registry_object/models/_transforms.php");
/**
 * 
 */
class Importer {
	
	private $CI;
	private $db;

	private $xmlPayload;
	private $nativePayload;
	private $crosswalk;
	private $harvestID;
	private $dataSource;
	private $start_time;

	private $importedRecords;

	public $ingest_attempts;
	public $ingest_successes;
	public $ingest_failures;

	public $ingest_new_revision;
	public $ingest_duplicate_ignore;
	public $ingest_new_record;

	public $reindexed_records;

	public $error_log = array();
	public $message_log = array();

	private $valid_classes = array("collection","party","activity","service");

	public function Importer()
	{
		$this->CI =& get_instance();

		// setup the DB connection
		$this->db = $this->CI->db;

		// Initialise our variables
		$this->_reset();
	}


	/**
	 * 
	 */
	public function commit()
	{
		$this->start_time = microtime(true);

		// Some sanity checks
		if (!($this->dataSource instanceof _data_source))
			throw new Exception("No valid data source selected before import commit.");

		// Apply the crosswalk (if applicable)
		$this->_executeCrosswalk();

		// Last chance to check valid format of the payload
		$this->_validateRIFCS($this->xmlPayload);

		// Set a a default HarvestID if necessary
		if (is_null($this->harvestID)) 
		{
			$this->harvestID = "MANUAL-".time();
		}

		// Build a SimpleXML object from the converted data
		// We will throw an exception here if the payload isn't well-formed XML (which, by now, it should be)
		try
		{
			$sxml = @$this->_getSimpleXMLFromString($this->xmlPayload);
		}
		catch (Exception $e)
		{
			throw new Exception("Unable to parse XML into object: " . NL . $e->getMessage());
		}
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
	

		// Right then, lets start parsing each registryObject & importing! 
		foreach($sxml->xpath('//ro:registryObject') AS $registryObject)
		{
			$this->ingest_attempts++;
			try
			{
				$this->_ingestRecord($registryObject);
			}
			catch (Exception $e)
			{
				$this->ingest_failures++;
				$this->error_log[] = "Error whilst ingesting record #" . $this->ingest_attempts . ": " . $e->getMessage();
			}
		}

		// And now, onto the second stage...
		$this->_enrichRecords();

		$this->_reindexRecords();



		// Finish up by returning our stats...
		$time_taken = sprintf ("%.3f", (float) (microtime(true) - $this->start_time));
		$this->message_log[] = "Harvest complete! Took " . ($time_taken) . "s...";
		$this->message_log[] = "Registry Object(s) in feed: " . $this->ingest_attempts;
		$this->message_log[] = "Registry Object(s) created: " . $this->ingest_new_record;
		$this->message_log[] = "Registry Object(s) updated: " . $this->ingest_new_revision;
		$this->message_log[] = "Registry Object(s) failed : " . $this->ingest_failures;
		if ($this->ingest_duplicate_ignore)
		{
			$this->message_log[] = "Registry Object duplicates: " . $this->ingest_duplicate_ignore;
		}
		$this->message_log[] = "Reindexed record count: " . $this->reindexed_records;


	}


	/**
	 * 
	 */
	public function _ingestRecord($registryObject)
	{
		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->CI->load->model('registry_object/rifcs', 'rifcs');
		$this->CI->load->model('data_source/data_sources', 'ds');


		$status = $this->_getDefaultRecordStatusForDataSource($this->dataSource);

		foreach ($this->valid_classes AS $class)
		{
			if (property_exists($registryObject, $class))
			{
				
				$ro_xml =& $registryObject->{$class}[0];
	

				// Flag records that are duplicates within this harvest and choose not to harvest them again (repeated keys in single harvest are dumb!)
				$reharvest = true;
				if($oldRo = $this->CI->ro->getByKey((string)$registryObject->key))
				{
					$oldharvestID = $oldRo->getAttribute("harvest_id");
					if($oldharvestID == $this->harvestID)
					$reharvest = false;

					// XXX: Record ownership, reject if record already exists within the registry
				}

				if($reharvest)
				{
					// Clean up crosswalk XML if applicable
					$ro_xml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

					$nativeHarvestIdx = null;
					$idx = 0;

					foreach($ro_xml->relatedInfo AS $relatedInfo)
					{
						if((string)$relatedInfo['type'] == NATIVE_HARVEST_FORMAT_TYPE)
						{
							$nativeHarvestIdx = $idx;
						}
						$idx++;
					}

					// This is a post-crosswalk record, lets extract the native data and store it!
					if(!is_null($nativeHarvestIdx))
					{
						// Extract
						$nativeSchemaFormat = (string)$ro_xml->relatedInfo[$nativeHarvestIdx]->identifier[0];
						$nativeData = trim((string) $ro_xml->relatedInfo[$nativeHarvestIdx]->notes[0]);

						// Delete the temporary node from the registry object
						unset($ro_xml->relatedInfo[$nativeHarvestIdx]);
					}



					// XXX: Record owner should only be system if this is a harvest?
					$record_owner = "SYSTEM";

					// Create a frame instance of the registryObject
					$ro = $this->CI->ro->create($this->dataSource->key, (string)$registryObject->key, $class, "", $status, "defaultSlug", $record_owner, $this->harvestID);
					$ro->created_who = $record_owner;
					$ro->data_source_key = $this->dataSource->key;
					$ro->group = (string) $registryObject['group'];
					$ro->setAttribute("harvest_id", $this->harvestID);

					// Clean up all previous versions (set = FALSE, "prune" extRif)
					$ro->cleanupPreviousVersions();

					// Store the native format, if we had one
					if (isset($nativeSchemaFormat) && isset($nativeData))
					{
						$ro->updateXML($nativeData, TRUE, $nativeSchemaFormat);
						unset($nativeSchemaFormat);
						unset($nativeData);
					}

					// Order is important here!
					$ro->updateXML($registryObject->asXML());

					// Generate the list and display titles first, then the SLUG
					$ro->updateTitles();
					$ro->generateSlug();

					// Save all our attributes to the object
					$ro->save();

					// Add this record to our counts, etc.
					$this->importedRecords[] = $ro->id;
					$this->ingest_new_record++;

					// Memory management...
					unset($ro);
					clean_cycles();
				}
				else
				{
					// XXX: Verbose message?
					$this->ingest_duplicate_ignore++;
				}
			}
		}

		unset($sxml);
		unset($xml);
		gc_collect_cycles();
	}

	/**
	 * 
	 */
	public function _enrichRecords()
	{
		// Only enrich records received in this harvest
		foreach ($this->importedRecords AS $ro_id)
		{

			$ro = $this->CI->ro->getByID($ro_id);

			// XXX: delete previous enriched records

			// xxx: REMOVE PREVIOUS RELATIONSHIPS 
			// add reverse relationships
			$ro->addRelationships();
			// XXX: re-enrich records which are related to this one

			$ro->update_quality_metadata();

			// spatial resooultion, center, coords in enrich?
			$ro->determineSpatialExtents();

			// vocab indexing resolution

			// Generate extrif
			$ro->enrich();

			unset($ro);
			clean_cycles();
		}

		gc_collect_cycles();

	}

	/**
	 *
	 */
	function _reindexRecords(){
		$solrUrl = $this->CI->config->item('solr_url');
		$solrUpdateUrl = $solrUrl.'update/?wt=json';
		$this->CI->load->model('registry_objects', 'ro');
		$this->CI->load->model('data_source/data_sources', 'ds');

		foreach($this->importedRecords AS $ro_id){
			try{
				$ro = $this->CI->ro->getByID($ro_id);
				//echo $ro->getExtRif();
				//$solrXML =  str_replace("&lt;field","\n&lt;field", htmlentities($ro->transformForSOLR()));
				$solrXML = $ro->transformForSOLR();
				//echo $solrXML;
				$result = curl_post($solrUpdateUrl, $solrXML);
				$result = json_decode($result);
				if($result->{'responseHeader'}->{'status'}==0){
					$this->reindexed_records++;
				}
			}
			catch (Exception $e)
			{
				$this->error_log[] = "UNABLE TO Index this registry object id = ".$ro_id . BR . "<pre>" . nl2br($e->getMessage()) . "</pre>";	
			}
		}

		return curl_post($solrUpdateUrl.'?commit=true', '<commit waitSearcher="false"/>');
	}

	/**
	 * 
	 */
	private function _executeCrosswalk()
	{
		// Apply the crosswalk (if applicable)
		if (!is_null($this->crosswalk))
		{
			// At this point, $this->xmlPayload is actually the native payload (which might
			// not even be XML!) -- the crosswalk should implement a validate method (throwing
			// an exception on failure -- the entire harvest will be aborted as a partial 
			// transform might be erroneous if assumed at this point.

			// Throws an exception up if unable to validate in the payload's native schema
			$this->crosswalk->validate($this->xmlPayload);

			// Crosswalk will create <registryObjects> with a <relatedInfo> element appended with the native format
			$this->xmlPayload = $this->crosswalk->payloadToRIFCS($this->xmlPayload);
		}
	}

	/**
	 * 
	 */
	public function setXML($payload)
	{
		$this->xmlPayload = $payload;
		log_message('debug', 'Loaded XML fragment of ' . count($payload) . ' chars into memory.');
		return;
	}

	/**
	 * 
	 */
	public function setDataSource(_data_source $data_source)
	{
		$this->dataSource = $data_source;
		return;
	}


	/**
	 * 
	 */
	public function setHarvestID($harvestID)
	{
		$this->harvestID = $harvestID;
		return;
	}

	/**
	 * 
	 */
	public function setCrosswalk($crosswalk_name)
	{
		$crosswalks = getCrossWalks();
		if (isset($crosswalks[$crosswalk_name]))
		{
			$this->crosswalk = $crosswalks[$crosswalk_name];
		}
		else
		{
			throw new Exception("Unable to load crosswalk: " . $crosswalk_name);
		}
	}



	/**
	 * 
	 */
	private function _validateRIFCS($xml)
	{
		$doc = @DOMDocument::loadXML($xml);
		if(!$doc)
		{
			throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?".$xml);
		}

		// TODO: Does this cache in-memory?
		libxml_use_internal_errors(true);
		$validation_status = $doc->schemaValidate(APP_PATH . "registry_object/schema/registryObjects.xsd");
		
		if ($validation_status === TRUE) 
		{
			return TRUE;
		}
		else
		{
			$errors = libxml_get_errors();
			$error_string = '';
			foreach ($errors as $error) {
			    $error_string .= TAB . "Line " .$error->line . ": " . $error->message;
			}
			libxml_clear_errors();
			throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
		}
	}


	private function _getSimpleXMLFromString($xml)
	{
		// Simplexml doesn't play nicely with namespaces :-(
		$xml = simplexml_load_string($xml, "SimpleXMLElement", 0);

		if ($xml === false)
		{
			$exception_message = "Could not parse Registry Object XML" . NL;
			foreach(libxml_get_errors() as $error) {
        		$exception_message .= "\t" . $error->message;
			}
			throw new Exception($exception_message);	
		}
		return $xml;
	}


	/**
	 * XXX: BROKEN?
	 */
	private function _getRifcsFromHarvest($xmlData)
	{
		$result = ''; 

		$xslt_processor = HarvestTransforms::get_feed_to_rif_transformer();
		$dom = new DOMDocument();
		if(substr($xmlData, 0, 1) == '<')
		{
			$dom->loadXML($xmlData);
		}
		else
		{
			$dom->loadXML(utf8_decode($xmlData));
		}

		$result = $xslt_processor->transformToXML($dom);

		return $result;
	}


	/**
 	 * 
 	 */
	private function _getDefaultRecordStatusForDataSource(_data_source $data_source)
	{

		/*
		 * Harvest to the correct record mode
		 * QA = SUBMIT FOR ASSESSMENT
		 * !QA, AP = PUBLISHED
		 * !QA, !AP = APPROVED
		 */
		if ($data_source->qa_flag === DB_TRUE)
		{
			$status = SUBMITTED_FOR_ASSESSMENT;
		}
		else
		{
			if ($data_source->auto_publish === DB_FALSE)
			{
				$status = APPROVED;
			}
			else
			{
				$status = PUBLISHED;
			}
		}

		return $status;
	}


	/**
	 * 
	 */
	public function _reset()
	{
		$this->harvestID = null;
		$this->crosswalk = null;
		$this->xmlPayload = '';
		$this->dataSource = null;
		$this->importedRecords = array();

		$this->ingest_attempts = 0;
		$this->ingest_successes = 0;
		$this->ingest_failures = 0;
		$this->ingest_duplicate_ignore = 0;
		$this->ingest_new_revision = 0;
		$this->reindexed_records = 0;

		$this->error_log = array();
		$this->message_log = array();

		$this->start_time = null;
	}


}