<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>

<div class="container" id="main-content">
	<div class="row">
		<div class="span12">
			<!-- <div class="alert alert-error">YOU DO NOT have ANY affiliations to ANY data source of ANY SORT!! fill in the form or there WILL BE BLOOD!</div> -->

			<div class="box">
				<div class="box-header clearfix"><h1>Publish My Data</h1></div>
				<div class="box-content">
					<form class="form-horizontal" action="publish_my_data/publish" method="post">
					  <div class="control-group">
					    <label class="control-label">Name</label>
					    <div class="controls">
					      <input type="text" name="name" required placeholder="Name" value="<?php echo $this->user->name();?>">
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label">Email Address</label>
					    <div class="controls">
					      <input type="email" name="email" required placeholder="Email Address">
					    </div>
					  </div>
					  <hr/>
					  <div class="control-group">
					    <label class="control-label">Data Source Title</label>
					    <div class="controls">
					      <input type="text" name="ds_title" required placeholder="Data Source Title">
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label">Reason for Publishing</label>
					    <div class="controls">
					      <textarea name="notes" placeholder="notes"></textarea>
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <button type="submit" class="btn btn-primary">Continue</button>
					    </div>
					  </div>
					</form>
				</div>
			</div>
			<!-- <div class="alert alert-error">you have been warned</div> -->
		</div>
	</div>
</div>
<?php $this->load->view('footer');?>