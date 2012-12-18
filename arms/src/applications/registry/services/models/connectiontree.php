<?php

class ConnectionTree extends CI_Model
{
	public $parent_relationships = array("isPartOf");
	public $child_relationships = array("hasPart");
	public $max_width = 5;
	public $published_only = TRUE;
	public $root_ro_key;


	function get($root_registry_object, $depth, $published_only)
	{
		$this->published_only = $published_only;

		if ($root_registry_object)
		{
			$this->root_ro_key = $root_registry_object->key;
			$relationship_tree = $this->getChildren($root_registry_object->id, $depth);
			$mappings = $this->getParentMapping(
								array("registry_object_id" => $root_registry_object->id, "slug"=>$root_registry_object->slug), 
								$relationship_tree
						);
			if (count($mappings) > 0)
			{
				return $this->formatMappingForGoogleCharts($mappings, $root_registry_object);
			}
			else
			{
				return null;
			}
		}
		else
		{
			return null;
		}
	}

	function formatMappingForGoogleCharts($mappings, $root_registry_object)
	{
		foreach($mappings AS &$map)
		{
			$key = ($this->published_only ? $map[1]['slug'] : $map[1]['registry_object_id']);
			$link = ($this->published_only ? $map[1]['slug'] : "view/?id=" . $map[1]['registry_object_id']);
			$map = 	array( 
							array("v" => $key, "f" => $map[1]['title']),
							$map[0],
							"",
							$link
					);
		}
		// Add in the root element
		$link = ($this->published_only ? $root_registry_object->slug : "view/?id=" . $root_registry_object->id);
		array_unshift($mappings, array(array("v"=>($this->published_only ? $root_registry_object->slug : $root_registry_object->id), 
												"f"=>$this->formatNodeForGoogleCharts($root_registry_object)), "", "", $link));
		return $mappings;
	}

	function formatNodeForGoogleCharts($node)
	{
		$key = ($this->published_only ? $node->slug : $node->id);

		base_url();

		$html = $node->title;

		return $html;
	}

	function getParentMapping($root, $tree)
	{
		$relationships = array();

		foreach ($tree AS $branch)
		{
			if (isset($branch['children']) && is_array($branch['children']))
			{
				
				$relationships = array_merge($relationships, $this->getParentMapping($branch, $branch['children']));
				unset($branch['children']);
			}
			
			$relationships[] = array( ($this->published_only ? $root['slug'] : $root['id']), $branch);
			
			
		}
		return $relationships;
	}


	function getChildren($root_registry_object_id, $depth)
	{
		$my_children = array();
		$depth--;

		$this->load->model('registry_object/registry_objects','ro');
		$root_registry_object = $this->ro->getByID($root_registry_object_id);

		if (!$root_registry_object) { return array(); }

		/* Explicit relationships (i.e. `a` hasPart `b`) */
		$this->db->select('r.registry_object_id, r.class, r.title, r.slug, r.status, rr.relation_type')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.related_object_key = r.key')
						 ->where('rr.registry_object_id',$root_registry_object->id)
						 ->where_in('rr.relation_type',$this->child_relationships)
						 ->limit($this->max_width);
		if ($this->published_only) 
		{
			$this->db->where('r.status', PUBLISHED);
		}
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			if ($depth > 0)
			{
				$row['children'] = $this->getChildren($row['registry_object_id'], $depth);	
			}
			$my_children[$row['registry_object_id']] = $row;
		}

		/* Inverse relationships (i.e. `b` isPartOf `a`) */
		$this->db->select('r.registry_object_id, r.class, r.title, r.slug, r.status, rr.relation_type')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id')
						 ->where('rr.related_object_key',$root_registry_object->key)
						 ->where_in('rr.relation_type',$this->parent_relationships)
						 ->limit($this->max_width);
		if ($this->published_only) 
		{
			$this->db->where('r.status', PUBLISHED);
		}
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			if ($depth > 0)
			{
				$row['children'] = $this->getChildren($row['registry_object_id'], $depth);	
			}
			$my_children[$row['registry_object_id']] = $row;
		}

		return $my_children;
	}


}