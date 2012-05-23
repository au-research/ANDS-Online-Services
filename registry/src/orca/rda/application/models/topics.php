<?php
class Topics extends CI_Model {

	private $topics = array (

			"tropics" =>
			array(
					"name"=>"Tropical Sciences",
					"html"=>"blah blah",
					"boxes" => array(
							array ("id"=>"collections",
									"query"=>"*:*",
									"heading"=>"Collections",
									"record_limit"=>3),
							array ("id"=>"groups",
									"query"=>'type:("group")',
									"heading"=>"Research Groups",
									"record_limit"=>3),
							array ("id"=>"services",
									"query"=>'class:("service")',
									"heading"=>"Related Services",
									"record_limit"=>3),

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