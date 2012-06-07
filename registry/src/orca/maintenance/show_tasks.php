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
// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';
require '../../_includes/header.php';
$page = getQueryValue('page');
$taskId = getQueryValue('taskId');
$dsKey = getQueryValue('ds');
$roKey = getQueryValue('ro');
$status = getQueryValue('status');
$pagiDiv = '';
$rows = 10;
if(!$page){
	$start = 0;
}else{
	$start = ($page - 1) * $rows;
}

// BEGIN: Page Content
// =============================================================================
// TODO: get queries based on params
// but for now... show all tasks
$allTasks = getTask(null, null);
$numFound=(sizeof($allTasks));

?>
<h2><?php print 'Background Task:s ('.$numFound.' jobs)'?></h2>

<h5>this list contains all jobs submitted to run in the background.</h5>
<div>

	<?php
	foreach($allTasks as $task)
	{
		//print_r($r);
		echo $task['task_id'];
		echo $task['status'];
		echo $task['method'];
		echo $task['started'];
		echo $task['added'];
		echo $task['completed'];
		echo $task['dependent_task']; 
		echo $task['params'];
		echo $task['key_hash'];
		echo $task['data_source_key_hash'];
		echo '<hr/>';
	}
     ?>
</div>

<?php
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
