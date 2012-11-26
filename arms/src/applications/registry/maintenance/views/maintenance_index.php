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
<div class="container" id="main-content">
	<div class="row">
		<div class="span3">
			<div id="solr"></div>
		</div>
		<div class="span3">
			<div id="ds"></div>
		</div>
		<div class="span3">

		</div>
		<div class="span3">

		</div>
	</div>
</div>


<script type="text/x-mustache" id="solr-template">
<div class="box">
	<div class="box-header clearfix"><h1>Index Stat</h1></div>
	<div class="box-content">
		<p>Total Count (DB): <b>{{totalCountDB}}</b></p>
		<p>Total Count (SOLR): <b>{{totalCountSOLR}}</b></p>
		<hr/>
		<div class="btn-group btn-group-vertical">
			<button class="btn task" action="reindex_all">ReIndex Everything</button>
			<button class="btn task" action="reindex_missing_all">ReIndex <b>{{notIndexed}}</b> Missing</button>
			<button class="btn task" action="optimize">Optimize</button>
			<button class="btn task btn-danger" action="clearindex_all">Clear Everything</button>
		</div>
	</div>
</div>
</script>


<script type="text/x-mustache" id="ds-template">
<div class="box">
	<div class="box-header clearfix"><h1>Datasources</h1></div>
	<div class="box-content">
		<select class="chzn" id="dataSourceSelect">
		{{#dataSources}}
			<option value="{{id}}">{{title}}</option>
		{{/dataSources}}
		</select>
		<hr/>
		<div class="btn-group btn-group-vertical">
			<button class="btn task" action="reindex_ds">ReIndex</button>
			<button class="btn task" action="reindex_missing_ds">ReIndex <b>{{notIndexed}}</b> Missing</button>
			<button class="btn task btn-danger" action="clearindex_ds">Clear</button>
		</div>
	</div>
</div>
</script>

<?php $this->load->view('footer');?>