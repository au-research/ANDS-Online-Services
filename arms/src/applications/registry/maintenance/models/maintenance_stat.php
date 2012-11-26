<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Maintenance Stat Model
 *
 * XXX:
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/registryobject
 *
 */

class Maintenance_stat extends CI_Model {

	
	function getTotalRegistryObjectsCount($where='db'){
		if($where=='db'){
			$this->db->from('registry_objects');
			$query = $this->db->get();
			return $query->num_rows();
		}else if($where=='solr'){
			$this->load->library('solr');
			$this->solr->setOpt('q', '*:*');
			$this->solr->executeSearch();
			return $this->solr->getNumFound();
		}
	}

	function getAllIDs($where='db'){
		$array = array();
		if($where=='db'){
			$this->db->from('registry_objects');
			$query = $this->db->get();
			foreach($query->result() as $r){
				array_push($array, $r->registry_object_id);
			}
			return $array;
		}else if($where=='solr'){
			$this->load->library('solr');
			$this->solr->setOpt('q', '*:*');
			$this->solr->setOpt('rows', '2147483647');//MAX INT, MWAHAHAHAHAHA!!!!
			$this->solr->setOpt('fl', 'id');
			$this->solr->executeSearch();
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d){
				array_push($array, $d->{'id'});
			}
			return $array;
		}
	}

	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
	}

}
