<?php


class Relationships_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	function addRelationships()
	{
		// Delete any old relationships (we only run this on ingest, so its a once-off update)
		$this->db->where(array('registry_object_id' => $this->ro->id));
		$this->db->delete('registry_object_relationships');	
		// maybe ADD a getSimpleXML() method!!
		$sxml = $this->ro->getSimpleXml();
		foreach ($sxml->xpath('//'.$this->ro->class.'/relatedObject/key') AS $related_object_key)
		{
			$result = $this->db->select('class')->get_where('registry_objects', array('key'=>(string)$related_object_key));
			$class = NULL;
			if ($result->num_rows() > 0)
			{
				$class = array_shift($result->result_array());
				$result->free_result();
				$class = $class['class'];
			}
			
			$this->db->insert('registry_object_relationships', array("registry_object_id"=>$this->ro->id, "related_object_key" => (string)$related_object_key,'related_object_class'=>$class));
		}
	}
	
	function getRelatedClasses()
	{
		// Delete any old relationships (we only run this on ingest, so its a once-off update)
		$this->db->where(array('registry_object_id' => $this->ro->id));
		$this->db->select('DISTINCT(related_object_class)', FALSE)->from('registry_object_relationships');
		$result = $this->db->get();	
		$classes = array();
		foreach($result->result_array() AS $class)
		{
			$classes[] = $class['related_object_class'];
		}
		$result->free_result();
		return $classes;
	}
	
	function getRelatedClassesString()
	{
		$classes = "";
		$list = $this->getRelatedClasses();
		foreach($list AS $item)
		{
			$classes.=ucfirst($item);
		}
		return $classes;
	}
}