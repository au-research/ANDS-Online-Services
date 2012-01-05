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
require '../_includes/help_content_init.php';
?>
<h2>Login</h2>


<p>Access to additional functionality provided by <i>ANDS Online Services</i>
(including <i>Publish My Data</i> and <i>Self Service Persistent Identifiers</i>) can
be gained by logging in.</p>

<p>In order to login you will need to select the login method that is
appropriate for you and the additional functionality that you wish to
access. There are currently two methods:</p>

<ol>
	<li><b>Login in using Australian Access Federation (AAF) Pilot
	credentials</b>.
	<ul>
		<li>Successful login using this method will provide access to Publish
		My Data and Self Service Persistent Identifiers.</li>
		<li>To login successfully using this method you will need to have an
		account on a participating identity provider within the AAF Pilot and
		that provider must release the auEduPersonSharedToken (referred to as
		the shared token) to us. We require release of the shared token so
		that we can uniquely identify you within the federation, as in order
		to provide self-service functionality we need to know who you are.</li>
		<li>Not all participating members release the shared token to us. If
		your participating identity provider does not release the shared token
		to us you can <a href="https://idp.arcs.org.au/idp_reg/">apply for an
		identity on the Australian Research Collaboration Service (ARCS)
		identity provider</a> (which does release the shared token to us).</li>
	</ul>
	</li>

	<li><b>Login in using local credentials</b>.
	
	<ul>
	<li>Successful login using this method is required for functionality
	beyond that obtained under the first method (including managing a data
	feed into the registry). Please contact us for further information
	about logging in this way.</li>
	</ul>
	</li>
</ol>
<p>For more information please email <a href="mailto:services@ands.org.au">services@ands.org.au</a>.</p>