-- Table: dba.tbl_google_statistics

-- DROP TABLE dba.tbl_google_statistics;

CREATE TABLE dba.tbl_google_statistics
(
  id serial NOT NULL,
  slug character varying(512),
  key character varying(512),
  "group" character varying(255),
  data_source character varying(255),
  page_views integer,
  unique_page_views integer,
  display_title character varying(512),
  object_class character varying(255),
  day date,
  CONSTRAINT tbl_google_statistics_pkey PRIMARY KEY (id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_google_statistics
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_google_statistics TO dba;
GRANT SELECT, INSERT, DELETE ON TABLE dba.tbl_google_statistics TO webuser;

GRANT ALL ON TABLE dba.tbl_google_statistics_id_seq TO dba;
GRANT SELECT, USAGE ON TABLE dba.tbl_google_statistics_id_seq TO webuser;


-- Table: dba.tbl_internal_search_results

-- DROP TABLE dba.tbl_internal_search_results;

CREATE TABLE dba.tbl_internal_search_results
(
  id serial NOT NULL,
  search_term character varying(512),
  time_stamp timestamp without time zone,
  result_count integer
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_internal_search_results
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_internal_search_results TO dba;
GRANT SELECT, INSERT, DELETE ON TABLE dba.tbl_internal_search_results TO webuser;

GRANT ALL ON TABLE dba.tbl_internal_search_results_id_seq TO dba;
GRANT SELECT, USAGE ON TABLE dba.tbl_internal_search_results_id_seq TO webuser;

-- Table: dba.tbl_internal_search_statistics

-- DROP TABLE dba.tbl_internal_search_statistics;

CREATE TABLE dba.tbl_internal_search_statistics
(
  id serial NOT NULL,
  search_term character varying(512),
  slug character varying(512),
  time_stamp timestamp without time zone,
  CONSTRAINT id PRIMARY KEY (id )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_internal_search_statistics
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_internal_search_statistics TO dba;
GRANT SELECT, INSERT, DELETE ON TABLE dba.tbl_internal_search_statistics TO webuser;

GRANT ALL ON TABLE dba.tbl_internal_search_statistics_id_seq TO dba;
GRANT SELECT, USAGE ON TABLE dba.tbl_internal_search_statistics_id_seq TO webuser;
