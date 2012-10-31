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

<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
  </div>
</div>

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

					{{#language}}
						<h5>Subjects</h5> {{subject}}
					{{/language}}

					{{#notes}}
						<h5>Notes</h5>
						{{notes}}
					{{/notes}}



					
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

			<div id="versions-view">
			
			</div>

			<div class="box">
				<div class="box-header"><h3>Changes</h3><div class="clearfix"/></div>
				<div class="box-content">
					{{#hasChanges}}
						<ul>
						{{#changes}}
							<li><a href="javascript:;" class="viewChange" change_id="{{change_id}}" change_description="{{change_description}}">{{change_date}}</a></li>
						{{/changes}}
						</ul>
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

<script type="text/x-mustache" id="vocab-versions">
{{#item}}
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
{{/item}}
</script>

<script type="text/x-mustache" id="vocab-format-downloadable-template">
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
			  			{{#owned}}
				  		<button class="btn deleteFormat" format_id="{{id}}"><i class="icon-trash"></i></button>
				  		{{/owned}}
					</div>
				</td>
			</tr>
			{{/items}}
		</tbody>
	</table>
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
			  			{{#owned}}
				  		<button class="btn deleteFormat" format_id="{{id}}"><i class="icon-trash"></i></button>
				  		{{/owned}}
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


<script type="text/x-mustache" id="vocab-edit-template">
	{{#item}}
	<div class="row">
		<div class="span8">
			<div class="box">
				<div class="box-header clearfix">
					<ul class="breadcrumbs">
						<li><a href="javascript:;"><i class="icon-home"></i></a></li>
						<li><?php echo anchor('vocab_service', 'Manage My Vocabs');?></li>
						<li><a href="javascript:;" class="active"></a></li>
					</ul>
				</div>

				<div class="box-content">
					<form class="form-horizontal"  enctype="multipart/form-data"  id="edit-form" vocab_id="{{id}}">
							<fieldset>
								<legend>Vocabulary Administration Information</legend>

								<div class="control-group">
									<label class="control-label" for="title">Title</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="title" value="{{title}}" required placeholder="Enter a title for the vocabulary">
										<p class="help-inline"><small></small></p>
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="description">Description</label>
									<div class="controls">
										<textarea class="input-xlarge" name="description" required placeholder="Enter a description for the vocabulary">{{description}}</textarea>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="publisher">Publisher</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="publisher" value="{{publisher}}" required placeholder="Enter a publisher for the vocabulary">
									</div>
								</div>					

								<div class="control-group">
									<label class="control-label" for="subjects">Subjects</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="subjects" value="{{subjects}}">
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
							</fieldset>

							<fieldset>
								<legend>Contact Details</legend>
								<div class="control-group">
									<label class="control-label" for="contact_name">Contact Name</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="contact_name" value="{{contact_name}}">
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="contact_email">Contact Email</label>
									<div class="controls">
										<input type="email" class="input-xlarge" name="contact_email" value="{{contact_email}}" required placeholder="Enter a valid contact email">
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

							</fieldset>
						<button class="btn" id="save-edit-form" data-loading-text="Saving..." >Save</button> <span id="save-edit-form-message"></span>
						
					</form>

					
				</div>
			</div>
		</div>

		<div class="span4">
			<div id="versions-edit">
	
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
		</div>

		

	</div>
	{{/item}}
</script>


<?php $this->load->view('footer');?>