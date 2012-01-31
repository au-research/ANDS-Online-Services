-- Function: dba.udf_get_organisation_count(character varying)

-- DROP FUNCTION dba.udf_get_organisation_count(character varying);

CREATE OR REPLACE FUNCTION dba.udf_get_organisation_count(_date_filter character varying)
  RETURNS bigint AS
$BODY$SELECT 
count(DISTINCT(trim(both '-' from trim(both ' ' from lower(substring(role_id from 1 for 4)))))) from dba.tbl_roles where role_type_id = 'ROLE_ORGANISATIONAL'
AND created_when <= CAST($1 AS timestamp with time zone);$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_get_organisation_count(character varying)
  OWNER TO dba;

-- Function: dba.udf_get_user_count(character varying)

-- DROP FUNCTION dba.udf_get_user_count(character varying);

CREATE OR REPLACE FUNCTION dba.udf_get_user_count(_date_filter character varying)
  RETURNS bigint AS
$BODY$SELECT COUNT(DISTINCT(roles.role_id)) FROM dba.tbl_roles roles ,dba.tbl_role_relations relations 
WHERE roles.role_type_id = 'ROLE_USER' 
AND relations.parent_role_id <> 'ORCA_CLIENT_LIAISON' 
AND roles.role_id = relations.child_role_id and roles.role_type_id = 'ROLE_USER' and roles.authentication_service_id <> 'AUTHENTICATION_LDAP'
AND roles.created_when <= CAST($1 AS timestamp with time zone);$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_get_user_count(character varying)
  OWNER TO dba;

INSERT INTO dba.tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_RUN_TASKS','SYSTEM','SYSTEM');
INSERT INTO dba.tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN','aORCA_RUN_TASKS','SYSTEM','SYSTEM');