-- Increase field lengths
ALTER TABLE dba.tbl_related_info
   ALTER COLUMN notes TYPE character varying(4000);
ALTER TABLE dba.tbl_citation_information
   ALTER COLUMN full_citation TYPE character varying(4000);


-- CC-159
DELETE FROM dba.tbl_terms WHERE identifier='UTC' AND vocabulary_identifier='RIFCSTemporalCoverageDateFormat';

-- CC-162
INSERT INTO dba.tbl_terms VALUES ('metadata', 'local', '', 'metadata', 'An alternative metadata format for the Object. This is most likely to be a discipline or system-specific format. E.g. NetCDF or ANZLIC. ', '', 'RIFCSRelatedInformationType', '', 'pt', '', 'RIFCS Related Information Type', '');

INSERT INTO dba.tbl_terms VALUES ('mediaType', 'local', '', 'mediaType', 'The Media Type (MIME type) of the information.', '', 'RIFCSRelatedInformationIdentifierType', '', 'pt', '', 'RIFCS Related Information Identifier Type', '');

-- CC-333
INSERT INTO dba.tbl_terms VALUES ('hasPrincipalInvestigator', 'local', '', 'hasPrincipalInvestigator', 'is researched by the related party', '', 'RIFCSActivityRelationType', '', 'pt', '', 'RIFCS Activity Relation Type', '');
INSERT INTO dba.tbl_terms VALUES ('hasPrincipalInvestigator', 'local', '', 'hasPrincipalInvestigator', 'is researched by the related party', '', 'RIFCSCollectionRelationType', '', 'pt', '', 'RIFCS Collection Relation Type', '');
INSERT INTO dba.tbl_terms VALUES ('isPrincipalInvestigatorOf', 'local', '', 'isPrincipalInvestigatorOf', 'is researcher of the related collection/activity', '', 'RIFCSPartyRelationType', '', 'pt', '', 'RIFCS Party Relation Type', '');

-- CC-353
INSERT INTO dba.tbl_terms VALUES ('iso31663', 'local', '', 'iso31663', 'ISO 3166-3 Codes for country names which have been deleted from ISO 3166-1 since its first publication in 1974.', '', 'RIFCSSpatialType', '', 'pt', '', 'RIFCS Spatial Type', '');
DELETE FROM dba.tbl_terms WHERE identifier='kml' AND vocabulary_identifier='RIFCSSpatialType';
DELETE FROM dba.tbl_terms WHERE identifier='gml' AND vocabulary_identifier='RIFCSSpatialType';

-- CC-387
DELETE FROM dba.tbl_terms WHERE identifier='initial' AND vocabulary_identifier='RIFCSNamePartType';

-- CC-85
INSERT INTO dba.tbl_vocabularies VALUES ('RIFCSDatesType', 'local', '1.4', 'RIFCS DatesType', 'primary', '', 'http://services.ands.org.au/documentation/rifcs/1.4/vocabs/vocabularies.html');

INSERT INTO dba.tbl_terms VALUES ('available', 'local', '', 'available', 'Date (often a range) that the resource became or will become available.', '', 'RIFCSDatesType', '', 'pt', '', 'RIFCS Dates Type', '');

INSERT INTO dba.tbl_terms VALUES ('created', 'local', '', 'created', 'Date of creation of the resource. ', '', 'RIFCSDatesType', '', 'pt', '', 'RIFCS Dates Type', '');

INSERT INTO dba.tbl_terms VALUES ('dateAccepted', 'local', '', 'dateAccepted', 'Date of acceptance of the resource.', '', 'RIFCSDatesType', '', 'pt', '', 'RIFCS Dates Type', '');

INSERT INTO dba.tbl_terms VALUES ('dateSubmitted', 'local', '', 'dateSubmitted', 'Date of submission of the resource.', '', 'RIFCSDatesType', '', 'pt', '', 'RIFCS Dates Type', '');

INSERT INTO dba.tbl_terms VALUES ('issued', 'local', '', 'issued', 'Date of formal issuance (e.g.publication) of the resource.', '', 'RIFCSDatesType', '', 'pt', '', 'RIFCS Dates Type', '');

INSERT INTO dba.tbl_terms VALUES ('valid', 'local', '', 'valid', 'Date (often a range) of validity of a resource.', '', 'RIFCSDatesType', '', 'pt', '', 'RIFCS Dates Type', '');



-- NEW TABLES

CREATE TABLE dba.tbl_dates
(
  registry_object_key character varying(512),
  id serial NOT NULL,
  date_type character varying(64),
  CONSTRAINT pk_dates PRIMARY KEY (id),
  CONSTRAINT fk_registry_object_dates FOREIGN KEY (registry_object_key)
      REFERENCES dba.tbl_registry_objects (registry_object_key) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_dates
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_dates TO dba;
GRANT ALL ON TABLE dba.tbl_dates TO webuser;


CREATE TABLE dba.tbl_date
(
  id serial NOT NULL,
  date_id integer,
  type character varying(64),
  date_format character varying(64),
  value character varying(128),
  CONSTRAINT pk_date PRIMARY KEY (id),
  CONSTRAINT fk_date_id FOREIGN KEY (date_id)
      REFERENCES dba.tbl_dates (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_date
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_date TO dba;
GRANT ALL ON TABLE dba.tbl_date TO webuser;



CREATE TABLE dba.tbl_related_info_format
(
  id serial NOT NULL,
  related_info_id bigint,
  identifier_type character varying(64),
  identifier_value character varying(512),
  CONSTRAINT pk_related_info_identifier PRIMARY KEY (id),
  CONSTRAINT fk_related_info_format FOREIGN KEY (related_info_id)
      REFERENCES dba.tbl_related_info (related_info_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE dba.tbl_related_info_format
  OWNER TO dba;
GRANT ALL ON TABLE dba.tbl_related_info_format TO dba;
GRANT ALL ON TABLE dba.tbl_related_info_format TO webuser;


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

-- dates
DELETE FROM dba.tbl_date
WHERE date_id IN (
 SELECT DS.id 
   FROM dba.tbl_dates DS
  WHERE DS.registry_object_key = _registry_object_key
);

DELETE FROM dba.tbl_dates DS
WHERE DS.registry_object_key = _registry_object_key;


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

-- relation format
DELETE FROM dba.tbl_related_info_format RD
WHERE RD.related_info_id IN (
 SELECT RDX.related_info_id
   FROM dba.tbl_related_info RDX
  WHERE RDX.registry_object_key = _registry_object_key
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
ALTER FUNCTION dba.udf_delete_registry_object(character varying)
  OWNER TO dba;


-- PERMISSIONS
GRANT ALL on dba.tbl_dates TO webuser;
GRANT ALL on dba.tbl_date TO webuser;
GRANT ALL on dba.tbl_related_info_format TO webuser;


GRANT SELECT, USAGE ON TABLE dba.tbl_date_id_seq TO webuser;
GRANT SELECT, USAGE ON TABLE dba.tbl_dates_id_seq TO webuser;
GRANT SELECT, USAGE ON TABLE dba.tbl_related_info_format_id_seq TO webuser;


