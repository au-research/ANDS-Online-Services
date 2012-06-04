<?php //var_dump($search_result);?>

<?php
	$numFound = $search_result->{'response'}->{'numFound'};
	$docs = $search_result->{'response'}->{'docs'};
	$data['json'] = $search_result;
?>

<div class="miniSearch" page="<?php echo $page;?>" type="<?php echo $type;?>">
	<h2>Vocab <?php echo $type;?> match: <?php echo $numFound;?> collections</h2>
	<?php if($numFound>0):?>
	<div class="accordion">
		<?php foreach($docs as $doc):?>
		<h3><a href="#"><?php echo $doc->{'list_title'};?></a></h3>
		<div>
			<?php echo $doc->{'description_value'}[0];?>
			<hr/>
			<?php echo '<a href="'.base_url().$doc->{'url_slug'}.'" class="button">View Record</a>';?>
		</div>
		<?php endforeach;?>	
	</div>
	<?php
		if($numFound > 5){
			$this->load->view('search/pagination', $data);
			echo '<div class="clearfix"></div>';
		}
	?>
	<?php endif;?>
</div>