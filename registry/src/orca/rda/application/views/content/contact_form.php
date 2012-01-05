<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>
<div class="box">
<h1>Contact Research Data Australia</h1>
<p>If you have any questions or queries regarding Research Data Australia or you are interested in contributing, please complete the following form or alternatively email <a href="mailto:services@ands.org.au">services@ands.org.au</a> and we will respond to your request as soon as possible.</p>
<p>
	<!-- Start Form -->
	<div id="contact-us-form">
	<input type="text" value="Name" default="Name" name="name" size="40" title="please input your name" id="contact-name"/>
	<input type="text" value="Email Address" default="Email Address" name="email" id="contact-email" size="40" title="please input a valid email address"/>
	<textarea name="content" rows="10" cols="40" id="contact-content" title="please enter some text" default=""></textarea>
	<button id="send-button">Send</button>
	</div>
	<!-- End Form -->
</p>
</div>
<?php $this->load->view('tpl/footer');?>