<?php


class Migrate extends MX_Controller
{
	private $input; // pointer to the shell input
	private $start_time; // time script run (microtime float)
	private $exec_time; // time execution started
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $source;
	
	function index()
	{
		set_error_handler(array(&$this, 'cli_error_handler'));
		echo "Connected to migration target database..." . NL;

		$this->source->select('*');
		$query = $this->source->get('dba.tbl_data_sources');

		$num_data_sources = $query->num_rows();
		
		/*echo $num_data_sources . " data sources found. Do you wish to migrate these data sources? [y/n]: ";

		if ($this->getInput() != 'y'){
			echo "Exiting..." . NL;
			return;
		}*/
		// Start the clock...

		$this->exec_time = microtime(true);
		$this->load->model('data_source/data_sources','ds');
		foreach ($query->result() AS $result)
		{
			echo "Importing data source: " . $result->title . ".";
			//var_dump($result);
			//exit();
			$data_source = $this->ds->getByKey($result->data_source_key);
			if($data_source === NULL)
				$data_source = $this->ds->create($result->data_source_key, url_title($result->title)); // XXX: Generate slug.
			
			$data_source->title = $result->title;
			$data_source->setAttribute("provider_type", $result->provider_type);
			$data_source->setAttribute("uri", $result->uri);
			$data_source->setAttribute("contact_name", $result->contact_name);
			$data_source->setAttribute("contact_email", $result->contact_email);
			$data_source->setAttribute("notes", substr($result->notes, 0 ,255));
			$data_source->setAttribute("record_owner", $result->record_owner);
			$data_source->setAttribute("created_when", $result->created_when);
			$data_source->setAttribute("modified_when", $result->modified_when);
			$data_source->setAttribute("modified_who", $result->modified_who);
			$data_source->setAttribute("harvest_method", $result->harvest_method);
			$data_source->setAttribute("oai_set", $result->oai_set);
			$data_source->setAttribute("harvest_date", $result->harvest_date);
			$data_source->setAttribute("harvest_frequency", $result->harvest_frequency);
			$data_source->setAttribute("isil_value", $result->isil_value);
			$data_source->setAttribute("push_to_nla", $result->push_to_nla);
			$data_source->setAttribute("allow_reverse_internal_links", $result->allow_reverse_internal_links);
			$data_source->setAttribute("allow_reverse_external_links", $result->allow_reverse_external_links);
			$data_source->setAttribute("assessement_notify_email_addr", $result->assessement_notification_email_addr);
			$data_source->setAttribute("auto_publish", $result->auto_publish);
			if($result->auto_publish)
			{
				$data_source->setAttribute("manual_publish", false);
			}else{
				$data_source->setAttribute("manual_publish", true);
			}
			$data_source->setAttribute("qa_flag", $result->qa_flag);
			$data_source->setAttribute("create_primary_relationships", $result->create_primary_relationships);
			$data_source->setAttribute("primary_key_1", $result->primary_key_1);
			$data_source->setAttribute("class_1", $result->class_1);
			$data_source->setAttribute("collection_rel_1", $result->collection_rel_1);
			$data_source->setAttribute("party_rel_1", $result->party_rel_1);
			$data_source->setAttribute("activity_rel_1", $result->activity_rel_1);
			$data_source->setAttribute("service_rel_1", $result->service_rel_1);
			$data_source->setAttribute("primary_key_2", $result->primary_key_2);
			$data_source->setAttribute("class_2", $result->class_2);
			$data_source->setAttribute("collection_rel_2", $result->collection_rel_2);
			$data_source->setAttribute("party_rel_2", $result->party_rel_2);
			$data_source->setAttribute("activity_rel_2", $result->activity_rel_2);
			$data_source->setAttribute("service_rel_2", $result->service_rel_2);
			$data_source->setAttribute("time_zone_value", $result->time_zone_value);
			$data_source->save();
			$data_source->append_log("IMPORTED FROM: ".$this->source->database, "info");
			echo "." .NL;

			$this->deleteAllrecordsForDataSource($data_source);
			// Now start importing registry objects
			
			
			echo $data_source->key;
			echo ". complete!" . NL;
			//exit();
		}

		foreach($query->result() as $result){
			$data_source = $this->ds->getByKey($result->data_source_key);
			$this->migrateRegistryObjectsForDatasource($data_source);
			$this->migrateDraftRegistryObjectsForDatasource($data_source);
		}

		foreach($query->result() as $result){
			$data_source = $this->ds->getByKey($result->data_source_key);
			$data_source->updateStats();
		}


	}

	function migrateRegistryObjectsForDatasource(_data_source $data_source)
	{
		$query = $this->source->get_where("dba.tbl_registry_objects", array("data_source_key"=>$data_source->key));
		$num_records = $query->num_rows();
		echo "FOUND: ". $num_records . " records" .NL;
		$this->_CI->load->model("registry_object/registry_objects", "ro");
		foreach ($query->result() AS $result)
		{
			echo "Importing Record: " . $result->registry_object_key . "." .NL;
			$gotXML = false;

			$createdWho = $result->created_who;
			$recordOwner = $result->record_owner;
			$harvestId = NULL;
			if($createdWho == $recordOwner && $createdWho != 'SYSTEM')					// created by direct import
				$harvestId  = $recordOwner; 

			//create(_data_source $data_source, $registry_object_key, $class, $title, $status, $slug, $record_owner, $harvestID)
			

			$registry_object = $this->ro->getPublishedByKey($result->registry_object_key);
			//if($registry_object) $registry_object = $registry_object[0];

			if($registry_object === NULL)
				$registry_object = $this->ro->create($data_source, $result->registry_object_key, $result->registry_object_class, $result->display_title, $result->status, $result->url_slug, $recordOwner, $harvestId);
			
			
			$registry_object->created = $result->created_when;
			$registry_object->group = $result->object_group;
			$registry_object->type = $result->type;
			$registry_object->list_title = $result->list_title;
			$query = $this->source->get_where("dba.tbl_raw_records", array("registry_object_key"=>$result->registry_object_key, "data_source"=>$data_source->key));
			//$this->source->order_by("created_when", "desc"); 
			$this->source->limit(1);
			foreach ($query->result() AS $result)
			{
				if($result->rifcs_fragment)
				{
					$registry_object->updateXML($result->rifcs_fragment);
					$gotXML = true;
				}

			}
			if($gotXML)
			{
				$registry_object->save();
				$registry_object->addRelationships();
				$registry_object->update_quality_metadata();
				$registry_object->enrich();
			}
			unset($registry_object);
			
		}
	}



	function migrateDraftRegistryObjectsForDatasource(_data_source $data_source)
	{
		$query = $this->source->get_where("dba.tbl_draft_registry_objects", array("registry_object_data_source"=>$data_source->key));
		$num_records = $query->num_rows();
		echo "FOUND: ". $num_records . " records" .NL;
		$this->_CI->load->model("registry_object/registry_objects", "ro");
		foreach ($query->result() AS $result)
		{
			echo "Importing Draft Record: " . $result->draft_key . "." .NL;

			
			$recordOwner = $result->draft_owner;
			$registry_object = $this->ro->create($data_source, $result->draft_key, $result->class, $result->registry_object_title, $result->status, NULL, $recordOwner, $recordOwner);
			$registry_object->created = $result->date_created;
			$registry_object->group = $result->registry_object_group;
			$registry_object->type = $result->registry_object_type;
			$registry_object->list_title = $result->registry_object_title;	
			$registryObjects = simplexml_load_string($result->rifcs);
			$registryObjects->registerXPathNamespace('rif', 'http://ands.org.au/standards/rif-cs/registryObjects');
			$registryObject = $registryObjects->xpath('//rif:registryObject');
			$registry_object->updateXML($registryObject[0]->asXML());
			$registry_object->save();
			unset($registry_object);
			
		}
	}

	function deleteAllrecordsForDataSource(_data_source $data_source)
	{
		$this->_CI->load->model("registry_object/registry_objects", "rox");
		$ids = $this->rox->getIDsByDataSourceID($data_source->id, false);
		if($ids)
		{
			foreach($ids as $ro_id){
			$ro = $this->rox->getByID($ro_id);
			if($ro)
				$ro->eraseFromDatabase();
			}
		}
	}

	function __construct()
    {
            parent::__construct();
            
            $this->input = fopen ("php://stdin","r");
            $this->start_time = microtime(true);
			$this->_CI =& get_instance();
            $this->source = $this->load->database('migration', true);

            define('IS_CLI_SCRIPT', true);

    }

    function __destruct() {
       print "Execution finished! Took " . sprintf ("%.3f", (float) (microtime(true) - $this->exec_time)) . "s" . NL;
   	}


   	private function getInput()
	{
		if (is_resource(($this->input)))
		{
			return trim(fgets($this->input));
		}
	}


	function cli_error_handler($number, $message, $file, $line, $vars)
	{
		echo NL.NL.str_repeat("=", 15);
     	echo NL .NL . "An error ($number) occurred on line $line in the file: $file:" . NL;
        echo $message . NL . NL;
        echo str_repeat("=", 15) . NL . NL;

       //"<pre>" . print_r($vars, 1) . "</pre>";

        // Make sure that you decide how to respond to errors (on the user's side)
        // Either echo an error message, or kill the entire project. Up to you...
        // The code below ensures that we only "die" if the error was more than
        // just a NOTICE.
        if ( ($number !== E_NOTICE) && ($number < 2048) ) {
          //  die("Exiting on error...");
        }

	}
}