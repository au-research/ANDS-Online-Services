ALTER TABLE dba.tbl_data_sources ADD COLUMN advanced_harvesting_mode CHARACTER VARYING (512) DEFAULT 'STANDARD';
ALTER TABLE dba.tbl_data_sources ADD COLUMN post_code character varying(32);
