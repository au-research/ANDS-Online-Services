<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Persistent Identifier Service (PIDS)</h1>
	<div class="btn-group">
		<a data-toggle="modal" href="#add_trusted_client_modal" href="javascript:;" class="btn btn-large"><i class="icon icon-plus"></i> Add Trusted Clients</a>
	</div>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/pids', '<i class="icon-home"></i> List My Identifiers'); ?>
	<?php echo anchor('/list_trusted', 'List Trusted Clients', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div class="row-fluid">
		<div class="span2">&nbsp;</div>
		<div class="span8">
			<div id="trusted_clients">Loading...</div>
		</div>
		<div class="span3"></div>
	</div>
</div>

<div class="modal hide fade" id="add_trusted_client_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Add Trusted Client</h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="add_trusted_client_form">
				<div class="control-group">
					<label class="control-label">IP</label>
					<div class="controls">
						<input type="text" name="ip" value=""/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Description</label>
					<div class="controls">
						<input type="text" name="desc"/>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a href="javascript:;" class="btn btn-primary" id="app_id_show">Include Existing App ID</a>
					</div>
				</div>
				<div class="control-group hide" id="app_id_field">
					<label class="control-label">App ID</label>
					<div class="controls">
						<input type="text" name="app_id"/>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="modal-footer">
		<a id="add_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Adding...This might take several seconds">Add Trusted Client</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<script type="text/x-mustache" id="trusted_clients-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Trusted Clients</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>IP</th>
					<th>App ID</th>
					<th>Description </th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td>{{ip_address}}</td>
					<td>{{app_id}}</td>
					<td>{{description}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php  $this->load->view('footer');?>