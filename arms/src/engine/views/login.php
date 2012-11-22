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
					<div class="right-widget">
						<a href="javascript:;" id="showBuiltInLoginForm"><i class="icon-chevron-down"></i></a>
					</div>
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

					<img src="<?php echo asset_url('img/aaf_logo.gif');?>" style="display:block;margin:10px auto;"/>
					<a href="<?php echo secure_host_url();?><?php echo gSHIBBOLETH_SESSION_INITIATOR;?>?target=<?php echo secure_base_url();?>auth/setUser" class="btn btn-primary btn-block">Login using Australian Access Federation (AAF) credentials</a>

				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>


	<div class="hide" id="BuiltInLoginForm">
		<form class="form" action="<?=base_url("auth/login");?>" method="post">
		  <div class="control-group">
		    <div class="controls">
		    	<label>Username</label>
		    	<input type="text" id="inputUsername" name="inputUsername" placeholder="Username">
		    </div>
		  </div>
		  <div class="control-group">
		    <div class="controls">
		    	<label>Password</label>
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

<?php $this->load->view('footer');?>