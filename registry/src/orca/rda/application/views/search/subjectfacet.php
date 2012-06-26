<div id="subject_toolbar" class="toolbar">
	<input id="subject_search_filter"/>
	<select id="subject_category">
		<option value="anzsrcfor" <?php if($view=='anzsrcfor') echo 'selected="selected"';?>>ANZSRC-FOR</option>
		<option value="keywords" <?php if($view=='keywords') echo 'selected="selected"';?>>Keywords</option>
	</select>
</div>
<div id="subject_content"><?php echo $bigTree;?></div>