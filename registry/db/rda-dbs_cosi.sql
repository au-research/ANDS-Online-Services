/*******************************************************************************
$Date: 2010-05-17 15:26:46 +1000 (Mon, 17 May 2010) $
$Revision: 368 $
*******************************************************************************/

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = dba, pg_catalog;


INSERT INTO tbl_activities (activity_id) VALUES ('aRDA_HOME');
INSERT INTO tbl_activities (activity_id) VALUES ('aRDA_ABOUT');
INSERT INTO tbl_activities (activity_id) VALUES ('aRDA_DISCLAIMER');
INSERT INTO tbl_activities (activity_id) VALUES ('aRDA_HELP');
INSERT INTO tbl_activities (activity_id) VALUES ('aRDA_VIEW');
INSERT INTO tbl_activities (activity_id) VALUES ('aRDA_LIST');


INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aRDA_HOME');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aRDA_ABOUT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aRDA_DISCLAIMER');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aRDA_HELP');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aRDA_VIEW');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aRDA_LIST');



