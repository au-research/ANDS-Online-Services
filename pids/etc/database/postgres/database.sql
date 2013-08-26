\c pids;

CREATE TABLE nas
(
    na       BYTEA PRIMARY KEY
);

CREATE TABLE handles
(
    handle      BYTEA NOT NULL,
    idx         INT4 NOT NULL,
    type        BYTEA,
    data        BYTEA,
    ttl_type    INT2,
    ttl         INT4,
    timestamp   INT4,
    refs        TEXT,
    admin_read  BOOLEAN,
    admin_write BOOLEAN,
    pub_read    BOOLEAN,
    pub_write   BOOLEAN,
    PRIMARY KEY(handle, idx)
);
  
CREATE INDEX handles_data_idx ON handles(data);
CREATE INDEX handles_handle_idx on handles(handle);

CREATE TABLE trusted_client
(
    ip_address    VARCHAR(15),
    app_id        VARCHAR(40),
    description   VARCHAR(256)
);

CREATE INDEX trusted_client_ip_address_idx ON trusted_client(ip_address);


-- TO DO: set up admin and resolve users? Maybe don't need this. If do this
-- must make resolver a separate webapp.
-- GRANT ALL ON nas, handle TO handle;  
-- GRANT ALL ON nas, handle TO resolve;

-- sequence for handle suffix up to 2^63 values
CREATE SEQUENCE handlesuffix_seq;


GRANT SELECT ON TABLE handles, nas, trusted_client TO pidmaster;
GRANT INSERT ON TABLE handles, nas, trusted_client TO pidmaster;
GRANT UPDATE ON TABLE handles, nas, trusted_client TO pidmaster;
GRANT DELETE ON TABLE handles, nas, trusted_client TO pidmaster;
GRANT USAGE ON SEQUENCE handlesuffix_seq TO pidmaster;

-- R 10.2 release changes
ALTER TABLE trusted_client ADD COLUMN created_when timestamp with time zone DEFAULT now();

CREATE OR REPLACE VIEW search_view AS 
SELECT encode(handles.handle, 'escape'::text) AS handle, encode(handles.data, 'escape'::text) AS data
FROM handles
WHERE handles.type = 'DESC'::bytea OR handles.type = 'URL'::bytea;

ALTER TABLE search_view
  OWNER TO pidmaster;

CREATE OR REPLACE RULE "_RETURN" AS
ON SELECT TO search_view DO INSTEAD  SELECT encode(handles.handle, 'escape'::text) AS handle, encode(handles.data, 'escape'::text) AS data
FROM handles
WHERE handles.type = 'DESC'::bytea OR handles.type = 'URL'::bytea;