<?php
/*
Copyright 2009 The Australian National University
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
// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';

// Set the Content-Type header.
header("Content-Type: text/xml; charset=UTF-8", true);

// BEGIN: XML Response
// =============================================================================
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
print('<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">'."\n");
print('<ShortName>'.esc(eINSTANCE_TITLE_SHORT.' '.eAPP_TITLE)." Collections Registry Search</ShortName>\n");
print('<LongName>'.esc(eINSTANCE_TITLE.' '.eAPP_TITLE)." Collections Registry Search</LongName>\n");
print('<Description>Searches the '.esc(eINSTANCE_TITLE.' '.eAPP_TITLE)." Collections Registry for collection, service, party, and activity metadata</Description>\n");
print('<Contact>'.esc(eCONTACT_EMAIL)."</Contact>\n");
print("<Tags>Collection Service Party Activity</Tags>\n");
print("<SyndicationRight>open</SyndicationRight>\n");
print('<Url type="application/rss+xml" template="'.esc(eAPP_ROOT).'orca/services/OpenSearch.php?search={searchTerms}" />'."\n");
print('<Query role="example" searchTerms="Australia" />'."\n");
print('<OutputEncoding>UTF-8</OutputEncoding>');
print('<InputEncoding>UTF-8</InputEncoding>');
print("</OpenSearchDescription>\n");
// END: XML Response
// =============================================================================
require '../../_includes/finish.php';
?>

