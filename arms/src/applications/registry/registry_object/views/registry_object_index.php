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
	<div class="content-header">
		<h1><?php echo $ro->title;?> <?php if($viewing_revision) echo '<small>('.$revisionInfo.')</small>'?></h1>
		<div class="btn-group">
			<?php 
				if(!$viewing_revision) {
					echo anchor('registry_object/edit/'.$ro->id, '<i class="icon-edit"></i> Edit', array('class'=>'btn btn-large', 'title'=>'Edit Registry Object'));
				}
			?>
		</div>
	</div>
	<div id="breadcrumb">
		<?php 
			if ($this->user->hasFunction('REGISTRY_USER')) 
			{
				// // User has registry access...links can be more specific
				echo anchor('data_source/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'));
				echo anchor('data_source/manage#!/view/'.$ds->id, $ds->title, array('class'=>'', 'title'=>''));
			}
			else
			{
				// Just a guest user, take them to the *real* home page, no link to data source
				echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'));
			}
		?>
		<a href="#" class="current"><?php echo $ro->title;?> </a>
		<?php if($viewing_revision) echo '<a href="#">('.$revisionInfo.')</a>'?>
	</div>
	<input class="hide" type="hidden" value="<?php echo $ro->id;?>" id="ro_id"/>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span8">
				<?php echo $rif_html;?>
			</div>
			<div class="span4">

				<div>
					<center>
					<?php if($ro->status=='PUBLISHED'){$anchor = portal_url().$ro->slug;}else{$anchor = portal_url().'view/?id='.$ro->id ;} ?>
					<?php echo anchor($anchor, '<i class="icon-globe icon icon-white"></i> View in Research Data Australia', array('class'=>'btn btn-primary','target'=>'_blank'));?>
					</center>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Metadata</h5>
					</div>
					<div class="widget-content">
						<table class="table table-bordered table-striped">
							<tr><th>Title</th><td><?php echo $ro->title;?></td></tr>
							<tr><th>Status</th><td><?php if($viewing_revision && !$currentRevision) echo 'SUPERSEDED'; else echo $ro->status;?></td></tr>
							<tr><th>Key</th><td><?php echo $ro->key;?></td></tr>
							<tr><th>ID</th><td><?php echo $ro->id;?></td></tr>						
							<tr><th>slug</th><td><?php echo $ro->slug;?></td></tr>
							<tr><td></td><td></td></tr>
							<tr><th>RIFCS Format</th><td><a href="javascript:;" class="btn btn-small" id="exportRIFCS"><i class="icon-eject"></i> Export RIFCS</a></td></tr>
							<?php 
								if($native_format != 'rif') {
									echo '<tr><th>Native Format</th><td><a href="javascript:;" class="btn btn-small" id="exportNative"><i class="icon-eject"></i>Export '.$native_format.'</a></td></tr>';
								}
							?>
							<input type="hidden" id="registry_object_id" value="<?php echo $ro_id;?>"/>
						</table>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Tags Management</h5>
					</div>
					<div class="widget-content">
						<?php $data['ro'] = $ro; $this->load->view('tagging_interface', $data);?>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Quality Report</h5>
					</div>
					<div class="widget-content nopadding">
						<?php echo $quality_text;?>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Revision</h5>
					</div>
					<div class="widget-content">
						<ul>
						<?php
							foreach($revisions as $time=>$revision){
								echo '<li>'.anchor('registry_object/view/'.$ro->id.'/'.$revision['id'], $time.$revision['current']).'</li>';
							}
						?>
						</ul>

					</div>
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