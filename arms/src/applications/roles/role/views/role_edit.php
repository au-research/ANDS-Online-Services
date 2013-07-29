<?php 

/**
 * Core Maintenance Dashboard
 *  
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/vocab_services/_vocab
 * @package ands/vocab_services
 * 
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Edit Role - <?php echo $role->name;?></h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home')); ?>
	<?php echo anchor('/role', 'List Roles'); ?>
	<?php echo anchor('/role/view/'.rawurlencode($role->role_id), $role->name); ?>
	<?php echo anchor('/role/edit/'.rawurlencode($role->role_id), 'Edit Role',array('class'=>'current'));?>
</div>
<div class="container-fluid">
	<div class="row-fluid">

		<div class="span3"></div>
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title"><h5>Edit Role - <?php echo $role->name;?></h5></div>
				<div class="widget-content">
					<form action="?posted=true" method="post" class="form-horizontal">
						<div class="control-group">
							<label for="" class="control-label">ID</label>
							<div class="controls"><span class="uneditable-input"><?php echo $role->role_id;?></span></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Name *</label>
							<div class="controls"><input type="text" name="name" required value="<?php echo $role->name;?>"></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Type</label>
							<div class="controls">
								<span class="uneditable-input"><?php echo $role->role_type_id;?></span>
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Enabled</label>
							<div class="controls"><input type="checkbox" name="enabled" <?php echo ($role->enabled==DB_TRUE ? 'checked=checked': '');?>></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Authentication Service</label>
							<div class="controls">
								<span class="uneditable-input"><?php echo $role->authentication_service_id;?></span>
							</div>
						</div>
						<div class="control-group">
							<div class="controls"><button type="submit" class="btn btn-primary">Submit</button></div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>

</div>

<?php $this->load->view('footer');?>