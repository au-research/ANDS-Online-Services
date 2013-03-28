<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<?php echo $content;?>
<script type="text/x-mustache"  id="related_object_search_result">
<ul class="search_related_list">
{{#results}}
	<li><a href="javascript:;" key="{{key}}" class="select_related"><img src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/><span>{{title}}</span></a></li>
{{/results}}
{{#no_result}}
	<li>No Result</li>
{{/no_result}}
</ul>
</script>
<?php $this->load->view('footer');?>