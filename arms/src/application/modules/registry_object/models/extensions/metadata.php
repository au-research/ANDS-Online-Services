<?php


class Metadata_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
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
}