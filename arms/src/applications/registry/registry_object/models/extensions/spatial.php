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
			$north = null;
			$south = null;
			$west  = null;
			$east  = null;
			$type = $spatial["type"];
			$value = (string)$spatial;
			if($type == 'kmlPolyCoords' || $type == 'gmlKmlPolyCoords')
			{
				if(isValidKmlPolyCoords($value))	
				{
					$north = -90;
					$south = 90;
					$west  = 180;
					$east  = -180;
					$tok = strtok($value, " ");
					while ($tok !== FALSE)
					{
						$keyValue = explode(",", $tok);
						//$msg = $msg.'<br/>lat ' .$keyValue[1]. ' long '.$keyValue[0];
						if(is_numeric($keyValue[1]) && is_numeric($keyValue[0]))
							{
			
							$lng = floatval($keyValue[0]);
							$lat = floatval($keyValue[1]);
							//$msg = $msg.'<br/>lat ' .$lat. ' long '.$lng;
							if ($lat > $north)
							{
							 $north = $lat;
							}
							if($lat < $south)
							{
							 $south = $lat;
							}
							if($lng < $west)
							{
							 $west = $lng;
							}
							if($lng > $east)
							{
							 $east = $lng;
							}
						}
						$tok = strtok(" ");
					}
				}
			}
			elseif($type == 'iso19139dcmiBox')
			{
			//northlimit=-23.02; southlimit=-25.98; westlimit=166.03; eastLimit=176.1; projection=WGS84
				$north = null;
				$south = null;
				$west  = null;
				$east  = null;
				$tok = strtok($value, ";");
				while ($tok !== FALSE)
				{
					$keyValue = explode("=",$tok);
					if(strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1]))
					{
					  $north = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1]))
					{
					  $south = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1]))
					{
					  $west = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1]))
					{
					  $east = floatval($keyValue[1]);
					}
				  	$tok = strtok(";");
				}
			}
			elseif($type == 'iso19139dcmiPoint' || $type == 'dcmiPoint') //"name=Tasman Sea, AU; east=160.0; north=-40.0"
			{
			//northlimit=-23.02; southlimit=-25.98; westlimit=166.03; eastLimit=176.1; projection=WGS84
				$north = null;
				$south = null;
				$west  = null;
				$east  = null;
				$tok = strtok($value, ";");
				while ($tok !== FALSE)
				{
					$keyValue = explode("=",$tok);
					if(strtolower(trim($keyValue[0])) == 'north' && is_numeric($keyValue[1]))
					{
					  $north = floatval($keyValue[1]);
					  $south = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'east' && is_numeric($keyValue[1]))
					{
					  $west = floatval($keyValue[1]);
					  $east = floatval($keyValue[1]);
					}
				  	$tok = strtok(";");
				}
			}
			elseif($type == 'text' || $type == 'iso31661' || $type == 'iso31662' || $type == 'iso31663' || $type == 'iso3166') //"name=Tasman Sea, AU; east=160.0; north=-40.0"
			{
			//northlimit=-23.02; southlimit=-25.98; westlimit=166.03; eastLimit=176.1; projection=WGS84
				$north = null;
				$south = null;
				$west  = null;
				$east  = null;
				// Insert to DB
		//foreach ($extents AS $extent)
		//{
		//	$this->db->where('registry_object_id',$this->id)->delete('spatial_extents');
		//	$this->db->insert('spatial_extents', array('registry_object_id'=>$this->id, 'coordinates' => $extent));
		//}
		
			// XXX NOT YET...
			//	$searchText = trim($value);
			//	getExtentFromGoogle(trim($value), &$north, &$south, &$west, &$east);
			}
			//$msg = $msg.'<br/> north:'.$north.' south:'.$south.' west:'.$west.' east:'.$east;
			if($north != null && $south != null && $west  != null && $east != null && $north <= 90 && $south >= -90 && $west  >= -180 && $east <= 180){
				//A lat-lon rectangle can be indexed with 4 numbers in minX minY maxX maxY order:
    			// <field name="geo">-74.093 41.042 -69.347 44.558</field> 				
				$extents[] = $west." ".$south." ".$east." ".$north;
			}

		}		
		return $extents;
	}
	
	function getLocationAsLonLats()
	{
		$coords = array();
		
		$sxml = $this->ro->getSimpleXML();		
		$spatial_elts = $sxml->xpath('//spatial');
		
		foreach ($spatial_elts AS $spatial)
		{
			
			$type = $spatial["type"];
			$value = (string)$spatial;
			
			if($this->isValidKmlPolyCoords($value) && ($type == 'kmlPolyCoords' || $type == 'gmlKmlPolyCoords'))
			{
				$coords[] = $value;					
			}
			elseif($type == 'iso19139dcmiBox')
			{
				$tok = strtok($value, ";");
				while ($tok !== FALSE)
				{
					$keyValue = explode("=",$tok);
					if(strtolower(trim($keyValue[0])) == 'northlimit' && is_numeric($keyValue[1]))
					{
					  $north = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'southlimit' && is_numeric($keyValue[1]))
					{
					  $south = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'westlimit' && is_numeric($keyValue[1]))
					{
					  $west = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'eastlimit' && is_numeric($keyValue[1]))
					{
					  $east = floatval($keyValue[1]);
					}
				  	$tok = strtok(";");
				}
				if($north == $south && $east == $west){
					$coords[] = $east.",".$north;	
				}
				else{
					$coords[] = $east.",".$north." ".$east.",".$south." ".$west.",".$south." ".$west.",".$north." ".$east.",".$north;
				}
			}
			elseif($type == 'iso19139dcmiPoint' || $type == 'dcmiPoint') //"name=Tasman Sea, AU; east=160.0; north=-40.0"
			{
				$tok = strtok($value, ";");
				while ($tok !== FALSE)
				{
					$keyValue = explode("=",$tok);
					if(strtolower(trim($keyValue[0])) == 'north' && is_numeric($keyValue[1]))
					{
					  $north = floatval($keyValue[1]);
					}
					if(strtolower(trim($keyValue[0])) == 'east' && is_numeric($keyValue[1]))
					{
					  $east = floatval($keyValue[1]);
					}
				  	$tok = strtok(";");
				}
				$coords[] = $east.",".$north;	
			}
		}		
		return $coords;
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
	
	function calcExtent($coords)
	{
		$north = -90;
		$south = 90;
		$west  = 180;
		$east  = -180;
		$tok = strtok($coords, " ");
		while ($tok !== FALSE)
		{
			$keyValue = explode(",", $tok);
			if(is_numeric($keyValue[1]) && is_numeric($keyValue[0]))
				{

				$lng = floatval($keyValue[0]);
				$lat = floatval($keyValue[1]);
				//$msg = $msg.'<br/>lat ' .$lat. ' long '.$lng;
				if ($lat > $north)
				{
				 $north = $lat;
				}
				if($lat < $south)
				{
				 $south = $lat;
				}
				if($lng < $west)
				{
				 $west = $lng;
				}
				if($lng > $east)
				{
				 $east = $lng;
				}
			}
			$tok = strtok(" ");
		}
		if($north == $south && $east == $west){
			return $west." ".$south;	
		}
		else{
			return $west." ".$south." ".$east." ".$north;
		}			
	}
	
	function isValidKmlPolyCoords($coords)
	{
		$valid = false;
		$coordinates = preg_replace("/\s+/", " ", trim($coords));
		if( preg_match('/^(\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?)( (\-?\d+(\.\d+)?),(\-?\d+(\.\d+)?))*$/', $coordinates) )
		{
			$valid = true;
		}
		return $valid;
	}
	
}
	
	