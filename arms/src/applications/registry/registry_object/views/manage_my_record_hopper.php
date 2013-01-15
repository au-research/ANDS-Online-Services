<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $data_source['id'];?>" id="data_source_id"/>
<div id="content" style="margin-top:45px;margin-left:0px">
	<div id="content-header">
		<h1>Manage My Record</h1>
		<ul class="nav nav-pills">
			<li class="active"><a href="#">Manage</a></li>
			<li class=""><a href="#">Report</a></li>

		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('registry_object/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<a href="#" class="current"><?php echo $data_source['title'];?></a>
		<a href="#">Manage</a>
		<div style="float:right">
			<a>Selected <b>3</b> / 146</a>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<form class="form-search">
				<input type="text" class="input-medium search-query" placeholder="search">
				<button type="submit" class="btn">Search</button>
			</form>
		</div>

		<div class="pool" id="mmr_hopper">
			<div class="block hide">
				<div id="MORE_WORK_REQUIRED"></div>
				<div id="DRAFT"></div>
			</div>
			<div class="block hide">
				<div id="SUBMITTED_FOR_ASSESSMENT"></div>
			</div>
			<div class="block hide">
				<div id="ASSESSMENT_IN_PROGRESS"></div>
			</div>
			<div class="block hide">
				<div id="APPROVED"></div>
			</div>
			<div class="block hide">
				<div id="PUBLISHED"></div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>

<script type="text/x-mustache" id="mmr_status_template">
<div class="widget-box ro_box" status="{{name}}">
	<div class="widget-title stick">
		<span class="icon">{{count}}</span>
		<h5>{{display_name}}</h5>
		<div class="buttons"><a href="javascript:;" class="show_menu"><i class="icon-chevron-down no-border"></i></a></div>
		<div class="hide ro_menu">
			<ul class="nav nav-list">
				<li><a href="#">Select All</a></li>
				<li><a href="#">Sort by Title</a></li>
				<li><a href="#">Sort by Date Modified</a></li>
			</ul>
		</div>
	</div>
	<div class="widget-content nopadding">
		<ul class="sortable" connect_to="{{connectTo}}" status="{{name}}">
			{{#ro}}
			<li id="{{id}}" class="status_{{status}}">
			<div class="ro_title"><a ro_id="{{id}}" class="ro_preview">{{title}}</a></div>
			<div class="ro_content">
				<p>
					<span class="tag"><i class="icon icon-time"></i> {{updated}}</span>
					<img class="tag" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/> <span class="tag ql_2">2</span>
				</p>
			</div>
			<div class='clearfix'></div>
			</li>
			{{/ro}}
		</ul>
		{{#hasMore}}
		<span class="show_more" offset="{{offset}}" ds_id="{{ds_id}}" status="{{name}}">Show More</span>
		{{/hasMore}}
	</div>
</div>
</script>


<script type="text/x-mustache" id="mmr_data_more">
{{#items}}
<li id="{{id}}" class="status_{{status}}">
	<div class="ro_title"><a ro_id="{{id}}" class="ro_preview">{{title}}</a></div>
	<div class="ro_content">
		<p>
			<span class="tag"><i class="icon icon-time"></i> {{updated}}</span>
			<img class="tag" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/> <span class="tag ql_2">2</span>
		</p>
	</div>
	<div class='clearfix'></div>
	</li>
{{/items}}
</script>
<?php $this->load->view('footer');?>