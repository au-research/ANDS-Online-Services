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

// COSI Activity
class activity
{
	public $id = '';                      // required
	public $title = '';                   // required
	public $path = '';                    // required
	public $menu_id = '';                 // optional
	public $help_content_uri = '';        // optional
	public $help_content_fragment_id = '';// optional
	
	/*
	Activities that cannot be accessed directly, but require
    a path to be taken to provide an id to them, should not be visible in the
	menu structure until they are active. Examples include view, edit, and delete
	style activities which are reached after list or search type activities.
	For these, set $only_show_if_active = true in the application_config.
	*/
	public $only_show_if_active = false;
	public $no_check_ssl = false; // If set to true then we will not redirect requests 
	                              // for this activity from http to https when running checkSSL().
	
	function __construct($id, $title, $path)
	{
		$this->id = $id;
		$this->title = $title;
		$this->path = $path;
	}
}

?>