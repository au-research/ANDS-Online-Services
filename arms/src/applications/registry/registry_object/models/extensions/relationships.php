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
		$sxml = $this->ro->getSimpleXml();

		/* Explicit relationships */
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		$explicit_keys = array();
		foreach ($sxml->xpath('//ro:relatedObject') AS $related_object)
		{
			$related_object_key = (string)$related_object->key;
			$related_object_type = (string)$related_object->relation[0]['type'];
			$related_object_relation_description = (string)$related_object->relation[0]->description;
			$related_object_relation_url = (string)$related_object->relation[0]->url;

			$result = $this->db->select('class, title')->get_where('registry_objects', array('key'=>(string)$related_object_key));
			
			$class = NULL;
			$title = 'no title';

			if ($result->num_rows() > 0)
			{
				$record = $result->result_array();
				$record = array_shift($record);
				$result->free_result();
				$class = $record['class'];
				$title = $record['title'];
			}
			
			$explicit_keys[] = (string) $related_object_key;

			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $related_object_key,
						'related_object_class'=> (string) $class,
						"relation_type" => (string) $related_object_type,
						"relation_description" => (string) $related_object_relation_description,
						"relation_url" => (string) $related_object_relation_url,
				)
			);
		}

		/* Create primary relationships links */
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		if ($ds->create_primary_relationships == DB_TRUE && $ds->primary_key_1 && $ds->primary_key_1 != $this->ro->key && !in_array($ds->primary_key_1, $explicit_keys))
		{
			$this_relationship = $ds->{strtolower($this->ro->class) . "_rel_1"};
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $ds->primary_key_1,
						'related_object_class'=> (string) $ds->class_1,
						"relation_type" => (string) $this_relationship,
						"origin" => PRIMARY_RELATIONSHIP
				)
			);
		}

		if ($ds->create_primary_relationships == DB_TRUE && $ds->primary_key_2 && $ds->primary_key_2 != $this->ro->key && !in_array($ds->primary_key_2, $explicit_keys))
		{
			$this_relationship = $ds->{strtolower($this->ro->class) . "_rel_2"};
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $ds->primary_key_2,
						'related_object_class'=> (string) $ds->class_2,
						"relation_type" => (string) $this_relationship,
						"origin" => PRIMARY_RELATIONSHIP
				)
			);
		}

		return $explicit_keys;
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
		/* Holy crap! Use getConnections to infer relationships to drafts and reverse links :-))) */
		$classes = array();
		$connections = $this->ro->getConnections(false);
		$connections = array_pop($connections);
		if (isset($connections['activity']))
		{
			$classes[] = "Activity";
		}
		if (isset($connections['collection']))
		{
			$classes[] = "Collection";
		}
		if (isset($connections['party']) || isset($connections['party_one']) || isset($connections['party_multi']) || isset($connections['contributor']))
		{
			$classes[] = "Party";
		}
		if (isset($connections['service']))
		{
			$classes[] = "Service";
		}

		return $classes;
	}
	
	function getRelatedClassesString()
	{
		$classes = "";
		$list = $this->getRelatedClasses();
		return implode($list);
	}
}