<?php
$gmclient= new GearmanClient();
$gmclient->addServer($gearman_server);

function triggerAsyncTasks()
{
	global $gmclient;
	$gmclient->addTaskBackground("executeNextPendingTask","");
	$gmclient->runTasks();
	return;
}

/*
 * could be useful later for synchronous tasking???

# Send reverse job
do
{
  $gmclient->addTaskBackground("executeNextPendingTask",$data_source);
  $gmclient->runTasks();

  # Check for various return packets and errors.
  switch($gmclient->returnCode())
  {
    case GEARMAN_WORK_DATA:
      echo "Data: $result\n";
      break;
    case GEARMAN_WORK_STATUS:
      list($numerator, $denominator)= $gmclient->doStatus();
      echo "Status: $numerator/$denominator complete\n";
      break;
    case GEARMAN_WORK_FAIL:
      echo "Failed\n";
      exit;
    case GEARMAN_SUCCESS:
      break;
    default:
      echo "RET: " . $gmclient->returnCode() . "\n";
      exit;
  }
}
while($gmclient->returnCode() != GEARMAN_SUCCESS);

 */