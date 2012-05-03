<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/*
 * Authentication Methods
 */
define('gCOSI_AUTH_METHOD_BUILT_IN', 'AUTHENTICATION_BUILT_IN');
define('gCOSI_AUTH_METHOD_LDAP', 'AUTHENTICATION_LDAP');
define('gCOSI_AUTH_METHOD_SHIBBOLETH', 'AUTHENTICATION_SHIBBOLETH');

define('gCOSI_AUTH_ROLE_FUNCTIONAL', 'ROLE_FUNCTIONAL');
define('gCOSI_AUTH_ROLE_ORGANISATIONAL', 'ROLE_ORGANISATIONAL');


define('gCOSI_AUTH_LDAP_HOST', "ldaps://ldap.anu.edu.au");
define('gCOSI_AUTH_LDAP_PORT', 636); // 636 | 389
define('gCOSI_AUTH_LDAP_BASE_DN', "ou=People, o=anu.edu.au");
// The resource distinguished name.
// The string @@ROLE_ID@@ will be replace with the user role_id, and escaped
// for LDAP reserved characters before the bind is attempted.
define('gCOSI_AUTH_LDAP_UID', "uid=@@ROLE_ID@@"); 
define('gCOSI_AUTH_LDAP_DN', gCOSI_AUTH_LDAP_UID . ", " . gCOSI_AUTH_LDAP_BASE_DN); 



/* End of file constants.php */
/* Location: ./application/config/constants.php */