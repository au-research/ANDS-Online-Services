<?php 

/**
 * Role Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Import Your Work</h1>
</div>
<div class="container-fluid" id="main-content">
	<div class="widget-box">
		<div class="widget-title">
			<h5>Import Your Work from Research Data Australia</h5>
		</div>
		<div class="widget-content">
			<h4><?php echo $name;?> <span class="label label-info"><?php echo $orcid_id; ?></span></h4>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title">
					<h5>Search for your relevant works in Research Data Australia</h5>
				</div>
				<div class="widget-content">
					<form class="form-search">
					  <div class="input-append">
					    <input type="text" class="search-query">
					    <button type="submit" class="btn">Search</button>
					  </div>
					  <a class="btn btn-link">Advanced Search <b class="caret"></b></a>
					</form>

				</div>
			</div>
		</div>
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title">
					<h5>Works to be imported</h5>
					
				</div>
				<div class="widget-content">
					<div id="works">
						<ul>
							<?php
								if(sizeof($suggested_collections) > 0){
									foreach($suggested_collections as $c){
										echo '<li class="to_import">';
										echo '<a>'.$c['title'].'</a>';
										echo '</li>';
									}
								}else{
									echo 'Add a work to be imported!';
								}
							?>
							
						</ul>
					</div>
					<a class="btn btn-primary">Start Importing</a>
					<a class="btn btn-small">View ORCID XML</a>
				</div>
			</div>
		</div>
	</div>

</div>

<?php $this->load->view('footer');?>