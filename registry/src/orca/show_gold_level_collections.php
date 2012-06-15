<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
// Include required files and initialisation.
require '../_includes/init.php';
require 'orca_init.php';
require '../_includes/header.php';
$page= getQueryValue('page');
$pagiDiv = '';
$rows = 10;
if(!$page){
	$start = 0;
}else{
	$start = ($page - 1) * $rows;
}

// BEGIN: Page Content
// =============================================================================



	$q = 'gold_status_flag:1';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>$start,'rows'=>$rows, 'wt'=>'json',
		'fl'=>'*'
	);
	//$extra = '&facet=true&facet.field=status&&facet.field=class&facet.limit=-1&facet.mincount=0';
	$content = solr($solr_url, $fields);
	$json = json_decode($content);
	$numFound = $json->{'response'}->{'numFound'};

?>
<h2><?php print 'Gold Standard Records ('.$numFound.' records)'?></h2>

<?php if($numFound>0):?>
<?php

	$orca = eAPPLICATION_ROOT.'orca/';
?>
<h5>The following records have been verified as exemplary records by the ANDS Metadata Assessment Group.</h5>
<div>

	<?php
	if($numFound > $rows)
	{
		$pagiDiv = doPagination($json);
		echo $pagiDiv;
	}
	foreach($json->{'response'}->{'docs'} as $r)
	{
		//print_r($r);
		$type = $r->{'type'};
		$ro_key = $r->{'key'};
		$name = $r->{'list_title'};
		$class = $r->{'class'};
		$group = $r->{'group'};
		$status = $r->{'status'};
		$ds = $r->{'data_source_key'};

		echo '<a href="view.php?key='.urlencode($ro_key).'">'.$name.'</a> <br/>';
		echo '<span class="resultListItemLabel">Status: </span><span style="color: #ffffff; background-color: #32CD32; border: 1px solid #888888; padding-left: 2px; padding-right: 2px;">'.$status.' </span><br/>';
		//echo '<span class="resultListItemLabel">Class: </span>'.$class.' <br/>';
		echo '<span class="resultListItemLabel">Type: </span>'.$type.' <br/>';
		echo '<span class="resultListItemLabel">Data Source Key: </span>'.$ds.' <br/>';
		echo '<span class="resultListItemLabel">Group: </span>'.$group.' <br/>';

		$descriptions = array();if(isset($r->{'description_value'})) $descriptions = $r->{'description_value'};
		$description_type=array();if(isset($r->{'description_type'})) $description_type = $r->{'description_type'};

		$relations = array();
		if(isset($r->{'relatedObject_relation'})){
			$relations = $r->{'relatedObject_relation'};
			$related_name = $r->{'relatedObject_relatedObjectListTitle'};
			$related_key = $r->{'relatedObject_key'};
			echo '<span class="resultListItemLabel">Relations: </span> ';
			foreach($relations as $key=>$relation){
				echo '<a href="view.php?key='.urlencode($related_key[$key]).'">'.$related_name[$key].'</a> | ';
			}
			echo '<br/>';
		}

		$subjects=array();$subjects_type=array();
		if(isset($r->{'subject_value'})){
			$subjects = $r->{'subject_value'};
			$subjects_type = $r->{'subject_type'};
			echo '<span class="resultListItemLabel">Subjects: </span> ';
			foreach($subjects_type as $key=>$t){
				echo '<span class="attribute">'.$t.'</span> '.'<a href="#" class="subjectFilter" name="'.$subjects[$key].'">'.$subjects[$key].'</a> ';
			}
			echo '<br/>';
		}


		echo '<span class="resultListItemLabel">Descriptions: </span>';

		foreach($description_type as $key=>$t){
			echo '<span class="attribute">'.$t.'</span>:'.strip_tags(htmlspecialchars_decode(($descriptions[$key]))).'<br/>';
		}

		echo '<hr/>';
		if($numFound > $rows)
		{
			echo $pagiDiv;
		}
	}

     else:?>
	<h5>
		No gold standard records currently exist in the registry.
	</h5>
	<?php endif;?>
</div>

<?php
require '../_includes/footer.php';
require '../_includes/finish.php';

function doPagination($json)
{


	$numFound = $json->{'response'}->{'numFound'};
	$timeTaken = $json->{'responseHeader'}->{'QTime'};
	$timeTaken = $timeTaken / 1000;

	$row = $json->{'responseHeader'}->{'params'}->{'rows'};
	$start = $json->{'responseHeader'}->{'params'}->{'start'};
	//$query = $json->{'responseHeader'}->{'params'}->{'q'};
	$end = $start + $row;

	$h_start = $start + 1;
	$h_end = $end + 1;

	if ($h_end > $numFound) $h_end = $numFound;

	$totalPage = ceil($numFound / $row);
	$currentPage = ceil($start / $row)+1;

	$range = 3;

	$pagiDiv = '<br/><div class="pagination">';
	$pagiDiv .= 'Page: '.$currentPage.'/'.$totalPage.'   |  ';

	//if not on page 1, show Previous
	$pagiDiv .= '<a href="show_gold_level_collections.php?page=1" class="gotoPage">First</a>';
	if($currentPage > 1){
		$pagiDiv .= '<a href="show_gold_level_collections.php?page='.($currentPage-1).'" class="pagination-page"> &lt;</a>';
	}

	for ($x = ($currentPage - $range); $x < (($currentPage + $range) + 1); $x++) {
		if (($x > 0) && ($x <= $totalPage)) { //if it's valid
			if($x==$currentPage){//if we're on currentPage
				$pagiDiv .= '<a class="pagination-page pagination-currentPage">'.$x.'</a>';//don't make a link
			}else{//not CurrentPage
				$pagiDiv .= '<a href="show_gold_level_collections.php?page='.$x.'" class="pagination-page gotoPage" id="'.$x.'">'.$x.'</a>';
			}
		}
	}

	//if not on last page, show Next
	if($currentPage < $totalPage){
		$pagiDiv .= '<a href="show_gold_level_collections.php?page='.($currentPage+1).'" class="pagination-page">&gt;</a>';
	}

	$pagiDiv .= '<a href="show_gold_level_collections.php?page='.$totalPage.'" class="gotoPage">Last</a>';

	//echo '<a href="javascript:void(0);" id="prev">Previous Page</a> <a href="javascript:void(0);" id="next">Next Page</a>';
	$pagiDiv .= '</div><br/>';
 return $pagiDiv;
}
?>
