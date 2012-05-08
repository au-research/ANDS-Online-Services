<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
require '../../_includes/help_content_init.php';
?>
<h2><i>Publish My Data</i></h2>

<div id="overview">
<h3>Overview</h3>
<ul>
	<li><i>Publish My Data</i> allows Australian researchers and research
	organisations to publicise the existence of research collections via
	the internet. <b>Collections must be accessible online</b>.</li>

	<li>ANDS prefers to harvest collection description information
	automatically, at the institutional level, as this allows for the
	responsibility of ongoing maintenance of collection description
	information to rest with the institution. However, ANDS recognises that
	this is not always possible. This self-service option is intended for
	use by researchers at organisations where there is no formal data
	archiving service and where ANDS has no distributed services. Please
	contact <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a> for more information on setting up an
	institutional harvest.</li>

	<li>This self-service option allows individuals to manually enter
	collection description information and obtain a persistent identifier
	for the collection. This information will be stored in the ANDS
	Collections Registry and will be discoverable through Research Data
	Australia. <b>The individual who enters the collection description
	information is responsible for any required future updates to this
	information</b>.</li>
	
	
	<li>Collection descriptions entered through the <i>Publish My Data</i> online
	service are not immediately accepted into the <i>ANDS Collection Registry</i>
	or <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. Descriptions are held in a PENDING state
	until an ANDS administrator approves the collection description. This
	is to ensure that obscene or malicious material is not published in
	<i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. This approval process will generally take less
	than 5 working days.</li>

	<li><i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> is a
	set of web pages describing data collections produced by or relevant to
	Australian researchers. It is designed to promote the visibility of
	research data collections in search discovery engines such as Google
	and Yahoo. <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> includes pages on:
	<ol>
		<li>Collections</li>
		<li>Activities (undertakings related to the creation, update, or
		maintenance of a collection, such as a project)</li>
		<li>Services (mechanisms for gaining some kind of access to or
		information about a collection, such as an RSS feed)</li>
		<li>Parties (persons or organisations that have some relationship to a
		collection, service, activity, or party).</li>
	</ol>
	</li>
</ul>

<p>When you create a collection description record with the Publish My
Data online service a party record will be automatically created for you
in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. You can view and edit this information using
the View Publisher details option from the left hand menu. If you wish
to have your party record removed from <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> you will
need to contact <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a></p>
</div>


<div id="list">
<h3>List My Published Collections</h3>
<ul>
	<li>This screen lists any collections that you have previously
	described using <i>Publish My Data</i>. <b>Clicking on the key for a
	collection description will take you to the View Collection screen
	which will allow you to edit that collection description.</b></li>
	<li>The list is presented as a table:
	<ol>
		<li>The first column is titled 'Created/Updated' and shows the most
		recent date and time that the status of a collection record was
		modified. This may be the date and time that you first created the
		record, or the date and time that the record was approved by an ANDS
		administrator, or the date and time that you last edited the
		record—whichever was the most recent.</li>
		
		<li>The second column shows the system status for the collection
		description. Collection descriptions entered through the <i>Publish My
		Data</i> online service are not immediately accepted into the ANDS
		Collection Registry or <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. Descriptions are held
		in a PENDING state until an ANDS administrator approves the collection
		description. This is to ensure that obscene or malicious material is
		not published in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. This approval process will
		generally take less than 5 working days.</li>
		
		
		<li>The third column shows the key for the collection description.
		This key is the persistent identifier that was allocated to your
		collection by the system. <b>Clicking on this key will take you to the
		View Collection screen which will allow you to edit a collection
		description</b>. You can find out more about persistent identifiers in the
		<a class="external" href="http://ands.org.au/guides/persistent-identifiers-working.html">ANDS Guide to Persistent Identifiers</a>.</li>
		
		<li>The fourth column shows the title which you entered for the
		collection.</li>
		
		<li>The fifth column shows the URL that you entered as the location
		for your collection.</li>
	</ol>
	</li>
</ul>
</div>


<div id="add">
<h3>Publish a Collection</h3>
<ul>
<li>Before you can publish a collection description you must agree to the
	terms and conditions of ANDS <i>Publish My Data</i> by checking the box
	marked 'I AGREE'. You will need to do this each time you publish a
	collection description. It is important to carefully read the terms and
	conditions before you check the box. Once you have checked the 'I AGREE' checkbox, 
	clicking on <input type="button" value="Continue" /> will take you to the Add
	Collection screen.</li> 

	<li>This screen allows you to publish a description for your collection.
	The <i>Type</i>, <i>Title</i>, <i>URL</i> and <i>Description</i> fields are mandatory. You may
	enter information in any or all of the subsequent fields. Entering this
	additional information will enrich the value of your collection record.</li>
</ul>

<h4 id="type">Type</h4>
<ul>
	<li>This field is mandatory and allows you to enter a type for your
	collection. You can select a type by clicking on the vocabulary widget
	<img src="<?php print eAPP_ROOT.'orca/_images/_controls/_vocab_control/vc_icon_inactive.gif' ?>" alt="" /> located at the right of the entry box or if none of
	these apply you can enter your own. Types included in the widget are:
	<ul>
		<li><b>catalogueOrIndex</b>: a set of resource descriptions describing
		the content of one or more repositories or collective works</li>
		<li><b>collection</b>: aggregated items created as separate and
		independent works and assembled into a collective whole for
		distribution and use--you should only use this type where none of the
		other types apply</li>
		<li><b>registry</b>: a group of descriptive objects collected to
		support the business of a given community</li>
		<li><b>repository</b>: a collection of physical or digital objects
		collected for information and documentation purposes and/or for
		storage and safekeeping</li>
		<li><b>dataset</b>: collection of physical or digital objects
		generated by research activities.</li>
	</ul>
	</li>
</ul>

<h4>Title</h4>
<ul>
	<li>This field is mandatory and allows you to enter a title for your
	collection. You should ensure that this name will be meaningful to
	researchers outside your discipline area.</li>
</ul>

<h4>URL</h4>
<ul>
	<li>This field is mandatory and allows you to enter the online
	location of your collection. If your collection is not located online
	then you cannot use this <i>Publish My Data</i> self-service option. However,
	it is possible to register your collection with ANDS using other ANDS
	services. Contact <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a> if you would like more
	information on these options.</li>
</ul>

<h4>Description</h4>
<ul>
	<li>This field is mandatory and allows you to provide a short
	description of your collection. You should ensure that this description
	will be meaningful to researchers outside your discipline area. Try to
	include key information that will help other researchers to discover
	your collection via text searching. Do not use discipline-specific
	acronyms.</li>
</ul>

<h4>Contributors</h4>
<ul>
	<li>This field is optional and allows you to acknowledge any
	contributors to the collection other than yourself. You should enter
	their name and role in the form: Dr John Smith, Research Assistant. You
	can include institutions or organisations in this section. e.g.
	Australian National University.</li>
	<li>You should put each contributor on a new line.</li>
	<li>Contributors you list in this section will not become parties in
	<i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. This is because you cannot agree to the terms
	and conditions of the ANDS <i>Publish My Data</i> service on their behalf.
	However, the text that you enter in this section will appear in
	<i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> and will be discoverable via the 
	<i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> text search function.</li>
</ul>

<h4>ANZSRC Fields of Research</h4>
<ul>
	<li>This field is optional and allows you to add one or more ANZSRC fields of research that are relevant for your collection to your collection description. 
	You can find more information about the <a class="external" href="http://www.abs.gov.au/ausstats/abs@.nsf/0/4AE1B46AE2048A28CA25741800044242?opendocument">ANZSRC fields of
	research on the ABS website</a>.</li>
	
	<li>You can choose a field of research from the vocabulary widget <img src="<?php print eAPP_ROOT.'orca/_images/_controls/_vocab_control/vc_icon_inactive.gif' ?>" alt="" />
	located at the right of the entry box.</li>
	
	<li>If you already know the relevant ANZSRC code number you can enter the code into this field without using the widget.</li>
	
	<li>Click on the <button class="buttonSmall">add</button> button to add an
	additional ANZSRC field of research for your collection. You can add as
	many ANZSRC codes as you would like to your record.</li>
	<li>Click on the <button class="buttonSmall">remove</button> button to remove
	a field of research. Clicking on the <button class="buttonSmall">remove</button> button will remove the field even if it is empty.</li>
	<li>Only the code for the ANZSRC field of research that you have
	selected will appear in the data entry field. However, both the code
	and the text label associated with the code will appear in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. If you entered a code without using the vocabulary
	widget and your code is invalid, no text label will appear in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>.</li>
</ul>

<h4>Subject Keywords</h4>
<ul>
	<li>This field is optional and allows you to type in one or more
	subject keywords relevant to your collection record.</li>
	<li>Click on the <button class="buttonSmall">add</button> button to add an
	additional subject keyword for your collection. You can add as many
	subject keywords as you would like to your record.</li>
	<li>Click on the <button class="buttonSmall">remove</button> button to remove
	a subject keyword. Clicking on the <button class="buttonSmall">remove</button>
	button will remove the field even if it is empty.</li>
</ul>

<h4>Spatial Coverage</h4>
<ul>
	<li>This field is optional. Spatial coverage information will not be relevant for all
	collections.</li>
	
	<li>Choose a method for marking coverage from the options in the black
	bar above the map. An active option is indicated by its orange colour
	and underline. Active options can be deactivated by clicking them. Points and regions are marked in blue,
	editable points and regions are marked in orange. The options function
	as follows:

	<ul>
		<li><b>point</b><br />
		This will allow you to drop a marker to designate a point by clicking
		on the map. If a point marker already exists on the map then selecting
		the point option will make the marker editable (indicated by its
		orange colour). An editable marker may be repositioned by dragging it. You can exit
		from the edit state by clicking the highlighted point option.
		Alternatively, while in point edit mode you can simply drop a new marker by
		clicking on the map.</li>
		<li><b>region</b><br />
		This will allow you to designate a region by clicking on the map to
		mark the vertices of a polygon that defines the region. <i>The polygon
		must be closed</i> and this can be done by either clicking on the first
		vertex, or clicking on the last vertex you marked. If a region already
		exists on the map then selecting the region option will make the
		polygon editable (indicated by its orange colour and boxes at and
		between the vertices). An editable region can be modified by dragging
		the boxes on its perimeter. A vertex can be removed by clicking on it.
		You can exit from the edit state by clicking the highlighted region
		option. Alternatively while in region edit mode you can simply draw a new
		region on the map.</li>
		<li><b>search...</b><br />
		The search option can be used to lookup a place that can then be
		selected from the search results by clicking on it to mark a point or
		region on the map. Search results that represent a point are listed
		with a preceding &#8226; while results that represent a region are
		listed with a preceding &#9633;.</li>
		<li><b>coordinates...</b><br />
		You can describe the spatial coverage of your collection using
		longitude/latitude coordinates as per the <a class="external"
			href="http://code.google.com/apis/kml/documentation/kmlreference.html#coordinates">KML
		coordinates element</a>. Use commas to separate longitude from
		latitude in a coordinate and use spaces to separate the coordinates
		when defining a region. Enter a single coordinate to represent a
		point, for example 143.4947,-9.3985. Enter three or more coordinates
		with the last point being the same as the first point to represent a
		region, for example 143.4947,-9.3985 154.9387,-24.4123
		155.3876,-30.5956 149.4398,-45.5735 145.3856,-45.5232
		130.4967,-34.4765 117.4523,-37.3957 113.5287,-34.4645
		112.4745,-21.3876 129.4475,-10.3825 143.4947,-9.3985. Once you have
		entered coordinates clicking the set button will mark the described
		point or region on the map. If you have entered invalid coordinate text
		you will be presented with an explanatory message and you will need
		to either correct the error and click set again or cancel.</li>
		<li><b>clear</b><br />This will remove any marker or region from the map.
		</li>
		<li><b>reset</b><br />
		This will restore the map to the state that it was in when the page
		loaded. Note that some actions may reload the page before the form is
		saved, and so reset will only return the map to its state at the last
		page load.</li>
	</ul>
	
	
	
	</li>
</ul>

<h4>Citation</h4>
<ul>
	<li>This field is optional and allows you type in a citation for the data collection you are describing.</li>
	<li>This field should not be used to cite publications which cite or are relevant to your data collection. If you would like to include this information in your record you should include it in the Description section above.</li>
	<li>The citation that you key in here will be reproduced in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> in exactly the same citation format that you enter it in here; ANDS will not alter or amend the citation format.</li>
</ul>


<h4>Access Rights</h4>
<ul>
	<li>Access rights information will not be relevant for all collections.</li>
	<li>This field is optional and allows you to describe the access
	rights for your collection, such as any security, privacy or ethics
	constraints that would prevent immediate access to your collection.</li>
</ul>
</div>


<div id="view">
<h3>View Collection</h3>
<ul>
	<li>You can access the View Collection screen by choosing the List My
	Published Collections option from the left-hand menu and clicking on the key of
	the collection that you want to view.</li>
	
	<li>This screen displays a collection description that you have
	previously entered.</li>
	
	<li>You can see what this record looks like in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>
	by clicking on the preview link.</li>
	
	<li>Clicking on the <input type="button" value="Edit" /> button will take
	you to the Edit Collection screen which will enable you to amend the
	collection information that you previously entered.</li>
	
	<li>Editing a collection description which has previously been approved
	and was displayed in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> will cause the collection
	description to revert to a status of PENDING and remove the description
	from <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> until the revised record has been
	approved. This is to ensure that obscene or malicious material is not
	published in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. This approval process will
	generally take less than 5 working days.</li>
	
	<li id="delete">Clicking on the <input type="button" value="Delete" /> button will allow you
	delete a collection description. The collection description will be
	removed from the ANDS Collections Registry and from Research Data
	Australia. <b>You will not be able to recover this information from ANDS
	once you have deleted it</b>.</li>
	
	<li>Deleting the collection description will not delete your party
	record from the ANDS Collections Registry or <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>.
	If you would like your party record deleted you should email
	<a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a></li>
</ul>
</div>


<div id="edit">
<h3>Edit Collection</h3>
<ul>
	<li>You can access the Edit Collection screen by choosing the List My
	Published Collections option from the left-hand menu and clicking on the key of
	the collection that you want to edit. You should then click on the
	<input type="button" value="Edit" /> button.</li>
	
	<li>This screen allows you to edit a collection description that you
	have previously saved.</li>
	
	<li>You cannot edit the persistent identifier that was automatically
	assigned as a key to your collection. You can find out more about
	persistent identifiers in the 
	<a class="external" href="http://ands.org.au/guides/persistent-identifiers-working.html">ANDS Guide to Persistent Identifiers</a>.</li>
	
	<li>Editing a collection description which has previously been approved
	and was displayed in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> will cause the collection
	description to revert to a status of PENDING and remove the description
	from <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> until the revised record has been
	approved. This is to ensure that obscene or malicious material is not
	published in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. This approval process will
	generally take less than 5 working days.</li>

</ul>
</div>


<div id="view_publisher">
<h3>View/Update My Details</h3>
<ul>
	<li>This screen displays your party record. This record was
	automatically generated by the system when you saved your collection
	description.</li>
	
	<li>You can see what this record looks like in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>
	by clicking on the preview link.</li>
	
	<li>Clicking on the <input type="button" value="Edit" /> button will take
	you to the View/Update My Details screen which will enable you to edit
	your name and email address.</li>

</ul>
</div>


<div id="edit_publisher">
<h3>Edit My Details</h3>
<ul>
	<li>This screen allows you to edit your name and address as they appear
	in your party record.</li>
	
	<li>You cannot amend the key for your party record.</li>
	
	<li>You cannot delete your party record. If you would like your party
	record deleted you should email <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a></li>
</ul>
</div>
