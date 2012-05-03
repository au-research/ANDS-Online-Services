<ul class="nav main">
	<?php if (isset($menu['menuitems'])) { printMenuItems($menu['menuitems']); }?>
						
	<li class="secondary">
	<?php if (isLoggedIn()){?>
		<a href="#"><?php echo $this->session->userdata('name') . ' <font color=red>(' . $this->session->userdata('notification'). ')</font>'; ?></a>
		<ul>
			<li><a href="#!/auth/whoami">Who Am I?</a></li>
			<li><a href="#" id="logout">Logout</a></li>
		</ul>
	<?php } else {?>
		<a href="#!/auth/login">Login</a>
	<?php } ?>
	</li>
	
</ul>