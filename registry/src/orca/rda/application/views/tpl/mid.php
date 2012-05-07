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
			if(!isset($search_term))
			{
				$search_term='';
			}else{
				$search_term = urldecode(rawurldecode($search_term));
			}
		?>
		<div id="mid" class="clearfix">
			<div id="logo">
					<a href="<?php echo base_url();?>"><img src="<?php echo base_url();?>img/rda-design.png"/></a>
				</div>
			<div id="search-bar">
				
				<div id="search-wrapper" class="clearfix">
					<div class="ui-widget left-align"><input class="searchbox" id ="search-box" type="text" value="" name="query" ></div>
					<button class="searchbox_submit" id="search-button" title="Search Research Data Australia">search</button>
					<img src="<?php echo base_url();?>img/delete.png" id="clearSearch" class="hide" title="Clear Search"/>
					<img src="<?php echo base_url();?>img/ajax-loader.gif" id="loading" class="hide"/>
				</div>
				
			</div>
			<div id="placeholder">
				
				<p><a href="JavaScript:void(0);" id="advanced-search-button">Advanced Search</a></p>
			</div>
		</div>
		<?php
			if($this->input->cookie('advanced-search')!=''){
				if($this->input->cookie('advanced-search')=='open'){
					$class='';
				}else{
					$class='hide';
				}
			}else{
				$class='hide';
			}
		?>
		<div id="advanced" class="clearfix hide">
			<div id="advanced-spatial">
				<div id="spatialmap"></div>
				<div class="ui-widget-header">
				<span id="map-stuff">
					<button id="start-drawing">Start Drawing</button>
					<button id="clear-drawing">Clear Drawing</button>
					<button id="expand">Expand</button>
					<button id="collapse">Collapse</button>
					<input type="text" id="address"/>
					<button id="map-info">Info</button>
				</span>
					
					<span id="map-help-stuff">
						
					</span>
				</div>
			</div>
			<div id="spatial-info2" class="hide">
				<ul style="text-align:left">
				<li>To use the spatial search tool, click on 'Start Drawing' at the bottom of the map. Then left-click anywhere on the map, and release your mouse button. Next, drag your mouse and left-click and release again. This will create a rectangular search area on the map. You can use the 'Clear Drawing' button to clear your search area and start again.</li>
				<li>You can use the arrow found on the right hand side of the 'Start Drawing' button to expand the map.</li>
				<li>Spatial search results will be displayed in the results list and on the interactive map.</li>
				<li>Only those objects which have geospatial information associated with them will be returned as results from a Spatial search. Not all metadata Providers include geospatial information with their objects.</li>
				<li>Only the objects that are listed in the current search results view will appear on the map. Choose a results page number or click on '>' to move further down the results list.</li>
				</ul>
			</div>
			
			<div id="advanced-text">
				<p><b>Find </b>
					<select id="classSelect">
						<option value="All" selected="selected">All Records</option>
						<option value="collection">Collections</option>
						<option value="party">Parties</option>
						<option value="activity">Activities</option>
						<option value="service">Services</option>
					</select>
				that have:</p>
				<img src="<?php echo base_url();?>img/delete.png" id="close_advanced"/>
				<p><label>All of these words:</label><input class="search-input long" id="advanced-all" type="text" value=""/></p>
				<p><label title="You can do this in standard search by surrounding your phrase with quotes">This exact phrase:</label><input class="search-input long" id="advanced-exact" type="text" value=""/></p>
				<p><label title="You do this in standard search by typing OR between your alternate words.">One or more of these words:</label> <input class="search-input short" id="advanced-or1" type="text" value=""/> OR <input class="search-input short" id="advanced-or2" type="text" value=""/> OR <input class="search-input short" id="advanced-or3" type="text" value=""/></p>
				<p><label title="You can do this in standard search by adding a - (minus sign) to the beginning of the word you don't want.">But not these words:</label><input class="search-input long" id="advanced-not" type="text" value=""/></p>
				<p><img src="<?php echo base_url();?>/img/no.png" id="show-temporal-search" title="toggle to enable/disable temporal search"/> Restrict temporal range</p>
				<div id="temporal-search">
				<p><b>In the range:</b></p>
				<!-- <p>From: <input class="short" id="dateFrom" type="text" value="1544" title="a year from 1544 to 2011"/> To: <input class="short" id="dateTo" type="text" value="2011" title="a year from 1544 to 2011"/></p>-->
				<p>From: 
				<select id="dateFrom">
					<?php 
						for($i=1544;$i<2011;$i++){
							echo '<option value="'.$i.'">'.$i.'</option>';
						}
					?>
				</select>
				To: 
				<select id="dateTo">
					<?php 
						for($i=2011;$i>1545;$i--){
							echo '<option value="'.$i.'">'.$i.'</option>';
						}
					?>
				</select>
				<div id="date-slider"></div>
				<div class="clearfix"></div>
				</div>
				<p>
					<button id="search_advanced">Search</button>
					<a href="javascript:void(0);" id="clear_advanced">Clear Search</a>
				</p>
			</div>
			
			<div class="clearfix"></div>
		</div>
		
		<div id="content" class="clearfix">
		<div id="cover"></div>