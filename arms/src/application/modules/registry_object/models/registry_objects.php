<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 *
 * XXX:
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/registryobject
 *
 */

class Registry_objects extends CI_Model {

	public $valid_classes = array("collection","activity","party","service");
	public $valid_status  = array("DRAFT"=>"DRAFT", "PUBLISHED"=>"PUBLISHED", "APPROVED"=>"APPROVED", "SUBMITTED_FOR_ASSESSMENT"=>"SUBMITTED_FOR_ASSESSMENT");
	public $valid_levels  = array("level_1"=>"1", "level_2"=>"2", "level_3"=>"3", "level_4"=>"4" );

	private $_mkro_callback = 'return new _registry_object($r["registry_object_id"]);';

	/**
	 * Get a number of registry_objects, limited by various parameters.
	 * If no records are found, and empty array is returned.
	 * Note that registry_objects is joined to both data_sources and registry_object_attributes:
	 * ensure the `$where` and `$sort` clauses are keyed accordingly.
	 *
	 * @param array of key-value pairs passed to the CI where() routine (optional)
	 * @param list of registry_object_ids to satisfy a 'where id in' clause (optional)
	 * @param how many records to return (optional)
	 * @param query offset (optional)
	 * @param sort field (optional)
	 * @param sort direction (optional)
	 * @param passed to CI ActiveRecord where() routine, with no automatic escaping (optional)
	 * @return array:
	 *  - 'count' => complete query result count (disregarding limit/offset)
	 *  - 'rows' => array(_registry_object)
	 */
	function get($where=array(),
		     $in=false,
		     $limit=0,
		     $offset=0,
		     $sort='registry_objects.registry_object_id',
		     $sortd='asc',
		     $rawwhere=array())
	{
		$count = 0;
		if (is_array($in) and count($in) == 0)
		{
			return array('count' => $count, 'rows' => array());
		}
		else
		{
			$this->db->distinct()->select("registry_objects.registry_object_id")
				->join("data_sources",
				       "data_sources.data_source_id = registry_objects.data_source_id",
				       "inner")
				->join("registry_object_attributes",
				       "registry_object_attributes.registry_object_id = registry_objects.registry_object_id",
				       "inner")
				->where($where)
				->where($rawwhere, null, false)
				->order_by($sort, $sortd);
			if ($in !== false)
			{
				$this->db->where_in("registry_objects.registry_object_id", $in);
			}

			/**
			 * FIXME: Cannot for the life of me figure out the correct way to retrieve the
			 * number of matching rows while using $this->db->get([table], $limit, $offset).
			 * So instead, we'll suck down the lot, get the count, and take our slice
			 */
			$query = $this->db->get('registry_objects');
			$count = $query->num_rows();
			if ($query->num_rows() > 0)
			{
				$results = $query->result_array();
				print_r($results);
				$results = array_slice($results, $offset, $limit);
				$records = array_map(create_function('$r',
								     $this->_mkro_callback),
						     $results);
			}
			else
			{
				$count = 0;
				$records = array();
			}
			$query->free_result();
		}
		return array('count' => $count, 'rows' => $records);
	}

	/**
	 * Returns exactly one registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getByKey($key)
	{
		$query = $this->db->select("registry_object_id")->get_where('registry_objects', array('key'=>$key));
		if ($query->num_rows() == 0)
		{
			$query->free_result();
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			$query->free_result();
			return new _registry_object($id[0]['registry_object_id']);
		}
	}

	/**
	 * Returns exactly one registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getByID($id)
	{
		return new _registry_object($id);
	}


	/**
	 * Returns exactly one registry object by URL slug (or NULL)
	 *
	 * @param the registry object slug
	 * @return _registry_object object or NULL
	 */
	function getBySlug($slug)
	{
		$query = $this->db->select("registry_object_id")->get_where('registry_objects', array('slug'=>$slug));
		if ($query->num_rows() == 0)
		{
			$query->free_result();
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			$query->free_result();
			return new _registry_object($id[0]['registry_object_id']);
		}
	}


	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the name of the attribute to match by
	 * @param the value that the attribute must match
	 * @return array(_registry_object)
	 */
	function getByAttribute($attribute_name, $value, $core = FALSE)
	{
		$matches = array();
		$this->db->save_queries = FALSE;
		if ($core)
		{
			$query = $this->db->select("registry_object_id")->get_where('registry_objects', array($attribute_name=>$value));
		}
		else
		{
			$query = $this->db->select("registry_object_id")->get_where('registry_object_attributes', array("attribute"=>$attribute_name, "value"=>$value));
		}
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _registry_object($result['registry_object_id']);
			}
		}
		$query->free_result();
		//var_dump($matches);
		return $matches;
	}

	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the data source ID to match by
	 * @return array(_registry_object)
	 */
	function getIDsByDataSourceID($data_source_id)
	{
		$matches = array();
		$query = $this->db->select("registry_object_id")->get_where('registry_objects', array("data_source_id"=>$data_source_id));
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = $result['registry_object_id'];
			}
		}
		$query->free_result();
		return $matches;
	}

	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the data source ID to match by
	 * @return array(_registry_object)
	 */
	function getByDataSourceKey($data_source_key)
	{
		$matches = array();
		$query = $this->db->select("registry_object_id")->join('data_sources', 'data_sources.data_source_id = registry_objects.data_source_id')->get_where('registry_objects', array("data_sources.key"=>$data_source_key));
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _registry_object($result['registry_object_id']);
			}
		}
		$query->free_result();
		return $matches;
	}


	/**
	 * Get a number of registry_objects that match the class requirement (or an empty array)
	 *
	 * @param the value that the class must match
	 * @return array(_registry_object)
	 */
	function getByClass($class)
	{
		return $this->get(array("class"=>$class));
	}


	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function create($data_source_key, $registry_object_key, $class, $title, $status, $slug, $record_owner, $harvestID)
	{
		if (is_null($this->getByKey($registry_object_key)))
		{

			$ro = new _registry_object();

			// Get the data_source_id for this data source key
			$this->load->model('data_source/data_sources','ds');
			$ds = $this->ds->getByKey($data_source_key);
			$ro->_initAttribute("data_source_id", $ds->getAttribute('data_source_id'), TRUE);


			$ro->_initAttribute("key",$registry_object_key, TRUE);
			$ro->_initAttribute("class",$class, TRUE);
			$ro->_initAttribute("title",$title, TRUE);
			$ro->_initAttribute("status",$status, TRUE);
			$ro->_initAttribute("slug",$slug, TRUE);
			$ro->_initAttribute("record_owner",$record_owner, TRUE);

			// Some extras
			$ro->setAttribute("created",time());
			$ro->setAttribute("harvest_id", $harvestID);

			$ro->create();
			return $ro;

		}
		else
		{
			return $this->update($registry_object_key, $class, $title, $status, $slug, $record_owner);
		}
	}

	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function update($registry_object_key, $class, $title, $status, $slug, $record_owner)
	{
		$ro = $this->getByKey($registry_object_key);
		if (!is_null($ro))
		{

			$ro->setAttribute("class",$class);
			$ro->setAttribute("title",$title);
			$ro->setAttribute("status",$status);
			$ro->setAttribute("slug",$slug);
			$ro->setAttribute("record_owner",$record_owner);

			$ro->save();
			return $ro;
		}
		else
		{
			throw new Exception ("Unable to update registry object (this registry object key does not exist in the registry)");
		}
	}



	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		include_once("_registry_object.php");
	}

}
