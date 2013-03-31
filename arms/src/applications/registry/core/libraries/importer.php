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
	public $standardLog;
	public $statusAlreadyChanged = false;

	public $error_log = array();
	public $message_log = array();

	private $valid_classes = array("collection","party","activity","service");

	public function Importer()
	{
		$this->CI =& get_instance();
				
		// This is not a perfect science... the web server can still 
		// reclaim the worker thread and terminate the PHP script execution....
		ini_set('memory_limit', '1024M');
		ini_set('max_execution_time',3*ONE_HOUR);

		set_time_limit(0);
		ignore_user_abort(true);

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
		// Enable memory profiling...
		//ini_set('xdebug.profiler_enable',1);
		//xdebug_enable();

		//$this->CI->output->enable_profiler(TRUE);
		if (is_null($this->start_time))
		{
			$this->start_time = microtime(true);
		}

		// Some sanity checks
		if (!($this->dataSource instanceof _data_source))
			throw new Exception("No valid data source selected before import commit.");

		$this->CI->benchmark->mark('crosswalk_execution_start');
		
			// Apply the crosswalk (if applicable)
		$this->_executeCrosswalk();
		
		$this->CI->benchmark->mark('crosswalk_execution_end');


		// Set a a default HarvestID if necessary
		if (is_null($this->harvestID)) 
		{
			$this->harvestID = "MANUAL-".time();
		}

		// Decide on the default status for these records
		$this->status = $this->_getDefaultRecordStatusForDataSource($this->dataSource);

		// want to treat the payload as an array of split XML documents, even if it's not
		// (I reckon that SimpleXML goes memory-ape if it's trying to process a 100k-line XML doc)
		if (!is_array($this->xmlPayload))
		{
			// So fake it
			$this->xmlPayload = array($this->xmlPayload);
		}

		$this->CI->benchmark->mark('ingest_stage_1_start');
			foreach ($this->xmlPayload AS $idx => $payload)
			{
				// Escape XML entities from the start...
				$payload = str_replace("&", "&amp;", $payload);

				// Build a SimpleXML object from the converted data
				// We will throw an exception here if the payload isn't well-formed XML (which, by now, it should be)
				try
				{
					$sxml = $this->_getSimpleXMLFromString($payload);
				}
				catch (Exception $e)
				{
					throw new Exception("Unable to parse XML into object (registryObject #".($idx+1)."): " . NL . $e->getMessage());
				}

				// Last chance to check valid format of the payload
				$this->_validateRIFCS($payload);	
							
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
			}
		$this->CI->benchmark->mark('ingest_stage_1_end');



		// Partial commits mean that there is more to come in this harvest...woooah-on donkey
		if (!$this->partialCommitOnly)
		{
			// And now, onto the second stage...
			$this->CI->benchmark->mark('ingest_enrich_start');
				$this->_enrichRecords();
			$this->CI->benchmark->mark('ingest_enrich_end');

			$this->CI->benchmark->mark('ingest_reindex_start');
				$this->_reindexRecords();
			$this->CI->benchmark->mark('ingest_reindex_end');
		
			// XXX: Don't do this...it's crappy
			if ($this->dataSource)
			{
				// Update the data source stats
				$this->dataSource->updateStats();
			}

			// Finish up by returning our stats...
			$time_taken = sprintf ("%.3f", (float) (microtime(true) - $this->start_time));
			$this->message_log[] = NL;
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
			$this->message_log[] = $this->standardLog;
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

					//  Record owner should only be system if this is a harvest
					$record_owner = "SYSTEM";
					if($this->CI->user->isLoggedIn())
					{
						$record_owner = $this->CI->user->identifier();
					}

					if (is_null($revision_record_id))
					{
						// We are creating a new registryObject
						$ro = $this->CI->ro->create($this->dataSource, (string)$registryObject->key, $class, "", $this->status, "temporary_slug" . time(), $record_owner, $this->harvestID);

						if($this->dataSource->qa_flag===DB_TRUE && $this->ingest_new_record<1)
						{
		
							$this->CI->ro->emailAssessor($this->dataSource);
						
						}
						$this->ingest_new_record++;
					}
					else
					{
						// The registryObject exists, just add a new revision to it?
						$ro = $this->CI->ro->getByID($revision_record_id);

						// GEt rid of status change recursion on DRAFT->PUBLISHED
						if($this->statusAlreadyChanged)
						{
							$ro->original_status = $this->status;
						}
						$ro->status = $this->status;
						
						
						$ro->harvest_id = $this->harvestID;
						$ro->record_owner = $record_owner;

						$this->ingest_new_revision++;
					}

					$ro->class = $class;
					$ro->created_who = $record_owner;
					$ro->data_source_key = $this->dataSource->key;
					$ro->group = (string) $registryObject['group'];
					if($this->harvestID)
					{
						$ro->setAttribute("harvest_id", $this->harvestID);
					}
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
					$ro->updateXML(wrapRegistryObjects($registryObject->asXML()));

					// Generate the list and display titles first, then the SLUG
					$ro->updateTitles($ro->getSimpleXML());

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
					

					// WTF IS THIS DOING HERE? 
					// $ro->enrich(); 


					//if this is ds has the qa flag set we need to check if this is the first submitted for assesmment record and if so email the notify address


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
	public function _enrichRecords($directly_affected_records = array())
	{
		// Keep track of our imported keys...
		$imported_keys = array();

		// Only enrich records received in this harvest
		foreach ($this->importedRecords AS $ro_id)
		{

			$ro = $this->CI->ro->getByID($ro_id);

			// add reverse relationships
			// previous relationships are reset by this call
			if($ro)
			{
				$related_keys = $ro->addRelationships();

				// directly affected records are re-enriched below (and reindexed...)
				// we consider any related record keys to be directly affected and reindex them...
				$directly_affected_records = array_merge($related_keys, $directly_affected_records);
				$imported_keys[] = $ro->key;

				// Update our quality levels data!
				$ro->update_quality_metadata();

				// spatial resooultion, center, coords in enrich?
				$ro->determineSpatialExtents();

				// vocab indexing resolution

				// Generate extrif
				$ro->enrich();

				unset($ro);
			}
			clean_cycles();
		}
		gc_collect_cycles();

		// Exclude those keys we already processed above
		$directly_affected_records = array_unique(array_diff($directly_affected_records, $imported_keys));

		// enrich related records
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
	function _reindexRecords($specific_target_keys = array())
	{

		$this->CI->load->model('registry_object/registry_objects', 'ro');
		$this->CI->load->model('data_source/data_sources', 'ds');

		$this->CI->load->library('solr');

		// Called from outside the importer (i.e. $this->importer->_reindexRecords(array_of_keys...))
		if (is_array($specific_target_keys) && count($specific_target_keys) > 0)
		{
			/// Called from outside the Importer
			foreach ($specific_target_keys AS $key)
			{
				$ro = $this->CI->ro->getPublishedByKey($key);
				if ($ro)
				{
					$this->queueSOLRAdd($ro->transformForSOLR(false));
				}
				unset($ro);
				gc_collect_cycles();
			}
			$this->flushSOLRAdd();
			$this->commitSOLR();

			return array("count"=>$this->reindexed_records, "errors"=>array());
		}

		// Called from inside the Importer
		else
		{
			$allAffectedRecords = array_merge($this->importedRecords, $this->affected_records);
			foreach($allAffectedRecords AS $ro_id){

				$ro = $this->CI->ro->getByID($ro_id);
				if ($ro && $ro->status == PUBLISHED)
				{
					$this->queueSOLRAdd($ro->transformForSOLR(false));
				}
				unset($ro);
				gc_collect_cycles();
			}

			// Push through the last chunk...
			$this->flushSOLRAdd();
			$this->commitSOLR();
		}

		return true;
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
		if (isDraftStatus($this->status))
		{
			$existingRegistryObject = $this->CI->ro->getDraftByKey((string)$registryObject->key);
		}
		elseif (isPublishedStatus($this->status))
		{
			// If there is a draft, add to this one
			$existingRegistryObject = $this->CI->ro->getDraftByKey((string)$registryObject->key);
			if (!$existingRegistryObject)
			{
				$existingRegistryObject = $this->CI->ro->getPublishedByKey((string)$registryObject->key);
			}
		}
		

		if ($existingRegistryObject)
		{

			// Check for duplicates: Reject this record if it is already in the feed
			if ($existingRegistryObject->harvest_id == $this->harvestID)
			{
				$reharvest = false;
				$this->message_log[] = "Ignored a record received twice in this harvest: " . $registryObject->key;
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
				$this->message_log[] = "Ignored a record already existing in a different data source: " . $registryObject->key;
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
			$this->xmlPayload = $this->crosswalk->payloadToRIFCS($this->xmlPayload, $this->message_log);

			$temp_crosswalk_name = $this->crosswalk->metadataFormat();
			unset($this->crosswalk);
			$this->setCrosswalk($temp_crosswalk_name);
		}
	}

	/**
	 * 
	 */
	public function setXML($payload)
	{
		$this->xmlPayload = $payload;
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
		if (!$crosswalk_metadata_format) { return; }
		
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
		$doc = new DOMDocument('1.0','utf-8');
		$doc->loadXML($xml);

		if(!$doc)
		{
			//$this->dataSource->append_log("Unable to parse XML. Perhaps your XML file is not well-formed?", HARVEST_ERROR, "importer","DOCUMENT_LOAD_ERROR");
			throw new Exception("Unable to parse XML. Perhaps your XML file is not well-formed?");
		}

		// TODO: Does this cache in-memory?
		libxml_use_internal_errors(true);
		$validation_status = $doc->schemaValidate(APP_PATH . "registry_object/schema/registryObjects.xsd");

		if ($validation_status === TRUE) 
		{
			libxml_use_internal_errors(false);
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
			libxml_use_internal_errors(false);

			//$this->dataSource->append_log("Unable to validate XML document against schema: ".$error_string, HARVEST_ERROR, "importer","DOCUMENT_VALIDATION_ERROR");
			throw new Exception("Unable to validate XML document against schema: " . NL . $error_string);
		}
	}


	private function _getSimpleXMLFromString($xml)
	{
		$xml = simplexml_load_string($xml);

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
	public function getRifcsFromFeed($oai_feed)
	{

		/*if(substr($oai_feed, 0, 1) != '<')
		{
			$oai_feed = utf8_decode($oai_feed);
		}*/
		$result = '';
		//$sxml = $this._getSimpleXMLFromString($oai_feed);
		$sxml = simplexml_load_string($oai_feed);
		if($sxml)
		{
			
			@$sxml->registerXPathNamespace("oai", OAI_NAMESPACE);
			@$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);

			$registryObjects = $sxml->xpath('//ro:registryObject');
			foreach ($registryObjects AS $ro)
			{
				$result .= $ro->asXML();
			}

			$result = wrapRegistryObjects($result);

		}
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


	/* * * * 
	 * SOLR UPDATE FUNCTIONS 
	 * * * */

	var $solr_queue = array();
	const SOLR_CHUNK_SIZE = 50;
	const SOLR_RESPONSE_CODE_OK = 0;

	/**
	 * Queue up a request to send to SOLR ("chunking" of <add><doc> statements)
	 */
	function queueSOLRAdd($doc_statement)
	{
		$this->solr_queue[] = $doc_statement;
		if (count($this->solr_queue) > self::SOLR_CHUNK_SIZE)
		{
			$this->flushSOLRAdd();
		}
	}

	/**
	 * Send an update request to SOLR for all <add><doc> statements in the queue...
	 */
	function flushSOLRAdd()
	{
		if (count($this->solr_queue) == 0) return;

		$solrUrl = $this->CI->config->item('solr_url');
		$solrUpdateUrl = $solrUrl.'update/?wt=json';

		try{

			$result = json_decode(curl_post($solrUpdateUrl, "<add>" . implode("\n",$this->solr_queue) . "</add>"), true);
			if($result['responseHeader']['status'] == self::SOLR_RESPONSE_CODE_OK)
			{
				$this->reindexed_records += count($this->solr_queue);
			}
			else
			{
				// Throw back the SOLR response...
				throw new Exception(var_export((isset($result['error']['msg']) ? $result['error']['msg'] : $result),true));
			}

		}
		catch (Exception $e)
		{
			$this->error_log[] = "[INDEX] Error during reindex of registry object..." . BR . "<pre>" . nl2br($e->getMessage()) . "</pre>";	
		}

		$this->solr_queue = array();
		return true;
	}

	function commitSOLR()
	{
		$solrUrl = $this->CI->config->item('solr_url');
		$solrUpdateUrl = $solrUrl.'update/?wt=json';
		return curl_post($solrUpdateUrl.'?commit=true', '<commit waitSearcher="false"/>');
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
		$this->solr_queue = array();
		$this->forcePublish = false;
		$this->forceDraft = false; 
		$this->statusAlreadyChanged = false;
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