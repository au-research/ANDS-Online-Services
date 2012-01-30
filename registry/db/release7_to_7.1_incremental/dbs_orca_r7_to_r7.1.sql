-- Function: dba.udf_get_harvest_method_count(character varying, character varying)

-- DROP FUNCTION dba.udf_get_harvest_method_count(character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_get_harvest_method_count(_date_filter character varying, _type_filter character varying)
  RETURNS bigint AS
$BODY$SELECT
COUNT(*) from dba.tbl_data_sources WHERE harvest_method = $2 
AND created_when <= CAST($1 AS timestamp with time zone)
;$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_get_harvest_method_count(character varying, character varying)
  OWNER TO dba;

