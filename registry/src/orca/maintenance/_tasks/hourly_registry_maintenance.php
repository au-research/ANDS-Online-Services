<?php
function task_hourly_registry_maintenance($task)
{
	$taskId = $task['task_id'];
	$message = '';

	// XXX: Check Harvester

	// XXX: Check PIDs

	// XXX: Check DOIs

	// XXX: Check system resources

	// XXX: Update stats

	/*
	 * Clear completed tasks
	 */
	deleteCompletedTasksBefore("1 day");

	/*
	 * Check for null data sources (XXX: slugs, ro hashes)
	 */
	$dataSources = getDataSources(null,null);
	foreach ($dataSources AS $ds)
	{
		if ($ds['key_hash'] == "")
		{
			// Calling this function will update the hash if it doesn't exist
			getDataSourceHashForKey($ds['data_source_key']);
		}
	}
	// Don't forget PMD!
	getDataSourceHashForKey('PUBLISH_MY_DATA');

	/*
	 *  Check for null registry object hashes
	 */ 
	if ($emptyRegistryObjectsList = getEmptyRegistryObjectHashes())
	{
		foreach ($emptyRegistryObjectsList AS $registry_object)
		{
			getRegistryObjectHashForKey($registry_object['registry_object_key']);
		}
	}


	/*
	 * Check for null url_slugs
	 */
	if ($emptyRegistryObjectsList = getEmptyRegistryObjectURLSlugs())
	{
		foreach ($emptyRegistryObjectsList AS $registry_object)
		{
			updateRegistryObjectSLUG($registry_object['registry_object_key'], $registry_object['display_title']);
		}
	}
	
	
	

	/*
	 * Requeue for next time...
	*/
	addNewTask($task['method'], "Requeued task from task run: " . $taskId, '', '', null, "1 hour");
	return $message;
}