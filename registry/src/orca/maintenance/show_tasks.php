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
<table>
<tbody>
<tr>
<th>task_id</th>
<th>prerequisite_task</th>
<th>status</th>
<th>method</th>
<th>added</th>
<th>started</th>
<th>completed</th>
<th>data_source_key</th>
<th>registry_object_keys</th>
<th>log_msg</th>
</tr>
	<?php
	foreach($allTasks as $task)
	{
		print("<tr>");
		print("<td>".$task['task_id']."</td>");
		print("<td>".$task['prerequisite_task']."</td>"); 
		print("<td>".$task['status']."</td>");
		print("<td>".$task['method']."</td>");
		print("<td>".$task['added']."</td>");
		print("<td>".$task['started']."</td>");
		print("<td>".$task['completed']."</td>");
		print("<td>".$task['data_source_key']."</td>");
		print("<td>".$task['registry_object_keys']."</td>");
		print("<td>".$task['log_msg']."</td>");
		print("</tr>");
	}
     ?>
</tbody>
</table>
</div>

<?php
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
