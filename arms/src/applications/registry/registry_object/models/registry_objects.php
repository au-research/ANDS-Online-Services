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
	public $valid_status  = array("MORE_WORK_REQUIRED"=>"MORE_WORK_REQUIRED", "DRAFT"=>"DRAFT", "SUBMITTED_FOR_ASSESSMENT"=>"SUBMITTED_FOR_ASSESSMENT", "ASSESSMENT_IN_PROGRESS"=>"ASSESSMENT_IN_PROGRESS", "APPROVED"=>"APPROVED", "PUBLISHED"=>"PUBLISHED");
	public $valid_levels  = array("level_1"=>"1", "level_2"=>"2", "level_3"=>"3", "level_4"=>"4" );


	/**
	 * Generic registry_objects get handler.
	 *
	 * This moderately nifty piece of code lets you get some `_registry_object`
	 * goodness by any means necessary. Just add wat^H^H^H the following:
	 *   - a list of callback functions to apply to this (the `Registry_objects`) model
	 *   - whether you want `_registry_object`s or plain old `registry_object_id`s (i.e. ints)
	 *   - number of records to limit the reponse to (optional)
	 *   - offset from which to retrieve record set (optional)
	 *
	 * The callback pipeline expects an array of arrays. Crazy, I know. I blame PHP for not
	 * having a tuple/list/set datatype, or even a plain hash. That's right: I'm blaming my tools.
	 * Anyway, `$pipeline` should look something like:
	 *
	 *
	 * array(                                 //a list of callbacks to apply
	 *   array(                               //the first callback
	 *     'args' => ...,                     //arguments to pass to callback. this will be the second parameter. stuff with array as required
	 *     'fn' => function($db,$args){...}   //callback. takes a CodeIgniter db object, which should be returned by the function.
	 *  )
	 * )
	 *
	 *
	 * @param an array of processing callbacks (type `callable`). See above description
	 * for specific details.
	 * @param should we create `_registry_object` objects for each result? (default: true)
	 * @param query limit (int) (default: false; i.e. no limit)
	 * @param query offset (int) (default: false; i.e. no offset)
	 * @return an array of results, or null if no results.
	 */
	public function _get($pipeline, $make_ro=true, $limit=false, $offset=false)
	{
		if (!is_array($pipeline))
		{
			throw new Exception("pipeline must be an array");
		}

		foreach ($pipeline as $p)
		{
			if (!is_callable($p['fn']))
			{
				throw new Exception("pipeline members must be callable");
			}
		}

		$CI =& get_instance();
		$db = $CI->db;
		foreach ($pipeline as $p)
		{
			$db = call_user_func($p['fn'], $db, $p['args']);
		}
		$results = null;
		$query = false;
		if ($limit && $offset)
		{
			$query = $db->get(null, $limit, $offset);
		}
		elseif ($limit)
		{
			$query = $db->get(null, $limit);
		}
		elseif ($offset)
		{
			$query = $db->get(null, null, $offset);
		}
		else
		{
			$query = $db->get();
		}
		if ($query->num_rows() > 0)
		{
			$results = array();
			foreach ($query->result_array() as $rec)
			{
				$results[] = $make_ro ? new _registry_object($rec["registry_object_id"]) : $rec;
			}
		}
		if ($query)
		{
			$query->free_result();
		}
		return $results;
	}

	
	// **** DEPRECATED, use getPublishedByKey, etc *****
	/*function getByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("key", $key);
							    return $db;
						    })));
		return is_array($results) ? $results : null;
	}*/


	/**
	 * Returns exactly one PUBLISHED registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getPublishedByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("key", $key)
								    ->where("status", PUBLISHED);
							    return $db;
						    })),
							true,
							1);
		return is_array($results) ? $results[0] : null;
	}

	/**
	 * Returns exactly one DRAFT (or draft-equivalent) registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getDraftByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("key", $key)
								    ->where_in("status", getDraftStatusGroup());
							    return $db;
						    })),
							true,
							1);
		return is_array($results) ? $results[0] : null;
	}

	/**
	 * Returns all registry objects with a given key (or NULL)
	 *
	 * @param the registry object key
	 * @return array(_registry_object) or NULL
	 */
	function getAllByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("key", $key);
							    return $db;
						    })),
							true
							);
		return is_array($results) ? $results : null;
	}


	/**
	 * Returns exactly one registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getByID($id)
	{
		$results = $this->_get(array(array('args' => $id,
						   'fn' => function($db,$id) {
							   $db->select("registry_object_id")
								   ->from("registry_objects")
								   ->where("registry_object_id", $id);
							   return $db;
						   })),
				       true,
				       1);
		return is_array($results) ? $results[0] : null;
	}


	/**
	 * Returns exactly one registry object by URL slug (or NULL)
	 *
	 * @param the registry object slug
	 * @param the status of the registry object we want 
	 * @return _registry_object object or NULL
	 */
	function getBySlug($slug, $status = "PUBLISHED")
	{
		$results = $this->_get(array(array('args' => $slug,
						   'fn' => function($db,$slug) {
							   $db->select("registry_object_id")
								   ->from("registry_objects")
								   ->where("status", "PUBLISHED")
								   ->where("slug", $slug);
							   return $db;
						   })),
				       true,
				       1);
		return is_array($results) ? $results[0] : null;
	}


	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the name of the attribute to match by
	 * @param the value that the attribute must match
	 * @return array(_registry_object)
	 */
	function getByAttribute($attribute_name, $value, $core=false, $make_ro=true)
	{
		$args = array('name' => $attribute_name, 'val' => $value);
		return $core == true ?
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_objects")
								->where($args['name'], $args['val']);
							return $db;
						})), $make_ro)
			:
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_object_attributes")
								->where("attribute", $args['name'])
								->where("value", $args['val']);
							return $db;
						})), $make_ro)
			;
	}

	function getByAttributeDatasource($data_source_id, $attribute_name, $value, $core=false, $make_ro=true)
	{
		$args = array('name' => $attribute_name, 'val' => $value, 'data_source_id'=>$data_source_id);
		return $core == true ?
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_objects")
								->where('data_source_id', $args['data_source_id'])
								->where($args['name'], $args['val']);
							return $db;
						})), $make_ro)
			:
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_objects.registry_object_id")
								->from("registry_object_attributes")
								->join('registry_objects', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id', 'right')
								->where('data_source_id', $args['data_source_id'])
								->where("attribute", $args['name'])
								->where("value", $args['val']);
							return $db;
						})), $make_ro)
			;
	}


	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array).
	 * Note that by default, this method returns registry_object_id's only. If you want
	 * `_registry_object`s, pass an additional boolean `true` for the second parameter
	 *
	 * @param the data source ID to match by
	 * @param boolean flag indicating whether to return an array of IDs (int), or an
	 * array of `_registry_object`s.
	 * @return array of results, or null if no matching records
	 */
	function getIDsByDataSourceID($data_source_id, $make_ro=false, $status='All')
	{
		$results =  $this->_get(array(array('args' => array('ds_id'=>$data_source_id, 'status'=>$status),
						    'fn' => function($db, $args) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("data_source_id", $args['ds_id']);
								if($args['status']!='All') $db->where('status', $args['status']);
							    return $db;
						    })),
					$make_ro);
		if(is_array($results))
			return $make_ro ? $results : array_map(function($r){return $r['registry_object_id'];}, $results);
		else
			return null;
	}

	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the data source ID to match by
	 * @deprecated USE getIDsByDataSourceID() instead
	 * @return array(_registry_object)
	 */
	function getByDataSourceKey($data_source_key)
	{
		return $this->_get(array(array('args' => $data_source_key,
					       'fn' => function($db, $dsk) {
						       $db->select("registry_object_id")
							       ->from("registry_objects")
							       ->join("data_sources",
								      "data_sources.data_source_id = registry_objects.data_source_id")
							       ->where("data_sources.key", $dsk);
						       return $db;
					       })));
	}

	function getAll($limit=10, $offset=0, $args=null)
	{
		return $this->_get(array(array('args' => array(
									'search'=>$args['search'] ? $args['search'] : false,
									'sort'=>$args['sort'],
									'filter'=>$args['filter']
								),
					       'fn' => function($db, $args) {
						       $db->select("registry_object_id")
							       ->from("registry_objects");
							   	if($args['search']) {
							   		$db->like('title',$args['search'],'both');
							   		$db->or_like('key', $args['search'],'both');
							   	}
						   		if($args['sort']){
						   			foreach($args['sort'] as $sort){
						   				foreach($sort as $key=>$value){
						   					$db->order_by($key, $value);
						   				}
						   			}
						   		}
						   		if($args['filter']){
						   			foreach($args['filter'] as $key=>$value){
						   				$db->where($key,$value);
						   			}
						   		}
						       return $db;
					       })),true, $limit, $offset);
	}

	function getByDataSourceID($data_source_id, $limit=10, $offset=0, $args=null, $make_ro=true){
		
		$white_list = array('title', 'class', 'key', 'status', 'slug', 'record_owner');
		$filtered = array();
		if($args['filter']){
			foreach($args['filter'] as $key=>$value){
				if(!in_array($key, $white_list)){
					$filtered = array_merge($filtered, $this->getByAttributeDatasource($data_source_id, $key, $value, false, false));
				}
			}
		}
		$where_in = array(); 
		foreach($filtered as $f){
			array_push($where_in, $f['registry_object_id']);
		}
		// var_dump($where_in);

		return $this->_get(array(array('args' => array(
									'data_source_id'=>$data_source_id,
									'search'=>isset($args['search']) ? $args['search'] : false,
									'sort'=>isset($args['sort']) ? $args['sort'] : false,
									'filter'=>isset($args['filter']) ? $args['filter'] : false,
									'where_in'=>isset($where_in) ? $where_in : false
								),
					       'fn' => function($db, $args) {
						       	$db->select("registry_objects.registry_object_id")->from("registry_objects");
						       	$db->where('data_source_id', $args['data_source_id']);

							   	if($args['search']) {
							   		// $db->where('(title LIKE \'%'.$args['search'].'%\' || key LIKE \'%'.$args['search'].'%\' || id LIKE \'%'.$args['search'].'%\')');
							   		$db->like('title', $args['search']);
							   	}

							   	$white_list = array('title', 'class', 'key', 'status', 'slug', 'record_owner');
						   		if($args['sort']){
						   			foreach($args['sort'] as $key=>$value){
						   				$db->join('registry_object_attributes', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id', 'left');
						   				$db->select('registry_object_attributes.value as v');
						   				$db->where('registry_object_attributes.attribute', $key);
						   				if(in_array($key, $white_list)){
						   					$db->order_by($key, $value);
						   				}else{
						   					$db->order_by('v', $value);
						   				}
						   			}
						   		}

						   		if($args['filter']){
						   			foreach($args['filter'] as $key=>$value){
						   				if(in_array($key, $white_list)){
						   					$db->where($key,$value);
						   				}
						   			}
						   			//var_dump($args['where_in']);
						   			
						   		}
						   		if($args['where_in']){
						   			if(sizeof($args['where_in'])>0){
						   				$db->where_in('registry_objects.registry_object_id',$args['where_in']);
						   			}else return false;
						   		}
						       return $db;
					       })),$make_ro, $limit, $offset);
	}


	function getByDataSourceID_old($data_source_id, $limit=10, $offset=0, $args=null, $make_ro = true){
		return $this->_get(array(array('args' => array(
									'data_source_id'=>$data_source_id,
									'search'=>isset($args['search']) ? $args['search'] : false,
									'sort'=>isset($args['sort']) ? $args['sort'] : false,
									'filter'=>isset($args['filter']) ? $args['filter'] : false,
									'filtered_id'=>isset($args['filtered_id']) ? $args['filtered_id'] : false,
									'or_filter'=>isset($args['or_filter']) ? $args['or_filter'] : false
								),
					       'fn' => function($db, $args) {
						       $db->select("registry_objects.registry_object_id")
							       ->from("registry_objects");

						   		$where='`data_source_id` ="'.$args['data_source_id'].'"';

						   		$white_list = array('title', 'class', 'key', 'status', 'slug', 'record_owner');
						   		
						   		if($args['filter'] || $args['or_filter']){
						   			$where.='AND (';
							   		if($args['filter']){
							   			foreach($args['filter'] as $key=>$value){
						   					$where.='`'.$key.'`="'.$value.'"';
						   					if($value!=end($args['filter'])){
						   						$where .='  AND ';
						   					}
							   			}
							   		}
									if($args['or_filter']){
										foreach($args['or_filter'] as $key=>$value){
											$where.=' OR `'.$key.'`="'.$value.'"';
										}
									}
									if($args['search']){
										$where.=') AND (';
										$where.='`registry_objects`.`title` LIKE \'%'.$args['search'].'%\'';
										$where.='OR `registry_objects`.`key` LIKE \'%'.$args['search'].'%\'';
										$where.='OR `registry_objects`.`registry_object_id` LIKE \'%'.$args['search'].'%\'';
										//$where.=')';
									}
									$where.=')';
						   		}
						   		
						   		if($args['sort']){
						   			foreach($args['sort'] as $key=>$value){
						   				if(in_array($key, $white_list)){
						   					$db->order_by($key, $value);
						   				}else{
						   					$db->join('registry_object_attributes', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id', 'left');
						   					$db->select('registry_object_attributes.value as v');
							   				$where.=' AND registry_object_attributes.attribute = "'.$key.'"';
							   				$db->order_by('v', $value);
						   				}
						   			}
						   		}

						   		if($args['filtered_id']){
						   			$db->where_in('`registry_objects`.`registry_object_id`', $args['filtered_id']);
						   		}
						   		// var_dump($args['filtered_id']);
						   		//var_dump($where);
						   		$db->where($where);

						       return $db;
					       })),$make_ro, $limit, $offset);
	}

	/**
	 * Get a number of registry_objects that match the class requirement (or an empty array)
	 *
	 * @param the value that the class must match
	 * @return array(_registry_object)
	 */
	function getByClass($class)
	{
		return $this->_get(array(array('args' => $class,
					       'fn' => function($db, $class) {
						       $db->select("registry_object_id")
							       ->from("registry_objects")
							       ->where("class", $class);
						       return $db;
					       })));
	}


	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function create(_data_source $data_source, $registry_object_key, $class, $title, $status, $slug, $record_owner, $harvestID)
	{
		$ro = new _registry_object();
		$ro->_initAttribute("data_source_id", $data_source->getAttribute('data_source_id'), TRUE);


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
	  * XXX: 
	  */ 
	function cloneToDraft($registry_object)
	{
		if (!($registry_object instanceof _registry_object))
		{
			// Then this is a registry object ID
			$registry_object = $this->getByID($registry_object);
		}
		if (!$registry_object) { throw new Exception ("Could not load registry object to create draft."); }

		// Add the XML content of this draft to the published record (and follow enrichment process, etc.)
		$this->load->model('data_source/data_sources', 'ds');
		$this->importer->_reset();
		$this->importer->setXML(wrapRegistryObjects($registry_object->getRif()));
		$this->importer->setDatasource($this->ds->getByID($registry_object->data_source_id));
		$this->importer->forceDraft();
		$this->importer->commit();

		if ($error_log = $this->importer->getErrors())
		{
			throw new Exception("Errors occured whilst migrating to PUBLISHED status: " . NL . $error_log);
		}
		
		return $this->getDraftByKey($registry_object->key);
	}

	/**
	 * Deletes a RegistryObject 
	 *
	 * @param the registry object key
	 * @return TRUE if delete was successful
	 */
	public function deleteRegistryObject($target_ro, $dry_run = false)
	{
		$reenrich_queue = array();

		// Check target_ro
		if (!$target_ro instanceof _registry_object)
		{
			$target_ro = $this->getByID($target_ro);
			if (!$target_ro)
			{
				throw new Exception("Registry Object targeted for delete does not exist?");
			}
		}

		if (isPublishedStatus($target_ro->status))
		{
			// Handle URL backup
			$this->db->where('registry_object_id', $target_ro->id);
			$this->db->update('url_mappings', array(	"registry_object_id"=>NULL, 
														"search_title"=>$target_ro->title, 
														"updated"=>time()
													));

			// Add to deleted_records table
			$this->db->set(array(
								'data_source_id'=>$target_ro->data_source_id,
								'key'=>$target_ro->key,
								'deleted'=>time(),
								'title'=>$target_ro->title,
								'record_data'=>$target_ro->getRif(),
							));
			$this->db->insert('deleted_registry_objects');

			// Re-enrich and reindex related
			$reenrich_queue = array_merge($target_ro->getRelationships(), $reenrich_queue);

			// Delete from the index
			$this->solr->deleteByQueryCondition("id:(\"".$target_ro->id."\")");
			$this->solr->commit();
		}

		// Delete the actual registry object
		if (!$dry_run)
		{
			if (isDraftStatus($target_ro->status))
			{
				//$this->db->where('registry_object_id', $target_ro->id);
				//$this->db->update('registry_objects', array(	"status" => "DELETED"
				//									));
				$target_ro->eraseFromDatabase($target_ro->id);
			}
			else
			{
				// Publish records get deleted
				//$this->db->where('registry_object_id', $target_ro->id);
				//$this->db->update('registry_objects', array(	"status" => "DELETED"
				//										));
				$target_ro->eraseFromDatabase($target_ro->id);

				// And then their related records get reindexed...
				$this->importer->_enrichRecords($reenrich_queue);
				$this->importer->_reindexRecords($reenrich_queue);
				log_message('debug', "Reindexed " . count($reenrich_queue) . " related record(s) when " . $target_ro->key . " was deleted.");
			}
		}
	}

	public function getDeletedRegistryObjects($data_source_id)
	{
		$query = $this->db->get_where('deleted_registry_objects', array("data_source_id" => $data_source_id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->result_array();
		}
	
	}


	public function getDeletedRegistryObject($id)
	{
		$query = $this->db->get_where('deleted_registry_objects', array("id" => $id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->result_array();
		}
	
	}

	public function removeDeletedRegistryObject($id)
	{
		$this->db->where("id", $id)->delete('deleted_registry_objects');
		return;
	}


	public function clearAllFromDatasourceUnsafe($data_source_id)
	{
		$reenrich_queue = array();

		$registryObjects = $this->getIDsByDataSourceID($data_source_id);

		foreach($registryObjects AS $target_ro_id)
		{
			$target_ro = $this->ro->getByID($target_ro_id);
			$target_ro->eraseFromDatabase();
		}

		// Delete from the index
		$this->solr->deleteByQueryCondition("data_source_id:(\"".$data_source_id."\")");
		$this->solr->commit();

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
