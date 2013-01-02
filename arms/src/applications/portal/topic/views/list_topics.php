<?php $this->load->view('rda_header');?>

<h2>Topics listed in Research Data Australia</h2>

<ul>
<?php
foreach ($topics AS $topic)
{
	echo "<li><a href='".base_url("topic/" . $topic['slug'])."'>" . $topic['name'] . "</a></li>";
}
?>
</ul>

<div>&nbsp;</div>
</script>
<?php $this->load->view('rda_footer');?>