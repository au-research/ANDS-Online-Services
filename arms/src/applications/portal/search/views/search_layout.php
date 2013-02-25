<?php $this->load->view('rda_header');?>
<div class="main">
	<div id="searchmap" class="hide"></div>
	<div class="page_title">
		<h1 id="selected_subject"></h1>
		<h3 id="selected_group"></h3>
	</div>
	<div class="tabs">
		<?php
			$tabs = array(
				'All' => array('facet_value'=>'all','display'=>'All','selected'=>true, 'count'=>0),
				'collection' => array('facet_value'=>'collection','display'=>'Collections','selected'=>false, 'count'=>0),
				'party' => array('facet_value'=>'party','display'=>'Parties','selected'=>false, 'count'=>0),
				'activity' => array('facet_value'=>'activity','display'=>'Activities','selected'=>false, 'count'=>0),
				'service' => array('facet_value'=>'service','display'=>'Services','selected'=>false, 'count'=>0)
			);
			
			foreach($tabs as $t){
				if($t['selected']){
					$s = 'current';
				}else $s = '';
				echo "<a href='javascript:;' class='filter ".$s."' filter_type='tab' filter_value=".$t['facet_value'].">".$t['display']."</a>";
			}
		?>
		
		<a href="javascript:;" class="toggle_sidebar"></a>	
		<div class="clear"></div>
	</div>
	<div class="pagination"></div>
	<div id="search-result"></div>
	<div class="pagination"></div>

</div><!-- main -->
<div class="sidebar">
	<div id="facet-result"></div>				
</div><!-- sidebar -->				
<div class="container_clear"></div>
<!-- <div class="border"></div> -->
	

<script type="text/x-mustache" id="search-result-template">
{{#docs}}
	<div class="post" ro_id="{{id}}">
		<a href="<?php echo base_url();?>{{slug}}" class="title">{{display_title}}</a>
		<div class="excerpt">
			{{{description_value}}}
		</div>
	</div>
{{/docs}}
</script>

<script type="text/x-mustache" id="pagination-template">
<div class="results_navi">
	<div class="results">{{numFound}} results ({{timeTaken}} seconds)</div>
	{{{pagination}}}
	<div class="clear"></div>
</div>
</script>

<script type="text/x-mustache" id="facet-template">
{{#facet_result}}
<div class="widget">
	<h3 class="widget_title">{{label}}</h3>
	<ul>
		{{#values}}
			<li><a href="javascript:;" class="filter" filter_type="{{facet_type}}" filter_value="{{title}}">{{title}} ({{count}})</a></li>
		{{/values}}
	</ul>
</div>
{{/facet_result}}
</script>

<?php $this->load->view('rda_footer');?>