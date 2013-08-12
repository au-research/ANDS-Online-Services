<?php 

/**
 * PIDs Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Persistent Identifier Service (PIDS)</h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/pids', '<i class="icon-home"></i> List My Identifiers', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div id="pids">Loading...</div>
</div>

<script type="text/x-mustache" id="pids-list-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Identifiers</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>Handle</th>
					<th>Info type</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td>{{handle}}</td>
					<td>{{type}}</td>
					<td>{{data}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>

<script type="text/x-mustache" id="trusted_clients-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Trusted Clients</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>IP</th>
					<th>App ID</th>
					<th>Description </th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td>{{ip_address}}</td>
					<td>{{app_id}}</td>
					<td>{{description}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php $this->load->view('footer');?>