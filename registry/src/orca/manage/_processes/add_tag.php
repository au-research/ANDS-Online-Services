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

$response = "no tag_id ";
$tag = trim(getQueryValue('tag'));
$contributed_by = getLoggedInUser();
$keyHash = getQueryValue('keyHash');
$tag = strtolower($tag);
$tag = str_replace('\'', '\\\'', $tag);
$tag = preg_replace('/[^a-zA-Z0-9\-]/', '', $tag);
$tagID = 0;
if(!tagExist($tag, $keyHash))
{
	$tagID = insertTag($tag, $keyHash, $contributed_by);
	print $tagID;
}else print $tagID;



?>
