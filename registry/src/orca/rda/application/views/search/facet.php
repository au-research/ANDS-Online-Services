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
		if($subjectFilter!='All') {
			//echo $subjectFilter;
			if (substr(rawurldecode($subjectFilter), 0, 7) === 'http://'){
				echo '<li class="limit">
					<a href="javascript:void(0);"
						title="'.resolveLabelFromVocabTermURI($subjectFilter,false).'"
						class="clearFilter clearSubjects" id="'.$subjectFilter.'">'.
						''.resolveLabelFromVocabTermURI($subjectFilter, false).'
						</a></li>';
			}else{
				echo '<li class="limit">
					<a href="javascript:void(0);"
						title="'.($subjectFilter).'"
						class="clearFilter clearSubjects" id="'.$subjectFilter.'">'.
						''.($subjectFilter).'
						</a></li>';
			}
    		//displaySelectedFacet('subject_value_resolved',$subjectFilter,$json);
		}
		//if($subjectFilter!='All') displaySelectedFacet('subject_vocab_uri',$subjectFilter,$json);
		if($licenceFilter!='All') displaySelectedFacet('licence_group',$licenceFilter,$json);
		echo '</ul>';
	echo '</div>';
	echo '</div>';
}

?>
<div class="right-box shadow">
	<h2>Subjects</h2>
	<div id="facet-content">
		<a href="javascript:;" id="browse_more_subject">Browse more subjects</a>
		<div id="anzsrc-toplevelfacet"></div>
		<div id="anzsrc-subject-facet-result" class="hide"></div>
	</div>
</div>

<?php
	/*
	echo '<pre>';
	print_r($json->{'facet_counts'}->{'facet_fields'}->{'group'});
	echo '</pre>';
	 * displayFacet is in helpers
	*/
	//displayFacet('subject_value_resolved', $subjectFilter, $json_subject_facet, $classFilter);
	//displayFacet('subject_vocab_uri', $subjectFilter, $json, $classFilter);
	displayFacet('group', $groupFilter, $json, $classFilter);
	//displayFacet('subject_value_resolved', $subjectFilter, $json, $classFilter);
	displayFacet('type', $typeFilter, $json, $classFilter);
	displayFacet('licence_group', $licenceFilter, $json, $classFilter);
?>
