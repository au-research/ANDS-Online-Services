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
					  <!--a class="btn btn-link">Advanced Search <b class="caret"></b></a-->
					</form>
					<hr/>
					<div id="result">Search for collections to be imported</div>
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
										echo '<li class="to_import" ro_id="'.$c['registry_object_id'].'">';
										echo anchor('registry_object/view/'.$c['registry_object_id'],$c['title'], array('target'=>'_blank', 'tip'=>$c['key']));
										echo '  <a class="remove" href="javascript:;"><i class="icon icon-remove"></i></a>';
										echo '</li>';
									}
								}else{
									echo 'Add a work to be imported!';
								}
							?>
						</ul>
					</div>
					<a class="btn btn-primary" id="start_import">Start Importing</a>
					<a class="btn btn-small" id="view_xml">View ORCID XML</a>
					<p></p>
					<div class="alert alert-success hide" id="alert-msg">Your works have been updated in the ORCID registry</div>
					<div class="alert alert-error hide" id="error-msg">There was a problem accessing the server. Please try again.</div>
				</div>
			</div>
		</div>
	</div>

	<div class="widget-box">
		<div class="widget-title">
			<h5><?php echo $name;?> - Works</h5>
		</div>
		<div class="widget-content">
			<div class="dash_news">
			<?php foreach($bio['orcid-activities']['orcid-works']['orcid-work'] as $w):?>
				<h5><?php echo $w['work-title']['title']['value']; ?></h5>
				<p>
					<?php echo $w['url']['value'];?><br/>
					<?php echo $w['short-description']; ?>
				</p>
				<hr/>
			<?php endforeach;?>
			</div>
			<?php //var_dump($bio['orcid-activities']); ?>

		</div>
	</div>

</div>


<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
  </div>
</div>
<?php $this->load->view('footer');?>