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
</div>
</div>




	</div>


<div id="footer" class="no_print">
	<?php echo anchor('','Home');?> |
	<?php echo anchor('home/about', 'About');?> |
	<?php echo anchor('home/contact', 'Contact');?> |
	<?php echo anchor('home/disclaimer', 'Disclaimer');?> |
	<?php echo anchor('search/browse/All/collection', 'All Collections');?> |
	<?php echo anchor('search/browse/All/party', 'All Parties');?> |
	<?php echo anchor('search/browse/All/activity', 'All Activities');?> |
	<?php echo anchor('search/browse/All/service', 'All Services');?> |
	<?php echo anchor('topic', 'All Topics');?> |
	<?php
		if(isset($key)){
			echo anchor($this->config->item('orca_url').'view.php?key='.urlencode($key), 'ANDS Online Services(current key)');
		}else echo anchor($this->config->item('orca_url'), 'ANDS Online Services');
	?>
</div>

	<script type="text/javascript">
  		var base_url = "<?php echo base_url(); ?>";
  		var secure_base_url = "<?php echo getHTTPs(base_url());?>";
		var service_url = "<?php echo service_url();?>";
		var enable_warning_notices = "<?php echo $this->config->item('enable_warning_notices');?>";
		var warning_notices = "<?php echo $this->config->item('warning_notices');?>";
	</script>


	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.5.2.min.js"></script> <!-- jQuery -->
	<script type="text/javascript" src="<?php echo base_url();?>js/multicol.js"></script> <!-- jQuery Multi Column-->

	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.tools.min.js"></script> <!-- jQuery Tools-->
	<!--  <script src="http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"></script>JQuery Tools -->
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.ba-hashchange.min.js"></script> <!-- Monitoring on Hash Change-->
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> <!--Google Map v3 from Google-->

	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-ui-1.8.14.custom.min.js"></script> <!-- jQuery UI-->
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.tipsy.js"></script> <!-- jQuery Tooltip-->
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.cookie.js"></script> <!-- jQuery Cookie-->

	
	<script type="text/javascript" src="<?php echo base_url();?>js/jstree/jquery.jstree.js"></script><!-- jQuery treeview -->

	<script type="text/javascript" src="<?php echo base_url();?>js/script.js"></script> <!-- DHTML and Searching -->
	<script type="text/javascript" src="<?php echo base_url();?>js/superfish.js"></script> <!-- Top menu -->
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.fancybox.pack.js?v=2.0.6"></script> <!-- FancyBox Image Lightbox -->


	<script type="text/javascript" src="<?php echo base_url();?>js/qtip/jquery.qtip.min.js"></script> <!-- FancyBox Image Lightbox -->

	<?php if ($this->agent->browser()=='Internet Explorer' && $this->agent->version() < 9):?>
		<!-- Rounded Corners for IE -->
    	<script type="text/javascript" src="<?php echo base_url();?>js/DD_roundies_0.0.2a-min.js"></script>
    	<script>
    		DD_roundies.addRule('.box', '10px');
    		DD_roundies.addRule('.hp-class-item', '10px');

    	</script>
	 <?php endif;?>


</body>
</html>