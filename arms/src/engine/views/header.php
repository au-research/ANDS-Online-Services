<?php
/**
 * Core Template File (header)
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/
 * @package ands/
 * 
 */
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Twitter Bootstrap Styles -->
    <link href="<?php echo base_url();?>assets/lib/twitter_bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/lib/twitter_bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- ANDS Less file and general styling correction-->
    <link href="<?php echo base_url();?>assets/css/base.css" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/less/arms.less" rel="stylesheet/less" type="text/css">

    <!-- Libraries Styles-->
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/chosen/chosen.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/bootstrap_toggle_button/jquery.toggle.buttons.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/qtip2/jquery.qtip.min.css">

    

    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- The fav and touch icons -->
    <!--link rel="shortcut icon" href="ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png"-->
  </head>

<body<?php echo(array_search('prettyprint', $js_lib) !== FALSE ? ' onload="prettyPrint();"' : '');?>>

<div class="container-fluid" id="topbar">
    <div class="row-fluid">
      <div class="span2" id="logo">
        <img src="<?php echo base_url();?>/assets/img/ands_logo_white.png" alt="ANDS Logo White"/>
      </div>
      <div class="span10" id="main-nav">
        <ul>
        	
    	<?php if($this->user->hasFunction('REGISTRY_USER')): ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">My Records <b class="caret"></b></a>
            <ul class="dropdown-menu sub-menu pull-right">
              <li class=""><?php echo anchor('registry_object/manage', 'Manage My Records');?></li>
              <li class=""><a href="#">Add My Records</a></li>
              <li class=""><a href="#">Publish My Records</a></li>
            </ul>
          </li>
     	<?php endif; ?>
          
        <?php if($this->user->hasFunction('REGISTRY_USER')): ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">My Datasources <b class="caret"></b></a>
            <ul class="dropdown-menu sub-menu pull-right">
              <li class=""><?php echo anchor('data_source/manage', 'Manage My Datasources');?></li>
              <li class=""><a href="#">Datasources Tools</a></li>
            </ul>
          </li>
        <?php endif; ?>

      <?php if($this->user->hasFunction('PUBLIC')):?>
      <?php //if($this->user->hasFunction('VOCAB_USER')): ?>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Vocabularies <b class="caret"></b></a>
        <ul class="dropdown-menu sub-menu pull-right">
          <li class=""><?php echo anchor('vocab_service/', 'Browse Vocabularies');?></li>
          <?php if($this->user->loggedIn()):?>
            <li class=""><?php echo anchor('vocab_service/addVocabulary', 'Publish');?></li>
          <?php else:?>
            <li class=""><?php echo anchor('vocab_service/publish', 'Publish');?></li>
          <?php endif;?>
          <li class=""><?php echo anchor('vocab_service/support', 'Support');?></li>
          <li class=""><?php echo anchor('vocab_service/about', 'About');?></li>
        </ul>
      </li>
      <?php endif;?>
          
        <?php if($this->user->hasFunction('AUTHENTICATED_USER')): ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Tools <b class="caret"></b></a>
            <ul class="dropdown-menu sub-menu pull-right">
            	
            	<?php if (($this->user->hasFunction('DOIS_USER') || $this->user->hasFunction('AUTHENTICATED_USER')) && mod_enabled('mydois')): ?>
            		<li class=""><?php echo anchor('mydois/', 'DOI Query Tool');?></li>
            	<?php endif; ?>
            	<?php if (($this->user->hasFunction('PUBLIC')) && mod_enabled('abs_sdmx_querytool')): ?>
            		<li class=""><?php echo anchor('abs_sdmx_querytool/', 'ABS SDMX Query Tool');?></li>
            	<?php endif; ?>
            	<?php if ($this->user->hasFunction('AUTHENTICATED_USER')): ?>
              		<li class=""><?php echo anchor('location_capture_widget/', 'Location Capture Widget');?></li>
              	<?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>
          
        <?php if($this->user->hasFunction('REGISTRY_USER')): ?>
          <li>
            <a href="javascript:;" id="main-nav-search"><i class="icon-search icon-white"></i></a>
          </li>
	    <?php endif; ?>
	      
	    <?php if($this->user->hasFunction('PUBLIC')): ?>
          <li>
            <a href="javascript:;" id="main-nav-user-account" title="aaa"><i class="icon-user icon-white"></i></a>
          </li>
        <?php endif; ?>
          
        </ul>
      </div>

      <div class="hide" id="user-account-info">
      	<?php if($this->user->loggedIn()): ?>
        	Logged in as <?=$this->user->name();?> <br/>
        	 <?php echo anchor('auth/logout', 'Logout'); ?>
		  <?php else: ?>
        <?php echo anchor('auth/login', 'Login');?>
      <?php endif;?>
      </div>
      
    </div>
</div>

