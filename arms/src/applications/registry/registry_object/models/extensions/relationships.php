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

		/* Explicit relationships */
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		foreach ($sxml->xpath('//ro:relatedObject') AS $related_object)
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

		/* Create primary relationships links */
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		if ($ds->create_primary_relationships == DB_TRUE && $ds->primary_key_1 && $ds->primary_key_1 != $this->ro->key)
		{
			$this_relationship = $ds->{strtolower($this->ro->class) . "_rel_1"};
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $ds->primary_key_1,
						'related_object_class'=> (string) $ds->class_1,
						"relation_type" => (string) $this_relationship
				)
			);
		}

		if ($ds->create_primary_relationships == DB_TRUE && $ds->primary_key_2 && $ds->primary_key_2 != $this->ro->key)
		{
			$this_relationship = $ds->{strtolower($this->ro->class) . "_rel_2"};
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $ds->primary_key_2,
						'related_object_class'=> (string) $ds->class_2,
						"relation_type" => (string) $this_relationship
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
		$this->db->select('r.title, r.registry_object_id as related_id, r.class as class, rr.*')
				 ->from('registry_object_relationships rr')
				 ->join('registry_objects r','rr.related_object_key = r.key','left')
				 ->where('rr.registry_object_id',(string)$this->ro->id)
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