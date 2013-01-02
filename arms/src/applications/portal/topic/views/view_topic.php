<?php $this->load->view('rda_header');?>

<?php
if (isset($topic))
{
?>


<div class="main">
	<h2><?=$topic['name'];?></h2>
	<?=$topic['html'];?>
</div>
<div class="sidebar">
	
	<?php foreach($topic['auto_boxes'] AS $box_cfg): ?>
		<div>
			<h4><?=$box_cfg['heading'];?></h4>
		</div>
		<br/>
	<?php endforeach; ?>

	<?php foreach($topic['manual_boxes'] AS $box_cfg): ?>
		<div>
			<h4><?=$box_cfg['heading'];?></h4>
		</div>
		<br/>
	<?php endforeach; ?>

</div>
<div class="container_clear"></div>



<?php
}
else
{
	// Error:
	echo "The topic you requested does not exist!";
}
?>

<?php $this->load->view('rda_footer');?>