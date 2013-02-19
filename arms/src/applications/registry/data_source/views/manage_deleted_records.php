<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $ds->id;?>" id="data_source_id"/>
<div id="content" style="margin-top:45px;margin-left:0px">
	<div class="content-header">
		<h1><?php echo $ds->title;?></h1>
		<ul class="nav nav-pills">
			<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Status');?></li>
			<li class=""><?php echo anchor('data_source/manage_records/'.$ds->id,'Manage Records');?></li>
			<li class="active"><?php echo anchor('data_source/report/'.$ds->id,'Quality Reports');?></li>
			<li class=""><?php echo anchor('data_source/manage#!/settings/'.$ds->id,'Settings');?></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage/', 'List My Datasources');?>
		<?php echo anchor('data_source/manage#!/view/'.$ds->id, $ds->title);?>
		<a href="#" class="current"><?php echo $title;?></a>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<?php
					if($record_count == 0)
					{
						echo "No deleted records were found."; 
					}
					elseif($record_count == 1)
					{
						echo $record_count."deleted record"; 
					}
					else
					{
						echo "There are ".$record_count."deleted records found."; 
					}								
				 foreach($deleted_records as $key=>$history):?>
					<div class="widget-box" id=<?php echo '"'.$key.'"' ?>>
						<div class="widget-title">	
						<h5><?php echo 'Regsitry Object Key: '.$key;?></h5>						
						<div class="btn-group">
							<a class="btn dropdown-toggle importRecords" data-toggle="dropdown" href="javascript:;">
								View Record's History <span class="caret"></span>
							</a>
								<ul class="dropdown-menu">
									<?php foreach($history as $id=>$r):?>
										<li parentKey=<?php echo '"'.$key.'"' ?>
										<h5><?php echo $r['title']."(deted on:" .$r['deleted_date'].")"?></h5>
										
										<?php echo "<button class='btn btn-small viewrecord' record_key='".$r['id']."'><i class='icon icon-eye-open'></i> View RIF-CS</button>";?>								
										<?php echo "<button class='btn btn-small undelete_record' record_key='".$r['id']."'><i class='icon-download-alt'></i> Reinstatethis record</button>";?>								
										<?php echo "<div class='hide' id='".$r['id']."'>".htmlentities($r['record_data'])."</div>";?>
										</li>
									<?php endforeach;?>
								</ul>
							</div>
						</div>
					</div>
				<?php endforeach;?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<?php echo 'limit:'.$limit.'  offset: '.$offset; ?>
<div class="modal hide" id="myModal">
  	<div class="modal-header">
    	<button type="button" class="close" data-dismiss="modal">×</button>
    	<h3>RIF-CS</h3>
  	</div>
  	<div class="modal-body"></div>
  	<div name="loadingScreen" class="modal-body hide loading"> <b>Loading XML</b>
		<div id="remoteSourceURLDisplay"></div>
		<div class="progress progress-striped active">
			<div class="bar" style="width: 100%;"></div>
		</div>
	</div>								 
  			<!-- A hidden loading screen -->
	<div name="resultScreen" class="modal-body hide loading"></div>
  	<div class="modal-footer">
  		<button class='btn btn-small undelete_record' record_key=''><i class='icon-download-alt'></i> Reinstate this record</button>
  	</div>
</div>


<div class="hide" id="import-screen-success-report-template">
	<div class="alert alert-success">
		{{message}}
	</div>
	
	{{#log}}
		<pre class="well linenums">{{log}}</pre>
	{{/log}}
</div>

<?php $this->load->view('footer');?>