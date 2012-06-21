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
<?php //echo '<pre>';
	//print_r($json);
	//echo '</pre>';?>

<div class="accordion">
<?php
foreach($json->{'response'}->{'docs'} as $r)
{
	echo '<h3><a href="#">'.$r->{'list_title'}.'</a></h3>';
	$something = '';
	echo '<div>';
	if(isset($r->{'description_type'})){
		foreach($r->{'description_type'} as $index=>$description_type){
			if($something==''){
				if(($description_type!='rights') && ($description_type!='logo')){
					$something = $r->{'description_value'}[$index];
				}
			}
		}
		echo $something;
		echo '<hr/>';
	}



	if(isset($r->{'subject_type'})){
		echo '<ul class="subjects">';
		foreach($r->{'subject_type'} as $index=>$subject_type){
			echo '<li><a href="javascript:void(0);" class="subjectFilter" id="'.$r->{'subject_value_resolved'}[$index].'">'.$r->{'subject_value_resolved'}[$index].'</a></li>';
		}
		echo '</ul>';
		echo '<hr/>';
	}
	if ($r->{'url_slug'})
	{
		echo '<a href="'.base_url().$r->{'url_slug'}.'" class="button">View Record</a>';
	}
	else
	{
		echo '<a href="'.base_url().'view?key='.urlencode($r->{'key'}).'" class="button">View Record</a>';
	}
	//echo anchor('view/?key='.$r->{'key'},'View Record', array('class'=>'button'));
	echo '</div>';

}
echo '</div>';
echo '<div class="hide">';
	//$numFound = $json->{'response'}->{'numFound'};
	$numFound = $json->{'response'}->{'numFound'};
	//print_r($json->{'responseHeader'}->{'params'});
	$row = $json->{'responseHeader'}->{'params'}->{'rows'};
	$start = $json->{'responseHeader'}->{'params'}->{'start'};
	$end = $start + $row;

	$totalPage = ceil($numFound / $row);
	$currentPage = ceil($start / $row)+1;
	echo '<div id="seeAlsoTotalPage">'.$totalPage.'</div>';
	echo '<div id="seeAlsoCurrentPage">'.$currentPage.'</div>';
?>
</div>
