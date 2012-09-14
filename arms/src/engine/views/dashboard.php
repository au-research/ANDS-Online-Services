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
					<h1>ANDS Registry Dashboard</h1>
				</div>
				
				<div class="box-content">
					<div class="hero-unit">
						  <p>You are now logged in as <strong><?=loggedInName();?></strong><br/>
							 using the authentication provider's identifier of <strong><?=loggedInUserPrefix();?></strong></p>
						
						
						<p>You can <em>Logout</em> by clicking the user icon in the upper right of your screen.</p>
					</div>
					<span class="label label-important">DEBUG</span>
					<div class="row">
					      <div class="span3">
					      	<h3>My Roles</h3>
					      	<p>
					      		<?php
					      			foreach($this->session->userdata(AUTH_ROLE_ARRAY) AS $role)
									{
										echo $role . "<BR/>";
									}
					      		?>
					      	</p>
					      </div>
					      <div class="span3">
					      	<h3>My Data Sources</h3>
					      	<p>
					      		<?php
					      			if ($this->session->userdata(AUTH_ORG_ARRAY))
									{
						      			foreach($this->session->userdata(AUTH_ORG_ARRAY) AS $org)
										{
											echo $org . "<BR/>";
										}
									}
					      		?>
					      	</p>
					      </div>
				    </div>
				</div>
			</div>
		</div>
		<div class="span2">&nbsp;</div>
	</div>
</div>

<?php $this->load->view('footer');?>