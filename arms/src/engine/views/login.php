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
			<div class="widget-box">
				<div class="widget-title">
					<h5>Login</h5>
					<div class="buttons">
						<?php printAlternativeLoginControl($authenticators); ?>
					</div>
					<div class="right-widget">
												
					</div>
				</div>
				<div class="widget-content">
					
					<?php if (isset($error_message)): ?>
						<div class="alert alert-error">
							<?php echo $error_message; ?>
						</div>
					<?php endif; ?>
					
					<?php /* REMOVED - prints user's password to screen */
					// if (isset($exception)): 
					if(false): ?>
						<div class="alert alert-error">
							<?php echo $exception; ?>
						</div>
					<?php endif; ?>
					<?php 
					prinfLoginForm($authenticators, $default_authenticator, 'loginForm');
					printAlternativeLoginForms($authenticators, $default_authenticator);
					?>
					
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>	
</div>



<?php $this->load->view('footer');?>


<?php

function prinfLoginForm($authenticators, $authenticator , $class)
{
	
	if($authenticator == gCOSI_AUTH_METHOD_SHIBBOLETH)
	{
		print "<div class='".$class."' id='".$authenticator."_LoginForm'>";
		print "	<img src='".asset_url('img/aaf_logo.gif')."' style='display:block;margin:10px auto;'/>";
		print "	<a href='".secure_host_url().gSHIBBOLETH_SESSION_INITIATOR."?target=".secure_base_url()."auth/setUser' class='btn btn-primary btn-block'>Login using ".$authenticators[$authenticator]."</a>";
		print "</div>";
	}
	else
	{
		print "<div class='".$class."' id='".$authenticator."_LoginForm'>";
		print "	<form class='form' action='".base_url("auth/login")."' method='post'>";
		print "	  <div class='control-group'>";
		print "	    <div class='controls'>";
		print "	    	<label>Username</label>";
		print "	    	<input type='text' id='inputUsername' name='inputUsername' placeholder='Username'>";
		print "	    </div>";
		print "	  </div>";
		print "	  <div class='control-group'>";
		print "	    <div class='controls'>";
		print "	    	<label>Password</label>";
		print "	    	<input type='password' id='inputPassword' name='inputPassword' placeholder='Password'>";
		print "	    </div>";
		print "	  </div>";
		print "	  <div class='control-group'>";
		print "	    <div class='controls'>";
		print "	    	<button type='submit' class='btn btn-primary btn-block'>Login using ".$authenticators[$authenticator]."</button>";
		print "	    </div>";
		print "	  </div>";
		print "	</form>";
		print "</div>";
	}

}

function printAlternativeLoginControl($authenticators)
{
	print "<div class='btn-group'>";
	print "<a class='btn btn-small dropdown-toggle ' data-toggle='dropdown' href='#'>Alternative Login <b class='caret'></b></a>";
	print "<ul class='dropdown-menu'>";
		foreach($authenticators as $key => $value){
			print "<li class=''><a href='javascript:;' class='loginSelector' id='".$key."'>".$value."</a></li>";
		}
	print "</ul>";
	print "</div>";
}

function printAlternativeLoginForms($authenticators, $default_authenticator)
{
	foreach($authenticators as $key => $value){
		if($key != $default_authenticator)
			prinfLoginForm($authenticators, $key, 'loginForm hide');
	}
}
?>