<?php 

/**
 * Core Maintenance Dashboard
 *  
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/vocab_services/_vocab
 * @package ands/vocab_services
 * 
 */
?>

<?php  $this->load->view('header');?>
<div class="container-fluid" id="main-content">
	<div id="stat"></div>
	<div id="ds"></div>
</div>

<script type="text/x-mustache" id="stat-template">
<div class="row-fluid">
	<div class="span12 center" style="text-align: center;">					
		<ul class="stat-boxes">
			<li>
				<div class="left peity_bar_good"><span>Database</span>Registry Objects</div>
				<div class="right">
					<strong>{{totalCountDB}}</strong>
				</div>
			</li>
			<li>
				<div class="left peity_bar_neutral"><span>SOLR Indexed</span>Registry Objects</div>
				<div class="right">
					<strong>{{totalCountSOLR}}</strong>
				</div>
			</li>
			<li>
				<div class="left peity_bar_bad"><span>Missing</span>Registry Objects</div>
				<div class="right">
					<strong>{{notIndexed}}</strong>
				</div>
			</li>
		</ul>
	</div>
</div>
</script>



<script type="text/x-mustache" id="ds-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Data Sources</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>id</th>
					<th>Title</th>
					<th>Total Registry Count</th>
					<th>Total Indexed</th>
					<th>Total Missing</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
			{{#dataSources}}
				<tr>
					<td>{{id}}</td>
					<td>{{title}}</td>
					<td>{{totalCountDB}}</td>
					<td>{{totalCountSOLR}}</td>
					<td>{{totalMissing}}</td>
					<td>
						<div class="btn-group">
							<button class="btn task reindex_ds" ds_id="{{id}}" data-loading-text="Reindexing">ReIndex</button>
							<button class="btn task btn-danger clearindex_ds" ds_id="{{id}}" data-loading-text="Clearing">Clear</button>
						</div>
					</td>
				</tr>
			{{/dataSources}}
			</tbody>
		</table>  
	</div>
</div>
</script>

<?php $this->load->view('footer');?>