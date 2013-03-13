<?php $this->load->view('rda_header');?>
<div class="container">
	<h3>Who contributes to Research Data Australia?</h3>
	<p><?php echo sizeof($groups);?> research organisations from around Australia contribute information to Research Data Australia.</p> 
	<div id="who_contributes">
		<ul>
			<?php 
				foreach($groups as $g=>$count){
					echo '<li><a href="search#!/q='.$g.'">'.$g.' ('.$count.')</a></li>';
				}
			?>
		</ul>
	</div>
</div><!-- container -->
<div class="social">
	<a href="feed/rss"><img src="<?php echo asset_url('images/rss.png','core');?>" alt="" /></a><a href="https://twitter.com/andsdata"><img src="<?php echo asset_url('images/twitter.png','core');?>" alt="" /></a>
</div><!-- social -->
<?php $this->load->view('rda_footer');?>