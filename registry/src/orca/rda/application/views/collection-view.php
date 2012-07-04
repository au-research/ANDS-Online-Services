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
	
	$theCollections = $collections->{'response'}->{'docs'};	
	if(count($theCollections)<5)
	{
		$theNum = count($theCollections);
	}else{
		$theNum = 5;
	}
	if($theNum == 1) 
	{
		$theStr = $theNum." Collection ";
	} else {
	
		$theStr = $theNum." Collections ";
	}
	if($theNum>0)
	{
		echo  '		<h2>Last '.$theStr.' Added</h2>
			<div id="collectionsAdded">';
				
			
		echo "<ul>";
		foreach($theCollections as $collection)
		{
			echo '<li><a href="'.base_url().'view/?key='.urlencode($collection->{'key'}).'">'.$collection->{'display_title'}.'</a></li>';
		}
		echo "</ul></div>";		
	}
?>