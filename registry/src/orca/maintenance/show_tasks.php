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
if(!userIsORCA_ADMIN()){die('Permission Override: ORCA Admins ONLY');}

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
//$allTasks = getTask(null, null);
//$numFound=(sizeof($allTasks));

echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_styles/taskmgr.css" />';

// Include the Flexigrid styles & other info
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_javascript/flexigrid/css/flexigrid.css" />
<script src="'. eAPP_ROOT.'orca/_javascript/flexigrid/js/flexigrid.js" type="text/javascript"></script>';
?>


<h2><?php print 'Background Tasks'?></h2>

<div>
<table id="queuedTaskGrid"></table>

<br/><hr/><br/>

<table id="completedTaskGrid"></table>

<br/><hr/><br/>

<table id="failedTaskGrid"></table>

</div>

<div id="taskAddDialog" style="display:none; cursor: default; text-align:left; padding:8px;">
        <h3>Add New Task</h3>

        <div style="width:375px;padding:5px;">
        	<label>
		        <span>Choose a Method:</span> <?php echo task_chooser(); ?>
		    </label><br/>
		    <label>
		        <span>Data Source: </span> <?php echo ds_chooser(); ?>
		    </label><br/>
		    <label>
		        <span>Reg Obj Keys: </span> <input type="text" id="registry_object_keys" />
		    </label><br/>

		    <label>
		        <span>Schedule Delay (time from now): </span> <?php echo schedule_chooser(); ?>
		    </label><br/>
		</div>
		
			<div><input type="checkbox" checked="checked" id="automatically_trigger_on_submit" style="display:inline; width:auto;" /> Automatically trigger on submit</div>

        <input type="button" id="taskAddConfirm" value="Add Task" style="cursor:pointer;" />
        <input type="button" id="taskAddCancel" value="Cancel" style="cursor:pointer;" />
</div>

<script type="text/javascript">
var taskListResourcePoint = "<?php echo eAPP_ROOT;?>orca/manage/process_registry_object.php?task=getRegistryTasks";

$("#queuedTaskGrid").flexigrid({
	url: taskListResourcePoint+'&subset=pending',
	dataType: 'json',
	colModel : [
	    {display: '', name : 'opts', width : 60, sortable : false, align: 'center'},
		{display: 'Task ID', name : 'task_id', width : 40, sortable : false, align: 'center'},
		{display: 'Pre-Req', name : 'prereq_task', width : 40, sortable :false, align: 'center'},
		{display: 'Name', name : 'method', width : 120, sortable : false, align: 'left'},
		{display: 'Added', name : 'added', width : 70, sortable : false, align: 'left'},
		{display: 'Waiting for', name : 'time_waiting', width : 75, sortable : false, align: 'right'},
		{display: 'Scheduled For', name : 'scheduled_for', width : 85, sortable : false, align: 'right'},
		{display: 'Status', name : 'status', width : 80, sortable : false, align: 'right'},
		{display: 'Data Source', name : 'data_source_key', width : 80, sortable : false, align: 'right'},
		{display: 'Reg Obj Keys', name : 'reg_obj_keys', width : 80, sortable : false, align: 'right'},
		{display: 'Log Message', name : 'log_msg', width : 280, sortable : false, align: 'left'}
		],
	buttons : [
	   	{name: 'Refresh', onpress : refreshQueued},
	   	{name: 'Trigger Worker', onpress : triggerWorker},
	   	{name: 'Add New Task', onpress : addNewTaskDialog}
		],
	sortname: "task_id",
	sortorder: "desc",
	usepager: true,
	tableTitle: 'Queued/Pending Tasks',
	useRp: true,
	rp: 15,
	showTableToggleBtn: true,
	hideOnNoRows: false,
	width: 1050,
	height: 130
});

$("#completedTaskGrid").flexigrid({
	url: taskListResourcePoint+'&subset=completed',
	dataType: 'json',
	colModel : [
		{display: 'Task ID', name : 'task_id', width : 40, sortable : false, align: 'center'},
		{display: 'Pre-Req', name : 'prereq_task', width : 40, sortable :false, align: 'center'},
		{display: 'Name', name : 'method', width : 120, sortable : false, align: 'left'},
		{display: 'Executed', name : 'executed', width : 100, sortable : false, align: 'left'},
		{display: 'Duration', name : 'duration', width : 65, sortable : false, align: 'right'},
		{display: 'Status', name : 'status', width : 80, sortable : false, align: 'right'},
		{display: 'Data Source', name : 'data_source_key', width : 80, sortable : false, align: 'right'},
		{display: 'Reg Obj Keys', name : 'reg_obj_keys', width : 80, sortable : false, align: 'right'},
		{display: 'Log Message', name : 'log_msg', width : 280, sortable : false, align: 'left'}
		],
	buttons : [
	   	{name: 'Refresh', onpress : refreshCompleted},
		{name: 'Flush tasks older than 24hrs', onpress : flushCompleted}
		],
	sortname: "task_id",
	sortorder: "desc",
	usepager: true,
	tableTitle: 'Completed Tasks',
	useRp: true,
	rp: 15,
	showTableToggleBtn: true,
	hideOnNoRows: false,
	width: 1050,
	height: 250
});

$("#failedTaskGrid").flexigrid({
	url: taskListResourcePoint+'&subset=failed',
	dataType: 'json',
	colModel : [
	    {display: '', name : 'opts', width : 60, sortable : false, align: 'center'},
		{display: 'Task ID', name : 'task_id', width : 40, sortable : false, align: 'center'},
		{display: 'Pre-Req', name : 'prereq_task', width : 40, sortable :false, align: 'center'},
		{display: 'Name', name : 'method', width : 90, sortable : false, align: 'left'},
		{display: 'Queued', name : 'added', width : 70, sortable : false, align: 'left'},
		{display: 'Started', name : 'started', width : 65, sortable : false, align: 'right'},
		{display: 'Completed', name : 'completed', width : 65, sortable : false, align: 'right'},
		{display: 'Duration', name : 'duration', width : 65, sortable : false, align: 'right'},
		{display: 'Status', name : 'status', width : 80, sortable : false, align: 'right'},
		{display: 'Data Source', name : 'data_source_key', width : 80, sortable : false, align: 'right'},
		{display: 'Reg Obj Keys', name : 'reg_obj_keys', width : 80, sortable : false, align: 'right'},
		{display: 'Log Message', name : 'log_msg', width : 280, sortable : false, align: 'left'}
		],
	buttons : [
	   	{name: 'Refresh', onpress : refreshFailed },
	   	{name: 'Flush failed tasks', onpress : flushFailed}
		],
	sortname: "task_id",
	sortorder: "desc",
	usepager: true,
	tableTitle: 'Failed/Stalled Tasks',
	useRp: true,
	rp: 15,
	showTableToggleBtn: true,
	hideOnNoRows: true,
	width: 1050,
	height: 250
});

function addNewTaskDialog()
{
	$.blockUI({ message: $("#taskAddDialog"), css: { width: '400px' } });

	setTimeout(function() {$('#queuedTaskGrid').flexReload();}, 150);
}

$('#taskAddCancel').click(function() {
    $.unblockUI();
    return false;
});

$('#taskAddConfirm').click(function() {

	addNewTask();
    return false;
});


function addNewTask()
{
	var taskString = taskListResourcePoint+'&subset=taskAdd';
	taskString += '&method=' + $('#method').val();
	taskString += '&data_source_key=' + $('#data_source_key').val();
	taskString += '&registry_object_keys=' + encodeURIComponent($('#registry_object_keys').val());
	taskString += '&schedule_for=' + encodeURIComponent($('#schedule_for').val());

	$.get(taskString);
	$.unblockUI();

	if ($('#automatically_trigger_on_submit').is(':checked'))
	{
		triggerWorker();
	}

	reloadAllGrids(500);
}

function flushCompleted()
{
	$.get(taskListResourcePoint+'&subset=flushCompleted');
	$('#completedTaskGrid').flexReload();
}
function refreshCompleted()
{
	$('#completedTaskGrid').flexReload();
}

function triggerWorker()
{
	$.get(taskListResourcePoint+'&subset=triggerWorker');
	reloadAllGrids(1500);
}
function refreshQueued()
{
	$('#queuedTaskGrid').flexReload();
}

function deleteTask(task_id)
{
	$.get(taskListResourcePoint+'&subset=deleteTask&task_id='+task_id);
	reloadAllGrids(500);
}
function rescheduleTask(task_id)
{
	$.get(taskListResourcePoint+'&subset=rescheduleTask&task_id='+task_id);
	reloadAllGrids(500);
}
function refreshFailed()
{
	$('#failedTaskGrid').flexReload();
}
function flushFailed()
{
	$.get(taskListResourcePoint+'&subset=flushFailed');
	$('#failedTaskGrid').flexReload();
}

function reloadAllGrids(delay)
{
	setTimeout(function() {$('#queuedTaskGrid').flexReload();$('#completedTaskGrid').flexReload();$('#failedTaskGrid').flexReload();} , delay);
}
</script>
<?php
function task_chooser()
{
	$tasks = array('');
	$tasks = scandir('_tasks/');
	$return = '<select id="method" style="width:150px;">';
	$return .= '<option></option>';
	foreach ($tasks AS $t)
	{
		if (substr($t,0,1) == '.' || substr($t,0,1) == '_') continue;

		$t = substr(strtoupper($t),0,-4);
		$return .= "<option value=\"".$t."\">".$t."</option>\n";

	}

	$return .= '</select>';

	return $return;

}

function ds_chooser()
{

	// Execute the search.
	$rawResults = getDataSources(null, null);

	$return = '<select id="data_source_key" style="width:300px;">
			<option value=""></option>';

	// Present the results.
	for( $i=0; $i < count($rawResults); $i++ )
	{
		$dataSourceKey =$rawResults[$i]['data_source_key'];
		$dataSourceTitle = $rawResults[$i]['title'];
		$return .= "<option value=\"".rawurlencode($dataSourceKey)."\">".esc($dataSourceTitle)."</option>\n";
	}
	$return .='<option value="PUBLISH_MY_DATA">PUBLISH MY DATA</option>';
	$return .= '</select>';

	return $return;
}

function schedule_chooser()
{

	// Execute the search.
	$opts = array('', '15 seconds', '1 minute', '5 minutes', '30 minutes',
					'1 hour','6 hours','12 hours','24 hours');

	$return = '<select id="schedule_for" style="width:300px;">';

	// Present the results.
	foreach ( $opts AS $opt )
	{
		$return .= "<option value=\"".$opt."\">".esc($opt)."</option>\n";
	}
	$return .= '</select>';

	return $return;
}
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
