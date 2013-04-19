<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>

<div class="modal hide fade" id="AddNewDS">
	<div class="modal-header">
	<a href="javascript:;" class="close" data-dismiss="modal">×</a>
	<h3>Add New Registry Object</h3>
</div>

<div class="modal-screen-container">

	<div class="modal-body">
		
		<div class="alert alert-info">
			Please provide the required information for the registry object
		</div>			
		<div class="alert alert-error hide">
			An Error Occured while adding your record
		</div>
		<form action="#" method="get" class="form-vertical">
			<div class="control-group" style="background-color:#ffffff;margin-bottom:10px;padding:0em">
				<label class="control-label" for="key">Key</label>
				<div class="controls">
					<div class="input-append">
					  <input type="text" class="input-xlarge" name="key" value="" required>
					  <button class="btn" id="generate_random_key" tip="Generate Random Key"><i class="icon-refresh"></i></button>
					</div>
					<p class="help-inline"><small>Key must be unique and is case sensitive</small></p>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label">Data source</label>
				<div class="controls">
					<select name="data_source_id" required class="input-xlarge">
						<?php foreach($ownedDatasource as $ds):?>
							<option value="<?php echo $ds->id;?>"><?php echo $ds->title;?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>

			<div class="control-group hidden">
				<label class="control-label" for="key">Originating Source</label>
				<div class="controls">
					<input required type="text" class="input-xlarge" name="originatingSource" value="<?php echo base_url();?>/orca/register_my_data">
				</div>
			</div> 

			<div class="control-group" style="background-color:#ffffff;margin-bottom:10px;padding:0em">
				<label class="control-label" for="key">Group</label>
				<div class="controls">
					<input required type="text" class="input-xlarge" name="group" value="">
				</div>
			</div>

			<div class="control-group" style="background-color:#ffffff;margin-bottom:10px;padding:0em">
				<label class="control-label" for="key">Type</label>
				<div class="controls" id="ro_type">
					
				</div>
			</div>
			</div>


		</form>

	</div>
	<div class="modal-footer">
		<a id="AddNewDS_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Saving..." ro_class="collection">Add New Collection</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<div class="addButtons">
	<div class="container">
		<div class="row">
			<div class="span3">
				<div class="widget-box">
					<div class="widget-content">
						<img src="assets/img/collection.png"/>
						<span>Research datasets or collections of research materials.</span>
						<div class="clearfix"></div>
					</div>
					<button class="btn btn-primary btn-large btn-block addButton" id="collection"><i class="icon icon-plus icon-white"></i> Add a Collection</button>	
				</div>
			</div>
			<div class="span3">
				<div class="widget-box">
					<div class="widget-content">
						<img src="assets/img/party.png"/>
						<span>Researchers or research organisations that create or maintain research datasets or collections.</span>
						<div class="clearfix"></div>
					</div>
					<button class="btn btn-primary btn-large btn-block addButton" id="party"><i class="icon icon-plus icon-white"></i> Add a Party</button>	
				</div>
			</div>
			<div class="span3">
				<div class="widget-box">
					<div class="widget-content">
						<img src="assets/img/activity.png"/>
						<span>Projects or programs that create research datasets or collections.</span>
						<div class="clearfix"></div>
					</div>
					<button class="btn btn-primary btn-large btn-block addButton" id="activity"><i class="icon icon-plus icon-white"></i> Add an Activity</button>	
				</div>
			</div>
			<div class="span3">
				<div class="widget-box">
					<div class="widget-content">
						<img src="assets/img/service.png"/>
						<span>Services that support the creation or use of research datasets or collections.</span>
						<div class="clearfix"></div>
					</div>
					<button class="btn btn-primary btn-large btn-block addButton" id="service"><i class="icon icon-plus icon-white"></i> Add a Service</button>	
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('footer');?>