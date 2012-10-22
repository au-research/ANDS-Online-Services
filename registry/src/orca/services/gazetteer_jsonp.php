<?php


	header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$jsonData['status'] = 'ERROR';	
		$jsonData['message'] = 'searchText must be defined';
		$searchText = '';
		$limit = 99;
		$recCount = 0;
		$feature = '';
		$callback = "function";
		if (isset($_GET['searchText'])){
			$searchText= $_GET['searchText'];
			$jsonData['message'] = 'searchText'.$searchText;
		}
		if (isset($_GET['callback'])){
			$callback = $_GET['callback'];
		}
		if (isset($_GET['limit'])){
			$limit = $_GET['limit'];
			$jsonData['limit'] = $limit;
		}
		if (isset($_GET['feature'])){
			$feature = $_GET['feature'];
			$jsonData['feature'] = $feature;
		}
		
		
        if($searchText)
        {
        $mctGazetteerGeocoderUrl = 'http://gazetteer.mymaps.gov.au/geoserver/wfs?service=wfs&version=1.1.0&request=GetFeature&typename=iso19112:SI_LocationInstance&maxFeatures=100&filter=';       	
		$filterText = '<ogc:Filter xmlns:ogc="http://www.opengis.net/ogc"><ogc:PropertyIsLike wildCard="*" singleChar="#" escapeChar="\\"><ogc:PropertyName>iso19112:alternativeGeographicIdentifiers/iso19112:alternativeGeographicIdentifier/iso19112:name</ogc:PropertyName><ogc:Literal>'.$searchText.'</ogc:Literal></ogc:PropertyIsLike></ogc:Filter>';
        }
        if($feature)
        {
        $mctGazetteerGeocoderUrl = 'http://gazetteer.mymaps.gov.au/geoserver/wfs?service=wfs&version=1.1.0&request=GetFeature&typename=iso19112:SI_LocationType&filter=';        	
		$filterText = '<ogc:Filter xmlns:ogc="http://www.opengis.net/ogc"><ogc:PropertyIsLike wildCard="%" singleChar="#" escapeChar="\\"><ogc:PropertyName>@gml:id</ogc:PropertyName><ogc:Literal>'.$feature.'%</ogc:Literal></ogc:PropertyIsLike></ogc:Filter>';
        }
		
		$jsonData = array();
		$jsonData['status'] = 'OK';	
		$ch = curl_init() or die(curl_error()); 		
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_URL,$mctGazetteerGeocoderUrl.rawurlencode($filterText));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		$data =curl_exec($ch) or die(curl_error()); 
		$gazetteerDoc = new DOMDocument();
		$gazetteerDoc->loadXML($data);
		$gXPath = new DOMXpath($gazetteerDoc);
		if($searchText)
		{
			$featureMemberListTOP = $gXPath->evaluate('gml:featureMember[descendant::node()[contains(@xlink:href,"LOCU")] or descendant::node()[contains(@xlink:href,"SUB")] or descendant::node()[contains(@xlink:href,"URBN")]]');
			$featureMemberListBOTTOM = $gXPath->evaluate('gml:featureMember[not(descendant::node()[contains(@xlink:href,"LOCU")] or descendant::node()[contains(@xlink:href,"SUB")] or descendant::node()[contains(@xlink:href,"URBN")])]');
			$jsonData['items_count'] = ($featureMemberListTOP->length) + ($featureMemberListBOTTOM->length) ;		
			$items = array();
	        for( $i=0; $i < $featureMemberListTOP->length; $i++ )
			{
				$item = array();
				$featureMember = $featureMemberListTOP->item($i);
				$item['title'] =  $gXPath->evaluate('.//iso19112:name', $featureMember)->item(0)->nodeValue;
				$coordsStr = $gXPath->evaluate('.//gml:pos', $featureMember)->item(0)->nodeValue;
				$spPos = strpos($coordsStr, ' ');
				$item['coords'] = $coordsStr;
				$item['lat'] = substr($coordsStr, 0, $spPos);
				$item['lng'] = substr($coordsStr, $spPos+1);
				$typeArray = array();
				$featureTypes = $gXPath->evaluate('.//@xlink:href', $featureMember);
				for( $j=0; $j < $featureTypes->length -1; $j++ )
				{			
					$attrvalue = $featureTypes->item($j)->nodeValue;
					$trimPos = strpos($attrvalue, ':AUSOSP:') + 8;
					array_push($typeArray, substr($attrvalue, $trimPos));
				}
				if($featureTypes->length > 0)
				{
				$item['types'] = $typeArray;
				}
				array_push($items, $item);
				if(++$recCount >= $limit)
				break;
			}
				
			for( $i=0; $i < $featureMemberListBOTTOM ->length; $i++ )
			{
				if(++$recCount >= $limit)
				break;
				$item = array();
				$featureMember = $featureMemberListBOTTOM ->item($i);
				$item['title'] =  $gXPath->evaluate('.//iso19112:name', $featureMember)->item(0)->nodeValue;
				$coordsStr = $gXPath->evaluate('.//gml:pos', $featureMember)->item(0)->nodeValue;
				$spPos = strpos($coordsStr, ' ');
				$item['coords'] = $coordsStr;
				$item['lat'] = substr($coordsStr, 0, $spPos);
				$item['lng'] = substr($coordsStr, $spPos+1);
				$typeArray = array();
				$featureTypes = $gXPath->evaluate('.//@xlink:href', $featureMember);
				for( $j=0; $j < $featureTypes->length - 1; $j++ )
				{			
					$attrvalue = $featureTypes->item($j)->nodeValue;
					$trimPos = strpos($attrvalue, ':AUSOSP:') + 8;
					array_push($typeArray, substr($attrvalue, $trimPos));
				}
				if($featureTypes->length > 0)
				{
				$item['types'] = $typeArray;
				}
				array_push($items, $item);
			}
			
			$jsonData['items'] = $items;
	        
		}
		if($feature)
        {
        	$featureMemberList = $gXPath->evaluate('gml:featureMember');
			$jsonData['items_count'] = $featureMemberList->length;		
			$items = array();
	        for( $i=0; $i < $featureMemberList->length; $i++ )
			{
				$item = array();
				$featureMember = $featureMemberList->item($i);
				$item['title'] =  $gXPath->evaluate('.//iso19112:name', $featureMember)->item(0)->nodeValue;
				$item['id'] = 	  $gXPath->evaluate('./iso19112:SI_LocationType/@gml:id', $featureMember)->item(0)->nodeValue;
				array_push($items, $item);
			}
        	$jsonData['items'] = $items;
        }
		$jsonData = json_encode($jsonData);
		echo $callback."(".$jsonData.");";
				
?>