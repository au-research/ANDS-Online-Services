<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
include_once("_xml.php");

/**
 * Registry Object PHP object
 * 
 * XXX:
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/datasource
 * @subpackage helpers
 */
class _registry_object {
	
	private $id; 	// the unique ID for this data source
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $db; 	// another internal reference to save typing!
	private $_xml;	// internal pointer for RIFCS XML
	
	public $attributes = array();		// An array of attributes for this Data Source
	const MAX_NAME_LEN = 32;
	const MAX_VALUE_LEN = 255;
	
	function __construct($id = NULL, $core_attributes_only = FALSE)
	{		
		if (!is_numeric($id) && !is_null($id)) 
		{
			throw new Exception("Registry Object Wrapper must be initialised with a numeric Identifier");
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
	
	/*
	 * 	Metadata operations
	 */
	function get_metadata($name, $graceful = TRUE)
	{
		$query = $this->db->get_where("registry_object_metadata", array('registry_object_id' => $this->id, 'attribute' => $name));
		if ($query->num_rows() == 1)
		{
			return $query->result_array();
		}
		else if (!$graceful)
		{
			throw new Exception("Unknown/NULL metadata attribute requested by get_metadata($name) method");
		}
		else
		{
			return NULL;
		}
	}
	
	function set_metadata($name, $value = '')
	{
		$query = $this->db->get_where("registry_object_metadata", array('registry_object_id' => $this->id, 'attribute' => $name));
		if ($query->num_rows() == 1)
		{
			$this->db->where(array('registry_object_id'=>$this->id, 'attribute'=>$name));
			$this->db->update('registry_object_metadata', array('value'=>$value));
		}
		else
		{
			$this->db->insert('registry_object_metadata', array('registry_object_id'=>$this->id, 'attribute'=>$name, 'value'=>$value));
		}
	}
	
	
	/*
	 * Record data methods
	 */
	 
	function getXML($record_data_id = NULL)
	{
		if (!is_null($_xml) && $_xml->record_data_id == $record_data_id)
		{
			return $_xml->xml;
		}
		else
		{
			$_xml = new _xml($this->id, $record_data_id);
			return $_xml->xml;
		}
	}
	
		 
	function updateXML($data, $current = TRUE, $scheme = NULL)
	{
			$_xml = new _xml($this->id);
			$_xml->update($data, $current, $scheme); 
	}
	
	
	function getXMLVersions()
	{
		$versions = array();
		$result = $this->db->select('id, timestamp, scheme, current')->get_where('record_data', array('registry_object_id'=>$this->id));
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$versions[] = $row;
			}
		}
		return $versions;
	}
	
	
	
	/*
	 * magic methods
	 */
	function __toString()
	{
		$return = sprintf("%s (%s) [%d]", $this->getAttribute("key", TRUE), $this->getAttribute("status", TRUE), $this->id) . BR;
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