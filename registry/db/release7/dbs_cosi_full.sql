/*
Copyright 2012 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

--
-- COSI Database installation script (full)
-- Release version: 7.0 
-- Support/enquiries: services@ands.org.au
-- Created: 18/01/2012 (ben.greenwood@ands.org.au)
--
-- NOTE: This is a full installation script and should only be run
--       against a freshly created database. If you are upgrading from
--       a previous version of the registry then you should use the 
--       incremental update scripts in registry/db/
--

-- If you haven't yet created the database:
---   CREATE USER webuser;
---   CREATE USER dba;
---   CREATE DATABASE dbs_cosi ENCODING = 'UTF8' LC_COLLATE = 'en_AU.UTF-8' LC_CTYPE = 'en_AU.UTF-8';
---   ALTER DATABASE dbs_cosi OWNER TO dba;
---   GRANT CONNECT, TEMPORARY ON DATABASE dbs_cosi TO webuser;
---   GRANT ALL ON DATABASE dbs_cosi TO dba;
---   \connect dbs_cosi

-------------
-- SETUP THE DATABASE
-------------
-- Connection-specific settings
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

-- Create the default schema (dba)
CREATE SCHEMA dba;
ALTER SCHEMA dba OWNER TO dba;
DROP SCHEMA IF EXISTS public; 
SET search_path = dba;

-- Check that the plpgsql extension is loaded (requires postgres user)
CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';

-------------
-- CREATE THE DEFAULT TABLES
-------------
CREATE TABLE tbl_role_types (
    role_type_id character(20) NOT NULL,
    name character varying(64) NOT NULL
);
ALTER TABLE dba.tbl_role_types OWNER TO dba;

INSERT INTO tbl_role_types VALUES ('ROLE_USER           ', 'User');
INSERT INTO tbl_role_types VALUES ('ROLE_FUNCTIONAL     ', 'Functional');
INSERT INTO tbl_role_types VALUES ('ROLE_ORGANISATIONAL ', 'Organisational');

CREATE TABLE tbl_authentication_services (
    authentication_service_id character(32) NOT NULL,
    name character varying(64) NOT NULL,
    description text,
    enabled boolean DEFAULT false NOT NULL
);
ALTER TABLE dba.tbl_authentication_services OWNER TO dba;

INSERT INTO tbl_authentication_services VALUES ('AUTHENTICATION_BUILT_IN         ', 'Built-in', '', true);
INSERT INTO tbl_authentication_services VALUES ('AUTHENTICATION_LDAP             ', 'LDAP', '', true);
INSERT INTO tbl_authentication_services VALUES ('AUTHENTICATION_SHIBBOLETH       ', 'Shibboleth', '', true);

CREATE TABLE tbl_authentication_built_in (
    role_id character varying(255) NOT NULL,
    passphrase_sha1 character varying(40) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);
ALTER TABLE dba.tbl_authentication_built_in OWNER TO dba;
-- default cosiadmin user with password abc123
INSERT INTO tbl_authentication_built_in (role_id, passphrase_sha1, created_who, modified_who) VALUES ('cosiadmin', '6367c48dd193d56ea7b0baad25b19455e529f5ee', 'SYSTEM', 'SYSTEM');

CREATE TABLE tbl_activities (
    activity_id character varying(64) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);
ALTER TABLE dba.tbl_activities OWNER TO dba;

INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_ABOUT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_HELP','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_INDEX','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_VERSIONS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_LOGIN','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_CHANGE_BUILT_IN_PASS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_ROLE_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_ROLE_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_ROLE_VIEW','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_ROLE_EDIT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_ROLE_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aEXAMPLE_STYLE_SAMPLER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aEXAMPLE_FORM_STYLE_SAMPLER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aEXAMPLE_CHART_SAMPLER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aEXAMPLE_THEME_ONE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aEXAMPLE_THEME_TWO','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_HELP','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_SEARCH','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_PIDS_IP_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_PIDS_IP_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_PIDS_IP_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_PIDS_IP_MANAGE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('mORCA_PIDS_IP_MANAGE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_EXPORT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('mORCA_PIDS_IP_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('mORCA_PIDS_IP_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('mORCA_PIDS_IP_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_FETCH_ELEMENT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_LIST_DATA_SOURCES','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_MANAGE_RECORDS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_ADD_COLLECTION','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_ADD_ACTIVITY','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_ADD_PARTY','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_ADD_SERVICE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_VIEW_DRAFT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aCOSI_RESET_BUILT_IN_PASS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_STATISTICS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_STATISTICS_VIEW','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_PUT_NLA_DATA','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DOIS_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DOIS_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DOIS_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aPIDS_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aPIDS_CREATE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aPIDS_VIEW','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aPIDS_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aPIDS_EDIT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aPIDS_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_HOME','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_ABOUT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_DISCLAIMER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_VIEW','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aRDA_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_INDEX','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SEARCH','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_INDEX','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_VIEW','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_WEB_SERVICES','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECTS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_DATA_SOURCES','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECT_GROUPS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_OAI_DATA_PROVIDER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_PUT_HARVEST_DATA','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_OPENSEARCH_DESCRIPTION','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_OPENSEARCH','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECT_KML','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_REGISTRY_OBJECTS_KML','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_SERVICE_GET_REGISTRY_SEARCH_XHTML','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_LIST','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_VIEW','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_EDIT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADD','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_EDIT','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_DELETE','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_LIST_COLLECTIONS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_VIEW_COLLECTION','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_ADD_COLLECTION','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_EDIT_COLLECTION','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_DELETE_COLLECTION','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_VIEW_PUBLISHER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_USER_EDIT_PUBLISHER','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_HISTORY','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_MY_RECORDS','SYSTEM','SYSTEM');
INSERT INTO tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_QUALITY_CHECK','SYSTEM','SYSTEM');

CREATE TABLE tbl_roles (
    role_id character varying(255) NOT NULL,
    role_type_id character(20) NOT NULL,
    name character varying(255) NOT NULL,
    authentication_service_id character(32),
    enabled boolean DEFAULT true NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    last_login timestamp(6) with time zone
);
ALTER TABLE dba.tbl_roles OWNER TO dba;

INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('cosiadmin', 'ROLE_USER           ', 'Default COSI Administrator', 'AUTHENTICATION_BUILT_IN         ', true, 'SYSTEM', 'SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('PUBLIC', 'ROLE_FUNCTIONAL     ', 'Public', NULL, true, 'SYSTEM', 'SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('COSI_BUILT_IN_USERS', 'ROLE_FUNCTIONAL     ', 'COSI Built-in Authentication User', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('COSI_ADMIN', 'ROLE_FUNCTIONAL     ', 'COSI Administrator', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('LDAP_AUTHENTICATED', 'ROLE_FUNCTIONAL     ', 'LDAP Authenticated Users', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('LDAP_STAFF', 'ROLE_FUNCTIONAL     ', 'LDAP Affiliation - STAFF', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('LDAP_STUDENT', 'ROLE_FUNCTIONAL     ', 'LDAP Affiliation  - STUDENT', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('SHIB_AUTHENTICATED', 'ROLE_FUNCTIONAL     ', 'Shibboleth Authenticated Users', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('PIDS_USER', 'ROLE_FUNCTIONAL     ', 'PIDS Users', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_USER', 'ROLE_FUNCTIONAL     ', 'ORCA Users', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_STATS_ADMIN', 'ROLE_FUNCTIONAL     ', 'ORCA Statistics Administrtor', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_ADMIN', 'ROLE_FUNCTIONAL     ', 'ORCA Administrator', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_SOURCE_ADMIN', 'ROLE_FUNCTIONAL     ', 'ORCA Data Source Administrator', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_QUALITY_ASSESSOR', 'ROLE_FUNCTIONAL     ', 'ANDS Quality Assessor', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_CLIENT_LIAISON', 'ROLE_FUNCTIONAL     ', 'ANDS Client Liaison', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_PIDS_ADMIN', 'ROLE_FUNCTIONAL     ', 'ORCA IMD Service Administrator', NULL, true,'SYSTEM','SYSTEM', NULL);
INSERT INTO tbl_roles (role_id, role_type_id, name, authentication_service_id, enabled, created_who, modified_who, last_login) VALUES ('ORCA_DOIS_ADMIN', 'ROLE_FUNCTIONAL     ', 'ORCA DOIS Administrator', NULL, true,'SYSTEM','SYSTEM', NULL);

CREATE TABLE tbl_role_activities (
    role_id character varying(255) NOT NULL,
    activity_id character varying(64) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);
ALTER TABLE dba.tbl_role_activities OWNER TO dba;

INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aCOSI_ABOUT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aCOSI_HELP', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aCOSI_INDEX', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aCOSI_VERSIONS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aCOSI_LOGIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_BUILT_IN_USERS', 'aCOSI_CHANGE_BUILT_IN_PASS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aEXAMPLE_STYLE_SAMPLER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aEXAMPLE_FORM_STYLE_SAMPLER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aEXAMPLE_CHART_SAMPLER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aEXAMPLE_THEME_ONE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aEXAMPLE_THEME_TWO', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_HELP', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'aORCA_PIDS_IP_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'aORCA_PIDS_IP_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_MANAGE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'mORCA_PIDS_IP_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_EXPORT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_LIST_DATA_SOURCES', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_MANAGE_RECORDS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_ACTIVITY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_PARTY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_SERVICE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_PUT_NLA_DATA', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_MANAGE_RECORDS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_ACTIVITY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_PARTY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_SERVICE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_VIEW_DRAFT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_VIEW_DRAFT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_FETCH_ELEMENT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_FETCH_ELEMENT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PIDS_USER', 'aPIDS_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PIDS_USER', 'aPIDS_CREATE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PIDS_USER', 'aPIDS_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PIDS_USER', 'aPIDS_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PIDS_USER', 'aPIDS_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PIDS_USER', 'aPIDS_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_HOME', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_ABOUT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_DISCLAIMER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_INDEX', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SEARCH', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_REGISTRY_INDEX', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_WEB_SERVICES', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECTS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_DATA_SOURCES', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECT_GROUPS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_OAI_DATA_PROVIDER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_PUT_HARVEST_DATA', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_OPENSEARCH_DESCRIPTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_OPENSEARCH', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECT_KML', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_OBJECTS_KML', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aORCA_SERVICE_GET_REGISTRY_SEARCH_XHTML', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_DATA_SOURCE_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'aORCA_REGISTRY_OBJECT_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_LIST_COLLECTIONS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_VIEW_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_ADD_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_EDIT_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_DELETE_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_VIEW_PUBLISHER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_USER', 'aORCA_USER_EDIT_PUBLISHER', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC', 'aRDA_SEARCH', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'aORCA_PIDS_IP_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'aORCA_PIDS_IP_MANAGE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'aCOSI_RESET_BUILT_IN_PASS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_STATS_ADMIN', 'aORCA_STATISTICS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_STATS_ADMIN', 'aORCA_STATISTICS_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_DOIS_ADMIN', 'aORCA_DOIS_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_DOIS_ADMIN', 'aORCA_DOIS_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_DOIS_ADMIN', 'aORCA_DOIS_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_HISTORY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_DATA_SOURCE_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_DATA_SOURCE_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_DATA_SOURCE_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_FETCH_ELEMENT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADD', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_ACTIVITY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_COLLECTION', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_PARTY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_ADD_SERVICE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_MANAGE_RECORDS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_MY_RECORDS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_DATA_SOURCE_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_DATA_SOURCE_LIST', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_DATA_SOURCE_VIEW', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_FETCH_ELEMENT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_REGISTRY_OBJECT_DELETE', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_REGISTRY_OBJECT_EDIT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_REGISTRY_OBJECT_ADMIN_MY_RECORDS', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_REGISTRY_OBJECT_ADMIN_VIEW_DRAFT', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_REGISTRY_OBJECT_HISTORY', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'aORCA_DATA_SOURCE_QUALITY_CHECK', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN', 'aORCA_DATA_SOURCE_QUALITY_CHECK', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'aORCA_DATA_SOURCE_QUALITY_CHECK', 'SYSTEM', 'SYSTEM');

CREATE TABLE tbl_role_relations (
    parent_role_id character varying(255) NOT NULL,
    child_role_id character varying(255) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);
ALTER TABLE dba.tbl_role_relations OWNER TO dba;

INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('COSI_ADMIN', 'cosiadmin', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_DOIS_ADMIN', 'COSI_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_STATS_ADMIN', 'COSI_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_ADMIN', 'COSI_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_PIDS_ADMIN', 'COSI_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('PIDS_USER', 'COSI_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('PIDS_USER', 'SHIB_AUTHENTICATED', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_USER', 'SHIB_AUTHENTICATED', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_QUALITY_ASSESSOR', 'ORCA_ADMIN', 'SYSTEM', 'SYSTEM');
INSERT INTO tbl_role_relations (parent_role_id, child_role_id, created_who, modified_who) VALUES ('ORCA_CLIENT_LIAISON', 'ORCA_QUALITY_ASSESSOR', 'SYSTEM', 'SYSTEM');


-------------
-- DATABASE FUNCTIONS
-------------

CREATE FUNCTION udf_authenticate_with_built_in(_role_id character varying, _passphrase_sha1 character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$ 
DECLARE
	_valid_credentials BOOLEAN := FALSE;
	_count INT := 0;
BEGIN
	SELECT INTO _count
	 COUNT(role_id)
	FROM dba.tbl_authentication_built_in ABI
	WHERE ABI.role_id = _role_id
	  AND ABI.passphrase_sha1 = _passphrase_sha1;

	IF _count = 1 THEN
	 _valid_credentials = TRUE;
	END IF;
	RETURN _valid_credentials;
END;
$$;
ALTER FUNCTION dba.udf_authenticate_with_built_in(_role_id character varying, _passphrase_sha1 character varying) OWNER TO dba;

CREATE FUNCTION udf_delete_role(_role_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 
DELETE FROM dba.tbl_authentication_built_in WHERE role_id = $1;
DELETE FROM dba.tbl_role_activities WHERE role_id = $1;
DELETE FROM dba.tbl_role_relations WHERE parent_role_id = $1 OR child_role_id = $1;
DELETE FROM dba.tbl_roles WHERE role_id = $1;

$_$;

ALTER FUNCTION dba.udf_delete_role(_role_id character varying) OWNER TO dba;

CREATE FUNCTION udf_delete_role_activity(_role_id character varying, _activity_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_role_activities
WHERE role_id = $1
  AND activity_id = $2
;
$_$;

ALTER FUNCTION dba.udf_delete_role_activity(_role_id character varying, _activity_id character varying) OWNER TO dba;


CREATE FUNCTION udf_delete_role_relation(_child_role_id character varying, _parent_role_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_role_relations
WHERE child_role_id = $1
  AND parent_role_id = $2
;
$_$;

ALTER FUNCTION dba.udf_delete_role_relation(_child_role_id character varying, _parent_role_id character varying) OWNER TO dba;

SET default_tablespace = '';

SET default_with_oids = false;


CREATE FUNCTION udf_get_activity_role_ids(_activity_id character varying) RETURNS SETOF tbl_role_activities
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.tbl_role_activities
WHERE activity_id = $1;

$_$;

ALTER FUNCTION dba.udf_get_activity_role_ids(_activity_id character varying) OWNER TO dba;

CREATE FUNCTION udf_get_authentication_services() RETURNS SETOF tbl_authentication_services
    LANGUAGE sql
    AS $$ 
SELECT 
 *
FROM dba.tbl_authentication_services
;

$$;

ALTER FUNCTION dba.udf_get_authentication_services() OWNER TO dba;

CREATE FUNCTION udf_get_child_role_ids(_parent_role_id character varying) RETURNS SETOF tbl_role_relations
    LANGUAGE sql
    AS $_$ 

SELECT 
 RR.*
FROM dba.tbl_role_relations RR
JOIN dba.tbl_roles R
  ON RR.child_role_id=R.role_id
WHERE RR.parent_role_id = $1
  AND R.enabled=TRUE; -- Don't return disabled roles

$_$;

ALTER FUNCTION dba.udf_get_child_role_ids(_parent_role_id character varying) OWNER TO dba;


CREATE FUNCTION udf_get_data_source_admin_count(_date_filter character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$
	SELECT 
COUNT(*) 
FROM dba.tbl_role_relations 
-- THIS NEEDS TO BE REMOVED...HARDCODING UIDS MAKES JEBUS KILL KITTENS :-(
WHERE parent_role_id='ORCA_SOURCE_ADMIN' 
AND child_role_id NOT LIKE '%@ands.org.au' 
AND child_role_id <> 'COSI_ADMIN'
AND child_role_id <> 'u4187959'
AND child_role_id <> 'u4958094'
AND child_role_id <> 'u4552016'
AND created_when <= CAST($1 AS timestamp with time zone);$_$;

ALTER FUNCTION dba.udf_get_data_source_admin_count(_date_filter character varying) OWNER TO dba;

CREATE VIEW vw_roles AS
    SELECT r.role_id, r.role_type_id, rt.name AS role_type_name, r.name AS role_name, r.authentication_service_id, a.name AS authentication_service_name, a.enabled AS authentication_service_enabled, r.enabled AS role_enabled, r.created_when, r.created_who, r.modified_when, r.modified_who, r.last_login FROM ((tbl_roles r JOIN tbl_role_types rt ON ((rt.role_type_id = r.role_type_id))) LEFT JOIN tbl_authentication_services a ON ((a.authentication_service_id = r.authentication_service_id)));

ALTER TABLE dba.vw_roles OWNER TO dba;

CREATE FUNCTION udf_get_organisational_roles() RETURNS SETOF vw_roles
    LANGUAGE sql
    AS $$ 
SELECT 
 *
FROM dba.vw_roles
WHERE role_type_id = 'ROLE_ORGANISATIONAL'
ORDER BY role_name ASC
;

$$;

ALTER FUNCTION dba.udf_get_organisational_roles() OWNER TO dba;

CREATE FUNCTION udf_get_parent_role_ids(_child_role_id character varying) RETURNS SETOF tbl_role_relations
    LANGUAGE sql
    AS $_$ 

SELECT 
 RR.*
FROM dba.tbl_role_relations RR
JOIN dba.tbl_roles R
  ON RR.parent_role_id=R.role_id
WHERE RR.child_role_id = $1
  AND R.enabled=TRUE; -- Don't return disabled roles

$_$;

ALTER FUNCTION dba.udf_get_parent_role_ids(_child_role_id character varying) OWNER TO dba;



CREATE FUNCTION udf_get_role_activities(_role_id character varying) RETURNS SETOF tbl_role_activities
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.tbl_role_activities
WHERE role_id = $1
ORDER BY activity_id ASC
;

$_$;

ALTER FUNCTION dba.udf_get_role_activities(_role_id character varying) OWNER TO dba;

CREATE FUNCTION udf_get_role_activity_add_list(_role_id character varying) RETURNS SETOF tbl_activities
    LANGUAGE sql
    AS $_$ 

SELECT
 *
FROM dba.tbl_activities
WHERE activity_id NOT IN
(
  SELECT activity_id
  FROM dba.tbl_role_activities
  WHERE role_id = $1
     OR role_id = 'PUBLIC'
     OR role_id = 'COSI_BUILT_IN_USERS'
)
-- Only functional roles can have activities.
AND $1 IN
(
  SELECT role_id
  FROM dba.tbl_roles
  WHERE role_type_id = 'ROLE_FUNCTIONAL'
)
-- Exclude the special activity for password changes
AND activity_id <> 'aCOSI_CHANGE_BUILT_IN_PASS'
ORDER BY activity_id ASC
;
$_$;

ALTER FUNCTION dba.udf_get_role_activity_add_list(_role_id character varying) OWNER TO dba;


CREATE FUNCTION udf_get_role_auth_service_id(_role_id character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$ 

DECLARE
	_auth_service_id VARCHAR := '';
BEGIN
	SELECT INTO _auth_service_id
	 R.authentication_service_id
	FROM dba.tbl_roles R
	JOIN dba.tbl_authentication_services A
	  ON R.authentication_service_id=A.authentication_service_id
	WHERE R.role_id = _role_id
	  AND A.enabled
	  AND R.enabled;
	RETURN _auth_service_id;
END;
$$;

ALTER FUNCTION dba.udf_get_role_auth_service_id(_role_id character varying) OWNER TO dba;


CREATE FUNCTION udf_get_role_enabled(_role_id character varying) RETURNS boolean
    LANGUAGE sql
    AS $_$ 

SELECT
 enabled
FROM dba.tbl_roles
WHERE role_id = $1
;
$_$;

ALTER FUNCTION dba.udf_get_role_enabled(_role_id character varying) OWNER TO dba;



CREATE FUNCTION udf_get_role_name(_role_id character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$ 

DECLARE
	_name VARCHAR := '';
BEGIN
	SELECT INTO _name
	 name
	FROM dba.tbl_roles
	WHERE role_id = _role_id;
	RETURN _name;
END;
$$;

ALTER FUNCTION dba.udf_get_role_name(_role_id character varying) OWNER TO dba;

CREATE FUNCTION udf_get_role_relation_add_list(_role_id character varying, _parent_role_type_id character) RETURNS SETOF tbl_roles
    LANGUAGE sql
    AS $_$ 

SELECT
 *
FROM dba.tbl_roles
WHERE role_type_id = $2
AND role_id NOT IN
(
  SELECT parent_role_id
  FROM dba.tbl_role_relations
  WHERE child_role_id = $1
)
AND role_id <> $1
AND role_id <> 'PUBLIC'
AND role_id <> 'COSI_BUILT_IN_USERS'
AND enabled = true
ORDER BY role_id ASC
;
$_$;

ALTER FUNCTION dba.udf_get_role_relation_add_list(_role_id character varying, _parent_role_type_id character) OWNER TO dba;

CREATE VIEW vw_role_relations AS
    SELECT rr.child_role_id, rr.parent_role_id, r.role_type_id AS parent_role_type_id, r.name AS parent_role_name, rr.created_when, rr.created_who FROM (tbl_roles r JOIN tbl_role_relations rr ON (((rr.parent_role_id)::text = (r.role_id)::text)));

ALTER TABLE dba.vw_role_relations OWNER TO dba;

CREATE FUNCTION udf_get_role_relations(_role_id character varying, _parent_role_type_id character) RETURNS SETOF vw_role_relations
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.vw_role_relations
WHERE child_role_id = $1
  AND parent_role_type_id = $2
ORDER BY parent_role_id ASC
;
$_$;

ALTER FUNCTION dba.udf_get_role_relations(_role_id character varying, _parent_role_type_id character) OWNER TO dba;


CREATE FUNCTION udf_get_role_types() RETURNS SETOF tbl_role_types
    LANGUAGE sql
    AS $$ 

SELECT 
 *
FROM dba.tbl_role_types
;

$$;

ALTER FUNCTION dba.udf_get_role_types() OWNER TO dba;


CREATE FUNCTION udf_get_roles(_role_id character varying, _filter character varying) RETURNS SETOF vw_roles
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.vw_roles
WHERE
(
  role_id = $1
  OR $1 IS NULL
) AND (
  UPPER(role_id) LIKE '%'||UPPER($2)||'%'
  OR UPPER(role_name) LIKE '%'||UPPER($2)||'%'
  OR UPPER(role_type_name) LIKE '%'||UPPER($2)
  OR UPPER(authentication_service_name) LIKE '%'||UPPER($2)
  OR $2 IS NULL
  -- Exclude the special built-in roles from the results.
) AND (
  role_id <> 'PUBLIC'
) AND (
  role_id <> 'COSI_BUILT_IN_USERS'
)
ORDER BY role_type_id, authentication_service_id, role_name ASC
;

$_$;

ALTER FUNCTION dba.udf_get_roles(_role_id character varying, _filter character varying) OWNER TO dba;


CREATE FUNCTION udf_has_built_in_authentication(_role_id character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$ 
SELECT 
 count(role_id) AS exists
FROM dba.tbl_authentication_built_in
WHERE role_id = $1
;

$_$;

ALTER FUNCTION dba.udf_has_built_in_authentication(_role_id character varying) OWNER TO dba;


CREATE FUNCTION udf_insert_built_in_authentication_user(_user character varying, _role_id character varying, _passphrase_sha1 character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 
INSERT INTO dba.tbl_authentication_built_in
(
  created_who,
  modified_who,
  role_id,
  passphrase_sha1
) VALUES (
  $1,
  $1,
  $2,
  $3
)
;
$_$;

ALTER FUNCTION dba.udf_insert_built_in_authentication_user(_user character varying, _role_id character varying, _passphrase_sha1 character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_role(_user character varying, _role_id character varying, _role_type_id character, _name character varying, _authentication_service_id character, _enabled boolean) RETURNS void
    LANGUAGE sql
    AS $_$ 
INSERT INTO dba.tbl_roles
(
  created_who,
  modified_who,
  role_id,
  role_type_id,
  name,
  authentication_service_id,
  enabled
) VALUES (
  $1,
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
)
;
$_$;

ALTER FUNCTION dba.udf_insert_role(_user character varying, _role_id character varying, _role_type_id character, _name character varying, _authentication_service_id character, _enabled boolean) OWNER TO dba;

CREATE FUNCTION udf_insert_role_activity(_user character varying, _role_id character varying, _activity_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_role_activities
(
 role_id,
 activity_id,
 created_who,
 modified_who
) VALUES (
 $2,
 $3,
 $1,
 $1
)
;
$_$;

ALTER FUNCTION dba.udf_insert_role_activity(_user character varying, _role_id character varying, _activity_id character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_role_relation(_user character varying, _child_role_id character varying, _parent_role_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 
INSERT INTO dba.tbl_role_relations
(
 child_role_id,
 parent_role_id,
 created_who,
 modified_who
) VALUES (
 $2,
 $3,
 $1,
 $1
)
;
$_$;

ALTER FUNCTION dba.udf_insert_role_relation(_user character varying, _child_role_id character varying, _parent_role_id character varying) OWNER TO dba;

CREATE FUNCTION udf_update_built_in_passphrase(_role_id character varying, _passphrase_sha1 character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 
UPDATE dba.tbl_authentication_built_in
SET passphrase_sha1 = $2,
modified_when = now(),
modified_who = $1
WHERE role_id = $1;

$_$;

ALTER FUNCTION dba.udf_update_built_in_passphrase(_role_id character varying, _passphrase_sha1 character varying) OWNER TO dba;

CREATE FUNCTION udf_update_last_login(_role_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

UPDATE dba.tbl_roles
SET last_login = now()
WHERE role_id = $1
;

$_$;

ALTER FUNCTION dba.udf_update_last_login(_role_id character varying) OWNER TO dba;

CREATE FUNCTION udf_update_role(_user character varying, _role_id character varying, _name character varying, _authentication_service_id character, _enabled boolean) RETURNS void
    LANGUAGE sql
    AS $_$ 

UPDATE dba.tbl_roles SET
  modified_who = $1,
  modified_when = now(),
  name = $3,
  authentication_service_id = $4,
  enabled = $5
WHERE role_id = $2
;
$_$;
ALTER FUNCTION dba.udf_update_role(_user character varying, _role_id character varying, _name character varying, _authentication_service_id character, _enabled boolean) OWNER TO dba;



-------------
-- ADD CONSTRAINTS (Primary/Foreign keys, etc.)
-------------

ALTER TABLE ONLY tbl_activities
    ADD CONSTRAINT pk_tbl_activities PRIMARY KEY (activity_id);

ALTER TABLE ONLY tbl_authentication_built_in
    ADD CONSTRAINT pk_tbl_authentication_built_in PRIMARY KEY (role_id);

ALTER TABLE ONLY tbl_authentication_services
    ADD CONSTRAINT pk_tbl_authentication_services PRIMARY KEY (authentication_service_id);

ALTER TABLE ONLY tbl_role_activities
    ADD CONSTRAINT pk_tbl_role_activities PRIMARY KEY (role_id, activity_id);

ALTER TABLE ONLY tbl_role_relations
    ADD CONSTRAINT pk_tbl_role_relations PRIMARY KEY (parent_role_id, child_role_id);

ALTER TABLE ONLY tbl_role_types
    ADD CONSTRAINT pk_tbl_role_types PRIMARY KEY (role_type_id);

ALTER TABLE ONLY tbl_roles
    ADD CONSTRAINT pk_tbl_roles PRIMARY KEY (role_id);

ALTER TABLE ONLY tbl_role_activities
    ADD CONSTRAINT fk_tbl_role_activities_2 FOREIGN KEY (activity_id) REFERENCES tbl_activities(activity_id);

ALTER TABLE ONLY tbl_role_relations
    ADD CONSTRAINT fk_tbl_role_relations_1 FOREIGN KEY (parent_role_id) REFERENCES tbl_roles(role_id);

ALTER TABLE ONLY tbl_role_relations
    ADD CONSTRAINT fk_tbl_role_relations_2 FOREIGN KEY (child_role_id) REFERENCES tbl_roles(role_id);

ALTER TABLE ONLY tbl_role_activities
    ADD CONSTRAINT fk_tbl_role_resource_actions_1 FOREIGN KEY (role_id) REFERENCES tbl_roles(role_id);

ALTER TABLE ONLY tbl_roles
    ADD CONSTRAINT fk_tbl_roles_1 FOREIGN KEY (role_type_id) REFERENCES tbl_role_types(role_type_id);

ALTER TABLE ONLY tbl_roles
    ADD CONSTRAINT fk_tbl_roles_2 FOREIGN KEY (authentication_service_id) REFERENCES tbl_authentication_services(authentication_service_id);

-------------
-- CLEANUP PERMISSIONS
-------------
REVOKE ALL ON SCHEMA dba FROM PUBLIC;
REVOKE ALL ON SCHEMA dba FROM dba;
GRANT ALL ON SCHEMA dba TO dba;
GRANT USAGE ON SCHEMA dba TO webuser;

REVOKE ALL ON TABLE tbl_role_activities FROM PUBLIC;
REVOKE ALL ON TABLE tbl_role_activities FROM dba;
GRANT ALL ON TABLE tbl_role_activities TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_role_activities TO webuser;

REVOKE ALL ON TABLE tbl_authentication_services FROM PUBLIC;
REVOKE ALL ON TABLE tbl_authentication_services FROM dba;
GRANT ALL ON TABLE tbl_authentication_services TO dba;
GRANT SELECT ON TABLE tbl_authentication_services TO webuser;

REVOKE ALL ON TABLE tbl_role_relations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_role_relations FROM dba;
GRANT ALL ON TABLE tbl_role_relations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_role_relations TO webuser;

REVOKE ALL ON TABLE tbl_role_types FROM PUBLIC;
REVOKE ALL ON TABLE tbl_role_types FROM dba;
GRANT ALL ON TABLE tbl_role_types TO dba;
GRANT SELECT ON TABLE tbl_role_types TO webuser;

REVOKE ALL ON TABLE tbl_roles FROM PUBLIC;
REVOKE ALL ON TABLE tbl_roles FROM dba;
GRANT ALL ON TABLE tbl_roles TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_roles TO webuser;

REVOKE ALL ON TABLE vw_roles FROM PUBLIC;
REVOKE ALL ON TABLE vw_roles FROM dba;
GRANT ALL ON TABLE vw_roles TO dba;
GRANT SELECT ON TABLE vw_roles TO webuser;

REVOKE ALL ON TABLE tbl_activities FROM PUBLIC;
REVOKE ALL ON TABLE tbl_activities FROM dba;
GRANT ALL ON TABLE tbl_activities TO dba;
GRANT SELECT ON TABLE tbl_activities TO webuser;

REVOKE ALL ON TABLE vw_role_relations FROM PUBLIC;
REVOKE ALL ON TABLE vw_role_relations FROM dba;
GRANT ALL ON TABLE vw_role_relations TO dba;
GRANT SELECT ON TABLE vw_role_relations TO webuser;

REVOKE ALL ON TABLE tbl_authentication_built_in FROM PUBLIC;
REVOKE ALL ON TABLE tbl_authentication_built_in FROM dba;
GRANT ALL ON TABLE tbl_authentication_built_in TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_authentication_built_in TO webuser;
