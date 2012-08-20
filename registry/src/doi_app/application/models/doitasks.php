<?php
	
	class Doitasks extends CI_Model {

  
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	function putxml(){
		global $host, $doi_root;
		$base_url	= 'http://'.$host.'/home/dois/';
		$CI =& get_instance();
		$doiList = getDoiListxml();
		foreach($doiList->result() as $doi)
		{
			$xml = file_get_contents($base_url."/doi_xml.php?doi=".$doi->doi_id);
			$data = array('datacite_xml' => $xml);
			$where = "doi_id = '".$doi->doi_id."'";
    		$query_str = $this->db->update_string('doi_objects', $data, $where);
    		$result = $CI->db->query($query_str);
    		if($result!=1)
    		{
    			echo 'Error updating object xml '.$doi->doi_id.'<br/>';
    		}else{
      			echo 'Updated object xml '.$doi->doi_id.'<br/>';
    		}	
		}
		exit;
	}

	function xml(){	
		global $api_version;		
		$xml = ''; $error = '';
		$debug = $this->input->get('debug');
		
		if($debug && $debug == 'true')	
		{
			$this->debugOn();
		}	
				
		$doi_id = $this->input->get('doi');	
		$doi_id = rawurldecode($doi_id);
		$response_type = $this->input->get('response_type');
		if(!$response_type)	$response_type = 'string';		
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';	
		
		if(!$doi_id)
		{
			$error = doisGetUserMessage("MT010", $doi_id ,$response_type,$app_id=NULL, "You must provide the doi value to obtain it's xml",$urlValue=NULL);
		}	
		if(!$error)
		{
			$doidata = getxml($doi_id);		
			if($doidata->num_rows() > 0){			
				foreach($doidata->result() as $row)
				{
					if($row->status=='ACTIVE')
					{
						$xml = $row->datacite_xml;							
					}else{				
						$error = doisGetUserMessage("MT012", $doi_id, $response_type,$app_id=NULL, "",$urlValue=NULL);
					}
				}

			}else{			
				$error = doisGetUserMessage("MT011", $doi_id, $response_type,$app_id=NULL, "",$urlValue=NULL);		
			}	
		}
		
		if ($error)
		{
			echo $error;
		}
		elseif ($response_type == "json")
		{
			// Backwards compatible fix for CC-213 to address XML geting returned as unwrapped XML (even when JSON requested)
			header('Content-type: application/json');
			echo doisGetUserMessage("MT013", $doi_id, $response_type, NULL, $xml, NULL);
		}
		elseif ($response_type == "xml")
		{
			// Keep existing behaviour for backwards compatibility
			header('Content-type: text/xml');
			echo $xml;
		}
		elseif ($response_type == "string")
		{
			echo $xml;
		}
		
		
	}
	
	function update(){
		global $dataciteSchema;
		global $api_version;				
		$xml ='';	
		$errorMessages = '';	
		$notifyMessage = '';
		$logMessage = '';
		$verbosemessage = '';
		$outstr = '';
		$doiObjects = null;
		$response1 = "OK";
		$response2 = "OK";
		$testing = 'no';	

		$debug = $this->input->get('debug');
		
		if($debug && $debug == 'true')	
		{
			$this->debugOn();
		}		
		$app_id = $this->input->get('app_id');		//passed as a parameter
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$urlValue = $this->input->get('url');		//passed as a parameter
		$urlValue = rawurldecode($urlValue);
		$doiValue = $this->input->get('doi');		//passed as a parameter
		$doiValue = rawurldecode($doiValue);
		$response_type = $this->input->get('response_type');	//passed as a parameter		
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';		
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to update a doi",$urlValue);
		}
		if(!$doiValue)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
		}
		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		} 
		if($errorMessages == '')	
		{
			$client_id = checkDoisValidClient($ip,$app_id);

			if(!$client_id)
			{
				$errorMessages = doisGetUserMessage("MT009", $doi_id=NULL,$response_type,$app_id, $verbosemessage,$urlValue);

			}else{				
				if(!checkDoisClientDoi($doiValue,$client_id))
				{
					$errorMessages = doisGetUserMessage("MT008", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
				} 				
			}
		}
		if($errorMessages == '')
		{
		$doidata = getxml($doiValue);		 			// check if doi is a valid doi and get information about it
		
		if($doidata->num_rows() > 0){
			//we need to get the xml if that is to be updated as well
			if($_POST){
				$xml = trim(implode($_POST));				// passed as posted content
			}
			
			//first up, lets check that this client is permitted to update this doi.

							
			if($xml) // if the client has posted xml to be updated
			{
				$doiObjects = new DOMDocument();
						
				$result = $doiObjects->loadXML($xml);
		
				$errors = error_get_last();
			
				if( $errors )
				{
					$errorMessages = "Document Load Error: ".$errors['message']."\n";
				}
				else 
				{
					// Validate it against the datacite schema.
					error_reporting(0);
					// Create temporary file and save manually created DOMDocument.
					$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';						  
					$doiObjects->save($tempFile);				 
					// Create temporary DOMDocument and re-load content from file.
					$doiObjects = new DOMDocument();
					$doiObjects->load($tempFile);					  
					 //Delete temporary file.
					if (is_file($tempFile))
					{
						unlink($tempFile);
					}
		  
					$result = $doiObjects->schemaValidate(gCMD_SCHEMA_URI);

					$xml = $doiObjects->saveXML();

					$errors = error_get_last();
					if( $errors )
					{
						$verbosemessage = "Document Validation Error: ".$errors['message']."\n";						
						$errorMessages = doisGetUserMessage("MT007", $doi_id=NULL,$response_type,$app_id, $verbosemessage,$urlValue);
					}				
				}	
			}
			
			if( $errorMessages == '' )
			{
				// Update doi information
				$updateError = updateDoiObject($doiValue,$doiObjects,$urlValue,$xml);	
				if(!$updateError){	
				// Update the DOI.
					if($urlValue)
					{
						$response1 = $this->doisRequest("mint",$doiValue, $urlValue, $xml,$client_id);		
					}
					
					if($doiObjects)
					{
						$response2 = $this->doisRequest("update",$doiValue, $urlValue, $xml,$client_id);			
					}
					
					if( $response1 && $response2 )
					{
						if( doisGetResponseType($response1) == gDOIS_RESPONSE_SUCCESS && doisGetResponseType($response2) == gDOIS_RESPONSE_SUCCESS)
						{
							// We have successfully updated the doi through datacite.
							$verbosemessage = $response1." ".$response2;
							$notifyMessage = doisGetUserMessage("MT002", $doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
						}
						else
						{
							$verbosemessage = $response;							
							$errorMessages = doisGetUserMessage("MT010", $doi=NULL,$response_type,$app_id, $verbosemessage,$urlValue);
						}
					}
					else
					{	
						$verbosemessage = '';
						if($response1!=gDOIS_RESPONSE_SUCCESS) $verbosemessage .= $response1;
						if($response2!=gDOIS_RESPONSE_SUCCESS) $verbosemessage .= $response2;						
						$errorMessages = doisGetUserMessage("MT005", $doi=NULL,$response_type,$app_id, $verbosemessage,$urlValue);
					}
				}else{
					$verbosemessage = $updateError;	
					$errorMessages = doisGetUserMessage("MT010", $doi=NULL,$response_type,$app_id, $verbosemessage,$urlValue);
				}
			}
		}else{
			$errorMessages = doisGetUserMessage("MT011", $doi_id=$doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
		}
		}		
		echo $errorMessages;
		echo $notifyMessage;		
	}
	
	function mint(){
		global $dataciteSchema;
		global $api_version;	
		global $gDOIS_PREFIX_TYPES;			
		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		} 
		
		
		$xml ='';	
		$client_id = '';
		$errorMessages = '';	
		$notifyMessage = '';
		$logMessage = '';
		$outstr = '';
		$doiValue = '';
		$verbosemessage = '';
		$errors = '';
		$testing = 'no';
		$doiObjects = null;
		$response1 = "OK";
		$response2 = "OK";	
		$debug = $this->input->get('debug');
		
		if($debug && $debug == 'true')	
		{
			$this->debugOn();
		}
		
		$app_id = $this->input->get('app_id');		//passed as a parameter
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$urlValue = $this->input->get('url');		//passed as a parameter
		$urlValue = rawurldecode($urlValue);
		$response_type = $this->input->get('response_type');
		if(!$response_type) $response_type = 'string';		
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';
					//first up, lets check that this client is permitted to update this doi.
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue=NULL ,$response_type,$app_id, "You must provide an app id to mint a doi",$urlValue);
		}	
		if($urlValue=='' && $errorMessages == '')
		{
			$verbosemessage = 'You must provide a url when minting a doi.';			
			$errorMessages = doisGetUserMessage("MT010", $doi_id=NULL, $response_type,$app_id, $verbosemessage,$urlValue);//"URL is a mandatory value to mint a doi.<br />";
		}
		if($errorMessages == '')
		{
			$client_id = checkDoisValidClient($ip,$app_id);
			
			if(!$client_id)
			{
				$verbosemessage = 'Client with app_id '.$app_id.' from ip address '.$ip. ' is not a registered doi client.';
				$errorMessages = doisGetUserMessage("MT009", $doi_id=NULL, $response_type,$app_id, $verbosemessage,$urlValue);
			}		
						

			
			if($_POST){
				$xml = trim(implode($_POST));				// passed as posted content
			}else{
				$xml = '';
				$verbosemessage = 'You must post xml when minting a doi.';							
				$errorMessages = doisGetUserMessage("MT010", $doi_id=NULL, $response_type,$app_id, $verbosemessage,$urlValue);
			}
		

		}
		if(!$errorMessages)
		{
			$clientDetails = getDoisClient($app_id );

			foreach($clientDetails->result() as $clientDetail)
			{
				if($clientDetail->client_id<'10')
				{
					$client_id2 = "0".$clientDetail->client_id;
				}else{
					$client_id2 = $clientDetail->client_id;
				}
			}
			if($testing=='yes')
			{
				$datacite_prefix = $gDOIS_PREFIX_TYPES[3];
			}else{
				$datacite_prefix = $clientDetail->datacite_prefix;
			}

				
			$doiValue = strtoupper($datacite_prefix.$client_id2.'/'.uniqid());	//generate a unique suffix for this doi for this client 
			

		
			$doiObjects = new DOMDocument();
						
			$result = $doiObjects->loadXML($xml);
			$resources = $doiObjects->getElementsByTagName('resource');
			$theSchema = 'unknown';
			if(isset($resources->item(0)->attributes->item(0)->name))
			{
				$theSchema = $this->getXmlSchema($resources->item(0)->attributes->item(0)->nodeValue);
			}

			if($theSchema=="unknown")
			{
				$errors['message'] = "You have not provided a known schema location in your xml";
			}
				
			if( $errors )
			{
				
				$verbosemessage = "Document Load Error: ".$errors['message'];
				$errorMessages .= doisGetUserMessage("MT010", $doi_id=NULL, $response_type, $app_id, $verbosemessage,$urlValue);
			}	
			
			$errors = error_get_last();
			
			// we need to insert the determined doi value into the xml string to be sent to datacite
			// so we create a new 'identifier' element, set the identifierType attribute to DOI and 
			// replace the current identifier element then  write out to the xml string that is passed
			$currentIdentifier=$doiObjects->getElementsByTagName('identifier');
			for($i=0;$i<$currentIdentifier->length;$i++){
				$doiObjects->getElementsByTagName('resource')->item(0)->removeChild($currentIdentifier->item($i));
			}
			$newdoi = $doiObjects->createElement('identifier',$doiValue);
			$newdoi->setAttribute('identifierType',"DOI");	
			$doiObjects->getElementsByTagName('resource')->item(0)->insertBefore($newdoi,$doiObjects->getElementsByTagName('resource')->item(0)->firstChild);
		
			//$xml = $doiObjects->saveXML();

			if( $errors )
			{
				
				$verbosemessage = "Document Load Error: ".$errors['message'];
				$errorMessages .= doisGetUserMessage("MT010", $doi_id=NULL, $response_type, $app_id, $verbosemessage,$urlValue);
			}else{
				// Validate it against the datacite schema.
				error_reporting(0);

				// Create temporary file and save manually created DOMDocument.
				$tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
						  
				$doiObjects->save($tempFile);
					 
				// Create temporary DOMDocument and re-load content from file.
				$doiObjects = new DOMDocument();
				$doiObjects->load($tempFile);
					  
				//Delete temporary file.
				if (is_file($tempFile))
				{
					unlink($tempFile);
				}

				$result = $doiObjects->schemaValidate($dataciteSchema[$theSchema]);
				
				$xml = $doiObjects->saveXML();
			
				$errors = error_get_last();
				if( $errors )
				{
					$verbosemessage = "Document Validation Error: ".$errors['message'];
					$errorMessages = doisGetUserMessage("MT006", $doi_id=NULL, $response_type, $app_id, $verbosemessage,$urlValue);
				}			
				
			}					
			
		}	
			
		if( $errorMessages == '' )
		{
			// Insert doi information into the database
			$insertResult = importDoiObject($doiObjects,$urlValue, $client_id, $created_who='SYSTEM', $status='REQUESTED',$xml);

			if(!$insertResult){	
				// Mint the DOI.					
				$response = $this->doisRequest("mint",$doiValue, $urlValue, $xml,$client_id);
	
				if( $response )
				{
					if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )
					{
						// We have successfully minted the doi through datacite.										
						$response = $this->doisRequest("update",$doiValue, $urlValue, $xml,$client_id);		
		
						if(doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS )			
						{
							$notifyMessage = doisGetUserMessage("MT001", $doiValue, $response_type, $app_id,$response,$urlValue);
							$status = "ACTIVE";
							$activateResult = setDoiStatus($doiValue,$status);
						}else{
							$errorMessages .=  doisGetUserMessage("MT010", $doiValue, $response_type, $app_id,$response,$urlValue);
						}												
					}else{
						$errorMessages .=  doisGetUserMessage("MT010", $doiValue, $response_type, $app_id,$response,$urlValue);
					}
				}else{
					$errorMessages .=  doisGetUserMessage("MT005", $doiValue, $response_type, $app_id,$response,$urlValue);
				}
			}else{
				$errorMessages .= '..<br />'.$insertResult;
			
			}				
		}
		
		if($errorMessages)
		{		
			$outstr =  $errorMessages;	
			//We need to log this activity as errorred

			insertDoiActivity("MINT",$doiValue,"FAILURE",$client_id,$errorMessages);		
		}
			
		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity("MINT",$doiValue,"SUCCESS",$client_id,$notifyMessage);		
			$outstr = $notifyMessage;
		}
			
		//we now need to return the result back to the calling program.
		echo $outstr;		
		
	}
	
	function activate(){
		global $api_version;
		global $host, $doi_root;
		$base_url	= 'http://'.$host.$doi_root;				
		$errorMessages = '';
		$notifyMessage = '';
		$outstr = '';
		$urlValue = '';
		$verbosemessage = '';	
		$client_id ='';	

		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		} 
		$debug = $this->input->get('debug');
		
		if($debug && $debug == 'true')	
		{
			$this->debugOn();
		}
		$app_id = $this->input->get('app_id');		//passed as a parameter
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$doiValue = $this->input->get('doi');		//passed as a parameter	
		$doiValue = rawurldecode($doiValue);
		$response_type = $this->input->get('response_type');		//passed as a parameter			
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';		
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to update a doi",$urlValue);
		}
		if(!$doiValue)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
		}		
		//first up, lets check that this client is permitted to update this doi.
		if($errorMessages =='')
		{
		$client_id = checkDoisValidClient($ip,$app_id);
		if(!$client_id)
		{
			$verbosemessage = '';
			$errorMessages = doisGetUserMessage("MT009", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);

		}else{				
			if(!checkDoisClientDoi($doiValue,$client_id))
			{
				$verbosemessage = '';
				$errorMessages = doisGetUserMessage("MT008", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
			} 				
		}	
		}			
		if($errorMessages == '')
		{

	

		if(getDoiStatus($doiValue)!="INACTIVE")
		{
			$verbosemessage = "DOI ".$doiValue." is not set to inactive so cannot activate it.<br />";	
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, $verbosemessage,$urlValue);
		}
	
		if( $errorMessages == '' )
		{
			// Update doi information
			$status = "ACTIVE";
			$activateResult = setDoiStatus($doiValue,$status);
			
			$doidata = getxml($doiValue);		
			if($doidata->num_rows() > 0){			
				foreach($doidata->result() as $row)
				{
						$xml = $row->datacite_xml;
				}
			}
			if(!$activateResult){	
			// Activate the DOI.
	
				$response = $this->doisRequest("update",$doiValue,$urlValue = NULL ,$xml, $client_id );
	
				if($response)
				{
					if( doisGetResponseType($response) == gDOIS_RESPONSE_SUCCESS || $response == gDOIS_RESPONSE_SUCCESS)
					{
						// We have successfully activated the doi through datacite.
						$notifyMessage = doisGetUserMessage("MT004", $doiValue,$response_type,$app_id, $response,$urlValue);
			
	
					}
					else
					{
						$activateResult = setDoiStatus($doiValue,'INACTIVE');
						$errorMessages = doisGetUserMessage("MT010", $doiValue,$response_type,$app_id, $response,$urlValue);
										
					}
				}
				else
				{	
					$errorMessages = doisGetUserMessage("MT005",$doiValue,$response_type,$app_id, $verbosemessage,$urlValue);
				
				}
			}else{
			
				$verbosemessage = $response;		
				$errorMessages = doisGetUserMessage("MT010",$doiValue,$response_type,$app_id, $verbosemessage,$urlValue);				
			}
		}
		}
		if($errorMessages)
		{	
			
			$outstr =  $errorMessages;	
			//We need to log this activity as errorred	

			insertDoiActivity("ACTIVATE",$doiValue,"FAILURE",$client_id,$errorMessages);		
	
		}
		
		if($notifyMessage)
		{
			//We need to log this activity as successful
			insertDoiActivity("ACTIVATE",$doiValue,"SUCCESS",$client_id,$notifyMessage);		
			$outstr = $notifyMessage;
		}
		
		//we now need to return the result back to the calling program.
		echo $outstr;		
	}
	
	function deactivate(){
		global $api_version;			
		$errorMessages = '';
		$notifyMessage = '';
		$outstr = '';
		$urlValue = '';
		$verbosemessage = '';	
		$client_id ='';	

		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
			$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
			$ip=$_SERVER["HTTP_CLIENT_IP"];
		} else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
			$ip=$_SERVER["REMOTE_ADDR"];
		} else {
			// Run by command line??
			$ip="127.0.0.1";
		} 
		$debug = $this->input->get('debug');
		
		if($debug && $debug == 'true')	
		{
			$this->debugOn();
		}
		$app_id = $this->input->get('app_id');		//passed as a parameter
		if(substr($app_id,0,4)=='TEST')
		{
			$app_id = substr($app_id,4,strlen($app_id));
			$testing = 'yes';
		}
		$doiValue = $this->input->get('doi');		//passed as a parameter	
		$doiValue = rawurldecode($doiValue);
		$response_type = $this->input->get('response_type');		//passed as a parameter			
		if(!$response_type) $response_type = 'string';
		$api_version = $this->input->get('api_version');
		if(!$api_version)	$api_version = '1.0';		
		if(!$app_id)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide an app id to update a doi",$urlValue);
		}
		if(!$doiValue)
		{
			$errorMessages = doisGetUserMessage("MT010", $doiValue ,$response_type,$app_id, "You must provide the doi value to update a doi",$urlValue);
		}		
		//first up, lets check that this client is permitted to update this doi.
		if($errorMessages =='')
		{
		$client_id = checkDoisValidClient($ip,$app_id);
		if(!$client_id)
		{
			$verbosemessage = '';
			$errorMessages = doisGetUserMessage("MT009", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);

		}else{				
			if(!checkDoisClientDoi($doiValue,$client_id))
			{
				$verbosemessage = '';
				$errorMessages = doisGetUserMessage("MT008", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);
			} 				
		}	
		}			
		if($errorMessages == '')
		{
	
	
		if(getDoiStatus($doiValue)!="ACTIVE")
		{
				$verbosemessage = "DOI ".$doiValue." is not set to active so cannot deactivate it.";
				$errorMessages = doisGetUserMessage("MT010", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);				
		}
	
		if( $errorMessages == '' )
		{
			// Update doi information
			$status = "INACTIVE";
			$inactivateResult = setDoiStatus($doiValue,$status);
			if(!$inactivateResult){	
				// deactivate the DOI.	
				$response = $this->doisRequest("delete",$doiValue,$urlValue = NULL ,$xml = NULL,$client_id );
	
				if($response)
				{
					if( $response == gDOIS_RESPONSE_SUCCESS )
					{
						// We have successfully deactivated the doi through datacite.
						$notifyMessage .= doisGetUserMessage("MT003", $doiValue, $response_type,$app_id, $response,$urlValue);						
					}
					else
					{			
						$errorMessages .= doisGetUserMessage("MT010", $doi=NULL, $response_type,$app_id, $response,$urlValue);				;
					}
				}
				else
				{	
					$errorMessages .= doisGetUserMessage("MT005", $doi=NULL, $response_type,$app_id, $verbosemessage,$urlValue);				

				}
			}else{
					
				$verbosemessage = $inactivateResult;
				$errorMessages = doisGetUserMessage("MT010", $doiValue, $response_type,$app_id, $verbosemessage,$urlValue);		
			}
		}
		}
		if($errorMessages)
		{	
			
			$outstr =  $errorMessages;
			//We need to log this activity as errorred

			insertDoiActivity("INACTIVATE",$doiValue,"FAILURE",$client_id,$errorMessages);
	
		}
		
		if($notifyMessage)
		{
			//We need to log this activity
			insertDoiActivity("INACTIVATE",$doiValue,"SUCCESS",$client_id,$notifyMessage);
		
			$outstr = $notifyMessage;
		}		
		//we now need to return the result back to the calling program.
		echo $outstr;		
		
	}
	
	function checkurl(){	
		$unavailableCount = 0;
		$message = '';
		$subject = "Cite My Data DOI url availability check";
		$recipient = "services@ands.org.au";
		$recipient = "lizwoods.ands@gmail.com";
		$notifyMessage = '';
		$lastupdate = '';
		$doiList = getDoiList();
		
		if($doiList)
		{
			foreach($doiList->result() as $doi)
			{
				//we want to check if the url is available
				if(!doisDomainAvailible($doi->url))
				{
					$lastupdate = $doi->updated_when;
					if(!$lastupdate) $lastupdate = $doi->created_when;
					$clientDetails = getDoisClientDetails($doi->client_id);
					$clientName = $clientDetails->result();	
					$notifyMessage .= $doi->doi_id." ".$doi->url." ".$clientName[0]->client_name." ".$lastupdate."\n";
					$unavailableCount++;
				}
			}	
		}
		
		
		$message .= "There are ".$unavailableCount." doi urls unavailable on ".date("d/m/Y h:m:s")."\n"; 
		$message .= $notifyMessage;
		mail($recipient,$subject,$message);	
	}	

	

		
	function doisRequest($service, $doi, $url, $metadata,$client_id)
	{
	
		$resultXML = '';

		$mode='';
		
		if($client_id<10) $client_id = '-'.$client_id;	
		
		$authstr = gDOIS_DATACENTRE_NAME_PREFIX.".".gDOIS_DATACENTRE_NAME_MIDDLE.$client_id.":".gDOIS_DATACITE_PASSWORD;
		$requestURI = gDOIS_SERVICE_BASE_URI;

		$ch = curl_init();
				
		if($service=="mint")
		{
			$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
			$metadata="url=".$url."\ndoi=".$doi;
			$requestURI = gDOIS_SERVICE_BASE_URI."doi".$mode;
			curl_setopt($ch, CURLOPT_POST,1);		
		}
		elseif($service=="update")
		{	
			$context  = array('Content-Type:application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));			
			$requestURI = gDOIS_SERVICE_BASE_URI."metadata".$mode;
			curl_setopt($ch, CURLOPT_POST,1);	
		}
		elseif($service=="delete")
		{
			$context  = array('Content-Type:text/plain;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));
			$requestURI = gDOIS_SERVICE_BASE_URI."metadata/".$doi;			
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");				
		}
	
		curl_setopt($ch, CURLOPT_URL, $requestURI);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
		curl_setopt($ch, CURLOPT_HTTPHEADER,$context);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$metadata);
		$result = curl_exec($ch);
	
		$curlinfo = curl_getinfo($ch);
	
		curl_close($ch);
	
		if($result)
		{
			$resultXML = $result;
		}
		return $resultXML;
	}	

    function getXmlSchema($theSchemaLocation)
    {
    	if(str_replace("kernel-2.0","",$theSchemaLocation)!=$theSchemaLocation) 
    	{
    		return "2.0";
    	}
    	elseif(str_replace("kernel-2.1","",$theSchemaLocation)!=$theSchemaLocation)
    	{
    		return "2.1";
    	}
    	elseif(str_replace("kernel-2.2","",$theSchemaLocation)!=$theSchemaLocation)
    	{
    		return "2.2";
    	}
    	else 
    	{
    		return "unknown";
    	}
    }
		
	function debugOn()
	{

		ini_set('display_errors',1); 
 		error_reporting(E_ALL);
		$theGets = $this->input->get();
		if($theGets)
		{
			echo "Get parameters passed:<br />";
	
			foreach($theGets as $name=>$value)
			{
				print $name. "=".$value."<br />";
			}
		}
		$thePosts = $this->input->post();
		if($thePosts)
		{
			echo "Post parameters passed:<br />";
		
			foreach($thePosts as $name=>$value)
			{
				print $name. "=".$value."<br />";
			}	
		}		
	}    
	}
 ?>