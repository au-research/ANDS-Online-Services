<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/registry_object/_registry_object
 * @package ands/registry_object
 * 
 */
?>
<?php $this->load->view('header');?>
<div id="content" style="margin-top:45px;margin-left:0px">
	<div id="content-header">
		<h1><?php echo $ro->title;?> <?php if($viewing_revision) echo '<small>(Revision '.$revision.')</small>'?></h1>
		<div class="btn-group">
			<?php if(!$viewing_revision) echo anchor('registry_object/edit/'.$ro->id, '<i class="icon-edit"></i> Edit', array('class'=>'btn btn-large', 'title'=>'Edit Registry Object'))?>
		</div>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('registry_object/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<?php echo anchor('registry_object/manage/'.$ds->id, $ds->title, array('class'=>'', 'title'=>''))?>
		<a href="#" class="current"><?php echo $ro->title;?> </a>
		<?php if($viewing_revision) echo '<a href="#">(Revision '.$revision.')</a>'?></h1>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span8">
				<?php echo $rif_html;?>
			</div>
			<div class="span4">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Metadata</h5>
					</div>
					<div class="widget-content">
						<table class="table table-bordered table-striped">
							<tr><th>Title</th><td><?php echo $ro->title;?></td></tr>
							<tr><th>Status</th><td><?php echo $ro->status;?></td></tr>
							<tr><th>Key</th><td><?php echo $ro->key;?></td></tr>
							<tr><th>ID</th><td><?php echo $ro->id;?></td></tr>
							<tr><th>slug</th><td><?php echo $ro->slug;?></td></tr>
						</table>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Revision</h5>
					</div>
					<div class="widget-content">
						<ul>
						<?php
							foreach($revisions as $time=>$id){
								echo '<li>'.anchor('registry_object/view/'.$ro->id.'/'.$id, $time).'</li>';
							}
						?>
						</ul>

					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>
<?php $this->load->view('footer');?>