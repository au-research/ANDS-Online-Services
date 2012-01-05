/*******************************************************************************
$Date: 2009-08-12 12:24:07 +1000 (Wed, 12 Aug 2009) $
$Revision: 51 $
*******************************************************************************/
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = dba, pg_catalog;


INSERT INTO tbl_roles (role_id, role_type_id, name) VALUES ('PIDS_USER', 'ROLE_FUNCTIONAL', 'PIDS Users');


INSERT INTO tbl_activities (activity_id) VALUES ('aPIDS_LIST');
INSERT INTO tbl_activities (activity_id) VALUES ('aPIDS_CREATE');
INSERT INTO tbl_activities (activity_id) VALUES ('aPIDS_VIEW');
INSERT INTO tbl_activities (activity_id) VALUES ('aPIDS_ADD');
INSERT INTO tbl_activities (activity_id) VALUES ('aPIDS_EDIT');
INSERT INTO tbl_activities (activity_id) VALUES ('aPIDS_DELETE');


INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PIDS_USER', 'aPIDS_LIST');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PIDS_USER', 'aPIDS_CREATE');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PIDS_USER', 'aPIDS_VIEW');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PIDS_USER', 'aPIDS_ADD');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PIDS_USER', 'aPIDS_EDIT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PIDS_USER', 'aPIDS_DELETE');


