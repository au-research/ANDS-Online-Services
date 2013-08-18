<?php $this->load->view('header');?>
<div class="content-header">
	<h1><?php echo $pid['handle'];?></h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/pids', '<i class="icon-home"></i> List My Identifiers'); ?>
	<?php echo anchor('/pids/view/?handle='.$pid['handle'], $pid['handle'], array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div class="row-fluid">
		<div class="span2">&nbsp;</div>
		<div class="span8">
			<div class="widget-box">
				<div class="widget-title">
					<h5><?php echo $pid['handle'];?></h5>
				</div>
				<div class="widget-content">
					<dl>
						<?php if(isset($pid['desc'])): ?>
						<dt>Description</dt>
						<dd><?php echo $pid['desc']; ?></dd>
						<?php endif; ?>
						<?php if(isset($pid['url'])): ?>
						<dt>URL</dt>
						<dd><?php echo $pid['url']; ?></dd>
						<?php endif; ?>
					</dl>
					<a data-toggle="modal" href="#edit_modal" href="javascript:;" class="btn btn-primary">Edit</a>
					<a data-toggle="modal" href="#delete_modal" href="javascript:;" class="btn btn-link">Delete</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal hide fade" id="delete_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3><?php echo $pid['handle'] ?></h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-error">
				Are you sure you want to delete this PID? This is irreversible
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a id="delete_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Deleting...">Proceed</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<div class="modal hide fade" id="edit_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3><?php echo $pid['handle'] ?></h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="mint_form">
				<div class="control-group">
					<label class="control-label">URL</label>
					<div class="controls">
						<input type="url" name="url" value="<?php echo (isset($pid['url'])? $pid['url']:''); ?>" idx="<?php echo (isset($pid['url_index'])? $pid['url_index']:''); ?>" changed="false"/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Description</label>
					<div class="controls">
						<input type="text" name="desc" value="<?php echo (isset($pid['desc'])? $pid['desc']:''); ?>" idx="<?php echo (isset($pid['desc_index'])? $pid['desc_index']:''); ?>" changed="false"/>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="modal-footer">
		<a id="update_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Updating..." handle="<?php echo $pid['handle']; ?>">Update</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<?php $this->load->view('footer');?>