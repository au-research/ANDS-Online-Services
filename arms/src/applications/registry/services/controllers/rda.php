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
	const default_retrieval_status = PUBLISHED;


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
										       ->select("record_data.data, registry_objects.key, registry_objects.registry_object_id")
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
		if ($record && count($record) > 0)
		{
			// Contributor pages logic (constants in engine/config/)
			$this->load->model('data_source/data_sources', 'ds');
			//we have a reord, get the object to get the object's group
			$theObject = $this->ro->getByID($record[0]['registry_object_id']);
			//see if this group has a contributor page
			$contributor = $this->db->get_where('institutional_pages',array('group' => $theObject->getAttribute('group')));
			if ($contributor->num_rows() >0)
			{				
				//if there is a contributor page see if the key of the page is this one (to cater for when a draft and published contibutor page exists)
				$contributorRecord = array_pop($contributor->result_array());
				$theContributor = $this->ro->getByID($contributorRecord['registry_object_id']);
				if($theContributor && $theContributor->getAttribute('key')==$record[0]['key'])
				{
					$record[0]['template'] = CONTRIBUTOR_PAGE_TEMPLATE;
				}
				
			}

			echo json_encode($record[0]);
			return;
		}
		else
		{

			if ($this->input->get('slug'))
			{

				// Check for redirects from old slugs
				$query = $this->db->query("SELECT * FROM url_mappings u JOIN registry_objects r ON r.registry_object_id = u.registry_object_id WHERE u.slug = ?", $this->input->get('slug'));
				
				if ($query->num_rows() > 0)
				{
					$orphan_slug = array_pop($query->result_array());
					if ($orphan_slug['slug'] == $this->input->get('slug'))
					{
						throw new Exception("Error: Unable to fetch extRif, despite active SLUG mapping.");
					}
					
					$contents = array('redirect_registry_object_slug' => $orphan_slug['slug']);
					echo json_encode($contents);
					return;
				}

				// Check for orphans! (SLUGS whose registry_object has been deleted)
				$query = $this->db->select('search_title')->get_where('url_mappings',
											array("slug"=> $this->input->get('slug'), "registry_object_id IS NULL" => null));
				
				if ($query->num_rows() > 0)
				{
					$orphan_slug = array_pop($query->result_array());
					$contents = array('previously_valid_title' => $orphan_slug['search_title']);
					echo json_encode($contents);
					return;
				}
			}
			$contents = array('message'=>'404');
			echo json_encode($contents);
			return;
			//throw new Exception("No data could be selected for the specified URL/ID");
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
		$limit = ($this->input->get('limit') ? $this->input->get('limit') : 5);
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

		$connections = $registry_object->getConnections($published_only,$type_filter,$limit,$offset);

		// XXX: TODO: some logic to limit to 20 per "class of connection" and offset on request (for pagination)

		// Return this registry object's connections
		echo json_encode(array("connections"=>$connections, 'class'=>$registry_object->class));
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
			case "ands_identifiers":
				$suggestor = "ands_identifiers";
			break;
			case "ands_subjects":
				$suggestor = "ands_subjects";
			break;
			case "datacite":
				$suggestor = "datacite";
			break;
			default:
				throw new Exception("Variant of suggested links is not supported.");
		}

		// Some validation on the target registry object
		if (! $this->input->get('slug') && !$this->input->get('id'))
		{ 
			throw new Exception("Invalid URL SLUG or registry_object_id specified.");
		}

		// Get the RO instance for this registry object so we can fetch its suggested links
		$this->load->model('registry_object/registry_objects', 'ro');
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}
		elseif ($this->input->get('id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('id'));
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch suggested links for this registry object.");
		}

		$links = $registry_object->getSuggestedLinks($suggestor,$this->input->get('start'),$this->input->get('rows'));

		echo json_encode(array("links"=>$links));
	}

	/**
	 * Fetch a list of registry contents by group
	 *
	 * XXX: TODO
	 */
	public function getContributorData()
	{
		$contents = array();

		// Get the RO instance for this registry object so we can fetch its contributor datat
		$this->load->model('registry_object/registry_objects', 'ro');
		
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}
		elseif ($this->input->get('id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('id'));
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch contributor data registry object.");
		}
		

		// XXX: TODO: LIMIT and offset (pass to getSuggestedLinks...)
		$this->load->library('solr');
		$contents = $registry_object->getContributorData();

		echo json_encode(array("contents"=>$contents));
	}

	public function getInstitutionals(){
		$result = $this->db->select('registry_object_id')->from('institutional_pages')->get();
		$inst = array();
		foreach($result->result() as $r){
			array_push($inst, $r->registry_object_id);
		}
		$result = $this->db->select('title, slug')->from('registry_objects')->where('status', 'PUBLISHED')->where_in('registry_object_id', $inst)->get();
		echo json_encode(array("contents"=>$result->result()));
	}

	/**
	 * Fetch canned text for contributor page
	 *
	 * XXX: TODO
	 */
	public function getContributorText()
	{
		$cannedText = array();

		// Get the RO instance for this registry object so we can fetch its contributor datat
		$this->load->model('registry_object/registry_objects', 'ro');
		
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}
		elseif ($this->input->get('id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('id'));
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch suggested links for this registry object.");
		}
		

		// XXX: TODO: LIMIT and offset (pass to getSuggestedLinks...)
	
		$cannedText = $registry_object->getContributorText();

		echo json_encode(array("theText"=>$cannedText));
	}
	/**
	 * Return a list of Spotlight Partners along with their brief description and location (URL)
	 */
	public function getSpotlight(){
		$this->output->set_content_type(rda::response_format);
		$partners = array();

		$this->load->helper('file');
		$file = read_file('./assets/shared/spotlight/spotlight.json');
		$file = json_decode($file, true);
		if(is_array($file['items']) && count($file['items']) > 0)
		{
			foreach($file['items'] as $partner){
				if($partner['visible']=='yes'){
					$item = array(
						'title'=>$partner['title'],
						'description'=>$partner['content'],
						'img_url'=>$partner['img_url'],
						'url'=>$partner['url'],
						'visible'=>$partner['visible']
					);
					if(isset($partner['new_window']) && $partner['new_window']=='yes') $item['new_window']=$partner['new_window'];
					if(isset($item['img_attr'])) $item['img_attr']=$partner['img_attr'];
					if (isset($partner['url_text']) && $partner['url_text'])
					{
						$item['url_text'] = $partner['url_text'];
					}
					$partners[] = $item;
				}
			}
		}
		$partners = array_reverse($partners);
		// services_spotlight_partners_data_source
		$this->output->set_output(json_encode(array("items"=>$partners)));
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


	public function getAncestryGraph()
	{
		$this->load->model('connectiontree');
		$this->load->model('registry_object/registry_objects','ro');

		$depth = 5;
		$this_registry_object = null;
		
		// Get the RO instance for this registry object so we can fetch its graphs
		if ($this->input->get('slug'))
		{
			$this_registry_object = $this->ro->getBySlug($this->input->get('slug'));
			$published_only = TRUE;
		}
		elseif ($this->input->get('registry_object_id'))
		{
			$this_registry_object = $this->ro->getByID($this->input->get('registry_object_id'));
			$published_only = FALSE;
		}

		if (!$this_registry_object)
		{
			throw new Exception("Unable to fetch connection graph for this registry object.");
		}

		// Loop through to get all immediate ancestors and build their trees
		$trees = array();
		$ancestors = $this->connectiontree->getImmediateAncestors($this_registry_object, $published_only);

		if ($ancestors)
		{
			foreach ($ancestors AS $ancestor_element)
			{
				if($this_registry_object->id != $ancestor_element['registry_object_id']){
					$root_element_id = $this->connectiontree->getRootAncestor($this->ro->getByID($ancestor_element['registry_object_id']), $published_only);
					$root_registry_object = $this->ro->getByID($root_element_id->id);

					// Only generate the tree if this is a unique ancestor
					if (!isset($this->connectiontree->recursed_children[$root_registry_object->id]))
					{
						$trees[] = $this->connectiontree->get($root_registry_object, $depth, $published_only, $this_registry_object->id);
					}
				}
			}
		}
		else
		{
			$trees[] = $this->connectiontree->get($this_registry_object, $depth, $published_only);
		}

		echo json_encode(array("status"=>"success", "trees"=>$trees));
	}


	public function getContributorPage()
	{
		$registry_object_id = $this->input->get('registry_object_id') ?: 0;
		$published_only = $this->input->get('published_only') ?: true;

		if (!$registry_object_id)
		{
			throw new Exception("Unable to get contributor page information: invalid ID");
		}

		$contributor_page_data = getContributorData();
		// XXX: go fetch this record with ->getByID()
		// XXX: Do some checking that this is actually a contributor page using a new model in data_sources/ ??
		// XXX: use the functions in the model to get the precanned values, from SOLR/wherever...
		// XXX: remember to pass along $published_only so draft contributor pages look reasonableish!

		echo json_encode(array("data" => $contributor_page_data));



	}
	public function getSlugFromKey()
	{
		$key = $this->input->get("key");

		$this->db->select("slug,registry_object_id,status")->from("registry_objects")->where("key",$key);
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$result = $query->result_array();
			echo json_encode($result);
		}
		else
		{
			echo json_encode(array());
		}
	}

	/* Setup this controller to handle the expected response format */
	public function __construct()
    {
    	parent::__construct();

    	// JSON output at all times?
    	//$this->output->set_content_type(rda::response_format);

    	// Set our exception handler to function in JSON mode
    	set_exception_handler('json_exception_handler');
    }
}