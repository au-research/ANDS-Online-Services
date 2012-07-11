<div id="top" class="top-corner">

	<ul id="breadcrumb" class="top-corner">
		<li><a href="<?php echo base_url(); ?>" class="crumb">Home</a></li>
		<li><a href="#" class="crumb">Topics</a></li>
	</ul>

</div>

<div id="item-view-inner" class="clearfix">

<div id="left">

 	<div id="displaytitle" style="width:100%;"><h1 itemprop="name">Topics listed in Research Data Australia</h1></div>
 	<div class="clearfix"></div>

 	<ul>
 	<?php
 	foreach ($topics AS $key => $data)
 	{
 		echo "<li><a href='" . base_url() . "topic/" . $key ."'>".$data['name']."</a></li>";
 	}
 	?>
 	</ul>
</div>

</div>

</div>