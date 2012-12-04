<?php


class Migrate extends MX_Controller
{
	private $input; // pointer to the shell input
	private $start_time; // time script run (microtime float)
	private $exec_time; // time execution started

	private $source;
	
	function index()
	{
		set_error_handler(array(&$this, 'cli_error_handler'));
		echo "Connected to migration target database..." . NL;

		$this->source->select('*');
		$query = $this->source->get('dba.tbl_data_sources');

		$num_data_sources = $query->num_rows();
		
		echo $num_data_sources . " data sources found. Do you wish to migrate these data sources? [y/n]: ";

		if ($this->getInput() != 'y'){
			echo "Exiting..." . NL;
			return;
		}

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
				$data_source = $this->ds->create($result->data_source_key, $result->title); // XXX: Generate slug.
			
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


			// Now start importing registry objects
			$this->migrateRegistryObjectsForDatasource($data_source);
			echo $data_source->key;
			echo ". complete!" . NL;
		}




	}

	function migrateRegistryObjectsForDatasource(_data_source $data_source)
	{
		$query = $this->source->get_where("dba.tbl_registry_objects", array("data_source_key"=>$data_source->key));
		$num_records = $query->num_rows();
		echo "FOUND: ". $num_records . "records" .NL;
	}


	function __construct()
    {
            parent::__construct();
            
            $this->input = fopen ("php://stdin","r");
            $this->start_time = microtime(true);

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