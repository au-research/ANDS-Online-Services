/* ******************************************************************************
Update orca_dba database schema to 4.0 (RIF-CS 1.2.0 support)
$Date: 2010-11-15 15:15:41 +1100 (Mon, 15 Nov 2010) $
$Revision: 563 $
****************************************************************************** */

/* New Register My Data draft table */
CREATE TABLE dba.tbl_rmd_draft_objects
(
   draft_owner character varying(255) NOT NULL DEFAULT 'SYSTEM', 
   category character varying(32) NOT NULL, 
   registry_object_key character varying(512) NOT NULL, 
   registry_object_group character varying(512) NOT NULL, 
   registry_object_type character varying(32) NOT NULL, 
   registry_object_data_source character varying(255) NOT NULL, 
   date_modified timestamp(6) with time zone NOT NULL, 
   serialised_json text, 
   serialised_draft_rifcs text, 
   CONSTRAINT pk_tbl_rmd_draft_objects PRIMARY KEY (draft_owner, registry_object_key, registry_object_group, registry_object_type, registry_object_data_source), 
   CONSTRAINT fk_tbl_rmd_draft_objects_1 FOREIGN KEY (registry_object_data_source) REFERENCES dba.tbl_data_sources (data_source_key) ON UPDATE NO ACTION ON DELETE NO ACTION
) 
WITH (
  OIDS = FALSE
)
;
ALTER TABLE dba.tbl_rmd_draft_objects OWNER TO dba;
COMMENT ON TABLE dba.tbl_rmd_draft_objects IS 'Objects created using the Regiser My Data tool are stored in this table. These objects are draft versions of registry objects, but needn''t be valid during the draft stage. For this reason, we keep them in a serialised JSON/RIF-CS format until they have been previewed, validated and "registered" by the user. ';




/* Alter existing tables */
ALTER TABLE dba.tbl_related_info 
	ADD COLUMN notes varchar(512);

ALTER TABLE dba.tbl_related_info 
	ADD COLUMN title varchar(512);

ALTER TABLE dba.tbl_related_info 
	ADD COLUMN identifier_type varchar(512);

ALTER TABLE dba.tbl_related_info 
	ADD COLUMN identifier varchar(512);

ALTER TABLE dba.tbl_related_info 
	ADD COLUMN info_type varchar(64);
	
ALTER TABLE dba.tbl_registry_objects 
	ADD COLUMN schema_version varchar(20);
	
ALTER TABLE dba.tbl_name_parts 
	ADD COLUMN citation_contributor_id int8;

CREATE INDEX idx_name_parts_citation_contributor_id_1 ON dba.tbl_name_parts USING BTREE (
	citation_contributor_id
);

ALTER TABLE dba.tbl_spatial_locations 
	ADD COLUMN coverage_id int8;
	
CREATE INDEX idx_spacial_locations_coverage_id_1 ON dba.tbl_spatial_locations USING BTREE (
	coverage_id
);

	
	
/* New tables */	
CREATE TABLE dba.tbl_temporal_coverage_text (
	coverage_text_id BIGSERIAL NOT NULL,
	temporal_coverage_id int8,
	value varchar(512),
	PRIMARY KEY(coverage_text_id)
);


CREATE INDEX idx_temporal_coverage_text_temporal_coverage_id_1 ON dba.tbl_temporal_coverage_text USING BTREE (
	temporal_coverage_id
);


ALTER TABLE dba.tbl_temporal_coverage_text OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_temporal_coverage_text FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_temporal_coverage_text FROM dba;
GRANT ALL ON TABLE dba.tbl_temporal_coverage_text TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_temporal_coverage_text TO webuser;


CREATE TABLE dba.tbl_temporal_coverage_dates (
	coverage_date_id BIGSERIAL NOT NULL,
	temporal_coverage_id int8,
	type varchar(512),
	date_format varchar(512),
	value varchar(512),
	timestamp_value timestamp(6) with time zone,
	PRIMARY KEY(coverage_date_id)
);


CREATE INDEX idx_temporal_coverage_dates_temporal_coverage_id_1 ON dba.tbl_temporal_coverage_dates USING BTREE (
	temporal_coverage_id
);

ALTER TABLE dba.tbl_temporal_coverage_dates OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_temporal_coverage_dates FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_temporal_coverage_dates FROM dba;
GRANT ALL ON TABLE dba.tbl_temporal_coverage_dates TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_temporal_coverage_dates TO webuser;



CREATE TABLE dba.tbl_temporal_coverage (
	temporal_coverage_id BIGSERIAL NOT NULL,
	coverage_id int8,
	PRIMARY KEY(temporal_coverage_id)
);


CREATE INDEX idx_temporal_coverage_coverage_id_1 ON dba.tbl_temporal_coverage USING BTREE (
	coverage_id
);


ALTER TABLE dba.tbl_temporal_coverage OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_temporal_coverage FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_temporal_coverage FROM dba;
GRANT ALL ON TABLE dba.tbl_temporal_coverage TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_temporal_coverage TO webuser;




CREATE TABLE dba.tbl_coverage (
	coverage_id BIGSERIAL NOT NULL,
	registry_object_key varchar(512),
	PRIMARY KEY(coverage_id)
);


CREATE INDEX idx_coverage_registry_object_key_1 ON dba.tbl_coverage USING BTREE (
	registry_object_key
);


ALTER TABLE dba.tbl_coverage OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_coverage FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_coverage FROM dba;
GRANT ALL ON TABLE dba.tbl_coverage TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_coverage TO webuser;



CREATE TABLE dba.tbl_citation_contributors (
	citation_contributor_id BIGSERIAL NOT NULL,
	citation_info_id int8,
	seq int8,
	PRIMARY KEY(citation_contributor_id)
);


CREATE INDEX idx_citation_contributors_citation_info_id_1 ON dba.tbl_citation_contributors USING BTREE (
	citation_info_id
);

ALTER TABLE dba.tbl_citation_contributors OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_citation_contributors FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_citation_contributors FROM dba;
GRANT ALL ON TABLE dba.tbl_citation_contributors TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_citation_contributors TO webuser;



CREATE TABLE dba.tbl_citation_dates (
	metadata_date_id BIGSERIAL NOT NULL,
	citation_info_id int8,
	date varchar(512),
	type varchar(512),
	PRIMARY KEY(metadata_date_id)
);


CREATE INDEX idx_citation_dates_citation_info_id_1 ON dba.tbl_citation_dates USING BTREE (
	citation_info_id
);


ALTER TABLE dba.tbl_citation_dates OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_citation_dates FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_citation_dates FROM dba;
GRANT ALL ON TABLE dba.tbl_citation_dates TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_citation_dates TO webuser;




CREATE TABLE dba.tbl_citation_information (
	citation_info_id BIGSERIAL NOT NULL,
	registry_object_key varchar(512) NOT NULL,
	style varchar(512),
	full_citation varchar(512),
	metadata_identifier varchar(512),
	metadata_type varchar(512),
	metadata_title varchar(512),
	metadata_edition varchar(512),
	metadata_place_published varchar(512),
	metadata_url varchar(512),
	metadata_context varchar(512),
	PRIMARY KEY(citation_info_id)
);


CREATE INDEX idx_registry_object_key_1 ON dba.tbl_citation_information USING BTREE (
	registry_object_key
);


ALTER TABLE dba.tbl_citation_information OWNER TO dba;
REVOKE ALL ON TABLE dba.tbl_citation_information FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_citation_information FROM dba;
GRANT ALL ON TABLE dba.tbl_citation_information TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_citation_information TO webuser;


/* Foreign Key constraints */

ALTER TABLE dba.tbl_temporal_coverage_text
   ADD CONSTRAINT Ref_tbl_temporal_coverage_text_to_tbl_temporal_coverage FOREIGN KEY (temporal_coverage_id)
    REFERENCES dba.tbl_temporal_coverage(temporal_coverage_id)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_temporal_coverage_dates
   ADD CONSTRAINT Ref_tbl_temporal_coverage_dates_to_tbl_temporal_coverage FOREIGN KEY (temporal_coverage_id)
    REFERENCES dba.tbl_temporal_coverage(temporal_coverage_id)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_temporal_coverage
   ADD CONSTRAINT Ref_tbl_temporal_coverage_to_tbl_coverage FOREIGN KEY (coverage_id)
    REFERENCES dba.tbl_coverage(coverage_id)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_coverage
   ADD CONSTRAINT Ref_tbl_coverage_to_tbl_registry_objects FOREIGN KEY (registry_object_key)
    REFERENCES dba.tbl_registry_objects(registry_object_key)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_citation_contributors
   ADD CONSTRAINT Ref_tbl_citation_contributors_to_tbl_citation_information FOREIGN KEY (citation_info_id)
    REFERENCES dba.tbl_citation_information(citation_info_id)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_citation_dates
   ADD CONSTRAINT Ref_tbl_citation_metadata_dates_to_tbl_citation_information FOREIGN KEY (citation_info_id)
    REFERENCES dba.tbl_citation_information(citation_info_id)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_citation_information
   ADD CONSTRAINT Ref_tbl_citation_information_to_tbl_registry_objects FOREIGN KEY (registry_object_key)
    REFERENCES dba.tbl_registry_objects(registry_object_key)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
      NOT DEFERRABLE;

ALTER TABLE dba.tbl_name_parts ADD CONSTRAINT Ref_tbl_name_parts_to_tbl_citation_contributors FOREIGN KEY (citation_contributor_id)
	REFERENCES dba.tbl_citation_contributors(citation_contributor_id)
	MATCH SIMPLE
	ON DELETE NO ACTION
	ON UPDATE NO ACTION
	NOT DEFERRABLE;

ALTER TABLE dba.tbl_spatial_locations ADD CONSTRAINT Ref_tbl_spatial_locations_to_tbl_coverage FOREIGN KEY (coverage_id)
	REFERENCES dba.tbl_coverage(coverage_id)
	MATCH SIMPLE
	ON DELETE NO ACTION
	ON UPDATE NO ACTION
	NOT DEFERRABLE;

-- Function: dba.udf_insert_citation_date(bigint, bigint, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_citation_date(bigint, bigint, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_citation_date(_metadata_date_id bigint, _citation_info_id bigint, _date character varying, _type character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_citation_dates
(
  metadata_date_id,
  citation_info_id,
  "date",
  "type"
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_citation_date(bigint, bigint, character varying, character varying) OWNER TO dba;



-- Function: dba.udf_insert_citation_contributor(bigint, bigint, bigint)

-- DROP FUNCTION dba.udf_insert_citation_contributor(bigint, bigint, bigint);

CREATE OR REPLACE FUNCTION dba.udf_insert_citation_contributor(_citation_contributor_id bigint, _citation_info_id bigint, _seq bigint)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_citation_contributors
(
  citation_contributor_id,
  citation_info_id,
  seq
 ) VALUES (
  $1,
  $2,
  $3
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_citation_contributor(bigint, bigint, bigint) OWNER TO dba;



-- Function: dba.udf_insert_contributor_name_part(bigint, bigint, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_contributor_name_part(bigint, bigint, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_contributor_name_part(_name_part_id bigint, _contributor_name_id bigint, _value character varying, _type character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_name_parts
(
  name_part_id,
  citation_contributor_id,
  value,
  type
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
;

--UPDATE dba.tbl_registry_objects 
--   SET names_search_index = coalesce(names_search_index, '') || to_tsvector(coalesce($3, ''))
-- WHERE registry_object_key = (SELECT registry_object_key FROM dba.tbl_citation_information NATURAL JOIN --dba.tbl_citation_contributors WHERE citation_contributor_id = $2)
--;

$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_name_part(bigint, bigint, character varying, character varying) OWNER TO dba;

-- Function: dba.udf_insert_citation_information(bigint, character varying, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_citation_information(bigint, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_citation_information(_citation_info_id bigint, _registry_object_key character varying, _style character varying, _full_citation character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_citation_information
(
  citation_info_id,
  registry_object_key,
  style,
  full_citation
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
;

$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_citation_information(bigint, character varying, character varying, character varying) OWNER TO dba;


-- Function: dba.udf_insert_citation_information(bigint, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_citation_information(bigint, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_citation_information(_citation_info_id bigint, _registry_object_key character varying, _metadata_identifier character varying, _metadata_type character varying, _metadata_title character varying, _metadata_edition character varying, _metadata_place_published character varying, _metadata_url character varying, _metadata_context character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_citation_information
(
  citation_info_id,
  registry_object_key,
  metadata_identifier,
  metadata_type,
  metadata_title,
  metadata_edition,
  metadata_place_published,
  metadata_url,
  metadata_context
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6,
  $7,
  $8,
  $9
 )
;

$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_citation_information(bigint, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying) OWNER TO dba;




-- Function: dba.udf_get_contributor_name_parts(bigint)

-- DROP FUNCTION dba.udf_get_contributor_name_parts(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_contributor_name_parts(_citation_contributor_id bigint)
  RETURNS SETOF dba.tbl_name_parts AS
$BODY$

SELECT 
 * 
FROM dba.tbl_name_parts
WHERE citation_contributor_id = $1
ORDER BY UPPER(type) ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_contributor_name_parts(bigint) OWNER TO dba;


-- Function: dba.udf_get_citation_contributors(bigint)

-- DROP FUNCTION dba.udf_get_citation_contributors(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_citation_contributors(_citation_info_id bigint)
  RETURNS SETOF dba.tbl_citation_contributors AS
$BODY$

SELECT 
 * 
FROM dba.tbl_citation_contributors
WHERE citation_info_id = $1
ORDER BY seq ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_citation_contributors(bigint) OWNER TO dba;


-- Function: dba.udf_get_citation_dates(bigint)

-- DROP FUNCTION dba.udf_get_citation_dates(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_citation_dates(_citation_info_id bigint)
  RETURNS SETOF dba.tbl_citation_dates AS
$BODY$

SELECT 
 * 
FROM dba.tbl_citation_dates
WHERE citation_info_id = $1
ORDER BY UPPER(type) ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_citation_dates(bigint) OWNER TO dba;


-- Function: dba.udf_get_citation_information(character varying)

-- DROP FUNCTION dba.udf_get_citation_information(character varying);

CREATE OR REPLACE FUNCTION dba.udf_get_citation_information(_registry_object_key character varying)
  RETURNS SETOF dba.tbl_citation_information AS
$BODY$

SELECT 
 * 
FROM dba.tbl_citation_information
WHERE registry_object_key = $1
ORDER BY citation_info_id ASC
;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_citation_information(character varying) OWNER TO dba;



-- Function: dba.udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character varying, _record_owner character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_registry_objects
(
  registry_object_key,
  registry_object_class,
  type,
  originating_source,
  originating_source_type,
  data_source_key,
  object_group,
  date_accessioned,
  date_modified,
  created_who,
  status,
  record_owner
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6,
  $7,
  $8,
  $9,
  $10,
  $11,
  $12
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character varying, character varying) OWNER TO dba;

-- Function: dba.udf_insert_related_info(bigint, character varying, character varying, character varying, character varying, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_related_info(bigint, character varying, character varying, character varying, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_related_info(_related_info_id bigint, _registry_object_key character varying, _info_type character varying, _identifier character varying, _identifier_type character varying, _title character varying, _notes character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_related_info
(
  related_info_id,
  registry_object_key,
  info_type,
  identifier,
  identifier_type,
  title,
  notes
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6,
  $7
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_related_info(bigint, character varying, character varying, character varying, character varying, character varying, character varying) OWNER TO dba;


-- Function: dba.udf_insert_temporal_coverage_date(bigint, bigint, character varying, character varying, character varying, timestamp with time zone)

-- DROP FUNCTION dba.udf_insert_temporal_coverage_date(bigint, bigint, character varying, character varying, character varying, timestamp with time zone);

CREATE OR REPLACE FUNCTION dba.udf_insert_temporal_coverage_date(_coverage_date_id bigint, _temporal_coverage_id bigint, _type character varying, _date_format character varying, _value character varying, _timestamp_value timestamp with time zone)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_temporal_coverage_dates
(
  coverage_date_id,
  temporal_coverage_id,
  "type",
  date_format,
  "value",
  timestamp_value
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_temporal_coverage_date(bigint, bigint, character varying, character varying, character varying, timestamp with time zone) OWNER TO dba;


-- Function: dba.udf_insert_temporal_coverage_text(bigint, bigint, character varying)

-- DROP FUNCTION dba.udf_insert_temporal_coverage_text(bigint, bigint, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_temporal_coverage_text(_coverage_text_id bigint, _temporal_coverage_id bigint, _value character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_temporal_coverage_text
(
  coverage_text_id,
  temporal_coverage_id,
  "value"
 ) VALUES (
  $1,
  $2,
  $3
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_temporal_coverage_text(bigint, bigint, character varying) OWNER TO dba;

-- Function: dba.udf_insert_temporal_coverage(bigint, bigint)

-- DROP FUNCTION dba.udf_insert_temporal_coverage(bigint, bigint);

CREATE OR REPLACE FUNCTION dba.udf_insert_temporal_coverage(_temporal_coverage_id bigint, _coverage_id bigint)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_temporal_coverage
(
  temporal_coverage_id,
  coverage_id
 ) VALUES (
  $1,
  $2
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_temporal_coverage(bigint, bigint) OWNER TO dba;


-- Function: dba.udf_get_temporal_coverage_dates(bigint)

-- DROP FUNCTION dba.udf_get_temporal_coverage_dates(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_temporal_coverage_dates(_temporal_coverage_id bigint)
  RETURNS SETOF dba.tbl_temporal_coverage_dates AS
$BODY$

SELECT 
 * 
FROM dba.tbl_temporal_coverage_dates
WHERE temporal_coverage_id = $1
ORDER BY timestamp_value ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_temporal_coverage_dates(bigint) OWNER TO dba;


-- Function: dba.udf_get_temporal_coverage_text(bigint)

-- DROP FUNCTION dba.udf_get_temporal_coverage_text(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_temporal_coverage_text(_temporal_coverage_id bigint)
  RETURNS SETOF dba.tbl_temporal_coverage_text AS
$BODY$

SELECT 
 * 
FROM dba.tbl_temporal_coverage_text
WHERE temporal_coverage_id = $1
ORDER BY coverage_text_id ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_temporal_coverage_text(bigint) OWNER TO dba;

-- Function: dba.udf_get_temporal_coverage(bigint)

-- DROP FUNCTION dba.udf_get_temporal_coverage(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_temporal_coverage(_coverage_id bigint)
  RETURNS SETOF dba.tbl_temporal_coverage AS
$BODY$

SELECT 
 * 
FROM dba.tbl_temporal_coverage
WHERE coverage_id = $1
ORDER BY temporal_coverage_id ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_temporal_coverage(bigint) OWNER TO dba;

-- Function: dba.udf_insert_spatial_coverage(bigint, bigint, character varying, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_spatial_coverage(bigint, bigint, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_spatial_coverage(_spatial_location_id bigint, _coverage_id bigint, _value character varying, _type character varying, _lang character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_spatial_locations
(
  spatial_location_id,
  coverage_id,
  value,
  type,
  lang
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_spatial_coverage(bigint, bigint, character varying, character varying, character varying) OWNER TO dba;


-- Function: dba.udf_get_spatial_coverage(bigint)

-- DROP FUNCTION dba.udf_get_spatial_coverage(bigint);

CREATE OR REPLACE FUNCTION dba.udf_get_spatial_coverage(_coverage_id bigint)
  RETURNS SETOF dba.tbl_spatial_locations AS
$BODY$

SELECT 
 * 
FROM dba.tbl_spatial_locations
WHERE coverage_id = $1
;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_spatial_coverage(bigint) OWNER TO dba;


-- Function: dba.udf_insert_coverage(bigint, character varying)

-- DROP FUNCTION dba.udf_insert_coverage(bigint, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_coverage(_coverage_id bigint, _registry_object_key character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_coverage
(
  coverage_id,
  registry_object_key
 ) VALUES (
  $1,
  $2
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_coverage(bigint, character varying) OWNER TO dba;


-- Function: dba.udf_get_coverage(character varying)

-- DROP FUNCTION dba.udf_get_coverage(character varying);

CREATE OR REPLACE FUNCTION dba.udf_get_coverage(_registry_object_key character varying)
  RETURNS SETOF dba.tbl_coverage AS
$BODY$

SELECT 
 * 
FROM dba.tbl_coverage
WHERE registry_object_key = $1
ORDER BY coverage_id ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_coverage(character varying) OWNER TO dba;

-- Function: dba.udf_delete_registry_object(character varying)

-- DROP FUNCTION dba.udf_delete_registry_object(character varying);

CREATE OR REPLACE FUNCTION dba.udf_delete_registry_object(_registry_object_key character varying)
  RETURNS void AS
$BODY$ 

BEGIN
-- identifiers
DELETE FROM dba.tbl_identifiers
WHERE registry_object_key = _registry_object_key;

-- name parts
DELETE FROM dba.tbl_name_parts NP
WHERE NP.name_part_id IN (
 SELECT NPX.name_part_id 
   FROM dba.tbl_name_parts NPX
   JOIN dba.tbl_complex_names CNX
     ON NPX.complex_name_id = CNX.complex_name_id
  WHERE CNX.registry_object_key = _registry_object_key
);

-- complex names
DELETE FROM dba.tbl_complex_names
WHERE registry_object_key = _registry_object_key;


-- citation dates
DELETE FROM dba.tbl_citation_dates
WHERE citation_info_id IN (
 SELECT CIX.citation_info_id 
   FROM dba.tbl_citation_information CIX
  WHERE CIX.registry_object_key = _registry_object_key
);


-- citation contributor name parts
DELETE FROM dba.tbl_name_parts NP
WHERE NP.name_part_id IN (
 SELECT NPX.name_part_id 
   FROM dba.tbl_name_parts NPX
   JOIN dba.tbl_citation_contributors CCX
     ON NPX.citation_contributor_id = CCX.citation_contributor_id
   JOIN dba.tbl_citation_information CIX
     ON CCX.citation_info_id = CIX.citation_info_id
  WHERE CIX.registry_object_key = _registry_object_key
);


-- citation contributors
DELETE FROM dba.tbl_citation_contributors
WHERE citation_info_id IN (
 SELECT CIX.citation_info_id 
   FROM dba.tbl_citation_information CIX
  WHERE CIX.registry_object_key = _registry_object_key
);

-- citation information
DELETE FROM dba.tbl_citation_information
WHERE registry_object_key = _registry_object_key;



-- electronic address args
DELETE FROM dba.tbl_electronic_address_args EAA
WHERE EAA.electronic_address_arg_id IN (
 SELECT EAAX.electronic_address_arg_id
   FROM dba.tbl_electronic_address_args EAAX
   JOIN dba.tbl_electronic_addresses EAX
     ON EAAX.electronic_address_id = EAX.electronic_address_id
   JOIN dba.tbl_address_locations ALX
     ON EAX.address_id = ALX.address_id
   JOIN dba.tbl_locations LX
     ON ALX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);

-- electronic addresses
DELETE FROM dba.tbl_electronic_addresses EA
WHERE EA.electronic_address_id IN (
 SELECT EAX.electronic_address_id
   FROM dba.tbl_electronic_addresses EAX
   JOIN dba.tbl_address_locations ALX
     ON EAX.address_id = ALX.address_id
   JOIN dba.tbl_locations LX
     ON ALX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);

-- address parts
DELETE FROM dba.tbl_address_parts AP
WHERE AP.address_part_id IN (
 SELECT APX.address_part_id
   FROM dba.tbl_address_parts APX
   JOIN dba.tbl_physical_addresses PAX
     ON APX.physical_address_id = PAX.physical_address_id
   JOIN dba.tbl_address_locations ALX
     ON PAX.address_id = ALX.address_id
   JOIN dba.tbl_locations LX
     ON ALX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);

-- physical addresses
DELETE FROM dba.tbl_physical_addresses PA
WHERE PA.physical_address_id IN (
 SELECT PAX.physical_address_id
   FROM dba.tbl_physical_addresses PAX
   JOIN dba.tbl_address_locations ALX
     ON PAX.address_id = ALX.address_id
   JOIN dba.tbl_locations LX
     ON ALX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);

-- address locations
DELETE FROM dba.tbl_address_locations AL
WHERE AL.address_id IN (
 SELECT ALX.address_id 
   FROM dba.tbl_address_locations ALX
   JOIN dba.tbl_locations LX
     ON ALX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);

-- spatial extent
DELETE FROM dba.tbl_spatial_extents
WHERE registry_object_key = _registry_object_key;

-- spatial locations
DELETE FROM dba.tbl_spatial_locations SL
WHERE SL.spatial_location_id IN (
 SELECT SLX.spatial_location_id 
   FROM dba.tbl_spatial_locations SLX
   JOIN dba.tbl_locations LX
     ON SLX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);

-- spatial coverages
DELETE FROM dba.tbl_spatial_locations SL
WHERE SL.spatial_location_id IN (
 SELECT SLX.spatial_location_id 
   FROM dba.tbl_spatial_locations SLX
   JOIN dba.tbl_coverage CX
     ON SLX.coverage_id = CX.coverage_id
  WHERE CX.registry_object_key = _registry_object_key
);

-- temporal coverage dates
DELETE FROM dba.tbl_temporal_coverage_dates CD
WHERE CD.coverage_date_id IN (
 SELECT CDX.coverage_date_id
   FROM dba.tbl_temporal_coverage_dates CDX
   NATURAL JOIN dba.tbl_temporal_coverage TCX
   NATURAL JOIN dba.tbl_coverage CX
  WHERE CX.registry_object_key = _registry_object_key
);

-- temporal coverage texts
DELETE FROM dba.tbl_temporal_coverage_text CT
WHERE CT.coverage_text_id IN (
 SELECT CTX.coverage_text_id
   FROM dba.tbl_temporal_coverage_text CTX
   NATURAL JOIN dba.tbl_temporal_coverage TCX
   NATURAL JOIN dba.tbl_coverage CX
  WHERE CX.registry_object_key = _registry_object_key
);

-- temporal coverage
DELETE FROM dba.tbl_temporal_coverage TC
WHERE TC.coverage_id IN (
 SELECT TCX.coverage_id
   FROM dba.tbl_temporal_coverage TCX
   NATURAL JOIN dba.tbl_coverage CX
  WHERE CX.registry_object_key = _registry_object_key
);

-- spatial coverage
DELETE FROM dba.tbl_spatial_locations SC
WHERE SC.coverage_id IN (
 SELECT SCX.coverage_id
   FROM dba.tbl_spatial_locations SCX
   NATURAL JOIN dba.tbl_coverage CX
  WHERE CX.registry_object_key = _registry_object_key
);

-- coverage
DELETE FROM dba.tbl_coverage C
WHERE C.registry_object_key = _registry_object_key;



-- locations
DELETE FROM dba.tbl_locations
WHERE registry_object_key = _registry_object_key;

-- relation descriptions
DELETE FROM dba.tbl_relation_descriptions RD
WHERE RD.relation_description_id IN (
 SELECT RDX.relation_description_id 
   FROM dba.tbl_relation_descriptions RDX
   JOIN dba.tbl_related_objects ROX
     ON RDX.relation_id = ROX.relation_id
  WHERE ROX.registry_object_key = _registry_object_key
);

-- related objects
DELETE FROM dba.tbl_related_objects
WHERE registry_object_key = _registry_object_key;

-- subjects
DELETE FROM dba.tbl_subjects
WHERE registry_object_key = _registry_object_key;

-- descriptions
DELETE FROM dba.tbl_descriptions
WHERE registry_object_key = _registry_object_key;

-- access policies
DELETE FROM dba.tbl_access_policies
WHERE registry_object_key = _registry_object_key;

-- related info
DELETE FROM dba.tbl_related_info
WHERE registry_object_key = _registry_object_key;

-- registry objects
DELETE FROM dba.tbl_registry_objects
WHERE registry_object_key = _registry_object_key;
END;

$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_delete_registry_object(character varying) OWNER TO dba;


-- Function: dba.udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character varying, character varying, character varying)

-- DROP FUNCTION dba.udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character varying, _record_owner character varying, _schema_version character varying)
  RETURNS void AS
$BODY$ 

INSERT INTO dba.tbl_registry_objects
(
  registry_object_key,
  registry_object_class,
  type,
  originating_source,
  originating_source_type,
  data_source_key,
  object_group,
  date_accessioned,
  date_modified,
  created_who,
  status,
  record_owner,
  schema_version
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6,
  $7,
  $8,
  $9,
  $10,
  $11,
  $12,
  $13
 )
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character varying, character varying, character varying) OWNER TO dba;

DROP FUNCTION dba.udf_get_registry_object_names(character varying);
DROP VIEW dba.vw_names;

-- View: dba.vw_names

-- DROP VIEW dba.vw_names;

CREATE OR REPLACE VIEW dba.vw_names AS 
 SELECT cn.registry_object_key, cn.complex_name_id, cn.type AS "name_type", cn.date_from, cn.date_to, np.value, np.type, cn.lang
   FROM dba.tbl_complex_names cn
   JOIN dba.tbl_name_parts np ON cn.complex_name_id = np.complex_name_id;

ALTER TABLE dba.vw_names OWNER TO dba;
GRANT ALL ON TABLE dba.vw_names TO dba;
GRANT SELECT ON TABLE dba.vw_names TO webuser;

-- Function: dba.udf_get_registry_object_names(character varying)

-- DROP FUNCTION dba.udf_get_registry_object_names(character varying);

CREATE OR REPLACE FUNCTION dba.udf_get_registry_object_names(_registry_object_key character varying)
  RETURNS SETOF dba.vw_names AS
$BODY$

SELECT 
 * 
FROM dba.vw_names
WHERE registry_object_key = $1
ORDER BY date_from DESC, UPPER(type) ASC
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_get_registry_object_names(character varying) OWNER TO dba;



-- Change many-to-many NULL constraints

ALTER TABLE dba.tbl_spatial_locations 
ALTER location_id DROP NOT NULL;

ALTER TABLE dba.tbl_name_parts
ALTER complex_name_id DROP NOT NULL;

ALTER TABLE dba.tbl_related_info
ALTER "value" DROP NOT NULL;


-- New table ids

INSERT INTO dba.tbl_ids VALUES ('dba.tbl_citation_contributors.citation_contributor_id', 0);
INSERT INTO dba.tbl_ids VALUES ('dba.tbl_citation_dates.metadata_date_id', 0);
INSERT INTO dba.tbl_ids VALUES ('dba.tbl_citation_information.citation_info_id', 0);
INSERT INTO dba.tbl_ids VALUES ('dba.tbl_temporal_coverage_dates.coverage_date_id', 0);
INSERT INTO dba.tbl_ids VALUES ('dba.tbl_temporal_coverage_text.coverage_text_id', 0);
INSERT INTO dba.tbl_ids VALUES ('dba.tbl_temporal_coverage.temporal_coverage_id', 0);
INSERT INTO dba.tbl_ids VALUES ('dba.tbl_coverage.coverage_id', 0);	
	

/* 	Update related_info table with new table structure
	(Deprecated "value" field becomes "identifier")
	This is only for users migrating to the 4.0 version.
*/

/* 	Update registry_objects table and mark all existing 
	entries as v1.1 (assume anything inserted before the
	migration script must be in v1.1)
	(Deprecated "value" field becomes "identifier")
	This is only for users migrating to the 4.0 version.
*/
