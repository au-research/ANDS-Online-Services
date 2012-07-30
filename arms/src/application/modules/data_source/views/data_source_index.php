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
	<div class="page-header">
        <h1><?php echo $title;?><small><?php echo $small_title;?></small></h1>
    </div>

    <!-- Toolbar -->
    <div class="row-fluid" id="mmr_toolbar">
    	<div class="span4">
    		<span class="dropdown" id="switch_menu">
    		<a class="btn dropdown-toggle" data-toggle="dropdown" data-target="#switch_menu" href="#switch_menu">View<span class="caret"></span></a>
			  <ul class="dropdown-menu" id="switch_view">
			    <li><a href="javascript:;" name="thumbnails"><i class="icon-th"></i> Thumbnails View</a></li>
			    <li><a href="javascript:;" name="lists"><i class="icon-th-list"></i> List View</a></li>
			  </ul>
			</span>
		</div>
		<div class="span4"></div>
    	<div class="span4">
    		<select data-placeholder="Choose a Datasource" tabindex="1" class="chzn-select" style="width:350px;" id="datasource-chooser">
				<option value=""></option>
				<?php
					foreach($dataSources as $ds){
						echo '<option value="'.$ds['id'].'">'.$ds['title'].'</option>';
					}
				?>
			</select>
    	</div>
    </div>


    <!-- List of items will be displayed here, in this ul -->
	<ul class="thumbnails" id="items"></ul>

	<!-- Load More Link -->
	<div class="row-fluid">
		<div class="span12">
			<div class="well"><a href="javascript:;" id="load_more" page="1">Show More...</a></div>
		</div>
	</div>

</section>

<section id="view-datasource" class="hide">Loading...</section>
<section id="edit-datasource" class="hide">Loading...</section>

</div>
<!-- end of main content container -->

<!-- mustache template for list of items-->
<div class="hide" id="items-template">
	{{#items}}
		<li class="span3">
		  	<div class="item" data_source_id="{{id}}">
		  		<div class="item-info"></div>
		  		<div class="item-snippet">
			  		<h3>{{title}}</h3>
			  	</div>
		  		<div class="btn-group">
		  			<button class="btn open"><i class="icon-eye-open"></i></button>
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
	<div class="page-header">
        <h1>{{title}}<small><a href="javascript:;" class="close">&times;</a></small></h1>
    </div>
    <div class="row-fluid">
    	<div class="well">

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
			<dd>1<br/>2</dd>

			{{#create_primary_relationships}}
			<dt>Create Primary Relationships</dt>
			<dd>{{create_primary_relationships}}</dd>
			{{/create_primary_relationships}}

			{{#push_to_nla}}
			<dt>Push To NLA</dt>
			<dd>{{push_to_nla}}</dd>
			{{/push_to_nla}}

			{{#auto_publish}}
			<dt>Auto Publish</dt>
			<dd>{{auto_publish}}</dd>
			{{/auto_publish}}

			{{#qa_flag}}
			<dt>Quality Assessment Required</dt>
			<dd>{{qa_flag}}</dd>
			{{/qa_flag}}

			{{#assessement_notification_email}}
			<dt>Assessment Notification Email</dt>
			<dd>{{assessement_notification_email}}</dd>
			{{/assessement_notification_email}}

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
	 	<h3>Activity Log</h3>
	 	<div class="well">Loading ...</div>
	 	</div>
    </div>
	{{/item}}
</div>

<div id="error-template" class="hide">
	<div class="alert alert-error">
		{{.}}
	</div>
</div>

<?php $this->load->view('footer');?>