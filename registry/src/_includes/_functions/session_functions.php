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
// Session Key Globals
// -----------------------------------------------------------------------------
define("sROLE_ID", "role_id");
define('sAUTH_DOMAIN', 'auth_domain');
define("sNAME", "name");
define("sREMOTE_ADDRESS", "remote_address");
define("sUSER_AGENT", "user_agent");
define("sTIMEOUT","timeout");
define("sLDAP_ATTRIBUTES", "LDAP_attributes");

// Session Functions
// -----------------------------------------------------------------------------
function getSessionId()
{
	$sessionId = '';
	if( isset($_COOKIE['PHPSESSID']) )
	{
		$sessionId = $_COOKIE['PHPSESSID'];
	}
	return $sessionId;
}

function setSessionVar($key, $var)
{
	$_SESSION[$key] = $var;
}

function getSessionVar($key)
{
	$value = null;
	if( isset($_SESSION[$key]) )
	{
		$value = $_SESSION[$key];
	}
	return $value;
}

function clearSessionVar($key)
{
	if( isset($_SESSION[$key]) )
	{
		unset($_SESSION[$key]);
	}
}

function startSession($roleID, $name, $authDomain='')
{
	global $gSessionTimeoutMinutes;
	
	setSessionVar(sROLE_ID, $roleID);
	setSessionVar(sAUTH_DOMAIN, $authDomain);
	setSessionVar(sNAME, $name);
	setSessionVar(sREMOTE_ADDRESS, getRemoteAddress());
	setSessionVar(sUSER_AGENT, getUserAgent());
	setSessionVar(sTIMEOUT, minutesSinceEpoch() + $gSessionTimeoutMinutes);
}

function endSession()
{
	// Unset all of the session data.
	$_SESSION = array();

	// Remove the session cookie.
	if( isset($_COOKIE[session_name()]) )
	{
	    setcookie(session_name(), '', -1);
	}
	
	// Destroy the session.
	session_destroy();
}

function getUserAgent()
{
	return $_SERVER['HTTP_USER_AGENT'];
}

function getRemoteAddress()
{
	
	if ( isset($_SERVER["HTTP_CLIENT_IP"]) )   
	{
		$ip=$_SERVER["HTTP_CLIENT_IP"];
	} 
	else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    
	{
		$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
	} 
	else if ( isset($_SERVER["REMOTE_ADDR"]) )   
	{
		$ip=$_SERVER["REMOTE_ADDR"];
	} 
	else 
	{
		$ip = "0.0.0.0";
	}
	
	// Prevent errors when X_FORWARDED_FOR creates arbitrarily long IP strings
	if (strlen($ip) > 15)
	{
		// Split header by commas - http://en.wikipedia.org/wiki/X-Forwarded-For
		$ip = explode(",", $ip);
		// No commas found?
		if (count($ip) == 1)
		{
			// Just trim the IP and append a special symbol
			$ip = substr($ip, 0, 14) . "X";
		}
		else
		{
			// Take the first IP in the list
			$ip = substr($ip[0], 0, 15);
		}
	
	}
	
	return $ip;
}

function isValidSession()
{
	global $gSessionTimeoutMinutes;
	
	$valid = true;
	
	if( getSessionVar(sROLE_ID) ) // This is an authenticated session.
	{
		if( getSessionVar(sTIMEOUT) <= minutesSinceEpoch() )
		{
			// There have been no requests with this session for 
			// at least $gSessionTimeoutMinutes.
			$valid = false;
		}
		else
		{
			// Timeout hasn't been exceeded, so reset it.
			setSessionVar(sTIMEOUT, minutesSinceEpoch() + $gSessionTimeoutMinutes);
		}
	}
	return $valid;
}

function minutesSinceEpoch()
{
	$secondsSinceEpoch = (int)(microtime(true));
	return (int)($secondsSinceEpoch/60);
}

function checkSession()
{
	if( !isValidSession() )
	{
		// End the session.
		endSession();
		// Redirect to login.
		responseRedirect(eAPP_ROOT.'login.php?logout=1&expired=1');
	}
}
?>