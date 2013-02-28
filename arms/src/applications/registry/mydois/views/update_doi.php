<?php 

/**
 * UPDATE DOI
 * 
 * @author LIZ WOODS <liz.woods@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<h3>Update DOI <?=$doi_id;?></h3>

	<div class="box-content">
			    	
		<form action="<?=base_url('mydois/updateDoiUrl/');?>" method="POST">
			    	 	
			<label><strong>Enter the URL for <?=$doi_id ?></strong></label>
			<input type="text" name="new_url" value="<?=$url?>" />
			<input type="hidden" name="old_url" value="<?=$url?>"/>
			<input type="hidden" name="doi_id" value="<?=$doi_id?>"/>
			<input type="hidden" name="client_id" value="<?=$client_id?>"/>
			<br/>
			<button type="submit" class="btn">Update the DOI</button> 	  			    	  
		</form>
	</div>

