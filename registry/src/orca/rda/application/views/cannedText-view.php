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
	//print("<pre>");
	//print_r($content);
	//print("</pre>");	
	
	$class = $content->{'facet_counts'}->{'facet_fields'}->{'class'};
	$types = $content->{'facet_counts'}->{'facet_fields'}->{'type'};	
	$subjects = $content->{'facet_counts'}->{'facet_fields'}->{'subject_value_resolved'};	

	for($i=0;$i<count($class);$i++)
	{
		if($class[$i]=="collection") $collectionCount = $class[$i+1];

		$i++;
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
	if($subjectNum<1)
	{
		$subjectStr = ".";
	}
	elseif($subjectNum==1)
	{
		$subjectStr = ' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[0].'">'.$subject[0].'</a>.';
	}
	elseif($subjectNum==2)
	{
		$subjectStr = ' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[0].'">'.$subject[0].'</a> and <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[1].'">'.$subject[1].'</a>.';
	}	
	else 
	{
		$subjectStr = ' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[0].'">'.$subject[0].'</a>, <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[1].'">'.$subject[1].'</a> and <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subject[2].'">'.$subject[2].'</a>.';	
	}
	echo '<p>Up to date, ' .urldecode($group). ' has <a id="hp-count-collection" href="'.base_url().'search#!/tab=collection/group='.urlencode($group).'">' .$collectionCount .'  collections</a> in RDA, which covers 
	'.$subjectNum.' subject areas'.$subjectStr.' ' .$groupCount. ' 
	 research groups have been actively involved in collecting data and creating metadata records for the data.</p>';
?>