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
	<h1>Add Role</h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home')); ?>
	<?php echo anchor('/role', 'List Roles'); ?>
	<?php echo anchor('/roles/add/', 'Add Role',array('class'=>'current'));?>
</div>
<div class="container-fluid">
	<div class="row-fluid">

		<div class="span3"></div>
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title"><h5>Add Role</h5></div>
				<div class="widget-content">
					<form action="?posted=true" method="post" class="form-horizontal">
						<div class="control-group">
							<label for="" class="control-label">ID *</label>
							<div class="controls"><input type="text" name="role_id" required></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Name *</label>
							<div class="controls"><input type="text" name="name" required></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Type</label>
							<div class="controls">
								<select name="role_type_id">
									<option value="ROLE_USER">User</option>
									<option value="ROLE_ORGANISATIONAL">Organisational</option>
									<option value="ROLE_FUNCTIONAL">Functional</option>
									<option value="ROLE_DOI_APPID">DOI Application Identifier</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Enabled</label>
							<div class="controls"><input type="checkbox" name="enabled" checked="checked"></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Authentication Service</label>
							<div class="controls">
								<select name="authentication_service_id">
									<option value="AUTHENTICATION_BUILT_IN">Built In</option>
									<option value="AUTHENTICATION_LDAP">LDAP</option>
									<option value="AUTHENTICATION_SHIBBOLETH">Shibboleth</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<div class="controls"><button type="submit" class="btn btn-primary">Add Role</button></div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>

</div>

<?php $this->load->view('footer');?>