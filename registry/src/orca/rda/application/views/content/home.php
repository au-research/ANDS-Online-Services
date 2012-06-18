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
		foreach($d->{'description_type'} as $index=>$type){
			if($type=='logo') $partners[$key]['logo']=$d->{'description_value'}[$index];
			if($type=='full' || $type=='brief') {
				$partners[$key]['full']='<h1><a href="search#!/q='.str_replace('-','',trim($d->{'displayTitle'})).'/tab=activity">'.$d->{'displayTitle'}.'</a></h1>'
											.'<p><a href="'.$d->{'location'}[0].'" title="Visit Partner Portal">'.$d->{'location'}[0].'</a></p>'
											.$d->{'description_value'}[$index];
			}
			$partners[$key]['url']=$d->{'location'}[0];
		}
	}
?>
<div id="search-result">

<div class="box">
	<div class="hp-left" itemscope itemtype="http://schema.org/Article">
		<h2 itemprop="name">What's in Research Data Australia</h2>
		<div class="hp-class" itemprop="articleBody">

			<div class="hp-icons">
				<a href="search#!/tab=collection"><img src="<?php echo base_url();?>img/icon/collections_64.png" class="active" id="collection"/></a>
				<a href="search#!/tab=party"><img src="<?php echo base_url();?>img/icon/party_multi_64.png" id="party"/></a>
				<a href="search#!/tab=service"><img src="<?php echo base_url();?>img/icon/services_64.png" id="service"/></a>
				<a href="search#!/tab=activity"><img src="<?php echo base_url();?>img/icon/activities_64.png" id="activity"/></a>
			</div>


				<div id="hp-content-collection" class="hp-icon-content">
					<p>Research datasets or collections of research materials.</p>
					<p><a href="search#!/tab=collection">Browse All Collections <span id="hp-browse-collection"></span></a></p>
				</div>
				<div id="hp-content-party" class="hide hp-icon-content">
					<p>Researchers or research organisations that create or maintain research datasets or collections.</p>
					<p><a href="search#!/tab=party">Browse All Parties <span id="hp-browse-party"></span></a></p>
				</div>
				<div id="hp-content-service" class="hide hp-icon-content">
					<p>Services that support the creation or use of research datasets or collections.</p>
					<p><a href="search#!/tab=service">Browse All Services <span id="hp-browse-service"></span></a></p>
				</div>
				<div id="hp-content-activity" class="hide hp-icon-content">
					<p>Projects or programs that create research datasets or collections.</p>
					<p><a href="search#!/tab=activity">Browse All Activities <span id="hp-browse-activity"></span></a></p>
				</div>



		</div>
		<div class="clearfix"></div>
		<div id="hp-stat">
			Loading Stats...
		</div>
		<div class="clearfix"></div>

	</div>

	<div class="hp-right">
		<h2>NCRIS Partner Spotlight</h2>
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
</div>





<div class="box">
	<center><img src="<?php echo base_url(); ?>img/DIISRTE_stacked.jpg"/></center>
	<p>
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