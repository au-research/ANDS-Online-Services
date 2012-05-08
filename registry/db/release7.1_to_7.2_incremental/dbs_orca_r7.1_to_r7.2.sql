ALTER TABLE dba.tbl_registry_objects ADD COLUMN key_hash character varying(255) DEFAULT ''::character varying;
ALTER TABLE dba.tbl_data_sources ADD COLUMN key_hash character varying(255) DEFAULT ''::character varying;

ALTER TABLE dba.tbl_registry_objects ADD COLUMN registry_date_modified bigint;
UPDATE dba.tbl_registry_objects AS oldValue 
SET registry_date_modified = (
    SELECT CAST(extract(epoch FROM created_when) AS bigint) 
    FROM dba.tbl_registry_objects AS newValue 
    WHERE oldValue.registry_object_key= newValue.registry_object_key
);
