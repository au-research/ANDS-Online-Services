<div class="preview_pane">
	<div class="box">
		<div class="box-header">
			<h5><?php echo $ro->title;?></h5>
		</div>
		<div class="box_content">
			<table class="table table-bordered table-striped">
				<tr><th>Key</th><td><?php echo $ro->key;?></td></tr>
				<tr><th>Title</th><td><?php echo $ro->title;?></td></tr>
				<tr><th>Status</th><td><?php echo $ro->status;?></td></tr>
				<tr><th>ID</th><td><?php echo $ro->id;?></td></tr>
				<tr><th>slug</th><td><?php echo $ro->slug;?></td></tr>
				<tr><th>Quality Level</th><td><?php echo $ro->quality_level;?></td></tr>
				<tr><th>Errors Count</th><td><?php echo $ro->error_count;?></td></tr>
			</table>
			<?php echo anchor('registry_object/view/'.$ro->id, '<i class="icon-eye-open icon icon-white"></i> View Registry Object', array('class'=>'btn btn-primary'));?>
			<?php echo anchor('registry_object/edit/'.$ro->id, '<i class="icon-edit icon"></i> Edit', array('class'=>'btn'));?>
		</div>
	</div>
</div>