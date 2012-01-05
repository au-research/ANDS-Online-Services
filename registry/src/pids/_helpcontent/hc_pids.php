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
<h2>Self Service <i>Identify My Data</i></h2>

<div id="create">
<h3>Create Identifier</h3>
<ul>

	<li>Before you can mint a persistent identifier you must agree to the
	terms and conditions of ANDS <i>Identify My Data</i> by checking the box
	marked 'I AGREE'. You will need to do this each time you mint a
	persistent identifier. It is important to carefully read the terms and
	conditions before you check the box. Once you have checked the 'I
	AGREE' checkbox, clicking on 'Continue' will take you to the Create
	identifier screen.</li>

	<li>This screen allows you to create ('mint') a new persistent identifier using the Handle system.</li>
	
	<li>ANDS recommends that if you have not used this service before and
	are unfamiliar with the Handle system you should read the ANDS
	Awareness level Guides on <a class="external" href="http://www.ands.org.au/guides/identify-my-data-awareness.html"><i>Identify My Data</i></a> and 
	<a class="external" href="http://www.ands.org.au/guides/persistent-identifiers-awareness.html">Persistent Identifiers</a>.</li>

	<li>Your persistent identifier must be associated with at least one
	value, which may be text information or a URL. Choose a property type
	from the drop down box and type a value into the value field. Click on
	'Submit'. The system will display your new Handle and the resolver
	(clickable) link to that Handle. Note that any values you add against a
	Handle will be able to be publicly viewed on the Handle system website.</li>
	
	<li>You can now add additional description or URL values against your
	Handle. You can also edit or delete values that you have already
	entered. <b>Note that you cannot delete the identifier itself</b>.</li>
	
	<li>The Handle system will resolve the link to the first URL field
	that is listed against your identifier. If you do not enter at least
	one URL against your Handle then the link will resolve to the Handle
	system display page which will show any text information you have
	entered for the Handle.</li>
	</li>
</ul>
</div>

<div id="list">
<h3>List My Identifiers</h3>
<ul>
	<li>This screen lists any identifiers that you have previously minted using <i>Identify My Data</i> self-service.</li>
	
	<li>Clicking on any Handle which appears on the list will take you to
	the View Identifier screen which will allow you to choose to edit the
	values associated with that Handle and to add additional values to that
	Handle. <b>Note that you cannot delete identifiers once they have been
	minted</b>.</li>
	
	<li>Note that if you update the first URL associated with your Handle
	it may take up to 30 minutes before the resolver link for your Handle
	resolves to the new URL.</li>
</ul>
</div>

<p>For more information please email <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a>.</p>