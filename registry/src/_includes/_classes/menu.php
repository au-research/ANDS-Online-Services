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

class menu 
{
	public $id = '';        // required
	public $title = '';     // required
	public $parent_id = ''; // required
	public $margin_class = ''; // optional, will only be applied to menus
                               // with the special parent id gROOT_MENU_ID
                               
	public $default_state = 'MENU_OPEN'; // optional
                          
	
	function __construct($id, $title, $parent_id)
	{
		$this->id = $id;
		$this->title = $title;
		$this->parent_id = $parent_id;
		
	}
}

?>