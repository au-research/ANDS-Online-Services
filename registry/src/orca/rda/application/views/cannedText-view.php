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

	$class = $content->{'facet_counts'}->{'facet_fields'}->{'class'};
	$types = $content->{'facet_counts'}->{'facet_fields'}->{'type'};	
	$subjects = $content->{'facet_counts'}->{'facet_fields'}->{'subject_value_resolved'};	
	
	$collectionCount = 0;
	for($i=0;$i<count($class);$i++)
	{
		if($class[$i]=="collection") {$collectionCount = $class[$i+1];}
		$i++;
	}
		if($collectionCount==0)
	{
		$collectionCount = 'no';
	}	
	if($collectionCount==1)
	{
		$collectionWord = 'collection';
		$collectionCount = 'one';
	}else{
		$collectionWord = 'collections';
	}

	
	
	$groupCount = 0;
	for($i=0;$i<count($types);$i++)
	{
		if($types[$i]=="group") 
		{
			$groupCount = $types[$i+1];
		}
		$i++;
	}	
		
	if($groupCount==1 || $groupCount==0)
	{
		$groupStr = '';
	}else{
		$groupStr = $groupCount. '	 research groups have been actively involved in collecting data and creating metadata records for the data.';
	}


	$subjectNum = count($subjects)/2;
	
	if(count($subjects)/2 < 4) 
	{
		$subMax = count($subjects)/2;
	}else{
	 	$subMax = 3;
	}
	
	$counter = 0;
	for($i=0;$i<($subMax*2);$i++)
	{
		$subject[$counter]=$subjects[$i];
		$i++;
		$counter++;
	}	
	$subjectStr='';
	$subjectWord = 'areas';
	if($subjectNum<1)
	{
		$subjectStr = ".";
	}
	elseif($subjectNum==1)
	{
		$subjectWord = 'area';		
		$subjectStr = ', which covers the subject '.$subjectWord.' <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[0].'">'.$subject[0].'</a>.';
	}
	elseif($subjectNum==2)
	{
		$subjectStr = ', which covers 	'.$subjectNum.' subject '.$subjectWord.' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[0].'">'.$subject[0].'</a> and <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[1].'">'.$subject[1].'</a>.';
	}	
	else 
	{
		$subjectStr = ', which covers 	'.$subjectNum.' subject '.$subjectWord.' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[0].'">'.$subject[0].'</a>, <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[1].'">'.$subject[1].'</a> and <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[2].'">'.$subject[2].'</a>.';	
	}
	
	echo '<p>To date, ' .urldecode($group). ' has ';
	if($collectionCount!='no'){
		echo '<a id="hp-count-collection" href="'.base_url().'search#!/tab=collection/group='.urlencode($group).'">' .$collectionCount .' '.$collectionWord.'</a> in RDA';
	}else{
		echo  ' '.$collectionCount .' '.$collectionWord.' in RDA';
	}
	echo $subjectStr.' ' .$groupStr. ' All the Collections, Parties, Activities and Services associated with ' .urldecode($group). '  can be accessed from the Registry Contents box on the right hand side of this page.</p>';
?>