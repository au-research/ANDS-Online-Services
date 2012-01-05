\c harvester;

CREATE TABLE provider
(
   provider_id           SERIAL PRIMARY KEY,
   source_url            VARCHAR(256)
);

CREATE INDEX source_url_idx ON provider(source_url);

CREATE TABLE harvest
(
   harvest_id       VARCHAR(256) PRIMARY KEY,
   provider_id      INTEGER REFERENCES provider(provider_id),
   response_url     VARCHAR(256),
   method           VARCHAR(32),
   mode             VARCHAR(32),
   date_started     TIMESTAMP,
   date_completed   TIMESTAMP,
   date_from        VARCHAR(20),
   date_until       VARCHAR(20),
   set              VARCHAR(128),
   resumption_token VARCHAR(128),
   metadata_prefix  VARCHAR(32),
   status           INTEGER
);

-- additional parameters which may be required for custom harvests 
-- for example, values for passing to stylesheets or other information
-- not core to a harvest but required for custom processing.
CREATE TABLE harvest_parameter
(
    harvest_id  VARCHAR(256) REFERENCES harvest(harvest_id),
    name        VARCHAR(64),
    value       VARCHAR(256),
    PRIMARY KEY(harvest_id, name)    
);

CREATE TABLE request
(
    request_id SERIAL PRIMARY KEY,
    request    VARCHAR(24)
);
INSERT INTO request VALUES(DEFAULT, 'Identify');
INSERT INTO request VALUES(DEFAULT, 'ListSets');
INSERT INTO request VALUES(DEFAULT, 'ListMetadataFormats');
INSERT INTO request VALUES(DEFAULT, 'ListRecords');
INSERT INTO request VALUES(DEFAULT, 'ListIdentifiers');
INSERT INTO request VALUES(DEFAULT, 'GetRecord');

CREATE TABLE fragment
(
    fragment_id    SERIAL PRIMARY KEY,
    harvest_id     VARCHAR(256) REFERENCES harvest(harvest_id),
    request_id     INTEGER REFERENCES request,
    date_stored    TIMESTAMP,
    text           TEXT
);

CREATE TABLE schedule
(
    harvest_id     VARCHAR(256) PRIMARY KEY REFERENCES harvest(harvest_id),
    last_run       TIMESTAMP,
    next_run       TIMESTAMP,
    frequency      VARCHAR(16)
);

GRANT SELECT ON TABLE provider, harvest, harvest_parameter, request, fragment, schedule TO harvester;
GRANT INSERT ON TABLE provider, harvest, harvest_parameter, request, fragment, schedule TO harvester;
GRANT UPDATE ON TABLE provider, harvest, harvest_parameter, request, fragment, schedule TO harvester;
GRANT DELETE ON TABLE provider, harvest, harvest_parameter, request, fragment, schedule TO harvester;
grant usage on fragment_fragment_id_seq to harvester;
grant usage on provider_provider_id_seq to harvester;
grant usage on request_request_id_seq to harvester;