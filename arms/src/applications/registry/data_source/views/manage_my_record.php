<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $ds->id;?>" id="data_source_id"/>
<div id="content" style="margin-top:45px;margin-left:0px">
	<div class="content-header">
		<h1><?php echo $ds->title;?></h1>
		<ul class="nav nav-pills">
			<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
			<li class="active mmr"><a href="#">Manage Records</a></li>
			<li class=""><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
			<li class=""><?php echo anchor('data_source/manage#!/settings/'.$ds->id,'Settings');?></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage/', 'List My Datasources');?>
		<?php echo anchor('data_source/manage#!/view/'.$ds->id, $ds->title);?>
		<a href="#" class="current">Manage Records</a>
		<div style="float:right">
			<a>Selected <b>3</b> / 146</a>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span6">
				<form class="form-search" id="search_form">
					<input type="text" class="input-medium search-query" placeholder="search">
					<button type="submit" class="btn">Search</button>
					<div class="btn-group">
						<button class="btn">Sort</button>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="javascript:;" class="sort" sort="updated" value="">Date Modified <span class="icon"></span></a></li>
							<li><a href="javascript:;" class="sort" sort="quality_level" value="">Quality Level  <span class="icon"></span></a></a></li>
						</ul>
					</div>
					<div class="btn-group">
						<button class="btn">Filter</button>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li <?php echo 'class="'.($ds->count_collection > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="collection">Collections (<?php echo $ds->count_collection;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_party > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="party">Parties (<?php echo $ds->count_party;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_service > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="service">Services (<?php echo $ds->count_service;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_activity > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="activity">Activities (<?php echo $ds->count_activity;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_1 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="1">Quality Level 1 (<?php echo $ds->count_level_1;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_2 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="2">Quality Level 2 (<?php echo $ds->count_level_2;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_3 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="3">Quality Level 3 (<?php echo $ds->count_level_3;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_4 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="4">Quality Level 4 (<?php echo $ds->count_level_4;?>)<span class="icon"></span></a></li>
							<li><a href="javascript:;" class="filter" name="flag" value="t">Flagged <span class="icon"></span></a></li>
						</ul>
					</div>
					<span id="active_filters">
						
					</span>
					
				</form>
			</div>

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
		<h5 class="ellipsis" style="width:60%">{{display_name}}</h5>
		<div class="buttons"><a href="javascript:;" class="select_all" status="{{name}}"><i class="icon-ok-circle no-border"></i></a></div>
	</div>
	<div class="widget-content nopadding ">
		<div class='selected_status hide'>asdfasdf</div>
		<ul class="sortable" connect_to="{{connectTo}}" status="{{name}}">
			{{#items}}
			<li id="{{id}}" data-toggle="context" data-target="#context-menu-{{status}}" class="status_{{status}} ro_item {{#has_error}}ro_error{{/has_error}}" status="{{status}}">
			<div class="ro_title"><a ro_id="{{id}}" class="ro_preview">{{title}}</a></div>
			<div class="ro_content ">
				<p>
					<span class="tag" tip="Last Modified"><i class="icon icon-time"></i> {{updated}}</span>
					<img class="tag" tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/>
					<span class="tag ql_{{quality_level}}" tip="Quality Level {{quality_level}}">{{quality_level}}</span>
					{{#flag}}
					<span class="tag flag" tip="Flagged"><i class="icon icon-flag"></i></span>
					{{/flag}}
					{{#gold_status_flag}}
					<span class="tag gold_status_flag" tip="Gold Standard"><i class="icon icon-star-empty"></i></span>
					{{/gold_status_flag}}
				</p>
			</div>
			<div class='clearfix'></div>
			</li>
			{{/items}}
			{{#noResult}}<small style="margin-left:-20px;"><i>There are no records which match this status</i></small>{{/noResult}}
		</ul>
		{{#hasMore}}<span class="show_more" offset="{{offset}}" ds_id="{{ds_id}}" status="{{name}}">Show More</span>{{/hasMore}}
	</div>
</div>

<div id="context-menu-{{name}}">
	<ul class="dropdown-menu" role="menu">
		{{#menu}}
		<li><a tabindex="-1" href="javascript:;" class="op" action="{{action}}" status="{{name}}">{{display}}</a></li>
		{{/menu}}
		<li class="hide unflag"><a tabindex="-1" href="javascript:;" class="op" action="un_flag" status="{{name}}">Remove Flag<a/></li>
		<li class="hide unsetgoldstatus"><a tabindex="-1" href="javascript:;" class="op" action="un_set_gold_status_flag" status="{{name}}">Remove Gold Status<a/></li>
	</ul>
</div>
</script>


<script type="text/x-mustache" id="mmr_data_more">
{{#items}}
<li id="{{id}}" class="status_{{status}} ro_item {{#has_error}}ro_error{{/has_error}}" status="{{status}}">
<div class="ro_title"><a ro_id="{{id}}" class="ro_preview">{{title}}</a></div>
<div class="ro_content ">
	<p>
		<span class="tag" tip="Last Modified"><i class="icon icon-time"></i> {{updated}}</span>
		<img class="tag" tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/>
		<span class="tag ql_{{quality_level}}" tip="Quality Level {{quality_level}}">{{quality_level}}</span>
		{{#flag}}
		<span class="tag" tip="Flagged"><i class="icon icon-flag"></i></span>
		{{/flag}}
		{{#gold_status_flag}}
		<span class="tag gold_status_flag" tip="Gold Standard"><i class="icon icon-star-empty"></i></span>
		{{/gold_status_flag}}
	</p>
</div>
<div class='clearfix'></div>
</li>
{{/items}}
</script>
<?php $this->load->view('footer');?>