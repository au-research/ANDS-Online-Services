ALTER TABLE dba.tbl_data_sources ADD COLUMN advanced_harvesting_mode CHARACTER VARYING (512) DEFAULT 'STANDARD';
UPDATE dba.tbl_data_sources SET advanced_harvesting_mode = 'INCREMENTAL';
ALTER TABLE dba.tbl_data_sources ADD COLUMN post_code character varying(32);
ALTER TABLE dba.tbl_data_sources ADD COLUMN address_line_1 character varying(128);
ALTER TABLE dba.tbl_data_sources ADD COLUMN address_line_2 character varying(128);
ALTER TABLE dba.tbl_data_sources ADD COLUMN city character varying(32);
ALTER TABLE dba.tbl_data_sources ADD COLUMN state character varying(32);

#HARVESTER TABLE
ALTER TABLE public.harvest ADD COLUMN advanced_harvesting_mode CHARACTER VARYING(512) DEFAULT 'STANDARD';