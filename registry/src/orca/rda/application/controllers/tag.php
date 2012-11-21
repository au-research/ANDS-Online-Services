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
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tag extends CI_Controller {

    public function __construct()
    {
         parent::__construct();
    }

	public function index()
	{
		//var_dump($params);

	}

	public function addRecordTag(){
		$tag = $this->input->post('tag');
		$keyHash = $this->input->post('keyHash');	
		$contributed_by = $this->input->post('contributed_by');			
		$result = addTag($tag,$keyHash,$contributed_by);
		echo $result;
	}
	
}
?>