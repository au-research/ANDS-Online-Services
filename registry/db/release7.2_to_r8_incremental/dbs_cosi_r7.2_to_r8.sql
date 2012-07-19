INSERT INTO dba.tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_DATA_SOURCE_REPORTS','SYSTEM','SYSTEM');
INSERT INTO dba.tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('ORCA_SOURCE_ADMIN','aORCA_DATA_SOURCE_REPORTS','SYSTEM','SYSTEM');

INSERT INTO dba.tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_GOLD_INDEX','SYSTEM','SYSTEM');
INSERT INTO dba.tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('PUBLIC','aORCA_GOLD_INDEX','SYSTEM','SYSTEM');

INSERT INTO dba.tbl_activities (activity_id, created_who, modified_who) VALUES ('aORCA_RUN_TASKS','SYSTEM','SYSTEM');
INSERT INTO dba.tbl_role_activities (role_id, activity_id, created_who, modified_who) VALUES ('COSI_ADMIN','aORCA_RUN_TASKS','SYSTEM','SYSTEM');
