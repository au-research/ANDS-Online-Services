<?php 

/**
 * PIDs Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>List PIDs</h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/pids', '<i class="icon-home"></i> List PIDs', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div id="pids">Loading...</div>
</div>

<script type="text/x-mustache" id="pids-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Roles</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>ID</th>
					<th>Stuff</th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td>{{id}}</td>
					<td>{{{stuff}}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php $this->load->view('footer');?>