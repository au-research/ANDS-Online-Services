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
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {

	public function index(){
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		if(isset($_GET['q'])){
			$q = $_GET['q'];
			//echo $q;
			redirect(base_url().'search#!/q='.$q);
		}else{
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();
			$this->load->view('layout', $data);
		}
	}

	public function rss_twitter()
	{
		$this->output->set_header('Content-Type: application/rss+xml');
		$this->load->model('Rss_channel', 'rss');
		$result['rssArray'] = $this->rss->getTwitterArray('*:*');
		$this->load->view('search/rss_twitter', $result);
	}
	
	public function rss()
	{
		$this->output->set_header('Content-Type: application/rss+xml');
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		if(isset($_GET['q'])){
			$q = $_GET['q'];
			$classFilter = $_GET['classFilter'];
			$typeFilter = $_GET['typeFilter'];
			$groupFilter = $_GET['groupFilter'];
			$subjectFilter = $_GET['subjectFilter'];
			$licenceFilter = $_GET['licenceFilter'];
			$queryStr = '?q='.$q.'&classFilter='.$classFilter.'&typeFilter='.$typeFilter.'&groupFilter='.$groupFilter.'&subjectFilter='.$subjectFilter.'&licenceFilter='.$licenceFilter;
			
			
			$this->load->model('Rss_channel', 'rss');
			$result['rssArray'] = $this->rss->getRssArrayForQuery($queryStr);
		

			// Prepare a friendly name for the RSS feed if it contains subject search
			$result['subjectSearchTitleSuffix'] = '';
			if($subjectFilter!='All')
			{
				// treat http://-style subject searches as URI searches
				if (strpos(rawurldecode($subjectFilter), "http://") !== FALSE)
				{
					$resolvedTitles = array();
					foreach (explode(",", $subjectFilter) AS $uri)
					{
						if (strpos($uri,"~") !== FALSE)
						{
							$uri = str_replace("~","",$uri);
						}
						$resolvedTitles[] = resolveLabelFromVocabTermURI($uri);
					}

					$result['subjectSearchTitleSuffix'] .= implode(" AND ", $resolvedTitles);
				}
				else
				{
					$result['subjectSearchTitleSuffix'] .= str_replace(",", " AND ",  $subjectFilter);
				}
			}
			
			$this->load->view('search/rss', $result);
			

		}
	}
	public function atom()
	{
		$this->output->set_header('Content-Type: application/atom+xml');
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		if(isset($_GET['q'])){
			$q = $_GET['q'];
			$classFilter = $_GET['classFilter'];
			$typeFilter = $_GET['typeFilter'];
			$groupFilter = $_GET['groupFilter'];
			$subjectFilter = $_GET['subjectFilter'];
			$licenceFilter = $_GET['licenceFilter'];
			$queryStr = '?q='.$q.'&classFilter='.$classFilter.'&typeFilter='.$typeFilter.'&groupFilter='.$groupFilter.'&subjectFilter='.$subjectFilter.'&licenceFilter='.$licenceFilter;
			$this->load->model('Rss_channel', 'atom');
			$result['rssArray'] = $this->atom->getRssArrayForQuery($queryStr);
			
			// Prepare a friendly name for the RSS feed if it contains subject search
			$result['subjectSearchTitleSuffix'] = '';
			if($subjectFilter!='All')
			{
				// treat http://-style subject searches as URI searches
				if (strpos(rawurldecode($subjectFilter), "http://") !== FALSE)
				{
					$resolvedTitles = array();
					foreach (explode(",", $subjectFilter) AS $uri)
					{
						if (strpos($uri,"~") !== FALSE)
						{
							$uri = str_replace("~","",$uri);
						}
						$resolvedTitles[] = resolveLabelFromVocabTermURI($uri);
					}

					$result['subjectSearchTitleSuffix'] .= implode(" AND ", $resolvedTitles);
				}
				else
				{
					$result['subjectSearchTitleSuffix'] .= str_replace(",", " AND ",  $subjectFilter);
				}
			}
			
			$this->load->view('search/atom', $result);

		}
	}
	public function bwredirect(){//backward redirection with list.php
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		$class='All';$group='All';
		if(isset($_GET['class'])) $class=$_GET['class'];
		if(isset($_GET['group'])) $group=$_GET['group'];
		$this->browse($group,$class);
	}

	public function browse($group = 'All', $class='All'){//yeah, just redirect the browsing to the search page for now
		if($class!='All') $class=strtolower($class);
		redirect(base_url().'search#!/p=1/tab='.$class.'/group='.urldecode($group).'');
	}



	public function updateStatistic(){//update the statistics
		$query = $this->input->post('q');
		$class = $this->input->post('classFilter');
		$group = $this->input->post('groupFilter');
		$subject = $this->input->post('subjectFilter');
		$type = $this->input->post('typeFilter');
		$licence = $this->input->post('licenceFilter');
		$temporal = $this->input->post('temporal');
		$this->load->model('Registryobjects','ro');
		$this->ro->updateStatistic($query, $class, $group, $subject, $type, $temporal,$licence);
	}

	public function service_front(){//front    end for orca search service
		$this->load->view('service_front');
	}

	public function service(){//orca search service
		$this->load->model('solr');
		$query = $this->input->post('query');
		$class = $this->input->post('class');
		$group = $this->input->post('group');
		$subject = $this->input->post('subject');
		$licence = $this->input->post('licence');
		$page = $this->input->post('page');
		$status = $this->input->post('status');
		$source_key = $this->input->post('source_key');

		$extended_query = '';
		if($source_key!='undefined'){
			if($source_key!='All') $extended_query = '+data_source_key:("'.$source_key.'")';
		}
		if($status=='undefined'){
			$status='All';
		}

		$data['json']=$this->solr->search($query,$extended_query,'json',$page,$class, $group, 'All', $subject, $status);
		$this->load->view('search/service', $data);
	}

	public function seeAlso($type="count", $seeAlsoType='subjects'){//for rda see also
        $this->load->model('solr');
        $query = $this->input->post('q');
        $class = $this->input->post('classFilter');
        $group = $this->input->post('groupFilter');
        $subject = $this->input->post('subjectFilter');
        $licence = $this->input->post('licenceFilter');
        $page = $this->input->post('page');
        $extended = $this->input->post('extended');
        $excluded_key = $this->input->post('excluded_key');

        $extended_query = $extended.'-key:("'.escapeSolrValue($excluded_key).'")';
        //$extended_query='';
        //$extended_query .=constructFilterQuery('subject_value', $subject).'^100';

        $data['json']=$this->solr->search($query,$extended_query,'json',$page,$class, $group, 'All', $subject, $licence, 'PUBLISHED');

		$data['numfound']=0;
		if(isset($data['json']->{'response'}->{'numFound'}))$data['numfound'] = $data['json']->{'response'}->{'numFound'};
        $data['seeAlsoType'] = $seeAlsoType;

        if($type=='count'){
        	$this->load->view('search/seeAlso', $data);
        }elseif($type=='content'){
        	$this->load->view('search/seeAlsoInfoBox', $data);
        }
	}
	public function seeAlsoDataCite($type="count", $seeAlsoTitle='title'){//for rda see also

        $this->load->model('datacitesolr');
        $query = $this->input->post('q');
        $page = $this->input->post('page');
        $data['json']=$this->datacitesolr->search($query, 'json', $page);

		$data['numfound']=0;
		if(isset($data['json']->{'response'}->{'numFound'}))$data['numfound'] = $data['json']->{'response'}->{'numFound'};
      	//$data['seeAlsoType'] = $seeAlsoTitle;
      	//echo "a hit <br />";

        if($type=='count'){
        	$this->load->view('search/seeAlsoDataCite', $data);
        }elseif($type=='content'){
        	$this->load->view('search/seeAlsoDataCiteInfoBox', $data);
        }
	}


	public function seeAlsoOLD(){
		echo 'testing';
	}

       function getSeeAlsoParty($key){
               $relation_types = array('custodian', 'isManagedBy', 'isManagerOf');
               $relatedObjectsKeys = $this->getRelatedObjects($key, $relation_types);
               foreach($relatedObjectsKeys->{'response'}->{'docs'} as $index=>$r){
                       $relation_types2 = array('custodian', 'isManagedBy');
                       echo '<pre>';
                       print_r($relation =$this->getRelatedObjects($r->{'relatedObject_key'}[$index],$relation_types2));
                       echo '</pre>';
               }
       }


	function getRelationship($key,$relatedKey,$class)
	{
		$related[] = $relatedKey;
		$class= strtolower($class);
		if($class=='party')
		{
		$typeArray = array(
		"Associated with" => "Associated with",
		"Has member" => "Member of",
		"Has part" => "Part of",
		"Collector of" => "Collected by",
		"Funded by" => "Funds",
		"Funds" => "Funded by",
		"Managed by" => "Manages",
		"Manages" => "Managed by",
		"Member of" => "Has member",
		"Owned by" => "Owner of",
		"Owner of" => "Owned by",
		"Participant in" => "Part of",
		"Part of" => "Participant in",
		"Has collector"	 => "Aggregated by",
		"Aggregated by" => "Has collector",
		"Enriched by" => "Enriches",
		"Enriches" => "Enriched by"
		);
		}
		elseif($class=='collection')
		{
		$typeArray = array(
			"Describes" => "Described by",
			"Associated with" => "Associated with",
			"Aggregated by" => "Collector of",
			"Part of" => "Contains",
			"Described by" => "Describes",
			"Located in" => "Location for",
			"Location for" => "Located in",
			"Managed by" => "Manages",
			"Manages" => "Managed by",
			"Output of" => "Outputs",
			"isOutputOf" => "Outputs",
			"Owned by" => "Owns",
			"Contains" => "Part of",
			"Supports" => "Supported by",
			"Enriched by" => "Enriches",
			"Available through" => "Makes available",
			"Makes available" => "Available through",
			"Has collector"	 => "Collector of",
			"Derived from" => "Derived collection",
			"Produced by" => "Produces",
			"Operated on by" => "Operates on",
			"Adds value to "=> "Value added by",
			"Derived collection" => "Derived from"
		);
		}
		elseif($class=='service')
		{
		$typeArray = array(
			"Associated with" => "Associated with",
			"Part of" => "Includes",
			"Managed by" => "Manages",
			"Manages" => "Managed by",
			"Owned by" => "Owns",
			"Part of" => "Has part",
			"Supported by" => "Supports",
			"Available through" => "Makes available",
			"Makes available" => "Available through",
			"Includes" => "Part of",
			"Produces" => "Produced by",
			"Produced by" => "Produces",
			"Presents" => "Presented by",
			"Operates on" => "Operated on by",
			"Operated on by" => "Operates on",
			"Adds value to" => "Value added by",
			"Value added by" => "Adds value to",
		);
		}
		else
		{
		$typeArray = array(
			"Associated with" => "Associated with",
			"Produces" => "Output of",
			"Includes" => "Part of",
			"Undertaken by" => "Has participant",
			"Funded by" => "Funds",
			"Managed by" => "Manages",
			"Owned by" => "Owns",
			"Part of" => "Includes",
			"Has collector"	 => "Collector of",
		);
		}
		$this->load->model('solr');
		$object = $this->solr->getObjects($related,null,null,null);
		if(isset($object->{'response'}->{'docs'}[0])){
//print_r(($object->{'response'}->{'docs'}[0]));
		$keyList = $object->{'response'}->{'docs'}[0]->{'related_object_key'};
		$relationshipList = $object->{'response'}->{'docs'}[0]->{'related_object_relation'};
		$relationship = '';

		for($i=0;$i<count($keyList);$i++)
		{
			if($keyList[$i]==$key) $relationship = $relationshipList[$i];
		}

		if( array_key_exists($relationship, $typeArray) )
		{
			return  $typeArray[$relationship];
		}
		else
		{
			return   $relationship;
		}
		}
	}
	public function connections($type="count",$class=null,$types=null){//for rda connections
        $this->load->model('solr');
        $relatedKeys = array();
        $query = $this->input->post('q');
        $classArray = array('collection','service','activity');
        $typeArray = array('person', 'group');
        $page = $this->input->post('page');
       	if(!$page)$page=null;
       	$key = $this->input->post('key');
        $keyArray[] = $key;
        $data['json'] =$this->solr->getObjects($keyArray,$class,$types,null);
      //print_r($data['json']);
		$data['externalKeys']  ='';

        $reverseLinks = $data['json']->{'response'}->{'docs'}[0]->{'reverse_links'};
        $dataSourceKey = $data['json']->{'response'}->{'docs'}[0]->{'data_source_key'};
		$data['theGroup'] = $data['json']->{'response'}->{'docs'}[0]->{'group'};

        $data['thisClass'] = $data['json']->{'response'}->{'docs'}[0]->{'class'};

		$data['externalKeys'] = '';
        if(!$types and !$class)
        {
        	foreach($classArray as $class)
        	{
        		$data[$class]['json'][0] =$this->solr->getRelated($key,$class,$types);
         		$relatedKeys = array();
         		$numFound = $data[$class]['json'][0]->{'response'}->{'numFound'};
         		if($numFound>0){
        			foreach($data[$class]['json'][0]->{'response'}->{'docs'} as $r)
        			{
        				$relatedNum = count($r->{'related_object_key'});

        				$relatedKeys = '';
        	        	$data[$class]['relatedKey'] = '';
        				for($i = 0; $i<$relatedNum;$i++)
        				{
        					if($r->{'related_object_class'}[$i]==$class)
        					{
        						$relatedKeys[] = $r->{'related_object_key'}[$i];
        						$data[$class]['relationship'][] = $r->{'related_object_relation'}[$i];
        						if(isset( $r->{'related_object_relation_description'}[$i])){
       								$data[$class]['relationship_description'][] = $r->{'related_object_relation_description'}[$i];
        						}else{
        							$data[$class]['relationship_description'][] = 'null';
        						}
         						$data[$class]['relatedKey'][] = $r->{'related_object_key'}[$i];

        					}
        				}
        			}
         		}
        		if($reverseLinks!="NONE"){
        			$data[$class]['json'][1] =$this->solr->getConnections($key,$class,$types,$relatedKeys,$reverseLinks,$dataSourceKey);
        		//	print_r($data[$class]['json'][1]);
        			$data[$class]["external"] =$this->solr->getConnections($key,$class,$types,$relatedKeys,'EXT',$dataSourceKey);

					if($data[$class]["external"]->{'response'}->{'numFound'}>0)
					{

        				foreach($data[$class]["extrnal"]->{'response'}->{'docs'} as $r)
        				{
        					$extrnalNum = count($r->{'key'});
        					for($i = 0; $i<$extrnalNum;$i++)
        					{
        						if($r->{'class'}==$class)
        						{
        							$data['externalKeys'][] = $r->{'key'};
        						}
        					}
        				}
					}

      				foreach($data[$class]['json'][1]->{'response'}->{'docs'} as $r)
        			{
        				//echo count($r->{'key'});
        				$connectedNum = count($r->{'key'});
        				for($i = 0; $i<$connectedNum;$i++)
        				{
        					//echo $r->{'class'}."==".$class."<br />";
        					if($r->{'class'}==$class)
        					{
        						$relatedKeys[] = $r->{'key'};
        						$data[$class]['relationship'][] = $this->getRelationship($key,$r->{'key'},$class);
        						//echo $this->getRelationship($key,$r->{'key'},$class)."<br />";
         						$data[$class]['relatedKey'][] = $r->{'key'};
        					}
        				}
        			}
        		}
  				$data[$class]['json'] = $this->solr->getObjects($relatedKeys,$class,$types,$page=null);
  			//	print_r($relatedKeys);
  			//	echo "<br />";

  			//	echo $class."<br />";
  			//	if($class=="collection") print_r($relatedKeys);
  				//print_r($data[$class]['json']);
   			//	echo "<br />Found:".$data[$class]['json']->{'response'}->{'numFound'}."<br />-------------------<br />";
  				$data[$class]['numfound'] = $data[$class]['json']->{'response'}->{'numFound'};
        	}
            foreach($typeArray as $types)
        	{
        		$relatedKeys = '';
        		$data[$types]['json'][0]=$this->solr->getRelated($key,'party',$types);
        		$relatedKeys = array();
        		foreach($data[$types]['json'][0]->{'response'}->{'docs'} as $r)
        		{
        			$relatedNum = count($r->{'related_object_key'});
        			$relatedKeys = '';
        			for($i = 0; $i<$relatedNum;$i++)
        			{
        				if($r->{'related_object_type'}[$i]==$types)
        				{
        					$relatedKeys[] = $r->{'related_object_key'}[$i];
        					$data[$types]['relationship'][] = $r->{'related_object_relation'}[$i];
        					if(isset($r->{'related_object_relation_description'}[$i])){
       							$data[$types]['relationship_description'][] = $r->{'related_object_relation_description'}[$i];
        					}else{
       							$data[$types]['relationship_description'][] = 'null';
        					}
         					$data[$types]['relatedKey'][] = $r->{'related_object_key'}[$i];

        				}
        			}
        		}
        		if($reverseLinks!="NONE"){
            		$data[$types]['json'][1] =$this->solr->getConnections($key,'party',$types,$relatedKeys,$reverseLinks,$dataSourceKey);
           			//echo $types."------------------<br />";
        			//print_r($data[$types]['json'][1]) ;
           			//echo "------------------<br />";
        			$data[$types]["extrnal"] =$this->solr->getConnections($key,'party',$types,$relatedKeys,'EXT',$dataSourceKey);
        			//echo $types."+++++++++++++<br />";
        			//print_r($data[$types]["extrnal"]) ;
           			//echo "+++++++++++++<br />";
       				if($data[$types]["extrnal"]->{'response'}->{'numFound'}>0)
					{

        				foreach($data[$types]["extrnal"]->{'response'}->{'docs'} as $r)
        				{
        					$extrnalNum = count($r->{'key'});
        					for($i = 0; $i<$extrnalNum;$i++)
        					{
        						if($r->{'type'}==$types)
        						{
        							$data['externalKeys'][] = $r->{'key'};
        						}
        					}
        				}
					}

        			foreach($data[$types]['json'][1]->{'response'}->{'docs'} as $r)
        			{
        				$connectedNum = count($r->{'key'});
        				//echo $connectedNum."<br />";
        				for($i = 0; $i<$connectedNum;$i++)
        				{
         					if($r->{'type'}==$types)
        					{
        						$relatedKeys[] = $r->{'key'};
        						$data[$types]['relationship'][] = $this->getRelationship($r->{'key'},$key,$class);
         						$data[$types]['relatedKey'][] = $r->{'key'};
        					}
        				}
        			}
        		}

       			$data[$types]['json'] = $this->solr->getObjects($relatedKeys,'party',$types,$page=null);
       			$data[$types]['numfound'] = $data[$types]['json']->{'response'}->{'numFound'};
        	}
        }else{
        	$relatedKeys = '';
        	$relatedDescriptions = '';
			if($types='undefined')$types = null;
         	$data['json'] =$this->solr->getRelated($key,$class,$types);
        	$relatedKeys = array();
        	foreach($data['json']->{'response'}->{'docs'} as $r)
        	{
        		$relatedNum = count($r->{'related_object_key'});
        		$relatedKeys = '';
        		for($i = 0; $i<$relatedNum;$i++)
        		{
        			if($r->{'related_object_class'}[$i]==$class)
        			{
        				$relatedKeys[] = $r->{'related_object_key'}[$i];
        				//print_r($r);
        				$relatedDescriptions[] = $r->{'related_object_relation'}[$i];
         			}
        		}
        	}
        	if($reverseLinks!="NONE")
        	{
           		$data['json'] =$this->solr->getConnections($key,$class,$types,$relatedKeys,$reverseLinks,$dataSourceKey);
         		$data["extrnal"] =$this->solr->getConnections($key,$class,$types,$relatedKeys,'EXT',$dataSourceKey);
				if($data["extrnal"]->{'response'}->{'numFound'}>0)
				{
        			foreach($data["extrnal"]->{'response'}->{'docs'} as $r)
        			{
        				$extrnalNum = count($r->{'key'});
        				for($i = 0; $i<$extrnalNum;$i++)
        				{
        					if($r->{'class'}==$class)
        					{
        						$data['externalKeys'][] = $r->{'key'};
        					}
        				}
        			}
				}
      			foreach($data['json']->{'response'}->{'docs'} as $r)
        		{
        			$connectedNum = count($r->{'key'});
        			for($i = 0; $i<$connectedNum;$i++)
        			{
        				if($r->{'class'}==$class)
        				{
        					$relatedKeys[] = $r->{'key'};
         				}
        			}
        		}
        	}

       		$data['json'] =$this->solr->getObjects($relatedKeys,$class,$types,$page);
       		$data['numfound'] = $data['json']->{'response'}->{'numFound'};

        }
        if($type=='count'){
        	$this->load->view('search/connections', $data);
        }elseif($type=='content'){
        	$this->load->view('search/connectionsInfoBox', $data);
        }
	}



    public function getRelatedObjects($key, $relation_types){
    	$this->load->model('solr');
    	$relatedObject_keys = $this->solr->getRelatedKeys($key,$relation_types);
    	return $relatedObject_keys;
   	}

	public function filter(){//AJAX CALL, VERY IMPORTANT, this thing is called on every search
	    $this->benchmark->mark('search_start');
		$q = $this->input->post('q');
		$q = trim($q); //remove spaces

		if($q=='') $q="*:*";
		$qrss = $q;
		//echo $q;
		//Filtering if there is any
		$classFilter = $this->input->post('classFilter');
		$typeFilter = urldecode($this->input->post('typeFilter'));
		$groupFilter = urldecode($this->input->post('groupFilter'));
		$subjectFilter = urldecode($this->input->post('subjectFilter'));
		$licenceFilter = urldecode($this->input->post('licenceFilter'));
		$page = $this->input->post('page');
		$spatial_included_ids = $this->input->post('spatial_included_ids');
		$temporal = $this->input->post('temporal');
		$sort = $this->input->post('sort');
		$query = $q;
		$ds = '';
		$ds = $this->input->post('dataSource');
		$extended_query = '';

		if($ds!='')	$extended_query .= '+data_source_key:("'.$ds.'")';
		//echo '+spatial:('.$spatial_included_ids.')';
		if($spatial_included_ids!='') {
			$extended_query .= $spatial_included_ids;
		}
		if($temporal!='All'){
			$temporal_array = explode('-', $temporal);
			$extended_query ='+date_from:['.$temporal_array[0].' TO *]+date_to:[* TO '.$temporal_array[1].']';
		}

		//echo $query;

		/*Search Part*/
		$this->load->model('solr');
		$data['json']=$this->solr->search($query, $extended_query, 'json', $page, $classFilter, $groupFilter, $typeFilter, $subjectFilter,$licenceFilter,'PUBLISHED', $sort);

		//print_r($data['json']);

		/**getting the tabbing right**/
		$query_tab = $q;
		$data['json_tab']=$this->solr->search($query, $extended_query, 'json', $page, 'All', $groupFilter, $typeFilter, $subjectFilter,$licenceFilter,'PUBLISHED', $sort);//just for the tabbing mechanism (getting the numbers right)
		/*just that! and json_tab is used in tab view*/

		/*Passing Variables down to the view*/
		if($q=='*:*') $q = 'All Records';
		$data['query']=$q;
		$data['classFilter']=$classFilter;
		$data['typeFilter']=$typeFilter;
		$data['groupFilter']=$groupFilter;
		$data['subjectFilter']=$subjectFilter;
		$data['licenceFilter']=$licenceFilter;
		$data['page']=$page;
		$data['spatial_included_ids']=$spatial_included_ids;
		$data['temporal']=$temporal;
		$dataSourceString ='';
		if($ds!='') $dataSourceString = "&dataSource=".$ds;
		$data['queryStr'] = '?q='.$qrss.$dataSourceString.'&classFilter='.$classFilter.'&typeFilter='.$typeFilter.'&groupFilter='.$groupFilter.'&subjectFilter='.$subjectFilter.'&licenceFilter='.$licenceFilter;

		$this->benchmark->mark('search_end');
		//echo $this->benchmark->elapsed_time('search_start', 'search_end');

		$this->load->view('search/search_result', $data);//load the view
	}

	public function subjectfacet($view){
		$data['view']=$view;
		$params = $this->input->post('params');
		$this->load->model('solr');
		$params = $this->solr->constructQuery($params);
		//echo $params;
		if($view=='anzsrcfor'){
			$this->load->model('vocabularies', 'vmodel');
			$data['bigTree']=$this->vmodel->getBigTree($this->config->item('vocab_resolver_service'), array(), $params, 'searchfacet');
			
		}else{
			$this->load->model('solr');
			$data['bigTree']=$this->solr->getSubjectFacet($view, $params);
		}
		$this->load->view('search/subjectfacet', $data);
	}

	public function subjectfacettree($view){
		$data['view'] = $view;
		$params = $this->input->post('params');
		$startsWith = $this->input->post('startsWith');
		$this->load->model('solr');
		$data['tree'] = $this->solr->getSubjectFacetTree($params, $view, $startsWith);
		echo $data['tree'];
	}

	public function toplevelfacet(){
		$params = $this->input->post('params');
		$this->load->model('solr');
		$params = $this->solr->constructQuery($params);
		$this->load->model('vocabularies', 'vmodel');
		$toplevelfacets=$this->vmodel->getTopConceptsFacet($this->config->item('vocab_resolver_service'), $params);

		uasort($toplevelfacets['topConcepts'], 'cmpTopLevelFacet');//cmp function is in rda_display_helper
		echo '<ul class="more">';
		foreach($toplevelfacets['topConcepts'] as $f){
			if($f['collectionNum']>0) echo '<li class="limit">'.'<a href="javascript:;" id="'.$f['uri'].'" class="subjectFilter" title="'.$f['prefLabel'].' ('.$f['collectionNum'].' results)">'.$f['prefLabel'].' ('.$f['collectionNum'].')</a>'.'</li>';
		}
		echo '</ul>';
	}

	

	public function spatial() {//AJAX CALL
		$north = $this->input->post('north');
		$south = $this->input->post('south');
		$east = $this->input->post('east');
		$west = $this->input->post('west');

		//echo $north;
		$query = 'select distinct rs.registry_object_key from dba.tbl_registry_objects rs, dba.tbl_spatial_extents se
	where rs.registry_object_key = se.registry_object_key
	and se.bound_box && box ((point('.$north.','.$west.')),(point('.$south.','.$east.')))';
		$this->load->database();
		$data['registryObjects'] = $this->db->query($query);
		$this->load->view('search/listIDs', $data);
		//var_dump($keys);
	}

}

