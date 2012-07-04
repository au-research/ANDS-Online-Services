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
	$contents = $content->{'facet_counts'}->{'facet_fields'}->{'class'};
	echo "<ul>";

	for($i=0;$i<count($contents);$i++)
	{
		$tab =$contents[$i];
		if($contents[$i]=="collection" && $contents[$i+1]=='1')
		{
			$contents[$i]="Collection";
		}elseif($contents[$i]=="collection" && $contents[$i+1]!='1'){
			$contents[$i]="Collections";
		}
			
		if($contents[$i]=="party" && $contents[$i+1]=='1')
		{
			$contents[$i]="Party";
		}elseif($contents[$i]=="party" && $contents[$i+1]!='1'){
			$contents[$i]="Parties";
		}
		if($contents[$i]=="service" && $contents[$i+1]=='1')
		{
			$contents[$i]="Service";
		}elseif($contents[$i]=="service" && $contents[$i+1]!='1'){
			$contents[$i]="Services";
		}
				
		if($contents[$i]=="activity" && $contents[$i+1]=='1')
		{
			$contents[$i]="Activity";
		}elseif($contents[$i]=="activity" && $contents[$i+1]!='1'){
			$contents[$i]="Activities";
		}
				
		if($contents[$i+1]>0)
		{					
			echo '<li><a id="hp-count-collection" href="'.base_url().'search#!/tab='.$tab.'/group='.urlencode($group).'">'.number_format($contents[$i+1])." ".$contents[$i].'</a></li>';
		}else{
			echo '<li>'.number_format($contents[$i+1])." ".$contents[$i].'</li>';			
		}
		$i++;
	}
	echo "</ul>";
?>