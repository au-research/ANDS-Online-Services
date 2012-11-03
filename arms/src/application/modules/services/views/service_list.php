<?php 

/**
 * Service Listing
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
				<h1>Registry Web Services</h1>
			</div>
		
			<div class="alert alert-block">
				<h4>API Key Required</h4>
				You must <?php echo anchor('/services/register/','register for an API key');?> in order to utilise the ANDS Web Services.
			</div>
		
			<div>	
				<!-- getRIFCS -->
			    <div class="box-content">
			    	
			    	<h3>getRIFCS</h3>
			    	
			    	<div class="span8">
			    		<blockquote>
							<p class="lead">Request the RIFCS representation of an object stored in the registry.</p>
						</blockquote>
						<dl class="dl-horizontal">
						  <dt>Service URL: </dt>
						  <dd><code>.../services/&lt;your API key&gt;/getRIFCS.xml/?&lt;params&gt;</code></dd>
						</dl>
					</div>
					
			    	<div>
			    		<span class="label label-info">Head's Up</span><br/>Request a JSON response by substituting .json for .xml in the request URI
			    	</div>
			    	
			    </div>
			    
			    <hr/>
			    
			    <!-- getMetadata -->
			    <div class="box-content">
			    	
			    	<h3>getMetadata</h3>
			    	
			    	<div>
			    		<blockquote>
							<p class="lead">Search for specific metadata in the registry</p>
						</blockquote>
						<dl class="dl-horizontal">
						  <dt>Service URL: </dt>
						  <dd><code>.../services/&lt;your API key&gt;/getMetadata.json/?&lt;params&gt;</code></dd>
						</dl>
					</div>
				
			    </div>		    
			    
		</div>
	</div>
</div>

</section>

</div>
<?php $this->load->view('footer');?>