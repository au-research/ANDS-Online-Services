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

	echo '<h3><a href="#">'.$r->{'title'}[0].'</a></h3>';
	echo '<div>';
	
	if(isset($r->{'description'})){
		echo "<p><strong>Description:</strong></p>";
		echo substr($r->{'description'}[0],0,500);
		if(strlen($r->{'description'}[0])>500) echo " ...";
		echo '<hr/>';
	}

	$citation ='';
	foreach($r->{'creator'} as $index=>$creator){
		$citation .= $r->{'creator'}[$index]."; ";
	}
	
	echo "<p><strong>Citation:</strong></p>";
	$citation .= '('.$r->{'publicationYear'}.'): '.$r->{'title'}[0].'; '.$r->{'publisher'}.'. http://dx.doi.org/'.$r->{'doi'};
	echo $citation;
	echo "<hr />";
	
	if(isset($r->{'resourceTypeGeneral'})){
		echo "<p><strong>Resource Type:</strong></p>";	
		echo $r->{'resourceTypeGeneral'}."<hr />";
	}
	
	echo '<a href="http://data.datacite.org/'.$r->{'doi'}.'" class="button" target="_blank">View DataCite Metadata</a>';
	echo '<div style="float:right;position:relative;"><a href="http://dx.doi.org/'.$r->{'doi'}.'" class="button" target="_blank">View record webpage</a></div>';
	echo '</div>';
	
}

echo '<div class="hide">';
	$numFound = $json->{'response'}->{'numFound'};	
	$start = $json->{'response'}->{'start'};
	echo $start." is the start<br />";
	$end = $start + 10;
		
	$totalPage = ceil($numFound / 10);
	$currentPage = ceil($start / 10)+1;
	echo '<div id="seeAlsoDataCiteTotalPage">'.$totalPage.'</div>';
	echo '<div id="seeAlsoDataCiteCurrentPage">'.$currentPage.'</div>';
?>
</div>
