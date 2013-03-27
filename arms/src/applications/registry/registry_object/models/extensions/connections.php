<?php


class Connections_Extension extends ExtensionBase
{

	private $party_one_types = array('person','administrativePosition');
	private $party_multi_types = array('group');

	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/**
	 * Get a list of connections, returning the details needed to 
	 * display the relationship:
	 *
	 * Business Rules:
	 * 1) If `a` is related to `b`, display the connection
	 *
	 * 2) If INTERNAL LINKS are enabled and `b` is related to `a`
	 *    AND `b` and `a` are in the same data source, display the
	 *    connection. 
	 *
	 * 3) If EXTERNAL LINKS are enabled and `b` is related to `a`
	 *    AND `b` and `a` are in different data sources, display
	 *    the connection. 
	 *
	 * 4) If the group of `a` has a contributor page, infer and 
	 *    display the connection.
	 *
	 * 5) If this record is 'not published', then allow links to other
	 *    'not published' records. 
	 *
	 * @return 	array ( 
	 *					array(
	 *						origin (of inference)
	 *						key
	 *						type
	 *						description
	 *						class
	 *						title
	 *					)
	 *			)
	 */
	function getConnections($published_only = true, $specific_type = null, $limit = 100, $offset = 0)
	{
		$unordered_connections = array();
		$ordered_connections = 	array();
		$total_connection_count = 0;

		$this->_CI->load->model('data_source/data_sources','ds');
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		$allow_reverse_internal_links = ($ds->allow_reverse_internal_links == "t");
		$allow_reverse_external_links = ($ds->allow_reverse_external_links == "t");
		
		/* Step 1 - Straightforward link relationships */
		$unordered_connections = array_merge($unordered_connections, $this->_getExplicitLinks());

		/* Step 2 - Internal reverse links */
		if ($allow_reverse_internal_links)
		{
			$unordered_connections = array_merge($unordered_connections, $this->_getInternalReverseLinks());
		}

		/* Step 3 - External reverse links */
		if ($allow_reverse_external_links)
		{
			$unordered_connections = array_merge($unordered_connections, $this->_getExternalReverseLinks());
		}

		/* Step 4 - Contributor */
		$unordered_connections = array_merge($unordered_connections, $this->_getContributorLinks());


		/* Now sort according to "type" (collection / party_one / party_multi / activity...etc.) */
		foreach($unordered_connections AS $connection)
		{

			// some witchcraft to disambiguate between single researchers 
			// and groups (based on registry object type)
			if ($connection['class'] == "party")
			{
				// Get the type attribute (without loading the whole model)
				$this->db->select('value')
						 ->from('registry_object_attributes')
						 ->where('attribute', 'type')
						 ->where('registry_object_id',$connection['registry_object_id']);

				$query = $this->db->get();
				foreach($query->result_array() AS $row)
				{
					if (isset($row['value']))
					{
						if(in_array($row['value'],$this->party_one_types))
						{
							$connection['class'] = "party_one";
						}
						elseif (in_array($row['value'],$this->party_multi_types))
						{
							$connection['class'] = "party_multi";
						}
					}
				}
			}
			// $connection['description'] = $this->_getDescription($connection['registry_object_id']);

			// Continue on for all types:
			/* - Check the constraints */
			$class_valid = (is_null($specific_type) || ($connection['class'] == $specific_type));
			$status_valid = (!$published_only || ($connection['status'] == PUBLISHED));
			if ($class_valid && $status_valid)
			{

				/* - Now classify the counts  */
				if (!isset($ordered_connections[$connection['class']]))
				{
					$ordered_connections[$connection['class']] = array();
					$ordered_connections[$connection['class'] . '_count'] = 0;
				}

				// Stop the same connected object coming from two different sources
				if(!isset($ordered_connections[$connection['class']][$connection['registry_object_id']]))
				{
					$ordered_connections[$connection['class']][(int)$connection['registry_object_id']] = $connection;
					$ordered_connections[$connection['class'] . '_count']++;
				}				

			}
		}

		/* - Handle the offsetting/limits */
		if ($limit || $offset)
		{
			foreach($ordered_connections AS $name => $list)
			{
				if (is_array($list))
				{
					$ordered_connections[$name] = array_slice($list, $offset, $limit);
					foreach($ordered_connections[$name] as &$connection){
						$connection['description'] = $this->_getDescription($connection['registry_object_id']);
						$connection['logo'] = $this->_getLogo($connection['registry_object_id']);					
					}
				}
			}
		}
		return array($ordered_connections);
	}


	function getAllRelatedObjects()
	{
		$connections = array();
		$this->_CI->load->model('data_source/data_sources','ds');
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		$allow_reverse_internal_links = ($ds->allow_reverse_internal_links == "t");
		$allow_reverse_external_links = ($ds->allow_reverse_external_links == "t");
		
		$connections = array_merge($connections, $this->_getExplicitLinks());

		/* Step 2 - Internal reverse links */
		if ($allow_reverse_internal_links)
		{
			$connections = array_merge($connections, $this->_getInternalReverseLinks());
		}

		/* Step 3 - External reverse links */
		if ($allow_reverse_external_links)
		{
			$connections = array_merge($connections, $this->_getExternalReverseLinks());
		}

		return $connections;
	}

	function _getDescription($id){
		$this->db->select('value')->from('registry_object_metadata')->where('registry_object_id', $id)->where('attribute', 'the_description')->limit(1);
		$query = $this->db->get();
		foreach($query->result() as $row){
			return $row->value;
		}
	}


	function _getLogo($id){
		$this->db->select('value')->from('registry_object_metadata')->where('registry_object_id', $id)->where('attribute', 'the_logo')->limit(1);
		$query = $this->db->get();
		foreach($query->result() as $row){
			return $row->value;
		}
	}

	function _getExplicitLinks()
	{
		/* Step 1 - Straightforward link relationships */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description')
				 ->from('registry_object_relationships rr')
				 ->join('registry_objects r','rr.related_object_key = r.key')
				 ->where('rr.registry_object_id',$this->id);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "EXPLICIT";
			$my_connections[] = $row;
		}

		return $my_connections;
	}



	function _getInternalReverseLinks()
	{
		/* Step 2 - Internal reverse links */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id')
						 ->where('rr.related_object_key',$this->ro->key)
						 ->where('r.data_source_id',$this->ro->data_source_id);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "REVERSE_INT";
			$my_connections[] = $row;
		}

		return $my_connections;
	}



	function _getExternalReverseLinks()
	{
		/* Step 3 - External reverse links */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id')
						 ->where('rr.related_object_key',$this->ro->key)
						 ->where('r.data_source_id !=',$this->ro->data_source_id);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "REVERSE_EXT";
			$my_connections[] = $row;
		}

		return $my_connections;
	}



	function _getContributorLinks()
	{
		/* Step 4 - Contributor */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.class, r.title, r.slug, r.status')
						 ->from('institutional_pages i')
						 ->join('registry_objects r','i.registry_object_id = r.registry_object_id')
						 ->where('i.group',$this->ro->group);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "CONTRIBUTOR";
			$row['class'] = "contributor";
			$my_connections[] = $row;
		}

		return $my_connections;
	}


	
}