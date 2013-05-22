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
						<?php /* REMOVED - prints user's password to screen
						// USEFUL FOR DEBUGGING ONLY
						if(false): ?>
							<div class="alert alert-error">
								Error: <?php echo $exception->getMessage(); ?>
							</div>
						<?php endif; */ ?>
						<?php 
						printLoginForm($authenticators, $default_authenticator, 'loginForm');
						printAlternativeLoginForms($authenticators, $default_authenticator);
						?>
					</div>

				</div>
			</div>

			<div class="span3 pull-right">
			</div>
	</div>


	<div class="row">
		<div class="span3">&nbsp;</div>
		<div class="span6">
			<div class="alert alert-info">
				<center>
					<small>Searching for Research Data? <a href="<?php echo portal_url();?>" target="_blank" style="color:inherit;">Visit <b>Research Data Australia</b> <i class="icon-globe icon"></i></a></small>
				</center>
			</div>
		</div>
	</div>
</div>


<!-- Prompt user to upgrade browser -->
<script type="text/javascript"> 
var $buoop = {vs:{i:7,f:3.6,o:10.6,s:4,n:9}} 
	$buoop.ol = window.onload; 
	window.onload=function(){ 
	 try {if ($buoop.ol) $buoop.ol();}catch (e) {} 
	 var e = document.createElement("script"); 
	 e.setAttribute("type", "text/javascript"); 
	 e.setAttribute("src", "../../assets/js/update.js"); 
	 document.body.appendChild(e); 
	} 
</script> 

<?php $this->load->view('footer');?>


<?php

function printLoginForm($authenticators, $authenticator , $class)
{
	
	if($authenticator == gCOSI_AUTH_METHOD_SHIBBOLETH)
	{
		print "<div class='".$class."' id='".$authenticator."_LoginForm'>";
		print "<small>Log into the ANDS Online Services Dashboard using your AAF credentials:</small>";
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
	//print "<div class='btn-group'>"; // prevent double-padding in <div widget-title>
	print "<a class='btn btn-small dropdown-toggle ' data-toggle='dropdown' href='#'>Alternative Login <b class='caret'></b></a>";
	print "<ul class='dropdown-menu'>";
		foreach($authenticators as $key => $value){
			print "<li class=''><a href='javascript:;' class='loginSelector' id='".$key."'>".$value."</a></li>";
		}
	print "</ul>";
	//print "</div>";
}

function printAlternativeLoginForms($authenticators, $default_authenticator)
{
	foreach($authenticators as $key => $value){
		if($key != $default_authenticator)
			printLoginForm($authenticators, $key, 'loginForm hide');
	}
}
?>
