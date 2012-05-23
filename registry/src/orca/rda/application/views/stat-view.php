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
	//echo $json;
	
	$object_class = $classes->{'facet_counts'}->{'facet_fields'}->{'class'};
    $classes = array();// array to stores the class
	for($i=0;$i<sizeof($object_class)-1 ;$i=$i+2){
	    $classes[$object_class[$i]] = $object_class[$i+1];
	}
	//print_r($object_group);
	$object_group = $groups->{'facet_counts'}->{'facet_fields'}->{'group'};
    	$groups = array();// array to stores the class
	for($i=0;$i<sizeof($object_group)-1 ;$i=$i+2){
	    $groups[$object_group[$i]] = $object_group[$i+1];
	}
	echo '<div class="hide">';
	echo '<h2>Registry Contents</h2>';
	//print_r($classes);
	foreach($classes as $index=>$c){
		echo 'Number of '. $index. ': <a id="hp-count-'.$index.'" href="search#!/tab='.$index.'">'.number_format($c).'</a> <br/>';
	}
	echo '<hr/>';
	echo '</div>';
	
	//echo 'sort='.$sort;
	//echo '<h2>Research Groups</h2>';
	//echo '<div><a href="javascript:void(0);" id="hp-atoz">A to Z</a> | <a href="javascript:void(0);" id="hp-count">Count</a></div>';
	echo '<div><ul id="hp-groups">';
	foreach($groups as $index=>$g){
		$theGroup = getInstitutionPage($index);
		if($theGroup){
			echo '<li><a href="view/group/?group='.rawurlencode($theGroup). '&groupName='.$index.'" class="hp-minibrowse-item">'.$index.' ('.number_format($g).')</a></li>';
		}elseif(number_format($g)>0){
			echo '<li><a href="search#!/group='.$index.'/tab=collection" class="hp-minibrowse-item">'.$index.' ('.number_format($g).')</a></li>';			
		}
	}
	echo '</ul></div>';
?>