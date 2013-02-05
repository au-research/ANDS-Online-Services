<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>
	<?php
		if(isset($title)){
			echo $title;
		}else{
			echo 'Research Data Australia';
		}
	?>
</title>

<!-- Zoo Stylesheets Untouched -->
<link rel="stylesheet" href="<?php echo asset_url('style.css','core');?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo asset_url('css/ands-theme/jquery-ui-1.10.0.custom.min.css', 'core');?>" type="text/css" media="screen" />

<link rel="stylesheet" href="<?php echo asset_url('css/flexslider.css', 'core');?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo asset_url('css/ui.dynatree.css', 'core');?>" type="text/css" media="screen" />

<!-- ANDS Less file and general styling correction-->
<link href="<?php echo asset_url('less/ands.less', 'core');?>" rel="stylesheet/less" type="text/css">

<!-- Library files -->
<link rel="stylesheet" href="<?php echo asset_url('lib/qtip2/jquery.qtip.min.css', 'base');?>" type="text/css">

<link rel="stylesheet" href="<?php echo asset_url('lib/jQRangeSlider/css/iThing.css', 'base');?>" type="text/css" media="screen" > 

</head>
<body>
	<div class="header">
		<div class="head">
			<a href="<?=base_url('');?>" class="logo"><img src="<?php echo asset_url('images/logo.png','core');?>" alt="Research Data Australia Home Page Link (brought to you by ANDS)" /></a>
			<div class="tagline">
				<span>Research Data</span> Australia
			</div><!-- tagline -->
			<ul class="top_nav">
				<li><a href="<?=base_url('');?>">Home</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=collection');?>">Collections</a></li>								
				<li><a href="<?=base_url('search/#!/q=/tab=party');?>">Parties</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=activity');?>">Activities</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=service');?>">Services</a></li>
				<li><a href="<?=base_url("topic");?>">Topics</a></li>
				<li><a href="<?=base_url('home/contact');?>">Contact Us</a></li>
			</ul><!-- top_nav -->
			<div class="clear"></div>
		</div><!-- head -->
	</div><!-- header -->
	<div class="search">
		<div class="inner">
			<form action="/" method="post">
				<input type="text" id="search_box" name="s" value="" placeholder="Search" onclick="if(this.value == 'Search eg. Something Something') this.value='';" />
				<div class="text_select">
						<input type="text" name="Subject" autocomplete="off" />
						<span class="default_value">Subject</span>				
						<ul>
							<li>Option 1</li>
							<li>Option 2</li>							
							<li>Option 3</li>							
						</ul>
				</div><!-- text_select -->
				<a href="javascript:;" class="search_map" id="search_map_toggle">Map</a>
				<div class="clear buttons">
					<a href="#" id="ad_st">Advanced Search</a>
				</div>
			</form>
		</div><!-- inner -->

		<div class="advanced_search">
			<div class="adv_inner">
				<form action="/" method="post">
					<p>Find  
						<select id="record_tab" name="record">
							<option value="collection">Collection</option>
							<option value="party">Parties</option>
							<option value="activity">Activities</option>
							<option value="service">Services</option>
						</select>
					   that have:
					</p>
					<div class="inputs">
						<label for="words">All of these words:</label>
						<input type="text" name="words" class="adv_all b_inputs" /> 
					</div><!-- inputs -->	
					<div class="inputs">
						<label for="more_words">One or more of these words:</label>
						<input type="text" name="more_words" class="adv_input b_inputs" /> 
					</div><!-- inputs -->	
					<div class="inputs">
						<label for="words_ex">But not these words:</label>
						<input type="text" name="words_ex" id="words_ex" class="s_inputs adv_not" /> 
						<span class="or">OR</span>
						<input type="text" name="words_ex" class="s_inputs adv_not" /> 
						<span class="or">OR</span>
						<input type="text" name="words_ex" class="s_inputs adv_not" /> 						
					</div><!-- inputs -->
					<div class="range_slider_wrap">
						<p><input type="checkbox" name="rst_range" id="rst_range" value="1" /><label for="rst_range">Restrict temporal range</label></p>
						<p><br/></p>
						<div id="slider"></div>
						<!-- <div id="range_slider"></div> -->
					</div><!-- range_slider -->	
					<div class="sbuttons">
						<input type="submit" value="Start Search" id="adv_start_search"/> 	
						<a href="#" id="clear_search">Clear Search</a>
					</div>
				
				</form>	
				<div class="clear"></div>			
			</div><!-- adv_inner -->
		</div><!-- advanced_search -->
	</div><!-- search -->

	<div class="container">