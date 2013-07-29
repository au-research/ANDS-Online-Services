<?php 

/**
 * Viewing Role Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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


			<?php if(trim($role->role_type_id)=='ROLE_ORGANISATIONAL' || trim($role->role_type_id)=='ROLE_FUNCTIONAL'):?>
			<div class="widget-box">
				<div class="widget-title">
					<h5>Users</h5>
				</div>
				<div class="widget-content">
					<ul>
						<?php 
							foreach($users as $u){
								echo '<li>';
								echo anchor('/role/view/'.rawurlencode($u->role_id), $u->name);
								if($u->childs){
									echo '<ul>';
									foreach($u->childs as $uu){
										echo '<li>';
										echo anchor('/role/view/'.rawurlencode($uu->role_id), $uu->name);
										echo '</li>';
									}
									echo '</ul>';
								}
								//echo '<a href="javascript:;" class="remove_relation" tip="Remove This Role Relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'"><i class="icon icon-remove"></i></a>';
								echo '</li>';
							}
						?>
					</ul>
				</div>
			</div>
			<?php endif;?>

			<div class="widget-box">
				<div class="widget-title"><h5>Functional Roles</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($childs as $c):?>
						<?php
							if(trim($c->role_type_id) == "ROLE_FUNCTIONAL"){
								echo '<li>';
								echo anchor('/role/view/'.rawurlencode($c->parent_role_id), $c->name);
								echo '<a href="javascript:;" class="remove_relation" tip="Remove This Role Relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'"><i class="icon icon-remove"></i></a>';
								if($c->childs){
									echo '<ul>';
									foreach($c->childs as $cc){
										echo '<li>';
										echo anchor('/role/view/'.rawurlencode($cc->parent_role_id), $cc->name);
										if($cc->childs){
											foreach($cc->childs as $ccc){
												echo '<ul>';
												echo anchor('/role/view/'.rawurlencode($ccc->parent_role_id), $ccc->name);
												echo '</ul>';
											}
										}
										echo '</li>';
									}
									echo '</ul>';
								}
								echo '</li>';
							}
						?>
					<?php endforeach;?>
					</ul>
					<form class="form-inline">
						<select>
							<option value=""></option>
							<?php foreach($missingFunctionalRoles as $f):?>
								<option value="<?php echo $f;?>"><?php echo $f;?></option>
							<?php endforeach ?>
						</select>
						<a href="javascript:;" child="<?php echo $role->role_id;?>"class="btn add_role" tip="Add This Role Relation"><i class="icon icon-plus"></i> Add</a>
					</form>
				</div>
			</div>

			<div class="widget-box">
				<div class="widget-title"><h5>Organisational Roles</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($childs as $c):?>
						<?php
							if(trim($c->role_type_id) == "ROLE_ORGANISATIONAL"){
								echo '<li>';
								echo anchor('/role/view/'.rawurlencode($c->parent_role_id), $c->name);
								echo '<a href="javascript:;" class="remove_relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'" tip="Remove This Role Relation"><i class="icon icon-remove"></i></a>';
								echo '</li>';
							}
						?>
					<?php endforeach;?>
					</ul>
					<select>
						<option value=""></option>
						<?php foreach($missingOrgRoles as $f):?>
							<option value="<?php echo $f;?>"><?php echo $f;?></option>
						<?php endforeach ?>
					</select>
					<a href="javascript:;" child="<?php echo $role->role_id;?>"class="btn add_role" tip="Add This Role Relation"><i class="icon icon-plus"></i> Add</a>
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
								<td><?php echo readable($role->role_type_id);?></td>
							</tr>
							<tr>
								<th>Authentication Service</th>
								<td><?php echo readable($role->authentication_service_id);?></td>
							</tr>
							<tr>
								<th>Enabled</th>
								<td><?php echo readable($role->enabled);?></td>
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
					<a class="btn btn-danger" id="delete_role" role_id="<?php echo $role->role_id?>">Delete</a>
				</div>
			</div>
			
		</div>

	</div>

</div>

<?php $this->load->view('footer');?>