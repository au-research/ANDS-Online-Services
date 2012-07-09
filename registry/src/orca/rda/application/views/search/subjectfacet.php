<div id="subject_toolbar" class="toolbar" style="border-bottom:1px solid #ccc">
	<input id="subject_search_filter"/>
	
	<div id="subject_dropdown">
	Type: <select id="subject_category">
		<option value="anzsrcfor" <?php if($view=='anzsrcfor') echo 'selected="selected"';?>>ANZSRC-FOR</option>
		<?php
			$categories = $this->config->item('subjects_categories');
			foreach($categories as $key=>$subj){
				echo '<option value="'.$key.'" ';
				if($view==$key) echo 'selected="selected"';
				echo '>';
				echo $subj['display'];
				echo '</option>';
			}
		?>
	</select>
	<div class="clearfix"></div>
	</div>
</div>
<div id="subject_content"><?php echo $bigTree;?></div>