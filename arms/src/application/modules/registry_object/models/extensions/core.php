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
			// Lets get all the rest of the data source attributes
			$query = $this->db->get_where("registry_object_attributes", array('registry_object_id' => $this->id));
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