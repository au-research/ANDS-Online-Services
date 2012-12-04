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
			$data_source = $this->ds->create($result->data_source_key, $result->title); // XXX: Generate slug.
			$data_source->title = $result->title;
			// etc...

			$data_source->save();

			echo ".";


			// Now start importing registry objects
			$this->migrateRegistryObjectsForDatasource($data_source);

			echo ". complete!" . NL;
		}




	}

	function migrateRegistryObjectsForDatasource(_data_source $data_source)
	{
		// Do some stuff here...
		echo "Called migrateRegistryObjectsForDatasource()" . NL;
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