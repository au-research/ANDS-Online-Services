</div><!-- container -->

<div class="footer">
		<div class="foot">
			<p>Research Data Australia is an Internet-based discovery service designed to provide rich connections between data, projects, researchers and institutions, and promote visibility of Australian research data collections in search engines. 
				<a href="<?=base_url('home/about');?>">Read more about us...</a>
			</p>
			<p class="small">ANDS is supported by the Australian Government through the National Collaborative Research Infrastructure Strategy Program and the Education Investment Fund (EIF) Super Science Initiative.</p>
			<a href="http://www.innovation.gov.au/" class="gov_logo"><img src="<?php echo asset_url('images/gov_logo.jpg', 'core');?>" alt="" /></a>
			<a href="http://www.ands.org.au/" class="footer_logo"><img src="<?php echo asset_url('images/footer_logo.jpg', 'core');?>" alt="" /></a>			
		</div><!-- foot -->		
	</div><!-- footer -->	
	<div class="foot_nav">
		<div class="inner">
			<ul>
				<li><a href="<?=base_url('');?>">Home</a></li>
				<li><a href="<?=base_url('home/about');?>">About</a></li>				
				<li><a href="<?=base_url('home/contact');?>">Contact Us</a></li>
				<li><a href="<?=base_url('home/disclaimer');?>">Disclaimer</a></li>	
				<li><a href="<?=base_url('search/#!/q=/tab=collection');?>">All Collections</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=party');?>">All Parties</a></li>			
				<li><a href="<?=base_url('search/#!/q=/tab=activity');?>">All Activities</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=service');?>">All Services</a></li>				
				<li><a href="<?=base_url('topic/');?>">All Topics</a></li>
				<li><a href="<?=registry_url('');?>" target="_blank">ANDS Online Services</a></li>													
			</ul>
			<div class="clear"></div>
		</div><!-- inner -->
	</div><!-- foot_nav -->


	 <script>
        localStorage.clear();
        var base_url = '<?php echo base_url();?>';
        var default_base_url = "<?php echo $this->config->item('default_base_url');?>";
        var suffix = '#!/';
    </script>

	<!-- Zoo Scripts Untouched -->
	<script type="text/javascript" src="<?php echo asset_url('js/jquery-1.7.2.min.js', 'core');?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/jquery.flexslider-min.js', 'core');?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/jquery-ui.js', 'core');?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/script.js', 'core');?>"></script>

	<!-- base libraries -->
	<script type="text/javascript" src="<?php echo asset_url('lib/less-1.3.0.min.js', 'base');?>" type="text/javascript"></script>
	<script type="text/javascript" src="<?php echo asset_url('lib/mustache.js','base');?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('lib/jquery.ba-hashchange.min.js', 'base');?>"></script> <!-- Monitoring on Hash Change-->
    
    <?php if(isset($js_lib)): ?>
	    <?php foreach($js_lib as $lib):?>
	 		<?php if($lib=='googleapi'):?>
	            <script type='text/javascript' src='https://www.google.com/jsapi'></script>
	            <script type="text/javascript">
	            	localGoogle = google;
	            	google.load("visualization", "1", {packages:["orgchart"]});
				</script>
	        <?php endif; ?>
		<?php endforeach;?>
	<?php endif; ?>

	<!-- Module-specific styles and scripts -->
    <?php if (isset($scripts)): foreach($scripts as $script):?>
        <script src="<?php echo asset_url('js/' . $script);?>.js"></script>
    <?php endforeach; endif; ?>

</body>
</html>