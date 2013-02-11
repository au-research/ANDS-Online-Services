<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
?>
<?php $this->load->view('header');?>
<div id="content" style="margin-top:45px;margin-left:0px">
<section id="browse-datasources" class="hide">
	<div class="content-header">
		<h1>Manage My Datasources</h1>
		<div class="btn-group">
			<a class="btn btn-small" id="open_add_ds_form" data-toggle="modal" href="#AddNewDS"><i class="icon-plus"></i> Add New Datasource</a>
		</div>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'List My Datasources', array('class'=>'current'))?>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<select data-placeholder="Choose a Datasource to View" tabindex="1" class="chzn-select" id="datasource-chooser">
				<option value=""></option>
				<?php
					foreach($dataSources as $ds){
						echo '<option value="'.$ds['id'].'">'.$ds['title'].'</option>';
					}
				?>
			</select>
		</div>
		<div class="row-fluid">
			<ul class="lists" id="items"></ul>
		</div>
		<!--div class="row-fluid">
			<div class="span12">
				<div class="well"><a href="javascript:;" id="load_more" page="1">Show More...</a></div>
			</div>
		</div-->
	</div>

	<div class="modal hide fade" id="AddNewDS">
	
		<div class="modal-header">
			<a href="javascript:;" class="close" data-dismiss="modal">×</a>
			<h3>Add New Datasource</h3>
		</div>
		
		<div class="modal-screen-container">
			<div class="modal-body">
				
				<div class="alert alert-info">
					Please provide the key and the title for the data source
				</div>			

				<form action="#" method="get" class="form-vertical">
					<div class="control-group">
						<label class="control-label">Key</label>
						<div class="controls">
							<input type="text" name="data_source_key" required>
							<span class="help-block">Key has to be unique</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" name="title">Title</label>
						<div class="controls"><input required type="text" name="title"></div>
					</div>
					<div class="control-group">
						<label class="control-label" name="title">Owner</label>
						<div class="controls">
							<select name="record_owner">
								<?php foreach($this->user->affiliations() as $a):?>
								<option value="<?php echo $a;?>"><?php echo $a;?></option>
								<?php endforeach;?>
							</select>
						</div>
					</div>
				</form>

			</div>
		</div>
		
		
		<div class="modal-footer">
			<a id="AddNewDS_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Saving...">Save</a>
			<a href="#" class="btn hide" data-dismiss="modal">Close</a>
		</div>

		
	</div>
</section>

<section id="view-datasource" class="hide">Loading...</section>
<section id="settings-datasource" class="hide">Loading...</section>
<section id="edit-datasource" class="hide">Loading...</section>

</div>
<!-- end of main content container -->


<section id="datasource-templates">
<!-- mustache template for list of items-->

<div class="hide" id="items-template">
	{{#items}}
		<div class="widget-box">
			<div class="widget-title">
				<h5 class="ellipsis"><a class="view" href="#!/view/{{id}}">{{title}}</a></h5>
			</div>
			
			<div class="widget-content">
				<strong>Key:</strong> {{key}}<br/>
				{{#record_owner}}
					<strong>Record Owner:</strong> {{record_owner}}
				{{/record_owner}}
				<p></p>
				{{#counts}}
			  		{{#status}}
			  			<span class="tag status_{{status}}">{{name}} ({{count}})</span>
			  		{{/status}}
		  		{{/counts}}

		  		{{#classcounts}}
			  		{{#class}}
			  			<span class="tag name"><img tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/>{{name}}</span>
			  		{{/class}}
		  		{{/classcounts}}
		  		<div class="btn-group item-control">
		  			<button class="btn btn-small view page-control" data_source_id="{{id}}"><i class="icon-eye-open"></i> View</button>
		  			<button class="btn btn-small mmr page-control" data_source_id="{{id}}"><i class="icon-folder-open"></i> Manage</button>
			  		<button class="btn btn-small settings page-control" data_source_id="{{id}}"><i class="icon-edit"></i> Settings</button>
				</div>
			</div>
		</div>
	{{/items}}
</div>

<script type="text/x-mustache" id="data_source_logs_template">
	{{#items}}
		<li class="{{type}}">
			<a href="javascript:;" class="{{type}}"><i class="icon-list-alt"></i>{{log_snippet}} <span class="label">{{date_modified}}</span></a>
			<div class="log hide">
				<pre>{{log}}</pre>
			</div>
		</li>
	{{/items}}
</script>





<!-- mustache template for data source view single-->
<script type="text/x-mustache"  id="data-source-view-template">
<?php
	$data_source_view_fields = array(
		'key' => 'Key',
		'title' => 'Title',
		'record_owner' => 'Record Owner',
		'contact_name' => 'Contact Name',
		'contact_email' => 'Contact Email',
		'notes' => 'Notes',
		'created_when' => 'Created When',
		'created_who' => 'Created Who'
	);
?>

	{{#item}}
	<div class="content-header">
		<h1>{{title}}</h1>
		<ul class="nav nav-pills">
			<li class="active view page-control" data_source_id="{{data_source_id}}"><a href="#">Dashboard</a></li>
			<li class="mmr page-control" data_source_id="{{data_source_id}}"><a href="#">Manage Records</a></li>
			<li class="report page-control" data_source_id="{{data_source_id}}"><a href="#">Reports</a></li>
			<li class="settings page-control" data_source_id="{{data_source_id}}"><a href="#">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'List My Datasources')?>
		<a href="javascript:;" class="current">{{title}}</a>
	</div>
<div class="container-fluid">
<div class="row-fluid">

	
	<div class="span8" id="data_source_view_container" data_source_id="{{data_source_id}}">
		<div class="widget-box">
	    	
	 		<div class="widget-content">
	 			<div class="btn-toolbar">
					<div class="btn-group">
				  		<button class="btn edit page-control" data_source_id="{{data_source_id}}"><i class="icon-edit"></i> Edit Settings</button>
				  		<button class="btn mmr page-control" data_source_id="{{data_source_id}}"><i class="icon-folder-open"></i> Manage Records</button>
				  		<button class="btn history page-control" data_source_id="{{data_source_id}}"><i class="icon-time"></i> View History</button>
					</div>
					<div class="btn-group pull-right">
						<a class="btn dropdown-toggle ExportDataSource" data-toggle="modal" href="#exportDataSource" id="exportDS">
							 Export Records
						</a>						
					</div>
					<div class="btn-group pull-right">
						<a class="btn dropdown-toggle importRecords" data-toggle="dropdown" href="javascript:;">
							<i class="icon-download-alt"></i> Import Records <span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li><a data-toggle="modal" href="#importRecordsFromURLModal" id="importFromURLLink">From a URL</a></li>
							<li><a data-toggle="modal" href="#importRecordsFromXMLModal" id="importFromXMLLink">From XML Contents</a></li>
							<li><a href="" id="importFromHarvesterLink">From the Harvester</a></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="widget-title">
				<h5>Activity Log</h5>
				<span class="label label-info" id="log_summary"></span>
			</div>
			<div class="widget-content nopadding">
				<ul class="activity-list" id="data_source_log_container"></ul>
				<ul class="activity-list">
					<li class="viewall"><a id='show_more_log' class="tip-top" href="javascript:;" data-original-title="View all posts">Show More<i class='icon-arrow-down'></i></a></li>
				</ul>
			</div>
	 
	    </div>
	</div>

	<div class="span4">
		<div class="widget-box">
			<div class="widget-title"><h5>Data Source Status Summary</h5></div>
			<div class="widget-content nopadding">
				<ul class="ro-list">
					{{#statuscounts}}
				  		{{#status}}
				  			<li class="status_{{status}}" name="{{status}}" type="status"><span class="name">{{name}}</span><span class="num">{{count}}</span></li>
				  		{{/status}}
			  		{{/statuscounts}}
				</ul>
			</div>
		</div>

		<div class="widget-box">
			<div class="widget-title"><h5>Data Source Class Summary</h5></div>
			<div class="widget-content nopadding">
				<ul class="ro-list">
					{{#classcounts}}
				  		{{#class}}
				  			<li class="" name="{{class}}" type="class"><span class="name"><img tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/> {{name}}</span> <span class="num">{{count}}</span></li>
				  		{{/class}}
			  		{{/classcounts}}
				</ul>
			</div>
		</div>

		<div class="widget-box">
			<div class="widget-title"><h5>Data Source Quality Summary</h5></div>
			<div class="widget-content nopadding">
				<ul class="ro-list">
					{{#qlcounts}}
				  		{{#level}}
				  			<li class="ql_{{level}}" name="{{level}}" type="quality_level"><span class="name">Quality Level {{level}}</span> <span class="num">{{count}}</span></li>
				  		{{/level}}
			  		{{/qlcounts}}
				</ul>
			</div>
		</div>

		


	</div>

</div>


</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromURLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from a URL</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Import registry objects from a test feed or backup.</div>

			<form class="form-horizontal">
				<label class="control-label">URL to import records from:</label>
				<div class="controls">
					<input type="text" name="url" placeholder="http://" />
					<p class="help-block">
						<small>Use full URL format (including http://)</small>
					</p>
				</div>
			</form>

			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool does not support OAI-PMH. You must use the Harvester to import from an OAI-PMH feed.
				</small>
			</p>
		</div>
		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading">
				<b>Loading XML from: </b><div id="remoteSourceURLDisplay"></div>
				<div class="progress progress-striped active">
				  <div class="bar" style="width: 100%;"></div>
				</div>
		</div>
		
		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading">
		</div>
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromXMLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from pasted contents</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Paste the registry object contents into the field below</div>

			<form class="form-vertical">
				<fieldset>
					<label> <b>Data to import:</b>
					</label>
					<textarea name="xml" id="xml_paste" rows="18" style="width:97%;font-family:Courier;font-size:8px;line-height:9px;"></textarea>
				</fieldset>
			</form>

			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool is designed for small imports (&lt;100 records). It may fail with larger bulk imports.
				</small>
			</p>
		</div>

		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading"> <b>Loading XML from:</b>
			<div id="remoteSourceURLDisplay"></div>
			<div class="progress progress-striped active">
				<div class="bar" style="width: 100%;"></div>
			</div>
		</div>

		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading"></div>
	</div>

	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>

</div>


<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="exportDataSource">
	
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Export Records As RIF-CS</h3>
	</div>
	
	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">
			
			<div class="alert alert-info">
				Select the type of records you want to export from this datasource. 
			</div>			
			<form class="form-vertical" id="data_source_export_form">
				<fieldset>
					<label><b>Selection form</b> </label>
					<input type="checkbox" name="activity" value="yes" checked="checked" />Activities<br/>
					<input type="checkbox" name="collection" value="yes" checked="checked" />Collections<br/>
					<input type="checkbox" name="party" value="yes" checked="checked" />Parties<br/>
					<input type="checkbox" name="service" value="yes" checked="checked" />Services<br/>
					<br/>
					<select name="status" data-placeholder="Choose by Status" tabindex="1" class="chzn-select input-xlarge" for="class_1">
						<option value="All">ALL status</option>
						<option value="PUBLISHED">PUBLISHED</option>
						<option value="APPROVED">APPROVED</option>
						<option value="DRAFT">DRAFT</option>
						<option value="SUBMITTED_FOR_ASSESSMENT">SUBMITTED_FOR_ASSESSMENT</option>
						<option value="MORE_WORK_REQUIRED">MORE_WORK_REQUIRED</option>
						<option value="ASSESSMENT_IN_PROGRESS">ASSESSMENT_IN_PROGRESS</option>
					</select>
					<!--label><b>Limit: </b> </label><input type="text" name="limit" value="20" /><br/-->
				</fieldset>
			</form>
		</div>
		
		<!-- A hidden loading screen -->
		<div id="loadingScreen" class="modal-body hide loading">
				<b>Generating XML...
				<div class="progress progress-striped active">
				  <div class="bar" style="width: 100%;"></div>
				</div>
		</div>
		<div id="error-modal" class="modal-body hide loading">
				<b>Failed to generate export file...</b>
		</div>

		
		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading">
		</div>
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary exportRecord" type="xml" data-loading-text="Fetching records...">Show me the XML</a>
		<a href="javascript:;" class="btn btn-primary exportRecord" type="file" data-loading-text="Fetching records...">Get My File</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

	{{/item}}
</script>

<script type="text/x-mustache"  id="data-source-settings-template">
<?php
	$data_source_view_fields = array(
		'key' => 'Key',
		'title' => 'Title',
		'record_owner' => 'Record Owner',
		'contact_name' => 'Contact Name',
		'contact_email' => 'Contact Email',
		'notes' => 'Notes',
		'created_when' => 'Created When',
		'created_who' => 'Created Who'
	);
?>

	{{#item}}
	<div class="content-header">
		<h1>{{title}}</h1>
		<ul class="nav nav-pills">
			<li class="view page-control" data_source_id="{{data_source_id}}"><a href="#">Dashboard</a></li>
			<li class="mmr page-control" data_source_id="{{data_source_id}}"><a href="#">Manage Records</a></li>
			<li class="report page-control" data_source_id="{{data_source_id}}"><a href="#">Reports</a></li>
			<li class="active settings page-control" data_source_id="{{data_source_id}}"><a href="#">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'List My Datasources')?>
		<a href="#!/view/{{data_source_id}}">{{title}}</a>
		<a href="javascript:;" class="current">Settings</a>
	</div>
<div class="container-fluid">
<div class="row-fluid">

	
	<div class="span8" id="data_source_view_container" data_source_id="{{data_source_id}}">
		<div class="widget-box">
	    	
	 		<div class='widget-content'>
	 			<a href="javascript:;" class="page-control edit btn btn-primary" data_source_id="{{data_source_id}}">Edit Settings</a>
	 		</div>
	 
	    	<div class="widget-content">

				<h4>Account Administration Information</h4>
				<dl class="dl-horizontal">
					<?php 
					foreach($data_source_view_fields as $key=>$name){
						echo '{{#'.$key.'}}';
						echo '<dt>'.$name.'</dt>';
						echo '<dd>{{'.$key.'}}</dd>';
						echo '{{/'.$key.'}}';
					}
					?>
			 	</dl>
			 	<h4>Records Management Settings</h4>
			 	<dl class="dl-horizontal">
					<dt>Reverse Links</dt>
					<dd>
						<p>
							<div class="normal-toggle-button" value="{{allow_reverse_internal_links}}">
								<input type="checkbox" for="allow_reverse_internal_links" >
							</div>
							Allow Reverse Internal Links
						</p>
						<p>
							<div class="normal-toggle-button" value="{{allow_reverse_external_links}}">
								<input type="checkbox" for="allow_reverse_external_links" >
							</div>
							Allow Reverse External Links
						</p>
					</dd>

					{{#create_primary_relationships}}
					<dt>Create Primary Relationships</dt>
					<dd>
						<p>
							<div class="normal-toggle-button" value="{{create_primary_relationships}}">
								<input type="checkbox" for="create_primary_relationships" >
							</div>
						</p>
					</dd>
					{{/create_primary_relationships}}
					

					{{#auto_publish}}
					<dt>Auto Publish</dt>
					<dd>
						<p>
							<div class="normal-toggle-button" value="{{auto_publish}}">
								<input type="checkbox" for="auto_publish" >
							</div>
						</p>
					</dd>
					{{/auto_publish}}

					{{#qa_flag}}
					<dt>Quality Assessment Required</dt>
					<dd>
						<p>
							<div class="normal-toggle-button" value="{{qa_flag}}">
								<input type="checkbox" for="qa_flag" >
							</div>
						</p>
					</dd>
					{{/qa_flag}}

					{{#assessement_notification_email}}
					<dt>Assessment Notification Email</dt>
					<dd>
						<p>
							{{assessement_notification_email}}
						</p>
					</dd>
					{{/assessement_notification_email}}
					
			 	</dl>
			 				
			 
			 	<h4>Harvester Settings</h4>
			 	<dl class="dl-horizontal">
			 		{{#uri}}
					<dt>URI</dt>
					<dd>{{uri}}</dd>
					{{/uri}}

					{{#provider_type}}
					<dt>Provider Type</dt>
					<dd>{{provider_type}}</dd>
					{{/provider_type}}

					{{#harvest_method}}
					<dt>Harvest Method</dt>
					<dd>{{harvest_method}}</dd>
					{{/harvest_method}}

					{{#harvest_date}}
					<dt>Harvest Date</dt>
					<dd>{{harvest_date}}</dd>
					{{/harvest_date}}

					{{#oai_set}}
					<dt>OAI-PMH Set</dt>
					<dd>{{oai_set}}</dd>
					{{/oai_set}}
			 	</dl>
		 	</div>
	    </div>
	</div>

	<div class="span4">
		<div class="widget-box">
			<div class="widget-title"><h5>Contributor Pages</h5></div>
			<div class="widget-content">
				<div class="well" id="contributor_groups"></div>
			</div>
		</div>
	</div>

</div>


</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromURLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from a URL</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Import registry objects from a test feed or backup.</div>

			<form class="form-horizontal">
				<label class="control-label">URL to import records from:</label>
				<div class="controls">
					<input type="text" name="url" placeholder="http://" />
					<p class="help-block">
						<small>Use full URL format (including http://)</small>
					</p>
				</div>
			</form>

			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool does not support OAI-PMH. You must use the Harvester to import from an OAI-PMH feed.
				</small>
			</p>
		</div>
		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading">
				<b>Loading XML from: </b><div id="remoteSourceURLDisplay"></div>
				<div class="progress progress-striped active">
				  <div class="bar" style="width: 100%;"></div>
				</div>
		</div>
		
		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading">
		</div>
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromXMLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from pasted contents</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Paste the registry object contents into the field below</div>

			<form class="form-vertical">
				<fieldset>
					<label> <b>Data to import:</b>
					</label>
					<textarea name="xml" id="xml_paste" rows="18" style="width:97%;font-family:Courier;font-size:8px;line-height:9px;"></textarea>
				</fieldset>
			</form>

			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool is designed for small imports (&lt;100 records). It may fail with larger bulk imports.
				</small>
			</p>
		</div>

		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading"> <b>Loading XML from:</b>
			<div id="remoteSourceURLDisplay"></div>
			<div class="progress progress-striped active">
				<div class="bar" style="width: 100%;"></div>
			</div>
		</div>

		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading"></div>
	</div>

	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>

</div>


<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="exportDataSource">
	
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Export Records As RIF-CS</h3>
	</div>
	
	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">
			
			<div class="alert alert-info">
				Select the type of records you want to export from this datasource. 
			</div>			
			<form class="form-vertical" id="data_source_export_form">
				<fieldset>
					<label><b>Selection form</b> </label>
					<input type="checkbox" name="activity" value="yes" checked="checked" />Activities<br/>
					<input type="checkbox" name="collection" value="yes" checked="checked" />Collections<br/>
					<input type="checkbox" name="party" value="yes" checked="checked" />Parties<br/>
					<input type="checkbox" name="service" value="yes" checked="checked" />Services<br/>
					<br/>
					<select name="status" data-placeholder="Choose by Status" tabindex="1" class="chzn-select input-xlarge" for="class_1">
						<option value="All">ALL status</option>
						<option value="PUBLISHED">PUBLISHED</option>
						<option value="APPROVED">APPROVED</option>
						<option value="DRAFT">DRAFT</option>
						<option value="SUBMITTED_FOR_ASSESSMENT">SUBMITTED_FOR_ASSESSMENT</option>
						<option value="MORE_WORK_REQUIRED">MORE_WORK_REQUIRED</option>
						<option value="ASSESSMENT_IN_PROGRESS">ASSESSMENT_IN_PROGRESS</option>
					</select>
					<!--label><b>Limit: </b> </label><input type="text" name="limit" value="20" /><br/-->
				</fieldset>
			</form>
		</div>
		
		<!-- A hidden loading screen -->
		<div id="loadingScreen" class="modal-body hide loading">
				<b>Generating XML...
				<div class="progress progress-striped active">
				  <div class="bar" style="width: 100%;"></div>
				</div>
		</div>
		<div id="error-modal" class="modal-body hide loading">
				<b>Failed to generate export file...</b>
		</div>

		
		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading">
		</div>
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary exportRecord" type="xml" data-loading-text="Fetching records...">Show me the XML</a>
		<a href="javascript:;" class="btn btn-primary exportRecord" type="file" data-loading-text="Fetching records...">Get My File</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

	{{/item}}
</script>

<!-- Successful import screen mustache template -->
<div class="hide" id="import-screen-success-report-template">
	<div class="alert alert-success">
		{{message}}
	</div>
	
	{{#log}}
		<pre class="well linenums">{{log}}</pre>
	{{/log}}
</div>




<!-- mustache template for data source edit single-->
<script type="text/x-mustache"  id="data-source-edit-template">
{{#item}}
	<div class="content-header">
		<h1>{{title}}</h1>
		<ul class="nav nav-pills">
			<li class="view page-control" data_source_id="{{data_source_id}}"><a href="#">Dashboard</a></li>
			<li class="mmr page-control" data_source_id="{{data_source_id}}"><a href="#">Manage Records</a></li>
			<li class="report page-control" data_source_id="{{data_source_id}}"><a href="#">Reports</a></li>
			<li class="active settings page-control" data_source_id="{{data_source_id}}"><a href="#">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'List My Datasources')?>
		<a href="#!/view/{{data_source_id}}" class="">{{title}}</a>
		<a href="javascript:;" class="current">Edit</a>
	</div>
<div class="container-fluid">
<div class="row-fluid">

	<div class="widget-box">
		<div class="widget-title">
		    <ul class="nav nav-tabs">
		  <li class="active"><a href="#admin" data-toggle="tab">Account Administration Information</a></li>
		  <li><a href="#records" data-toggle="tab">Records Management Settings</a></li>
		  <li><a href="#harvester" data-toggle="tab">Harvester Settings</a></li>
		</ul>
		</div>
	<div class="widget-content nopadding">
		

		<form class="form-horizontal" id="edit-form">
			<div class="tab-content">
				<div id="admin" class="tab-pane active">
					<fieldset>
						<legend>Account Administration Information</legend>
						<div class="control-group">
							<label class="control-label">Key</label>
							<div class="controls">
								{{key}}
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="title">Title</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="title" value="{{title}}">
								<p class="help-inline"><small>Help</small></p>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="record_owner">Record Owner</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="record_owner" value="{{record_owner}}">
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
							<label class="control-label" for="notes">Notes</label>
							<div class="controls">
								<textarea class="input-xlarge" name="notes">{{notes}}</textarea>
							</div>
						</div>
						
					</fieldset>
				</div>
				<div id="records" class="tab-pane">
					<fieldset>
						<legend>Records Management Settings</legend>
						<div class="control-group">
							<label class="control-label">Reverse Links</label>
							<div class="controls">
								<p class="help-inline">
								<div class="normal-toggle-button" value="{{allow_reverse_internal_links}}">
    								<input type="checkbox" for="allow_reverse_internal_links">
								</div>
								<p class="help-inline">Allow reverse internal Links</p>
								</p>

								<p class="help-inline">
								<div class="normal-toggle-button" value="{{allow_reverse_external_links}}">
    								<input type="checkbox" for="allow_reverse_external_links">
								</div>
								<p class="help-inline">Allow reverse external Links</p>
								</p>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label">Create Primary Relationships</label>
							<div class="controls">
								<p class="help-inline">
									<div class="create-primary normal-toggle-button" value="{{create_primary_relationships}}">
	    								<input type="checkbox" for="create_primary_relationships" name="create_primary_relationships">
									</div>
								</p>
							</div>
						</div>
						
						<div id="primary-relationship-form">
							<div class="well">
								<i>Datasources can have up to 2 primary relationships</i>
								<div class="clearfix"></div>
								<div class="pull-left">
									<div class="control-group">
										<label class="control-label">Registry Object Key</label>
										<div class="controls">
											<input type="text" class="input" name="primary_key_1" value="{{primary_key_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Collection</label>
										<div class="controls">
											<input type="text" class="input" name="collection_rel_1" value="{{collection_rel_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Service</label>
										<div class="controls">
											<input type="text" class="input" name="service_rel_1" value="{{service_rel_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Activity</label>
										<div class="controls">
											<input type="text" class="input" name="activity_rel_1" value="{{activity_rel_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Party</label>
										<div class="controls">
											<input type="text" class="input" name="party_rel_1" value="{{party_rel_1}}"/>
										</div>
									</div>
								</div>
								<div class="pull-left">
									<div class="control-group">
										<label class="control-label">Registry Object Key</label>
										<div class="controls">
											<input type="text" class="input" name="primary_key_2" value="{{primary_key_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Collection</label>
										<div class="controls">
											<input type="text" class="input" name="collection_rel_2" value="{{collection_rel_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Service</label>
										<div class="controls">
											<input type="text" class="input" name="service_rel_2" value="{{service_rel_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Activity</label>
										<div class="controls">
											<input type="text" class="input" name="activity_rel_2" value="{{activity_rel_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Party</label>
										<div class="controls">
											<input type="text" class="input" name="party_rel_2" value="{{party_rel_2}}"/>
										</div>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>

						

					<!-- This poush to NLA functionality has been excluded for release 10 as NLA are not using it
						<div class="control-group">
							<label class="control-label">Party Records to NLA</label>
							<div class="controls">
								<p class="help-inline">
								<div class="push_to_nla normal-toggle-button" value="{{push_to_nla}}">
    								<input type="checkbox" for="push_to_nla">
								</div>
								</p>
							</div>
						</div>

						<div id="nla-push-div" class="hide">
							<div class="control-group">					
								<div class="controls">	
									<p>
										ISIL: <input name="isil_value" value="{{isil_value}}"/>
									</p>
								</div>
							</div>	
						</div> -->
						<div class="control-group">
							<label class="control-label">Auto Publish Records</label>
							<div class="controls">
								<p class="help-inline">
								<div class="normal-toggle-button" value="{{auto_publish}}">
    								<input type="checkbox" for="auto_publish">
								</div>
								</p>								
							</div>
						</div>

						<div class="control-group">
							<label class="control-label">Quality Assessment Required</label>
							<div class="controls">
								<p class="help-inline">
								<div class="normal-toggle-button" value="{{qa_flag}}">
    								<input type="checkbox" for="qa_flag">
								</div>
								</p>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="assessement_notification_email">Assessment Notification Email</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="assessement_notification_email" value="{{assessement_notification_email}}">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="assessement_notification_email">Contributor Pages</label>
							<div class="controls">
								<input type="radio" class="contributor-page" name="institution_pages_radio" value="0"><p class="help-inline">Do not have contributor pages</p><br />
								<input type="radio" class="contributor-page" name="institution_pages_radio" value="1"><p class="help-inline">Auto generate Contributor Pages for all my groups</p><br />
								<input type="radio" class="contributor-page" name="institution_pages_radio" value="2"><p class="help-inline">Manually manage my Contributor Pages and groups</p><br />
								<input type="text" class="input-small hide" name="institution_pages"  id="institution_pages" value="{{institution_pages}}">	
								<p>
									<div class="well" id="contributor_groups2"></div>
								</p>
							</div>
						</div>
					</fieldset>
				</div>
				<div id="harvester" class="tab-pane">
					<fieldset>
						<legend>Harvester Settings</legend>
						<div class="control-group">
							<label class="control-label" for="uri">URI</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="uri" value="{{uri}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="provider_type">Provider Type</label>
							<div class="controls">
								<select data-placeholder="Choose a Provider Type" tabindex="1" class="chzn-select input-xlarge" for="provider_type">
									<option value=""></option>
									<option value="<?=RIFCS_SCHEME;?>">RIFCS</option>
									<?php 
									$crosswalks = getCrosswalks();
									foreach ($crosswalks AS $crosswalk)
									{
										echo '<option value="' . $crosswalk->metadataFormat() . '">' . $crosswalk->identify() . '</option>' . NL;
									}
									?>
								</select>
								<input type="text" class="input-small hide" name="provider_type" id="provider_type" value="{{provider_type}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_method">Harvest Method</label>
							<div class="controls">
								<select data-placeholder="Choose a Harvest Method" tabindex="1" class="chzn-select input-xlarge" for="harvest_method">
									<option value="GET">DIRECT (HTTP)</option>
									<option value="RIF">Harvested (OAI-PMH)</option>
								</select>
								<input type="text" class="input-small hide" name="harvest_method" id="harvest_method" value="{{harvest_method}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="oai_set">OAI Set</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="oai_set" value="{{oai_set}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="advanced_harvest_mode">Advanced Harvest Mode</label>
							<div class="controls">
								<select data-placeholder="Choose an Advanced Harvest Mode" tabindex="1" class="chzn-select input-xlarge" for="advanced_harvest_mode">
									<option value="STANDARD">Standard Mode</option>
									<option value="INCREMENTAL">Incremental Mode</option>
									<option value="REFRESH">Full Refresh Mode</option>
								</select>
								<input type="text" class="input-small hide" name="advanced_harvest_mode" id="advanced_harvest_mode" value="{{advanced_harvest_mode}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_date">Harvest Date</label>
							<div class="controls">																					
								<div class="input-append">						
									<input type="text" class="input-large datepicker" name="harvest_date" value="{{harvest_date}}"/>
									<button class="triggerDatePicker" type="button"><i class="icon-calendar"></i></button>								
								</div>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_frequency">Harvest Frequency</label>
							<div class="controls">
								<select data-placeholder="Choose a Harvest Frequency" tabindex="1" class="chzn-select input-xlarge" for="harvest_frequency">
									<option value=""></option>
									<option value="daily">daily</option>
									<option value="weekly">weekly</option>
									<option value="fortnightly">fortnightly</option>
									<option value="monthly">monthly</option>
								</select>
								<input type="text" class="input-small hide" name="harvest_frequency" id="harvest_frequency" value="{{harvest_frequency}}">
							</div>
						</div>
					</fieldset>
				</div>
			</div>

			<div class="form-actions">
				<button class="btn btn-primary" id="save-edit-form" data-loading-text="Saving..." data_source_id="{{data_source_id}}">Save</button>
				<button class="btn" id="test-harvest" data-loading-text="Testing Harvest..." data_source_id="{{data_source_id}}">Test Harvest</button>
			</div>
			<div class="modal hide" id="test_harvest_activity_log">
			  <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal">×</button>
			    <h3>Activity Log</h3>
			  </div>
			  <div class="modal-body"></div>
			  <div class="modal-footer">
			    
			  </div>
			</div>
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
</div>
{{/item}}
</script>


</section>


<?php $this->load->view('footer');?>