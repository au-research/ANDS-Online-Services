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

<script type="text/x-mustache"  id="save-record-template">
<div class="alert alert-success alert-block">This draft has been saved successfully</div>

<span class="label label-info">Quality Level : {{ro_quality_level}}</span>
<p>
	{{#qa_1}}
		This record meets some of the Metadata Content Requirements  satisfying  minimal requirements for discovery, but does not comply with the Minimum Metadata Content Requirements.
	{{/qa_1}}
	{{#qa_2}}
		Congratulations! This record satisfies the minimum Metadata Content Requirements.
	{{/qa_2}}
	{{#qa_3}}
		Congratulations! This record meets and exceeds the minimum Metadata Content Requirements.
	{{/qa_3}}
</p>

<div class="qa">
	{{{qa}}}
</div>

<div class="alert alert-info">
	<b>Note:</b> This record has been saved in <u>DRAFT state</u>
	
</div>

</script>
<script type="text/x-mustache"  id="save-error-record-template">
<div class="alert alert-error alert-block">This draft has not been saved due to validation errors in the record. <br/> Please refer to the tabs marked with a red error icon to the left of the page. </div>
<div class="alert well alert-error alert-block"><pre>{{message}}</pre></div>

</script>



<?php $this->load->view('footer');?>