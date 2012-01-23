/*
Copyright 2012 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/


-- ORCA Database installation script (full)
-- Release version: 7.0 
-- Support/enquiries: services@ands.org.au
-- Created: 23/01/2012 (ben.greenwood@ands.org.au)

-- NOTE: This is a full installation script and should only be run
--       against a freshly created database. If you are upgrading from
--       a previous version of the registry then you should use the 
--       incremental update scripts in registry/db/


-- If you haven't yet created the database:
---   CREATE DATABASE dbs_orca ENCODING = 'UTF8' LC_COLLATE = 'en_AU.UTF-8' LC_CTYPE = 'en_AU.UTF-8';
---   ALTER DATABASE dbs_orca OWNER TO dba;
---   GRANT CONNECT, TEMPORARY ON DATABASE dbs_orca TO webuser;
---   GRANT ALL ON DATABASE dbs_orca TO dba;
---   \connect dbs_orca

-------------
-- SETUP THE DATABASE
-------------
-- Connection-specific settings
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

-- Create the default schema (dba)
CREATE SCHEMA dba;
ALTER SCHEMA dba OWNER TO dba;
DROP SCHEMA IF EXISTS public; 
SET search_path = dba;

-- Check that the plpgsql extension is loaded (requires postgres user)
CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';

-------------
-- CREATE SOME TYPES
-------------
CREATE TYPE nlapartyset AS (
	registry_object_key character varying,
	originating_source character varying,
	data_source_key character varying,
	data_source_title character varying,
	object_group character varying,
	date_accessioned timestamp with time zone,
	date_modified timestamp with time zone,
	created_when timestamp with time zone,
	registry_object_class character varying,
	type character varying,
	status character(1),
	record_owner character varying,
	isil_value character varying
);
ALTER TYPE dba.nlapartyset OWNER TO dba;

CREATE TYPE stat_type_count AS (
	the_type character varying,
	the_count bigint
);
ALTER TYPE dba.stat_type_count OWNER TO postgres;

CREATE TYPE udt_draft_registry_object AS (
	draft_owner character varying,
	draft_key character varying,
	class character varying,
	registry_object_group character varying,
	registry_object_title character varying,
	registry_object_data_source_key character varying,
	registry_object_data_source character varying,
	date_created timestamp with time zone,
	date_modified timestamp with time zone,
	rifcs text
);
ALTER TYPE dba.udt_draft_registry_object OWNER TO dba;

CREATE TYPE udt_name_search_result AS (
	registry_object_key character varying,
	display_title character varying,
	status character varying,
	type character varying
);
ALTER TYPE dba.udt_name_search_result OWNER TO dba;

CREATE TYPE udt_search_result AS (
	registry_object_key character varying,
	originating_source character varying,
	data_source_key character varying,
	data_source_title character varying,
	object_group character varying,
	date_accessioned timestamp with time zone,
	date_modified timestamp with time zone,
	created_when timestamp with time zone,
	registry_object_class character varying,
	type character varying,
	status character(20),
	record_owner character varying
);
ALTER TYPE dba.udt_search_result OWNER TO dba;

CREATE TYPE udt_term_search_result AS (
	name character varying,
	identifier character varying,
	vocabpath character varying,
	vocabulary_identifier character varying
);
ALTER TYPE dba.udt_term_search_result OWNER TO dba;





-------------
-- DATABASE FUNCTIONS
-------------
CREATE TABLE tbl_name_parts (
    name_part_id bigint NOT NULL,
    complex_name_id bigint,
    value character varying(512) NOT NULL,
    type character varying(512),
    citation_contributor_id bigint
);
ALTER TABLE dba.tbl_name_parts OWNER TO dba;

CREATE TABLE tbl_complex_names (
    complex_name_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    date_from timestamp(6) with time zone,
    date_to timestamp(6) with time zone,
    type character varying(512),
    lang character varying(64)
);
ALTER TABLE dba.tbl_complex_names OWNER TO dba;

CREATE TABLE tbl_access_policies (
    access_policy_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL
);
ALTER TABLE dba.tbl_access_policies OWNER TO dba;

CREATE TABLE tbl_address_locations (
    address_id bigint NOT NULL,
    location_id bigint NOT NULL
);
ALTER TABLE dba.tbl_address_locations OWNER TO dba;

CREATE TABLE tbl_address_parts (
    address_part_id bigint NOT NULL,
    physical_address_id bigint NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL
);
ALTER TABLE dba.tbl_address_parts OWNER TO dba;

CREATE TABLE tbl_citation_contributors (
    citation_contributor_id bigint NOT NULL,
    citation_info_id bigint,
    seq bigint
);
ALTER TABLE dba.tbl_citation_contributors OWNER TO dba;

CREATE TABLE tbl_citation_dates (
    metadata_date_id bigint NOT NULL,
    citation_info_id bigint,
    date character varying(512),
    type character varying(512)
);
ALTER TABLE dba.tbl_citation_dates OWNER TO dba;

CREATE TABLE tbl_citation_information (
    citation_info_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    style character varying(512),
    full_citation character varying(512),
    metadata_identifier character varying(512),
    metadata_type character varying(512),
    metadata_title character varying(512),
    metadata_edition character varying(512),
    metadata_place_published character varying(512),
    metadata_url character varying(512),
    metadata_context character varying(512),
    metadata_publisher character varying(512)
);
ALTER TABLE dba.tbl_citation_information OWNER TO dba;

CREATE TABLE tbl_coverage (
    coverage_id bigint NOT NULL,
    registry_object_key character varying(512)
);
ALTER TABLE dba.tbl_coverage OWNER TO dba;

CREATE TABLE tbl_data_source_logs (
    event_id bigint NOT NULL,
    data_source_key character varying(255) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255),
    request_ip character varying(24),
    event_description character varying(2000),
    log_type character varying DEFAULT 'INFO'::character varying
);
ALTER TABLE dba.tbl_data_source_logs OWNER TO dba;

CREATE TABLE tbl_data_sources (
    data_source_key character varying(255) NOT NULL,
    provider_type character varying(64),
    uri character varying(255),
    title character varying(255),
    contact_name character varying(128),
    contact_email character varying(128),
    notes character varying(2000),
    record_owner character varying(255),
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255),
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255),
    harvest_method character varying(64),
    oai_set character varying(255),
    harvest_date timestamp(6) with time zone,
    harvest_frequency character varying(64),
    isil_value character varying,
    push_to_nla boolean DEFAULT false,
    allow_reverse_internal_links boolean DEFAULT true NOT NULL,
    allow_reverse_external_links boolean DEFAULT false NOT NULL,
    assessement_notification_email_addr character varying,
    auto_publish boolean DEFAULT false NOT NULL,
    qa_flag boolean DEFAULT true NOT NULL,
    create_primary_relationships boolean DEFAULT false,
    primary_key_1 character varying(512),
    class_1 character varying(512),
    collection_rel_1 character varying(512),
    party_rel_1 character varying(512),
    activity_rel_1 character varying(512),
    service_rel_1 character varying(512),
    primary_key_2 character varying(512),
    class_2 character varying(512),
    collection_rel_2 character varying(512),
    party_rel_2 character varying(512),
    activity_rel_2 character varying(512),
    service_rel_2 character varying(512),
    time_zone_value character varying(512)
);
ALTER TABLE dba.tbl_data_sources OWNER TO dba;

CREATE TABLE tbl_descriptions (
    description_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(4000) NOT NULL,
    type character varying(512) NOT NULL,
    lang character varying(64)
);
ALTER TABLE dba.tbl_descriptions OWNER TO dba;

CREATE TABLE tbl_draft_registry_objects (
    draft_owner character varying(255) DEFAULT 'SYSTEM'::character varying NOT NULL,
    draft_key character varying(512) NOT NULL,
    class character varying(32) NOT NULL,
    registry_object_group character varying(512),
    registry_object_type character varying(32),
    registry_object_data_source character varying(255) NOT NULL,
    date_created timestamp(6) with time zone DEFAULT now(),
    date_modified timestamp(6) with time zone DEFAULT now(),
    rifcs text,
    registry_object_title character varying(512) DEFAULT '(no name/title)'::character varying NOT NULL,
    error_count integer,
    warning_count integer,
    quality_test_result text,
    flag boolean DEFAULT false NOT NULL,
    status character varying DEFAULT 'DRAFT'::character varying NOT NULL
);
ALTER TABLE dba.tbl_draft_registry_objects OWNER TO dba;

CREATE TABLE tbl_authorities (
    identifier character varying(1024) NOT NULL,
    identifier_type character varying(512),
    name character varying(1024),
    uri character varying(512)
);
ALTER TABLE dba.tbl_authorities OWNER TO dba;

CREATE TABLE tbl_ids (
    column_identifier character varying(128) NOT NULL,
    next_id bigint NOT NULL
);
ALTER TABLE dba.tbl_ids OWNER TO dba;

CREATE TABLE tbl_oai_rt_complete_list_records (
    record_number integer NOT NULL,
    complete_list_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL
);
ALTER TABLE dba.tbl_oai_rt_complete_list_records OWNER TO dba;

CREATE TABLE tbl_oai_rt_complete_lists (
    complete_list_id bigint NOT NULL
);
ALTER TABLE dba.tbl_oai_rt_complete_lists OWNER TO dba;

CREATE TABLE tbl_search_statistics (
    search_term character varying(256) NOT NULL,
    occurrence bigint DEFAULT 1 NOT NULL
);
ALTER TABLE dba.tbl_search_statistics OWNER TO dba;

CREATE TABLE tbl_spatial_extents (
    spatial_extents_id bigint NOT NULL,
    spatial_location_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    bound_box box NOT NULL
);
ALTER TABLE dba.tbl_spatial_extents OWNER TO dba;

CREATE TABLE tbl_statuses (
    status character varying NOT NULL
);
ALTER TABLE dba.tbl_statuses OWNER TO dba;

CREATE TABLE tbl_vocabularies (
    identifier character varying(1024) NOT NULL,
    identifier_type character varying(512),
    version character varying(512) NOT NULL,
    name character varying(1024) NOT NULL,
    name_type character varying(512) NOT NULL,
    description character varying(1024) NOT NULL,
    source character varying(512),
    authority_identifier character varying(512)
);
ALTER TABLE dba.tbl_vocabularies OWNER TO dba;

CREATE TABLE tbl_raw_records (
    registry_object_key character varying(512) NOT NULL,
    data_source character varying(255) NOT NULL,
    created_when timestamp with time zone NOT NULL,
    created_who character varying(255),
    rifcs_fragment text
);
ALTER TABLE dba.tbl_raw_records OWNER TO dba;

CREATE TABLE tbl_physical_addresses (
    physical_address_id bigint NOT NULL,
    address_id bigint NOT NULL,
    type character varying(512),
    lang character varying(64)
);
ALTER TABLE dba.tbl_physical_addresses OWNER TO dba;

CREATE TABLE tbl_electronic_address_args (
    electronic_address_arg_id bigint NOT NULL,
    electronic_address_id bigint NOT NULL,
    name character varying(512) NOT NULL,
    required boolean NOT NULL,
    type character varying(512) NOT NULL,
    use character varying(512)
);
ALTER TABLE dba.tbl_electronic_address_args OWNER TO dba;

CREATE TABLE tbl_electronic_addresses (
    electronic_address_id bigint NOT NULL,
    address_id bigint NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512)
);
ALTER TABLE dba.tbl_electronic_addresses OWNER TO dba;

CREATE TABLE tbl_existence_dates (
    registry_object_key character varying(512) NOT NULL,
    existence_date_id integer NOT NULL,
    start_date character varying(512),
    start_date_format character varying(512),
    end_date character varying(512),
    end_date_format character varying(512)
);
ALTER TABLE dba.tbl_existence_dates OWNER TO dba;


CREATE TABLE tbl_related_objects (
    relation_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    related_registry_object_key character varying(512) NOT NULL
);
ALTER TABLE dba.tbl_related_objects OWNER TO dba;


CREATE TABLE tbl_harvest_requests (
    harvest_request_id character varying(40) NOT NULL,
    data_source_key character varying(255) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255),
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255),
    status character varying(2000),
    harvester_base_uri character varying(255),
    response_target_uri character varying(255),
    source_uri character varying(255),
    method character varying(64),
    mode character varying(64),
    harvester_ip character varying(24),
    oai_set character varying(255),
    harvest_date timestamp(6) with time zone,
    harvest_frequency character varying(64)
);
ALTER TABLE dba.tbl_harvest_requests OWNER TO dba;


CREATE TABLE tbl_identifiers (
    identifier_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL
);
ALTER TABLE dba.tbl_identifiers OWNER TO dba;

CREATE TABLE tbl_registry_objects (
    registry_object_key character varying(512) NOT NULL,
    data_source_key character varying(255) NOT NULL,
    object_group character varying(512) NOT NULL,
    date_modified timestamp(6) with time zone,
    registry_object_class character varying(512) NOT NULL,
    type character varying(32) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    originating_source character varying(512) NOT NULL,
    originating_source_type character varying(512),
    date_accessioned timestamp(6) with time zone,
    status character varying DEFAULT 'APPROVED'::bpchar NOT NULL,
    record_owner character varying(255) DEFAULT 'SYSTEM'::character varying NOT NULL,
    status_modified_when timestamp(6) with time zone DEFAULT now(),
    status_modified_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    schema_version character varying(20),
    list_title character varying(512),
    display_title character varying(512),
    flag boolean DEFAULT false NOT NULL,
    quality_test_result text,
    warning_count integer,
    error_count integer,
    CONSTRAINT registry_object_class_check CHECK (((registry_object_class)::text = ANY (ARRAY[('Activity'::character varying)::text, ('Collection'::character varying)::text, ('Service'::character varying)::text, ('Party'::character varying)::text])))
);
ALTER TABLE dba.tbl_registry_objects OWNER TO dba;


CREATE TABLE tbl_locations (
    location_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    date_from timestamp(6) with time zone,
    date_to timestamp(6) with time zone,
    type character varying(512)
);
ALTER TABLE dba.tbl_locations OWNER TO dba;

CREATE TABLE tbl_spatial_locations (
    spatial_location_id bigint NOT NULL,
    location_id bigint,
    value character varying(4000) NOT NULL,
    type character varying(512) NOT NULL,
    lang character varying(64),
    coverage_id bigint
);
ALTER TABLE dba.tbl_spatial_locations OWNER TO dba;

CREATE TABLE tbl_related_info (
    related_info_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512),
    notes character varying(512),
    title character varying(512),
    identifier_type character varying(512),
    identifier character varying(512),
    info_type character varying(64)
);
ALTER TABLE dba.tbl_related_info OWNER TO dba;

CREATE TABLE tbl_relation_descriptions (
    relation_description_id bigint NOT NULL,
    relation_id bigint NOT NULL,
    description character varying(512),
    type character varying(512) NOT NULL,
    lang character varying(64),
    url character varying(512)
);
ALTER TABLE dba.tbl_relation_descriptions OWNER TO dba;

CREATE TABLE tbl_oai_rt_resumption_tokens (
    resumption_token_id character varying(40) NOT NULL,
    complete_list_id bigint NOT NULL,
    status integer NOT NULL,
    first_record_number integer NOT NULL,
    complete_list_size integer NOT NULL,
    expiration_date timestamp(6) with time zone NOT NULL,
    metadata_prefix character varying(64) NOT NULL
);
ALTER TABLE dba.tbl_oai_rt_resumption_tokens OWNER TO dba;

CREATE TABLE tbl_rights (
    rights_id integer NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    rights_statement character varying(4000),
    rights_statement_uri character varying(512),
    licence character varying(4000),
    access_rights character varying(4000),
    access_rights_uri character varying(512),
    access_rights_type character varying(512),
    licence_uri character varying(512),
    licence_type character varying(512)
);
ALTER TABLE dba.tbl_rights OWNER TO dba;

CREATE TABLE tbl_registry_objects_statistics (
    registry_object_count bigint,
    collection_object_count bigint,
    party_object_count bigint,
    activity_object_count bigint,
    service_object_count bigint,
    count_date date NOT NULL,
    trusted_sw_agreements_count bigint
);
ALTER TABLE dba.tbl_registry_objects_statistics OWNER TO dba;

CREATE TABLE tbl_terms (
    identifier character varying(1024) NOT NULL,
    identifier_type character varying(512),
    qualifier character varying(512),
    name character varying(512),
    description character varying(1024),
    description_type character varying(512),
    vocabulary_identifier character varying(1024) NOT NULL,
    parent_term_identifier character varying(1024),
    type character varying(512) NOT NULL,
    relationtype character varying(512),
    vocabpath character varying(1024),
    lang character varying(512)
);
ALTER TABLE dba.tbl_terms OWNER TO dba;

CREATE TABLE tbl_subjects (
    subject_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL,
    lang character varying(64),
    "termIdentifier" character varying(512)
);


ALTER TABLE dba.tbl_subjects OWNER TO dba;


CREATE TABLE tbl_temporal_coverage (
    temporal_coverage_id bigint NOT NULL,
    coverage_id bigint
);


ALTER TABLE dba.tbl_temporal_coverage OWNER TO dba;



CREATE TABLE tbl_temporal_coverage_dates (
    coverage_date_id bigint NOT NULL,
    temporal_coverage_id bigint,
    type character varying(512),
    date_format character varying(512),
    value character varying(512),
    timestamp_value timestamp(6) with time zone
);
ALTER TABLE dba.tbl_temporal_coverage_dates OWNER TO dba;

CREATE TABLE tbl_temporal_coverage_text (
    coverage_text_id bigint NOT NULL,
    temporal_coverage_id bigint,
    value character varying(512)
);


ALTER TABLE dba.tbl_temporal_coverage_text OWNER TO dba;


---
-- SEQUENCES
---
CREATE SEQUENCE tbl_citation_contributors_citation_contributor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_citation_contributors_citation_contributor_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_citation_contributors_citation_contributor_id_seq OWNED BY tbl_citation_contributors.citation_contributor_id;

CREATE SEQUENCE tbl_citation_dates_metadata_date_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_citation_dates_metadata_date_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_citation_dates_metadata_date_id_seq OWNED BY tbl_citation_dates.metadata_date_id;

CREATE SEQUENCE tbl_citation_information_citation_info_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_citation_information_citation_info_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_citation_information_citation_info_id_seq OWNED BY tbl_citation_information.citation_info_id;

CREATE SEQUENCE tbl_coverage_coverage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_coverage_coverage_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_coverage_coverage_id_seq OWNED BY tbl_coverage.coverage_id;

CREATE SEQUENCE tbl_temporal_coverage_dates_coverage_date_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_temporal_coverage_dates_coverage_date_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_temporal_coverage_dates_coverage_date_id_seq OWNED BY tbl_temporal_coverage_dates.coverage_date_id;

CREATE SEQUENCE tbl_temporal_coverage_temporal_coverage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_temporal_coverage_temporal_coverage_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_temporal_coverage_temporal_coverage_id_seq OWNED BY tbl_temporal_coverage.temporal_coverage_id;

CREATE SEQUENCE tbl_temporal_coverage_text_coverage_text_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE dba.tbl_temporal_coverage_text_coverage_text_id_seq OWNER TO dba;
ALTER SEQUENCE tbl_temporal_coverage_text_coverage_text_id_seq OWNED BY tbl_temporal_coverage_text.coverage_text_id;




-------------
-- DATABASE FUNCTIONS
-------------
CREATE FUNCTION limit_raw_records() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
theTenthRecord timestamp;
raw_record_keys RECORD;
BEGIN
FOR raw_record_keys IN SELECT DISTINCT registry_object_key FROM
dba.tbl_raw_records
LOOP
       theTenthRecord := NULL;
       RAISE NOTICE 'registry_object_key:%',
raw_record_keys.registry_object_key;
       SELECT INTO theTenthRecord created_when FROM dba.tbl_raw_records
               WHERE registry_object_key = raw_record_keys.registry_object_key
               ORDER BY created_when DESC
               LIMIT 1 OFFSET 9;
       IF theTenthRecord IS NOT NULL THEN
       RAISE NOTICE 'theTenthRecord:%', theTenthRecord;
               DELETE FROM dba.tbl_raw_records
               WHERE registry_object_key = raw_record_keys.registry_object_key
               AND created_when < theTenthRecord;
       END IF;
END LOOP;
END;
$$;
ALTER FUNCTION dba.limit_raw_records() OWNER TO dba;

CREATE FUNCTION udf_cleanup_complete_lists() RETURNS void
    LANGUAGE sql
    AS $$

-- Delete all expired resumption tokens.
DELETE FROM dba.tbl_oai_rt_resumption_tokens WHERE expiration_date <= now();

-- Delete all expired data.
DELETE FROM dba.tbl_oai_rt_complete_list_records 
WHERE complete_list_id NOT IN
(SELECT complete_list_id
   FROM dba.tbl_oai_rt_resumption_tokens
);

-- Delete unused complete lists.
DELETE FROM dba.tbl_oai_rt_complete_lists 
WHERE complete_list_id NOT IN
(SELECT DISTINCT complete_list_id
  FROM dba.tbl_oai_rt_resumption_tokens
)
  AND complete_list_id NOT IN
(SELECT complete_list_id
   FROM dba.tbl_oai_rt_complete_list_records
);

$$;
ALTER FUNCTION dba.udf_cleanup_complete_lists() OWNER TO dba;

CREATE FUNCTION udf_delete_all_registry_objects() RETURNS void
    LANGUAGE sql
    AS $$ 

-- identifiers
TRUNCATE TABLE dba.tbl_identifiers;

-- simple names
TRUNCATE TABLE dba.tbl_simple_names;

-- name parts
TRUNCATE TABLE dba.tbl_name_parts;

-- complex names
TRUNCATE TABLE dba.tbl_complex_names CASCADE;

-- electronic address args
TRUNCATE TABLE dba.tbl_electronic_address_args;

-- electronic addresses
TRUNCATE TABLE dba.tbl_electronic_addresses CASCADE;

-- address parts
TRUNCATE TABLE dba.tbl_address_parts;

-- physical addresses
TRUNCATE TABLE dba.tbl_physical_addresses CASCADE;

-- address locations
TRUNCATE TABLE dba.tbl_address_locations CASCADE;

-- spatial locations
TRUNCATE TABLE dba.tbl_spatial_locations;

-- locations
TRUNCATE TABLE dba.tbl_locations CASCADE;

-- relation descriptions
TRUNCATE TABLE dba.tbl_relation_descriptions;

-- related objects
TRUNCATE TABLE dba.tbl_related_objects CASCADE;

-- subjects
TRUNCATE TABLE dba.tbl_subjects;

-- descriptions
TRUNCATE TABLE dba.tbl_descriptions;

-- access policies
TRUNCATE TABLE dba.tbl_access_policies;

-- related info
TRUNCATE TABLE dba.tbl_related_info;

-- registry objects
TRUNCATE TABLE dba.tbl_registry_objects CASCADE;

$$;
ALTER FUNCTION dba.udf_delete_all_registry_objects() OWNER TO dba;

CREATE FUNCTION udf_delete_data_source(_data_source_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_data_source_logs
WHERE data_source_key = $1;

DELETE FROM dba.tbl_harvest_requests
WHERE data_source_key = $1;

DELETE FROM dba.tbl_data_sources
WHERE data_source_key = $1 AND data_source_key <> 'SYSTEM';
$_$;
ALTER FUNCTION dba.udf_delete_data_source(_data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_delete_data_source_log(_data_source_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_data_source_logs
WHERE data_source_key = $1;
$_$;
ALTER FUNCTION dba.udf_delete_data_source_log(_data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_delete_draft_registry_object(_registry_object_data_source character varying, _draft_key character varying) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 

BEGIN

-- delete draft
DELETE FROM dba.tbl_draft_registry_objects
WHERE draft_key = $2 AND registry_object_data_source = $1;

END;

$_$;
ALTER FUNCTION dba.udf_delete_draft_registry_object(_registry_object_data_source character varying, _draft_key character varying) OWNER TO dba;

CREATE FUNCTION udf_delete_harvest_request(_harvest_request_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_harvest_requests
WHERE harvest_request_id = $1
;
$_$;
ALTER FUNCTION dba.udf_delete_harvest_request(_harvest_request_id character varying) OWNER TO dba;

CREATE FUNCTION udf_delete_registry_object(_registry_object_key character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$

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

-- existenceDates
DELETE FROM dba.tbl_existence_dates
WHERE registry_object_key = _registry_object_key;

-- rights
DELETE FROM dba.tbl_rights
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

$$;
ALTER FUNCTION dba.udf_delete_registry_object(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_filter_registry(_filter_string character varying, _classes character varying, _object_group character varying) RETURNS SETOF udt_search_result
    LANGUAGE sql
    AS $_$ 

SELECT
 registry_object_key,
 originating_source,
 data_source_key,
 data_source_title,
 object_group,
 date_accessioned,
 date_modified,
 created_when,
 registry_object_class,
 type,
 status,
 record_owner
FROM
(
 SELECT DISTINCT ON (registry_object_key)
  ro.registry_object_key,
  ro.originating_source,
  ro.data_source_key,
  ro.data_source_title,
  ro.object_group,
  ro.date_accessioned,
  ro.date_modified,
  ro.created_when,
  ro.registry_object_class,
  ro.type,
  ro.status,
  ro.record_owner,
  n.value AS name_value
 FROM dba.vw_registry_search ro
 LEFT OUTER JOIN dba.vw_names n ON ro.registry_object_key = n.registry_object_key
 WHERE ( 
             n.value ~* ('^'||$1)
          OR n.value ~* ('^[^a-zA-Z]*'||$1)
          OR n.value ~* ('^THE '||$1)
          OR n.value ~* ('^A '||$1)
          OR ($1 = 'ZZ' AND n.value !~* ('^[^a-zA-Z]*[a-zA-Z]'))
          OR ($1 = 'ZZ' AND n.value IS NULL)
          OR n.value IS NULL
       )
   AND $2 ~* registry_object_class
   AND ($3 IS NULL OR object_group = $3)
   AND status = 'PUBLISHED'
 ) AS ordered_result
ORDER BY UPPER(name_value) ASC
;
$_$;
ALTER FUNCTION dba.udf_filter_registry(_filter_string character varying, _classes character varying, _object_group character varying) OWNER TO dba;

CREATE FUNCTION udf_filter_registry_count(_filter_string character varying, _class character varying) RETURNS SETOF bigint
    LANGUAGE sql
    AS $_$ 


 SELECT COUNT(DISTINCT ro.registry_object_key)
 FROM dba.tbl_registry_objects ro
 LEFT OUTER JOIN dba.vw_names n ON ro.registry_object_key = n.registry_object_key
 WHERE ( 
             n.value ~* ('^'||$1)
          OR n.value ~* ('^[^a-zA-Z]*'||$1)
          OR n.value ~* ('^THE '||$1)
          OR n.value ~* ('^A '||$1)
          OR ($1 = 'ZZ' AND n.value !~* ('^[^a-zA-Z]*[a-zA-Z]'))
          OR ($1 = 'ZZ' AND n.value IS NULL)
       )
   AND $2 = ro.registry_object_class
   AND ro.status = 'PUBLISHED'
;
$_$;
ALTER FUNCTION dba.udf_filter_registry_count(_filter_string character varying, _class character varying) OWNER TO dba;

CREATE FUNCTION udf_get_access_policies(_registry_object_key character varying) RETURNS SETOF tbl_access_policies
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_access_policies
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_access_policies(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_address_locations(_location_id bigint) RETURNS SETOF tbl_address_locations
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_address_locations
WHERE location_id = $1
;
$_$;
ALTER FUNCTION dba.udf_get_address_locations(_location_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_address_parts(_physical_address_id bigint) RETURNS SETOF tbl_address_parts
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_address_parts
WHERE physical_address_id = $1
ORDER BY address_part_id ASC
;
$_$;
ALTER FUNCTION dba.udf_get_address_parts(_physical_address_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_citation_contributors(_citation_info_id bigint) RETURNS SETOF tbl_citation_contributors
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_citation_contributors
WHERE citation_info_id = $1
ORDER BY seq ASC
;
$_$;
ALTER FUNCTION dba.udf_get_citation_contributors(_citation_info_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_citation_dates(_citation_info_id bigint) RETURNS SETOF tbl_citation_dates
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_citation_dates
WHERE citation_info_id = $1
ORDER BY UPPER(type) ASC
;
$_$;
ALTER FUNCTION dba.udf_get_citation_dates(_citation_info_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_citation_information(_registry_object_key character varying) RETURNS SETOF tbl_citation_information
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_citation_information
WHERE registry_object_key = $1
ORDER BY citation_info_id ASC
;
$_$;
ALTER FUNCTION dba.udf_get_citation_information(_registry_object_key character varying) OWNER TO dba;


CREATE FUNCTION udf_get_complex_names(_registry_object_key character varying) RETURNS SETOF tbl_complex_names
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_complex_names
WHERE registry_object_key = $1
ORDER BY date_from DESC, UPPER(type) ASC
;
$_$;
ALTER FUNCTION dba.udf_get_complex_names(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_contributor_name_parts(_citation_contributor_id bigint) RETURNS SETOF tbl_name_parts
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_name_parts
WHERE citation_contributor_id = $1
ORDER BY UPPER(type) ASC
;
$_$;
ALTER FUNCTION dba.udf_get_contributor_name_parts(_citation_contributor_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_coverage(_registry_object_key character varying) RETURNS SETOF tbl_coverage
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_coverage
WHERE registry_object_key = $1
ORDER BY coverage_id ASC
;
$_$;
AlTER FUNCTION dba.udf_get_coverage(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_data_source_count(_date_filter character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$SELECT
COUNT(*)
FROM dba.tbl_data_sources 
WHERE data_source_key != 'PUBLISH_MY_DATA'
AND created_when <= CAST($1 AS timestamp with time zone)
;$_$;
ALTER FUNCTION dba.udf_get_data_source_count(_date_filter character varying) OWNER TO dba;

CREATE FUNCTION udf_get_data_source_log(_data_source_key character varying) RETURNS SETOF tbl_data_source_logs
    LANGUAGE sql
    AS $_$ 

SELECT * FROM dba.tbl_data_source_logs
WHERE data_source_key = $1
ORDER BY created_when DESC
LIMIT 20;
$_$;
ALTER FUNCTION dba.udf_get_data_source_log(_data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_data_sources(_data_source_key character varying, _filter character varying) RETURNS SETOF tbl_data_sources
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.tbl_data_sources
WHERE 
(
  data_source_key = $1
  OR $1 IS NULL
) 
AND 
(
  title ~* $2
  OR $2 IS NULL
)
AND data_source_key <> 'PUBLISH_MY_DATA'
ORDER BY title ASC
;

$_$;
ALTER FUNCTION dba.udf_get_data_sources(_data_source_key character varying, _filter character varying) OWNER TO dba;

CREATE FUNCTION udf_get_descriptions(_registry_object_key character varying) RETURNS SETOF tbl_descriptions
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_descriptions
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_descriptions(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_descriptions_type_count(the_month character varying) RETURNS SETOF stat_type_count
    LANGUAGE sql
    AS $$select type, COUNT(type) as type_count
from dba.tbl_descriptions

GROUP BY type
ORDER BY type$$;


ALTER FUNCTION dba.udf_get_descriptions_type_count(the_month character varying) OWNER TO dba;

CREATE FUNCTION udf_get_draft_count_by_status(_data_source_key character varying, _status character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$

SELECT 
 COUNT(*)
FROM dba.tbl_draft_registry_objects
WHERE registry_object_data_source = $1
AND status = $2;
$_$;
ALTER FUNCTION dba.udf_get_draft_count_by_status(_data_source_key character varying, _status character varying) OWNER TO dba;

CREATE FUNCTION udf_get_draft_registry_object(_draft_key character varying, _registry_object_data_source_key character varying) RETURNS SETOF tbl_draft_registry_objects
    LANGUAGE sql
    AS $_$

SELECT
 *
FROM dba.tbl_draft_registry_objects dro

WHERE ( $1 = draft_key OR $1 IS NULL )
     AND
     ( $2 = registry_object_data_source OR $2 IS NULL )

ORDER BY date_created DESC;

$_$;
ALTER FUNCTION dba.udf_get_draft_registry_object(_draft_key character varying, _registry_object_data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_earliest_created_when() RETURNS SETOF timestamp with time zone
    LANGUAGE sql
    AS $$

SELECT MIN(created_when) FROM dba.tbl_registry_objects
WHERE status='PUBLISHED';
;
$$;
ALTER FUNCTION dba.udf_get_earliest_created_when() OWNER TO dba;

CREATE FUNCTION udf_get_electronic_address_args(_electronic_address_id bigint) RETURNS SETOF tbl_electronic_address_args
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_electronic_address_args
WHERE electronic_address_id = $1
ORDER BY required DESC
;
$_$;
ALTER FUNCTION dba.udf_get_electronic_address_args(_electronic_address_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_electronic_addresses(_address_id bigint) RETURNS SETOF tbl_electronic_addresses
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_electronic_addresses
WHERE address_id = $1
;
$_$;
ALTER FUNCTION dba.udf_get_electronic_addresses(_address_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_existence_dates(_registry_object_key character varying) RETURNS SETOF tbl_existence_dates
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_existence_dates
WHERE registry_object_key = $1
ORDER BY existence_date_id ASC
;

$_$;
ALTER FUNCTION dba.udf_get_existence_dates(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_external_reverse_related_objects(_registry_object_key character varying, _data_source_key character varying) RETURNS SETOF tbl_related_objects
    LANGUAGE sql
    AS $_$

SELECT 
 rel.* 
FROM dba.tbl_related_objects rel, dba.tbl_registry_objects ro
WHERE related_registry_object_key = $1
AND ro.registry_object_key = rel.registry_object_key 
AND ro.data_source_key != $2
;
$_$;
ALTER FUNCTION dba.udf_get_external_reverse_related_objects(_registry_object_key character varying, _data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_harvest_requests(_harvest_request_id character varying, _data_source_key character varying) RETURNS SETOF tbl_harvest_requests
    LANGUAGE sql
    AS $_$ 

SELECT * FROM dba.tbl_harvest_requests
WHERE (harvest_request_id = $1 OR $1 IS NULL )
  AND (data_source_key = $2 OR $2 IS NULL)
ORDER BY created_when DESC
;
$_$;
ALTER FUNCTION dba.udf_get_harvest_requests(_harvest_request_id character varying, _data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_highlighted_querytext(_text character varying, _querytext character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$

SELECT ts_headline($1, plainto_tsquery($2), 'StartSel=@@@@, StopSel=$$$$, MinWords=1, MaxWords=4000, HighlightAll=TRUE')
;
$_$;
ALTER FUNCTION dba.udf_get_highlighted_querytext(_text character varying, _querytext character varying) OWNER TO dba;

CREATE FUNCTION udf_get_id(_column_identifier character varying) RETURNS bigint
    LANGUAGE plpgsql
    AS $$ 


DECLARE
  this_id bigint := 0;
BEGIN

  LOCK TABLE dba.tbl_ids;
  
  this_id := (SELECT next_id FROM dba.tbl_ids WHERE column_identifier = _column_identifier) + 1;

  UPDATE dba.tbl_ids
     SET next_id = this_id
   WHERE column_identifier = _column_identifier;

  RETURN this_id;

END;
$$;
ALTER FUNCTION dba.udf_get_id(_column_identifier character varying) OWNER TO dba;

CREATE FUNCTION udf_get_identifiers(_registry_object_key character varying) RETURNS SETOF tbl_identifiers
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_identifiers
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_identifiers(_registry_object_key character varying) OWNER TO dba;


CREATE VIEW vw_registry_objects AS
    SELECT ro.registry_object_key, ro.originating_source, ro.originating_source_type, ro.data_source_key, ds.title AS data_source_title, ro.object_group, ro.date_accessioned, ro.date_modified, ro.registry_object_class, ro.type, ro.created_when, ro.created_who, ro.status_modified_when, ro.status_modified_who, ro.status, ro.record_owner, ds.isil_value, ro.display_title, ro.list_title, ro.error_count, ro.warning_count, ro.quality_test_result, ro.flag FROM (tbl_registry_objects ro JOIN tbl_data_sources ds ON (((ro.data_source_key)::text = (ds.data_source_key)::text)));
ALTER TABLE dba.vw_registry_objects OWNER TO dba;







CREATE FUNCTION udf_get_incomplete_list(_complete_list_id integer, _first_record_number integer, _num_records integer) RETURNS SETOF vw_registry_objects
    LANGUAGE sql
    AS $_$ 

SELECT * FROM dba.vw_registry_objects
WHERE registry_object_key IN
(
SELECT registry_object_key
 FROM dba.tbl_oai_rt_complete_list_records
WHERE complete_list_id = $1
  AND record_number >= $2
  AND record_number < $2+$3
)
;
$_$;


ALTER FUNCTION dba.udf_get_incomplete_list(_complete_list_id integer, _first_record_number integer, _num_records integer) OWNER TO dba;







CREATE FUNCTION udf_get_internal_reverse_related_objects(_registry_object_key character varying, _data_source_key character varying) RETURNS SETOF tbl_related_objects
    LANGUAGE sql
    AS $_$

SELECT 
 rel.* 
FROM dba.tbl_related_objects rel, dba.tbl_registry_objects ro
WHERE related_registry_object_key = $1
AND ro.registry_object_key = rel.registry_object_key 
AND ro.data_source_key = $2
;
$_$;


ALTER FUNCTION dba.udf_get_internal_reverse_related_objects(_registry_object_key character varying, _data_source_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_locations(_registry_object_key character varying) RETURNS SETOF tbl_locations
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_locations
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_locations(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_name_parts(_complex_name_id bigint) RETURNS SETOF tbl_name_parts
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_name_parts
WHERE complex_name_id = $1
ORDER BY name_part_id ASC
;
$_$;
ALTER FUNCTION dba.udf_get_name_parts(_complex_name_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_nla_nonlinked_related_objects() RETURNS SETOF character varying
    LANGUAGE sql
    AS $$SELECT DISTINCT(related_registry_object_key) FROM dba.tbl_related_objects 
WHERE related_registry_object_key LIKE 'http://nla.gov.au/nla.party%' 
AND related_registry_object_key NOT IN 
(SELECT registry_object_key FROM dba.tbl_registry_objects)$$;
ALTER FUNCTION dba.udf_get_nla_nonlinked_related_objects() OWNER TO dba;

CREATE FUNCTION udf_get_nla_set(class character varying) RETURNS SETOF nlapartyset
    LANGUAGE sql
    AS $_$SELECT 
ro.registry_object_key,
ro.originating_source,
ro.data_source_key,
ds.title as data_source_title,
ro.object_group,
ro.date_accessioned,
ro.date_modified,
ro.created_when,
ro.registry_object_class,
ro.type,
ro.status,
ro.record_owner,
ds.isil_value
FROM dba.tbl_registry_objects AS ro, dba.tbl_data_sources AS ds
WHERE upper(ro.registry_object_class)=upper($1)
AND ro.data_source_key = ds.data_source_key
AND push_to_nla$_$;
ALTER FUNCTION dba.udf_get_nla_set(class character varying) OWNER TO dba;

CREATE FUNCTION udf_get_object_groups() RETURNS SETOF character varying
    LANGUAGE sql
    AS $$ 

SELECT DISTINCT object_group
FROM dba.tbl_registry_objects
ORDER BY object_group ASC
;

$$;
ALTER FUNCTION dba.udf_get_object_groups() OWNER TO dba;

CREATE FUNCTION udf_get_party_nla_identifiers() RETURNS SETOF character varying
    LANGUAGE sql
    AS $$SELECT trim(value) FROM dba.tbl_identifiers,dba.tbl_registry_objects 
WHERE dba.tbl_identifiers.value LIKE 'http://nla.gov.au/nla.party%' 
AND lower(dba.tbl_registry_objects.registry_object_class) = 'party'
AND trim(dba.tbl_registry_objects.registry_object_key) = trim(dba.tbl_identifiers.registry_object_key)
AND trim(value) NOT IN 
(SELECT registry_object_key FROM dba.tbl_registry_objects)$$;
ALTER FUNCTION dba.udf_get_party_nla_identifiers() OWNER TO dba;

CREATE FUNCTION udf_get_physical_addresses(_address_id bigint) RETURNS SETOF tbl_physical_addresses
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_physical_addresses
WHERE address_id = $1
;
$_$;
ALTER FUNCTION dba.udf_get_physical_addresses(_address_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_publish_my_data_object_count(_date_filter character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$SELECT
COUNT(*)
FROM dba.tbl_registry_objects
WHERE upper(object_group) = 'PUBLISH MY DATA'
AND created_when <= CAST($1 AS timestamp with time zone);$_$;
ALTER FUNCTION dba.udf_get_publish_my_data_object_count(_date_filter character varying) OWNER TO dba;

CREATE FUNCTION udf_get_raw_records(_registry_object_key character varying, _data_source character varying, _created_when timestamp with time zone) RETURNS SETOF tbl_raw_records
    LANGUAGE sql
    AS $_$


SELECT
 *
FROM dba.tbl_raw_records


WHERE ( $1 = registry_object_key OR $1 IS NULL )
     AND
     ( $2 = data_source OR $2 IS NULL )
     AND 
     ( created_when BETWEEN ($3) AND ($3 + '2 second') OR $3 IS NULL )


ORDER BY created_when ASC;
$_$;
ALTER FUNCTION dba.udf_get_raw_records(_registry_object_key character varying, _data_source character varying, _created_when timestamp with time zone) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object(_registry_object_key character varying) RETURNS SETOF vw_registry_objects
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.vw_registry_objects
WHERE registry_object_key = $1;

$_$;
ALTER FUNCTION dba.udf_get_registry_object(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_count(_data_source_key character varying, _object_group character varying, _registry_object_class character varying, _status character) RETURNS bigint
    LANGUAGE sql
    AS $_$

SELECT COUNT('x')
  FROM dba.tbl_registry_objects
 WHERE ($1 IS NULL OR data_source_key = $1)
   AND ($2 IS NULL OR object_group = $2)
   AND ($3 IS NULL OR registry_object_class = $3)
   AND ($4 IS NULL OR status = $4)
;

$_$;
ALTER FUNCTION dba.udf_get_registry_object_count(_data_source_key character varying, _object_group character varying, _registry_object_class character varying, _status character) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_electronic_addresses(_registry_object_key character varying) RETURNS SETOF tbl_electronic_addresses
    LANGUAGE sql
    AS $_$

SELECT 
 EA.* 
FROM dba.tbl_electronic_addresses EA
JOIN dba.tbl_address_locations AL ON EA.address_id = AL.address_id
JOIN dba.tbl_locations L ON AL.location_id = L.location_id
WHERE L.registry_object_key = $1
ORDER BY UPPER(EA.type) ASC
;
$_$;
ALTER FUNCTION dba.udf_get_registry_object_electronic_addresses(_registry_object_key character varying) OWNER TO dba;

CREATE VIEW vw_names AS
    SELECT cn.registry_object_key, cn.complex_name_id, cn.type AS name_type, cn.date_from, cn.date_to, np.value, np.type, cn.lang FROM (tbl_complex_names cn JOIN tbl_name_parts np ON ((cn.complex_name_id = np.complex_name_id)));
ALTER TABLE dba.vw_names OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_names(_registry_object_key character varying) RETURNS SETOF vw_names
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.vw_names
WHERE registry_object_key = $1
ORDER BY date_from DESC, UPPER(type) ASC
;
$_$;
ALTER FUNCTION dba.udf_get_registry_object_names(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_physical_addresses(_registry_object_key character varying) RETURNS SETOF tbl_physical_addresses
    LANGUAGE sql
    AS $_$

SELECT 
 PA.*
FROM dba.tbl_physical_addresses PA
JOIN dba.tbl_address_locations AL ON PA.address_id = AL.address_id
JOIN dba.tbl_locations L ON AL.location_id = L.location_id
WHERE L.registry_object_key = $1
ORDER BY L.date_from DESC
;
$_$;
ALTER FUNCTION dba.udf_get_registry_object_physical_addresses(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_spatial_locations(_registry_object_key character varying) RETURNS SETOF tbl_spatial_locations
    LANGUAGE sql
    AS $_$

SELECT 
 SL.*
FROM dba.tbl_spatial_locations SL
JOIN dba.tbl_locations L
  ON SL.location_id = L.location_id
AND L.registry_object_key = $1
ORDER BY L.date_from DESC, UPPER(SL.type) ASC
;
$_$;
ALTER FUNCTION dba.udf_get_registry_object_spatial_locations(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_stat_count(_date_filter character varying, _class_filter character varying) RETURNS bigint
    LANGUAGE sql
    AS $_$SELECT COUNT(*)
  FROM dba.tbl_registry_objects
 WHERE (created_when <= CAST($1 AS timestamp with time zone))
   AND ($2 IS NULL OR registry_object_class = $2)$_$;
ALTER FUNCTION dba.udf_get_registry_object_stat_count(_date_filter character varying, _class_filter character varying) OWNER TO dba;

CREATE FUNCTION udf_get_registry_object_type_count(the_month character varying, registry_object_class character varying, the_type character varying) RETURNS SETOF stat_type_count
    LANGUAGE sql
    AS $_$select type, COUNT(registry_object_class) as type_count
from dba.tbl_registry_objects 
WHERE ($2 is NULL or registry_object_class = $2)
AND ($1 is NULL or created_when < CAST($1 as timestamp with time zone))
AND ($3 is NULL or type = $3)
GROUP BY registry_object_class,type
ORDER BY registry_object_class$_$;
ALTER FUNCTION dba.udf_get_registry_object_type_count(the_month character varying, registry_object_class character varying, the_type character varying) OWNER TO dba;

CREATE FUNCTION udf_get_registry_objects_for_data_source(_key character varying) RETURNS SETOF tbl_registry_objects
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.tbl_registry_objects
WHERE data_source_key = $1;

$_$;
ALTER FUNCTION dba.udf_get_registry_objects_for_data_source(_key character varying) OWNER TO dba;

CREATE VIEW vw_registry_search AS
    SELECT ro.registry_object_key, ro.originating_source, ro.data_source_key, ro.data_source_title, ro.object_group, ro.date_accessioned, ro.date_modified, ro.created_when, ro.registry_object_class, ro.type, i.value AS identifier_value, i.type AS identifier_type, ro.status, ro.record_owner, ro.error_count, ro.warning_count, ro.quality_test_result, ro.flag FROM (vw_registry_objects ro LEFT JOIN tbl_identifiers i ON (((ro.registry_object_key)::text = (i.registry_object_key)::text)));
ALTER TABLE dba.vw_registry_search OWNER TO dba;

CREATE FUNCTION udf_get_registry_objects_inbound(north real, south real, west real, east real) RETURNS SETOF vw_registry_search
    LANGUAGE sql
    AS $_$ 
SELECT rs.* FROM dba.vw_registry_search rs, dba.tbl_spatial_extents se
WHERE rs.registry_object_key = se.registry_object_key 
AND se.bound_box && box ((point($1,$3)),(point($2,$4)))
AND rs.status = 'APPROVED'
ORDER BY area(se.bound_box) ASC;
$_$;
ALTER FUNCTION dba.udf_get_registry_objects_inbound(north real, south real, west real, east real) OWNER TO dba;

CREATE FUNCTION udf_get_registry_objects_inbound_two(north real, south real, west real, east real, north2 real, south2 real, west2 real, east2 real) RETURNS SETOF vw_registry_search
    LANGUAGE sql
    AS $_$ 
SELECT rs.* FROM dba.vw_registry_search rs, dba.tbl_spatial_extents se
WHERE rs.registry_object_key = se.registry_object_key 
AND (se.bound_box && box ((point($1,$3)),(point($2,$4))) OR se.bound_box && box ((point($5,$7)),(point($6,$8))))
AND rs.status = 'APPROVED'
ORDER BY area(se.bound_box) ASC;
$_$;
ALTER FUNCTION dba.udf_get_registry_objects_inbound_two(north real, south real, west real, east real, north2 real, south2 real, west2 real, east2 real) OWNER TO dba;

CREATE FUNCTION udf_get_related_info(_registry_object_key character varying) RETURNS SETOF tbl_related_info
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_related_info
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_related_info(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_related_info_type_count(the_month character varying) RETURNS SETOF stat_type_count
    LANGUAGE sql
    AS $$select info_type, COUNT(info_type) as type_count
from dba.tbl_related_info

GROUP BY info_type
ORDER BY info_type$$;
ALTER FUNCTION dba.udf_get_related_info_type_count(the_month character varying) OWNER TO dba;

CREATE FUNCTION udf_get_related_objects(_registry_object_key character varying) RETURNS SETOF tbl_related_objects
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_related_objects
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_related_objects(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_relation_descriptions(_relation_id bigint) RETURNS SETOF tbl_relation_descriptions
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_relation_descriptions
WHERE relation_id = $1
;
$_$;
ALTER FUNCTION dba.udf_get_relation_descriptions(_relation_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_resumption_token(_resumption_token_id character varying, _complete_list_id integer) RETURNS SETOF tbl_oai_rt_resumption_tokens
    LANGUAGE sql
    AS $_$ 


-- Get the token if it exists and isn't expired.
SELECT * FROM dba.tbl_oai_rt_resumption_tokens
WHERE (resumption_token_id = $1 OR $1 IS NULL)
  AND ( (complete_list_id = $2 AND status = 0) -- OAI_RT_LATEST from orca_oai_functions.php
        OR $2 IS NULL
      )
  AND expiration_date > now();
;
$_$;
ALTER FUNCTION dba.udf_get_resumption_token(_resumption_token_id character varying, _complete_list_id integer) OWNER TO dba;

CREATE FUNCTION udf_get_rights(_registry_object_key character varying) RETURNS SETOF tbl_rights
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_rights
WHERE registry_object_key = $1
ORDER BY rights_id ASC
;

$_$;
ALTER FUNCTION dba.udf_get_rights(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_spatial_coverage(_coverage_id bigint) RETURNS SETOF tbl_spatial_locations
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_spatial_locations
WHERE coverage_id = $1
;
$_$;
ALTER FUNCTION dba.udf_get_spatial_coverage(_coverage_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_spatial_locations(_location_id bigint) RETURNS SETOF tbl_spatial_locations
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_spatial_locations
WHERE location_id = $1
;
$_$;
ALTER FUNCTION dba.udf_get_spatial_locations(_location_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_statuses() RETURNS SETOF character
    LANGUAGE sql
    AS $$ 

SELECT status FROM dba.tbl_statuses
;

$$;
ALTER FUNCTION dba.udf_get_statuses() OWNER TO dba;

CREATE FUNCTION udf_get_stored_stat_count(_date_filter character varying, _column_filter character varying) RETURNS SETOF tbl_registry_objects_statistics
    LANGUAGE sql
    AS $_$SELECT *
  FROM dba.tbl_registry_objects_statistics
 WHERE (count_date = CAST($1 AS date))
   $_$;
ALTER FUNCTION dba.udf_get_stored_stat_count(_date_filter character varying, _column_filter character varying) OWNER TO dba;

CREATE FUNCTION udf_get_subject_value(identifier character varying) RETURNS SETOF tbl_terms
    LANGUAGE sql
    AS $_$SELECT * FROM dba.tbl_terms
WHERE identifier =  $1 $_$;
ALTER FUNCTION dba.udf_get_subject_value(identifier character varying) OWNER TO dba;

CREATE FUNCTION udf_get_subjects(_registry_object_key character varying) RETURNS SETOF tbl_subjects
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_subjects
WHERE registry_object_key = $1
;
$_$;
ALTER FUNCTION dba.udf_get_subjects(_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_get_temporal_coverage(_coverage_id bigint) RETURNS SETOF tbl_temporal_coverage
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_temporal_coverage
WHERE coverage_id = $1
ORDER BY temporal_coverage_id ASC
;
$_$;
ALTER FUNCTION dba.udf_get_temporal_coverage(_coverage_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_temporal_coverage_dates(_temporal_coverage_id bigint) RETURNS SETOF tbl_temporal_coverage_dates
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_temporal_coverage_dates
WHERE temporal_coverage_id = $1
ORDER BY timestamp_value ASC
;
$_$;
ALTER FUNCTION dba.udf_get_temporal_coverage_dates(_temporal_coverage_id bigint) OWNER TO dba;

CREATE FUNCTION udf_get_temporal_coverage_text(_temporal_coverage_id bigint) RETURNS SETOF tbl_temporal_coverage_text
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_temporal_coverage_text
WHERE temporal_coverage_id = $1
ORDER BY coverage_text_id ASC
;
$_$;
ALTER FUNCTION dba.udf_get_temporal_coverage_text(_temporal_coverage_id bigint) OWNER TO dba;

CREATE FUNCTION udf_insert_access_policy(_access_policy_id bigint, _registry_object_key character varying, _value character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_access_policies
(
  access_policy_id,
  registry_object_key,
  value
 ) VALUES (
  $1,
  $2,
  $3
 )
$_$;
ALTER FUNCTION dba.udf_insert_access_policy(_access_policy_id bigint, _registry_object_key character varying, _value character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_address_location(_address_id bigint, _location_id bigint) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_address_locations
(
  address_id,
  location_id
 ) VALUES (
  $1,
  $2
 )
$_$;
ALTER FUNCTION dba.udf_insert_address_location(_address_id bigint, _location_id bigint) OWNER TO dba;

CREATE FUNCTION udf_insert_address_part(_address_part_id bigint, _physical_address_id bigint, _value character varying, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_address_parts
(
  address_part_id,
  physical_address_id,
  value,
  type
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
$_$;
ALTER FUNCTION dba.udf_insert_address_part(_address_part_id bigint, _physical_address_id bigint, _value character varying, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_citation_contributor(_citation_contributor_id bigint, _citation_info_id bigint, _seq bigint) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_citation_contributor(_citation_contributor_id bigint, _citation_info_id bigint, _seq bigint) OWNER TO dba;

CREATE FUNCTION udf_insert_citation_date(_metadata_date_id bigint, _citation_info_id bigint, _date character varying, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_citation_date(_metadata_date_id bigint, _citation_info_id bigint, _date character varying, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_citation_information(_citation_info_id bigint, _registry_object_key character varying, _style character varying, _full_citation character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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

$_$;
ALTER FUNCTION dba.udf_insert_citation_information(_citation_info_id bigint, _registry_object_key character varying, _style character varying, _full_citation character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_citation_information(_citation_info_id bigint, _registry_object_key character varying, _metadata_identifier character varying, _metadata_type character varying, _metadata_title character varying, _metadata_edition character varying, _metadata_place_published character varying, _metadata_url character varying, _metadata_context character varying, _metadata_publisher character varying) RETURNS void
    LANGUAGE sql
    AS $_$

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
  metadata_context,
  metadata_publisher
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
  $10
 )
;

$_$;
ALTER FUNCTION dba.udf_insert_citation_information(_citation_info_id bigint, _registry_object_key character varying, _metadata_identifier character varying, _metadata_type character varying, _metadata_title character varying, _metadata_edition character varying, _metadata_place_published character varying, _metadata_url character varying, _metadata_context character varying, _metadata_publisher character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_complete_list(_complete_list_id integer) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_oai_rt_complete_lists (complete_list_id) VALUES ($1);
$_$;
ALTER FUNCTION dba.udf_insert_complete_list(_complete_list_id integer) OWNER TO dba;

CREATE FUNCTION udf_insert_complete_list_record(_complete_list_id integer, _record_number integer, _registry_object_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_oai_rt_complete_list_records (complete_list_id, record_number, registry_object_key) VALUES ($1, $2, $3);
$_$;
ALTER FUNCTION dba.udf_insert_complete_list_record(_complete_list_id integer, _record_number integer, _registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_complex_name(_complex_name_id bigint, _registry_object_key character varying, _type character varying, _date_from timestamp with time zone, _date_to timestamp with time zone, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_complex_names
(
  complex_name_id,
  registry_object_key,
  type,
  date_from,
  date_to,
  lang
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )
$_$;
ALTER FUNCTION dba.udf_insert_complex_name(_complex_name_id bigint, _registry_object_key character varying, _type character varying, _date_from timestamp with time zone, _date_to timestamp with time zone, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_contributor_name_part(_name_part_id bigint, _contributor_name_id bigint, _value character varying, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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

$_$;
ALTER FUNCTION dba.udf_insert_contributor_name_part(_name_part_id bigint, _contributor_name_id bigint, _value character varying, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_coverage(_coverage_id bigint, _registry_object_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_coverage
(
  coverage_id,
  registry_object_key
 ) VALUES (
  $1,
  $2
 )
$_$;
ALTER FUNCTION dba.udf_insert_coverage(_coverage_id bigint, _registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying, _push_to_nla boolean, _isil_value character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_data_sources
(
  created_who,
  modified_who,
  data_source_key,
  title,
  uri,
  provider_type,
  harvest_method,
  oai_set,
  harvest_date, 
  harvest_frequency, 
  allow_reverse_internal_links,
  allow_reverse_external_links,
  contact_name,
  contact_email,
  notes,
  record_owner,
  isil_value,
  push_to_nla
 ) VALUES (
  $1,
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
  $13,
  $14,
  $15,
  $17,
  $16
 )
$_$;
ALTER FUNCTION dba.udf_insert_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying, _push_to_nla boolean, _isil_value character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_data_source(_user character varying, _data_source_key character varying, _title character varying, _record_owner character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _create_primary_relationships boolean, _class_1 character varying, _primary_key_1 character varying, _collection_rel_1 character varying, _service_rel_1 character varying, _activity_rel_1 character varying, _party_rel_1 character varying, _class_2 character varying, _primary_key_2 character varying, _collection_rel_2 character varying, _service_rel_2 character varying, _activity_rel_2 character varying, _party_rel_2 character varying, _push_to_nla boolean, _isil_value character varying, _auto_publish boolean, _qa_flag boolean, _assess_notif_email_addr character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _time_zone_value character varying, _harvest_frequency character varying) RETURNS void
    LANGUAGE sql
    AS $_$

INSERT INTO dba.tbl_data_sources
(created_who,
  modified_who,
  data_source_key,
  title,
  record_owner,
  contact_name,
  contact_email,
  notes,
  allow_reverse_internal_links,
  allow_reverse_external_links, 
  create_primary_relationships,
  class_1,
  primary_key_1,
  collection_rel_1,
  service_rel_1,
  activity_rel_1,
  party_rel_1,
  class_2,
  primary_key_2,
  collection_rel_2,
  service_rel_2,
  activity_rel_2,
  party_rel_2, 
  push_to_nla,
  isil_value,
  auto_publish,
  qa_flag,
  assessement_notification_email_addr,
  uri,
  provider_type,
  harvest_method,
  oai_set,
  harvest_date,
  time_zone_value,
  harvest_frequency
 ) VALUES (
  $1,
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
  $13,
  $14,
  $15,
  $16,
  $17,
  $18,
  $19,
  $20,
  $21,
  $22,
  $23,
  $24,
  $25,
  $26,
  $27,
  $28,
  $29,
  $30,
  $31,
  $32,
  $33,
  $34
 )

$_$;
ALTER FUNCTION dba.udf_insert_data_source(_user character varying, _data_source_key character varying, _title character varying, _record_owner character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _create_primary_relationships boolean, _class_1 character varying, _primary_key_1 character varying, _collection_rel_1 character varying, _service_rel_1 character varying, _activity_rel_1 character varying, _party_rel_1 character varying, _class_2 character varying, _primary_key_2 character varying, _collection_rel_2 character varying, _service_rel_2 character varying, _activity_rel_2 character varying, _party_rel_2 character varying, _push_to_nla boolean, _isil_value character varying, _auto_publish boolean, _qa_flag boolean, _assess_notif_email_addr character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _time_zone_value character varying, _harvest_frequency character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_data_source_event(_event_id bigint, _data_source_key character varying, _created_who character varying, _request_ip character varying, _event_description character varying, _log_type character varying) RETURNS void
    LANGUAGE sql
    AS $_$

INSERT INTO dba.tbl_data_source_logs
(
  event_id,
  data_source_key,
  created_who,
  request_ip,
  event_description,
  log_type
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )

$_$;
ALTER FUNCTION dba.udf_insert_data_source_event(_event_id bigint, _data_source_key character varying, _created_who character varying, _request_ip character varying, _event_description character varying, _log_type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_description(_description_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_descriptions
(
  description_id,
  registry_object_key,
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
;

$_$;
ALTER FUNCTION dba.udf_insert_description(_description_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_draft_registry_object(_draft_owner character varying, _draft_key character varying, _class character varying, _registry_object_group character varying, _registry_object_type character varying, _registry_object_title character varying, _registry_object_data_source character varying, _date_created timestamp with time zone, _date_modified timestamp with time zone, _rifcs text) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_draft_registry_objects WHERE draft_owner = $1 AND draft_key = $2;

INSERT INTO dba.tbl_draft_registry_objects
(
  draft_owner,
  draft_key,
  "class",
  registry_object_group,
  registry_object_type,
  registry_object_title,
  registry_object_data_source,
  date_created,
  date_modified,
  rifcs
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
  $10
 )
$_$;
ALTER FUNCTION dba.udf_insert_draft_registry_object(_draft_owner character varying, _draft_key character varying, _class character varying, _registry_object_group character varying, _registry_object_type character varying, _registry_object_title character varying, _registry_object_data_source character varying, _date_created timestamp with time zone, _date_modified timestamp with time zone, _rifcs text) OWNER TO dba;

CREATE FUNCTION udf_insert_draft_registry_object(_draft_owner character varying, _draft_key character varying, _class character varying, _registry_object_group character varying, _registry_object_type character varying, _registry_object_title character varying, _registry_object_data_source character varying, _date_created timestamp with time zone, _date_modified timestamp with time zone, _rifcs text, _quality_test_result text, _error_count integer, _warning_count integer, _status character) RETURNS void
    LANGUAGE sql
    AS $_$

DELETE FROM dba.tbl_draft_registry_objects WHERE registry_object_data_source = $7 AND draft_key = $2;

INSERT INTO dba.tbl_draft_registry_objects
(
  draft_owner,
  draft_key,
  "class",
  registry_object_group,
  registry_object_type,
  registry_object_title,
  registry_object_data_source,
  date_created,
  date_modified,
  rifcs,
  quality_test_result,
  error_count,
  warning_count,
  status
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
  $13,
  $14
 )

$_$;
ALTER FUNCTION dba.udf_insert_draft_registry_object(_draft_owner character varying, _draft_key character varying, _class character varying, _registry_object_group character varying, _registry_object_type character varying, _registry_object_title character varying, _registry_object_data_source character varying, _date_created timestamp with time zone, _date_modified timestamp with time zone, _rifcs text, _quality_test_result text, _error_count integer, _warning_count integer, _status character) OWNER TO dba;

CREATE FUNCTION udf_insert_electronic_address(_electronic_address_id bigint, _address_id bigint, _value character varying, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_electronic_addresses
(
  electronic_address_id,  
  address_id,
  value,
  type
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
$_$;
ALTER FUNCTION dba.udf_insert_electronic_address(_electronic_address_id bigint, _address_id bigint, _value character varying, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_electronic_address_arg(_electronic_address_arg_id bigint, _electronic_address_id bigint, _name character varying, _required boolean, _type character varying, _use character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_electronic_address_args
(
  electronic_address_arg_id,
  electronic_address_id,
  name,
  required,
  type,
  use
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )
$_$;
ALTER FUNCTION dba.udf_insert_electronic_address_arg(_electronic_address_arg_id bigint, _electronic_address_id bigint, _name character varying, _required boolean, _type character varying, _use character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_existence_dates(_date_id bigint, _registry_object_key character varying, _start_date character varying, _start_date_format character varying, _end_date character varying, _end_date_format character varying) RETURNS void
    LANGUAGE sql
    AS $_$

INSERT INTO dba.tbl_existence_dates
(
  existence_date_id,
  registry_object_key,
  start_date,
  start_date_format,
  end_date,
  end_date_format
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )
;

$_$;
ALTER FUNCTION dba.udf_insert_existence_dates(_date_id bigint, _registry_object_key character varying, _start_date character varying, _start_date_format character varying, _end_date character varying, _end_date_format character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_harvest_request(_harvest_request_id character varying, _data_source_key character varying, _harvester_base_uri character varying, _harvester_ip character varying, _response_target_uri character varying, _source_uri character varying, _method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _mode character varying, _created_who character varying, _status character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_harvest_requests
(
  harvest_request_id,
  data_source_key,
  harvester_base_uri,
  harvester_ip,
  response_target_uri,
  source_uri,
  method,
  oai_set,
  harvest_date,
  harvest_frequency,
  mode,
  created_who,
  modified_who,
  status
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
  $12,
  $13
 )
$_$;
ALTER FUNCTION dba.udf_insert_harvest_request(_harvest_request_id character varying, _data_source_key character varying, _harvester_base_uri character varying, _harvester_ip character varying, _response_target_uri character varying, _source_uri character varying, _method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _mode character varying, _created_who character varying, _status character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_identifier(_identifier_id bigint, _registry_object_key character varying, _value character varying, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_identifiers
(
  identifier_id,
  registry_object_key,
  value,
  type
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
;


$_$;
ALTER FUNCTION dba.udf_insert_identifier(_identifier_id bigint, _registry_object_key character varying, _value character varying, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_location(_location_id bigint, _registry_object_key character varying, _date_from timestamp with time zone, _date_to timestamp with time zone, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_locations
(
  location_id,
  registry_object_key,
  date_from,
  date_to,
  type
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5
 )
$_$;
ALTER FUNCTION dba.udf_insert_location(_location_id bigint, _registry_object_key character varying, _date_from timestamp with time zone, _date_to timestamp with time zone, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_name_part(_name_part_id bigint, _complex_name_id bigint, _value character varying, _type character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_name_parts
(
  name_part_id,
  complex_name_id,
  value,
  type
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
;

$_$;
ALTER FUNCTION dba.udf_insert_name_part(_name_part_id bigint, _complex_name_id bigint, _value character varying, _type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_physical_address(_physical_address_id bigint, _address_id bigint, _type character varying, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_physical_addresses
(
  physical_address_id,
  address_id,
  type,
  lang
 ) VALUES (
  $1,
  $2,
  $3,
  $4
 )
$_$;
ALTER FUNCTION dba.udf_insert_physical_address(_physical_address_id bigint, _address_id bigint, _type character varying, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_raw_record(_registry_object_key character varying, _data_source_key character varying, _created_when timestamp with time zone, _created_who character varying, _rifcs_fragment text) RETURNS void
    LANGUAGE plpgsql
    AS $_$
DECLARE
theTenthRecord timestamp;
BEGIN

INSERT INTO dba.tbl_raw_records
(
 registry_object_key,
 data_source,
 created_when,
 created_who,
 rifcs_fragment
 ) VALUES (
 $1,
 $2,
 $3,
 $4,
 $5
 );
theTenthRecord := NULL;
SELECT INTO theTenthRecord created_when FROM dba.tbl_raw_records
       WHERE registry_object_key = $1
       AND data_source = $2
       ORDER BY created_when DESC
       LIMIT 1 OFFSET 9;
IF theTenthRecord IS NOT NULL THEN
       DELETE FROM dba.tbl_raw_records
       WHERE registry_object_key = $1
       AND data_source = $2
       AND created_when < theTenthRecord;
END IF;
END;
$_$;
ALTER FUNCTION dba.udf_insert_raw_record(_registry_object_key character varying, _data_source_key character varying, _created_when timestamp with time zone, _created_who character varying, _rifcs_fragment text) OWNER TO dba;


CREATE FUNCTION udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
  created_who
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
  $10
 )
$_$;
ALTER FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
  status
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
  $11
 )
$_$;
ALTER FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character) OWNER TO dba;

CREATE FUNCTION udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character, _record_owner character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character, _record_owner character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character varying, _record_owner character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character varying, _record_owner character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character varying, _record_owner character varying, _schema_version character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_registry_object(_registry_object_key character varying, _registry_object_class character varying, _type character varying, _originating_source character varying, _originating_source_type character varying, _data_source_key character varying, _object_group character varying, _date_accessioned timestamp with time zone, _date_modified timestamp with time zone, _created_who character varying, _status character varying, _record_owner character varying, _schema_version character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_related_info(_related_info_id bigint, _registry_object_key character varying, _value character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_related_info
(
  related_info_id,
  registry_object_key,
  value
 ) VALUES (
  $1,
  $2,
  $3
 )
$_$;
ALTER FUNCTION dba.udf_insert_related_info(_related_info_id bigint, _registry_object_key character varying, _value character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_related_info(_related_info_id bigint, _registry_object_key character varying, _info_type character varying, _identifier character varying, _identifier_type character varying, _title character varying, _notes character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_related_info(_related_info_id bigint, _registry_object_key character varying, _info_type character varying, _identifier character varying, _identifier_type character varying, _title character varying, _notes character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_related_info_old(_related_info_id bigint, _registry_object_key character varying, _value character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_related_info
(
  related_info_id,
  registry_object_key,
  value
 ) VALUES (
  $1,
  $2,
  $3
 )
$_$;
ALTER FUNCTION dba.udf_insert_related_info_old(_related_info_id bigint, _registry_object_key character varying, _value character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_related_object(_relation_id bigint, _registry_object_key character varying, _related_registry_object_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_related_objects
(
  relation_id,
  registry_object_key,
  related_registry_object_key
 ) VALUES (
  $1,
  $2,
  $3
 )
$_$;
ALTER FUNCTION dba.udf_insert_related_object(_relation_id bigint, _registry_object_key character varying, _related_registry_object_key character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_relation_description(_relation_arg_id bigint, _relation_id bigint, _description character varying, _type character varying, _lang character varying, _url character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_relation_descriptions
(
  relation_description_id,
  relation_id,
  description,
  type,
  lang,
  url
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )
$_$;
ALTER FUNCTION dba.udf_insert_relation_description(_relation_arg_id bigint, _relation_id bigint, _description character varying, _type character varying, _lang character varying, _url character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_resumption_token(_resumption_token_id character varying, _complete_list_id integer, _status integer, _first_record_number integer, _complete_list_size integer, _expiration_date timestamp with time zone, _metadata_prefix character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_oai_rt_resumption_tokens (
 resumption_token_id,
 complete_list_id,
 status,
 first_record_number,
 complete_list_size,
 expiration_date,
 metadata_prefix
) VALUES (
 $1, 
 $2, 
 $3,
 $4,
 $5,
 $6,
 $7
);

$_$;
ALTER FUNCTION dba.udf_insert_resumption_token(_resumption_token_id character varying, _complete_list_id integer, _status integer, _first_record_number integer, _complete_list_size integer, _expiration_date timestamp with time zone, _metadata_prefix character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_rights(_rights_id bigint, _registry_object_key character varying, _rights_statement character varying, _rights_statement_uri character varying, _licence character varying, _licence_uri character varying, _access_rights character varying, _access_rights_uri character varying, _licence_type character varying, _access_rights_type character varying) RETURNS void
    LANGUAGE sql
    AS $_$

INSERT INTO dba.tbl_rights
(
  rights_id,
  registry_object_key,
  rights_statement,
  rights_statement_uri,
  licence,
  licence_uri, 
  access_rights, 
  access_rights_uri,
  licence_type,
  access_rights_type
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
  $10
 )
;

$_$;
ALTER FUNCTION dba.udf_insert_rights(_rights_id bigint, _registry_object_key character varying, _rights_statement character varying, _rights_statement_uri character varying, _licence character varying, _licence_uri character varying, _access_rights character varying, _access_rights_uri character varying, _licence_type character varying, _access_rights_type character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_spatial_coverage(_spatial_location_id bigint, _coverage_id bigint, _value character varying, _type character varying, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_spatial_coverage(_spatial_location_id bigint, _coverage_id bigint, _value character varying, _type character varying, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_spatial_extent(_spatial_extents_id bigint, _spatial_location_id bigint, registry_object_key character varying, _north real, _south real, _west real, _east real) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_spatial_extents
(
  spatial_extents_id,
  spatial_location_id,
  registry_object_key,
  bound_box
 ) VALUES (
  $1,
  $2,
  $3,
  box(point($4,$6),point($5,$7))
 )
$_$;
ALTER FUNCTION dba.udf_insert_spatial_extent(_spatial_extents_id bigint, _spatial_location_id bigint, registry_object_key character varying, _north real, _south real, _west real, _east real) OWNER TO dba;

CREATE FUNCTION udf_insert_spatial_location(_spatial_location_id bigint, _location_id bigint, _value character varying, _type character varying, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_spatial_locations
(
  spatial_location_id,
  location_id,
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
$_$;
ALTER FUNCTION dba.udf_insert_spatial_location(_spatial_location_id bigint, _location_id bigint, _value character varying, _type character varying, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_stored_stat_count(_date_filter character varying, total_count bigint, collection_count bigint, party_count bigint, activity_count bigint, service_count bigint, trusted_sw_count bigint) RETURNS void
    LANGUAGE sql
    AS $_$INSERT INTO dba.tbl_registry_objects_statistics 
(count_date, registry_object_count,collection_object_count, party_object_count, activity_object_count, service_object_count,trusted_sw_agreements_count )
VALUES(CAST($1 AS date),$2,$3,$4,$5,$6,$7)
   $_$;
ALTER FUNCTION dba.udf_insert_stored_stat_count(_date_filter character varying, total_count bigint, collection_count bigint, party_count bigint, activity_count bigint, service_count bigint, trusted_sw_count bigint) OWNER TO dba;

CREATE FUNCTION udf_insert_subject(_subject_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_subjects
(
  subject_id,
  registry_object_key,
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
;

$_$;
ALTER FUNCTION dba.udf_insert_subject(_subject_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_subject(_subject_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _termidentifier character varying, _lang character varying) RETURNS void
    LANGUAGE sql
    AS $_$

INSERT INTO dba.tbl_subjects
(
  subject_id,
  registry_object_key,
  value,
  type,
  "termIdentifier",
  lang
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
 )
;


$_$;
ALTER FUNCTION dba.udf_insert_subject(_subject_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _termidentifier character varying, _lang character varying) OWNER TO dba;

CREATE FUNCTION udf_insert_temporal_coverage(_temporal_coverage_id bigint, _coverage_id bigint) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_temporal_coverage
(
  temporal_coverage_id,
  coverage_id
 ) VALUES (
  $1,
  $2
 )
$_$;
ALTER FUNCTION dba.udf_insert_temporal_coverage(_temporal_coverage_id bigint, _coverage_id bigint) OWNER TO dba;

CREATE FUNCTION udf_insert_temporal_coverage_date(_coverage_date_id bigint, _temporal_coverage_id bigint, _type character varying, _date_format character varying, _value character varying, _timestamp_value timestamp with time zone) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_temporal_coverage_date(_coverage_date_id bigint, _temporal_coverage_id bigint, _type character varying, _date_format character varying, _value character varying, _timestamp_value timestamp with time zone) OWNER TO dba;

CREATE FUNCTION udf_insert_temporal_coverage_text(_coverage_text_id bigint, _temporal_coverage_id bigint, _value character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

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
$_$;
ALTER FUNCTION dba.udf_insert_temporal_coverage_text(_coverage_text_id bigint, _temporal_coverage_id bigint, _value character varying) OWNER TO dba;

CREATE FUNCTION udf_registry_object_class_count(_class character varying) RETURNS SETOF bigint
    LANGUAGE sql
    AS $_$ 


 SELECT COUNT('x')
 FROM dba.tbl_registry_objects ro
WHERE $1 = ro.registry_object_class
;
$_$;
ALTER FUNCTION dba.udf_registry_object_class_count(_class character varying) OWNER TO dba;

CREATE FUNCTION udf_search_by_name(_search_text character varying, _object_class character varying, _data_source character varying, _limit integer) RETURNS SETOF udt_name_search_result
    LANGUAGE sql
    AS $_$

SELECT registry_object_key, display_title, status,"type" FROM dba.tbl_registry_objects
WHERE display_title like '%'||$1||'%'
AND ($2 = '' OR registry_object_class = $2)
AND ($3 = '' OR data_source_key = $3)
UNION
SELECT draft_key AS registry_object_key, registry_object_title AS display_title, status,registry_object_type AS "type" FROM dba.tbl_draft_registry_objects
WHERE lower(registry_object_title) like lower('%'||$1||'%')
AND ($2 = '' OR lower("class") = lower($2))
AND ($3 = '' OR registry_object_data_source = $3)
LIMIT $4
$_$;
ALTER FUNCTION dba.udf_search_by_name(_search_text character varying, _object_class character varying, _data_source character varying, _limit integer) OWNER TO dba;

CREATE FUNCTION udf_search_children_in_vocabs(_parent_term_identifier character varying, _search_text character varying) RETURNS SETOF udt_term_search_result
    LANGUAGE sql
    AS $_$

SELECT t.name , t.identifier , t.vocabPath , t.vocabulary_identifier FROM dba.tbl_terms t
 
WHERE 
($2 ~ E'^\\d{2}$' AND t.vocabulary_identifier = $1 AND t.identifier like $2 || '%' || '00') 
OR
($2 ~ E'^\\d{4}$' AND t.vocabulary_identifier = $1 AND t.identifier like $2 || '%') 
OR
($2 <> '' AND NOT ($2 ~ E'^\\d{2}$' OR $2 ~ E'^\\d{4}$') AND char_length($2) > 3 AND t.vocabulary_identifier = $1 AND upper(t.name) like upper('%' || $2 || '%')) 
OR 
($2 = '' AND t.parent_term_identifier = '' AND t.vocabulary_identifier = $1)

ORDER BY t.identifier ASC LIMIT 40;

$_$;
ALTER FUNCTION dba.udf_search_children_in_vocabs(_parent_term_identifier character varying, _search_text character varying) OWNER TO dba;

CREATE FUNCTION udf_search_names(_search_text character varying, _object_class character varying, _data_source character varying, _limit integer) RETURNS SETOF character varying
    LANGUAGE sql
    AS $_$

SELECT DISTINCT(n.registry_object_key) FROM dba.tbl_registry_objects ro
INNER JOIN dba.vw_names n ON n.registry_object_key =
ro.registry_object_key
WHERE lower(n.value) like lower($1)
AND ($2 = '' OR ro.registry_object_class = $2)
AND ($3 = '' OR ro.data_source_key = $3)
LIMIT $4
$_$;
ALTER FUNCTION dba.udf_search_names(_search_text character varying, _object_class character varying, _data_source character varying, _limit integer) OWNER TO dba;

CREATE FUNCTION udf_search_registry(_search_string character varying, _classes character varying, _data_source_key character varying, _object_group character varying, _created_before_equals timestamp with time zone, _created_after_equals timestamp with time zone, _status character, _record_owner character varying) RETURNS SETOF udt_search_result
    LANGUAGE sql
    AS $_$ 


SELECT
 registry_object_key,
 originating_source,
 data_source_key,
 data_source_title,
 object_group,
 date_accessioned,
 date_modified,
 created_when,
 registry_object_class,
 type,
 status,
 record_owner
FROM
(
 SELECT DISTINCT ON (registry_object_key)
  registry_object_key,
  originating_source,
  data_source_key,
  data_source_title,
  object_group,
  date_accessioned,
  date_modified,
  created_when,
  registry_object_class,
  status,
  record_owner,
  type
 FROM dba.vw_registry_search
 WHERE ( $2 ~* registry_object_class OR $2 = '' )
   AND ( data_source_key = $3 OR $3 IS NULL )
   AND ( object_group = $4 OR $4 IS NULL )
   AND ( registry_object_key ~* ('^'||$1)
--         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.identifiers_search_index @@ plainto_tsquery($1))
--         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.names_search_index @@ plainto_tsquery($1))
--         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.subjects_search_index @@ plainto_tsquery($1))
--         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.descriptions_search_index @@ plainto_tsquery($1))
         OR $1 = ''
   )
   AND ( status = $7 OR $7 IS NULL )
   AND ( record_owner = $8 OR $8 IS NULL )
 ORDER BY registry_object_key ASC
) 
AS distinct_matches
WHERE ( created_when <= $5 OR $5 IS NULL )
  AND ( created_when >= $6 OR $6 IS NULL )
ORDER BY created_when DESC
;
$_$;
ALTER FUNCTION dba.udf_search_registry(_search_string character varying, _classes character varying, _data_source_key character varying, _object_group character varying, _created_before_equals timestamp with time zone, _created_after_equals timestamp with time zone, _status character, _record_owner character varying) OWNER TO dba;

CREATE FUNCTION udf_search_registry_objects_inbound(_north real, _south real, _west real, _east real, _search_string character varying, _classes character varying) RETURNS SETOF udt_search_result
    LANGUAGE sql
    AS $_$ 
SELECT
 registry_object_key,
 originating_source,
 data_source_key,
 data_source_title,
 object_group,
 date_accessioned,
 date_modified,
 created_when,
 registry_object_class,
 type,
 status,
 record_owner
FROM
(
 SELECT DISTINCT ON (registry_object_key)
  registry_object_key,
  originating_source,
  data_source_key,
  data_source_title,
  object_group,
  date_accessioned,
  date_modified,
  created_when,
  registry_object_class,
  status,
  record_owner,
  type
 FROM dba.vw_registry_search
 WHERE ( $6 ~* registry_object_class OR $6 = '' )
   AND status = 'APPROVED'
   AND (registry_object_key IN (SELECT registry_object_key FROM dba.tbl_spatial_extents WHERE bound_box && box ((point($1,$3)),(point($2,$4)))))
)
AS distinct_matches
ORDER BY created_when DESC;
$_$;
ALTER FUNCTION dba.udf_search_registry_objects_inbound(_north real, _south real, _west real, _east real, _search_string character varying, _classes character varying) OWNER TO dba;

CREATE FUNCTION udf_search_registry_objects_inbound_two(_north real, _south real, _west real, _east real, _north2 real, _south2 real, _west2 real, _east2 real, _search_string character varying, _classes character varying) RETURNS SETOF udt_search_result
    LANGUAGE sql
    AS $_$ 
SELECT
 registry_object_key,
 originating_source,
 data_source_key,
 data_source_title,
 object_group,
 date_accessioned,
 date_modified,
 created_when,
 registry_object_class,
 type,
 status,
 record_owner
FROM
(
 SELECT DISTINCT ON (registry_object_key)
  registry_object_key,
  originating_source,
  data_source_key,
  data_source_title,
  object_group,
  date_accessioned,
  date_modified,
  created_when,
  registry_object_class,
  status,
  record_owner,
  type
 FROM dba.vw_registry_search
 WHERE ( $10 ~* registry_object_class OR $10 = '' )
   AND status = 'APPROVED'
   AND (
	registry_object_key IN (SELECT registry_object_key FROM dba.tbl_spatial_extents WHERE bound_box && box ((point($1,$3)),(point($2,$4))))
	OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_spatial_extents WHERE bound_box && box ((point($5,$7)),(point($6,$8))))
	)
)
AS distinct_matches
ORDER BY created_when DESC;
$_$;
ALTER FUNCTION dba.udf_search_registry_objects_inbound_two(_north real, _south real, _west real, _east real, _north2 real, _south2 real, _west2 real, _east2 real, _search_string character varying, _classes character varying) OWNER TO dba;

CREATE FUNCTION udf_search_terms_in_vocabs(_vocabulary_identifier character varying, _search_text character varying) RETURNS SETOF udt_term_search_result
    LANGUAGE sql
    AS $_$

SELECT t.name , t.identifier , t.vocabPath , t.vocabulary_identifier FROM dba.tbl_terms t 
WHERE t.vocabulary_identifier = $1
AND upper(t.name) like upper('%' || $2 || '%');

$_$;
ALTER FUNCTION dba.udf_search_terms_in_vocabs(_vocabulary_identifier character varying, _search_text character varying) OWNER TO dba;

CREATE FUNCTION udf_search_terms_in_vocabs_by_identifier(_vocabulary_identifier character varying, _search_text character varying) RETURNS SETOF udt_term_search_result
    LANGUAGE sql
    AS $_$

SELECT t.name , t.identifier , t.vocabPath , t.vocabulary_identifier FROM dba.tbl_terms t 
WHERE ($1 IS NULL OR t.vocabulary_identifier = $1)
AND upper(t.identifier) like upper($2) LIMIT 1;

$_$;
ALTER FUNCTION dba.udf_search_terms_in_vocabs_by_identifier(_vocabulary_identifier character varying, _search_text character varying) OWNER TO dba;

CREATE FUNCTION udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying, _push_to_nla boolean, _isil_value character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

UPDATE dba.tbl_data_sources SET
  modified_who = $1,
  modified_when = now(),
  title = $3,
  uri = $4,
  provider_type = $5,
  harvest_method = $6,
  oai_set = $7,
  harvest_date = $8,
  harvest_frequency = $9,
  allow_reverse_internal_links = $10,
  allow_reverse_external_links = $11,
  contact_name = $12,
  contact_email = $13,
  notes = $14,
  record_owner = $15,
  isil_value = $17,
  push_to_nla = $16
WHERE data_source_key = $2

$_$;
ALTER FUNCTION dba.udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying, _push_to_nla boolean, _isil_value character varying) OWNER TO dba;

CREATE FUNCTION udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _record_owner character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _create_primary_relationships boolean, _class_1 character varying, _primary_key_1 character varying, _collection_rel_1 character varying, _service_rel_1 character varying, _activity_rel_1 character varying, _party_rel_1 character varying, _class_2 character varying, _primary_key_2 character varying, _collection_rel_2 character varying, _service_rel_2 character varying, _activity_rel_2 character varying, _party_rel_2 character varying, _push_to_nla boolean, _isil_value character varying, _auto_publish boolean, _qa_flag boolean, _assess_notif_email_addr character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _time_zone_value character varying, _harvest_frequency character varying) RETURNS void
    LANGUAGE sql
    AS $_$

UPDATE dba.tbl_data_sources SET
  modified_who = $1,
  modified_when = now(),
  title = $3,
  record_owner = $4,
  contact_name = $5,
  contact_email = $6,
  notes = $7,
  allow_reverse_internal_links = $8,
  allow_reverse_external_links = $9, 
  create_primary_relationships =$10,
  class_1 = $11,
  primary_key_1 = $12,
  collection_rel_1 = $13,
  service_rel_1 = $14,
  activity_rel_1 = $15,
  party_rel_1 = $16,
  class_2 = $17,
  primary_key_2 = $18,
  collection_rel_2 = $19,
  service_rel_2 = $20,
  activity_rel_2 = $21,
  party_rel_2 = $22,
  push_to_nla = $23,
  isil_value = $24,
  auto_publish = $25,
  qa_flag = $26,
  assessement_notification_email_addr = $27,
  uri = $28,
  provider_type = $29,
  harvest_method = $30,
  oai_set = $31,
  harvest_date = $32,
  time_zone_value = $33, 
  harvest_frequency = $34
WHERE data_source_key = $2

$_$;
ALTER FUNCTION dba.udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _record_owner character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _create_primary_relationships boolean, _class_1 character varying, _primary_key_1 character varying, _collection_rel_1 character varying, _service_rel_1 character varying, _activity_rel_1 character varying, _party_rel_1 character varying, _class_2 character varying, _primary_key_2 character varying, _collection_rel_2 character varying, _service_rel_2 character varying, _activity_rel_2 character varying, _party_rel_2 character varying, _push_to_nla boolean, _isil_value character varying, _auto_publish boolean, _qa_flag boolean, _assess_notif_email_addr character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _time_zone_value character varying, _harvest_frequency character varying) OWNER TO dba;

CREATE FUNCTION udf_update_data_source_last_run(_user character varying, _data_source_key character varying, _last_run_result character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

UPDATE dba.tbl_data_sources SET
  last_run = now(),
  last_run_who = $1,
  last_run_result = $3
WHERE data_source_key = $2
;
$_$;
ALTER FUNCTION dba.udf_update_data_source_last_run(_user character varying, _data_source_key character varying, _last_run_result character varying) OWNER TO dba;

CREATE FUNCTION udf_update_draft_quality_test_result(_draft_key character varying, _data_source_key character varying, _quality_test_result character varying, _error_count integer, _warning_count integer) RETURNS void
    LANGUAGE sql
    AS $_$ 


UPDATE dba.tbl_draft_registry_objects SET
  quality_test_result = $3,
  error_count = $4,
  warning_count = $5
WHERE 
draft_key = $1 AND registry_object_data_source = $2


$_$;
ALTER FUNCTION dba.udf_update_draft_quality_test_result(_draft_key character varying, _data_source_key character varying, _quality_test_result character varying, _error_count integer, _warning_count integer) OWNER TO dba;

CREATE FUNCTION udf_update_draft_registry_object_status(_draft_key character varying, _data_source_key character varying, _status character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 


UPDATE dba.tbl_draft_registry_objects SET
  status = $3,
  flag = 'f',
  date_modified = now()
WHERE 
draft_key = $1 AND registry_object_data_source = $2


$_$;
ALTER FUNCTION dba.udf_update_draft_registry_object_status(_draft_key character varying, _data_source_key character varying, _status character varying) OWNER TO dba;

CREATE FUNCTION udf_update_draft_registry_quality_test_result(_draft_key character varying, _data_source_key character varying, _quality_test_result character varying, _error_count integer, _warning_count integer) RETURNS void
    LANGUAGE sql
    AS $_$ 


UPDATE dba.tbl_draft_registry_objects SET
  quality_test_result = $3,
  error_count = $4,
  warning_count = $5
WHERE 
draft_key = $1 AND registry_object_data_source = $2


$_$;
ALTER FUNCTION dba.udf_update_draft_registry_quality_test_result(_draft_key character varying, _data_source_key character varying, _quality_test_result character varying, _error_count integer, _warning_count integer) OWNER TO dba;

CREATE FUNCTION udf_update_harvest_request(_harvest_request_id character varying, _modified_who character varying, _status character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

UPDATE dba.tbl_harvest_requests SET
  modified_who = $2,
  modified_when = now(),
  status = $3
WHERE harvest_request_id = $1
;
$_$;
ALTER FUNCTION dba.udf_update_harvest_request(_harvest_request_id character varying, _modified_who character varying, _status character varying) OWNER TO dba;

CREATE FUNCTION udf_update_registry_object_flag(_registry_object_key character varying, _flag boolean) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 
BEGIN

UPDATE dba.tbl_registry_objects SET
  flag = $2
WHERE registry_object_key = $1;

END
$_$;
ALTER FUNCTION dba.udf_update_registry_object_flag(_registry_object_key character varying, _flag boolean) OWNER TO dba;

CREATE FUNCTION udf_update_registry_object_status(_display_title character varying, _list_title character varying) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 
BEGIN
IF $2 = '' THEN
UPDATE dba.tbl_registry_objects SET
  status = $2,
  status_modified_who = $3,
  status_modified_when = now()
WHERE registry_object_key = $1;
ELSE 

END IF;
END
$_$;
ALTER FUNCTION dba.udf_update_registry_object_status(_display_title character varying, _list_title character varying) OWNER TO dba;

CREATE FUNCTION udf_update_registry_object_status(_registry_object_key character varying, _status character, _user character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

UPDATE dba.tbl_registry_objects SET
  status = $2,
  status_modified_who = $3,
  status_modified_when = now()
WHERE registry_object_key = $1

$_$;
ALTER FUNCTION dba.udf_update_registry_object_status(_registry_object_key character varying, _status character, _user character varying) OWNER TO dba;

CREATE FUNCTION udf_update_registry_object_titles(_registry_object_key character varying, _display_title character varying, _list_title character varying) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 


BEGIN


IF $3 = '' THEN


UPDATE dba.tbl_registry_objects SET
  display_title = $2
WHERE registry_object_key = $1;


ELSIF $2 = '' THEN


UPDATE dba.tbl_registry_objects SET
  list_title = $3
WHERE registry_object_key = $1;


ELSE 
UPDATE dba.tbl_registry_objects SET
  display_title = $2,
  list_title = $3
WHERE registry_object_key = $1;
END IF;


RETURN;


END;


$_$;
ALTER FUNCTION dba.udf_update_registry_object_titles(_registry_object_key character varying, _display_title character varying, _list_title character varying) OWNER TO dba;

CREATE FUNCTION udf_update_registry_quality_test_result(_registry_object_key character varying, _quality_test_result character varying, _error_count bigint, _warning_count bigint) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 
BEGIN
UPDATE dba.tbl_registry_objects SET
  quality_test_result = $2,
  error_count = $3,
  warning_count = $4
WHERE registry_object_key = $1;
END;
$_$;
ALTER FUNCTION dba.udf_update_registry_quality_test_result(_registry_object_key character varying, _quality_test_result character varying, _error_count bigint, _warning_count bigint) OWNER TO dba;

CREATE FUNCTION udf_update_resumption_tokens(_complete_list_id integer) RETURNS void
    LANGUAGE sql
    AS $_$ 


-- Delete any existing OAI_RT_PREVIOUS resumptionToken for this complete list.
DELETE FROM dba.tbl_oai_rt_resumption_tokens
WHERE complete_list_id = $1
  AND status = 1; -- OAI_RT_PREVIOUS from orca_oai_functions.php

-- Set the status of the remaining (OAI_RT_LATEST) resumptionToken to OAI_RT_PREVIOUS.
UPDATE dba.tbl_oai_rt_resumption_tokens
   SET status = 1 -- OAI_RT_PREVIOUS from orca_oai_functions.php
WHERE complete_list_id = $1;


$_$;
ALTER FUNCTION dba.udf_update_resumption_tokens(_complete_list_id integer) OWNER TO dba;

CREATE FUNCTION udf_update_spatial_extents() RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    spatialRecord RECORD;
    lat float4;
    lng float4;
    n float4;
    s float4;
    w float4;
    e float4;
    cLat float4;
    cLng float4;
    nlp int;
    slp int;
    wlp int;
    elp int;
    nlimit varchar(40);    
    slimit varchar(40);   
    wlimit varchar(40);   
    elimit varchar(40);
    strLength int;
    coordsStr varchar(512);
    str varchar(40)[];
    coords varchar(40)[];
    i int;
    array_len int;
BEGIN
    FOR spatialRecord IN select ro.registry_object_key, sl.spatial_location_id, sl.type, sl.value from dba.tbl_registry_objects ro,
	dba.tbl_locations l, dba.tbl_spatial_locations sl where ro.registry_object_key = l.registry_object_key AND l.location_id = sl.location_id order by 2 asc 
	LOOP
	IF 'iso19139dcmiBox' = spatialRecord.type THEN
	strLength := length(spatialRecord.value);
	--RAISE NOTICE 'spatialRecord%1', spatialRecord.value;
	nlp := position('northlimit=' in lower(spatialRecord.value));
	nlimit = split_part(substring(spatialRecord.value ,(nlp + 11), (strLength-nlp)),';',1);
	slp := position('southlimit=' in lower(spatialRecord.value));
	slimit = split_part(substring(spatialRecord.value ,slp + 11, strLength-slp),';',1);
	wlp := position('westlimit=' in lower(spatialRecord.value));
	wlimit = split_part(substring(spatialRecord.value ,wlp + 10, strLength-wlp),';',1);
	elp := position('eastlimit=' in lower(spatialRecord.value));
	elimit := split_part(substring(spatialRecord.value ,elp + 10, strLength-elp),';',1);
		IF(nlimit != '' AND slimit != '' AND wlimit != '' AND elimit != '') THEN
		n := to_number(nlimit, '999D999999');
		s := to_number(slimit, '999D999999');
		w := to_number(wlimit, '999D999999');
		e := to_number(elimit, '999D999999');
	RAISE NOTICE 'n:% s:% w:% e:%', n,s,w,e;
		INSERT INTO tbl_spatial_extents(spatial_extents_id, 
						spatial_location_id,
						registry_object_key,
						bound_box) 
						VALUES (spatialRecord.spatial_location_id, 
						spatialRecord.spatial_location_id, 
						spatialRecord.registry_object_key, 
						box(point(n,w),point(s,e)));
		END IF;
	END IF;
	IF (spatialRecord.type = 'gmlKmlPolyCoords' OR spatialRecord.type = 'kmlPolyCoords') THEN
	
	coordsStr := replace(spatialRecord.value, chr(10),' ');
	--RAISE NOTICE 'spatialRecord % - % ', spatialRecord.value , coordsStr;
	str := string_to_array(coordsStr,' ');  
	n := -90;
	s := 90;
	w := 180;
	e := -180; 
	array_len := array_upper(str, 1);
	FOR i IN 1 .. array_len LOOP
		coords := string_to_array(str[i],',');  
		lng := to_number(coords[1], '999D999999');
		lat := to_number(coords[2], '999D999999');
			IF (lat > n) THEN
			 n := lat;
			END IF;
			IF (lat < s) THEN
			 s := lat;
			END IF;
			IF (lng > e) THEN
			 e := lng;
			END IF;
			IF (lng < w) THEN
			 w := lng;
			END IF;
	END LOOP; 
	RAISE NOTICE 'n:% s:% w:% e:%', n,s,w,e;
	INSERT INTO tbl_spatial_extents(spatial_extents_id, 
					spatial_location_id,
					registry_object_key,
					bound_box) 
					VALUES (spatialRecord.spatial_location_id, 
					spatialRecord.spatial_location_id, 
					spatialRecord.registry_object_key, 
					box(point(n,w),point(s,e)));		
	END IF;
    END LOOP;
    RETURN 1;
END;
$$;
ALTER FUNCTION dba.udf_update_spatial_extents() OWNER TO dba;


-------------
-- ADD CONSTRAINTS (Indexes/Primary/Foreign keys, etc.)
-------------

ALTER TABLE tbl_citation_contributors ALTER COLUMN citation_contributor_id SET DEFAULT nextval('tbl_citation_contributors_citation_contributor_id_seq'::regclass);
ALTER TABLE tbl_citation_dates ALTER COLUMN metadata_date_id SET DEFAULT nextval('tbl_citation_dates_metadata_date_id_seq'::regclass);
ALTER TABLE tbl_citation_information ALTER COLUMN citation_info_id SET DEFAULT nextval('tbl_citation_information_citation_info_id_seq'::regclass);
ALTER TABLE tbl_coverage ALTER COLUMN coverage_id SET DEFAULT nextval('tbl_coverage_coverage_id_seq'::regclass);
ALTER TABLE tbl_temporal_coverage ALTER COLUMN temporal_coverage_id SET DEFAULT nextval('tbl_temporal_coverage_temporal_coverage_id_seq'::regclass);
ALTER TABLE tbl_temporal_coverage_dates ALTER COLUMN coverage_date_id SET DEFAULT nextval('tbl_temporal_coverage_dates_coverage_date_id_seq'::regclass);
ALTER TABLE tbl_temporal_coverage_text ALTER COLUMN coverage_text_id SET DEFAULT nextval('tbl_temporal_coverage_text_coverage_text_id_seq'::regclass);


ALTER TABLE ONLY tbl_registry_objects_statistics
    ADD CONSTRAINT pk PRIMARY KEY (count_date);
ALTER TABLE ONLY tbl_raw_records
    ADD CONSTRAINT pk_raw_record_data PRIMARY KEY (registry_object_key, data_source, created_when);
ALTER TABLE ONLY tbl_rights
    ADD CONSTRAINT pk_rights_id PRIMARY KEY (rights_id);
ALTER TABLE ONLY tbl_access_policies
    ADD CONSTRAINT pk_tbl_access_policies PRIMARY KEY (access_policy_id);
ALTER TABLE ONLY tbl_address_locations
    ADD CONSTRAINT pk_tbl_address_locations PRIMARY KEY (address_id);
ALTER TABLE ONLY tbl_address_parts
    ADD CONSTRAINT pk_tbl_address_parts PRIMARY KEY (address_part_id);
ALTER TABLE ONLY tbl_complex_names
    ADD CONSTRAINT pk_tbl_complex_names PRIMARY KEY (complex_name_id);
ALTER TABLE ONLY tbl_data_source_logs
    ADD CONSTRAINT pk_tbl_data_source_logs PRIMARY KEY (event_id);
ALTER TABLE ONLY tbl_data_sources
    ADD CONSTRAINT pk_tbl_data_sources PRIMARY KEY (data_source_key);
ALTER TABLE ONLY tbl_descriptions
    ADD CONSTRAINT pk_tbl_descriptions PRIMARY KEY (description_id);
ALTER TABLE ONLY tbl_electronic_address_args
    ADD CONSTRAINT pk_tbl_electronic_address_args PRIMARY KEY (electronic_address_arg_id);
ALTER TABLE ONLY tbl_electronic_addresses
    ADD CONSTRAINT pk_tbl_electronic_adresses PRIMARY KEY (electronic_address_id);
ALTER TABLE ONLY tbl_existence_dates
    ADD CONSTRAINT pk_tbl_existence_dates PRIMARY KEY (existence_date_id);
ALTER TABLE ONLY tbl_harvest_requests
    ADD CONSTRAINT pk_tbl_harvest_requests PRIMARY KEY (harvest_request_id);
ALTER TABLE ONLY tbl_identifiers
    ADD CONSTRAINT pk_tbl_identifiers PRIMARY KEY (identifier_id);
ALTER TABLE ONLY tbl_ids
    ADD CONSTRAINT pk_tbl_ids PRIMARY KEY (column_identifier);
ALTER TABLE ONLY tbl_locations
    ADD CONSTRAINT pk_tbl_locations PRIMARY KEY (location_id);
ALTER TABLE ONLY tbl_name_parts
    ADD CONSTRAINT pk_tbl_name_parts PRIMARY KEY (name_part_id);
ALTER TABLE ONLY tbl_oai_rt_complete_list_records
    ADD CONSTRAINT pk_tbl_oai_rt_complete_list_records PRIMARY KEY (record_number, complete_list_id);
ALTER TABLE ONLY tbl_oai_rt_complete_lists
    ADD CONSTRAINT pk_tbl_oai_rt_complete_lists PRIMARY KEY (complete_list_id);
ALTER TABLE ONLY tbl_oai_rt_resumption_tokens
    ADD CONSTRAINT pk_tbl_oai_rt_resumption_tokens PRIMARY KEY (resumption_token_id);
ALTER TABLE ONLY tbl_physical_addresses
    ADD CONSTRAINT pk_tbl_physical_addresses PRIMARY KEY (physical_address_id);
ALTER TABLE ONLY tbl_registry_objects
    ADD CONSTRAINT pk_tbl_registry_objects PRIMARY KEY (registry_object_key);
ALTER TABLE ONLY tbl_related_info
    ADD CONSTRAINT pk_tbl_related_info PRIMARY KEY (related_info_id);
ALTER TABLE ONLY tbl_related_objects
    ADD CONSTRAINT pk_tbl_related_objects PRIMARY KEY (relation_id);
ALTER TABLE ONLY tbl_relation_descriptions
    ADD CONSTRAINT pk_tbl_relation_descriptions PRIMARY KEY (relation_description_id);
ALTER TABLE ONLY tbl_draft_registry_objects
    ADD CONSTRAINT pk_tbl_rmd_draft_objects PRIMARY KEY (draft_key, registry_object_data_source);
ALTER TABLE ONLY tbl_spatial_extents
    ADD CONSTRAINT pk_tbl_spatial_extents PRIMARY KEY (spatial_extents_id);
ALTER TABLE ONLY tbl_spatial_locations
    ADD CONSTRAINT pk_tbl_spatial_locations PRIMARY KEY (spatial_location_id);
ALTER TABLE ONLY tbl_statuses
    ADD CONSTRAINT pk_tbl_statuses PRIMARY KEY (status);
ALTER TABLE ONLY tbl_subjects
    ADD CONSTRAINT pk_tbl_subjects PRIMARY KEY (subject_id);
ALTER TABLE ONLY tbl_terms
    ADD CONSTRAINT pk_tbl_terms PRIMARY KEY (identifier, vocabulary_identifier);
ALTER TABLE ONLY tbl_authorities
    ADD CONSTRAINT tbl_authority_pkey PRIMARY KEY (identifier);
ALTER TABLE ONLY tbl_citation_contributors
    ADD CONSTRAINT tbl_citation_contributors_pkey PRIMARY KEY (citation_contributor_id);
ALTER TABLE ONLY tbl_citation_dates
    ADD CONSTRAINT tbl_citation_dates_pkey PRIMARY KEY (metadata_date_id);
ALTER TABLE ONLY tbl_citation_information
    ADD CONSTRAINT tbl_citation_information_pkey PRIMARY KEY (citation_info_id);
ALTER TABLE ONLY tbl_coverage
    ADD CONSTRAINT tbl_coverage_pkey PRIMARY KEY (coverage_id);
ALTER TABLE ONLY tbl_temporal_coverage_dates
    ADD CONSTRAINT tbl_temporal_coverage_dates_pkey PRIMARY KEY (coverage_date_id);
ALTER TABLE ONLY tbl_temporal_coverage
    ADD CONSTRAINT tbl_temporal_coverage_pkey PRIMARY KEY (temporal_coverage_id);
ALTER TABLE ONLY tbl_temporal_coverage_text
    ADD CONSTRAINT tbl_temporal_coverage_text_pkey PRIMARY KEY (coverage_text_id);
ALTER TABLE ONLY tbl_vocabularies
    ADD CONSTRAINT tbl_vocabularies_pkey PRIMARY KEY (identifier);

CREATE INDEX idx_access_policies_registry_object_key_1 ON tbl_access_policies USING btree (registry_object_key);
CREATE INDEX idx_address_locations_location_id_1 ON tbl_address_locations USING btree (location_id);
CREATE INDEX idx_address_parts_physical_address_id_1 ON tbl_address_parts USING btree (physical_address_id);
CREATE INDEX idx_citation_contributors_citation_info_id_1 ON tbl_citation_contributors USING btree (citation_info_id);
CREATE INDEX idx_citation_dates_citation_info_id_1 ON tbl_citation_dates USING btree (citation_info_id);
CREATE INDEX idx_complex_names_registry_object_key_1 ON tbl_complex_names USING btree (registry_object_key);
CREATE INDEX idx_coverage_registry_object_key_1 ON tbl_coverage USING btree (registry_object_key);
CREATE INDEX idx_data_source_logs_data_source_key_1 ON tbl_data_source_logs USING btree (data_source_key);
CREATE INDEX idx_descriptions_registry_object_key_1 ON tbl_descriptions USING btree (registry_object_key);
CREATE INDEX idx_electronic_address_args_electronic_address_id_1 ON tbl_electronic_address_args USING btree (electronic_address_id);
CREATE INDEX idx_electronic_addresses_address_id_1 ON tbl_electronic_addresses USING btree (address_id);
CREATE INDEX idx_harvest_requests_data_source_key_1 ON tbl_harvest_requests USING btree (data_source_key);
CREATE INDEX idx_identifiers_registry_object_key_1 ON tbl_identifiers USING btree (registry_object_key);
CREATE INDEX idx_locations_registry_object_key_1 ON tbl_locations USING btree (registry_object_key);
CREATE INDEX idx_name_parts_citation_contributor_id_1 ON tbl_name_parts USING btree (citation_contributor_id);
CREATE INDEX idx_name_parts_complex_name_id_1 ON tbl_name_parts USING btree (complex_name_id);
CREATE INDEX idx_oai_rt_complete_list_records_complete_list_id_1 ON tbl_oai_rt_complete_list_records USING btree (complete_list_id);
CREATE INDEX idx_oai_rt_resumption_tokens_complete_list_id_1 ON tbl_oai_rt_resumption_tokens USING btree (complete_list_id);
CREATE INDEX idx_physical_addresses_address_id_1 ON tbl_physical_addresses USING btree (address_id);
CREATE INDEX idx_registry_object_key_1 ON tbl_citation_information USING btree (registry_object_key);
CREATE INDEX idx_registry_objects_data_source_key_1 ON tbl_registry_objects USING btree (data_source_key);
ALTER TABLE tbl_registry_objects CLUSTER ON idx_registry_objects_data_source_key_1;
CREATE INDEX idx_related_info_registry_object_key_1 ON tbl_related_info USING btree (registry_object_key);
CREATE INDEX idx_related_objects_registry_object_key_1 ON tbl_related_objects USING btree (registry_object_key);
CREATE INDEX idx_relation_descriptions_relation_id_1 ON tbl_relation_descriptions USING btree (relation_id);
CREATE INDEX idx_spacial_locations_coverage_id_1 ON tbl_spatial_locations USING btree (coverage_id);
CREATE INDEX idx_spatial_locations_location_id_1 ON tbl_spatial_locations USING btree (location_id);
CREATE INDEX idx_subjects_registry_object_key_1 ON tbl_subjects USING btree (registry_object_key);
CREATE INDEX idx_temporal_coverage_coverage_id_1 ON tbl_temporal_coverage USING btree (coverage_id);
CREATE INDEX idx_temporal_coverage_dates_temporal_coverage_id_1 ON tbl_temporal_coverage_dates USING btree (temporal_coverage_id);
CREATE INDEX idx_temporal_coverage_text_temporal_coverage_id_1 ON tbl_temporal_coverage_text USING btree (temporal_coverage_id);
CREATE INDEX idx_terms_identifier ON tbl_terms USING btree (identifier);
CREATE INDEX idx_vocabularies_identifier ON tbl_vocabularies USING btree (identifier);

ALTER TABLE ONLY tbl_access_policies
    ADD CONSTRAINT fk_tbl_access_policies_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_address_locations
    ADD CONSTRAINT fk_tbl_address_locations_1 FOREIGN KEY (location_id) REFERENCES tbl_locations(location_id);
ALTER TABLE ONLY tbl_address_parts
    ADD CONSTRAINT fk_tbl_address_parts_1 FOREIGN KEY (physical_address_id) REFERENCES tbl_physical_addresses(physical_address_id);
ALTER TABLE ONLY tbl_complex_names
    ADD CONSTRAINT fk_tbl_complex_names_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_data_source_logs
    ADD CONSTRAINT fk_tbl_data_source_logs_1 FOREIGN KEY (data_source_key) REFERENCES tbl_data_sources(data_source_key);
ALTER TABLE ONLY tbl_descriptions
    ADD CONSTRAINT fk_tbl_descriptions_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_draft_registry_objects
    ADD CONSTRAINT fk_tbl_draft_registry_objects FOREIGN KEY (status) REFERENCES tbl_statuses(status);
ALTER TABLE ONLY tbl_electronic_address_args
    ADD CONSTRAINT fk_tbl_electronic_address_args_1 FOREIGN KEY (electronic_address_id) REFERENCES tbl_electronic_addresses(electronic_address_id);
ALTER TABLE ONLY tbl_electronic_addresses
    ADD CONSTRAINT fk_tbl_electronic_addresses_1 FOREIGN KEY (address_id) REFERENCES tbl_address_locations(address_id);
ALTER TABLE ONLY tbl_harvest_requests
    ADD CONSTRAINT fk_tbl_harvest_requests_1 FOREIGN KEY (data_source_key) REFERENCES tbl_data_sources(data_source_key);
ALTER TABLE ONLY tbl_identifiers
    ADD CONSTRAINT fk_tbl_identifiers_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_locations
    ADD CONSTRAINT fk_tbl_locations_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_name_parts
    ADD CONSTRAINT fk_tbl_name_parts_1 FOREIGN KEY (complex_name_id) REFERENCES tbl_complex_names(complex_name_id);
ALTER TABLE ONLY tbl_oai_rt_complete_list_records
    ADD CONSTRAINT fk_tbl_oai_rt_complete_list_records_1 FOREIGN KEY (complete_list_id) REFERENCES tbl_oai_rt_complete_lists(complete_list_id);
ALTER TABLE ONLY tbl_oai_rt_resumption_tokens
    ADD CONSTRAINT fk_tbl_oai_rt_resumption_tokens_1 FOREIGN KEY (complete_list_id) REFERENCES tbl_oai_rt_complete_lists(complete_list_id);
ALTER TABLE ONLY tbl_physical_addresses
    ADD CONSTRAINT fk_tbl_physical_addresses_1 FOREIGN KEY (address_id) REFERENCES tbl_address_locations(address_id);
ALTER TABLE ONLY tbl_registry_objects
    ADD CONSTRAINT fk_tbl_registry_objects_1 FOREIGN KEY (data_source_key) REFERENCES tbl_data_sources(data_source_key);
ALTER TABLE ONLY tbl_registry_objects
    ADD CONSTRAINT fk_tbl_registry_objects_status FOREIGN KEY (status) REFERENCES tbl_statuses(status);
ALTER TABLE ONLY tbl_related_info
    ADD CONSTRAINT fk_tbl_related_info_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_related_objects
    ADD CONSTRAINT fk_tbl_related_objects_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_relation_descriptions
    ADD CONSTRAINT fk_tbl_relation_descriptions_1 FOREIGN KEY (relation_id) REFERENCES tbl_related_objects(relation_id);
ALTER TABLE ONLY tbl_draft_registry_objects
    ADD CONSTRAINT fk_tbl_rmd_draft_objects_1 FOREIGN KEY (registry_object_data_source) REFERENCES tbl_data_sources(data_source_key);
ALTER TABLE ONLY tbl_spatial_extents
    ADD CONSTRAINT fk_tbl_spatial_extents_1 FOREIGN KEY (spatial_location_id) REFERENCES tbl_spatial_locations(spatial_location_id);
ALTER TABLE ONLY tbl_spatial_locations
    ADD CONSTRAINT fk_tbl_spatial_locations_1 FOREIGN KEY (location_id) REFERENCES tbl_locations(location_id);
ALTER TABLE ONLY tbl_subjects
    ADD CONSTRAINT fk_tbl_subjects_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_existence_dates
    ADD CONSTRAINT idx_existence_dates_registry_object_key_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_rights
    ADD CONSTRAINT idx_rights_registry_object_key_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_citation_contributors
    ADD CONSTRAINT ref_tbl_citation_contributors_to_tbl_citation_information FOREIGN KEY (citation_info_id) REFERENCES tbl_citation_information(citation_info_id);
ALTER TABLE ONLY tbl_citation_information
    ADD CONSTRAINT ref_tbl_citation_information_to_tbl_registry_objects FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_citation_dates
    ADD CONSTRAINT ref_tbl_citation_metadata_dates_to_tbl_citation_information FOREIGN KEY (citation_info_id) REFERENCES tbl_citation_information(citation_info_id);
ALTER TABLE ONLY tbl_coverage
    ADD CONSTRAINT ref_tbl_coverage_to_tbl_registry_objects FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);
ALTER TABLE ONLY tbl_name_parts
    ADD CONSTRAINT ref_tbl_name_parts_to_tbl_citation_contributors FOREIGN KEY (citation_contributor_id) REFERENCES tbl_citation_contributors(citation_contributor_id);
ALTER TABLE ONLY tbl_spatial_locations
    ADD CONSTRAINT ref_tbl_spatial_locations_to_tbl_coverage FOREIGN KEY (coverage_id) REFERENCES tbl_coverage(coverage_id);
ALTER TABLE ONLY tbl_temporal_coverage_dates
    ADD CONSTRAINT ref_tbl_temporal_coverage_dates_to_tbl_temporal_coverage FOREIGN KEY (temporal_coverage_id) REFERENCES tbl_temporal_coverage(temporal_coverage_id);
ALTER TABLE ONLY tbl_temporal_coverage_text
    ADD CONSTRAINT ref_tbl_temporal_coverage_text_to_tbl_temporal_coverage FOREIGN KEY (temporal_coverage_id) REFERENCES tbl_temporal_coverage(temporal_coverage_id);
ALTER TABLE ONLY tbl_temporal_coverage
    ADD CONSTRAINT ref_tbl_temporal_coverage_to_tbl_coverage FOREIGN KEY (coverage_id) REFERENCES tbl_coverage(coverage_id);
ALTER TABLE ONLY tbl_terms
    ADD CONSTRAINT ref_tbl_terms_vocabulary_identifier FOREIGN KEY (vocabulary_identifier) REFERENCES tbl_vocabularies(identifier);
ALTER TABLE ONLY tbl_vocabularies
    ADD CONSTRAINT ref_tbl_vocablularies_authority_identifier FOREIGN KEY (authority_identifier) REFERENCES tbl_authorities(identifier);


-------------
-- CLEANUP PERMISSIONS
-------------

REVOKE ALL ON SCHEMA dba FROM PUBLIC;
REVOKE ALL ON SCHEMA dba FROM dba;
GRANT ALL ON SCHEMA dba TO dba;
GRANT USAGE ON SCHEMA dba TO webuser;

REVOKE ALL ON TABLE tbl_access_policies FROM PUBLIC;
REVOKE ALL ON TABLE tbl_access_policies FROM dba;
GRANT ALL ON TABLE tbl_access_policies TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_access_policies TO webuser;

REVOKE ALL ON TABLE tbl_address_locations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_address_locations FROM dba;
GRANT ALL ON TABLE tbl_address_locations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_address_locations TO webuser;

REVOKE ALL ON TABLE tbl_address_parts FROM PUBLIC;
REVOKE ALL ON TABLE tbl_address_parts FROM dba;
GRANT ALL ON TABLE tbl_address_parts TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_address_parts TO webuser;

REVOKE ALL ON TABLE tbl_citation_contributors FROM PUBLIC;
REVOKE ALL ON TABLE tbl_citation_contributors FROM dba;
GRANT ALL ON TABLE tbl_citation_contributors TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_citation_contributors TO webuser;

REVOKE ALL ON TABLE tbl_citation_dates FROM PUBLIC;
REVOKE ALL ON TABLE tbl_citation_dates FROM dba;
GRANT ALL ON TABLE tbl_citation_dates TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_citation_dates TO webuser;

REVOKE ALL ON TABLE tbl_citation_information FROM PUBLIC;
REVOKE ALL ON TABLE tbl_citation_information FROM dba;
GRANT ALL ON TABLE tbl_citation_information TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_citation_information TO webuser;

REVOKE ALL ON TABLE tbl_complex_names FROM PUBLIC;
REVOKE ALL ON TABLE tbl_complex_names FROM dba;
GRANT ALL ON TABLE tbl_complex_names TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_complex_names TO webuser;

REVOKE ALL ON TABLE tbl_name_parts FROM PUBLIC;
REVOKE ALL ON TABLE tbl_name_parts FROM dba;
GRANT ALL ON TABLE tbl_name_parts TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_name_parts TO webuser;

REVOKE ALL ON TABLE tbl_coverage FROM PUBLIC;
REVOKE ALL ON TABLE tbl_coverage FROM dba;
GRANT ALL ON TABLE tbl_coverage TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_coverage TO webuser;

REVOKE ALL ON TABLE tbl_data_source_logs FROM PUBLIC;
REVOKE ALL ON TABLE tbl_data_source_logs FROM dba;
GRANT ALL ON TABLE tbl_data_source_logs TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_data_source_logs TO webuser;

REVOKE ALL ON TABLE tbl_data_sources FROM PUBLIC;
REVOKE ALL ON TABLE tbl_data_sources FROM dba;
GRANT ALL ON TABLE tbl_data_sources TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_data_sources TO webuser;

REVOKE ALL ON TABLE tbl_descriptions FROM PUBLIC;
REVOKE ALL ON TABLE tbl_descriptions FROM dba;
GRANT ALL ON TABLE tbl_descriptions TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_descriptions TO webuser;

REVOKE ALL ON TABLE tbl_draft_registry_objects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_draft_registry_objects FROM dba;
GRANT ALL ON TABLE tbl_draft_registry_objects TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_draft_registry_objects TO webuser;

REVOKE ALL ON TABLE tbl_electronic_address_args FROM PUBLIC;
REVOKE ALL ON TABLE tbl_electronic_address_args FROM dba;
GRANT ALL ON TABLE tbl_electronic_address_args TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_electronic_address_args TO webuser;

REVOKE ALL ON TABLE tbl_electronic_addresses FROM PUBLIC;
REVOKE ALL ON TABLE tbl_electronic_addresses FROM dba;
GRANT ALL ON TABLE tbl_electronic_addresses TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_electronic_addresses TO webuser;

REVOKE ALL ON TABLE tbl_existence_dates FROM PUBLIC;
REVOKE ALL ON TABLE tbl_existence_dates FROM dba;
GRANT ALL ON TABLE tbl_existence_dates TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_existence_dates TO webuser;

REVOKE ALL ON TABLE tbl_related_objects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_related_objects FROM dba;
GRANT ALL ON TABLE tbl_related_objects TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_related_objects TO webuser;

REVOKE ALL ON TABLE tbl_harvest_requests FROM PUBLIC;
REVOKE ALL ON TABLE tbl_harvest_requests FROM dba;
GRANT ALL ON TABLE tbl_harvest_requests TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_harvest_requests TO webuser;

REVOKE ALL ON TABLE tbl_identifiers FROM PUBLIC;
REVOKE ALL ON TABLE tbl_identifiers FROM dba;
GRANT ALL ON TABLE tbl_identifiers TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_identifiers TO webuser;

REVOKE ALL ON TABLE tbl_registry_objects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_registry_objects FROM dba;
GRANT ALL ON TABLE tbl_registry_objects TO dba;
GRANT ALL ON TABLE tbl_registry_objects TO webuser;

REVOKE ALL ON TABLE vw_registry_objects FROM PUBLIC;
REVOKE ALL ON TABLE vw_registry_objects FROM dba;
GRANT ALL ON TABLE vw_registry_objects TO dba;
GRANT SELECT ON TABLE vw_registry_objects TO webuser;

REVOKE ALL ON TABLE tbl_locations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_locations FROM dba;
GRANT ALL ON TABLE tbl_locations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_locations TO webuser;

REVOKE ALL ON FUNCTION udf_get_party_nla_identifiers() FROM PUBLIC;
REVOKE ALL ON FUNCTION udf_get_party_nla_identifiers() FROM dba;
GRANT ALL ON FUNCTION udf_get_party_nla_identifiers() TO dba;
GRANT ALL ON FUNCTION udf_get_party_nla_identifiers() TO PUBLIC;

REVOKE ALL ON TABLE tbl_physical_addresses FROM PUBLIC;
REVOKE ALL ON TABLE tbl_physical_addresses FROM dba;
GRANT ALL ON TABLE tbl_physical_addresses TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_physical_addresses TO webuser;

REVOKE ALL ON TABLE tbl_raw_records FROM PUBLIC;
REVOKE ALL ON TABLE tbl_raw_records FROM dba;
GRANT ALL ON TABLE tbl_raw_records TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_raw_records TO webuser;

REVOKE ALL ON TABLE vw_names FROM PUBLIC;
REVOKE ALL ON TABLE vw_names FROM dba;
GRANT ALL ON TABLE vw_names TO dba;
GRANT SELECT ON TABLE vw_names TO webuser;

REVOKE ALL ON TABLE tbl_spatial_locations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_spatial_locations FROM dba;
GRANT ALL ON TABLE tbl_spatial_locations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_spatial_locations TO webuser;

REVOKE ALL ON TABLE vw_registry_search FROM PUBLIC;
REVOKE ALL ON TABLE vw_registry_search FROM dba;
GRANT ALL ON TABLE vw_registry_search TO dba;
GRANT SELECT ON TABLE vw_registry_search TO webuser;

REVOKE ALL ON TABLE tbl_related_info FROM PUBLIC;
REVOKE ALL ON TABLE tbl_related_info FROM dba;
GRANT ALL ON TABLE tbl_related_info TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_related_info TO webuser;

REVOKE ALL ON TABLE tbl_relation_descriptions FROM PUBLIC;
REVOKE ALL ON TABLE tbl_relation_descriptions FROM dba;
GRANT ALL ON TABLE tbl_relation_descriptions TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_relation_descriptions TO webuser;

REVOKE ALL ON TABLE tbl_oai_rt_resumption_tokens FROM PUBLIC;
REVOKE ALL ON TABLE tbl_oai_rt_resumption_tokens FROM dba;
GRANT ALL ON TABLE tbl_oai_rt_resumption_tokens TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_oai_rt_resumption_tokens TO webuser;

REVOKE ALL ON TABLE tbl_rights FROM PUBLIC;
REVOKE ALL ON TABLE tbl_rights FROM dba;
GRANT ALL ON TABLE tbl_rights TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_rights TO webuser;

REVOKE ALL ON TABLE tbl_registry_objects_statistics FROM PUBLIC;
REVOKE ALL ON TABLE tbl_registry_objects_statistics FROM dba;
GRANT ALL ON TABLE tbl_registry_objects_statistics TO dba;
GRANT ALL ON TABLE tbl_registry_objects_statistics TO webuser;

REVOKE ALL ON TABLE tbl_terms FROM PUBLIC;
REVOKE ALL ON TABLE tbl_terms FROM dba;
GRANT ALL ON TABLE tbl_terms TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_terms TO webuser;

REVOKE ALL ON TABLE tbl_subjects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_subjects FROM dba;
GRANT ALL ON TABLE tbl_subjects TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_subjects TO webuser;

REVOKE ALL ON TABLE tbl_temporal_coverage FROM PUBLIC;
REVOKE ALL ON TABLE tbl_temporal_coverage FROM dba;
GRANT ALL ON TABLE tbl_temporal_coverage TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_temporal_coverage TO webuser;

REVOKE ALL ON TABLE tbl_temporal_coverage_dates FROM PUBLIC;
REVOKE ALL ON TABLE tbl_temporal_coverage_dates FROM dba;
GRANT ALL ON TABLE tbl_temporal_coverage_dates TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_temporal_coverage_dates TO webuser;

REVOKE ALL ON TABLE tbl_temporal_coverage_text FROM PUBLIC;
REVOKE ALL ON TABLE tbl_temporal_coverage_text FROM dba;
GRANT ALL ON TABLE tbl_temporal_coverage_text TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_temporal_coverage_text TO webuser;

REVOKE ALL ON TABLE tbl_authorities FROM PUBLIC;
REVOKE ALL ON TABLE tbl_authorities FROM dba;
GRANT ALL ON TABLE tbl_authorities TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_authorities TO webuser;

REVOKE ALL ON TABLE tbl_ids FROM PUBLIC;
REVOKE ALL ON TABLE tbl_ids FROM dba;
GRANT ALL ON TABLE tbl_ids TO dba;
GRANT SELECT,UPDATE ON TABLE tbl_ids TO webuser;

REVOKE ALL ON TABLE tbl_oai_rt_complete_list_records FROM PUBLIC;
REVOKE ALL ON TABLE tbl_oai_rt_complete_list_records FROM dba;
GRANT ALL ON TABLE tbl_oai_rt_complete_list_records TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_oai_rt_complete_list_records TO webuser;

REVOKE ALL ON TABLE tbl_oai_rt_complete_lists FROM PUBLIC;
REVOKE ALL ON TABLE tbl_oai_rt_complete_lists FROM dba;
GRANT ALL ON TABLE tbl_oai_rt_complete_lists TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_oai_rt_complete_lists TO webuser;

REVOKE ALL ON TABLE tbl_search_statistics FROM PUBLIC;
REVOKE ALL ON TABLE tbl_search_statistics FROM dba;
GRANT ALL ON TABLE tbl_search_statistics TO dba;
GRANT SELECT,INSERT,UPDATE ON TABLE tbl_search_statistics TO webuser;

REVOKE ALL ON TABLE tbl_spatial_extents FROM PUBLIC;
REVOKE ALL ON TABLE tbl_spatial_extents FROM dba;
GRANT ALL ON TABLE tbl_spatial_extents TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_spatial_extents TO webuser;

REVOKE ALL ON TABLE tbl_statuses FROM PUBLIC;
REVOKE ALL ON TABLE tbl_statuses FROM dba;
GRANT ALL ON TABLE tbl_statuses TO dba;
GRANT SELECT ON TABLE tbl_statuses TO webuser;

REVOKE ALL ON TABLE tbl_vocabularies FROM PUBLIC;
REVOKE ALL ON TABLE tbl_vocabularies FROM dba;
GRANT ALL ON TABLE tbl_vocabularies TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_vocabularies TO webuser;


---
-- INSERT DEFAULT DATA VALUES
---
INSERT INTO tbl_statuses VALUES ('APPROVED');
INSERT INTO tbl_statuses VALUES ('PENDING');
INSERT INTO tbl_statuses VALUES ('PUBLISHED');
INSERT INTO tbl_statuses VALUES ('DRAFT');
INSERT INTO tbl_statuses VALUES ('ASSESSMENT_IN_PROGRESS');
INSERT INTO tbl_statuses VALUES ('SUBMITTED_FOR_ASSESSMENT');
INSERT INTO tbl_statuses VALUES ('MORE_WORK_REQUIRED');
INSERT INTO tbl_statuses VALUES ('DELETED');

INSERT INTO tbl_ids VALUES ('dba.tbl_simple_names.simple_name_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_citation_dates.metadata_date_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_citation_contributors.citation_contributor_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_electronic_address_args.electronic_address_arg_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_related_objects.relation_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_relation_description_id.relation_description_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_oai_rt_complete_lists.complete_list_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_citation_information.citation_info_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_access_policies.access_policy_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_electronic_addresses.electronic_address_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_temporal_coverage_text.coverage_text_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_existence_dates.existence_dates_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_rights.rights_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_identifiers.identifier_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_complex_names.complex_name_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_name_parts.name_part_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_locations.location_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_address_locations.address_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_subjects.subject_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_descriptions.description_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_related_info.related_info_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_coverage.coverage_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_spatial_locations.spatial_location_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_temporal_coverage.temporal_coverage_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_temporal_coverage_dates.coverage_date_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_physical_addresses.physical_address_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_address_parts.address_part_id', 0);
INSERT INTO tbl_ids VALUES ('dba.tbl_data_source_logs.event_id', 0);

INSERT INTO tbl_vocabularies VALUES ('RIFCSOriginatingSourceType', 'local', '1.2', 'RIFCS Originating Source Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSCollectionType', 'local', '1.2', 'RIFCS Collection Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSPartyType', 'local', '1.2', 'RIFCS Party Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSServiceType', 'local', '1.2', 'RIFCS Service Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSDescriptionType', 'local', '1.2', 'RIFCS Description Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSSpatialType', 'local', '1.2', 'RIFCS Spatial Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSPhysicalAddressType', 'local', '1.2', 'RIFCS Physical Address Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSPhysicalAddressPartType', 'local', '1.2', 'RIFCS Physical Address Part Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSActivityRelationType', 'local', '1.2', 'RIFCS Activity Relation Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSCollectionRelationType', 'local', '1.2', 'RIFCS Collection Relation Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSPartyRelationType', 'local', '1.2', 'RIFCS Party Relation Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSServiceRelationType', 'local', '1.2', 'RIFCS Service Relation Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSNameType', 'local', '1.2', 'RIFCS Name Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSNamePartType', 'local', '1.2', 'RIFCS Name Part Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSIdentifierType', 'local', '1.2', 'RIFCS Identifier Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSElectronicAddressType', 'local', '1.2', 'RIFCS Electronic Address Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSArgType', 'local', '1.2', 'RIFCS Arg Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSArgUse', 'local', '1.2', 'RIFCS Arg Use', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSTemporalCoverageDateType', 'local', '1.2', 'RIFCS Temporal Coverage Date Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSTemporalCoverageDateFormat', 'local', '1.2', 'RIFCS Temporal Coverage Date Format', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSCitationStyle', 'local', '1.2', 'RIFCS Citation Style', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSCitationIdentifierType', 'local', '1.2', 'RIFCS Citation Identifier Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSCitationDateType', 'local', '1.2', 'RIFCS Citation Date Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSRelatedInformationType', 'local', '1.2', 'RIFCS Related Information Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSRelatedInformationIdentifierType', 'local', '1.2', 'RIFCS Related Information Identifier Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSSubjectType', 'local', '1.2', 'RIFCS Subject Type', 'primary', 'RIF-CS Subject Type Vocabulary', 'http://services.ands.org.au/documentation/rifcs/schema/vocabularies.html#Arg%20Use', NULL);
INSERT INTO tbl_vocabularies VALUES ('RIFCSActivityType', 'local', '1.2', 'RIFCS Activity Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.2.0/vocabs/vocabularies.html', NULL);
INSERT INTO tbl_vocabularies VALUES ('ANZSRC-FOR', 'local', '2008', 'ANZSRC Field of Research', 'primary', '', 'http://www.abs.gov.au/', NULL);
INSERT INTO tbl_vocabularies VALUES ('ANZSRC-SEO', 'local', '2008', 'ANZSRC Socio-Economic Objective', 'primary', '', 'http://www.abs.gov.au/', NULL);
INSERT INTO tbl_vocabularies VALUES ('ANZSRC-TOA', 'local', '2008', 'ANZSRC Type of Activity', 'primary', '', 'http://www.abs.gov.au/', NULL);

INSERT INTO tbl_terms VALUES ('iso19139dcmiBox', 'local', '', 'iso19139dcmiBox', 'DCMI Box notation derived from bounding box metadata conformant with the iso19139 schema', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('iso31661', 'local', '', 'iso31661', 'ISO 3166-1 Codes for the representation of names of countries and their subdivisions - Part 1: Country codes', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('iso31662', 'local', '', 'iso31662', 'Codes for the representation of names of countries and their subdivisions - Part 2: Country subdivision codes', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('kml', 'local', '', 'kml', 'Keyhole Markup Language developed for use with Google Earth', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('kmlPolyCoords', 'local', '', 'kmlPolyCoords', 'A set of KML long/lat co-ordinates defining a polygon as described by the KML coordinates element', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('logo', 'local', '', 'logo', 'symbol used as an identifying mark', '', 'RIFCSDescriptionType', NULL, 'pt', '', 'RIFCS Description Type', '');
INSERT INTO tbl_terms VALUES ('note', 'local', '', 'note', 'a brief informational message, not object metadata, to notify the record consumer of some important aspect regarding the object or its metadata', '', 'RIFCSDescriptionType', NULL, 'pt', '', 'RIFCS Description Type', '');
INSERT INTO tbl_terms VALUES ('person', 'local', '', 'person', 'human being or identity assumed by one or more human beings', '', 'RIFCSPartyType', NULL, 'pt', '', 'RIFCS Party Type', '');
INSERT INTO tbl_terms VALUES ('postalAddress', 'local', '', 'postalAddress', 'address where mail for an entity should be sent', '', 'RIFCSPhysicalAddressType', NULL, 'pt', '', 'RIFCS Physical Address Type', '');
INSERT INTO tbl_terms VALUES ('program', 'local', '', 'program', 'system of activities intended to meet a public need', '', 'RIFCSActivityType', NULL, 'pt', '', 'RIFCS Activity Type', '');
INSERT INTO tbl_terms VALUES ('project', 'local', '', 'project', 'piece of work that is undertaken or attempted, with a start and end date and defined objectives', '', 'RIFCSActivityType', NULL, 'pt', '', 'RIFCS Activity Type', '');
INSERT INTO tbl_terms VALUES ('registry', 'local', '', 'registry', 'collection of registry objects compiled to support the business of a given community', '', 'RIFCSCollectionType', NULL, 'pt', '', 'RIFCS Collection Type', '');
INSERT INTO tbl_terms VALUES ('report', 'local', '', 'report', 'visualisation, summary', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('repository', 'local', '', 'repository', 'collection of physical or digital objects compiled for information and documentation purposes and/or for storage and safekeeping', '', 'RIFCSCollectionType', NULL, 'pt', '', 'RIFCS Collection Type', '');
INSERT INTO tbl_terms VALUES ('890106', 'local', '', 'Videoconference Services', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('search-http', 'local', '', 'search-http', 'Search service over HTTP', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('search-opensearch', 'local', '', 'search-opensearch', 'OpenSearch search', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('search-sru', 'local', '', 'search-sru', 'SRU search', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('search-srw', 'local', '', 'search-srw', 'SRW search', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('search-z3950', 'local', '', 'search-z3950', 'z39.50 search', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('syndicate-atom', 'local', '', 'syndicate-atom', 'ATOM syndication', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('hasPart', 'local', '', 'hasPart', 'contains the related service', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isLocatedIn', 'local', '', 'isLocatedIn', 'is held in the related repository', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isLocationFor', 'local', '', 'isLocationFor', 'is the repository where the related collection is held', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isManagedBy', 'local', '', 'isManagedBy', 'is organised and/or delivered by the related party', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('isManagedBy', 'local', '', 'isManagedBy', 'is maintained and made accessible by the related party', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isManagedBy', 'local', '', 'isManagedBy', 'is overseen by the related party', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isManagedBy', 'local', '', 'isManagedBy', 'is overseen by the related party', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isManagerOf', 'local', '', 'isManagerOf', 'oversees the related party or administers the related collection', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isMemberOf', 'local', '', 'isMemberOf', 'is enroled in the related group', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isOutputOf', 'local', '', 'isOutputOf', 'is a product of the related activity', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isOwnedBy', 'local', '', 'isOwnedBy', 'legally belongs to the related party', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('isOwnedBy', 'local', '', 'isOwnedBy', 'legally belongs to the related party', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isOwnedBy', 'local', '', 'isOwnedBy', 'legally belongs to the related party', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isOwnedBy', 'local', '', 'isOwnedBy', 'legally belongs to the related party', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isOwnerOf', 'local', '', 'isOwnerOf', 'legally possesses the related activity, collection, service or group', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isParticipantIn', 'local', '', 'isParticipantIn', 'is enroled in the related activity', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isPartOf', 'local', '', 'isPartOf', 'is contained in the related activity', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('isPartOf', 'local', '', 'isPartOf', 'is contained within the related collection', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isPartOf', 'local', '', 'isPartOf', '(group only) is contained in the related group', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isPartOf', 'local', '', 'isPartOf', 'is contained in the related service', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isSupportedBy', 'local', '', 'isSupportedBy', 'enables contribution and access to and use of the related collection', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('makesAvailable', 'local', '', 'makesAvailable', 'specialisation of supports type - for Harvest, Search and Syndicate', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('supports', 'local', '', 'supports', 'can be contributed to, accessed or used through the related service', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('telephoneNumber', 'local', '', 'telephoneNumber', 'an address part that contains a telephone number including a mobile telephone number', '', 'RIFCSPhysicalAddressPartType', NULL, 'pt', '', 'RIFCS Physical Address Part Type', '');
INSERT INTO tbl_terms VALUES ('isOperatedOnBy', 'local', '', 'isOperatedOnBy', 'specialisation of isSupportBy type - for Transform', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isPresentedBy', 'local', '', 'isPresentedBy', 'specialisation of isSupportBy type - for Report', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isProducedBy', 'local', '', 'isProducedBy', 'specialisation of isSupportBy type - for Create, Generate and Assemble', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('keyValue', 'local', '', 'keyValue', '(service only) indicates the argument is passed using key=value pairings in the query component of a URL', '', 'RIFCSArgUse', NULL, 'pt', '', 'RIFCS Arg Use', '');
INSERT INTO tbl_terms VALUES ('local', 'local', '', 'local', 'identifer unique within a local context', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('MLA', 'local', '', 'MLA', 'Modern Language Association of America', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('object', 'local', '', 'object', 'indicates the value of an argument is an object, most likely in serialized form', '', 'RIFCSArgType', NULL, 'pt', '', 'RIFCS Arg Type', '');
INSERT INTO tbl_terms VALUES ('operatesOn', 'local', '', 'operatesOn', 'specialisation of supports type - for Transform', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('other', 'local', '', 'other', 'other electronic address', '', 'RIFCSElectronicAddressType', NULL, 'pt', '', 'RIFCS Electronic Address Type', '');
INSERT INTO tbl_terms VALUES ('presents', 'local', '', 'presents', 'specialisation of supports type - for Report', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('primary', 'local', '', 'primary', 'official name of the registry object', '', 'RIFCSNameType', NULL, 'pt', '', 'RIFCS Name Type', '');
INSERT INTO tbl_terms VALUES ('produces', 'local', '', 'produces', 'specialisation of supports type - for Create, Generate and Assemble', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('purl', 'local', '', 'purl', 'Persistent Uniform Resource Locator', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('string', 'local', '', 'string', '(service only) Indicates the value of an argument is a plain text string', '', 'RIFCSArgType', NULL, 'pt', '', 'RIFCS Arg Type', '');
INSERT INTO tbl_terms VALUES ('suffix', 'local', '', 'suffix', 'honours, awards, qualifications and other identifiers conferred', '', 'RIFCSNamePartType', NULL, 'pt', '', 'RIFCS Name Part Type', '');
INSERT INTO tbl_terms VALUES ('title', 'local', '', 'title', 'word or phrase indicative of rank, office, nobility, honour, etc., or a term of address associated with a person', '', 'RIFCSNamePartType', NULL, 'pt', '', 'RIFCS Name Part Type', '');
INSERT INTO tbl_terms VALUES ('uri', 'local', '', 'uri', 'Uniform Resource Identifier', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('url', 'local', '', 'url', 'Uniform Resource Locator', '', 'RIFCSElectronicAddressType', NULL, 'pt', '', 'RIFCS Electronic Address Type', '');
INSERT INTO tbl_terms VALUES ('UTC', 'local', '', 'UTC', 'Coordinated Universal Time', '', 'RIFCSTemporalCoverageDateFormat', NULL, 'pt', '', 'RIFCS Temporal Coverage Date Format', '');
INSERT INTO tbl_terms VALUES ('Vancouver', 'local', '', 'Vancouver', '', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('W3CDTF', 'local', '', 'W3CDTF', 'W3C Date Time Format', '', 'RIFCSTemporalCoverageDateFormat', NULL, 'pt', '', 'RIFCS Temporal Coverage Date Format', '');
INSERT INTO tbl_terms VALUES ('wsdl', 'local', '', 'wsdl', '(service only) Web Service Definition Language', '', 'RIFCSElectronicAddressType', NULL, 'pt', '', 'RIFCS Electronic Address Type', '');
INSERT INTO tbl_terms VALUES ('140100', 'local', '', '140100', '', '', 'ANZSRC-FOR', '140000', 'pt', '', 'ANZSRC>>ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('190900', 'local', '', '190900', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('019999', 'local', '', 'Mathematical Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '019900', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>OTHER MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020000', 'local', '', 'PHYSICAL SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('020100', 'local', '', 'ASTRONOMICAL AND SPACE SCIENCES', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020101', 'local', '', 'Astrobiology', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020102', 'local', '', 'Astronomical and Space Instrumentation', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020103', 'local', '', 'Cosmology and Extragalactic Astronomy', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020104', 'local', '', 'Galactic Astronomy', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020105', 'local', '', 'General Relativity and Gravitational Waves', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020106', 'local', '', 'High Energy Astrophysics; Cosmic Rays', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020107', 'local', '', 'Mesospheric, Ionospheric and Magnetospheric Physics', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020108', 'local', '', 'Planetary Science (excl. Extraterrestrial Geology)', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020109', 'local', '', 'Space and Solar Physics', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020110', 'local', '', 'Stellar Astronomy and Planetary Systems', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020199', 'local', '', 'Astronomical and Space Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '020100', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ASTRONOMICAL AND SPACE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('199900', 'local', '', '199900', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('issn', 'local', '', 'issn', 'International Standard Serial Number', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('issn', 'local', '', 'issn', 'International Standard Serial Number', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('issued', 'local', '', 'issued', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('istc', 'local', '', 'istc', 'International Standard Text Code. http://www.istc-international.org/html/', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('istc', 'local', '', 'istc', 'International Standard Text Code. http://www.istc-international.org/html/', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('lissn', 'local', '', 'lissn', '', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('lissn', 'local', '', 'lissn', '', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('local', 'local', '', 'local', 'identifer unique within a local context', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('local', 'local', '', 'local', 'identifer unique within a local context', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('modified', 'local', '', 'modified', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('publication', 'local', '', 'publication', 'any formally published document, whether available in digital or online form or not.', '', 'RIFCSRelatedInformationType', NULL, 'pt', '', 'RIFCS Related Information Type', '');
INSERT INTO tbl_terms VALUES ('publicationDate', 'local', '', 'publicationDate', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('010000', 'local', '', 'MATHEMATICAL SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('010100', 'local', '', 'PURE MATHEMATICS', '', '', 'ANZSRC-FOR', '010000', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('010101', 'local', '', 'Algebra and Number Theory', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010102', 'local', '', 'Algebraic and Differential Geometry', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010103', 'local', '', 'Category Theory, K Theory, Homological Algebra', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010104', 'local', '', 'Combinatorics and Discrete Mathematics (excl. Physical Combinatorics)', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010105', 'local', '', 'Group Theory and Generalisations', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010106', 'local', '', 'Lie Groups, Harmonic and Fourier Analysis', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010107', 'local', '', 'Mathematical Logic, Set Theory, Lattices and Universal  Algebra', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010108', 'local', '', 'Operator Algebras and Functional Analysis', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010109', 'local', '', 'Ordinary Differential Equations, Difference Equations and  Dynamical Systems', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010110', 'local', '', 'Partial Differential Equations', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010111', 'local', '', 'Real and Complex Functions (incl. Several Variables)', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010112', 'local', '', 'Topology', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010199', 'local', '', 'Pure Mathematics not elsewhere classified', '', '', 'ANZSRC-FOR', '010100', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>PURE MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010200', 'local', '', 'APPLIED MATHEMATICS', '', '', 'ANZSRC-FOR', '010000', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('010201', 'local', '', 'Approximation Theory and Asymptotic Methods', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010202', 'local', '', 'Biological Mathematics', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010203', 'local', '', 'Calculus of Variations, Systems Theory and Control Theory', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010204', 'local', '', 'Dynamical Systems in Applications', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010205', 'local', '', 'Financial Mathematics', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010206', 'local', '', 'Operations Research', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010207', 'local', '', 'Theoretical and Applied Mechanics', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010299', 'local', '', 'Applied Mathematics not elsewhere classified', '', '', 'ANZSRC-FOR', '010200', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>APPLIED MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010300', 'local', '', 'NUMERICAL AND COMPUTATIONAL MATHEMATICS', '', '', 'ANZSRC-FOR', '010000', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('010301', 'local', '', 'Numerical Analysis', '', '', 'ANZSRC-FOR', '010300', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>NUMERICAL AND COMPUTATIONAL MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010302', 'local', '', 'Numerical Solution of Differential and Integral Equations', '', '', 'ANZSRC-FOR', '010300', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>NUMERICAL AND COMPUTATIONAL MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010303', 'local', '', 'Optimisation', '', '', 'ANZSRC-FOR', '010300', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>NUMERICAL AND COMPUTATIONAL MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010399', 'local', '', 'Numerical and Computational Mathematics not elsewhere classified', '', '', 'ANZSRC-FOR', '010300', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>NUMERICAL AND COMPUTATIONAL MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('010400', 'local', '', 'STATISTICS', '', '', 'ANZSRC-FOR', '010000', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('010401', 'local', '', 'Applied Statistics', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010402', 'local', '', 'Biostatistics', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010403', 'local', '', 'Forensic Statistics', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010404', 'local', '', 'Probability Theory', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010405', 'local', '', 'Statistical Theory', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010406', 'local', '', 'Stochastic Analysis and Modelling', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010499', 'local', '', 'Statistics not elsewhere classified', '', '', 'ANZSRC-FOR', '010400', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>STATISTICS', '');
INSERT INTO tbl_terms VALUES ('010500', 'local', '', 'MATHEMATICAL PHYSICS', '', '', 'ANZSRC-FOR', '010000', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('010501', 'local', '', 'Algebraic Structures in Mathematical Physics', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('010502', 'local', '', 'Integrable Systems (Classical and Quantum)', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('010503', 'local', '', 'Mathematical Aspects of Classical Mechanics, Quantum  Mechanics and Quantum Information Theory', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('010504', 'local', '', 'Mathematical Aspects of General Relativity', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('010505', 'local', '', 'Mathematical Aspects of Quantum and Conformal Field  Theory, Quantum Gravity and String Theory', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('010506', 'local', '', 'Statistical Mechanics, Physical Combinatorics and Mathematical Aspects of Condensed Matter', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('010599', 'local', '', 'Mathematical Physics not elsewhere classified', '', '', 'ANZSRC-FOR', '010500', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES>>MATHEMATICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('019900', 'local', '', 'OTHER MATHEMATICAL SCIENCES', '', '', 'ANZSRC-FOR', '010000', 'pt', '', 'ANZSRC>>MATHEMATICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020200', 'local', '', 'ATOMIC, MOLECULAR, NUCLEAR, PARTICLE AND  PLASMA PHYSICS', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('local', 'local', '', 'local', 'Uncontrolled keyword or keyword from a local vocabulary', 'sn', 'RIFCSSubjectType', NULL, 'pt', '', 'RIF-CS Subject Type', '');
INSERT INTO tbl_terms VALUES ('020201', 'local', '', 'Atomic and Molecular Physics', '', '', 'ANZSRC-FOR', '020200', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ATOMIC, MOLECULAR, NUCLEAR, PARTICLE AND  PLASMA PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020202', 'local', '', 'Nuclear Physics', '', '', 'ANZSRC-FOR', '020200', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ATOMIC, MOLECULAR, NUCLEAR, PARTICLE AND  PLASMA PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020203', 'local', '', 'Particle Physics', '', '', 'ANZSRC-FOR', '020200', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ATOMIC, MOLECULAR, NUCLEAR, PARTICLE AND  PLASMA PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020204', 'local', '', 'Plasma Physics; Fusion Plasmas; Electrical Discharges', '', '', 'ANZSRC-FOR', '020200', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ATOMIC, MOLECULAR, NUCLEAR, PARTICLE AND  PLASMA PHYSICS', '');
INSERT INTO tbl_terms VALUES ('upc', 'local', '', 'upc', 'Universal Product Code', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('020299', 'local', '', 'Atomic, Molecular, Nuclear, Particle and Plasma Physics not elsewhere classified', '', '', 'ANZSRC-FOR', '020200', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>ATOMIC, MOLECULAR, NUCLEAR, PARTICLE AND  PLASMA PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020300', 'local', '', 'CLASSICAL PHYSICS', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020301', 'local', '', 'Acoustics and Acoustical Devices; Waves', '', '', 'ANZSRC-FOR', '020300', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CLASSICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020302', 'local', '', 'Electrostatics and Electrodynamics', '', '', 'ANZSRC-FOR', '020300', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CLASSICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020303', 'local', '', 'Fluid Physics', '', '', 'ANZSRC-FOR', '020300', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CLASSICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020304', 'local', '', 'Thermodynamics and Statistical Physics', '', '', 'ANZSRC-FOR', '020300', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CLASSICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020399', 'local', '', 'Classical Physics not elsewhere classified', '', '', 'ANZSRC-FOR', '020300', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CLASSICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020400', 'local', '', 'CONDENSED MATTER PHYSICS', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020401', 'local', '', 'Condensed Matter Characterisation Technique  Development', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020402', 'local', '', 'Condensed Matter Imaging', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020403', 'local', '', 'Condensed Matter Modelling and Density Functional  Theory', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020404', 'local', '', 'Electronic and Magnetic Properties of Condensed Matter;  Superconductivity', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020405', 'local', '', 'Soft Condensed Matter', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020406', 'local', '', 'Surfaces and Structural Properties of Condensed Matter', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020499', 'local', '', 'Condensed Matter Physics not elsewhere classified', '', '', 'ANZSRC-FOR', '020400', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>CONDENSED MATTER PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020500', 'local', '', 'OPTICAL PHYSICS', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020501', 'local', '', 'Classical and Physical Optics', '', '', 'ANZSRC-FOR', '020500', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OPTICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020502', 'local', '', 'Lasers and Quantum Electronics', '', '', 'ANZSRC-FOR', '020500', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OPTICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020503', 'local', '', 'Nonlinear Optics and Spectroscopy', '', '', 'ANZSRC-FOR', '020500', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OPTICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020504', 'local', '', 'Photonics, Optoelectronics and Optical Communications', '', '', 'ANZSRC-FOR', '020500', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OPTICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020599', 'local', '', 'Optical Physics not elsewhere classified', '', '', 'ANZSRC-FOR', '020500', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OPTICAL PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020600', 'local', '', 'QUANTUM PHYSICS', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('020601', 'local', '', 'Degenerate Quantum Gases and Atom Optics', '', '', 'ANZSRC-FOR', '020600', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>QUANTUM PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020602', 'local', '', 'Field Theory and String Theory', '', '', 'ANZSRC-FOR', '020600', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>QUANTUM PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020603', 'local', '', 'Quantum Information, Computation and Communication', '', '', 'ANZSRC-FOR', '020600', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>QUANTUM PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020604', 'local', '', 'Quantum Optics', '', '', 'ANZSRC-FOR', '020600', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>QUANTUM PHYSICS', '');
INSERT INTO tbl_terms VALUES ('020699', 'local', '', 'Quantum Physics not elsewhere classified', '', '', 'ANZSRC-FOR', '020600', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>QUANTUM PHYSICS', '');
INSERT INTO tbl_terms VALUES ('029900', 'local', '', 'OTHER PHYSICAL SCIENCES', '', '', 'ANZSRC-FOR', '020000', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('029901', 'local', '', 'Biological Physics', '', '', 'ANZSRC-FOR', '029900', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OTHER PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('029902', 'local', '', 'Complex Physical Systems', '', '', 'ANZSRC-FOR', '029900', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OTHER PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('029903', 'local', '', 'Medical Physics', '', '', 'ANZSRC-FOR', '029900', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OTHER PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('029904', 'local', '', 'Synchrotrons; Accelerators; Instruments and Techniques', '', '', 'ANZSRC-FOR', '029900', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OTHER PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('029999', 'local', '', 'Physical Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '029900', 'pt', '', 'ANZSRC>>PHYSICAL SCIENCES>>OTHER PHYSICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('030000', 'local', '', 'CHEMICAL SCIENCE', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('030100', 'local', '', 'ANALYTICAL CHEMISTRY', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030101', 'local', '', 'Analytical Spectrometry', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030102', 'local', '', 'Electroanalytical Chemistry', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030103', 'local', '', 'Flow Analysis', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030104', 'local', '', 'Immunological and Bioassay Methods', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030105', 'local', '', 'Instrumental Methods (excl. Immunological and Bioassay Methods)', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030106', 'local', '', 'Quality Assurance, Chemometrics, Traceability and  Metrological Chemistry', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030107', 'local', '', 'Sensor Technology (Chemical aspects)', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030108', 'local', '', 'Separation Science', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030199', 'local', '', 'Analytical Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030100', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ANALYTICAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030200', 'local', '', 'INORGANIC CHEMISTRY', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030201', 'local', '', 'Bioinorganic Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030202', 'local', '', 'f-Block Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030203', 'local', '', 'Inorganic Green Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030204', 'local', '', 'Main Group Metal Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030205', 'local', '', 'Non-metal Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030206', 'local', '', 'Solid State Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030207', 'local', '', 'Transition Metal Chemistry', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030299', 'local', '', 'Inorganic Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030200', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>INORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030300', 'local', '', 'MACROMOLECULAR AND MATERIALS CHEMISTRY', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030301', 'local', '', 'Chemical Characterisation of Materials', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030302', 'local', '', 'Nanochemistry and Supramolecular Chemistry', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030303', 'local', '', 'Optical Properties of Materials', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030304', 'local', '', 'Physical Chemistry of Materials', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030305', 'local', '', 'Polymerisation Mechanisms', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030306', 'local', '', 'Synthesis of Materials', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030307', 'local', '', 'Theory and Design of Materials', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030399', 'local', '', 'Macromolecular and Materials Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030300', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MACROMOLECULAR AND MATERIALS CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030400', 'local', '', 'MEDICINAL AND BIOMOLECULAR CHEMISTRY', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030401', 'local', '', 'Biologically Active Molecules', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030402', 'local', '', 'Biomolecular Modelling and Design', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030403', 'local', '', 'Characterisation of Biological Macromolecules', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030404', 'local', '', 'Cheminformatics and Quantitative Structure-Activity Relationships', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030405', 'local', '', 'Molecular Medicine', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030406', 'local', '', 'Proteins and Peptides', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030499', 'local', '', 'Medicinal and Biomolecular Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030400', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>MEDICINAL AND BIOMOLECULAR CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030500', 'local', '', 'ORGANIC CHEMISTRY', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030501', 'local', '', 'Free Radical Chemistry', '', '', 'ANZSRC-FOR', '030500', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030502', 'local', '', 'Natural Products Chemistry', '', '', 'ANZSRC-FOR', '030500', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030503', 'local', '', 'Organic Chemical Synthesis', '', '', 'ANZSRC-FOR', '030500', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030504', 'local', '', 'Organic Green Chemistry', '', '', 'ANZSRC-FOR', '030500', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030505', 'local', '', 'Physical Organic Chemistry', '', '', 'ANZSRC-FOR', '030500', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030599', 'local', '', 'Organic Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030500', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>ORGANIC CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030600', 'local', '', 'PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030601', 'local', '', 'Catalysis and Mechanisms of Reactions', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030602', 'local', '', 'Chemical Thermodynamics and Energetics', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030603', 'local', '', 'Colloid and Surface Chemistry', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030604', 'local', '', 'Electrochemistry', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030605', 'local', '', 'Solution Chemistry', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030606', 'local', '', 'Structural Chemistry and Spectroscopy', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030607', 'local', '', 'Transport Properties and Non-Equilibrium Processes', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030699', 'local', '', 'Physical Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030600', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>PHYSICAL CHEMISTRY (INCL. STRUCTURAL)', '');
INSERT INTO tbl_terms VALUES ('030700', 'local', '', 'THEORETICAL AND COMPUTATIONAL CHEMISTRY', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('030701', 'local', '', 'Quantum Chemistry', '', '', 'ANZSRC-FOR', '030700', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>THEORETICAL AND COMPUTATIONAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030702', 'local', '', 'Radiation and Matter', '', '', 'ANZSRC-FOR', '030700', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>THEORETICAL AND COMPUTATIONAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030703', 'local', '', 'Reaction Kinetics and Dynamics', '', '', 'ANZSRC-FOR', '030700', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>THEORETICAL AND COMPUTATIONAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030704', 'local', '', 'Statistical Mechanics in Chemistry', '', '', 'ANZSRC-FOR', '030700', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>THEORETICAL AND COMPUTATIONAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('030799', 'local', '', 'Theoretical and Computational Chemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '030700', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>THEORETICAL AND COMPUTATIONAL CHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('039900', 'local', '', 'OTHER CHEMICAL SCIENCES', '', '', 'ANZSRC-FOR', '030000', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('039901', 'local', '', 'Environmental Chemistry (incl. Atmospheric Chemistry)', '', '', 'ANZSRC-FOR', '039900', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>OTHER CHEMICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('039902', 'local', '', 'Forensic Chemistry', '', '', 'ANZSRC-FOR', '039900', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>OTHER CHEMICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('039903', 'local', '', 'Industrial Chemistry', '', '', 'ANZSRC-FOR', '039900', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>OTHER CHEMICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('039904', 'local', '', 'Organometallic Chemistry', '', '', 'ANZSRC-FOR', '039900', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>OTHER CHEMICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('039999', 'local', '', 'Chemical Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '039900', 'pt', '', 'ANZSRC>>CHEMICAL SCIENCE>>OTHER CHEMICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040000', 'local', '', 'EARTH SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('040100', 'local', '', 'ATMOSPHERIC SCIENCES', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040101', 'local', '', 'Atmospheric Aerosols', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040102', 'local', '', 'Atmospheric Dynamics', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040103', 'local', '', 'Atmospheric Radiation', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040104', 'local', '', 'Climate Change Processes', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040105', 'local', '', 'Climatology (excl. Climate Change Processes)', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040106', 'local', '', 'Cloud Physics', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040107', 'local', '', 'Meteorology', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040108', 'local', '', 'Tropospheric and Stratospheric Physics', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040199', 'local', '', 'Atmospheric Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '040100', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>ATMOSPHERIC SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040200', 'local', '', 'GEOCHEMISTRY', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040201', 'local', '', 'Exploration Geochemistry', '', '', 'ANZSRC-FOR', '040200', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOCHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('040202', 'local', '', 'Inorganic Geochemistry', '', '', 'ANZSRC-FOR', '040200', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOCHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('040203', 'local', '', 'Isotope Geochemistry', '', '', 'ANZSRC-FOR', '040200', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOCHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('040204', 'local', '', 'Organic Geochemistry', '', '', 'ANZSRC-FOR', '040200', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOCHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('040299', 'local', '', 'Geochemistry not elsewhere classified', '', '', 'ANZSRC-FOR', '040200', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOCHEMISTRY', '');
INSERT INTO tbl_terms VALUES ('040300', 'local', '', 'GEOLOGY', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040301', 'local', '', 'Basin Analysis', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040302', 'local', '', 'Extraterrestrial Geology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040303', 'local', '', 'Geochronology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040304', 'local', '', 'Igneous and Metamorphic Petrology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040305', 'local', '', 'Marine Geoscience', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040306', 'local', '', 'Mineralogy and Crystallography', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040307', 'local', '', 'Ore Deposit Petrology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040308', 'local', '', 'Palaeontology (incl. Palynology)', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040309', 'local', '', 'Petroleum and Coal Geology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040310', 'local', '', 'Sedimentology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040311', 'local', '', 'Stratigraphy (incl. Biostratigraphy and Sequence Stratigraphy)', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040312', 'local', '', 'Structural Geology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040313', 'local', '', 'Tectonics', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040314', 'local', '', 'Volcanology', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040399', 'local', '', 'Geology not elsewhere classified', '', '', 'ANZSRC-FOR', '040300', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOLOGY', '');
INSERT INTO tbl_terms VALUES ('040400', 'local', '', 'GEOPHYSICS', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040401', 'local', '', 'Electrical and Electromagnetic Methods in Geophysics', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040402', 'local', '', 'Geodynamics', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040403', 'local', '', 'Geophysical Fluid Dynamics', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040404', 'local', '', 'Geothermics and Radiometrics', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040405', 'local', '', 'Gravimetrics', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040406', 'local', '', 'Magnetism and Palaeomagnetism', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040407', 'local', '', 'Seismology and Seismic Exploration', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040499', 'local', '', 'Geophysics not elsewhere classified', '', '', 'ANZSRC-FOR', '040400', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>GEOPHYSICS', '');
INSERT INTO tbl_terms VALUES ('040500', 'local', '', 'OCEANOGRAPHY', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040501', 'local', '', 'Biological Oceanography', '', '', 'ANZSRC-FOR', '040500', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>OCEANOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('040502', 'local', '', 'Chemical Oceanography', '', '', 'ANZSRC-FOR', '040500', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>OCEANOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('040503', 'local', '', 'Physical Oceanography', '', '', 'ANZSRC-FOR', '040500', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>OCEANOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('040599', 'local', '', 'Oceanography not elsewhere classified', '', '', 'ANZSRC-FOR', '040500', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>OCEANOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('040600', 'local', '', 'PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('040601', 'local', '', 'Geomorphology and Regolith and Landscape Evolution', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040602', 'local', '', 'Glaciology', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040603', 'local', '', 'Hydrogeology', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040604', 'local', '', 'Natural Hazards', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040605', 'local', '', 'Palaeoclimatology', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040606', 'local', '', 'Quaternary Environments', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040607', 'local', '', 'Surface Processes', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040608', 'local', '', 'Surfacewater Hydrology', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('040699', 'local', '', 'Physical Geography and Environmental Geoscience not elsewhere classified', '', '', 'ANZSRC-FOR', '040600', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>PHYSICAL GEOGRAPHY AND ENVIRONMENTAL GEOSCIENCE', '');
INSERT INTO tbl_terms VALUES ('049900', 'local', '', 'OTHER EARTH SCIENCES', '', '', 'ANZSRC-FOR', '040000', 'pt', '', 'ANZSRC>>EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('049999', 'local', '', 'Earth Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '049900', 'pt', '', 'ANZSRC>>EARTH SCIENCES>>OTHER EARTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050000', 'local', '', 'ENVIRONMENTAL SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('050100', 'local', '', 'ECOLOGICAL APPLICATIONS', '', '', 'ANZSRC-FOR', '050000', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050101', 'local', '', 'Ecological Impacts of Climate Change', '', '', 'ANZSRC-FOR', '050100', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ECOLOGICAL APPLICATIONS', '');
INSERT INTO tbl_terms VALUES ('050102', 'local', '', 'Ecosystem Function', '', '', 'ANZSRC-FOR', '050100', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ECOLOGICAL APPLICATIONS', '');
INSERT INTO tbl_terms VALUES ('050103', 'local', '', 'Invasive Species Ecology', '', '', 'ANZSRC-FOR', '050100', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ECOLOGICAL APPLICATIONS', '');
INSERT INTO tbl_terms VALUES ('050104', 'local', '', 'Landscape Ecology', '', '', 'ANZSRC-FOR', '050100', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ECOLOGICAL APPLICATIONS', '');
INSERT INTO tbl_terms VALUES ('050199', 'local', '', 'Ecological Applications not elsewhere classified', '', '', 'ANZSRC-FOR', '050100', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ECOLOGICAL APPLICATIONS', '');
INSERT INTO tbl_terms VALUES ('050200', 'local', '', 'ENVIRONMENTAL SCIENCE AND MANAGEMENT', '', '', 'ANZSRC-FOR', '050000', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050201', 'local', '', 'Aboriginal and Torres Strait Islander Environmental Knowledge', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050202', 'local', '', 'Conservation and Biodiversity', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050203', 'local', '', 'Environmental Education and Extension', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050204', 'local', '', 'Environmental Impact Assessment', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050205', 'local', '', 'Environmental Management', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050206', 'local', '', 'Environmental Monitoring', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050207', 'local', '', 'Environmental Rehabilitation (excl. Bioremediation)', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050208', 'local', '', 'Maori Environmental Knowledge', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050209', 'local', '', 'Natural Resource Management', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050210', 'local', '', 'Pacific Peoples Environmental Knowledge', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050211', 'local', '', 'Wildlife and Habitat Management', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050299', 'local', '', 'Environmental Science and Management not elsewhere classified', '', '', 'ANZSRC-FOR', '050200', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>ENVIRONMENTAL SCIENCE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('050300', 'local', '', 'SOIL SCIENCES', '', '', 'ANZSRC-FOR', '050000', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050301', 'local', '', 'Carbon Sequestration Science', '', '', 'ANZSRC-FOR', '050300', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>SOIL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050302', 'local', '', 'Land Capability and Soil Degradation', '', '', 'ANZSRC-FOR', '050300', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>SOIL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050303', 'local', '', 'Soil Biology', '', '', 'ANZSRC-FOR', '050300', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>SOIL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050304', 'local', '', 'Soil Chemistry (excl. Carbon Sequestration Science)', '', '', 'ANZSRC-FOR', '050300', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>SOIL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050305', 'local', '', 'Soil Physics', '', '', 'ANZSRC-FOR', '050300', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>SOIL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('050399', 'local', '', 'Soil Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '050300', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>SOIL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('059900', 'local', '', 'OTHER ENVIRONMENTAL SCIENCES', '', '', 'ANZSRC-FOR', '050000', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('059999', 'local', '', 'Environmental Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '059900', 'pt', '', 'ANZSRC>>ENVIRONMENTAL SCIENCES>>OTHER ENVIRONMENTAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060000', 'local', '', 'BIOLOGICAL SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('060100', 'local', '', 'BIOCHEMISTRY AND CELL BIOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060101', 'local', '', 'Analytical Biochemistry', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060102', 'local', '', 'Bioinformatics', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060103', 'local', '', 'Cell Development, Proliferation and Death', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060104', 'local', '', 'Cell Metabolism', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060105', 'local', '', 'Cell Neurochemistry', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060106', 'local', '', 'Cellular Interactions (incl. Adhesion, Matrix, Cell Wall)', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060107', 'local', '', 'Enzymes', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060108', 'local', '', 'Protein Trafficking', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060109', 'local', '', 'Proteomics and Intermolecular Interactions (excl. Medical Proteomics)', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060110', 'local', '', 'Receptors and Membrane Biology', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060111', 'local', '', 'Signal Transduction', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060112', 'local', '', 'Structural Biology (incl. Macromolecular Modelling)', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060113', 'local', '', 'Synthetic Biology', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060114', 'local', '', 'Systems Biology', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060199', 'local', '', 'Biochemistry and Cell Biology not elsewhere classified', '', '', 'ANZSRC-FOR', '060100', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>BIOCHEMISTRY AND CELL BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060200', 'local', '', 'ECOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060201', 'local', '', 'Behavioural Ecology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060202', 'local', '', 'Community Ecology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060203', 'local', '', 'Ecological Physiology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060204', 'local', '', 'Freshwater Ecology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060205', 'local', '', 'Marine and Estuarine Ecology (incl. Marine Ichthyology)', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060206', 'local', '', 'Palaeoecology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060207', 'local', '', 'Population Ecology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060208', 'local', '', 'Terrestrial Ecology', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060299', 'local', '', 'Ecology not elsewhere classified', '', '', 'ANZSRC-FOR', '060200', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ECOLOGY', '');
INSERT INTO tbl_terms VALUES ('060300', 'local', '', 'EVOLUTIONARY BIOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060301', 'local', '', 'Animal Systematics and Taxonomy', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060302', 'local', '', 'Biogeography and Phylogeography', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060303', 'local', '', 'Biological Adaptation', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060304', 'local', '', 'Ethology and Sociobiology', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060305', 'local', '', 'Evolution of Developmental Systems', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060306', 'local', '', 'Evolutionary Impacts of Climate Change', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060307', 'local', '', 'Host-Parasite Interactions', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060308', 'local', '', 'Life Histories', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060309', 'local', '', 'Phylogeny and Comparative Analysis', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060310', 'local', '', 'Plant Systematics and Taxonomy', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060311', 'local', '', 'Speciation and Extinction', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060399', 'local', '', 'Evolutionary Biology not elsewhere classified', '', '', 'ANZSRC-FOR', '060300', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>EVOLUTIONARY BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060400', 'local', '', 'GENETICS', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060401', 'local', '', 'Anthropological Genetics', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060402', 'local', '', 'Cell and Nuclear Division', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060403', 'local', '', 'Developmental Genetics (incl. Sex Determination)', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060404', 'local', '', 'Epigenetics (incl. Genome Methylation and Epigenomics)', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060405', 'local', '', 'Gene Expression (incl. Microarray and other genome-wide approaches)', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060406', 'local', '', 'Genetic Immunology', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060407', 'local', '', 'Genome Structure and Regulation', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060408', 'local', '', 'Genomics', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060409', 'local', '', 'Molecular Evolution', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060410', 'local', '', 'Neurogenetics', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060411', 'local', '', 'Population, Ecological and Evolutionary Genetics', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060412', 'local', '', 'Quantitative Genetics (incl. Disease and Trait Mapping Genetics', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060499', 'local', '', 'Genetics not elsewhere classified', '', '', 'ANZSRC-FOR', '060400', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>GENETICS', '');
INSERT INTO tbl_terms VALUES ('060500', 'local', '', 'MICROBIOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060501', 'local', '', 'Bacteriology', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060502', 'local', '', 'Infectious Agents', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060503', 'local', '', 'Microbial Genetics', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060504', 'local', '', 'Microbial Ecology', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060505', 'local', '', 'Mycology', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060506', 'local', '', 'Virology', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060599', 'local', '', 'Microbiology not elsewhere classified', '', '', 'ANZSRC-FOR', '060500', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060600', 'local', '', 'PHYSIOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060601', 'local', '', 'Animal Physiology - Biophysics', '', '', 'ANZSRC-FOR', '060600', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060602', 'local', '', 'Animal Physiology - Cell', '', '', 'ANZSRC-FOR', '060600', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060603', 'local', '', 'Animal Physiology - Systems', '', '', 'ANZSRC-FOR', '060600', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060604', 'local', '', 'Comparative Physiology', '', '', 'ANZSRC-FOR', '060600', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060699', 'local', '', 'Physiology not elsewhere classified', '', '', 'ANZSRC-FOR', '060600', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060700', 'local', '', 'PLANT BIOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060701', 'local', '', 'Phycology (incl. Marine Grasses)', '', '', 'ANZSRC-FOR', '060700', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PLANT BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060702', 'local', '', 'Plant Cell and Molecular Biology', '', '', 'ANZSRC-FOR', '060700', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PLANT BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060703', 'local', '', 'Plant Developmental and Reproductive Biology', '', '', 'ANZSRC-FOR', '060700', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PLANT BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060704', 'local', '', 'Plant Pathology', '', '', 'ANZSRC-FOR', '060700', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PLANT BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060705', 'local', '', 'Plant Physiology', '', '', 'ANZSRC-FOR', '060700', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PLANT BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060799', 'local', '', 'Plant Biology not elsewhere classified', '', '', 'ANZSRC-FOR', '060700', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>PLANT BIOLOGY', '');
INSERT INTO tbl_terms VALUES ('060800', 'local', '', 'ZOOLOGY', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('060801', 'local', '', 'Animal Behaviour', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060802', 'local', '', 'Animal Cell and Molecular Biology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060803', 'local', '', 'Animal Developmental and Reproductive Biology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060804', 'local', '', 'Animal Immunology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060805', 'local', '', 'Animal Neurobiology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060806', 'local', '', 'Animal Physiological Ecology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060807', 'local', '', 'Animal Structure and Function', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060808', 'local', '', 'Invertebrate Biology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060809', 'local', '', 'Vertebrate Biology', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('060899', 'local', '', 'Zoology not elsewhere classified', '', '', 'ANZSRC-FOR', '060800', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>ZOOLOGY', '');
INSERT INTO tbl_terms VALUES ('069900', 'local', '', 'OTHER BIOLOGICAL SCIENCES', '', '', 'ANZSRC-FOR', '060000', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('069901', 'local', '', 'Forensic Biology', '', '', 'ANZSRC-FOR', '069900', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>OTHER BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('069902', 'local', '', 'Global Change Biology', '', '', 'ANZSRC-FOR', '069900', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>OTHER BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('069999', 'local', '', 'Biological Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '069900', 'pt', '', 'ANZSRC>>BIOLOGICAL SCIENCES>>OTHER BIOLOGICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070000', 'local', '', 'AGRICULTURAL AND VETERINARY SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('070100', 'local', '', 'AGRICULTURE, LAND AND FARM MANAGEMENT', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070101', 'local', '', 'Agricultural Land Management', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070102', 'local', '', 'Agricultural Land Planning', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070103', 'local', '', 'Agricultural Production Systems Simulation', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070104', 'local', '', 'Agricultural Spatial Analysis and Modelling', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070105', 'local', '', 'Agricultural Systems Analysis and Modelling', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070106', 'local', '', 'Farm Management, Rural Management and Agribusiness', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070107', 'local', '', 'Farming Systems Research', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070108', 'local', '', 'Sustainable Agricultural Development', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070199', 'local', '', 'Agriculture, Land and Farm Management not elsewhere classified', '', '', 'ANZSRC-FOR', '070100', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>AGRICULTURE, LAND AND FARM MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('070200', 'local', '', 'ANIMAL PRODUCTION', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070201', 'local', '', 'Animal Breeding', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070202', 'local', '', 'Animal Growth and Development', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070203', 'local', '', 'Animal Management', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070204', 'local', '', 'Animal Nutrition', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070205', 'local', '', 'Animal Protection (Pests and Pathogens)', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070206', 'local', '', 'Animal Reproduction', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070207', 'local', '', 'Humane Animal Treatment', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070299', 'local', '', 'Animal Production not elsewhere classified', '', '', 'ANZSRC-FOR', '070200', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070300', 'local', '', 'CROP AND PASTURE PRODUCTION', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070301', 'local', '', 'Agro-ecosystem Function and Prediction', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070302', 'local', '', 'Agronomy', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070303', 'local', '', 'Crop and Pasture Biochemistry and Physiology', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070304', 'local', '', 'Crop and Pasture Biomass and Bioproducts', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070305', 'local', '', 'Crop and Pasture Improvement (Selection and Breeding)', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070306', 'local', '', 'Crop and Pasture Nutrition', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070307', 'local', '', 'Crop and Pasture Post Harvest Technologies (incl. Transportation and Storage)', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070308', 'local', '', 'Crop and Pasture Protection (Pests, Diseases and Weeds)', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070399', 'local', '', 'Crop and Pasture Production not elsewhere classified', '', '', 'ANZSRC-FOR', '070300', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>CROP AND PASTURE PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070400', 'local', '', 'FISHERIES SCIENCES', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070401', 'local', '', 'Aquaculture', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070402', 'local', '', 'Aquatic Ecosystem Studies and Stock Assessment', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070403', 'local', '', 'Fisheries Management', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070404', 'local', '', 'Fish Pests and Diseases', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070405', 'local', '', 'Fish Physiology and Genetics', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070406', 'local', '', 'Post-Harvest Fisheries Technologies (incl. Transportation)', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070499', 'local', '', 'Fisheries Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '070400', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FISHERIES SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070500', 'local', '', 'FORESTRY SCIENCES', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070501', 'local', '', 'Agroforestry', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070502', 'local', '', 'Forestry Biomass and Bioproducts', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070503', 'local', '', 'Forestry Fire Management', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070504', 'local', '', 'Forestry Management and Environment', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070505', 'local', '', 'Forestry Pests, Health and Diseases', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070506', 'local', '', 'Forestry Product Quality Assessment', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070507', 'local', '', 'Tree Improvement (Selection and Breeding)', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070508', 'local', '', 'Tree Nutrition and Physiology', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070509', 'local', '', 'Wood Fibre Processing', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070510', 'local', '', 'Wood Processing', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070599', 'local', '', 'Forestry Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '070500', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>FORESTRY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070600', 'local', '', 'HORTICULTURAL PRODUCTION', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070601', 'local', '', 'Horticultural Crop Growth and Development', '', '', 'ANZSRC-FOR', '070600', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>HORTICULTURAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070602', 'local', '', 'Horticultural Crop Improvement (Selection and Breeding)', '', '', 'ANZSRC-FOR', '070600', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>HORTICULTURAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070603', 'local', '', 'Horticultural Crop Protection (Pests, Diseases and Weeds)', '', '', 'ANZSRC-FOR', '070600', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>HORTICULTURAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070604', 'local', '', 'Oenology and Viticulture', '', '', 'ANZSRC-FOR', '070600', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>HORTICULTURAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070605', 'local', '', 'Post Harvest Horticultural Technologies (incl.  Transportation and Storage)', '', '', 'ANZSRC-FOR', '070600', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>HORTICULTURAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070699', 'local', '', 'Horticultural Production not elsewhere classified', '', '', 'ANZSRC-FOR', '070600', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>HORTICULTURAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('070700', 'local', '', 'VETERINARY SCIENCES', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070701', 'local', '', 'Veterinary Anaesthesiology and Intensive Care', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070702', 'local', '', 'Veterinary Anatomy and Physiology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070703', 'local', '', 'Veterinary Diagnosis and Diagnostics', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070704', 'local', '', 'Veterinary Epidemiology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070705', 'local', '', 'Veterinary Immunology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070706', 'local', '', 'Veterinary Medicine', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070707', 'local', '', 'Veterinary Microbiology (excl. Virology)', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070708', 'local', '', 'Veterinary Parasitology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070709', 'local', '', 'Veterinary Pathology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070710', 'local', '', 'Veterinary Pharmacology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070711', 'local', '', 'Veterinary Surgery', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070712', 'local', '', 'Veterinary Virology', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('070799', 'local', '', 'Veterinary Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '070700', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('079900', 'local', '', 'OTHER AGRICULTURAL AND VETERINARY SCIENCES', '', '', 'ANZSRC-FOR', '070000', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('079901', 'local', '', 'Agricultural Hydrology (Drainage, Flooding, Irrigation, Quality, etc)', '', '', 'ANZSRC-FOR', '079900', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>OTHER AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('079902', 'local', '', 'Fertilisers and Agrochemicals (Application etc.)', '', '', 'ANZSRC-FOR', '079900', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>OTHER AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('079999', 'local', '', 'Agricultural and Veterinary Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '079900', 'pt', '', 'ANZSRC>>AGRICULTURAL AND VETERINARY SCIENCES>>OTHER AGRICULTURAL AND VETERINARY SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080000', 'local', '', 'INFORMATION AND COMPUTING SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('080100', 'local', '', 'ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080101', 'local', '', 'Adaptive Agents and Intelligent Robotics', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080102', 'local', '', 'Artificial Life', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080103', 'local', '', 'Computer Graphics', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080104', 'local', '', 'Computer Vision', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080105', 'local', '', 'Expert Systems', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080106', 'local', '', 'Image Processing', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080107', 'local', '', 'Natural Language Processing', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080108', 'local', '', 'Neural, Evolutionary and Fuzzy Computation', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080109', 'local', '', 'Pattern Recognition and Data Mining', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080110', 'local', '', 'Simulation and Modelling', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080111', 'local', '', 'Virtual Reality and Related Simulation', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080199', 'local', '', 'Artificial Intelligence and Image Processing not elsewhere classified', '', '', 'ANZSRC-FOR', '080100', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>ARTIFICIAL INTELLIGENCE AND IMAGE PROCESSING', '');
INSERT INTO tbl_terms VALUES ('080200', 'local', '', 'COMPUTATION THEORY AND MATHEMATICS', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080201', 'local', '', 'Analysis of Algorithms and Complexity', '', '', 'ANZSRC-FOR', '080200', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTATION THEORY AND MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('080202', 'local', '', 'Applied Discrete Mathematics', '', '', 'ANZSRC-FOR', '080200', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTATION THEORY AND MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('080203', 'local', '', 'Computational Logic and Formal Languages', '', '', 'ANZSRC-FOR', '080200', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTATION THEORY AND MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('080204', 'local', '', 'Mathematical Software', '', '', 'ANZSRC-FOR', '080200', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTATION THEORY AND MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('080205', 'local', '', 'Numerical Computation', '', '', 'ANZSRC-FOR', '080200', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTATION THEORY AND MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('080299', 'local', '', 'Computation Theory and Mathematics not elsewhere classified', '', '', 'ANZSRC-FOR', '080200', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTATION THEORY AND MATHEMATICS', '');
INSERT INTO tbl_terms VALUES ('080300', 'local', '', 'COMPUTER SOFTWARE', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080301', 'local', '', 'Bioinformatics Software', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080302', 'local', '', 'Computer System Architecture', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080303', 'local', '', 'Computer System Security', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080304', 'local', '', 'Concurrent Programming', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080305', 'local', '', 'Multimedia Programming', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080306', 'local', '', 'Open Software', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080307', 'local', '', 'Operating Systems', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080308', 'local', '', 'Programming Languages', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080309', 'local', '', 'Software Engineering', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080399', 'local', '', 'Computer Software not elsewhere classified', '', '', 'ANZSRC-FOR', '080300', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>COMPUTER SOFTWARE', '');
INSERT INTO tbl_terms VALUES ('080400', 'local', '', 'DATA FORMAT', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080401', 'local', '', 'Coding and Information Theory', '', '', 'ANZSRC-FOR', '080400', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DATA FORMAT', '');
INSERT INTO tbl_terms VALUES ('080402', 'local', '', 'Data Encryption', '', '', 'ANZSRC-FOR', '080400', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DATA FORMAT', '');
INSERT INTO tbl_terms VALUES ('080403', 'local', '', 'Data Structures', '', '', 'ANZSRC-FOR', '080400', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DATA FORMAT', '');
INSERT INTO tbl_terms VALUES ('080404', 'local', '', 'Markup Languages', '', '', 'ANZSRC-FOR', '080400', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DATA FORMAT', '');
INSERT INTO tbl_terms VALUES ('080499', 'local', '', 'Data Format not elsewhere classified', '', '', 'ANZSRC-FOR', '080400', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DATA FORMAT', '');
INSERT INTO tbl_terms VALUES ('080500', 'local', '', 'DISTRIBUTED COMPUTING', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080501', 'local', '', 'Distributed and Grid Systems', '', '', 'ANZSRC-FOR', '080500', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DISTRIBUTED COMPUTING', '');
INSERT INTO tbl_terms VALUES ('080502', 'local', '', 'Mobile Technologies', '', '', 'ANZSRC-FOR', '080500', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DISTRIBUTED COMPUTING', '');
INSERT INTO tbl_terms VALUES ('080503', 'local', '', 'Networking and Communications', '', '', 'ANZSRC-FOR', '080500', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DISTRIBUTED COMPUTING', '');
INSERT INTO tbl_terms VALUES ('080504', 'local', '', 'Ubiquitous Computing', '', '', 'ANZSRC-FOR', '080500', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DISTRIBUTED COMPUTING', '');
INSERT INTO tbl_terms VALUES ('080505', 'local', '', 'Web Technologies (excl. Web Search)', '', '', 'ANZSRC-FOR', '080500', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DISTRIBUTED COMPUTING', '');
INSERT INTO tbl_terms VALUES ('080599', 'local', '', 'Distributed Computing not elsewhere classified', '', '', 'ANZSRC-FOR', '080500', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>DISTRIBUTED COMPUTING', '');
INSERT INTO tbl_terms VALUES ('080600', 'local', '', 'INFORMATION SYSTEMS', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080601', 'local', '', 'Aboriginal and Torres Strait Islander Information and  Knowledge Systems', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080602', 'local', '', 'Computer-Human Interaction', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080603', 'local', '', 'Conceptual Modelling', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080604', 'local', '', 'Database Management', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080605', 'local', '', 'Decision Support and Group Support Systems', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080606', 'local', '', 'Global Information Systems', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080607', 'local', '', 'Information Engineering and Theory', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080608', 'local', '', 'Information Systems Development Methodologies', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080609', 'local', '', 'Information Systems Management', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080610', 'local', '', 'Information Systems Organisation', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080611', 'local', '', 'Information Systems Theory', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080612', 'local', '', 'Interorganisational Information Systems and Web Services', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080613', 'local', '', 'Maori Information and Knowledge Systems', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080614', 'local', '', 'Pacific Peoples Information and Knowledge Systems', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080699', 'local', '', 'Information Systems not elsewhere classified', '', '', 'ANZSRC-FOR', '080600', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>INFORMATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('080700', 'local', '', 'LIBRARY AND INFORMATION STUDIES', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('080701', 'local', '', 'Aboriginal and Torres Strait Islander Knowledge Management', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080702', 'local', '', 'Health Informatics', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080703', 'local', '', 'Human Information Behaviour', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080704', 'local', '', 'Information Retrieval and Web Search', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080705', 'local', '', 'Informetrics', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080706', 'local', '', 'Librarianship', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('upc', 'local', '', 'upc', 'Universal Product Code', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('080707', 'local', '', 'Organisation of Information and Knowledge Resources', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080708', 'local', '', 'Records and Information Management (excl. Business  Records and Information Management)', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080709', 'local', '', 'Social and Community Informatics', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('080799', 'local', '', 'Library and Information Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '080700', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>LIBRARY AND INFORMATION STUDIES', '');
INSERT INTO tbl_terms VALUES ('089900', 'local', '', 'OTHER INFORMATION AND COMPUTING SCIENCES', '', '', 'ANZSRC-FOR', '080000', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('089999', 'local', '', 'Information and Computing Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '089900', 'pt', '', 'ANZSRC>>INFORMATION AND COMPUTING SCIENCES>>OTHER INFORMATION AND COMPUTING SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090000', 'local', '', 'ENGINEERING', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('090100', 'local', '', 'AEROSPACE ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090101', 'local', '', 'Aerodynamics (excl. Hypersonic Aerodynamics)', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090102', 'local', '', 'Aerospace Materials', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090103', 'local', '', 'Aerospace Structures', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090104', 'local', '', 'Aircraft Performance and Flight Control Systems', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090105', 'local', '', 'Avionics', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090106', 'local', '', 'Flight Dynamics', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090107', 'local', '', 'Hypersonic Propulsion and Hypersonic Aerodynamics', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090108', 'local', '', 'Satellite, Space Vehicle and Missile Design and Testing', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090199', 'local', '', 'Aerospace Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090100', 'pt', '', 'ANZSRC>>ENGINEERING>>AEROSPACE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090200', 'local', '', 'AUTOMOTIVE ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090201', 'local', '', 'Automotive Combustion and Fuel Engineering (incl.  Alternative/Renewable Fuels)', '', '', 'ANZSRC-FOR', '090200', 'pt', '', 'ANZSRC>>ENGINEERING>>AUTOMOTIVE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090202', 'local', '', 'Automotive Engineering Materials', '', '', 'ANZSRC-FOR', '090200', 'pt', '', 'ANZSRC>>ENGINEERING>>AUTOMOTIVE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090203', 'local', '', 'Automotive Mechatronics', '', '', 'ANZSRC-FOR', '090200', 'pt', '', 'ANZSRC>>ENGINEERING>>AUTOMOTIVE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090204', 'local', '', 'Automotive Safety Engineering', '', '', 'ANZSRC-FOR', '090200', 'pt', '', 'ANZSRC>>ENGINEERING>>AUTOMOTIVE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090205', 'local', '', 'Hybrid Vehicles and Powertrains', '', '', 'ANZSRC-FOR', '090200', 'pt', '', 'ANZSRC>>ENGINEERING>>AUTOMOTIVE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090299', 'local', '', 'Automotive Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090200', 'pt', '', 'ANZSRC>>ENGINEERING>>AUTOMOTIVE ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090300', 'local', '', 'BIOMEDICAL ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090301', 'local', '', 'Biomaterials', '', '', 'ANZSRC-FOR', '090300', 'pt', '', 'ANZSRC>>ENGINEERING>>BIOMEDICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090302', 'local', '', 'Biomechanical Engineering', '', '', 'ANZSRC-FOR', '090300', 'pt', '', 'ANZSRC>>ENGINEERING>>BIOMEDICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090303', 'local', '', 'Biomedical Instrumentation', '', '', 'ANZSRC-FOR', '090300', 'pt', '', 'ANZSRC>>ENGINEERING>>BIOMEDICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090304', 'local', '', 'Medical Devices', '', '', 'ANZSRC-FOR', '090300', 'pt', '', 'ANZSRC>>ENGINEERING>>BIOMEDICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090305', 'local', '', 'Rehabilitation Engineering', '', '', 'ANZSRC-FOR', '090300', 'pt', '', 'ANZSRC>>ENGINEERING>>BIOMEDICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090399', 'local', '', 'Biomedical Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090300', 'pt', '', 'ANZSRC>>ENGINEERING>>BIOMEDICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090400', 'local', '', 'CHEMICAL ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090401', 'local', '', 'Carbon Capture Engineering (excl. Sequestration)', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090402', 'local', '', 'Catalytic Process Engineering', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090403', 'local', '', 'Chemical Engineering Design', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090404', 'local', '', 'Membrane and Separation Technologies', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090405', 'local', '', 'Non-automotive Combustion and Fuel Engineering (incl.  Alternative/Renewable Fuels)', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090406', 'local', '', 'Powder and Particle Technology', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090407', 'local', '', 'Process Control and Simulation', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090408', 'local', '', 'Rheology', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090409', 'local', '', 'Wastewater Treatment Processes', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090410', 'local', '', 'Water Treatment Processes', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090499', 'local', '', 'Chemical Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090400', 'pt', '', 'ANZSRC>>ENGINEERING>>CHEMICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090500', 'local', '', 'CIVIL ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090501', 'local', '', 'Civil Geotechnical Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090502', 'local', '', 'Construction Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090503', 'local', '', 'Construction Materials', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090504', 'local', '', 'Earthquake Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090505', 'local', '', 'Infrastructure Engineering and Asset Management', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090506', 'local', '', 'Structural Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090507', 'local', '', 'Transport Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090508', 'local', '', 'Water Quality Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090509', 'local', '', 'Water Resources Engineering', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090599', 'local', '', 'Civil Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090500', 'pt', '', 'ANZSRC>>ENGINEERING>>CIVIL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090600', 'local', '', 'ELECTRICAL AND ELECTRONIC ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090601', 'local', '', 'Circuits and Systems', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090602', 'local', '', 'Control Systems, Robotics and Automation', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090603', 'local', '', 'Industrial Electronics', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090604', 'local', '', 'Microelectronics and Integrated Circuits', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090605', 'local', '', 'Photodetectors, Optical Sensors and Solar Cells', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090606', 'local', '', 'Photonics and Electro-Optical Engineering (excl. Communications)', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090607', 'local', '', 'Power and Energy Systems Engineering (excl. Renewable  Power)', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090608', 'local', '', 'Renewable Power and Energy Systems Engineering (excl.  Solar Cells)', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090609', 'local', '', 'Signal Processing', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090699', 'local', '', 'Electrical and Electronic Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090600', 'pt', '', 'ANZSRC>>ENGINEERING>>ELECTRICAL AND ELECTRONIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090700', 'local', '', 'ENVIRONMENTAL ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090701', 'local', '', 'Environmental Engineering Design', '', '', 'ANZSRC-FOR', '090700', 'pt', '', 'ANZSRC>>ENGINEERING>>ENVIRONMENTAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090702', 'local', '', 'Environmental Engineering Modelling', '', '', 'ANZSRC-FOR', '090700', 'pt', '', 'ANZSRC>>ENGINEERING>>ENVIRONMENTAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090703', 'local', '', 'Environmental Technologies', '', '', 'ANZSRC-FOR', '090700', 'pt', '', 'ANZSRC>>ENGINEERING>>ENVIRONMENTAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090799', 'local', '', 'Environmental Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090700', 'pt', '', 'ANZSRC>>ENGINEERING>>ENVIRONMENTAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090800', 'local', '', 'FOOD SCIENCES', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090801', 'local', '', 'Food Chemistry and Molecular Gastronomy (excl. Wine)', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090802', 'local', '', 'Food Engineering', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090803', 'local', '', 'Food Nutritional Balance', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090804', 'local', '', 'Food Packaging, Preservation and Safety', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090805', 'local', '', 'Food Processing', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090806', 'local', '', 'Wine Chemistry and Wine Sensory Science', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090899', 'local', '', 'Food Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '090800', 'pt', '', 'ANZSRC>>ENGINEERING>>FOOD SCIENCES', '');
INSERT INTO tbl_terms VALUES ('090900', 'local', '', 'GEOMATIC ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090901', 'local', '', 'Cartography', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090902', 'local', '', 'Geodesy', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090903', 'local', '', 'Geospatial Information Systems', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090904', 'local', '', 'Navigation and Position Fixing', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090905', 'local', '', 'Photogrammetry and Remote Sensing', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090906', 'local', '', 'Surveying (incl. Hydrographic Surveying)', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('090999', 'local', '', 'Geomatic Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '090900', 'pt', '', 'ANZSRC>>ENGINEERING>>GEOMATIC ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091000', 'local', '', 'MANUFACTURING ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091001', 'local', '', 'CAD/CAM Systems', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091002', 'local', '', 'Flexible Manufacturing Systems', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091003', 'local', '', 'Machine Tools', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091004', 'local', '', 'Machining', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091005', 'local', '', 'Manufacturing Management', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091006', 'local', '', 'Manufacturing Processes and Technologies (excl. Textiles)', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091007', 'local', '', 'Manufacturing Robotics and Mechatronics (excl. Automotive  Mechatronics)', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091008', 'local', '', 'Manufacturing Safety and Quality', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091009', 'local', '', 'Microtechnology', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091010', 'local', '', 'Packaging, Storage and Transportation', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091011', 'local', '', 'Precision Engineering', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091012', 'local', '', 'Textile Technology', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091099', 'local', '', 'Manufacturing Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '091000', 'pt', '', 'ANZSRC>>ENGINEERING>>MANUFACTURING ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091100', 'local', '', 'MARITIME ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091101', 'local', '', 'Marine Engineering', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091102', 'local', '', 'Naval Architecture', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091103', 'local', '', 'Ocean Engineering', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091104', 'local', '', 'Ship and Platform Hydrodynamics', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091105', 'local', '', 'Ship and Platform Structures', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091106', 'local', '', 'Special Vehicles', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091199', 'local', '', 'Maritime Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '091100', 'pt', '', 'ANZSRC>>ENGINEERING>>MARITIME ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091200', 'local', '', 'MATERIALS ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091201', 'local', '', 'Ceramics', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('100109', 'local', '', 'Transgenesis', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('091202', 'local', '', 'Composite and Hybrid Materials', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091203', 'local', '', 'Compound Semiconductors', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091204', 'local', '', 'Elemental Semiconductors', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091205', 'local', '', 'Functional Materials', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091206', 'local', '', 'Glass', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091207', 'local', '', 'Metals and Alloy Materials', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091208', 'local', '', 'Organic Semiconductors', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091209', 'local', '', 'Polymers and Plastics', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091210', 'local', '', 'Timber, Pulp and Paper', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091299', 'local', '', 'Materials Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '091200', 'pt', '', 'ANZSRC>>ENGINEERING>>MATERIALS ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091300', 'local', '', 'MECHANICAL ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091301', 'local', '', 'Acoustics and Noise Control (excl. Architectural Acoustics)', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091302', 'local', '', 'Automation and Control Engineering', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091303', 'local', '', 'Autonomous Vehicles', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091304', 'local', '', 'Dynamics, Vibration and Vibration Control', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091305', 'local', '', 'Energy Generation, Conversion and Storage Engineering', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091306', 'local', '', 'Microelectromechanical Systems (MEMS)', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091307', 'local', '', 'Numerical Modelling and Mechanical Characterisation', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091308', 'local', '', 'Solid Mechanics', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091309', 'local', '', 'Tribology', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091399', 'local', '', 'Mechanical Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '091300', 'pt', '', 'ANZSRC>>ENGINEERING>>MECHANICAL ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091400', 'local', '', 'RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091401', 'local', '', 'Electrometallurgy', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091402', 'local', '', 'Geomechanics and Resources Geotechnical Engineering', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091403', 'local', '', 'Hydrometallurgy', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091404', 'local', '', 'Mineral Processing/Beneficiation', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091405', 'local', '', 'Mining Engineering', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091406', 'local', '', 'Petroleum and Reservoir Engineering', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091407', 'local', '', 'Pyrometallurgy', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091499', 'local', '', 'Resources Engineering and Extractive Metallurgy not  elsewhere classified', '', '', 'ANZSRC-FOR', '091400', 'pt', '', 'ANZSRC>>ENGINEERING>>RESOURCES ENGINEERING AND EXTRACTIVE  METALLURGY', '');
INSERT INTO tbl_terms VALUES ('091500', 'local', '', 'INTERDISCIPLINARY ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091501', 'local', '', 'Computational Fluid Dynamics', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091502', 'local', '', 'Computational Heat Transfer', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091503', 'local', '', 'Engineering Practice', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091504', 'local', '', 'Fluidisation and Fluid Mechanics', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091505', 'local', '', 'Heat and Mass Transfer Operations', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091506', 'local', '', 'Nuclear Engineering (incl. Fuel Enrichment and Waste  Processing and Storage)', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091507', 'local', '', 'Risk Engineering (excl. Earthquake Engineering)', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091508', 'local', '', 'Turbulent Flows', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('091599', 'local', '', 'Interdisciplinary Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '091500', 'pt', '', 'ANZSRC>>ENGINEERING>>INTERDISCIPLINARY ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('099900', 'local', '', 'OTHER ENGINEERING', '', '', 'ANZSRC-FOR', '090000', 'pt', '', 'ANZSRC>>ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('099901', 'local', '', 'Agricultural Engineering', '', '', 'ANZSRC-FOR', '099900', 'pt', '', 'ANZSRC>>ENGINEERING>>OTHER ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('099902', 'local', '', 'Engineering Instrumentation', '', '', 'ANZSRC-FOR', '099900', 'pt', '', 'ANZSRC>>ENGINEERING>>OTHER ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('099999', 'local', '', 'Engineering not elsewhere classified', '', '', 'ANZSRC-FOR', '099900', 'pt', '', 'ANZSRC>>ENGINEERING>>OTHER ENGINEERING', '');
INSERT INTO tbl_terms VALUES ('100000', 'local', '', 'TECHNOLOGY', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('100100', 'local', '', 'AGRICULTURAL BIOTECHNOLOGY', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100101', 'local', '', 'Agricultural Biotechnology Diagnostics (incl. Biosensors)', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100102', 'local', '', 'Agricultural Marine Biotechnology', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100103', 'local', '', 'Agricultural Molecular Engineering of Nucleic Acids and  Proteins', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100104', 'local', '', 'Genetically Modified Animals', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100105', 'local', '', 'Genetically Modified Field Crops and Pasture', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100106', 'local', '', 'Genetically Modified Horticulture Plants', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100107', 'local', '', 'Genetically Modified Trees', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100108', 'local', '', 'Livestock cloning', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100199', 'local', '', 'Agricultural Biotechnology not elsewhere classified', '', '', 'ANZSRC-FOR', '100100', 'pt', '', 'ANZSRC>>TECHNOLOGY>>AGRICULTURAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100200', 'local', '', 'ENVIRONMENTAL BIOTECHNOLOGY', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100201', 'local', '', 'Biodiscovery', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100202', 'local', '', 'Biological Control', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100203', 'local', '', 'Bioremediation', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100204', 'local', '', 'Environmental Biotechnology Diagnostics (incl. Biosensors)', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100205', 'local', '', 'Environmental Marine Biotechnology', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100206', 'local', '', 'Environmental Molecular Engineering of Nucleic Acids and  Proteins', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100299', 'local', '', 'Environmental Biotechnology not elsewhere classified', '', '', 'ANZSRC-FOR', '100200', 'pt', '', 'ANZSRC>>TECHNOLOGY>>ENVIRONMENTAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100300', 'local', '', 'INDUSTRIAL BIOTECHNOLOGY', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100301', 'local', '', 'Biocatalysis and Enzyme Technology', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100302', 'local', '', 'Bioprocessing, Bioproduction and Bioproducts', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100303', 'local', '', 'Fermentation', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100304', 'local', '', 'Industrial Biotechnology Diagnostics (incl. Biosensors)', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100305', 'local', '', 'Industrial Microbiology (incl. Biofeedstocks)', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100306', 'local', '', 'Industrial Molecular Engineering of Nucleic Acids and  Proteins', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100399', 'local', '', 'Industrial Biotechnology not elsewhere classified', '', '', 'ANZSRC-FOR', '100300', 'pt', '', 'ANZSRC>>TECHNOLOGY>>INDUSTRIAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100400', 'local', '', 'MEDICAL BIOTECHNOLOGY', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100401', 'local', '', 'Gene and Molecular Therapy', '', '', 'ANZSRC-FOR', '100400', 'pt', '', 'ANZSRC>>TECHNOLOGY>>MEDICAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100402', 'local', '', 'Medical Biotechnology Diagnostics (incl. Biosensors)', '', '', 'ANZSRC-FOR', '100400', 'pt', '', 'ANZSRC>>TECHNOLOGY>>MEDICAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100403', 'local', '', 'Medical Molecular Engineering of Nucleic Acids and Proteins', '', '', 'ANZSRC-FOR', '100400', 'pt', '', 'ANZSRC>>TECHNOLOGY>>MEDICAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100404', 'local', '', 'Regenerative Medicine (incl. Stem Cells and Tissue  Engineering)', '', '', 'ANZSRC-FOR', '100400', 'pt', '', 'ANZSRC>>TECHNOLOGY>>MEDICAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100499', 'local', '', 'Medical Biotechnology not elsewhere classified', '', '', 'ANZSRC-FOR', '100400', 'pt', '', 'ANZSRC>>TECHNOLOGY>>MEDICAL BIOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100500', 'local', '', 'COMMUNICATIONS TECHNOLOGIES', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100501', 'local', '', 'Antennas and Propagation', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100502', 'local', '', 'Broadband and Modem Technology', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100503', 'local', '', 'Computer Communications Networks', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100504', 'local', '', 'Data Communications', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100505', 'local', '', 'Microwave and Millimetrewave Theory and Technology', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100506', 'local', '', 'Optical Fibre Communications', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100507', 'local', '', 'Optical Networks and Systems', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100508', 'local', '', 'Satellite Communications', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100509', 'local', '', 'Video Communications', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100510', 'local', '', 'Wireless Communications', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100599', 'local', '', 'Communications Technologies not elsewhere classified', '', '', 'ANZSRC-FOR', '100500', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMMUNICATIONS TECHNOLOGIES', '');
INSERT INTO tbl_terms VALUES ('100600', 'local', '', 'COMPUTER HARDWARE', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100601', 'local', '', 'Arithmetic and Logic Structures', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100602', 'local', '', 'Input, Output and Data Devices', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100603', 'local', '', 'Logic Design', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100604', 'local', '', 'Memory Structures', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100605', 'local', '', 'Performance Evaluation; Testing and Simulation of  Reliability', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100606', 'local', '', 'Processor Architectures', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100699', 'local', '', 'Computer Hardware not elsewhere classified', '', '', 'ANZSRC-FOR', '100600', 'pt', '', 'ANZSRC>>TECHNOLOGY>>COMPUTER HARDWARE', '');
INSERT INTO tbl_terms VALUES ('100700', 'local', '', 'NANOTECHNOLOGY', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100701', 'local', '', 'Environmental Nanotechnology', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100702', 'local', '', 'Molecular and Organic Electronics', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100703', 'local', '', 'Nanobiotechnology', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100704', 'local', '', 'Nanoelectromechanical Systems', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100705', 'local', '', 'Nanoelectronics', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100706', 'local', '', 'Nanofabrication, Growth and Self Assembly', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100707', 'local', '', 'Nanomanufacturing', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100708', 'local', '', 'Nanomaterials', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100709', 'local', '', 'Nanomedicine', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100710', 'local', '', 'Nanometrology', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100711', 'local', '', 'Nanophotonics', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100712', 'local', '', 'Nanoscale Characterisation', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100713', 'local', '', 'Nanotoxicology, Health and Safety', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('100799', 'local', '', 'Nanotechnology not elsewhere classified', '', '', 'ANZSRC-FOR', '100700', 'pt', '', 'ANZSRC>>TECHNOLOGY>>NANOTECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('109900', 'local', '', 'OTHER TECHNOLOGY', '', '', 'ANZSRC-FOR', '100000', 'pt', '', 'ANZSRC>>TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('109999', 'local', '', 'Technology not elsewhere classified', '', '', 'ANZSRC-FOR', '109900', 'pt', '', 'ANZSRC>>TECHNOLOGY>>OTHER TECHNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110000', 'local', '', 'MEDICAL AND HEALTH SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('110100', 'local', '', 'MEDICAL BIOCHEMISTRY AND METABOLOMICS', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110101', 'local', '', 'Medical Biochemistry: Amino Acids and Metabolites', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110102', 'local', '', 'Medical Biochemistry: Carbohydrates', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110103', 'local', '', 'Medical Biochemistry: Inorganic Elements and  Compounds', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110104', 'local', '', 'Medical Biochemistry: Lipids', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110105', 'local', '', 'Medical Biochemistry: Nucleic Acids', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110106', 'local', '', 'Medical Biochemistry: Proteins and Peptides (incl. Medical  Proteomics)', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110107', 'local', '', 'Metabolic Medicine', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110199', 'local', '', 'Medical Biochemistry and Metabolomics not elsewhere classified', '', '', 'ANZSRC-FOR', '110100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL BIOCHEMISTRY AND METABOLOMICS', '');
INSERT INTO tbl_terms VALUES ('110200', 'local', '', 'CARDIOVASCULAR MEDICINE AND HAEMATOLOGY', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110201', 'local', '', 'Cardiology (incl. Cardiovascular Diseases)', '', '', 'ANZSRC-FOR', '110200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CARDIOVASCULAR MEDICINE AND HAEMATOLOGY', '');
INSERT INTO tbl_terms VALUES ('110202', 'local', '', 'Haematology', '', '', 'ANZSRC-FOR', '110200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CARDIOVASCULAR MEDICINE AND HAEMATOLOGY', '');
INSERT INTO tbl_terms VALUES ('110203', 'local', '', 'Respiratory Diseases', '', '', 'ANZSRC-FOR', '110200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CARDIOVASCULAR MEDICINE AND HAEMATOLOGY', '');
INSERT INTO tbl_terms VALUES ('110299', 'local', '', 'Cardiovascular Medicine and Haematology not elsewhere classified', '', '', 'ANZSRC-FOR', '110200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CARDIOVASCULAR MEDICINE AND HAEMATOLOGY', '');
INSERT INTO tbl_terms VALUES ('110300', 'local', '', 'CLINICAL SCIENCES', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110301', 'local', '', 'Anaesthesiology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110302', 'local', '', 'Clinical Chemistry (diagnostics)', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110303', 'local', '', 'Clinical Microbiology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110304', 'local', '', 'Dermatology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110305', 'local', '', 'Emergency Medicine', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110306', 'local', '', 'Endocrinology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110307', 'local', '', 'Gastroenterology and Hepatology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110308', 'local', '', 'Geriatrics and Gerontology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110309', 'local', '', 'Infectious Diseases', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110310', 'local', '', 'Intensive Care', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110311', 'local', '', 'Medical Genetics (excl. Cancer Genetics)', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110312', 'local', '', 'Nephrology and Urology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110313', 'local', '', 'Nuclear Medicine', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110314', 'local', '', 'Orthopaedics', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110315', 'local', '', 'Otorhinolaryngology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110316', 'local', '', 'Pathology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110317', 'local', '', 'Physiotherapy', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110318', 'local', '', 'Podiatry', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110319', 'local', '', 'Psychiatry (incl. Psychotherapy)', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110320', 'local', '', 'Radiology and Organ Imaging', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110321', 'local', '', 'Rehabilitation and Therapy (excl. Physiotherapy)', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110322', 'local', '', 'Rheumatology and Arthritis', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110323', 'local', '', 'Surgery', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110324', 'local', '', 'Venereology', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110399', 'local', '', 'Clinical Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '110300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>CLINICAL SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110400', 'local', '', 'COMPLEMENTARY AND ALTERNATIVE MEDICINE', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110401', 'local', '', 'Chiropractic', '', '', 'ANZSRC-FOR', '110400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>COMPLEMENTARY AND ALTERNATIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('110402', 'local', '', 'Naturopathy', '', '', 'ANZSRC-FOR', '110400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>COMPLEMENTARY AND ALTERNATIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('110403', 'local', '', 'Traditional Aboriginal and Torres Strait Islander Medicine  and Treatments', '', '', 'ANZSRC-FOR', '110400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>COMPLEMENTARY AND ALTERNATIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('110404', 'local', '', 'Traditional Chinese Medicine and Treatments', '', '', 'ANZSRC-FOR', '110400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>COMPLEMENTARY AND ALTERNATIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('110405', 'local', '', 'Traditional Maori Medicine and Treatments', '', '', 'ANZSRC-FOR', '110400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>COMPLEMENTARY AND ALTERNATIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('110499', 'local', '', 'Complementary and Alternative Medicine not elsewhere classified', '', '', 'ANZSRC-FOR', '110400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>COMPLEMENTARY AND ALTERNATIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('110500', 'local', '', 'DENTISTRY', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110501', 'local', '', 'Dental Materials and Equipment', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110502', 'local', '', 'Dental Therapeutics, Pharmacology and Toxicology', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110503', 'local', '', 'Endodontics', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110504', 'local', '', 'Oral and Maxillofacial Surgery', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110505', 'local', '', 'Oral Medicine and Pathology', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110506', 'local', '', 'Orthodontics and Dentofacial Orthopaedics', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110507', 'local', '', 'Paedodontics', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110508', 'local', '', 'Periodontics', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110509', 'local', '', 'Special Needs Dentistry', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110599', 'local', '', 'Dentistry not elsewhere classified', '', '', 'ANZSRC-FOR', '110500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>DENTISTRY', '');
INSERT INTO tbl_terms VALUES ('110600', 'local', '', 'HUMAN MOVEMENT AND SPORTS SCIENCE', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110601', 'local', '', 'Biomechanics', '', '', 'ANZSRC-FOR', '110600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>HUMAN MOVEMENT AND SPORTS SCIENCE', '');
INSERT INTO tbl_terms VALUES ('110602', 'local', '', 'Exercise Physiology', '', '', 'ANZSRC-FOR', '110600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>HUMAN MOVEMENT AND SPORTS SCIENCE', '');
INSERT INTO tbl_terms VALUES ('110603', 'local', '', 'Motor Control', '', '', 'ANZSRC-FOR', '110600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>HUMAN MOVEMENT AND SPORTS SCIENCE', '');
INSERT INTO tbl_terms VALUES ('110604', 'local', '', 'Sports Medicine', '', '', 'ANZSRC-FOR', '110600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>HUMAN MOVEMENT AND SPORTS SCIENCE', '');
INSERT INTO tbl_terms VALUES ('110699', 'local', '', 'Human Movement and Sports Science not elsewhere classified', '', '', 'ANZSRC-FOR', '110600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>HUMAN MOVEMENT AND SPORTS SCIENCE', '');
INSERT INTO tbl_terms VALUES ('110700', 'local', '', 'IMMUNOLOGY', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110701', 'local', '', 'Allergy', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110702', 'local', '', 'Applied Immunology (incl. Antibody Engineering,  Xenotransplantation and T-cell Therapies)', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110703', 'local', '', 'Autoimmunity', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110704', 'local', '', 'Cellular Immunology', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110705', 'local', '', 'Humoural Immunology and Immunochemistry', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110706', 'local', '', 'Immunogenetics (incl. Genetic Immunology)', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110707', 'local', '', 'Innate Immunity', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110708', 'local', '', 'Transplantation Immunology', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110709', 'local', '', 'Tumour Immunology', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110799', 'local', '', 'Immunology not elsewhere classified', '', '', 'ANZSRC-FOR', '110700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>IMMUNOLOGY', '');
INSERT INTO tbl_terms VALUES ('110800', 'local', '', 'MEDICAL MICROBIOLOGY', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110801', 'local', '', 'Medical Bacteriology', '', '', 'ANZSRC-FOR', '110800', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('110802', 'local', '', 'Medical Infection Agents (incl. Prions)', '', '', 'ANZSRC-FOR', '110800', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('110803', 'local', '', 'Medical Parasitology', '', '', 'ANZSRC-FOR', '110800', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('110804', 'local', '', 'Medical Virology', '', '', 'ANZSRC-FOR', '110800', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('110899', 'local', '', 'Medical Microbiology not elsewhere classified', '', '', 'ANZSRC-FOR', '110800', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL MICROBIOLOGY', '');
INSERT INTO tbl_terms VALUES ('110900', 'local', '', 'NEUROSCIENCES', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('110901', 'local', '', 'Autonomic Nervous System', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('110902', 'local', '', 'Cellular Nervous System', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('110903', 'local', '', 'Central Nervous System', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('110904', 'local', '', 'Neurology and Neuromuscular Diseases', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('110905', 'local', '', 'Peripheral Nervous System', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('110906', 'local', '', 'Sensory Systems', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('110999', 'local', '', 'Neurosciences not elsewhere classified', '', '', 'ANZSRC-FOR', '110900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NEUROSCIENCES', '');
INSERT INTO tbl_terms VALUES ('111000', 'local', '', 'NURSING', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111001', 'local', '', 'Aged Care Nursing', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111002', 'local', '', 'Clinical Nursing: Primary (Preventative)', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111003', 'local', '', 'Clinical Nursing: Secondary (Acute Care)', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111004', 'local', '', 'Clinical Nursing: Tertiary (Rehabilitative)', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111005', 'local', '', 'Mental Health Nursing', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111006', 'local', '', 'Midwifery', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111099', 'local', '', 'Nursing not elsewhere classified', '', '', 'ANZSRC-FOR', '111000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NURSING', '');
INSERT INTO tbl_terms VALUES ('111100', 'local', '', 'NUTRITION AND DIETETICS', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111101', 'local', '', 'Clinical and Sports Nutrition', '', '', 'ANZSRC-FOR', '111100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NUTRITION AND DIETETICS', '');
INSERT INTO tbl_terms VALUES ('111102', 'local', '', 'Dietetics and Nutrigenomics', '', '', 'ANZSRC-FOR', '111100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NUTRITION AND DIETETICS', '');
INSERT INTO tbl_terms VALUES ('111103', 'local', '', 'Nutritional Physiology', '', '', 'ANZSRC-FOR', '111100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NUTRITION AND DIETETICS', '');
INSERT INTO tbl_terms VALUES ('111104', 'local', '', 'Public Nutrition Intervention', '', '', 'ANZSRC-FOR', '111100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NUTRITION AND DIETETICS', '');
INSERT INTO tbl_terms VALUES ('111199', 'local', '', 'Nutrition and Dietetics not elsewhere classified', '', '', 'ANZSRC-FOR', '111100', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>NUTRITION AND DIETETICS', '');
INSERT INTO tbl_terms VALUES ('111200', 'local', '', 'ONCOLOGY AND CARCINOGENESIS', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111201', 'local', '', 'Cancer Cell Biology', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111202', 'local', '', 'Cancer Diagnosis', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111203', 'local', '', 'Cancer Genetics', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111204', 'local', '', 'Cancer Therapy (excl. Chemotherapy and Radiation Therapy)', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111205', 'local', '', 'Chemotherapy', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111206', 'local', '', 'Haematological Tumours', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111207', 'local', '', 'Molecular Targets', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111208', 'local', '', 'Radiation Therapy', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111209', 'local', '', 'Solid Tumours', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111299', 'local', '', 'Oncology and Carcinogenesis not elsewhere classified', '', '', 'ANZSRC-FOR', '111200', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>ONCOLOGY AND CARCINOGENESIS', '');
INSERT INTO tbl_terms VALUES ('111300', 'local', '', 'OPTOMETRY AND OPHTHALMOLOGY', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111301', 'local', '', 'Ophthalmology', '', '', 'ANZSRC-FOR', '111300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>OPTOMETRY AND OPHTHALMOLOGY', '');
INSERT INTO tbl_terms VALUES ('111302', 'local', '', 'Optical Technology', '', '', 'ANZSRC-FOR', '111300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>OPTOMETRY AND OPHTHALMOLOGY', '');
INSERT INTO tbl_terms VALUES ('111303', 'local', '', 'Vision Science', '', '', 'ANZSRC-FOR', '111300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>OPTOMETRY AND OPHTHALMOLOGY', '');
INSERT INTO tbl_terms VALUES ('111399', 'local', '', 'Optometry and Ophthalmology not elsewhere classified', '', '', 'ANZSRC-FOR', '111300', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>OPTOMETRY AND OPHTHALMOLOGY', '');
INSERT INTO tbl_terms VALUES ('111400', 'local', '', 'PAEDIATRICS AND REPRODUCTIVE MEDICINE', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111401', 'local', '', 'Foetal Development and Medicine', '', '', 'ANZSRC-FOR', '111400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PAEDIATRICS AND REPRODUCTIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('111402', 'local', '', 'Obstetrics and Gynaecology', '', '', 'ANZSRC-FOR', '111400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PAEDIATRICS AND REPRODUCTIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('111403', 'local', '', 'Paediatrics', '', '', 'ANZSRC-FOR', '111400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PAEDIATRICS AND REPRODUCTIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('111404', 'local', '', 'Reproduction', '', '', 'ANZSRC-FOR', '111400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PAEDIATRICS AND REPRODUCTIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('111499', 'local', '', 'Paediatrics and Reproductive Medicine not elsewhere classified', '', '', 'ANZSRC-FOR', '111400', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PAEDIATRICS AND REPRODUCTIVE MEDICINE', '');
INSERT INTO tbl_terms VALUES ('111500', 'local', '', 'PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111501', 'local', '', 'Basic Pharmacology', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111502', 'local', '', 'Clinical Pharmacology and Therapeutics', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111503', 'local', '', 'Clinical Pharmacy and Pharmacy Practice', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111504', 'local', '', 'Pharmaceutical Sciences', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111505', 'local', '', 'Pharmacogenomics', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111506', 'local', '', 'Toxicology (incl. Clinical Toxicology)', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111599', 'local', '', 'Pharmacology and Pharmaceutical Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '111500', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PHARMACOLOGY AND PHARMACEUTICAL  SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111600', 'local', '', 'MEDICAL PHYSIOLOGY', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111601', 'local', '', 'Cell Physiology', '', '', 'ANZSRC-FOR', '111600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('111602', 'local', '', 'Human Biophysics', '', '', 'ANZSRC-FOR', '111600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('111603', 'local', '', 'Systems Physiology', '', '', 'ANZSRC-FOR', '111600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('111699', 'local', '', 'Medical Physiology not elsewhere classified', '', '', 'ANZSRC-FOR', '111600', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>MEDICAL PHYSIOLOGY', '');
INSERT INTO tbl_terms VALUES ('111700', 'local', '', 'PUBLIC HEALTH AND HEALTH SERVICES', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('111701', 'local', '', 'Aboriginal and Torres Strait Islander Health', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111702', 'local', '', 'Aged Health Care', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111703', 'local', '', 'Care for Disabled', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111704', 'local', '', 'Community Child Health', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111705', 'local', '', 'Environmental and Occupational Health and Safety', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111706', 'local', '', 'Epidemiology', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111707', 'local', '', 'Family Care', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111708', 'local', '', 'Health and Community Services', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111709', 'local', '', 'Health Care Administration', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111710', 'local', '', 'Health Counselling', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('uri', 'local', '', 'uri', 'Uniform Resource Identifier', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('111711', 'local', '', 'Health Information Systems (incl. Surveillance)', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('140203', 'local', '', 'Economic History', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('111712', 'local', '', 'Health Promotion', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111713', 'local', '', 'Maori Health', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111714', 'local', '', 'Mental Health', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111715', 'local', '', 'Pacific Peoples Health', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111716', 'local', '', 'Preventive Medicine', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111717', 'local', '', 'Primary Health Care', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111718', 'local', '', 'Residential Client Care', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('111799', 'local', '', 'Public Health and Health Services not elsewhere classified', '', '', 'ANZSRC-FOR', '111700', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>PUBLIC HEALTH AND HEALTH SERVICES', '');
INSERT INTO tbl_terms VALUES ('119900', 'local', '', 'OTHER MEDICAL AND HEALTH SCIENCES', '', '', 'ANZSRC-FOR', '110000', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('119999', 'local', '', 'Medical and Health Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '119900', 'pt', '', 'ANZSRC>>MEDICAL AND HEALTH SCIENCES>>OTHER MEDICAL AND HEALTH SCIENCES', '');
INSERT INTO tbl_terms VALUES ('120000', 'local', '', 'BUILT ENVIRONMENT AND DESIGN', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('120100', 'local', '', 'ARCHITECTURE', '', '', 'ANZSRC-FOR', '120000', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('120101', 'local', '', 'Architectural Design', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120102', 'local', '', 'Architectural Heritage and Conservation', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120103', 'local', '', 'Architectural History and Theory', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120104', 'local', '', 'Architectural Science and Technology (incl. Acoustics,  Lighting, Structure and Ecologically Sustainable Design)', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120105', 'local', '', 'Architecture Management', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120106', 'local', '', 'Interior Design', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120107', 'local', '', 'Landscape Architecture', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120199', 'local', '', 'Architecture not elsewhere classified', '', '', 'ANZSRC-FOR', '120100', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ARCHITECTURE', '');
INSERT INTO tbl_terms VALUES ('120200', 'local', '', 'BUILDING', '', '', 'ANZSRC-FOR', '120000', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('120201', 'local', '', 'Building Construction Management and Project Planning', '', '', 'ANZSRC-FOR', '120200', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>BUILDING', '');
INSERT INTO tbl_terms VALUES ('120202', 'local', '', 'Building Science and Techniques', '', '', 'ANZSRC-FOR', '120200', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>BUILDING', '');
INSERT INTO tbl_terms VALUES ('120203', 'local', '', 'Quantity Surveying', '', '', 'ANZSRC-FOR', '120200', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>BUILDING', '');
INSERT INTO tbl_terms VALUES ('120299', 'local', '', 'Building not elsewhere classified', '', '', 'ANZSRC-FOR', '120200', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>BUILDING', '');
INSERT INTO tbl_terms VALUES ('120300', 'local', '', 'DESIGN PRACTICE AND MANAGEMENT', '', '', 'ANZSRC-FOR', '120000', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('120301', 'local', '', 'Design History and Theory', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120302', 'local', '', 'Design Innovation', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120303', 'local', '', 'Design Management and Studio and Professional Practice', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120304', 'local', '', 'Digital and Interaction Design', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120305', 'local', '', 'Industrial Design', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120306', 'local', '', 'Textile and Fashion Design', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120307', 'local', '', 'Visual Communication Design (incl. Graphic Design)', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120399', 'local', '', 'Design Practice and Management not elsewhere classified', '', '', 'ANZSRC-FOR', '120300', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>DESIGN PRACTICE AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('120400', 'local', '', 'ENGINEERING DESIGN', '', '', 'ANZSRC-FOR', '120000', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('120401', 'local', '', 'Engineering Design Empirical Studies', '', '', 'ANZSRC-FOR', '120400', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ENGINEERING DESIGN', '');
INSERT INTO tbl_terms VALUES ('120402', 'local', '', 'Engineering Design Knowledge', '', '', 'ANZSRC-FOR', '120400', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ENGINEERING DESIGN', '');
INSERT INTO tbl_terms VALUES ('120403', 'local', '', 'Engineering Design Methods', '', '', 'ANZSRC-FOR', '120400', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ENGINEERING DESIGN', '');
INSERT INTO tbl_terms VALUES ('120404', 'local', '', 'Engineering Systems Design', '', '', 'ANZSRC-FOR', '120400', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ENGINEERING DESIGN', '');
INSERT INTO tbl_terms VALUES ('120405', 'local', '', 'Models of Engineering Design', '', '', 'ANZSRC-FOR', '120400', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ENGINEERING DESIGN', '');
INSERT INTO tbl_terms VALUES ('120499', 'local', '', 'Engineering Design not elsewhere classified', '', '', 'ANZSRC-FOR', '120400', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>ENGINEERING DESIGN', '');
INSERT INTO tbl_terms VALUES ('120500', 'local', '', 'URBAN AND REGIONAL PLANNING', '', '', 'ANZSRC-FOR', '120000', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('120501', 'local', '', 'Community Planning', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120502', 'local', '', 'History and Theory of the Built Environment (excl.  Architecture)', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120503', 'local', '', 'Housing Markets, Development, Management', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120504', 'local', '', 'Land Use and Environmental Planning', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120505', 'local', '', 'Regional Analysis and Development', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120506', 'local', '', 'Transport Planning', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120507', 'local', '', 'Urban Analysis and Development', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('120508', 'local', '', 'Urban Design', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('140202', 'local', '', 'Economic Development and Growth', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('120599', 'local', '', 'Urban and Regional Planning not elsewhere classified', '', '', 'ANZSRC-FOR', '120500', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>URBAN AND REGIONAL PLANNING', '');
INSERT INTO tbl_terms VALUES ('129900', 'local', '', 'OTHER BUILT ENVIRONMENT AND DESIGN', '', '', 'ANZSRC-FOR', '120000', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('129999', 'local', '', 'Built Environment and Design not elsewhere classified', '', '', 'ANZSRC-FOR', '129900', 'pt', '', 'ANZSRC>>BUILT ENVIRONMENT AND DESIGN>>OTHER BUILT ENVIRONMENT AND DESIGN', '');
INSERT INTO tbl_terms VALUES ('130000', 'local', '', 'EDUCATION', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('130100', 'local', '', 'EDUCATION SYSTEMS', '', '', 'ANZSRC-FOR', '130000', 'pt', '', 'ANZSRC>>EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130101', 'local', '', 'Continuing and Community Education', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130102', 'local', '', 'Early Childhood Education (excl. Maori)', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130103', 'local', '', 'Higher Education', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130104', 'local', '', 'Kura Kaupapa Maori (Maori Primary Education)', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130105', 'local', '', 'Primary Education (excl. Maori)', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130106', 'local', '', 'Secondary Education', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130107', 'local', '', 'Te Whariki (Maori Early Childhood Education)', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130108', 'local', '', 'Technical, Further and Workplace Education', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130199', 'local', '', 'Education Systems not elsewhere classified', '', '', 'ANZSRC-FOR', '130100', 'pt', '', 'ANZSRC>>EDUCATION>>EDUCATION SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('130200', 'local', '', 'CURRICULUM AND PEDAGOGY', '', '', 'ANZSRC-FOR', '130000', 'pt', '', 'ANZSRC>>EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130201', 'local', '', 'Creative Arts, Media and Communication Curriculum and  Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130202', 'local', '', 'Curriculum and Pedagogy Theory and Development', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130203', 'local', '', 'Economics, Business and Management Curriculum and  Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130204', 'local', '', 'English and Literacy Curriculum and Pedagogy (excl.  LOTE, ESL and TESOL)', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130205', 'local', '', 'Humanities and Social Sciences Curriculum and Pedagogy  (excl. Economics, Business and Management)', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130206', 'local', '', 'Kohanga Reo (Maori Language Curriculum and Pedagogy)', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130207', 'local', '', 'LOTE, ESL and TESOL Curriculum and Pedagogy (excl. Maori)', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130208', 'local', '', 'Mathematics and Numeracy Curriculum and Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130209', 'local', '', 'Medicine, Nursing and Health Curriculum and Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130210', 'local', '', 'Physical Education and Development Curriculum and  Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130211', 'local', '', 'Religion Curriculum and Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130212', 'local', '', 'Science, Technology and Engineering Curriculum and  Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130213', 'local', '', 'Vocational Education and Training Curriculum and  Pedagogy', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130299', 'local', '', 'Curriculum and Pedagogy not elsewhere classified', '', '', 'ANZSRC-FOR', '130200', 'pt', '', 'ANZSRC>>EDUCATION>>CURRICULUM AND PEDAGOGY', '');
INSERT INTO tbl_terms VALUES ('130300', 'local', '', 'SPECIALIST STUDIES IN EDUCATION', '', '', 'ANZSRC-FOR', '130000', 'pt', '', 'ANZSRC>>EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130301', 'local', '', 'Aboriginal and Torres Strait Islander Education', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130302', 'local', '', 'Comparative and Cross-Cultural Education', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130303', 'local', '', 'Education Assessment and Evaluation', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130304', 'local', '', 'Educational Administration, Management and Leadership', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130305', 'local', '', 'Educational Counselling', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130306', 'local', '', 'Educational Technology and Computing', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130307', 'local', '', 'Ethnic Education (excl. Aboriginal and Torres Strait  Islander, Maori and Pacific Peoples)', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130308', 'local', '', 'Gender, Sexuality and Education', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130309', 'local', '', 'Learning Sciences', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130310', 'local', '', 'Maori Education (excl. Early Childhood and Primary  Education)', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130311', 'local', '', 'Pacific Peoples Education', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130312', 'local', '', 'Special Education and Disability', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130313', 'local', '', 'Teacher Education and Professional Development of  Educators', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('130399', 'local', '', 'Specialist Studies in Education not elsewhere classified', '', '', 'ANZSRC-FOR', '130300', 'pt', '', 'ANZSRC>>EDUCATION>>SPECIALIST STUDIES IN EDUCATION', '');
INSERT INTO tbl_terms VALUES ('139900', 'local', '', 'OTHER EDUCATION', '', '', 'ANZSRC-FOR', '130000', 'pt', '', 'ANZSRC>>EDUCATION', '');
INSERT INTO tbl_terms VALUES ('139999', 'local', '', 'Education not elsewhere classified', '', '', 'ANZSRC-FOR', '139900', 'pt', '', 'ANZSRC>>EDUCATION>>OTHER EDUCATION', '');
INSERT INTO tbl_terms VALUES ('140000', 'local', '', 'ECONOMICS', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('140101', 'local', '', 'History of Economic Thought', '', '', 'ANZSRC-FOR', '140100', 'pt', '', 'ANZSRC>>ECONOMICS>>', '');
INSERT INTO tbl_terms VALUES ('140102', 'local', '', 'Macroeconomic Theory', '', '', 'ANZSRC-FOR', '140100', 'pt', '', 'ANZSRC>>ECONOMICS>>', '');
INSERT INTO tbl_terms VALUES ('140103', 'local', '', 'Mathematical Economics', '', '', 'ANZSRC-FOR', '140100', 'pt', '', 'ANZSRC>>ECONOMICS>>', '');
INSERT INTO tbl_terms VALUES ('140104', 'local', '', 'Microeconomic Theory', '', '', 'ANZSRC-FOR', '140100', 'pt', '', 'ANZSRC>>ECONOMICS>>', '');
INSERT INTO tbl_terms VALUES ('140199', 'local', '', 'Economic Theory not elsewhere classified', '', '', 'ANZSRC-FOR', '140100', 'pt', '', 'ANZSRC>>ECONOMICS>>', '');
INSERT INTO tbl_terms VALUES ('140200', 'local', '', 'APPLIED ECONOMICS', '', '', 'ANZSRC-FOR', '140000', 'pt', '', 'ANZSRC>>ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140201', 'local', '', 'Agricultural Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140204', 'local', '', 'Economics of Education', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140205', 'local', '', 'Environment and Resource Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140206', 'local', '', 'Experimental Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140207', 'local', '', 'Financial Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140208', 'local', '', 'Health Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140209', 'local', '', 'Industry Economics and Industrial Organisation', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140210', 'local', '', 'International Economics and International Finance', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140211', 'local', '', 'Labour Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140212', 'local', '', 'Macroeconomics (incl. Monetary and Fiscal Theory)', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140213', 'local', '', 'Public Economics- Public Choice', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140214', 'local', '', 'Public Economics- Publically Provided Goods', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140215', 'local', '', 'Public Economics- Taxation and Revenue', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140216', 'local', '', 'Tourism Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140217', 'local', '', 'Transport Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140218', 'local', '', 'Urban and Regional Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140219', 'local', '', 'Welfare Economics', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140299', 'local', '', 'Applied Economics not elsewhere classified', '', '', 'ANZSRC-FOR', '140200', 'pt', '', 'ANZSRC>>ECONOMICS>>APPLIED ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140300', 'local', '', 'ECONOMETRICS', '', '', 'ANZSRC-FOR', '140000', 'pt', '', 'ANZSRC>>ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('140302', 'local', '', 'Econometric and Statistical Methods', '', '', 'ANZSRC-FOR', '140300', 'pt', '', 'ANZSRC>>ECONOMICS>>ECONOMETRICS', '');
INSERT INTO tbl_terms VALUES ('140303', 'local', '', 'Economic Models and Forecasting', '', '', 'ANZSRC-FOR', '140300', 'pt', '', 'ANZSRC>>ECONOMICS>>ECONOMETRICS', '');
INSERT INTO tbl_terms VALUES ('140304', 'local', '', 'Panel Data Analysis', '', '', 'ANZSRC-FOR', '140300', 'pt', '', 'ANZSRC>>ECONOMICS>>ECONOMETRICS', '');
INSERT INTO tbl_terms VALUES ('140305', 'local', '', 'Time-Series Analysis', '', '', 'ANZSRC-FOR', '140300', 'pt', '', 'ANZSRC>>ECONOMICS>>ECONOMETRICS', '');
INSERT INTO tbl_terms VALUES ('140399', 'local', '', 'Econometrics not elsewhere classified', '', '', 'ANZSRC-FOR', '140300', 'pt', '', 'ANZSRC>>ECONOMICS>>ECONOMETRICS', '');
INSERT INTO tbl_terms VALUES ('149900', 'local', '', 'OTHER ECONOMICS', '', '', 'ANZSRC-FOR', '140000', 'pt', '', 'ANZSRC>>ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('149901', 'local', '', 'Comparative Economic Systems', '', '', 'ANZSRC-FOR', '149900', 'pt', '', 'ANZSRC>>ECONOMICS>>OTHER ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('149902', 'local', '', 'Ecological Economics', '', '', 'ANZSRC-FOR', '149900', 'pt', '', 'ANZSRC>>ECONOMICS>>OTHER ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('149903', 'local', '', 'Heterodox Economics', '', '', 'ANZSRC-FOR', '149900', 'pt', '', 'ANZSRC>>ECONOMICS>>OTHER ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('149999', 'local', '', 'Economics not elsewhere classified', '', '', 'ANZSRC-FOR', '149900', 'pt', '', 'ANZSRC>>ECONOMICS>>OTHER ECONOMICS', '');
INSERT INTO tbl_terms VALUES ('150000', 'local', '', 'COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('150100', 'local', '', 'ACCOUNTING, AUDITING AND ACCOUNTABILITY', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('150101', 'local', '', 'Accounting Theory and Standards', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150102', 'local', '', 'Auditing and Accountability', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150103', 'local', '', 'Financial Accounting', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150104', 'local', '', 'International Accounting', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150105', 'local', '', 'Management Accounting', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150106', 'local', '', 'Sustainability Accounting and Reporting', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150107', 'local', '', 'Taxation Accounting', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150199', 'local', '', 'Accounting, Auditing and Accountability not elsewhere classified', '', '', 'ANZSRC-FOR', '150100', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>ACCOUNTING, AUDITING AND ACCOUNTABILITY', '');
INSERT INTO tbl_terms VALUES ('150200', 'local', '', 'BANKING, FINANCE AND INVESTMENT', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('150201', 'local', '', 'Finance', '', '', 'ANZSRC-FOR', '150200', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BANKING, FINANCE AND INVESTMENT', '');
INSERT INTO tbl_terms VALUES ('150202', 'local', '', 'Financial Econometrics', '', '', 'ANZSRC-FOR', '150200', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BANKING, FINANCE AND INVESTMENT', '');
INSERT INTO tbl_terms VALUES ('150203', 'local', '', 'Financial Institutions (incl. Banking)', '', '', 'ANZSRC-FOR', '150200', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BANKING, FINANCE AND INVESTMENT', '');
INSERT INTO tbl_terms VALUES ('150204', 'local', '', 'Insurance Studies', '', '', 'ANZSRC-FOR', '150200', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BANKING, FINANCE AND INVESTMENT', '');
INSERT INTO tbl_terms VALUES ('150205', 'local', '', 'Investment and Risk Management', '', '', 'ANZSRC-FOR', '150200', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BANKING, FINANCE AND INVESTMENT', '');
INSERT INTO tbl_terms VALUES ('150299', 'local', '', 'Banking, Finance and Investment not elsewhere classified', '', '', 'ANZSRC-FOR', '150200', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BANKING, FINANCE AND INVESTMENT', '');
INSERT INTO tbl_terms VALUES ('150300', 'local', '', 'BUSINESS AND MANAGEMENT', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('970112', 'local', '', 'Expanding Knowledge in Built Environment and Design', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('150301', 'local', '', 'Business Information Management (incl. Records,  Knowledge and Information Management, and  Intelligence)', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150302', 'local', '', 'Business Information Systems', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150303', 'local', '', 'Corporate Governance and Stakeholder Engagement', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150304', 'local', '', 'Entrepreneurship', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150305', 'local', '', 'Human Resources Management', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150306', 'local', '', 'Industrial Relations', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150307', 'local', '', 'Innovation and Technology Management', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150308', 'local', '', 'International Business', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150309', 'local', '', 'Logistics and Supply Chain Management', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150310', 'local', '', 'Organisation and Management Theory', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150311', 'local', '', 'Organisational Behaviour', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150312', 'local', '', 'Organisational Planning and Management', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150313', 'local', '', 'Quality Management', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150314', 'local', '', 'Small Business Management', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150399', 'local', '', 'Business and Management not elsewhere classified', '', '', 'ANZSRC-FOR', '150300', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>BUSINESS AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('150400', 'local', '', 'COMMERCIAL SERVICES', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('150401', 'local', '', 'Food and Hospitality Services', '', '', 'ANZSRC-FOR', '150400', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>COMMERCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('150402', 'local', '', 'Hospitality Management', '', '', 'ANZSRC-FOR', '150400', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>COMMERCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('150403', 'local', '', 'Real Estate and Valuation Services', '', '', 'ANZSRC-FOR', '150400', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>COMMERCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('150404', 'local', '', 'Sport and Leisure Management', '', '', 'ANZSRC-FOR', '150400', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>COMMERCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('150499', 'local', '', 'Commercial Services not elsewhere classified', '', '', 'ANZSRC-FOR', '150400', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>COMMERCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('150500', 'local', '', 'MARKETING', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('150501', 'local', '', 'Consumer-Oriented Product or Service Development', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150502', 'local', '', 'Marketing Communications', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150503', 'local', '', 'Marketing Management (incl. Strategy and Customer Relations)', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150504', 'local', '', 'Marketing Measurement', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150505', 'local', '', 'Marketing Research Methodology', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150506', 'local', '', 'Marketing Theory', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150507', 'local', '', 'Pricing (incl. Consumer Value Estimation)', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150599', 'local', '', 'Marketing not elsewhere classified', '', '', 'ANZSRC-FOR', '150500', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>MARKETING', '');
INSERT INTO tbl_terms VALUES ('150600', 'local', '', 'TOURISM', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('150601', 'local', '', 'Impacts of Tourism', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150602', 'local', '', 'Tourism Forecasting', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150603', 'local', '', 'Tourism Management', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150604', 'local', '', 'Tourism Marketing', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150605', 'local', '', 'Tourism Resource Appraisal', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150606', 'local', '', 'Tourist Behaviour and Visitor Experience', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150699', 'local', '', 'Tourism not elsewhere classified', '', '', 'ANZSRC-FOR', '150600', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('150700', 'local', '', 'TRANSPORTATION AND FREIGHT SERVICES', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('150701', 'local', '', 'Air Transportation and Freight Services', '', '', 'ANZSRC-FOR', '150700', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TRANSPORTATION AND FREIGHT SERVICES', '');
INSERT INTO tbl_terms VALUES ('150702', 'local', '', 'Rail Transportation and Freight Services', '', '', 'ANZSRC-FOR', '150700', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TRANSPORTATION AND FREIGHT SERVICES', '');
INSERT INTO tbl_terms VALUES ('150703', 'local', '', 'Road Transportation and Freight Services', '', '', 'ANZSRC-FOR', '150700', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TRANSPORTATION AND FREIGHT SERVICES', '');
INSERT INTO tbl_terms VALUES ('150799', 'local', '', 'Transportation and Freight Services not elsewhere classified', '', '', 'ANZSRC-FOR', '150700', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>TRANSPORTATION AND FREIGHT SERVICES', '');
INSERT INTO tbl_terms VALUES ('159900', 'local', '', 'OTHER COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '', '', 'ANZSRC-FOR', '150000', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('159999', 'local', '', 'Commerce, Management, Tourism and Services not  elsewhere classified', '', '', 'ANZSRC-FOR', '159900', 'pt', '', 'ANZSRC>>COMMERCE, MANAGEMENT, TOURISM AND SERVICES>>OTHER COMMERCE, MANAGEMENT, TOURISM AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('160000', 'local', '', 'STUDIES IN HUMAN SOCIETY', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('160100', 'local', '', 'ANTHROPOLOGY', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160101', 'local', '', 'Anthropology of Development', '', '', 'ANZSRC-FOR', '160100', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>ANTHROPOLOGY', '');
INSERT INTO tbl_terms VALUES ('160102', 'local', '', 'Biological (Physical) Anthropology', '', '', 'ANZSRC-FOR', '160100', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>ANTHROPOLOGY', '');
INSERT INTO tbl_terms VALUES ('160103', 'local', '', 'Linguistic Anthropology', '', '', 'ANZSRC-FOR', '160100', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>ANTHROPOLOGY', '');
INSERT INTO tbl_terms VALUES ('160104', 'local', '', 'Social and Cultural Anthropology', '', '', 'ANZSRC-FOR', '160100', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>ANTHROPOLOGY', '');
INSERT INTO tbl_terms VALUES ('160199', 'local', '', 'Anthropology not elsewhere classified', '', '', 'ANZSRC-FOR', '160100', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>ANTHROPOLOGY', '');
INSERT INTO tbl_terms VALUES ('160200', 'local', '', 'CRIMINOLOGY', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160201', 'local', '', 'Causes and Prevention of Crime', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160202', 'local', '', 'Correctional Theory, Offender Treatment and Rehabilitation', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160203', 'local', '', 'Courts and Sentencing', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160204', 'local', '', 'Criminological Theories', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160205', 'local', '', 'Police Administration, Procedures and Practice', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160206', 'local', '', 'Private Policing and Security Services', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160299', 'local', '', 'Criminology not elsewhere classified', '', '', 'ANZSRC-FOR', '160200', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>CRIMINOLOGY', '');
INSERT INTO tbl_terms VALUES ('160300', 'local', '', 'DEMOGRAPHY', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160301', 'local', '', 'Family and Household Studies', '', '', 'ANZSRC-FOR', '160300', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>DEMOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160302', 'local', '', 'Fertility', '', '', 'ANZSRC-FOR', '160300', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>DEMOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160303', 'local', '', 'Migration', '', '', 'ANZSRC-FOR', '160300', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>DEMOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160304', 'local', '', 'Mortality', '', '', 'ANZSRC-FOR', '160300', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>DEMOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160305', 'local', '', 'Population Trends and Policies', '', '', 'ANZSRC-FOR', '160300', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>DEMOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160399', 'local', '', 'Demography not elsewhere classified', '', '', 'ANZSRC-FOR', '160300', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>DEMOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160400', 'local', '', 'HUMAN GEOGRAPHY', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160401', 'local', '', 'Economic Geography', '', '', 'ANZSRC-FOR', '160400', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>HUMAN GEOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160402', 'local', '', 'Recreation, Leisure and Tourism Geography', '', '', 'ANZSRC-FOR', '160400', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>HUMAN GEOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160403', 'local', '', 'Social and Cultural Geography', '', '', 'ANZSRC-FOR', '160400', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>HUMAN GEOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160404', 'local', '', 'Urban and Regional Studies (excl. Planning)', '', '', 'ANZSRC-FOR', '160400', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>HUMAN GEOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160499', 'local', '', 'Human Geography not elsewhere classified', '', '', 'ANZSRC-FOR', '160400', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>HUMAN GEOGRAPHY', '');
INSERT INTO tbl_terms VALUES ('160500', 'local', '', 'POLICY AND ADMINISTRATION', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160501', 'local', '', 'Aboriginal and Torres Strait Islander Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160502', 'local', '', 'Arts and Cultural Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160503', 'local', '', 'Communications and Media Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160504', 'local', '', 'Crime Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160505', 'local', '', 'Economic Development Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160506', 'local', '', 'Education Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160507', 'local', '', 'Environment Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160508', 'local', '', 'Health Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160509', 'local', '', 'Public Administration', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160510', 'local', '', 'Public Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160511', 'local', '', 'Research, Science and Technology Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160512', 'local', '', 'Social Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160513', 'local', '', 'Tourism Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160514', 'local', '', 'Urban Policy', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160599', 'local', '', 'Policy and Administration not elsewhere classified', '', '', 'ANZSRC-FOR', '160500', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLICY AND ADMINISTRATION', '');
INSERT INTO tbl_terms VALUES ('160600', 'local', '', 'POLITICAL SCIENCE', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160601', 'local', '', 'Australian Government and Politics', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160602', 'local', '', 'Citizenship', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160603', 'local', '', 'Comparative Government and Politics', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160604', 'local', '', 'Defence Studies', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160605', 'local', '', 'Environmental Politics', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160606', 'local', '', 'Government and Politics of Asia and the Pacific', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160607', 'local', '', 'International Relations', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160608', 'local', '', 'New Zealand Government and Politics', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160609', 'local', '', 'Political Theory and Political Philosophy', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160699', 'local', '', 'Political Science not elsewhere classified', '', '', 'ANZSRC-FOR', '160600', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>POLITICAL SCIENCE', '');
INSERT INTO tbl_terms VALUES ('160700', 'local', '', 'SOCIAL WORK', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160701', 'local', '', 'Clinical Social Work Practice', '', '', 'ANZSRC-FOR', '160700', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIAL WORK', '');
INSERT INTO tbl_terms VALUES ('160702', 'local', '', 'Counselling, Welfare and Community Services', '', '', 'ANZSRC-FOR', '160700', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIAL WORK', '');
INSERT INTO tbl_terms VALUES ('160703', 'local', '', 'Social Program Evaluation', '', '', 'ANZSRC-FOR', '160700', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIAL WORK', '');
INSERT INTO tbl_terms VALUES ('160799', 'local', '', 'Social Work not elsewhere classified', '', '', 'ANZSRC-FOR', '160700', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIAL WORK', '');
INSERT INTO tbl_terms VALUES ('160800', 'local', '', 'SOCIOLOGY', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('160801', 'local', '', 'Applied Sociology, Program Evaluation and Social Impact  Assessment', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160802', 'local', '', 'Environmental Sociology', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('180110', 'local', '', 'Criminal Law and Procedure', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('160803', 'local', '', 'Race and Ethnic Relations', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160804', 'local', '', 'Rural Sociology', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160805', 'local', '', 'Social Change', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160806', 'local', '', 'Social Theory', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160807', 'local', '', 'Sociological Methodology and Research Methods', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160808', 'local', '', 'Sociology and Social Studies of Science and Technology', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160809', 'local', '', 'Sociology of Education', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160810', 'local', '', 'Urban Sociology and Community Studies', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('160899', 'local', '', 'Sociology not elsewhere classified', '', '', 'ANZSRC-FOR', '160800', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>SOCIOLOGY', '');
INSERT INTO tbl_terms VALUES ('169900', 'local', '', 'OTHER STUDIES IN HUMAN SOCIETY', '', '', 'ANZSRC-FOR', '160000', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('169901', 'local', '', 'Gender Specific Studies', '', '', 'ANZSRC-FOR', '169900', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>OTHER STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('169902', 'local', '', 'Studies of Aboriginal and Torres Strait Islander Society', '', '', 'ANZSRC-FOR', '169900', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>OTHER STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('169903', 'local', '', 'Studies of Asian Society', '', '', 'ANZSRC-FOR', '169900', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>OTHER STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('169904', 'local', '', 'Studies of Maori Society', '', '', 'ANZSRC-FOR', '169900', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>OTHER STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('169905', 'local', '', 'Studies of Pacific Peoples'' Societies', '', '', 'ANZSRC-FOR', '169900', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>OTHER STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('169999', 'local', '', 'Studies in Human Society not elsewhere classified', '', '', 'ANZSRC-FOR', '169900', 'pt', '', 'ANZSRC>>STUDIES IN HUMAN SOCIETY>>OTHER STUDIES IN HUMAN SOCIETY', '');
INSERT INTO tbl_terms VALUES ('170000', 'local', '', 'PSYCHOLOGY AND COGNITIVE SCIENCES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('170100', 'local', '', 'PSYCHOLOGY', '', '', 'ANZSRC-FOR', '170000', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('170101', 'local', '', 'Biological Psychology (Neuropsychology,  Psychopharmacology, Physiological Psychology)', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170102', 'local', '', 'Developmental Psychology and Ageing', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170103', 'local', '', 'Educational Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170104', 'local', '', 'Forensic Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170105', 'local', '', 'Gender Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170106', 'local', '', 'Health, Clinical and Counselling Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170107', 'local', '', 'Industrial and Organisational Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170108', 'local', '', 'Kaupapa Maori Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170109', 'local', '', 'Personality, Abilities and Assessment', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170110', 'local', '', 'Psychological Methodology, Design and Analysis', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170111', 'local', '', 'Psychology of Religion', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170112', 'local', '', 'Sensory Processes, Perception and Performance', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170113', 'local', '', 'Social and Community Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170114', 'local', '', 'Sport and Exercise Psychology', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170199', 'local', '', 'Psychology not elsewhere classified', '', '', 'ANZSRC-FOR', '170100', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>PSYCHOLOGY', '');
INSERT INTO tbl_terms VALUES ('170200', 'local', '', 'COGNITIVE SCIENCE', '', '', 'ANZSRC-FOR', '170000', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('170201', 'local', '', 'Computer Perception, Memory and Attention', '', '', 'ANZSRC-FOR', '170200', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>COGNITIVE SCIENCE', '');
INSERT INTO tbl_terms VALUES ('170202', 'local', '', 'Decision Making', '', '', 'ANZSRC-FOR', '170200', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>COGNITIVE SCIENCE', '');
INSERT INTO tbl_terms VALUES ('170203', 'local', '', 'Knowledge Representation and Machine Learning', '', '', 'ANZSRC-FOR', '170200', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>COGNITIVE SCIENCE', '');
INSERT INTO tbl_terms VALUES ('170204', 'local', '', 'Linguistic Processes (incl. Speech Production and  Comprehension)', '', '', 'ANZSRC-FOR', '170200', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>COGNITIVE SCIENCE', '');
INSERT INTO tbl_terms VALUES ('170205', 'local', '', 'Neurocognitive Patterns and Neural Networks', '', '', 'ANZSRC-FOR', '170200', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>COGNITIVE SCIENCE', '');
INSERT INTO tbl_terms VALUES ('170299', 'local', '', 'Cognitive Science not elsewhere classified', '', '', 'ANZSRC-FOR', '170200', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>COGNITIVE SCIENCE', '');
INSERT INTO tbl_terms VALUES ('179900', 'local', '', 'OTHER PSYCHOLOGY AND COGNITIVE SCIENCES', '', '', 'ANZSRC-FOR', '170000', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('179999', 'local', '', 'Psychology and Cognitive Sciences not elsewhere classified', '', '', 'ANZSRC-FOR', '179900', 'pt', '', 'ANZSRC>>PSYCHOLOGY AND COGNITIVE SCIENCES>>OTHER PSYCHOLOGY AND COGNITIVE SCIENCES', '');
INSERT INTO tbl_terms VALUES ('180000', 'local', '', 'LAW AND LEGAL STUDIES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('180100', 'local', '', 'LAW', '', '', 'ANZSRC-FOR', '180000', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('180101', 'local', '', 'Aboriginal and Torres Strait Islander Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180102', 'local', '', 'Access to Justice', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180103', 'local', '', 'Administrative Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180104', 'local', '', 'Civil Law and Procedure', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180105', 'local', '', 'Commercial and Contract Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180106', 'local', '', 'Comparative Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180107', 'local', '', 'Conflict of Laws (Private International Law)', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180108', 'local', '', 'Constitutional Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180109', 'local', '', 'Corporations and Associations Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180111', 'local', '', 'Environmental and Natural Resources Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180112', 'local', '', 'Equity and Trusts Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180113', 'local', '', 'Family Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180114', 'local', '', 'Human Rights Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180115', 'local', '', 'Intellectual Property Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180116', 'local', '', 'International Law (excl. International Trade Law)', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180117', 'local', '', 'International Trade Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180118', 'local', '', 'Labour Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180119', 'local', '', 'Law and Society', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180120', 'local', '', 'Legal Institutions (incl. Courts and Justice Systems)', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180121', 'local', '', 'Legal Practice, Lawyering and the Legal Profession', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180122', 'local', '', 'Legal Theory, Jurisprudence and Legal Interpretation', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180123', 'local', '', 'Litigation, Adjudication and Dispute Resolution', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180124', 'local', '', 'Property Law (excl. Intellectual Property Law)', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180125', 'local', '', 'Taxation Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180126', 'local', '', 'Tort Law', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180199', 'local', '', 'Law not elsewhere classified', '', '', 'ANZSRC-FOR', '180100', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>LAW', '');
INSERT INTO tbl_terms VALUES ('180200', 'local', '', 'MAORI LAW', '', '', 'ANZSRC-FOR', '180000', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('180201', 'local', '', 'Nga Tikanga Maori (Maori Customary Law)', '', '', 'ANZSRC-FOR', '180200', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>MAORI LAW', '');
INSERT INTO tbl_terms VALUES ('180202', 'local', '', 'Te Maori Whakakaere Rauemi (Maori Resource Law)', '', '', 'ANZSRC-FOR', '180200', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>MAORI LAW', '');
INSERT INTO tbl_terms VALUES ('180203', 'local', '', 'Te Tiriti O Waitangi (The Treaty of Waitangi)', '', '', 'ANZSRC-FOR', '180200', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>MAORI LAW', '');
INSERT INTO tbl_terms VALUES ('180204', 'local', '', 'Te Ture Whenua (Maori Land Law)', '', '', 'ANZSRC-FOR', '180200', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>MAORI LAW', '');
INSERT INTO tbl_terms VALUES ('180299', 'local', '', 'Maori Law not elsewhere classified', '', '', 'ANZSRC-FOR', '180200', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>MAORI LAW', '');
INSERT INTO tbl_terms VALUES ('189900', 'local', '', 'OTHER LAW AND LEGAL STUDIES', '', '', 'ANZSRC-FOR', '180000', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('189999', 'local', '', 'Law and Legal Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '189900', 'pt', '', 'ANZSRC>>LAW AND LEGAL STUDIES>>OTHER LAW AND LEGAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('190000', 'local', '', 'STUDIES IN CREATIVE ARTS AND WRITING', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('190100', 'local', '', 'ART THEORY AND CRITICISM', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('190101', 'local', '', 'Art Criticism', '', '', 'ANZSRC-FOR', '190100', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>ART THEORY AND CRITICISM', '');
INSERT INTO tbl_terms VALUES ('190102', 'local', '', 'Art History', '', '', 'ANZSRC-FOR', '190100', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>ART THEORY AND CRITICISM', '');
INSERT INTO tbl_terms VALUES ('190103', 'local', '', 'Art Theory', '', '', 'ANZSRC-FOR', '190100', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>ART THEORY AND CRITICISM', '');
INSERT INTO tbl_terms VALUES ('190104', 'local', '', 'Visual Cultures', '', '', 'ANZSRC-FOR', '190100', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>ART THEORY AND CRITICISM', '');
INSERT INTO tbl_terms VALUES ('190199', 'local', '', 'Art Theory and Criticism not elsewhere classified', '', '', 'ANZSRC-FOR', '190100', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>ART THEORY AND CRITICISM', '');
INSERT INTO tbl_terms VALUES ('190200', 'local', '', 'FILM, TELEVISION AND DIGITAL MEDIA', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('190201', 'local', '', 'Cinema Studies', '', '', 'ANZSRC-FOR', '190200', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>FILM, TELEVISION AND DIGITAL MEDIA', '');
INSERT INTO tbl_terms VALUES ('190202', 'local', '', 'Computer Gaming and Animation', '', '', 'ANZSRC-FOR', '190200', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>FILM, TELEVISION AND DIGITAL MEDIA', '');
INSERT INTO tbl_terms VALUES ('190203', 'local', '', 'Electronic Media Art', '', '', 'ANZSRC-FOR', '190200', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>FILM, TELEVISION AND DIGITAL MEDIA', '');
INSERT INTO tbl_terms VALUES ('190204', 'local', '', 'Film and Television', '', '', 'ANZSRC-FOR', '190200', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>FILM, TELEVISION AND DIGITAL MEDIA', '');
INSERT INTO tbl_terms VALUES ('190205', 'local', '', 'Interactive Media', '', '', 'ANZSRC-FOR', '190200', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>FILM, TELEVISION AND DIGITAL MEDIA', '');
INSERT INTO tbl_terms VALUES ('190299', 'local', '', 'Film, Television and Digital Media not elsewhere classified', '', '', 'ANZSRC-FOR', '190200', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>FILM, TELEVISION AND DIGITAL MEDIA', '');
INSERT INTO tbl_terms VALUES ('190300', 'local', '', 'JOURNALISM AND PROFESSIONAL WRITING', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('190301', 'local', '', 'Journalism Studies', '', '', 'ANZSRC-FOR', '190300', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>JOURNALISM AND PROFESSIONAL WRITING', '');
INSERT INTO tbl_terms VALUES ('190302', 'local', '', 'Professional Writing', '', '', 'ANZSRC-FOR', '190300', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>JOURNALISM AND PROFESSIONAL WRITING', '');
INSERT INTO tbl_terms VALUES ('190303', 'local', '', 'Technical Writing', '', '', 'ANZSRC-FOR', '190300', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>JOURNALISM AND PROFESSIONAL WRITING', '');
INSERT INTO tbl_terms VALUES ('190399', 'local', '', 'Journalism and Professional Writing not elsewhere classified', '', '', 'ANZSRC-FOR', '190300', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>JOURNALISM AND PROFESSIONAL WRITING', '');
INSERT INTO tbl_terms VALUES ('190400', 'local', '', 'PERFORMING ARTS AND CREATIVE WRITING', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('190401', 'local', '', 'Aboriginal and Torres Strait Islander Performing Arts', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190402', 'local', '', 'Creative Writing (incl. Playwriting)', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190403', 'local', '', 'Dance', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190404', 'local', '', 'Drama, Theatre and Performance Studies', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190405', 'local', '', 'Maori Performing Arts', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190406', 'local', '', 'Music Composition', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190407', 'local', '', 'Music Performance', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190408', 'local', '', 'Music Therapy', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190409', 'local', '', 'Musicology and Ethnomusicology', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190410', 'local', '', 'Pacific Peoples Performing Arts', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190499', 'local', '', 'Performing Arts and Creative Writing not elsewhere classified', '', '', 'ANZSRC-FOR', '190400', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>PERFORMING ARTS AND CREATIVE WRITING', '');
INSERT INTO tbl_terms VALUES ('190500', 'local', '', 'VISUAL ARTS AND CRAFTS', '', '', 'ANZSRC-FOR', '190000', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING', '');
INSERT INTO tbl_terms VALUES ('190501', 'local', '', 'Crafts', '', '', 'ANZSRC-FOR', '190500', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>VISUAL ARTS AND CRAFTS', '');
INSERT INTO tbl_terms VALUES ('190502', 'local', '', 'Fine Arts (incl. Sculpture and Painting)', '', '', 'ANZSRC-FOR', '190500', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>VISUAL ARTS AND CRAFTS', '');
INSERT INTO tbl_terms VALUES ('190503', 'local', '', 'Lens-based Practice', '', '', 'ANZSRC-FOR', '190500', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>VISUAL ARTS AND CRAFTS', '');
INSERT INTO tbl_terms VALUES ('190504', 'local', '', 'Performance and Installation Art', '', '', 'ANZSRC-FOR', '190500', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>VISUAL ARTS AND CRAFTS', '');
INSERT INTO tbl_terms VALUES ('190599', 'local', '', 'Visual Arts and Crafts not elsewhere classified', '', '', 'ANZSRC-FOR', '190500', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>VISUAL ARTS AND CRAFTS', '');
INSERT INTO tbl_terms VALUES ('190999', 'local', '', 'OTHER STUDIES IN CREATIVE ARTS AND WRITING', '', '', 'ANZSRC-FOR', '190900', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>', '');
INSERT INTO tbl_terms VALUES ('199999', 'local', '', 'Studies in the Creative Arts and Writing not elsewhere classified', '', '', 'ANZSRC-FOR', '199900', 'pt', '', 'ANZSRC>>STUDIES IN CREATIVE ARTS AND WRITING>>', '');
INSERT INTO tbl_terms VALUES ('200000', 'local', '', 'LANGUAGES, COMMUNICATION AND CULTURE', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('200100', 'local', '', 'COMMUNICATION AND MEDIA STUDIES', '', '', 'ANZSRC-FOR', '200000', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('200101', 'local', '', 'Communication Studies', '', '', 'ANZSRC-FOR', '200100', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>COMMUNICATION AND MEDIA STUDIES', '');
INSERT INTO tbl_terms VALUES ('200102', 'local', '', 'Communication Technology and Digital Media Studies', '', '', 'ANZSRC-FOR', '200100', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>COMMUNICATION AND MEDIA STUDIES', '');
INSERT INTO tbl_terms VALUES ('200103', 'local', '', 'International and Development Communication', '', '', 'ANZSRC-FOR', '200100', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>COMMUNICATION AND MEDIA STUDIES', '');
INSERT INTO tbl_terms VALUES ('200104', 'local', '', 'Media Studies', '', '', 'ANZSRC-FOR', '200100', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>COMMUNICATION AND MEDIA STUDIES', '');
INSERT INTO tbl_terms VALUES ('200105', 'local', '', 'Organisational, Interpersonal and Intercultural Communication', '', '', 'ANZSRC-FOR', '200100', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>COMMUNICATION AND MEDIA STUDIES', '');
INSERT INTO tbl_terms VALUES ('200199', 'local', '', 'Communication and Media Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '200100', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>COMMUNICATION AND MEDIA STUDIES', '');
INSERT INTO tbl_terms VALUES ('200200', 'local', '', 'CULTURAL STUDIES', '', '', 'ANZSRC-FOR', '200000', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('200201', 'local', '', 'Aboriginal and Torres Strait Islander Cultural Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200202', 'local', '', 'Asian Cultural Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200203', 'local', '', 'Consumption and Everyday Life', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200204', 'local', '', 'Cultural Theory', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200205', 'local', '', 'Culture, Gender, Sexuality', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200206', 'local', '', 'Globalisation and Culture', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200207', 'local', '', 'Maori Cultural Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200208', 'local', '', 'Migrant Cultural Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200209', 'local', '', 'Multicultural, Intercultural and Cross-cultural Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200210', 'local', '', 'Pacific Cultural Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200211', 'local', '', 'Postcolonial Studies', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200212', 'local', '', 'Screen and Media Culture', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200299', 'local', '', 'Cultural Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '200200', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>CULTURAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('200300', 'local', '', 'LANGUAGE STUDIES', '', '', 'ANZSRC-FOR', '200000', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('200301', 'local', '', 'Early English Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200302', 'local', '', 'English Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200303', 'local', '', 'English as a Second Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200304', 'local', '', 'Central and Eastern European Languages (incl. Russian)', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200305', 'local', '', 'Latin and Classical Greek Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200306', 'local', '', 'French Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200307', 'local', '', 'German Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200308', 'local', '', 'Iberian Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200309', 'local', '', 'Italian Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200310', 'local', '', 'Other European Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200311', 'local', '', 'Chinese Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200312', 'local', '', 'Japanese Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200313', 'local', '', 'Indonesian Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200314', 'local', '', 'South-East Asian Languages (excl. Indonesian)', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200315', 'local', '', 'Indian Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200316', 'local', '', 'Korean Language', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200317', 'local', '', 'Other Asian Languages (excl. South-East Asian)', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200318', 'local', '', 'Middle Eastern Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200319', 'local', '', 'Aboriginal and Torres Strait Islander Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200320', 'local', '', 'Pacific Languages', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200321', 'local', '', 'Te Reo Maori (Maori Language)', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200322', 'local', '', 'Comparative Language Studies', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200323', 'local', '', 'Translation and Interpretation Studies', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200399', 'local', '', 'Language Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '200300', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LANGUAGE STUDIES', '');
INSERT INTO tbl_terms VALUES ('200400', 'local', '', 'LINGUISTICS', '', '', 'ANZSRC-FOR', '200000', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('200401', 'local', '', 'Applied Linguistics and Educational Linguistics', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200402', 'local', '', 'Computational Linguistics', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200403', 'local', '', 'Discourse and Pragmatics', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200404', 'local', '', 'Laboratory Phonetics and Speech Science', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200405', 'local', '', 'Language in Culture and Society (Sociolinguistics)', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200406', 'local', '', 'Language in Time and Space (incl. Historical Linguistics, Dialectology)', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200407', 'local', '', 'Lexicography', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200408', 'local', '', 'Linguistic Structures (incl. Grammar, Phonology, Lexicon,  Semantics)', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200499', 'local', '', 'Linguistics not elsewhere classified', '', '', 'ANZSRC-FOR', '200400', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LINGUISTICS', '');
INSERT INTO tbl_terms VALUES ('200500', 'local', '', 'LITERARY STUDIES', '', '', 'ANZSRC-FOR', '200000', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('200501', 'local', '', 'Aboriginal and Torres Strait Islander Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200502', 'local', '', 'Australian Literature (excl. Aboriginal and Torres Strait Islander Literature)', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200503', 'local', '', 'British and Irish Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200504', 'local', '', 'Maori Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200505', 'local', '', 'New Zealand Literature (excl. Maori Literature)', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200506', 'local', '', 'North American Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200507', 'local', '', 'Pacific Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200508', 'local', '', 'Other Literatures in English', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200509', 'local', '', 'Central and Eastern European Literature (incl. Russian)', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200510', 'local', '', 'Latin and Classical Greek Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200511', 'local', '', 'Literature in French', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200512', 'local', '', 'Literature in German', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200513', 'local', '', 'Literature in Italian', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200514', 'local', '', 'Literature in Spanish and Portuguese', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200515', 'local', '', 'Other European Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200516', 'local', '', 'Indonesian Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200517', 'local', '', 'Literature in Chinese', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200518', 'local', '', 'Literature in Japanese', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200519', 'local', '', 'South-East Asian Literature (excl. Indonesian)', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200520', 'local', '', 'Indian Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200521', 'local', '', 'Korean Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200522', 'local', '', 'Other Asian Literature (excl. South-East Asian)', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200523', 'local', '', 'Middle Eastern Literature', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200524', 'local', '', 'Comparative Literature Studies', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200525', 'local', '', 'Literary Theory', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200526', 'local', '', 'Stylistics and Textual Analysis', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('200599', 'local', '', 'Literary Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '200500', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>LITERARY STUDIES', '');
INSERT INTO tbl_terms VALUES ('209900', 'local', '', 'OTHER LANGUAGE, COMMUNICATION AND CULTURE', '', '', 'ANZSRC-FOR', '200000', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('820100', 'local', '', 'FORESTRY', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('209999', 'local', '', 'Language, Communication and Culture not elsewhere classified', '', '', 'ANZSRC-FOR', '209900', 'pt', '', 'ANZSRC>>LANGUAGES, COMMUNICATION AND CULTURE>>OTHER LANGUAGE, COMMUNICATION AND CULTURE', '');
INSERT INTO tbl_terms VALUES ('210000', 'local', '', 'HISTORY AND ARCHAEOLOGY', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('210100', 'local', '', 'ARCHAEOLOGY', '', '', 'ANZSRC-FOR', '210000', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210101', 'local', '', 'Aboriginal and Torres Strait Islander Archaeology', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210102', 'local', '', 'Archaeological Science', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210103', 'local', '', 'Archaeology of Asia, Africa and the Americas', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210104', 'local', '', 'Archaeology of Australia (excl. Aboriginal and Torres Strait  Islander)', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210105', 'local', '', 'Archaeology of Europe, the Mediterranean and the Levant', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210106', 'local', '', 'Archaeology of New Guinea and Pacific Islands (excl. New  Zealand)', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210107', 'local', '', 'Archaeology of New Zealand (excl. Maori)', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210108', 'local', '', 'Historical Archaeology (incl. Industrial Archaeology)', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210109', 'local', '', 'Maori Archaeology', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210110', 'local', '', 'Maritime Archaeology', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210199', 'local', '', 'Archaeology not elsewhere classified', '', '', 'ANZSRC-FOR', '210100', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210200', 'local', '', 'CURATORIAL AND RELATED STUDIES', '', '', 'ANZSRC-FOR', '210000', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210201', 'local', '', 'Archival, Repository and Related Studies', '', '', 'ANZSRC-FOR', '210200', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>CURATORIAL AND RELATED STUDIES', '');
INSERT INTO tbl_terms VALUES ('210202', 'local', '', 'Heritage and Cultural Conservation', '', '', 'ANZSRC-FOR', '210200', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>CURATORIAL AND RELATED STUDIES', '');
INSERT INTO tbl_terms VALUES ('210203', 'local', '', 'Materials Conservation', '', '', 'ANZSRC-FOR', '210200', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>CURATORIAL AND RELATED STUDIES', '');
INSERT INTO tbl_terms VALUES ('210204', 'local', '', 'Museum Studies', '', '', 'ANZSRC-FOR', '210200', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>CURATORIAL AND RELATED STUDIES', '');
INSERT INTO tbl_terms VALUES ('210299', 'local', '', 'Curatorial and Related Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '210200', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>CURATORIAL AND RELATED STUDIES', '');
INSERT INTO tbl_terms VALUES ('210300', 'local', '', 'HISTORICAL STUDIES', '', '', 'ANZSRC-FOR', '210000', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('210301', 'local', '', 'Aboriginal and Torres Strait Islander History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210302', 'local', '', 'Asian History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210303', 'local', '', 'Australian History (excl. Aboriginal and Torres Strait  Islander History)', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210304', 'local', '', 'Biography', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210305', 'local', '', 'British History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210306', 'local', '', 'Classical Greek and Roman History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210307', 'local', '', 'European History (excl. British, Classical Greek and Roman)', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210308', 'local', '', 'Latin American History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210309', 'local', '', 'Maori History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210310', 'local', '', 'Middle Eastern and African History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210311', 'local', '', 'New Zealand History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210312', 'local', '', 'North American History', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210313', 'local', '', 'Pacific History (excl. New Zealand and Maori)', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('210399', 'local', '', 'Historical Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '210300', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>HISTORICAL STUDIES', '');
INSERT INTO tbl_terms VALUES ('219900', 'local', '', 'OTHER HISTORY AND ARCHAEOLOGY', '', '', 'ANZSRC-FOR', '210000', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('219999', 'local', '', 'History and Archaeology not elsewhere classified', '', '', 'ANZSRC-FOR', '219900', 'pt', '', 'ANZSRC>>HISTORY AND ARCHAEOLOGY>>OTHER HISTORY AND ARCHAEOLOGY', '');
INSERT INTO tbl_terms VALUES ('220000', 'local', '', 'PHILOSOPHY AND RELIGIOUS STUDIES', '', '', 'ANZSRC-FOR', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('220100', 'local', '', 'APPLIED ETHICS', '', '', 'ANZSRC-FOR', '220000', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES', '');
INSERT INTO tbl_terms VALUES ('220101', 'local', '', 'Bioethics (human and animal)', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220102', 'local', '', 'Business Ethics', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220103', 'local', '', 'Ethical Use of New Technology (e.g. Nanotechnology, Biotechnology)', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220104', 'local', '', 'Human Rights and Justice Issues', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220105', 'local', '', 'Legal Ethics', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220106', 'local', '', 'Medical Ethics', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220107', 'local', '', 'Professional Ethics (incl. police and research ethics)', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220199', 'local', '', 'Applied Ethics not elsewhere classified', '', '', 'ANZSRC-FOR', '220100', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>APPLIED ETHICS', '');
INSERT INTO tbl_terms VALUES ('220200', 'local', '', 'HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '', '', 'ANZSRC-FOR', '220000', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES', '');
INSERT INTO tbl_terms VALUES ('220201', 'local', '', 'Business and Labour History', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220202', 'local', '', 'History and Philosophy of Education', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220203', 'local', '', 'History and Philosophy of Engineering and Technology', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('910104', 'local', '', 'Exchange Rates', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('220204', 'local', '', 'History and Philosophy of Law and Justice', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220205', 'local', '', 'History and Philosophy of Medicine', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220206', 'local', '', 'History and Philosophy of Science (incl. Non-historical Philosophy of Science)', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220207', 'local', '', 'History and Philosophy of the Humanities', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220208', 'local', '', 'History and Philosophy of the Social Sciences', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220209', 'local', '', 'History of Ideas', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220210', 'local', '', 'History of Philosophy', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220299', 'local', '', 'History and Philosophy of Specific Fields not elsewhere classified', '', '', 'ANZSRC-FOR', '220200', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>HISTORY AND PHILOSOPHY OF SPECIFIC FIELDS', '');
INSERT INTO tbl_terms VALUES ('220300', 'local', '', 'PHILOSOPHY', '', '', 'ANZSRC-FOR', '220000', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES', '');
INSERT INTO tbl_terms VALUES ('220301', 'local', '', 'Aesthetics', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220302', 'local', '', 'Decision Theory', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220303', 'local', '', 'Environmental Philosophy', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220304', 'local', '', 'Epistemology', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220305', 'local', '', 'Ethical Theory', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220306', 'local', '', 'Feminist Theory', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220307', 'local', '', 'Hermeneutic and Critical Theory', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220308', 'local', '', 'Logic', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220309', 'local', '', 'Metaphysics', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220310', 'local', '', 'Phenomenology', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220311', 'local', '', 'Philosophical Psychology (incl. Moral Psychology and  Philosophy of Action)', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220312', 'local', '', 'Philosophy of Cognition', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220313', 'local', '', 'Philosophy of Language', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220314', 'local', '', 'Philosophy of Mind (excl. Cognition)', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220315', 'local', '', 'Philosophy of Religion', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220316', 'local', '', 'Philosophy of Specific Cultures (incl. Comparative  Philosophy)', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220317', 'local', '', 'Poststructuralism', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220318', 'local', '', 'Psychoanalytic Philosophy', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220319', 'local', '', 'Social Philosophy', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220399', 'local', '', 'Philosophy not elsewhere classified', '', '', 'ANZSRC-FOR', '220300', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>PHILOSOPHY', '');
INSERT INTO tbl_terms VALUES ('220400', 'local', '', 'RELIGION AND RELIGIOUS TRADITIONS', '', '', 'ANZSRC-FOR', '220000', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES', '');
INSERT INTO tbl_terms VALUES ('220401', 'local', '', 'Christian Studies (incl. Biblical Studies and Church History)', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220402', 'local', '', 'Comparative Religious Studies', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220403', 'local', '', 'Islamic Studies', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220404', 'local', '', 'Jewish Studies', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220405', 'local', '', 'Religion and Society', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220406', 'local', '', 'Studies in Eastern Religious Traditions', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220407', 'local', '', 'Studies in Religious Traditions (excl. Eastern, Jewish,  Christian and Islamic Traditions)', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('220499', 'local', '', 'Religion and Religious Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '220400', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>RELIGION AND RELIGIOUS TRADITIONS', '');
INSERT INTO tbl_terms VALUES ('229900', 'local', '', 'OTHER PHILOSOPHY AND RELIGIOUS STUDIES', '', '', 'ANZSRC-FOR', '220000', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES', '');
INSERT INTO tbl_terms VALUES ('229999', 'local', '', 'Philosophy and Religious Studies not elsewhere classified', '', '', 'ANZSRC-FOR', '229900', 'pt', '', 'ANZSRC>>PHILOSOPHY AND RELIGIOUS STUDIES>>OTHER PHILOSOPHY AND RELIGIOUS STUDIES', '');
INSERT INTO tbl_terms VALUES ('810000', 'local', '', 'DEFENCE', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('810100', 'local', '', 'DEFENCE', '', '', 'ANZSRC-SEO', '810000', 'pt', '', 'ANZSRC>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810101', 'local', '', 'Air Force', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810102', 'local', '', 'Army', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810103', 'local', '', 'Command, Control and Communications', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810104', 'local', '', 'Emerging Defence Technologies', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810105', 'local', '', 'Intelligence', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810106', 'local', '', 'Logistics', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810107', 'local', '', 'National Security', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810108', 'local', '', 'Navy', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810109', 'local', '', 'Personnel', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('810199', 'local', '', 'Defence not elsewhere classified', '', '', 'ANZSRC-SEO', '810100', 'pt', '', 'ANZSRC>>DEFENCE>>DEFENCE', '');
INSERT INTO tbl_terms VALUES ('820000', 'local', '', 'PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('820101', 'local', '', 'Hardwood Plantations', '', '', 'ANZSRC-SEO', '820100', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>FORESTRY', '');
INSERT INTO tbl_terms VALUES ('820102', 'local', '', 'Harvesting and Transport of Forest Products', '', '', 'ANZSRC-SEO', '820100', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>FORESTRY', '');
INSERT INTO tbl_terms VALUES ('820103', 'local', '', 'Integration of Farm and Forestry', '', '', 'ANZSRC-SEO', '820100', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>FORESTRY', '');
INSERT INTO tbl_terms VALUES ('820104', 'local', '', 'Native Forests', '', '', 'ANZSRC-SEO', '820100', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>FORESTRY', '');
INSERT INTO tbl_terms VALUES ('820105', 'local', '', 'Softwood Plantations', '', '', 'ANZSRC-SEO', '820100', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>FORESTRY', '');
INSERT INTO tbl_terms VALUES ('820199', 'local', '', 'Forestry not elsewhere classified', '', '', 'ANZSRC-SEO', '820100', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>FORESTRY', '');
INSERT INTO tbl_terms VALUES ('820200', 'local', '', 'HORTICULTURAL CROPS', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820201', 'local', '', 'Almonds', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820202', 'local', '', 'Berry Fruit (excl. Kiwifruit)', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820203', 'local', '', 'Citrus Fruit', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820204', 'local', '', 'Hops', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820205', 'local', '', 'Kiwifruit', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820206', 'local', '', 'Macadamias', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820207', 'local', '', 'Mushrooms and Truffles', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820208', 'local', '', 'Olives', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820209', 'local', '', 'Ornamentals, Natives, Flowers and Nursery Plants', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820210', 'local', '', 'Pome Fruit, Pip Fruit', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820211', 'local', '', 'Stone Fruit', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820212', 'local', '', 'Table Grapes', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820213', 'local', '', 'Tree Nuts (excl. Almonds and Macadamias)', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820214', 'local', '', 'Tropical Fruit', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820215', 'local', '', 'Vegetables', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820299', 'local', '', 'Horticultural Crops not elsewhere classified', '', '', 'ANZSRC-SEO', '820200', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HORTICULTURAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820300', 'local', '', 'INDUSTRIAL CROPS', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820301', 'local', '', 'Cotton', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820302', 'local', '', 'Essential Oil Crops (e.g. Tea Tree, Eucalyptus, Lavender, Peppermint, Boronia, Sandlewood)', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820303', 'local', '', 'Plant Extract Crops (e.g. Pyrethrum, Jojoba)', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820304', 'local', '', 'Sugar', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820305', 'local', '', 'Tobacco', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820306', 'local', '', 'Wine Grapes', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820399', 'local', '', 'Industrial Crops not elsewhere classified', '', '', 'ANZSRC-SEO', '820300', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>INDUSTRIAL CROPS', '');
INSERT INTO tbl_terms VALUES ('820400', 'local', '', 'SUMMER GRAINS AND OILSEEDS', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820401', 'local', '', 'Maize', '', '', 'ANZSRC-SEO', '820400', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>SUMMER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820402', 'local', '', 'Rice', '', '', 'ANZSRC-SEO', '820400', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>SUMMER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820403', 'local', '', 'Safflower Seed', '', '', 'ANZSRC-SEO', '820400', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>SUMMER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820404', 'local', '', 'Sorghum', '', '', 'ANZSRC-SEO', '820400', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>SUMMER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820405', 'local', '', 'Soybeans', '', '', 'ANZSRC-SEO', '820400', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>SUMMER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820499', 'local', '', 'Summer Grains and Oilseeds not elsewhere classified', '', '', 'ANZSRC-SEO', '820400', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>SUMMER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820500', 'local', '', 'WINTER GRAINS AND OILSEEDS', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820501', 'local', '', 'Barley', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820502', 'local', '', 'Canola', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820503', 'local', '', 'Grain Legumes', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820504', 'local', '', 'Linseed', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820505', 'local', '', 'Lupins', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820506', 'local', '', 'Oats', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820507', 'local', '', 'Wheat', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820599', 'local', '', 'Winter Grains and Oilseeds not elsewhere classified', '', '', 'ANZSRC-SEO', '820500', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>WINTER GRAINS AND OILSEEDS', '');
INSERT INTO tbl_terms VALUES ('820600', 'local', '', 'HARVESTING AND PACKING OF PLANT PRODUCTS', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820601', 'local', '', 'Cotton Lint and Cotton Seed', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('850203', 'local', '', 'Oil and Gas Extraction', '', '', 'ANZSRC-SEO', '850200', 'pt', '', 'ANZSRC>>ENERGY>>MINING AND EXTRACTION OF ENERGY RESOURCES', '');
INSERT INTO tbl_terms VALUES ('820602', 'local', '', 'Fresh Fruit and Vegetables (Post Harvest)', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820603', 'local', '', 'Sugar Cane (Cut for Crushing)', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820604', 'local', '', 'Tobacco Leaf', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820605', 'local', '', 'Unprocessed Grains', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820606', 'local', '', 'Unprocessed Industrial Crops (excl. Sugar, Tobacco and Cotton)', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820607', 'local', '', 'Unprocessed Oilseeds', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('820699', 'local', '', 'Harvesting and Packing of Plant Products not elsewhere classified', '', '', 'ANZSRC-SEO', '820600', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>HARVESTING AND PACKING OF PLANT PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('829800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('829801', 'local', '', 'Management of Gaseous Waste from Plant Production (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '829800', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('829802', 'local', '', 'Management of Greenhouse Gas Emissions from Plant Production', '', '', 'ANZSRC-SEO', '829800', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('829803', 'local', '', 'Management of Liquid Waste from Plant Production (excl. Water)', '', '', 'ANZSRC-SEO', '829800', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('829804', 'local', '', 'Management of Solid Waste from Plant Production', '', '', 'ANZSRC-SEO', '829800', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('829805', 'local', '', 'Management of Water Consumption by Plant Production', '', '', 'ANZSRC-SEO', '829800', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('829899', 'local', '', 'Environmentally Sustainable Plant Production not elsewhere classified', '', '', 'ANZSRC-SEO', '829800', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE PLANT  PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('829900', 'local', '', 'OTHER PLANT PRODUCTION AND PLANT PRIMARY  PRODUCTS', '', '', 'ANZSRC-SEO', '820000', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('829901', 'local', '', 'Forest Product Traceability and Quality Assurance', '', '', 'ANZSRC-SEO', '829900', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>OTHER PLANT PRODUCTION AND PLANT PRIMARY  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('829902', 'local', '', 'Plant Product, Traceability and Quality Assurance (excl. Forest Products)', '', '', 'ANZSRC-SEO', '829900', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>OTHER PLANT PRODUCTION AND PLANT PRIMARY  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('829999', 'local', '', 'Plant Production and Plant Primary Products not elsewhere classified', '', '', 'ANZSRC-SEO', '829900', 'pt', '', 'ANZSRC>>PLANT PRODUCTION AND PLANT PRIMARY PRODUCTS>>OTHER PLANT PRODUCTION AND PLANT PRIMARY  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830000', 'local', '', 'ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('830100', 'local', '', 'FISHERIES - AQUACULTURE', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830101', 'local', '', 'Aquaculture Crustaceans (excl. Rock Lobster and Prawns)', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830102', 'local', '', 'Aquaculture Fin Fish (excl. Tuna)', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830103', 'local', '', 'Aquaculture Molluscs (excl. Oysters)', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830104', 'local', '', 'Aquaculture Oysters', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830105', 'local', '', 'Aquaculture Prawns', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830106', 'local', '', 'Aquaculture Rock Lobster', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830107', 'local', '', 'Aquaculture Tuna', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830199', 'local', '', 'Fisheries - Aquaculture not elsewhere classified', '', '', 'ANZSRC-SEO', '830100', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - AQUACULTURE', '');
INSERT INTO tbl_terms VALUES ('830200', 'local', '', 'FISHERIES - WILD CAUGHT', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830201', 'local', '', 'Fisheries - Recreational', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830202', 'local', '', 'Wild Caught Crustaceans (excl. Rock Lobster and Prawns)', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830203', 'local', '', 'Wild Caught Edible Molluscs', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830204', 'local', '', 'Wild Caught Fin Fish (excl. Tuna)', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830205', 'local', '', 'Wild Caught Rock Lobster', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830206', 'local', '', 'Wild Caught Prawns', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830207', 'local', '', 'Wild Caught Tuna', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('970113', 'local', '', 'Expanding Knowledge in Education', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('830299', 'local', '', 'Fisheries - Wild Caught not elsewhere classified', '', '', 'ANZSRC-SEO', '830200', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>FISHERIES - WILD CAUGHT', '');
INSERT INTO tbl_terms VALUES ('830300', 'local', '', 'LIVESTOCK RAISING', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830301', 'local', '', 'Beef Cattle', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830302', 'local', '', 'Dairy Cattle', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830303', 'local', '', 'Deer', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830304', 'local', '', 'Goats', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830305', 'local', '', 'Game Livestock (e.g. Kangaroos, Wallabies, Camels, Buffaloes, Possums)', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830306', 'local', '', 'Horses', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830307', 'local', '', 'Minor Livestock (e.g. Alpacas, Ostriches, Crocodiles, Farmed Rabbits)', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830308', 'local', '', 'Pigs', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830309', 'local', '', 'Poultry', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830310', 'local', '', 'Sheep - Meat', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830311', 'local', '', 'Sheep - Wool', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830399', 'local', '', 'Livestock Raising not elsewhere classified', '', '', 'ANZSRC-SEO', '830300', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>LIVESTOCK RAISING', '');
INSERT INTO tbl_terms VALUES ('830400', 'local', '', 'PASTURE, BROWSE AND FODDER CROPS', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830401', 'local', '', 'Browse Crops', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830402', 'local', '', 'Lucerne', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830403', 'local', '', 'Native and Residual Pastures', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830404', 'local', '', 'Non-Cereal Crops for Hay', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830405', 'local', '', 'Non-Cereal Crops for Silage/Green Feed', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830406', 'local', '', 'Sown Pastures (excl. Lucerne)', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830499', 'local', '', 'Pasture, Browse and Fodder Crops not elsewhere classified', '', '', 'ANZSRC-SEO', '830400', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PASTURE, BROWSE AND FODDER CROPS', '');
INSERT INTO tbl_terms VALUES ('830500', 'local', '', 'PRIMARY ANIMAL PRODUCTS', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830501', 'local', '', 'Eggs', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830502', 'local', '', 'Honey', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830503', 'local', '', 'Live Animals', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830504', 'local', '', 'Pearls', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830505', 'local', '', 'Raw Wool', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830506', 'local', '', 'Unprocessed or Minimally Processed Fish', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830507', 'local', '', 'Unprocessed or Minimally Processed Milk', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('830599', 'local', '', 'Primary Animal Products not elsewhere classified', '', '', 'ANZSRC-SEO', '830500', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>PRIMARY ANIMAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('839800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('839801', 'local', '', 'Management of Gaseous Waste from Animal Production  (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '839800', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('839802', 'local', '', 'Management of Greenhouse Gas Emissions from Animal Production', '', '', 'ANZSRC-SEO', '839800', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('839803', 'local', '', 'Management of Liquid Waste from Animal Production (excl. Water)', '', '', 'ANZSRC-SEO', '839800', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('839804', 'local', '', 'Management of Solid Waste from Animal Production', '', '', 'ANZSRC-SEO', '839800', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('839805', 'local', '', 'Management of Water Consumption by Animal Production', '', '', 'ANZSRC-SEO', '839800', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('839899', 'local', '', 'Environmentally Sustainable Animal Production not elsewhere classified', '', '', 'ANZSRC-SEO', '839800', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>ENVIRONMENTALLY SUSTAINABLE ANIMAL PRODUCTION', '');
INSERT INTO tbl_terms VALUES ('839900', 'local', '', 'OTHER ANIMAL PRODUCTION AND ANIMAL  PRIMARY PRODUCTS', '', '', 'ANZSRC-SEO', '830000', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('839901', 'local', '', 'Animal Welfare', '', '', 'ANZSRC-SEO', '839900', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>OTHER ANIMAL PRODUCTION AND ANIMAL  PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('839902', 'local', '', 'Fish Product Traceability and Quality Assurance', '', '', 'ANZSRC-SEO', '839900', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>OTHER ANIMAL PRODUCTION AND ANIMAL  PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('839903', 'local', '', 'Livestock Product Traceability and Quality Assurance', '', '', 'ANZSRC-SEO', '839900', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>OTHER ANIMAL PRODUCTION AND ANIMAL  PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('839999', 'local', '', 'Animal Production and Animal Primary Products not elsewhere classified', '', '', 'ANZSRC-SEO', '839900', 'pt', '', 'ANZSRC>>ANIMAL PRODUCTION AND ANIMAL PRIMARY PRODUCTS>>OTHER ANIMAL PRODUCTION AND ANIMAL  PRIMARY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('840000', 'local', '', 'MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('840100', 'local', '', 'MINERAL EXPLORATION', '', '', 'ANZSRC-SEO', '840000', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '');
INSERT INTO tbl_terms VALUES ('840101', 'local', '', 'Aluminium Ore Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840102', 'local', '', 'Copper Ore Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840103', 'local', '', 'Diamond Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840104', 'local', '', 'Iron Ore Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840105', 'local', '', 'Precious (Noble) Metal Ore Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840106', 'local', '', 'Stone and Clay Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840107', 'local', '', 'Titanium Minerals, Zircon, and Rare Earth Metal Ore (e.g. Monazite) Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840108', 'local', '', 'Zinc Ore Exploration', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840199', 'local', '', 'Mineral Exploration not elsewhere classified', '', '', 'ANZSRC-SEO', '840100', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>MINERAL EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('840200', 'local', '', 'PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '', '', 'ANZSRC-SEO', '840000', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '');
INSERT INTO tbl_terms VALUES ('840201', 'local', '', 'Aluminium Ore Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840202', 'local', '', 'Copper Ore Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840203', 'local', '', 'Diamond Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840204', 'local', '', 'Iron Ore Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840205', 'local', '', 'Precious (Noble) Metal Ore Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840206', 'local', '', 'Stone and Clay Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840207', 'local', '', 'Titanium Minerals, Zircon, and Rare Earth Metal Ore (e.g. Monazite) Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840208', 'local', '', 'Zinc Ore Exploration', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840299', 'local', '', 'Mineral Exploration not elsewhere classified', '', '', 'ANZSRC-SEO', '840200', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>PRIMARY MINING AND EXTRACTION OF MINERAL RESOURCES', '');
INSERT INTO tbl_terms VALUES ('840300', 'local', '', 'FIRST STAGE TREATMENT OF ORES AND MINERALS', '', '', 'ANZSRC-SEO', '840000', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '');
INSERT INTO tbl_terms VALUES ('840301', 'local', '', 'Alumina Production', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('840302', 'local', '', 'Beneficiation of Bauxite and Aluminium Ores (excl. Alumina Production)', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('840303', 'local', '', 'Beneficiation or Dressing of Iron Ores', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('840304', 'local', '', 'Beneficiation or Dressing of Non-Metallic Minerals (incl. Diamonds)', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('840305', 'local', '', 'Concentrating Processes of Base Metal Ores (excl. Aluminium and Iron Ores)', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('840306', 'local', '', 'Production of Unrefined Precious Metal Ingots and  Concentrates', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('840399', 'local', '', 'First Stage Treatment of Ores and Minerals not elsewhere  classified', '', '', 'ANZSRC-SEO', '840300', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>FIRST STAGE TREATMENT OF ORES AND MINERALS', '');
INSERT INTO tbl_terms VALUES ('849800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '', '', 'ANZSRC-SEO', '840000', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '');
INSERT INTO tbl_terms VALUES ('849801', 'local', '', 'Management of Gaseous Waste From Mineral Resource Activities (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '849800', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('849802', 'local', '', 'Management of Greenhouse Gas Emissions from Mineral Resource Activities', '', '', 'ANZSRC-SEO', '849800', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('849803', 'local', '', 'Management of Liquid Waste from Mineral Resource Activities (excl. Water)', '', '', 'ANZSRC-SEO', '849800', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('849804', 'local', '', 'Management of Solid Waste from Mineral Resource  Activities', '', '', 'ANZSRC-SEO', '849800', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('849805', 'local', '', 'Management of Water Consumption by Mineral Resource  Activities', '', '', 'ANZSRC-SEO', '849800', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('849899', 'local', '', 'Environmentally Sustainable Mineral Resource Activities not elsewhere classified', '', '', 'ANZSRC-SEO', '849800', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>ENVIRONMENTALLY SUSTAINABLE MINERAL RESOURCE ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('849900', 'local', '', 'OTHER MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '', '', 'ANZSRC-SEO', '840000', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '');
INSERT INTO tbl_terms VALUES ('849999', 'local', '', 'Mineral Resources (excl. Energy Resources) not elsewhere classified', '', '', 'ANZSRC-SEO', '849900', 'pt', '', 'ANZSRC>>MINERAL RESOURCES (EXCL. ENERGY RESOURCES)>>OTHER MINERAL RESOURCES (EXCL. ENERGY RESOURCES)', '');
INSERT INTO tbl_terms VALUES ('850000', 'local', '', 'ENERGY', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('850100', 'local', '', 'ENERGY EXPLORATION', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850101', 'local', '', 'Coal Exploration', '', '', 'ANZSRC-SEO', '850100', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('850102', 'local', '', 'Geothermal Exploration', '', '', 'ANZSRC-SEO', '850100', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('850103', 'local', '', 'Oil and Gas Exploration', '', '', 'ANZSRC-SEO', '850100', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('850104', 'local', '', 'Oil Shale and Tar Sands Exploration', '', '', 'ANZSRC-SEO', '850100', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('850105', 'local', '', 'Uranium Exploration', '', '', 'ANZSRC-SEO', '850100', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('850199', 'local', '', 'Energy Exploration not elsewhere classified', '', '', 'ANZSRC-SEO', '850100', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY EXPLORATION', '');
INSERT INTO tbl_terms VALUES ('850200', 'local', '', 'MINING AND EXTRACTION OF ENERGY RESOURCES', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850201', 'local', '', 'Coal Mining and Extraction', '', '', 'ANZSRC-SEO', '850200', 'pt', '', 'ANZSRC>>ENERGY>>MINING AND EXTRACTION OF ENERGY RESOURCES', '');
INSERT INTO tbl_terms VALUES ('850202', 'local', '', 'Geothermal Energy Extraction', '', '', 'ANZSRC-SEO', '850200', 'pt', '', 'ANZSRC>>ENERGY>>MINING AND EXTRACTION OF ENERGY RESOURCES', '');
INSERT INTO tbl_terms VALUES ('850204', 'local', '', 'Oil Shale and Tar Sands Mining and Extraction', '', '', 'ANZSRC-SEO', '850200', 'pt', '', 'ANZSRC>>ENERGY>>MINING AND EXTRACTION OF ENERGY RESOURCES', '');
INSERT INTO tbl_terms VALUES ('850205', 'local', '', 'Uranium Mining and Extraction', '', '', 'ANZSRC-SEO', '850200', 'pt', '', 'ANZSRC>>ENERGY>>MINING AND EXTRACTION OF ENERGY RESOURCES', '');
INSERT INTO tbl_terms VALUES ('850299', 'local', '', 'Mining and Extraction of Energy Resources not elsewhere classified', '', '', 'ANZSRC-SEO', '850200', 'pt', '', 'ANZSRC>>ENERGY>>MINING AND EXTRACTION OF ENERGY RESOURCES', '');
INSERT INTO tbl_terms VALUES ('850300', 'local', '', 'PREPARATION AND PRODUCTION OF ENERGY SOURCES', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850301', 'local', '', 'Hydrogen Production from Fossil Fuels', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850302', 'local', '', 'Hydrogen Production from Nuclear Energy', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850303', 'local', '', 'Hydrogen Production from Renewable Energy', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850304', 'local', '', 'Oil and Gas Refining', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850305', 'local', '', 'Preparation of Black Coal', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850306', 'local', '', 'Preparation of Brown Coal (Lignite)', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850307', 'local', '', 'Preparation of Oil Shale and Tar Sands', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850308', 'local', '', 'Preparation of Uranium', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850309', 'local', '', 'Production of Biofuels (Biomass)', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850399', 'local', '', 'Preparation and Production of Energy Sources not  elsewhere classified', '', '', 'ANZSRC-SEO', '850300', 'pt', '', 'ANZSRC>>ENERGY>>PREPARATION AND PRODUCTION OF ENERGY SOURCES', '');
INSERT INTO tbl_terms VALUES ('850400', 'local', '', 'ENERGY TRANSFORMATION', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850401', 'local', '', 'Fuel Cells (excl. Solid Oxide)', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850402', 'local', '', 'Hydrogen-based Energy Systems (incl. Internal Hydrogen Combustion Engines)', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850403', 'local', '', 'Nuclear Energy', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850404', 'local', '', 'Solid Oxide Fuel Cells', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850405', 'local', '', 'Transformation of Black Coal into Electricity', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850406', 'local', '', 'Transformation of Brown Coal (Lignite) into Electricity', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850407', 'local', '', 'Transformation of Coal into Gaseous Fuels', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850408', 'local', '', 'Transformation of Coal into Liquid Fuels', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850409', 'local', '', 'Transformation of Gas into Electricity', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850410', 'local', '', 'Transformation of Gas into Liquid Fuels', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850499', 'local', '', 'Energy Transformation not elsewhere classified', '', '', 'ANZSRC-SEO', '850400', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY TRANSFORMATION', '');
INSERT INTO tbl_terms VALUES ('850500', 'local', '', 'RENEWABLE ENERGY', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850501', 'local', '', 'Biofuel (Biomass) Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850502', 'local', '', 'Geothermal Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850503', 'local', '', 'Hydro-Electric Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850504', 'local', '', 'Solar-Photovoltaic Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850505', 'local', '', 'Solar-Thermal Electric Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850506', 'local', '', 'Solar-Thermal Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850507', 'local', '', 'Tidal Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850508', 'local', '', 'Wave Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850509', 'local', '', 'Wind Energy', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850599', 'local', '', 'Renewable Energy not elsewhere classified', '', '', 'ANZSRC-SEO', '850500', 'pt', '', 'ANZSRC>>ENERGY>>RENEWABLE ENERGY', '');
INSERT INTO tbl_terms VALUES ('850600', 'local', '', 'ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850601', 'local', '', 'Energy Services and Utilities', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850602', 'local', '', 'Energy Storage (excl. Hydrogen)', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850603', 'local', '', 'Energy Systems Analysis', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850604', 'local', '', 'Energy Transmission and Distribution (excl. Hydrogen)', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850605', 'local', '', 'Hydrogen Distribution', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850606', 'local', '', 'Hydrogen Storage', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850699', 'local', '', 'Energy Storage, Distribution and Supply not elsewhere classified', '', '', 'ANZSRC-SEO', '850600', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY STORAGE, DISTRIBUTION AND SUPPLY', '');
INSERT INTO tbl_terms VALUES ('850700', 'local', '', 'ENERGY CONSERVATION AND EFFICIENCY', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('850701', 'local', '', 'Commercial Energy Conservation and Efficiency', '', '', 'ANZSRC-SEO', '850700', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY CONSERVATION AND EFFICIENCY', '');
INSERT INTO tbl_terms VALUES ('850702', 'local', '', 'Energy Conservation and Efficiency in Transport', '', '', 'ANZSRC-SEO', '850700', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY CONSERVATION AND EFFICIENCY', '');
INSERT INTO tbl_terms VALUES ('850703', 'local', '', 'Industrial Energy Conservation and Efficiency', '', '', 'ANZSRC-SEO', '850700', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY CONSERVATION AND EFFICIENCY', '');
INSERT INTO tbl_terms VALUES ('850704', 'local', '', 'Residential Energy Conservation and Efficiency', '', '', 'ANZSRC-SEO', '850700', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY CONSERVATION AND EFFICIENCY', '');
INSERT INTO tbl_terms VALUES ('850799', 'local', '', 'Energy Conservation and Efficiency not elsewhere classified', '', '', 'ANZSRC-SEO', '850700', 'pt', '', 'ANZSRC>>ENERGY>>ENERGY CONSERVATION AND EFFICIENCY', '');
INSERT INTO tbl_terms VALUES ('859800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('859801', 'local', '', 'Management of Gaseous Waste from Energy Activities (excl.  Greenhouse Gases)', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('880303', 'local', '', 'Air Safety', '', '', 'ANZSRC-SEO', '880300', 'pt', '', 'ANZSRC>>TRANSPORT>>AEROSPACE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('859802', 'local', '', 'Management of Greenhouse Gas Emissions from Electricity Generation', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('859803', 'local', '', 'Management of Greenhouse Gas Emissions from Energy  Activities (excl. Electricity Generation)', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('859804', 'local', '', 'Management of Liquid Waste from Energy Activities (excl.  Water)', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('859805', 'local', '', 'Management of Solid Waste from Energy Activities', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('859806', 'local', '', 'Management of Water Consumption by Energy Activities', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('859899', 'local', '', 'Environmentally Sustainable Energy Activities not elsewhere  classified', '', '', 'ANZSRC-SEO', '859800', 'pt', '', 'ANZSRC>>ENERGY>>ENVIRONMENTALLY SUSTAINABLE ENERGY ACTIVITIES', '');
INSERT INTO tbl_terms VALUES ('859900', 'local', '', 'OTHER ENERGY', '', '', 'ANZSRC-SEO', '850000', 'pt', '', 'ANZSRC>>ENERGY', '');
INSERT INTO tbl_terms VALUES ('859999', 'local', '', 'Energy not elsewhere classified', '', '', 'ANZSRC-SEO', '859900', 'pt', '', 'ANZSRC>>ENERGY>>OTHER ENERGY', '');
INSERT INTO tbl_terms VALUES ('860000', 'local', '', 'MANUFACTURING', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('860100', 'local', '', 'PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860101', 'local', '', 'Bakery Products', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860102', 'local', '', 'Beverages (excl. Fruit Juices)', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860103', 'local', '', 'Carcass Meat (incl. Fish and Seafood)', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860104', 'local', '', 'Flour Mill and Cereal Food', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860105', 'local', '', 'Nutraceuticals and Functional foods', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860106', 'local', '', 'Oils and Fats (incl. Margarines)', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860107', 'local', '', 'Processed Fish and Seafood Products', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860108', 'local', '', 'Processed Fruit and Vegetable Products (incl. Fruit Juices)', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860109', 'local', '', 'Processed Meat Products', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860110', 'local', '', 'Soy Products', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860111', 'local', '', 'Sugar and Confectionery Products', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860199', 'local', '', 'Processed Food Products and Beverages (excl. Dairy  Products) not elsewhere classified', '', '', 'ANZSRC-SEO', '860100', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED FOOD PRODUCTS AND BEVERAGES  (EXCL. DAIRY PRODUCTS)', '');
INSERT INTO tbl_terms VALUES ('860200', 'local', '', 'DAIRY PRODUCTS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860201', 'local', '', 'Butter and Milk-Derived Fats and Oils (excl. Cream)', '', '', 'ANZSRC-SEO', '860200', 'pt', '', 'ANZSRC>>MANUFACTURING>>DAIRY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860202', 'local', '', 'Casein', '', '', 'ANZSRC-SEO', '860200', 'pt', '', 'ANZSRC>>MANUFACTURING>>DAIRY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860203', 'local', '', 'Cheese', '', '', 'ANZSRC-SEO', '860200', 'pt', '', 'ANZSRC>>MANUFACTURING>>DAIRY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860204', 'local', '', 'Processed Milk and Cream (incl. Powder, Evaporated and Condensed)', '', '', 'ANZSRC-SEO', '860200', 'pt', '', 'ANZSRC>>MANUFACTURING>>DAIRY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860205', 'local', '', 'Whey', '', '', 'ANZSRC-SEO', '860200', 'pt', '', 'ANZSRC>>MANUFACTURING>>DAIRY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860299', 'local', '', 'Dairy Products not elsewhere classified', '', '', 'ANZSRC-SEO', '860200', 'pt', '', 'ANZSRC>>MANUFACTURING>>DAIRY PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860300', 'local', '', 'PROCESSED NON-FOOD AGRICULTURE PRODUCTS  (EXCL. WOOD, PAPER AND FIBRE)', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860301', 'local', '', 'Essential Oils (e.g. Tea Tree, Eucalyptus, Lavender, Peppermint, Boronia, Sandlewood)', '', '', 'ANZSRC-SEO', '860300', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED NON-FOOD AGRICULTURE PRODUCTS  (EXCL. WOOD, PAPER AND FIBRE)', '');
INSERT INTO tbl_terms VALUES ('860302', 'local', '', 'Organic Fertilisers', '', '', 'ANZSRC-SEO', '860300', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED NON-FOOD AGRICULTURE PRODUCTS  (EXCL. WOOD, PAPER AND FIBRE)', '');
INSERT INTO tbl_terms VALUES ('860303', 'local', '', 'Plant Extracts (e.g. Pyrethrum, Alkaloids, Jojoba Oil)', '', '', 'ANZSRC-SEO', '860300', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED NON-FOOD AGRICULTURE PRODUCTS  (EXCL. WOOD, PAPER AND FIBRE)', '');
INSERT INTO tbl_terms VALUES ('860304', 'local', '', 'Prepared Animal Feed', '', '', 'ANZSRC-SEO', '860300', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED NON-FOOD AGRICULTURE PRODUCTS  (EXCL. WOOD, PAPER AND FIBRE)', '');
INSERT INTO tbl_terms VALUES ('860399', 'local', '', 'Processed Non-Food Agricultural Products (excl. Wood, Paper and Fibre) not elsewhere classified', '', '', 'ANZSRC-SEO', '860300', 'pt', '', 'ANZSRC>>MANUFACTURING>>PROCESSED NON-FOOD AGRICULTURE PRODUCTS  (EXCL. WOOD, PAPER AND FIBRE)', '');
INSERT INTO tbl_terms VALUES ('860400', 'local', '', 'LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860401', 'local', '', 'Clothing', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860402', 'local', '', 'Cotton Ginning', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860403', 'local', '', 'Natural Fibres, Yarns and Fabrics', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860404', 'local', '', 'Non-Fabric Textiles (e.g. Felt)', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('970114', 'local', '', 'Expanding Knowledge in Economics', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('860405', 'local', '', 'Processed Skins, Leather and Leather Products', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860406', 'local', '', 'Synthetic Fibres, Yarns and Fabrics', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860407', 'local', '', 'Wool Scouring and Top Making', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860499', 'local', '', 'Leather Products, Fibre Processing and Textiles not elsewhere classified', '', '', 'ANZSRC-SEO', '860400', 'pt', '', 'ANZSRC>>MANUFACTURING>>LEATHER PRODUCTS, FIBRE PROCESSING AND  TEXTILES', '');
INSERT INTO tbl_terms VALUES ('860500', 'local', '', 'WOOD, WOOD PRODUCTS AND PAPER', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860501', 'local', '', 'Paper Products (incl. Coated Paper)', '', '', 'ANZSRC-SEO', '860500', 'pt', '', 'ANZSRC>>MANUFACTURING>>WOOD, WOOD PRODUCTS AND PAPER', '');
INSERT INTO tbl_terms VALUES ('860502', 'local', '', 'Printing and Publishing Processes', '', '', 'ANZSRC-SEO', '860500', 'pt', '', 'ANZSRC>>MANUFACTURING>>WOOD, WOOD PRODUCTS AND PAPER', '');
INSERT INTO tbl_terms VALUES ('860503', 'local', '', 'Pulp and Paper', '', '', 'ANZSRC-SEO', '860500', 'pt', '', 'ANZSRC>>MANUFACTURING>>WOOD, WOOD PRODUCTS AND PAPER', '');
INSERT INTO tbl_terms VALUES ('860504', 'local', '', 'Reconstituted Timber Products (e.g. Chipboard, Particleboard)', '', '', 'ANZSRC-SEO', '860500', 'pt', '', 'ANZSRC>>MANUFACTURING>>WOOD, WOOD PRODUCTS AND PAPER', '');
INSERT INTO tbl_terms VALUES ('860505', 'local', '', 'Wood Sawing and Veneer', '', '', 'ANZSRC-SEO', '860500', 'pt', '', 'ANZSRC>>MANUFACTURING>>WOOD, WOOD PRODUCTS AND PAPER', '');
INSERT INTO tbl_terms VALUES ('860599', 'local', '', 'Wood, Wood Products and Paper not elsewhere classified', '', '', 'ANZSRC-SEO', '860500', 'pt', '', 'ANZSRC>>MANUFACTURING>>WOOD, WOOD PRODUCTS AND PAPER', '');
INSERT INTO tbl_terms VALUES ('860600', 'local', '', 'INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860601', 'local', '', 'Industrial Gases', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860602', 'local', '', 'Inorganic Industrial Chemicals', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860603', 'local', '', 'Lubricants', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860604', 'local', '', 'Organic Industrial Chemicals (excl. Resins, Rubber and  Plastics)', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860605', 'local', '', 'Paints', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860606', 'local', '', 'Plastics in Primary Forms', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860607', 'local', '', 'Plastic Products (incl. Construction Materials)', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860608', 'local', '', 'Rubber and Synthetic Resins', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860609', 'local', '', 'Soaps and Cosmetics', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860699', 'local', '', 'Industrial Chemicals and Related Products not elsewhere classified', '', '', 'ANZSRC-SEO', '860600', 'pt', '', 'ANZSRC>>MANUFACTURING>>INDUSTRIAL CHEMICALS AND RELATED PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860700', 'local', '', 'AGRICULTURAL CHEMICALS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860701', 'local', '', 'Animal Protection Chemicals', '', '', 'ANZSRC-SEO', '860700', 'pt', '', 'ANZSRC>>MANUFACTURING>>AGRICULTURAL CHEMICALS', '');
INSERT INTO tbl_terms VALUES ('860702', 'local', '', 'Chemical Fertilisers', '', '', 'ANZSRC-SEO', '860700', 'pt', '', 'ANZSRC>>MANUFACTURING>>AGRICULTURAL CHEMICALS', '');
INSERT INTO tbl_terms VALUES ('860703', 'local', '', 'Crop Protection Chemicals', '', '', 'ANZSRC-SEO', '860700', 'pt', '', 'ANZSRC>>MANUFACTURING>>AGRICULTURAL CHEMICALS', '');
INSERT INTO tbl_terms VALUES ('860799', 'local', '', 'Agricultural Chemicals not elsewhere classified', '', '', 'ANZSRC-SEO', '860700', 'pt', '', 'ANZSRC>>MANUFACTURING>>AGRICULTURAL CHEMICALS', '');
INSERT INTO tbl_terms VALUES ('860800', 'local', '', 'HUMAN PHARMACEUTICAL PRODUCTS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860801', 'local', '', 'Human Biological Preventatives (e.g. Vaccines)', '', '', 'ANZSRC-SEO', '860800', 'pt', '', 'ANZSRC>>MANUFACTURING>>HUMAN PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860802', 'local', '', 'Human Diagnostics', '', '', 'ANZSRC-SEO', '860800', 'pt', '', 'ANZSRC>>MANUFACTURING>>HUMAN PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860803', 'local', '', 'Human Pharmaceutical Treatments (e.g. Antibiotics)', '', '', 'ANZSRC-SEO', '860800', 'pt', '', 'ANZSRC>>MANUFACTURING>>HUMAN PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860899', 'local', '', 'Human Pharmaceutical Products not elsewhere classified', '', '', 'ANZSRC-SEO', '860800', 'pt', '', 'ANZSRC>>MANUFACTURING>>HUMAN PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860900', 'local', '', 'VETERINARY PHARMACEUTICAL PRODUCTS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('860901', 'local', '', 'Veterinary Biological Preventatives (e.g. Vaccines)', '', '', 'ANZSRC-SEO', '860900', 'pt', '', 'ANZSRC>>MANUFACTURING>>VETERINARY PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860902', 'local', '', 'Veterinary Diagnostics', '', '', 'ANZSRC-SEO', '860900', 'pt', '', 'ANZSRC>>MANUFACTURING>>VETERINARY PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860903', 'local', '', 'Veterinary Pharmaceutical Treatments (e.g. Antibiotics)', '', '', 'ANZSRC-SEO', '860900', 'pt', '', 'ANZSRC>>MANUFACTURING>>VETERINARY PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('860999', 'local', '', 'Veterinary Pharmaceutical Products not elsewhere classified', '', '', 'ANZSRC-SEO', '860900', 'pt', '', 'ANZSRC>>MANUFACTURING>>VETERINARY PHARMACEUTICAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861000', 'local', '', 'CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861001', 'local', '', 'Cement and Concrete Materials', '', '', 'ANZSRC-SEO', '861000', 'pt', '', 'ANZSRC>>MANUFACTURING>>CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861002', 'local', '', 'Ceramics', '', '', 'ANZSRC-SEO', '861000', 'pt', '', 'ANZSRC>>MANUFACTURING>>CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861003', 'local', '', 'Clay Products', '', '', 'ANZSRC-SEO', '861000', 'pt', '', 'ANZSRC>>MANUFACTURING>>CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861004', 'local', '', 'Plaster and Plaster Products', '', '', 'ANZSRC-SEO', '861000', 'pt', '', 'ANZSRC>>MANUFACTURING>>CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861005', 'local', '', 'Structural Glass and Glass Products', '', '', 'ANZSRC-SEO', '861000', 'pt', '', 'ANZSRC>>MANUFACTURING>>CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861099', 'local', '', 'Ceramics, Glass and Industrial Mineral Products not elsewhere classified', '', '', 'ANZSRC-SEO', '861000', 'pt', '', 'ANZSRC>>MANUFACTURING>>CERAMICS, GLASS AND INDUSTRIAL MINERAL  PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861100', 'local', '', 'BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861101', 'local', '', 'Basic Aluminium Products', '', '', 'ANZSRC-SEO', '861100', 'pt', '', 'ANZSRC>>MANUFACTURING>>BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '');
INSERT INTO tbl_terms VALUES ('861102', 'local', '', 'Basic Copper Products', '', '', 'ANZSRC-SEO', '861100', 'pt', '', 'ANZSRC>>MANUFACTURING>>BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '');
INSERT INTO tbl_terms VALUES ('861103', 'local', '', 'Basic Iron and Steel Products', '', '', 'ANZSRC-SEO', '861100', 'pt', '', 'ANZSRC>>MANUFACTURING>>BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '');
INSERT INTO tbl_terms VALUES ('861104', 'local', '', 'Basic Precious Metal Products', '', '', 'ANZSRC-SEO', '861100', 'pt', '', 'ANZSRC>>MANUFACTURING>>BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '');
INSERT INTO tbl_terms VALUES ('861105', 'local', '', 'Basic Zinc Products', '', '', 'ANZSRC-SEO', '861100', 'pt', '', 'ANZSRC>>MANUFACTURING>>BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '');
INSERT INTO tbl_terms VALUES ('861199', 'local', '', 'Basic Metal Products not elsewhere classified', '', '', 'ANZSRC-SEO', '861100', 'pt', '', 'ANZSRC>>MANUFACTURING>>BASIC METAL PRODUCTS (INCL. SMELTING, ROLLING, DRAWING AND EXTRUDING)', '');
INSERT INTO tbl_terms VALUES ('861200', 'local', '', 'FABRICATED METAL PRODUCTS', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861201', 'local', '', 'Coated Metal and Metal-Coated Products', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861202', 'local', '', 'Machined Metal Products', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861203', 'local', '', 'Metal Castings', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861204', 'local', '', 'Semi-Finished Metal Products', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861205', 'local', '', 'Sheet Metal Products', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861206', 'local', '', 'Structural Metal Products', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861299', 'local', '', 'Fabricated Metal Products not elsewhere classified', '', '', 'ANZSRC-SEO', '861200', 'pt', '', 'ANZSRC>>MANUFACTURING>>FABRICATED METAL PRODUCTS', '');
INSERT INTO tbl_terms VALUES ('861300', 'local', '', 'TRANSPORT EQUIPMENT', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861301', 'local', '', 'Aerospace Equipment', '', '', 'ANZSRC-SEO', '861300', 'pt', '', 'ANZSRC>>MANUFACTURING>>TRANSPORT EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861302', 'local', '', 'Automotive Equipment', '', '', 'ANZSRC-SEO', '861300', 'pt', '', 'ANZSRC>>MANUFACTURING>>TRANSPORT EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861303', 'local', '', 'Nautical Equipment (excl. Yachts)', '', '', 'ANZSRC-SEO', '861300', 'pt', '', 'ANZSRC>>MANUFACTURING>>TRANSPORT EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861304', 'local', '', 'Rail Equipment', '', '', 'ANZSRC-SEO', '861300', 'pt', '', 'ANZSRC>>MANUFACTURING>>TRANSPORT EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861305', 'local', '', 'Yachts', '', '', 'ANZSRC-SEO', '861300', 'pt', '', 'ANZSRC>>MANUFACTURING>>TRANSPORT EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861399', 'local', '', 'Transport Equipment not elsewhere classified', '', '', 'ANZSRC-SEO', '861300', 'pt', '', 'ANZSRC>>MANUFACTURING>>TRANSPORT EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861400', 'local', '', 'MACHINERY AND EQUIPMENT', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861401', 'local', '', 'Agricultural Machinery and Equipment', '', '', 'ANZSRC-SEO', '861400', 'pt', '', 'ANZSRC>>MANUFACTURING>>MACHINERY AND EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861402', 'local', '', 'Appliances and Electrical Machinery and Equipment', '', '', 'ANZSRC-SEO', '861400', 'pt', '', 'ANZSRC>>MANUFACTURING>>MACHINERY AND EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861403', 'local', '', 'Industrial Machinery and Equipment', '', '', 'ANZSRC-SEO', '861400', 'pt', '', 'ANZSRC>>MANUFACTURING>>MACHINERY AND EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861404', 'local', '', 'Mining Machinery and Equipment', '', '', 'ANZSRC-SEO', '861400', 'pt', '', 'ANZSRC>>MANUFACTURING>>MACHINERY AND EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861499', 'local', '', 'Machinery and Equipment not elsewhere classified', '', '', 'ANZSRC-SEO', '861400', 'pt', '', 'ANZSRC>>MANUFACTURING>>MACHINERY AND EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861500', 'local', '', 'INSTRUMENTATION', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861501', 'local', '', 'Industrial Instruments', '', '', 'ANZSRC-SEO', '861500', 'pt', '', 'ANZSRC>>MANUFACTURING>>INSTRUMENTATION', '');
INSERT INTO tbl_terms VALUES ('861502', 'local', '', 'Medical Instruments', '', '', 'ANZSRC-SEO', '861500', 'pt', '', 'ANZSRC>>MANUFACTURING>>INSTRUMENTATION', '');
INSERT INTO tbl_terms VALUES ('861503', 'local', '', 'Scientific Instruments', '', '', 'ANZSRC-SEO', '861500', 'pt', '', 'ANZSRC>>MANUFACTURING>>INSTRUMENTATION', '');
INSERT INTO tbl_terms VALUES ('861599', 'local', '', 'Instrumentation not elsewhere classified', '', '', 'ANZSRC-SEO', '861500', 'pt', '', 'ANZSRC>>MANUFACTURING>>INSTRUMENTATION', '');
INSERT INTO tbl_terms VALUES ('861600', 'local', '', 'COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861601', 'local', '', 'Computer and Electronic Office Equipment', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861602', 'local', '', 'Consumer Electronic Equipment (excl. Communication Equipment)', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861603', 'local', '', 'Integrated Circuits and Devices', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861604', 'local', '', 'Integrated Systems', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861605', 'local', '', 'Processor Modules', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861606', 'local', '', 'Satellite Navigation Equipment', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861699', 'local', '', 'Computer Hardware and Electronic Equipment not elsewhere classified', '', '', 'ANZSRC-SEO', '861600', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMPUTER HARDWARE AND ELECTRONIC  EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861700', 'local', '', 'COMMUNICATION EQUIPMENT', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('861701', 'local', '', 'Network Infrastructure Equipment', '', '', 'ANZSRC-SEO', '861700', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMMUNICATION EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861702', 'local', '', 'Telemetry Equipment', '', '', 'ANZSRC-SEO', '861700', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMMUNICATION EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861703', 'local', '', 'Voice and Data Equipment', '', '', 'ANZSRC-SEO', '861700', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMMUNICATION EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('861799', 'local', '', 'Communication Equipment not elsewhere classified', '', '', 'ANZSRC-SEO', '861700', 'pt', '', 'ANZSRC>>MANUFACTURING>>COMMUNICATION EQUIPMENT', '');
INSERT INTO tbl_terms VALUES ('869800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869801', 'local', '', 'Management of Gaseous Waste from Manufacturing Activities (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '869800', 'pt', '', 'ANZSRC>>MANUFACTURING>>ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869802', 'local', '', 'Management of Greenhouse Gas Emissions from  Manufacturing Activities', '', '', 'ANZSRC-SEO', '869800', 'pt', '', 'ANZSRC>>MANUFACTURING>>ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869803', 'local', '', 'Management of Liquid Waste from Manufacturing Activities  (excl. Water)', '', '', 'ANZSRC-SEO', '869800', 'pt', '', 'ANZSRC>>MANUFACTURING>>ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869804', 'local', '', 'Management of Solid Waste from Manufacturing Activities', '', '', 'ANZSRC-SEO', '869800', 'pt', '', 'ANZSRC>>MANUFACTURING>>ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869805', 'local', '', 'Management of Water Consumption by Manufacturing  Activities', '', '', 'ANZSRC-SEO', '869800', 'pt', '', 'ANZSRC>>MANUFACTURING>>ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869899', 'local', '', 'Environmentally Sustainable Manufacturing not elsewhere classified', '', '', 'ANZSRC-SEO', '869800', 'pt', '', 'ANZSRC>>MANUFACTURING>>ENVIRONMENTALLY SUSTAINABLE  MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869900', 'local', '', 'OTHER MANUFACTURING', '', '', 'ANZSRC-SEO', '860000', 'pt', '', 'ANZSRC>>MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869901', 'local', '', 'Furniture', '', '', 'ANZSRC-SEO', '869900', 'pt', '', 'ANZSRC>>MANUFACTURING>>OTHER MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('869999', 'local', '', 'Manufacturing not elsewhere classified', '', '', 'ANZSRC-SEO', '869900', 'pt', '', 'ANZSRC>>MANUFACTURING>>OTHER MANUFACTURING', '');
INSERT INTO tbl_terms VALUES ('870000', 'local', '', 'CONSTRUCTION', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('870100', 'local', '', 'CONSTRUCTION PLANNING', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('870101', 'local', '', 'Civil Construction Planning', '', '', 'ANZSRC-SEO', '870100', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PLANNING', '');
INSERT INTO tbl_terms VALUES ('870102', 'local', '', 'Commercial Construction Planning', '', '', 'ANZSRC-SEO', '870100', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PLANNING', '');
INSERT INTO tbl_terms VALUES ('870103', 'local', '', 'Regional Planning', '', '', 'ANZSRC-SEO', '870100', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PLANNING', '');
INSERT INTO tbl_terms VALUES ('870104', 'local', '', 'Residential Construction Planning', '', '', 'ANZSRC-SEO', '870100', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PLANNING', '');
INSERT INTO tbl_terms VALUES ('870105', 'local', '', 'Urban Planning', '', '', 'ANZSRC-SEO', '870100', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PLANNING', '');
INSERT INTO tbl_terms VALUES ('870199', 'local', '', 'Construction Planning not elsewhere classified', '', '', 'ANZSRC-SEO', '870100', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PLANNING', '');
INSERT INTO tbl_terms VALUES ('870200', 'local', '', 'CONSTRUCTION DESIGN', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('870201', 'local', '', 'Civil Construction Design', '', '', 'ANZSRC-SEO', '870200', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION DESIGN', '');
INSERT INTO tbl_terms VALUES ('870202', 'local', '', 'Commercial Construction Design', '', '', 'ANZSRC-SEO', '870200', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION DESIGN', '');
INSERT INTO tbl_terms VALUES ('870203', 'local', '', 'Industrial Construction Design', '', '', 'ANZSRC-SEO', '870200', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION DESIGN', '');
INSERT INTO tbl_terms VALUES ('870204', 'local', '', 'Residential Construction Design', '', '', 'ANZSRC-SEO', '870200', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION DESIGN', '');
INSERT INTO tbl_terms VALUES ('870299', 'local', '', 'Construction Design not elsewhere classified', '', '', 'ANZSRC-SEO', '870200', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION DESIGN', '');
INSERT INTO tbl_terms VALUES ('870300', 'local', '', 'CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('870301', 'local', '', 'Cement and Concrete Materials', '', '', 'ANZSRC-SEO', '870300', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870302', 'local', '', 'Metals (e.g. Composites, Coatings, Bonding)', '', '', 'ANZSRC-SEO', '870300', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870303', 'local', '', 'Polymeric Materials (e.g. Paints)', '', '', 'ANZSRC-SEO', '870300', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870304', 'local', '', 'Stone, Ceramics and Clay Materials', '', '', 'ANZSRC-SEO', '870300', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870305', 'local', '', 'Timber Materials', '', '', 'ANZSRC-SEO', '870300', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870399', 'local', '', 'Construction Materials Performance and Processes not  elsewhere classified', '', '', 'ANZSRC-SEO', '870300', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION MATERIALS PERFORMANCE AND  PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870400', 'local', '', 'CONSTRUCTION PROCESSES', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('870401', 'local', '', 'Civil Construction Processes', '', '', 'ANZSRC-SEO', '870400', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870402', 'local', '', 'Commercial Construction Processes', '', '', 'ANZSRC-SEO', '870400', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870403', 'local', '', 'Industrial Construction Processes', '', '', 'ANZSRC-SEO', '870400', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870404', 'local', '', 'Residential Construction Processes', '', '', 'ANZSRC-SEO', '870400', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870499', 'local', '', 'Construction Processes not elsewhere classified', '', '', 'ANZSRC-SEO', '870400', 'pt', '', 'ANZSRC>>CONSTRUCTION>>CONSTRUCTION PROCESSES', '');
INSERT INTO tbl_terms VALUES ('870500', 'local', '', 'BUILDING MANAGEMENT AND SERVICES', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('870501', 'local', '', 'Civil Building Management and Services', '', '', 'ANZSRC-SEO', '870500', 'pt', '', 'ANZSRC>>CONSTRUCTION>>BUILDING MANAGEMENT AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('870502', 'local', '', 'Commercial Building Management and Services', '', '', 'ANZSRC-SEO', '870500', 'pt', '', 'ANZSRC>>CONSTRUCTION>>BUILDING MANAGEMENT AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('870503', 'local', '', 'Residential Building Management and Services', '', '', 'ANZSRC-SEO', '870500', 'pt', '', 'ANZSRC>>CONSTRUCTION>>BUILDING MANAGEMENT AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('870599', 'local', '', 'Building Management and Services not elsewhere classified', '', '', 'ANZSRC-SEO', '870500', 'pt', '', 'ANZSRC>>CONSTRUCTION>>BUILDING MANAGEMENT AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('879800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879801', 'local', '', 'Management of Gaseous Waste from Construction Activities  (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '879800', 'pt', '', 'ANZSRC>>CONSTRUCTION>>ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879802', 'local', '', 'Management of Greenhouse Gas Emissions from  Construction Activities', '', '', 'ANZSRC-SEO', '879800', 'pt', '', 'ANZSRC>>CONSTRUCTION>>ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879803', 'local', '', 'Management of Liquid Waste from Construction Activities  (excl. Water)', '', '', 'ANZSRC-SEO', '879800', 'pt', '', 'ANZSRC>>CONSTRUCTION>>ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879804', 'local', '', 'Management of Solid Waste from Construction Activities', '', '', 'ANZSRC-SEO', '879800', 'pt', '', 'ANZSRC>>CONSTRUCTION>>ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879805', 'local', '', 'Management of Water Consumption by Construction Activities', '', '', 'ANZSRC-SEO', '879800', 'pt', '', 'ANZSRC>>CONSTRUCTION>>ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879899', 'local', '', 'Environmentally Sustainable Construction not elsewhere classified', '', '', 'ANZSRC-SEO', '879800', 'pt', '', 'ANZSRC>>CONSTRUCTION>>ENVIRONMENTALLY SUSTAINABLE CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879900', 'local', '', 'OTHER CONSTRUCTION', '', '', 'ANZSRC-SEO', '870000', 'pt', '', 'ANZSRC>>CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('879999', 'local', '', 'Construction not elsewhere classified', '', '', 'ANZSRC-SEO', '879900', 'pt', '', 'ANZSRC>>CONSTRUCTION>>OTHER CONSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('880000', 'local', '', 'TRANSPORT', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('880100', 'local', '', 'GROUND TRANSPORT', '', '', 'ANZSRC-SEO', '880000', 'pt', '', 'ANZSRC>>TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880101', 'local', '', 'Rail Freight', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880102', 'local', '', 'Rail Infrastructure and Networks', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880103', 'local', '', 'Rail Passenger Movements', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880104', 'local', '', 'Rail Safety', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880105', 'local', '', 'Road Freight', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880106', 'local', '', 'Road Infrastructure and Networks', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880107', 'local', '', 'Road Passenger Movements (excl. Public Transport)', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880108', 'local', '', 'Road Public Transport', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880109', 'local', '', 'Road Safety', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880199', 'local', '', 'Ground Transport not elsewhere classified', '', '', 'ANZSRC-SEO', '880100', 'pt', '', 'ANZSRC>>TRANSPORT>>GROUND TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880200', 'local', '', 'WATER TRANSPORT', '', '', 'ANZSRC-SEO', '880000', 'pt', '', 'ANZSRC>>TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880201', 'local', '', 'Coastal Sea Freight Transport', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880202', 'local', '', 'International Sea Freight Transport (excl. Live Animal Transport)', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880203', 'local', '', 'International Sea Transport of Live Animals', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880204', 'local', '', 'Passenger Water Transport', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880205', 'local', '', 'Port Infrastructure and Management', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880206', 'local', '', 'Water Safety', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880299', 'local', '', 'Water Transport not elsewhere classified', '', '', 'ANZSRC-SEO', '880200', 'pt', '', 'ANZSRC>>TRANSPORT>>WATER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880300', 'local', '', 'AEROSPACE TRANSPORT', '', '', 'ANZSRC-SEO', '880000', 'pt', '', 'ANZSRC>>TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880301', 'local', '', 'Air Freight', '', '', 'ANZSRC-SEO', '880300', 'pt', '', 'ANZSRC>>TRANSPORT>>AEROSPACE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880302', 'local', '', 'Air Passenger Transport', '', '', 'ANZSRC-SEO', '880300', 'pt', '', 'ANZSRC>>TRANSPORT>>AEROSPACE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880304', 'local', '', 'Air Terminal Infrastructure and Management', '', '', 'ANZSRC-SEO', '880300', 'pt', '', 'ANZSRC>>TRANSPORT>>AEROSPACE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880305', 'local', '', 'Space Transport', '', '', 'ANZSRC-SEO', '880300', 'pt', '', 'ANZSRC>>TRANSPORT>>AEROSPACE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('880399', 'local', '', 'Aerospace Transport not elsewhere classified', '', '', 'ANZSRC-SEO', '880300', 'pt', '', 'ANZSRC>>TRANSPORT>>AEROSPACE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '', '', 'ANZSRC-SEO', '880000', 'pt', '', 'ANZSRC>>TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889801', 'local', '', 'Management of Gaseous Waste from Transport Activities  (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889802', 'local', '', 'Management of Greenhouse Gas Emissions from Transport  Activities', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889803', 'local', '', 'Management of Liquid Waste from Transport Activities (excl.  Water)', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889804', 'local', '', 'Management of Noise and Vibration from Transport Activities', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889805', 'local', '', 'Management of Solid Waste from Transport Activities', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889806', 'local', '', 'Management of Water Consumption by Transport Activities', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889899', 'local', '', 'Environmentally Sustainable Transport not elsewhere classified', '', '', 'ANZSRC-SEO', '889800', 'pt', '', 'ANZSRC>>TRANSPORT>>ENVIRONMENTALLY SUSTAINABLE TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889900', 'local', '', 'OTHER TRANSPORT', '', '', 'ANZSRC-SEO', '880000', 'pt', '', 'ANZSRC>>TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889901', 'local', '', 'Intermodal Materials Handling', '', '', 'ANZSRC-SEO', '889900', 'pt', '', 'ANZSRC>>TRANSPORT>>OTHER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889902', 'local', '', 'Multimodal Transport', '', '', 'ANZSRC-SEO', '889900', 'pt', '', 'ANZSRC>>TRANSPORT>>OTHER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889903', 'local', '', 'Pipeline Transport', '', '', 'ANZSRC-SEO', '889900', 'pt', '', 'ANZSRC>>TRANSPORT>>OTHER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889904', 'local', '', 'Postal and Package Services (incl. Courier Services)', '', '', 'ANZSRC-SEO', '889900', 'pt', '', 'ANZSRC>>TRANSPORT>>OTHER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('889999', 'local', '', 'Transport not elsewhere classified', '', '', 'ANZSRC-SEO', '889900', 'pt', '', 'ANZSRC>>TRANSPORT>>OTHER TRANSPORT', '');
INSERT INTO tbl_terms VALUES ('890000', 'local', '', 'INFORMATION AND COMMUNICATION SERVICES', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('890100', 'local', '', 'COMMUNICATION NETWORKS AND SERVICES', '', '', 'ANZSRC-SEO', '890000', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('890101', 'local', '', 'Fixed Line Data Networks and Services', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890102', 'local', '', 'Fixed Line Telephone Networks and Services', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890103', 'local', '', 'Mobile Data Networks and Services', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890104', 'local', '', 'Mobile Telephone Networks and Services', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890105', 'local', '', 'Satellite Communication Networks and Services', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('isFunderOf', 'local', '', 'isFunderOf', 'provides monetary or in-kind aid to the related party or activity', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isil', 'local', '', 'isil', 'International Standard Identifier for Libraries', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('890200', 'local', '', 'COMPUTER SOFTWARE AND SERVICES', '', '', 'ANZSRC-SEO', '890000', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('890201', 'local', '', 'Application Software Packages (excl. Computer Games)', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('purl', 'local', '', 'purl', 'Persistent Uniform Resource Locator', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('890203', 'local', '', 'Computer Gaming Software', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('purl', 'local', '', 'purl', 'Persistent Uniform Resource Locator', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('890205', 'local', '', 'Information Processing Services (incl. Data Entry and Capture)', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890206', 'local', '', 'Internet Hosting Services (incl. Application Hosting Services)', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890299', 'local', '', 'Computer Software and Services not elsewhere classified', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890300', 'local', '', 'INFORMATION SERVICES', '', '', 'ANZSRC-SEO', '890000', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('significanceStatement', 'local', '', 'significanceStatement', '(collections only) a statement describing the significance of a collection within its domain or context', '', 'RIFCSDescriptionType', NULL, 'pt', '', 'RIFCS Description Type', '');
INSERT INTO tbl_terms VALUES ('890302', 'local', '', 'Library and Archival Services', '', '', 'ANZSRC-SEO', '890300', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>INFORMATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('890303', 'local', '', 'News Collection Services', '', '', 'ANZSRC-SEO', '890300', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>INFORMATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('890399', 'local', '', 'Information Services not elsewhere classified', '', '', 'ANZSRC-SEO', '890300', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>INFORMATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('890400', 'local', '', 'MEDIA SERVICES', '', '', 'ANZSRC-SEO', '890000', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('startPublicationDate', 'local', '', 'startPublicationDate', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('890402', 'local', '', 'Film and Video Services (excl. Animation and Computer Generated Imagery)', '', '', 'ANZSRC-SEO', '890400', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>MEDIA SERVICES', '');
INSERT INTO tbl_terms VALUES ('streetAddress', 'local', '', 'streetAddress', 'address where an entity is physically located', '', 'RIFCSPhysicalAddressType', NULL, 'pt', '', 'RIFCS Physical Address Type', '');
INSERT INTO tbl_terms VALUES ('syndicate-rss', 'local', '', 'syndicate-rss', 'RSS feed', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('text', 'local', '', 'text', 'a single address part that contains the whole address in unstructured form', '', 'RIFCSPhysicalAddressPartType', NULL, 'pt', '', 'RIFCS Physical Address Part Type', '');
INSERT INTO tbl_terms VALUES ('text', 'local', '', 'text', 'free-text representation of spatial location', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('transform', 'local', '', 'transform', 'analysis, conversion', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('899800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE INFORMATION AND COMMUNICATION SERVICES', '', '', 'ANZSRC-SEO', '890000', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('899801', 'local', '', 'Management of Greenhouse Gas Emissions from Information and Communication Services', '', '', 'ANZSRC-SEO', '899800', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>ENVIRONMENTALLY SUSTAINABLE INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('899802', 'local', '', 'Management of Solid Waste from Information and Communication Services', '', '', 'ANZSRC-SEO', '899800', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>ENVIRONMENTALLY SUSTAINABLE INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('899803', 'local', '', 'Management of Water Consumption by Information and Communication Services', '', '', 'ANZSRC-SEO', '899800', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>ENVIRONMENTALLY SUSTAINABLE INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('899899', 'local', '', 'Environmentally Sustainable Information and Communication Services not elsewhere classified', '', '', 'ANZSRC-SEO', '899800', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>ENVIRONMENTALLY SUSTAINABLE INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('899900', 'local', '', 'OTHER INFORMATION AND COMMUNICATION  SERVICES', '', '', 'ANZSRC-SEO', '890000', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('899999', 'local', '', 'Information and Communication Services not elsewhere classified', '', '', 'ANZSRC-SEO', '899900', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>OTHER INFORMATION AND COMMUNICATION  SERVICES', '');
INSERT INTO tbl_terms VALUES ('900000', 'local', '', 'COMMERCIAL SERVICES AND TOURISM', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('900100', 'local', '', 'FINANCIAL SERVICES', '', '', 'ANZSRC-SEO', '900000', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('uri', 'local', '', 'uri', 'Uniform Resource Identifier', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('900102', 'local', '', 'Investment Services (excl. Superannuation)', '', '', 'ANZSRC-SEO', '900100', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>FINANCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('900103', 'local', '', 'Superannuation and Insurance Services', '', '', 'ANZSRC-SEO', '900100', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>FINANCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('900199', 'local', '', 'Financial Services not elsewhere classified', '', '', 'ANZSRC-SEO', '900100', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>FINANCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('900200', 'local', '', 'PROPERTY, BUSINESS SUPPORT SERVICES AND TRADE', '', '', 'ANZSRC-SEO', '900000', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('900201', 'local', '', 'Administration and Business Support Services', '', '', 'ANZSRC-SEO', '900200', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>PROPERTY, BUSINESS SUPPORT SERVICES AND TRADE', '');
INSERT INTO tbl_terms VALUES ('urn', 'local', '', 'urn', 'Uniform Resource Name', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('900203', 'local', '', 'Property Services (incl. Security)', '', '', 'ANZSRC-SEO', '900200', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>PROPERTY, BUSINESS SUPPORT SERVICES AND TRADE', '');
INSERT INTO tbl_terms VALUES ('urn', 'local', '', 'urn', 'Uniform Resource Name', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('900299', 'local', '', 'Property, Business Support Services and Trade not elsewhere classified', '', '', 'ANZSRC-SEO', '900200', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>PROPERTY, BUSINESS SUPPORT SERVICES AND TRADE', '');
INSERT INTO tbl_terms VALUES ('900300', 'local', '', 'TOURISM', '', '', 'ANZSRC-SEO', '900000', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('900301', 'local', '', 'Economic Issues in Tourism', '', '', 'ANZSRC-SEO', '900300', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('900302', 'local', '', 'Socio-Cultural Issues in Tourism', '', '', 'ANZSRC-SEO', '900300', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('900303', 'local', '', 'Tourism Infrastructure Development', '', '', 'ANZSRC-SEO', '900300', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('900399', 'local', '', 'Tourism not elsewhere classified', '', '', 'ANZSRC-SEO', '900300', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>TOURISM', '');
INSERT INTO tbl_terms VALUES ('900400', 'local', '', 'WATER AND WASTE SERVICES', '', '', 'ANZSRC-SEO', '900000', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('valid', 'local', '', 'valid', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('website', 'local', '', 'website', 'any publicly accessible web location containing information related to the collection, activity, party or service.', '', 'RIFCSRelatedInformationType', NULL, 'pt', '', 'RIFCS Related Information Type', '');
INSERT INTO tbl_terms VALUES ('AU-ANL:PEAU', 'local', '', 'AU-ANL:PEAU', 'National Library of Australia identifier', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('900404', 'local', '', 'Water Services and Utilities', '', '', 'ANZSRC-SEO', '900400', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>WATER AND WASTE SERVICES', '');
INSERT INTO tbl_terms VALUES ('900499', 'local', '', 'Water and Waste Services not elsewhere classified', '', '', 'ANZSRC-SEO', '900400', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>WATER AND WASTE SERVICES', '');
INSERT INTO tbl_terms VALUES ('909800', 'local', '', 'ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '', '', 'ANZSRC-SEO', '900000', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909801', 'local', '', 'anagement of Gaseous Waste from Commercial Services and Tourism (excl. Greenhouse Gases)', '', '', 'ANZSRC-SEO', '909800', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909802', 'local', '', 'Management of Greenhouse Gas Emissions from Commercial Services and Tourism', '', '', 'ANZSRC-SEO', '909800', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909803', 'local', '', 'Management of Liquid Waste from Commercial Services and Tourism (excl. Water)', '', '', 'ANZSRC-SEO', '909800', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909804', 'local', '', 'Management of Solid Waste from Commercial Services and Tourism', '', '', 'ANZSRC-SEO', '909800', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909805', 'local', '', 'Management of Water Consumption by Commercial Services and Tourism', '', '', 'ANZSRC-SEO', '909800', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909899', 'local', '', 'Environmentally Sustainable Commercial Services and Tourism not elsewhere classified', '', '', 'ANZSRC-SEO', '909800', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>ENVIRONMENTALLY SUSTAINABLE COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909900', 'local', '', 'OTHER COMMERCIAL SERVICES AND TOURISM', '', '', 'ANZSRC-SEO', '900000', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909901', 'local', '', 'Hospitality Services', '', '', 'ANZSRC-SEO', '909900', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>OTHER COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909902', 'local', '', 'Recreational Services', '', '', 'ANZSRC-SEO', '909900', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>OTHER COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('909999', 'local', '', 'Commercial Services and Tourism not elsewhere classified', '', '', 'ANZSRC-SEO', '909900', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>OTHER COMMERCIAL SERVICES AND TOURISM', '');
INSERT INTO tbl_terms VALUES ('910000', 'local', '', 'ECONOMIC FRAMEWORK', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('910100', 'local', '', 'MACROECONOMICS', '', '', 'ANZSRC-SEO', '910000', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('910101', 'local', '', 'Balance of Payments', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910102', 'local', '', 'Demography', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910103', 'local', '', 'Economic Growth', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910105', 'local', '', 'Fiscal Policy', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910106', 'local', '', 'Income Distribution', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910107', 'local', '', 'Macro Labour Market Issues', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910108', 'local', '', 'Monetary Policy', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910109', 'local', '', 'Savings and Investments', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910110', 'local', '', 'Taxation', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910199', 'local', '', 'Macroeconomics not elsewhere classified', '', '', 'ANZSRC-SEO', '910100', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MACROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910200', 'local', '', 'MICROECONOMICS', '', '', 'ANZSRC-SEO', '910000', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('910201', 'local', '', 'Consumption', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910202', 'local', '', 'Human Capital Issues', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910203', 'local', '', 'Industrial Organisations', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910204', 'local', '', 'Industry Costs and Structure', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910205', 'local', '', 'Industry Policy', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910206', 'local', '', 'Market-Based Mechanisms', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910207', 'local', '', 'Microeconomic Effects of Taxation', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910208', 'local', '', 'Micro Labour Market Issues', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910209', 'local', '', 'Preference, Behaviour and Welfare', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910210', 'local', '', 'Production', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910211', 'local', '', 'Supply and Demand', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910299', 'local', '', 'Microeconomics not elsewhere classified', '', '', 'ANZSRC-SEO', '910200', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MICROECONOMICS', '');
INSERT INTO tbl_terms VALUES ('910300', 'local', '', 'INTERNATIONAL TRADE', '', '', 'ANZSRC-SEO', '910000', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('910301', 'local', '', 'International Agreements on Trade', '', '', 'ANZSRC-SEO', '910300', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>INTERNATIONAL TRADE', '');
INSERT INTO tbl_terms VALUES ('910302', 'local', '', 'Trade Assistance and Protection', '', '', 'ANZSRC-SEO', '910300', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>INTERNATIONAL TRADE', '');
INSERT INTO tbl_terms VALUES ('910303', 'local', '', 'Trade Policy', '', '', 'ANZSRC-SEO', '910300', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>INTERNATIONAL TRADE', '');
INSERT INTO tbl_terms VALUES ('910399', 'local', '', 'International Trade not elsewhere classified', '', '', 'ANZSRC-SEO', '910300', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>INTERNATIONAL TRADE', '');
INSERT INTO tbl_terms VALUES ('910400', 'local', '', 'MANAGEMENT AND PRODUCTIVITY', '', '', 'ANZSRC-SEO', '910000', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('910401', 'local', '', 'Industrial Relations', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910402', 'local', '', 'Management', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910403', 'local', '', 'Marketing', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910404', 'local', '', 'Productivity (excl. Public Sector)', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910405', 'local', '', 'Public Sector Productivity', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910406', 'local', '', 'Technological and Organisational Innovation', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910499', 'local', '', 'Management and Productivity not elsewhere classified', '', '', 'ANZSRC-SEO', '910400', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MANAGEMENT AND PRODUCTIVITY', '');
INSERT INTO tbl_terms VALUES ('910500', 'local', '', 'MEASUREMENT STANDARDS AND CALIBRATION  SERVICES', '', '', 'ANZSRC-SEO', '910000', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('910501', 'local', '', 'Agricultural and Environmental Standards', '', '', 'ANZSRC-SEO', '910500', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MEASUREMENT STANDARDS AND CALIBRATION  SERVICES', '');
INSERT INTO tbl_terms VALUES ('910502', 'local', '', 'Defence Standards and Calibrations', '', '', 'ANZSRC-SEO', '910500', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MEASUREMENT STANDARDS AND CALIBRATION  SERVICES', '');
INSERT INTO tbl_terms VALUES ('910503', 'local', '', 'Manufacturing Standards and Calibrations', '', '', 'ANZSRC-SEO', '910500', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MEASUREMENT STANDARDS AND CALIBRATION  SERVICES', '');
INSERT INTO tbl_terms VALUES ('910504', 'local', '', 'Service Industries Standards and Calibrations', '', '', 'ANZSRC-SEO', '910500', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MEASUREMENT STANDARDS AND CALIBRATION  SERVICES', '');
INSERT INTO tbl_terms VALUES ('910599', 'local', '', 'Measurement Standards and Calibration Services not elsewhere classified', '', '', 'ANZSRC-SEO', '910500', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>MEASUREMENT STANDARDS AND CALIBRATION  SERVICES', '');
INSERT INTO tbl_terms VALUES ('919900', 'local', '', 'OTHER ECONOMIC FRAMEWORK', '', '', 'ANZSRC-SEO', '910000', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('919901', 'local', '', 'Carbon and Emissions Trading', '', '', 'ANZSRC-SEO', '919900', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>OTHER ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('919902', 'local', '', 'Ecological Economics', '', '', 'ANZSRC-SEO', '919900', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>OTHER ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('919999', 'local', '', 'Economic Framework not elsewhere classified', '', '', 'ANZSRC-SEO', '919900', 'pt', '', 'ANZSRC>>ECONOMIC FRAMEWORK>>OTHER ECONOMIC FRAMEWORK', '');
INSERT INTO tbl_terms VALUES ('920000', 'local', '', 'HEALTH', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('920100', 'local', '', 'CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '', '', 'ANZSRC-SEO', '920000', 'pt', '', 'ANZSRC>>HEALTH', '');
INSERT INTO tbl_terms VALUES ('920101', 'local', '', 'Blood Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920102', 'local', '', 'Cancer and Related Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920103', 'local', '', 'Cardiovascular System and Diseases', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920104', 'local', '', 'Diabetes', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920105', 'local', '', 'Digestive System Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920106', 'local', '', 'Endocrine Organs and Diseases (excl. Diabetes)', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920107', 'local', '', 'Hearing, Vision, Speech and Their Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920108', 'local', '', 'Immune System and Allergy', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920109', 'local', '', 'Infectious Diseases', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920110', 'local', '', 'Inherited Diseases (incl. Gene Therapy)', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920111', 'local', '', 'Nervous System and Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920112', 'local', '', 'Neurodegenerative Disorders Related to Ageing', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920113', 'local', '', 'Oro-Dental Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920114', 'local', '', 'Reproductive System and Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920115', 'local', '', 'Respiratory System and Diseases (incl. Asthma)', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920116', 'local', '', 'Skeletal System and Disorders (incl. Arthritis)', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920117', 'local', '', 'Skin and Related Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920118', 'local', '', 'Surgical Methods and Procedures', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920119', 'local', '', 'Urogenital System and Disorders', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920120', 'local', '', 'Zoonoses', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920199', 'local', '', 'Clinical Health (Organs, Diseases and Abnormal Conditions) not elsewhere classified', '', '', 'ANZSRC-SEO', '920100', 'pt', '', 'ANZSRC>>HEALTH>>CLINICAL HEALTH (ORGANS, DISEASES AND ABNORMAL CONDITIONS)', '');
INSERT INTO tbl_terms VALUES ('920200', 'local', '', 'HEALTH AND SUPPORT SERVICES', '', '', 'ANZSRC-SEO', '920000', 'pt', '', 'ANZSRC>>HEALTH', '');
INSERT INTO tbl_terms VALUES ('920201', 'local', '', 'Allied Health Therapies (excl. Mental Health Services)', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920202', 'local', '', 'Carer Health', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920203', 'local', '', 'Diagnostic Methods', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920204', 'local', '', 'Evaluation of Health Outcomes', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920205', 'local', '', 'Health Education and Promotion', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920206', 'local', '', 'Health Inequalities', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920207', 'local', '', 'Health Policy Economic Outcomes', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920208', 'local', '', 'Health Policy Evaluation', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920209', 'local', '', 'Mental Health Services', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920210', 'local', '', 'Nursing', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920211', 'local', '', 'Palliative Care', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920299', 'local', '', 'Health and Support Services not elsewhere classified', '', '', 'ANZSRC-SEO', '920200', 'pt', '', 'ANZSRC>>HEALTH>>HEALTH AND SUPPORT SERVICES', '');
INSERT INTO tbl_terms VALUES ('920300', 'local', '', 'INDIGENOUS HEALTH', '', '', 'ANZSRC-SEO', '920000', 'pt', '', 'ANZSRC>>HEALTH', '');
INSERT INTO tbl_terms VALUES ('920301', 'local', '', 'Aboriginal and Torres Strait Islander Health - Determinants of Health', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920302', 'local', '', 'Aboriginal and Torres Strait Islander Health - Health Status and Outcomes', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920303', 'local', '', 'Aboriginal and Torres Strait Islander Health - Health System Performance (incl. Effectiveness of Interventions)', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920304', 'local', '', 'Maori Health - Determinants of Health', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920305', 'local', '', 'Maori Health - Health Status and Outcomes', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920306', 'local', '', 'Maori Health - Health System Performance (incl. Effectiveness of Interventions)', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920307', 'local', '', 'Pacific Peoples Health - Health Status and Outcomes', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920308', 'local', '', 'Pacific Peoples Health - Determinants of Health', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920309', 'local', '', 'Pacific Peoples Health - Health System Performance (incl. Effectiveness of Interventions)', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920399', 'local', '', 'Indigenous Health not elsewhere classified', '', '', 'ANZSRC-SEO', '920300', 'pt', '', 'ANZSRC>>HEALTH>>INDIGENOUS HEALTH', '');
INSERT INTO tbl_terms VALUES ('920400', 'local', '', 'PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '', '', 'ANZSRC-SEO', '920000', 'pt', '', 'ANZSRC>>HEALTH', '');
INSERT INTO tbl_terms VALUES ('920401', 'local', '', 'Behaviour and Health', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920402', 'local', '', 'Dental Health', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920403', 'local', '', 'Disability and Functional Capacity', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('970116', 'local', '', 'Expanding Knowledge through Studies of Human Society', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('920404', 'local', '', 'Disease Distribution and Transmission (incl. Surveillance and Response)', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920405', 'local', '', 'Environmental Health', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920406', 'local', '', 'Food Safety', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920407', 'local', '', 'Health Protection and/or Disaster Response', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920408', 'local', '', 'Health Status (e.g. Indicators of Well-Being)', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920409', 'local', '', 'Injury Control', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920410', 'local', '', 'Mental Health', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920411', 'local', '', 'Nutrition', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920412', 'local', '', 'Preventive Medicine', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920413', 'local', '', 'Social Structure and Health', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920414', 'local', '', 'Substance Abuse', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920499', 'local', '', 'Public Health (excl. Specific Population Health) not  elsewhere classified', '', '', 'ANZSRC-SEO', '920400', 'pt', '', 'ANZSRC>>HEALTH>>PUBLIC HEALTH (EXCL. SPECIFIC POPULATION HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920500', 'local', '', 'SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '', '', 'ANZSRC-SEO', '920000', 'pt', '', 'ANZSRC>>HEALTH', '');
INSERT INTO tbl_terms VALUES ('920501', 'local', '', 'Child Health', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920502', 'local', '', 'Health Related to Ageing', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920503', 'local', '', 'Health Related to Specific Ethnic Groups', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920504', 'local', '', 'Men''s Health', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920505', 'local', '', 'Occupational Health', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920506', 'local', '', 'Rural Health', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920507', 'local', '', 'Women''s Health', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('920599', 'local', '', 'Specific Population Health (excl. Indigenous Health) not  elsewhere classified', '', '', 'ANZSRC-SEO', '920500', 'pt', '', 'ANZSRC>>HEALTH>>SPECIFIC POPULATION HEALTH (EXCL. INDIGENOUS HEALTH)', '');
INSERT INTO tbl_terms VALUES ('929900', 'local', '', 'OTHER HEALTH', '', '', 'ANZSRC-SEO', '920000', 'pt', '', 'ANZSRC>>HEALTH', '');
INSERT INTO tbl_terms VALUES ('929999', 'local', '', 'Health not elsewhere classified', '', '', 'ANZSRC-SEO', '929900', 'pt', '', 'ANZSRC>>HEALTH>>OTHER HEALTH', '');
INSERT INTO tbl_terms VALUES ('930000', 'local', '', 'EDUCATION AND TRAINING', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('930100', 'local', '', 'LEARNER AND LEARNING', '', '', 'ANZSRC-SEO', '930000', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('930101', 'local', '', 'Learner and Learning Achievement', '', '', 'ANZSRC-SEO', '930100', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>LEARNER AND LEARNING', '');
INSERT INTO tbl_terms VALUES ('930102', 'local', '', 'Learner and Learning Processes', '', '', 'ANZSRC-SEO', '930100', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>LEARNER AND LEARNING', '');
INSERT INTO tbl_terms VALUES ('930103', 'local', '', 'Learner Development', '', '', 'ANZSRC-SEO', '930100', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>LEARNER AND LEARNING', '');
INSERT INTO tbl_terms VALUES ('930104', 'local', '', 'Moral and Social Development (incl. Affect)', '', '', 'ANZSRC-SEO', '930100', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>LEARNER AND LEARNING', '');
INSERT INTO tbl_terms VALUES ('930199', 'local', '', 'Learner and Learning not elsewhere classified', '', '', 'ANZSRC-SEO', '930100', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>LEARNER AND LEARNING', '');
INSERT INTO tbl_terms VALUES ('930200', 'local', '', 'TEACHING AND INSTRUCTION', '', '', 'ANZSRC-SEO', '930000', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('930201', 'local', '', 'Pedagogy', '', '', 'ANZSRC-SEO', '930200', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>TEACHING AND INSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('930202', 'local', '', 'Teacher and Instructor Development', '', '', 'ANZSRC-SEO', '930200', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>TEACHING AND INSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('930203', 'local', '', 'Teaching and Instruction Technologies', '', '', 'ANZSRC-SEO', '930200', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>TEACHING AND INSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('930299', 'local', '', 'Teaching and Instruction not elsewhere classified', '', '', 'ANZSRC-SEO', '930200', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>TEACHING AND INSTRUCTION', '');
INSERT INTO tbl_terms VALUES ('930300', 'local', '', 'CURRICULUM', '', '', 'ANZSRC-SEO', '930000', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('930301', 'local', '', 'Assessment and Evaluation of Curriculum', '', '', 'ANZSRC-SEO', '930300', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>CURRICULUM', '');
INSERT INTO tbl_terms VALUES ('930302', 'local', '', 'Syllabus and Curriculum Development', '', '', 'ANZSRC-SEO', '930300', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>CURRICULUM', '');
INSERT INTO tbl_terms VALUES ('930399', 'local', '', 'Curriculum not elsewhere classified', '', '', 'ANZSRC-SEO', '930300', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>CURRICULUM', '');
INSERT INTO tbl_terms VALUES ('930400', 'local', '', 'SCHOOL/INSTITUTION', '', '', 'ANZSRC-SEO', '930000', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('930401', 'local', '', 'Management and Leadership of Schools/Institutions', '', '', 'ANZSRC-SEO', '930400', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>SCHOOL/INSTITUTION', '');
INSERT INTO tbl_terms VALUES ('930402', 'local', '', 'School/Institution Community and Environment', '', '', 'ANZSRC-SEO', '930400', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>SCHOOL/INSTITUTION', '');
INSERT INTO tbl_terms VALUES ('930403', 'local', '', 'School/Institution Policies and Development', '', '', 'ANZSRC-SEO', '930400', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>SCHOOL/INSTITUTION', '');
INSERT INTO tbl_terms VALUES ('930499', 'local', '', 'School/Institution not elsewhere classified', '', '', 'ANZSRC-SEO', '930400', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>SCHOOL/INSTITUTION', '');
INSERT INTO tbl_terms VALUES ('930500', 'local', '', 'EDUCATION AND TRAINING SYSTEMS', '', '', 'ANZSRC-SEO', '930000', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('930501', 'local', '', 'Education and Training Systems Policies and Development', '', '', 'ANZSRC-SEO', '930500', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>EDUCATION AND TRAINING SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('930502', 'local', '', 'Management of Education and Training Systems', '', '', 'ANZSRC-SEO', '930500', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>EDUCATION AND TRAINING SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('930503', 'local', '', 'Resourcing of Education and Training Systems', '', '', 'ANZSRC-SEO', '930500', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>EDUCATION AND TRAINING SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('930599', 'local', '', 'Education and Training Systems not elsewhere classified', '', '', 'ANZSRC-SEO', '930500', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>EDUCATION AND TRAINING SYSTEMS', '');
INSERT INTO tbl_terms VALUES ('939900', 'local', '', 'OTHER EDUCATION AND TRAINING', '', '', 'ANZSRC-SEO', '930000', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939901', 'local', '', 'Aboriginal and Torres Strait Islander Education', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939902', 'local', '', 'Education and Training Theory and Methodology', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939903', 'local', '', 'Equity and Access to Education', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939904', 'local', '', 'Gender Aspects of Education', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939905', 'local', '', 'Maori Education', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939906', 'local', '', 'Pacific Peoples Education', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939907', 'local', '', 'Special Needs Education', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939908', 'local', '', 'Workforce Transition and Employment', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('939999', 'local', '', 'Education and Training not elsewhere classified', '', '', 'ANZSRC-SEO', '939900', 'pt', '', 'ANZSRC>>EDUCATION AND TRAINING>>OTHER EDUCATION AND TRAINING', '');
INSERT INTO tbl_terms VALUES ('940000', 'local', '', 'LAW, POLITICS AND COMMUNITY SERVICES', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('940100', 'local', '', 'COMMUNITY SERVICE (EXCL. WORK)', '', '', 'ANZSRC-SEO', '940000', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('940101', 'local', '', 'Ability and Disability', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940102', 'local', '', 'Aboriginal and Torres Strait Islander Development and Welfare', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940103', 'local', '', 'Ageing and Older People', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940104', 'local', '', 'Carers'' Development and Welfare', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940105', 'local', '', 'Children''s/Youth Services and Childcare', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940106', 'local', '', 'Citizenship and National Identity', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940107', 'local', '', 'Comparative Structure and Development of Community Services', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940108', 'local', '', 'Distribution of Income and Wealth', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940109', 'local', '', 'Employment Services', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940110', 'local', '', 'Environmental Services', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940111', 'local', '', 'Ethnicity, Multiculturalism and Migrant Development and Welfare', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940112', 'local', '', 'Families and Family Services', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940113', 'local', '', 'Gender and Sexualities', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940114', 'local', '', 'Maori Development and Welfare', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940115', 'local', '', 'Pacific Peoples Development and Welfare', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940116', 'local', '', 'Social Class and Inequalities', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940117', 'local', '', 'Structure, Delivery and Financing of Community Services', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940199', 'local', '', 'Community Service (excl. Work) not elsewhere classified', '', '', 'ANZSRC-SEO', '940100', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>COMMUNITY SERVICE (EXCL. WORK)', '');
INSERT INTO tbl_terms VALUES ('940200', 'local', '', 'GOVERNMENT AND POLITICS', '', '', 'ANZSRC-SEO', '940000', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('940201', 'local', '', 'Civics and Citizenship', '', '', 'ANZSRC-SEO', '940200', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>GOVERNMENT AND POLITICS', '');
INSERT INTO tbl_terms VALUES ('940202', 'local', '', 'Electoral Systems', '', '', 'ANZSRC-SEO', '940200', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>GOVERNMENT AND POLITICS', '');
INSERT INTO tbl_terms VALUES ('940203', 'local', '', 'Political Systems', '', '', 'ANZSRC-SEO', '940200', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>GOVERNMENT AND POLITICS', '');
INSERT INTO tbl_terms VALUES ('940204', 'local', '', 'Public Services Policy Advice and Analysis', '', '', 'ANZSRC-SEO', '940200', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>GOVERNMENT AND POLITICS', '');
INSERT INTO tbl_terms VALUES ('940299', 'local', '', 'Government and Politics not elsewhere classified', '', '', 'ANZSRC-SEO', '940200', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>GOVERNMENT AND POLITICS', '');
INSERT INTO tbl_terms VALUES ('940300', 'local', '', 'INTERNATIONAL RELATIONS', '', '', 'ANZSRC-SEO', '940000', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('940301', 'local', '', 'Defence and Security Policy', '', '', 'ANZSRC-SEO', '940300', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>INTERNATIONAL RELATIONS', '');
INSERT INTO tbl_terms VALUES ('940302', 'local', '', 'International Aid and Development', '', '', 'ANZSRC-SEO', '940300', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>INTERNATIONAL RELATIONS', '');
INSERT INTO tbl_terms VALUES ('940303', 'local', '', 'International Organisations', '', '', 'ANZSRC-SEO', '940300', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>INTERNATIONAL RELATIONS', '');
INSERT INTO tbl_terms VALUES ('940304', 'local', '', 'International Political Economy (excl. International Trade)', '', '', 'ANZSRC-SEO', '940300', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>INTERNATIONAL RELATIONS', '');
INSERT INTO tbl_terms VALUES ('940399', 'local', '', 'International Relations not elsewhere classified', '', '', 'ANZSRC-SEO', '940300', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>INTERNATIONAL RELATIONS', '');
INSERT INTO tbl_terms VALUES ('940400', 'local', '', 'JUSTICE AND THE LAW', '', '', 'ANZSRC-SEO', '940000', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('940401', 'local', '', 'Civil Justice', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940402', 'local', '', 'Crime Prevention', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940403', 'local', '', 'Criminal Justice', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940404', 'local', '', 'Law Enforcement', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940405', 'local', '', 'Law Reform', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940406', 'local', '', 'Legal Processes', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940407', 'local', '', 'Legislation, Civil and Criminal Codes', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940408', 'local', '', 'Rehabilitation and Correctional Services', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940499', 'local', '', 'Justice and the Law not elsewhere classified', '', '', 'ANZSRC-SEO', '940400', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>JUSTICE AND THE LAW', '');
INSERT INTO tbl_terms VALUES ('940500', 'local', '', 'WORK AND INSTITUTIONAL DEVELOPMENT', '', '', 'ANZSRC-SEO', '940000', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('940501', 'local', '', 'Employment Patterns and Change', '', '', 'ANZSRC-SEO', '940500', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>WORK AND INSTITUTIONAL DEVELOPMENT', '');
INSERT INTO tbl_terms VALUES ('940502', 'local', '', 'Professions and Professionalisation', '', '', 'ANZSRC-SEO', '940500', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>WORK AND INSTITUTIONAL DEVELOPMENT', '');
INSERT INTO tbl_terms VALUES ('940503', 'local', '', 'Time Use, Unpaid Work and Volunteering', '', '', 'ANZSRC-SEO', '940500', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>WORK AND INSTITUTIONAL DEVELOPMENT', '');
INSERT INTO tbl_terms VALUES ('940504', 'local', '', 'Work and Family Responsibilities', '', '', 'ANZSRC-SEO', '940500', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>WORK AND INSTITUTIONAL DEVELOPMENT', '');
INSERT INTO tbl_terms VALUES ('940505', 'local', '', 'Workplace Safety', '', '', 'ANZSRC-SEO', '940500', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>WORK AND INSTITUTIONAL DEVELOPMENT', '');
INSERT INTO tbl_terms VALUES ('ar', 'local', '', 'Applied research', '', '', 'ANZSRC-TOA', NULL, 'pt', '', 'ANZSRC>>Type of Activity', NULL);
INSERT INTO tbl_terms VALUES ('940599', 'local', '', 'Work and Institutional Development not elsewhere classified', '', '', 'ANZSRC-SEO', '940500', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>WORK AND INSTITUTIONAL DEVELOPMENT', '');
INSERT INTO tbl_terms VALUES ('949900', 'local', '', 'OTHER LAW, POLITICS AND COMMUNITY SERVICES', '', '', 'ANZSRC-SEO', '940000', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('949999', 'local', '', 'Law, Politics and Community Services not elsewhere  classified', '', '', 'ANZSRC-SEO', '949900', 'pt', '', 'ANZSRC>>LAW, POLITICS AND COMMUNITY SERVICES>>OTHER LAW, POLITICS AND COMMUNITY SERVICES', '');
INSERT INTO tbl_terms VALUES ('950000', 'local', '', 'CULTURAL UNDERSTANDING', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('950100', 'local', '', 'ARTS AND LEISURE', '', '', 'ANZSRC-SEO', '950000', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('950101', 'local', '', 'Music', '', '', 'ANZSRC-SEO', '950100', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>ARTS AND LEISURE', '');
INSERT INTO tbl_terms VALUES ('950102', 'local', '', 'Organised Sports', '', '', 'ANZSRC-SEO', '950100', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>ARTS AND LEISURE', '');
INSERT INTO tbl_terms VALUES ('950103', 'local', '', 'Recreation', '', '', 'ANZSRC-SEO', '950100', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>ARTS AND LEISURE', '');
INSERT INTO tbl_terms VALUES ('950104', 'local', '', 'The Creative Arts (incl. Graphics and Craft)', '', '', 'ANZSRC-SEO', '950100', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>ARTS AND LEISURE', '');
INSERT INTO tbl_terms VALUES ('950105', 'local', '', 'The Performing Arts (incl. Theatre and Dance)', '', '', 'ANZSRC-SEO', '950100', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>ARTS AND LEISURE', '');
INSERT INTO tbl_terms VALUES ('950199', 'local', '', 'Arts and Leisure not elsewhere classified', '', '', 'ANZSRC-SEO', '950100', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>ARTS AND LEISURE', '');
INSERT INTO tbl_terms VALUES ('950200', 'local', '', 'COMMUNICATION', '', '', 'ANZSRC-SEO', '950000', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('950201', 'local', '', 'Communication Across Languages and Culture', '', '', 'ANZSRC-SEO', '950200', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>COMMUNICATION', '');
INSERT INTO tbl_terms VALUES ('950202', 'local', '', 'Languages and Literacy', '', '', 'ANZSRC-SEO', '950200', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>COMMUNICATION', '');
INSERT INTO tbl_terms VALUES ('950203', 'local', '', 'Languages and Literature', '', '', 'ANZSRC-SEO', '950200', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>COMMUNICATION', '');
INSERT INTO tbl_terms VALUES ('950204', 'local', '', 'The Media', '', '', 'ANZSRC-SEO', '950200', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>COMMUNICATION', '');
INSERT INTO tbl_terms VALUES ('950205', 'local', '', 'Visual Communication', '', '', 'ANZSRC-SEO', '950200', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>COMMUNICATION', '');
INSERT INTO tbl_terms VALUES ('950299', 'local', '', 'Communication not elsewhere classified', '', '', 'ANZSRC-SEO', '950200', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>COMMUNICATION', '');
INSERT INTO tbl_terms VALUES ('950300', 'local', '', 'HERITAGE', '', '', 'ANZSRC-SEO', '950000', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('950301', 'local', '', 'Ahuatanga Maori (Maori Tradition)', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950302', 'local', '', 'Conserving Aboriginal and Torres Strait Islander Heritage', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950303', 'local', '', 'Conserving Collections and Movable Cultural Heritage', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950304', 'local', '', 'Conserving Intangible Cultural Heritage', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950305', 'local', '', 'Conserving Natural Heritage', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950306', 'local', '', 'Conserving Pacific Peoples Heritage', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950307', 'local', '', 'Conserving the Historic Environment', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950308', 'local', '', 'Matauranga Maori (Maori Knowledge)', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950309', 'local', '', 'Taonga (Maori Artefacts)', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950310', 'local', '', 'Tikanga Maori (Maori Customary Practices)', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950311', 'local', '', 'Wahi Taonga (Maori Places of Significance)', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950399', 'local', '', 'Heritage not elsewhere classified', '', '', 'ANZSRC-SEO', '950300', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>HERITAGE', '');
INSERT INTO tbl_terms VALUES ('950400', 'local', '', 'RELIGION AND ETHICS', '', '', 'ANZSRC-SEO', '950000', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('950401', 'local', '', 'Bioethics', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950402', 'local', '', 'Business Ethics', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950403', 'local', '', 'Environmental Ethics', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950404', 'local', '', 'Religion and Society', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950405', 'local', '', 'Religious Structures and Ritual', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950406', 'local', '', 'Religious Traditions (excl. Structures and Rituals)', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950407', 'local', '', 'Social Ethics', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950408', 'local', '', 'Technological Ethics', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950409', 'local', '', 'Workplace and Organisational Ethics', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950499', 'local', '', 'Religion and Ethics not elsewhere classified', '', '', 'ANZSRC-SEO', '950400', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>RELIGION AND ETHICS', '');
INSERT INTO tbl_terms VALUES ('950500', 'local', '', 'UNDERSTANDING PAST SOCIETIES', '', '', 'ANZSRC-SEO', '950000', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('950501', 'local', '', 'Understanding Africa''s Past', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('950502', 'local', '', 'Understanding Asia''s Past', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('950503', 'local', '', 'Understanding Australia''s Past', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('950504', 'local', '', 'Understanding Europe''s Past', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('950505', 'local', '', 'Understanding New Zealand''s Past', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('950506', 'local', '', 'Understanding the Past of the Americas', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('950599', 'local', '', 'Understanding Past Societies not elsewhere classified', '', '', 'ANZSRC-SEO', '950500', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>UNDERSTANDING PAST SOCIETIES', '');
INSERT INTO tbl_terms VALUES ('959900', 'local', '', 'OTHER CULTURAL UNDERSTANDING', '', '', 'ANZSRC-SEO', '950000', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('959999', 'local', '', 'Cultural Understanding not elsewhere classified', '', '', 'ANZSRC-SEO', '959900', 'pt', '', 'ANZSRC>>CULTURAL UNDERSTANDING>>OTHER CULTURAL UNDERSTANDING', '');
INSERT INTO tbl_terms VALUES ('960000', 'local', '', 'ENVIRONMENT', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('960100', 'local', '', 'AIR QUALITY', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960101', 'local', '', 'Antarctic and Sub-Antarctic Air Quality', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960102', 'local', '', 'Coastal and Estuarine Air Quality', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960103', 'local', '', 'Farmland, Arable Cropland and Permanent Cropland Air Quality', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960104', 'local', '', 'Marine Air Quality', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960105', 'local', '', 'Mining Air Quality', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960106', 'local', '', 'Urban and Industrial Air Quality', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960199', 'local', '', 'Air Quality not elsewhere classified', '', '', 'ANZSRC-SEO', '960100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>AIR QUALITY', '');
INSERT INTO tbl_terms VALUES ('960200', 'local', '', 'ATMOSPHERE AND WEATHER', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960201', 'local', '', 'Atmospheric Composition (incl. Greenhouse Gas Inventory)', '', '', 'ANZSRC-SEO', '960200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ATMOSPHERE AND WEATHER', '');
INSERT INTO tbl_terms VALUES ('960202', 'local', '', 'Atmospheric Processes and Dynamics', '', '', 'ANZSRC-SEO', '960200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ATMOSPHERE AND WEATHER', '');
INSERT INTO tbl_terms VALUES ('960203', 'local', '', 'Weather', '', '', 'ANZSRC-SEO', '960200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ATMOSPHERE AND WEATHER', '');
INSERT INTO tbl_terms VALUES ('960299', 'local', '', 'Atmosphere and Weather not elsewhere classified', '', '', 'ANZSRC-SEO', '960200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ATMOSPHERE AND WEATHER', '');
INSERT INTO tbl_terms VALUES ('960300', 'local', '', 'CLIMATE AND CLIMATE CHANGE', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960301', 'local', '', 'Climate Change Adaptation Measures', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960302', 'local', '', 'Climate Change Mitigation Strategies', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960303', 'local', '', 'Climate Change Models', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960304', 'local', '', 'Climate Variability (excl. Social Impacts)', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960305', 'local', '', 'Ecosystem Adaptation to Climate Change', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960306', 'local', '', 'Effects of Climate Change and Variability on Antarctic and Sub-Antarctic Environments (excl. Social Impacts)', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960307', 'local', '', 'Effects of Climate Change and Variability on Australia (excl.  Social Impacts)', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960308', 'local', '', 'Effects of Climate Change and Variability on New Zealand (excl. Social Impacts)', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960309', 'local', '', 'Effects of Climate Change and Variability on the South Pacific (excl. Australia and New Zealand) (excl. Social Impacts)', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960310', 'local', '', 'Global Effects of Climate Change and Variability (excl. Australia, New Zealand, Antarctica and the South Pacific) (excl. Social Impacts)', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960311', 'local', '', 'Social Impacts of Climate Change and Variability', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960399', 'local', '', 'Climate and Climate Change not elsewhere classified', '', '', 'ANZSRC-SEO', '960300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CLIMATE AND CLIMATE CHANGE', '');
INSERT INTO tbl_terms VALUES ('960400', 'local', '', 'CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960401', 'local', '', 'Border Biosecurity (incl. Quarantine and Inspection)', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960402', 'local', '', 'Control of Animal Pests, Diseases and Exotic Species in Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960403', 'local', '', 'Control of Animal Pests, Diseases and Exotic Species in  Farmland, Arable Cropland and Permanent Cropland Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960404', 'local', '', 'Control of Animal Pests, Diseases and Exotic Species in  Forest and Woodlands Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960405', 'local', '', 'Control of Pests, Diseases and Exotic Species at Regional or  Larger Scales', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960406', 'local', '', 'Control of Pests, Diseases and Exotic Species in Fresh, Ground and Surface Water Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960407', 'local', '', 'Control of Pests, Diseases and Exotic Species in Marine Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960408', 'local', '', 'Control of Pests, Diseases and Exotic Species in Mining Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960409', 'local', '', 'Control of Pests, Diseases and Exotic Species in Mountain and High Country Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960410', 'local', '', 'Control of Pests, Diseases and Exotic Species in Sparseland, Permanent Grassland and Arid Zone Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960411', 'local', '', 'Control of Pests, Diseases and Exotic Species in Urban and Industrial Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('970115', 'local', '', 'Expanding Knowledge in Commerce, Management, Tourism and Services', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('960412', 'local', '', 'Control of Plant Pests, Diseases and Exotic Species in  Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960413', 'local', '', 'Control of Plant Pests, Diseases and Exotic Species in  Farmland, Arable Cropland and Permanent Cropland Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960414', 'local', '', 'Control of Plant Pests, Diseases and Exotic Species in Forest and Woodlands Environments', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960415', 'local', '', 'Pre-Border Biosecurity', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960499', 'local', '', 'Control of Pests, Diseases and Exotic Species not elsewhere  classified', '', '', 'ANZSRC-SEO', '960400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>CONTROL OF PESTS, DISEASES AND EXOTIC SPECIES', '');
INSERT INTO tbl_terms VALUES ('960500', 'local', '', 'ECOSYSTEM ASSESSMENT AND MANAGEMENT', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960501', 'local', '', 'Ecosystem Assessment and Management at Regional or Larger Scales', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960502', 'local', '', 'Ecosystem Assessment and Management of Antarctic and Sub-Antarctic Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960503', 'local', '', 'Ecosystem Assessment and Management of Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960504', 'local', '', 'Ecosystem Assessment and Management of Farmland, Arable Cropland and Permanent Cropland Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960505', 'local', '', 'Ecosystem Assessment and Management of Forest and Woodlands Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960506', 'local', '', 'Ecosystem Assessment and Management of Fresh, Ground and Surface Water Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960507', 'local', '', 'Ecosystem Assessment and Management of Marine Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960508', 'local', '', 'Ecosystem Assessment and Management of Mining Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960509', 'local', '', 'Ecosystem Assessment and Management of Mountain and High Country Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960510', 'local', '', 'Ecosystem Assessment and Management of Sparseland, Permanent Grassland and Arid Zone Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960511', 'local', '', 'Ecosystem Assessment and Management of Urban and Industrial Environments', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960599', 'local', '', 'Ecosystem Assessment and Management not elsewhere', '', '', 'ANZSRC-SEO', '960500', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ECOSYSTEM ASSESSMENT AND MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960600', 'local', '', 'ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960601', 'local', '', 'Economic Incentives for Environmental Protection', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960602', 'local', '', 'Eco-Verification (excl. Environmental Lifecycle Assessment)', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960603', 'local', '', 'Environmental Lifecycle Assessment', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960604', 'local', '', 'Environmental Management Systems', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960605', 'local', '', 'Institutional Arrangements for Environmental Protection', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960606', 'local', '', 'Rights to Environmental and Natural Resources (excl. Water Allocation)', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960607', 'local', '', 'Rural Land Evaluation', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960608', 'local', '', 'Rural Water Evaluation (incl. Water Quality)', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960609', 'local', '', 'Sustainability Indicators', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960610', 'local', '', 'Urban Land Evaluation', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960611', 'local', '', 'Urban Water Evaluation (incl. Water Quality)', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960699', 'local', '', 'Environmental and Natural Resource Evaluation not elsewhere classified', '', '', 'ANZSRC-SEO', '960600', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL AND NATURAL RESOURCE EVALUATION', '');
INSERT INTO tbl_terms VALUES ('960700', 'local', '', 'ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960701', 'local', '', 'Coastal and Marine Management Policy', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960702', 'local', '', 'Consumption Patterns, Population Issues and the Environment', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960703', 'local', '', 'Environmental Education and Awareness', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960704', 'local', '', 'Land Stewardship', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960705', 'local', '', 'Rural Land Policy', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960706', 'local', '', 'Rural Water Policy', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960707', 'local', '', 'Trade and Environment', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960708', 'local', '', 'Urban Land Policy', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960709', 'local', '', 'Urban Water Policy', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960799', 'local', '', 'Environmental Policy, Legislation and Standards not elsewhere classified', '', '', 'ANZSRC-SEO', '960700', 'pt', '', 'ANZSRC>>ENVIRONMENT>>ENVIRONMENTAL POLICY, LEGISLATION AND STANDARDS', '');
INSERT INTO tbl_terms VALUES ('960800', 'local', '', 'FLORA, FAUNA AND BIODIVERSITY', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960801', 'local', '', 'Antarctic and Sub-Antarctic Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960802', 'local', '', 'Coastal and Estuarine Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960803', 'local', '', 'Documentation of Undescribed Flora and Fauna', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960804', 'local', '', 'Farmland, Arable Cropland and Permanent Cropland Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960805', 'local', '', 'Flora, Fauna and Biodiversity at Regional or Larger Scales', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960806', 'local', '', 'Forest and Woodlands Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960807', 'local', '', 'Fresh, Ground and Surface Water Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960808', 'local', '', 'Marine Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960809', 'local', '', 'Mining Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960810', 'local', '', 'Mountain and High Country Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960811', 'local', '', 'Sparseland, Permanent Grassland and Arid Zone Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960812', 'local', '', 'Urban and Industrial Flora, Fauna and Biodiversity', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960899', 'local', '', 'Flora, Fauna and Biodiversity of environments not elsewhere classified', '', '', 'ANZSRC-SEO', '960800', 'pt', '', 'ANZSRC>>ENVIRONMENT>>FLORA, FAUNA AND BIODIVERSITY', '');
INSERT INTO tbl_terms VALUES ('960900', 'local', '', 'LAND AND WATER MANAGEMENT', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('960901', 'local', '', 'Antarctic and Sub-Antarctic Land and Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960902', 'local', '', 'Coastal and Estuarine Land Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960903', 'local', '', 'Coastal and Estuarine Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960904', 'local', '', 'Farmland, Arable Cropland and Permanent Cropland Land Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960905', 'local', '', 'Farmland, Arable Cropland and Permanent Cropland Water  Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960906', 'local', '', 'Forest and Woodlands Land Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960907', 'local', '', 'Forest and Woodlands Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960908', 'local', '', 'Mining Land and Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960909', 'local', '', 'Mountain and High Country Land and Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960910', 'local', '', 'Sparseland, Permanent Grassland and Arid Zone Land and  Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960911', 'local', '', 'Urban and Industrial Land Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960912', 'local', '', 'Urban and Industrial Water Management', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960913', 'local', '', 'Water Allocation and Quantification', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('960999', 'local', '', 'Land and Water Management of environments not  elsewhere classified', '', '', 'ANZSRC-SEO', '960900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>LAND AND WATER MANAGEMENT', '');
INSERT INTO tbl_terms VALUES ('961000', 'local', '', 'NATURAL HAZARDS', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('961001', 'local', '', 'Natural Hazards in Antarctic and Sub-Antarctic Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961002', 'local', '', 'Natural Hazards in Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961003', 'local', '', 'Natural Hazards in Farmland, Arable Cropland and Permanent Cropland Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961004', 'local', '', 'Natural Hazards in Forest and Woodlands Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961005', 'local', '', 'Natural Hazards in Fresh, Ground and Surface Water  Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961006', 'local', '', 'Natural Hazards in Marine Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961007', 'local', '', 'Natural Hazards in Mining Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961008', 'local', '', 'Natural Hazards in Mountain and High Country Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961009', 'local', '', 'Natural Hazards in Sparseland, Permanent Grassland and  Arid Zone Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961010', 'local', '', 'Natural Hazards in Urban and Industrial Environments', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961099', 'local', '', 'Natural Hazards not elsewhere classified', '', '', 'ANZSRC-SEO', '961000', 'pt', '', 'ANZSRC>>ENVIRONMENT>>NATURAL HAZARDS', '');
INSERT INTO tbl_terms VALUES ('961100', 'local', '', 'PHYSICAL AND CHEMICAL CONDITIONS OF WATER', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('961101', 'local', '', 'Physical and Chemical Conditions of Water for Urban and Industrial Use', '', '', 'ANZSRC-SEO', '961100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>PHYSICAL AND CHEMICAL CONDITIONS OF WATER', '');
INSERT INTO tbl_terms VALUES ('961102', 'local', '', 'Physical and Chemical Conditions of Water in Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '961100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>PHYSICAL AND CHEMICAL CONDITIONS OF WATER', '');
INSERT INTO tbl_terms VALUES ('961103', 'local', '', 'Physical and Chemical Conditions of Water in Fresh, Ground and Surface Water Environments (excl. Urban and Industrial Use)', '', '', 'ANZSRC-SEO', '961100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>PHYSICAL AND CHEMICAL CONDITIONS OF WATER', '');
INSERT INTO tbl_terms VALUES ('961104', 'local', '', 'Physical and Chemical Conditions of Water in Marine Environments', '', '', 'ANZSRC-SEO', '961100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>PHYSICAL AND CHEMICAL CONDITIONS OF WATER', '');
INSERT INTO tbl_terms VALUES ('961199', 'local', '', 'Physical and Chemical Conditions of Water not elsewhere classified', '', '', 'ANZSRC-SEO', '961100', 'pt', '', 'ANZSRC>>ENVIRONMENT>>PHYSICAL AND CHEMICAL CONDITIONS OF WATER', '');
INSERT INTO tbl_terms VALUES ('961200', 'local', '', 'REHABILITATION OF DEGRADED ENVIRONMENTS', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('961201', 'local', '', 'Rehabilitation of Degraded Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961202', 'local', '', 'Rehabilitation of Degraded Farmland, Arable Cropland and Permanent Cropland Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961203', 'local', '', 'Rehabilitation of Degraded Forest and Woodlands Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961204', 'local', '', 'Rehabilitation of Degraded Fresh, Ground and Surface Water Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961205', 'local', '', 'Rehabilitation of Degraded Mining Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961206', 'local', '', 'Rehabilitation of Degraded Mountain and High Country Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('890202', 'local', '', 'Application Tools and System Utilities', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('961207', 'local', '', 'Rehabilitation of Degraded Sparseland, Permanent Grassland and Arid Zone Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961208', 'local', '', 'Rehabilitation of Degraded Urban and Industrial Environments', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961299', 'local', '', 'Rehabilitation of Degraded Environments not elsewhere classified', '', '', 'ANZSRC-SEO', '961200', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REHABILITATION OF DEGRADED ENVIRONMENTS', '');
INSERT INTO tbl_terms VALUES ('961300', 'local', '', 'REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('961301', 'local', '', 'Protected Conservation Areas in Antarctic and Sub-Antarctic Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961302', 'local', '', 'Protected Conservation Areas in Fresh, Ground and Surface Water Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961303', 'local', '', 'Protected Conservation Areas in Marine Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961304', 'local', '', 'Remnant Vegetation and Protected Conservation Areas in Coastal and Estuarine Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961305', 'local', '', 'Remnant Vegetation and Protected Conservation Areas in Farmland, Arable Cropland and Permanent Cropland Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961306', 'local', '', 'Remnant Vegetation and Protected Conservation Areas in Forest and Woodlands Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961307', 'local', '', 'emnant Vegetation and Protected Conservation Areas in Mountain and High Country Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961308', 'local', '', 'Remnant Vegetation and Protected Conservation Areas at Regional or Larger Scales', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961309', 'local', '', 'Remnant Vegetation and Protected Conservation Areas in Sparseland, Permanent Grassland and Arid Zone Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961310', 'local', '', 'Remnant Vegetation and Protected Conservation Areas in Urban and Industrial Environments', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961399', 'local', '', 'Remnant Vegetation and Protected Conservation Areas not elsewhere classified', '', '', 'ANZSRC-SEO', '961300', 'pt', '', 'ANZSRC>>ENVIRONMENT>>REMNANT VEGETATION AND PROTECTED CONSERVATION AREAS', '');
INSERT INTO tbl_terms VALUES ('961400', 'local', '', 'SOILS', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('961401', 'local', '', 'Coastal and Estuarine Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961402', 'local', '', 'Farmland, Arable Cropland and Permanent Cropland Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961403', 'local', '', 'Forest and Woodlands Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961404', 'local', '', 'Mining Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961405', 'local', '', 'Mountain and High Country Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961406', 'local', '', 'Sparseland, Permanent Grassland and Arid Zone Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961407', 'local', '', 'Urban and Industrial Soils', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('961499', 'local', '', 'Soils not elsewhere classified', '', '', 'ANZSRC-SEO', '961400', 'pt', '', 'ANZSRC>>ENVIRONMENT>>SOILS', '');
INSERT INTO tbl_terms VALUES ('969900', 'local', '', 'OTHER ENVIRONMENT', '', '', 'ANZSRC-SEO', '960000', 'pt', '', 'ANZSRC>>ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('969901', 'local', '', 'Antarctic and Sub-Antarctic Oceanography', '', '', 'ANZSRC-SEO', '969900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>OTHER ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('969902', 'local', '', 'Marine Oceanic Processes (excl. climate related)', '', '', 'ANZSRC-SEO', '969900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>OTHER ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('969999', 'local', '', 'Environment not elsewhere classified', '', '', 'ANZSRC-SEO', '969900', 'pt', '', 'ANZSRC>>ENVIRONMENT>>OTHER ENVIRONMENT', '');
INSERT INTO tbl_terms VALUES ('970000', 'local', '', 'EXPANDING KNOWLEDGE', '', '', 'ANZSRC-SEO', '', 'pt', '', 'ANZSRC', '');
INSERT INTO tbl_terms VALUES ('970100', 'local', '', 'EXPANDING KNOWLEDGE', '', '', 'ANZSRC-SEO', '970000', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970101', 'local', '', 'Expanding Knowledge in the Mathematical Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970102', 'local', '', 'Expanding Knowledge in the Physical Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970103', 'local', '', 'Expanding Knowledge in the Chemical Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970104', 'local', '', 'Expanding Knowledge in the Earth Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970105', 'local', '', 'Expanding Knowledge in the Environmental Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970106', 'local', '', 'Expanding Knowledge in the Biological Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970107', 'local', '', 'Expanding Knowledge in the Agricultural and Veterinary Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970108', 'local', '', 'Expanding Knowledge in the Information and Computing Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970109', 'local', '', 'Expanding Knowledge in Engineering', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970110', 'local', '', 'Expanding Knowledge in Technology', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970111', 'local', '', 'Expanding Knowledge in the Medical and Health Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970117', 'local', '', 'Expanding Knowledge in Psychology and Cognitive  Sciences', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970118', 'local', '', 'Expanding Knowledge in Law and Legal Studies', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970119', 'local', '', 'Expanding Knowledge through Studies of the Creative Arts and Writing', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970120', 'local', '', 'Expanding Knowledge in Languages, Communication and Culture', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970121', 'local', '', 'Expanding Knowledge in History and Archaeology', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('970122', 'local', '', 'Expanding Knowledge in Philosophy and Religious Studies', '', '', 'ANZSRC-SEO', '970100', 'pt', '', 'ANZSRC>>EXPANDING KNOWLEDGE>>EXPANDING KNOWLEDGE', '');
INSERT INTO tbl_terms VALUES ('sbr', 'local', '', 'Strategic basic research', '', '', 'ANZSRC-TOA', NULL, 'pt', '', 'ANZSRC>>Type of Activity', '');
INSERT INTO tbl_terms VALUES ('pbr', 'local', '', 'Pure basic research', '', '', 'ANZSRC-TOA', NULL, 'pt', '', 'ANZSRC>>Type of Activity', NULL);
INSERT INTO tbl_terms VALUES ('ed', 'local', '', 'Experimental development', '', '', 'ANZSRC-TOA', NULL, 'pt', '', 'ANZSRC>>Type of Activity', '');
INSERT INTO tbl_terms VALUES ('infouri', 'local', '', 'infouri', '"info" URI scheme', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('infouri', 'local', '', 'infouri', '"info" URI scheme', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('abbreviated', 'local', '', 'abbreviated', 'shortened form of, or acronym for, the official name', '', 'RIFCSNameType', NULL, 'pt', '', 'RIFCS Name Type', '');
INSERT INTO tbl_terms VALUES ('abn', 'local', '', 'abn', 'Australian Business Number', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('890204', 'local', '', 'Computer Time Leasing, Sharing and Renting Services', '', '', 'ANZSRC-SEO', '890200', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMPUTER SOFTWARE AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('ACS', 'local', '', 'ACS', 'American Chemical Society', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('addressLine', 'local', '', 'addressLine', 'an address part that is a separate line of a structured address', '', 'RIFCSPhysicalAddressPartType', NULL, 'pt', '', 'RIFCS Physical Address Part Type', '');
INSERT INTO tbl_terms VALUES ('addsValueTo', 'local', '', 'addsValueTo', 'specialisation of isSupportBy type - for Annotate', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('AGLC', 'local', '', 'AGLC', 'Australian Guide to Legal Citation', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('AGPS-AGIMO', 'local', '', 'AGPS-AGIMO', 'Australian Style Manual', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('alternative', 'local', '', 'alternative', 'any other form of name used now or in the past as a substitute or alternative for the official name', '', 'RIFCSNameType', NULL, 'pt', '', 'RIFCS Name Type', '');
INSERT INTO tbl_terms VALUES ('AMA', 'local', '', 'AMA', 'American Medical Association', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('annotate', 'local', '', 'annotate', '', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('anzsrc', 'local', '', 'anzsrc', 'ANZSRC classifications', 'sn', 'RIFCSSubjectType', NULL, 'nl', '', 'RIF-CS Subject Type', '');
INSERT INTO tbl_terms VALUES ('anzsrc-for', 'local', '', 'anzsrc-for', 'ANZSRC Field of Research', 'sn', 'RIFCSSubjectType', 'anzsrc', 'pt', '', 'ANZSRC Classification>>Field of Research', '');
INSERT INTO tbl_terms VALUES ('anzsrc-seo', 'local', '', 'anzsrc-seo', 'ANZSRC Socio-Economic Objective', 'sn', 'RIFCSSubjectType', 'anzsrc', 'pt', '', 'ANZSRC Classification>>Socio-Economic Objective', '');
INSERT INTO tbl_terms VALUES ('anzsrc-toa', 'local', '', 'anzsrc-toa', 'ANZSRC Type of Activity', 'sn', 'RIFCSSubjectType', 'anzsrc', 'pt', '', 'ANZSRC Classification>>Type of Activity', '');
INSERT INTO tbl_terms VALUES ('APA', 'local', '', 'APA', 'American Psychological Association', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('arc', 'local', '', 'arc', 'Australian Research Council identifier', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('ark', 'local', '', 'ark', 'ARK Persistent Identifier Scheme', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('ark', 'local', '', 'ark', 'ARK Persistent Identifier Scheme', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('ark', 'local', '', 'ark', 'ARK Persistent Identifier Scheme', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('assemble', 'local', '', 'assemble', 'aggregation', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('authoritative', 'local', '', 'authoritative', 'the source holds the authoritative version of the metadata about the registry object', '', 'RIFCSOriginatingSourceType', NULL, 'pt', '', 'RIFCS Originating Source Type', '');
INSERT INTO tbl_terms VALUES ('available', 'local', '', 'available', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('award', 'local', '', 'award', 'something given to recognize excellence in a certain field', '', 'RIFCSActivityType', NULL, 'pt', '', 'RIFCS Activity Type', '');
INSERT INTO tbl_terms VALUES ('brief', 'local', '', 'brief', 'short account for selection purposes', '', 'RIFCSDescriptionType', NULL, 'pt', '', 'RIFCS Description Type', '');
INSERT INTO tbl_terms VALUES ('catalogueOrIndex', 'local', '', 'catalogueOrIndex', 'collection of resource descriptions describing the content of one or more repositories or collective works', '', 'RIFCSCollectionType', NULL, 'pt', '', 'RIFCS Collection Type', '');
INSERT INTO tbl_terms VALUES ('Chicago', 'local', '', 'Chicago', 'Chicago Manual of Style', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('collection', 'local', '', 'collection', 'compiled content created as separate and independent works and assembled into a collective whole for distribution and use', '', 'RIFCSCollectionType', NULL, 'pt', '', 'RIFCS Collection Type', '');
INSERT INTO tbl_terms VALUES ('course', 'local', '', 'course', 'education imparted in a series of lessons or meetings', '', 'RIFCSActivityType', NULL, 'pt', '', 'RIFCS Activity Type', '');
INSERT INTO tbl_terms VALUES ('create', 'local', '', 'create', 'instrument', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('created', 'local', '', 'created', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('CSE', 'local', '', 'CSE', 'Council of Science Editors', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('Datacite', 'local', '', 'Datacite', 'International Data Citation', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('dataset', 'local', '', 'dataset', 'collection of physical or digital objects generated by research activities', '', 'RIFCSCollectionType', NULL, 'pt', '', 'RIFCS Collection Type', '');
INSERT INTO tbl_terms VALUES ('date', 'local', '', 'date', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('dateAccepted', 'local', '', 'dateAccepted', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('dateFrom', 'local', '', 'dateFrom', 'start date for a temporal coverage period', '', 'RIFCSTemporalCoverageDateType', NULL, 'pt', '', 'RIFCS Temporal Coverage Date Type', '');
INSERT INTO tbl_terms VALUES ('dateSubmitted', 'local', '', 'dateSubmitted', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('dateTo', 'local', '', 'dateTo', 'end date for a temporal coverage period', '', 'RIFCSTemporalCoverageDateType', NULL, 'pt', '', 'RIFCS Temporal Coverage Date Type', '');
INSERT INTO tbl_terms VALUES ('dcmiPoint', 'local', '', 'dcmiPoint', 'spatial location information specified in DCMI Point notation', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('deliverymethod', 'local', '', 'deliverymethod', '(services only) information about how the service is delivered. Should be one of: webservice, software, offline, workflow', '', 'RIFCSDescriptionType', NULL, 'pt', '', 'RIFCS Description Type', '');
INSERT INTO tbl_terms VALUES ('describes', 'local', '', 'describes', 'is a catalogue for, or index of, of items in the related collection', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('doi', 'local', '', 'doi', 'Digital Object Identifier', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('doi', 'local', '', 'doi', 'Digital Object Identifier', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('doi', 'local', '', 'doi', 'Digital Object Identifier', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('ean13', 'local', '', 'ean13', 'International Article Number', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('ean13', 'local', '', 'ean13', 'International Article Number', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('eissn', 'local', '', 'eissn', 'electronic International Standard Serial Number', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('eissn', 'local', '', 'eissn', 'electronic International Standard Serial Number', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('email', 'local', '', 'email', 'string used to receive messages by means of a computer network', '', 'RIFCSElectronicAddressType', NULL, 'pt', '', 'RIFCS Electronic Address Type', '');
INSERT INTO tbl_terms VALUES ('endPublicationDate', 'local', '', 'endPublicationDate', '', '', 'RIFCSCitationDateType', NULL, 'pt', '', 'RIFCS Citation Date Type', '');
INSERT INTO tbl_terms VALUES ('enriches', 'local', '', 'enriches', 'provides additional value to a collection', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('event', 'local', '', 'event', 'something that happens at a particular place or time as an organized activity with participants or an audience', '', 'RIFCSActivityType', NULL, 'pt', '', 'RIFCS Activity Type', '');
INSERT INTO tbl_terms VALUES ('family', 'local', '', 'family', 'last name or surname', '', 'RIFCSNamePartType', NULL, 'pt', '', 'RIFCS Name Part Type', '');
INSERT INTO tbl_terms VALUES ('faxNumber', 'local', '', 'faxNumber', 'an address part that contains a fax (facsimile) number', '', 'RIFCSPhysicalAddressPartType', NULL, 'pt', '', 'RIFCS Physical Address Part Type', '');
INSERT INTO tbl_terms VALUES ('full', 'local', '', 'full', 'full account', '', 'RIFCSDescriptionType', NULL, 'pt', '', 'RIFCS Description Type', '');
INSERT INTO tbl_terms VALUES ('generate', 'local', '', 'generate', 'simulator', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('given', 'local', '', 'given', 'forename or given or Christian name', '', 'RIFCSNamePartType', NULL, 'pt', '', 'RIFCS Name Part Type', '');
INSERT INTO tbl_terms VALUES ('gml', 'local', '', 'gml', 'OpenGIS Geography Markup Language (GML) Encoding Standard', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('gmlKmlPolyCoords', 'local', '', 'gmlKmlPolyCoords', 'A set of KML long/lat co-ordinates derived from GML defining a polygon as described by the KML coordinates element but without the altitude component', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('gpx', 'local', '', 'gpx', 'the GPS Exchange Format', '', 'RIFCSSpatialType', NULL, 'pt', '', 'RIFCS Spatial Type', '');
INSERT INTO tbl_terms VALUES ('group', 'local', '', 'group', 'one or more persons acting as a family, group, association, partnership or corporation', '', 'RIFCSPartyType', NULL, 'pt', '', 'RIFCS Party Type', '');
INSERT INTO tbl_terms VALUES ('handle', 'local', '', 'handle', 'HANDLE System Identifier', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('handle', 'local', '', 'handle', 'HANDLE System Identifier', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('handle', 'local', '', 'handle', 'HANDLE System Identifier', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('Harvard', 'local', '', 'Harvard', '', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('harvest-oaipmh', 'local', '', 'harvest-oaipmh', 'OAI-PMH Harvest', '', 'RIFCSServiceType', NULL, 'pt', '', 'RIFCS Service Type', '');
INSERT INTO tbl_terms VALUES ('hasAssociationWith', 'local', '', 'hasAssociationWith', 'has an unspecified relationship with the related activity', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasAssociationWith', 'local', '', 'hasAssociationWith', 'has an undefined relationship with the related collection', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasAssociationWith', 'local', '', 'hasAssociationWith', 'has an unspecified relationship with the related registry object', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasAssociationWith', 'local', '', 'hasAssociationWith', 'has an unspecified relationship with the related registry object', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasCollector', 'local', '', 'hasCollector', 'has been aggregated by the related party', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasDerivedCollection', 'local', '', 'hasDerivedCollection', 'the related collection is derived from the collection, e.g. through analysis', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasMember', 'local', '', 'hasMember', '(group only) has enroled the related party in the group', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasOutput', 'local', '', 'hasOutput', 'delivers materials in the related collection', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasPart', 'local', '', 'hasPart', 'contains the related activity', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasPart', 'local', '', 'hasPart', 'contains the related collection', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasPart', 'local', '', 'hasPart', '(group only) contains the related group', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasParticipant', 'local', '', 'hasParticipant', 'is undertaken by the related party', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('hasValueAddedBy', 'local', '', 'hasValueAddedBy', 'specialisation of supports type - for Annotate', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('IEEE', 'local', '', 'IEEE', 'Institute of Electrical and Electronic Engineers', '', 'RIFCSCitationStyle', NULL, 'pt', '', 'RIFCS Citation Style', '');
INSERT INTO tbl_terms VALUES ('infouri', 'local', '', 'infouri', '"info" URI scheme', '', 'RIFCSIdentifierType', NULL, 'pt', '', 'RIFCS Identifier Type', '');
INSERT INTO tbl_terms VALUES ('initial', 'local', '', 'initial', 'a single initial', '', 'RIFCSNamePartType', NULL, 'pt', '', 'RIFCS Name Part Type', '');
INSERT INTO tbl_terms VALUES ('inline', 'local', '', 'inline', '(service only) indicates the argument forms part of the base URL', '', 'RIFCSArgUse', NULL, 'pt', '', 'RIFCS Arg Use', '');
INSERT INTO tbl_terms VALUES ('isAvailableThrough', 'local', '', 'isAvailableThrough', 'specialisation of isSupportBy type - for Harvest, Search and Syndicate', '', 'RIFCSServiceRelationType', NULL, 'pt', '', 'RIFCS Service Relation Type', '');
INSERT INTO tbl_terms VALUES ('isbn', 'local', '', 'isbn', 'International Standard Book Number', '', 'RIFCSCitationIdentifierType', NULL, 'pt', '', 'RIFCS Citation Identifier Type', '');
INSERT INTO tbl_terms VALUES ('isbn', 'local', '', 'isbn', 'International Standard Book Number', '', 'RIFCSRelatedInformationIdentifierType', NULL, 'pt', '', 'RIFCS Related Information Identifier Type', '');
INSERT INTO tbl_terms VALUES ('isCollectorOf', 'local', '', 'isCollectorOf', 'has aggregated the related collection', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('isDerivedFrom', 'local', '', 'isDerivedFrom', 'collection is derived from the related collection, e.g. through analysis', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isDescribedBy', 'local', '', 'isDescribedBy', 'is catalogued or indexed by the related collection', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isEnrichedBy', 'local', '', 'isEnrichedBy', 'additional value provided to a collection by a party', '', 'RIFCSCollectionRelationType', NULL, 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO tbl_terms VALUES ('isFundedBy', 'local', '', 'isFundedBy', 'receives monetary or in-kind aid from the related program', '', 'RIFCSActivityRelationType', NULL, 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO tbl_terms VALUES ('isFundedBy', 'local', '', 'isFundedBy', 'receives monetary or in-kind aid from the related party or program', '', 'RIFCSPartyRelationType', NULL, 'pt', '', 'RIFCS Party Relation Type', '');
INSERT INTO tbl_terms VALUES ('superior', 'local', NULL, 'superior', 'part of a name that describes a party (group) that contains one or more integral subordinate parties (sub-groups or sub-units).', NULL, 'RIFCSNamePartType', NULL, 'pt', NULL, 'RIFCS Name Part Type', NULL);
INSERT INTO tbl_terms VALUES ('subordinate', 'local', NULL, 'subordinate', 'part of a name that describes a party (group) that is an integral sub-group or sub-unit of a superior party (group).', NULL, 'RIFCSNamePartType', NULL, 'pt', NULL, 'RIFCS Name Part Type', NULL);
INSERT INTO tbl_terms VALUES ('reuseInformation', 'local', NULL, 'reuseInformation', 'information that supports reuse of data, such as data definitions, instrument calibration or settings, units of measurement, sample descriptions, experimental parameters, methodology, data analysis techniques, or data derivation rules.', NULL, 'RIFCSRelatedInformationType', NULL, 'pt', NULL, 'RIFCS Related Information Type', NULL);
INSERT INTO tbl_terms VALUES ('dataQualityInformation', 'local', NULL, 'dataQualityInformation', 'data quality statements or summaries of data quality issues affecting the data.', NULL, 'RIFCSRelatedInformationType', NULL, 'pt', NULL, 'RIFCS Related Information Type', NULL);
INSERT INTO tbl_terms VALUES ('administrativePosition', 'local', NULL, 'administrativePosition', 'a kind of party where the position name and contact information are present but the identity of the party filling role is not specified', NULL, 'RIFCSPartyType', NULL, 'pt', NULL, 'RIFCS Party Type', NULL);
INSERT INTO tbl_terms VALUES ('890199', 'local', '', 'Communication Networks and Services not elsewhere classified', '', '', 'ANZSRC-SEO', '890100', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>COMMUNICATION NETWORKS AND SERVICES', '');
INSERT INTO tbl_terms VALUES ('890301', 'local', '', 'Electronic Information Storage and Retrieval Services', '', '', 'ANZSRC-SEO', '890300', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>INFORMATION SERVICES', '');
INSERT INTO tbl_terms VALUES ('890401', 'local', '', 'Animation and Computer Generated Imagery Services', '', '', 'ANZSRC-SEO', '890400', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>MEDIA SERVICES', '');
INSERT INTO tbl_terms VALUES ('890403', 'local', '', 'Internet Broadcasting', '', '', 'ANZSRC-SEO', '890400', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>MEDIA SERVICES', '');
INSERT INTO tbl_terms VALUES ('890404', 'local', '', 'Publishing and Print Services (incl. Internet Publishing)', '', '', 'ANZSRC-SEO', '890400', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>MEDIA SERVICES', '');
INSERT INTO tbl_terms VALUES ('890405', 'local', '', 'Radio and Television Broadcasting', '', '', 'ANZSRC-SEO', '890400', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>MEDIA SERVICES', '');
INSERT INTO tbl_terms VALUES ('890499', 'local', '', 'Media Services not elsewhere classified', '', '', 'ANZSRC-SEO', '890400', 'pt', '', 'ANZSRC>>INFORMATION AND COMMUNICATION SERVICES>>MEDIA SERVICES', '');
INSERT INTO tbl_terms VALUES ('900101', 'local', '', 'Finance Services', '', '', 'ANZSRC-SEO', '900100', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>FINANCIAL SERVICES', '');
INSERT INTO tbl_terms VALUES ('900202', 'local', '', 'Professional, Scientific and Technical Services', '', '', 'ANZSRC-SEO', '900200', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>PROPERTY, BUSINESS SUPPORT SERVICES AND TRADE', '');
INSERT INTO tbl_terms VALUES ('900204', 'local', '', 'Wholesale and Retail Trade', '', '', 'ANZSRC-SEO', '900200', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>PROPERTY, BUSINESS SUPPORT SERVICES AND TRADE', '');
INSERT INTO tbl_terms VALUES ('900401', 'local', '', 'Waste Management Services', '', '', 'ANZSRC-SEO', '900400', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>WATER AND WASTE SERVICES', '');
INSERT INTO tbl_terms VALUES ('900402', 'local', '', 'Waste Recycling Services', '', '', 'ANZSRC-SEO', '900400', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>WATER AND WASTE SERVICES', '');
INSERT INTO tbl_terms VALUES ('900403', 'local', '', 'Water Recycling Services (incl. Sewage and Greywater)', '', '', 'ANZSRC-SEO', '900400', 'pt', '', 'ANZSRC>>COMMERCIAL SERVICES AND TOURISM>>WATER AND WASTE SERVICES', '');
