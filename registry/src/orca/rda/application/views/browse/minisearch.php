<?php //var_dump($search_result);?>

<?php
	$numFound = $search_result->{'response'}->{'numFound'};
	$docs = $search_result->{'response'}->{'docs'};
	$data['json'] = $search_result;
?>

<div class="miniSearch" page="<?php echo $page;?>" type="<?php echo $type;?>">
	<?php if ($type=="both"):?>
		<h2>Subject matches: <?php echo $numFound;?> collections</h2>
	<?php else:?>
		<h2>Subject matches: <?php echo $numFound;?> collections</h2>
	<?php endif;?>
	<?php if($numFound>0):?>
	<div class="accordion">
		<?php foreach($docs as $doc):?>
		<h3><a href="#"><?php echo $doc->{'list_title'};?></a></h3>
		<div>
			<?php echo $doc->{'description_value'}[0];?>
			<hr/>
			<?php echo '<a href="'.base_url().$doc->{'url_slug'}.'" target="_blank" class="button">View Record</a>';?>
		</div>
		<?php endforeach;?>
	</div>

	<div class="toolbar">
	<span class="left" style="font-size:1em;"><?php

	$q = '*:*';
	$classFilter = 'collection';
	$typeFilter = 'All';
	$groupFilter = 'All';
	$licenceFilter = 'All';
	if ($type=='exact')
	{
		$subjectFilter = $vocab_uri;
	}
	else
	{
		// include narrower matches
		$subjectFilter = "~" . $vocab_uri;
	}

	$queryStr = '?q='.$q.'&classFilter='.$classFilter.'&typeFilter='.$typeFilter.'&groupFilter='.$groupFilter.'&subjectFilter='.$subjectFilter.'&licenceFilter='.$licenceFilter;
	echo "<div class='subscriptions' style='color:#555;float:left;'><div class='rss_icon'></div> Subscribe to this web feed. <a href='".base_url()."search/rss/".$queryStr."&subscriptionType=rss'>RSS</a>/<a href='".base_url()."search/atom/".$queryStr."&subscriptionType=atom'>ATOM</a></div>";


	?></span>
	<?php
		if($numFound > 5){
			$this->load->view('search/pagination', $data);
			echo '<div class="clearfix"></div>';
		}
	?>
	</div>
	<?php endif;?>
</div>