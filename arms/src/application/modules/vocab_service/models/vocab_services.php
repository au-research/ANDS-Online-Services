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
	 * Returns all versions of a vocab by vocab  ID 
	 * 
	 * @param the vocab ID
	 * @return vocab versions or NULL
	 */	
	function getVersionsByID($id)
	{
		$query = $this->db->order_by('status asc')->select()->get_where('vocab_versions', array('vocab_id'=>$id));
		
		if ($query->num_rows() == 0)
		{
			return NULL;
			
		}
		else
		{
			$vocab_versions = $query->result();
			//print_r($vocab_versions);
			return $vocab_versions;
		}	
		
	}


	/**
	 * Returns all distinct formats of a vocab by vocab  ID 
	 * 
	 * @param the vocab ID
	 * @return vocab formats or NULL
	 */	
	function getAvailableFormatsByID($id)
	{
		$qry = 'SELECT distinct(format) FROM dbs_vocabs.vocab_version_formats  WHERE version_id IN(SELECT id FROM dbs_vocabs.vocab_versions WHERE vocab_id = '.$id.');';
		$query = $this->db->query($qry);
		
		if ($query->num_rows() == 0)
		{
			return NULL;
			
		}
		else
		{
			$vocab_formats = $query->result();
			return $vocab_formats;
		}	
		
	}



	/**
	 * Returns all downloadable file of a certain format belongs to a certain vocab 
	 * 
	 * @param the vocab ID, the format
	 * @return vocab formats or NULL
	 */	
	function getDownloadableByFormat($id, $format)
	{
		$qry = 'SELECT f.*, v.title, v.status from dbs_vocabs.vocab_version_formats f, dbs_vocabs.vocab_versions v WHERE f.format=\''.$format.'\' AND v.vocab_id='.$id.' AND f.version_id = v.id order by status asc;';
		$query = $this->db->query($qry);
		
		if ($query->num_rows() == 0)
		{
			return NULL;
			
		}
		else
		{
			$vocab_formats = $query->result();
			return $vocab_formats;
		}	
		
	}




	/**
	 * Returns all versions of a vocab by vocab  ID (or NULL)
	 * 
	 * @param the vocab ID
	 * @return vocab versions or NULL
	 */	
	function getVersionByID_old($id)
	{
		$qry = 'SELECT * FROM vocab_versions, vocab_version_formats WHERE vocab_versions.id = '.$id.' AND vocab_version_formats.version_id = vocab_versions.id';
		$query = $this->db->query($qry);
		
		if ($query->num_rows() == 0)
		{
			return NULL;
			
		}
		else
		{
			$vocab_version = $query->result();
			//print_r($vocab_versions);
			return $vocab_version;
		}	
		
	}

	function getVersionByID($id){
		$query = $this->db->limit(1)->select()->get_where('vocab_versions', array('id'=>$id));
		if($query->num_rows()==0){
			return null;
		}else{
			$results = $query->result();
			return $results[0];
		}
	}




	function getVocabIDbyVersion($version_id){
		$query = $this->db->select()->get_where('vocab_versions', array('id'=>$version_id));
		if($query->num_rows()==0){
			return null;
		}else{
			$results = $query->result();
			return $results[0]->vocab_id;
		}
	}
	
	/**
	 * Returns all formats of a version (or NULL)
	 * 
	 * @param the version ID
	 * @return formats or NULL
	 */	
	function getFormatByVersion($id)
	{
		$query = $this->db->select()->get_where('vocab_version_formats', array('version_id'=>$id));
		
		if ($query->num_rows() == 0){
			return NULL;
		}
		else{
			$formats = $query->result();
			return $formats;
		}	
		
	}	


	/**
	 * deletes a given format from a vocab version
	 * 
	 * @param the vocab version format ID
	 * @return NULL
	 */	
	function deleteFormat($id)
	{
		$qry = 'DELETE FROM vocab_version_formats WHERE id = '.$id;
		$query = $this->db->query($qry);
		
		if ($query)
		{
			return NULL;
		}
	}	

	function deleteVersion($id){
		return $this->db->delete('vocab_versions', array('id' => $id)); 
	}
	
	/**
	 * adds a  format to a vocab version
	 * 
	 * @param the vocab version format ID
	 * @return NULL
	 */	
	function addFormat($version_id,$format,$type,$value)
	{
		$qry = 'INSERT INTO vocab_version_formats (`version_id`,`type`,`value`,`format`) VALUES (\''.$version_id.'\',\''.$type.'\',\''.$value.'\',\''.$format.'\')';
		$query = $this->db->query($qry);
		
		if ($query)
		{
			return NULL;
		}
	}	

	function addVersion($vocab_id, $version){
		if($version['makeCurrent']){
			$status = 'current';
		}else $status = 'superceded';

		//if adding a current version, all other versions must be superceded
		if($status=='current'){
			$data = array(
               'status' => 'superceded'
            );
			$this->db->update('vocab_versions', $data); 
		}

		//and then we add the version
		$data = array(
			'title'=>$version['title'],
			'status'=>$status,
			'vocab_id'=>$vocab_id
		);

		$this->db->insert('vocab_versions', $data);
	}

	function updateVersion($version){
		if($version['makeCurrent']){
			$status = 'current';
		}else $status = 'superceded';

		//if adding a current version, all other versions must be superceded
		if($status=='current'){
			$data = array(
               'status' => 'superceded'
            );
			$this->db->update('vocab_versions', $data);
		}

		//now we update our version
		$data = array(
			'title' => $version['title'],
        	'status' => $status
        );
        $this->db->where('id', $version['id']);
		$this->db->update('vocab_versions', $data); 

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
	 * Get all datasources
	 * 
	 * @param limit by value
	 * @param the offset value
	 * @return array(_data_source) or empty array
	 */
	function getOwnedVocabs($limit = 16, $offset =0)
	{
		$vocabs = array();
		$affiliations = $this->user->affiliations();
		if (is_array($affiliations) && count($affiliations) > 0)
		{
			if($limit == 0)
			{
				$query = $this->db->select('id')->where_in('record_owner',$affiliations)->get('vocab_metadata');
			}
			else
			{
				$query = $this->db->select('id')->where_in('record_owner',$affiliations)->get('vocab_metadata', $limit, $offset);
			}
			
			if ($query->num_rows() == 0)
			{
				return $vocabs;
			}
			else
			{
				
				foreach($query->result_array() AS $v)
				{
					$vocabs[] =  new _vocab($v['id']);
				}
				
			}
		}	
		return $vocabs;
	} 	

	
	/**
	 * XXX: 
	 * @return array(_vocab) or NULL
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
