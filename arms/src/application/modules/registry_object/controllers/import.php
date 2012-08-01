<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Import Registry Object controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/registryobject
 * 
 */
class Import extends MX_Controller {

	function test()
	{
		
		$this->load->model('registry_objects', 'ro');
		//$registry_objects = $this->ro->getByAttribute('status','DRAFT', TRUE);
		$registry_objects = array($this->ro->getByKey('abctb.org.au 20'));
		foreach ($registry_objects AS $ro)
		{
			//$ro->updateTitles();	
			//$ro->generateSlug();
			print $ro->key .  " => " . $ro->display_title .  ' ('.$ro->slug.')'.BR;
			$ro->update_quality_metadata();
			echo $ro; 
			echo $ro->getMetadata('level_html');
		}
	
	}

	function show()
	{
		$this->load->model('registry_objects', 'ro');
		$registry_objects = $this->ro->getByDataSourceID(24);
		
		foreach($registry_objects AS $ro)
		{
			print $ro->getXML();	
		}
	
		
	}
	
	function index()
	{
		$this->load->model('registry_objects', 'ro');
		$this->load->model('rifcs', 'rifcs');
		
		$this->load->model('data_source/data_sources', 'ds');
		$data_sources = $this->ds->getAll();
		
		foreach($data_sources AS $ds)
		{
			echo BR.BR."<h1>" . $ds->title . "</h1>" . BR.BR;
			try
			{
				$this->ingestForDataSource($ds->key);
				
				print $ds->updateStats();
				die();
			}
			catch (Exception $e)
			{
				echo "UNABLE TO HARVEST FROM THIS DATA SOURCE";	
			}
		}
		
		
	}
	
	function ingestForDataSource($data_source_key)
	{
		
		// Simplexml doesn't play nicely with namespaces :-(
		$url = str_replace('xmlns="http://ands.org.au/standards/rif-cs/registryObjects"', '', file_get_contents("http://devl.ands.org.au/home/orca/services/getRegistryObjects.php?parties=party&activities=activity&services=service&collections=collection&source_key=".rawurlencode($data_source_key)));
		$xml = simplexml_load_string($url, "SimpleXMLElement", 0);
		//$xml->registerXPathNamespace("ro", "http://ands.org.au/standards/rif-cs/registryObjects");
		if ($xml === false)
		{
			throw new Exception("Could not parse XML");	
			//foreach(libxml_get_errors() as $error) {
        	//	echo "\t", $error->message;
		}
		
		foreach($xml->xpath('//registryObject') AS $registryObject)
		{
			// Determine the registry object class
			$ro_class = NULL;
			foreach ($this->ro->valid_classes AS $class)
			{
				if (property_exists($registryObject, $class))
				{
					$ro_class = $class;
				}
				
				foreach($registryObject->{$class} AS $ro_xml)
				{
					
					// Determine the record title
					$title = $this->rifcs->getRecordTitle($ro_xml);
					
					// Dummy, get record status
					$status = $this->ro->valid_status['DRAFT'];
					$record_owner = "SYSTEM";
					
					$ro = $this->ro->create($data_source_key, (string)$registryObject->key, $ro_class, "", $status, "", $record_owner);
					$ro->data_source_key = $data_source_key;
					$ro->group = (string) $registryObject['group'];
					
					// Order is important here!
					$ro->updateXML($registryObject->asXML());
					
					$ro->updateTitles();
					$ro->generateSlug();
					
					$ro->update_quality_metadata();
					
					$ro->save();
					print $ro;

					echo BR.BR.BR;
				}
				
				
			}
			
		}
				
	}
		
	
		
}
	