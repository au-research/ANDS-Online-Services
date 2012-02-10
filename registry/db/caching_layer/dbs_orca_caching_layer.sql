CREATE TABLE dba.tbl_registry_objects_qa_results
(
  qa_id serial NOT NULL,
  draft_key character varying(512),
  registry_object_data_source character varying(255),
  registry_object_key character varying(512),
  result_type character varying(64),
  result_value text,
  date_checked bigint DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT pk_tbl_registry_objects_qa_results PRIMARY KEY (qa_id ),
  CONSTRAINT fk_tbl_registry_objects_qa_results_1 FOREIGN KEY (draft_key, registry_object_data_source)
      REFERENCES dba.tbl_draft_registry_objects (draft_key, registry_object_data_source) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_tbl_registry_objects_qa_results_2 FOREIGN KEY (registry_object_key)
      REFERENCES dba.tbl_registry_objects (registry_object_key) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
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


--delete from dba.tbl_registry_objects_qa_results;

INSERT INTO dba.tbl_registry_objects_qa_results
(draft_key, registry_object_data_source, result_type, result_value)
SELECT draft_key, registry_object_data_source,'error_count',error_count
from dba.tbl_draft_registry_objects d where d.error_count is not null;

INSERT INTO dba.tbl_registry_objects_qa_results
(draft_key, registry_object_data_source, result_type, result_value)
SELECT draft_key, registry_object_data_source,'warning_count',warning_count
from dba.tbl_draft_registry_objects d where d.warning_count is not null;

INSERT INTO dba.tbl_registry_objects_qa_results
(draft_key, registry_object_data_source, result_type, result_value)
SELECT draft_key, registry_object_data_source,'quality_test_result',quality_test_result
from dba.tbl_draft_registry_objects d where d.quality_test_result is not null;

INSERT INTO dba.tbl_registry_objects_qa_results
(registry_object_key, registry_object_data_source, result_type, result_value)
SELECT registry_object_key, data_source_key,'error_count',error_count
from dba.tbl_registry_objects d where d.error_count is not null;

INSERT INTO dba.tbl_registry_objects_qa_results
(registry_object_key, registry_object_data_source, result_type, result_value)
SELECT registry_object_key, data_source_key,'warning_count',warning_count
from dba.tbl_registry_objects d where d.warning_count is not null;

INSERT INTO dba.tbl_registry_objects_qa_results
(registry_object_key, registry_object_data_source, result_type, result_value)
SELECT registry_object_key, data_source_key,'quality_test_result',quality_test_result
from dba.tbl_registry_objects d where d.quality_test_result is not null;


--select * from dba.tbl_registry_objects_qa_results where draft_key = 'DCB0066';
--select * from dba.tbl_registry_objects_qa_results where registry_object_key = '03c59e36-bee4-4186-8bd4-f1c5dd69c396';
