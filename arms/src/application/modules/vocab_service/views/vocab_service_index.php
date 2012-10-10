<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/vocab_services/_vocab
 * @package ands/vocab_services
 * 
 */
?>

<?php  $this->load->view('header');?>
<div class="container" id="main-content">

<section id="browse-vocabs" class="hide">
<div class="row">
	<div class="span8" id="browse-datasources-left">
		<div class="box">
			<div class="box-header clearfix">
				<h1><?php echo $title;?><small><?php echo $small_title;?></small></h1>
				<span class="right-widget">
					<a href="javascript:;" tip="Show/Hide side bar" my="right center" at="left center" id="toggle_side_bar_btn"><i class="icon-tasks"></i></a>
				</span>
			</div>
			<div class="box-content">

				<!-- Toolbar -->
			    <div class="row-fluid" id="mmr_toolbar">
			    	<div class="span6">
			    		<span class="dropdown" id="switch_menu">
			    		<a class="btn dropdown-toggle" data-toggle="dropdown" data-target="#switch_menu" href="#switch_menu">Switch View <span class="caret"></span></a>
						  <ul class="dropdown-menu" id="switch_view">
						    <li><a href="javascript:;" name="thumbnails"><i class="icon-th"></i> Thumbnails View</a></li>
						    <li><a href="javascript:;" name="lists"><i class="icon-th-list"></i> List View</a></li>
						  </ul>
						</span>
					</div>
					
			    	<div class="span6 right-aligned">
			    		<select data-placeholder="Choose a Vocabulary to View" tabindex="1" class="chzn-select" id="vocab-chooser">
							<option value=""></option>
							<?php
								foreach($vocabs as $vocab){
									echo '<option value="'.$vocab['id'].'">'.$vocab['title'].'</option>';
								}
							?>
						</select>
			    	</div>
			    </div>

			    <!-- List of items will be displayed here-->
			    <ul class="lists" id="items"></ul>
			    
			    <!-- View More Link -->
			    <div class="row-fluid">
					<div class="span12">
						<div class="well"><a href="javascript:;" id="load_more" page="1">Show More...</a></div>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="span4 item-control" id="browse-vocabs-right">
	<div vocab_id="0">
	<div class="">
		<button class="btn add" id="add" vocab_id="0"><i class="icon-add"></i>Add a vocabulary</button>
	</div>
</div>
</div>
	

	<!-- Load More Link -->
	

</section>

<section id="view-vocab" class="hide">Loading...</section>
<section id="edit-vocab" class="hide">Loading...</section>
<section id="add-vocab" class="hide">Loading...</section>
</div>
<!-- end of main content container -->


<section id="vocab-templates">
<!-- mustache template for list of items-->
<div class="hide" id="items-template">
	{{#items}}
		<li>
		  	<div class="item" vocab_id="{{id}}">
		  		<div class="item-info"></div>
		  		<div class="item-snippet">
			  		<h3>{{title}}</h3>
				  	<p>{{description}}</p>
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


<!-- mustache template for vocab view single-->
<div class="hide" id="vocab-view-template">
<?php
	$vocab_view_fields = array(
		'id' => 'Id',
		'title' => 'Title',
		'description' => 'Description',
		'contact_name' => 'Contact Name',
		'contact_email' => 'Contact Email',
		'contact_number' => 'Contact Number',
		'website' => 'Website',
		'revision_cycle' => 'Revision cycle',
		'available_formats' =>'Available Formats',	
		'notes' => 'Notes',
		'language' => 'Language',
		'information_sources' => 'Information Sources',

	);
?>

{{#item}}
	<div class="container">
	<div class="row">
	<div class="span8" id="vocab_view_container" vocab_id="{{id}}">
		<div class="box">
		<div class="box-header">
			<ul class="breadcrumbs">
				<li><a href="javascript:;"><i class="icon-home"></i></a></li>
				<li><?php echo anchor('vocab_service', 'Manage My Vocabs');?></li>
				<li><a href="javascript:;" class="active">{{title}}</a></li>
			</ul>
	        <!--h1>{{title}}</h1-->
	        <div class="clearfix"></div>
	    </div>
	    <div class="row-fluid">    	
	 		<div>
	 			<div class="btn-toolbar"  vocab_id="{{id}}">
					<div class="btn-group item-control" vocab_id="{{id}}">
				  		<button class="btn edit"><i class="icon-edit"></i> Edit Vocabulary</button>
				  		<button class="btn deleteRecord"><i class="icon-trash"></i> Delete Record</button>
					</div>
				</div>
			</div>
			
	    <div class="">		
		<dl class="dl-horizontal">
			<?php 
			foreach($vocab_view_fields as $key=>$name){
				echo '{{#'.$key.'}}';
				echo '<dt>'.$name.'</dt>';
				echo '<dd>{{'.$key.'}}</dd>';
				echo '{{/'.$key.'}}';
			}	
			?>
		</dl>

		<section id="view-vocab-version" class="hide">Loading...</section>
		<section id="view-vocab-changes" class="hide">Loading...</section>
		</div>
	    </div>
		</div>
	</div>
	</div>
	</div>
{{/item}}
</div>	


<?php
	$vocab_version_view_fields = array(
		'id' => 'Id',
		'status' => 'Status',
		'title' => 'Version',
			'format' => 'Format',
			'type' => 'Type',
	);
?>

<div class="hide" id="vocab-version-view-template">
	<div class="" id="vocab_view_container" vocab_id="{{id}}">
		<dl class="dl-horizontal">
		<dt>Versions</dt>
		<dd>
		{{#items}}
		<?php 
			foreach($vocab_version_view_fields as $key=>$name){
				echo '{{#'.$key.'}}';
				echo '{{'.$key.'}} ';
				echo '{{/'.$key.'}}';
			}					
		?>
		<br />
		{{/items}}
		</dd>
		</dl>
	</div>
</div>


			<?php
	$vocab_changes_view_fields = array(
		'id' => 'Id',
		'change_date' => 'Change Date',
		'description' => 'Description',
	);
?>

<div class="hide" id="vocab-changes-view-template">
	<div class="" id="vocab_view_container" vocab_id="{{id}}">
		<dl class="dl-horizontal">
		<dt>Changes</dt>
		<dd>
		{{#items}}	
			<?php 
			foreach($vocab_changes_view_fields as $key=>$name){
				echo '{{#'.$key.'}}';
				echo '{{'.$key.'}} ';
				echo '{{/'.$key.'}}';
			}					
			?>
			<br />
		{{/items}}
		</dd>
		</dl>
	</div>
</div>

<div class="hide" id="items-template">
	{{#items}}
		<li>
		  	<div class="item" vocab_id="{{id}}">
		  		<div class="item-info"></div>
		  		<div class="item-snippet">
			  		<h3>{{title}}</h3>
				  	<p>{{description}}</p>
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




<!-- mustache template for data source edit single-->
<div class="hide" id="vocab-edit-template">
{{#item}}
	<div class="box">
	<div class="box-header">
				<ul class="breadcrumbs">
				<li><a href="javascript:;"><i class="icon-home"></i></a></li>
				<li><?php echo anchor('vocab_service', 'Manage My Vocabs');?></li>
				<li><a href="javascript:;" class="active">Edit: {{title}}</a></li>
			</ul>
	    <span class="right-widget">
        	<small><a href="javascript:;" class="close return-to-browse">&times;</a></small>
        </span>
        <div class="clearfix"></div>
	</div>
	<div class="">


		<form class="form-horizontal"  enctype="multipart/form-data"  id="edit-form">
			<div class="tab-content">
				<div id="admin" class="tab-pane active">
					<fieldset>
						<legend>Vocabulary Administration Information</legend>

						<div class="control-group">
							<label class="control-label" for="title">Title</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="title" value="{{title}}">
								<p class="help-inline"><small>Help</small></p>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="description">Description</label>
							<div class="controls">
								<textarea class="input-xlarge" name="description">{{description}}</textarea>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="publisher">Publisher</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="publisher" value="{{publisher}}">
							</div>
						</div>										
						<div class="control-group">
							<label class="control-label" for="contact_name">Contact Name</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_name" value="{{contact_name}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="contact_email">Contact Email</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_email" value="{{contact_email}}">
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="contact_number">Contact Number</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_number" value="{{contact_number}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="website">Website</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="website" value="{{website}}">
							</div>
						</div>	
						
						<div class="control-group">
							<label class="control-label" for="revision_cycle">Revision Cycle</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="revision_cycle" value="{{revision_cycle}}">
							</div>
						</div>	
																	
						<div class="control-group">
							<label class="control-label" for="notes">Notes</label>
							<div class="controls">
								<textarea class="input-xlarge" name="notes">{{notes}}</textarea>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="language">Language</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="language" value="{{language}}"></input>
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="information_sources">Information Sources</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="information_sources" value="{{information_sources}}"></input>
							</div>
						</div>	
		<section id="edit-vocab-version" class="hide">Loading...</section>
	<!-- 	<section id="edit-vocab-changes" class="hide">Loading...</section>	 -->

					</fieldset>
				</div>
	
			</div>
			<button class="btn" id="save-edit-form" data-loading-text="Saving..." >Save</button>
			<div class="modal hide" id="myModal">
			  <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal">×</button>
			    <h3>Alert</h3>
			  </div>
			  <div class="modal-body"></div>
			  <div class="modal-footer">
			    
			  </div>
			</div>
		</form>

		
	</div>
</div>
{{/item}}
</div>
<?php
	$vocab_version_edit_fields = array(
		'id' => 'Id',
		'status' => 'Status',
		'title' => 'Version',

	);
?>

<div class="hide" id="vocab-version-edit-template">
		{{#items}}
<p>Add a new format to <em>"{{#title}}{{title}}{{/title}}"</em></p>		
<div class="item-control"> 
			<select class="chzn-select" id="versionFormat" style="width:100px">
				<option value="">Select</option>
				<option value="SKOS">SKOS</option>
				<option value="CSV">CSV</option>
				<option value="TEXT">Text</option>				
			</select>
				 
			<select class="chzn-select" id="versionFormatType" style="width:100px">
				<option value="">Select</option>
				<option value="file">Upload File</option>
				<option value="uri">URI</option>
			</select>
			
			<span id="versionFormatValueBox"><input type="text" value="" id="versionFormatValue" style="width:300px"/></span> <button class="btn version-format-add" version_id="{{#id}}{{id}}{{/id}}">Add</button></div>

			
			</p>
			

		<?php 
		/*	foreach($vocab_version_edit_fields as $key=>$name){
				echo '{{#'.$key.'}}';
				echo '{{'.$key.'}} ::  ';
				echo '{{/'.$key.'}}';					
			}	*/	?>

			{{#formats}} <?php 
	
				echo ' <div class="item-control"> {{#value}}{{value}}{{/value}} {{#format}}{{format}}{{/format}} <button class="btn version-format-delete" format_id="{{#format_id}}{{format_id}}{{/format_id}}"><i class="icon-trash"></i></button></div><br />';
			
		?>	{{/formats}}
		{{/items}}	


</div>

<?php
	$vocab_changes_edit_fields = array(
		'id' => 'Id',
		'change_date' => 'Change Date',
		'description' => 'Description',
	);
?>			

	
	
	
	
<div class="hide" id="vocab-add-template">

	<div class="box">
	<div class="box-header">
					<ul class="breadcrumbs">
				<li><a href="javascript:;"><i class="icon-home"></i></a></li>
				<li><?php echo anchor('vocab_service', 'Manage My Vocabs');?></li>
				<li><a href="javascript:;" class="active">Add Vocabulary</a></li>
			</ul>
	    <span class="right-widget">
        	<small><a href="javascript:;" class="close return-to-browse">&times;</a></small>
        </span>
        <div class="clearfix"></div>
	</div>
	<div class="">


		<form class="form-horizontal" id="add-form">
			<div class="tab-content">
				<div id="admin" class="tab-pane active">
					<fieldset>
						<legend>Vocabulary Administration Information</legend>

						<div class="control-group">
							<label class="control-label" for="title">Title</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="title" value="">
								<p class="help-inline"><small>Help</small></p>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="description">Description</label>
							<div class="controls">
								<textarea class="input-xlarge" name="description"></textarea>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="publisher">Publisher</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_name" value="">
							</div>
						</div>										
						<div class="control-group">
							<label class="control-label" for="contact_name">Contact Name</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_name" value="">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="contact_email">Contact Email</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_email" value="">
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="contact_number">Contact Number</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_number" value="">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="website">Website</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="website" value="">
							</div>
						</div>	
						
						<div class="control-group">
							<label class="control-label" for="revision_cycle">Revision Cycle</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="revision_cycle" value="">
							</div>
						</div>	
						
				<!--  	<div class="control-group">
							<label class="control-label" for="available_formats">Available Formats</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="available_formats" value="">
							</div>
						</div>	-->	
																					
						<div class="control-group">
							<label class="control-label" for="notes">Notes</label>
							<div class="controls">
								<textarea class="input-xlarge" name="notes"></textarea>
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="language">Language</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="language"></input>
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label" for="information_sources">Information Sources</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="information_sources"></input>
							</div>
						</div>												
					</fieldset>
				</div>
	
			</div>
			<button class="btn" id="save-add-form" data-loading-text="Saving..." >Save</button>
			<div class="modal hide" id="myModal">
			  <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal">×</button>
			    <h3>Alert</h3>
			  </div>
			  <div class="modal-body"></div>
			  <div class="modal-footer">
			    
			  </div>
			</div>
		</form>

		
	</div>
</div>

</div>

</section>


<?php $this->load->view('footer');?>