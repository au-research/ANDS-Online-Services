<?php $this->load->view('rda_header');?>
<div class="container less_padding">
	<?php
		if (isset($registry_object_contents))
		{
			$this->load->view('main'); 
		}
		else
		{
			echo "<h4>Error: Registry Object could not be loaded</h4>";
		}
	?>
<div id="collections_explanation" class="hide">
	<strong>Collection</strong><br />
	Research dataset or collection of research materials.
</div>
<div id="activities_explanation" class="hide">
	<strong>Activity</strong><br />	
	Project or program that creates research datasets or collections.
</div>
<div id="services_explanation" class="hide">
	<strong>Service</strong><br />
	Service that supports the creation or use of research datasets or collections.
</div>
<div id="party_one_explanation" class="hide">
	<strong>Party</strong><br />
	Researcher or research organisation that creates or maintains research datasets or collections.
</div>
</div>
<?php $this->load->view('rda_footer');?>