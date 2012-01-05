<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';
// Page processing
// -----------------------------------------------------------------------------
	

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<script type="text/javascript">vcSetImagePath('<?php print gORCA_IMAGE_ROOT ?>_controls/_vocab_control/')</script>
<form id="registry_object_add" action="registry_object_add.php" method="post">
<table id="outer-table" summary="Add Registry Object">	
	<tbody>
		<tr>
			<td id="content-cell">
				<div id="aro-tabs-container">
					<table id="aro-tabs-table" summary="Search Tabs layout" cellspacing="0" cellpadding="0">
						<tr>
							<td id="aro-tab-1" class="aro-tab-active" onclick="updateAROTab('aro-tab-1')">Mandatory Information</td>
							<td id="aro-tab-2" class="aro-tab" onclick="updateAROTab('aro-tab-2')">Names</td>
							<td id="aro-tab-3" class="aro-tab" onclick="updateAROTab('aro-tab-3')">Identifiers</td>
							<td id="aro-tab-4" class="aro-tab" onclick="updateAROTab('aro-tab-4')">Location</td>
							<td id="aro-tab-5" class="aro-tab" onclick="updateAROTab('aro-tab-5')">Related Objects</td>
							<td id="aro-tab-6" class="aro-tab" onclick="updateAROTab('aro-tab-6')">Subjects</td>
							<td id="aro-tab-7" class="aro-tab" onclick="updateAROTab('aro-tab-7')">Descriptions</td>
							<td id="aro-tab-8" class="aro-tab" onclick="updateAROTab('aro-tab-8')">Related Info</td>
							<td id="aro-tab-9" class="aro-tab" onclick="updateAROTab('aro-tab-9')">Object Preview</td>
						</tr>
					</table>
				</div>
				<div id="aro-content">
					<table class="formTable" id="aro-content-table" summary="add registry content" cellspacing="0" cellpadding="0">
						<tr id="aro-tab-1-content">
						<td>
							<table id="aro-tab-1-table">
								<tr>
									<td>
										<label class="elementName">Mandatory Information:</label>					
									</td>
								</tr>
								<tr>
									<td>
									<ul class="guideNotes">
									<li>
									physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity
									</li>
									<li>
									physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity
									</li>
									<li>
									physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity
									</li>
									</ul>						
									</td>
								</tr>
								<tr>
									<td class="green-td">
										<table width="750px">
											<tr>
												<td class="green-td"><label class="fieldName">* Data Source:</label></td>
												<td class="green-td">
													<select name="data_source_key" id="data_source_key">
														  <option value=""></option>
														  <option value="aims.gov.au">	Australian Institute of Marine Science</option>
														  <option value="www.marine.csiro.au">CSIRO Marine and Atmospheric Research Laboratories Information Network</option>
														  <option value="ausdata">Data Australia</option>
														  <option value="mest.ivec.org">iVEC MEST - Western Australian Marine Data and Projects</option>
														  <option value="monikaTest">monikaTest</option>
													</select>
												</td>
											</tr>
											<tr>
												<td class="green-td"><label class="fieldName">* Group:</label></td>
												<td class="green-td"><input type="text" name="registry_object_group" id="registry_object_group" size="40" maxlength="255" value="" /></td>
											</tr>
											<tr>
												<td class="green-td"><label class="fieldName">* Key:</label></td>
												<td class="green-td"><input type="text" name="key" id="key" size="40" maxlength="255" value="" /></td>
											</tr>
											<tr>
												<td class="green-td"><label class="fieldName">* Class:</label></td>
												<td class="green-td">
													<select name="class" id="class">
														<option value=""></option>
														<option value="collection">collection</option>
														<option value="service">service</option>
														<option value="party">party</option>
														<option value="activity">activity</option>
													</select>
												</td>
											</tr>
											<tr>
												<td class="green-td"><label class="fieldName">* Type:</label></td>
												<td class="green-td"><input type="text" name="key" id="key" size="40" maxlength="255" value="" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						</tr>
						<tr id="aro-tab-2-content">
						<td>aro-tab-2-content</td>
						</tr>
						<tr id="aro-tab-3-content">
						<td>aro-tab-3-content</td>
						</tr>
						<tr id="aro-tab-4-content">
							<td>
								<table id="aro-tab-4-table">
										<tr>
											<td>
												<label class="elementName">Location:</label>					
											</td>
										</tr>
										<tr>
											<td>
												<ul class="guideNotes">
												<li>physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity</li>
												</ul>
												<ul class="guideNotes">
												<li>physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity</li>
												</ul>
												<ul class="guideNotes">
												<li>physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity</li>
												</ul>						
											</td>
										</tr>
										<tr>
										<td class="green-td">
										<table width="750px">
										<tr>
												<td class="green-td"><label class="fieldName">Type:</label></td>
												<td><input name="location_type_0" id="location_type_0" size="40" maxlength="255" value="" type="text"> <img class="vcIcon" id="location_type_0_RIFCSLocationType_vcIcon" alt="" title="Suggested vocabulary" src="https://devl.ands.org.au/workareas/leo/cosi/orca/_images/_controls/_vocab_control/vc_icon_inactive.gif" onclick="vcDisplayVocabControl('location_type_0','location_type_0_RIFCSLocationType')">
									<div id="location_type_0_RIFCSLocationType" style="display: none; position: absolute; z-index: 100;" class="vocabControl">
									<div class="vcInnerContainer">
									<div class="vcCloseBar" onmousedown="startMove(event, getObject('location_type_0_RIFCSLocationType'))"><img src="https://devl.ands.org.au/workareas/leo/cosi/orca/_images/_controls/_vocab_control/vc_close.gif" alt="" title="Close" class="vcClose" onclick="vcCloseVocabControl('location_type_0_RIFCSLocationType')"></div>
									<div class="vcScrollPane">
									<div class="vcContent">
									<b>RIF-CS Location Type</b><br>
									<div class="vcTermGroup">
									  <div title="coverage" class="vcTerm" onclick="vcUpdateInputFieldValue('location_type_0','location_type_0_RIFCSLocationType', 'coverage')">coverage</div>
									</div>
									</td>
											</tr>
											<tr>
												<td class="green-td"><label class="fieldName">Date From:</label></td>
												<td><input size="20" maxlength="20" name="location_date_from_0" id="location_date_from_0" value="" type="text">
									<script type="text/javascript">dctGetDateTimeControl('location_date_from_0', 'YYYY-MM-DDThh:mm:00Z')</script><span class="fieldName">YYYY-MM-DDThh:mm:00Z</span></td>
									
											</tr>
											<tr>
												<td class="green-td"><label class="fieldName">Date To:</label></td>
												<td><input size="20" maxlength="20" name="location_date_to_0" id="location_date_to_0" value="" type="text">
									<script type="text/javascript">dctGetDateTimeControl('location_date_to_0', 'YYYY-MM-DDThh:mm:00Z')</script><span class="fieldName">YYYY-MM-DDThh:mm:00Z</span></td>
											</tr>
											<tr>
												<td colspan="2" class="green-td"><label class="elementName">Addresses:</label></td>									
											</tr>
											<tr>								
												<td colspan="2" class="orange-td">
									<div style="margin: 2px 0px 0px 6px;"><input class="buttonSmall" value="Add an Address" title="Add an Address" type="button"/></div>
									         </td>
											</tr>

											<tr>
												<td colspan="2" class="green-td"><label class="elementName">Spatial:</td>									
											</tr>
											<tr>								
												<td colspan="2" class="orange-td">
									<div style="margin: 2px 0px 0px 6px;"><input class="buttonSmall" value="Add Spatial Location" title="Add Spatial Location" type="button"/></div>
									         </td>
											</tr>									
											<tr>
												<td><input class="buttonSmall" value="Add a Location" title="Add a Location" type="button"/></td>
												<td>
												</td>
											</tr>
											</table>																						
										</td>
										</tr>
								</table>	
							</td>
						</tr>
						<tr id="aro-tab-5-content">
						<td>							

							<table id="aro-tab-5-table">
								<tr>
									<td>
										<label class="elementName">Related Objects:</label>					
									</td>
								</tr>
								<tr>
									<td>
									<ul class="guideNotes">
									<li>
									physical or electronic interfaces (for example, an RSS feeds) to work done by a party or to a collection or activity
									</li>
									</ul>						
									</td>
								</tr>
								<tr>
									<td class="green-td">
										<table width="750px">
											<tr>
												<td class="green-td"><label class="fieldName">* Key:</label></td>
												<td class="green-td"><input type="text" name="registry_object_group" id="registry_object_group" size="40" maxlength="255" value="" />
											</tr>
											<tr>
												<td colspan="2" class="green-td"><label class="elementName">Relations:</label></td>									
											</tr>
											<tr>
												<td class="orange-td"><label class="fieldName">* Type:</label></td>
												<td class="orange-td"><input type="text" name="registry_object_group" id="registry_object_group" size="40" maxlength="255" value="" /></td>
											</tr>
											<tr>
												<td class="orange-td"><label class="fieldName">* Description:</label></td>
												<td class="orange-td"><input type="text" name="key" id="key" size="40" maxlength="255" value="" /></td>
											</tr>
											<tr>
												<td class="orange-td"><label class="fieldName">* url:</label></td>
												<td class="orange-td"><input type="text" name="key" id="key" size="40" maxlength="255" value="" />&nbsp;&nbsp;<input class="buttonSmall" value="Add Relation" title="Add Relation" type="button"/></td>
											</tr>
											<tr>
												<td class="green-td" colspan="2"><input class="buttonSmall" type="button" name="verb" value="Add a Related Object" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						</tr>
						<tr id="aro-tab-6-content">
						<td>aro-tab-6-content</td>
						</tr>
						<tr id="aro-tab-7-content">
						<td>aro-tab-7-content</td>
						</tr>
						<tr id="aro-tab-8-content">
						<td>aro-tab-8-content</td>
						</tr>
						<tr id="aro-tab-9-content">
						<td>aro-tab-9-content</td>
						</tr>
					</table>
					<div id="aro-form-buttons">
					<table class="formTable">
						<tbody>
							<tr>
								<td width="400px">&nbsp;</td>
								<td><input type="button" name="verb" value="Back" />&nbsp;&nbsp;<input type="button" name="verb" value="Next" />&nbsp;&nbsp;<input type="button" name="verb" value="Cancel" />&nbsp;&nbsp;<input type="button" disabled="disbled" name="verb" value="Finish" />&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2" class="formNotes">
									Fields marked * are mandatory.<br />
									Any existing registryObject with this Key will be overwritten.<br />
									Data other than Data Source	will be validated againt the RIF-CS schema.<br />
								</td>
							</tr>
						</tbody>
					</table>	
					</div>
				</div>		
			</td>
			<td id="help-cell">
				<div id="overview">
				<h3>H.E.L.P.</h3>
				<ul>
					<li><i>Publish My Data</i> allows Australian researchers and research
					organisations to publicise the existence of research collections via
					the internet. <b>Collections must be accessible online</b>.</li>
				
					<li>ANDS prefers to harvest collection description information
					automatically, at the institutional level, as this allows for the
					responsibility of ongoing maintenance of collection description
					information to rest with the institution. However, ANDS recognises that
					this is not always possible. This self-service option is intended for
					use by researchers at organisations where there is no formal data
					archiving service and where ANDS has no distributed services. Please
					contact <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a> for more information on setting up an
					institutional harvest.</li>
				
					<li>This self-service option allows individuals to manually enter
					collection description information and obtain a persistent identifier
					for the collection. This information will be stored in the ANDS
					Collections Registry and will be discoverable through Research Data
					Australia. <b>The individual who enters the collection description
					information is responsible for any required future updates to this
					information</b>.</li>
					
					
					<li>Collection descriptions entered through the <i>Publish My Data</i> online
					service are not immediately accepted into the <i>ANDS Collection Registry</i>
					or <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. Descriptions are held in a PENDING state
					until an ANDS administrator approves the collection description. This
					is to ensure that obscene or malicious material is not published in
					<i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. This approval process will generally take less
					than 5 working days.</li>
				
					<li><i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> is a
					set of web pages describing data collections produced by or relevant to
					Australian researchers. It is designed to promote the visibility of
					research data collections in search discovery engines such as Google
					and Yahoo. <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> includes pages on:
					<ol>
						<li>Collections</li>
						<li>Activities (undertakings related to the creation, update, or
						maintenance of a collection, such as a project)</li>
						<li>Services (mechanisms for gaining some kind of access to or
						information about a collection, such as an RSS feed)</li>
						<li>Parties (persons or organisations that have some relationship to a
						collection, service, activity, or party).</li>
					</ol>
					</li>
				</ul>
				
				<p>When you create a collection description record with the Publish My
				Data online service a party record will be automatically created for you
				in <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i>. You can view and edit this information using
				the View Publisher details option from the left hand menu. If you wish
				to have your party record removed from <i><a class="external" href="http://services.ands.org.au/">Research Data Australia</a></i> you will
				need to contact <a class="external" href="mailto:services@ands.org.au">services@ands.org.au</a></p>
				</div>
			</td>
		</tr>		
	</tbody>
</table>
<script type="text/javascript">updateAROTab('aro-tab-1');</script>
</form>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
