$Date: 2010-03-04 11:51:34 +1100 (Thu, 04 Mar 2010) $
$Revision: 322 $

REQUIREMENTS
================================================================================
Minimum
--------------------------------------------------------------------------------
- A web server (tested on Apache 2.2.8).
- PHP (tested on version 5.2.5) configured with pgsql, libxml 2.6.31, 
  libxslt 1.1.22, ldap and open ssl.
- PostgreSQL (tested on version 8.3.x).

Optional
--------------------------------------------------------------------------------
- SSL (recommended).
- An LDAP server.


INSTALLATION INSTRUCTIONS
================================================================================
1. Open the file cosi_setup.sql and follow the instructions in it.

2. Place the cosi source in the desired location on your web server. 
This location will be referred to as COSI_ROOT in these instructions. 
Make sure that the web server has read permissions on all of the files 
within this location.

3. Edit the file COSI_ROOT/_includes/_environment/database_env.php to set the 
location of the COSI database in eCNN_DBS_COSI (change from 'db.example.com').

4. Edit the file COSI_ROOT/_includes/_environment/application_env.php to set
appropriate values for the following (and other settings as required):

 eHOST.......................change from 'www.example.com'
 eROOT_DIR...................change from 'cosi' if required

 $eLDAPHost..................change from 'ldap.example.com'
 $eLDAPBaseDN................change from 'example.com' and as required
 $eLDAPuid...................change as required


 eINSTANCE_TITLE.............change from 'Place Holder Long Name'
 eINSTANCE_TITLE_SHORT.......change from 'Place Holder'

 eCONTACT_EMAIL..............change from 'feedback@example.com'

 eCOPYRIGHT_NOTICE...........change from 'Copyright © Place Holder Long Name (PHLN) 2007'

Set the path to the PHP error_log and create the file if necessary. This file 
will need to be writable by the web server.
 
 ini_set("error_log", "/usr/local/php5/bin/error-log");

5. Edit the file COSI_ROOT/versions.php to remove version information for the 
modules that are not installed with this instance.

6. Edit the file COSI_ROOT/about.php to provide information about the services
provided by this instance of the software.

7. Edit the file COSI_ROOT/_helpcontent/hc_login.php to provide information about 
the access to this instance of the software.

8. Point your web browser at the application and login as 'cosiadmin' with 
passphrase 'abc123'. Add roles as required, and change the passphrase of the
'cosiadmin' user. You can add a user role for yourself, give it the 
'COSI Administrator' functional role, and then remove the default 'cosiadmin' role.


THEME CUSTOMISATION
================================================================================
1. Modify the file COSI_ROOT/_images/_logos/logo_EXAMPLE.gif and rename it
appropriately. Make it go transparent to a white background.

2. Modify the file COSI_ROOT/_images/_layout/bg_marginLeft_EXAMPLE.gif 
and rename it appropriately. Alter the hue and saturation as desired.

3. Edit the file COSI_ROOT/_styles/layout.css. Alter the style for the class
.marginLeft_EXAMPLE (near the end of the file) rename it appropriately.
Set the url for the background of this class to to point to the file created
in step 2.

4. Alter the array $eThemes in the file COSI_ROOT/_includes/_environment/application_env.php
to utilise the changes made in the previous steps and to set a colour for the
title text that will match the logo and margin colour.

