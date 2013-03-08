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
			<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
			<li class=""><?php echo anchor('data_source/manage_records/'.$ds->id,'Manage Records');?></li>
			<li class="active"><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
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
				<div class="box">

					<div class="pull-right">
						<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://ands.org.au/resource/metadata-content-requirements.html#qualitylevels">Quality Level Definitions</a></span>
					
					  <select id="quality_report_status_dropdown">
					  	<option value="">All Records</option>
						<?php foreach($status_tabs as $status=>$label):?>
						    <option value="<?=$status;?>"><?=$label;?></option>
						<?php endforeach;?>
					  </select>
					  
					</div>

					<h4>Record Quality Overview <small>(<a href="<?=base_url('data_source/quality_report/'.$ds->id);?>">printable quality report</a> / <a href="<?=base_url('data_source/charts/getDataSourceQualityChart/'.$ds->id.'/ALL/csv');?>">download</a>)</small></h4>
					<div id="overall_chart_div" style="width:80%; margin:auto; min-height:250px;">
						<i>Loading data source quality information...</i>
					</div>





				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<div class="box">

					<h4>Record Status Overview <small>(<a href="<?=base_url('data_source/charts/getDataSourceStatusChart/'.$ds->id.'/csv');?>">download</a>)</small></h4>
					
					<div id="status_charts">
					</div>

				</div>
			</div>
		</div>	

		<div class="clearfix"></div>

	</div>


<?php $this->load->view('footer');?>