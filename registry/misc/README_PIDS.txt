$Date: 2009-08-11 16:35:30 +1000 (Tue, 11 Aug 2009) $
$Revision: 37 $

REQUIREMENTS
================================================================================
Minumium
--------------------------------------------------------------------------------
- An instance of the COSI v1.2.2 framework.
- An instance of the Persistent Identifiers v1.0 service.

INSTALLATION INSTRUCTIONS
================================================================================
1. Insert the PIDS Self-Service roles and activites into the appropriate COSI database
using pids-dbs_cosi.sql.

2. Place the pids source into a folder named 'pids' in the root 
of the COSI install on the web server (COSI_ROOT). Make sure that the web server 
has read permissions on all of the files within this location.

3. Edit the file COSI_ROOT/_includes/_configuration/application_config.php, 
adding the code contained in the file pids_application_config.php  at the 
appropriate location. Note that the menus and activities are rendered in the user 
interface in the order that they are read from this file.

5. Edit the file COSI_ROOT/pids/pids_init.php to set appropriate
values for gPIDS_SERVICE_BASE_URI and gPIDS_APP_ID (as issued to you by the 
administrator of the service).

6. Login to the application and add the 'PIDS Users' functional role to the required 
functional roles (generally the authentication roles such as LDAP Authenticated users).