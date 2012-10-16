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
						  <p>You are now logged in as <strong><?=$this->user->name();?></strong><br/>
							 using the authentication provider's identifier of <strong><?=$this->user->localIdentifier();?></strong></p>
						
						
						<p>You can <em>Logout</em> by clicking the user icon in the upper right of your screen.</p>
					</div>
					<span class="label label-important">DEBUG</span>
					<div class="row">
					      <div class="span3">
					      	<h3>My Affiliations</h3>
					      	<p>
					      		<?php
					      			foreach($this->user->affiliations() AS $role)
									{
										echo $role . "<BR/>";
									}
					      		?>
					      	</p>
					      </div>
					      <div class="span3">
					      	<h3>My Functions</h3>
					      	<p>
					      		<?php
					      			foreach($this->user->functions() AS $org)
									{
										echo $org . "<BR/>";
									}
					      		?>
					      	</p>
					      </div>
				    </div>
				    <div class="row">
				    	<?php if (mod_enabled('data_source')): ?>
					      <div class="span3">
					      	<h3>My Data Sources</h3>
					      	<p>
					      		<?php
					      			foreach($my_datasources AS $ds)
									{
										echo $ds->title . " (".$ds->key.")" . "<BR/>";
									}
					      		?>
					      	</p>
					      </div>
					   <?php endif; ?>
					   <?php if (mod_enabled('vocab_service')): ?>
					      <div class="span3">
					      	<h3>My Vocabularies</h3>
					      	<p>
					      		<?php
		
					      			foreach($my_vocabs AS $v)
									{
										echo $v->title . "<BR/>";
									}
					      		?>
					      	</p>
					      </div>
					   <?php endif; ?>
				    </div>
				</div>
			</div>
		</div>
		<div class="span2">&nbsp;</div>
	</div>
</div>

<?php $this->load->view('footer');?>