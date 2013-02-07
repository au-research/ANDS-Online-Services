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
					  <strong>Developer Zone!</strong><br/> Some basic web development knowledge may be needed to implement this widget.
				    </div>
			    	 <h4>What is this widget?</h4>
			    	 <p>
				   		The ANDS Vocabulary Widget allows you to instantly add Data Classification capabilities to your data capture tools through the ANDS Vocabulary Service. 
				   	 </p>
					 <p>
					   	The widget has been written in the style of a jQuery plugin, allowing complete control over styling and functionality with just a few lines of javascript.
					 </p>
					 <p>
					   	Currently the widget offers the ability to create a navigable "autocomplete", meaning that users can be prompted to use appropriate controlled vocabulary classifications when inputting data. 

					   	Tree-style concept browsing (such as that used in the RDA "Browse" screen) is coming soon. 
					 </p>
			    	 <p>
			    		 <a target="_blank" class="btn btn-success" href="<?=asset_url('demo.html');?>"><i class="icon-circle-arrow-right icon-white"></i> View the Demo</a>
			    	 </p>
			    	 <br/>


			    	 <h4>How does it work?</h4>
			    	 <p>
				   The widget requires jQuery; load this, and the plugin itself (and associated CSS styles) in your document's &lt;head&gt;&lt;/head&gt; segment:
<pre class="prettyprint pre-scrollable" style="min-height:5em">
&lt;script src='http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js'&gt;&lt;/script&gt;
&lt;script type="text/javascript" src="http://services.ands.org.au/api/resolver/vocab_widget.js"&gt;&lt;/script&gt;
&lt;link rel="stylesheet" type="text/css" href="http://services.ands.org.au/api/resolver/vocab_widget.css" /&gt;
</pre>
			    	 </p>
				 <p>
				   Ensure there is a text input box ready to accept user input:
				 </p>
<pre class="prettyprint pre-scrollable">
  &lt;input type="text" id="vocabInput"&gt;
</pre>
				 <p>
				   Then, either inside a <code>$(document).ready()</code> handler, or at the bottom of your document, call the plugin on a jQuery selector:
				 </p>
<pre class="prettyprint pre-scrollable">
  &lt;script text="text/javascript"&gt;
    $(document).ready(function() {
      $("#vocabInput").ands_vocab_widget();
    }
  &lt;/script&gt;
</pre>
                                 <p>
				   The plugin accepts a suite of options, detailed below.
				 </p>

			    	 <h4>Advanced Options / Customisation</h4>
			    	 <p>
				   The following options can be passed into the plugin using a Javascript hash/object, such as <code>$("#vocabInput").ands_vocab_widget({cache: false});</code>. Be sure to quote strings, and separate multiple options with a comma (<code>,</code>).
			    	 </p>
				 <table class="table" style="font-size:0.9em">
				   <caption>
				     <strong>Legend:</strong>
				     <span class="badge badge-info">S</span>: String,
				     <span class="badge badge-info">I</span>: Integer,
				     <span class="badge badge-info">B</span>: Boolean,
				     <span class="badge badge-info">[n]</span>: Array of 'n'
				   </caption>
				   <thead>
				     <tr>
				       <th style="width:25%">Option</th>
				       <th>Default value</th>
				       <th>Description</th>
				   </thead>
				   <tbody>
				     <tr>
				       <td>endpoint <span class="pull-right badge badge-info">S</span></td>
				       <td>"http://services.ands.org.au/api/resolver/vocab_widget/"</td>
				       <td>Location (absolute URL) of the (JSONP) SISSvoc provider.</td>
				     </tr>
				     <tr>
				       <td>mode <span class="pull-right badge badge-info">S</span></td>
				       <td>"search"</td>
				       <td>Reserved for future use. Currently, only "search" is allowed.</td>
				     </tr>
				     <tr>
				       <td>max_results <span class="pull-right badge badge-info">I</span></td>
				       <td>100</td>
				       <td>At most, how many results should be returned?</td>
				     </tr>
				     <tr>
				       <td>min_chars <span class="pull-right badge badge-info">I</span></td>
				       <td>3</td>
				       <td>How many characters are required before a search is executed?</td>
				     </tr>
				     <tr>
				       <td>delay <span class="pull-right badge badge-info">I</span></td>
				       <td>500</td>
				       <td>How long to wait (after initial user input) before executing the search? Provide in milliseconds</td>
				     </tr>
				     <tr>
				       <td>cache <span class="pull-right badge badge-info">B</span></td>
				       <td>true</td>
				       <td>Cache search results?</td>
				     </tr>
				     <tr>
				       <td>nohits_msg <span class="pull-right badge badge-info">S</span> <span class="pull-right badge badge-info">B</span></td>
				       <td>"No matches found"</td>
				       <td>Message to display when no matching concepts are found. Set to <span class="badge badge-info">B</span> <code>false</code> to suppress such messages</td>
				     </tr>
				     <tr>
				       <td>error_msg <span class="pull-right badge badge-info">S</span> <span class="pull-right badge badge-info">B</span></td>
				       <td>"ANDS Vocabulary Widget service error"</td>
				       <td>Message title to display when an error is encountered. Set to <span class="badge badge-info">B</span> <code>false</code> to suppress such messages</td>
				     </tr>
				     <tr>
				       <td>list_class <span class="pull-right badge badge-info">S</span></td>
				       <td>"ands_vocab_list"</td>
				       <td>CSS 'class' references for the dropdown list. Separate multiple classes by spaces</td>
				     </tr>
				     <tr>
				       <td>fields <span class="pull-right badge badge-info">[S]</span></td>
				       <td>["label", "notation", "about"]</td>
				       <td>Which fields do you want to display? currently ('label', 'notation', 'about') are available</td>
				     </tr>
				     <tr>
				       <td>target <span class="pull-right badge badge-info">S</span></td>
				       <td>"notation"</td>
				       <td>What data field should be stored upon selection?</td>
				     </tr>
				   </tbody>
				 </table>
			    </div>



			    <div class="span4">
			    	<h5>Enrich your web forms in seconds...</h5>
				<p>
				  <a href="<?=asset_url('demo.html');?>"><img src="<?=asset_url('img/vocab_widget_screenshot.png');?>" class="img-rounded" alt="Resolution of Place Names" /></a>
						<small class="pull-right"><em>Allow data capture tools to quickly classify data through supported vocabularies (FOR, RIFCS, etc).</em></small>
				</p>

			    	 <br/><br/>
			    	<h4>Download Sourcecode</h4>
			    	 <a class="btn btn-success" href="<?=asset_url('ands_vocab_widget.zip');?>"><i class="icon-download icon-white"></i> &nbsp;Download Now - v0.1</a>

			    	 <br/><br/>
					<h4>License</h4>
			    	 Apache License, Version 2.0: <br/>

			    	 <a href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>

			    </div>


		   </div>
		</div>

	</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>
