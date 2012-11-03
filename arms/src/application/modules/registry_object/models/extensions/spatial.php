<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spatial_Extension extends ExtensionBase
{
		
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	
	function determineSpatialExtents()
	{
		$extents = array();
		
		$sxml = $this->ro->getSimpleXML();
		
		$spatial_elts = $sxml->xpath('//spatial');
		foreach ($spatial_elts AS $spatial)
		{
			/** XXX: COORDINATES, POLYGONS, FIXYFIXY **/
			// XXX: if type kmlPolyCoords
			$coords = explode(" ",$spatial);
			
			if (count($coords) == 1)
			{
				// XXX: check valid coords
				$coords = explode(",", $coords[0]);
				$extents[] = ((int)$coords[0]) ." ". ((int)$coords[1]);
			}
			// XXX: box
			
			// XXX: check length? 1024 chars?
		}




		// Insert to DB
		foreach ($extents AS $extent)
		{
			$this->db->where('registry_object_id',$this->id)->delete('spatial_extents');
			$this->db->insert('spatial_extents', array('registry_object_id'=>$this->id, 'coordinates' => $extent));
		}
		
		return $extents;
	}

	
	function getSpatialExtents()
	{
		$extents = array();
		$query = $this->db->get_where("spatial_extents", array('registry_object_id' => $this->id));
		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() AS $row)
			{
				$extents[] = $row['coordinates'];
			}
		}

		return $extents;	
	}
	
}
	
	