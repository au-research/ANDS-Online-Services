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
<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>
<?php 
	$partners = array();
	$keys = array();
	foreach($json->{'response'}->{'docs'} as $d){
		$key = $d->{'key'};
		$keys[] = $key;
		if(isset($d->{'description_type'}))
		{
		foreach($d->{'description_type'} as $index=>$type){
			if($type=='logo') $partners[$key]['logo']=$d->{'description_value'}[$index];
			if($type=='brief') {
				$partners[$key]['full']='<h3><a href="search#!/q='.str_replace('-','',trim($d->{'display_title'})).'/tab=activity">'.$d->{'display_title'}.'</a></h3>'
											.''.
											$d->{'description_value'}[$index].
											'<p><a href="'.trim($d->{'location'}[0]).'" title="Visit Partner Portal">'.$d->{'location'}[0].'</a></p>';
			}
			$partners[$key]['url']=$d->{'location'}[0];
		}
	}
	}
?>
<div id="search-result">

<div class="box shadow" itemscope itemtype="http://schema.org/Article">
	<h1 class="branding" itemprop="name">Research Data Australia is a discovery service for Australian research data.</h1>
	<div class="clearfix"></div>
	<div class="hp-left" itemprop="articleBody">
		<div class="hp-class-items">
		<h2>What's in Research Data Australia</h2>
			<div class="hp-class-item clearfix" id="collection">
				<img src="<?php echo base_url();?>img/icon/collections_64_hp.png" alt="collection"/>
				<div>
					<p>Research datasets or collections of research materials.</p>
					<p><a href="search#!/tab=collection">Browse All Collections <span id="hp-browse-collection"></span></a></p>
				</div>
			</div>
			<div class="hp-class-item clearfix" id="party">
				<img src="<?php echo base_url();?>img/icon/party_multi_64_hp.png" alt="party"/>
				<div>
					<p>Researchers or research organisations that create or maintain research datasets or collections.</p>
					<p><a href="search#!/tab=party">Browse All Parties <span id="hp-browse-party"></span></a></p>
				</div>
			</div>
			<div class="hp-class-item clearfix" id="service">
				<img src="<?php echo base_url();?>img/icon/services_64_hp.png" alt="service"/>
				<div>
					<p>Services that support the creation or use of research datasets or collections.</p>
					<p><a href="search#!/tab=service">Browse All Services <span id="hp-browse-service"></span></a></p>
				</div>
			</div>
			<div class="hp-class-item clearfix" id="activity">
				<img src="<?php echo base_url();?>img/icon/activities_64_hp.png" alt="activity"/>
				<div>
					<p>Projects or programs that create research datasets or collections.</p>
					<p><a href="search#!/tab=activity">Browse All Activities <span id="hp-browse-activity"></span></a></p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="hp-right" itemprop="articleBody">
		<h2>Spotlight on research domains</h2>
		<p class="alt_listTitle">More information on research data infrastructure for specific domains:</p>
			<div id="carousel">
				<div class="clearfix">
					<div class="prev browse left"></div>
					<div id="scrollable">     
					   <!-- root element for the items -->
					   <div class="items" id="items" style="left: 0px; ">
					   		<?php
					   			foreach($keys as $key){
					   				$image = '';
					   				if(isset($partners[$key]['logo'])) {
					   					$image = $partners[$key]['logo'];
					   					echo '<img src="'.$image.'" alt="'.$key.'"/>';
					   				}
					   			}
					   		?>
					   </div>
					</div>
					<div class="next browse right"></div>
					</div>
					<div class="clearfix"></div>
			<div id="display-wrapper">
				<div id="display-here"></div>
			</div>
		</div>
	</div>
	
	<div class="clearfix"></div>
	<div style="float:left;">
		<div style="float:left;margin:0px 5px;">
			<img src="<?php echo base_url();?>img/feed_icon.png"/><img src="<?php echo base_url();?>img/twitter_icon.png"/>
		</div>
		<div style="float:left;line-height:32px">
			<h4 style="margin-top:3px;">RSS,ATOM and Twitter feeds are now available. <?php echo anchor('home/feeds','Learn more here....');?></h4>
		</div>
	</div>
	<div style="float:right;">
		 <!-- AddToAny BEGIN -->   
        <p>
        <div class="a2a_kit a2a_default_style no_print" style="position:relative;clear:both;">
        <a class="a2a_dd" href="http://www.addtoany.com/share_save">Share</a>
        <span class="a2a_divider"></span>
        <a class="a2a_button_linkedin"></a>
        <a class="a2a_button_facebook"></a>
        <a class="a2a_button_twitter"></a>
        <a class="a2a_button_wordpress"></a>
        <a class="a2a_button_stumbleupon"></a>
        <a class="a2a_button_delicious"></a>
        <a class="a2a_button_digg"></a>
        <a class="a2a_button_reddit"></a>
        <a class="a2a_button_email"></a>
        </div>
        <script type="text/javascript">
        var a2a_config = a2a_config || {};
        a2a_config.linkname = "Research Data Australia";
        </script>
        <script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
        </p>
        <!-- AddToAny END -->  
	</div>
	<div class="clearfix"></div>
</div>


<div class="box clearfix shadow" itemscope itemtype="http://schema.org/Article">	
	<h2 itemprop="name">Who contributes to Research Data Australia</h2>
	<div id="hp-stat" itemprop="articleBody">
		Loading Stats...
	</div>
</div>

<div class="box shadow" itemscope itemtype="http://schema.org/Article">
	<center><img src="<?php echo base_url();?>img/DIISRTE_stacked.jpg" style="height:200px;" alt="DIISRTE Logo"/></center>
	<p style="text-align:center" itemprop="articleBody">
		ANDS is supported by the Australian Government through the <a href="http://ncris.innovation.gov.au/">National Collaborative Research Infrastructure Strategy Program</a> and the Education Investment Fund (EIF) Super Science Initiative.
	</p>
	
</div>


<div class="hide">
	<?php 
	foreach($keys as $key){
		$d = '';
		if(isset($partners[$key]['full'])){
			$d = $partners[$key]['full'];
   			echo '<div name="'.urldecode($key).'">'.$d.'</div>';
		}
	}
	?>
</div>
</div>
<?php $this->load->view('tpl/footer');?>