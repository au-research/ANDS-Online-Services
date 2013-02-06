<?php $this->load->view('rda_header');?>
<div class="container less_padding">
	<?php
		if (isset($registry_object_contents))
		{
			echo "<h2>Oh Look -- a contributor!!!</h2>";
			echo "AND...some data from the registry about this contributor: " . var_export($some_random_data_for_the_view_to_parse, true) . BR.BR;
			$this->load->view('main'); 
		}
		else
		{
			echo "<h4>Error: Registry Object could not be loaded</h4>";
		}
	?>
</div>
<?php $this->load->view('rda_footer');?>