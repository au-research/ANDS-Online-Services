CREATE TABLE dba.tbl_registry_objects_qa_results
(
  qa_id serial NOT NULL,
  draft_key character varying(512),
  registry_object_data_source character varying(255),
  registry_object_key character varying(512),
  flag smallint NOT NULL DEFAULT 0,
  gold_status_flag smallint NOT NULL DEFAULT 0,
  manually_assessed_flag smallint NOT NULL DEFAULT 0,
  quality_test_result text,
  warning_count smallint,
  error_count smallint,
  quality_level smallint,
  quality_level_result text,
  status character varying,
  date_checked bigint DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT pk_tbl_registry_objects_qa_results PRIMARY KEY (qa_id ),
  CONSTRAINT fk_tbl_registry_objects_qa_results_1 FOREIGN KEY (draft_key, registry_object_data_source)
      REFERENCES dba.tbl_draft_registry_objects (draft_key, registry_object_data_source) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE,
  CONSTRAINT fk_tbl_registry_objects_qa_results_2 FOREIGN KEY (registry_object_key)
      REFERENCES dba.tbl_registry_objects (registry_object_key) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_registry_objects_qa_results
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_registry_objects_qa_results TO dba;
GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE dba.tbl_registry_objects_qa_results TO webuser;
COMMENT ON TABLE dba.tbl_registry_objects_qa_results
  IS 'meta-meta data about Registry Objects';


INSERT INTO dba.tbl_registry_objects_qa_results
(draft_key, registry_object_data_source, error_count, warning_count, status, quality_test_result,flag)
SELECT draft_key, registry_object_data_source, error_count, warning_count, status, quality_test_result, (CASE flag WHEN 't' then 1 ELSE 0 END)
from dba.tbl_draft_registry_objects;


INSERT INTO dba.tbl_registry_objects_qa_results
(registry_object_key, registry_object_data_source, error_count, warning_count, status, quality_test_result,flag)
SELECT registry_object_key, data_source_key, error_count, warning_count, status, quality_test_result, (CASE flag WHEN 't' then 1 ELSE 0 END)
from dba.tbl_registry_objects d;


ALTER TABLE dba.tbl_registry_objects ADD manually_assessed_flag smallint NOT NULL DEFAULT 0
ALTER TABLE dba.tbl_registry_objects ADD gold_status_flag smallint NOT NULL DEFAULT 0;
ALTER TABLE dba.tbl_registry_objects ADD quality_level smallint;
ALTER TABLE dba.tbl_registry_objects ADD quality_level_result text;

ALTER TABLE dba.tbl_draft_registry_objects ADD quality_level smallint;
ALTER TABLE dba.tbl_draft_registry_objects ADD quality_level_result text;
