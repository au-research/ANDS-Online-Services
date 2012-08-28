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
					<form class="form-horizontal" action="registry_object/manage">
					  <div class="control-group">
					    <label class="control-label" for="inputEmail">User ID</label>
					    <div class="controls">
					      <input type="text" id="inputEmail" placeholder="Email">
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="inputPassword">Password</label>
					    <div class="controls">
					      <input type="password" id="inputPassword" placeholder="Password">
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <label class="checkbox">
					        <input type="checkbox"> Remember me
					      </label>
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