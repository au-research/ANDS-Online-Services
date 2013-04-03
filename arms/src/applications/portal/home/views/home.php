<?php $this->load->view('rda_header');?>
<div class="container">
	<div class="intro">
		<h3>What’s in Research Data Australia</h3>
		
		<a href="<?=base_url('search/#!/q=/tab=collection');?>">
			<div class="intro_box">
				<div class="intro_inner" id="collection_icon">
					<h4>Collections <span>(<?php echo $collection;?>)</span></h4>
					Research datasets or collections of research materials.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

		<a href="<?=base_url('search/#!/q=/tab=party');?>">
			<div class="intro_box">
				<div class="intro_inner" id="party_icon">
					<h4>Parties <span>(<?php echo $party;?>)</span></h4>
					Researchers or research organisations that create or maintain research datasets or collections.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

		<a href="<?=base_url('search/#!/q=/tab=activity');?>">
			<div class="intro_box">
				<div class="intro_inner" id="activity_icon">
					<h4>Activities <span>(<?php echo $activity;?>)</span></h4>
					Projects or programs that create research datasets or collections.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

		<a href="<?=base_url('search/#!/q=/tab=service');?>">
			<div class="intro_box">
				<div class="intro_inner" id="service_icon">
					<h4>Services <span>(<?php echo $service;?>)</span></h4>
					Services that support the creation or use of research datasets or collections.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

	</div><!-- intro -->
	<div class="right">
		<h3>Spotlight on research data</h3>
		<div class="flexslider" id="spotlight">
		</div><!-- flexslider -->
		<div class="clear"></div>
		<h3>Who contributes to Research Data Australia?</h3>
		<p><?php echo sizeof($groups);?> research organisations from around Australia contribute information to Research Data Australia.</p> 			
		<a href="home/contributors" id=""><strong>See All</strong></a>
	</div><!-- right -->		
	<div class="clear"></div>

	<div class="hide" id="who_contributes">
		<ul>
		<?php 
			foreach($groups as $g=>$count){
				echo '<li><a href="search#!/q='.$g.'">'.$g.' ('.$count.')</a></li>';
			}
		?>
		</ul>
	</div>
	
	<!--div class="social">
		<a href="feed/rss"><img src="<?php echo asset_url('images/rss.png','core');?>" alt="" /></a><a href="https://twitter.com/andsdata"><img src="<?php echo asset_url('images/twitter.png','core');?>" alt="" /></a>
	</div-->


<script type="text/x-mustache" id="spotlight_template">
	<ul class="slides">
	{{#partners}}
		<li>
			<img src="{{img_url}}" alt="" />
			<a href="{{url}}" class="title">{{{title}}}</a>
			<div class="excerpt">
				{{{description}}}
				<p><a href="{{{url}}}"><strong>{{{url}}}</strong></a></p>
			</div>
			
		</li>
	{{/partners}}
	</ul>
</script>
</div>
<?php $this->load->view('rda_footer');?>