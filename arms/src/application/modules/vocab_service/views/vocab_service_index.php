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
		<div class="span8">
			<div class="box">
				<div class="box-header clearfix">
					<h1><?php echo $title;?><small><?php echo $small_title;?></small></h1>
					<span class="right-widget">
						
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
				    <div class="row-fluid" id="load_more_container">
						<div class="span12">
							<div class="well"><a href="javascript:;" id="load_more" page="1">Show More...</a></div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="span4">
			<div class="box">
				<div class="box-header clearfix">
					<h1>My Vocabularies</h1>
				</div>
				<div class="box-content">
					<?php
						if (sizeof($my_vocabs)==0){
							echo "<p>You don't seem to own a vocabulary, you can create one</p>";
						}else{
							echo '<ul>';
							foreach($my_vocabs AS $v){
								echo '<li><a href="#!/view/'.$v->id.'">'.$v->title . "</a></li>";
							}
							echo '</ul>';
						}
					?>
					<button class="btn add" id="add"><i class="icon-plus"></i>Add a vocabulary</button>
				</div>
			</div>
		</div>
	</div>
</section>

<section id="view-vocab" class="hide">Loading...</section>
<section id="edit-vocab" class="hide">Loading...</section>
<section id="add-vocab" class="hide">Loading...</section>
</div>
<!-- end of main content container -->


<!-- mustache template for list of items-->
<script type="text/x-mustache" id="items-template">
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
		  			{{#owned}}
				  		<button class="btn edit"><i class="icon-edit"></i></button>
				  		<button class="btn delete"><i class="icon-trash"></i></button>
			  		{{/owned}}
				</div>
		  	</div>
		  </li>
	{{/items}}
</script>


<!-- mustache template for vocab view single-->
<script type="text/x-mustache" id="vocab-view-template">
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
	<div class="row">
		<div class="span8" id="vocab_view_container" vocab_id="{{id}}">
			<div class="box">
				<div class="box-header">
					<ul class="breadcrumbs">
						<li><a href="javascript:;"><i class="icon-home"></i></a></li>
						<li><?php echo anchor('vocab_service', 'Manage My Vocabs');?></li>
						<li><a href="javascript:;" class="active">{{title}}</a></li>
					</ul>
			        <div class="clearfix"></div>
			    </div>

			    <div class="box-content">

			    	<h3>{{title}}</h3>
			    	

					{{#description}}
						<h5>Description</h5>
						{{description}}
					{{/description}}

					<h5>Available Formats</h5>
					{{#hasFormats}}
						{{#available_formats}}	
							<span class="largeTag format" format="{{.}}" vocab_id="{{id}}">{{.}}</span>
						{{/available_formats}}
					{{/hasFormats}}

					{{#noFormats}}
						There is no available format for this vocabulary
					{{/noFormats}}

					

					{{#language}}
						<h5>Language</h5> {{language}}
					{{/language}}

					{{#notes}}
						<h5>Notes</h5>
						{{notes}}
					{{/notes}}

					{{#information_sources}}
						<h5>Information Sources</h5>{{information_sources}}
					{{/information_sources}}

					
			    	<div class="btn-toolbar">
						<div class="btn-group item-control" vocab_id="{{id}}">
							<button class="btn contact"><i class="icon-user"></i> Contact Publisher</button>
						</div>
					</div>
					


			    </div>
			</div>
		</div>

		<div class="span4">
			{{#contact}}
			<div class="box">
				<div class="box-header"><h3>Contacts</h3><div class="clearfix"></div></div>
				<div class="box-content">
					{{#contact_name}}
						{{contact_name}}<br/>
					{{/contact_name}}
					{{#contact_email}}
						{{&contact_email}}<br/>
					{{/contact_email}}
					{{#contact_number}}
						{{contact_number}}<br/>
					{{/contact_number}}
				</div>
			</div>
			{{/contact}}
			
			{{#owned}}
			<div class="box">
				<div class="box-header"><h3>Admin</h3><div class="clearfix"></div></div>
				<div class="box-content">
					<div class="btn-toolbar">
						<div class="btn-group btn-group-vertical btn-group-left item-control" vocab_id="{{id}}">
							<button class="btn edit"><i class="icon-edit"></i> Edit Vocabulary</button>
							<button class="btn addVersion" vocab_id="{{id}}"><i class="icon-plus"></i> Add A Version</button>
						</div>
					</div>
					
				</div>
			</div>
			{{/owned}}

			<div class="box" vocab_id="{{id}}">
				<div class="box-header"><h3>Versions</h3><div class="clearfix"/></div>
				<div class="box-content">
					<ul class="ro-list">
					{{#hasVersions}}
						{{#versions}}
							<li><a href="javascript:;" class="version" version_id="{{id}}"><span class="name">{{title}}</span></a><span class="num">{{status}}</span></li>
						{{/versions}}
					{{/hasVersions}}
					{{#noVersions}}
						<li>This vocab does not have any available versions</li>
					{{/noVersions}}

					{{#owned}}
					<li class="addVersion" vocab_id="{{id}}"><a href="javascript:;"  ><i class="icon-plus"></i> Add a Version</a></li>
					{{/owned}}
					</ul>
				</div>
			</div>

			<div class="box">
				<div class="box-header"><h3>Changes</h3><div class="clearfix"/></div>
				<div class="box-content">
					{{#hasChanges}}
						{{#changes}}
							<li>{{change_date}}</li>
						{{/changes}}
					{{/hasChanges}}

					{{#noChanges}}
						<div class="well">This vocabulary has no changes</div>
					{{/noChanges}}
				</div>
			</div>

		</div>
	</div>

	<div class="hide" id="add-version-to-vocab">
		<form class="form-inline" vocab_id="{{id}}">
			<label>Version Title: </label>
			<input type="text" class="input-medium" name="title" placeholder="Version Title">
			<label class="checkbox">
		    	<input type="checkbox" name="current"> Make Current
		    </label>
			<button type="submit" class="btn addVersionButton">Add Version</button>
		</form>
	</div>
			
	{{/item}}
</script>	


<script type="text/x-mustache" id="vocab-format-downloadable-template">
	{{#hasItems}}
	<ul>
		{{#items}}
		<li>{{value}} <span class="label label-info">{{type}}</span> <span class="label label-error">{{status}}</span></li>
		{{/items}}
	</ul>
	{{/hasItems}}
</script>

<script type="text/x-mustache" id="vocab-format-downloadable-template-by-version">
	{{#hasItems}}
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Name</th><th>Type</th><th>Format</th><th>Action</th>
			</tr>
		</thead>
		<tbody>
			{{#items}}
			<tr class="formatRow" format_id="{{id}}">
				<td>{{value}}</td><td><span class="label label-info">{{type}}</span></td><td><span class="label label-info">{{format}}</span></td>
				<td>
					<div class="btn-group">
			  			<button class="btn downloadFormat" format_id="{{id}}"><i class="icon-download"></i></button>
				  		<button class="btn deleteFormat" format_id="{{id}}"><i class="icon-trash"></i></button>
					</div>
				</td>
			</tr>
			{{/items}}
		</tbody>
	</table>
	{{/hasItems}}

	{{#noItems}}
		<p>This version has no downloadable formats available</p>
	{{/noItems}}

	{{#owned}}
		<div class="btn-group">
  			<button class="btn addFormat" version_id="{{id}}"><i class="icon-plus"></i> Add a Format</button>
  			<button class="btn editVersion" version_id="{{id}}"><i class="icon-edit"></i></button>
	  		<button class="btn deleteVersion" version_id="{{id}}"><i class="icon-trash"></i></button>
		</div>

		<div class="addFormatForm hide" version_id="{{id}}"><hr/>
			<div class="form well">
				<div class="control-group">
				<label>Format:</label>
					<div class="btn-group toggleAddFormat" data-toggle="buttons-radio" version_id="{{id}}">
						<button type="button" class="btn" value="SKOS">SKOS</button>
						<button type="button" class="btn" value="TEXT">TEXT</button>
					</div>
					<input type="hidden" name="format" class="inputAddFormat" version_id="{{id}}" value=""/>
				</div>
				<div class="control-group">
				<label>Type:</label>
					<div class="btn-group toggleAddFormatType" data-toggle="buttons-radio" version_id="{{id}}">
						<button type="button" class="btn" value="file" content="fileSubmit">File</button>
						<button type="button" class="btn" value="uri" content="uriSubmit">URI</button>
					</div>
					<input type="hidden" name="type" class="inputAddFormatType" version_id="{{id}}" value=""/>
				</div>
				<div class="control-group">
					<div class="uriSubmit hide addFormatTypeContent" version_id="{{id}}">
						<label>URI:</label>
						<input type="text" class="input-medium" name="value"/>
					</div>
					<div class="fileSubmit hide addFormatTypeContent" version_id="{{id}}">
						<label>File Upload:</label>
						<input type="file" class="input-medium addFormatUploadValue" name="file" version_id="{{id}}"/>
					</div>
					<hr/>
					<button type="submit" class="btn addFormatSubmit" version_id="{{id}}">Add Format</button> <a href="javascript:;" version_id="{{id}}" class="cancelAddFormat">Cancel</a>
				</div>
			</div>
		</div>
		
		<div class="editVersionForm hide" version_id="{{id}}"><hr/>
			<form class="form well" vocab_id="{{id}}">
				<label>Version Title: </label>
				<input type="text" class="input-medium" name="title" value="{{title}}">
				{{#current}}
				<label class="checkbox">
			    	<input type="checkbox" checked=checked name="current"> Make Current
			    </label>
				{{/current}}
				{{#notCurrent}}
				<label class="checkbox">
			    	<input type="checkbox" name="current"> Make Current
			    </label>
				{{/notCurrent}}
				
				<button type="submit" class="btn editVersionConfirm" version_id="{{id}}">Submit Changes</button> <a href="javascript:;" version_id="{{id}}" class="cancelEdit">Cancel</a>
			</form>
		</div>

		<div class="deleteVersionForm hide" version_id="{{id}}"><hr/>
			<div class='well'>
				<p>Are you sure you want to delete this version <br/>and all file formats associated with this version?</p>
				<p>
					<button type="submit" version_id="{{id}}" vocab_id="{{vocab_id}}" class="btn btn-error deleteVersionConfirm">Yes</button>
					<a href="javascript:;" version_id="{{id}}" class="cancelDelete">No</a>
				</p>
			</div>
		</div>
	{{/owned}}
</script>


<script type="text/x-mustache" id="vocab-changes-view-template">
	{{#hasChanges}}
	<table class="table table-hover">
		<thead><tr><th>#</th><th>Description</th><th>Date Changed</th></tr></thead>
		<tbody>
			{{#items}}
				<tr>
					<td>{{id}}</td><td>{{description}}</td><td>{{change_date}}</td>
				</tr>
			{{/items}}
		</tbody>
    </table>
    {{/hasChanges}}

    {{#noChanges}}
    	No change have been done on this vocabulary
    {{/noChanges}}
</script>

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
<script type="text/x-mustache" id="vocab-edit-template">
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


		<form class="form-horizontal"  enctype="multipart/form-data"  id="edit-form" vocab_id="{{id}}">
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
			<button class="btn" id="save-edit-form" data-loading-text="Saving..." >Save</button> <span id="save-edit-form-message"></span>
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
	{{/item}}
</script>


<?php
	$vocab_version_edit_fields = array(
		'id' => 'Id',
		'status' => 'Status',
		'title' => 'Version',

	);
?>

<script type="text/x-mustache" id="vocab-version-edit-template">
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


</script>

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
												<legend>Vocabulary Versions</legend>
						<div class="control-group">
							<label class="control-label" for="title">Version Title</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="title"></input>
							</div>
						</div>												
							<div class="control-group">
							<label class="control-label" for="status">Version Status</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="status"></input>
							</div>
						</div>	
						</div>												
							<div class="control-group">
							<label class="control-label" for="format"></label>
							<div class="controls">
								<button >Add Format</button>
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