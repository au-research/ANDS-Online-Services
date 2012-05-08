<?php
/*
Copyright 2008 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*******************************************************************************/
// DOIS environment settings.
// -----------------------------------------------------------------------------
// Service configuration.
// Service configuration.
//define('gDOIS_SERVICE_BASE_URI', "https://api.datacite.org/metadata");
//define('gDOIS_SERVICE_BASE_URI', "https://mds.datacite.org/");
//define('gDOIS_DATACITE_PASSWORD', "and3do1p455");

define('gDOIS_DATACENTRE_BASE_URI', "https://mds.datacite.org/datacentre");
define('gDOIS_DATACENTRE_USERNAME', "ANDS");
define('gDOIS_DATACENTRE_PASSWORD', "and3do1p455");

define('gDOIS_DATACENTRE_NAME_PREFIX', "ANDS");
define('gDOIS_DATACENTRE_NAME_MIDDLE', "CENTRE");

define('gDOIS_RESPONSE_SUCCESS', 'OK');

$gDOIS_PREFIX_TYPES = array( '10.4225/','10.4226/','10.4227/','10.5072/');
?>