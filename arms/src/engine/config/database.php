<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A registry table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By registry there is only one group (the 'registry' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'registry';
$active_record = TRUE;

$db['registry']['hostname'] = 'localhost';
$db['registry']['username'] = 'webuser';
$db['registry']['password'] = 'q7fb94z';
$db['registry']['database'] = 'dbs_registry';
$db['registry']['dbdriver'] = 'mysql';
$db['registry']['dbprefix'] = '';
$db['registry']['pconnect'] = TRUE;
$db['registry']['db_debug'] = TRUE;
$db['registry']['cache_on'] = FALSE;
$db['registry']['cachedir'] = '';
$db['registry']['char_set'] = 'utf8';
$db['registry']['dbcollat'] = 'utf8_general_ci';
$db['registry']['swap_pre'] = '';
$db['registry']['autoinit'] = TRUE;
$db['registry']['stricton'] = FALSE;
$db['registry']['save_queries'] = FALSE; 


$db['cosi']['hostname'] = 'ands3.anu.edu.au';
$db['cosi']['username'] = 'webuser';
$db['cosi']['password'] = '';
$db['cosi']['database'] = 'archive_dbs_cosi_production';
$db['cosi']['dbdriver'] = 'postgre';
$db['cosi']['dbprefix'] = '';
$db['cosi']['pconnect'] = TRUE;
$db['cosi']['db_debug'] = TRUE;
$db['cosi']['cache_on'] = FALSE;
$db['cosi']['cachedir'] = '';
$db['cosi']['char_set'] = 'utf8';
$db['cosi']['dbcollat'] = 'utf8_general_ci';
$db['cosi']['swap_pre'] = '';
$db['cosi']['autoinit'] = FALSE;
$db['cosi']['stricton'] = FALSE;
$db['cosi']['save_queries'] = FALSE; 


$db['vocabs']['hostname'] = 'localhost';
$db['vocabs']['username'] = 'webuser';
$db['vocabs']['password'] = 'q7fb94z';
$db['vocabs']['database'] = 'dbs_vocabs';
$db['vocabs']['dbdriver'] = 'mysql';
$db['vocabs']['dbprefix'] = '';
$db['vocabs']['pconnect'] = TRUE;
$db['vocabs']['db_debug'] = TRUE;
$db['vocabs']['cache_on'] = FALSE;
$db['vocabs']['cachedir'] = '';
$db['vocabs']['char_set'] = 'utf8';
$db['vocabs']['dbcollat'] = 'utf8_general_ci';
$db['vocabs']['swap_pre'] = '';
$db['vocabs']['autoinit'] = TRUE;
$db['vocabs']['stricton'] = FALSE;
$db['vocabs']['save_queries'] = FALSE; 

/* End of file database.php */
/* Location: ./application/config/database.php */