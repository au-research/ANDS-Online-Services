<?php $this->load->view('rda_header');?>

	<div class="intro">
		<h3>What’s in Research Data Australia</h3>
		<div class="intro_box">
			<div class="intro_inner" id="intro_inner_1">
				<h4><a href="#">Collections <span>(<?php echo $collection;?>)</span></a></h4>
				Research datasets or collections of research materials.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
		<div class="intro_box">
			<div class="intro_inner" id="intro_inner_2">
				<h4><a href="#">Parties <span>(<?php echo $party;?>)</span></a></h4>
				Researchers or research organisations that create or maintain research datasets or collections.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
					<div class="intro_box">
			<div class="intro_inner" id="intro_inner_3">
				<h4><a href="#">Services <span>(<?php echo $service;?>)</span></a></h4>
				Services that support the creation or use of research datasets or collections.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
					<div class="intro_box">
			<div class="intro_inner" id="intro_inner_4">
				<h4><a href="#">Activities <span>(<?php echo $activity;?>)</span></a></h4>
				Projects or programs that create research datasets or collections.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
	</div><!-- intro -->
	<div class="right">
		<h3>Featured research domains</h3>
		<div class="flexslider" id="spotlight">
			<!--ul class="slides">
				<li>
					<img src="images/t/auscope.jpg" alt="" />
					<a href="#" class="title">The Australian Plant Phenomics Facility</a>
					<div class="excerpt">
						Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua Lorem ipsum dolor sit amet, consectetur adipisicing elit. eiusmod tempor incididunt ut labore et dolore magna aliqua
					</div>
					<a href="http://www.australianplantphenomics.com.au"><strong>www.australianplantphenomics.com.au</strong></a>
				</li>	
											<li>
					<img src="images/t/auscope.jpg" alt="" />
					<a href="#" class="title">The Australian Plant Phenomics Facility2</a>
					<div class="excerpt">
						Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua Lorem ipsum dolor sit amet, consectetur adipisicing elit. eiusmod tempor incididunt ut labore et dolore magna aliqua
					</div>
					<a href="http://www.australianplantphenomics.com.au"><strong>www.australianplantphenomics.com.au</strong></a>
				</li>
			</ul-->
		</div><!-- flexslider -->
		<div class="clear"></div>
		<h3>Who contributes to Research Data Australia?</h3>
		<p>61 research organisations from around Australia contribute information to Research Data Australia.</p> 			
		<a href="#"><strong>See All</strong></a>
	</div><!-- right -->		
	<div class="clear"></div>



<script type="text/x-mustache" id="spotlight_template">
	<ul class="slides">
	{{#partners}}
		<li>
			<a href="#" class="title">{{{title}}}</a>
			<div class="excerpt">
				{{{description}}}
			</div>
			<a href="{{{url}}}"><strong>{{{url}}}</strong></a>
		</li>
	{{/partners}}
	</ul>
</script>

<?php $this->load->view('rda_footer');?>