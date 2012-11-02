<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/registry_object/_registry_object
 * @package ands/datasource
 * 
 */
?>
<?php $this->load->view('header');?>

<div class="container" id="main-content">
	<div class="row">
		<div class="span3">&nbsp;</div>
		<div class="span6">
			<div class="box">
				<div class="box-header clearfix">
					<h1>Login</h1>
				</div>
				<div class="box-content">
					
					<?php if (isset($error_message)): ?>
						<div class="alert alert-error">
							<?php echo $error_message; ?>
						</div>
					<?php endif; ?>
					
					<form class="form-horizontal" action="<?=base_url("auth/login");?>" method="post">
					  <div class="control-group">
					    <label class="control-label" for="inputUsername">Username</label>
					    <div class="controls">
					      <input type="text" id="inputUsername" name="inputUsername" placeholder="Username">
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="inputPassword">Password</label>
					    <div class="controls">
					      <input type="password" id="inputPassword" name="inputPassword" placeholder="Password">
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      
					      <button type="submit" class="btn">Sign in</button>
					    </div>
					  </div>
					</form>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>
</div>

<?php $this->load->view('footer');?>