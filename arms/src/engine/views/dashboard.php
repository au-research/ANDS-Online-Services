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
					<h1>ANDS Services Dashboard</h1>	

					<a href="<?php echo portal_url();?>" style="margin-top:5px;" class="btn btn-info pull-right" target="_blank">
					<i class="icon-globe icon icon-white"></i> Visit Research Data Australia</a>

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
		<div class="span6 hide">
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
		      			echo '<p><select id="organisational_roles" class="chosen">';
		      			foreach($available_organisations as $o){
		      				echo '<option value="'.$o['role_id'].'">'.$o['name'].'</option>';
		      			}
		      			echo '</select></p>';
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
							 <input type="text" class="input-large orgName" localIdentifier="<?php echo $this->user->localIdentifier();?>" required maxLength="255"/>
						</div>
					</div>
					<button class="btn" id="confirmAddOrganisation">Add</button>
				</form>
			</div>
		</div>

	<?php
	if(mod_enabled('registry')){
	?>
		<div class="span6">
			<div class="box">
				<div class="box-header clearfix">
					<h1>My Data Sources</h1>
				</div>
				<div class="box-content">
						<?php
							if(!$this->user->hasFunction('REGISTRY_USER'))
							{
								echo 'You are not registered as a Data Source Administrator.';
							}
							elseif(sizeof($data_sources)>0){
								echo '<ul>';
								$i=0;
								for(; $i < sizeof($data_sources) && $i < 7; $i++){
									echo '<li><a href="'.registry_url('data_source/manage#!/view/'.$data_sources[$i]->id).'">'.$data_sources[$i]->title . "</a></li>";
								}
								if ($i < sizeof($data_sources))
								{
									echo '<li><a href="'.registry_url('data_source/manage').'">More...</a></li>';
								}
								echo '</ul>';
							}else{
								echo 'You are not associated with any data sources yet!';
							}
						?>
				</div>
			</div>
		</div>
	<?php
	}
	?>

		<div class="span6">
			<div class="box">
				<div class="box-header clearfix">
					<h1>My Vocabularies</h1>
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
								echo 'You have no vocabularies.';
							}
						}else{
							echo 'You have no vocabularies.';
							//echo "You can't manage any vocabulary unless you are affiliate with an organisation";
						}
					?>
				</div>
			</div>
		</div>
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