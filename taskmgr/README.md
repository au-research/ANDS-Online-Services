# ANDS Registry Task Manager
This supporting service performs asynchronous tasks to ensure that the registry performs optimally. This provides significantly improved performance to users of the registry's web interface(s).

A typical example of a task that is better performed in the background:
* When a record is removed from the registry, this could affect the status of records which previous related to it.
* i.e. if an Activity record is deleted, then a collection which related to that Activity may have changed in quality level/status. 
* This requires a full check and reindex of any related records which is a time-intensive process.

With the ANDS Registry Task Manager, these tasks can now be processed in the background so that this additional computation does delay the user from continuing their next registry operation. 

*Whilst not currently supported, this style of task management may allow us to utilise multiprocessing to manage large tasks in the future.*

## Installation & Configuration
ANDS uses [Gearman](http://gearman.org/ "Gearman.org") as the framework for farming out task processing. This additional dependency ensures that tasks are completed consistently and that facilities for multi-processing/distributed task allocation are available. 

On CentOS systems, Gearman can be installed by:
	
	yum install gearmand 
	service gearmand start
	chkconfig --level 5 gearmand on	
	
The PHP library for interfacing with the task manager is installed by:

	yum install php-pecl-gearman
	service httpd restart
	
This will provide a Gearman background daemon which listens for tasks on localhost (127.0.0.1). Ensure that your server is properly protected (i.e. firewall) as the task server runs without any form of authentication to minimise overhead. 

From Release 8, the registry comes preconfigured with software that uses the Registry Task Manager to handle computationally intensive operations. These are provided in the `registry/orca/functions/orca_taskmgr_functions.php` module. This relies on the `$gearman_server` being declared in your global_config.php (see latest sample for details). 

Once registered with the Gearman framework, tasks require workers to be available to actually perform the computation. These workers reside in the new `taskmgr` folder of the ANDS Online Services repository. 

To install the worker, copy the directory to a suitable place on your server and modify `taskmgr/init.php` to point the `$APPLICATION_BASE` to the directory of your registry on the filesystem (i.e. */var/www/htdocs/registry/*). There is no need for the `taskmgr` folder to reside in a web-accessible folder as the software only deals in background tasks (at a minimum, it is recommended that this script be protected by a .htaccess rule). 

Once configured, we must initialise our workers. This can be done by one of two methods: 
**Method 1** - Start the PHP task manually and launch into the background

	php -f /path/to/generic_ands_worker.php 2>&1 >/dev/null

**Method 2** - Install the `andstaskmgr` as a service (CentOS)

	cp bin/andstaskmgr /etc/init.d/
	# Edit /etc/init.d/andstaskmgr to point to the appropriate task location
	chkconfig --add andstaskmgr
	chkconfig andstaskmgr on
	service andstaskmgr start
	
Method 2 has the added benefit of being able to quickly start/stop the worker as well as logging to `/var/log/andstaskmgr`. Edit the script as necessary to change the default output location. 


#### Task framework implementation

The task framework uses a "push" methodology. Scripts within the registry will queue up tasks (which can be chained together for dependencies) using the `addNewTask()` function and then calls `triggerAsyncTasks()` to force the worker to begin processing all queued tasks. Tasks will then be evaluated and moved from the "WAITING" status to "STARTED" and then "COMPLETED"/"FAILED".

The default `dynamic_task_worker.php` reads the task name using `getNextWaitingTask()` and then loads the appropriate function from `registry/orca/maintenance/_tasks/`. The dynamic task worker has the following behaviours:
* 	Will maintain connection to the ORCA database server (defined in global_config.php) and, if lost, poll the server every 30 seconds before continuing
* 	Will load task functions dynamically:
	** If the task that is queued is named "DO_SOMETHING":
	**  Open `registry/orca/maintenance/_tasks/do_something.php`
	**	Search for and execute the function task_do_something()  

Any errors should be thrown as an Exception (in which case the task will be marked as failed with the Exception message). Any output that should be logged with a completed task should be passed as a return value. Any text which is printed by the task during its execution will be send to stdout/logged (if using andstaskmgr). 

We suggest that you periodically restart the worker tasks due to poor memory management/garbage collection in PHP. 


# Support / Queries
Please note that this is an auxillery module intended to support the ANDS Software Suite. Use beyond what is provided in the ANDS Registry will not be actively supported. 

Contact services@ands.org.au for further information. 