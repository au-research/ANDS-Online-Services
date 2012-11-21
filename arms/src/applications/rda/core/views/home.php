<?php $this->load->view('tpl/rda_header');?>
<div class="container">
	<div class="intro">
		<h3>What’s in Research Data Australia</h3>
		<div class="intro_box">
			<div class="intro_inner" id="intro_inner_1">
				<h4><a href="#">Collections <span>(41,478)</span></a></h4>
				Research datasets or collections of research materials.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
		<div class="intro_box">
			<div class="intro_inner" id="intro_inner_2">
				<h4><a href="#">Parties <span>(5,960)</span></a></h4>
				Researchers or research organisations that create or maintain research datasets or collections.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
					<div class="intro_box">
			<div class="intro_inner" id="intro_inner_3">
				<h4><a href="#">Services <span>(124)</span></a></h4>
				Services that support the creation or use of research datasets or collections.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
					<div class="intro_box">
			<div class="intro_inner" id="intro_inner_4">
				<h4><a href="#">Activities <span>(27,483)</span></a></h4>
				Projects or programs that create research datasets or collections.
			</div><!-- intro_inner -->
		</div><!-- intro_box -->
	</div><!-- intro -->
	<div class="right">
		<h3>Featured research domains</h3>
		<div class="flexslider">
			<ul class="slides">
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
			</ul>
		</div><!-- flexslider -->
		<div class="clear"></div>
		<h3>Who contributes to Research Data Australia?</h3>
		<p>61 research organisations from around Australia contribute information to Research Data Australia.</p> 			
		<a href="#"><strong>See All</strong></a>
	</div><!-- right -->		
	<div class="clear"></div>
</div><!-- container -->
<div class="social">
	<a href="#"><img src="<?php echo asset_url('images/facebook.png');?>" alt="" /></a><a href="#"><img src="<?php echo asset_url('images/twitter.png');?>" alt="" /></a> RSS,ATOM and Twitter feeds are now available. Learn more here....
</div><!-- social -->
<?php $this->load->view('tpl/rda_footer');?>