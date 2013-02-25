<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<a class="btn btn-small" id="open_add_ds_form" data-toggle="modal" href="#AddNewDS"><i class="icon-plus"></i> Add New Datasource</a>
<div class="modal hide fade" id="AddNewDS">
	
		<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Add New Datasource</h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			
			<div class="alert alert-info">
				Please provide the key and the title for the data source
			</div>			

			<form action="#" method="get" class="form-vertical">
				<div class="control-group">
					<label class="control-label" for="key">Key</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="key" value="">
						<button class="btn btn" id="generate_random_key"><i class="icon-refresh"></i> Generate Random Key </button>
						<p class="help-inline"><small>Key must be unique and is case sensitive</small></p>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Data source</label>
					<div class="controls">
						<select class="chosen" name="data_source_id">
							<?php foreach($ownedDatasource as $ds):?>
								<option value="<?php echo $ds->id;?>"><?php echo $ds->title;?></option>
							<?php endforeach;?>
						</select>
						<span class="help-block">Data source is required</span>
					</div>
				</div>

				<label for="group">Originating Source: </label><input type="text" class="input-xlarge" name="originatingSource" value="">
				<label for="group">Group: </label><input type="text" class="input-xlarge" name="group" value="">
				<label for="type">Type: </label><input type="text" class="input-xlarge" name="type" value="">
				</div>


			</form>

		</div>
		<div class="modal-footer">
			<a id="AddNewDS_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Saving..." ro_class="collection">Add New Collection</a>
			<a href="#" class="btn hide" data-dismiss="modal">Close</a>
		</div>
	</div>
	
	


	
</div>

<div class='addButtons'>
	<div class="addButton collection">
		<p class="info">A Collection is a blah blah blah</p>
		<span class="button" id="collection">Add a Collection</span>	
	</div>
	<div class="addButton party">
		<p class="info">A Collection is a blah blah blah</p>
		<span class="button" id="party">Add a Party</span>
	</div>
	<div class="addButton activity">
		<p class="info">A Collection is a blah blah blah</p>
		<span class="button" id="activity">Add a Activity</span>
	</div>
	<div class="addButton service">
		<p class="info">A Collection is a blah blah blah</p>
		<span class="button" id="service">Add a Service</span>
	</div>
</div>

<div class="modal hide fade" id="myModal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Add New Datasource</h3>
	</div>
	<form id="newRegistryObjectForm">
		<input type="text" name="key" value="registry Object Key Goes Here"/>
	</form>
</div>
<?php $this->load->view('footer');?>