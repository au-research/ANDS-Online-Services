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

class Vocab_services extends CI_Model {
		
	
	/**
	 * Returns exactly one vocab by ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return _vocab object or NULL
	 */
	function getByID($id)
	{

		$query = $this->db->select()->get_where('vocab_metadata', array('id'=>$id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$vocab = $query->result_array();
			return new _vocab($vocab[0]['id']);
		}
	} 	

	/**
	 * Returns all versions of a vocab by vocab  ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return vocab versions or NULL
	 */	
	function getVersionsByID($id)
	{

		$query = $this->db->select()->get_where('vocab_versions', array('vocab_id'=>$id));

		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$vocab_versions = $query->result();
			return $vocab_versions;
		}	
		
	}
	
	/**
	 * Returns all changes of a vocab by vocab  ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return vocab changes or NULL
	 */	
	function getChangesByID($id)
	{

		$query = $this->db->select()->get_where('vocab_change_history', array('vocab_id'=>$id));

		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$vocab_changes = $query->result();
			return $vocab_changes;
		}	
		
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
			$query = $this->db->select("id")->get('vocab_metadata');
		}else{
			$query = $this->db->select("id")->get('vocab_metadata', $limit, $offset);
		}

		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _vocab($result['id']);
			}
		}
		
		return $matches;
	} 	
	
	/**
	 * XXX: 
	 * @return array(_data_source) or NULL
	 */
	function create()
	{
		$vocab = new _vocab();
		
		$vocab->create();
		return $vocab;
	} 	
	
	/**
	 * @ignore
	 */
	function __construct()
	{

		parent::__construct();
		$this->load->database('vocabs',TRUE);
		include_once("_vocab.php");

	}	
		
}
