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
<div class="accordion">
<?php
foreach($json->{'response'}->{'docs'} as $r)
{
	$autoLink = '';
	if($externalKeys)
		{
			for($j=0;$j<count($externalKeys);$j++)
			{
				if($r->{'key'}==$externalKeys[$j])
				$autoLink = '<span class="faded">(Automatic link)</span>';
			}
		}
	echo '<h3><a href="#">'.$r->{'list_title'}.' '.$autoLink.'</a></h3>';
	$something = '';
	$logostr = '';
	echo '<div>';
	if(isset($r->{'description_type'})){
		foreach($r->{'description_type'} as $index=>$description_type){
			if($description_type=='logo')
			{
				$logostr = '<div><img id="party_logo"  style="max-width:130px;max-height:63px;" src="'.$r->{'description_value'}[$index].'"/></div>';
			}
			if($something==''){

				if(($description_type!='rights') && ($description_type!='logo')){
					$something = $r->{'description_value'}[$index];
				}

			}
		}
		echo $logostr.$something;
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
		echo anchor($r->{'url_slug'},'View Record', array('class'=>'button'));
	}
	else
	{
		echo anchor('view/?key='.urlencode($r->{'key'}),'View Record', array('class'=>'button'));
	}
	echo '</div>';

}
echo '</div>';
echo '<div class="hide">';

	$numFound = $json->{'response'}->{'numFound'};

	$row = $json->{'responseHeader'}->{'params'}->{'rows'};

	$start = $json->{'responseHeader'}->{'params'}->{'start'};

	$end = $start + $row;

	$totalPage = ceil($numFound / $row);
	$currentPage = ceil($start / $row) + 1;
	echo '<div id="connectionsTotalPage">'.$totalPage.'</div>';
	echo '<div id="connectionsCurrentPage">'.$currentPage.'</div>';
?>
</div>
