<?php
echo $nextTask[0]['task_id'];
$taskId = $nextTask[0]['task_id'];
setTaskStarted($taskId);
 

echo " complete!";
//setTaskCompleted($taskId);

