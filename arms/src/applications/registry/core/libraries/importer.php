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
	private $status; // status of the currently ingested record

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
						$ro = $this->CI->ro->create($this->dataSource->key, (string)$registryObject->key, $class, "", $this->status, "temporary_slug", $record_owner, $this->harvestID);
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
					if (in_array($this->status, getApprovedStatusGroup()))
					{
						$ro->generateSlug();
					}
					else
					{
						$ro->slug = 'draft_record_slug-' . $ro->id;
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
	public function decideHarvestability($registryObject)
	{
		$reharvest = true;
		$revision_record_id = null;

		// Get any existing registry objects with the same key
		$existingRegistryObjects = $this->CI->ro->getByKey((string)$registryObject->key);
		if (!is_array($existingRegistryObjects)) return array($reharvest, $revision_record_id);

		foreach ($existingRegistryObjects AS $existingRO)
		{
			// Reject this record if it is already in the feed
			if ($existingRO->harvest_id == $this->harvestID)
			{
				$reharvest = false;
				$this->error_log[] = "Ignored a record received twice in this harvest: " . $registryObject->key;
				$this->ingest_duplicate_ignore++;
				break;
			}

			// Record ownership, reject if record already exists within the registry
			if($existingRO->data_source_id != $this->dataSource->id)
			{
				$reharvest = false;
				$this->error_log[] = "Ignored a record already existing in a different data source: " . $registryObject->key;
				$this->ingest_duplicate_ignore++;
				break;
			}

			// Handle overwriting existing records of same "Status group"
			if (  	
				(in_array($this->status,  getDraftStatusGroup()) && in_array($existingRO->status,  getDraftStatusGroup()))
				OR 
				(in_array($this->status,  getApprovedStatusGroup()) && in_array($existingRO->status,  getApprovedStatusGroup()))
			)
			{
				// We should overwrite the record revision (can only have at most one registry object in each status group)
				$revision_record_id = $existingRO->registry_object_id;
				break;
			}

		}

		return  array($reharvest, $revision_record_id);
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
		$this->partialCommitOnly = false;

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