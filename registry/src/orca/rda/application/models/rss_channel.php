<?php
/**
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/
?>
<?php
	class Rss_channel extends CI_Model {

		private $startCount = 0;
		private $rowCount = 5;
		private	$rssArraySize = 20;
		private $maxEntriesPerDataSource = 5;
		private $recursionLevel = 0;
		private $recursionMax = 21;
		private $query = "";
		private $rssArray = array();
		private $solrInstance;
		private $rdaInstance;

	    function __construct() {
	        parent::__construct();
			$this->solrInstance = $this->config->item('solr_url');
			$this->rdaInstance = base_url();
	    }

		function getRssArrayForQuery($query)
		{
			parse_str($_SERVER['QUERY_STRING'], $_GET);
			$dataSource = '';
			$dataGroup = '';
			$query = $this->input->get('q');
			$classFilter = $this->input->get('classFilter');
			$groupFilter = $this->input->get('groupFilter');
			$typeFilter = $this->input->get('typeFilter');
			$subjectFilter = $this->input->get('subjectFilter');
			$licenceFilter = $this->input->get('licenceFilter');
			$dataSource = $this->input->get('dataSource');
			$digest = $this->input->get('digest');
			if(!$digest) $digest = 'true';

			$status = 'PUBLISHED';

			$filter_query = '';
    		if($classFilter!='All') $filter_query .= constructFilterQuery('class', $classFilter);
    		if($typeFilter!='All') $filter_query .= constructFilterQuery('type', $typeFilter);
    		if($groupFilter!='All') $filter_query .= constructFilterQuery('group', $groupFilter);


    		if($subjectFilter!='All')
    		{
	    		// treat http://-style subject searches as URI searches
	    		if (strpos(rawurldecode($subjectFilter), "http://") !== FALSE)
	    		{

	    			$filter_query .= constructFilterQuery('subject_vocab_uri', rawurldecode($subjectFilter));
	    		}
	    		else
	    		{
	    			$filter_query .= constructFilterQuery('subject_value_resolved', $subjectFilter);
	    		}
    		}
    		// Fix: if there is no subject to match against (i.e. blank subject) suitably random string will prevent any matches
    		if($subjectFilter == '') $filter_query .= constructFilterQuery('subject_value_resolved', "nr3kl90u3asd");


    		if($licenceFilter!='All')$filter_query .= constructFilterQuery('licence_group', $licenceFilter);
    		if($status!='All') $filter_query .= constructFilterQuery('status', $status);
     		if($dataGroup!='') $filter_query .= constructFilterQuery('group', $dataGroup);

    		$this->query = $query;
			$solrOutput = array('response'=>array('numFound'=>999));

			while (count($this->rssArray) < $this->rssArraySize && $this->startCount <= $solrOutput['response']['numFound'])
			{
				$this->recursionLevel++;
				if ($this->recursionLevel == $this->recursionMax) break;

				// This crazy bit of code is a work around for missing qotes on a 'magically' inserted bit of query	which attempts to eliminate any results back from the Belvedere Walkthrough group
				$this->query = str_replace('Belvedere Walkthrough','"Belvedere Walkthrough"',$this->query);


				// This is to cater for the new solar not having a default search field so if our query comes in with *:* we don't have to set up the now required default filed.
				if(str_replace('*:*','',$this->query)!=$this->query)
				{
					$start = '';
				}else{
					$start = 'fulltext:';
				}

				$solrUrl = $this->solrInstance . "select/?q=".$start.rawurlencode($this->query).$filter_query."&version=2.2&start=".$this->startCount."&rows=".$this->rowCount."&indent=on&fl=key,%20group%20data_source_key,%20description_value,,%20description_type,%20list_title,%20date_modified&wt=json&sort=date_modified%20desc";

				$solrOutput = json_decode(file_get_contents($solrUrl), true);




				foreach ($solrOutput['response']['docs'] AS $response)
				{

					if (!$this->isDigested($response['group'].":".date("Y-m-d",strtotime($response['date_modified']))) or $digest=='false')
					{
						// "we don't want digest";
						$this->addToRssArray($response,$groupFilter,$dataGroup,$digest);
					}
					else
					{
						$this->addToDigest($response['group'].":".date("Y-m-d",strtotime($response['date_modified'])), $response);
					}
				}

				$this->startCount += $this->rowCount;
			}
			return $this->rssArray;
		}



		function addToRssArray($response,$groupFilter,$dataSource,$digest)
		{

			if ($this->getCountByDataSource($response['group'].":".date("Y-m-d",strtotime($response['date_modified'])))+1 > $this->maxEntriesPerDataSource  && $digest!="false")
			{
				// digest them
				$digest_entry_index = null;

				$digest_entry = array('type'=>'digest', 'key'=> $response['group'], 'group'=>$response['group'].":".date("Y-m-d",strtotime($response['date_modified'])),'updated_date'=>date("Y-m-d",strtotime($response['date_modified'])), 'updated_items'=>array());

				foreach ($this->rssArray AS $index => $item)
				{
					$dateStr = '';
					if(isset($item['date_modified'])) $dateStr = ":".date("Y-m-d",strtotime($item['date_modified'])) ;
					if (isset($item['updated_date'])) $dateStr = ":".date("Y-m-d",strtotime($item['updated_date'])) ;

					if ($item['group'].$dateStr== $response['group'].":".date("Y-m-d",strtotime($response['date_modified'])))
					{
						if (!$digest_entry_index)
						{
							$digest_entry_index = $index;
						}

						$digest_entry['updated_items'][] = $item;

						unset($this->rssArray[$index]);
					}
				}
				$digest_entry['updated_items'][] = $response;

				$this->rssArray[$digest_entry_index] = $digest_entry;
				$this->rssArray = array_values($this->rssArray);
				$this->query .= ' -(group:("'.$response['group'].'") +date_modified:['.date("Y-m-d\T00:00:00\Z",strtotime($response['date_modified'])).' TO '.date("Y-m-d\T23:59:59\Z",strtotime($response['date_modified'])).'])';
				$this->startCount += $this->rowCount;
			}
			else
			{
				$keyFound = false;
				foreach ($this->rssArray AS $item)
				{
					if ($item['key'] ==  $response['key'])$keyFound=true;
				}

				if(!$keyFound) $this->rssArray[] = array_merge($response, array('type'=>'item'));
			}
		}


		function getCountByDataSource($group)
		{
			$count=0;

			foreach ($this->rssArray AS $item)
			{

				$dateStr = '';
				if(isset($item['date_modified'])) $dateStr = ":".date("Y-m-d",strtotime($item['date_modified'])) ;
				if ($item['group'].$dateStr == $group)
				{
					$count++;
				}
				//echo $count." is the count<br />";
			}
			return $count;
		}


		function isDigested($group)
		{
			foreach ($this->rssArray AS $item)
			{
				if ($item['type'] == 'digest' && $item['group'] == $group)
				{
					return true;
				}
			}
			return false;
		}

		function addToDigest($group, $response)
		{


			foreach ($this->rssArray AS $index => $item)
			{

				if ($item['type'] == 'digest' && $item['group']  == $group)

				{
					$target_id = $index;
				}
			}

			if (!is_null($target_id) && count($this->rssArray[$target_id]['updated_items'])<$this->rowCount)
			{
				$this->rssArray[$target_id]['updated_items'][] = $response;
				return true;
			}
			else
			{
				return false;
			}
		}
		
		
		function getTwitterArray()
		{
			// Get the subject count of items resolved in the past 24 hours
			$filter_query = 'date_modified:[NOW-7DAY%20TO%20NOW]%20status:("PUBLISHED")%20class:("collection")';
			$solrUrl = $this->solrInstance . "select/?q=".$filter_query.rawurlencode($this->query)."&version=2.2&rows=0&wt=json&facet=on&facet.field=broader_subject_value_unresolved&facet.mincount=1";

			$solrOutput = json_decode(file_get_contents($solrUrl), true);
			if (isset($solrOutput['facet_counts']['facet_fields']['broader_subject_value_unresolved']))
			{
				$subject_list = $solrOutput['facet_counts']['facet_fields']['broader_subject_value_unresolved'];
				$subjects = array();
				
				// Count backwards in steps of 2
				for ($i=count($subject_list)-2; $i>=0; $i-=2)
				{
					if (is_numeric($subject_list[$i]))
					{
						$subjects[$subject_list[$i]] = $subject_list[$i+1];
					}
				}
				
				foreach ($subjects AS $code => $count)
				{
					$resolvedSubject = resolveFromVocabNotation($code);
					if ($resolvedSubject)
					{
						$this->rssArray[] = array(
											"count"=>$count, 
											"code"=>$code, 
											"resolved_subject"=>(isset($resolvedSubject['prefLabel']['_value']) ? $resolvedSubject['prefLabel']['_value'] : $code),
											"resolved_uri"=>(isset($resolvedSubject['_about']) ? $resolvedSubject['_about'] : $code), 
											"type"=>"twitter");
					}
				}
				
			}
			return $this->rssArray;
		}

}
?>