<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/registry_object/_registry_object
 * @package ands/datasource
 * 
 */
?>
<?php $this->load->view('header');?>

<input type="hidden" class="hide" id="ds_id" value="<?php echo $data_source_id;?>"/>

<div class="container" id="main-content">
<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
  </div>
</div>

<section id="browse-ro">

	<div class="row">
		<div class="box">
			<div class="box-header clearfix">
				<h1><?php echo $data_source_title;?> <small>Manage My Records</small></h1>
				<span class="right-widget">
					<h1><small><?php echo anchor('data_source/#!/view/40', 'Manage This Datasource', array('class'=>'manage_ds_link'));?></small></h1>
				</span>
				<div class="clearfix"></div>
			</div>

			<!-- Toolbar -->
		    <div class="row-fluid" id="mmr_toolbar">
		    	<div class="span4">
		    		<span class="dropdown" id="switch_menu">
		    		<a class="btn dropdown-toggle" data-toggle="dropdown" data-target="#switch_menu" href="#switch_menu">Switch View <span class="caret"></span></a>
					  <ul class="dropdown-menu" id="switch_view">
					    <li><a href="javascript:;" name="thumbnails"><i class="icon-th"></i> Thumbnails View</a></li>
					    <li><a href="javascript:;" name="lists"><i class="icon-th-list"></i> List View</a></li>
					  </ul>
					</span>
					<a class="btn toggleFilter">Filter</a>


					
				</div>
				<div class="span4 centered">
					<span>
						<form class="form-search" id="search-records">
						  <input type="text" class="input-medium" placeholder="Search..." name="fulltext">
						  <button class="btn btn-primary">Search</button>
						</form>
					</span>
				</div>
		    	<div class="span4 right-aligned">
		    		<span class="btn-toolbar">
		    			<div class="btn-group">
		    				<a class="btn" id="select_all" name="select_all">Select All</a>
						  
						  	<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
						    	Options<span class="caret"></span>
						  	</a>
							<ul class="dropdown-menu pull-right">
								<li><a href="javascript:;" name="thumbnails">Enable Drag and Drop Select</a></li>
								<li><a href="javascript:;" name="thumbnails">Hide minibar</a></li>
							</ul>
						</div>
			    		
					</span>
		    	</div>
		    </div>

		    <!-- Middle bar for filtering and item display -->
		    <div class="well hide" id="filter_container">

			    <div class="row-fluid" id="filter_fields">
			    	<small><a href="javascript:;" class="close toggleFilter">&times;</a></small>
			    	<div class="span12">
			    		<div class="span3">
			    			<h3>Sort</h3>
			    			<ul>
			    				<li><a href="javascript:;" class="sort" name="date_modified">Date Modified</a> <span></span></li>
			    				<li><a href="javascript:;" class="sort" name="quality_level">Quality Level</a> <span></span></li>
			    				<li><a href="javascript:;" class="sort" name="s_list_title">Title</a> <span></span></li>
			    			</ul>
			    		</div>
			    		<div class="span3 facets" name="class">
			    			<h3>Class</h3>
			    			<ul></ul>
			    		</div>
			    		<div class="span3 facets" name="status">
			    			<h3>Status</h3>
			    			<ul></ul>
			    		</div>
			    		<div class="span3 facets" name="quality_level">
			    			<h3>Quality Levels</h3>
			    			<ul></ul>
			    		</div>
			    	</div>
			    </div>
			    <div class="clear"></div>
			    <div class="row-fluid">
			    	<div class="span12">
		    			<div id="applied_filters"></div>
		    		</div>
			    </div>
			</div>

			
		    <div class="well hide" id="items_info"></div>



		    <!-- List of items will be displayed here, in this ul -->
			<ul class="thumbnails" id="items"></ul>

			<!-- Load More Link -->
			<div class="row-fluid">
				<div class="span12">
					<div class="well"><a href="javascript:;" id="load_more" page="1">Show More...</a></div>
				</div>
			</div>
		</div>
	</div>
</div><!-- end main content container -->

<div id="view-ro" class="hide">Loading...</div>
<section id="edit-ro" class="hide">Loading...</section>
<section id="expand-ro" class="hide">Loading...</section>
<section id="delete-ro" class="hide">Loading...</section>

</section>






<!-- end template section-->
<?php $this->load->view('footer');?>


<!-- template section -->
<section class="hide" id="ro-templates">
<div class="hide" id="items-template">
	{{#items}}
		<li class="span4">
		  	<div class="item" ro_id="{{id}}">
		  		<div class="item-info"></div>
		  		<div class="item-snippet">
	  				<h3><span class="ands_class_icon icon_{{class}}"></span>{{list_title}}</h3>
	  				<span class="tag ql_{{quality_level}}">{{quality_level}}</span>
			  		<span class="tag status_{{status}}">{{status}}</span>
			  		<i class="icon-time"></i>
			  		Last modified {{date_modified}} by {{last_modified_by}}
	  				<div class="item-description">
	  					{{{description}}}
	  				</div>
			  		
			  		
			  	</div>
		  		<div class="btn-group item-control">
		  			<button class="btn view"><i class="icon-eye-open"></i></button>
			  		<button class="btn edit"><i class="icon-edit"></i></button>
			  		<button class="btn delete"><i class="icon-trash"></i></button>
				</div>
		  	</div>
		</li>
	{{/items}}
</div>


<div class="hide" id="item-template">
{{#ro}}
<div class="container">
<div class="row">
	<div class="box" ro_id="{{id}}">
		<div class="box-header clearfix">
			<h1>{{title}}</h1>
			<span class="right-widget">
				<ul class="tab-view-list">
					<li><a href="javascript:;" name="view" ro_id="{{id}}">View</a></li>
					<li><a href="javascript:;" name="edit" ro_id="{{id}}">Edit</a></li>
					<li><a href="javascript:;" name="preview" ro_id="{{id}}">Preview</a></li>
				</ul>
			</span>
			<div class="clearfix"></div>
		</div>
		<div class="box-content tab-content tab-view-content" name="view">
			<h1>XML</h1>
			<pre class="prettyprint">
				{{xml}}
			</pre>
			<h1>ExtRif</h1>
			<pre class="prettyprint">
				{{extrif}}
			</pre>
		</div>

		<div class="box-content tab-content tab-view-content" name="preview">
			Preview in RDA iframe
		</div>

		<div class="box-content tab-content tab-view-content" name="edit">
			Loading RIFCS
		</div>
	</div>
</div>
{{/ro}}
</div>
</div>
</section>
