$Date: 2010-09-24 15:33:17 +1000 (Fri, 24 Sep 2010) $
$Revision: 509 $

REQUIREMENTS
================================================================================
Minimum
--------------------------------------------------------------------------------
- An instance of the COSI v1.2.2 framework.
- PostgreSQL 8.3.x.

Optional
--------------------------------------------------------------------------------
To support scheduled harvest and harvest via OAI-PMH you will need an instance
of the Harvester v1.0 service.


INSTALLATION INSTRUCTIONS
================================================================================
1. Insert the ORCA roles and activites into the appropriate COSI database
using orca-dbs_cosi.sql, then build the ORCA database using orca_setup.sql.

2. Place the orca source into a folder named 'orca' in the root of 
of the COSI install on the web server (COSI_ROOT). Make sure that the web server 
has read permissions on all of the files within this location.

3. Edit the file COSI_ROOT/_includes/_environment/database_env.php, adding the  
code contained in the file orca-database_env.php. Edit further to set 
the location of the ORCA database in eCNN_DBS_ORCA (change from 'db.example.com').

4. Edit the file COSI_ROOT/_includes/_configuration/application_config.php, 
adding the code contained in the file orca_application_config_excerpt.txt 
(co-located with this file) at the appropriate location--just before the
'// Tidy up' comment near the end of the file. Note that the menus and activities
are rendered in the user interface in the order that they are read from this file.

5. If you have an instance of Harvester Service that you wish to use with this 
instance of ORCA, then edit the file COSI_ROOT/orca/orca_init.php to set appropriate
values for gORCA_HARVESTER_BASE_URI and gORCA_HARVESTER_IP.

6. If you intend to use this instance of ORCA to administer a PIDS Service, see the
commented sections of orca-application_config.txt and orca-dbs_cosi.txt for instructions.

7. Login to the application and add the 'ORCA Administrator' and 
'ORCA Data Source Administrator' functional roles to the required user 
functional roles. Note that members of 'ORCA Data Source Administrator' will
be restricted to viewing and accessing Data Sources identified as being owned by 
the organisational roles of which they are a also member (and will not be able
to add or delete data sources).

8. Update the version information for this application in the hosting COSI-Framework.