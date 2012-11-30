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
			    		 <a target="_blank" class="btn btn-success" href="<?=base_url('location_capture_widget/demo/');?>"><i class="icon-circle-arrow-right icon-white"></i> View the Demo</a>
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
			    	 <p>Yes, if you want the widget to display information that has already entered into a form, simply define the <code>lonLat</code> variable before calling the widget:
			    	 <pre class="prettyprint">
&lt;script type="text/javascript"&gt;
  mapInputFieldId = 'geoLocation1';  // this will be the name of the form element containing the coordinates
  lonLat = '132.121094,-19.110135';  // a number of comma-seperated longtitude,latitude pairs
  mctServicePath = 'http://services.ands.org.au/api/resolver/'; //ANDS hosts a resolver proxy service
&lt;/script&gt;
&lt;script type="text/javascript" src="http://services.ands.org.au/api/resolver/location_capture_widget.js"&gt;&lt;/script&gt;   </pre>	
					<em>lonLat can only contain valid longtitude,latitude pairs (each pair seperated by a space). Place names specified as a latLon will cause the plugin to fail. </em>
			    	 </p>
			    	 <br/>
			    	 
			    	 <h5>I'm getting an "insecure content" warning? Can the widget run under HTTPS?</h5>
			    	 <p>Yes! Ensure that all the <code>&lt;script&gt;</code> and <code>&lt;link&gt;</code> tags (from Step 1 &amp; 2) are pointing to the securely-hosted version of the resource.
			    	 	In other words, the URL starts with <b>https://</b> (such as <code>https://maps.google.com/api...</code>).
			    	 </p>
			    	 <br/>
			    	 
			    	 
			    	 <h5>What service is doing the placename resolution? </h5>
			    	 <p>ANDS hosts a resolver proxy service that provides JSONP results based on the response from the Gazetteer service. 
			    	 	An example of this script is included in the source code package. You can customise this proxy service yourself 
			    	 	and change the location by defining <code>mctServicePath</code> before calling the widget.
			    	 </p>
			    	 <br/>


			    	 <h5>How do I reference the Google Map object / how do I trigger rendering the map post-pageload?</h5>
			    	 <p>
			    	 	The Google Map container is required to be visible in order for the widget to render the map against it. If you have embedded the map in a modal form, this might not be possible. <br/><br/>

			    	 	In this case, you will need to call a trigger on the instance of the Google Map object. You can reference a specific map based on the <code>mapInputFieldId</code> prepended with <code>mct_control_</code>.<br/><br/>

						For example, if <a href="<?=base_url('location_capture_widget/demo/');?>">the demo</a> were in a modal-style form, we would call this function once the form was visible:<br/>
						<code>google.maps.event.trigger(mctMaps['mct_control_geoLocation'], 'resize');</code><br/><br/>

						<i>Thanks to eResearch Services, Griffith University for pointing out this use case.</i>
			    	 </p>
			    	 <br/>

			    	 <h5>How are the placename results ordered?</h5>
			    	 <p>
			    	 	The Gazetteer Web Service does not currently provide any effective mechanism for ordering search results. <br/><br/>

			    	 	At present, a crude ordering is performed by the resolver proxy (either hosted by ANDS or available in the open source package). It attempts to bubble potentially-significant
			    	 	place names to the top (such as states, suburbs and urban areas).<br/><br/>

			    	 	Note: As of December 2012, the proxy will return up to 500 results to search queries. This may cause some congestion and can be limited in either the Javascript or resolver. 
			    	 </p>
			    	 <br/>



			    	 <h5>How can I customise the widget / not use the ANDS-hosted resources? </h5>
			    	 <p>Full source code for this widget is available and licensed under Apache License, Version 2.0.</p>
			    	 <br/>
			    	
			    </div> 
			    
			    
			    
			    <div class="span4">
			    	<h5>Enrich your web forms in seconds...</h5>
			    	<p>
				    	<a href="<?=base_url('location_capture_widget/demo');?>"><img src="<?=asset_url('img/resolution_widget1.png');?>" class="img-rounded" alt="Resolution of Place Names" /></a>
						<small class="pull-right"><em>Resolve place names to coordinates using the Australian Gazetteer Service and Google Maps API.</em></small>
					</p>
					<br/>
					<p>
				    	<a href="<?=base_url('location_capture_widget/demo');?>"><img src="<?=asset_url('img/resolution_widget2.png');?>" class="img-rounded" alt="Draw Regions" /></a>
						<small class="pull-right"><em>Allow your users to provide richer location content by defining their own regions by drawing them on the map.</em></small>
					</p>
					
			    	 <br/><br/>
			    	<h4>Download Sourcecode</h4>
			    	 <a class="btn btn-success" href="<?=asset_url('location_capture_widget_v0.1.zip');?>"><i class="icon-download icon-white"></i> &nbsp;Download Now - v0.1</a>
			    	 
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