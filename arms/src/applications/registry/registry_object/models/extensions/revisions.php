<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Revisions_extension extends ExtensionBase
{
	
	
	/**
	 * @ignore
	 * This MUST be defined in order to get the in-scope extensions variables
	 */
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}
	
	function getAllRevisions()
	{
		date_default_timezone_set('GMT');
		$this->db->where(array('registry_object_id' => $this->ro->id, 'scheme'=>RIFCS_SCHEME));
		$this->db->where('current != TRUE');
		$this->db->order_by('timestamp', 'desc');
		$this->db->select('*')->from('record_data');
		$result = $this->db->get();	
		$revisions = array();
		foreach($result->result_array() AS $r)
		{
			$time = date("F j, Y, g:i a", $r['timestamp']);
			$revisions[$time] = $r['id'];
		}
		$result->free_result();
		return $revisions;
	}
}