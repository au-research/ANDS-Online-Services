<?php $this->load->view('rda_header');?>
<div class="container less_padding" ng-app="portal_theme">
	<div class="breadcrumb">
		<?php echo anchor('/', 'Home', array('class'=>'crumb')); ?> / 
		<?php echo anchor('/theme_page/view/'.$page['slug'], ' '.$page['title'], array('class'=>'crumb')); ?>
	</div>
	<div class="main item-view-inner">
		<div class="page-title" id="pageTitle"><h1><?php echo $page['title']; ?></h1></div>
		
		<div class="post clear" ng-controller="init">
			<input type="hidden" id="slug" value="<?php echo $page['slug']; ?>">
			<?php foreach($page['left'] as $f): ?>

				<?php if($f['type']=='html'): ?>
					<?php echo $f['content']; ?>
				<?php endif; ?>

				<?php if($f['type']=='separator'): ?><hr/><?php endif; ?>

				<?php if($f['type']=='gallery'): ?>
						<?php foreach($f['gallery'] as $i): ?>
						<a colorbox href="<?php echo $i['src']; ?>" rel="<?php echo $f['title'] ?>"><img src="<?php echo $i['src']; ?>" alt="" style="width:100px;" rel="<?php echo $f['title']; ?>"></a>
						<?php endforeach; ?>
				<?php endif; ?>

				<?php if($f['type']=='search'): ?>
					<div class="theme_search search-result hide" id="<?php echo $f['search']['id']; ?>">
						<input type="hidden" value="<?php echo $f['search']['query']; ?>" class="theme_search_query">
						<?php foreach($f['search']['fq'] as $fq): ?>
							<input type="hidden" value="<?php echo $fq['value']; ?>" class="theme_search_fq" fq-type="<?php echo $fq['name'] ?>">
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		
	</div>
	<div class="sidebar">
		<?php foreach($page['right'] as $f): ?>
			<div class="right-box">
				<?php if($f['type']=='html'): ?>
					<h2><?php echo $f['title']; ?></h2>
					<p><?php echo $f['content']; ?></p>
				<?php endif; ?>

				<?php if($f['type']=='separator'): ?><hr/><?php endif; ?>

				<?php if($f['type']=='facet'): ?>
					<h2><?php echo $f['title']; ?></h2>
					<div class="theme_facet" search-id="<?php echo $f['facet']['search_id'] ?>" facet-type="<?php echo $f['facet']['type'] ?>"></div>
				<?php endif; ?>

				<?php if($f['type']=='gallery'): ?>
					<?php foreach($f['gallery'] as $i): ?>
					<a colorbox href="<?php echo $i['src']; ?>" rel="<?php echo $f['title'] ?>"><img src="<?php echo $i['src']; ?>" alt="" style="width:100px;" rel="<?php echo $f['title']; ?>"></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="container_clear"></div>
</div>

<script type="text/x-mustache" id="search-result-template">
{{#has_result}}
	<div class="tabs">
		<a href="<?php echo portal_url('search'); ?>#!/{{filter_query}}">All</a>
		{{#tabs}}
			<a href="<?php echo portal_url('search'); ?>#!/{{filter_query}}class={{inc_title}}" {{#current}}class="current"{{/current}}>{{title}}</a>
		{{/tabs}}
	</div>
	{{#result.docs}}
		<div class="post clear" ro_id="{{id}}">
			{{#contributor_page}}
			<span class="contributor hide" slug="{{slug}}">{{contributor_page}}</span>
			{{/contributor_page}}
			{{#logo}}
				<img src="{{logo}}" class="logo right"/>
			{{/logo}}
			{{#class}}
				<img src="<?php echo base_url();?>assets/img/{{class}}.png" class="class_icon icontip_{{class}}" type="{{class}}"/>
		    {{/class}}
			{{#list_title}}
				<a href="<?php echo base_url();?>{{slug}}" class="title">{{list_title}}</a>
			{{/list_title}}
			{{#description}}
				<div class="excerpt">
				  {{description}}
				</div>
		    {{/description}}
		</div>
	{{/result.docs}}
	<a href="<?php echo portal_url('search');?>#!/{{filter_query}}">View Full Search</a>
{{/has_result}}
</script>

<script type="text/x-mustache" id="facet-template">
<div class="widget facet_{{facet_type}}">
	<h3 class="widget_title">{{label}}</h3>
	<ul>
		{{#values}}
			<li><a href="<?php echo portal_url('search');?>#!/{{filter_query}}{{facet_type}}={{inc_title}}" class="filter" filter_type="{{facet_type}}" filter_value="{{title}}">{{title}} ({{count}})</a></li>
		{{/values}}
	</ul>
</div>
</script>
<?php $this->load->view('rda_footer');?>