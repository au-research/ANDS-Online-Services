<?php 

/**
 * Registry Administration Panel 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/administration
 * @package registry/administration
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section id="registry-administration">
	
<div class="row">
	<div class="span6" id="registry-administration">
		<div class="box">
			<div class="box-header clearfix">
				<h1>Registry Administration</h1>
			</div>
		

			<div>	
			    <div class="box-content">

					<p>
						<a href="<?=base_url('administration/api_keys');?>" alt="API Keys">
							Web Service API Keys
						</a>
					</p>			    	
			    	<p>
						<a href="<?=base_url('administration/api_log');?>" alt="API Log">
							Web Service API Log
						</a>
					</p>

					<p>
						<a href="<?=base_url('administration/nla_pullback');?>" alt="API Log">
							NLA Party Pullback (Manual Trigger)
						</a>
					</p>

					<p>
						<a href="<?=apps_url('topics/update_index');?>">
							Update Topic List
						</a>
					</p>

			    </div>
			    
			</div>
		</div>
	</div>
</div>

</section>

</div>
<?php $this->load->view('footer');?>