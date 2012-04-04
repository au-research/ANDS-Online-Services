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

$json_mode = trim(getQueryValue('as_json')) === "true";

//echo $keyValue;
if($published = getRegistryObject($keyValue)){
	//print_r($published);
	if ($json_mode)
	{
		formatPreviewJSON($published, 'published');
	}
	else
	{
		echo '<div class="ro_preview_box">';
		formatPreviewHTML($published, 'published');
		echo '</div>';
	}
	
}else if($draft = getDraftRegistryObject($keyValue, null)){
	
	if ($json_mode)
	{
		formatPreviewJSON($draft, 'draft');
	}
	else
	{
		echo '<div class="ro_preview_box">';
		formatPreviewHTML($draft, 'draft');
		echo '</div>';
	}
	
}else{
	if ($json_mode)
	{
		echo json_encode(array('key'=>$keyValue, 'status' => 'NOTFOUND'));
	}
	else
	{
		echo '<div class="ro_preview_box">';
		echo 'Record not found!';
		echo '</div>';
	}
}


function formatPreviewHTML($d, $state){
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

function formatPreviewJSON($d, $state)
{
	$json_output = array();
	
	$json_output['status'] = $d[0]['status'];
	$json_output['status_span'] = getRegistryObjectStatusSpan($d[0]['status']);
	
	/*switch($d[0]['status']){
		case 'PUBLISHED': $json_output['status_html'] = '<span class="tag published">'.$d[0]['status'].'</span>';break;
		case 'APPROVED':$json_output['status_html'] = '<span class="tag approved">'.$d[0]['status'].'</span>';break;
		case 'DRAFT':$json_output['status_html'] =  '<span class="tag draft">'.$d[0]['status'].'</span>';break;
		case 'ASSESSMENT_IN_PROGRESS':$json_output['status_html'] =  '<span class="tag inprogress">'.$d[0]['status'].'</span>';break;
		case 'SUBMITTED_FOR_ASSESSMENT':$json_output['status_html'] = '<span class="tag submitted">'.$d[0]['status'].'</span>';break;
		default:$json_output['status_html'] = '<span class="tag">'.$d[0]['status'].'</span>';
	}*/
	
	if ($state == 'published')
	{
		$json_output['data_source'] = $d[0]['data_source_key'];
		$json_output['class'] = $d[0]['registry_object_class'];
		$json_output['title'] = $d[0]['display_title'];
		$json_output['key'] = $d[0]['registry_object_key'];
	}
	elseif ($state == 'draft')
	{
		$json_output['data_source'] = $d[0]['registry_object_data_source'];
		$json_output['class'] = $d[0]['class'];
		$json_output['title'] = $d[0]['registry_object_title'];
		$json_output['key'] = $d[0]['draft_key'];
	}
	
	echo json_encode($json_output);
}

?>