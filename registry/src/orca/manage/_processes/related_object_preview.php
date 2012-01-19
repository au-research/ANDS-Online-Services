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
if (!IN_ORCA) die('No direct access to this file is permitted.');

//echo $keyValue;
echo '<div class="ro_preview_box">';
if($published = getRegistryObject($keyValue)){
	//print_r($published);
	formatPreview($published, 'published');
	
}else if($draft = getDraftRegistryObject($keyValue, null)){
	formatPreview($draft, 'draft');
	
}else{
	echo 'Record not found!';
}
echo '</div>';

function formatPReview($d, $state){
	if($state=='published'){
		echo '<p><b>'.$d[0]['display_title'].'</b> ';
	}elseif($state=='draft'){
		echo '<p><b>'.$d[0]['registry_object_title'].'</b> ';
	}
	
	switch($d[0]['status']){
		case 'PUBLISHED':echo '<span class="tag published">'.$d[0]['status'].'</span>';break;
		case 'APPROVED':echo '<span class="tag approved">'.$d[0]['status'].'</span>';break;
		case 'DRAFT':echo '<span class="tag draft">'.$d[0]['status'].'</span>';break;
		case 'ASSESSMENT_IN_PROGRESS':echo '<span class="tag inprogress">'.$d[0]['status'].'</span>';break;
		case 'SUBMITTED_FOR_ASSESSMENT':echo '<span class="tag submitted">'.$d[0]['status'].'</span>';break;
	}
	echo '</p>';
	if($state=='published'){
		echo '<p><label>Class: </label>'.$d[0]['registry_object_class'].'</p>';
	}elseif($state=='draft'){
		echo '<p><label>Class: </label>'.$d[0]['class'].'</p>';
	}

	if($state=='published'){
		echo '<p><label>DataSource: </label>'.$d[0]['data_source_key'].'</p>';
	}elseif($state=='draft'){
		echo '<p><label>DataSource: </label>'.$d[0]['registry_object_data_source'].'</p>';
	}
	
	
	
	
}

?>