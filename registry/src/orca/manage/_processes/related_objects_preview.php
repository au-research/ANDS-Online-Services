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


$keys = getPostedValue('keys');
$keys = json_decode($keys);
$json_result = Array();

foreach($keys AS $key)
{
	$target = $key[1];
	if($published = getRegistryObject($key[0]))
	{
		$json_result[$target] = formatPreviewJSON($published, 'published');		
	}
	else if($draft = getDraftRegistryObject($key[0], null))
	{
		$json_result[$target] = formatPreviewJSON($draft, 'draft');		
	}
	else
	{
		$json_result[$target] = array('key'=>$key[0], 'status' => 'NOTFOUND');
	}
}

echo json_encode($json_result);

function formatPreviewJSON($d, $state)
{
	$json_output = array();
	
	$json_output['status'] = $d[0]['status'];
	$json_output['status_span'] = getRegistryObjectStatusSpan($d[0]['status']);
	
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
	$json_output['html']=formatPreviewHTML($d, $state);
	return $json_output;
}


function formatPreviewHTML($d, $state){
	$string ='<div class="ro_preview_box">';
	if($state=='published'){
		$string .= '<p><b>'.$d[0]['display_title'].'</b> ';
	}elseif($state=='draft'){
		$string .= '<p><b>'.$d[0]['registry_object_title'].'</b> ';
	}

	switch($d[0]['status']){
		case 'PUBLISHED':$string .= '<span class="tag published">'.$d[0]['status'].'</span>';break;
		case 'APPROVED':$string .= '<span class="tag approved">'.$d[0]['status'].'</span>';break;
		case 'DRAFT':$string .= '<span class="tag draft">'.$d[0]['status'].'</span>';break;
		case 'ASSESSMENT_IN_PROGRESS':$string .= '<span class="tag inprogress">'.$d[0]['status'].'</span>';break;
		case 'SUBMITTED_FOR_ASSESSMENT':$string .= '<span class="tag submitted">'.$d[0]['status'].'</span>';break;
	}
	$string .= '</p>';
	if($state=='published'){
		$string .= '<p><label>Class: </label>'.$d[0]['registry_object_class'].'</p>';
	}elseif($state=='draft'){
		$string .= '<p><label>Class: </label>'.$d[0]['class'].'</p>';
	}

	if($state=='published'){
		$string .= '<p><label>DataSource: </label>'.$d[0]['data_source_key'].'</p>';
	}elseif($state=='draft'){
		$string .= '<p><label>DataSource: </label>'.$d[0]['registry_object_data_source'].'</p>';
	}
	
	$string .='</div>';
	
	return $string;
	
}
?>