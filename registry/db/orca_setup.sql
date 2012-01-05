/*******************************************************************************
$Date: 2010-09-21 16:16:54 +1000 (Tue, 21 Sep 2010) $
$Revision: 499 $
********************************************************************************

There are 3 steps.

================================================================================
1. CREATE THE ORCA DATABASE
================================================================================
 i) Connect to PostgreSQL as the postgres user and create the ORCA database as follows:
*/
CREATE DATABASE dbs_orca
  WITH OWNER = dba
       ENCODING = 'UTF8'
       TABLESPACE = pg_default;
GRANT ALL ON DATABASE dbs_orca TO dba;
GRANT CONNECT, TEMPORARY ON DATABASE dbs_orca TO webuser;

/*
 ii) With the new ORCA database:
   - Create the plpgsql language.
   - Create the dba schema.
   - Drop the public schema.
*/
CREATE LANGUAGE plpgsql;

CREATE SCHEMA dba
  AUTHORIZATION dba;
GRANT ALL ON SCHEMA dba TO dba;
GRANT USAGE ON SCHEMA dba TO webuser;

DROP SCHEMA IF EXISTS public;

/*
================================================================================
2. CREATE THE ORCA DATABASE STRUCTURE
================================================================================
Connect to new ORCA database (dbs_orca) as the dba user to create tables, 
views and user defined types & functions:
*/
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = dba, pg_catalog;

--
-- Name: udt_search_result; Type: TYPE; Schema: dba; Owner: dba
--

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
	record_owner character varying,
	rank real
);


ALTER TYPE dba.udt_search_result OWNER TO dba;

--
-- Name: udf_cleanup_complete_lists(); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_delete_all_registry_objects(); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_delete_data_source(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_delete_data_source_log(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_delete_data_source_log(_data_source_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_data_source_logs
WHERE data_source_key = $1;
$_$;


ALTER FUNCTION dba.udf_delete_data_source_log(_data_source_key character varying) OWNER TO dba;

--
-- Name: udf_delete_harvest_request(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_delete_harvest_request(_harvest_request_id character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

DELETE FROM dba.tbl_harvest_requests
WHERE harvest_request_id = $1
;
$_$;


ALTER FUNCTION dba.udf_delete_harvest_request(_harvest_request_id character varying) OWNER TO dba;

--
-- Name: udf_delete_registry_object(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

-- spatial locations
DELETE FROM dba.tbl_spatial_locations SL
WHERE SL.spatial_location_id IN (
 SELECT SLX.spatial_location_id 
   FROM dba.tbl_spatial_locations SLX
   JOIN dba.tbl_locations LX
     ON SLX.location_id = LX.location_id
  WHERE LX.registry_object_key = _registry_object_key
);


-- locations
DELETE FROM dba.tbl_locations
WHERE registry_object_key = _registry_object_key;

-- spatial extent
DELETE FROM dba.tbl_spatial_extents
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

--
-- Name: udf_filter_registry(character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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
 record_owner,
 0.0::float4 AS rank
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
   AND status = 'APPROVED'
 ) AS ordered_result
ORDER BY UPPER(name_value) ASC
;
$_$;


ALTER FUNCTION dba.udf_filter_registry(_filter_string character varying, _classes character varying, _object_group character varying) OWNER TO dba;

--
-- Name: udf_filter_registry_count(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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
   AND ro.status = 'APPROVED'
;
$_$;


ALTER FUNCTION dba.udf_filter_registry_count(_filter_string character varying, _class character varying) OWNER TO dba;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: tbl_access_policies; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_access_policies (
    access_policy_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL
);


ALTER TABLE dba.tbl_access_policies OWNER TO dba;

--
-- Name: udf_get_access_policies(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_address_locations; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_address_locations (
    address_id bigint NOT NULL,
    location_id bigint NOT NULL
);


ALTER TABLE dba.tbl_address_locations OWNER TO dba;

--
-- Name: udf_get_address_locations(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_address_parts; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_address_parts (
    address_part_id bigint NOT NULL,
    physical_address_id bigint NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL
);


ALTER TABLE dba.tbl_address_parts OWNER TO dba;

--
-- Name: udf_get_address_parts(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_address_parts(_physical_address_id bigint) RETURNS SETOF tbl_address_parts
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_address_parts
WHERE physical_address_id = $1
ORDER BY UPPER(type) ASC
;
$_$;


ALTER FUNCTION dba.udf_get_address_parts(_physical_address_id bigint) OWNER TO dba;

--
-- Name: tbl_complex_names; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_complex_names (
    complex_name_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    date_from timestamp(6) with time zone,
    date_to timestamp(6) with time zone,
    type character varying(512),
    lang character varying(64)
);


ALTER TABLE dba.tbl_complex_names OWNER TO dba;

--
-- Name: udf_get_complex_names(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_data_source_logs; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_data_source_logs (
    event_id bigint NOT NULL,
    data_source_key character varying(255) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255),
    request_ip character varying(24),
    event_description character varying(2000)
);


ALTER TABLE dba.tbl_data_source_logs OWNER TO dba;

--
-- Name: udf_get_data_source_log(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_data_source_log(_data_source_key character varying) RETURNS SETOF tbl_data_source_logs
    LANGUAGE sql
    AS $_$ 

SELECT * FROM dba.tbl_data_source_logs
WHERE data_source_key = $1
ORDER BY created_when DESC;
$_$;


ALTER FUNCTION dba.udf_get_data_source_log(_data_source_key character varying) OWNER TO dba;

--
-- Name: tbl_data_sources; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

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
    harvest_frequency character varying(64)
);


ALTER TABLE dba.tbl_data_sources OWNER TO dba;

--
-- Name: udf_get_data_sources(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_descriptions; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_descriptions (
    description_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(4000) NOT NULL,
    type character varying(512) NOT NULL,
    lang character varying(64)
);


ALTER TABLE dba.tbl_descriptions OWNER TO dba;

--
-- Name: udf_get_descriptions(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_earliest_created_when(); Type: FUNCTION; Schema: dba; Owner: dba
--


CREATE FUNCTION udf_get_earliest_created_when()
  RETURNS SETOF timestamp with time zone AS
$BODY$

SELECT MIN(created_when) FROM dba.tbl_registry_objects
WHERE status='APPROVED';
;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION udf_get_earliest_created_when() OWNER TO dba;

--
-- Name: tbl_electronic_address_args; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_electronic_address_args (
    electronic_address_arg_id bigint NOT NULL,
    electronic_address_id bigint NOT NULL,
    name character varying(512) NOT NULL,
    required boolean NOT NULL,
    type character varying(512) NOT NULL,
    use character varying(512)
);


ALTER TABLE dba.tbl_electronic_address_args OWNER TO dba;

--
-- Name: udf_get_electronic_address_args(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_electronic_addresses; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_electronic_addresses (
    electronic_address_id bigint NOT NULL,
    address_id bigint NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512)
);


ALTER TABLE dba.tbl_electronic_addresses OWNER TO dba;

--
-- Name: udf_get_electronic_addresses(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_harvest_requests; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

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

--
-- Name: udf_get_harvest_requests(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_highlighted_querytext(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_highlighted_querytext(_text character varying, _querytext character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$

SELECT ts_headline($1, plainto_tsquery($2), 'StartSel=@@@@, StopSel=$$$$, MinWords=1, MaxWords=4000, HighlightAll=TRUE')
;
$_$;


ALTER FUNCTION dba.udf_get_highlighted_querytext(_text character varying, _querytext character varying) OWNER TO dba;

--
-- Name: udf_get_id(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_identifiers; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_identifiers (
    identifier_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL
);


ALTER TABLE dba.tbl_identifiers OWNER TO dba;

--
-- Name: udf_get_identifiers(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_registry_objects; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_registry_objects (
    registry_object_key character varying(512) NOT NULL,
    data_source_key character varying(255) NOT NULL,
    object_group character varying(512) NOT NULL,
    date_modified timestamp(6) with time zone,
    identifiers_search_index tsvector,
    subjects_search_index tsvector,
    names_search_index tsvector,
    descriptions_search_index tsvector,
    registry_object_class character varying(512) NOT NULL,
    type character varying(32) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    originating_source character varying(512) NOT NULL,
    originating_source_type character varying(512),
    date_accessioned timestamp(6) with time zone,
    status character(20) DEFAULT 'APPROVED'::bpchar NOT NULL,
    record_owner character varying(255) DEFAULT 'SYSTEM'::character varying NOT NULL,
    status_modified_when timestamp(6) with time zone DEFAULT now(),
    status_modified_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    CONSTRAINT registry_object_class_check CHECK (((registry_object_class)::text = ANY ((ARRAY['Activity'::character varying, 'Collection'::character varying, 'Service'::character varying, 'Party'::character varying])::text[])))
);


ALTER TABLE dba.tbl_registry_objects OWNER TO dba;

--
-- Name: vw_registry_objects; Type: VIEW; Schema: dba; Owner: dba
--

CREATE VIEW vw_registry_objects AS
    SELECT ro.registry_object_key, ro.originating_source, ro.originating_source_type, ro.data_source_key, ds.title AS data_source_title, ro.object_group, ro.date_accessioned, ro.date_modified, ro.registry_object_class, ro.type, ro.created_when, ro.created_who, ro.status_modified_when, ro.status_modified_who, ro.status, ro.record_owner FROM (tbl_registry_objects ro JOIN tbl_data_sources ds ON (((ro.data_source_key)::text = (ds.data_source_key)::text)));


ALTER TABLE dba.vw_registry_objects OWNER TO dba;

--
-- Name: udf_get_incomplete_list(integer, integer, integer); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_locations; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_locations (
    location_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    date_from timestamp(6) with time zone,
    date_to timestamp(6) with time zone,
    type character varying(512)
);


ALTER TABLE dba.tbl_locations OWNER TO dba;

--
-- Name: udf_get_locations(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_name_parts; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_name_parts (
    name_part_id bigint NOT NULL,
    complex_name_id bigint NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512)
);


ALTER TABLE dba.tbl_name_parts OWNER TO dba;

--
-- Name: udf_get_name_parts(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_name_parts(_complex_name_id bigint) RETURNS SETOF tbl_name_parts
    LANGUAGE sql
    AS $_$

SELECT 
 * 
FROM dba.tbl_name_parts
WHERE complex_name_id = $1
ORDER BY UPPER(type) ASC
;
$_$;


ALTER FUNCTION dba.udf_get_name_parts(_complex_name_id bigint) OWNER TO dba;

--
-- Name: udf_get_object_groups(); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_object_groups() RETURNS SETOF character varying
    LANGUAGE sql
    AS $$ 

SELECT DISTINCT object_group
FROM dba.tbl_registry_objects
ORDER BY object_group ASC
;

$$;


ALTER FUNCTION dba.udf_get_object_groups() OWNER TO dba;

--
-- Name: tbl_physical_addresses; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_physical_addresses (
    physical_address_id bigint NOT NULL,
    address_id bigint NOT NULL,
    type character varying(512),
    lang character varying(64)
);


ALTER TABLE dba.tbl_physical_addresses OWNER TO dba;

--
-- Name: udf_get_physical_addresses(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_registry_object(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_registry_object(_registry_object_key character varying) RETURNS SETOF vw_registry_objects
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.vw_registry_objects
WHERE registry_object_key = $1;

$_$;


ALTER FUNCTION dba.udf_get_registry_object(_registry_object_key character varying) OWNER TO dba;

--
-- Name: udf_get_registry_object_count(character varying, character varying, character varying, character); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_registry_object_electronic_addresses(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: vw_names; Type: VIEW; Schema: dba; Owner: dba
--

CREATE VIEW vw_names AS
    SELECT cn.registry_object_key, cn.date_from, cn.date_to, np.value, np.type, cn.lang FROM (tbl_complex_names cn JOIN tbl_name_parts np ON ((cn.complex_name_id = np.complex_name_id)));


ALTER TABLE dba.vw_names OWNER TO dba;

--
-- Name: udf_get_registry_object_names(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_registry_object_physical_addresses(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_spatial_locations; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_spatial_locations (
    spatial_location_id bigint NOT NULL,
    location_id bigint NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL,
    lang character varying(64)
);


ALTER TABLE dba.tbl_spatial_locations OWNER TO dba;

--
-- Name: udf_get_registry_object_spatial_locations(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_registry_objects_for_data_source(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_registry_objects_for_data_source(_key character varying) RETURNS SETOF tbl_registry_objects
    LANGUAGE sql
    AS $_$ 

SELECT 
 *
FROM dba.tbl_registry_objects
WHERE data_source_key = $1;

$_$;


ALTER FUNCTION dba.udf_get_registry_objects_for_data_source(_key character varying) OWNER TO dba;

--
-- Name: tbl_related_info; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_related_info (
    related_info_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL
);


ALTER TABLE dba.tbl_related_info OWNER TO dba;

--
-- Name: udf_get_related_info(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_related_objects; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_related_objects (
    relation_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    related_registry_object_key character varying(512) NOT NULL
);


ALTER TABLE dba.tbl_related_objects OWNER TO dba;

--
-- Name: udf_get_related_objects(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_relation_descriptions; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_relation_descriptions (
    relation_description_id bigint NOT NULL,
    relation_id bigint NOT NULL,
    description character varying(512),
    type character varying(512) NOT NULL,
    lang character varying(64),
    url character varying(512)
);


ALTER TABLE dba.tbl_relation_descriptions OWNER TO dba;

--
-- Name: udf_get_relation_descriptions(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_oai_rt_resumption_tokens; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

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

--
-- Name: udf_get_resumption_token(character varying, integer); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_spatial_locations(bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_get_statuses(); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_get_statuses() RETURNS SETOF character
    LANGUAGE sql
    AS $$ 

SELECT status FROM dba.tbl_statuses
;

$$;


ALTER FUNCTION dba.udf_get_statuses() OWNER TO dba;

--
-- Name: tbl_subjects; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_subjects (
    subject_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL,
    value character varying(512) NOT NULL,
    type character varying(512) NOT NULL,
    lang character varying(64)
);


ALTER TABLE dba.tbl_subjects OWNER TO dba;

--
-- Name: udf_get_subjects(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_access_policy(bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_address_location(bigint, bigint); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_address_part(bigint, bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_complete_list(integer); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_insert_complete_list(_complete_list_id integer) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_oai_rt_complete_lists (complete_list_id) VALUES ($1);
$_$;


ALTER FUNCTION dba.udf_insert_complete_list(_complete_list_id integer) OWNER TO dba;

--
-- Name: udf_insert_complete_list_record(integer, integer, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_insert_complete_list_record(_complete_list_id integer, _record_number integer, _registry_object_key character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_oai_rt_complete_list_records (complete_list_id, record_number, registry_object_key) VALUES ($1, $2, $3);
$_$;


ALTER FUNCTION dba.udf_insert_complete_list_record(_complete_list_id integer, _record_number integer, _registry_object_key character varying) OWNER TO dba;

--
-- Name: udf_insert_complex_name(bigint, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_data_source(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_insert_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying) RETURNS void
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
  contact_name,
  contact_email,
  notes,
  record_owner
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
  $13
 )
$_$;


ALTER FUNCTION dba.udf_insert_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying) OWNER TO dba;

--
-- Name: udf_insert_data_source_event(bigint, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_insert_data_source_event(_event_id bigint, _data_source_key character varying, _created_who character varying, _request_ip character varying, _event_description character varying) RETURNS void
    LANGUAGE sql
    AS $_$ 

INSERT INTO dba.tbl_data_source_logs
(
  event_id,
  data_source_key,
  created_who,
  request_ip,
  event_description
 ) VALUES (
  $1,
  $2,
  $3,
  $4,
  $5
 )
$_$;


ALTER FUNCTION dba.udf_insert_data_source_event(_event_id bigint, _data_source_key character varying, _created_who character varying, _request_ip character varying, _event_description character varying) OWNER TO dba;

--
-- Name: udf_insert_description(bigint, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

UPDATE dba.tbl_registry_objects 
   SET descriptions_search_index = coalesce(descriptions_search_index, '') || to_tsvector(coalesce($3, ''))
 WHERE registry_object_key = $2
;
$_$;


ALTER FUNCTION dba.udf_insert_description(_description_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _lang character varying) OWNER TO dba;

--
-- Name: udf_insert_electronic_address(bigint, bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_electronic_address_arg(bigint, bigint, character varying, boolean, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_harvest_request(character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_identifier(bigint, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

UPDATE dba.tbl_registry_objects 
   SET identifiers_search_index = coalesce(identifiers_search_index, '') || to_tsvector(coalesce($3, ''))
 WHERE registry_object_key = $2
;
$_$;


ALTER FUNCTION dba.udf_insert_identifier(_identifier_id bigint, _registry_object_key character varying, _value character varying, _type character varying) OWNER TO dba;

--
-- Name: udf_insert_location(bigint, character varying, timestamp with time zone, timestamp with time zone, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_name_part(bigint, bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

UPDATE dba.tbl_registry_objects 
   SET names_search_index = coalesce(names_search_index, '') || to_tsvector(coalesce($3, ''))
 WHERE registry_object_key = (SELECT registry_object_key FROM dba.tbl_complex_names WHERE complex_name_id = $2)
;
$_$;


ALTER FUNCTION dba.udf_insert_name_part(_name_part_id bigint, _complex_name_id bigint, _value character varying, _type character varying) OWNER TO dba;

--
-- Name: udf_insert_physical_address(bigint, bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_registry_object(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character varying, character, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_related_info(bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_related_object(bigint, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_relation_description(bigint, bigint, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_resumption_token(character varying, integer, integer, integer, integer, timestamp with time zone, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_spatial_location(bigint, bigint, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_insert_subject(bigint, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

UPDATE dba.tbl_registry_objects 
   SET subjects_search_index = coalesce(subjects_search_index, '') || to_tsvector(coalesce($3, ''))
 WHERE registry_object_key = $2
;
$_$;


ALTER FUNCTION dba.udf_insert_subject(_subject_id bigint, _registry_object_key character varying, _value character varying, _type character varying, _lang character varying) OWNER TO dba;

--
-- Name: udf_registry_object_class_count(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_registry_object_class_count(_class character varying) RETURNS SETOF bigint
    LANGUAGE sql
    AS $_$ 


 SELECT COUNT('x')
 FROM dba.tbl_registry_objects ro
WHERE $1 = ro.registry_object_class
;
$_$;


ALTER FUNCTION dba.udf_registry_object_class_count(_class character varying) OWNER TO dba;

--
-- Name: udf_search_registry(character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_search_registry(_search_string character varying, _classes character varying, _data_source_key character varying, _object_group character varying, _created_before_equals timestamp with time zone, _created_after_equals timestamp with time zone, _status character, _record_owner character varying)
  RETURNS SETOF udt_search_result AS
$BODY$ 


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
 record_owner,
 ts_rank((SELECT setweight(coalesce(r.identifiers_search_index,''), 'A') || setweight(coalesce(r.names_search_index,''), 'B') || setweight(coalesce(r.subjects_search_index,''), 'C') || setweight(coalesce(r.descriptions_search_index,''), 'D') FROM dba.tbl_registry_objects r WHERE r.registry_object_key=distinct_matches.registry_object_key), plainto_tsquery($1)) AS rank
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
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.identifiers_search_index @@ plainto_tsquery($1))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.names_search_index @@ plainto_tsquery($1))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.subjects_search_index @@ plainto_tsquery($1))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.descriptions_search_index @@ plainto_tsquery($1))
         OR $1 = ''
   )
   AND ( status = $7 OR $7 IS NULL )
   AND ( record_owner = $8 OR $8 IS NULL )
 ORDER BY registry_object_key ASC
) 
AS distinct_matches
WHERE ( created_when <= $5 OR $5 IS NULL )
  AND ( created_when >= $6 OR $6 IS NULL )
ORDER BY rank DESC
;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION udf_search_registry(character varying, character varying, character varying, character varying, timestamp with time zone, timestamp with time zone, character, character varying) OWNER TO dba;

--
-- Name: udf_update_data_source(character varying, character varying, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying) RETURNS void
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
  contact_name = $10,
  contact_email = $11,
  notes = $12,
  record_owner = $13
WHERE data_source_key = $2

$_$;


ALTER FUNCTION dba.udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _harvest_frequency character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _record_owner character varying) OWNER TO dba;

--
-- Name: udf_update_data_source_last_run(character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_update_harvest_request(character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_update_registry_object_status(character varying, character, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: udf_update_resumption_tokens(integer); Type: FUNCTION; Schema: dba; Owner: dba
--

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

--
-- Name: tbl_ids; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_ids (
    column_identifier character varying(128) NOT NULL,
    next_id bigint NOT NULL
);


ALTER TABLE dba.tbl_ids OWNER TO dba;

--
-- Name: tbl_oai_rt_complete_list_records; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_oai_rt_complete_list_records (
    record_number integer NOT NULL,
    complete_list_id bigint NOT NULL,
    registry_object_key character varying(512) NOT NULL
);


ALTER TABLE dba.tbl_oai_rt_complete_list_records OWNER TO dba;

--
-- Name: tbl_oai_rt_complete_lists; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_oai_rt_complete_lists (
    complete_list_id bigint NOT NULL
);


ALTER TABLE dba.tbl_oai_rt_complete_lists OWNER TO dba;

--
-- Name: tbl_statuses; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE tbl_statuses (
    status character(20) NOT NULL
);


ALTER TABLE dba.tbl_statuses OWNER TO dba;


CREATE TABLE tbl_spatial_extents
(
  spatial_extents_id bigint NOT NULL,
  spatial_location_id bigint NOT NULL,
  registry_object_key character varying(512) NOT NULL,
  bound_box box NOT NULL
)
WITH (OIDS=FALSE);
ALTER TABLE dba.tbl_spatial_extents OWNER TO dba;
GRANT ALL ON TABLE tbl_spatial_extents TO dba;
GRANT SELECT, INSERT, DELETE ON TABLE tbl_spatial_extents TO webuser;

--
-- Name: vw_registry_search; Type: VIEW; Schema: dba; Owner: dba
--

CREATE VIEW vw_registry_search AS
    SELECT ro.registry_object_key, ro.originating_source, ro.data_source_key, ro.data_source_title, ro.object_group, ro.date_accessioned, ro.date_modified, ro.created_when, ro.registry_object_class, ro.type, i.value AS identifier_value, i.type AS identifier_type, ro.status, ro.record_owner FROM (vw_registry_objects ro LEFT JOIN tbl_identifiers i ON (((ro.registry_object_key)::text = (i.registry_object_key)::text)));


ALTER TABLE dba.vw_registry_search OWNER TO dba;


---INSERT SPATIAL EXTENT UDF
CREATE OR REPLACE FUNCTION udf_insert_spatial_extent(_spatial_extents_id bigint, _spatial_location_id bigint, registry_object_key character varying, _north real, _south real, _west real, _east real)
  RETURNS void AS
$BODY$ 

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
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100;
ALTER FUNCTION udf_insert_spatial_extent(bigint, bigint, character varying, real, real, real, real) OWNER TO dba;

CREATE OR REPLACE FUNCTION udf_get_registry_objects_inbound(north real, south real, west real, east real)
  RETURNS SETOF dba.vw_registry_search AS
$BODY$ 
SELECT rs.* FROM dba.vw_registry_search rs, dba.tbl_spatial_extents se
WHERE rs.registry_object_key = se.registry_object_key 
AND se.bound_box && box ((point($1,$3)),(point($2,$4)))
AND rs.status = 'APPROVED'
ORDER BY area(se.bound_box) ASC;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION udf_get_registry_objects_inbound(real, real, real, real) OWNER TO dba;

--AND

CREATE OR REPLACE FUNCTION udf_get_registry_objects_inbound_two(north real, south real, west real, east real, north2 real, south2 real, west2 real, east2 real)
  RETURNS SETOF dba.vw_registry_search AS
$BODY$ 
SELECT rs.* FROM dba.vw_registry_search rs, dba.tbl_spatial_extents se
WHERE rs.registry_object_key = se.registry_object_key 
AND (se.bound_box && box ((point($1,$3)),(point($2,$4))) OR se.bound_box && box ((point($5,$7)),(point($6,$8))))
AND rs.status = 'APPROVED'
ORDER BY area(se.bound_box) ASC;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION udf_get_registry_objects_inbound_two(real, real, real, real, real, real, real, real) OWNER TO dba;

CREATE OR REPLACE FUNCTION udf_search_registry_objects_inbound(_north real, _south real, _west real, _east real, _search_string character varying, _classes character varying)
  RETURNS SETOF udt_search_result AS
$BODY$ 
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
 record_owner,
 ts_rank((SELECT setweight(coalesce(r.identifiers_search_index,''), 'A') || setweight(coalesce(r.names_search_index,''), 'B') || setweight(coalesce(r.subjects_search_index,''), 'C') || setweight(coalesce(r.descriptions_search_index,''), 'D') FROM dba.tbl_registry_objects r WHERE r.registry_object_key=distinct_matches.registry_object_key), plainto_tsquery($5)) AS rank
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
   AND (
	    registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.identifiers_search_index @@ plainto_tsquery($5))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.names_search_index @@ plainto_tsquery($5))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.subjects_search_index @@ plainto_tsquery($5))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.descriptions_search_index @@ plainto_tsquery($5))
      )
)
AS distinct_matches
ORDER BY rank DESC;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION udf_search_registry_objects_inbound(real, real, real, real, character varying, character varying) OWNER TO dba;


CREATE OR REPLACE FUNCTION udf_search_registry_objects_inbound_two(_north real, _south real, _west real, _east real, _north2 real, _south2 real, _west2 real, _east2 real, _search_string character varying, _classes character varying)
  RETURNS SETOF udt_search_result AS
$BODY$ 
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
 record_owner,
 ts_rank((SELECT setweight(coalesce(r.identifiers_search_index,''), 'A') || setweight(coalesce(r.names_search_index,''), 'B') || setweight(coalesce(r.subjects_search_index,''), 'C') || setweight(coalesce(r.descriptions_search_index,''), 'D') FROM dba.tbl_registry_objects r WHERE r.registry_object_key=distinct_matches.registry_object_key), plainto_tsquery($9)) AS rank
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
	
   AND (
	    registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.identifiers_search_index @@ plainto_tsquery($9))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.names_search_index @@ plainto_tsquery($9))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.subjects_search_index @@ plainto_tsquery($9))
         OR registry_object_key IN (SELECT registry_object_key FROM dba.tbl_registry_objects r WHERE r.descriptions_search_index @@ plainto_tsquery($9))
      )
)
AS distinct_matches
ORDER BY rank DESC;
$BODY$
  LANGUAGE 'sql' VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION udf_search_registry_objects_inbound_two(real, real, real, real, real, real, real, real, character varying, character varying) OWNER TO dba;




--
-- Name: pk_tbl_access_policies; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_access_policies
    ADD CONSTRAINT pk_tbl_access_policies PRIMARY KEY (access_policy_id);


--
-- Name: pk_tbl_address_locations; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_address_locations
    ADD CONSTRAINT pk_tbl_address_locations PRIMARY KEY (address_id);


--
-- Name: pk_tbl_address_parts; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_address_parts
    ADD CONSTRAINT pk_tbl_address_parts PRIMARY KEY (address_part_id);


--
-- Name: pk_tbl_complex_names; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_complex_names
    ADD CONSTRAINT pk_tbl_complex_names PRIMARY KEY (complex_name_id);


--
-- Name: pk_tbl_data_source_logs; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_data_source_logs
    ADD CONSTRAINT pk_tbl_data_source_logs PRIMARY KEY (event_id);


--
-- Name: pk_tbl_data_sources; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_data_sources
    ADD CONSTRAINT pk_tbl_data_sources PRIMARY KEY (data_source_key);


--
-- Name: pk_tbl_descriptions; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_descriptions
    ADD CONSTRAINT pk_tbl_descriptions PRIMARY KEY (description_id);


--
-- Name: pk_tbl_electronic_address_args; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_electronic_address_args
    ADD CONSTRAINT pk_tbl_electronic_address_args PRIMARY KEY (electronic_address_arg_id);


--
-- Name: pk_tbl_electronic_adresses; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_electronic_addresses
    ADD CONSTRAINT pk_tbl_electronic_adresses PRIMARY KEY (electronic_address_id);


--
-- Name: pk_tbl_harvest_requests; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_harvest_requests
    ADD CONSTRAINT pk_tbl_harvest_requests PRIMARY KEY (harvest_request_id);


--
-- Name: pk_tbl_identifiers; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_identifiers
    ADD CONSTRAINT pk_tbl_identifiers PRIMARY KEY (identifier_id);


--
-- Name: pk_tbl_ids; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_ids
    ADD CONSTRAINT pk_tbl_ids PRIMARY KEY (column_identifier);


--
-- Name: pk_tbl_locations; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_locations
    ADD CONSTRAINT pk_tbl_locations PRIMARY KEY (location_id);


--
-- Name: pk_tbl_name_parts; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_name_parts
    ADD CONSTRAINT pk_tbl_name_parts PRIMARY KEY (name_part_id);


--
-- Name: pk_tbl_oai_rt_complete_list_records; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_oai_rt_complete_list_records
    ADD CONSTRAINT pk_tbl_oai_rt_complete_list_records PRIMARY KEY (record_number, complete_list_id);


--
-- Name: pk_tbl_oai_rt_complete_lists; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_oai_rt_complete_lists
    ADD CONSTRAINT pk_tbl_oai_rt_complete_lists PRIMARY KEY (complete_list_id);


--
-- Name: pk_tbl_oai_rt_resumption_tokens; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_oai_rt_resumption_tokens
    ADD CONSTRAINT pk_tbl_oai_rt_resumption_tokens PRIMARY KEY (resumption_token_id);


--
-- Name: pk_tbl_physical_addresses; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_physical_addresses
    ADD CONSTRAINT pk_tbl_physical_addresses PRIMARY KEY (physical_address_id);


--
-- Name: pk_tbl_registry_objects; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_registry_objects
    ADD CONSTRAINT pk_tbl_registry_objects PRIMARY KEY (registry_object_key);


--
-- Name: pk_tbl_related_info; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_related_info
    ADD CONSTRAINT pk_tbl_related_info PRIMARY KEY (related_info_id);


--
-- Name: pk_tbl_related_objects; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_related_objects
    ADD CONSTRAINT pk_tbl_related_objects PRIMARY KEY (relation_id);


--
-- Name: pk_tbl_relation_descriptions; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_relation_descriptions
    ADD CONSTRAINT pk_tbl_relation_descriptions PRIMARY KEY (relation_description_id);


--
-- Name: pk_tbl_spatial_locations; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_spatial_locations
    ADD CONSTRAINT pk_tbl_spatial_locations PRIMARY KEY (spatial_location_id);


--
-- Name: pk_tbl_statuses; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_statuses
    ADD CONSTRAINT pk_tbl_statuses PRIMARY KEY (status);


--
-- Name: pk_tbl_subjects; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY tbl_subjects
    ADD CONSTRAINT pk_tbl_subjects PRIMARY KEY (subject_id);


--
-- Name: idx_access_policies_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_access_policies_registry_object_key_1 ON tbl_access_policies USING btree (registry_object_key);


--
-- Name: idx_address_locations_location_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_address_locations_location_id_1 ON tbl_address_locations USING btree (location_id);


--
-- Name: idx_address_parts_physical_address_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_address_parts_physical_address_id_1 ON tbl_address_parts USING btree (physical_address_id);


--
-- Name: idx_complex_names_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_complex_names_registry_object_key_1 ON tbl_complex_names USING btree (registry_object_key);


--
-- Name: idx_data_source_logs_data_source_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_data_source_logs_data_source_key_1 ON tbl_data_source_logs USING btree (data_source_key);


--
-- Name: idx_descriptions_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_descriptions_registry_object_key_1 ON tbl_descriptions USING btree (registry_object_key);


--
-- Name: idx_electronic_address_args_electronic_address_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_electronic_address_args_electronic_address_id_1 ON tbl_electronic_address_args USING btree (electronic_address_id);


--
-- Name: idx_electronic_addresses_address_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_electronic_addresses_address_id_1 ON tbl_electronic_addresses USING btree (address_id);


--
-- Name: idx_harvest_requests_data_source_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_harvest_requests_data_source_key_1 ON tbl_harvest_requests USING btree (data_source_key);


--
-- Name: idx_identifiers_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_identifiers_registry_object_key_1 ON tbl_identifiers USING btree (registry_object_key);


--
-- Name: idx_locations_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_locations_registry_object_key_1 ON tbl_locations USING btree (registry_object_key);


--
-- Name: idx_name_parts_complex_name_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_name_parts_complex_name_id_1 ON tbl_name_parts USING btree (complex_name_id);


--
-- Name: idx_oai_rt_complete_list_records_complete_list_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_oai_rt_complete_list_records_complete_list_id_1 ON tbl_oai_rt_complete_list_records USING btree (complete_list_id);


--
-- Name: idx_oai_rt_resumption_tokens_complete_list_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_oai_rt_resumption_tokens_complete_list_id_1 ON tbl_oai_rt_resumption_tokens USING btree (complete_list_id);


--
-- Name: idx_physical_addresses_address_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_physical_addresses_address_id_1 ON tbl_physical_addresses USING btree (address_id);


--
-- Name: idx_registry_objects_data_source_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_registry_objects_data_source_key_1 ON tbl_registry_objects USING btree (data_source_key);


--
-- Name: idx_related_info_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_related_info_registry_object_key_1 ON tbl_related_info USING btree (registry_object_key);


--
-- Name: idx_related_objects_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_related_objects_registry_object_key_1 ON tbl_related_objects USING btree (registry_object_key);


--
-- Name: idx_relation_descriptions_relation_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_relation_descriptions_relation_id_1 ON tbl_relation_descriptions USING btree (relation_id);


--
-- Name: idx_spatial_locations_location_id_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_spatial_locations_location_id_1 ON tbl_spatial_locations USING btree (location_id);


--
-- Name: idx_subjects_registry_object_key_1; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX idx_subjects_registry_object_key_1 ON tbl_subjects USING btree (registry_object_key);


--
-- Name: registry_object_description_search_idx; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX registry_object_description_search_idx ON tbl_registry_objects USING gist (descriptions_search_index);


--
-- Name: registry_object_identifier_search_idx; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX registry_object_identifier_search_idx ON tbl_registry_objects USING gist (identifiers_search_index);


--
-- Name: registry_object_name_search_idx; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX registry_object_name_search_idx ON tbl_registry_objects USING gist (names_search_index);


--
-- Name: registry_object_subject_search_idx; Type: INDEX; Schema: dba; Owner: dba; Tablespace: 
--

CREATE INDEX registry_object_subject_search_idx ON tbl_registry_objects USING gist (subjects_search_index);


--
-- Name: fk_tbl_access_policies_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_access_policies
    ADD CONSTRAINT fk_tbl_access_policies_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_address_locations_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_address_locations
    ADD CONSTRAINT fk_tbl_address_locations_1 FOREIGN KEY (location_id) REFERENCES tbl_locations(location_id);


--
-- Name: fk_tbl_address_parts_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_address_parts
    ADD CONSTRAINT fk_tbl_address_parts_1 FOREIGN KEY (physical_address_id) REFERENCES tbl_physical_addresses(physical_address_id);


--
-- Name: fk_tbl_complex_names_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_complex_names
    ADD CONSTRAINT fk_tbl_complex_names_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_data_source_logs_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_data_source_logs
    ADD CONSTRAINT fk_tbl_data_source_logs_1 FOREIGN KEY (data_source_key) REFERENCES tbl_data_sources(data_source_key);


--
-- Name: fk_tbl_descriptions_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_descriptions
    ADD CONSTRAINT fk_tbl_descriptions_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_electronic_address_args_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_electronic_address_args
    ADD CONSTRAINT fk_tbl_electronic_address_args_1 FOREIGN KEY (electronic_address_id) REFERENCES tbl_electronic_addresses(electronic_address_id);


--
-- Name: fk_tbl_electronic_addresses_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_electronic_addresses
    ADD CONSTRAINT fk_tbl_electronic_addresses_1 FOREIGN KEY (address_id) REFERENCES tbl_address_locations(address_id);


--
-- Name: fk_tbl_harvest_requests_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_harvest_requests
    ADD CONSTRAINT fk_tbl_harvest_requests_1 FOREIGN KEY (data_source_key) REFERENCES tbl_data_sources(data_source_key);


--
-- Name: fk_tbl_identifiers_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_identifiers
    ADD CONSTRAINT fk_tbl_identifiers_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_locations_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_locations
    ADD CONSTRAINT fk_tbl_locations_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_name_parts_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_name_parts
    ADD CONSTRAINT fk_tbl_name_parts_1 FOREIGN KEY (complex_name_id) REFERENCES tbl_complex_names(complex_name_id);


--
-- Name: fk_tbl_oai_rt_complete_list_records_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_oai_rt_complete_list_records
    ADD CONSTRAINT fk_tbl_oai_rt_complete_list_records_1 FOREIGN KEY (complete_list_id) REFERENCES tbl_oai_rt_complete_lists(complete_list_id);


--
-- Name: fk_tbl_oai_rt_resumption_tokens_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_oai_rt_resumption_tokens
    ADD CONSTRAINT fk_tbl_oai_rt_resumption_tokens_1 FOREIGN KEY (complete_list_id) REFERENCES tbl_oai_rt_complete_lists(complete_list_id);


--
-- Name: fk_tbl_physical_addresses_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_physical_addresses
    ADD CONSTRAINT fk_tbl_physical_addresses_1 FOREIGN KEY (address_id) REFERENCES tbl_address_locations(address_id);


--
-- Name: fk_tbl_registry_objects_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_registry_objects
    ADD CONSTRAINT fk_tbl_registry_objects_1 FOREIGN KEY (data_source_key) REFERENCES tbl_data_sources(data_source_key);


--
-- Name: fk_tbl_registry_objects_status; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_registry_objects
    ADD CONSTRAINT fk_tbl_registry_objects_status FOREIGN KEY (status) REFERENCES tbl_statuses(status);


--
-- Name: fk_tbl_related_info_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_related_info
    ADD CONSTRAINT fk_tbl_related_info_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_related_objects_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_related_objects
    ADD CONSTRAINT fk_tbl_related_objects_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);


--
-- Name: fk_tbl_relation_descriptions_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_relation_descriptions
    ADD CONSTRAINT fk_tbl_relation_descriptions_1 FOREIGN KEY (relation_id) REFERENCES tbl_related_objects(relation_id);


--
-- Name: fk_tbl_spatial_locations_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_spatial_locations
    ADD CONSTRAINT fk_tbl_spatial_locations_1 FOREIGN KEY (location_id) REFERENCES tbl_locations(location_id);


--
-- Name: fk_tbl_subjects_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY tbl_subjects
    ADD CONSTRAINT fk_tbl_subjects_1 FOREIGN KEY (registry_object_key) REFERENCES tbl_registry_objects(registry_object_key);

ALTER TABLE ONLY tbl_spatial_extents
  ADD CONSTRAINT pk_tbl_spatial_extents PRIMARY KEY (spatial_extents_id),
  ADD CONSTRAINT fk_tbl_spatial_extents_1 FOREIGN KEY (spatial_location_id)
      REFERENCES tbl_spatial_locations (spatial_location_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;
	
	
--
-- Name: dba; Type: ACL; Schema: -; Owner: dba
--

REVOKE ALL ON SCHEMA dba FROM PUBLIC;
REVOKE ALL ON SCHEMA dba FROM dba;
GRANT ALL ON SCHEMA dba TO dba;
GRANT USAGE ON SCHEMA dba TO webuser;


--
-- Name: tbl_access_policies; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_access_policies FROM PUBLIC;
REVOKE ALL ON TABLE tbl_access_policies FROM dba;
GRANT ALL ON TABLE tbl_access_policies TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_access_policies TO webuser;


--
-- Name: tbl_address_locations; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_address_locations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_address_locations FROM dba;
GRANT ALL ON TABLE tbl_address_locations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_address_locations TO webuser;


--
-- Name: tbl_address_parts; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_address_parts FROM PUBLIC;
REVOKE ALL ON TABLE tbl_address_parts FROM dba;
GRANT ALL ON TABLE tbl_address_parts TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_address_parts TO webuser;


--
-- Name: tbl_complex_names; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_complex_names FROM PUBLIC;
REVOKE ALL ON TABLE tbl_complex_names FROM dba;
GRANT ALL ON TABLE tbl_complex_names TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_complex_names TO webuser;


--
-- Name: tbl_data_source_logs; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_data_source_logs FROM PUBLIC;
REVOKE ALL ON TABLE tbl_data_source_logs FROM dba;
GRANT ALL ON TABLE tbl_data_source_logs TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_data_source_logs TO webuser;


--
-- Name: tbl_data_sources; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_data_sources FROM PUBLIC;
REVOKE ALL ON TABLE tbl_data_sources FROM dba;
GRANT ALL ON TABLE tbl_data_sources TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_data_sources TO webuser;


--
-- Name: tbl_descriptions; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_descriptions FROM PUBLIC;
REVOKE ALL ON TABLE tbl_descriptions FROM dba;
GRANT ALL ON TABLE tbl_descriptions TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_descriptions TO webuser;


--
-- Name: tbl_electronic_address_args; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_electronic_address_args FROM PUBLIC;
REVOKE ALL ON TABLE tbl_electronic_address_args FROM dba;
GRANT ALL ON TABLE tbl_electronic_address_args TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_electronic_address_args TO webuser;


--
-- Name: tbl_electronic_addresses; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_electronic_addresses FROM PUBLIC;
REVOKE ALL ON TABLE tbl_electronic_addresses FROM dba;
GRANT ALL ON TABLE tbl_electronic_addresses TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_electronic_addresses TO webuser;


--
-- Name: tbl_harvest_requests; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_harvest_requests FROM PUBLIC;
REVOKE ALL ON TABLE tbl_harvest_requests FROM dba;
GRANT ALL ON TABLE tbl_harvest_requests TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_harvest_requests TO webuser;


--
-- Name: tbl_identifiers; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_identifiers FROM PUBLIC;
REVOKE ALL ON TABLE tbl_identifiers FROM dba;
GRANT ALL ON TABLE tbl_identifiers TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_identifiers TO webuser;


--
-- Name: tbl_registry_objects; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_registry_objects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_registry_objects FROM dba;
GRANT ALL ON TABLE tbl_registry_objects TO dba;
GRANT ALL ON TABLE tbl_registry_objects TO webuser;


--
-- Name: vw_registry_objects; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE vw_registry_objects FROM PUBLIC;
REVOKE ALL ON TABLE vw_registry_objects FROM dba;
GRANT ALL ON TABLE vw_registry_objects TO dba;
GRANT SELECT ON TABLE vw_registry_objects TO webuser;


--
-- Name: tbl_locations; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_locations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_locations FROM dba;
GRANT ALL ON TABLE tbl_locations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_locations TO webuser;


--
-- Name: tbl_name_parts; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_name_parts FROM PUBLIC;
REVOKE ALL ON TABLE tbl_name_parts FROM dba;
GRANT ALL ON TABLE tbl_name_parts TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_name_parts TO webuser;


--
-- Name: tbl_physical_addresses; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_physical_addresses FROM PUBLIC;
REVOKE ALL ON TABLE tbl_physical_addresses FROM dba;
GRANT ALL ON TABLE tbl_physical_addresses TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_physical_addresses TO webuser;


--
-- Name: vw_names; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE vw_names FROM PUBLIC;
REVOKE ALL ON TABLE vw_names FROM dba;
GRANT ALL ON TABLE vw_names TO dba;
GRANT SELECT ON TABLE vw_names TO webuser;


--
-- Name: tbl_spatial_locations; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_spatial_locations FROM PUBLIC;
REVOKE ALL ON TABLE tbl_spatial_locations FROM dba;
GRANT ALL ON TABLE tbl_spatial_locations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_spatial_locations TO webuser;


--
-- Name: tbl_related_info; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_related_info FROM PUBLIC;
REVOKE ALL ON TABLE tbl_related_info FROM dba;
GRANT ALL ON TABLE tbl_related_info TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_related_info TO webuser;


--
-- Name: tbl_related_objects; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_related_objects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_related_objects FROM dba;
GRANT ALL ON TABLE tbl_related_objects TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_related_objects TO webuser;


--
-- Name: tbl_relation_descriptions; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_relation_descriptions FROM PUBLIC;
REVOKE ALL ON TABLE tbl_relation_descriptions FROM dba;
GRANT ALL ON TABLE tbl_relation_descriptions TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_relation_descriptions TO webuser;


--
-- Name: tbl_oai_rt_resumption_tokens; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_oai_rt_resumption_tokens FROM PUBLIC;
REVOKE ALL ON TABLE tbl_oai_rt_resumption_tokens FROM dba;
GRANT ALL ON TABLE tbl_oai_rt_resumption_tokens TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_oai_rt_resumption_tokens TO webuser;


--
-- Name: tbl_subjects; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_subjects FROM PUBLIC;
REVOKE ALL ON TABLE tbl_subjects FROM dba;
GRANT ALL ON TABLE tbl_subjects TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE tbl_subjects TO webuser;


--
-- Name: tbl_ids; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_ids FROM PUBLIC;
REVOKE ALL ON TABLE tbl_ids FROM dba;
GRANT ALL ON TABLE tbl_ids TO dba;
GRANT SELECT,UPDATE ON TABLE tbl_ids TO webuser;


--
-- Name: tbl_oai_rt_complete_list_records; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_oai_rt_complete_list_records FROM PUBLIC;
REVOKE ALL ON TABLE tbl_oai_rt_complete_list_records FROM dba;
GRANT ALL ON TABLE tbl_oai_rt_complete_list_records TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_oai_rt_complete_list_records TO webuser;


--
-- Name: tbl_oai_rt_complete_lists; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_oai_rt_complete_lists FROM PUBLIC;
REVOKE ALL ON TABLE tbl_oai_rt_complete_lists FROM dba;
GRANT ALL ON TABLE tbl_oai_rt_complete_lists TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE tbl_oai_rt_complete_lists TO webuser;


--
-- Name: tbl_statuses; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE tbl_statuses FROM PUBLIC;
REVOKE ALL ON TABLE tbl_statuses FROM dba;
GRANT ALL ON TABLE tbl_statuses TO dba;
GRANT SELECT ON TABLE tbl_statuses TO webuser;


--
-- Name: vw_registry_search; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE vw_registry_search FROM PUBLIC;
REVOKE ALL ON TABLE vw_registry_search FROM dba;
GRANT ALL ON TABLE vw_registry_search TO dba;
GRANT SELECT ON TABLE vw_registry_search TO webuser;


/*
================================================================================
3. INSERT THE INITIAL DATA
================================================================================
*/

INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_oai_rt_complete_lists.complete_list_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_spatial_locations.spatial_location_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_subjects.subject_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_identifiers.identifier_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_complex_names.complex_name_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_name_parts.name_part_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_related_objects.relation_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_relation_description_id.relation_description_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_access_policies.access_policy_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_related_info.related_info_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_simple_names.simple_name_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_locations.location_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_address_locations.address_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_electronic_addresses.electronic_address_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_electronic_address_args.electronic_address_arg_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_physical_addresses.physical_address_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_address_parts.address_part_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_descriptions.description_id', 0);
INSERT INTO dba.tbl_ids (column_identifier, next_id) VALUES ('dba.tbl_data_source_logs.event_id', 0);

INSERT INTO dba.tbl_data_sources (data_source_key, title) VALUES ('PUBLISH_MY_DATA', 'Publish My Data');

INSERT INTO dba.tbl_statuses (status) VALUES ('APPROVED');
INSERT INTO dba.tbl_statuses (status) VALUES ('PENDING');


