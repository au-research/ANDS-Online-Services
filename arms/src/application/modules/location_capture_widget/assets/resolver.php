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
/*
 * ANDS Location Capture Widget - resolution service v0.1
 * 
 * Code Example ONLY - JSONP service
 * 
 * This code snippet emulates the behaviour of the ANDS
 * location resolution service powering the Location Capture
 * Widget. It will resolve place names against the Gazetteer 
 * (including partial matches) and return them in a JSON array
 * for use with the map widget.
 * 
 * Please DO NOT use this resolver service for general location 
 * resolution services - you should be using the Gazetteer directly.
 * Any unintended usage will result in your application being blocked. 
 * 
 * Requires libxml and cURL PHP extensions (and PHP5+)
 */
 
// Setup HTTP headers so jQuery/browser interprets as JSON
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

// Some defaults
$jsonData['status'] = 'ERROR';
$jsonData['message'] = 'searchText must be defined';
$searchText = '';
$limit = 500;
$recCount = 0;
$feature = '';
$callback = "function";

// Parse parameters
if (isset($_GET['searchText'])) {
	$searchText = $_GET['searchText'];
	$jsonData['message'] = 'searchText' . $searchText;
}
// jQuery will interpolate the callback function as a random integer
if (isset($_GET['callback'])) {
	$callback = $_GET['callback'];
}
if (isset($_GET['limit'])) {
	$limit = $_GET['limit'];
	$jsonData['limit'] = $limit;
}
if (isset($_GET['feature'])) {
	$feature = $_GET['feature'];
	$jsonData['feature'] = $feature;
}


// Design the XML query (Gazetteer uses bizarre XML fragments in the URL request)
if ($searchText) {
	$mctGazetteerGeocoderUrl = 'http://gazetteer.mymaps.gov.au/geoserver/wfs?service=wfs&version=1.1.0&request=GetFeature&typename=iso19112:SI_LocationInstance&maxFeatures=5000&filter=';
	$filterText = '<ogc:Filter xmlns:ogc="http://www.opengis.net/ogc"><ogc:PropertyIsLike wildCard="*" singleChar="#" escapeChar="\\"><ogc:PropertyName>iso19112:alternativeGeographicIdentifiers/iso19112:alternativeGeographicIdentifier/iso19112:name</ogc:PropertyName><ogc:Literal>' . $searchText . '</ogc:Literal></ogc:PropertyIsLike></ogc:Filter>';
}
if ($feature) {
	$mctGazetteerGeocoderUrl = 'http://gazetteer.mymaps.gov.au/geoserver/wfs?service=wfs&version=1.1.0&request=GetFeature&typename=iso19112:SI_LocationType&filter=';
	$filterText = '<ogc:Filter xmlns:ogc="http://www.opengis.net/ogc"><ogc:PropertyIsLike wildCard="%" singleChar="#" escapeChar="\\"><ogc:PropertyName>@gml:id</ogc:PropertyName><ogc:Literal>' . $feature . '%</ogc:Literal></ogc:PropertyIsLike></ogc:Filter>';
}

// Send the query (curl)
$jsonData = array();
$jsonData['status'] = 'OK';
$ch = curl_init() or die(curl_error());
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL, $mctGazetteerGeocoderUrl . rawurlencode($filterText));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch) or die(curl_error());

// Parse the response 
$gazetteerDoc = new DOMDocument();
$gazetteerDoc -> loadXML($data);
$gXPath = new DOMXpath($gazetteerDoc);

if ($searchText) {
	// Resolve and order the results (we only want a few of the feature types to avoid a massive list)
	$featureMemberListTOP = $gXPath -> evaluate('gml:featureMember[descendant::node()[contains(@xlink:href,"STAT")] or descendant::node()[contains(@xlink:href,"SUB")] or descendant::node()[contains(@xlink:href,"URBN")]]');
	$featureMemberListBOTTOM = $gXPath -> evaluate('gml:featureMember[not(descendant::node()[contains(@xlink:href,"STAT")] or descendant::node()[contains(@xlink:href,"SUB")] or descendant::node()[contains(@xlink:href,"URBN")])]');
	$jsonData['items_count'] = ($featureMemberListTOP -> length) + ($featureMemberListBOTTOM -> length);
	$items = array();
	for ($i = 0; $i < $featureMemberListTOP -> length; $i++) {
		$item = array();
		$featureMember = $featureMemberListTOP -> item($i);
		$item['title'] = $gXPath -> evaluate('.//iso19112:name', $featureMember) -> item(0) -> nodeValue;
		$coordsStr = $gXPath -> evaluate('.//gml:pos', $featureMember) -> item(0) -> nodeValue;
		$spPos = strpos($coordsStr, ' ');
		$item['coords'] = $coordsStr;
		$item['lat'] = substr($coordsStr, 0, $spPos);
		$item['lng'] = substr($coordsStr, $spPos + 1);
		$typeArray = array();
		$featureTypes = $gXPath -> evaluate('.//@xlink:href', $featureMember);
		for ($j = 0; $j < $featureTypes -> length - 1; $j++) {
			$attrvalue = $featureTypes -> item($j) -> nodeValue;
			$trimPos = strpos($attrvalue, ':AUSOSP:') + 8;
			array_push($typeArray, substr($attrvalue, $trimPos));
		}
		if ($featureTypes -> length > 0) {
			$item['types'] = $typeArray;
		}
		array_push($items, $item);
		if (++$recCount >= $limit)
			break;
	}

	for ($i = 0; $i < $featureMemberListBOTTOM -> length; $i++) {
		if (++$recCount >= $limit)
			break;
		$item = array();
		$featureMember = $featureMemberListBOTTOM -> item($i);
		$item['title'] = $gXPath -> evaluate('.//iso19112:name', $featureMember) -> item(0) -> nodeValue;
		$coordsStr = $gXPath -> evaluate('.//gml:pos', $featureMember) -> item(0) -> nodeValue;
		$spPos = strpos($coordsStr, ' ');
		$item['coords'] = $coordsStr;
		$item['lat'] = substr($coordsStr, 0, $spPos);
		$item['lng'] = substr($coordsStr, $spPos + 1);
		$typeArray = array();
		$featureTypes = $gXPath -> evaluate('.//@xlink:href', $featureMember);
		for ($j = 0; $j < $featureTypes -> length - 1; $j++) {
			$attrvalue = $featureTypes -> item($j) -> nodeValue;
			$trimPos = strpos($attrvalue, ':AUSOSP:') + 8;
			array_push($typeArray, substr($attrvalue, $trimPos));
		}
		if ($featureTypes -> length > 0) {
			$item['types'] = $typeArray;
		}
		array_push($items, $item);
	}

	$jsonData['items'] = $items;

}

// Resolve the feature type
if ($feature) {
	$featureMemberList = $gXPath -> evaluate('gml:featureMember');
	$jsonData['items_count'] = $featureMemberList -> length;
	$items = array();
	for ($i = 0; $i < $featureMemberList -> length; $i++) {
		$item = array();
		$featureMember = $featureMemberList -> item($i);
		$item['title'] = $gXPath -> evaluate('.//iso19112:name', $featureMember) -> item(0) -> nodeValue;
		$item['id'] = $gXPath -> evaluate('./iso19112:SI_LocationType/@gml:id', $featureMember) -> item(0) -> nodeValue;
		array_push($items, $item);
	}
	$jsonData['items'] = $items;
}

// Send the response as JSONP
$jsonData = json_encode($jsonData);
echo $callback . "(" . $jsonData . ");";
?>