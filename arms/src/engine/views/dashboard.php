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
		<div class="span12">
			<div class="box">
				<div class="box-header clearfix">
					<h1>ANDS Registry Dashboard</h1>
				</div>
				<div class="box-content">
					<div class="well">
						<p>You are now logged in as <strong><?=$this->user->name();?></strong><br/>
						using the authentication provider's identifier of <strong><?=$this->user->localIdentifier();?></strong></p> <p>You can <em>Logout</em> by clicking the user icon in the upper right of your screen.</p>			
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="span4">
			<div class="box">
				<div class="box-header clearfix">
					<h1>Affiliations</h1>
				</div>
				<div class="box-content">
					<?php
		      			if($hasAffiliation){
		      				echo '<ul>';
		      				foreach($this->user->affiliations() AS $role){
								echo '<li>'.$role. "</li>";
							}
							echo '</ul>';
		      			}else{
		      				echo '	<p>
		      							You currently do not have any affiliation with any organisation.
		      						</p>';
		      			}

		      			echo '<div class="well">';
		      			echo '<select id="organisational_roles">';
		      			foreach($available_organisations as $o){
		      				echo '<option value="'.$o['role_id'].'">'.$o['name'].'</option>';
		      			}
		      			echo '</select><br/>';
		      			echo '<p><button class="btn" id="affiliation_signup" localIdentifier="'.$this->user->localIdentifier().'">Affiliate with this Organisation</button></p>';
		      			echo '<p><a href="javascript:;" id="openAddOrganisation">Organisation not in list?</a></p>';
		      			echo '</div>';
		      		?>
				</div>
			</div>

			<div class="hide" id="addOrgHTML">
				<form class="addOrgForm">
					<p>Please enter the name of your organisation to add it to the system:</p>
					<div class="control-group">
						<label class="control-label" for="title">Organisation Name:</label>
						<div class="controls">
							 <input type="text" class="input-large orgName" localIdentifier="<?php echo $this->user->localIdentifier();?>" required/>
						</div>
					</div>
					<button class="btn" id="confirmAddOrganisation">Add</button>
				</form>
			</div>
		</div>

		<div class="span4">
			<div class="box">
				<div class="box-header clearfix">
					<h1>Group vocabularies</h1>
				</div>
				<div class="box-content">
					<?php
						if($hasAffiliation){
							if(sizeof($group_vocabs)>0){
								echo '<ul>';
								foreach($group_vocabs as $g){
									echo '<li><a href="vocab_service/#!/view/'.$g->id.'">'.$g->title . "</a></li>";
								}
								echo '</ul>';
							}else{
								echo 'Your group has not created any vocabulary yet';
							}
						}else{
							echo "You can't manage any vocabulary unless you are affiliate with an organisation";
						}
					?>
				</div>
			</div>
		</div>

		<div class="span4">
			<div class="box">
				<div class="box-header clearfix">
					<h1>My vocabularies</h1>
				</div>
				<div class="box-content">
					<?php
						if($hasAffiliation){
							if(sizeof($owned_vocabs)>0){
								echo '<ul>';
								foreach($owned_vocabs as $g){
									echo '<li><a href="vocab_service/#!/view/'.$g->id.'">'.$g->title . "</a></li>";
								}
								echo '</ul>';
							}else{
								echo 'You have not created any vocabulary yet';
							}
							
						}else{
							echo "You can't manage any vocabulary unless you are affiliate with an organisation";
						}
					?>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="container hide">
	<div class="row">
		<div class="span2">&nbsp;</div>
		<div class="span8">
			<div class="box">
				<div class="box-header clearfix">
					<h1>ANDS Registry Dashboard</h1>
				</div>
				
				<div class="box-content">
					<div class="hero-unit">
						  
						
						
					</div>
					<span class="label label-important">DEBUG</span>
					<div class="row">
					      <div class="span3">
					      	<h3>My Affiliations</h3>
					      	<p>
					      		<?php
					      			if($hasAffiliation){
					      				foreach($this->user->affiliations() AS $role){
											echo $role . "<BR/>";
										}
					      			}else{
					      				echo 'You currently do not have any affiliation with any organisation';
					      			}

					      			echo '<div class="well">';
					      			echo '<select id="organisational_roles">';
					      			foreach($available_organisations as $o){
					      				echo '<option value="'.$o['role_id'].'">'.$o['name'].'</option>';
					      			}
					      			echo '</select>';
					      			echo '<button class="btn" id="affiliation_signup" localIdentifier="'.$this->user->localIdentifier().'">Affiliate with this Organisation</button>';
					      			echo '</div>';
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
					      			if(sizeof($my_datasources)>0){
					      				foreach($my_datasources AS $ds){
											echo $ds->title . " (".$ds->key.")" . "<BR/>";
										}
					      			}else{
					      				echo "You can't manage any data source unless you are affiliate with an organisation";
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

<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
  </div>
</div>

<?php $this->load->view('footer');?>