<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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
class _xml
{
	const DEFAULT_SCHEME = "rif";
	
	public $registry_object_id;
	public $record_data_id;
	public $_CI;
	public $db;
	public $xml;
	public $current;
	public $timestamp;
	public $scheme = "rif";
	
	function __construct($registry_object_id = NULL, $record_data_id = NULL)
	{
		if (!is_numeric($registry_object_id) && !is_null($registry_object_id)) 
		{
			throw new Exception("Registry Object _xml class must be initialised with a numeric Identifier");
		}
		
		$this->registry_object_id = $registry_object_id;	// Set this object's ID
		$this->record_data_id = $record_data_id;
		$this->_CI =& get_instance();						// Get a pointer to the framework's instance
		$this->db =& $this->_CI->db;						// Shorthand pointer to database
		
		if (!is_null($registry_object_id))
		{
			$this->init($record_data_id);
		}
	}	
	
	
	function init($record_data_id)
	{
		if (!is_null($record_data_id))
		{
			$result = $this->db->get_where('record_data', array('registry_object_id' => $this->registry_object_id, 'current' => 'TRUE'), 1);
		}
		else 
		{
			$result = $this->db->get_where('record_data', array('id' => $record_data_id), 1);
		}
		
		if ($result->num_rows() == 1)
		{
			$result = $result->result_array();
			$this->xml = $result['data'];
			$this->timestamp = $result['timestamp'];
			$this->scheme = $result['scheme'];	
			$this->record_data_id = $result['id'];
		}
	}
	
	function update($xml, $current = TRUE, $scheme = NULL)
	{
		if (is_null($scheme)) { $scheme = self::DEFAULT_SCHEME; }
		
		$this->xml = $xml;
		$this->current = $current;
		$this->scheme = $scheme;
		
		$this->db->insert('record_data', array(
												'registry_object_id'=>$this->registry_object_id,
												'data' => $xml,
												'timestamp' => time(),
												'current' => ($current ? "TRUE" : "FALSE"),
												'scheme' => $scheme
											));
													
	}
}
	