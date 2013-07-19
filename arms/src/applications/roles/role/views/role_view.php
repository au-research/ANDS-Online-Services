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
	<h1><?php echo $role->name;?></h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home')); ?>
	<?php echo anchor('/role', 'List Roles'); ?>
	<?php echo anchor('/roles/view/'.rawurlencode($role->role_id), $role->name, array('class'=>'current'));?>
</div>
<div class="container-fluid">
	<div class="row-fluid">

		<div class="span8">
			<div class="widget-box">
				<div class="widget-title"><h5>Functional Roles</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($roles['functional_roles'] as $f):?>
						<li><?php echo anchor('/role/view/'.rawurlencode($f), $f);?> <i class="icon icon-remove"></i></li>
					<?php endforeach;?>
					</ul>
					<form class="form-inline">
					<select>
						<option value=""></option>
						<?php foreach($missingFunctionalRoles as $f):?>
							<option value="<?php echo $f;?>"><?php echo $f;?></option>
						<?php endforeach ?>
					</select>
					<a href="javascript:;" class="btn"><i class="icon icon-plus"></i> Add</a>
				</form>
				</div>
			</div>

			<div class="widget-box">
				<div class="widget-title"><h5>Organisational Roles</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($roles['organisational_roles'] as $f):?>
						<li><?php echo $f;?></li>
					<?php endforeach;?>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="span4">
			<div class="widget-box">
				<div class="widget-title">
					<h5><?php echo $role->name;?></h5>
				</div>
				<div class="widget-content">
					<table class="table table-bordered data-table">
						<tbody>
							<tr>
								<th>ID</th>
								<td><?php echo $role->role_id;?></td>
							</tr>
							<tr>
								<th>Name</th>
								<td><?php echo $role->name;?></td>
							</tr>
							<tr>
								<th>Type</th>
								<td><?php echo $role->role_type_id;?></td>
							</tr>
							<tr>
								<th>Enabled</th>
								<td><?php echo $role->enabled;?></td>
							</tr>
							<tr>
								<th>Last Login</th>
								<td><?php echo $role->last_login;?></td>
							</tr>
							<tr>
								<th>Created When</th>
								<td><?php echo $role->created_when;?></td>
							</tr>
							<tr>
								<th>Created Who</th>
								<td><?php echo $role->created_who;?></td>
							</tr>
							<tr>
								<th>Modified When</th>
								<td><?php echo $role->modified_when;?></td>
							</tr>
							<tr>
								<th>Modified Who</th>
								<td><?php echo $role->modified_who;?></td>
							</tr>
						</tbody>
					</table>  
					<?php echo anchor('role/edit/'.rawurlencode($role->role_id), 'Edit', array('class'=>'btn btn-primary'));?>
					<?php echo anchor('role/delete/'.rawurlencode($role->role_id), 'Delete', array('class'=>'btn btn-danger'));?>
				</div>
			</div>
			
		</div>

	</div>

</div>

<?php $this->load->view('footer');?>