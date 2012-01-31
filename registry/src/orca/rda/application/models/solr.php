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
	class Solr extends CI_Model {

  
    function __construct()
    {
        parent::__construct();
    }
    
	function search($query, $extended_query, $write_type='json', $page, $classFilter ='All', $groupFilter ='All', $typeFilter ='All', $subjectFilter = 'All', $status='All', $sort='score desc')
    {
        $q=$query;
		$q=rawurlencode($q);$q=str_replace("%5C%22", "\"", $q);//silly encoding
		$start = 0;$row = 10;
		if($page!=1) $start = ($page - 1) * $row;
		
		$solr_url = $this->config->item('solr_url');

    	$filter_query = '';
    	if($classFilter!='All') $filter_query .= constructFilterQuery('class', $classFilter);
    	if($typeFilter!='All') $filter_query .= constructFilterQuery('type', $typeFilter);
    	if($groupFilter!='All') $filter_query .= constructFilterQuery('group', $groupFilter);
    	if($subjectFilter!='All') $filter_query .= constructFilterQuery('subject_value_resolved', $subjectFilter);
    	if($status!='All') $filter_query .= constructFilterQuery('status', $status);
    	
    	//echo $status;
    	
		//echo $extended_query;
    	
		//echo urldecode($q).'<br/>';
		
		
		$q = urldecode($q);
		if($q!='*:*')$q = escapeSolrValue($q);
		//$r = '(fulltext:('.$q.') OR key:('.$q.')^50 OR displayTitle:('.$q.')^50 OR listTitle:('.$q.')^50 OR description_value:('.$q.')^5 OR subject_value:('.$q.')^10 OR name_part:('.$q.')^30)';
		//$q .= $r . ' OR (fulltext:('.$q.') -data_source_key:("AU_RESEARCH_GRANTS"))^3000 OR (fulltext:('.$q.') -data_source_key:("nhmrc.gov.au"))^3000';
		$q = '(fulltext:('.$q.') OR key:('.$q.')^50 OR display_title:('.$q.')^50 OR list_title:('.$q.')^50 OR description_value:('.$q.')^5 OR subject_value_resolved:('.$q.')^10 OR name_part:('.$q.')^30 OR ((fulltext:('.$q.') -data_source_key:("AU_RESEARCH_GRANTS"))^3000 OR (fulltext:('.$q.') -data_source_key:("nhmrc.gov.au"))^3000))';

		//$q .= $r;
		//OR (fulltext:('.$q.') -data_source_key:("AU_RESEARCH_GRANTS"))^3000 OR (fulltext:('.$q.') -data_source_key:("nhmrc.gov.au"))^3000
		
		
		if($sort!='score desc') $filter_query.='&sort='.$sort;
		$q.=$filter_query;
		
		$q.=($extended_query);
		//echo $filter_query;
		//$filter_query .=$extended_query;//for spatial and temporal
		//$q .=$extended_query;//extended for spatial
		$fields = array(
			'q'=>$q,'version'=>'2.2','start'=>$start,'rows'=>$row, 'wt'=>$write_type,
			'fl'=>'*,score'
		);
		//if($filter_query!='') $fields['fq']=urlencode($filter_query);
		//print_r($fields);
		
		$facet = '&facet=true&facet.field=type&facet.field=class&facet.field=group&facet.field=subject_value_resolved&f.subject_value_resolved.facet.mincount=1&facet.sort=count';
		
		/*prep*/
		$fields_string='';
    	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
    	rtrim($fields_string,'&');
    	$fields_string .= $facet;//add the facet bits
    	
    	//$fields_string = urldecode($fields_string);
    	
    	//echo $fields_string.'<hr/>';
		
		
    	$ch = curl_init();
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
		curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl
    	
    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl
		
		if($write_type=='json'){
			$json = json_decode($content);
			return $json;
		}elseif($write_type=='xml'){
			return $content;
		}
    }
   
 
    public function getRelated($key, $class, $type){	
    	$fields = array(
			'q'=>'key:"'.escapeSolrValue($key).'"','version'=>'2.2','start'=>'0','indent'=>'on', 'wt'=>'json'
		);
		$filter_query = '+relatedObject_relatedObjectClass:("'.$class.'")';
		if($type) $filter_query = '+relatedObject_relatedObjectType:("'.$type.'")'; 
		$fields['fq']=$filter_query;
		$json = $this->fireSearch($fields, '');
		return $json;
    }   
    
    
    public function getRelatedKeys($key, $relationType=array()){
    	$fields = array(
			'q'=>'*:*','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>'json',
			'fl'=>'relatedObject_key'
		);
		$filter_query = '+key:("'.$key.'")+relatedObject_relation:(';
		$first = true;
		foreach($relationType as $re){
			if($first){
				$filter_query .= $re;
				$first = false;
			}else $filter_query .= ' OR '.$re;
		}
		$filter_query .=')';
		$fields['fq']=$filter_query;
		//echo $filter_query;
		$json = $this->fireSearch($fields, '');
		return $json;
    }
    
	public function getConnections($key, $class, $type, $exclude,$reverseLinks,$dataSourceKey){	
		//echo $key."<br />";
		$excludeKeys = '';
		if(count($exclude)>1)
		{		
		
			$excludes = array_keys($exclude);
			$excludeKeys = '(';

			for($i=0;$i<count($exclude);$i++)
			{
				$excludeKeys .= '"'.$exclude[$i].'" OR ';
			}
			$excludeKeys = trim($excludeKeys,'OR ');
			$excludeKeys .= ")";
		}else{
			$excludeKeys = '(';
			$excludeKeys .= '"'.$exclude.'"';	
			$excludeKeys .= ")";		
		}
    	$fields = array(
			'q'=>'relatedObject_key:"'.$key.'"','version'=>'2.2','rows'=>'200000','start'=>'0','indent'=>'on', 'wt'=>'json','fl'=>'key,class,type,data_source_key'
		);
		$filter_query = '+class:("'.$class.'")';
		if($type) $filter_query = '+type:("'.$type.'")'; 
		if($reverseLinks=="INT")$filter_query .= '+data_source_key:("'.$dataSourceKey.'")';
		if($reverseLinks=="EXT")$filter_query .= '-data_source_key:("'.$dataSourceKey.'")';		
		if($excludeKeys)$filter_query .= '-key: '.escapeSolrValue($excludeKeys);  

		$fields['fq']=$filter_query;
		$json = $this->fireSearch($fields, '');

		return $json;
    } 
    
	public function getObjects($keys, $class, $type, $page){	
		//echo $keys[0];
		if($page!=null){
			$start = 0;
			$rows = 10;
			if($page!=1) $start = (($page - 1) * $rows) + 0;			
		}else{
			$start = 0;
			$rows = 2000;			
		}
	
		$getkeys = '(';
		if(count($keys)>0)
 		{
			for($i=0;$i<count($keys);$i++)
			{
				$getkeys .= '"'.escapeSolrValue($keys[$i]).'" OR ';
			}
		}else{
			
			$getkeys .= '"'.$keys.'"';
		}
		$getkeys = trim($getkeys,'OR ');
		$getkeys .= ")";

    	$fields = array(
			'q'=>'key:'.$getkeys.' +status:(PUBLISHED)','version'=>'2.2','rows'=>$rows,'start'=>$start,'indent'=>'on', 'wt'=>'json'
		);

		$json = $this->fireSearch($fields, '');
		return $json;
    }  
       
	public function getByKey($key){
		return $this->getRegistryObjectSOLR($key, '*', 'json');
	}
    
    
    public function getNCRISPartners(){
    	$fields = array(
			'q'=>'group:NCRIS','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>'json',
			'fl'=>'description_value, description_type, key, displayTitle, location'
		);
		$json = $this->fireSearch($fields, '');
		return $json;
    }
    
       
    function seeAlso($key, $type){
    	$result = null;
    	switch($type){
    		case "subject":$result = $this->seeAlsoSubject($key);break;
    	}
    	return $result;
    }
    
    private function seeAlsoSubject($key){
    	//get only the subjects of the registry object
    	$ro = $this->getRegistryObjectSOLR($key, 'subject_value subject_type', 'json');
    	//loop through the subjects and construct the filter query
    	return $ro;
    }
    
    
    /*
     * Takes a key and returns the registry Object searched through SOLR
     * key is the registryObject key
     * flag is what to be returned, * for all fields
     * wt is the write type, accepted xml and json
     */
    private function getRegistryObjectSOLR($key, $flag, $wt){
    	$fields = array(
			'q'=>'key:"'.urlencode($key).'"','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>$wt,
			'fl'=>$flag, 'q.alt'=>'*:*'
		);
		$result = $this->fireSearch($fields, '');//no facet
		return $result;
    }
    
    /*
     * Returns the statistics with all facets
     */
    function getStat($sort, $type=''){
    	$fields = array(
			'q'=>'*:*','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>'json',
			'fl'=>'key', 'q.alt'=>'*:*','fq'=>'status:PUBLISHED'
		);
		if($type=='collection') $fields['fq'].='+class:collection';
		$facet = 'facet=true&facet.field=type&facet.field=class&facet.field=group&facet.field=subject_value&facet.sort=index&facet.mincount=1';
		$json = $this->fireSearch($fields, $facet);
		return $json;
    }
    
	function getDictionary($sort){
    	$fields = array(
			'q'=>'*:*','version'=>'2.2','start'=>'0','rows'=>'100','indent'=>'on', 'wt'=>'json',
			'fl'=>'key', 'q.alt'=>'*:*'
		);
		$facet = '&facet=true&facet.field=description_value&facet.limit=10000';
		$json = $this->fireSearch($fields, $facet);
		return $json;
    }
    /*
     * Fire a search, given an array of fields and a string of facets
     */
	private function fireSearch($fields, $facet){
		/*prep*/
		$fields_string='';
		//foreach($fields as $key=>$value) { $fields_string .= $key.'='.str_replace("+","%2B",$value).'&'; }//build the string
		foreach($fields as $key=>$value) { 
		//echo $value."<br />";
			$fields_string .= $key.'='.$value.'&'; 				
		}//build the string
    	$fields_string .= $facet;//add the facet bits
    	rtrim($fields_string,'&');
	
	//echo $fields_string."....<br />";
	
    	$ch = curl_init();
    	$solr_url = $this->config->item('solr_url');
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
		curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl
    	
    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl
		
		
		
		$json = json_decode($content);
		return $json;
    }
}
?>
