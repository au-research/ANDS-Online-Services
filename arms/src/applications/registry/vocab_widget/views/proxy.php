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

// some constants
define("BASE_URL", "http://devl.ands.org.au:8080/sissvoc/api/");
define("SEARCH_URL", "/concept.json?anycontains=");
define("NARROW_URL", "/concept/allNarrower.json?uri=");
define("BROAD_URL", "/concept/allBroader.json?uri="); #future use
define("MAX_RESULTS", 200); #sisvoc only returns 200 items

class VocabProxy
{

	//actions this proxy can execute
	private $valid_actions = array(
		"search" => array(
			'url' => SEARCH_URL,
			'queryprocessor' => false),
		"narrow" => array(
			'url' => NARROW_URL,
			'queryprocessor' => false));

	//what we send back
	private $jsonData = array('status' => 'ERROR',
				  'message' => 'action must be defined');

	// Some defaults
	private $repository = "anzsrc-for";
	private $limit = 100; #a mildly sane limit
	private $callback = "function";
	private $action = false;
	private $lookfor = false;

	/**
	 * Proxy is an atomic object: it gets instantiated, runs, and prints
	 * output all in one fell swoop.
	 */
	public function __construct() {
		// Setup HTTP headers so jQuery/browser interprets as JSON
		header('Cache-Control: private, must-revalidate');
		header('Content-type: application/json');

		//configure the action query processor callables; this can't be done up-front
		$valid_actions['search']['queryprocessor'] = function($e) { return urlencode($e); };

		if ($this->setup()) {
			$this->handle();
		}
		$this->display();
	}

	/**
	 * Handle the request by querying the action url
	 * with specified parameters
	 */
	private function handle() {
		$data = false;
		$url = $this->urlFor($this->action);

		if ($url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
			$data = json_decode(curl_exec($ch), true) or die(curl_error($ch));
			if ($data != null) {
				$this->jsonData['status'] = 'OK';
			}
		}
		if ($data && $this->jsonData['status'] == "OK") {
			$this->jsonData['items'] = array_map(function($i) {
					$i['label'] = $i['prefLabel']['_value'];
					$i['about'] = $i['_about'];
					unset($i['_about'],
					      $i['broader'],
					      $i['prefLabel']);
					return $i;
				},
				array_slice($data['result']['items'],
					    0,
					    $this->limit));
			$this->jsonData['count'] = count($this->jsonData['items']);
		}
	}

	/**
	 * Is the supplied action valid?
	 * @param the action
	 * @return true if valid, false otherwise
	 */
	private function isValidAction($action) {
		return array_key_exists($action, $this->valid_actions);
	}

	/**
	 * Get the endpoint url for the specified action
	 * @param the action
	 * @return the URL to query, or false if the action isn't valid
	 */
	private function urlFor($action) {
		if ($this->isValidAction($action)) {
			$validAction = $this->valid_actions[$action];
			$processor = $validAction['queryprocessor'];
			$querystring = is_callable($processor) ?
				call_user_func($processor, $this->lookfor) :
				$this->lookfor;

			return sprintf("%s%s%s%s",
				       BASE_URL,
				       $this->repository,
				       $validAction['url'],
				       $querystring);
		}
		else {
			return false;
		}
	}

	/**
	 * A "kitchen sink" setup routine:
	 *  - parses $_REQUEST params for expected data:
	 *     - repsitory
	 *     - action
	 *     - lookfor
	 *     - limit
	 *     - callback
	 *  - initialises internal variables based on request data
	 *  - sets message response accordingly
	 * @return true if preconditions met, false otherwise
	 */
	private function setup() {
		if (isset($_REQUEST['repository'])) {
			//strip leading "../", and normalise "/"
			$this->repository = preg_replace(
				"/\/+/",
				"/",
				preg_replace(
					"/^(\.\.\/)+/",
					"",
					$_REQUEST['repository']));
		}
		if (isset($_REQUEST['action'])) {
			if ($this->isValidAction($_REQUEST['action'])) {
				$this->action = $_REQUEST['action'];
				$this->jsonData['message'] = 'action: ' .
					$this->action;
			}
			else {
				$this->jsonData['message'] .= " and valid: " .
					"one of " .
					implode(", ", $this->valid_actions);
			}
		}

		if ($this->action) {
			if (isset($_REQUEST['lookfor'])) {
				$this->lookfor = rawurlencode($_REQUEST['lookfor']);
				$this->jsonData['message'] .= " (" . $_REQUEST['lookfor'] . ")";
			}

			if (isset($_REQUEST['limit'])) {
				if (is_numeric($_REQUEST['limit'])) {
					$this->limit = $_REQUEST['limit'];
					if ($this->limit > MAX_RESULTS) {
						$this->jsonData['warning'] = "only retrieving first " .
							MAX_RESULTS . " matches";
						$this->limit = MAX_RESULTS;
					}
				}
				else {
					$this->jsonData['warning'] = "limit must be numeric: " .
						"falling back to default limit of " . $this->limit;
				}
				$this->jsonData['limit'] = $this->limit;
			}

			if (isset($_REQUEST['callback'])) {
				$this->callback = $_REQUEST['callback'];
			}
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Dump a whole bunch of JSON to STDOUT
	 */
	private function display() {
		$this->jsonData = (defined(PHP_VERSION_ID) && PHP_VERSION_ID >= 50400) ?
			json_encode($this->jsonData, JSON_UNESCAPED_SLASHES) :
			str_replace('\/','/', json_encode($this->jsonData));
		echo $this->callback . "(" . $this->jsonData . ");";
	}
}

$proxy = new VocabProxy();
?>
