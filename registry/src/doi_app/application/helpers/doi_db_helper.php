<?php
    function getxml($doi_id){
    	$CI =& get_instance();
    	$result = $CI->db->get_where('doi_objects', array('doi_id'=>$doi_id));
    	return $result;
    }
    
    function getDoiList()
    {
     	$CI =& get_instance();
    	$result = $CI->db->get_where('doi_objects', array('status'=>"ACTIVE"));
    	return $result;   	
    }
    
    function getDoiListxml()
    {
     	$CI =& get_instance();
     	$result = $CI->db->query("select * from doi_objects WHERE status = 'ACTIVE'");
    	return $result;   	
    }  
    function getDoisClientDetails($client_id)
	{
     	$CI =& get_instance();
    	$result = $CI->db->get_where('doi_client', array('client_id'=>$client_id));
    	return $result; 
	} 
	   
	function importDoiObject($doiObjects, $url, $client_id, $created_who='SYSTEM', $status='REQUESTED',$xml)
	{
	
		$runErrors = '';
		$errors = null;	
				
		// Doi Object
		// =========================================================================
		$doiObjectList = $doiObjects->getElementsByTagName("resource");
		
		// Doi Object
		// =====================================================================
		$doiObject = $doiObjectList->item(0);
	
		// Doi Identifier
		// =====================================================================
		
		$doiIdentifier = $doiObject->getElementsByTagName("identifier")->item(0)->nodeValue;
				
		if( $doiIdentifier)
		{			
			//Doi publisher
			// =====================================================================		
			$publisher = $doiObject->getElementsByTagName("publisher")->item(0)->nodeValue;
			
			//Doi publish year
			// =====================================================================
			$publish_year = $doiObject->getElementsByTagName("publicationYear")->item(0)->nodeValue;			
			
			// Doi language
			// =====================================================================
			$languageValue = $doiObject->getElementsByTagName("language")->item(0)->nodeValue;						
			
			// Doi version
			// =====================================================================		
			$versionValue = $doiObject->getElementsByTagName("version")->item(0)->nodeValue;	
					
			// Doi rights
			// =====================================================================		
			$rightsValue = $doiObject->getElementsByTagName("rights")->item(0)->nodeValue;	
												
			$runErrors .= insertDoiObject($doiIdentifier,$publisher,$publish_year,$client_id,$created_who,"REQUESTED",$languageValue,$versionValue,"DOI",$rightsValue,$url,$xml);

		}
		else
		{
				$runErrors .= "Couldn't create DOI without identifier.</br>";
		}
			
		echo $runErrors;
		return $runErrors;
	}	
	   
	function updateDoiObject($doi_id, $doiObjects, $url,$xml)
	{	
		$runErrors = '';
		$errors = null;	
	
		if($url){
			$runErrors .= updateDoiUrl($doi_id,$url);
		}
		
		if($doiObjects)
		{
			// Doi Object
			// =========================================================================
			$doiObjectList = $doiObjects->getElementsByTagName("resource");
			// Doi Object
			// =====================================================================
			$doiObject = $doiObjectList->item(0);
	
			// Doi Identifier
			// =====================================================================	
			$doiIdentifier = $doiObject->getElementsByTagName("identifier")->item(0)->nodeValue;
			
			if( $doiIdentifier )
			{			
				//Doi publisher
				// =====================================================================		
				$publisher = $doiObject->getElementsByTagName("publisher")->item(0)->nodeValue;
			
				//Doi publish year
				// =====================================================================
				$publish_year = $doiObject->getElementsByTagName("publicationYear")->item(0)->nodeValue;			
			
				// Doi language
				// =====================================================================
				$languageValue = $doiObject->getElementsByTagName("language")->item(0)->nodeValue;						
			
				// Doi version
				// =====================================================================		
				$versionValue = $doiObject->getElementsByTagName("version")->item(0)->nodeValue;	
					
				// Doi rights
				// =====================================================================		
				$rightsValue = $doiObject->getElementsByTagName("rights")->item(0)->nodeValue;	
				
				$runErrors .= deleteDoiObjectXml($doiIdentifier);
			
				$runErrors .= updateDoiObjectAttributes($doiIdentifier,$publisher,$publish_year,$languageValue,$versionValue,$rightsValue,$xml);

			}
			else
			{
				$runErrors .= "Couldn't update DOI without identifier.</br>";
			}
		}	
		return $runErrors;
	}

	
	function insertDoiObject($doi_id,$publisher,$publicationYear,$client_id,$created_who,$status,$language,$version,$identifier_type,$rights,$url,$xml)
	{
		$updateTime = 'now()';
		$data = array(
		'doi_id'=> $doi_id, 
		'publisher' => $publisher,
		'publication_year' => $publicationYear,
		'client_id'=> $client_id,
		'created_who'=>$created_who,
		'status'=>$status,
		'language' => $language,
		'version' => $version,
		'identifier_type' => $identifier_type,		
		'rights' =>$rights, 
		'url' => $url,
		'datacite_xml'=>$xml);

		$CI =& get_instance();
    	$query_str = $CI->db->insert_string('doi_objects', $data); 

		$result = $CI->db->query($query_str);

    	if($result!=1)
    	{
    		return 'Error inserting object xml';
    	}
	}
	
	function deleteDoiObjectXml($doi_id)
	{
		$updateTime = 'now()';
		$data = array('publisher' => '','publication_year' => '','language' => '','version' => '','rights' =>'', 'updated_when' => $updateTime, 'datacite_xml'=>'');
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
    	$query_str = $CI->db->update_string('doi_objects', $data, $where); 
    	$query_str .= "; DELETE FROM doi_alternate_identifiers WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_contributors WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_creators WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_dates WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_descriptions WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_formats WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_related_identifiers WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_resource_types WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_sizes WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_subjects WHERE doi_id = '".$doi_id."'";
		$query_str .= "; DELETE FROM doi_titles WHERE doi_id = '".$doi_id."'";

		$result = $CI->db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error deleting object xml';
    	}
	}

	function updateDoiObjectAttributes($doi_id,$publisher,$publish_year,$languageValue,$versionValue,$rightsValue,$xml)
	{
		$data = array('publisher' => $publisher,'publication_year' => $publish_year,'language' => $languageValue,'version' => $versionValue,'rights' => $rightsValue,'datacite_xml'=>$xml);
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
    	$query_str = $CI->db->update_string('doi_objects', $data, $where); 
    	$result = $CI->db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error updating object attributes ';
    	}	
	}
	
	function updateDoiUrl($doi_id,$url)
	{
		$updateTime = 'now()';		
		$data = array('url' => $url, 'updated_when' => $updateTime,);
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
    	$query_str = $CI->db->update_string('doi_objects', $data, $where); 
    	$result = $CI->db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error updating url for doi '.$doi_id;
    	}	
	}	
	
	function checkDoisValidClient($ip_address,$app_id)
	{
     	$CI =& get_instance();
    	$results = $CI->db->get_where('doi_client', array('app_id'=>$app_id));
   			
		if( $results->num_rows()>0 )
		{			
			foreach($results->result() as $row)
			{			
				$iprange = explode(",",$row->ip_address);
			}
			if(count($iprange)>1)
			{
				if($ip_address>=$iprange[0]&&$ip_address<=$iprange[1]) return $row->client_id;			
			}
			else
			{
				return $row->client_id;					
			}
	
		}else{
			return false;
		}
	}
	
	function checkDoisClientDoi($doi_id,$client_id)
	{
     	$CI =& get_instance();
    	$results = $CI->db->get_where('doi_objects', array('doi_id'=>$doi_id,'client_id'=>$client_id));
   					
		if( $results->num_rows()>0 )
		{
			foreach($results->result() as $row)
			{
				$return = $row->client_id;		
			}
			return $return;		
		}else{
			return false;
		}
	}	
	
    function doisDomainAvailible($domain)
	{	
	    //initialize curl
	    $curlInit = curl_init($domain);
	    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
	    curl_setopt($curlInit,CURLOPT_HEADER,true);
	    curl_setopt($curlInit,CURLOPT_NOBODY,true);
	    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
	
	    //get answer
	    $response = curl_exec($curlInit);  
	    $curlInfo = curl_getinfo($curlInit);
	    curl_close($curlInit);
	    //check http status code
	    if ($curlInfo["http_code"]<400) return true;
	
	    return false;
	}
	
	function getDoisClient($app_id)
	{	
	   	$CI =& get_instance();
	    $results = $CI->db->get_where('doi_client', array('app_id'=>$app_id));
		
		return $results;
	}
	
	function insertDoiActivity($activity,$doiValue,$result,$client_id,$message)
	{
		$data = array('activity' => $activity,'doi_id' => $doiValue,'result'=>$result, 'client_id'=> $client_id, 'message'=>$message);
		$CI =& get_instance();
    	$query_str = $CI->db->insert_string('activity_log', $data); 
		$result = $CI->db->query($query_str);			
	    if($result!=1)
	    {
	    	$insertError = 'Error inserting into activity';
	    }  		
	}
	
	function setDoiStatus($doi_id, $status)
	{
		$data = array('status' => $status);
		$where = "doi_id = '".$doi_id."'";
		$CI =& get_instance();
    	$query_str = $CI->db->update_string('doi_objects', $data, $where); 
    	$result = $CI->db->query($query_str);
    	if($result!=1)
    	{
    		return 'Error updating object status ';
    	}				
	}
	
	function getDoiStatus($doi_id)
	{

	    $CI =& get_instance();
    	$results = $CI->db->get_where('doi_objects', array('doi_id'=>$doi_id));
   					
		if( $results->num_rows()>0 )
		{
			foreach($results->result() as $row)
			{
				$return = $row->status;		
			}
			return $return;		
		}else{
			return false;
		}		
		
	}	
 ?>