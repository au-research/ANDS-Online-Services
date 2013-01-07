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
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=drawing&amp;sensor=false"></script>
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
				<input type="text" id="search_box" name="s" value="Search eg. Something Something Something... Dark Side" onblur="if(this.value.length == 0) this.value='Search eg. Something Something';" onclick="if(this.value == 'Search eg. Something Something') this.value='';" />
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
				</div>
			</form>
		</div><!-- inner -->
	</div><!-- search -->
	<div id="searchmap"></div>
	<div class="container">