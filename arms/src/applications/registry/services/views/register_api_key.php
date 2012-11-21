<?php 

/**
 * Web Service API Key registration
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/services/controllers/services
 * @package ands/services
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section id="registry-web-services">
	
<div class="row">
	<div class="span12" id="registry-web-services-left">
		<div class="box">
			<div class="box-header clearfix">
				<h1>Registry Web Services <small>API Key Registration</small></h1>
			</div>
		
	
		
			<div>	
				<!-- getRIFCS -->
			    <div class="box-content">
			    	
				    <form>
					  <legend>Register for an API key</legend>
					  
					  <label><strong>Organisation</strong></label>
					  <input type="text" placeholder="Name of your project or institution">
					  
					  <label><strong>Contact Email Address</strong></label>
					  <input type="text" placeholder="Your email address">
					  
					  <label class="checkbox">
					    <input type="checkbox"> I agree to the Web Services Terms of Use
					  </label>
					  <button type="submit" class="btn">Register </button>
					</form>
					
			    </div>
			    
		</div>
	</div>
</div>

</section>

</div>
<?php $this->load->view('footer');?>