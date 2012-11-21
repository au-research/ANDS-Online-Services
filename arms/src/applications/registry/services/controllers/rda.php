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
	 * @param $_GET[slug] "SLUG" of the registry object to retrieve
	 * @param $_GET[status] A specific status to select (default is PUBLISHED)
	 */
	public function getRegistryObject()
	{
		$this->load->model('registry_object/Registry_objects', 'ro');

		// Some validation on input
		if (! $this->input->get('slug'))
		{ 
			throw new Exception("Invalid URL SLUG specified.");
		}

		// Lightweight registry object get (get the latest version of the extRif for this record)
		// See registry_object/models/registry_objects for description of this method syntax
		$record = $this->ro->_get(array(
									array('args' => array(	'slug'=>$this->input->get('slug'),
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
										       ->where("record_data.current", "TRUE")
										       ->where("slug", $args['slug'])
											   ->where("registry_objects.status",
												     		($args['status'] ? $args['status'] : Rda::default_retrieval_status));

									       $db->order_by("record_data.timestamp", "desc");
									       return $db;
								       })),
							   	false, 	// return RO object
							   	1 		// limit
								);
		if ($record && count($record) == 1)
		{
			echo json_encode($record[0]);
		}
		else
		{
			throw new Exception("No data could be selected for the specified URL/status");
		}
	}

	/**
	* Fetch a list of connections from the registry
	* 
	* @param string 
	*/
	public function getConnections()
	{

	}

	public function getSuggestedLinks()
	{

	}


	public function getSpotlightPartners()
	{

	}

	public function getWhoContributes()
	{

	}

	public function __construct()
    {
    	parent::__construct();

    	// JSON output at all times?
    	$this->output->set_content_type(rda::response_format);

    	// Set our exception handler to function in JSON mode
    	set_exception_handler('json_exception_handler');
    }
}