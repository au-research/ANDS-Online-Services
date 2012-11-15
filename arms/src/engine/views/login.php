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
		<div class="span2">&nbsp;</div>
		<div class="span8">
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
					
					<?php if (isset($exception)): ?>
						<div class="alert alert-error">
							<?php echo $exception; ?>
						</div>
					<?php endif; ?>
					

						<div class="clearfix">
							<div class="box-noStyle border-right">
								<img src="<?php echo asset_url('img/aaf_logo.gif');?>" style="display:block;margin:0 auto;"/>
								<a href="<?php echo secure_host_url();?>/Shibboleth.sso/<?php echo gSHIBBOLETH_SESSION_INITIATOR;?>?target=<?php echo secure_base_url();?>auth/setUser" class="btn btn-primary btn-block">Login using Australian Access Federation (AAF) credentials</a>
							</div>
							<div class="box-noStyle">
								<form class="form" action="<?=base_url("auth/login");?>" method="post">
								  <div class="control-group">
								    
								    <div class="controls">
								      <input type="text" id="inputUsername" name="inputUsername" placeholder="Username">
								    </div>
								  </div>
								  <div class="control-group">
								    <div class="controls">
								      <input type="password" id="inputPassword" name="inputPassword" placeholder="Password">
								    </div>
								  </div>
								  <div class="control-group">
								    <div class="controls">
								      <button type="submit" class="btn btn-primary btn-block">Login using local credentials</button>
								    </div>
								  </div>
								</form>
							</div>
						</div>


					
				</div>
			</div>
		</div>
		<div class="span2"></div>
	</div>
</div>

<?php $this->load->view('footer');?>