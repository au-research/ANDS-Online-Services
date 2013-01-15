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
<div class="container" id="main-content">
<section id="browse-datasources" class="hide">
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
			    		<select data-placeholder="Choose a Datasource to View" tabindex="1" class="chzn-select" id="datasource-chooser">
							<option value=""></option>
							<?php
								foreach($dataSources as $ds){
									echo '<option value="'.$ds['id'].'">'.$ds['title'].'</option>';
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

	<div class="span4" id="browse-datasources-right">
		<div class="box">
			<div class="box-header clearfix">
				<h1>Datasources statistics</h1>
			</div>
			<div class="box-content">
				Lorem ipsum tempor Duis Ut non ea voluptate. 
			</div>
		</div>

		<div class="box">
			<div class="box-header clearfix">
				<h1>Datasources statistics 2</h1>
			</div>
			<div class="box-content">
				Lorem ipsum tempor Duis Ut non ea voluptate. 
			</div>
		</div>
		
	</div>
</div>
	
	

    



	<!-- Load More Link -->
	

</section>

<section id="view-datasource" class="hide">Loading...</section>
<section id="edit-datasource" class="hide">Loading...</section>

</div>
<!-- end of main content container -->


<section id="datasource-templates">
<!-- mustache template for list of items-->
<div class="hide" id="items-template">
	{{#items}}
		<li>
		  	<div class="item" data_source_id="{{id}}">
		  		<div class="item-info"></div>
		  		<div class="item-snippet">
			  		<h3>{{title}}</h3>
			  		{{#counts}}
				  		{{#status}}
				  			<span class="tag status_{{status}}">{{status}} ({{count}})</span>
				  		{{/status}}
			  		{{/counts}}
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






<!-- mustache template for data source view single-->
<div class="hide" id="data-source-view-template">
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
<div class="container">
<div class="row">

	
	<div class="span8" id="data_source_view_container" data_source_id="">
		<div class="box">
		<div class="box-header">
			<ul class="breadcrumbs">
				<li><a href="javascript:;"><i class="icon-home"></i></a></li>
				<li><?php echo anchor('data_source/manage', 'Manage My Datasources');?></li>
				<li><a href="javascript:;" class="active">{{title}}</a></li>
			</ul>
	        <!--h1>{{title}}</h1-->
	        <div class="clearfix"></div>
	    </div>
	    <div class="row-fluid">
	    	
	 		<div>
	 			<div class="btn-toolbar">
					<div class="btn-group" data_source_id="{{data_source_id}}">
				  		<button class="btn edit"><i class="icon-edit"></i> Edit Data Source</button>
				  		<button class="btn history"><i class="icon-hdd"></i> View History</button>
				  		<button class="btn deleteRecord"><i class="icon-trash"></i> Delete Record</button>
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
	 
	    	

	    	<div class="">

				<h3>Account Administration Information</h3>
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
			 	<h3>Records Management Settings</h3>
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
					
				<!--
					{{#push_to_nla}}
					<dt>Push To NLA</dt>
					<dd>
						<p>
							<div class="normal-toggle-button" value="{{push_to_nla}}">
								<input type="checkbox" for="push_to_nla" >
							</div>
						</p>
					</dd>
					{{/push_to_nla}} -->

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
					<dt>Contributor Pages</dt>
					<dd>
						<p>
								<div class="well" id="contributor_groups"></div>
							</p>
						</dd>

			 	</dl>
			 				
			 
			 	<h3>Harvester Settings</h3>
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
	</div>

	<div class="span4">
		<div class="box">
			<div class="box-header clearfix"><h3>Data Source Status Summary</h3></div>
			<div class="box-content">
				<ul class="ro-list">
					{{#statuscounts}}
				  		{{#status}}
				  			<li class="status_{{status}}" name="{{status}}" type="status"><span class="name">{{status}}</span><span class="num">{{count}}</span></li>
				  		{{/status}}
			  		{{/statuscounts}}
				</ul>
			</div>
		</div>

		<div class="box">
			<div class="box-header clearfix"><h3>Data Source Quality Summary</h3></div>
			<div class="box-content">
				<ul class="ro-list">
					{{#qlcounts}}
				  		{{#level}}
				  			<li class="ql_{{level}}" name="{{level}}" type="quality_level"><span class="name">Quality Level {{level}}</span> <span class="num">{{count}}</span></li>
				  		{{/level}}
			  		{{/qlcounts}}
				</ul>
			</div>
		</div>

		<div class="box">
			<div class="box-header clearfix"><h3>Registry Objects Progression</h3></div>
			<div class="box-content" id="ro-progression">Loading...</div>
		</div>

	</div>

</div>

<div class="row">
	<div class="span12">
		<h3>Activity Log</h3><h5 id="log_summary"></h5>
		<div class="well" id="data_source_log_container"></div>
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
			
			<div class="alert alert-info">
				Import registry objects from a test feed or backup.
			</div>
			
			
				<form class="form-horizontal">
					<label class="control-label">URL to import records from: </label>
					<div class="controls"><input type="text" name="url" placeholder="http://" /><p class="help-block"><small>Use full URL format (including http://)</small></p></div>
				</form>
				
			<p><span class="label label-info">Note</span> <small>This tool does not support OAI-PMH. You must use the Harvester to import from an OAI-PMH feed.</small></p>
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
		<h3>Import Registry Objects from pasted XML</h3>
	</div>
	
	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">
			
			<div class="alert alert-info">
				Paste the XML contents into the field below. 
			</div>
			
			
			<form class="form-vertical">
				<fieldset>
					<label><b>XML to import:</b> </label>
					<textarea name="xml" id="xml_paste" rows="18" style="width:97%;font-family:Courier;font-size:8px;line-height:9px;"></textarea>
				</fieldset>
			</form>
			
			<p><span class="label label-info">Note</span> <small>This tool is designed for small imports (&lt;100 records). It may fail with larger bulk imports.</small></p>
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

	{{/item}}
</div>

<!-- Successful import screen mustache template -->
<div class="hide" id="import-screen-success-report-template">
	<div class="alert alert-success">
		{{message}}
	</div>
	
	{{#log}}
		<pre class="well linenums">{{log}}</pre>
	{{/log}}
</div>


<div class="hide" id="datasource-log-template">
	<table class="table table-hover">
		<thead><tr><th>#</th><th>TYPE</th><th>LOG</th><th>DATE</th></tr></thead>
		<tbody>
		{{#items}}
			<tr class="{{type}}"><td>{{id}}</td><td>{{type}}</td><td><pre>{{log}}</pre></td><td>{{date_modified}}</td></tr>
		{{/items}}
		</tbody>
	</table>
</div>


<!-- mustache template for data source edit single-->
<div class="hide" id="data-source-edit-template">
{{#item}}
	<div class="box">
	<div class="box-header">
	    <h1>Edit: {{title}}</h1>
	    <span class="right-widget">
        	<small><a href="javascript:;" class="close return-to-browse">&times;</a></small>
        </span>
        <div class="clearfix"></div>
	</div>
	<div class="">
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#admin" data-toggle="tab">Account Administration Information</a></li>
		  <li><a href="#records" data-toggle="tab">Records Management Settings</a></li>
		  <li><a href="#harvester" data-toggle="tab">Harvester Settings</a></li>
		</ul>

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
								<div class="creat-primary normal-toggle-button" value="{{create_primary_relationships}}">
    								<input type="checkbox" for="create_primary_relationships" name="create_primary_relationships">
								</div>

								</p>
							</div>
						</div>
						
						<div id="primary-div" class="hide">	
							<div class="control-group">
						
								<div class="controls">	
								<p>
									Class 			
 
								<select data-placeholder="Choose a Class" tabindex="1" class="chzn-select input-xlarge" for="class_1">
									<option value=""></option>
									<option value="party">Party</option>
									<option value="collection">Collection</option>
									<option value="service">Service</option>
									<option value="activity">Activity</option>
								</select>
								<input type="text" class="input-small hide" name="class_1"  id="class_1" value="{{class_1}}">									

								Class 			
    								<select data-placeholder="Choose a Class" tabindex="1" class="chzn-select input-xlarge" for="class_2">
    								<option value=""></option>	
									<option value="party">Party</option>
									<option value="collection">Collection</option>
									<option value="service">Service</option>
									<option value="activity">Activity</option>
								</select>	
								<input type="text" class="input-small hide" name="class_2"  id="class_2" value="{{class_2}}">	
								</p>
								</div>
								
								<div class="controls">	
								<p>
									Key: <input name="primary_key_1" value="{{primary_key_1}}"/>
									Key: <input name="primary_key_2" value="{{primary_key_2}}"/>	
								</p>						
    							</div>
    							
								<div class="controls">	
								<p>
									Relationship From: 
									Relationship From: 
								</p>						
    							</div> 
    							   							
 								<div class="controls">	
								<p>
									Collection: <input name="collection_rel_1" value="{{collection_rel_1}}"/>
									Collection: <input name="collection_rel_2" value="{{collection_rel_2}}"/>
								</p>						
    							</div> 
    							     							
  								<div class="controls">	
								<p>
									Service: <input name="service_rel_1" value="{{service_rel_1}}"/>
									Service: <input name="service_rel_2" value="{{service_rel_2}}"/>
								</p>						
    							</div> 
    							   							
    							<div class="controls">	
								<p>
									Activity: <input name="activity_rel_1" value="{{activity_rel_1}}"/>
									Activity: <input name="activity_rel_2" value="{{activity_rel_2}}"/>
								</p>						
    							</div> 
    							 							
    							<div class="controls">	
								<p>
									Party: <input name="party_rel_1" value="{{party_rel_1}}"/>
									Party: <input name="party_rel_2" value="{{party_rel_2}}"/>
								</p>						
    							</div> 
    							     							  							
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
									<option value="GET">RIF</option>
									<option value="RIF">RIF OAI-PMH</option>
								</select>
								<input type="text" class="input-small hide" name="provider_type" id="provider_type" value="{{provider_type}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_method">Harvest Method</label>
							<div class="controls">
								<select data-placeholder="Choose a Harvest Method" tabindex="1" class="chzn-select input-xlarge" for="harvest_method">
									<option value="RIF">DIRECT</option>
									<option value="RIF">Harvester DIRECT</option>
									<option value="RIF OAI-PMH">Harvester OAI-PMH</option>
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
			<button class="btn" id="save-edit-form" data-loading-text="Saving..." >Save</button>
			<button class="btn" id="test-harvest" data-loading-text="Testing Harvest..." >Test Harvest</button>
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
{{/item}}
</div>


</section>


<?php $this->load->view('footer');?>