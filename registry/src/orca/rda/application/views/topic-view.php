<div id="top" class="top-corner">

	<ul id="breadcrumb" class="top-corner">

		<li><a href="<?php echo base_url(); ?>" class="crumb">Home</a></li>
		<li><a href="<?php echo base_url(); ?>topic/" class="crumb">Topics</a></li>
		<li><a class="crumb">
			<?php echo $topic_name; ?>
		</li>

		<div id="breadcrumb-corner">


						 <!-- AddToAny BEGIN -->

		       			 <div class="a2a_kit a2a_default_style no_print" id="share">
		        		<a class="a2a_dd" href="http://www.addtoany.com/share_save">Share</a>
		        		<span class="a2a_divider"></span>
		       			 <a class="a2a_button_linkedin"></a>
		        		<a class="a2a_button_facebook"></a>
		        		<a class="a2a_button_twitter"></a>
		        		<a class="a2a_button_wordpress"></a>
		        		<a class="a2a_button_stumbleupon"></a>
		        		<a class="a2a_button_delicious"></a>
		        		<a class="a2a_button_digg"></a>
		        		<a class="a2a_button_reddit"></a>
		        		<a class="a2a_button_email"></a>
		        		</div>
		        		<script type="text/javascript">
		        		var a2a_config = a2a_config || {};
		        		</script>
		        		<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
		</div>
	</ul>

</div>

<div id="item-view-inner" class="clearfix" itemscope="" itemType="http://schema.org/Thing">

<div id="left">

 	<div id="displaytitle" style="width:100%;"><h1 itemprop="name"><?php echo $topic_name; ?></h1></div>
 	<div class="clearfix"></div>
 	<?php echo $html; ?>
</div>

<!--  we will now transform the rights handside stuff -->
<div id="right">
	<script src='//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js'></script>

<?php
foreach($topic_boxes AS $box)
{

	echo "<script type=\"text/javascript\"><!--\n";
	foreach ($box AS $name => $param)
	{
		echo "ands_search_" . $name . " = \"" . rawurlencode($param) . "\";\n";
	}
	echo 'ands_search_service_point = "'.base_url().'api"'."\n";
	echo 'ands_search_portal_url = "'.base_url().'"'."\n";
	echo "\n//--></script>";
	echo '<script type="text/javascript"';
	echo ' src="' . base_url() . 'js/jswidget.js">';
	echo '</script>';
}

foreach($manual_boxes AS $box)
{

	echo "<div class='right-box'>" .
			"<h2>".$box['heading']."</h2>" .
			"<ul>";
	foreach ($box['items'] AS $item):
			echo "<li><a target='_blank' href='" . $item['url'] . "'>" . $item['title'] . "</a></li>";
	endforeach;

	echo 	"</ul>" .
		 "</div>";
}
?>

</div>

</div>