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
require '../_includes/add_registry_object_strings.php';

$section = (isset($_GET['helpSection']) ? $_GET['helpSection'] : '');

if (isset($help_text[$section])) {
	
	print $help_text[$section];
	
} else {
	
	print "<span class=\"tabHelpTitle\">Section not found</span><br/>";
	
}