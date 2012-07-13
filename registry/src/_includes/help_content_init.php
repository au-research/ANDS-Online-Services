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
// Required Files
// -----------------------------------------------------------------------------
// Include environment settings.
require '../../global_config.php';
require '_environment/application_env.php';

// Help content access checking.
// -----------------------------------------------------------------------------
// Make sure that the request came from this instance's help page
/*if( !isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] !== eAPP_ROOT.'help.php' )
{
	// Build an empty string and stop execution.
	print '';
	exit;
}*/
?>