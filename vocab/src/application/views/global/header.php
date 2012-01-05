<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>ORCA Online Services - Powered by the Australian National Data Service</title>
		
		<!-- Styles -->
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>_css/reset.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>_css/basic.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>_css/shine.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>_css/layout.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>_css/treeview/jquery.treeview.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>_css/print.css" media="print" />
		
		<!-- Scripts -->
		<script src="<?php echo base_url();?>_js/jquery.min.js" type="text/javascript"></script>
		<script src="<?php echo base_url();?>_js/lib/superfish.js" type="text/javascript"></script>
        <script src="<?php echo base_url();?>_js/script.js" type="text/javascript"></script>
        
        <!--jQuery UI -->
        <link type="text/css" href="<?php echo base_url();?>_css/smoothness/jquery-ui-1.8.16.custom.css" rel="stylesheet" />	
		<script type="text/javascript" src="<?php echo base_url();?>_js/lib/jquery-ui-1.8.16.custom.min.js"></script>
        
        <!--jsTree-->
        <script type="text/javascript" src="<?php echo base_url();?>_js/lib/jstree/jquery.jstree.js"></script>
        
        <!-- VODKR SPECIFIC MOVEME -->
		<script type="text/javascript" src="<?php echo base_url();?>_js/lib/vodkr_dhtml.js"></script>
		<script type="text/javascript" src="<?php echo base_url();?>_js/lib/jquery-xslt.js"></script>
		
	</head>
	<body>
    	<div id="wrapper"><!-- BIG wrapper around everything-->
    		
    		<!-- Header-->
    		<div id="header">
    			<ul id="top-menu">
    				<li><a href="#">Home</a></li>
    				<li><a href="#">Vocabulary Browser</a></li>
    				<li><a href="#">Web Services</a></li>
    				<li><a href="#">Tools</a></li>
    				<li><a href="#">Support</a></li>
    			</ul>
    			<!-- End Top Menu -->
    			<a href="http://services.ands.org.au/">
    				<img alt="ANDS Online Services" src="<?php echo base_url();?>_img/ands_logo.gif" style="float:right;" />
    			</a>
    		</div>
    		<!-- End Header-->
    		<div id="brand">
    			<a href="http://services.ands.org.au">
    				<img alt="Classify My Data" src="<?php echo base_url();?>_img/cmd_logo.gif"/>
    			</a>
    			<div class="clearfix"></div>
    		</div>
    		
    		
    		<!-- Page content container -->
    		<div id="container">
    			
    			<!-- jsTREE-->
    			<div class="box w300">
    				<div class="box-header">Vocabulary Browser</div>
    				<div class="box-body">
    					<div id="vocab_tree">
    					</div>
    				</div>
    			</div>
    			
    			<div class="box w500">
    				<div class="box-header">Concept Viewer</div>
    				<div class="box-body">
    					<div id="vocab_concept_viewer">
    					
    					</div>
    				</div>
    			</div>
    			<div class="clearfix"></div>