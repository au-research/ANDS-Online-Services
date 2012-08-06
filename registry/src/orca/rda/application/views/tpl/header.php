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
	if(isset($_SERVER['HTTPS'])){
		redirect(current_url());
	}
	$md_title = 'Research Data Australia';
	$md_description = 'Research Data Australia is a mesh of searchable web pages describing (and where possible linking to) Australian research data collections. Research Data Australia is provided by the Australian National Data Service (ANDS).';
	$md_image = base_url() . 'img/rda-design.png';
	if(isset($title))$md_title = $title .' - Research Data Australia';
	if(isset($description))$md_description = htmlentities($description);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
	<title><?php echo $md_title;?></title>
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<meta property="og:title" content="<?php echo $md_title;?>" />
	<meta property="og:description" content="<?php echo $md_description;?>" />
	<meta property="og:image" content="<?php echo $md_image;?>"/>

	<meta name="title" content="<?php echo $md_title;?>"/>
	<meta name="description" content="<?php echo $md_description;?>"/>

	<?php if ($user_agent!='Internet Explorer'):?>
    	<link href="<?php echo base_url();?>css/superfish.css" media="screen" type="text/css" rel="stylesheet">
	<?php endif;?>


	<link href="<?php echo base_url();?>css/reset.css" media="reset" type="text/css" rel="stylesheet">
	<link type="text/css" href="<?php echo base_url();?>css/smoothness/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo base_url();?>css/tipsy.css" rel="stylesheet" />
	<link href="<?php echo base_url();?>css/screen.css" type="text/css" rel="stylesheet">
	<link href="<?php echo base_url();?>css/print.css" media="print" type="text/css" rel="stylesheet">
	<?php if ($user_agent=='Internet Explorer'):?>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/ie.superfish.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/ie.screen.css" />
	<?php endif;?>

	<link rel="stylesheet" href="<?php echo base_url();?>css/jquery.fancybox.css?v=2.0.6" type="text/css" media="screen" />

	<link rel="stylesheet" href="<?php echo base_url();?>js/qtip/jquery.qtip.css" type="text/css" media="screen" />



	<?php if($this->config->item('GA_enabled')):?>
	<script type="text/javascript">
	  var ga_code = "<?php echo $this->config->item('GA_code');?>";
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', ga_code]);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
	<?php endif;?>

</head>

<body>
<div class="hide">Research Data Australia</div>
	<div id="container">

		<div id="header" class="no_print">
			<div class="top-menu-cover">
				<a href="http://ands.org.au"><img src="<?php echo site_url('img/ands_logo_white.png');?>" id="ands-logo-white" alt="ANDS Logo"/></a>
			<ul class="sf-menu">
				<li><?php echo anchor('','Home');?></li>
				<li><?php echo anchor('browse/', 'Browse');?></li>
				<li><?php echo anchor('search/browse/All/collection', 'Collections');?></li>
				<li><?php echo anchor('search/browse/All/party', 'Parties');?></li>
				<li><?php echo anchor('search/browse/All/activity', 'Activities');?></li>
				<li><?php echo anchor('search/browse/All/service', 'Services');?></li>
				<li><?php echo anchor('topic', 'Topics');?></li>
				<li><?php echo anchor('home/about','About');?></li>
				<li><?php echo anchor('home/contact','Contact');?></li>
			</ul>
			</div>
		</div>
		<span class="hide" id="rda_activity_name"><?php echo (isset($activity_name) ? $activity_name : 'unknown'); ?></span>