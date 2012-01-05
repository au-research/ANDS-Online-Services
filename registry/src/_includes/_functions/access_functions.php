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
// Globals
// -----------------------------------------------------------------------------
// From dbs_cosi.
define('gAUTHENTICATION_BUILT_IN',   'AUTHENTICATION_BUILT_IN');
define('gAUTHENTICATION_LDAP',       'AUTHENTICATION_LDAP');
define('gAUTHENTICATION_SHIBBOLETH', 'AUTHENTICATION_SHIBBOLETH');

define('gROLE_USER',           'ROLE_USER');
define('gROLE_FUNCTIONAL',     'ROLE_FUNCTIONAL');
define('gROLE_ORGANISATIONAL', 'ROLE_ORGANISATIONAL');

define('gPUBLIC',              'PUBLIC');
define('gCOSI_BUILT_IN_USERS', 'COSI_BUILT_IN_USERS');

// Roles based on affiliations obtained from the authentication services.
// =====================================================================
// Shibboleth attributes.
// ---------------------------------------------------------------------
define('gSHIB_AUTHENTICATED', 'SHIB_AUTHENTICATED');



// Add additional roles for shibboleth attributes here...

// LDAP attributes.
// ---------------------------------------------------------------------
define('gLDAP_AUTHENTICATED',     'LDAP_AUTHENTICATED');
define('gLDAP_STAFF',             'LDAP_STAFF');
define('gLDAP_STAFF_ANU_GENERAL', 'LDAP_STAFF_ANU_GENERAL');

define('gLDAP_STUDENT',           'LDAP_STUDENT');


// Add additional roles for LDAP attributes here...

// =====================================================================
// Functions
// -----------------------------------------------------------------------------
function getAllUserRoleIDs($role_id)
{
	// Everybody has the special PUBLIC role by default.
	$userRoleIDs = array(gPUBLIC);

	// If the user has logged in then get their additional roles.
	if( $role_id && (isRoleEnabled($role_id) || !isCosiUser($role_id)) )
	{
		// Starting with their user role.
		addRole($role_id, &$userRoleIDs);
		
		// If this user role uses built-in authentication then give thenm the special COSI_BUILT_IN_USERS role.
		if( getUserAuthenticationService($role_id) == gAUTHENTICATION_BUILT_IN )
		{
			addRole(gCOSI_BUILT_IN_USERS, &$userRoleIDs);
		}		
			
		// Add roles based on affiliations.
		// =====================================================================
		// Shibboleth attributes.
		// ---------------------------------------------------------------------
		if( haveShibbolethAttributes() ) 
		{
			addRole(gSHIB_AUTHENTICATED, &$userRoleIDs);
				
			// Add additional roles for shibboleth attributes here...
		}	
		// LDAP attributes.
		// ---------------------------------------------------------------------
		if( getSessionVar(sLDAP_ATTRIBUTES) )
		{
			addRole(gLDAP_AUTHENTICATED, &$userRoleIDs);
			
			$LDAPattribute = null;
			if( $LDAPattribute = getLDAPAttribute('affiliation') )
			{
				if( in_array('staff', $LDAPattribute) )
				{
					addRole(gLDAP_STAFF, &$userRoleIDs);
				}
				if( in_array('student', $LDAPattribute) )
				{
					addRole(gLDAP_STUDENT, &$userRoleIDs);
				}
				// Add additional roles for LDAP 'affiliation' here...
			}
			
			if( $LDAPattribute = getLDAPAttribute('anustafftype') )
			{
				if( in_array('General Staff', $LDAPattribute) )
				{
					addRole(gLDAP_STAFF_ANU_GENERAL, &$userRoleIDs);
				}
				// Add additional roles for LDAP 'anustafftype' here...
			}

			// Add additional roles for LDAP attributes here...
		}
	}
	return $userRoleIDs;
}

function addRole($role_id, $userRoleIDs)
{
	if( isRoleEnabled($role_id) )
	{
		// Add this role_id.
		$userRoleIDs[count($userRoleIDs)] = $role_id;
	
		// Recursively add all parent functional and organisational role_ids.
		addParentRoleIDs($role_id, &$userRoleIDs);
	}
}

function authenticate($role_id, $passphrase, &$userMessage, &$userName, &$authDomain)
{
	global $eLDAPHost;
	
	$successful = false;
	// Get the authentication service to use for this user.
	// Note that if this role is not enabled, or if their
	// identified authentication service is not enabled, then 
	// the authentication_service_id will be false.
	$authentication_service_id = getUserAuthenticationService($role_id);
	
	// If we didn't get an authentication service, but this user is unknown
	// to COSI, then attempt authentication with the LDAP Service.
	if( !$authentication_service_id && !isCosiUser($role_id) && isAuthenticationServiceEnabled(gAUTHENTICATION_LDAP) )
	{
		$authentication_service_id = gAUTHENTICATION_LDAP;
	}
	
	if( $authentication_service_id )
	{
		// This role is enabled and has identified an enabled authentication
		// service so...
		// apply the appropriate handler for authentication_service_id.
		switch( $authentication_service_id ) 
		{
			case gAUTHENTICATION_BUILT_IN:
				$successful = authenticateWithBuiltIn($role_id, $passphrase, $userMessage);
				if( $successful )
				{
					$userName = getRoleName($role_id);
					$authDomain = eAPP_ROOT;
				}
			    break;
			case gAUTHENTICATION_LDAP:
				$successful = authenticateWithLDAP($role_id, $passphrase, $userMessage);
				if( $successful )
				{
					$userName = getRoleName($role_id);
					$authDomain = $eLDAPHost;
					if( !$userName )
					{
						// They're not in cosi users so try to get the name from the LDAP service.
						// This code is dependant on the structure of the LDAP directory being used.
						if( getLDAPAttribute('cn') )
						{
							// Get the array of common names.
							$cn = getLDAPAttribute('cn');
							// Use the first common name in the array.
							$userName = $cn[0];
						}
					}
				}
				break;
			default:
				$userMessage = "LOGIN FAILED\nWrong service for user [01].\n";
				break;
		}
	}
	else
	{
		// Either this user role is not enabled, or its identified authentication service
		// is not enabled.
		$userMessage = "LOGIN FAILED\nService unavailable [11].\n";
		
		if( !isRoleEnabled($role_id) )
		{
			$userMessage = "LOGIN FAILED\nInvalid user ID/password [10].\n";
		}
	}
	
	if( $successful && getRoleName($role_id) )
	{
		updateLastLogin($role_id);
	}
	
	return $successful;
}

function isCosiUser($role_id)
{
	$cosiUser = false;
	if( getRoleName($role_id) )
	{
		// Then the role is configured in the COSI database.
		$cosiUser = true;
	}
	return $cosiUser;
}

function getShibbolethUserName()
{
	$userName = '';
	if( getShibbolethAttribute(eSHIBBOLETH_IDENTITY_HEADER) )
	{
		if( getShibbolethAttribute(eSHIBBOLETH_DISPLAYNAME_HEADER) )
		{
			$userName = getShibbolethAttribute(eSHIBBOLETH_DISPLAYNAME_HEADER);
		}
		if( !$userName && getShibbolethAttribute('mail') )
		{
			$userName = getShibbolethAttribute('mail');
		}
	}

	return $userName;
}

function getShibbolethAttribute($attributeKey)
{
	$attributeValue = '';
	if( isset($_SERVER[$attributeKey]) && $_SERVER[$attributeKey] != '' )
	{
		$attributeValue = $_SERVER[$attributeKey];
	}
	return $attributeValue;
}

function getLDAPAttribute($attributeKey)
{
	$attributeValue = '';
	if( getSessionVar(sLDAP_ATTRIBUTES) )
	{
		$attributes = getSessionVar(sLDAP_ATTRIBUTES);
		if( isset($attributes[$attributeKey]) )
		{
			$attributeValue = $attributes[$attributeKey];
		}
	}
	return $attributeValue;
}

function authenticateWithLDAP($role_id, $passphrase, &$userMessage)
{
	global $eLDAPHost;
	global $eLDAPPort;		
	global $eLDAPBaseDN;
	global $eLDAPuid;
	global $eLDAPDN;
	
	$validCredentials = false;
	
	if( $eLDAPBaseDN && $eLDAPuid )
	{
		$ldapDN = str_replace("@@ROLE_ID@@", escLDAPChars($role_id), $eLDAPDN);
		$ldapconn = ldap_connect($eLDAPHost, $eLDAPPort);
	
		if( $ldapconn && $passphrase != '' )
		{
			$ldapbind = ldap_bind($ldapconn, $ldapDN, $passphrase);
			if( $ldapbind )
			{
				$validCredentials = true;
			
				// Put this user's LDAP attributes into session to make them available
				// for use with authorisations and stuff.
				$ldapUserDN = str_replace("@@ROLE_ID@@", escLDAPChars($role_id), $eLDAPuid);
				$searchResult = ldap_search($ldapconn, $eLDAPBaseDN, $ldapUserDN);
				if( $searchResult && ldap_count_entries($ldapconn, $searchResult) === 1 )
				{
					$entry = ldap_first_entry($ldapconn, $searchResult);
					$attributes = ldap_get_attributes($ldapconn, $entry);
					setSessionVar(sLDAP_ATTRIBUTES, $attributes);
				}
			
				ldap_unbind($ldapconn);
			}
			else
			{
				$ldapErrorNumber = ldap_errno($ldapconn);
				if( $ldapErrorNumber === 49 ) // 0x31 = 49 is the LDAP error number for invalid credentials.
				{
					$userMessage = "LOGIN FAILED\nInvalid user ID/password [31,49].\n";
				}
				else
				{
					$userMessage = "LOGIN FAILED\nAuthentication service error [32,$ldapErrorNumber].\n";
				}
				/* 
				LDAP error numbers have the same meaning across implementations, though the messages vary.
 
				A list of implementation specific error messages can be obtained using:
				 	for ($i=-1; $i<100; $i++) {
				 		printf("Error $i: %s<br />\n", ldap_err2str($i));
				 	}

				Error numbers and messages are for troubleshooting, and should not be displayed to users.

				Example results:
					Error -1: Can't contact LDAP server
					Error 0: Success
					Error 1: Operations error
					Error 2: Protocol error
					Error 3: Time limit exceeded
					Error 4: Size limit exceeded
					Error 5: Compare False
					Error 6: Compare True
					Error 7: Authentication method not supported
					Error 8: Strong(er) authentication required
					Error 9: Partial results and referral received
					Error 10: Referral
					Error 11: Administrative limit exceeded
					Error 12: Critical extension is unavailable
					Error 13: Confidentiality required
					Error 14: SASL bind in progress
					Error 16: No such attribute
					Error 17: Undefined attribute type
					Error 18: Inappropriate matching
					Error 19: Constraint violation
					Error 20: Type or value exists
					Error 21: Invalid syntax
					Error 32: No such object
					Error 33: Alias problem
					Error 34: Invalid DN syntax
					Error 35: Entry is a leaf
					Error 36: Alias dereferencing problem
					Error 47: Proxy Authorization Failure
					Error 48: Inappropriate authentication
					Error 49: Invalid credentials
					Error 50: Insufficient access
					Error 51: Server is busy
					Error 52: Server is unavailable
					Error 53: Server is unwilling to perform
					Error 54: Loop detected
					Error 64: Naming violation
					Error 65: Object class violation
					Error 66: Operation not allowed on non-leaf
					Error 67: Operation not allowed on RDN
					Error 68: Already exists
					Error 69: Cannot modify object class
					Error 70: Results too large
					Error 71: Operation affects multiple DSAs
					Error 80: Internal (implementation specific) error
				 */
			}
		}
		else
		{
			$userMessage = "LOGIN FAILED\nAuthentication service error [30].\n";
		}
	}
	else
	{
		$userMessage = "LOGIN FAILED\nAuthentication service error [31].\n";
	}
	
	return $validCredentials;
}

function authenticateWithBuiltIn($role_id, $passphrase, &$userMessage)
{
	$validCredentials = validBuiltInCredentials($role_id, $passphrase, $userMessage);
	return $validCredentials;
}

function getShibbolethUserId()
{
	return getShibbolethAttribute(eSHIBBOLETH_IDENTITY_HEADER);
}

function haveShibbolethAttributes()
{
	$haveAttributes = false;
	if( getShibbolethAttribute('Shib-Identity-Provider') )
	{
		$haveAttributes = true;
	}

	return $haveAttributes;
}

function getThisActivityID()
{
	global $gActivities;
	$activity_id = null;
	
	foreach( $gActivities as $aKey => $aValue )
	{
		if( strpos(eAPP_ROOT.$aValue->path, getCurrentPath()) )
		{
			// we're done so...
			$activity_id = $aValue->id;
			break;
		}
	}
	return $activity_id;
}

function checkActivityAccess($activity_id)
{
	global $gActivities;
	
	if( !$activity_id || !hasActivity($activity_id) )
	{
		// End any session.
		endSession();
		
		// Capture the intended destination.
		$uri = '';
		if( isset($_SERVER['REQUEST_URI']) )
		{
			$uri = '?logout=logout&page='.urlencode($_SERVER['REQUEST_URI']);
		}
		
		// Redirect to login.
		responseRedirect(eAPP_ROOT.'login.php'.$uri);
	}
}

function hasMenu($menu_id)
{
	global $gMenus;
	global $gActivities;
	
	$hasMenu = false; 
	// Check for at least one activity access in children of this menu.
	// Iterate activities for this menu.
	foreach( $gActivities as $aKey => $aValue )
	{
		if( $aValue->menu_id == $menu_id && hasActivity($aValue->id) )
		{
			$hasMenu = true;
			break;
		}
	}
	// If we haven't got an authorised activity yet, then check all submenus.
	if( !$hasMenu )
	{
		checkMenu($menu_id, $hasMenu);
	}
	return $hasMenu;
}

function checkMenu($menu_id, &$hasMenu)
{
	global $gMenus;
	global $gActivities;
	
	foreach( $gMenus as $mKey => $mValue )
	{
		if( $mValue->parent_id == $menu_id )
		{
			// Iterate activities for this menu.
			foreach( $gActivities as $aKey => $aValue )
			{
				if( $aValue->menu_id == $mValue->id && hasActivity($aValue->id) )
				{
					$hasMenu = true;
					break;
				}
			}
			
			if( !$hasMenu )
			{
				// Recursively check submenus.
				checkMenu($mValue->id, $hasMenu);
			}
		}
	}
}

function hasActivity($activity_id)
{
	global $gActivities;
	
	$hasActivity = false;
	
	$activity = getObject($gActivities, $activity_id);
	if( $activity && !pathIsRelative($activity->path) )
	{
		// Then it's a link to an external page for which we don't
		// control access.
		$hasActivity = true;
	}
	else
	{
		$activityRoleIDs = getActivityRoleIDs($activity_id);
		$userRoleIDs = getAllUserRoleIDs(getSessionVar(sROLE_ID));
		
		if( $activityRoleIDs )
		{
			foreach( $activityRoleIDs as $value )
			{
				if( in_array($value['role_id'], $userRoleIDs, true) )
				{
					$hasActivity = true;
					break;
				}
			}
		}
	}
	return $hasActivity;
}

function hasRole($role_id)
{
	$hasRole = false;
	
	$userRoleIDs = getAllUserRoleIDs(getSessionVar(sROLE_ID));
	if( in_array($role_id, $userRoleIDs, true) )
	{
		$hasRole = true;
	}
	return $hasRole;
}

function getRelatedRoleIDs($role_id)
{
	$roleIDs = array();
	
	// Recursively add all parent functional and organisational role_ids.
	addParentRoleIDs($role_id, &$roleIDs);
	
	// Recursively add all child functional and organisational role_ids.
	addChildRoleIDs($role_id, &$roleIDs);
	
	return $roleIDs;
}

function addParentRoleIDs($role_id, &$roleIDs)
{
	$parentRoleIDs = getParentRoleIDs($role_id);
	if( $parentRoleIDs )
	{
		foreach( $parentRoleIDs as $value )
		{
			$roleIDs[count($roleIDs)] = $value['role_id'];
			addParentRoleIDs($value['role_id'], $roleIDs);
		}
	}
}

function addChildRoleIDs($role_id, &$roleIDs)
{
	$childRoleIDs = getChildRoleIDs($role_id);
	if( $childRoleIDs )
	{
		foreach( $childRoleIDs as $value )
		{
			$roleIDs[count($roleIDs)] = $value['role_id'];
			addChildRoleIDs($value['role_id'], $roleIDs);
		}
	}
}

function escLDAPChars($unsafeString)
{
	$reservedChars = array(
		chr(0x0A), // <LF> Line feed           0x0A
		chr(0x0D), // <CR> Carriage return     0x0D
		chr(0x22), // "    Double quote        0x22
		chr(0x23), // #    Number sign         0x23
		chr(0x2B), // +    Plus sign           0x2B
		chr(0x2C), // ,    Comma               0x2C
		chr(0x2F), // /    Forward slash       0x2F
		chr(0x3B), // ;    Semicolon           0x3B
		chr(0x3C), // <    Left angle bracket  0x3C
		chr(0x3D), // =    Equals sign         0x3D
		chr(0x3E), // >    Right angle bracket 0x3E
		chr(0x5C), // \    Backward slash      0x5C
		chr(0x2A)  // *    Asterisk            0x2A	
	);
	
	$unsafeChars = str_split($unsafeString);
	
	$safeString = '';
	foreach( $unsafeChars as $char )
	{
		if( in_array($char, $reservedChars) )
		{
			$safeString .= '\\';
		}
		$safeString .= $char;
	}
	
	return $safeString;
}

function getLoggedInUser()
{
	$user = "";
	if( getSessionVar(sNAME) != null )
	{
		$user = getSessionVar(sNAME);
	}
	if( getSessionVar(sROLE_ID) != null )
	{
		$user .= ' ('.getSessionVar(sROLE_ID).')';
	}
	return $user;
}
?>