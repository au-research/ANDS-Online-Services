<?php
class Topics extends CI_Model {

	private $topics = array (

			"tropics" =>
			array(
					"name"=>"Tropical Sciences",
					"html"=>"",
					"auto_boxes" => array(
							array ("id"=>"collections",
									"query"=>'fulltext:tropics -data_source_key:("www.qfab.org/qfab2") class:("collection")',
									"heading"=>"Collections",
									"record_limit"=>3),
							array ("id"=>"groups",
									"query"=>'-data_source_key:("www.qfab.org/qfab2") class:("collection") fulltext:tropics',
									"query_facet"=>"group",
									"heading"=>"Research Groups",
									"record_limit"=>3),
							array ("id"=>"services",
									"query"=>'class:("service") (key:("fb5b093a-1ef1-453d-80fb-124ae1776e82") OR key:("http://imosmest.aodn.org.au/2009101401440ND"))',
									"heading"=>"Related Services",
									"record_limit"=>3),
							array ("id"=>"activities",
									"query"=>'(key:("griffith.edu.au/146e8bc57e955d5c908efc690fe3269c") OR key:("jcu.edu.au/tdh/activity/15549"))',
									"heading"=>"Related Activities",
									"record_limit"=>3),

					),
					"manual_boxes" => array(
							array("heading"=>"Other Related Links",
									"items"=> array(
												array("url" => "https://eresearch.jcu.edu.au/tdh", "title"=>"The Tropical Data Hub"),
												array("url" => "http://www.nccarf.edu.au/", "title"=>"The National Climate Change Adaptation Research Facility"),
												array("url" => "http://www.rrrc.org.au/", "title"=>"The Australian Centre for Tropical Freshwater Research"),
												array("url" => "http://www.wettropics.gov.au/", "title"=>"Wet Tropics Management Authority"),
												array("url" => "http://www.rrrc.org.au/mtsrf/", "title"=>"Marine and Tropical Sciences Research Facility "),
												array("url" => "http://www.csiro.au/Organisation-Structure/Divisions/Ecosystem-Sciences/Tropical-Arid-Systems.aspx", "title"=>"CSIRO Tropical and Arid Systems Research Program"),
												array("url" => "http://www.track.gov.au/", "title"=>"Tropical Rivers and Coastal Knowledge (TRaCK) hub"),
												array("url" => "http://www.aims.gov.au/docs/data/data.html", "title"=>"AIMS Data Centre"),

											))
					),
			),


	);


	function __construct()
	{
		parent::__construct();
	}

	function getTopics()
	{
		return $this->topics;
	}
}