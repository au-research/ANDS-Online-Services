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
<h1>Help</h1>
<p>
<div class="demo">

<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-tabs-vertical ui-helper-clearfix">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-tabs-selected ui-state-active ui-corner-left"><a href="#tabs-1">How to search</a></li>
		<li class="ui-state-default ui-corner-left"><a href="#tabs-2">How to do stuffs</a></li>
		<li class="ui-state-default ui-corner-left"><a href="#tabs-3">How to do stuffs</a></li>
	</ul>
	<div id="tabs-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
		<h2>How to Search</h2>
		<p>How to search here</p>
	</div>
	<div id="tabs-2" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
		<h2>How to do stuffs 1</h2>
		<p>Some content</p>
	</div>
	<div id="tabs-3" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
		<h2>How to do stuffs 2</h2>
		<p>Some content 2</p>
	</div>
</div>

</div>
</p>
</div>
<?php $this->load->view('tpl/footer');?>