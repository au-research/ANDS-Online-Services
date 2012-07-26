<?php
	header('Cache-Control: no-cache, must-revalidate');
	header('Content-type: application/json');

	$jsonData = array();
	$jsonData['status'] = 'OK';

	$items = array();
	for($i=0;$i<16;$i++){
		$item = array();
		$item['class'] = 'collection';
		$item['title'] = 'Sample Collection';
		$item['id'] = 123;
		$item['key'] = 'http://sample.collection.org';
		$item['brief'] = 'Lorem ipsum in Duis anim voluptate pariatur dolor eu pariatur aliquip et fugiat magna in in et labore aliquip proident officia culpa pariatur commodo. ';
		array_push($items, $item);
	}
	$jsonData['items'] = $items;
	$jsonData = json_encode($jsonData);
	echo $jsonData;
	
?>