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
	private $partialCommitOnly;
	private $forcePublish; // used when changing from DRAFT to PUBLISHED (ignore the QA flags, etc)
	private $forceDraft; 

	private $status; // status of the currently ingested record

	private $importedRecords;

	public $ingest_attempts;
	public $ingest_successes;
	public $ingest_failures;

	public $ingest_new_revision;
	public $ingest_duplicate_ignore;
	public $ingest_new_record;

	public $reindexed_records;
	public $affected_records;
	public $deleted_records;

	public $error_log = array();
	public $message_log = array();

	private $valid_classes = array("collection","party","activity","service");

	public function Importer()
	{
		$this->CI =& get_instance();
		ini_set('memory_limit', '1024M');
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
		if (is_null($this->start_time))
		{
			$this->start_time = microtime(true);
		}

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
	
		// Decide on the default status for these records
		$this->status = $this->_getDefaultRecordStatusForDataSource($this->dataSource);

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

		// Partial commits mean that there is more to come in this harvest...woooah-on donkey
		if (!$this->partialCommitOnly)
		{
			// And now, onto the second stage...
			$this->_enrichRecords();
			$this->_reindexRecords();
		
			// Finish up by returning our stats...
			$time_taken = sprintf ("%.3f", (float) (microtime(true) - $this->start_time));
			$this->message_log[] = "Harvest complete! Took " . ($time_taken) . "s...";
			$this->message_log[] = "Registry Object(s) in feed: " . $this->ingest_attempts;
			$this->message_log[] = "Registry Object(s) created: " . $this->ingest_new_record;
			$this->message_log[] = "Registry Object(s) updated: " . $this->ingest_new_revision;
			if ($this->ingest_failures)
			{
				$this->message_log[] = "Registry Object(s) failed : " . $this->ingest_failures;
			}
			if ($this->ingest_duplicate_ignore)
			{
				$this->message_log[] = "Registry Object duplicates: " . $this->ingest_duplicate_ignore;
			}
			
			$this->message_log[] = "Reindexed record count: " . $this->reindexed_records;
		}

	}


	/**
	 * 
	 */
	public function _ingestRecord($registryObject)
	{
		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->CI->load->model('data_source/data_sources', 'ds');

		foreach ($this->valid_classes AS $class)
		{
			if (property_exists($registryObject, $class))
			{
				
				$ro_xml =& $registryObject->{$class}[0];
	

				// Choose whether or not to harvest this record and whether this should overwrite 
				// the existing entry or just create a new revision
				list($reharvest, $revision_record_id) = $this->decideHarvestability($registryObject);

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

					
					if (is_null($revision_record_id))
					{
						// We are creating a new registryObject
						$ro = $this->CI->ro->create($this->dataSource, (string)$registryObject->key, $class, "", $this->status, "temporary_slug" . time(), $record_owner, $this->harvestID);
						$this->ingest_new_record++;
					}
					else
					{
						// The registryObject exists, just add a new revision to it?
						$ro = $this->CI->ro->getByID($revision_record_id);
						$ro->status = $this->status;
						$ro->harvest_id = $this->harvestID;
						$ro->class = $class;
						$ro->record_owner = $record_owner;

						$this->ingest_new_revision++;
					}

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

					// Only generate SLUGs for published records
					if (in_array($this->status, getPublishedStatusGroup()))
					{
						$ro->generateSlug();
					}
					else
					{
						$ro->slug = DRAFT_RECORD_SLUG . $ro->id;
					}
					// Save all our attributes to the object
					$ro->save();

					// Add this record to our counts, etc.
					$this->importedRecords[] = $ro->id;
					$this->ingest_successes++;

					// Memory management...
					unset($ro);
					clean_cycles();
				}

			}
		}

		unset($sxml);
		unset($xml);
		gc_collect_cycles();
	}

/* XXX: TODO
	public function deleteRecordsByIDs(array $deleted_record_ids)
	{
		$affected_record_ids = array();
		$this->deleted_records = $deleted_record_ids;
		foreach($this->deleted_records AS $ro_id)
		{
			$ro = $this->CI->ro->getByID($ro_id);
			$related_keys = $ro->getRelationships();
			foreach ($related_keys AS $key)
			{

			}
			$affected_record_keys = array_merge($affected_record_keys, $ro->getRelationships());
			$ro->delete();
		}
	}
*/
	/**
	 * 
	 */
	public function _enrichRecords($directly_affected_records = array())
	{
		// Only enrich records received in this harvest
		foreach ($this->importedRecords AS $ro_id)
		{

			$ro = $this->CI->ro->getByID($ro_id);

			// add reverse relationships
			// previous relationships are reset by this call
			$related_keys = $ro->addRelationships();
			$directly_affected_records = array_merge($related_keys, $directly_affected_records);

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


		// enrich related records, etc?
		foreach ($directly_affected_records AS $ro_key)
		{
			$registryObjects = $this->CI->ro->getAllByKey($ro_key);

			if (is_array($registryObjects))
			{
				foreach ($registryObjects AS $ro)
				{
					$this->affected_records[$ro->id] = $ro->id;

					$ro->addRelationships();
					$ro->update_quality_metadata();
					$ro->enrich();
					unset($ro);
					clean_cycles();
				}
			}
		}
		gc_collect_cycles();
	}

	/**
	 *
	 */
	function _reindexRecords($specific_target_keys = array()){
		$solrUrl = $this->CI->config->item('solr_url');
		$solrUpdateUrl = $solrUrl.'update/?wt=json';
		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->CI->load->model('data_source/data_sources', 'ds');

		$this->CI->load->library('solr');

		$deleted_records = array();

		if (is_array($specific_target_keys) && count($specific_target_keys) > 0)
		{
			$index_count = 0;
			$errors = array();
			/// Called from outside the Importer
			foreach ($specific_target_keys AS $key)
			{
				try{
					$ro = $this->CI->ro->getPublishedByKey($key);

					if ($ro)
					{
						$solrXML = $ro->transformForSOLR();
						$result = curl_post($solrUpdateUrl, $solrXML);
						$result = json_decode($result);
						if($result->{'responseHeader'}->{'status'}==0){
							$index_count++;
						}
					}
				}
				catch (Exception $e)
				{
					$errors[] = "UNABLE TO Index this registry object id = ".$key . BR . "<pre>" . nl2br($e->getMessage()) . "</pre>";	
				}
			}
			return array("count"=>$this->reindexed_records, "errors"=>$errors);
		}
		// Called from inside the Importer
		else
		{
			$allAffectedRecords = array_merge($this->importedRecords, $this->affected_records);

			foreach($allAffectedRecords AS $ro_id){
				try{
					$ro = $this->CI->ro->getByID($ro_id);
					if ($ro->status == PUBLISHED)
					{
						// XXX: Use the SOLR library, do update in batches
						$solrXML = $ro->transformForSOLR();
						$result = curl_post($solrUpdateUrl, $solrXML);
						$result = json_decode($result);

						if($result->{'responseHeader'}->{'status'}==0){
							$this->reindexed_records++;
						}
						else
						{
							if (isset($result->{'error'}->{'msg'}))
							{
								$this->error_log[] = "UNABLE TO Index this registry object id = ".$ro_id . BR . 
													"<pre>" . $result->{'error'}->{'msg'} . "</pre>";
							}
							else
							{
								$this->error_log[] = "UNABLE TO Index this registry object id = ".$ro_id . BR . "UNKNOWN ERROR";
							}
						}
					}
				}
				catch (Exception $e)
				{
					$this->error_log[] = "UNABLE TO Index this registry object id = ".$ro_id . BR . "<pre>" . nl2br($e->getMessage()) . "</pre>";	
				}
			}
		}
		// Update the data source stats
		$this->dataSource->updateStats();

		// Finalise the commit
		return curl_post($solrUpdateUrl.'?commit=true', '<commit waitSearcher="false"/>');
	}

	/**
	 * 
	 */
	public function decideHarvestability($registryObject)
	{
		$reharvest = true;
		$revision_record_id = null;
		$existingRegistryObject = null;

		// If there is something existing with the same class of status, overwrite it
		if (isPublishedStatus($this->status))
		{
			$existingRegistryObject = $this->CI->ro->getPublishedByKey((string)$registryObject->key);
		}
		elseif (isDraftStatus($this->status))
		{
			$existingRegistryObject = $this->CI->ro->getDraftByKey((string)$registryObject->key);
		}
		

		if ($existingRegistryObject)
		{

			// Check for duplicates: Reject this record if it is already in the feed
			if ($existingRegistryObject->harvest_id == $this->harvestID)
			{
				$reharvest = false;
				$this->error_log[] = "Ignored a record received twice in this harvest: " . $registryObject->key;
				$this->ingest_duplicate_ignore++;
			}

			if($existingRegistryObject->data_source_id == $this->dataSource->id)
			{	
				// Add a new revision to this existing registry object
				$revision_record_id = $existingRegistryObject->id;
			}
			else
			{
				// Duplicate key in alternate data source
				$reharvest = false;
				$this->error_log[] = "Ignored a record already existing in a different data source: " . $registryObject->key;
				$this->ingest_duplicate_ignore++;
			}

		}
		else
		{
			// Harvest this as a new registry object
			$reharvest = true;
			$revision_record_id = null;
		}
	
	
		return array($reharvest, $revision_record_id);

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
	public function setCrosswalk($crosswalk_metadata_format)
	{
		$crosswalks = getCrossWalks();
		foreach (getCrosswalks() AS $crosswalk)
		{
			if ($crosswalk->metadataFormat() == $crosswalk_metadata_format)
			{
				$this->crosswalk = $crosswalk;
			}
		}
		
		if (!$this->crosswalk)
		{
			throw new Exception("Unable to load crosswalk: " . $crosswalk_name);
		}
	}


	/**
	 * 
	 */
	public function setPartialCommitOnly($bool)
	{
		$this->partialCommitOnly = (boolean) $bool;
		return;
	}

	/**
	 * 
	 */
	private function _validateRIFCS($xml)
	{
		$lines = explode(NL, $xml);
		//echo htmlentities(implode(NL, array_slice($lines, 474090, 474120)));
		$doc = new DOMDocument('1.0','utf-8');
		$doc->loadXML(utf8_encode(str_replace("&", "&amp;", $xml)), LIBXML_NOENT);
		//echo htmlentities($xml);
		if(!$doc)
		{
			throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
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
		$xml = simplexml_load_string(utf8_encode(str_replace("&", "&amp;", $xml)), "SimpleXMLElement", LIBXML_NOENT);

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
		if ($this->forcePublish)
		{
			return PUBLISHED;
		}
		else if ($this->forceDraft)
		{
			return DRAFT;
		}

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


	public function forcePublish()
	{
		$this->forcePublish = TRUE;
	}

	public function forceDraft()
	{
		$this->forceDraft = TRUE;
	}

	/**
	 * 
	 */
	public function extractRIFCSFromOAI($oai_feed)
	{

		/*if(substr($oai_feed, 0, 1) != '<')
		{
			$oai_feed = utf8_decode($oai_feed);
		}*/
		
		$sxml = simplexml_load_string($oai_feed);
		$sxml->registerXPathNamespace("oai", OAI_NAMESPACE);
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

		$registryObjects = $sxml->xpath('//ro:registryObject');

		$result = '';
		foreach ($registryObjects AS $ro)
		{
			$result .= $ro->asXML();
		}

		$result = wrapRegistryObjects($result);
		
		return $result;
	}



	public function getErrors()
	{
		$log = '';
		if (count($this->error_log) > 0)
		{
			foreach ($this->error_log AS $error)
			{
				$log .= "  $error" . NL;
			}
			$log .= NL;
			
		}

		if ($log) return $log; else return FALSE;
	}

	public function getMessages()
	{
		$log = '';
		if (count($this->message_log) > 0)
		{
			foreach ($this->message_log AS $msg)
			{
				$log .= "  $msg" . NL;
			}			
		}

		if ($log) return $log; else return FALSE;
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
		$this->affected_records = array();
		$this->partialCommitOnly = false;

		$this->forcePublish = false;
		$this->forceDraft = false; 

		$this->ingest_attempts = 0;
		$this->ingest_successes = 0;
		$this->ingest_failures = 0;
		$this->ingest_duplicate_ignore = 0;
		$this->ingest_new_revision = 0;
		$this->ingest_new_record = 0;
		$this->reindexed_records = 0;

		$this->error_log = array();
		$this->message_log = array();

		$this->start_time = null;
	}


}