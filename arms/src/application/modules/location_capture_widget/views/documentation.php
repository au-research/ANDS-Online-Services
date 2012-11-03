<?php 
/*
 * XXX
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section>
	
<div class="row">
	<div class="span12">
		<div class="box">
			<div class="box-header clearfix">
				<h1><?=$title;?></h1>
			</div>
			
			<div class="row-fluid">
				
				<div class="span8">
				    <div class="alert alert-info">
					  <strong>Developer Zone!</strong> Some basic web development knowledge may be needed to implement this widget. 
					</div>
			    	 
			    	 <h4>What is this widget?</h4>
			    	 <p>
			    		 The ANDS Location Capture Widget allows you to instantly enrich your data 
				    	 capture system, adding geospatial capabilities such as custom drawings and
				    	 place name resolution (using the Australian Gazetteer Service and Google Maps API).
			    	 </p>
			    	 <p>
			    		 View the <a target="_blank" href="<?=asset_url('demo.html');?>">demo here</a>
			    	 </p>
			    	 <br/>
			    	 
			    	 
			    	 <h4>How does it work?</h4>
			    	 <p>Simply drop the following lines of HTML into your web form. You only need to
			    	 specify the name of the form field and the widget will do the rest!
			    	 </p>
			    	 
			    	 <em>Step 1.</em> Drop this code somewhere in the &lt;head&gt;&lt;/head&gt; of your web page
			    	 <pre class="prettyprint">
&lt;script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=drawing"&gt;&lt;/script&gt;
&lt;script src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js'&gt;&lt;/script&gt;
&lt;link rel="stylesheet" type="text/css" href="http://services.ands.org.au/api/resolver/location_capture_widget.css" /&gt;
					</pre>
					
					<em>Step 2.</em> And put this section in the form where you want the widget to appear
					<pre class="prettyprint">
&lt;script type="text/javascript"&gt;
  mapInputFieldId = 'geoLocation';  // this will be the name of the form element containing the coordinates
&lt;/script&gt;
&lt;script type="text/javascript" src="http://services.ands.org.au/api/resolver/location_capture_widget.js"&gt;&lt;/script&gt;   </pre>
			    	 
			    	 <em>Step 3.</em> Load the web page and see the new widget appear! <br/><br/>Once submitted, the 
			    	 coordinates of the location selected will be in the form value you chose for <code>mapInputFieldId</code>
			    	 
			    	 
			    	 <br/><br/>
			    	 
			    	 <h4>Advanced Options / Customisation</h4>
			    	 
			    	 <h5>Can I load existing coordinate data into the widget? </h5>
			    	 <p>Yes, if you want the widget to display information that has already entered into a form, simply define the <code>latLon</code> variable before calling the widget:
			    	 <pre class="prettyprint">
&lt;script type="text/javascript"&gt;
  mapInputFieldId = 'geoLocation1';  // this will be the name of the form element containing the coordinates
  latLon = '132.121094,-19.110135';  // a number of comma-seperated latitude,longtitude pairs
&lt;/script&gt;
&lt;script type="text/javascript" src="http://services.ands.org.au/api/resolver/location_capture_widget.js"&gt;&lt;/script&gt;   </pre>	
					<em>latLong can only contain valid latitude,longtitude pairs (each pair seperated by a space). Place names specified as a latLon will cause the plugin to fail. </em>
			    	 </p>
			    	 <br/>
			    	 
			    	 <h5>What service is doing the placename resolution? </h5>
			    	 <p>ANDS hosts a resolver proxy service that provides JSONP results based on the response from the Gazetteer service. 
			    	 	An example of this script is included in the source code package. You can customise this proxy service yourself 
			    	 	and change the location by defining <code>mctServicePath</code> before calling the widget.
			    	 </p>
			    	 <br/>
			    	 
			    	 <h5>How can I customise the widget / not use the ANDS-hosted resources? </h5>
			    	 <p>Full source code for this widget is available and licensed under Apache License, Version 2.0.</p>
			    	 <br/>
			    	 
			    	
			    </div> 
			    
			    
			    
			    <div class="span4">
			    	<h5>Enrich your web forms in seconds...</h5>
			    	<p>
				    	<a href="<?=asset_url('demo.html');?>"><img src="<?=asset_url('img/resolution_widget1.png');?>" class="img-rounded" alt="Resolution of Place Names" /></a>
						<small class="pull-right"><em>Resolve place names to coordinates using the Australian Gazetteer Service and Google Maps API.</em></small>
					</p>
					<br/>
					<p>
				    	<a href="<?=asset_url('demo.html');?>"><img src="<?=asset_url('img/resolution_widget2.png');?>" class="img-rounded" alt="Draw Regions" /></a>
						<small class="pull-right"><em>Allow your users to provide richer location content by defining their own regions by drawing them on the map.</em></small>
					</p>
					
			    	 <br/><br/>
			    	<h4>Download Sourcecode</h4>
			    	 <a class="btn btn-success" href="http://services.ands.org.au/api/resolver/location_capture_widget.zip"><i class="icon-download icon-white"></i> &nbsp;Download Now - v0.1</a>
			    	 
			    	 <br/><br/>
					<h4>License</h4>
			    	 Licensed under the Apache License, Version 2.0. <br/>
			    	 <a href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>
			    	 
			    </div> 
			    
			    
		   </div>
		</div>
		
	</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>