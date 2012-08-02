<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Slug_Extension extends ExtensionBase
{
	const maxLength = 255;
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	
	function generateSlug()
	{
		// This function expects a display_title to be present!
		if (!$this->ro->display_title)
		{
			$this->ro->updateTitles();
		}
	
		$result = strtolower($this->ro->title);
		
		$result = preg_replace("/[^a-z0-9\s-]/", "", $result);
		$result = trim(preg_replace("/[\s-]+/", " ", $result));
		$result = trim(substr($result, 0, self::maxLength));
		$result = preg_replace("/\s/", "-", $result);
		
		// Check that there are no clashes
		$query = $this->db->select('registry_object_id')->get_where('url_mappings',array("slug"=> $result));
		if ($query->num_rows() > 0)
		{
			$existing_slug = array_pop($query->result_array());
			$query->free_result();
			if ($existing_slug['registry_object_id'] == $this->id)
			{
				// XXX: Updated?
				$this->ro->slug = $result;
				$this->ro->save();
				return $result;
			}
			else
			{
				// this isn't guaranteed to be unique, but is likely to be
				$result .= "-" . sha1($this->id);
				$query = $this->db->select('registry_object_id')->get_where('url_mappings',array("slug"=> $result));
				if ($query->num_rows() == 0)
				{
					$this->db->insert('url_mappings', array("slug"=>$result, "registry_object_id"=>$this->id, "created"=>time(), "updated"=>time()));
				}
				else
				{
					// XXX: Updated?
				}
				$this->ro->slug = $result;
				$this->ro->save();
				return $result;
			}
			
		}
		else 
		{
			//Assume this is the first time
			$this->db->insert('url_mappings', array("slug"=>$result, "registry_object_id"=>$this->id, "created"=>time(), "updated"=>time()));
			$this->ro->slug = $result;
			$this->ro->save();
			return $result;
		}

	}
	
	function getAllSlugs()
	{
		$slugs = array();
		
		$query = $this->db->select("slug, created, updated")->get_where('url_mappings', array("registry_object_id"=>$this->id));
		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() AS $row)
			{
				$slugs[] = $row;	
			}
		}
		$query->free_result();
		return $slugs;
	}
}
	
	