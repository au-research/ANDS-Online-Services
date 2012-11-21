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


$draft_key = rawurldecode(getQueryValue("key"));
$data_source = rawurldecode(getQueryValue("data_source"));
if ($draft_key && $data_source)
{
	$flag = getQueryValue("flag");
	setDraftFlag($draft_key, $data_source, $flag);
}