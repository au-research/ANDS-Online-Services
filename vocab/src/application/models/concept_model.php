<?php
/*
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/

class Concept_model extends CI_Model {
	
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get all concept schemes which match the filter (if set)
     */
    public function getAllConceptSchemes($filter_id='', $filter_version='')
    {    
    	$conceptList = array();
    	
    	$this->db->where("object_class","VOCABULARY");
    	$this->db->where("disabled","0");
    	if ($filter_id !== '')
    	{
    		$this->db->where("(object_id = ".$this->db->escape($filter_id)." OR object_version = ".$this->db->escape($filter_version).")");
    	}
    	$query = $this->db->get("dba.tbl_vocab_objects");
    	$this->db->flush_cache();
    	
    	foreach ($query->result() AS $row)
    	{   		
    		    // Join to all appropriate attributes
    			$row->attributes = $this->getObjectAttributes($row->object_id, $row->object_version);
    			$row->relationships = $this->getObjectRelationships($row->object_id, $row->object_version);
    			$conceptList[] = $row;
    	}
    	
    	return $conceptList;
    }
    
    
    public function getConcepts($vocab_id, $vocab_version='', $identifier='', array $depth_configuration)
    {
    	$conceptList = array();
    	if (!$vocab_id) { return $conceptList; }
    	$vocab_id = strtoupper($vocab_id);
   
    	// First try to get the objects from the params (in order)
    	$this->db->where("object_class","TERM");
    	$this->db->where("disabled","0");
    	
    	// If no identifier is specified, we get all terms in the vocabulary (and weed out non-topConcepts later)
    	if ($identifier)
    	{
    		$this->db->where("object_id", $identifier);
    	}
    	
    	$this->db->where("object_version", $vocab_version);  

    	$query = $this->db->get("dba.tbl_vocab_objects");
    	$this->db->flush_cache();

    	// No matching items...try using the default version and pushing up the identifier
    	if ($query->num_rows() == 0)
    	{
    		$identifier = $vocab_version;
	    	$vocab_version = $this->db->query("SELECT object_version 
	    												FROM dba.tbl_vocab_objects 
	    												WHERE object_id = ? AND object_class = ? 
	    												ORDER BY object_created DESC 
	    												LIMIT 1",
	    											array($vocab_id, "VOCABULARY"));
	    						
	    	if ($vocab_version->num_rows() > 0)
	    	{
		    	foreach ($vocab_version->result() as $result)
		    	{
		    		$vocab_version = $result->object_version . "";
		    	}
    		}
    		else 
    		{
    			$vocab_version = false;
    		}
	    	
	    	$this->db->where("object_class","TERM");
    		$this->db->where("disabled","0");
    		if ($identifier)
	    	{
	    		$this->db->where("object_id", $identifier);
	    	}
	    	
	    	if($vocab_version)
	    	{
	    		$this->db->where("object_version", $vocab_version . "");  
	    	}
	    	
	    	$query = $this->db->get("dba.tbl_vocab_objects");
	    	$this->db->flush_cache();
    	}
    	
    	foreach ($query->result() AS $row)
    	{  
    			// Check if in the correct vocab/version
    			$this->db->where("source_object_id", $row->object_id);
    			$this->db->where("source_object_version", $row->object_version);
    			$this->db->where("target_object_id", $vocab_id . "");
    			$this->db->where("target_object_version", $vocab_version . "");
    			
    			// If no identifier is specified, then only get terms which are topConcepts of the vocab
    			if (!$identifier)
    			{
    				$this->db->where("relationship_type", "topConceptOf");
    			}
    			
    			$this->db->where("disabled", "0");
    			$this->db->from("dba.tbl_vocab_relationships");
    			$matches = $this->db->count_all_results();
    			$this->db->flush_cache();

   				// If this is a match
    			if ($matches > 0)
    			{	
	    		    // Join to all appropriate attributes
	    			$row->attributes = $this->getObjectAttributes($row->object_id, $row->object_version);
	    			$row->relationships = $this->getObjectRelationships($row->object_id, $row->object_version);
	    			$conceptList[] = $row;
	    			$conceptList = array_merge($conceptList, $this->getRelatedObjects($row->object_id, $row->object_version, $depth_configuration));
    			}
    			
	    }

    	return $conceptList;
    }
    
    
    public function getConceptsByPURL($identifier)
    {    
    	$conceptList = array();
 
    	if ($identifier == '') return $conceptList;
    	
    	//$this->db->where("object_class","TERM");
    	$this->db->where("disabled","0");
    	$this->db->where("purl", $identifier);

    	$query = $this->db->get("dba.tbl_vocab_objects");
    	$this->db->flush_cache();
    	
    	foreach ($query->result() AS $row)
    	{   		
    		    // Join to all appropriate attributes/relationships
    			$row->attributes = $this->getObjectAttributes($row->object_id, $row->object_version);
    			$row->relationships = $this->getObjectRelationships($row->object_id, $row->object_version);
    			$conceptList[] = $row;
    	}
    	
    	return $conceptList;
    }
    
    private function getObjectAttributes($id, $version)
    {
	    	$attributes = array();
	    		
    		$this->db->where("object_id", $id);
    		$this->db->where("object_version", $version);
    		$this->db->where("disabled", 0);
    		$this->db->order_by("order","asc");
    		
    		$attribute_query = $this->db->get("dba.tbl_vocab_attributes");   		
    		$this->db->flush_cache();
    		
    		foreach ($attribute_query->result() AS $attribute_row)
    		{
    			$attributes[] = $attribute_row;
    		}
    		return $attributes;
    		
    }
 
    private function getObjectRelationships($id, $version)
    {
	    	$relationships = array();
	    		
    		$this->db->where("source_object_id", $id);
    		$this->db->where("source_object_version", $version);
    		$this->db->where("dba.tbl_vocab_relationships.disabled", 0);
    		$this->db->join("dba.tbl_vocab_objects", "object_id = target_object_id AND object_version = target_object_version");
    		
    		$relationship_query = $this->db->get("dba.tbl_vocab_relationships");   		
    		$this->db->flush_cache();
    		
    		foreach ($relationship_query->result() AS $relationship_row)
    		{
    			$relationships[] = $relationship_row;
    		}
    		
    		return $relationships;
    		
    }
    
    private function getRelatedObjects($object_id, $object_version, $depth_configuration)
    {
    	$concepts = array();

    	$latest_level_concepts = array(array($object_id, $object_version));
    	while ($depth_configuration['n'] > 0)
    	{
    		$linked_concepts = array();
    		foreach($latest_level_concepts AS $target_concept)
    		{
    			$this->db->where("source_object_id", $target_concept[0]);
	    		$this->db->where("source_object_version", $target_concept[1]);
	    		$this->db->where("relationship_type", "narrower");
	    		$this->db->where("dba.tbl_vocab_relationships.disabled", 0);
	    		$this->db->join("dba.tbl_vocab_objects", "object_id = target_object_id AND object_version = target_object_version");
	    		
	    		$relationship_query = $this->db->get("dba.tbl_vocab_relationships");  
	    		$this->db->flush_cache();

	    		foreach ($relationship_query->result() AS $relationship_row)
	    		{
	    			$concepts[] = $relationship_row;
	    			$linked_concepts[] = array($relationship_row->object_id, $relationship_row->object_version);
	    		}
    		}    
    		$latest_level_concepts = $linked_concepts;
    		$depth_configuration['n']--;
    	}

  		$latest_level_concepts = array(array($object_id, $object_version));
    	while ($depth_configuration['b'] > 0)
    	{
    		$linked_concepts = array();
    		foreach($latest_level_concepts AS $target_concept)
    		{
    			$this->db->where("source_object_id", $target_concept[0]);
	    		$this->db->where("source_object_version", $target_concept[1]);
	    		$this->db->where("relationship_type", "broader");
	    		$this->db->where("dba.tbl_vocab_relationships.disabled", 0);
	    		$this->db->join("dba.tbl_vocab_objects", "object_id = target_object_id AND object_version = target_object_version");
	    		
	    		$relationship_query = $this->db->get("dba.tbl_vocab_relationships");  
	    		$this->db->flush_cache();

	    		foreach ($relationship_query->result() AS $relationship_row)
	    		{
	    			$concepts[] = $relationship_row;
	    			$linked_concepts[] = array($relationship_row->object_id, $relationship_row->object_version);
	    		}
    		}    
    		$latest_level_concepts = $linked_concepts;
    		$depth_configuration['b']--;
    	}
    	
    	$latest_level_concepts = array(array($object_id, $object_version));
    	while ($depth_configuration['m'] > 0)
    	{
    		$linked_concepts = array();
    		foreach($latest_level_concepts AS $target_concept)
    		{
    			$this->db->where("source_object_id", $target_concept[0]);
	    		$this->db->where("source_object_version", $target_concept[1]);
	    		$this->db->where_in("relationship_type", array("closeMatch", "exactMatch", "relatedMatch", "broadMatch", "narrowMatch"));
	    		$this->db->where("dba.tbl_vocab_relationships.disabled", 0);
	    		$this->db->join("dba.tbl_vocab_objects", "object_id = target_object_id AND object_version = target_object_version");
	    		
	    		$relationship_query = $this->db->get("dba.tbl_vocab_relationships");  
	    		$this->db->flush_cache();

	    		foreach ($relationship_query->result() AS $relationship_row)
	    		{
	    			$concepts[] = $relationship_row;
	    			$linked_concepts[] = array($relationship_row->object_id, $relationship_row->object_version);
	    		}
    		}    
    		$latest_level_concepts = $linked_concepts;
    		$depth_configuration['m']--;
    	}
    	
    	foreach ($concepts AS $concept)
    	{
    		$concept->attributes = $this->getObjectAttributes($concept->object_id, $concept->object_version);
    		$concept->relationships = $this->getObjectRelationships($concept->object_id, $concept->object_version);
    	}
    	
    	return $concepts;
    }
 
    
  
    

}