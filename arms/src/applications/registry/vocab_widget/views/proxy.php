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
if (isset($solr_base) && !empty($solr_base)) {
    define("SOLR_URL", $solr_base . "select?wt=phps&rows=0&q=subject_vocab_uri%%3A(%%22%s%%22)+%s+%s");
}
else {
    define("SOLR_URL", "http://ands3.anu.edu.au:8080/solr1/collection1/select?wt=phps&rows=0&q=subject_vocab_uri%%3A(%%22%s%%22)+%s+%s");
}
if (isset($sissvoc_base) && !empty($sissvoc_base)) {
    define("BASE_URL", $sissvoc_base);
}
else {
    define("BASE_URL", "http://ands3.anu.edu.au:8080/sissvoc/api/");
}

define("SEARCH_URL", "/concept.json?anycontains=");
define("NARROW_URL", "/concept/narrower.json?uri=");
define("ALLNARROW_URL", "/concept/allNarrower.json?uri=");
define("BROAD_URL", "/concept/broader.json?uri="); #future use
define("TOP_URL", "/topConcepts.json");
define("MAX_RESULTS", 200); #sisvoc only returns 200 items

class VocabProxy
{

	/*
	 * @var actions this proxy can execute; processor callbacks are
	 * initialised by the constructor
	 */
	private $valid_actions = array(
		"search" => array(
			'url' => SEARCH_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"allnarrow" => array(
			'url' => ALLNARROW_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"narrow" => array(
			'url' => NARROW_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false),
		"top" => array(
			'url' => TOP_URL,
			'queryprocessor' => false,
			'itemprocessor' => false,
			'sortprocessor' => false));

	//what we send back
	private $jsonData = array('status' => 'ERROR',
				  'message' => 'action must be defined');

	// Some defaults
	private $limit = 100; #a mildly sane limit
	private $callback = "function";
	private $action = false;
	private $lookfor = false;
	private $repository = false;
	private $debug = false;

	/**
	 * Proxy is an atomic object: it gets instantiated, runs, and prints
	 * output all in one fell swoop.
	 */
	public function __construct() {
		// Setup HTTP headers so jQuery/browser interprets as JSON
		header('Cache-Control: private, must-revalidate');
		header('Content-type: application/json');

		/**
		 * Set up the query processors:
		 *  - for 'search', we want to urlencode the query.
		 *  - for 'narrow', we have a URI, so we don't want to mess with that
		 *  - for 'top', we don't need (or want) a query: silently drop it
		 */
		$this->valid_actions['search']['queryprocessor'] = function($e) { return urlencode($e); };
		$this->valid_actions['top']['queryprocessor'] = function($e) { return ""; };

		/**
		 * Set up sort processors:
		 *  - for 'top', 'narrow', and 'allnarrow' we want terms sorted by notation, or label
		 */
		foreach (array('top', 'allnarrow') as $action) {
		    $this->valid_actions[$action]['sortprocessor'] = function($e1, $e2) {
			if (array_key_exists('notation', $e1))
			{
			    $l1 = (int)$e1['notation'];
			    $l2 = (int)$e2['notation'];
			    return $l1 <= $l2 ? -1 : 1;
			}
			else
			{
			    return;
			}
		    };
		}

		/**
		 * Set up item processors; this is used to inject solr term counts based on the
		 * vocab's 'about' URL. Note: this mightn't be the best way to go... awfully hard coded
		 */
		foreach($this->valid_actions as &$action)
		{
			$action['itemprocessor'] = function($items) {
				return array_map(function($e)  {
						if (array_key_exists('about', $e) &&
						    substr($e['about'], 0, 7) === 'http://')
						{
							$count_url = sprintf(SOLR_URL,$e['about']);
							$solr_response = unserialize(file_get_contents($count_url));
							try
							{
								$e['count'] = $solr_response['response']['numFound'];
							}
							catch (Exception $e)
							{
								$e['count'] = 0;
							}
						}
						else
						{
							$e['count'] = 0;
						}
						return $e;
					},
					$items);
			};
		}



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

		if ($this->debug)
		{
			$this->jsonData['message'] .= " [$url]";
		}

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
			$items = (array)$data['result']['items'];

			if (is_callable($this->valid_actions[$this->action]['sortprocessor']))
			{
				usort($items,
				      $this->valid_actions[$this->action]['sortprocessor']);
			}

			$this->jsonData['items'] = array_map(function($i) {
					if (is_string($i)) {
						return false;
					}
					$i['label'] = $i['prefLabel']['_value'];

					$i['about'] = $i['_about'];
					if (array_key_exists('broader', $i) &&
					    is_array($i['broader']))

					{
					    $i['broader'] = $i['broader']['_about'];
					}


					if (!array_key_exists('narrower', $i))
					{
					    $i['narrower'] = false;
					}
					else
					{
					    $i['narrower'] = (array)$i['narrower'];
					}

					unset($i['_about'],
					      $i['prefLabel']);
					return $i;
				},
				array_slice($items,
					    0,
					    $this->limit));
			$this->jsonData['items'] = array_values(array_filter($this->jsonData['items'],
									     function($e) {
										     return $e !== false;
									     }));
			if (is_callable($this->valid_actions[$this->action]['itemprocessor']))
			{
				$this->jsonData['items'] = call_user_func($this->valid_actions[$this->action]['itemprocessor'],
									  $this->jsonData['items']);
			}
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
		$this->debug = isset($_REQUEST['debug']);

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
					implode(", ", array_keys($this->valid_actions));
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
