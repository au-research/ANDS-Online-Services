<?php $this->load->view('rda_header');?>
<div class="main">
	<div class="page_title">
		<h1>Information and Computer Sciences</h1>
		<h3>Queensland University of Technology</h3>
	</div>
	<div class="tabs">
		<a href="#" class="current">All</a>
		<a href="#">Collections</a>
		<a href="#">Parties</a>
		<a href="#">Activities</a>
		<a href="#">Services</a>		
		<a href="#" class="tabs_nav"></a>	
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
<div class="border"></div>
	

<script type="text/x-mustache" id="search-result-template">
{{#docs}}
	<div class="post">
		<a href="#" class="title">{{display_title}}</a>
		<div class="excerpt">
			{{{description_value}}}
		</div>
	</div>
{{/docs}}
</script>

<script type="text/x-mustache" id="pagination-template">
<div class="results_navi">
	<div class="results">{{numFound}} results (0.006 seconds)</div>
	<div class="page_navi">
		Page: {{currentPage}}/{{totalPage}} |  <a href="#">First</a>  <span class="current">1</span>  <a href="#">2</a>  <a href="#">3</a>  <a href="#">4</a>  <a href="#">Last</a>
	</div>
	<div class="clear"></div>
</div>
</script>

<script type="text/x-mustache" id="facet-template">
{{#facet_result}}
<div class="widget">
	<h3 class="widget_title">{{label}}</h3>
	<ul>
		{{#values}}
			<li><a href="#">{{title}} ({{count}})</a></li>
		{{/values}}
	</ul>
</div>
{{/facet_result}}
</script>

<?php $this->load->view('rda_footer');?>