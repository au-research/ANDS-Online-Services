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
		private $rowCount = 6;
		private	$rssArraySize = 20;
		private $maxEntriesPerDataSource = 3;
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
			//$query = str_replace(' -group:(Belvedere Walkthrough)','',$query);
			//echo $query. "is the query<br />";
			$classFilter = $this->input->get('classFilter');
			$groupFilter = $this->input->get('groupFilter');
			$typeFilter = $this->input->get('typeFilter');
			$subjectFilter = $this->input->get('subjectFilter');
			$licenceFilter = $this->input->get('licenceFilter');
			$dataSource = $this->input->get('dataSource');
			$digest = $this->input->get('digest');
			//echo $digest." is the digest<br />";
			if(!$digest) $digest = 'true';

			$status = 'PUBLISHED';

			$filter_query = '';
    		if($classFilter!='All') $filter_query .= constructFilterQuery('class', $classFilter);
    		if($typeFilter!='All') $filter_query .= constructFilterQuery('type', $typeFilter);
    		if($groupFilter!='All') $filter_query .= constructFilterQuery('group', $groupFilter);
    		if($subjectFilter!='All') $filter_query .= constructFilterQuery('subject_value_resolved', $subjectFilter);
    		if($licenceFilter!='All')$filter_query .= constructFilterQuery('licence_group', $licenceFilter);
    		if($status!='All') $filter_query .= constructFilterQuery('status', $status);
     		if($dataGroup!='') $filter_query .= constructFilterQuery('group', $dataGroup);

    		$this->query = $query;
			$solrOutput = array('response'=>array('numFound'=>999));
			while (count($this->rssArray) < $this->rssArraySize && $this->startCount + $this->rowCount <= $solrOutput['response']['numFound'])
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

				$solrUrl = $this->solrInstance . "select/?q=".$start.rawurlencode($this->query).$filter_query."&version=2.2&start=".$this->startCount."&rows=".$this->rowCount."&indent=on&fl=key,%20group%20data_source_key,%20description_value,%20list_title,%20date_modified&wt=json&sort=date_modified%20desc";
				//echo $solrUrl."<br />";
				$solrOutput = json_decode(file_get_contents($solrUrl), true);
				//print_r($solrOutput);

				foreach ($solrOutput['response']['docs'] AS $response)
				{
					//print_r($response);
					if (!$this->isDigested($response['group']) or $groupFilter !="All" or $dataGroup !='' or $digest=='false')
					{
						//echo "we don't want digest";
						$this->addToRssArray($response,$groupFilter,$dataGroup,$digest);
					}
					else
					{
						$this->addToDigest($response['data_source_key'], $response);
					}
				}

				$this->startCount += $this->rowCount;
			}
			return $this->rssArray;
		}



		function addToRssArray($response,$groupFilter,$dataSource,$digest)
		{

			if ($this->getCountByDataSource($response['group'])+1 >= $this->maxEntriesPerDataSource && $groupFilter == 'All' && $dataSource == '' && $digest!="false")
			{
				// digest them
				$digest_entry_index = null;

				$digest_entry = array('type'=>'digest', 'key'=> $response['group']."~".date("Y-m-d",strtotime($response['date_modified'])), 'group'=>$response['group'],'updated_date'=>date("m-d-Y",strtotime($response['date_modified'])), 'updated_items'=>array());

				foreach ($this->rssArray AS $index => $item)
				{

					if ($item['group']== $response['group'] && $item['date_modified'] = $response['date_modified'])
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
				$this->query .= " -group:(".$response['group'].")";
				$this->startCount = -$this->rowCount;
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
				if ($item['group'] == $group)
				{
					$count++;
				}
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
			$target_id = null;

			foreach ($this->rssArray AS $index => $item)
			{

				if ($item['type'] == 'digest' && $item['group'] == $group)
				{
					$target_id = $index;
				}
			}

			if (!is_null($target_id))
			{
				$this->rssArray[$target_id]['updated_items'][] = $response;
				return true;
			}
			else
			{
				return false;
			}
		}

}
?>
