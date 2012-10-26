INSERT INTO dba.tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_REGISTRY_OBJECT_ADMIN_MY_TAGS','SYSTEM','SYSTEM');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('ORCA_TAG_MANAGER', 'ROLE_FUNCTIONAL', 'Role to Create Delete Tags for Registry Objects');
INSERT INTO dba.tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_TAG_MANAGER', 'aORCA_REGISTRY_OBJECT_ADMIN_MY_TAGS','SYSTEM','SYSTEM');
INSERT INTO dba.tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_TAG_MANAGER', 'aORCA_REGISTRY_OBJECT_ADMIN_MANAGE_RECORDS','SYSTEM','SYSTEM');
