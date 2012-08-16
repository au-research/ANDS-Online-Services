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
	$realNumFound = $json->{'response'}->{'numFound'};
	$numFound = $json_tab->{'response'}->{'numFound'};
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
		<?php
			echo '<div class="toolbar clearfix">';

			echo '<div id="realNumFound" class="hide">'.($realNumFound).'</div>';



			//echo $this->input->cookie('facets');

			$class='';
			if($this->input->cookie('facets')!=''){
				if($this->input->cookie('facets')=='yes'){
					$class='ui-icon-arrowthickstop-1-w';
				}else{
					$class='ui-icon-arrowthickstop-1-e';
				}
			}else{
				$class='ui-icon-arrowthickstop-1-w';
			}

			echo '<div class="ui-state-default ui-corner-all show-hide-facet"><span class="ui-icon '.$class.'" id="toggle-facets" title="Show/Hide Facet"></span></div>';
			//echo '<a href="JavaScript:void(0);" id="hide-facets">Expand</a><a href="JavaScript:void(0);" id="show-facets">Collapse (Show Filters)</a>';

			echo '<div class="result">';
			echo ''.number_format($realNumFound).' results ('.$timeTaken.' seconds)';
			echo '</div>';

			$this->load->view('search/pagination');

			echo '</div>';

			if($realNumFound==0){
				$this->load->view('search/no_result');
			}

			foreach($json->{'response'}->{'docs'} as $r)
			{
				$type = $r->{'type'};
				$ro_key = $r->{'key'};
				$name = $r->{'list_title'};
				if($name=='')$name='(no name/title)';
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
				if(isset($r->{'subject_value_resolved'})){
					$subjects = $r->{'subject_value_resolved'};
				}

				echo '<div class="search_item" itemscope itemType="http://schema.org/Thing">';

				//echo get_cookie('show_icons');
				echo '<p class="hide key">'.$ro_key.'</p>';
				if(get_cookie('show_icons')=='yes'){
					switch($class){
						case "collection":echo '<img itemprop="image" class="ro-icon" src="'.base_url().'img/icon/collections_32.png" title="Collection" alt="Collection"/>';break;
						case "activity":echo '<img itemprop="image" class="ro-icon" src="'.base_url().'img/icon/activities_32.png" title="Activity" alt="Activity"/>';break;
						case "service":echo '<img itemprop="image" class="ro-icon" src="'.base_url().'img/icon/services_32.png" title="Service" alt="Service"/>';break;
						case "party":
									if($type=='person'){
										echo '<img itemprop="image" class="ro-icon" src="'.base_url().'img/icon/party_one_32.png" title="Person" alt="Person"/>';
									}elseif($type=='group'){
										echo '<img itemprop="image" class="ro-icon" src="'.base_url().'img/icon/party_multi_32.png" title="Group" alt="Group"/>';
									}
							break;
					}
				}
				$theGroup = getInstitutionPage($r->{'group'});
				
				if($theGroup==$ro_key){
					$key_url = base_url().'view/group/?group='.rawurlencode($theGroup). '&groupName='.urlencode($r->{'group'});
				}			
				else if ($r->url_slug)
				{
					$key_url = base_url().$r->{'url_slug'};
				}
				else{
					$key_url =  base_url().'view/?key='.urlencode($ro_key);
				}
				//echo $key_url;
				echo '<h2 itemprop="name"><a itemprop="url" href="'.$key_url.'">'.$name.'</a></h2>';

				//echo '<pre>';

				if(isset($r->{'alt_listTitle'})){
					echo '<div class="alternatives">';
					foreach($r->{'alt_listTitle'} as $listTitle){
						echo '<p class="alt_listTitle">'.$listTitle.'</p>';
					}
					echo '</div>';
				}
				//echo '</pre>';
				//echo '<h2><a href="#!/view/'.$ro_key.'">'.$name.'</a></h2>';

				//DESCRIPTIONS';
				echo '<p itemprop="description">';
				if($found_brief){
					echo strip_tags(htmlspecialchars_decode($brief));
				}elseif($found_full){
					echo strip_tags(htmlspecialchars_decode($full));
				}
				echo '</p>';

				if($spatial){
					echo '<ul class="spatial">';
						foreach($spatial as $s){
							echo '<li>'.$s.'</li>';
						}
					echo '</ul>';
					echo '<a class="spatial_center">'.$center.'</a>';
				}

				if(get_cookie('show_subjects')=='yes'){
					if($subjects){
						echo '<div class="subject-container">';
						echo '<ul class="subjects">';
						foreach($subjects as $s){
							echo '<li><a href="javascript:void(0);" class="contentSubject" id="'.$s.'">'.$s.'</a></li>';
						}
						echo '</ul>';
						echo '</div>';
					}
				}
				echo '</div>';
			}
			echo '<div class="toolbar clearfix bottom-corner">';
			if(displaySubscriptions() )
			{
				echo "<div id='subscriptions'><div class='rss_icon'></div>Subscribe to this web feed. <a href='".base_url()."search/rss/".$queryStr."&subscriptionType=rss' title='Stay informed with RSS when any updates are made to this search query.' class='tiprss'>RSS</a>/<a href='".base_url()."search/atom/".$queryStr."&subscriptionType=atom' title='Stay informed with ATOM when any updates are made to this search query.' class='tiprss'>ATOM</a></div>";
			}
			$this->load->view('search/pagination');
			echo '</div>';
		?>