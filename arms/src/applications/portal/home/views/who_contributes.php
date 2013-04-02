<?php $this->load->view('rda_header');?>
<div class="container">
	<h3>Who contributes to Research Data Australia?</h3>
	<p><?php echo sizeof($groups);?> research organisations from around Australia contribute information to Research Data Australia.</p> 
	<div id="who_contributes">
		<ul>
			<?php 
				foreach($groups as $g=>$count){
					echo '<li><a href="'.base_url('search#!/q='.$g.'"').'>'.$g.' ('.$count.')</a></li>';
				}
			?>
		</ul>
	</div>
</div><!-- container -->
<?php $this->load->view('rda_footer');?>