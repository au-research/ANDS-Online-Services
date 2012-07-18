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
  
CREATE OR REPLACE FUNCTION dba.udf_search_draft_by_name(_search_text character varying, _object_class character varying, _data_source character varying, _limit integer)
  RETURNS SETOF dba.udt_name_search_result AS
$BODY$

SELECT draft_key AS registry_object_key, registry_object_title AS display_title, status,registry_object_type AS "type" FROM dba.tbl_draft_registry_objects
WHERE lower(registry_object_title) like lower('%'||$1||'%')
AND ($2 = '' OR "class" = $2)
AND ($3 = '' OR registry_object_data_source = $3)
LIMIT $4
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION dba.udf_search_draft_by_name(character varying, character varying, character varying, integer)
  OWNER TO dba;
  

-- fix issues caused by migrating {timestamp(6) with timezone} from PG 8.3 to PG 9.1
-- eg: 1844-01-01 10:04:52+10:04:52 should be 1844-01-01 10:00:00+10
--     1984-01-01 10:59:59+11       should be 1984-01-01 11:00:00+11

--5271;"516811d7-cd97-207a-e0440003ba8c79dd";"1983-01-01 11:00:00+11";"1984-01-01 10:59:59+11"
--5625;"516811d7-cb49-207a-e0440003ba8c79dd";"1965-01-01 10:00:00+10";"1966-01-01 09:59:59+10"
--125277;"paradisec.org.au/collection/AC2";"1844-01-01 10:04:52+10:04:52";"2022-08-02 10:00:00+10"
--125966;"paradisec.org.au/collection/NT3";"1864-01-01 10:04:52+10:04:52";"1923-01-01 10:00:00+10"
--1179646;"sydney.edu.au/stc/COL/0002";"1890-01-01 10:04:52+10:04:52";"1944-12-31 10:00:00+10"
--6353;"489c348f-be1a-4c76-a9d7-9e7fd48b97eb";"2008-06-09 13:32:00+10";"2008-08-29 13:32:00+10"
--5653;"b446b123-2692-4d4d-850d-023902db19b1";"2008-09-18 07:04:00+10";"2008-09-25 11:15:00+10"
--5465;"ab61701e-669f-47af-ae16-42aaf0b27064";"2008-06-09 13:32:00+10";"2008-08-29 13:32:00+10"
--5535;"516811d7-cd7b-207a-e0440003ba8c79dd";"1993-05-01 12:13:00+10";"1993-06-30 12:13:00+10"
--5601;"b2d77375-37c5-4bed-8173-8275e77d9a9e";"2008-09-18 14:43:00+10";"2008-09-19 14:44:00+10"
--5607;"316116c8-f889-42a0-adc3-ddd9aba2dced";"2008-09-18 07:04:00+10";"2008-09-18 12:00:00+10"
--5771;"516811d7-cb08-207a-e0440003ba8c79dd";"1890-01-01 10:04:52+10:04:52";""
--6003;"7848d4c6-7a0e-4e2e-87b1-9d22f27c5ce2";"2008-06-09 13:32:00+10";"2008-08-29 13:32:00+10"
--6093;"9f14afe6-f8ec-4a61-aa4d-bc7313b06727";"2008-06-09 13:32:00+10";"2008-08-29 13:32:00+10"

-- create the temporary fields:
alter table dba.tbl_locations add column "date_from_txt" text;
alter table dba.tbl_locations add column "date_to_txt" text;

alter table dba.tbl_locations add column "date_from_bkp" timestamp(6) with time zone;
alter table dba.tbl_locations add column "date_to_bkp" timestamp(6) with time zone;

-- backup the date_to and date_from fields
update dba.tbl_locations set date_from_bkp = date_from;
update dba.tbl_locations set date_to_bkp = date_to;
-- fix records with the faulty timezone by truncating hour
update dba.tbl_locations set date_to_bkp = date_trunc('hour',date_to_bkp) 
where round(extract(TIMEZONE FROM date_to_bkp)/3600) != extract(TIMEZONE FROM date_to_bkp)/3600;

update dba.tbl_locations set date_from_bkp = date_trunc('hour',date_from_bkp) 
where round(extract(TIMEZONE FROM date_from_bkp)/3600) != extract(TIMEZONE FROM date_from_bkp)/3600;

update dba.tbl_locations set date_from_bkp = date_trunc('hour',date_from_bkp) + interval '1 hour' where extract(minute from date_from_bkp) = 59;
update dba.tbl_locations set date_to_bkp = date_trunc('hour',date_to_bkp) + interval '1 hour' where extract(minute from date_to_bkp) = 59;

update dba.tbl_locations set date_to_txt = date_to_bkp where date_to_bkp is not null; 
update dba.tbl_locations set date_from_txt = date_from_bkp where date_from_bkp is not null; 
update dba.tbl_locations set date_from_txt = substr(date_from_txt,0,23) where date_from_bkp is not null;
update dba.tbl_locations set date_to_txt = substr(date_to_txt,0,23) where date_to_bkp is not null;

alter table dba.tbl_locations drop column "date_to_bkp";
alter table dba.tbl_locations drop column "date_from_bkp";

alter table dba.tbl_locations rename column "date_to_txt" to "date_to";
alter table dba.tbl_locations rename column "date_from_txt" to "date_from";
---- check result manually
----- eg: where date_from_old = '1844-01-01 10:04:52+10:04:52' the date_from should be "1844-01-01 10:00:00+10"
---- select * from dba.tbl_locations where location_id in (5465,5535,5601,5607,5653,5771,6003,6093,6353,125277,125966,1179646,5625,5271);
---- if satisfied by the results run
-- alter table dba.tbl_locations drop column "date_to_old";
-- alter table dba.tbl_locations drop column "date_from_old";

