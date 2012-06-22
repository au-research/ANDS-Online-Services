<h2><?php echo $vocab;?> / <?php echo $prefLabel;?> (<?php echo $notation;?>)</h2>

<?php //var_dump($r->{'result'});?>
<?php $p = $r->{'result'}->{'primaryTopic'};?>

<table class="vocab-info-table">
	<tr>
		<td>Prefered Label</td><td><?php echo $prefLabel;?></td>
	</tr>
	<tr>
		<td>URI</td><td><a href="<?php echo $uri;?>"><?php echo $uri;?></a></td>
	</tr>
	<tr>
		<td>Notation</td><td><?php echo $notation;?></td>
	</tr>

	<tr>
		<td>Resolve URL</td><td><a href="<?php echo $p->{'isPrimaryTopicOf'};?>"><?php echo $p->{'isPrimaryTopicOf'};?></a></td>
	</tr>

	<?php if(isset($p->{'broader'})):?>
	<tr>
		<td>Broader Concept</td>
		<td><a href="<?php echo $p->{'broader'}->{'_about'};?>"><?php echo $p->{'broader'}->{'_about'};?></a></td>
	</tr>
	<?php endif;?>
	<?php if(isset($p->{'narrower'})):?>
	<tr>
		<td>Narrower Concepts</td>
		<td>
			<?php
				if(is_array($p->{'narrower'})){
					foreach($p->{'narrower'} as $narrower){
						echo '<a href="'.$narrower->{'_about'}.'">'.$narrower->{'_about'}.'</a><br/>';
					}
				}else{
					echo '<a href="'.$p->{'narrower'}->{'_about'}.'">'.$p->{'narrower'}->{'_about'}.'</a><br/>';
				}
			?>
		</td>
	</tr>
	<?php endif;?>
</table>

<div id="vocab_uri" class="hide"><?php echo $uri;?></div>
<div id="exact_search_result">Loading exact match search result...</div>
<div id="narrower_search_result">Loading narrower search result...</div>