<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Data Sources PHP object
 * 
 * This class defines the PHP object representation of 
 * data sources. Objects can be initialised, modified 
 * and saved, abstracting away the underlying attribute
 * structure. 
 * 
 * "Core" attributes must be initialised before a registry
 * object can be created. 
 * 
 * <code>
 * 	// Creating a new data source 
	$ds = new _data_source();
		
		// Compulsory attributes
		$ds->_initAttribute("key","test.test3", TRUE);
		$ds->_initAttribute("slug","testtest3", TRUE);
		
		// Some extras
		$ds->setAttribute("record_owner","Tran");

		$ds->create();
		print "New DS received ID " . $ds->getID();

		
		// Updating a data source

		$ds = new _data_source(5);
		$ds->record_owner = "Bob";
		print $ds->save();
 * </code>
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/datasource
 * @subpackage helpers
 */
class _data_source {
	
	private $id; 	// the unique ID for this data source
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $db; 	// another internal reference to save typing!
	
	public $attributes = array();		// An array of attributes for this Data Source
	const MAX_NAME_LEN = 32;
	const MAX_VALUE_LEN = 255;

	public $stockAttributes = array('title'=>'','record_owner'=>'','contact_name'=>'-----', 'contact_email'=>'-----', 'provider_type'=>'RIF','notes'=>'-----');
	public $extendedAttributes = array('allow_reverse_internal_links'=>true,'allow_reverse_external_links'=>true,'auto_publish'=>true,'qa_flag'=>false,'create_primary_relationships'=>false,'assessment_notify_email_addr'=>'');
	public $harvesterParams = array('uri'=>'----- ','harvest_method'=>'DIRECT','harvest_date'=>'','oai_set'=>'','advanced_harvest_mode'=>'STANDARD');
	public $primaryRelationship = array('class_1','class_2','primary_key_1','primary_key_2','collection_rel_1','collection_rel_2','activity_rel_1','activity_rel_2','party_rel_1','party_rel_2','service_rel_1','service_rel_2');
	public $institutionPages = array('institution_pages');
	
	function __construct($id = NULL, $core_attributes_only = FALSE)
	{
		if (!is_numeric($id) && !is_null($id)) 
		{
			throw new Exception("Data Source Wrapper must be initialised with a numeric Identifier");
		}
		
		$this->id = $id;				// Set this object's ID
		$this->_CI =& get_instance();	// Get a pointer to the framework's instance
		$this->db =& $this->_CI->db;	// Shorthand pointer to database
		
		if (!is_null($id))
		{
			$this->init($core_attributes_only);
		}
	}
	
	
	function getID()
	{
		return $this->id;
	}
	
	function init($core_attributes_only = FALSE)
	{
	
	
		/* Initialise the "core" attributes */
		$query = $this->db->get_where("data_sources", array('data_source_id' => $this->id));
		
		if ($query->num_rows() == 1)
		{
			$core_attributes = $query->row();	
			foreach($core_attributes AS $name => $value)
			{
				$this->_initAttribute($name, $value, TRUE);
			}
		}
		else 
		{
			throw new Exception("Unable to select Data Source from database");
		}
			
		// If we just want more than the core attributes
		if (!$core_attributes_only)
		{
			// Lets get all the rest of the data source attributes
			$query = $this->db->get_where("data_source_attributes", array('data_source_id' => $this->id));
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() AS $row)
				{
					$this->_initAttribute($row->attribute, $row->value);
				}		
			}
		}
		return $this;

	}
	
	function setAttribute($name, $value = NULL)
	{
		if (strlen($name) > self::MAX_NAME_LEN || strlen($value) > self::MAX_VALUE_LEN)
		{
			throw new Exception("Attribute name exceeds " . self::MAX_NAME_LEN . " chars or value exceeds " . self::MAX_VALUE_LEN . ". Attribute not set"); 
		}
	
		// setAttribute
		if ($value !== NULL)
		{
			if (isset($this->attributes[$name]))
			{
				if ($this->attributes[$name]->value != $value)
				{
					// Attribute already exists, we're just updating it
					$this->attributes[$name]->value = $value;
					$this->attributes[$name]->dirty = TRUE;
				}
			}
			else 
			{
				// This is a new attribute that needs to be created when we save
				$this->attributes[$name] = new _data_source_attribute($name, $value);
				$this->attributes[$name]->dirty = TRUE;
				$this->attributes[$name]->new = TRUE;
			}
		}
		else
		{
			if (isset($this->attributes[$name]))
			{
				$this->attributes[$name]->value = NULL;
				$this->attributes[$name]->dirty = TRUE;
			}			
		}
		
		return $this;
	}
	
	function create()
	{
		$this->db->insert("data_sources", array("data_source_id" => $this->id, "key" => $this->getAttribute("key"), "slug" => $this->getAttribute("slug")));
		$this->id = $this->db->insert_id();
		$this->save();
		return $this;
	}
	
	function save()
	{
		// Mark this record as recently updated
		$this->setAttribute("updated", time());

		foreach($this->attributes AS $attribute)
		{

			if($attribute->name=='title') 
			{
				//if the user has changed the datasource's title then we need to update the datsources slug based on that title;
				$this->setSlug($attribute->value);
			}

			if ($attribute->dirty)
			{
				
				if ($attribute->core)
				{
					$theUpdate=array();
					$theUpdate[$attribute->name] =$attribute->value;
					$this->db->where("data_source_id", $this->id);
					$this->db->update("data_sources", $theUpdate);
					$attribute->dirty = FALSE;
				}
				else
				{

					if ($attribute->value !== NULL)
					{
						if ($attribute->new)
						{
							$this->db->insert("data_source_attributes", array("data_source_id" => $this->id, "attribute" => $attribute->name, "value"=>$attribute->value));
							$attribute->dirty = FALSE;
							$attribute->new = FALSE;
						}
						else
						{
							$this->db->where(array("data_source_id" => $this->id, "attribute" => $attribute->name));
							$this->db->update("data_source_attributes", array("value"=>$attribute->value));
							$attribute->dirty = FALSE;
						}
					}
					else
					{
						$this->db->where(array("data_source_id" => $this->id, "attribute" => $attribute->name));
						$this->db->delete("data_source_attributes");
						unset($this->attributes[$attribute->name]);
					}
				}


			}
		}
		return $this;
	}
	
	function setSlug($title)
	{

		$result = strtolower($title);	
		$result = preg_replace("/[^a-z0-9\s-]/", "", $result);
		$result = trim(preg_replace("/[\s-]+/", " ", $result));
		$result = trim(substr($result, 0, self::MAX_VALUE_LEN));
		$result = preg_replace("/\s/", "-", $result);

		$query_ds_slugs = $this->db->select('data_source_id')->get_where('data_sources',array("slug"=> $result));

		if($query_ds_slugs->num_rows==0){

			$this->setAttribute("slug", $result);

		}
		else if($query_ds_slugs->num_rows>0)
		{
			$existing_slug = array_pop($query_ds_slugs->result_array());

			if($existing_slug['data_source_id']!=$this->id)
			{
				$this->setAttribute("slug", $result."-");
			}

		}

	}

	function getAttribute($name, $graceful = TRUE)
	{
		if (isset($this->attributes[$name]) && $this->attributes[$name] != NULL) 
		{
			return $this->attributes[$name]->value;			
		}
		else if (!$graceful)
		{
			throw new Exception("Unknown/NULL attribute requested by getAttribute($name) method");
		}
		else
		{
			return NULL;
		}
	}
	
	function unsetAttribute($name)
	{
		$this->setAttribute($name, NULL);
	}
	
	
	function attributes()
	{
		$attributes = array();
		foreach ($this->attributes AS $attribute)
		{
			$attributes[$attribute->name] = $attribute->value;
		}
		return $attributes;
	}
		
	function _initAttribute($name, $value, $core=FALSE)
	{
		$this->attributes[$name] = new _data_source_attribute($name, $value);
		if ($core)
		{
			$this->attributes[$name]->core = TRUE;
		}
	}
	/*
	 * CONTRIBUTOR PAGES
	 */

	function get_groups()
	{
		
		$groups = array();

		$this->db->select('value');
		$this->db->from('registry_object_attributes');
		$this->db->join('registry_objects', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id');
		$this->db->where(array('registry_objects.data_source_id'=>$this->id, 'registry_object_attributes.attribute'=>'group'));
		$query = $this->db->get();

		if ($query->num_rows() == 0)
		{
			return $groups;
		}
		else
		{				
			foreach($query->result_array() AS $group)
			{
				$groups[] =  $group['value'];
			}
		}

		return array_unique($groups);
	
	}
	function get_group_contributor($group)
	{
		
		$contributor = '';

		$this->db->select('registry_objects.key,institutional_pages.authorative_data_source_id');
		$this->db->from('registry_objects');
		$this->db->join('institutional_pages', 'institutional_pages.registry_object_id = registry_objects.registry_object_id');
		$this->db->where(array('institutional_pages.group'=>$group,));
	
		$query = $this->db->get();


		if ($query->num_rows() == 0)
		{

			return $contributor;
		}
		else
		{				
			foreach($query->result_array() AS $contributors)
			{
				
				$contributor['key'] =  $contributors['key'];
				$contributor['authorative_data_source_id'] = $contributors['authorative_data_source_id']; 

			}

		}

		return $contributor;
	
	}	

	 function setContributorPages($value,$inputvalues)
	{
		$data_source_id = $this->id;

		$this->_CI->load->model("registry_object/registry_objects", "ro");
		$this->_CI->load->model("registry_object/rifcs_generator", "rifcs");
		$groups = $this->get_groups();

		$pages = array();
		switch($value)
		{
			case "0":
				//remove any reference to a contibutor page for this datasource from the institutional_pages db table
				$delete = $this->db->delete('institutional_pages', array('authorative_data_source_id'=>$data_source_id));
				break;
			case "1":
				// for each group for this datasource that is not already managed by another datasource
					foreach($groups as $group)
					{

						$query = '';
						$manageGroup[$group] = true;	
						//check that another ds is not the authoritive ds
						$query = $this->db->get_where('institutional_pages', array('group'=>$group));
						$this->_CI->load->library('importer');

						if($query->num_rows > 0)
						{
							foreach($query->result_array() AS $foundPage)
							{ 
					
								if($foundPage['authorative_data_source_id']==$data_source_id)
								{
									//we want to delete this record and reinsert it								
									$this->db->delete('institutional_pages', array('group'=>$group));
								}else{
									//we want to leave this group alone if the group belongs to another ds

									$manageGroup[$group] = false;
								}			
							}
						}
						if($manageGroup[$group])
						{
							$registry_object_key = 'contributor:'.$group;
							$data_source_key = $this->key;
							$title=$group;
							$contributorPage = $this->_CI->ro->getAllByKey('contributor:'.$group);
							if(!$contributorPage)
							{
								//we need to automatically create the group party record if it dosn't exist				
								$xml = wrapRegistryObjects($this->_CI->rifcs->xml($data_source_key ,$registry_object_key,$title,$group));
								$this->_CI->importer->setXML($xml);
								$this->_CI->importer->setDatasource($this);
								$the_key = $this->_CI->importer->commit();
								$this->_CI->importer->forceDraft();		
								$contributorPage = $this->_CI->ro->getAllByKey($registry_object_key);		

							} 

							$registry_object_id = $contributorPage[0]->id;
	
							//we need to add the  group , registry_object_id and autoritive datasource to the institutional_pages table
							$data = array(
								"id"=>null,
								"group"=> (string)$group,
								"registry_object_id"=>$registry_object_id,
								"authorative_data_source_id" => $data_source_id
								);
							$insert = $this->db->insert('institutional_pages',$data);
						}
					}
				break;
			case "2":

				// for each group for this datasource that is not already managed by another datasource
					foreach($groups as $group)
					{
						$manageGroup = true;
						$query = '';
						//check that another ds is not the authoritive ds
						$query = $this->db->get_where('institutional_pages', array('group'=>$group));

						if($query->num_rows > 0)
						{
							foreach($query->result_array() AS $foundPage)
							if($foundPage['authorative_data_source_id']==$data_source_id)
							{
								//we want to delete this record and reinsert it							
								$this->db->delete('institutional_pages', array('group'=>$group));
							}else{
								//we want to leave this group alone if the group belongs to another ds
								$manageGroup = false;
							}
						}

						if($manageGroup)
						{
							
							$registry_object_key = $inputvalues[str_replace(" ","_",$group)];
							if($registry_object_key!='')
							{
								$contributorPage = $this->_CI->ro->getAllByKey($registry_object_key);
				
								if(isset($contributorPage[0]->id))
								{
									$registry_object_id = $contributorPage[0]->id;
									//we need to add the  group , registry_object_id and autoritive datasource to the institutional_pages table
									$data = array(
										"id"=>null,
										"group"=> (string)$group,
										"registry_object_id"=>$registry_object_id,
										"authorative_data_source_id" => $data_source_id
										);
									$insert = $this->db->insert('institutional_pages',$data);
								}else{
									//how do we deal with the fact that its not a valid key?
									echo "not a valid registry party object";
								}
							}
						}
					}

				break;
		}
	}
	
	/*
	 * LOGS
	 */
	function append_log($log_message, $log_type = "info", $log_class="data_source", $harvester_error_type=NULL)
	{
		$this->db->insert("data_source_logs", array("data_source_id" => $this->id, "date_modified" => time(), "type" => $log_type, "log" => $log_message, "class" => $log_class,"harvester_error_type" => $harvester_error_type));
		return $this->db->insert_id();
	}
	
	function get_logs($offset = 0, $count = 10, $logid=null, $log_class='all', $log_type='all')
	{
		$logs = array();
		$this->db->from('data_source_logs');
		$this->db->limit($count, $offset);
		$this->db->where('data_source_id', $this->id);
		if($logid) $this->db->where('id >=', $logid);
		if($log_class!='all') $this->db->where('class', $log_class);
		if($log_type!='all') $this->db->where('type', $log_type);
		$this->db->order_by("id", "desc"); 
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			foreach ($query->result_array() AS $row)
			{
				$logs[] = $row;		
			}
		}
		return $logs;
	}
	
	function get_log_size($log_type)
	{
		$this->db->from("data_source_logs");
		$this->db->where(array("data_source_id"=>$this->id));
		if($log_type!='all') $this->db->where('type', $log_type);
		return $this->db->count_all_results();
	}
	
	function clear_logs()
	{
		$this->db->where(array("data_source_id" => $this->id));
		$this->db->delete("data_source_logs");
		return;
	}

	function getHarvesterStatus(){
		$query = $this->db->get_where("harvest_requests", array("data_source_id"=>$this->id,));
		if($query->num_rows()>0){
			return $query->result_array();
			//foreach($query->result_array() as $row){
			//	return $row;
			//}
		}
	}
	
	function getHarvestRequests($id = null)
	{
		$harvestRequests = array();
		$this->db->from("harvest_requests");
		if($id != null)
		$query = $this->db->where(array("data_source_id"=>$this->id, "id"=>$id));
		else 
		$query = $this->db->where(array("data_source_id"=>$this->id));
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $row)
			{
				$harvestRequests[] = $row;		
			}
		}
		return $harvestRequests;		
	}
	// TODO continue here!!!
	function insertHarvestRequest($harvestFrequency, $oaiSet, $created, $updated, $nextHarvest, $status)
	{
		$harvestRequestId = strtoupper(sha1($this->id.microtime(false)));
		date_default_timezone_set('Australia/Canberra');
		if(!$created) $created = date( 'Y-m-d\TH:i:s.uP', time());
		if(!$updated) $updated = date( 'Y-m-d\TH:i:s.uP', time());
		if(!$nextHarvest) $nextHarvest = date( 'Y-m-d\TH:i:s.uP', time());
		$this->db->insert("harvest_requests", array("data_source_id" => $this->id, "harvest_frequency" => $harvestFrequency, "oai_set" => $oaiSet, "status" => $status, "created"=>$created, "updated"=>$updated, "next_harvest"=>$nextHarvest));
		return $this->db->insert_id();		
	}
	
	function deleteHarvestRequest($harvestRequestId)
	{
		$this->db->delete("harvest_requests", array("id" => $harvestRequestId));
		return;		
	}

	function deleteOldRecords($harvest_id)
	{
		$this->append_log("TRYING TO REMOVE RECORDS FROM PREVIOUS HARVEST FROM DS: ".$this->id, HARVEST_INFO, "harvester","HARVESTER_INFO");	
		$this->_CI->load->model("registry_object/registry_objects", "ro");
		$oldRegistryObjects = $this->_CI->ro->getRecordsInDataSourceFromOldHarvest($this->id, $harvest_id);
		foreach($oldRegistryObjects AS $target_ro_id)
		{
			$target_ro = $this->_CI->ro->getByID($target_ro_id);
			$log = $target_ro->eraseFromDatabase();
			if($log)
				$this->append_log("ERROR REMOVING RECORD FROM PREVIOUS HARVEST: " .$target_ro_id, HARVEST_INFO, "harvester", "HARVESTER_INFO");
		}
		$this->append_log("REMOVING RECORDS FROM PREVIOUS HARVEST: " .sizeof($oldRegistryObjects)."DELETED", HARVEST_INFO, "harvester","HARVESTER_INFO");	
	}
	/*
	 * 	STATS
	 */
	
	function updateStats()
	{
		$this->_CI->load->model("registry_object/registry_objects", "ro");

		$this->db->where(array('data_source_id'=>$this->id));
		$this->setAttribute("count_total", ($this->db->count_all_results('registry_objects') ?: "0"));

		foreach ($this->_CI->ro->valid_classes AS $class)
		{
			$this->db->where(array('data_source_id'=>$this->id, 'class'=>$class));
			$this->setAttribute("count_$class", ($this->db->count_all_results('registry_objects') ?: "0"));
		}
		
		foreach ($this->_CI->ro->valid_status AS $status)
		{
			$this->db->where(array('data_source_id'=>$this->id, 'status'=>$status));
			$this->setAttribute("count_$status", ($this->db->count_all_results('registry_objects') ?: "0"));
		}
		foreach ($this->_CI->ro->valid_levels AS $attribute_name => $level)
		{
			// SO MUCH repetitiveness ;-(
			$this->db->join('registry_object_attributes', 'registry_object_attributes.registry_object_id = registry_objects.registry_object_id');
			$this->db->where(array('data_source_id'=>$this->id, 'attribute'=>'quality_level', 'value'=>$level));
			$this->setAttribute("count_$attribute_name", ($this->db->count_all_results('registry_objects') ?: "0"));
		}
		$this->save();
		return $this;
	}
	
	/*
	 * magic methods
	 */
	function __toString()
	{
		$return = sprintf("%s (%s) [%d]", $this->getAttribute("key", TRUE), $this->getAttribute("slug", TRUE), $this->id) . BR;
		foreach ($this->attributes AS $attribute)
		{
			$return .= sprintf("%s", $attribute) . BR;
		}
		return $return;	
	}
	
	/**
	 * This is where the magic mappings happen (i.e. $data_source->record_owner) 
	 *
	 * @ignore
	 */
	function __get($property)
	{
		if($property == "id")
		{
			return $this->id;
		}
		else
		{
			return call_user_func_array(array($this, "getAttribute"), array($property));
		}
	}
	
	/**
	 * This is where the magic mappings happen (i.e. $data_source->record_owner) 
	 *
	 * @ignore
	 */
	function __set($property, $value)
	{
		if($property == "id")
		{
			$this->id = $value;
		}
		else
		{
			return call_user_func_array(array($this, "setAttribute"), array($property, $value));
		}
	}
	
	
	function submitHarvestRequest($harvestRequest)
	{
		$runErrors = '';
		$harvesterBaseURI = $this->_CI->config->item('harvester_base_url');
		
		$resultMessage = new DOMDocument();
		$message = "";
		$result = $resultMessage->load($harvesterBaseURI.$harvestRequest);
		$errors = error_get_last();
		$logID = 0;
		if( $errors )
		{
			$logID = $this->append_log("harvestRequest Error[1]: ".$errors['message'],HARVEST_ERROR,'harvester');
		}
		else
		{
			$responseType = strtoupper($resultMessage->getElementsByTagName("response")->item(0)->getAttribute("type"));
			$message = $resultMessage->getElementsByTagName("message")->item(0)->nodeValue;
			
			if( $responseType != 'SUCCESS' )
			{
				$logID = $this->append_log("harvestRequest Error[2]: ".$message, "error", 'harvester');
			}
			else{
				$logID = $this->append_log("harvestRequest Success: ".$message, "message", 'harvester');
			}
		}
		return $logID;
	}
	
	function requestHarvest($created = '', $updated = '', $dataSourceURI = '', $providerType = '', $OAISet = '', $harvestMethod = '', $harvestDate = '', 			
		$harvestFrequency = '', $advancedHarvestingMethod = '', $nextHarvest = '', $testOnly = false)
	{
		$dataSource = $this->id;
		$responseTargetURI = base_url('data_source/putharvestData');
		
		if($created == '')
			$created = $this->getAttribute("created");		
		if($dataSourceURI == '')
			$dataSourceURI = $this->getAttribute("uri");
		
		if($OAISet == '')
			$OAISet = $this->getAttribute("oai_set");

		if($harvestMethod == '')
			$harvestMethod = $this->getAttribute("harvest_method");
		if ($harvestMethod == "rif") $harvestMethod = "RIF"; //crosswalk-introduced bugfix
	
		if($harvestDate == '')
			$harvestDate = $this->getAttribute("harvest_date");

		if($harvestFrequency == '')
			$harvestFrequency = $this->getAttribute("harvest_frequency");
			
        if($advancedHarvestingMethod = '')
        	$advancedHarvestingMethod = $this->getAttribute("advanced_harvesting_mode");
		
        if($nextHarvest == '')
			$nextHarvest = $harvestDate;	

		$status = "SCHEDULED FOR ". ($nextHarvest ? $nextHarvest : "NOW");
		
		$mode = 'harvest'; if( $testOnly ){ $mode = 'test'; }		
		
		$harvestRequestId = $this->insertHarvestRequest($harvestFrequency, $OAISet, $created, $updated, $nextHarvest, $status);
		
		$harvestRequest  = 'requestHarvest?';
		$harvestRequest .= 'responsetargeturl='.urlencode($responseTargetURI);		
		$harvestRequest .= '&harvestid='.urlencode($harvestRequestId);
		$harvestRequest .= '&sourceurl='.urlencode($dataSourceURI);
		$harvestRequest .= '&method='.urlencode($harvestMethod);
		if( $OAISet )
		{
			$harvestRequest .= '&set='.urlencode($OAISet);
		}
		$harvestRequest .= '&date='.urlencode($harvestDate);
		$harvestRequest .= '&frequency='.urlencode($harvestFrequency);
		$harvestRequest .= '&mode='.urlencode($mode);
		$harvestRequest .= '&ahm='.urlencode($advancedHarvestingMethod);
		
		// Submit the request.
		$logID = $this->submitHarvestRequest($harvestRequest);
	    return $logID;
	}
	
	function cancelHarvestRequest($harvestRequestId)
	{

	// Get the harvest request.
	//$harvestRequest = getHarvestRequests($harvestRequestId, null);
	$actions  = "DELETE HARVEST REQUEST".NL;
	$actions .= "Harvest Request ID: ".$harvestRequestId.NL;

	
	
	if( $harvestRequestId )
	{
		$harvesterBaseURI = $this->_CI->config->item('harvester_base_url');
		
		// Submit a deleteHarvestRequest to the harvester.
		$request = $harvesterBaseURI."deleteHarvestRequest?harvestid=".$harvestRequestId;
		
		// Submit the request.
		$runErrors = '';
		$resultMessage = new DOMDocument();
		$result = $resultMessage->load($request);
		$errors = error_get_last();
		if( $errors )
		{
			$runErrors = "deleteHarvestRequest Error[1]: ".$errors['message'].NL;
		}
		else
		{
			$responseType = strtoupper($resultMessage->getElementsByTagName("response")->item(0)->getAttribute("type"));
			$message = $resultMessage->getElementsByTagName("message")->item(0)->nodeValue;
			
			if( $responseType != 'SUCCESS' )
			{
				//maybe it was already deleted...
				$runErrors = "deleteHarvestRequest Error[2]: ".$message;
			}
		}
		
		if( $runErrors )
		{
			$actions .= ">>ERRORS".NL;
			$actions .= $runErrors;
		}
		else
		{
			$actions .= ">>SUCCESS".NL;
			// Remove the entry.
			


		}
		$errors = $this->deleteHarvestRequest($harvestRequestId);
		if( $errors )
		{
			$actions .= $runErrors.NL.$errors;
		}
	}
	else
	{
		$actions .= ">>ERRORS".NL;
		$actions .= 'The harvest request does not exist.';
	}

	// Log the activity.
	$logID = $this->append_log("CANCEL HARVEST REQUEST: ".$actions, "message", 'harvester');
	return $actions;
	
	}

}
/**
 * Data Source Attribute
 * 
 * A representation of attributes of a data source, allowing
 * the state of the attribute to be mainted, so that calls
 * to ->save() only write dirty data to the database.
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @version 0.1
 * @package ands/datasource
 * @subpackage helpers
 * 
 */
class _data_source_attribute 
{
	public $name;
	public $value;
	public $core = FALSE; 	// Is this attribute part of the core table or the attributes annex
	public $dirty = FALSE;	// Have we changed it since it was read from the DB
	public $new = FALSE;	// Is this new since we read from the DB
	
	function __construct($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * @ignore
	 */
	function __toString()
	{
		return sprintf("%s: %s", $this->name, $this->value) . ($this->dirty ? " (Dirty)" : "") . ($this->core ? " (Core)" : "") . ($this->new ? " (New)" : "");	
	}
}