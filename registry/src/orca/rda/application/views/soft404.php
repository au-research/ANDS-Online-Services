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
<div id="item-view-inner" class="clearfix">

<div id="left">
 	<div style="text-align:middle;"><div id="displaytitle"><h1 itemprop="name">Page/Record has been removed from the registry</h1></div></div>
 	<div class="clearfix"></div>
 	<div style="text-align:middle;">
 		You have reached a URL that is no longer valid. This is likely because the record you are looking for has been removed from the registry.
 		<br/><br/>
 		You may be able to locate similar records by searching for the record by title:
 			<a href="<?php echo base_url() . "search#!/q=" . rawurlencode($search_title); ?>/p=1/tab=All"><?php echo $search_title; ?></a>
 		<br/><br/>
 	</div>
</div>

</div>
<?php $this->load->view('tpl/footer');?>