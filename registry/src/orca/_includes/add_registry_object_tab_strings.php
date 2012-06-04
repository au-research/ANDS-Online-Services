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

/* Define the tab structure for Registry Object Add tabs */
$_strings = array();

/*
 * Tab elements: 
 * $_strings['*_mandatoryInformation']
 * $_strings['*_name']
 * $_strings['*_identifier']
 * $_strings['*_location']
 * $_strings['*_relatedObject'] 
 * $_strings['*_subject']
 * $_strings['*_description']
 * $_strings['*_coverage']
 * $_strings['*_citationInfo']
 * $_strings['*_relatedInfo']
 * $_strings['service_accessPolicy'] // Service ONLY
 * $_strings['*_preview']
 * 
 * $_strings['*']   // default content
 *  
 */

// Default content string
$_strings['*'] = "Element content not available";
$eAPP_ROOT = eAPP_ROOT;

/*********************************************
 * COLLECTION TAB CONTENT/STRUCTURE
 *********************************************/


// 
// Mandatory Information
//
$_strings['*_mandatoryInformation'] = <<<HTMLEND
				<table class="inner-table">
					<tr>
						<td class="tab_title">	
							<br/>				
							Record Administration							
						</td>
						<td width="62px" rowspan="3" valign="top" style="border-left:none; border-bottom:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>
					</tr>
					<tr>
						<td style="border-right:none;">					
							<div name="errors_mandatoryInformation"></div>						
						</td>
					</tr>
					<tr>
						<td style="border-bottom:none; border-right:none;" id="mandatoryInformation_metadata_guidance_container">					
																							
						</td>
					</tr>
					<tr>
								
						<td colspan="2" id="mandatoryInformation_container">
						<div class='overlay'></div>
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">
					getRemoteElement("#mandatoryInformation_metadata_guidance_container", 
						"mandatoryInformation_metadata_guidance");
				//	showLoading($("#field_container"));
				//	getRemoteElement("#field_container", 
				//		"mandatory_information_table");
				</script>
				
HTMLEND;


// 
// Names
//

$_strings['*_name'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title" style="border-right:none;">	
							<br/>				
							Names									
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>
					</tr>
					<tr>
						<td style="border-bottom:none; border-right:none;" id="name_metadata_guidance_container">					
																							
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_name" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td colspan="2" id="field_container">
							<div id="object.name_container">&nbsp;</div>
		
							<input type="button" class="buttonSmall" name="" value="Add new Name" 							
													onClick="getElement('name', [], 'object.', null, getNextSeq('name'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">								
					getRemoteElement("#name_metadata_guidance_container", 
						"name_metadata_guidance");
				</script>
				
HTMLEND;


// 
// Identifiers
//

$_strings['*_identifier'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title" style="border-right:none;">					
							<br/>
							Identifiers									
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>
					</tr>
					<tr>
						<td style="border:none;" id="identifier_metadata_guidance_container">					
																							
						</td>
					</tr>
					<tr>
						<td style="border:none;" style="border-right:none;">					
							<div name="errors_identifier" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.identifier_container">&nbsp;</div>
							
							<input type="button" class="buttonSmall" name="" value="Add new Identifier" 							
													onClick="getElement('identifier', [], 'object.', null, getNextSeq('identifier'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">								
					getRemoteElement("#identifier_metadata_guidance_container", 
						"identifier_metadata_guidance");
				</script>
HTMLEND;


//
// Locations
//

$_strings['*_location'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title" style="border-none;">					
							<br/>
							Locations									
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>
					</tr>
					<tr>
						<td style="border:none;" id="location_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_location" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.location_container">&nbsp;</div>
							<input type="button" class="buttonSmall" name="" value="Add new Location" onClick="getElement('location', [], 'object.', null, getNextSeq('location'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">
					getRemoteElement("#location_metadata_guidance_container", 
						"location_metadata_guidance");
				</script>
				
HTMLEND;


// 
// Related Objects
//

$_strings['*_relatedObject'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Related Objects						
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="relatedObject_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_relatedObject" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.relatedObject_container">&nbsp;</div>
							<input type="button" class="buttonSmall" name="" value="Add new Related Object" onClick="getElement('relatedObject', [], 'object.', null, getNextSeq('relatedObject'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">
					getRemoteElement("#relatedObject_metadata_guidance_container", 
						"relatedObject_metadata_guidance");
				</script>
				
				
				
HTMLEND;


//
// Descriptions
//

$_strings['*_description'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Descriptions									
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="description_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_description" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.description_container">&nbsp;</div>
							
							
							
							<input type="button" class="buttonSmall" name="" value="Add new Description" 							
													onClick="getElement('description', [], 'object.', null, getNextSeq('description'));" />
						</td>
					</tr>
					<tr>
						<td class="tab_title" colspan="2">					
							<br/>
							Rights									
						</td>	
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.rights_container">&nbsp;</div>
							
							
							
							<input type="button" class="buttonSmall" name="" value="Add new Rights" 							
													onClick="getElement('rights', [], 'object.', null, getNextSeq('rights'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">								
					getRemoteElement("#description_metadata_guidance_container", 
						"description_metadata_guidance");
				</script>
				
HTMLEND;


// 
// Subjects
//

$_strings['*_subject'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Subjects								
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="subject_metadata_guidance_container">																					
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_subject" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.subject_container">&nbsp;</div>
							
							<input type="button" class="buttonSmall" name="" value="Add new Subject" 							
													onClick="getElement('subject', [], 'object.', null, getNextSeq('subject'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">								
					getRemoteElement("#subject_metadata_guidance_container", 
						"subject_metadata_guidance");
				</script>
				
HTMLEND;


// 
// Coverages
//

$_strings['*_coverage'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Coverage								
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="coverage_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_coverage" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.coverage_container">&nbsp;</div>
							<input type="button" class="buttonSmall" name="" value="Add new Coverage" onClick="getElement('coverage', [], 'object.', null, getNextSeq('coverage'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">
					getRemoteElement("#coverage_metadata_guidance_container", 
						"coverage_metadata_guidance");
				</script>
				
HTMLEND;


//
// Citations
//

$_strings['*_citationInfo'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Citation Info								
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="citationInfo_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_citationInfo" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.citationInfo_container"></div>
							<input type="button" class="buttonSmall" name="" value="Add new Citation Info" onClick="getElement('citationInfo', [], 'object.', null, getNextSeq('citationInfo'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">
					getRemoteElement("#citationInfo_metadata_guidance_container", 
						"citationInfo_metadata_guidance");
				</script>
				
HTMLEND;


// 
// Related Info
//

$_strings['*_relatedInfo'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Related Info								
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td id="relatedInfo_metadata_guidance_container" style="border:none;">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_relatedInfo" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.relatedInfo_container"></div>
							<input type="button" class="buttonSmall" name="" value="Add new Related Info" onClick="getElement('relatedInfo', [], 'object.', null, getNextSeq('relatedInfo'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">
					getRemoteElement("#relatedInfo_metadata_guidance_container", 
						"relatedInfo_metadata_guidance");
				</script>
				
HTMLEND;

//
// Existence Dates (Party, Activity, Service)
//

$_strings['*_existenceDates'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Existence Dates	<br/><br/>						
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="existenceDates_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_existenceDates" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.existenceDates_container">&nbsp;</div>
							
							
							
							<input type="button" class="buttonSmall" name="" value="Add new Existence Dates" 							
													onClick="getElement('existenceDates', [], 'object.', null, getNextSeq('existenceDates'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">								
					getRemoteElement("#existenceDates_metadata_guidance_container", 
						"existenceDates_metadata_guidance");
				</script>
				
HTMLEND;


//
// Access Policy (Service ONLY)
//

$_strings['service_accessPolicy'] = <<<HTMLEND

				<table class="inner-table">
					<tr>
						<td class="tab_title">					
							<br/>
							Access Policy								
						</td>
						<td width="62px" rowspan="3" valign="top" style="border:none;">
							<a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank" id="cpgHelpButton" name="helpButton" style="float:right;"></a>
						</td>		
					</tr>
					<tr>
						<td style="border:none;" id="accessPolicy_metadata_guidance_container">																				
						</td>
					</tr>
					<tr>
						<td style="border:none;">					
							<div name="errors_accessPolicy" class="error_notification" style="display:none;"></div>				
						</td>
					</tr>
					<tr>
						<td id="field_container" colspan="2">
							<div id="object.accessPolicy_container">&nbsp;</div>
							
							
							
							<input type="button" class="buttonSmall" name="" value="Add new Access Policy" 							
													onClick="getElement('accessPolicy', [], 'object.', null, getNextSeq('accessPolicy'));" />
						</td>
					</tr>
				</table>
				
				<script type="text/javascript">								
					getRemoteElement("#accessPolicy_metadata_guidance_container", 
						"accessPolicy_metadata_guidance");
				</script>
				
HTMLEND;


//
// Preview Page
//
$orcaImgRoot = eAPP_ROOT ."orca/_images/";
$cosiImgRoot = eIMAGE_ROOT;
$_strings['*_preview'] = <<<HTMLEND

			<table class="inner-table">
					<tr>
						<td>
							<div id="save_notification" class="save_notification"></div>
														
						</td>
						<td>
							<div id="rda_preview_container"></div>	
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="qa_level_notification"></div>						
						</td>
					</tr>
					<tr>
						<td colspan="2">	
							<div id="qa_preview"></div>						
						</td>
					</tr>
					<tr>
						<td colspan="2">	
							<div id="errors_preview"></div>						
						</td>
					</tr>

					<tr>
						<td colspan="2" id="field_container">
							<div id="rmd_saving" class="loadingPlaceholder">
								<img src="{$cosiImgRoot}_icons/ajax_loading.gif" alt="Loading Image" style="padding-left:35px;" />
								<br/><br/>Saving &amp; Validating...
								<br/><br/><br/>
							</div>

							<div id="rmd_preview" style="display:inline-block; width:100%;">
							
							</div>
							</form>
						</td>
					</tr>
			</table>

HTMLEND;


