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

// Useful debug information.
// -----------------------------------------------------------------------------
if( $eDebugOnStatus ) 
{
	print "\n\n<h3>DEBUG INFO</h3>\n";
	print 'session_id='.session_id()."<br />\n";
	print 'isValidSession='.isValidSession()."<br />\n";
	print 'user agent='.getUserAgent()."<br />\n";
	print 'remote address='.getRemoteAddress()."<br />\n";
	print 'minutes since epoch='.minutesSinceEpoch()."<br />\n";
	printData('getAllUserRoleIDs(getSessionVar(sROLE_ID)', getAllUserRoleIDs(getSessionVar(sROLE_ID)));
	printData('getActivityRoleIDs(getThisActivityID())', getActivityRoleIDs(getThisActivityID()));
	printData('$_COOKIE', $_COOKIE);
	printData('$_SESSION', $_SESSION);
	printData('$_POST', $_POST);
	printData('$_GET', $_GET);
	printData('$_REQUEST', $_REQUEST);
	printData('$_SERVER', $_SERVER);
	
	if( getSessionVar(sLDAP_ATTRIBUTES) )
	{
		printData("getSessionVar(sLDAP_ATTRIBUTES)", getSessionVar(sLDAP_ATTRIBUTES));
	}
}

// Close all open database connections.
// -----------------------------------------------------------------------------
closeDatabaseConnections();
?>