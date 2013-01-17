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

		$related_keys = array();
		$sxml = $this->ro->getSimpleXml();
		foreach ($sxml->xpath('//'.$this->ro->class.'/relatedObject') AS $related_object)
		{
			$related_object_key = (string)$related_object->key;
			$related_object_type = (string)$related_object->relation[0]['type'];

			$result = $this->db->select('class')->get_where('registry_objects', array('key'=>(string)$related_object_key));
			$class = NULL;
			if ($result->num_rows() > 0)
			{
				$class = array_shift($result->result_array());
				$result->free_result();
				$class = $class['class'];
			}
			$related_keys[] = (string)$related_object_key;
			$title =  $this->db->select('title')->get_where('registry_objects', array('key'=>(string)$related_object_key));
			if ($title->num_rows() > 0)
			{
				$title = array_shift($title->result_array());
				$result->free_result();
				$title = $title['title'];
			}
			else{
				$title = 'no title';
			}
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $related_object_key,
						'related_object_class'=> (string) $class,
						"relation_type" => (string) $related_object_type
				)
			);
		}

		return $related_keys;
	}

	function getRelationships()
	{
		$related_keys = array();
		$result = $this->db->select('related_object_key')->get_where('registry_object_relationships', array('registry_object_id'=>(string)$this->ro->id));
		foreach ($result->result_array() AS $row)
		{
			$related_keys[] = $row['related_object_key'];
		}
		return $related_keys;
	}


	function getRelatedObjects()
	{
		$my_connections = array();
		$this->db->select('r.title, r.registry_object_id as related_id, a.value as related_object_type, rr.*')
				 ->from('registry_object_relationships rr')
				 ->join('registry_objects r','rr.related_object_key = r.key','left')
				 ->join('registry_object_attributes a','a.registry_object_id = r.registry_object_id')
				 ->where('rr.registry_object_id',(string)$this->ro->id)
				 ->where('a.attribute','type')
				 ->where('r.status','PUBLISHED');
		$query = $this->db->get();
		foreach ($query->result_array() AS $row)
		{
			$my_connections[] = $row;
		}

		return $my_connections;
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