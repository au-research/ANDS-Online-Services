<?php $this->load->view('rda_header');?>
<div class="topic_list">

<h2>Topics listed in Research Data Australia</h2>

<ul class="topic_list">
<?php
foreach ($topics AS $key => $topic)
{
	echo "<li><div class='topic_display'>";
	echo "<a class='title' href='".base_url("topic/" . $key)."'>";
	echo "<img src='".$image_base_url.$key."_preview.png' alt='Preview Image for ".$topic['name']."' />";
	echo $topic['name'];
	echo "</a>";
	echo "</div></li>";
}
?>
</ul>

</div>

<div>&nbsp;</div>
<?php $this->load->view('rda_footer');?>