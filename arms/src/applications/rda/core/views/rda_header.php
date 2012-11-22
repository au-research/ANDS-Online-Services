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
<link rel="stylesheet" href="<?php echo asset_url('css/jquery-ui.css', 'core');?>" type="text/css" media="screen" />

<!-- ANDS Less file and general styling correction-->
<link href="<?php echo asset_url('less/ands.less', 'core');?>" rel="stylesheet/less" type="text/css">

</head>
<body>
	<div class="header">
		<div class="head">
			<a href="#" class="logo"><img src="<?php echo asset_url('images/logo.png','core');?>" alt="" /></a>
			<div class="tagline">
				<span>Research Data</span> Australia
			</div><!-- tagline -->
			<ul class="top_nav">
				<li><a href="#">Home</a></li>
				<li><a href="#">About</a></li>
				<li><a href="#">Collections</a></li>								
				<li><a href="#">Parties</a></li>
				<li><a href="#">Activities</a></li>
				<li><a href="#">Services</a></li>
				<li><a href="#">Topics</a></li>
			</ul><!-- top_nav -->
			<div class="clear"></div>
		</div><!-- head -->
	</div><!-- header -->
	<div class="search">
		<div class="inner">
			<form action="/" method="post">
				<input type="text" name="s" value="Search eg. Something Something" onblur="if(this.value.length == 0) this.value='Search eg. Something Something';" onclick="if(this.value == 'Search eg. Something Something') this.value='';" />
				<div class="text_select">
						<input type="text" name="Subject" autocomplete="off" />
						<span class="default_value">Subject</span>				
						<ul>
							<li>Option 1</li>
							<li>Option 2</li>							
							<li>Option 3</li>							
						</ul>
				</div><!-- text_select -->
				<a href="#" class="search_map">Map</a>
				<div class="clear buttons">
					<a href="#" id="ad_st">Advanced Search</a>
					<a href="#">What can I Search for</a>
				</div>
			</form>
		</div><!-- inner -->
		<div class="advanced_search">
			<div class="adv_inner">
				<form action="/" method="post">
				<div class="left">
					<img src="<?php echo asset_url('images/t/map.jpg', 'core');?>" alt="" />
					<div class="draw_box">
						<a href="#" class="draw">Start drawing</a>
						<a href="#" class="play"></a>
						<span class="draw_input"><input type="text" name="draw_input" /></span>
						<a href="#" class="info"></a>
						<div class="clear"></div>	
					</div>
				</div><!-- left -->	
				<div class="right">
					<p>Find  
						<select id="record" name="record">
							<option>Option 1</option>
							<option>Option 2</option>
							<option>Option 3</option>
						</select>
					   that have:
					</p>
					<div class="inputs">
						<label for="words">All of these words:</label>
						<input type="text" name="words" id="words" /> 
					</div><!-- inputs -->	
					<div class="inputs">
						<label for="more_words">One or more of these words:</label>
						<input type="text" name="more_words" id="more_words" /> 
					</div><!-- inputs -->	
					<div class="inputs">
						<label for="words_ex">But not these words:</label>
						<input type="text" name="words_ex" id="words_ex" class="s_inputs" /> 
						<span class="or">OR</span>
						<input type="text" name="words_ex" class="s_inputs" /> 
						<span class="or">OR</span>
						<input type="text" name="words_ex" class="s_inputs" /> 						
					</div><!-- inputs -->
					<div class="range_slider_wrap">
						<p><input type="checkbox" name="rst_range" id="rst_range" value="1" /><label for="rst_range">Restrict temporal range</label></p>
						<p>In the range: From: 
							<select id="range_l" name="range_l">
								<option>1544</option>
								<option>1545</option>
								<option>1546</option>
							</select>                
							To:
							<select id="range_r" name="range_r">
								<option>2011</option>
								<option>2012</option>
								<option>2013</option>
							</select>   							
						</p>
						<div id="range_slider"></div>
					</div><!-- range_slider -->	
					<div class="sbuttons">
						<input type="submit" value="Start Search" /> 	
						<a href="#" id="clear_search">Clear Search</a>
					</div>
				</div><!-- right -->
				</form>	
				<div class="clear"></div>			
			</div><!-- adv_inner -->
		</div><!-- advanced_search -->
	</div><!-- search -->