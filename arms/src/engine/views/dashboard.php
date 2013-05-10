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
<span>
	<div class="pull-right">
				<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://ands.org.au/guides/cpguide/cpgdsaaccount.html"> New to these screens? Take a tour!</a></span>
	</div>
	&nbsp;
</span>

<div class="container" id="main-content">
	<div class="row">

		<div class="span7">
			<div class="box">
				<div class="box-header clearfix">
					<h1>ANDS Online Services Home</h1>	
					<a href="<?php echo portal_url();?>" style="margin-top:5px;" class="btn btn-info pull-right" target="_blank">
					<i class="icon-globe icon icon-white"></i> Visit Research Data Australia</a>
				</div>
				<div class="box-content">
					<h4>ANDS Online Services News</h4>
					<p><small><strong>Welcome to ANDS Online Services Release 10!</strong></small></p>
					<p><small>We are pleased to welcome you to your new ANDS Online Services dashboard. 
						Release 10 introduces a complete rewrite of the ANDS Registry system, with a focus on performance and improved usability.</small></p>

					<p><small>Most of the software features are unchanged, but you might find them in new areas of the screen. To help you find your 
						way around, we've created a number of helpful intructions and videos: </small></p>

					<p><small>
					<ul>
						<li>Day 1 Help - ANDS Registry (<em>PDF</em>)</li>
						<li>Finding your way - ANDS Registry(<em>video</em>)</li>
						<li>ANDS R10 Walkthrough (<em>webinar recording</em>)</li>
						<li><a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank">ANDS Content Providers Guide</a> (<em>web page</em>)</li>
						<li><a href="http://ands.org.au/resource/ands-faq.html" target="_blank">ANDS Online Services FAQ</a> (<em>web page</em>)</li>
					</ul>
					</small></p>

					<p><small>To get started, click on the My Data link at the top of the screen and take a look around! You can always get back to this dashboard by clicking on the ANDS logo (top left).</small></p>

					<p><small>The Publish My Data tool is temporarily unavailable at this time. Please contact ANDS Services Support team if you require assistance with your previously-created Publish My Data records.</small></p>

					<p><small>As always, the ANDS Services Support team is here to help! If your question isn't answered by your Client Liaison Officer or the 
						<a href="http://ands.org.au/resource/ands-faq.html" target="_blank">FAQ</a>, please contact ANDS Services Support at 
						<a href="mailto:services@ands.org.au" target="_blank">services@ands.org.au</a>.</small></p>


				</div>
			</div>
		</div>

		<div class="span5">
			
			<div class="box hide">
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
	

	<?php
	if(mod_enabled('registry')){
	?>

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
								for($i=0; $i < sizeof($data_sources) && $i < 7; $i++){
									echo '<li><a href="'.registry_url('data_source/manage#!/view/'.$data_sources[$i]->id).'">'.$data_sources[$i]->title . "</a></li>";
								}
								echo '</ul>';

								if ($i < sizeof($data_sources))
								{
									echo '<div style="margin-left:20px;">';
									echo '<select data-placeholder="Choose a Data Source to View" class="chzn-select" id="dashboard-datasource-chooser">';
									echo '	<option value=""></option>';
									foreach($data_sources as $ds){
										echo '<option value="'.$ds->id.'">'.$ds->title.'</option>';
									}
									echo '</select>';
									echo '</div>';
								}
								
							}else{
								echo 'You are not associated with any data sources yet!';
							}
						?>
				</div>
			</div>

	<?php
	}
	?>

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

<!-- Prompt user to upgrade browser -->
<script type="text/javascript"> 
var $buoop = {vs:{i:7,f:3.6,o:10.6,s:4,n:9}} 
	$buoop.ol = window.onload; 
	window.onload=function(){ 
	 try {if ($buoop.ol) $buoop.ol();}catch (e) {} 
	 var e = document.createElement("script"); 
	 e.setAttribute("type", "text/javascript"); 
	 e.setAttribute("src", "http://browser-update.org/update.js"); 
	 document.body.appendChild(e); 
	} 
</script> 

<?php 
/* Dirty hack to detect first login and display the growl notification */
if (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], "login") !== FALSE)
{
?>
<script type="text/javascript"> 
	var displayGrowl = "<p>Welcome <strong><?=$this->user->name();?></strong></p>"+
						"<p>You are now successfully logged in using your authentication provider's token of: <strong><?=$this->user->localIdentifier();?></strong></p>"+
						"<p>You can logout at any time by clicking on the user icon at the top right of the screen.</p>";

</script> 
<?php
}
?>


<?php $this->load->view('footer');?>