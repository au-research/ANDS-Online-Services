<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 * 
 * This model allows the reference and initialisation 
 * of Data Sources. All instances of the _data_source 
 * PHP class should be invoked through this model. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

class Data_sources extends CI_Model {
		

	/**
	 * Returns exactly one data source by Key (or NULL)
	 * 
	 * @param the data source key
	 * @return _data_source object or NULL
	 */
	function getByKey($key)
	{
		$query = $this->db->select("data_source_id")->get_where('data_sources', array('key'=>$key));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			return new _data_source($id[0]['data_source_id']);
		}
	} 	
	
	/**
	 * Returns exactly one data source by ID (or NULL)
	 * 
	 * @param the data source ID
	 * @return _data_source object or NULL
	 */
	function getByID($id, $as_object = true)
	{
		$query = $this->db->select("*")->get_where('data_sources', array('data_source_id'=>$id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->result_array();
			if ($as_object)
			{
				return new _data_source($row[0]['data_source_id']);
			}
			else
			{
				// Just return the DB result
				return array_pop($row);
			}
		}
	}
	
		/**
	 * Returns exactly one data source by ID (or NULL)
	 * 
	 * @param the data source ID
	 * @return _data_source object or NULL
	 */
	function getByHarvestID($harvestId)
	{
		$query = $this->db->select("data_source_id")->get_where('harvest_requests', array("id"=>$harvestId));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			return new _data_source($id[0]['data_source_id']);
		}
	}

	/**
	 * Returns data sources which this user has ownership of by virtue of their
	 * affiliation (organisational roles)
	 * 
	 * @param the data source ID
	 * @return _data_source object or NULL
	 */
	function getOwnedDataSources($just_id = false)
	{
		$data_sources = array();
		$affiliations = $this->user->affiliations();
		if (is_array($affiliations) && count($affiliations) > 0)
		{
			if ($this->user->hasFunction('REGISTRY_SUPERUSER'))
			{
				$query = $this->db->query("SELECT data_source_id FROM data_sources");	
			}
			else
			{
				$query = $this->db->select('data_source_id')->where('attribute','record_owner')->where_in('value',$affiliations)->get('data_source_attributes');
			}

			if ($query->num_rows() == 0)
			{
				return $data_sources;
			}
			else
			{				
				foreach($query->result_array() AS $ds)
				{
					if($just_id){
						$data_sources[] = $ds['data_source_id'];
					}else{
						$data_sources[] =  new _data_source($ds['data_source_id']);
					}
					
				}
			}
		}
		return $data_sources;	

	} 

	/**
	 * Returns groups which this datasource has objects which are contributed by
	 * 
	 * @param the data source ID
	 * @return array of groups or NULL
	 */
	function getDataSourceGroups($data_source_id)
	{
		$groups = array();

		$query = $this->db->select('group')->where('registry_object_id','data_source_id')->get('data_source_attributes');
			if ($query->num_rows() == 0)
			{
				return $groups;
			}
			else
			{				
				foreach($query->result_array() AS $group)
				{
					$groups[] =  $group['group'];
				}
			}
		return $groups;	
	} 	



	/**
	 * Returns exactly one data source by URL slug (or NULL)
	 * 
	 * @param the data source slug
	 * @return _data_source object or NULL
	 */
	function getBySlug($key)
	{
		$query = $this->db->select("data_source_id")->get_where('data_sources', array('slug'=>$key));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			return new _data_source($id[0]['data_source_id']);
		}
	} 	
	
	/**
	 * Get a number of datasources that match the attribute requirement (or an empty array)
	 * 
	 * @param the name of the attribute to match by
	 * @param the value that the attribute must match
	 * @return array(_data_source)
	 */
	function getByAttribute($attribute_name, $value)
	{
		$matches = array();
		$query = $this->db->select("data_source_id")->get_where('data_source_attributes', array("attribute"=>$attribute_name, "value"=>$value));
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _data_source($result['data_source_id']);
			}
		}
		return $matches;
	} 	
	
	/**
	 * Get all datasources
	 * 
	 * @param limit by value
	 * @param the offset value
	 * @return array(_data_source) or empty array
	 */
	function getAll($limit = 16, $offset =0)
	{
		$matches = array();
		if($limit==0){
			$query = $this->db->select("data_source_id")->get('data_sources');
		}else{
			$query = $this->db->select("data_source_id")->get('data_sources', $limit, $offset);
		}
		
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _data_source($result['data_source_id']);
			}
		}
		
		return $matches;
	} 	
	
	/**
	 * XXX: 
	 * @return array(_data_source) or NULL
	 */
	function create($key, $slug)
	{
		$ds = new _data_source();
		
		// Compulsory attributes
		$ds->_initAttribute("key",$key, TRUE);
		$ds->_initAttribute("slug",$slug, TRUE);
		
		// Some extras
		$ds->setAttribute("created",time());

		$ds->create();
		return $ds;
	} 	
	
	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		require_once("_data_source.php");
	}	
		
}
