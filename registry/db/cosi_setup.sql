/*******************************************************************************
$Date: 2009-08-11 16:36:22 +1000 (Tue, 11 Aug 2009) $
$Revision: 39 $
********************************************************************************

There are 4 steps.

================================================================================
1. CREATE THE REQUIRED LOGIN ROLES
================================================================================
Connect to PostgreSQL as the postgres user then:
 - Create a login role called webuser.
 - Create a login role called dba.

Edit the Postgres pg_hba.conf to allow trusted access from the web server for 
the 'webuser' account.

================================================================================
2. CREATE THE COSI DATABASE
================================================================================
 i) Create the COSI database as follows:
*/
CREATE DATABASE dbs_cosi
  WITH OWNER = dba
       ENCODING = 'UTF8'
       TABLESPACE = pg_default;
GRANT ALL ON DATABASE dbs_cosi TO dba;
GRANT CONNECT, TEMPORARY ON DATABASE dbs_cosi TO webuser;


/*
 ii) With the new COSI database:
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
3. CREATE THE COSI DATABASE STRUCTURE
================================================================================
Connect to new COSI database (dbs_cosi) as the dba user to create tables, 
views and user defined types & functions:
*/
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = dba, pg_catalog;

--
-- TOC entry 29 (class 1255 OID 77494)
-- Dependencies: 302 5
-- Name: dba.udf_authenticate_with_built_in(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_authenticate_with_built_in(_role_id character varying, _passphrase_sha1 character varying) RETURNS character varying
    AS $$ 
/*******************************************************************************
       Object: dba.udf_authenticate_with_built_in
   Written By: James Blanden
 Created Date: 08 May 2007
  Description: 

 Updated By           Date          Modifications
 ==================== ============= ============================================
 
 
*******************************************************************************/
DECLARE
	_valid_credentials BOOLEAN := FALSE;
	_count INT := 0;
BEGIN
	SELECT INTO _count
	 COUNT(role_id)
	FROM dba.tbl_authentication_built_in ABI
	WHERE ABI.role_id = _role_id
	  AND ABI.passphrase_sha1 = _passphrase_sha1;
	
	IF _count = 1 THEN
	 _valid_credentials = TRUE;
	END IF;
	RETURN _valid_credentials;
END;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION dba.udf_authenticate_with_built_in(_role_id character varying, _passphrase_sha1 character varying) OWNER TO dba;

--
-- TOC entry 32 (class 1255 OID 77480)
-- Dependencies: 5
-- Name: dba.udf_delete_role(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_delete_role(_role_id character varying) RETURNS void
    AS $_$ 

DELETE FROM dba.tbl_authentication_built_in WHERE role_id = $1;
DELETE FROM dba.tbl_role_activities WHERE role_id = $1;
DELETE FROM dba.tbl_role_relations WHERE parent_role_id = $1 OR child_role_id = $1;
DELETE FROM dba.tbl_roles WHERE role_id = $1;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_delete_role(_role_id character varying) OWNER TO dba;

--
-- TOC entry 41 (class 1255 OID 77473)
-- Dependencies: 5
-- Name: dba.udf_delete_role_activity(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_delete_role_activity(_role_id character varying, _activity_id character varying) RETURNS void
    AS $_$ 

DELETE FROM dba.tbl_role_activities
WHERE role_id = $1
  AND activity_id = $2
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_delete_role_activity(_role_id character varying, _activity_id character varying) OWNER TO dba;

--
-- TOC entry 43 (class 1255 OID 77475)
-- Dependencies: 5
-- Name: dba.udf_delete_role_relation(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_delete_role_relation(_child_role_id character varying, _parent_role_id character varying) RETURNS void
    AS $_$ 

DELETE FROM dba.tbl_role_relations
WHERE child_role_id = $1
  AND parent_role_id = $2
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_delete_role_relation(_child_role_id character varying, _parent_role_id character varying) OWNER TO dba;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1312 (class 1259 OID 17106)
-- Dependencies: 1662 1663 1664 1665 5
-- Name: dba.tbl_role_activities; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_role_activities (
    role_id character varying(255) NOT NULL,
    activity_id character varying(64) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);


ALTER TABLE dba.tbl_role_activities OWNER TO dba;

--
-- TOC entry 28 (class 1255 OID 77493)
-- Dependencies: 5 291
-- Name: dba.udf_get_activity_role_ids(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_activity_role_ids(_activity_id character varying) RETURNS SETOF dba.tbl_role_activities
    AS $_$ 

SELECT 
 *
FROM dba.tbl_role_activities
WHERE activity_id = $1;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_activity_role_ids(_activity_id character varying) OWNER TO dba;

--
-- TOC entry 1308 (class 1259 OID 17079)
-- Dependencies: 1652 5
-- Name: dba.tbl_authentication_services; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_authentication_services (
    authentication_service_id character(32) NOT NULL,
    name character varying(64) NOT NULL,
    description text,
    enabled boolean DEFAULT false NOT NULL
);


ALTER TABLE dba.tbl_authentication_services OWNER TO dba;

--
-- TOC entry 38 (class 1255 OID 77486)
-- Dependencies: 5 299
-- Name: dba.udf_get_authentication_services(); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_authentication_services() RETURNS SETOF dba.tbl_authentication_services
    AS $$ 

SELECT 
 *
FROM dba.tbl_authentication_services
;

$$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_authentication_services() OWNER TO dba;

--
-- TOC entry 1313 (class 1259 OID 17115)
-- Dependencies: 1666 1667 1668 1669 5
-- Name: dba.tbl_role_relations; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_role_relations (
    parent_role_id character varying(255) NOT NULL,
    child_role_id character varying(255) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);


ALTER TABLE dba.tbl_role_relations OWNER TO dba;

--
-- TOC entry 27 (class 1255 OID 77492)
-- Dependencies: 5 293
-- Name: dba.udf_get_child_role_ids(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_child_role_ids(_parent_role_id character varying) RETURNS SETOF dba.tbl_role_relations
    AS $_$ 

SELECT 
 RR.*
FROM dba.tbl_role_relations RR
JOIN dba.tbl_roles R
  ON RR.child_role_id=R.role_id
WHERE RR.parent_role_id = $1
  AND R.enabled=TRUE; -- Don't return disabled roles

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_child_role_ids(_parent_role_id character varying) OWNER TO dba;

--
-- TOC entry 1310 (class 1259 OID 17094)
-- Dependencies: 5
-- Name: dba.tbl_role_types; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_role_types (
    role_type_id character(20) NOT NULL,
    name character varying(64) NOT NULL
);


ALTER TABLE dba.tbl_role_types OWNER TO dba;

--
-- TOC entry 1311 (class 1259 OID 17096)
-- Dependencies: 1657 1658 1659 1660 1661 5
-- Name: dba.tbl_roles; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_roles (
    role_id character varying(255) NOT NULL,
    role_type_id character(20) NOT NULL,
    name character varying(255) NOT NULL,
    authentication_service_id character(32),
    enabled boolean DEFAULT true NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    last_login timestamp(6) with time zone
);


ALTER TABLE dba.tbl_roles OWNER TO dba;

--
-- TOC entry 1314 (class 1259 OID 55412)
-- Dependencies: 1390 5
-- Name: dba.vw_roles; Type: VIEW; Schema: dba; Owner: dba
--

CREATE VIEW dba.vw_roles AS
    SELECT r.role_id, r.role_type_id, rt.name AS role_type_name, r.name AS role_name, r.authentication_service_id, a.name AS authentication_service_name, a.enabled AS authentication_service_enabled, r.enabled AS role_enabled, r.created_when, r.created_who, r.modified_when, r.modified_who, r.last_login FROM ((tbl_roles r JOIN dba.tbl_role_types rt ON ((rt.role_type_id = r.role_type_id))) LEFT JOIN dba.tbl_authentication_services a ON ((a.authentication_service_id = r.authentication_service_id)));


ALTER TABLE dba.vw_roles OWNER TO dba;

--
-- TOC entry 18 (class 1255 OID 77499)
-- Dependencies: 5 295
-- Name: dba.udf_get_organisational_roles(); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_organisational_roles() RETURNS SETOF dba.vw_roles
    AS $$ 

SELECT 
 *
FROM dba.vw_roles
WHERE role_type_id = 'ROLE_ORGANISATIONAL'
ORDER BY role_name ASC
;

$$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_organisational_roles() OWNER TO dba;

--
-- TOC entry 26 (class 1255 OID 77491)
-- Dependencies: 5 293
-- Name: dba.udf_get_parent_role_ids(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_parent_role_ids(_child_role_id character varying) RETURNS SETOF dba.tbl_role_relations
    AS $_$ 

SELECT 
 RR.*
FROM dba.tbl_role_relations RR
JOIN dba.tbl_roles R
  ON RR.parent_role_id=R.role_id
WHERE RR.child_role_id = $1
  AND R.enabled=TRUE; -- Don't return disabled roles

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_parent_role_ids(_child_role_id character varying) OWNER TO dba;

--
-- TOC entry 21 (class 1255 OID 106349)
-- Dependencies: 5 291
-- Name: dba.udf_get_role_activities(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_activities(_role_id character varying) RETURNS SETOF dba.tbl_role_activities
    AS $_$ 

SELECT 
 *
FROM dba.tbl_role_activities
WHERE role_id = $1
ORDER BY activity_id ASC
;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_role_activities(_role_id character varying) OWNER TO dba;

--
-- TOC entry 1307 (class 1259 OID 17070)
-- Dependencies: 1648 1649 1650 1651 5
-- Name: dba.tbl_activities; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_activities (
    activity_id character varying(64) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);


ALTER TABLE dba.tbl_activities OWNER TO dba;

--
-- TOC entry 44 (class 1255 OID 77477)
-- Dependencies: 5 297
-- Name: dba.udf_get_role_activity_add_list(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_activity_add_list(_role_id character varying) RETURNS SETOF dba.tbl_activities
    AS $_$ 

SELECT
 *
FROM dba.tbl_activities
WHERE activity_id NOT IN
(
  SELECT activity_id
  FROM dba.tbl_role_activities
  WHERE role_id = $1
     OR role_id = 'PUBLIC'
     OR role_id = 'COSI_BUILT_IN_USERS'
)
-- Only functional roles can have activities.
AND $1 IN
(
  SELECT role_id
  FROM dba.tbl_roles
  WHERE role_type_id = 'ROLE_FUNCTIONAL'
)
-- Exclude the special activity for password changes
AND activity_id <> 'aCOSI_CHANGE_BUILT_IN_PASS'
ORDER BY activity_id ASC
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_role_activity_add_list(_role_id character varying) OWNER TO dba;

--
-- TOC entry 25 (class 1255 OID 77490)
-- Dependencies: 302 5
-- Name: dba.udf_get_role_auth_service_id(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_auth_service_id(_role_id character varying) RETURNS character varying
    AS $$ 

DECLARE
	_auth_service_id VARCHAR := '';
BEGIN
	SELECT INTO _auth_service_id
	 R.authentication_service_id
	FROM dba.tbl_roles R
	JOIN dba.tbl_authentication_services A
	  ON R.authentication_service_id=A.authentication_service_id
	WHERE R.role_id = _role_id
	  AND A.enabled
	  AND R.enabled;
	RETURN _auth_service_id;
END;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION dba.udf_get_role_auth_service_id(_role_id character varying) OWNER TO dba;

--
-- TOC entry 31 (class 1255 OID 77496)
-- Dependencies: 5
-- Name: dba.udf_get_role_enabled(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_enabled(_role_id character varying) RETURNS boolean
    AS $_$ 

SELECT
 enabled
FROM dba.tbl_roles
WHERE role_id = $1
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_role_enabled(_role_id character varying) OWNER TO dba;

--
-- TOC entry 23 (class 1255 OID 77489)
-- Dependencies: 302 5
-- Name: dba.udf_get_role_name(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_name(_role_id character varying) RETURNS character varying
    AS $$ 

DECLARE
	_name VARCHAR := '';
BEGIN
	SELECT INTO _name
	 name
	FROM dba.tbl_roles
	WHERE role_id = _role_id;
	RETURN _name;
END;
$$
    LANGUAGE plpgsql;


ALTER FUNCTION dba.udf_get_role_name(_role_id character varying) OWNER TO dba;

--
-- TOC entry 19 (class 1255 OID 106347)
-- Dependencies: 5 280
-- Name: dba.udf_get_role_relation_add_list(character varying, character); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_relation_add_list(_role_id character varying, _parent_role_type_id character) RETURNS SETOF dba.tbl_roles
    AS $_$ 

SELECT
 *
FROM dba.tbl_roles
WHERE role_type_id = $2
AND role_id NOT IN
(
  SELECT parent_role_id
  FROM dba.tbl_role_relations
  WHERE child_role_id = $1
)
AND role_id <> $1
AND role_id <> 'PUBLIC'
AND role_id <> 'COSI_BUILT_IN_USERS'
AND enabled = true
ORDER BY role_id ASC
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_role_relation_add_list(_role_id character varying, _parent_role_type_id character) OWNER TO dba;

--
-- TOC entry 1315 (class 1259 OID 68066)
-- Dependencies: 1391 5
-- Name: dba.vw_role_relations; Type: VIEW; Schema: dba; Owner: dba
--

CREATE VIEW dba.vw_role_relations AS
    SELECT rr.child_role_id, rr.parent_role_id, r.role_type_id AS parent_role_type_id, r.name AS parent_role_name, rr.created_when, rr.created_who FROM (tbl_roles r JOIN dba.tbl_role_relations rr ON (((rr.parent_role_id)::text = (r.role_id)::text)));


ALTER TABLE dba.vw_role_relations OWNER TO dba;

--
-- TOC entry 20 (class 1255 OID 106348)
-- Dependencies: 296 5
-- Name: dba.udf_get_role_relations(character varying, character); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_relations(_role_id character varying, _parent_role_type_id character) RETURNS SETOF dba.vw_role_relations
    AS $_$ 

SELECT 
 *
FROM dba.vw_role_relations
WHERE child_role_id = $1
  AND parent_role_type_id = $2
ORDER BY parent_role_id ASC
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_role_relations(_role_id character varying, _parent_role_type_id character) OWNER TO dba;

--
-- TOC entry 37 (class 1255 OID 77485)
-- Dependencies: 5 279
-- Name: dba.udf_get_role_types(); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_role_types() RETURNS SETOF dba.tbl_role_types
    AS $$ 

SELECT 
 *
FROM dba.tbl_role_types
;

$$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_role_types() OWNER TO dba;

--
-- TOC entry 39 (class 1255 OID 77487)
-- Dependencies: 295 5
-- Name: dba.udf_get_roles(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_get_roles(_role_id character varying, _filter character varying) RETURNS SETOF dba.vw_roles
    AS $_$ 

SELECT 
 *
FROM dba.vw_roles
WHERE
(
  role_id = $1
  OR $1 IS NULL
) AND (
  UPPER(role_id) LIKE '%'||UPPER($2)||'%'
  OR UPPER(role_name) LIKE '%'||UPPER($2)||'%'
  OR UPPER(role_type_name) LIKE '%'||UPPER($2)
  OR UPPER(authentication_service_name) LIKE '%'||UPPER($2)
  OR $2 IS NULL
  -- Exclude the special built-in roles from the results.
) AND (
  role_id <> 'PUBLIC'
) AND (
  role_id <> 'COSI_BUILT_IN_USERS'
)
ORDER BY role_type_id, authentication_service_id, role_name ASC
;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_get_roles(_role_id character varying, _filter character varying) OWNER TO dba;

--
-- TOC entry 33 (class 1255 OID 77481)
-- Dependencies: 5
-- Name: dba.udf_has_built_in_authentication(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_has_built_in_authentication(_role_id character varying) RETURNS bigint
    AS $_$ 

SELECT 
 count(role_id) AS exists
FROM dba.tbl_authentication_built_in
WHERE role_id = $1
;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_has_built_in_authentication(_role_id character varying) OWNER TO dba;

--
-- TOC entry 34 (class 1255 OID 77482)
-- Dependencies: 5
-- Name: dba.udf_insert_built_in_authentication_user(character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_insert_built_in_authentication_user(_user character varying, _role_id character varying, _passphrase_sha1 character varying) RETURNS void
    AS $_$ 

INSERT INTO dba.tbl_authentication_built_in
(
  created_who,
  modified_who,
  role_id,
  passphrase_sha1
) VALUES (
  $1,
  $1,
  $2,
  $3
)
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_insert_built_in_authentication_user(_user character varying, _role_id character varying, _passphrase_sha1 character varying) OWNER TO dba;

--
-- TOC entry 36 (class 1255 OID 77484)
-- Dependencies: 5
-- Name: dba.udf_insert_role(character varying, character varying, character, character varying, character, boolean); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_insert_role(_user character varying, _role_id character varying, _role_type_id character, _name character varying, _authentication_service_id character, _enabled boolean) RETURNS void
    AS $_$ 

INSERT INTO dba.tbl_roles
(
  created_who,
  modified_who,
  role_id,
  role_type_id,
  name,
  authentication_service_id,
  enabled
) VALUES (
  $1,
  $1,
  $2,
  $3,
  $4,
  $5,
  $6
)
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_insert_role(_user character varying, _role_id character varying, _role_type_id character, _name character varying, _authentication_service_id character, _enabled boolean) OWNER TO dba;

--
-- TOC entry 40 (class 1255 OID 77472)
-- Dependencies: 5
-- Name: dba.udf_insert_role_activity(character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_insert_role_activity(_user character varying, _role_id character varying, _activity_id character varying) RETURNS void
    AS $_$ 

INSERT INTO dba.tbl_role_activities
(
 role_id,
 activity_id,
 created_who,
 modified_who
) VALUES (
 $2,
 $3,
 $1,
 $1
)
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_insert_role_activity(_user character varying, _role_id character varying, _activity_id character varying) OWNER TO dba;

--
-- TOC entry 42 (class 1255 OID 77474)
-- Dependencies: 5
-- Name: dba.udf_insert_role_relation(character varying, character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_insert_role_relation(_user character varying, _child_role_id character varying, _parent_role_id character varying) RETURNS void
    AS $_$ 

INSERT INTO dba.tbl_role_relations
(
 child_role_id,
 parent_role_id,
 created_who,
 modified_who
) VALUES (
 $2,
 $3,
 $1,
 $1
)
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_insert_role_relation(_user character varying, _child_role_id character varying, _parent_role_id character varying) OWNER TO dba;

--
-- TOC entry 30 (class 1255 OID 77495)
-- Dependencies: 5
-- Name: dba.udf_update_built_in_passphrase(character varying, character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_update_built_in_passphrase(_role_id character varying, _passphrase_sha1 character varying) RETURNS void
    AS $_$ 

UPDATE dba.tbl_authentication_built_in
SET passphrase_sha1 = $2,
modified_when = now(),
modified_who = $1
WHERE role_id = $1;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_update_built_in_passphrase(_role_id character varying, _passphrase_sha1 character varying) OWNER TO dba;

--
-- TOC entry 22 (class 1255 OID 77488)
-- Dependencies: 5
-- Name: dba.udf_update_last_login(character varying); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_update_last_login(_role_id character varying) RETURNS void
    AS $_$ 

UPDATE dba.tbl_roles
SET last_login = now()
WHERE role_id = $1
;

$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_update_last_login(_role_id character varying) OWNER TO dba;

--
-- TOC entry 35 (class 1255 OID 77483)
-- Dependencies: 5
-- Name: dba.udf_update_role(character varying, character varying, character varying, character, boolean); Type: FUNCTION; Schema: dba; Owner: dba
--

CREATE FUNCTION dba.udf_update_role(_user character varying, _role_id character varying, _name character varying, _authentication_service_id character, _enabled boolean) RETURNS void
    AS $_$ 

UPDATE dba.tbl_roles SET
  modified_who = $1,
  modified_when = now(),
  name = $3,
  authentication_service_id = $4,
  enabled = $5
WHERE role_id = $2
;
$_$
    LANGUAGE sql;


ALTER FUNCTION dba.udf_update_role(_user character varying, _role_id character varying, _name character varying, _authentication_service_id character, _enabled boolean) OWNER TO dba;

--
-- TOC entry 1309 (class 1259 OID 17085)
-- Dependencies: 1653 1654 1655 1656 5
-- Name: dba.tbl_authentication_built_in; Type: TABLE; Schema: dba; Owner: dba; Tablespace: 
--

CREATE TABLE dba.tbl_authentication_built_in (
    role_id character varying(255) NOT NULL,
    passphrase_sha1 character varying(40) NOT NULL,
    created_when timestamp(6) with time zone DEFAULT now(),
    created_who character varying(255) DEFAULT 'SYSTEM'::character varying,
    modified_when timestamp(6) with time zone DEFAULT now(),
    modified_who character varying(255) DEFAULT 'SYSTEM'::character varying
);


ALTER TABLE dba.tbl_authentication_built_in OWNER TO dba;

--
-- TOC entry 1671 (class 2606 OID 17125)
-- Dependencies: 1307 1307
-- Name: pk_tbl_activities; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_activities
    ADD CONSTRAINT pk_tbl_activities PRIMARY KEY (activity_id);


--
-- TOC entry 1675 (class 2606 OID 17129)
-- Dependencies: 1309 1309
-- Name: pk_tbl_authentication_built_in; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_authentication_built_in
    ADD CONSTRAINT pk_tbl_authentication_built_in PRIMARY KEY (role_id);


--
-- TOC entry 1673 (class 2606 OID 17127)
-- Dependencies: 1308 1308
-- Name: pk_tbl_authentication_services; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_authentication_services
    ADD CONSTRAINT pk_tbl_authentication_services PRIMARY KEY (authentication_service_id);


--
-- TOC entry 1681 (class 2606 OID 17135)
-- Dependencies: 1312 1312 1312
-- Name: pk_tbl_role_activities; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_role_activities
    ADD CONSTRAINT pk_tbl_role_activities PRIMARY KEY (role_id, activity_id);


--
-- TOC entry 1683 (class 2606 OID 17137)
-- Dependencies: 1313 1313 1313
-- Name: pk_tbl_role_relations; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_role_relations
    ADD CONSTRAINT pk_tbl_role_relations PRIMARY KEY (parent_role_id, child_role_id);


--
-- TOC entry 1677 (class 2606 OID 17131)
-- Dependencies: 1310 1310
-- Name: pk_tbl_role_types; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_role_types
    ADD CONSTRAINT pk_tbl_role_types PRIMARY KEY (role_type_id);


--
-- TOC entry 1679 (class 2606 OID 17133)
-- Dependencies: 1311 1311
-- Name: pk_tbl_roles; Type: CONSTRAINT; Schema: dba; Owner: dba; Tablespace: 
--

ALTER TABLE ONLY dba.tbl_roles
    ADD CONSTRAINT pk_tbl_roles PRIMARY KEY (role_id);


--
-- TOC entry 1687 (class 2606 OID 115389)
-- Dependencies: 1312 1670 1307
-- Name: fk_tbl_role_activities_2; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY dba.tbl_role_activities
    ADD CONSTRAINT fk_tbl_role_activities_2 FOREIGN KEY (activity_id) REFERENCES dba.tbl_activities(activity_id);


--
-- TOC entry 1688 (class 2606 OID 17158)
-- Dependencies: 1678 1311 1313
-- Name: fk_tbl_role_relations_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY dba.tbl_role_relations
    ADD CONSTRAINT fk_tbl_role_relations_1 FOREIGN KEY (parent_role_id) REFERENCES dba.tbl_roles(role_id);


--
-- TOC entry 1689 (class 2606 OID 17163)
-- Dependencies: 1311 1313 1678
-- Name: fk_tbl_role_relations_2; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY dba.tbl_role_relations
    ADD CONSTRAINT fk_tbl_role_relations_2 FOREIGN KEY (child_role_id) REFERENCES dba.tbl_roles(role_id);


--
-- TOC entry 1686 (class 2606 OID 17148)
-- Dependencies: 1311 1678 1312
-- Name: fk_tbl_role_resource_actions_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY dba.tbl_role_activities
    ADD CONSTRAINT fk_tbl_role_resource_actions_1 FOREIGN KEY (role_id) REFERENCES dba.tbl_roles(role_id);


--
-- TOC entry 1684 (class 2606 OID 17138)
-- Dependencies: 1310 1676 1311
-- Name: fk_tbl_roles_1; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY dba.tbl_roles
    ADD CONSTRAINT fk_tbl_roles_1 FOREIGN KEY (role_type_id) REFERENCES dba.tbl_role_types(role_type_id);


--
-- TOC entry 1685 (class 2606 OID 17143)
-- Dependencies: 1308 1672 1311
-- Name: fk_tbl_roles_2; Type: FK CONSTRAINT; Schema: dba; Owner: dba
--

ALTER TABLE ONLY dba.tbl_roles
    ADD CONSTRAINT fk_tbl_roles_2 FOREIGN KEY (authentication_service_id) REFERENCES dba.tbl_authentication_services(authentication_service_id);


--
-- TOC entry 1692 (class 0 OID 0)
-- Dependencies: 5
-- Name: dba; Type: ACL; Schema: -; Owner: dba
--

REVOKE ALL ON SCHEMA dba FROM PUBLIC;
REVOKE ALL ON SCHEMA dba FROM dba;
GRANT ALL ON SCHEMA dba TO dba;
GRANT USAGE ON SCHEMA dba TO webuser;


--
-- TOC entry 1693 (class 0 OID 0)
-- Dependencies: 1312
-- Name: dba.tbl_role_activities; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_role_activities FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_role_activities FROM dba;
GRANT ALL ON TABLE dba.tbl_role_activities TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_role_activities TO webuser;


--
-- TOC entry 1694 (class 0 OID 0)
-- Dependencies: 1308
-- Name: dba.tbl_authentication_services; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_authentication_services FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_authentication_services FROM dba;
GRANT ALL ON TABLE dba.tbl_authentication_services TO dba;
GRANT SELECT ON TABLE dba.tbl_authentication_services TO webuser;


--
-- TOC entry 1695 (class 0 OID 0)
-- Dependencies: 1313
-- Name: dba.tbl_role_relations; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_role_relations FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_role_relations FROM dba;
GRANT ALL ON TABLE dba.tbl_role_relations TO dba;
GRANT SELECT,INSERT,DELETE ON TABLE dba.tbl_role_relations TO webuser;


--
-- TOC entry 1696 (class 0 OID 0)
-- Dependencies: 1310
-- Name: dba.tbl_role_types; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_role_types FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_role_types FROM dba;
GRANT ALL ON TABLE dba.tbl_role_types TO dba;
GRANT SELECT ON TABLE dba.tbl_role_types TO webuser;


--
-- TOC entry 1697 (class 0 OID 0)
-- Dependencies: 1311
-- Name: dba.tbl_roles; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_roles FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_roles FROM dba;
GRANT ALL ON TABLE dba.tbl_roles TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE dba.tbl_roles TO webuser;


--
-- TOC entry 1698 (class 0 OID 0)
-- Dependencies: 1314
-- Name: dba.vw_roles; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.vw_roles FROM PUBLIC;
REVOKE ALL ON TABLE dba.vw_roles FROM dba;
GRANT ALL ON TABLE dba.vw_roles TO dba;
GRANT SELECT ON TABLE dba.vw_roles TO webuser;


--
-- TOC entry 1699 (class 0 OID 0)
-- Dependencies: 1307
-- Name: dba.tbl_activities; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_activities FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_activities FROM dba;
GRANT ALL ON TABLE dba.tbl_activities TO dba;
GRANT SELECT ON TABLE dba.tbl_activities TO webuser;


--
-- TOC entry 1700 (class 0 OID 0)
-- Dependencies: 1315
-- Name: dba.vw_role_relations; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.vw_role_relations FROM PUBLIC;
REVOKE ALL ON TABLE dba.vw_role_relations FROM dba;
GRANT ALL ON TABLE dba.vw_role_relations TO dba;
GRANT SELECT ON TABLE dba.vw_role_relations TO webuser;


--
-- TOC entry 1701 (class 0 OID 0)
-- Dependencies: 1309
-- Name: dba.tbl_authentication_built_in; Type: ACL; Schema: dba; Owner: dba
--

REVOKE ALL ON TABLE dba.tbl_authentication_built_in FROM PUBLIC;
REVOKE ALL ON TABLE dba.tbl_authentication_built_in FROM dba;
GRANT ALL ON TABLE dba.tbl_authentication_built_in TO dba;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE dba.tbl_authentication_built_in TO webuser;


/*
================================================================================
4. INSERT THE INITIAL DATA
================================================================================
This will create a built-in user role with User ID 'cosiadmin' and Passphrase 'abc123'
which can then be used to administer roles via the web interface.
*/
-- TYPES
INSERT INTO dba.tbl_role_types (role_type_id, name) VALUES ('ROLE_USER', 'User');
INSERT INTO dba.tbl_role_types (role_type_id, name) VALUES ('ROLE_FUNCTIONAL', 'Functional');
INSERT INTO dba.tbl_role_types (role_type_id, name) VALUES ('ROLE_ORGANISATIONAL', 'Organisational');

INSERT INTO dba.tbl_authentication_services (authentication_service_id, name, description, enabled) VALUES ('AUTHENTICATION_BUILT_IN', 'Built-in', '', TRUE);
INSERT INTO dba.tbl_authentication_services (authentication_service_id, name, description, enabled) VALUES ('AUTHENTICATION_LDAP', 'LDAP', '', TRUE);
INSERT INTO dba.tbl_authentication_services (authentication_service_id, name, description, enabled) VALUES ('AUTHENTICATION_SHIBBOLETH', 'Shibboleth', '', TRUE);

-- INITIAL ROLES
-- Hidden Built-in Roles
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('PUBLIC', 'ROLE_FUNCTIONAL', 'Public');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('COSI_BUILT_IN_USERS', 'ROLE_FUNCTIONAL', 'COSI Built-in Authentication User');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('COSI_ADMIN', 'ROLE_FUNCTIONAL', 'COSI Administrator');

-- Authentication And Affiliation Based Roles
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('LDAP_AUTHENTICATED', 'ROLE_FUNCTIONAL', 'LDAP Authenticated Users');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('LDAP_STAFF', 'ROLE_FUNCTIONAL', 'LDAP Affiliation - STAFF');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('LDAP_STUDENT', 'ROLE_FUNCTIONAL', 'LDAP Affiliation  - STUDENT');

INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('SHIB_AUTHENTICATED', 'ROLE_FUNCTIONAL', 'Shibboleth Authenticated Users');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('SHIB_STAFF', 'ROLE_FUNCTIONAL', 'Shibboleth Affiliation  - STAFF');
INSERT INTO dba.tbl_roles (role_id, role_type_id, name) VALUES ('SHIB_STUDENT', 'ROLE_FUNCTIONAL', 'Shibboleth Affiliation  - STUDENT');

-- User Roles
INSERT INTO dba.tbl_roles (role_id, role_type_id, name, authentication_service_id) VALUES ('cosiadmin', 'ROLE_USER', 'Default COSI Administrator', 'AUTHENTICATION_BUILT_IN');
INSERT INTO dba.tbl_authentication_built_in(role_id, passphrase_sha1) VALUES ('cosiadmin', '6367c48dd193d56ea7b0baad25b19455e529f5ee');

-- Functional Roles
INSERT INTO dba.tbl_role_relations(parent_role_id, child_role_id) VALUES ('COSI_ADMIN', 'cosiadmin');
	
-- DEFAULT CONFIGURATION DATA
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_ABOUT');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_HELP');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_INDEX');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_VERSIONS');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_LOGIN');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_CHANGE_BUILT_IN_PASS');

INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_ROLE_LIST');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_ROLE_ADD');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_ROLE_VIEW');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_ROLE_EDIT');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aCOSI_ROLE_DELETE');

INSERT INTO dba.tbl_activities (activity_id) VALUES ('aEXAMPLE_STYLE_SAMPLER');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aEXAMPLE_FORM_STYLE_SAMPLER');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aEXAMPLE_CHART_SAMPLER');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aEXAMPLE_THEME_ONE');
INSERT INTO dba.tbl_activities (activity_id) VALUES ('aEXAMPLE_THEME_TWO');

-- INITIAL ACCESS
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aCOSI_ABOUT');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aCOSI_HELP');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aCOSI_INDEX');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aCOSI_VERSIONS');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('PUBLIC', 'aCOSI_LOGIN');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_BUILT_IN_USERS', 'aCOSI_CHANGE_BUILT_IN_PASS');

INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_LIST');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_ADD');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_VIEW');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_EDIT');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aCOSI_ROLE_DELETE');

INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aEXAMPLE_STYLE_SAMPLER');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aEXAMPLE_FORM_STYLE_SAMPLER');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aEXAMPLE_CHART_SAMPLER');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aEXAMPLE_THEME_ONE');
INSERT INTO dba.tbl_role_activities (role_id, activity_id) VALUES ('COSI_ADMIN', 'aEXAMPLE_THEME_TWO');


