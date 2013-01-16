<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Core_extension extends ExtensionBase
{
	
	public $attributes = array();		// An array of attributes for this Data Source
	
	const MAX_NAME_LEN = 32;
	const MAX_VALUE_LEN = 255;
	
	function init($core_attributes_only = FALSE)
	{
		/* Initialise the "core" attributes */
		$query = $this->db->get_where("registry_objects", array('registry_object_id' => $this->id));
		
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
			throw new Exception("Unable to select Registry Object from database");
		}
			
		// If we just want more than the core attributes
		if (!$core_attributes_only)
		{
			// Lets get all the rest of the registry object attributes
			$query = $this->db->get_where("registry_object_attributes", array('registry_object_id' => $this->id));
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() AS $row)
				{
					$this->_initAttribute($row->attribute, $row->value);
				}		
			}
		}
		$this->_initAttribute("original_status", $this->attributes['status']->value);

		return $this;

	}
	
	function setAttribute($name, $value = NULL)
	{
		//if (strlen($name) > self::MAX_NAME_LEN || strlen($value) > self::MAX_VALUE_LEN)
		//{
		//	throw new Exception("Attribute name exceeds " . self::MAX_NAME_LEN . " chars or value exceeds " . self::MAX_VALUE_LEN . ". Attribute not set (NAME: ".$name." VALUE: ".$value.")"); 
		//}
	
		if(strlen($value) > self::MAX_VALUE_LEN)
			$value = substr($value, 0 ,self::MAX_VALUE_LEN);

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
				$this->attributes[$name] = new _registry_object_attribute($name, $value);
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

	function getAttributes(){
		return $this->attributes;
	}
	
	function create()
	{

		$this->setAttribute("original_status", $this->getAttribute("status"));
		
		$this->db->insert("registry_objects", array("data_source_id" => $this->getAttribute("data_source_id"), 


													"key" => (string) $this->getAttribute("key"), 
													"class" => $this->getAttribute("class"),
													"title" => $this->getAttribute("title"),
													"status" => $this->getAttribute("status"),
													"slug" => $this->getAttribute("slug"),
													"record_owner" => $this->getAttribute("record_owner")								
													));

		$this->ro->id = $this->db->insert_id();
		$this->id = $this->ro->id;
		$this->save();
		return $this;
	}
	
	function save()
	{
		// A status change triggers special business logic
		if ($this->getAttribute("status") != $this->getAttribute("original_status"))
		{
			$this->handleStatusChange($this->getAttribute("status"));
		}

		// Mark this record as recently updated
		$this->setAttribute("updated", time());

		foreach($this->attributes AS $attribute)
		{
			if ($attribute->dirty)
			{
				if ($attribute->core)
				{				
					$this->db->where("registry_object_id", $this->id);
					$this->db->update("registry_objects", array($attribute->name => $attribute->value));
					$attribute->dirty = FALSE;
					
				}
				else
				{

					if ($attribute->value !== NULL)
					{
						if ($attribute->new)
						{
							$this->db->insert("registry_object_attributes", array("registry_object_id" => $this->id, "attribute" => $attribute->name, "value"=>$attribute->value));
							$attribute->dirty = FALSE;
							$attribute->new = FALSE;
						}
						else
						{
							$this->db->where(array("registry_object_id" => $this->id, "attribute" => $attribute->name));
							$this->db->update("registry_object_attributes", array("value"=>$attribute->value));
							$attribute->dirty = FALSE;
						}
					}
					else
					{
						$this->db->where(array("registry_object_id" => $this->id, "attribute" => $attribute->name));
						$this->db->delete("registry_object_attributes");
						unset($this->attributes[$attribute->name]);
					}
						
					
				}
			}
		}
		return $this;
	}

	/* Handles the changing of status soas not to cause inconsistencies */
	function handleStatusChange($target_status)
	{
		// Changing between draft statuses, nothing to worry about:
		if (isDraftStatus($this->getAttribute('original_status')) && isDraftStatus($target_status))
		{
			// pass; 
		}
		// Else, if the draft is being published:
		else if (isDraftStatus($this->getAttribute('original_status')) && isPublishedStatus($target_status))
		{
			$existingRegistryObject = $this->_CI->ro->getPublishedByKey($this->ro->key);
			if ($existingRegistryObject)
			{
				// Delete this original draft and change this object to point to the PUBLISHED (seamless changeover)
				$this->ro = $this->_CI->ro->getPublishedByKey($this->getAttribute("key"));
				$this->_CI->ro->deleteRegistryObject($this->id);
				$this->id = $this->ro->id;
				$this->init();
			}

			// Add the XML content of this draft to the published record (and follow enrichment process, etc.)
			$this->_CI->load->model('data_source/data_sources', 'ds');
			$this->_CI->importer->_reset();
			$this->_CI->importer->setXML(wrapRegistryObjects($this->ro->getRif()));
			$this->_CI->importer->setDatasource($this->_CI->ds->getByID($this->getAttribute('data_source_id')));
			$this->_CI->importer->forcePublish();
			$this->_CI->importer->commit();

			if ($error_log = $this->_CI->importer->getErrors())
			{
				throw new Exception("Errors occured whilst migrating to PUBLISHED status: " . NL . $error_log);
			}
		}
		else // Else, the PUBLISHED record is being converted to a DRAFT
		{
			$existingRegistryObject = $this->_CI->ro->getDraftByKey($this->ro->key);
			if ($existingRegistryObject)
			{
				// Delete any existing drafts (effectively overwriting them)
				$this->_CI->ro->deleteRegistryObject($existingRegistryObject->id);
			}

			// Reenrich related records (reindexes affected records)
			// XXX: REENRICH RECORDS RELATED TO ME WHEN I CHANGE STATUS
			/*
			$reenrich_queue = $target_ro->getRelationships();
			$this->_CI->importer->_enrichRecords($reenrich_queue);
			$this->_CI->importer->_reindexRecords($reenrich_queue);
			*/
			$this->ro->slug = DRAFT_RECORD_SLUG . $this->ro->id;
		}

		$this->_initAttribute("original_status", $target_status);
	}

	/* Removes all trace of the record from the database (use this wisely...) */
	function eraseFromDatabase()
	{
		$this->db->delete('registry_object_relationships', array('registry_object_id'=>$this->id));
		$this->db->delete('registry_object_metadata', array('registry_object_id'=>$this->id));
		$this->db->delete('registry_object_attributes', array('registry_object_id'=>$this->id));
		$this->db->delete('record_data', array('registry_object_id'=>$this->id));
		$this->db->delete('url_mappings', array('registry_object_id'=>$this->id));
		$this->db->delete('spatial_extents', array('registry_object_id'=>$this->id));
		$this->db->delete('registry_objects', array('registry_object_id'=>$this->id));

		return $this;
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
		setAttribute($name, NULL);
	}
	
		
	function _initAttribute($name, $value, $core=FALSE)
	{
		$this->attributes[$name] = new _registry_object_attribute($name, $value);
		if ($core)
		{
			$this->attributes[$name]->core = TRUE;
		}
	}
	
	function getID()
	{
		return $this->id;
	}
	
	
		
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}
	
}


/**
 * Registry Object Attribute
 * 
 * A representation of attributes of a Registry Object, allowing
 * the state of the attribute to be mainted, so that calls
 * to ->save() only write dirty data to the database.
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @version 0.1
 * @package ands/registryobject
 * @subpackage helpers
 * 
 */
class _registry_object_attribute 
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