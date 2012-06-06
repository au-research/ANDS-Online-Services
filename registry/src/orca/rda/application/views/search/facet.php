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
if(($spatial_included_ids!='') || ($temporal!='All') || ($typeFilter!='All') || ($groupFilter!='All')||($subjectFilter!='All')||($licenceFilter!='All'))
{
	echo '<div class="right-box shadow">';
	echo '<h2>Selected</h2>';
	echo '<div class="facet-content">';
		echo '<ul>';
		if($temporal!='All'){
			echo '<li><a href="javascript:void(0);" id="clearTemporal" class="clearFilter" title="Search results are restricted to this timeline, Click to remove this filter">'.$temporal.'</a></li>';
		}
		if($spatial_included_ids!=''){
			echo '<li><a href="javascript:void(0);" id="clearSpatial" class="clearFilter" title="Search results are restricted to spatial, Click to remove this filter">Clear Spatial</a></li>';
		}
		if($typeFilter!='All') displaySelectedFacet('type',$typeFilter,$json);
		if($groupFilter!='All') displaySelectedFacet('group',$groupFilter,$json);
		if($subjectFilter!='All') displaySelectedFacet('subject_value_resolved',$subjectFilter,$json);
		if($licenceFilter!='All') displaySelectedFacet('licence_group',$licenceFilter,$json);
		echo '</ul>';
	echo '</div>';
	echo '</div>';
}

?>

<?php
	/*
	echo '<pre>';
	print_r($json->{'facet_counts'}->{'facet_fields'}->{'group'});
	echo '</pre>';
	 * displayFacet is in helpers
	*/
	displayFacet('group', $groupFilter, $json, $classFilter);
	displayFacet('subject_value_resolved', $subjectFilter, $json, $classFilter);
	displayFacet('type', $typeFilter, $json, $classFilter);
	displayFacet('licence_group', $licenceFilter, $json, $classFilter);
?>
