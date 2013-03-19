<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	exclude-result-prefixes="extRif ro">
	<xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
	<xsl:param name="base_url"/>


	<xsl:variable name="ro_class">
		<xsl:apply-templates select="ro:registryObject/ro:collection | ro:registryObject/ro:activity | ro:registryObject/ro:party  | ro:registryObject/ro:service" mode="getClass"/>
	</xsl:variable>

	<xsl:template match="ro:registryObject">

	<xsl:variable name="registry_object_id"><xsl:value-of select="//extRif:id"/></xsl:variable>
	<xsl:variable name="display_title"><xsl:value-of select="//extRif:displayTitle"/></xsl:variable>

	
	<div id="sidebar">
		<div id="mode-switch" class="btn-group" style="text-align: center;margin: 10px auto 0px auto;">
			<button class="btn btn-primary" aro-mode="simple">Simple</button>
			<button class="btn" aro-mode="advanced">Advanced</button>
		</div>
		<ul id="simple-menu" class="hide">
			<li class="active"><a href="#simple_describe" data-toggle="tab"><span>Describe your Data</span></a></li>
			<li class=""><a href="#simple_link" data-toggle="tab"><span>Link your Data</span></a></li>
			<li class=""><a href="#simple_citation" data-toggle="tab"><span>Create a Citation</span></a></li>
			<li class=""><a href="#simple_protect" data-toggle="tab"><span>Protect your Data</span></a></li>
		</ul>
		<ul id="advanced-menu" class="">
			<li class="active"><a href="#admin" data-toggle="tab">Record Administration</a></li>
			<li><a href="#names" data-toggle="tab">Names</a></li>
			<li><a href="#descriptions_rights" data-toggle="tab">Descriptions/Rights</a></li>
			<li><a href="#identifiers" data-toggle="tab">Identifiers</a></li>
			<li><a href="#dates" data-toggle="tab">Dates</a></li>
			<li><a href="#locations" data-toggle="tab">Locations</a></li>
			<li><a href="#coverages" data-toggle="tab">Coverage</a></li>
			<li><a href="#relatedObjects" data-toggle="tab">Related Objects</a></li>
			<li><a href="#subjects" data-toggle="tab">Subjects</a></li>
			<li><a href="#relatedinfos" data-toggle="tab">Related Info</a></li>
			<xsl:if test="$ro_class = 'service'">
				<li><a href="#accesspolicies" data-toggle="tab">Accesspolicy</a></li>
			</xsl:if>
			<xsl:if test="$ro_class = 'collection'">
				<li><a href="#citationInfos" data-toggle="tab">Citation Info</a></li>
			</xsl:if>
			<xsl:if test="$ro_class != 'collection'">
				<li><a href="#existencedates" data-toggle="tab">Existence Dates</a></li>
			</xsl:if>
		</ul>
	</div>

	<div id="content" style="margin-top:45px;">
		<div class="content-header">
			<h1><xsl:value-of select="$display_title"/></h1>
			<div class="btn-group">
				<a class="btn" title="Manage Files" id="master_export_xml"><i class="icon-download"></i> Export RIFCS</a>
				<a class="btn btn-primary" title="Manage Files" id="validate">Validate</a>
				<a class="btn btn-primary" title="Manage Files" id="save"><i class="icon-white icon-hdd"></i> Save</a>
			</div>
		</div>
		<div id="breadcrumb">
			<a href="{$base_url}" title="Go to Home" class="tip-bottom">Home</a>
			<a href="{$base_url}registry_object/view/{$registry_object_id}" title="" class="current"><xsl:value-of select="$display_title"/></a>
			<a href="#" class="">Edit</a>
		</div>
		<form class="form-horizontal" id="edit-form">
			<xsl:call-template name="simpleDescribeTab" mode="{$ro_class}"/>
			<xsl:call-template name="recordAdminTab"/>
			<xsl:call-template name="namesTab"/>
			<xsl:call-template name="descriptionRightsTab"/>
			<xsl:call-template name="identifiersTab"/>
			<xsl:call-template name="datesTab"/>
			<xsl:call-template name="locationsTab"/>
			<xsl:call-template name="coverageTab"/>
			<xsl:call-template name="relatedObjectsTab"/>
			<xsl:call-template name="subjectsTab"/>
			<xsl:call-template name="relatedinfosTab"/>
			<xsl:if test="$ro_class = 'service'">
				<xsl:call-template name="accesspolicyTab"/>
			</xsl:if>
			<xsl:if test="$ro_class = 'collection'">
				<xsl:call-template name="citationInfoTab"/>
			</xsl:if>
			<xsl:if test="$ro_class != 'collection'">
				<xsl:call-template name="ExistenceDatesTab"/>
			</xsl:if>
		</form>
		<xsl:call-template name="blankTemplate"/>
		<div class="modal hide" id="myModal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3>Alert</h3>
			</div>
			<div class="modal-body"/>
			<div class="modal-footer"> </div>
		</div>
	</div>


		<input type="hidden" class="hide" id="ro_id" value="{$registry_object_id}"/>
		<input type="hidden" class="hide" id="ro_class" value="{$ro_class}"/>
		<input type="hidden" class="hide" id="originatingSource" value="{$ro_class}"/>
	</xsl:template>

	<xsl:template match="ro:collection | ro:activity | ro:party  | ro:service" mode="getClass">
		<xsl:value-of select="name()"/>
	</xsl:template>

	<xsl:template name="simpleDescribeTab" mode="collection">
		<!-- Record Admin-->
		<div id="simple_describe" class="pane">
			<fieldset>
				<legend>Describe your Data</legend>

				<xsl:variable name="simpleRecordName" select="ro:collection/ro:name[@type='primary']/ro:namePart[1]" />
				<xsl:variable name="simpleRecordType" select="ro:collection/@type" />
				<xsl:variable name="simpleBriefDescription" select="ro:collection/ro:description" />
				<xsl:variable name="simpleFullDescription" select="ro:collection/ro:description[@type='full']" />
				<xsl:variable name="simpleRecordIdentifier" select="ro:collection/ro:identifier[0]" />
				<xsl:variable name="simpleRecordIdentifierType" select="ro:collection/ro:identifier[0]/@type" />
				<xsl:variable name="simpleRecordGroup" select="@group" />


				<div class="control-group">
					<label class="control-label" for="simple_collectionTitle">* Collection Title</label>
					<div class="controls">
							<input type="text" field-bind="ro:collection/ro:name[@type='primary']/ro:namePart[1]" class="input-xxlarge" name="simpleRecordName" value="{$simpleRecordName}"/>
						<p class="help-inline">
							<small></small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="simple_briefDescription">* Brief Collection Description</label>
					<div class="controls">
							<textarea rows="5" class="input-xxlarge" name="simpleBriefDescription"><xsl:value-of select="$simpleBriefDescription"/></textarea>
						<p class="help-block">
							<button id="simpleFullDescriptionToggle" class="btn btn-mini btn-info">add an extended description</button>
						</p>
					</div>
				</div>

				<div class="control-group hide">
					<label class="control-label" for="simpleFullDescription">Full Collection Description</label>
					<div class="controls">
							<textarea rows="5" class="input-xxlarge" name="simpleFullDescription" id="simpleFullDescription"><xsl:value-of select="$simpleFullDescription"/></textarea>
						<p class="help-block">
							<small></small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="simpleRecordGroup">* Group/Institution Name</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" field-bind="ro:collection/@group" class="input-large" name="simpleRecordGroup" value="{$simpleRecordGroup}"/>
						</div>
						<p class="help-inline">
							<small></small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="simple_collectionTitle">* Type of Collection</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" field-bind="ro:collection/@type" class="input-large" name="simpleRecordType" value="{$simpleRecordType}"/>
						</div>
						<p class="help-inline">
							<small></small>
						</p>
					</div>
				</div>



				<hr/>
				<h4>About the Data</h4>

				<div class="split-left">
					<div class="control-group">
						<h5>How is the data identified?</h5>
						<label class="control-label" for="simple_briefDescription">* Identifier:						</label>

						<div class="controls">
							<div class="input-prepend">
								<button class="btn triggerTypeAhead" type="button">
									<i class="icon-chevron-down"/>
								</button>
								<input type="text" field-bind="ro:collection/ro:identifier/@type" class="input-mini" name="simpleRecordIdentifierType" value="{$simpleRecordIdentifierType}" placeholder="type"/>
							</div>

							<input type="text" field-bind="ro:collection/ro:identifier" class="input-medium" name="simpleRecordIdentifier" value="{$simpleRecordIdentifier}" placeholder="identifier value"/>		
						</div>

						<div>

							<p class="pull-right" style="margin-right:18px;">
								<button class="btn btn-mini pull-right" id="simpleAddMoreIdentifiers">
									<i class="icon-plus"></i> more
								</button><br/>
								<button class="btn btn-mini btn-info" style="margin-top:8px;" id="simpleAddMoreIdentifiers">
									<i class="icon-wrench icon-white"></i> No identifier?
								</button>
							</p>
						</div>

					</div>
					
				</div>

				<div class="split-right">
					<div class="control-group">
						<h5>What time period does the data cover?</h5>
						<label class="control-label" for="simple_briefDescription">Data Start Date</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="date_accessioned"
									value="{ro:collection/@dateAccessioned}"/>
								<button class="btn triggerDatePicker" type="button">
									<i class="icon-calendar"/>
								</button>
								<p class="help-inline">
									<small/>
								</p>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="simple_briefDescription">Data End Date</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="date_accessioned"
									value="{ro:collection/@dateAccessioned}"/>
								<button class="btn triggerDatePicker" type="button">
									<i class="icon-calendar"/>
								</button>
								<p class="help-inline">
									<small/>
								</p>
							</div>
						</div>
					</div>
				</div>

				<div class="clear"><br/></div>


				<div class="split-left">
					<div class="control-group">
						<h5>Field(s) of Research</h5>
						<label class="control-label" for="simpleFORSubject">* FOR Category:</label>
						<div class="controls">
							<input type="text" class="input-medium" name="simpleFORSubject"
								value="{ro:collection/@dateAccessioned}"/>
							
							<button class="btn btn-mini" style="margin-left:8px;" id="simpleAddMoreIdentifiers">
								<i class="icon-plus"></i> more
							</button>

							<p class="help-block">
								<small>Select the most specific category that applies</small>
							</p>
						</div>
					</div>
				</div>

				<div class="split-right">
					<div class="control-group">
						<h5><br/></h5>
						<label class="control-label" for="simpleKeywords">Subject Keywords:</label>
						<div class="controls">
							<input type="text" class="input-medium" name="simpleFORSubject"
								value="{ro:collection/@dateAccessioned}"/>
							
							<button class="btn btn-mini" style="margin-left:8px;" id="simpleAddMoreIdentifiers">
								<i class="icon-plus"></i> more
							</button>

							<p class="help-block">
								<small>Any topical keywords that will assist searching</small>
							</p>
						</div>
					</div>
				</div>

			</fieldset>


			<div class="center_footer">
				<button class="btn btn-primary pull-right" id="simpleAddMoreIdentifiers">
					<i class="icon-share-alt icon-white"></i> Proceed to Link your Data (Step 2)
				</button>
			</div>
			<div class="clear"></div>
		</div>
	</xsl:template>


	<xsl:template name="recordAdminTab">
		<!-- Record Admin-->
		<div id="admin" class="pane">
			<fieldset>
				<legend>Record Administration</legend>
				<xsl:variable name="ro_type">
					<xsl:apply-templates select="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type"/>
				</xsl:variable>
				<xsl:variable name="dataSourceID">
					<xsl:value-of select="extRif:extendedMetadata/extRif:dataSourceID"/>
				</xsl:variable>
				<xsl:variable name="dateModified">
					<xsl:apply-templates select="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified"/>
				</xsl:variable>

				<div class="control-group">
					<label class="control-label" for="type">Type</label>
					<div class="controls">
						<input type="text" id="{generate-id()}_value" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'Type')}" name="type" value="{$ro_type}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="ds">Data Source</label>
					<div class="controls">
						<select id="data_sources_select"/>
						<input type="text" id="data_source_id_value" class="input-small hide"
							name="ds" value="{$dataSourceID}"/>
					</div>
				</div>
				
				
				<div class="control-group">
					<label class="control-label" for="originatingSource">Originating Source</label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" id="originatingSource" name="originatingSource" placeholder="Value" value="{ro:originatingSource/text()}" class="inner_input"/>
							<input type="text" id="originatingSource" class="inner_input_type rifcs-type" vocab="RIFCSOriginatingSourceType" name="originatingSourceType" placeholder="Type"  value="{ro:originatingSource/@type}"/>
						</span>
					</div>
				</div>
				

				<div class="control-group">
					<label class="control-label" for="group">Group</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" class="input-large" name="group" value="{@group}"/>
						</div>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group warning">
					<label class="control-label" for="key">Key</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="key" value="{ro:key}"/>
						<button class="btn btn" id="generate_random_key">
							<i class="icon-refresh"/> Generate Random Key </button>
						<p class="help-inline">
							<small>Key must be unique and is case sensitive</small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="date_modified">Date Modified</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-large datepicker" name="date_modified"
								value="{$dateModified}"/>
							<button class="btn triggerDatePicker" type="button">
								<i class="icon-calendar"/>
							</button>
							<p class="help-inline">
								<small/>
							</p>
						</div>
					</div>
				</div>

				<xsl:if test="ro:collection">
					<div class="control-group">
						<label class="control-label" for="date_accessioned">Date Accessioned</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="date_accessioned"
									value="{ro:collection/@dateAccessioned}"/>
								<button class="btn triggerDatePicker" type="button">
									<i class="icon-calendar"/>
								</button>
								<p class="help-inline">
									<small/>
								</p>
							</div>
						</div>
					</div>
				</xsl:if>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="namesTab">
		<div id="names" class="pane">
			<fieldset>
				<legend>Names</legend>

				<xsl:apply-templates
					select="ro:collection/ro:name | ro:activity/ro:name | ro:party/ro:name  | ro:service/ro:name"/>
				<div class="separate_line"/>

				<button class="btn btn-primary addNew" type="name">
					<i class="icon-plus icon-white"/> Add Name </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="datesTab">
		<div id="dates" class="pane">
			<fieldset>
				<legend>Dates</legend>

				<xsl:apply-templates
					select="ro:collection/ro:dates | ro:activity/ro:dates | ro:party/ro:dates  | ro:service/ro:dates"/>
				<div class="separate_line"/>

				<button class="btn btn-primary addNew" type="dates">
					<i class="icon-plus icon-white"/> Add Dates </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template match="ro:collection/ro:dates | ro:activity/ro:dates | ro:party/ro:dates  | ro:service/ro:dates">
		<div class="aro_box" type="dates">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDatesType" name="type" placeholder="Date Type" value="{@type}"/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<xsl:apply-templates select="ro:date" mode="dates" />
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="dates_date">
					<i class="icon-plus icon-white"></i> Add new Date
				</button>
			</div>
			
		</div>
	</xsl:template>
	
	<xsl:template match="ro:date" mode="dates">
		<div class="aro_box_part" type="dates_date">
			<div class="control-group">
				<label class="control-label" for="title">Date: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input datepicker" value="{text()}"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value="{@type}"/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
				</div>
			</div>
		</div>
	</xsl:template>					
						
	<xsl:template match="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified">
		<xsl:value-of select="."/>
	</xsl:template>
	

	<xsl:template match="ro:collection/ro:name | ro:activity/ro:name | ro:party/ro:name  | ro:service/ro:name">
		<div class="aro_box" type="name">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-plus"/></a>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSNameType" name="type" placeholder="Type" value="{@type}"/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<xsl:apply-templates select="ro:namePart"/>
			<div class="separate_line"/>
			<div class="controls hide">
				<button class="btn btn-primary addNew" type="namePart">
					<i class="icon-plus icon-white"></i> Add Name Part
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:namePart">
		<div class="aro_box_part hide" type="namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input" value="{text()}"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value="{@type}"/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template name="descriptionRightsTab">
		<div id="descriptions_rights" class="pane">
			<fieldset>
				<legend>Descriptions / Rights</legend>
				<xsl:apply-templates
					select="ro:collection/ro:description | ro:activity/ro:description | ro:party/ro:description  | ro:service/ro:description"/>
				<xsl:apply-templates
					select="ro:collection/ro:rights | ro:activity/ro:rights | ro:party/ro:rights  | ro:service/ro:rights"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="description">
					<i class="icon-plus icon-white"/> Add Description </button>
				<button class="btn btn-primary addNew" type="rights">
					<i class="icon-plus icon-white"/> Add Rights </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="accesspolicyTab">
		<div id="accesspolicies" class="pane">
			<fieldset>
				<legend>Access Policy</legend>
				<xsl:apply-templates select="ro:service/ro:accessPolicy"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="accesspolicy">
					<i class="icon-plus icon-white"/> Add Access Policy </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="citationInfoTab">
		<div id="citationInfos" class="pane">
			<fieldset>
				<legend>Citation Info</legend>
				<xsl:apply-templates select="ro:collection/ro:citationInfo"/>
				<div class="separate_line"/>
				<div class="btn-group dropup">
					<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"> <i class="icon-pencil icon-white"></i> Add Citation Info</button>
					<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						<span class="caret"/>
					</button>
					<ul class="dropdown-menu">
						<li><a href="javascript:;" class="addNew" type="fullCitation">Add Full Citation</a></li>
						<li><a href="javascript:;" class="addNew" type="citationMetadata">Add Citation Metadata</a></li>
					</ul>
					<button class="btn export_xml btn-info"> Export XML fragment </button>
				</div>
				
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:description | ro:activity/ro:description | ro:party/ro:description  | ro:service/ro:description">
		<div class="aro_box" type="description">
			<div class="aro_box_display clearfix">
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDescriptionType" name="type" placeholder="Type" value="{@type}"/>
				<h1>Description</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<textarea name="value" class="editor">
				<xsl:value-of disable-output-escaping="yes" select="text()"/>
			</textarea>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:rights | ro:activity/ro:rights | ro:party/ro:rights  | ro:service/ro:rights">
		<div class="aro_box" type="rights">
			<div class="aro_box_display clearfix">
				<h1>Rights</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<div class="aro_box_part" type="rightStatement">
				<label>Rights Statement</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:rightStatement/@rightsUri}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:rightStatement/text()}"/>
			</div>
			<div class="aro_box_part" type="licence">
				<label>Licence</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:licence/@rightsUri}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:licence/text()}"/>
			</div>		
			<div class="aro_box_part" type="accessRights">
				<label>Access Rights</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:accessRights/@rightsUri}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:accessRights/text()}"/>
			</div>
		</div>
	</xsl:template>

	<xsl:template name="subjectsTab">
		<div id="subjects" class="pane">
			<fieldset>
				<legend>Subjects</legend>
				<xsl:apply-templates
					select="ro:collection/ro:subject | ro:activity/ro:subject | ro:party/ro:subject  | ro:service/ro:subject"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="subject">
					<i class="icon-plus icon-white"/> Add Subject </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="identifiersTab">
		<div id="identifiers" class="pane">
			<fieldset>
				<legend>Identifiers</legend>
				<xsl:apply-templates
					select="ro:collection/ro:identifier | ro:activity/ro:identifier | ro:party/ro:identifier  | ro:service/ro:identifier"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="identifier">
					<i class="icon-plus icon-white"/> Add Identifier </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="relatedObjectsTab">
		<div id="relatedObjects" class="pane">
			<fieldset>
				<legend>Related Objects</legend>
				<xsl:apply-templates
					select="ro:collection/ro:relatedObject | ro:activity/ro:relatedObject | ro:party/ro:relatedObject  | ro:service/ro:relatedObject"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="relatedObject">
					<i class="icon-plus icon-white"/> Add Related Object </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="relatedinfosTab">
		<div id="relatedinfos" class="pane">
			<fieldset>
				<legend>Related Infos</legend>
				<xsl:apply-templates
					select="ro:collection/ro:relatedInfo | ro:activity/ro:relatedInfo | ro:party/ro:relatedInfo | ro:service/ro:relatedInfo"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="relatedinfo">
					<i class="icon-plus icon-white"/> Add related Info </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="locationsTab">
		<div id="locations" class="pane">
			<fieldset>
				<legend>Locations</legend>
				<xsl:apply-templates
					select="ro:collection/ro:location | ro:activity/ro:location | ro:party/ro:location  | ro:service/ro:location"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="location">
					<i class="icon-plus icon-white"/> Add Location </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>
	
	<xsl:template name="coverageTab">
		<div id="coverages" class="pane">
			<fieldset>
				<legend>Coverage</legend>
				<xsl:apply-templates
					select="ro:collection/ro:coverage | ro:activity/ro:coverage | ro:party/ro:coverage  | ro:service/ro:coverage"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="coverage">
					<i class="icon-plus icon-white"/> Add Coverage </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>
	
	<xsl:template name="ExistenceDatesTab">
		<div id="existencedates" class="pane">
			<fieldset>
				<legend>Existence dates</legend>
				<xsl:apply-templates select="ro:activity/ro:existenceDates | ro:party/ro:existenceDates  | ro:service/ro:existenceDates"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="existenceDate">
					<i class="icon-plus icon-white"/> Add Existence Date </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template match="ro:activity/ro:existenceDates | ro:party/ro:existenceDates  | ro:service/ro:existenceDates">
		<div class="aro_box" type="existenceDate">
			<div class="aro_box_display clearfix">
				<div class="controls">
					<input type="text" class="input-small" name="startDate_type" placeholder="startDate Type" value="{startDate/@type}"/>
					<input type="text" class="input-xlarge" name="startDate_value" placeholder="startDate Value" value="{startDate/text()}"/>
					<input type="text" class="input-small" name="endDate_type" placeholder="endDate Type" value="{endDate/@type}"/>
					<input type="text" class="input-xlarge" name="endDate_value" placeholder="endDate Value" value="{endDate/text()}"/>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:collection/ro:relatedInfo | ro:activity/ro:relatedInfo | ro:party/ro:relatedInfo | ro:service/ro:relatedInfo">
		<div class="aro_box" type="relatedInfo">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSRelatedInformationType" name="type" placeholder="Type" value="{@type}"/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
		
			<div class="aro_box_part" type="relatedInfo">
				<div class="control-group">
					<label class="control-label" for="title">Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" placeholder="Title" value="{ro:title/text()}"/>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Identifier: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input input-large" name="identifier" placeholder="Identifier" value="{ro:identifier/text()}"/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSIdentifierType" name="identifier_type" placeholder="Identifier Type" value="{ro:identifier/@type}"/>
						</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Notes: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="notes" placeholder="Notes" value="{ro:notes/text()}"/>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:collection/ro:subject  | ro:activity/ro:subject  | ro:party/ro:subject   | ro:service/ro:subject">
		<div class="aro_box" type="subject">
			<div class="aro_box_display clearfix">
				<span class="inputs_group">
					<input type="text" class="input-xlarge inner_input" placeholder="Value" value="{text()}" name="value"/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSSubjectType" name="type" placeholder="type" value="{@type}"/>
				</span>
				<button class="btn btn-mini btn-danger remove" type="button">
					<i class="icon-remove icon-white"/>
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:identifier  | ro:activity/ro:identifier  | ro:party/ro:identifier   | ro:service/ro:identifier">
		<div class="aro_box" type="identifier">
			<div class="aro_box_display clearfix">
				<span class="inputs_group">
					<input type="text" class="input-xlarge inner_input" placeholder="Value" value="{text()}" name="value"/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSIdentifierType" name="type" placeholder="type" value="{@type}"/>
				</span>
				<button class="btn btn-mini btn-danger remove" type="button">
					<i class="icon-remove icon-white"/>
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:relatedObject | ro:activity/ro:relatedObject | ro:party/ro:relatedObject  | ro:service/ro:relatedObject">
		<div class="aro_box" type="relatedObject">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part">
				<div class="control-group">
					<label class="control-label" for="title">Key: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="key" value="{ro:key}" placeholder="Related Object Key"/>
					</div>
				</div>
			</div>

			<xsl:apply-templates select="ro:relation"/>
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="relation"><i class="icon-plus icon-white"/> Add Relation </button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:location | ro:activity/ro:location | ro:party/ro:location  | ro:service/ro:location">
		<div class="aro_box" type="location">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1/>
				<div class="control-group">
					<div class="controls">
						<input type="text" class="input-small" name="dateFrom" placeholder="dateFrom" value="{@dateFrom}"/>
						<input type="text" class="input-small" name="dateTo" placeholder="dateTo" value="{@dateTo}"/>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>
			<div class="aro_subbox" type="address">
				<h1>Address</h1>
				<xsl:apply-templates select="ro:address"/>
				<div class="separate_line"/>

				<div class="btn-group dropup">
					<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"> <i class="icon-envelope icon-white"></i> Add Address</button>
					<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						<span class="caret"/>
					</button>
					<ul class="dropdown-menu">
						<li>
							<a href="javascript:;" class="addNew" type="electronic">Add Electronic
								Address</a>
						</li>
						<li>
							<a href="javascript:;" class="addNew" type="physical">Add Physical
								Address</a>
						</li>
					</ul>
				</div>

			</div>

			<div class="aro_subbox" type="spatial">
				<h1>Spatial Location</h1>
				<xsl:apply-templates select="ro:spatial"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="spatial">
					<i class="icon-map-marker icon-white"/> Add Spatial Location </button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:coverage | ro:activity/ro:coverage | ro:party/ro:coverage  | ro:service/ro:coverage">
		<div class="aro_box" type="coverage">
			<xsl:apply-templates select="ro:temporal"/>
			<xsl:apply-templates select="ro:spatial"/>
			<div class="separate_line"/>	
			<div class="btn-group dropup">
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"> <i class="icon-envelope icon-white"></i> Add Coverage</button>
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="caret"/>
				</button>
				<ul class="dropdown-menu">
					<li>
						<a href="javascript:;" class="addNew" type="temporal">Add Temporal Coverage</a>
					</li>
					<li>
						<a href="javascript:;" class="addNew" type="spatial">Add Spatial Coverage</a>
					</li>
				</ul>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:temporal">
		<div class="aro_box" type="temporal">
			<div class="aro_box_display clearfix">
				<h1>Temporal Coverage</h1>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
			<xsl:apply-templates select="date" mode="coverage"/>
			<xsl:apply-templates select="text"/>
		</div>
	</xsl:template>

	<xsl:template match="ro:date" mode="coverage">
		<div class="aro_box_part" type="coverage_date">
			<label class="control-label" for="title">Date: </label>
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDateFormat" name="type" placeholder="Date Format" value=""/>
			</span>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>
	
	<xsl:template match="ro:date">
		<div class="aro_box_part" type="date">
			<label class="control-label" for="title">Date: </label>
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>
	
	<xsl:template match="ro:text">
		<div class="aro_box_part" type="text">
			<label class="control-label" for="title">Text: </label>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>

	<xsl:template match="ro:relation">
		<div class="aro_box_part" type="relation">
			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value="{@type}"/>
					<input type="text" class="inner_input input-large" name="description" placeholder="Description" value="{description}"/>
					<input type="text" class="input-small" name="url" placeholder="URL" value="{ro:url}"/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:spatial">
		<div class="aro_box_part" type="spatial">
			<div class="control-group">
				<label class="control-label" for="title">Spatial: </label>
				<div class="controls">					
					<span>Type:						
						<input type="text" class="input-small rifcs-type" vocab="RIFCSSpatialType" name="type" placeholder="Type" value="{@type}"/>
						<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					</span>Value:
						<input type="text" class="input-xlarge spatial_value" name="value" placeholder="Value" value="{text()}"/>
						<button class="btn triggerMapWidget" type="button"><i class="icon-globe"></i></button>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:address">
		<xsl:apply-templates select="ro:electronic | ro:physical"/>
		<div class="separate_line"/>
	</xsl:template>

	<xsl:template match="ro:electronic">
		<div class="aro_box_part" type="electronic">
			<label class="control-label" for="title">Electronic Address: </label>
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSElectronicAddressType" name="type" placeholder="Type" value="{@type}"/>
				</span>
				<input type="text" class="input-xlarge" name="value" placeholder="Value"
					value="{ro:value}"/>
				<xsl:if test="ancestor::ro:service">
					<button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
					<div class="parts hide">
						<xsl:apply-templates select="ro:arg"/>
						<div class="separate_line"/>
						<button class="btn btn-primary addNew" type="arg">
							<i class="icon-plus icon-white"></i> Add Args
						</button>
					</div>
				</xsl:if>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:physical">
		<div class="aro_box_part" type="physical">
			<label class="control-label" for="title">Physical Address: </label>
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSPhysicalAddressType" name="type" placeholder="Type" value="{@type}"/>
				</span>
				<button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
				<div class="aro_box_part" type="addressParts">
					<xsl:apply-templates select="ro:addressPart"/>
					<div class="separate_line"/>
					<button class="btn btn-primary addNew" type="addressPart">
						<i class="icon-plus icon-white"></i> Add Address Part
					</button>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:arg">
		<div class="aro_box_part" type="arg">
			<label class="control-label" for="title">Arg: </label>
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgType" name="type" placeholder="Type" value="{@type}"/>
				</span>
				<input type="text" class="input-xlarge" name="required"  placeholder="Required" value="{@required}"/>
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgUse" name="use"  placeholder="Use" value="{@use}"/>
				</span>
				<input type="text" class="input-xlarge" name="value"  placeholder="Value" value="{text()}"/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"></i>
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:addressPart">
		<div class="aro_box_part" type="addressPart">
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSPhysicalAddressPartType" name="type" placeholder="Type" value="{@type}"/>
				</span>
				<input type="text" class="input-xlarge" name="value" placeholder="value"
					value="{text()}"/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"/>
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:accessPolicy">
		<div class="aro_box" type="accessPolicy">
			<input type="text" class="input-xlarge" name="value" placeholder="value"
				value="{text()}"/>
		</div>
	</xsl:template>

	<!-- BLANK TEMPLATE -->
	<xsl:template name="blankTemplate">
		<div class="aro_box template" type="name">

			
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSNameType" name="type" placeholder="Type" value=""/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			
			<div class="aro_box_part" type="namePart">
				<div class="control-group">
					<label class="control-label" for="title">Name Part: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" name="value" class="inner_input" value="" placeholder="Value"/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
						</span>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>

			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="namePart">
					<i class="icon-plus icon-white"></i> Add Name Part
				</button>
			</div>

		</div>

		<div class="aro_box_part template" type="namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input" value="" placeholder="Value"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="description">
			<div class="aro_box_display clearfix">
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDescriptionType" name="type" placeholder="Type" value=""/>
				<h1>Description</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<textarea name="value" class=""/>
		</div>

		<div class="aro_box template" type="rights">	
			<div class="aro_box_display clearfix">
				<h1>Rights</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<div class="aro_box_part" type="rightStatement">
				<label>Rights Statement</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:rightStatement/@rightsURI}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:rightStatement/text()}"/>
			</div>
			<div class="aro_box_part" type="licence">
				<label>Licence</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{licence/@rightsURI}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{licence/text()}"/>
			</div>		
			<div class="aro_box_part" type="accessRights">
				<label>Access Rights</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{accessRights/@rightsURI}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{accessRights/text()}"/>
			</div>
		</div>

		<div class="aro_box template" type="subject">
			<div class="aro_box_display clearfix">
				<span class="inputs_group">
					<input type="text" class="input-xlarge inner_input" placeholder="Value" value="" name="value"/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSSubjectType" name="type" placeholder="type" value=""/>
				</span>
				<button class="btn btn-mini btn-danger remove" type="button">
					<i class="icon-remove icon-white"/>
				</button>
			</div>
		</div>



		<div class="aro_box template" type="identifier">
			<div class="aro_box_display clearfix">
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="input-xlarge inner_input" placeholder="Value" value="" name="value"/>
						<input type="text" class="inner_input_type rifcs-type btn" vocab="RIFCSIdentifierType" name="type" placeholder="type" value=""/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="relatedObject">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part">
				<div class="control-group">
					<label class="control-label" for="title">Key: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="key" value="" placeholder="Related Object Key"/>
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value=""/>
					<input type="text" class="inner_input input-large" name="description" placeholder="Description" value=""/>
					<input type="text" class="input-small" name="url" placeholder="URL" value=""/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
				</div>
			</div>

			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="relation"><i class="icon-plus icon-white"/> Add Relation </button>
			</div>
		</div>

		<div class="aro_box_part template" type="relation">
			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value=""/>
					<input type="text" class="inner_input input-large" name="description" placeholder="Description" value=""/>
					<input type="text" class="input-small" name="url" placeholder="URL" value=""/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="relatedInfo">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSRelatedInformationType" name="type" placeholder="Type" value=""/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
		
			<div class="aro_box_part" type="relatedInfo">
				<div class="control-group">
					<label class="control-label" for="title">Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" placeholder="Title" value=""/>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Identifier: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input input-large" name="identifier" placeholder="Identifier" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSIdentifierType" name="identifier_type" placeholder="Identifier Type" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
						</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Notes: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="notes" placeholder="Notes" value="{notes/text()}"/>
					</div>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="location">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1/>
				<div class="control-group">

					<div class="controls">
						<input type="text" class="input-small" name="dateFrom" placeholder="dateFrom" value=""/>
						<input type="text" class="input-small" name="dateTo" placeholder="dateTo" value=""/>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>			
			<div class="aro_subbox" type="address">
				<h1>Address</h1>
				<xsl:apply-templates select="ro:address"/>
				<div class="separate_line"/>
				<div class="btn-group dropup">
					<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"> <i class="icon-envelope icon-white"></i> Add Address</button>
					<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						<span class="caret"/>
					</button>
					<ul class="dropdown-menu">
						<li>
							<a href="javascript:;" class="addNew" type="electronic">Add Electronic
								Address</a>
						</li>
						<li>
							<a href="javascript:;" class="addNew" type="physical">Add Physical
								Address</a>
						</li>
					</ul>
				</div>
			</div>		
			<div class="aro_subbox" type="spatial">
				<h1>Spatial Location</h1>
				<xsl:apply-templates select="ro:spatial"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="spatial">
					<i class="icon-map-marker icon-white"/> Add Spatial Location </button>
			</div>
		</div>

		<div class="aro_box_part template" type="spatial">
			<div class="control-group">
				<label class="control-label" for="title">Spatial: </label>
				<div class="controls">
					<span>Type:						
						<input type="text" class="input-small rifcs-type" vocab="RIFCSSpatialType" name="type" placeholder="Type" value=""/>
						<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					</span>Value:
						<input type="text" class="input-xlarge spatial_value" name="value" placeholder="Value" value=""/>
						<button class="btn triggerMapWidget" type="button"><i class="icon-globe"></i></button>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box_part template" type="electronic">
			<label class="control-label" for="title">Electronic Address: </label>
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSElectronicAddressType" name="type" placeholder="Type" value=""/>
				</span>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
				<xsl:if test="ro:service">
					<button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
					<div class="parts hide">
						<div class="separate_line"/>
						<button class="btn btn-primary addNew" type="arg">
							<i class="icon-plus icon-white"></i> Add Args
						</button>
					</div>
				</xsl:if>
			</div>
		</div>
		<div class="aro_box_part template" type="physical">
			<label class="control-label" for="title">Physical Address: </label>
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSPhysicalAddressType" name="type" placeholder="Type" value=""/>
				</span>
				<button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
				<div class="parts hide" type="addressParts">
					<div class="separate_line"/>
					<button class="btn btn-primary addNew" type="addressPart">
						<i class="icon-plus icon-white"></i> Add Address Part
					</button>
				</div>
			</div>
		</div>

		<div class="aro_box_part template" type="arg">
			<label class="control-label" for="title">Arg: </label>
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgType" name="type" placeholder="Type" value=""/>
				</span>
				<input type="text" class="input-xlarge" name="required"  placeholder="Required" value=""/>
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgUse" name="use"  placeholder="Use" value=""/>
				</span>
				<input type="text" class="input-xlarge" name="value"  placeholder="Value" value=""/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"></i>
				</button>
			</div>
		</div>

		<div class="aro_box_part template" type="addressPart">
			<div class="control-group">
				<span>
					<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSPhysicalAddressPartType" name="type" placeholder="Type" value=""/>
				</span>
				<input type="text" class="input-xlarge" name="value" placeholder="value" value=""/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"/>
				</button>
			</div>

		</div>

		<div class="aro_box template" type="accessPolicy">
			<input type="text" class="input-xlarge" name="value" placeholder="value" value=""/>
		</div>

		<div class="aro_box template" type="fullCitation">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"></i></a><h1>Full Citation</h1>
				<div class="control-group">
					<label class="control-label">Style: </label>
					<div class="controls">
						<input type="text" class="input-small" name="style" placeholder="style" value=""/>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
			<textarea name="value" place-holder="value" class="input-xlarge"></textarea>
		</div>



		<div class="aro_box template" type="citationMetadata">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle"><i class="icon-minus"></i></a><h1>Citation Metadata</h1>
			</div>
			<div class="aro_box_part" type="identifier">
				<div class="control-group">
				<label class="control-label">Identifier:</label>
					<div class="controls">
						<span>
							<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
							<input type="text" class="input-small rifcs-type" vocab="RIFCSIdentifierType" name="type" placeholder="Type" value=""/>
						</span>
						<input type="text" class="input-xlarge" name="value" placeholder="Identifier" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="title">
				<div class="control-group">
				<label class="control-label">Title:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Title" value=""/>
					</div>
				</div>
			</div>


			<div class="aro_box_part" type="edition">
				<div class="control-group">
				<label class="control-label">Edition:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Edition" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="placePublished">
				<div class="control-group">
				<label class="control-label">Place Published:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Place Published" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="publisher">
				<div class="control-group">
				<label class="control-label">Publisher:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Publisher" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="url">
				<div class="control-group">
				<label class="control-label">URL:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="URL" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="context">
				<div class="control-group">
				<label class="control-label">Context:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Context" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="contributor">
				<div class="control-group"><div class="controls">
					<button class="btn btn-primary showParts">Contributors <i class="icon-chevron-right icon-white"></i></button>
					<div class="parts hide">
						<div class="separate_line"/>
						<button class="btn btn-primary addNew" type="contributor">
							<i class="icon-plus icon-white"></i> Add Contributor
						</button>
					</div>
				</div></div>
			</div>

			<div class="aro_box_part" type="date">
				<div class="control-group"><div class="controls">
					<button class="btn btn-primary showParts">Date <i class="icon-chevron-right icon-white"></i></button>
					<div class="parts hide">
						<div class="separate_line"/>
						<button class="btn btn-primary addNew" type="date">
							<i class="icon-plus icon-white"></i> Add Date
						</button>
					</div>
				</div></div>
			</div>

		</div>


		<div class="aro_box template" type="contributor">
			<div class="aro_box_display clearfix">
				Seq: <input type="text" class="input-small" name="seq" placeholder="Seq" value=""/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part" type="namePart">
				<div class="control-group">
					<label class="control-label" for="title">Name Part: </label>
					<div class="controls">
						<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
						<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>

			<div class="separate_line"/>
			<button class="btn btn-primary addNew" type="namePart"><i class="icon-plus icon-white"/> Add NamePart </button>
		
		</div>	
			
		<div class="aro_box template" type="date">
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-xlarge rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value=""/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
		
	
			
		<div class="aro_box_part template" type="citationInfo">
			<div class="control-group">
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="fullCitation">
					<i class="icon-plus icon-white"/> Add Full Citation </button>
				<button class="btn btn-primary addNew" type="citationMetadata">
					<i class="icon-plus icon-white"/> Add Citation Metadata </button>
			</div>
		</div>
		
		<div class="aro_box template" type="existenceDate">
			<div class="aro_box_display clearfix">
				<div class="controls">
					<input type="text" class="input-small" name="startDate_type" placeholder="startDate Type" value=""/>
					<input type="text" class="input-xlarge" name="startDate_value" placeholder="startDate Value" value=""/>
					<input type="text" class="input-small" name="endDate_type" placeholder="endDate Type" value=""/>
					<input type="text" class="input-xlarge" name="endDate_value" placeholder="endDate Value" value=""/>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>
		
		<div class="aro_box template" type="temporal">
			<div class="separate_line"/>	
			<h1>Temporal Coverage</h1>
			<div class="btn-group dropup">
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"> <i class="icon-envelope icon-white"></i> Add Date Value</button>
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="caret"/>
				</button>
				<ul class="dropdown-menu">
					<li>
						<a href="javascript:;" class="addNew" type="coverage_date">Date</a>
					</li>
					<li>
						<a href="javascript:;" class="addNew" type="text">Text</a>
					</li>
				</ul>
			</div>
		</div>
		
		<div class="aro_box template" type="coverage">
			<div class="separate_line"/>	
			<div class="btn-group dropup">
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"> <i class="icon-envelope icon-white"></i> Add Coverage</button>
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="caret"/>
				</button>
				<ul class="dropdown-menu">
					<li>
						<a href="javascript:;" class="addNew" type="temporal">Add Temporal Coverage</a>
					</li>
					<li>
						<a href="javascript:;" class="addNew" type="spatial">Add Spatial Coverage</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="aro_box_part template" type="dates">
			<div class="aro_box" type="dates">
				<div class="aro_box_display clearfix">
					<a href="javascript:;" class="toggle"><i class="icon-minus"/></a>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSDatesType" name="type" placeholder="Date Type" value=""/>
					<h1/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>

				<div class="aro_box_part" type="dates_date">
					<div class="control-group">
						<label class="control-label" for="title">Date: </label>
						<div class="controls">
							<span class="inputs_group">
								<input type="text" name="value" class="inner_input datepicker"  value=""/>
								<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value="dateFrom"/>
							</span>
							<button class="btn btn-mini btn-danger remove">
								<i class="icon-remove icon-white"/>
							</button>
						</div>
					</div>
				</div>
				<div class="aro_box_part" type="dates_date">
					<div class="control-group">
						<label class="control-label" for="title">Date: </label>
						<div class="controls">
							<span class="inputs_group">
								<input type="text" name="value" class="inner_input datepicker" value=""/>
								<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value="dateTo"/>
							</span>
							<button class="btn btn-mini btn-danger remove">
								<i class="icon-remove icon-white"/>
							</button>
						</div>
					</div>
				</div>
				<div class="separate_line"/>
				<div class="controls">
					<button class="btn btn-primary addNew" type="dates_date">
						<i class="icon-plus icon-white"></i> Add new Date
					</button>
				</div>
			</div>

		</div>

		<div class="aro_box_part template" type="dates_date">
			<div class="aro_box_part" type="dates_date">
				<div class="control-group">
					<label class="control-label" for="title">Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" name="value" class="inner_input datepicker" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value=""/>
						</span>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="aro_box_part template" type="date">
			<label class="control-label" for="title">Date: </label>
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value=""/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
		
		<div class="aro_box_part template" type="coverage_date">
			<label class="control-label" >Date: </label>
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<span>
				<button class="btn triggerTypeAhead" type="button"><span class="caret"></span></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDateFormat" name="type" placeholder="Date Format" value=""/>
			</span>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value=""/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
		
		<div class="aro_box_part template" type="text">
			<label class="control-label" for="title">Text: </label>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value=""/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
		

	</xsl:template>

</xsl:stylesheet>