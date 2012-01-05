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
// Page processing
// -----------------------------------------------------------------------------



// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<h2>Web Services</h2>

<!-- == OpenSearch ============================================== -->
<table class="recordTable" summary="OpenSearch">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>OpenSearch</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				An OpenSearch service (<a href="http://www.opensearch.org">www.opensearch.org</a>).
			</td>
		</tr>
		<tr>
			<td>Service Base URI:</td>
			<td><a href="<?php printSafe(eAPP_ROOT.'orca/services/OpenSearch.php') ?>"><?php printSafe(eAPP_ROOT.'orca/services/OpenSearch.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				Specified in <a href="<?php printSafe(eAPP_ROOT.'orca/services/OpenSearchDescription.php') ?>"><?php printSafe(eAPP_ROOT.'orca/services/OpenSearchDescription.php'); ?></a>
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				RSS 2.0 with OpenSearch extensions.<br />
				<a href="<?php printSafe(eAPP_ROOT.'orca/services/OpenSearch.php?search=Australia') ?>">Example query response.</a>
			</td>
		</tr>
	</tbody>
</table>

<!-- == OAI Data Provider ============================================== -->
<table class="recordTable" summary="OAI Data Provider">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>OAI Data Provider</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				An OAI Data Provider service.
			</td>
		</tr>
		<tr>
			<td>Service Base URI:</td>
			<td><a href="<?php print(getOAIBaseURL()) ?>"><?php print(getOAIBaseURL()); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET or POST.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				As per the <a href="http://www.openarchives.org/OAI/openarchivesprotocol.html">OAI-PMH Version 2.0 specification</a>.
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b><a href="http://www.openarchives.org/OAI/openarchivesprotocol.html#XMLResponse">OAI-PMH XML Response Format</a></b> <br />
				<a href="http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd</a><br />
				containing data described by<br />
				<b>Repository Interchange Format (RIF) Schema</b><br />
				<a href="<?php printSafe(gRIF_SCHEMA_URI) ?>"><?php printSafe(gRIF_SCHEMA_URI) ?></a>
			</td>
		</tr>
	</tbody>
</table>

<!-- == Get Registry Object Groups ===================================== -->
<table class="recordTable" summary="Get Registry Object Groups">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Get Registry Object Groups</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Retrieves a list of distinct registry object groups that can be provided as an object_group parameter to getRegistryObjects.
			</td>
		</tr>
		<tr>
			<td>Service URI:</td>
			<td><a href="<?php printSafe(eAPP_ROOT.'orca/services/getRegistryObjectGroups.php') ?>"><?php printSafe(eAPP_ROOT.'orca/services/getRegistryObjectGroups.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				None.
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b>ORCA Registry Object Group List Schema</b><br />
				<a href="<?php printSafe(gORCA_GROUP_LIST_SCHEMA_URI) ?>"><?php printSafe(gORCA_GROUP_LIST_SCHEMA_URI) ?></a>
			</td>
		</tr>
	</tbody>
</table>

<!-- == Get Data Sources =============================================== -->
<table class="recordTable" summary="Get Data Sources">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Get Data Sources</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Retrieves a list of data sources and useful metadata including a data source key that can be provided as a source_key parameter to getRegistryObjects.
			</td>
		</tr>
		<tr>
			<td>Service URI:</td>
			<td><a href="<?php printSafe(eAPP_ROOT.'orca/services/getDataSources.php') ?>"><?php printSafe(eAPP_ROOT.'orca/services/getDataSources.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				None.
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b>ORCA Data Source List Schema</b><br />
				<a href="<?php printSafe(gORCA_DATA_SOURCE_LIST_SCHEMA_URI) ?>"><?php printSafe(gORCA_DATA_SOURCE_LIST_SCHEMA_URI) ?></a>
			</td>
		</tr>
	</tbody>
</table>
	
<!-- == Get Registry Objects =========================================== -->
<table class="recordTable" summary="Get Registry Objects">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Get Registry Objects</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Retrieves registryObjects matching the search criteria defined by the parameter values.
			</td>
		</tr>
		<tr>
			<td>Uses:&nbsp;</td>
			<td>This service can be used to obtain records from any ORCA Registry. In the ANDS context, one possible use is for extracting records from the Sandbox environment as a first step in moving those records into production. <a href="http://services.ands.org.au/documentation/Get_Registry_Objects_Instructions.pdf">Step by step instructions for use</a></td>
		</tr>
		<tr>
			<td>Service Base URI:</td>
			<td><a href="<?php printSafe(eAPP_ROOT.'orca/services/getRegistryObjects.php') ?>"><?php printSafe(eAPP_ROOT.'orca/services/getRegistryObjects.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET with the parameters in a querystring appended to the base URI.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				<ul>
					<li><b>search</b> (string) <i>optional</i>.
						Only include objects where (ignoring case): 
						<ul>
							<li><i>Names</i> contains search phrase.</li>
							<li><i>Key</i> starts with search phrase.</li>
							<li><i>Identifiers</i>  contains search phrase.</li>
							<li><i>Subjects</i> contains search phrase.</li>
							<li><i>Descriptions</i> contains search phrase.</li>
						</ul>	
					</li>
					<li><b>source_key</b> (string) <i>optional</i>.
						<br />Only include objects where data_source_key = source_key.
					</li>
					<li><b>object_group</b> (string) <i>optional</i>.
						<br />Only include objects where group = object_group.
					</li>
					<li><b>activities</b> ('activity') <i>optional</i>.
						<br />Include activity objects.
					</li>
					<li><b>parties</b> ('party') <i>optional</i>.
						<br />Include party objects.
					</li>
					<li><b>collections</b> ('collection') <i>optional</i>.
						<br />Include collection objects.
					</li>
					<li><b>services</b> ('service') <i>optional</i>.
						<br />Include service objects.
					</li>
					<li><b>created_before_equals</b> (<?php printSafe(eDCT_FORMAT_ISO8601_DATETIMESEC_UTC) ?>) <i>optional</i>.
						<br />Only include records created before or on created_before_equals.
					</li>
					<li><b>created_after_equals</b> (<?php printSafe(eDCT_FORMAT_ISO8601_DATETIMESEC_UTC) ?>) <i>optional</i>.
						<br />Only include records created on or after created_after_equals.
					</li>
				</ul>
				Note that if none of activites=activity, collections=collection, parties=party, or services=service are included in the request, then no objects will be returned.
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b>Repository Interchange Format (RIF) Schema</b><br />
				<a href="<?php printSafe(gRIF_SCHEMA_URI) ?>"><?php printSafe(gRIF_SCHEMA_URI) ?></a>
			</td>
		</tr>
	</tbody>
</table>
	
<!-- == Get Registry Object =========================================== -->
<table class="recordTable" summary="Get Registry Object">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Get Registry Object</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Retrieves the identified registryObject.
			</td>
		</tr>
		<tr>
			<td>Service Base URI:</td>
			<td><a href="<?php printSafe(eAPP_ROOT.'orca/services/getRegistryObject.php') ?>"><?php printSafe(eAPP_ROOT.'orca/services/getRegistryObject.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET with the parameters in a querystring appended to the base URI.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td><b>key</b> (string). The registry object key.</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b>Repository Interchange Format (RIF) Schema</b><br />
				<a href="<?php printSafe(gRIF_SCHEMA_URI) ?>"><?php printSafe(gRIF_SCHEMA_URI) ?></a>
			</td>
		</tr>
	</tbody>
</table>
	
<!-- == Get Registry Objects KML =========================================== -->
<table class="recordTable" summary="Get Registry Objects KML">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Get Registry Objects KML</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Retrieves KML for registryObjects matching the search criteria defined by the parameter values and having suitably described spatial data.
			</td>
		</tr>
		<tr>
			<td>Service Base URI:</td>
			<td><a href="<?php printSafe('http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectsKML.php') ?>"><?php printSafe('http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectsKML.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET with the parameters in a querystring appended to the base URI.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				<ul>
					<li><b>search</b> (string) <i>optional</i>.
						Only include objects where (ignoring case): 
						<ul>
							<li><i>Names</i> contains search phrase.</li>
							<li><i>Key</i> starts with search phrase.</li>
							<li><i>Identifiers</i>  contains search phrase.</li>
							<li><i>Subjects</i> contains search phrase.</li>
							<li><i>Descriptions</i> contains search phrase.</li>
						</ul>	
					</li>
					<li><b>source_key</b> (string) <i>optional</i>.
						<br />Only include objects where data_source_key = source_key.
					</li>
					<li><b>object_group</b> (string) <i>optional</i>.
						<br />Only include objects where group = object_group.
					</li>
					<li><b>activities</b> ('activity') <i>optional</i>.
						<br />Include activity objects.
					</li>
					<li><b>parties</b> ('party') <i>optional</i>.
						<br />Include party objects.
					</li>
					<li><b>collections</b> ('collection') <i>optional</i>.
						<br />Include collection objects.
					</li>
					<li><b>services</b> ('service') <i>optional</i>.
						<br />Include service objects.
					</li>
					<li><b>created_before_equals</b> (<?php printSafe(eDCT_FORMAT_ISO8601_DATETIMESEC_UTC) ?>) <i>optional</i>.
						<br />Only include records created before or on created_before_equals.
					</li>
					<li><b>created_after_equals</b> (<?php printSafe(eDCT_FORMAT_ISO8601_DATETIMESEC_UTC) ?>) <i>optional</i>.
						<br />Only include records created on or after created_after_equals.
					</li>
					<li><b>limit</b> <i>optional</i>.
						<br />Only include the first limit objects containing suitably described spatial data from the result set.
					</li>
				</ul>
				Note that if none of activites=activity, collections=collection, parties=party, or services=service are included in the request, then no objects will be returned.
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b>KML 2.1</b><br />
				<a href="http://code.google.com/apis/kml/schema/kml21.xsd">http://code.google.com/apis/kml/schema/kml21.xsd</a>
			</td>
		</tr>
	</tbody>
</table>
	
<!-- == Get Registry Object KML =========================================== -->
<table class="recordTable" summary="Get Registry Object KML">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Get Registry Object KML</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Retrieves KML for the identified registryObject if it has suitably described spatial data.
			</td>
		</tr>
		<tr>
			<td>Service Base URI:</td>
			<td><a href="<?php printSafe('http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectKML.php') ?>"><?php printSafe('http://'.eHOST.'/'.eROOT_DIR.'/orca/services/getRegistryObjectKML.php'); ?></a></td>
		</tr>
		<tr>
			<td>Method:</td>
			<td>An HTTP GET with the parameters in a querystring appended to the base URI.</td>
		</tr>
		<tr>
			<td>Parameters:</td>
			<td>
				<b>key</b> (string). The registry object key.
			</td>
		</tr>
		<tr>
			<td>Response Data&nbsp;<br />XML Schema:</td>
			<td>
				<b>KML 2.1</b><br />
				<a href="http://code.google.com/apis/kml/schema/kml21.xsd">http://code.google.com/apis/kml/schema/kml21.xsd</a>
			</td>
		</tr>
	</tbody>
</table>

<!-- == Search Engine Index Seed ======================================= -->
<table class="recordTable" summary="Search Engine Index Seed">
	<thead>
		<tr>
			<td style="width: 120px;"></td>
			<td>Search Engine Index Seed</td>
		</tr>
	</thead>
	<tbody class="recordFields">
		<tr>
			<td>Description:</td>
			<td>
				Provides a visible hyperlink to the registry search for search engine crawlers 
				from which the registry contents in its entirety can be reached by following hyperlinks 
				(via the pagination of the results).
			</td>
		</tr>
		<tr>
			<td>Service URI:</td>
			<td><a href="<?php printSafe(eAPP_ROOT.'orca/search.php?source_key=&object_group=&collections=collection&services=service&parties=party&activities=activity&search=&action=Search') ?>"><?php printSafe(eAPP_ROOT.'orca/search.php?source_key=&object_group=&collections=collection&services=service&parties=party&activities=activity&search=&action=Search'); ?></a></td>
		</tr>
	</tbody>
</table>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
