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
			redirect(base_url().'search/#!/q='.$q);
		}else{	
			$this->load->library('user_agent');
			$data['user_agent']=$this->agent->browser();
			$this->load->view('layout', $data);
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
		$temporal = $this->input->post('temporal');
		$this->load->model('Registryobjects','ro');
		$this->ro->updateStatistic($query, $class, $group, $subject, $type, $temporal);
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
        $page = $this->input->post('page');
        $extended = $this->input->post('extended');
        $excluded_key = $this->input->post('excluded_key');

        $extended_query = $extended.'-key:("'.escapeSolrValue($excluded_key).'")';
        //$extended_query='';
        //$extended_query .=constructFilterQuery('subject_value', $subject).'^100';

        $data['json']=$this->solr->search($query,$extended_query,'json',$page,$class, $group, 'All', $subject, 'PUBLISHED');
	
		$data['numfound']=0;		
		if(isset($data['json']->{'response'}->{'numFound'}))$data['numfound'] = $data['json']->{'response'}->{'numFound'};
        $data['seeAlsoType'] = $seeAlsoType;

        if($type=='count'){
        	$this->load->view('search/seeAlso', $data);
        }elseif($type=='content'){
        	$this->load->view('search/seeAlsoInfoBox', $data);
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
                       print_r($relation =
$this->getRelatedObjects($r->{'relatedObject_key'}[$index],
$relation_types2));
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
			"Supports" => "Supports",
			"Enriched by" => "Enriches",
			"Available through" => "Makes available",
			"Makes available" => "Available through",	
			"Has collector"	 => "Collector of",	
		);		
		}
		elseif($class=='service')
		{
		$typeArray = array(
			"Associated with" => "Associated with",
			"Has part" => "Includes",
			"Managed by" => "Manages",
			"Manages" => "Managed by",			
			"Owned by" => "Owns",
			"Part of" => "Has part",
			"Supported by" => "Supports",
			"Available through" => "Makes available",
			"Makes available" => "Available through",			
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
		$keyList = $object->{'response'}->{'docs'}[0]->{'relatedObject_key'};
		$relationshipList = $object->{'response'}->{'docs'}[0]->{'relatedObject_relation'};
		
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
        $query = $this->input->post('q');
        $classArray = array('collection','service','activity');
        $typeArray = array('person', 'group');
        $page = $this->input->post('page');	
       	if(!$page)$page=null;
       	$key = $this->input->post('key'); 
        $keyArray[] = $key;   
        $data['json'] =$this->solr->getObjects($keyArray,$class,$types,null);
		$data['externalKeys']  ='';
		
		//print_r($data['json']);
		
        $reverseLinks = $data['json']->{'response'}->{'docs'}[0]->{'reverseLinks'};  
        $dataSourceKey = $data['json']->{'response'}->{'docs'}[0]->{'data_source_key'};
		
		//print_r($data['json']);
		
        $data['thisClass'] = $data['json']->{'response'}->{'docs'}[0]->{'class'};
		$data['externalKeys'] = '';
        if(!$types and !$class)
        { 
        	foreach($classArray as $class)
        	{          		
        		$data[$class]['json'][0] =$this->solr->getRelated($key,$class,$types); 
         		$relatedKeys = array();
        		foreach($data[$class]['json'][0]->{'response'}->{'docs'} as $r)
        		{
        			$relatedNum = count($r->{'relatedObject_key'});
 
        			$relatedKeys = ''; 
        	        $data[$class]['relatedKey'] = '';      			
        			for($i = 0; $i<$relatedNum;$i++)
        			{
        				if($r->{'relatedObject_relatedObjectClass'}[$i]==$class)
        				{
        					$relatedKeys[] = $r->{'relatedObject_key'}[$i];
        					$data[$class]['relationship'][] = $r->{'relatedObject_relation'}[$i];
         					$data[$class]['relatedKey'][] = $r->{'relatedObject_key'}[$i];       					
        				}
        			}
        		} 
        		if($reverseLinks!="NONE"){
        			
        			$data[$class]['json'][1] =$this->solr->getConnections($key,$class,$types,$relatedKeys,$reverseLinks,$dataSourceKey);  
        			$data[$class]["extrnal"] =$this->solr->getConnections($key,$class,$types,$relatedKeys,'EXT',$dataSourceKey); 
        			 
					if($data[$class]["extrnal"]->{'response'}->{'numFound'}>0) 
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
        				$connectedNum = count($r->{'key'});
        				for($i = 0; $i<$connectedNum;$i++)
        				{
        					if($r->{'class'}==$class)
        					{
        						$relatedKeys[] = $r->{'key'};
        						$data[$class]['relationship'][] = $this->getRelationship($key,$r->{'key'},$class);
         						$data[$class]['relatedKey'][] = $r->{'key'};       					
        					}
        				}
        			}  
        		}          		   		
  				$data[$class]['json'] = $this->solr->getObjects($relatedKeys,$class,$types,$page=null);
  				$data[$class]['numfound'] = $data[$class]['json']->{'response'}->{'numFound'};
        	}
            foreach($typeArray as $types)
        	{
        		$relatedKeys = '';
        		$data[$types]['json'][0]=$this->solr->getRelated($key,'party',$types);   
        		$relatedKeys = array();
        		foreach($data[$types]['json'][0]->{'response'}->{'docs'} as $r)
        		{
        			$relatedNum = count($r->{'relatedObject_key'});
        			$relatedKeys = '';      			
        			for($i = 0; $i<$relatedNum;$i++)
        			{
        				if($r->{'relatedObject_relatedObjectType'}[$i]==$types)
        				{
        					$relatedKeys[] = $r->{'relatedObject_key'}[$i];
        					$data[$types]['relationship'][] = $r->{'relatedObject_relation'}[$i];        					
         					$data[$types]['relatedKey'][] = $r->{'relatedObject_key'}[$i];          				
        				}
        			}
        		}  	
        		if($reverseLinks!="NONE"){    		
            		$data[$types]['json'][1] =$this->solr->getConnections($key,'party',$types,$relatedKeys,$reverseLinks,$dataSourceKey); 
        			$data[$class]["extrnal"] =$this->solr->getConnections($key,'party',$types,$relatedKeys,'EXT',$dataSourceKey);  
					if($data[$class]["extrnal"]->{'response'}->{'numFound'}>0) 
					{

        				foreach($data[$class]["extrnal"]->{'response'}->{'docs'} as $r)
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
			if($types='undefined')$types = null;
         	$data['json'] =$this->solr->getRelated($key,$class,$types); 
        	$relatedKeys = array();
        	foreach($data['json']->{'response'}->{'docs'} as $r)
        	{
        		$relatedNum = count($r->{'relatedObject_key'});
        		$relatedKeys = '';      			
        		for($i = 0; $i<$relatedNum;$i++)
        		{
        			if($r->{'relatedObject_relatedObjectClass'}[$i]==$class)
        			{
        				$relatedKeys[] = $r->{'relatedObject_key'}[$i];
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
		//echo $q;
		//Filtering if there is any
		$classFilter = $this->input->post('classFilter');
		$typeFilter = urldecode($this->input->post('typeFilter'));
		$groupFilter = urldecode($this->input->post('groupFilter'));
		$subjectFilter = urldecode($this->input->post('subjectFilter'));
		$page = $this->input->post('page');
		$spatial_included_ids = $this->input->post('spatial_included_ids');
		$temporal = $this->input->post('temporal');
		$sort = $this->input->post('sort');

		$query = $q;
		$extended_query = '';
		
		//echo '+spatial:('.$spatial_included_ids.')';
		
		if($spatial_included_ids!='') {
			$extended_query .= $spatial_included_ids;
		}
		if($temporal!='All'){
			$temporal_array = explode('-', $temporal);
			$extended_query .='+dateFrom:['.$temporal_array[0].' TO *]+dateTo:[* TO '.$temporal_array[1].']';
		}
		
		
		//echo $query;
		
		/*Search Part*/
		$this->load->model('solr');
		$data['json']=$this->solr->search($query, $extended_query, 'json', $page, $classFilter, $groupFilter, $typeFilter, $subjectFilter,'PUBLISHED', $sort);
		
		//print_r($data['json']);
		
		/**getting the tabbing right**/
		$query_tab = $q;
		$data['json_tab']=$this->solr->search($query, $extended_query, 'json', $page, 'All', $groupFilter, $typeFilter, $subjectFilter,'PUBLISHED', $sort);//just for the tabbing mechanism (getting the numbers right)
		/*just that! and json_tab is used in tab view*/
		
		/**getting the facet right**/
		//$query_tab = $q;
		//$data['json_facet']=$this->solr->search($query, $page, $classFilter);//just for the tabbing mechanism (getting the numbers right)
		
		/*Passing Variables down to the view*/
		if($q=='*:*') $q = 'All Records';
		$data['query']=$q;
		$data['classFilter']=$classFilter;
		$data['typeFilter']=$typeFilter;
		$data['groupFilter']=$groupFilter;
		$data['subjectFilter']=$subjectFilter;
		$data['page']=$page;
		$data['spatial_included_ids']=$spatial_included_ids;
		$data['temporal']=$temporal;
		
		
		
		$this->benchmark->mark('search_end');
		//echo $this->benchmark->elapsed_time('search_start', 'search_end');
		
		$this->load->view('search/search_result', $data);//load the view
	}

	public function spatial() {//AJAX CALL
		$north = $this->input->post('north');
		$south = $this->input->post('south');
		$east = $this->input->post('east');
		$west = $this->input->post('west');
		
		$this->load->model('registryobjects');
		$data['registryObjects']=$this->registryobjects->spatial($north, $east, $south, $west);
		$this->load->view('search/listIDs', $data);
	}

}