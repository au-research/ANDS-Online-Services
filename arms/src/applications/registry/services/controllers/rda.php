<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APP_PATH . "services/interfaces/_GenericPortalEndpoint.php");
/**
 * RDA Endpoint (allows RDA to query the registry)
 *
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services/rda_endpoint
 *
 */

class Rda extends MX_Controller implements GenericPortalEndpoint 
{
	// Some internal defaults 
	const response_format = "application/json";
	const default_retrieval_scheme = "extrif";
	const default_retrieval_status = "PUBLISHED";


	/**
	 * Fetch a registry object from the registry by "SLUG"	
	 *
	 * Responds with a JSON array containing the data of the record's extrif
	 * (or a JSON-formatted error response, if no matching data is available)
	 *
	 * @param $_GET[slug] "SLUG" of the registry object to retrieve
	 * @param $_GET[status] A specific status to select (default is PUBLISHED)
	 */
	public function getRegistryObject()
	{
		$this->load->model('registry_object/Registry_objects', 'ro');

		// Some validation on input
		if (! $this->input->get('slug') && ! $this->input->get('registry_object_id'))
		{ 
			throw new Exception("No valid URL SLUG or registry_object_id specified.");
		}

		// Lightweight registry object get (get the latest version of the extRif for this record)
		// See registry_object/models/registry_objects for description of this method syntax
		$record = $this->ro->_get(array(
									array('args' => array(	'slug'=>$this->input->get('slug'),
															'registry_object_id'=>$this->input->get('registry_object_id'),
															'status'=>$this->input->get('status')
														),
						     		  'fn' => function($db, $args) {
									       $db->distinct()
										       ->select("record_data.data")
										       ->from("registry_objects")
										       ->join("record_data",
											      "record_data.registry_object_id = registry_objects.registry_object_id",
											      "inner")
										       ->where("record_data.scheme", Rda::default_retrieval_scheme)
										       ->where("record_data.current", "TRUE");

											if ($args['registry_object_id'])
											{
												$db->where("registry_objects.registry_object_id", $args['registry_object_id']);
											}

											if ($args['slug'])
											{
												 $db->where("slug", $args['slug'])
											 		->where("registry_objects.status",
												     		($args['status'] ? $args['status'] : Rda::default_retrieval_status));
											}

									       $db->order_by("record_data.timestamp", "desc");
									       return $db;
								       })),
							   	false, 	// return RO object
							   	1 		// limit
								);

		// We should only have one record returned
		if ($record && count($record) == 1)
		{
			echo json_encode($record[0]);
		}
		else
		{
			throw new Exception("No data could be selected for the specified URL/ID");
		}
	}




	/**
	* Fetch a list of connections from the registry
	* 
	* XXX: TODO
	* XXX: Must have limit/offset (20 per "class" of connection)
	* XXX: must deal with draft records (so need to be able to specify a specific ID)
	*
	* @param string $_GET['slug'] The SLUG of the registry object to get connections for
	*/
	public function getConnections()
	{
		$connections = array();

		// Some validation on input
		if (! $this->input->get('slug') && ! $this->input->get('registry_object_id'))
		{ 
			throw new Exception("Invalid URL SLUG or registry_object_id specified.");
		}

		// Some filter variables
		$limit = ($this->input->get('limit') ? $this->input->get('limit') : 6);
		$offset = ($this->input->get('offset') ? $this->input->get('offset') : null);
		$type_filter = ($this->input->get('type_filter') ? $this->input->get('type_filter') : null);

		// Get the RO instance for this registry object so we can fetch its connections
		$this->load->model('registry_object/registry_objects', 'ro');
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
			$published_only = TRUE;
		}
		elseif ($this->input->get('registry_object_id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('registry_object_id'));
			$published_only = FALSE;
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch connections for this registry object.");
		}

		// XXX: TODO: some logic to limit to 20 per "class of connection" and offset on request (for pagination)

		// Return this registry object's connections
		echo json_encode(array("connections"=>$registry_object->getConnections($published_only,$type_filter,$limit,$offset)));
	}




	/**
	 * Fetch a list of suggested links
	 *
	 * XXX: TODO
	 */
	public function getSuggestedLinks()
	{
		$links = array();

		// Check that we can actually support this mode of request (ands/ABS/datacite, etc)
		switch ($this->input->get('suggestor'))
		{
			case "ands":
				$suggestor = "ands_links";
			break;
			case "datacite":
				$suggestor = "datacite";
			break;
			default:
				throw new Exception("Variant of suggested links is not supported.");
		}

		// Some validation on the target registry object
		if (! $this->input->get('slug') && ! $this->input->get('registry_object_id'))
		{ 
			throw new Exception("Invalid URL SLUG or registry_object_id specified.");
		}

		// Get the RO instance for this registry object so we can fetch its suggested links
		$this->load->model('registry_object/registry_objects', 'ro');
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}
		elseif ($this->input->get('registry_object_id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('registry_object_id'));
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch suggested links for this registry object.");
		}

		// XXX: TODO: LIMIT and offset (pass to getSuggestedLinks...)

		$links = $registry_object->getSuggestedLinks($suggestor);

		echo json_encode(array("links"=>$links));
	}



	/**
	 * Return a list of Spotlight Partners along with their brief description and location (URL)
	 */
	public function getSpotlightPartners()
	{
		$partners = array();

		$this->load->helper('file');
		$file = read_file('./applications/registry/spotlight/assets/spotlight.json');
		$file = json_decode($file, true);
		foreach($file['items'] as $partner){
			$partners[] = array(
				'title'=>$partner['title'],
				'description'=>$partner['content'],
				'img_url'=>$partner['img_url'],
				'url'=>$partner['url']
			);
		}

		// services_spotlight_partners_data_source
		echo json_encode(array("partners"=>$partners));
	}



	/**
	 * Return an array of contributors to the registry
	 * (returns unique "group" names, with published collections)
	 *
	 * XXX: TODO: Merge this group list with a list of contributor pages?
	 */
	public function getWhoContributes()
	{
		$contributors = array(); 

		// Get an array of groups in the registry using SOLR facets
		$this->load->library('solr');
		$this->solr->setOpt('rows',0);
		$this->solr->setOpt('q',''); // unset the default query XXX: REMOVE (THIS IS JUST FOR TESTING WITH NO PUBLISHED RECORDS!)
		$this->solr->addQueryCondition('+class:"collection"');
		$this->solr->setOpt('fl','');
		$this->solr->setFacetOpt('field', 'group');
		$this->solr->setFacetOpt('limit', '200');
		$this->solr->setFacetOpt('mincount', '1'); // at least one published collection (else don't return it)
		$result = $this->solr->getFacetResult('group');

		foreach($result AS $title => $count)
		{
			$contributors[] = array(
				'title' => $title,
				'type' => 'group', // XXX: maybe have this "contributor_page" for contributors???
				'collection_count' => $count
			);
		}

		echo json_encode(array("contributors"=>$contributors));
	}


	public function getConnectionGraph()
	{
		$this->load->model('connectiontree');
		$this->load->model('registry_object/registry_objects','ro');

		// Depth away from the current registry object (toward the branches)
		$depth = (int) ($this->input->get('depth') ?: 2);

		$published_only = $this->input->get('published_only') ?: TRUE;

		if ($this->input->get('key'))
			{
				$matching_regobjs = $this->ro->getByKey((string)$this->input->get('key'));
				if (is_array($matching_regobjs))
				{
					$root_registry_object = array_pop($matching_regobjs);
					echo json_encode(array("status"=>"success", "nodeid"=>$this->input->get('nodeid'),
										"tree"=>$this->connectiontree->get($root_registry_object, $depth,$published_only)));
				}
				else
				{
					echo json_encode(array("status"=>"fail", "tree"=>null));
				}

			}
			else
			{
				echo json_encode(array("status"=>"fail", "tree"=>null));
			}
		}



	/* Setup this controller to handle the expected response format */
	public function __construct()
    {
    	parent::__construct();

    	// JSON output at all times?
    	$this->output->set_content_type(rda::response_format);

    	// Set our exception handler to function in JSON mode
    	set_exception_handler('json_exception_handler');
    }
}