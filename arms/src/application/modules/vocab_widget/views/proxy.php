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
 * ANDS SISSVOC Vocab Widget - search service v0.1
 *
 * JSONP service
 *
 *
 * Requires cURL PHP extensions. PHP5.2+, PHP5.4 recommended
 */

// Setup HTTP headers so jQuery/browser interprets as JSON
header('Cache-Control: private, must-revalidate');
header('Content-type: application/json');

// some constants
define("BASE_URL", "http://devl.ands.org.au:8080/sissvoc/api/anzsrc-for");
define("SEARCH_URL", BASE_URL . "/concept.json");
define("NARROW_URL", BASE_URL . "/concept/allNarrower.json"); #future use
define("BROAD_URL", BASE_URL . "/concept/allBroader.json"); #future use
define("MAX_RESULTS", 200); #sisvoc only returns 200 items

$valid_actions = array("search"); #future use: narrow, broaden, list(?)

// Some defaults
$jsonData['status'] = 'ERROR';
$jsonData['message'] = 'action must be defined';
$limit = 100; #a mildly sane limit
$callback = "function";
$action = false;
$lookfor = false;
$url = false;
$data = false;

// Parse parameters
if (isset($_REQUEST['action'])) {
	if (in_array($_REQUEST['action'], $valid_actions)) {
		$action = $_REQUEST['action'];
		$jsonData['message'] = 'action: ' . $action;
	}
	else {
		$jsonData['message'] .= " and valid: one of " .
			implode(", ", $valid_actions);
	}
}

if ($action) {
	if (isset($_REQUEST['lookfor'])) {
		$lookfor = rawurlencode($_REQUEST['lookfor']);
		$jsonData['message'] .= " (" . $lookfor . ")";
	}

	if (isset($_REQUEST['limit'])) {
		if (is_numeric($_REQUEST['limit'])) {
			$limit = $_REQUEST['limit'];
			if ($limit > MAX_RESULTS) {
				$jsonData['warning'] = "only retrieving first " .
					MAX_RESULTS . " matches";
				$limit = MAX_RESULTS;
			}
		}
		else {
			$jsonData['warning'] = "limit must be numeric: " .
				"falling back to default limit";
		}
		$jsonData['limit'] = $limit;
	}

	if (isset($_REQUEST['callback'])) {
		$callback = $_REQUEST['callback'];
	}
}


if ($action === "search" && $lookfor !== false) {
	$url = SEARCH_URL . "?anycontains=" . urlencode($lookfor);
}

// Send the query (curl)
if ($url) {
	$ch = curl_init() or die(curl_error());
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
	$data = json_decode(curl_exec($ch), true) or die(curl_error());
	if ($data != null) {
		$jsonData['status'] = 'OK';
	}
}

// Parse the response: strip out some crud, use sensible labels
if ($data && $jsonData['status'] == "OK") {
	$jsonData['items'] = array_map(function($i) {
			$i['label'] = $i['prefLabel']['_value'];
			$i['about'] = $i['_about'];
			unset($i['_about'],
			      $i['broader'],
			      $i['prefLabel']);
			return $i;
		},
		array_slice($data['result']['items'],
			    0,
			    $limit));
	$jsonData['count'] = count($jsonData['items']);
}

// Send the response as JSONP: if we have PHP 5.4, we can unescape
// slashes. Otherwise, str_replace to the... er... rescue?
$jsonData = (defined(PHP_VERSION_ID) && PHP_VERSION_ID >= 50400) ?
	json_encode($jsonData, JSON_UNESCAPED_SLASHES) :
	str_replace('\/','/', json_encode($jsonData));
echo $callback . "(" . $jsonData . ");";
?>