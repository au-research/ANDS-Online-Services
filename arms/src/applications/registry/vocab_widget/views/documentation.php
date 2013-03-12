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
				   Currently the widget includes UI helpers for <strong>search</strong> and <strong>narrow</strong> modes. Search mode creates a navigable "autocomplete" widget, with users able to search for the appropriate controlled vocabulary classification when inputting data. Narrowing provides similar functionality by populating a select list with items comprising a base vocabulary classification URI.
				 </p>
				 <p>
				   Tree-style concept browsing (such as that used in the RDA "Browse" screen) is coming soon.
				 </p>
				 <p>
				   It is also possible to use the widget in a more programmatic manner; refer to the 'Advanced / core mode' section below for more details
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
				   What happens next very much depends on what you want to achieve:
				 </p>
				 <h5>1. Autocomplete search box</h5>
				 <p>
				   For search mode, ensure there is a text input box ready to accept user input:
				 </p>
<pre class="prettyprint pre-scrollable">
&lt;input type="text" id="vocabInput"&gt;
...
&lt;script text="text/javascript"&gt;
  $(document).ready(function() {
    $("#vocabInput").vocab_widget({mode:'search'[,... other options]});
  }
&lt;/script&gt;
</pre>

				 <h5>2. Drop-down / autocomplete constrained list (narrowing)</h5>
				 <p>
				   Narrow mode can be attached to a select element, or a text input box for an autocomplete-style list:
				 </p>
<pre class="prettyprint pre-scrollable">
&lt;select id="vocabInput"&gt;&lt;/select&gt;
...
&lt;script text="text/javascript"&gt;
  $(document).ready(function() {
    $("#vocabInput").vocab_widget({mode:'narrow'[,... other options]});
  }
&lt;/script&gt;
</pre>
				 <h5>3. Advanced / 'core' mode</h5>
				 <p>
				   Invoking the plugin with <code>mode:'advanced'</code> exposes core functionality without restricting you to UI element use, such as form input (text, select) elements or the like. Instead, you hook into <code>search</code> and <code>narrow</code> events, building the UI as best fits your needs.
				 </p>
				 <p>
				   A very basic example is shown below: it constructs a list of RIFCS identifier types.
				 </p>
<pre class="prettyprint pre-scrollable">
&lt;div id="ident-list"&gt;&lt;/div&gt;
...
&lt;script text="text/javascript"&gt;
  $(document).ready(function() {
    var elem = $("#ident-list");
    var widget = elem.vocab_widget({mode:'advanced'});
    widget.vocab_widget('repository', 'rifcs');

    //set up some handlers
    elem.on('narrow.avw', function(event, data) {
        var list = elem.append('&lt;ul /&gt;');
        $.each(data.items, function(idx, e) {
	    var link = $('&lt;a href="' + e['about'] + '"&gt;' +
		         e['label'] + '&lt;/a&gt;');
	    var item = $('&lt;li /&gt;');
	    item.append(link).append(' (' + e.definition + ')');
	    item.data('data', e);
	    list.append(item);
        });
    });
    elem.on('error.avw', function(event, xhr) {
        elem.addClass('error')
	    .empty()
	    .text('There was an error retrieving vocab data: ' + xhr);
    });

    //now, perform the vocab lookup
    widget.vocab_widget('narrow',
		        'http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType');



  }
&lt;/script&gt;

</pre>
				 <h6>Functions</h6>
				 <p>
				   This mode provides 2 functions: <strong>search</strong> and <strong>narrow</strong>. Both take an additional parameter; a search term, or narrow URI, respectively. The example above invokes the <strong>narrow</strong> function.
				 </p>
			    	 <h4>Configuration</h4>
                                 <p>
				   The plugin accepts a suite of options, detailed below. <strong>Please note</strong> that some options are required, and don't have default values (such as <code>mode</code>: you must provide values for such options. Incorrectly configured plugins will result in a javascript 'alert' box being displayed, describing the nature of the configuration problem.
				 </p>
			    	 <p>
				   Options are passed into the plugin using a Javascript hash/object, such as
<pre>
$("#vocabInput").vocab_widget({cache: false});
</pre>
                                 Be sure to quote strings, and separate multiple options with a comma (<code>,</code>).
			    	 </p>
				 <p>
				   Alternatively, options can be set after initialisation using the following form:
<pre>
$(...).vocab_widget('[option name]', [option value]);
</pre>
                                   This works for all options <strong>except</strong> <code>mode</code>, which must be specified at initialisation.
                                 </p>

				 <p>
				   Some options are specific to the chosen mode; the tables below are grouped in a way that makes this easy to comprehend
				 </p>
				 <table class="table" style="font-size:0.9em">
				   <caption>
				     <h4>Common options</h4>
				     <strong>Legend:</strong>
				     <span class="badge badge-info">S</span>: String,
				     <span class="badge badge-info">I</span>: Integer,
				     <span class="badge badge-info">B</span>: Boolean,
				     <span class="badge badge-info">[n]</span>: Array of 'n'<br/>
				     (required options with no default are marked <span style="background-color:#FFAAAA">like this</span>)
				   </caption>
				   <thead>
				     <tr>
				       <th style="width:22%;text-align:left">Option</th>
				       <th style="text-align:left">Default value</th>
				       <th style="text-align:left">Description</th>
				   </thead>
				   <tbody style="font-size:0.9em">
				     <tr>
				       <td><span style="background-color:#FFAAAA">mode</span> <span class="pull-right badge badge-info">S</span></td>
				       <td>-</td>
				       <td>Vocab widget mode: <code>search</code> provides an autocomplete widget on an HTML input element, while <code>narrow</code> populates an HTML select element with appropriate data. <code>advanced</code> mode exposes the core widget with no UI helpers.</td>
				     </tr>
				     <tr  class="required">
				       <td><span style="background-color:#FFAAAA">repository</span> <span class="pull-right badge badge-info">S</span></td>
				       <td>-</td>
				       <td>The SISSvoc repository to query (e.g. <code>anzsrc-for</code>, <code>rifcs</code>)</td>
				     </tr>
				     <tr>
				       <td>max_results <span class="pull-right badge badge-info">I</span></td>
				       <td>100</td>
				       <td>At most, how many results should be returned?</td>
				     </tr>
				     <tr>
				       <td>cache <span class="pull-right badge badge-info">B</span></td>
				       <td>true</td>
				       <td>Cache SISSvoc responses?</td>
				     </tr>
				     <tr>
				       <td>error_msg <span class="pull-right badge badge-info">S</span> <span class="pull-right badge badge-info">B</span></td>
				       <td>"ANDS Vocabulary Widget service error"</td>
				       <td>Message title to display (via a js 'alert' call) when an error is encountered. Set to <span class="badge badge-info">B</span> <code>false</code> to suppress such messages</td>
				     </tr>
				     <tr>
				       <td>fields <span class="pull-right badge badge-info">[S]</span></td>
				       <td>["label", "notation", "about"]</td>
				       <td>Which fields do you want to display? Available fields are defined by the chosen repository.<br/>
				       <div class="alert alert-small"><strong>Note:</strong> refer to mode-specific settings for further details</div></td>
				     </tr>
				     <tr>
				       <td>target <span class="pull-right badge badge-info">S</span></td>
				       <td>"notation"</td>
				       <td>What data field should be stored upon selection?</td>
				     </tr>
				     <tr>
				       <td>endpoint <span class="pull-right badge badge-info">S</span></td>
				       <td>"http://services.ands.org.au/api/resolver/vocab_widget/"</td>
				       <td>Location (absolute URL) of the (JSONP) SISSvoc provider.</td>
				     </tr>
				   </tbody>
				 </table>

				 <table class="table" style="font-size:0.9em">
				   <caption>
				     <h4>"Search" mode options</h4>
				     <strong>Legend:</strong>
				     <span class="badge badge-info">S</span>: String,
				     <span class="badge badge-info">I</span>: Integer,
				     <span class="badge badge-info">B</span>: Boolean,
				     <span class="badge badge-info">[n]</span>: Array of 'n'<br/>
				     (required options with no default are marked <span class="required">like this</span>)
				   </caption>
				   <thead>
				     <tr>
				       <th style="width:30%;text-align:left">Option</th>
				       <th style="width:30%;text-align:left">Default value</th>
				       <th style="text-align:left">Description</th>
				   </thead>
				   <tbody style="font-size:0.9em">
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
				       <td>nohits_msg <span class="pull-right badge badge-info">S</span> <span class="pull-right badge badge-info">B</span></td>
				       <td>"No matches found"</td>
				       <td>Message to display when no matching concepts are found. Set to <span class="badge badge-info">B</span> <code>false</code> to suppress such messages</td>
				     </tr>
				     <tr>
				       <td>list_class <span class="pull-right badge badge-info">S</span></td>
				       <td>"vocab_list"</td>
				       <td>CSS 'class' references for the dropdown list. Separate multiple classes by spaces</td>
				     </tr>
				   </tbody>
				 </table>


				 <table class="table" style="font-size:0.9em">
				   <caption>
				     <h4>"Narrow" mode options</h4>
				     <strong>Legend:</strong>
				     <span class="badge badge-info">S</span>: String,
				     <span class="badge badge-info">I</span>: Integer,
				     <span class="badge badge-info">B</span>: Boolean,
				     <span class="badge badge-info">[n]</span>: Array of 'n'<br/>
				     (required options with no default are marked <span style="background-color:#FFAAAA">like this</span>)
				   </caption>
				   <thead>
				     <tr>
				       <th style="width:30%;text-align:left">Option</th>
				       <th style="width:30%;text-align:left">Default value</th>
				       <th style="text-align:left">Description</th>
				   </thead>
				   <tbody style="font-size:0.9em">
				     <tr>
				       <td><span style="background-color:#FFAAAA">mode_params</span> <span class="pull-right badge badge-info">S</span></td>
				       <td>-</td>
				       <td>For narrow mode, <code>mode_params</code> defines the vocabulary item upon which to narrow.</td>
				     </tr>
				     <tr>
				       <td>fields <span class="pull-right badge badge-info">[S]</span></td>
				       <td>["label", "notation", "about"]</td>
				       <td>In narrow mode, this option <strong>must be overriden</strong> to be a single-element array of string  <span class="badge badge-info">[S]</span>. This selection defines the label for the select list options.</td>
				     </tr>
				     <tr>
				       <td>target <span class="pull-right badge badge-info">S</span></td>
				       <td>"notation"</td>
				       <td>What data field should be stored upon selection? In narrow mode, this field is used as the <code>value</code> attribute for the select list options</td>
				     </tr>
				   </tbody>
				 </table>

				 <h4>Events</h4>
				 <p>
				   When run in advance mode, events are fired to allow you to hook into the workflow and implement your customisations as you see fit.
				 </p>
				 <div class="alert alert-info">
				   Plugin event are placed in the <code>avw</code> namespace (AVW = ANDS Vocab Widget)
				 </div>
				 <table class="table" style="font-size:0.9em">
				   <thead>
				     <tr>
				       <th style="width:20%;text-align:left">Event name</th>
				       <th style="width:40%;text-align:left">Parameters</th>
				       <th style="text-align:left">Description</th>
				   </thead>
				   <tbody style="font-size:0.9em">
				     <tr>
				       <td>search.avw</td>
				       <td>
					 <ol>
					   <li>JS Event object</li>
					   <li>SISSVOC data object:
					     <ul>
					       <li>status: [OK, ERROR]</li>
					       <li>message: [response message]</li>
					       <li>limit: [results limited to this number]</li>
					       <li>items: array of:<ul>
						   <li>definition</li>
						   <li>label</li>
						   <li>about</li>
						   </ul></li>
					     </ul>
					   </li>
					 </ol>
				       </td>
				       <td>
					 Hook into the plugin's <code>search</code> core function; <code>data</code> is the search response.
				       </td>
				     </tr>
				     <tr>
				       <td>narrow.avw</td>
				       <td>
					 <ol>
					   <li>JS Event object</li>
					   <li>SISSVOC data object:
					     <ul>
					       <li>status: [OK, ERROR]</li>
					       <li>message: [response message]</li>
					       <li>limit: [results limited to this number]</li>
					       <li>items: array of:<ul>
						   <li>definition</li>
						   <li>label</li>
						   <li>about</li>
						   </ul></li>
					     </ul>
					   </li>
					 </ol>
				       </td>
				       <td>
					 Hook into the plugin's <code>narrow</code> core function; <code>data</code> is the search response.
				       </td>
				     </tr>
				     <tr>
				       <td>error.avw</td>
				       <td>
					 <ol>
					   <li>JS Event object</li>
					   <li>XMLHttpRequest</li>
					 </ol>
				       </td>
				       <td>
					 This event is fired whenever there is a problem communicating with the plugin's <code>endpoint</code>
				       </td>
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
			    	 <a class="btn btn-success" href="<?=asset_url('vocab_widget.zip');?>"><i class="icon-download icon-white"></i> &nbsp;Download Now - v0.1</a>

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
