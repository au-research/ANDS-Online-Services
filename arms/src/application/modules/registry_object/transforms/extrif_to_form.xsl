<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	exclude-result-prefixes="extRif">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
	<xsl:template match="registryObject">
		<xsl:variable name="ro_class">
			<xsl:apply-templates select="collection | activity | party  | service" mode="getClass"/>
		</xsl:variable>
		<div class="">
			<!-- tabs -->
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#admin" data-toggle="tab">Record Administration</a>
				</li>
				<li>
					<a href="#names" data-toggle="tab">Names</a>
				</li>
				<li>
					<a href="#descriptions_rights" data-toggle="tab">Descriptions/Rights</a>
				</li>
				<li>
					<a href="#identifiers" data-toggle="tab">Identifiers</a>
				</li>
				<li>
					<a href="#locations" data-toggle="tab">Locations</a>
				</li>
				<li>
					<a href="#coverages" data-toggle="tab">Coverage</a>
				</li>
				<li>
					<a href="#relatedObjects" data-toggle="tab">Related Objects</a>
				</li>
				<li>
					<a href="#subjects" data-toggle="tab">Subjects</a>
				</li>
				<li>
					<a href="#relatedinfos" data-toggle="tab">Related Info</a>
				</li>
				<xsl:if test="$ro_class = 'service'">
					<li>
						<a href="#accesspolicies" data-toggle="tab">Accesspolicy</a>
					</li>
				</xsl:if>
				<xsl:if test="$ro_class = 'collection'">
					<li>
						<a href="#citationinfos" data-toggle="tab">Citation Info</a>
					</li>
				</xsl:if>
				<xsl:if test="$ro_class != 'collection'">
					<li>
						<a href="#existencedates" data-toggle="tab">Existence Dates</a>
					</li>
				</xsl:if>

			</ul>

			<!-- form-->
			<form class="form-horizontal" id="edit-form">
				<!-- All the tab contents -->
				<div class="tab-content">
					<xsl:call-template name="recordAdminTab"/>
					<xsl:call-template name="namesTab"/>
					<xsl:call-template name="descriptionRightsTab"/>
					<xsl:call-template name="identifiersTab"/>
					<xsl:call-template name="locationsTab"/>
					<xsl:call-template name="coverageTab"/>
					<xsl:call-template name="relatedObjectsTab"/>
					<xsl:call-template name="subjectsTab"/>
					<xsl:call-template name="relatedinfosTab"/>
					<xsl:if test="$ro_class = 'service'">
						<xsl:call-template name="accesspolicyTab"/>
					</xsl:if>
					<xsl:if test="$ro_class = 'collection'">
						<xsl:call-template name="citationinfoTab"/>
					</xsl:if>
					<xsl:if test="$ro_class != 'collection'">
						<xsl:call-template name="ExistenceDatesTab"/>
					</xsl:if>
					<div class="modal hide" id="myModal">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">×</button>
							<h3>Alert</h3>
						</div>
						<div class="modal-body"/>
						<div class="modal-footer"> </div>
					</div>
				</div>


			</form>
			<div class="aro_toolbar">
				<div class="message"> Auto-saved: 5 seconds ago... </div>
				<div class="aro_controls">


					<div class="btn-toolbar">
						<button class="btn btn-info" id="load_xml">
							<i class="icon-download-alt icon-white"/> Load XML </button>
						<button class="btn btn-info" id="master_export_xml">
							<i class="icon-download-alt icon-white"/> Export XML </button>

						<div class="btn-group dropup">
							<button class="btn btn-primary">
								<i class="icon-download-alt icon-white"/> Save &amp;
								Validate</button>
							<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
								<span class="caret"/>
							</button>
							<ul class="dropdown-menu">
								<li>
									<a href="javascript:;">Save &amp; Publish</a>
								</li>
								<li>
									<a href="javascript:;">Save &amp; Exit</a>
								</li>
								<li>
									<a href="javascript:;">Save &amp; Validate</a>
								</li>
								<li>
									<a href="javascript:;">Quick Save</a>
								</li>
							</ul>
						</div>

						<div class="btn-group">
							<a class="btn">
								<i class="icon-chevron-left"/>
							</a>
							<a class="btn">
								<i class="icon-chevron-right"/>
							</a>
						</div>

					</div>
				</div>
				<div class="clearfix"/>
			</div>
			<xsl:call-template name="blankTemplate"/>
		</div>
		<input type="hidden" class="hide" id="ro_class" value="{$ro_class}"/>
		<input type="hidden" class="hide" id="originatingSource" value="{$ro_class}"/>
	</xsl:template>

	<xsl:template match="collection | activity | party  | service" mode="getClass">
		<xsl:value-of select="name()"/>
	</xsl:template>


	<xsl:template name="recordAdminTab">
		<!-- Record Admin-->
		<div id="admin" class="tab-pane active">
			<fieldset>
				<legend>Record Administration</legend>
				<xsl:variable name="ro_type">
					<xsl:apply-templates
						select="collection/@type | activity/@type | party/@type  | service/@type"/>
				</xsl:variable>
				<xsl:variable name="dataSourceID">
					<xsl:value-of select="extRif:extendedMetadata/extRif:dataSourceID"/>
				</xsl:variable>
				<xsl:variable name="dateModified">
					<xsl:apply-templates
						select="collection/@dateModified | activity/@dateModified | party/@dateModified  | service/@dateModified"/>
				</xsl:variable>

				<div class="control-group">
					<label class="control-label" for="title">Type</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" class="input-large" name="title" value="{$ro_type}"/>
						</div>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Data Source</label>
					<div class="controls">
						<select id="data_sources_select"/>
						<input type="text" id="data_source_id_value" class="input-small hide"
							name="title" value="{$dataSourceID}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
				
				
				<div class="control-group">
					<label class="control-label" for="title">Originating Source</label>
					<div class="controls">
						<input type="text" id="originatingSource" name="value" placeholder="Value" value="{originatingSource/text()}"/>
						<input type="text" id="originatingSource" name="type" placeholder="Type"  value="{originatingSource/@type}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
				

				<div class="control-group">
					<label class="control-label" for="title">Group</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" class="input-large" name="title" value="{@group}"/>
						</div>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group warning">
					<label class="control-label" for="title">Key</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" value="{key}"/>
						<button class="btn btn" id="generate_random_key">
							<i class="icon-refresh"/> Generate Random Key </button>
						<p class="help-inline">
							<small>Key must be unique and is case sensitive</small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Date Modified</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-large datepicker" name="title"
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

				<xsl:if test="collection">
					<div class="control-group">
						<label class="control-label" for="title">Date Accessioned</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="title"
									value="{collection/@dateAccessioned}"/>
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
		<div id="names" class="tab-pane">
			<fieldset>
				<legend>Names</legend>

				<xsl:apply-templates
					select="collection/name | activity/name | party/name  | service/name"/>
				<div class="separate_line"/>

				<button class="btn btn-primary addNew" type="name">
					<i class="icon-plus icon-white"/> Add Name </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template match="collection/@type | activity/@type | party/@type  | service/@type">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="collection/@dateModified | activity/@dateModified | party/@dateModified  | service/@dateModified">
		<xsl:value-of select="."/>
	</xsl:template>
	

	<xsl:template match="collection/name | activity/name | party/name  | service/name">
		<div class="aro_box" type="name">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-plus"/>
				</a>
				<h1/>
				<div class="control-group">
					<label class="control-label" for="title">Type: </label>
					<div class="controls">
						<input type="text" class="input-small" name="type" placeholder="Type"
							value="{@type}"/>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>
			
			<xsl:apply-templates select="namePart"/>
			<div class="separate_line"/>
			<div class="controls hide">
				<button class="btn btn-primary addNew" type="namePart">
					<i class="icon-plus icon-white"></i> Add Name Part
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="namePart">
		<div class="aro_box_part hide" type="namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type"
						value="{@type}"/>
					<input type="text" class="input-xlarge" name="value" value="{text()}"/>
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
		<div id="descriptions_rights" class="tab-pane">
			<fieldset>
				<legend>Descriptions / Rights</legend>
				<xsl:apply-templates
					select="collection/description | activity/description | party/description  | service/description"/>
				<xsl:apply-templates
					select="collection/rights | activity/rights | party/rights  | service/rights"/>
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
		<div id="accesspolicies" class="tab-pane">
			<fieldset>
				<legend>Access Policy</legend>
				<xsl:apply-templates select="service/accessPolicy"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="accesspolicy">
					<i class="icon-plus icon-white"/> Add Access Policy </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="citationinfoTab">
		<div id="citationinfos" class="tab-pane">
			<fieldset>
				<legend>Citation Info</legend>
				<xsl:apply-templates select="collection/citationInfo"/>
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

	<xsl:template match="collection/description | activity/description | party/description  | service/description">
		<div class="aro_box" type="description">
			<h1>Description</h1>
			<p>
				<input type="text" class="input-xlarge" name="type" placeholder="Type"
					value="{@type}"/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"/>
				</button>
			</p>
			<p>
				<textarea name="value" class="editor">
					<xsl:apply-templates select="text()"/>
				</textarea>
			</p>

			<p class="help-inline">
				<small/>
			</p>
		</div>
	</xsl:template>

	<xsl:template match="collection/rights | activity/rights | party/rights  | service/rights">
		<div class="aro_box" type="rights">
			<h1>Rights</h1>
			<p>
				<div class="aro_box_part" type="rightStatement">
					<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{rightStatement/@rightsURI}"/>
					<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{rightStatement/text()}"/>
				</div>
				<div class="aro_box_part" type="licence">
					<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{licence/@rightsURI}"/>
					<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{licence/text()}"/>
				</div>			
				<div class="aro_box_part" type="accessRights">
					<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{accessRights/@rightsURI}"/>
					<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{accessRights/text()}"/>
				</div>
			</p>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
			<p class="help-inline">
				<small/>
			</p>
		</div>
	</xsl:template>

	<xsl:template name="subjectsTab">
		<div id="subjects" class="tab-pane">
			<fieldset>
				<legend>Subjects</legend>
				<xsl:apply-templates
					select="collection/subject | activity/subject | party/subject  | service/subject"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="subject">
					<i class="icon-plus icon-white"/> Add Subject </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="identifiersTab">
		<div id="identifiers" class="tab-pane">
			<fieldset>
				<legend>Identifiers</legend>
				<xsl:apply-templates
					select="collection/identifier | activity/identifier | party/identifier  | service/identifier"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="identifier">
					<i class="icon-plus icon-white"/> Add Identifier </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="relatedObjectsTab">
		<div id="relatedObjects" class="tab-pane">
			<fieldset>
				<legend>Related Objects</legend>
				<xsl:apply-templates
					select="collection/relatedObject | activity/relatedObject | party/relatedObject  | service/relatedObject"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="relatedobject">
					<i class="icon-plus icon-white"/> Add Related Object </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="relatedinfosTab">
		<div id="relatedinfos" class="tab-pane">
			<fieldset>
				<legend>Related Infos</legend>
				<xsl:apply-templates
					select="collection/relatedInfo | activity/relatedInfo | party/relatedInfo | service/relatedInfo"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="relatedinfo">
					<i class="icon-plus icon-white"/> Add related Info </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="locationsTab">
		<div id="locations" class="tab-pane">
			<fieldset>
				<legend>Locations</legend>
				<xsl:apply-templates
					select="collection/location | activity/location | party/location  | service/location"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="location">
					<i class="icon-plus icon-white"/> Add Location </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>
	
	<xsl:template name="coverageTab">
		<div id="coverages" class="tab-pane">
			<fieldset>
				<legend>Coverage</legend>
				<xsl:apply-templates
					select="collection/coverage | activity/coverage | party/coverage  | service/coverage"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="coverage">
					<i class="icon-plus icon-white"/> Add Coverage </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>
	
	<xsl:template name="ExistenceDatesTab">
		<div id="existencedates" class="tab-pane">
			<fieldset>
				<legend>Existence dates</legend>
				<xsl:apply-templates select="activity/existenceDates | party/existenceDates  | service/existenceDates"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="existenceDate">
					<i class="icon-plus icon-white"/> Add Existence Date </button>
				<button class="btn export_xml btn-info"> Export XML fragment </button>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template match="activity/existenceDates | party/existenceDates  | service/existenceDates">
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


	<xsl:template match="collection/relatedInfo | activity/relatedInfo | party/relatedInfo | service/relatedInfo">
		<div class="aro_box" type="relatedInfo">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1/>
				<div class="control-group">
					<label class="control-label" for="title">Type: </label>
					<div class="controls">
						<input type="text" class="input-small" name="type" placeholder="Type"
							value="{@type}"/>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>
			<div class="aro_box_part" type="relatedInfo">
				<div class="control-group">
					<label class="control-label" for="title">Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" placeholder="Title"
							value="{title/text()}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Identifier: </label>
					<div class="controls">
						<input type="text" class="input-small" name="identifier_type"
							placeholder="Identifier Type" value="{identifier/@type}"/>
						<input type="text" class="input-xlarge" name="identifier"
							placeholder="Identifier" value="{identifier/text()}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Notes: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="notes" placeholder="Notes"
							value="{notes/text()}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="collection/subject  | activity/subject  | party/subject   | service/subject">
		<div class="aro_box" type="subject">
			<div class="aro_box_display clearfix">
				<div class="controls"> Type: <input type="text" class="input-small" name="type"
						placeholder="Type" value="{@type}"/> Value: <input type="text"
						class="input-xlarge" name="value" value="{text()}"/>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline"><small/></p>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="collection/identifier  | activity/identifier  | party/identifier   | service/identifier">
		<div class="aro_box" type="identifier">
			<div class="aro_box_display clearfix">
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type"
						value="{@type}"/>
					<input type="text" class="input-xlarge" name="value" value="{text()}"/>
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

	<xsl:template match="collection/relatedObject | activity/relatedObject | party/relatedObject  | service/relatedObject">
		<div class="aro_box" type="relatedobject">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1>Related Object Title goes here <small>relation</small></h1>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>

			<div class="aro_box_part">
				<div class="control-group">
					<label class="control-label" for="title">Key: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="key" value="{key}"/>
					</div>
				</div>
			</div>
			<xsl:apply-templates select="relation"/>
			<div class="separate_line"/>
			<button class="btn btn-primary addNew" type="relation">
				<i class="icon-plus icon-white"/> Add Relation </button>
		</div>
	</xsl:template>

	<xsl:template match="collection/location | activity/location | party/location  | service/location">
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
				<xsl:apply-templates select="address"/>
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
				<xsl:apply-templates select="spatial"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="spatial">
					<i class="icon-map-marker icon-white"/> Add Spatial Location </button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="collection/coverage | activity/coverage | party/coverage  | service/coverage">
		<div class="aro_box" type="coverage">
			<xsl:apply-templates select="temporal"/>
			<xsl:apply-templates select="spatial"/>
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


	<xsl:template match="temporal">
		<div class="aro_box_part" type="temporal">
			<xsl:apply-templates select="date" mode="coverage"/>
			<xsl:apply-templates select="text"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>

	<xsl:template match="date" mode="coverage">
		<div class="aro_box_part" type="coverage_date">
			<label class="control-label" for="title">Date: </label>
			<input type="text" class="input-xlarge" name="type" placeholder="Date Type" value="{@type}"/>
			<input type="text" class="input-xlarge" name="dateFormat" placeholder="Date Format" value="{@dateFormat}"/>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>
	
	<xsl:template match="date">
		<div class="aro_box_part" type="date">
			<label class="control-label" for="title">Date: </label>
			<input type="text" class="input-xlarge" name="type" placeholder="Date Type" value="{@type}"/>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>
	
	<xsl:template match="text">
		<div class="aro_box_part" type="text">
			<label class="control-label" for="title">Text: </label>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
	</xsl:template>

	<xsl:template match="relation">
		<div class="aro_box_part" type="relation">
			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type"
						value="{@type}"/>
					<input type="text" class="input-xlarge" name="description"
						placeholder="Description" value="{description}"/>
					<input type="text" class="input-xlarge" name="url" placeholder="Url"
						value="{url}"/>
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


	<xsl:template match="spatial">
		<div class="aro_box_part" type="spatial">
			<div class="control-group">
				<label class="control-label" for="title">Spatial: </label>
				<div class="controls">
					<input type="text" class="input-small" name="type" value="{@type}"/>
					<input type="text" class="input-xlarge" name="value" value="{text()}"/>
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

	<xsl:template match="address">
		<xsl:apply-templates select="electronic | physical"/>
		<div class="separate_line"/>
	</xsl:template>

	<xsl:template match="electronic">
		<div class="aro_box_part" type="electronic">
			<label class="control-label" for="title">Electronic Address: </label>
			<div class="control-group">
				<input type="text" class="input-small" name="type" placeholder="Type"
					value="{@type}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value"
					value="{value}"/>
				<xsl:if test="ancestor::service">
					<button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
					<div class="parts hide">
						<xsl:apply-templates select="arg"/>
						<div class="separate_line"/>
						<button class="btn btn-primary addNew" type="arg">
							<i class="icon-plus icon-white"></i> Add Args
						</button>
					</div>
				</xsl:if>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="physical">
		<div class="aro_box_part" type="physical">
			<label class="control-label" for="title">Physical Address: </label>
			<div class="control-group">
				<input type="text" class="input-small" name="type" placeholder="Type" value="{@type}"/>
				<button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
				<div class="parts hide" type="addressParts">
					<xsl:apply-templates select="addressPart"/>
					<div class="separate_line"/>
					<button class="btn btn-primary addNew" type="addressPart">
						<i class="icon-plus icon-white"></i> Add Address Part
					</button>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="arg">
		<div class="aro_box_part" type="arg">
			<label class="control-label" for="title">Arg: </label>
			<div class="control-group">
				<input type="text" class="input-small" name="type" placeholder="Type" value="{@type}"/>
				<input type="text" class="input-xlarge" name="required"  placeholder="Required" value="{@required}"/>
				<input type="text" class="input-xlarge" name="use"  placeholder="Use" value="{@use}"/>
				<input type="text" class="input-xlarge" name="value"  placeholder="Value" value="{text()}"/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"></i>
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="addressPart">
		<div class="aro_box_part" type="addressPart">
			<div class="control-group">
				<input type="text" class="input-small" name="type" placeholder="Type"
					value="{@type}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="value"
					value="{text()}"/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"/>
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="accessPolicy">
		<div class="aro_box" type="accessPolicy">
			<input type="text" class="input-xlarge" name="value" placeholder="value"
				value="{text()}"/>
		</div>
	</xsl:template>

	<!-- BLANK TEMPLATE -->
	<xsl:template name="blankTemplate">

		<div class="aro_box template" type="name">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1/>
				<div class="control-group">
					<label class="control-label" for="title">Type: </label>
					<div class="controls">
						<input type="text" class="input-small" name="type" placeholder="Type"
							value="{@type}"/>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>
			<div class="aro_box_part" type="namePart">
				<div class="control-group">
					<label class="control-label" for="title">Name Part: </label>
					<div class="controls">
						<input type="text" class="input-small" name="type" placeholder="Type"
							value=""/>
						<input type="text" class="input-xlarge" name="value" placeholder="Value"
							value=""/>
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
			<button class="btn btn-primary addNew" type="namePart">
				<i class="icon-plus icon-white"/> Add Name Part </button>
		</div>

		<div class="aro_box_part template" type="namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
					<input type="text" class="input-xlarge" name="value" placeholder="Value"
						value=""/>
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
			<h1>Description</h1>
			<p>
				<input type="text" class="input-xlarge" name="type" placeholder="Type" value=""/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"/>
				</button>
			</p>
			<p>
				<textarea name="value" class=""/>
			</p>

			<p class="help-inline">
				<small/>
			</p>
		</div>

		<div class="aro_box template" type="rights">	
				<h1>Rights</h1>
				<p>
					<div class="aro_box_part" type="rightsStatement">
						<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value=""/>
						<input type="text" class="input-xlarge" name="value" placeholder="value" value=""/>
					</div>
					<div class="aro_box_part" type="licence">
						<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value=""/>
						<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
					</div>			
					<div class="aro_box_part" type="accessRights">
						<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value=""/>
						<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
					</div>
				</p>
				<p class="help-inline">
					<small/>
				</p>

			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>

		<div class="aro_box template" type="subject">
			<div class="aro_box_display  clearfix">
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
					<input type="text" class="input-xlarge" name="value" value=""/>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="identifier">
			<div class="aro_box_display  clearfix">
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
					<input type="text" class="input-xlarge" name="value" value=""/>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="relatedobject">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1>Related Object Title goes here <small>relation</small></h1>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>

			<div class="aro_box_part">
				<div class="control-group">
					<label class="control-label" for="title">Key: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="key" value=""/>
					</div>
				</div>
			</div>


			<div class="separate_line"/>
			<button class="btn btn-primary addNew" type="relation">
				<i class="icon-plus icon-white"/> Add Relation </button>
		</div>

		<div class="aro_box_part template" type="relation">
			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
					<input type="text" class="input-xlarge" name="description"
						placeholder="Description" value=""/>
					<input type="text" class="input-xlarge" name="url" placeholder="Url" value=""/>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="relatedInfo">
			<div class="aro_box_display clearfix">
				<a href="javascript:;" class="toggle">
					<i class="icon-minus"/>
				</a>
				<h1/>
				<div class="control-group">
					<label class="control-label" for="title">Type: </label>
					<div class="controls">
						<input type="text" class="input-small" name="type" placeholder="Type"
							value=""/>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>
			</div>
			<div class="aro_box_part" type="relatedInfo">
				<div class="control-group">
					<label class="control-label" for="title">Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="type" placeholder="Title"
							value=""/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Identifier: </label>
					<div class="controls">
						<input type="text" class="input-small" name="identifier_type"
							placeholder="Identifier Type" value=""/>
						<input type="text" class="input-xlarge" name="identifier"
							placeholder="Identifier" value=""/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="title">Notes: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="notes" placeholder="Notes"
							value=""/>
						<p class="help-inline">
							<small/>
						</p>
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
				<xsl:apply-templates select="address"/>
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
				<xsl:apply-templates select="spatial"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="spatial">
					<i class="icon-map-marker icon-white"/> Add Spatial Location </button>
			</div>
		</div>

		<div class="aro_box_part template" type="spatial">
			<div class="control-group">
				<label class="control-label" for="title">Spatial: </label>
				<div class="controls">
					<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
					<input type="text" class="input-xlarge" name="value" placeholder="Value"
						value=""/>
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
				<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
				<xsl:if test="service">
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
				<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
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
				<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
				<input type="text" class="input-xlarge" name="required"  placeholder="Required" value=""/>
				<input type="text" class="input-xlarge" name="use"  placeholder="Use" value=""/>
				<input type="text" class="input-xlarge" name="value"  placeholder="Value" value=""/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"></i>
				</button>
			</div>
		</div>

		<div class="aro_box_part template" type="addressPart">
			<div class="control-group">
				<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
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
						<input type="text" class="input-small" name="type" placeholder="Type" value=""/>
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
			<input type="text" class="input-xlarge" name="type" placeholder="Date Type" value=""/>
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
		
		<div class="aro_box_part template" type="date">
			<label class="control-label" for="title">Date: </label>
			<input type="text" class="input-xlarge" name="type" placeholder="Date Type" value=""/>
			<input type="text" class="input-xlarge" name="value" placeholder="Date Value" value=""/>
			<button class="btn btn-mini btn-danger remove">
				<i class="icon-remove icon-white"/>
			</button>
		</div>
		
		<div class="aro_box_part template" type="coverage_date">
			<label class="control-label" >Date: </label>
			<input type="text" class="input-xlarge" name="type" placeholder="Date Type" value=""/>
			<input type="text" class="input-xlarge" name="dateFormat" placeholder="Date Format" value=""/>
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
