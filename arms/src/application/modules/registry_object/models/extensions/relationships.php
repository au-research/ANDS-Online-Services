<?php


class Relationships_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	function add_relationships()
	{
		$this->db->where('registry_object_id' => $this->ro->id);
		$this->db->delete();	
	}
}