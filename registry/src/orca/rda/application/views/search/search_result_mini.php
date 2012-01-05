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
	
	//$numFound = $json->{'response'}->{'numFound'};
	$numFound = $json->{'response'}->{'numFound'};
	$timeTaken = $json->{'responseHeader'}->{'QTime'};
	$timeTaken = $timeTaken / 1000;
	
	//print_r($json->{'responseHeader'}->{'params'});
	
	$row = $json->{'responseHeader'}->{'params'}->{'rows'};
	$start = $json->{'responseHeader'}->{'params'}->{'start'};
	$end = $start + $row;
	
	$h_start = $start + 1;//human start
	$h_end = $end + 1;//human end
	
	if ($h_end > $numFound) $h_end = $numFound;
	
	$totalPage = ceil($numFound / $row);
	$currentPage = ceil($start / $row)+1;
?>
<div id="mini-inner">
		<?php

			echo '<div id="numFound" class="hide">'.$numFound.'</div>';
			if($numFound==0){
				$this->load->view('search/no_result');
			}
			echo '<a href="search#!/group='.$group.'" class="button-me">View Complete Search</a>';
			echo '<h1>'.$group.'</h1>';
			
			foreach($json->{'response'}->{'docs'} as $r)
			{
				//var_dump($r->{'description_value'});
				$type = $r->{'type'};
				$ro_key = $r->{'key'};
				$name = $r->{'listTitle'};
				$descriptions = array();if(isset($r->{'description_value'})) $descriptions = $r->{'description_value'};
				$description_type=array();if(isset($r->{'description_type'})) $description_type = $r->{'description_type'};
				$class = '';if(isset($r->{'class'})) $class = $r->{'class'};
				$type = '';if(isset($r->{'type'})) $type = strtolower($r->{'type'});
				
				$brief = '';$found_brief = false;
				$full = '';$found_full = false;
				foreach($description_type as $key=>$t){
					if($t=='brief' && !$found_brief) {
						$brief = $descriptions[$key];
						$found_brief = true;
					}elseif($t=='full' && !$found_full){
						$full = $descriptions[$key];
						$found_full = true;
					}
				}
				
				$spatial ='';$center = '';
				if(isset($r->{'spatial_coverage'})){
					$spatial = $r->{'spatial_coverage'};
					$center = $r->{'spatial_coverage_center'}[0];
				}
				$subjects='';
				if(isset($r->{'subject_value'})){
					$subjects = $r->{'subject_value'};
				}

				echo '<div class="search_item">';
				
				//echo get_cookie('show_icons');
				/*
				echo '<p class="hide key">'.$ro_key.'</p>';
				if(get_cookie('show_icons')=='yes'){
					switch($class){
						case "collection":echo '<img class="ro-icon" src="'.base_url().'img/icon/collections_32.png" title="Collection"/>';break;
						case "activity":echo '<img class="ro-icon" src="'.base_url().'img/icon/activities_32.png" title="Activity"/>';break;
						case "service":echo '<img class="ro-icon" src="'.base_url().'img/icon/services_32.png" title="Service"/>';break;
						case "party": 
									if($type=='person'){
										echo '<img class="ro-icon" src="'.base_url().'img/icon/party_one_32.png" title="Person"/>';
									}elseif($type=='group'){
										echo '<img class="ro-icon" src="'.base_url().'img/icon/party_multi_32.png" title="Group"/>';
									}
							break;
					}
				}*/
				$key_url =  base_url().'view/?key='.$ro_key;
				echo '<h2><a href="'.$key_url.'">'.$name.'</a></h2>';
				//echo '<h2><a href="#!/view/'.$ro_key.'">'.$name.'</a></h2>';
				
				//DESCRIPTIONS';
				
				if($found_brief){
					echo strip_tags($brief);
				}elseif($found_full){
					echo strip_tags($full);
				}
				
				echo '<hr/>';
				echo '</div>';
			}
			
			echo '<a href="javascript:void(0);" class="button-me">View Complete Search</a>'
		?>
</div>