/*******************************************************************************
$Date: 2010-09-24 15:33:17 +1000 (Fri, 24 Sep 2010) $
$Revision: 509 $
*******************************************************************************/
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = dba, pg_catalog;



INSERT INTO tbl_roles (role_id, role_type_id, name) VALUES ('ORCA_ADMIN', 'ROLE_FUNCTIONAL', 'ORCA Administrator');
INSERT INTO tbl_roles (role_id, role_type_id, name) VALUES ('ORCA_SOURCE_ADMIN', 'ROLE_FUNCTIONAL', 'ORCA Data Source Administrator');
INSERT INTO tbl_roles (role_id, role_type_id, name) VALUES ('ORCA_USER', 'ROLE_FUNCTIONAL', 'ORCA Users');


INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_INDEX');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SEARCH');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_REGISTRY_INDEX');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_VIEW');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_WEB_SERVICES');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECTS');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_DATA_SOURCES');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECT_GROUPS');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_OAI_DATA_PROVIDER');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_PUT_HARVEST_DATA');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_OPENSEARCH_DESCRIPTION');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_OPENSEARCH');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECT');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECT_KML');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECTS_KML');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_SERVICE_GET_REGISTRY_SEARCH_XHTML');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_DATA_SOURCE_LIST');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_DATA_SOURCE_ADD');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_DATA_SOURCE_VIEW');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_DATA_SOURCE_EDIT');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_DATA_SOURCE_DELETE');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_REGISTRY_OBJECT_ADD');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_REGISTRY_OBJECT_EDIT');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_REGISTRY_OBJECT_DELETE');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_LIST_COLLECTIONS');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_VIEW_COLLECTION');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_ADD_COLLECTION');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_EDIT_COLLECTION');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_DELETE_COLLECTION');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_VIEW_PUBLISHER');
INSERT INTO tbl_activities (activity_id) VALUES ('aORCA_USER_EDIT_PUBLISHER');



INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_INDEX');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SEARCH');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_REGISTRY_INDEX');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_VIEW');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_WEB_SERVICES');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECTS');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_DATA_SOURCES');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECT_GROUPS');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_OAI_DATA_PROVIDER');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_PUT_HARVEST_DATA');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_OPENSEARCH_DESCRIPTION');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_OPENSEARCH');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECT_KML');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECTS_KML');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_SEARCH_XHTML');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_LIST');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_ADD');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_VIEW');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_EDIT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_DELETE');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADD');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_EDIT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_DELETE');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_LIST');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_VIEW');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_EDIT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADD');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_EDIT');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_DELETE');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_LIST_COLLECTIONS');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_VIEW_COLLECTION');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_ADD_COLLECTION');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_EDIT_COLLECTION');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_DELETE_COLLECTION');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_VIEW_PUBLISHER');
INSERT INTO tbl_role_activities (role_id, activity_id) VALUES ('ORCA_USER', 'aORCA_USER_EDIT_PUBLISHER');

/*
-------------------------------------------------------------------------------------------------
--- PIDS Administration (if managing own PIDS service)
-------------------------------------------------------------------------------------------------
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('ORCA_PIDS_ADMIN', 'ROLE_FUNCTIONAL', 'ORCA PIDS Administrator');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('mORCA_PIDS_IP_LIST');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('mORCA_PIDS_IP_ADD');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('mORCA_PIDS_IP_DELETE');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_ADD');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_LIST');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_DELETE');
-- END of PIDS Administration queries
*/
