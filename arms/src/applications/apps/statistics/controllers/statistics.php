<?php

class Statistics extends MX_Controller {


	// Default page, containing current statistics
	function index()
	{
		
		$from = '';
		$to = '';

		if($_GET = $this->input->get())
		{
			$from =strtotime($_GET['dateFrom']);
			$to = strtotime($_GET['dateTo']);			
		}

		if(!$from) $from = time();		
		if(!$to) $to = time();
	

		$data['registry_statistics'] = $this->getRegistryStatistics($from , $to);
		$data['doi_statistics'] = $this->getDoiStatistics($from, $to);
		$data['pids_statistics'] = $this->getPidsStatistics($from, $to);

		$data['user_statistics'] = $this->getUserStatistics($from, $to);
		$data['title'] = "Statistics";
		$data['js_lib'] = array('core', 'ands_datepicker', 'statistics');
		$this->load->view('statistics', $data);
	}

	// Returns the count by month of the registry objects
	function getRegistryStatistics($from,$to)
	{

		$number_of_months = 1;
		$newMonth = $to;
		$theMonth = date("m",$to);
		if($from)
		{

			$number_of_months = date("n",$from)- date("n",$to) +1;
			$newMonth = $from;
			$theMonth = date("m",$from);
			$theYear = date("Y",$from);
		}
		$registry_statistics =array();
		$newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
		$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);				
		while($aMonth<=$to)
		{
			//$CI =& get_instance();
			//$db =& $CI->db;
			$db = $this->load->database('registry', TRUE);
			$query = $db->query("SELECT COUNT(`registry_objects`.`class`) as theCount, `registry_objects`.`class`   
				FROM `registry_object_attributes` , `registry_objects`
				WHERE `registry_objects`.`registry_object_id` = `registry_object_attributes`.`registry_object_id`
				AND `registry_object_attributes`.`attribute` = 'created' 
				AND `registry_objects`.`status` = 'PUBLISHED'
				AND `registry_object_attributes`.`value` < ".$newMonth." GROUP BY `registry_objects`.`class`");


			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$registry_statistics[$month." - ".$theYear][$row->class] = $row->theCount;
			}
			$theMonth++;
			if ($theMonth==13)
			{
				$theMonth = 1;
				$theYear++;
			}
			$newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
			$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
		}
		return $registry_statistics;
	}

	// Returns the count by month of the doi statistics
	private function getDoiStatistics($from,$to)
	{
		
		$doi_db = $this->load->database('dois', TRUE);

		$number_of_months = 1;
		$newMonth = $to;
		$theMonth = date("m",$to);
		if($from)
		{

			$number_of_months = date("n",$from)- date("n",$to) +1;
			$newMonth = $from;
			$theMonth = date("m",$from);
			$theYear = date("Y",$from);
		}
		$doi_statistics =array();
		$newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
		$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
		while($aMonth<=$to)
		{
			$query = $doi_db->query("SELECT	COUNT(*) as thecount
			FROM doi_objects 
			WHERE created_when <  CAST('".date("Y-m-d",$newMonth)."' AS timestamp with time zone) AND doi_id NOT LIKE '10.5072/%' AND status = 'ACTIVE' ");
			
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$doi_statistics[$month." - ".$theYear]['DOIs Minted'] = $row->thecount;
			}

			$query = $doi_db->query("SELECT COUNT(DISTINCT(app_id)) as thecount
			FROM doi_client  
			WHERE created_when <  CAST('".date("Y-m-d",$newMonth)."' AS timestamp with time zone)");
			
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$doi_statistics[$month." - ".$theYear]['Registered Clients'] = $row->thecount;
			}

			$query = $doi_db->query("SELECT COUNT(*) as thecount
			FROM activity_log 
			WHERE timestamp <  CAST('".date("Y-m-d",$newMonth)."' AS timestamp with time zone) AND activity = 'MINT' AND result = 'FAILURE' AND doi_id NOT LIKE '10.5072/%'");
			
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$doi_statistics[$month." - ".$theYear]['Minting failures'] = $row->thecount;
			}
		
		
			$theMonth++;
			if ($theMonth==13)
			{
				$theMonth = 1;
				$theYear++;
			}
			$newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
			$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
			
		}
		return $doi_statistics;	
	}

	private function getPidsStatistics($from,$to)
	{
		
		$doi_db = $this->load->database('pids', TRUE);

		$number_of_months = 1;
		$newMonth = $to;
		$theMonth = date("m",$to);
		if($from)
		{

			$number_of_months = date("n",$from)- date("n",$to) +1;
			$newMonth = $from;
			$aMonth = $from;
			$theMonth = date("m",$from);
			$theYear = date("Y",$from);
		}
		$pids_statistics =array();
		$newMonth = mktime(0, 0, 0, $theMonth+ 1, 0, $theYear);
		$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
		while($aMonth<=$to)
		{
			$query = $doi_db->query("SELECT COUNT(*) as thecount FROM handles WHERE type='URL'AND timestamp < ".$newMonth);	
		
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$pids_statistics[$month." - ".$theYear]['PIDs Minted'] = $row->thecount;
			}

			$query = $doi_db->query("SELECT COUNT(DISTINCT(app_id)) as thecount FROM trusted_client 
			WHERE created_when <  CAST('".date("Y-m-d",$newMonth)."' AS timestamp with time zone)");
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$pids_statistics[$month." - ".$theYear]['Registered Clients'] = $row->thecount;
			}

			
		
			$theMonth++;
			if ($theMonth==13)
			{
				$theMonth = 1;
				$theYear++;
			}
			$newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
			$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
		}
		return $pids_statistics;	
	}


private function getUserStatistics($from,$to)
	{
		
		$roles_db = $this->load->database('roles', TRUE);
		$db = $this->load->database('registry', TRUE);
		$number_of_months = 1;
		$newMonth = $to;
		$theMonth = date("m",$to);
		if($from)
		{

			$number_of_months = date("n",$from)- date("n",$to) +1;
			$newMonth = $from;
			$aMonth = $from;
			$theMonth = date("m",$from);
			$theYear = date("Y",$from);
		}
		$user_statistics =array();
		$newMonth = mktime(0, 0, 0, $theMonth+ 1, 0, $theYear);
		$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
		while($aMonth<=$to)
		{
			$dateTimeMonth = date("Y-m-d 00:00:00", $newMonth);
			$query = $roles_db->query("SELECT count(DISTINCT(trim(both '-' from trim(both ' ' from lower(substring(role_id from 1 for 4)))))) as thecount 
			FROM `dbs_roles`.`roles` where `roles`.`role_type_id` = 'ROLE_ORGANISATIONAL'
			AND `roles`.`created_when` <= '".$dateTimeMonth."'");	
	
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$user_statistics[$month." - ".$theYear]['Organisations'] = $row->thecount;
			}

			$query = $roles_db->query("SELECT COUNT(DISTINCT(roles.role_id)) as thecount
			FROM `dbs_roles`.`roles`, `dbs_roles`.`role_relations` 
			WHERE `roles`.`role_type_id` = 'ROLE_USER'
			AND `role_relations`.`parent_role_id` <> 'ORCA_CLIENT_LIAISON'
			AND `roles`.`role_id` = `role_relations`.`child_role_id`
			AND `roles`.`authentication_service_id` <> 'AUTHENTICATION_LDAP' 
			AND `roles`.`created_when` <='".$dateTimeMonth."'");
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$user_statistics[$month." - ".$theYear]['Users'] = $row->thecount;
			}

			$query = $roles_db->query("SELECT COUNT(*) as thecount
			FROM `dbs_roles`.`role_relations` 
			WHERE `role_relations`.`parent_role_id`='ORCA_SOURCE_ADMIN' 
			AND `role_relations`.`child_role_id` NOT LIKE '%@ands.org.au' 
			AND `role_relations`.`child_role_id` <> 'COSI_ADMIN'
			AND `role_relations`.`child_role_id` <> 'u4187959'
			AND `role_relations`.`child_role_id` <> 'u4958094'
			AND `role_relations`.`child_role_id` <> 'u4552016'
			AND `role_relations`.`created_when` <= '".$dateTimeMonth."'");
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$user_statistics[$month." - ".$theYear]['Data Source Adminstrators'] = $row->thecount;
			}

			$query = $db->query("SELECT COUNT(DISTINCT(`data_sources`.`data_source_id`)) as thecount
			FROM `dbs_registry`.`data_sources`, `dbs_registry`.`data_source_attributes`
			WHERE `data_source_attributes`.`attribute` = 'created'
			AND `data_sources`.`data_source_id` = `data_source_attributes`.`data_source_id`
			AND  `data_source_attributes`.`value` < ".$newMonth);
			foreach($query->result() as $key=>$row)
			{
				$month=date("M", $newMonth);
				$user_statistics[$month." - ".$theYear]['Provider Organisations'] = $row->thecount;
			} 

		
			$theMonth++;
			if ($theMonth==13)
			{
				$theMonth = 1;
				$theYear++;
			}
			$newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
			$aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
		}
		return $user_statistics;	
	}
function getCitationStatistics()
	{
		$registry_db = $this->load->database('registry', TRUE);
		$statistics_db = $this->load->database('statistics', TRUE);

		$query = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collection_count, `data_sources`.`data_source_id`
							FROM `registry_objects`,`data_sources`
							WHERE `registry_objects`.`data_source_id` = `data_sources`.`data_source_id`
							AND `registry_objects`.`class` = 'collection'
							AND `registry_objects`.`status` = 'PUBLISHED'
							GROUP BY `registry_objects`.`data_source_id`; ");

		foreach($query->result() as $key=>$row)
		{
			$timestamp = time();
			$query = $statistics_db->query("INSERT INTO  `citations` (`data_source_id`,`collection_count`,`timestamp`) VALUES (".$row->data_source_id.", ".$row->collection_count.", ".$timestamp.")");
		}

		$citationMetadata_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as citationMetadata_count,  `registry_objects`.`data_source_id`
							FROM `record_data`, `registry_objects` 
							WHERE `record_data`.`data` LIKE '%citationMetadata%' 
							AND `record_data`.`scheme` = 'rif' 
							AND `record_data`.`current` = 'TRUE'
							AND `registry_objects`.`status` = 'PUBLISHED'							
							AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
							AND `registry_objects`.`class` = 'collection'
							GROUP BY `registry_objects`.`data_source_id`");
		foreach($citationMetadata_query->result() as $key=>$row)
		{
			$query = $statistics_db->query("UPDATE  `citations` SET `citationMetadata_count` = ".$row->citationMetadata_count." WHERE `data_source_id` = ".$row->data_source_id);
		}


		$fullCitation_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as fullCitation_count,  `registry_objects`.`data_source_id`
							FROM `record_data`, `registry_objects` 
							WHERE `record_data`.`data` LIKE '%fullCitation%' 
							AND `record_data`.`scheme` = 'rif' 
							AND `record_data`.`current` = 'TRUE'
							AND `registry_objects`.`status` = 'PUBLISHED'							
							AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
							AND `registry_objects`.`class` = 'collection'
							GROUP BY `registry_objects`.`data_source_id`");
		foreach($fullCitation_query->result() as $key=>$row)
		{
			$query = $statistics_db->query("UPDATE  `citations` SET `fullCitation_count` = ".$row->fullCitation_count." WHERE `data_source_id` = ".$row->data_source_id);
		}		
	}
	function collectCitationStatistics()
	{
		$registry_db = $this->load->database('registry', TRUE);
		$statistics_db = $this->load->database('statistics', TRUE);

		$query = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collection_count, `data_sources`.`data_source_id`
							FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`data_sources`
							WHERE `registry_objects`.`data_source_id` = `data_sources`.`data_source_id`
							AND `registry_objects`.`class` = 'collection'
							AND `registry_objects`.`status` = 'PUBLISHED'
							GROUP BY `registry_objects`.`data_source_id`; ");

		foreach($query->result() as $key=>$row)
		{
			$timestamp = time();
			$query = $statistics_db->query("INSERT INTO  `citations` (`data_source_id`,`collection_count`,`timestamp`) VALUES (".$row->data_source_id.", ".$row->collection_count.", ".$timestamp.")");
		}

		$citationMetadata_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as citationMetadata_count,  `registry_objects`.`data_source_id`
							FROM `dbs_registry`.`record_data`, `dbs_registry`.`registry_objects` 
							WHERE `record_data`.`data` LIKE '%citationMetadata%' 
							AND `record_data`.`scheme` = 'rif' 
							AND `record_data`.`current` = 'TRUE'
							AND `registry_objects`.`status` = 'PUBLISHED'							
							AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
							AND `registry_objects`.`class` = 'collection'
							GROUP BY `registry_objects`.`data_source_id`");
		foreach($citationMetadata_query->result() as $key=>$row)
		{
			$query = $statistics_db->query("UPDATE  `citations` SET `citationMetadata_count` = ".$row->citationMetadata_count." WHERE `data_source_id` = ".$row->data_source_id);
		}


		$fullCitation_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as fullCitation_count,  `registry_objects`.`data_source_id`
							FROM `dbs_registry`.`record_data`, `dbs_registry`.`registry_objects` 
							WHERE `record_data`.`data` LIKE '%fullCitation%' 
							AND `record_data`.`scheme` = 'rif' 
							AND `record_data`.`current` = 'TRUE'
							AND `registry_objects`.`status` = 'PUBLISHED'							
							AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
							AND `registry_objects`.`class` = 'collection'
							GROUP BY `registry_objects`.`data_source_id`");
		foreach($fullCitation_query->result() as $key=>$row)
		{
			$query = $statistics_db->query("UPDATE  `citations` SET `fullCitation_count` = ".$row->fullCitation_count." WHERE `data_source_id` = ".$row->data_source_id);
		}	
	}


	function collectIdentifierStatistics()
	{
		$registry_db = $this->load->database('registry', TRUE);
		$statistics_db = $this->load->database('statistics', TRUE);

		$this->load->library('solr');
		$query = $registry_db->query("SELECT  `data_sources`.`data_source_id`
							FROM `dbs_registry`.`data_sources`; ");

		foreach($query->result() as $key=>$row)
		{
			$timestamp = time();
			$doi_identifiers = 0;
			// use solr to get all identifiers of type doi for published collections
			$this->solr->setOpt('q', '+data_source_id:("'.$row->data_source_id.'") AND +class:collection AND +identifier_type:doi AND +status:PUBLISHED');


			$data['solr_result'] = $this->solr->executeSearch();
			$this->solr->setOpt('start', 0);
			$this->solr->setOpt('rows', 1);
			$data['result'] = $this->solr->getResult();		
			$data['numFound'] = $this->solr->getNumFound();
			$doi_identifiers = $data['numFound'];
		
			// use solr to get all identifiers of type uri with a doi value for published collections
			$this->solr->setOpt('q', '+data_source_id:("'.$row->data_source_id.'") AND +class:collection AND +identifier_type:uri AND +identifier_value:*doi.org* AND +status:PUBLISHED');	
			$data['solr_result'] = $this->solr->executeSearch();
			$this->solr->setOpt('start', 0);
			$this->solr->setOpt('rows', 1);
			$data['result'] = $this->solr->getResult();
			$data['numFound'] = $this->solr->getNumFound();
			$doi_identifiers = $doi_identifiers + $data['numFound'];


			$orcid_identifiers = 0;	
			// use solr to get all identifiers of type orcid for published parties
			$this->solr->setOpt('q', '+data_source_id:("'.$row->data_source_id.'") AND +class:party AND +identifier_type:orcid AND +status:PUBLISHED');
			$data['solr_result'] = $this->solr->executeSearch();
			$this->solr->setOpt('start', 0);
			$this->solr->setOpt('rows', 1);
			$data['result'] = $this->solr->getResult();		
			$data['numFound'] = $this->solr->getNumFound();
			$orcid_identifiers = $data['numFound'];
		
			// use solr to get all identifiers of type uri with a doi value for published collections
			$this->solr->setOpt('q', '+data_source_id:("'.$row->data_source_id.'") AND +class:party AND +identifier_type:uri AND +identifier_value:*orcid* AND +status:PUBLISHED');	
			$data['solr_result'] = $this->solr->executeSearch();
			$this->solr->setOpt('start', 0);
			$this->solr->setOpt('rows', 1);
			$data['result'] = $this->solr->getResult();
			$data['numFound'] = $this->solr->getNumFound();
			$orcid_identifiers = $orcid_identifiers + $data['numFound'];

			$handle_identifiers = 0;
			// use solr to get all identifiers of type handle for published collections
			$this->solr->setOpt('q', '+data_source_id:("'.$row->data_source_id.'") AND +class:collection AND +identifier_type:handl* AND +status:PUBLISHED');
			$data['solr_result'] = $this->solr->executeSearch();
			$this->solr->setOpt('start', 0);
			$this->solr->setOpt('rows', 3);
			$data['result'] = $this->solr->getResult();		
			$data['numFound'] = $this->solr->getNumFound();
			$handle_identifiers = $data['numFound'];
		
			// use solr to get all identifiers of type uri with a doi value for published collections
			$this->solr->setOpt('q', '+data_source_id:("'.$row->data_source_id.'") AND +class:party  AND +identifier_type:uri AND +identifier_value:*handle* AND +status:PUBLISHED');	
			$data['solr_result'] = $this->solr->executeSearch();
			$this->solr->setOpt('start', 0);
			$this->solr->setOpt('rows', 3);
			$data['result'] = $this->solr->getResult();
			$data['numFound'] = $this->solr->getNumFound();
			$handle_identifiers = $handle_identifiers + $data['numFound'];

			$query = $statistics_db->query("INSERT INTO  `identifiers` (`data_source_id`,`doi`,`orcid`,`handle`,`timestamp`) VALUES (".$row->data_source_id.",".$doi_identifiers.", ".$orcid_identifiers.", ".$handle_identifiers.", ".$timestamp.")");
		}

	}

	function collectRelationshipStatistics()
	{

		$registry_db = $this->load->database('registry', TRUE);
		$statistics_db = $this->load->database('statistics', TRUE);

		$collectionPartyRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collectionPartyCount, `registry_objects`.`data_source_id`
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'collection'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'party'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			GROUP BY  `registry_objects`.`data_source_id`");

		$timestamp = time();

		foreach($collectionPartyRelationship->result() as $key=>$row)
		{	
			echo "Datasource ".$row->data_source_id." has ".$row->collectionPartyCount." collections with related party records<br/>";			
		}


		/*
		SELECT `registry_objects`.`registry_object_id`, `registry_objects`.`data_source_id`, 
		`registry_object_relationships`.`related_object_class`,`registry_object_relationships`.`related_object_key`
		FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
		WHERE `registry_objects`.`status`='PUBLISHED' 
		AND `registry_objects`.`class` = 'collection'
		AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
		AND `registry_object_relationships`.`related_object_class` = 'activity'
		AND `registry_object_relationships`.`origin` = 'EXPLICIT'
		AND `registry_object_relationships`.`related_object_key` LIKE 'http://purl.org/au-research/grants/arc/%';
*/
	}


	// Initialise
	function __construct()
	{
		parent::__construct();
		//acl_enforce('PORTAL_STAFF');
	}


}