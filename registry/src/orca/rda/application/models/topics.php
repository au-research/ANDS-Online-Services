<?php
class Topics extends CI_Model {

	private $topics = array (

			"tropics" =>
			array(
					"name"=>"Tropical Research",
					"html"=>"",
					"auto_boxes" => array(
							array ("id"=>"collections",

									"query"=>'fulltext:tropics -key:("http://esrc.unimelb.edu.au/OHRM#E000849") -data_source_key:("www.qfab.org/qfab2") class:("collection")',
									"heading"=>"Collections",
									"record_limit"=>3),
							array ("id"=>"groups",
									"query"=>'-data_source_key:("www.qfab.org/qfab2") -data_source_key:("PUBLISH_MY_DATA") class:("collection") fulltext:tropics',

									"query_facet"=>"group",
									"heading"=>"Data Contributors",
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

												array("url" => "http://tropicaldatahub.org/", "title"=>"The Tropical Data Hub"),
												array("url" => "http://www.nerptropical.edu.au/", "title"=>"NERP Tropical Ecosystems Hub"),
												array("url" => "http://www.gbrmpa.gov.au/", "title"=>"Great Barrier Reef Marine Park Authority"),
												array("url" => "http://www.rrrc.org.au/", "title"=>"Reef & Rainforest Research Centre"),
												array("url" => "http://www.csiro.au/Organisation-Structure/Divisions/Ecosystem-Sciences/Tropical-Arid-Systems.aspx", "title"=>"CSIRO Tropical and Arid Systems Research Program"),
												array("url" => "http://www.ath.org.au/", "title"=>"Australian Tropical Herbarium"),
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