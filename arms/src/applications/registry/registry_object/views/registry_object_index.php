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
date_default_timezone_set('Australia/Melbourne');
?>
<?php $this->load->view('header');?>
<div id="content" style="margin-left:0px">
	<div class="content-header">
		
		<h1 style="position:relative;padding-right:80px;max-width:60%;"><?php echo $ro->title;?> <?php if($viewing_revision) echo '<small>('.$revisionInfo.')</small>'?></h1>
		
		<?php 
		if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)):
		?>
			<ul class="nav nav-pills" style="margin-right:80px;padding-top:5px;">
				<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
				<li class=""><?php echo anchor('data_source/manage_records/'.$ds->id,'Manage Records');?></li>
				<li class=""><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
				<li class=""><?php echo anchor('data_source/manage#!/settings/'.$ds->id,'Settings');?></li>
			</ul>
						<div class="btn-group">
				<?php 
					if(!$viewing_revision) {
						echo anchor('registry_object/edit/'.$ro->id, '<i class="icon-edit"></i> Edit', array('class'=>'btn btn-small', 'title'=>'Edit Registry Object'));
						// XXX: Delete?
					}
				?>
			</div>
		<?php 
		endif;
		?>

	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php 
			if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)) 
			{
				// // User has registry access...links can be more specific
				echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'));
				echo anchor('data_source/manage_records/'.$ds->id, ($ds->title ?: "unnamed datasource"), array('class'=>'', 'title'=>''));
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
	<input class="hide" type="hidden" value="<?=$ds->id;?>" id="data_source_id"/>

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

				<?php 
				if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)):
				?>
					<div class="widget-box">
						<div class="widget-title">
							<h5>Quality Report</h5>
						</div>
						<div class="widget-content nopadding">
							<?php echo $quality_text;?>
						</div>
					</div>
				<?php
				endif;
				?>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Revision</h5>
						<a href="javascript:;" class="btn btn-small pull-right" style="margin-top:5px; margin-right:5px;" id="exportRIFCS"><i class="icon-eject"></i> Show RIFCS</a>
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

				<?php 
				if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)):
				?>
				<div class="widget-box">
					<div class="widget-title">
						<h5>Registry Metadata</h5>
						<?php 
						if (isset($action_bar) && is_array($action_bar) && count($action_bar) > 0)
						{
							echo '<div class="btn-group pull-right">
									  <a class="btn btn-small btn-warning dropdown-toggle" data-toggle="dropdown" href="#">
									    Change Status
									     <span class="caret"></span>
									  </a>
									  <ul class="dropdown-menu">
									   	';
									   foreach ($action_bar AS $action)
									   {
									   	echo '<li><a class="status_change_action" to="'.$action.'">To ' . readable($action,true) . '</a></li>';
									   }

							echo '	  </ul>
									</div>';
						}
						?>
					</div>
					<div class="widget-content">

						<table class="table table-bordered table-striped table-small">
							<tr><th>Title</th><td><?php echo $ro->title;?></td></tr>
							
							<?php if(!($viewing_revision && !$currentRevision))
							{
								echo "<tr><th>Status</th><td>" . readable($ro->status, true) . "</td></tr>"; 
							}
							else
							{
								echo "<tr><th>Status</th><td style='background-color:#FF6633; color:white;'><b>SUPERSEDED</b></td></tr>"; 
							}
							?>
							<tr><th>Data Source</th><td><?php echo $ds->title;?></td></tr>
							<tr><th>Key</th><td style="width:100%; word-break:break-all;"><?php echo $ro->key;?></td></tr>
							<?php 
							if ($this->user->hasFunction('REGISTRY_STAFF')):
							?>
								<tr><th>ID</th><td><?php echo $ro->id;?></td></tr>						
								<tr><th>URL "Slug"</th><td><?php echo anchor(portal_url($ro->slug),$ro->slug);?></td></tr>
							<?php
							endif;
							?>
							<tr><th>Last edited by</th><td><?php echo $ro->getAttribute('created_who'); ?></td></tr>
							<tr><th>Date last changed</th><td><?php echo date("j F Y, g:i a", (int)$ro->getAttribute('updated')); ?></td></tr>
							<tr><th>Date created</th><td><?php echo date("j F Y, g:i a", (int)$ro->getAttribute('created')); ?></td></tr>
							<tr><th>Feed type</th><td><?php echo (strpos($ro->getAttribute('harvest_id'),'MANUAL') === 0 ? 'Manual entry' : 'Harvest');?></td></tr>
							<tr><th>Quality Assessed</th><td><?php echo ucfirst($ro->getAttribute('manually_assessed') ? $ro->getAttribute('manually_assessed') : 'no');?></td></tr>
							
							<?php 
								if($native_format != 'rif') {
									echo '<tr><th>Native Format</th><td><a href="javascript:;" class="btn btn-small" id="exportNative"><i class="icon-eject"></i>Export '.$native_format.'</a></td></tr>';
								}
							?>

							<tr><td colspan="2"><a class="btn btn-small btn-danger pull-right" id="delete_record_button"> <i class="icon-white icon-warning-sign"></i> Delete Record <i class="icon-white icon-trash"></i> </a></td></tr>
							<input type="hidden" id="registry_object_id" value="<?php echo $ro_id;?>"/>
						</table>
					</div>

				</div>
				<?php
				endif;
				?>

				<?php 
				if ($this->user->hasFunction('REGISTRY_STAFF')):
				?>
				<div class="widget-box">
					<div class="widget-title">
						<h5>Tags Management</h5>
					</div>
					<div class="widget-content">
						<?php $data['ro'] = $ro; $this->load->view('tagging_interface', $data);?>
					</div>
				</div>

				<?php
				endif;
				?>
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