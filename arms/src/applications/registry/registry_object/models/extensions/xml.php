<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class XML_Extension extends ExtensionBase
{
	
	private $_xml;	// internal pointer for RIFCS XML
	private $_simplexml;
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	
	/*
	 * Record data methods
	 */
	 
	function getXML($record_data_id = NULL)
	{
		if (!is_null($this->_xml) && $this->_xml->record_data_id == $record_data_id)
		{
			return $this->_xml->xml;
		}
		else
		{
			$this->_xml = new _xml($this->ro->id, $record_data_id);
			//return html_entity_decode($this->_xml->xml, ENT_QUOTES, "utf-8");
			return $this->_xml->xml;
		}
	}
	
	function getSimpleXML($record_data_id = NULL)
	{
		if (!is_null($this->_simplexml) && $this->_xml->record_data_id == $record_data_id)
		{
			return $this->_simplexml;
		}
		else
		{
			$xml = $this->getXML($record_data_id);
			$this->_simplexml = simplexml_load_string(str_replace('&','&amp;',$xml));
			return $this->_simplexml;
		}
	}
	
		 
	function updateXML($data, $current = TRUE, $scheme = NULL)
	{
			$_xml = new _xml($this->ro->id);
			$_xml->update($data, $current, $scheme); 
			$this->_xml = $_xml;
			$this->_simplexml = simplexml_load_string($_xml->xml);
	}
	
	
	function getXMLVersions()
	{
		$versions = array();
		$result = $this->db->select('id, timestamp, scheme, current')->get_where('record_data', array('registry_object_id'=>$this->ro->id));
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$versions[] = $row;
			}
		}
		$result->free_result();
		return $versions;
	}

	function getExtRif()
	{
		$data = false;
		$result = $this->db->select('data')->order_by('timestamp','desc')->limit(1)->get_where('record_data', array('registry_object_id'=>$this->ro->id, 'scheme'=>'extrif'));
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$data = $row['data'];
			}
		}
		$result->free_result();
		return $data;
	}

	function getExtRifDataRecord($id){
		$data = false;
		$result = $this->db->select('data')->limit(1)->get_where('record_data', array('id'=>$id, 'scheme'=>'extrif'));
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$data = $row['data'];
			}
		}
		$result->free_result();
		return $data;
	}

	function getRif(){
		$data = false;
		$result = $this->db->select('data')->order_by('timestamp','desc')->limit(1)->get_where('record_data', array('registry_object_id'=>$this->ro->id, 'scheme'=>'rif'));
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$data = $row['data'];
			}
		}
		$result->free_result();
		return $data;
	}

}



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
			return $this->init($record_data_id);
		}
		return $this;
	}	
	
	
	function init($record_data_id)
	{
		if (is_null($record_data_id))
		{
			$query = $this->db->order_by('timestamp','DESC')->get_where('record_data', array('registry_object_id' => $this->registry_object_id), 1);
		}
		else 
		{
			$query = $this->db->order_by('timestamp','DESC')->get_where('record_data', array('id' => $record_data_id), 1);
		}
	
		if ($query->num_rows() == 1)
		{
			$result = array_pop($query->result_array());
			$query->free_result();
			$this->xml = $result['data'];
			$this->timestamp = $result['timestamp'];
			$this->scheme = $result['scheme'];	
			$this->record_data_id = $result['id'];
		}
		return $this;
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
	
	