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

// Page header style is blue if the environment is not Production
if($this->config->item('environment_name'))
{
  $environment_name = $this->config->item('environment_name');
  $environment_colour = $this->config->item('environment_colour');
  $environment_header_style = " style='border-top: 4px solid " . ($env_colour ?: "#0088cc") . ";'";
}
else
{
  $environment_name = '';
  $environment_colour = '';
  $environment_header_style = '';
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title><?php echo $title;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Twitter Bootstrap Styles -->
    <link href="<?php echo base_url();?>assets/lib/twitter_bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/lib/twitter_bootstrap/css/bootstrap-responsive.css" rel="stylesheet">



    <!-- ANDS print stylesheet-->
    <link href="<?php echo base_url();?>assets/css/print.css" rel="stylesheet/less" type="text/css" media="print">

    <!-- Libraries Styles-->
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/chosen/chosen.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/bootstrap_toggle_button/jquery.toggle.buttons.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/qtip2/jquery.qtip.min.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/lib/youtubepopup/jquery-ui.css">
    <!-- unicorn -->
    <link href="<?php echo base_url();?>assets/lib/unicorn_styles/css/uniform.css" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/lib/unicorn_styles/css/unicorn.main.css" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/lib/unicorn_styles/css/unicorn.grey.css" rel="stylesheet">
    <!-- ANDS Less file and general styling correction-->
    <link href="<?php echo base_url();?>assets/css/base.css" rel="stylesheet">
    <link href="<?php echo base_url();?>assets/less/arms.less" rel="stylesheet/less" type="text/css">

    <!-- additional styles -->
 
    <?php
      if(isset($less)){
        foreach($less as $s){
          echo '<link href="'.asset_url('less/'.$s.'.less').'" rel="stylesheet/less" type="text/css">';
        }
      }
    ?>

    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- The fav and touch icons -->
    <!--link rel="shortcut icon" href="ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png"-->
  </head>

<body<?php echo(array_search('prettyprint', $js_lib) !== FALSE ? ' onload="prettyPrint();"' : '');?>>



    <div id="header" <?=$environment_header_style;?>">
      <a href="<?php echo base_url();?>" title="Back to ANDS Online Services Home" tip="Back to ANDS Online Services Home" my="center left" at="center right">
        <img src="<?php echo base_url();?>/assets/img/ands_logo_white.png" alt="ANDS Logo White"/> 
      </a>
      <strong style="color:<?=$environment_colour;?>;"><?=$environment_name;?></strong>
    </div>
    

    <?php try { $this->user; ?>
      <div id="user-nav" class="navbar navbar-inverse">

            <ul class="nav btn-group">
            
              <?php if($this->user->hasFunction('REGISTRY_USER') && mod_enabled('registry')): ?>
                <li class="btn btn-inverse dropdown">
                  <a class="dropdown-toggle" data-toggle="dropdown" href="#">My Data <b class="caret"></b></a>
                  <ul class="dropdown-menu pull-right">
                    <li class=""><?php echo anchor('data_source/manage', 'Manage My Data Sources');?></li>
                    <li class=""><?php echo anchor('registry_object/add', '<i class="icon icon-plus"></i> Add New Record');?></li>
                    <li class="divider"></li>
                    <li class=""><?php echo anchor(portal_url(), '<i class="icon-globe icon"></i> Research Data Australia',array("target"=>"_blank"));?></li>
                    <li class="divider"></li>
                    <li class=""><?php echo anchor('registry_object/gold_standard', 'Gold Standard Records');?></li>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if($this->user->hasFunction('AUTHENTICATED_USER') && (mod_enabled('pids') || mod_enabled('mydois'))): ?>
                <li class="btn btn-inverse dropdown">
                  <a class="dropdown-toggle" data-toggle="dropdown" href="#">Identifiers <b class="caret"></b></a>
                  <ul class="dropdown-menu pull-right">

                    <?php if (mod_enabled('pids') && $this->config->item('gPIDS_URL_PREFIX')): ?>
                      <li class=""><?php echo anchor($this->config->item('gPIDS_URL_PREFIX'), 'My Persistent Identifiers (PIDS) <i class="icon-share"></i>', array("target"=>"_blank"));?></li>
                    <?php endif; ?>

                    <?php if ($this->user->hasFunction('DOI_USER') && mod_enabled('mydois')): ?>
                      <li>
                        <?php echo anchor('mydois', 'My Digital Object Identifiers (DOI)');?>
                      </li>
                    <?php endif; ?>

                  </ul>
                </li>
              <?php endif; ?>

              <?php if($this->user->hasFunction('PUBLIC')):?>
              <li class="btn btn-inverse dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Vocabularies <b class="caret"></b></a>
                <ul class="dropdown-menu pull-right">
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
          <li class="btn btn-inverse dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Tools <b class="caret"></b></a>
            <ul class="dropdown-menu pull-right">
              
              <?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
                  <li class=""><?php echo anchor('administration/', 'Administration Panel');?></li>
              <?php endif; ?>
              <?php if (($this->user->hasFunction('PUBLIC')) && mod_enabled('abs_sdmx_querytool')): ?>
                <li class=""><?php echo anchor('abs_sdmx_querytool/', 'ABS SDMX Query Tool');?></li>
              <?php endif; ?>
              <?php if ($this->user->hasFunction('AUTHENTICATED_USER')): ?>
                  <li class=""><?php echo anchor('location_capture_widget/', 'Location Capture Widget');?></li>
                  <li class=""><?php echo anchor('vocab_widget/', 'Vocabulary Service Widget');?></li>
                  <li class=""><?php echo anchor('services/', 'Web Services');?></li>
                <?php endif; ?>
              <?php if ($this->user->hasFunction('PORTAL_STAFF')): ?>
                  <li class=""><?php echo anchor('spotlight/', 'Spotlight CMS Editor');?></li>
              <?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>
          
        <?php if($this->user->hasFunction('REGISTRY_USER') && mod_enabled('registry')): ?>
          <form class="navbar-search pull-left hide" id="navbar-search-form">
            <input type="text" class="search-query" placeholder="Search">
          </form>
          <li class="btn btn-inverse">

            <a href="javascript:;" id="main-nav-search"><i class="icon-search icon-white"></i></a>
          </li>
      <?php endif; ?>
        
      <?php if($this->user->hasFunction('PUBLIC')): ?>
      <?php if($this->user->isLoggedIn()): ?>
        <?php $link = "Logged in as <strong>" . $this->user->name() . '</strong>' . BR .
                  '<div class="pull-right">' .
                  ($this->user->authMethod() == gCOSI_AUTH_METHOD_BUILT_IN ? anchor("auth/change_password", "Change Password") . " / " : "") . 
                  anchor("auth/logout", "Logout") .
                  '</div>';
        ?>
      <?php else: ?>
        <?php $link = anchor("auth/login", "Login"); ?>
      <?php endif; ?>

          <li class="btn btn-inverse ">
            <a href="javascript:;" id="main-nav-user-account" title="<?=htmlentities($link);?>"><i class="icon-user icon-white"></i></a>
          </li>
        <?php endif; ?>
                
            </ul>
        </div>

        <?php 
        if ($this->session->flashdata('message'))
        {
          echo BR.'<div class="alert alert-success"><strong>Message: </strong>'. $this->session->flashdata('message') . '</div>';
        }
        ?>

    <?php } catch (Exception $e) {} ?> 