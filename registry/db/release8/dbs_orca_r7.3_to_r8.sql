CREATE TABLE tbl_background_tasks
(
 task_id bigserial NOT NULL,
 method character varying(64),
 started timestamp without time zone,
 added timestamp without time zone NOT NULL DEFAULT now(),
 completed timestamp without time zone,
 prerequisite_task bigint,
 log_msg text,
 registry_object_keys text,
 data_source_key text,
 status character varying(32) NOT NULL DEFAULT 'WAITING'::character
varying,
 CONSTRAINT tbl_background_tasks_pkey PRIMARY KEY (task_id )
)
WITH (
 OIDS=FALSE
);
ALTER TABLE dba.tbl_background_tasks ADD COLUMN scheduled_for timestamp without time zone DEFAULT NOW();
ALTER TABLE tbl_background_tasks
 OWNER TO dba;
GRANT ALL ON TABLE tbl_background_tasks TO dba;
GRANT SELECT, UPDATE, INSERT ON TABLE tbl_background_tasks TO webuser;
GRANT DELETE ON TABLE dba.tbl_background_tasks TO webuser;
--IMPORTANT
GRANT SELECT, USAGE ON TABLE tbl_background_tasks_task_id_seq TO webuser;

CREATE OR REPLACE FUNCTION dba.udf_delete_data_source(_data_source_key character varying)
  RETURNS void AS
$BODY$

DELETE FROM dba.tbl_data_source_logs
WHERE data_source_key = $1;

DELETE FROM dba.tbl_harvest_requests
WHERE data_source_key = $1;

DELETE FROM dba.tbl_institution_pages
WHERE authoritive_data_source_key = $1;

DELETE FROM dba.tbl_data_sources
WHERE data_source_key = $1 AND data_source_key <> 'SYSTEM';
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_delete_data_source(character varying)
  OWNER TO dba; 