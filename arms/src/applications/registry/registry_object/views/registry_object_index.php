<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/registry_object/_registry_object
 * @package ands/registry_object
 * 
 */
?>
<?php $this->load->view('header');?>
<div id="content" style="margin-top:45px;margin-left:0px">
	<div id="content-header">
		<h1><?php echo $ro->title;?></h1>
		<div class="btn-group">
			<a class="btn btn-large" title="Manage Files"><i class="icon-file"></i></a>
		</div>
	</div>
	<div id="breadcrumb">
		<a href="#" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a>
		<a href="#" class="current"><?php echo $ro->title;?></a>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<?php echo $rif_html;?>
			</div>
		</div>
		
	</div>
</div>
<?php $this->load->view('footer');?>