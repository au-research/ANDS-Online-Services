-- Table: dba.tbl_url_mappings

-- DROP TABLE dba.tbl_url_mappings;

CREATE TABLE dba.tbl_url_mappings
(
  url_fragment character varying(512) NOT NULL,
  registry_object_key character varying(512),
  date_created bigint,
  date_modified bigint,
  search_title character varying(512),
  CONSTRAINT pk_tbl_url_mappings PRIMARY KEY (url_fragment )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_url_mappings
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_url_mappings TO dba;
GRANT SELECT, INSERT, DELETE ON TABLE dba.tbl_url_mappings TO webuser;

-- Add SLUG column to ro table
ALTER TABLE dba.tbl_registry_objects ADD COLUMN slug character varying(512);

--
-- UPDATE dba.tbl_registry_objects ro SET url_slug = (SELECT url_fragment FROM dba.tbl_url_mappings url WHERE ro.registry_object_key = url.registry_object_key ORDER BY date_created DESC LIMIT 1)