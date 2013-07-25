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
<div class="content-header">
	<h1>Roles Management</h1>
	<div class="btn-group">
				<a href="<?php echo base_url()?>role/add" class="btn btn-large"><i class="icon icon-plus"></i> Add Role</a>
			</div>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home')); ?>
	<?php echo anchor('/roles', 'List Roles', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div id="roles">Loading...</div>
</div>

<script type="text/x-mustache" id="roles-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Roles</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Type</th>
					<th>Enabled</th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td><a href="<?php echo base_url();?>role/view/{{{role_id}}}">{{name}}</a></td>
					<td><span class="label">{{type}}</span></td>
					<td>{{{enabled}}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>

<?php $this->load->view('footer');?>