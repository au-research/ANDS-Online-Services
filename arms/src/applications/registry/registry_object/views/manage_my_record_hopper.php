<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $data_source['id'];?>" id="data_source_id"/>
<div id="content" style="margin-top:45px;margin-left:0px">
	<div id="content-header">
		<h1>Manage My Record</h1>
		<div class="btn-group">
			<a class="btn btn-large" title="Manage Files"><i class="icon-file"></i></a>
		</div>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('registry_object/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<a href="#" class="current"><?php echo $data_source['title'];?></a>
		<div style="float:right">
			<a>Selected <b>3</b> / 146</a>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			Quick Filters
		</div>

		<div class="row-fluid" id="mmr_hopper">
			
		</div>
	</div>
</div>
<script type="text/x-mustache" id="mmr_status_template">
{{#status}}
<div class="span{{span_count}} hopper_{{status}}">

	<div class="widget-box">
		<div class="widget-title"><h5>{{status}} ({{count}})</h5></div>
		<div class="widget-content nopadding ro_content">
			<ul class="sortable connectedSortable" status="{{status}}">
				{{#ro}}
				<li id="{{id}}" class="status_{{status}}">
					<div>{{id}}</div>
					<div>{{title}}</div>
				</li>
				{{/ro}}
				{{#hasMore}}
				<li>Show more..</li>
				{{/hasMore}}
			</ul>
		</div>
	</div>
</div>
{{/status}}
</script>
<?php $this->load->view('footer');?>