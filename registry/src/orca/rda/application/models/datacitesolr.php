<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<?php
	class Datacitesolr extends CI_Model {

  
    function __construct()
    {
        parent::__construct();
    }
    
	function search($query,  $write_type='json', $page)
    {
    	$q=$this->RemoveCommonWords($query);
        
		$q=rawurlencode($q);
		$q=str_replace("%5C%22", "\"", $q);//silly encoding
		$start = 0;$row = 10;
		if($page!=1) $start = ($page - 1) * $row;
		
		$datacite_solr_url = $this->config->item('datacite_solr_url');
		
		$url = $datacite_solr_url.'?q='.$q.'&defType=disMax&qf=resourceTypeGeneral:("Collection","Dataset")^9999%20+resourceTypeGeneral:("Film","Image","Sound","PhysicalObject","InteractiveResource")^5555%20+resourceTypeGeneral:("Model","Software","Service")^1777%20+resourceTypeGeneral:("Event","Text")^111%20+resourceTypeGeneral:""^1&fl=*,score&start='.$start.'&rows='.$row.'&version=2.2&wt='.$write_type;

   		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);//post to SOLR
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl
    	if($write_type=='json'){	
			$content = json_decode($content);
		}			
				
		$found = $content->{'response'}->{'numFound'};
		
		if($found<1)
		{
			//we want to search again  will check if there are more than 4 words to match	
			$searchStr = urldecode($q);
			$wordCount = explode(" ",$searchStr);
			if(count($wordCount)>3)
			{
				$WordMatch =75;
				while($WordMatch>24&&$found==0)
				{
					$additionalParams = '&defType=dismax&mm=3%3C'.$WordMatch.'%25';
					$newurl = $url.$additionalParams;
					curl_setopt($ch,CURLOPT_URL,$newurl);//post to SOLR
    				$content = curl_exec($ch);//execute the curl
			   		 if($write_type=='json'){	
						$content = json_decode($content);
					}	
					$found = $content->{'response'}->{'numFound'};		
					$WordMatch =$WordMatch-25;				
				}								
			}	
		
		}
		curl_close($ch);//close the curl				
		return $content;
    }
    
	function RemoveCommonWords($sText){
		$CommonWords = array (
		'at',
		'the',
		'and',
		'of',
		'in',
		'is',
		'to',
		'a'
		);
		
		for ($x = 0; $x < count($CommonWords); $x++) {
			$sText = str_replace(' ' . $CommonWords[$x] . ' ', ' ', $sText);
		}
		
		return $sText;
	}
    
}
?>
