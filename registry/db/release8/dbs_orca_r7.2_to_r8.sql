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


-- Column: value

-- ALTER TABLE dba.tbl_descriptions DROP COLUMN value;

ALTER TABLE dba.tbl_descriptions ADD COLUMN value character varying(12000);



-- Column: institution_pages

-- ALTER TABLE dba.tbl_data_sources DROP COLUMN institution_pages;

ALTER TABLE dba.tbl_data_sources ADD COLUMN institution_pages smallint;
ALTER TABLE dba.tbl_data_sources ALTER COLUMN institution_pages SET DEFAULT 0;
UPDATE dba.tbl_data_sources SET institution_pages = 0;



-- Table: dba.tbl_institution_pages

-- DROP TABLE dba.tbl_institution_pages;

CREATE TABLE dba.tbl_institution_pages
(
  object_group character varying(255) NOT NULL,
  registry_object_key character varying(512),
  authoritive_data_source_key character varying(512),
  CONSTRAINT primary_key PRIMARY KEY (object_group )
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_institution_pages
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_institution_pages TO dba;
GRANT SELECT, INSERT, DELETE ON TABLE dba.tbl_institution_pages TO webuser;

-- Function: dba.udf_update_data_source(character varying, character varying, character varying, character varying, character varying, character varying, character varying, boolean, boolean, boolean, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, boolean, character varying, boolean, boolean, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, character varying, character varying)

DROP FUNCTION dba.udf_update_data_source(character varying, character varying, character varying, character varying, character varying, character varying, character varying, boolean, boolean, boolean, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, boolean, character varying, boolean, boolean, character varying, character varying, character varying, character varying, character varying, timestamp with time zone, character varying, character varying);

CREATE OR REPLACE FUNCTION dba.udf_update_data_source(_user character varying, _data_source_key character varying, _title character varying, _record_owner character varying, _contact_name character varying, _contact_email character varying, _notes character varying, _allow_reverse_internal_links boolean, _allow_reverse_external_links boolean, _create_primary_relationships boolean, _class_1 character varying, _primary_key_1 character varying, _collection_rel_1 character varying, _service_rel_1 character varying, _activity_rel_1 character varying, _party_rel_1 character varying, _class_2 character varying, _primary_key_2 character varying, _collection_rel_2 character varying, _service_rel_2 character varying, _activity_rel_2 character varying, _party_rel_2 character varying, _push_to_nla boolean, _isil_value character varying, _auto_publish boolean, _qa_flag boolean, _assess_notif_email_addr character varying, _institution_pages smallint, _uri character varying, _provider_type character varying, _harvest_method character varying, _oai_set character varying, _harvest_date timestamp with time zone, _time_zone_value character varying, _harvest_frequency character varying)
  RETURNS void AS
$BODY$
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
  institution_pages = $28,
  uri = $29,
  provider_type = $30,
  harvest_method = $31,
  oai_set = $32,
  harvest_date = $33,
  time_zone_value = $34, 
  harvest_frequency = $35
WHERE data_source_key = $2
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION dba.udf_update_data_source(character varying, character varying, character varying, character varying, character varying, character varying, character varying, boolean, boolean, boolean, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, character varying, boolean, character varying, boolean, boolean, character varying, smallint, character varying, character varying, character varying, character varying, timestamp with time zone, character varying, character varying)
  OWNER TO dba;

--We need to set up a vocal for licence types

INSERT INTO dba.tbl_vocabularies (identifier, identifier_type, version, name, name_type, description, source) VALUES ('RIFCSLicenceType', 'local', '1.3','RIFCS Licence Type', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.3.0/vocabs/vocabularies.html');


INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('CC-BY', 'local','CC-BY', 'CC-BY licence type','RIFCSLicenceType','Open Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('CC-BY-SA', 'local','CC-BY-SA', 'CC-BY-SA licence type','RIFCSLicenceType','Open Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('GPL', 'local','GPL', 'GPL licence type','RIFCSLicenceType','Open Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('CC-BY-NC', 'local','CC-BY-NC', 'CC-BY-NC licence type','RIFCSLicenceType','Non-Commercial Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('CC-BY-NC-SA', 'local','CC-BY-NC-NA', 'CC-BY-NC-NA licence type','RIFCSLicenceType','Non-Commercial Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('CC-BY-ND', 'local','CC-BY-ND', 'CC-BY-ND licence type','RIFCSLicenceType','Non-Derivative Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('CC-BY-NC-ND', 'local','CC-BY-NC-ND', 'CC-BY-NC-ND licence type','RIFCSLicenceType','Non-Derivative Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('AusGOALRestrictive', 'local','AusGOALRestrictive', 'AusGOALRestrictive licence type','RIFCSLicenceType','Restrictive Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('NoLicence', 'local','NoLicence', 'NoLicence licence type','RIFCSLicenceType','No Licence','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('Unknown/Other', 'local','Unknown/Other', 'Unknown/Other licence type','RIFCSLicenceType','Unknown','pt','RIFCS Licence Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('researchAreas', 'local','researchAreas', 'Contributor research areas','RIFCSDescriptionType','','pt','RIFCS Description Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('researchShowcase', 'local','researchShowcase', 'Contributor research showcase','RIFCSDescriptionType','','pt','RIFCS Description Type');
INSERT INTO dba.tbl_terms (identifier, identifier_type, name, description, vocabulary_identifier, parent_term_identifier,type,vocabpath) VALUES ('eResearchSupport', 'local','eResearchSupport', 'Contributor eResearch support','RIFCSDescriptionType','','pt','RIFCS Description Type');