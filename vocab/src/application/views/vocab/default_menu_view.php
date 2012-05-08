<?php
$menu = array();

if ($root == -1)
{

	$menu[] = 	array(
					"data"=> "ANZSRC-FOR <span style='color:grey;'>(2008)</span>",
					"attr"=>array("id"=>"http://purl.org/au-research/vocabulary/ANZSRC-FOR/2008/"),
					"state"=>"closed",
				);
	
	$menu[] = 	array(
					"data"=>"RIF-CS <span style='color:grey;'>(v1.3.0)</span>",
					"attr"=>array("id"=>"http://purl.org/au-research/vocabulary/RIF-CS/1.3.0/"),
					//"classes"=>"vocab",
					"children"=>array ( 
						array(
							"data"=>"Child1",
						 	"attr"=>array("rel"=>"term", "id"=>"http://purl.org/au-research/vocabulary/RIF-CS/1.3.0/TERM"),
						),
					),
					"state"=>"closed",
				);
					
}
else
{
	$menu[] = 	array(
					"data"=>"CHILD2",
					"attr"=>array("rel"=>"term", "id"=>"http://purl.org/au-research/vocabulary/RIF-CS/1.3.0/TERM"),
					//"state"=>"closed",
				);
}


print json_encode($menu);
?>