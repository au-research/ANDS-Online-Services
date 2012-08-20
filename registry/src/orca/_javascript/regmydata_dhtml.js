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

//
// Constants/global variables

var helpEnabled = true;
var metaGuideEnabled = true;
var activeHelpSection = "";
var activeTab;
var userMode = 'edit';
var mapLoaded = false;
var isUniqueKey = true;
var hasFlashed = false;
var keyValue = '';
var contexts = {};
var mandatoryFields = {"object_mandatoryInformation_type":"","object_mandatoryInformation_dataSource":"","object_mandatoryInformation_group":"","object_mandatoryInformation_key":""};
var objectTypes = {'collection':'','service':'','party':'','activity':''};
var canContinue = true;
var requestsRemaining = 0;
var pageStatus = 'PRELOADING';  // PRELOADING -> LOADING_INTERFACE -> LOADING_ELTS -> LOADING_RELOBJS -> VALIDATING -> READY
var readyToAdvance = true;
var qaRequired = false;
var elementCache = { 'element': {}, 'tab': {}};
var qualityLevel = 0;
var STATUS_COOKIE_NAME = 'ORCA_REGISTRY_MANAGE_STATUS';
var STATUS_COOKIE_TTL_DAYS = 365*5;

// init namespace
$.rmd = {};

// =============================================================================
// Initialise the form and actions once the DOM is ready

$(document).ready(function() {
	
	// Disable backspace 
	function checkKey(e){
	     if (e.keyCode == 8 && !$(document.activeElement).is(":input"))
	     {
	    	 if(confirm("Your browser has tried to go back to the previous page.\n** ALL UNSAVED CHANGES WILL BE LOST **\n\nDo you still want to leave this page?"))
	    	 {
	    		 history.back();
	    	 }
	    	 else
	         {
	    		 return true;
	         }
	     }
	}
	
	if ($.browser.mozilla) {
	    $(document).keypress (checkKey);
	} else {
		$(document).keydown (checkKey);
	}

	
	
	
	if ($.browser.msie && parseInt($.browser.version, 10) < 7)
	{
		alert("*** WARNING: This software only supports Internet Explorer 7 and greater. Please update your browser version.");
	}
	
	advanceLoadingStatus();
	

	$('.relatedObjectKey').live('blur', function(){
		var key = $(this).val();
		if(key!=''){
			var target = $(this).parents().nextAll().find('.ro_preview').first();
			getRelatedObjectPreview(key, target);
		}
	});
	

	// =============================================================================
	// TAB NAVIGATION functionality
	// ----
	$("ul.tabs li").click(function() {

		
		activeTab = $(this).find("a").attr("href").substr($(this).find("a").attr("href").indexOf("#")); 

		if ($('#dctInnerContainer').css('visibility') == 'visible')
		{
			dctCloseDateTimeControl();
		}

		activateTab(activeTab);
		
		return false;
	});

	$("#mmr_msg_continue").live("click", function()
	{
		window.location.href = $('#baseURL').val() + 'manage/my_records.php?data_source=' + encodeURIComponent($('#object_mandatoryInformation_dataSource').val());
	});
	
	
	$("#draft_continue_where").live("click", function()
	{
		var save_action = $("input[name=save_action]:checked").val();
		
		if (save_action == 'draft')
		{
			window.location.href = $("#baseURL").val() + "manage/my_records.php?data_source=" + encodeURIComponent($('#object_mandatoryInformation_dataSource').val());
		}
		else if (save_action == 'flag')
		{
			$.get(
					$("#baseURL").val() + "manage/process_registry_object.php?task=flag_draft&data_source=" + encodeURIComponent($('#object_mandatoryInformation_dataSource').val()) + "&key=" + encodeURIComponent($('#object_mandatoryInformation_key').val()) + "&flag=true",
					function(){
						window.location.href = $("#baseURL").val() + "manage/my_records.php?data_source=" + encodeURIComponent($('#object_mandatoryInformation_dataSource').val()) ;
					}					
			);
		}
		else if(save_action == 'submit_for_review')
		{
			var targetKeys = new Array();
			var isPreApproval = true;
			var dataSourceKey = $('#object_mandatoryInformation_dataSource').val();
			targetKeys.push($('#object_mandatoryInformation_key').val());
			
			$.post(
					$("#baseURL").val() + "manage/process_registry_object.php?task=manage_my_records&action=SUBMIT_FOR_ASSESSMENT",
					{ 	
						'keys[]' : targetKeys, 
						'preapproval' : isPreApproval,
						'dataSourceKey' : dataSourceKey
					},
					function(data) {
						$.unblockUI(); 
						if (data['responsecode'] == 0)
						{
							// Error occured
							alert("Error Occured: Access Denied.");
						}
						else if (data['responsecode'] == "MT008")
						{
							$('#mmr_datasource_alert_msg').html('Your records have been sent to ANDS for assessment and approval. You should contact your ANDS Client Liaison Officer to notify them of the records.');
							$.blockUI({ message: $('#mmr_datasource_alert') }); 
						}
						else if (data['responsecode'] == "MT014")
						{
							$('#mmr_datasource_alert_msg').html('An ANDS Quality Assessor has been notified of your submitted record(s)');
							$.blockUI({ message: $('#mmr_datasource_alert') }); 
						}
						else
						{
							if (data['alert'])
							{
								$('#mmr_datasource_alert_msg').html(data['alert']);
								$.blockUI({ message: $('#mmr_datasource_alert') }); 
							}
						}
						window.location.href = $("#baseURL").val() + "manage/my_records.php?data_source=" + encodeURIComponent($('#object_mandatoryInformation_dataSource').val());
					},
					'json'
			);
		}

		
	});

});

function setStatusSpan(status)
{
	$('#status_span').html(status);
	$('#status_bar').show();
}

function setButtonBar(buttons)
{
	$('#button_bar').html(buttons);
}

function activateTab (activeTab) 
{

	// Deactivate the currently active tab
	if(canContinue && isUniqueKey)
	{
		$("ul.tabs li").removeClass("active"); 
		$("*").autocomplete( "close" );
		$(activeTab + "_tab").addClass("active");
	
		loadTabUI(activeTab);
	
		if (activeTab == "#preview")
		{
			saveAndPreview();
		} else {
			updateTabOrder(activeTab);
			$('input[id^=object_'+activeTab.substring(1)+']').first().focus();
			$("#rmd_saving").show();
			$("#rmd_preview").hide();
		}
	
		// Load help for new tab
		//getHelpText(activeTab.substring(1)); 
	
		updateButtonStatus();
		getHelpText();
	}
	return true;
}

function updateTabOrder(activeTab)
{
	var count = 1;
	$('input[id^=object_'+activeTab.substring(1)+'],select[id^=object_'+activeTab.substring(1)+'],textarea[id^=object_'+activeTab.substring(1)+']').each(function(index, element){$(element).attr("tabIndex",count+1); count++;});	
}

// =============================================================================
// OTHER FUNCTIONS
// -----------------------------------------------------------------------------

function traverse(context, fragment)
{
//	debugger;
	var callBacks = [];
	var leftOver = null;
	
	//if (!fragment || fragment == null) { return; }
	
	// Treat the root node differently as this
	// provides the mandatoryInformation which
	// is handled directly
	if(context==null && (fragment && fragment.hasChildNodes()))
	{
		
		var registryObject = fragment.getElementsByTagName("registryObject")[0];
		if (!registryObject) { alert ("Could not parse XML - Abort"); return false; }
		// Check we have a valid registry object
	
		if (registryObject.nodeName != "registryObject") { return false; }
		
		
		callBacks = addAllToCallback(registryObject.attributes, "object.mandatoryInformation", callBacks, true);	
		
		for (var i=0;i<registryObject.childNodes.length;i++)
		{
			
			if (registryObject.childNodes[i].nodeType != 3)
			{

				if (registryObject.childNodes[i].nodeName in objectTypes)
				{
					// this is the <collection/activity/service/party> node
					// leftOver = service type = registryObject.children[i].nodeName
					leftOver = registryObject.childNodes[i];
					$("#elementCategory").val(registryObject.childNodes[i].nodeName);
					callBacks = addAllToCallback(registryObject.childNodes[i].attributes, "object.mandatoryInformation", callBacks, true);
					
				} else {
					
					callBacks = addAllToCallback(registryObject.childNodes[i].attributes, "object.mandatoryInformation", callBacks, true);				
					callBacks = addAllToCallback(registryObject.childNodes[i].childNodes, "object.mandatoryInformation", callBacks, true);
					
				}


			} 

		}
		
		getElement("mandatoryInformation", callBacks, null, leftOver);
		
	} else if (fragment && fragment != null)  {
		
		for (var i=0;i<fragment.childNodes.length;i++)
		{
			var leftOver = null;                                   /// comment node
			if ( (fragment.childNodes[i].nodeType != 3 && fragment.childNodes[i].nodeType != 8)  || (fragment.childNodes[i].nodeType == 3 && $.trim(fragment.childNodes[i].nodeValue) != ""))
			{
				
				var seq = getNextSeq(context + fragment.childNodes[i].nodeName); 
				callBacks = addAllToCallback(fragment.childNodes[i].attributes, context + fragment.childNodes[i].nodeName +  "[" + seq + "]", callBacks);
				
				if (fragment.childNodes[i].childNodes.length == 1 && fragment.childNodes[i].childNodes[0].nodeType == 3 && $.trim(fragment.childNodes[i].childNodes[0].nodeValue) != "")
				{
					callBacks = addAllToCallback(fragment.childNodes[i].childNodes, context + fragment.childNodes[i].nodeName +  "[" + seq + "]", callBacks);
				} 
				else
				{
					leftOver = fragment.childNodes[i];
				}
				/*else {
					leftOver = null;
					callBacks = addAllToCallback(fragment.childNodes[i].childNodes, context + fragment.childNodes[i].nodeName +  "[" + seq + "]", callBacks);
				}*/
								
				getElement(fragment.childNodes[i].nodeName, callBacks, context, leftOver, seq);
				
			} 	
		
		}
		
	} 
}


function getNextSeq(context)
{
	context = context.replace(/object\./,"").replace(/\[|\]\./g,"_");
	if (isNaN(parseInt($(escJ("#formMetadata.seq." + context)).val()))) {
		$('#formMetadata').append('<input id="formMetadata.count.'+ context +'" value="0" />');
		$('#formMetadata').append('<input id="formMetadata.seq.'+ context +'" value="0" />');
	}
	
	$(escJ("#formMetadata.seq." + context)).val(parseInt($(escJ("#formMetadata.seq." + context)).val()) + 1);
	incCount(context);
	return $(escJ("#formMetadata.seq." + context)).val();
}


function incCount(context)
{
	context = context.replace(/object\./,"").replace(/\[|\]\./g,"_");
	$(escJ("#formMetadata.count." + context)).val(parseInt($(escJ("#formMetadata.count." + context)).val()) + 1);
	return $(escJ("#formMetadata.count." + context)).val();
}

function getCount(context)
{
	context = context.replace(/object\./,"").replace(/\[|\]\./g,"_");
	return parseInt($(escJ("#formMetadata.count." + context)).val());
}

function decCount(context)
{
	context = context.replace(/object\./,"").replace(/\[|\]\./g,"_");
	$(escJ("#formMetadata.count." + context)).val(parseInt($(escJ("#formMetadata.count." + context)).val()) - 1);
	return $(escJ("#formMetadata.count." + context)).val();
}

function removeButtonIfLast(name)
{
	if(name.indexOf('relation_1_remove') > 0)
	{
		$("[name='"+ name +"']").remove();
	}
}


function escJ(string)
{
	return string.replace(/\./g,"\\.");
}

function getElement(elementType, callback, context, fragment, seq)
{
	requestsRemaining += 1;

	// context = null
	// context = name[1]
	
	if (!fragment || !fragment.hasChildNodes()) 
	{
		fragment = null;
	}
	
	var remoteSequence = "";
	var metadataSeqPrefix = "formMetadata.seq.";
	var metadataCountPrefix = "formMetadata.count.";
	var metadataContext = "";
	
	if (context != null && context != "object.") {
		
		// Escape the context for jQuery
		metadataContext = context.replace("object.","").replace(/\[|\]\./g,"_"); 
		
		// Extract the full sequence code for getRemoteElement() from context
		remoteSequence = context.replace(/[a-zA-Z]+/g, "").replace(/\[/g, "").replace(/\]\./g,":").replace(/\]|\[|\./g,"");
	} 

	if (seq == null) {
		remoteSequence += $(escJ('#' + metadataSeqPrefix + metadataContext + elementType)).val();
	}
	

	var sURL = $("#elementSourceURL").attr("value");
	sURL += "?context=add_registry_object_element&";
	sURL += "tag=" + $("#elementCategory").attr("value") + "_";
	sURL += (context != null ? context.replace(/\[[0-9]+\]\./g, "_").replace("object.","") : "") + elementType + "&";
	
	if (remoteSequence == "undefined") {  
		remoteSequence = 1;
		sURL += "seq_num=1&";
	} else if (seq != null) {
		remoteSequence += (seq == "undefined" ? "1" : seq);
		sURL += "seq_num=" + remoteSequence + "&";
	} else {
		sURL += "seq_num=" + remoteSequence + "&";
	}
	
	var tcount = $(escJ('#' + metadataSeqPrefix + metadataContext + elementType)).val();
	var safeCBC = ((context == null ? "object." : context) + elementType + (tcount != undefined ? "[" + tcount + "]" : "")).replace(/\[/g,"_").replace(/\]/g,"").replace(/\./g,"_");
	
	if (fragment == null) { sURL += "fr=false"; } 
	else 
	{ 
		sURL += "fr=true";  
	}
	
	var cleanContext = (context == null ? "" : context.replace(/\[[0-9]+\]\./g, "_").replace("object.",""));
	
	var eltBase = "";
	//console.log(elementCache);
	//console.log(cleanContext + elementType);
	if (($("#elementCategory").attr("value") + "_" + cleanContext + elementType) in elementCache.element)
	{
		//console.log('Loaded from cache: ELT[' + cleanContext + elementType + ']');
		eltBase = elementCache.element[($("#elementCategory").attr("value") + "_" + cleanContext + elementType)];
	}
	else if (("*_" + cleanContext + elementType) in elementCache.element)
	{
		//console.log('Loaded from cache [wc]: ELT[' + cleanContext + elementType + ']');
		eltBase = elementCache.element[("*_" + cleanContext + elementType)];
	}
	
	if (eltBase != "")
	{
		var seqCnt = 0;
		var seqNum = remoteSequence.split(':');
		for (s in seqNum)
		{
			seqCnt++;
			eltBase = eltBase.replace(new RegExp("%%SEQNUM" + seqCnt + "%%", "g"), seqNum[s]);
		}

		if (fragment != null)
		{
			eltBase = eltBase.replace(/%%HASFRAGMENT%%/g, "true");
		}
		else
		{
			eltBase = eltBase.replace(/%%HASFRAGMENT%%/g, "false");
		}
		
		if (context != null)
	    {
	    	$(escJ("#" + context.replace(/\[|\]\./g,"_") + elementType + "_container")).append(eltBase);
	    } else {
	    	$(escJ("#" + elementType + "_container")).append(eltBase);
	    }
	    
		for (var i=0; i<callback.length; i++)
		{
			$(escJ(callback[i].id.replace(/\[/g,"_").replace(/\]/g,"").replace(/\./g,"_"))).val(callback[i].value).change();
		}
		
		if ($.browser.msie && parseInt($.browser.version, 10) < 8) 
		{
			$('label.mandatory').each( 
					function(index) { 
						if($(this).html().indexOf('*') == -1) 
						{ 
							$(this).html('<span style="font-style: normal;font-family: Georgia;color: blue;font-weight:bold;font-size:16px;">*</span> ' + $(this).html());
						}
					}
			);
		}
		
		traverse((context == null ? "object." : context + elementType + (tcount != undefined ? "[" + tcount + "]" : "") + "."), fragment);
		
		requestsRemaining -= 1;
		advanceLoadingStatus();
	}
	else
	{
		eval('contexts[\''+safeCBC+'\']={};');
		eval('contexts[\''+safeCBC+'\'][\'unsafeCBC\'] = ((context == null ? "object." : context) + elementType + (tcount != undefined ? "[" + tcount + "]" : ""));');
		eval('contexts[\''+safeCBC+'\'][\'fragment\'] = fragment;');
		eval('contexts[\''+safeCBC+'\'][\'elementType\'] = elementType;');
		eval('contexts[\''+safeCBC+'\'][\'context\'] = context;');
		eval('contexts[\''+safeCBC+'\'][\'tcount\'] = tcount;');
		eval('contexts[\''+safeCBC+'\'][\'callback\'] = callback;');
		callback = [];
		
		
		sURL += "&key=" + $.urlParam('key');
		sURL += "&data_source=" +encodeURIComponent($.urlParam('data_source'));
		sURL += "&cbc=" + safeCBC;
		
		
		$.ajaxSetup({
			dataType: "text",
			cache: false	
		});
		
		
	
		$.get(sURL, function(data) {
				
				eval('context = contexts[\''+data.cbc+'\'][\'context\'];');
				eval('elementType = contexts[\''+data.cbc+'\'][\'elementType\'];');
				eval('fragment = contexts[\''+data.cbc+'\'][\'fragment\'];');
				eval('callback = contexts[\''+data.cbc+'\'][\'callback\'];');
				eval('tcount = contexts[\''+data.cbc+'\'][\'tcount\'];');
				
			    if (context != null)
			    {
			    	$(escJ("#" + context.replace(/\[|\]\./g,"_") + elementType + "_container")).append(data.rawHTML);
			    } else {
			    	$(escJ("#" + elementType + "_container")).append(data.rawHTML);
			    }
			    
				for (var i=0; i<callback.length; i++)
				{
					$(escJ(callback[i].id.replace(/\[/g,"_").replace(/\]/g,"").replace(/\./g,"_"))).val(callback[i].value).change();
				}
				
				if ($.browser.msie && parseInt($.browser.version, 10) < 8) 
				{
					$('label.mandatory').each( 
							function(index) { 
								if($(this).html().indexOf('*') == -1) 
								{ 
									$(this).html('<span style="font-style: normal;font-family: Georgia;color: blue;font-weight:bold;font-size:16px;">*</span> ' + $(this).html());
								}
							}
					);
				}
				
				traverse((context == null ? "object." : context + elementType + (tcount != undefined ? "[" + tcount + "]" : "") + "."), fragment);
				
				eval('contexts[\''+data.cbc+'\'] = null;');
				
				requestsRemaining -= 1;
				advanceLoadingStatus();
			}, "json");
	}
}

function addAllToCallback (nodeArray, prefix, callback, useParent) 
{
	if (useParent == null) { useParent = false; }
	if (!nodeArray) { return callback; }
	
	var callbackArray = callback;
	var vid ="";
	for (var i=0;i<nodeArray.length;i++)
	{
		if (nodeArray[i].parentNode && nodeArray[i].parentNode.nodeName == "originatingSource") { continue; }
		
		vid ="";
		if (nodeArray[i].nodeName == "#text") {
			if (useParent) { 
				vid = '#' + prefix + "." + nodeArray[i].parentNode.nodeName.replace(/xml\:/,""); 
			} else {
				vid = '#' + prefix + ".value";
			}
		} 
		else 
		{
			if (nodeArray[i].nodeName != "field_id" && nodeArray[i].nodeName != "tab_id")
			{
				vid = '#' + prefix + "." + nodeArray[i].nodeName.replace(/xml\:/,"");
			}
		}
		
		callbackArray.push( {  id: vid, //nodeArray[i].parentNode.nodeName
							value: nodeArray[i].nodeValue } ); 	
	}
	
	return callbackArray;
}

function rmd_showMap(object) {
	$('#container_' + object).css("display","block"); 
	$("#" + object + "_value").attr("readonly","readonly");
	mctSetNoDelayMapControl(object+"_value");
}

function depluralize(string) {
	if (string.substr(-1) == "s") {
		return string.substr(0,string.length-1);
	}
	return string;
}

function underscoreToCamelCase(string) {
	var s = string;
	var o = "";
	while (s.indexOf("_") > -1) {
		o += s.substr(0, s.indexOf("_"));
		o += s.substr(s.indexOf("_")+1,1).toUpperCase();
		s = s.substr(s.indexOf("_")+2);
	}
	o += s;
	return o;
}

// Initialise the status cookie, setting it to default and toggle
// the help section and metadata guidance notes on/off accordingly
function RMD_initStatusCookie()
{
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{
		// Status Cookie format:
		// bool||bool||bool
		//   ^     ^     ^
		// help    |     |
		//       meta    |
		//             unused
		
		setCookie(STATUS_COOKIE_NAME, "true||true||false", STATUS_COOKIE_TTL_DAYS);
		RMD_initStatusCookie();
	} else {
		
		var currentHelpState = currentState.split("||")[0];
		var currentMetaState = currentState.split("||")[1];
		
		// temp disable all
		helpEnabled = false;
		toggleHelp();
		
		if (currentHelpState == "true") {
			//helpEnabled = false;
			//toggleHelp();
			//getHelpText(null);
		} else {
			//helpEnabled = true;
			//toggleHelp();
		}
		
		metaGuideEnabled = false;
		if (currentMetaState == "true") {
			metaGuideEnabled = true;
		}
		
		//alert("State:" + metaGuideEnabled);
		//alert("Cookie:" + currentMetaState );

	}		

}

function setStatusCookie(field, value) {
	
	// Check cookie exists and get its current state
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{	
		setCookie(STATUS_COOKIE_NAME, "true||true||false", STATUS_COOKIE_TTL_DAYS);
		RMD_initStatusCookie();
		
	} 
		
	// Update the appropriate value
	curVals = currentState.split("||");
		
	if (field == "help") {
		curVals[0] = value;
	} 
	else if (field == "meta") 
	{
		curVals[1] = value;
	}
		
	// Reset the cookie
	setCookie(	STATUS_COOKIE_NAME, 
				curVals.join("||"), 
				STATUS_COOKIE_TTL_DAYS);

	
}

// Enable/disable the help section and record this decision in a cookie
function toggleHelp()
{
	// temp
	$("#content-cell").css("width", "95%");
	$("#help-cell").css("width", "5%");
	$("#helpText").css("display","none");
	$("[name='helpButton']").css("display","block");
	helpEnabled = true;
	setStatusCookie("help", "true");
	return true;
	
		if (!helpEnabled) {
			helpEnabled = true;
			setStatusCookie("help", "true");
			$("ul.tabs li").addClass("formNotes"); 
			$("#content-cell").css("width", "80%");
			$("#help-cell").css("width", "20%");
			$("[name='helpButton']").css("display","none");
			$("#helpText").css("display","block");
		} else {
			helpEnabled = false;
			setStatusCookie("help", "false");
			$("ul.tabs li").removeClass("formNotes"); 
			$("#content-cell").css("width", "95%");
			$("#help-cell").css("width", "5%");
			$("#helpText").css("display","none");
			$("[name='helpButton']").css("display","block");
		}
}

//Enable/disable the help section and record this decision in a cookie
function toggleMetaGuide()
{	
	if (!metaGuideEnabled) {
		metaGuideEnabled = true;
		setStatusCookie("meta", "true");
	} else {
		metaGuideEnabled = false;
		setStatusCookie("meta", "false");
	}
	showMetaGuide();
}

function showMetaGuide()
{
	for (var tab in tabs) {
		if (tab == null) { tab = activeTab; }
		if(document.getElementById("guideNotesPrompt_" + tab.substring(1))) {
			
			var promptString = document.getElementById("guideNotesPrompt_" + tab.substring(1)).innerHTML; 
			
			if (promptString.indexOf("+ Show") != -1) {
				promptString = promptString.replace("+ Show", "- Hide");
				document.getElementById("guideNotes_" + tab.substring(1)).style.display = "block";
			} else {
				promptString = promptString.replace("- Hide", "+ Show");
				document.getElementById("guideNotes_" + tab.substring(1)).style.display = "none";	
			}
			
			document.getElementById("guideNotesPrompt_" + tab.substring(1)).innerHTML = promptString;
			
		}
	}			
}

function loadTabUI (tabname) {
	$(".dynamic_panel").hide();
	$("#panel_content_"+tabname.substring(1)).show();	
	if (tabs[tabname] && tabs[tabname].cpg) {
		$("[name='helpButton']").attr("href",tabs[tabname].cpg);
	} else {
		$("[name='helpButton']").attr("href","#");
	}
	

}

// Fetch the tab data/structure from remote source
function getTab(tabname) 
{
	if (($("#elementCategory").attr("value") + "_" + tabname.substring(1)) in elementCache.tab)
	{
		//console.log('Loaded from cache: TAB[' + tabname.substring(1) + ']');
		$("#panel_content_"+tabname.substring(1)).html(elementCache.tab[$("#elementCategory").attr("value") + "_" + tabname.substring(1)]);
		advanceLoadingStatus();
	}
	else if ("*_" + tabname.substring(1)  in elementCache.tab)
	{
		//console.log('Loaded from cache [wc]: TAB[' + tabname.substring(1) + ']');
		$("#panel_content_"+tabname.substring(1)).html(elementCache.tab["*_" + tabname.substring(1)]);
		advanceLoadingStatus();
	}
	else
	{
		var sURL = $("#elementSourceURL").attr("value");
		sURL += "?context=add_registry_object_tab&";
		sURL += "tag=" + $("#elementCategory").attr("value") + "_";
		sURL += tabname.substring(1);
		requestsRemaining += 1;
		
		$.get(sURL, function(data) {
			  $("#panel_content_"+tabname.substring(1)).html(data);
			  requestsRemaining -= 1;
			  		  
			  advanceLoadingStatus();
			}, "text");
	}
}

function advanceLoadingStatus () {
	
	//*********
	// Stage 1 - Preloading -> Interface (tabs)
	if (pageStatus == 'PRELOADING' && requestsRemaining == 0 && readyToAdvance == true)
	{
		// Don't display the page until loading completes.
		$("#rmd_interface").hide();	$("#rmd_loading").show();
		showLoading("Loading interface...");
		
		// Build an element cache to prevent multiple retrievals of elements/tabs
		requestsRemaining += 2;
		
		// tabs
		var sURL = $("#elementSourceURL").attr("value");
		sURL += "?cache_set";
		sURL += "&tag=" + $("#elementCategory").attr("value") + "_";
		sURL += "&context=add_registry_object_tab&";
	
		$.get(sURL, function(data) {
			elementCache['tab'] = {};
			for (var elt in data)
			{
				elementCache['tab'][elt] = data[elt];
			}
			requestsRemaining -= 1;
			
			advanceLoadingStatus();
			}, 
			"json"
		);
		
		// elements
		var sURL = $("#elementSourceURL").attr("value");
		sURL += "?cache_set";
		sURL += "&tag=" + $("#elementCategory").attr("value") + "_";
		sURL += "&context=add_registry_object_element&";
		
		$.get(sURL, function(data) {
			elementCache['element'] = {};
			for (var elt in data)
			{
				elementCache['element'][elt] = data[elt];
			}
			requestsRemaining -= 1;
			
			advanceLoadingStatus();
			}, 
			"json"
		);
		
		
		// Parse the status cookie and set visibility
		// of help and metadata guidance notes
		RMD_initStatusCookie();
		
		readyToAdvance = false;
		
		var tab_text = "";
		for (var t in tabs) {
			tab_text += '<li id="' + t.substr(1) + '_tab"><a href="' + t + '">' + tabs[t].name + '</a></li>';
			$('<div class="dynamic_panel" id="panel_content_'+t.substr(1)+'"></div>').appendTo("#panel_container");
		}
		$("#tabList").html(tab_text);
		
		// Mandatory information tab is always the default tab on page load
		activeTab = "#mandatoryInformation";
		
		// Unless it is specified
		if (window.location.hash) {
			if (tabs[window.location.hash]) {
				activeTab = window.location.hash;
			}
		}
		
		// Activate the first tab
		$(activeTab + "_tab").addClass("active");
		
		// Disable the back button (first tab has no previous)
		updateButtonStatus();
		
		pageStatus = 'LOADING_INTERFACE';
		readyToAdvance = true;
		advanceLoadingStatus();
	}
	
	//*********
	// Stage 2 - Interface -> Elements (load RIFCS and traverse (fetching elements as we go))
	if (pageStatus == 'LOADING_INTERFACE' && requestsRemaining == 0 && readyToAdvance == true) {
		
		readyToAdvance = false;
		
		// Get tabs (now that cache is loaded)
		for (var t in tabs) {
			getTab(t);
		}
		
		showLoading("Loading RIFCS...");

		key = $.urlParam('key');
		if (key == "") { key = $("#elementCategory").val(); }
		
		requestsRemaining += 1;
		$.get(rootAppPath + "orca/manage/process_registry_object.php?task=get&data_source="+$.urlParam('data_source')+"&key=" + key, 
				function(data) {
					requestsRemaining -= 1;
					traverse(null, data);

					pageStatus = 'LOADING_ELTS'; 
					readyToAdvance = true;
					advanceLoadingStatus();
						
				},
			'xml');
		
	}
	
	//*********
	// Stage 2.5 - Resolve relatedObject class names
	if (pageStatus == 'LOADING_ELTS' && requestsRemaining == 0 && readyToAdvance == true) {	
		pageStatus = 'LOADING_RELOBJS'; 
		setRelatedObjectClasses();		
	}
	
	
	//*********
	// Stage 3 - Elements -> Validating (post the loaded form to the validation service)
	if (pageStatus == 'LOADING_RELOBJS' && requestsRemaining == 0 && readyToAdvance == true) {
		pageStatus = 'VALIDATING'; 
		readyToAdvance = false;
		
		// Some final interface stuff...
		if (metaGuideEnabled) {
			showMetaGuide();
		}

		showLoading("Validating Data...");
		key = $.urlParam('key');
		
		if (key != "") 
		{
			//alert(JSON.stringify(form2object('registry_object_add')));
			//, JSON.stringify(form2object('registry_object_add')
			readyToAdvance = true;
			advanceLoadingStatus();

			$.post(rootAppPath + "orca/manage/process_registry_object.php?task=validate&data_source="+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+"&key="+key+"&firstLoad=ggg&userMode=" + userMode, JSON.stringify(form2object('registry_object_add')),

					function(data) {
						
						$("#rmd_scripts").html(data);
						
						readyToAdvance = true;
						advanceLoadingStatus();
						
					}
			
			, 'html');

		} else {
			
			readyToAdvance = true;
			advanceLoadingStatus();
			
		}
	}

	//*********
	// Stage 4 - Elements -> Validating (post the loaded form to the validation service)
	if (pageStatus == 'VALIDATING' && requestsRemaining == 0 && readyToAdvance == true) {
		$("#rmd_loading").hide(); $("#rmd_interface").show();
		loadTabUI(activeTab);
		pageStatus = 'READY';
		
		
		
		// Validate DateTime strings
		$('.dateTimeField').each(function(){
			if ($(this).val().length > 0 && this.id.indexOf("_location_") > 0 ) {
				checkDateDiff(this.id);
			}
			if ($(this).val().length > 0 && !matchDTF($(this).val())) {
				SetErrors(this.id.replace(/object/,"errors"), "The format of this value must match xs:dateTime (ISO 8601).");
			}
		});
		

		$('.ckeditor_text').each(function(){
			testAddressPart(this.id)
		});

		// Validate URI strings
		$('.validUri').each(function(){
			testAnyURI(this.id);
		});
		
		// check related Objects 
		$('.relatedObjectKey').each(function(){
			testRelatedKey(this.id);
		});
	
		
		checkMandatoryFields(null);
		////console.log(userMode);
		if(userMode == 'readOnly')
		{
			$('#enableBtn').removeAttr('disabled');
			disableEditing();
		}

		
		//load related objects preview
		$('.relatedObjectKey').each(function(){
			//console.log($(this).val());
			var k = $(this).val();
			if(k!=''){
				var target = $(this).parents().nextAll().find('.ro_preview').first();
				getRelatedObjectPreview(k, target);
			}
		});	

	}
	
	var count = 1;
	$('input[id^=object_'+activeTab.substring(1)+'],select[id^=object_'+activeTab.substring(1)+']').each(function(index, element){$(element).attr("tabIndex",count+1); count++;});


}

function getRelatedObjectPreview(key, target){
	$.get('process_registry_object.php?task=related_object_preview&key='+key, function(data) {
	  $(target).html(data);
	  setRelatedObjectClasses();
	});

}

function doKeepAlive() {
	$.get('process_registry_object.php?task=keepalive');
	window.setTimeout(doKeepAlive, 1000 * 60 * 20);
}

// Fetch an element (usually HTML/JS) from source
function getRemoteElement(dest_element, tag, seq_num, registry_object_key, registry_object_status)
{
	
	if (dest_element.innerHTML) { dest_element = $("#" + dest_element); }
	if (tag == null) { return "No element tag specified"; }
	tag = $("#elementCategory").attr("value") + "_" + tag;
	var sURL = $("#elementSourceURL").attr("value");
	sURL += "?context=add_registry_object_element&";
	sURL += "tag=" + tag + "&";
	
	if (seq_num == null) {  
		seq_num = 0;
	}
	sURL += "seq_num=" + seq_num + "&";
		
	if (elementCache.element[tag] != undefined)
	{
		//console.log('Loaded from cache: ' + tag);
		$(dest_element).html(deSeqNumerize(elementCache.element[tag], seq_num));
	}
	else if (elementCache.element[tag.replace(/[a-z]+_/,"*_")] != undefined)
	{
		//console.log('Loaded from cache [wc]: ' + tag);
		$(dest_element).html(deSeqNumerize(elementCache.element[tag.replace(/[a-z]+_/,"*_")], seq_num));
	}
	else 
	{	
		//console.log('NOT loaded from cache: ' + tag);
		if (registry_object_key != null) {  
			if (registry_object_status == null) {  
				return "Registry object status may not be null if key specified";
			} else {
				sURL += "registry_object_key=" + registry_object_key + "&";
				sURL += "registry_object_status=" + registry_object_status + "&";
			}
		}
		
		$.ajaxSetup({
			dataType: "text",
			cache: false	
		});
		//console.log(sURL);
		$.get(sURL, function(data) {
			  $(dest_element).html(data.rawHTML);
			}, "json");
	}

	
}

function deSeqNumerize (content, seq_num)
{
	// xxx:todo
	return content;
}

//Fetch an element (usually HTML/JS) from source and append to dest_element
function appendRemoteElement(dest_element, tag, seq_num, registry_object_key, registry_object_status)
{
	if (dest_element.innerHTML) { dest_element = $("#" + dest_element); }
	if (tag == null) { return "No element tag specified"; }
	tag = $("#elementCategory").attr("value") + "_" + tag;
	var sURL = $("#elementSourceURL").attr("value");
	sURL += "?context=add_registry_object_element&";
	sURL += "tag=" + tag;
	
	if (seq_num == null) {  
		seq_num = 0;
	}
	sURL += "seq_num=" + seq_num + "&";
		
	//console.log(tag);
	if (elementCache.element[tag] != undefined)
	{
		//console.log('Loaded from cache: ' + tag);
		$(dest_element).append(elementCache.element[tag]);
	}
	else if (elementCache.element[tag.replace(/[a-z]+_/,"*_")] != undefined)
	{
		//console.log('Loaded from cache [wc]: ' + tag);
		$(dest_element).append(elementCache.element[tag.replace(/[a-z]+_/,"*_")]);
	}
	else 
	{	
		//console.log('NOT loaded from cache: ' + tag);
		if (registry_object_key != null) {  
			if (registry_object_status == null) {  
				return "Registry object status may not be null if key specified";
			} else {
				sURL += "registry_object_key=" + registry_object_key + "&";
				sURL += "registry_object_status=" + registry_object_status + "&";
			}
		}
		
		$.ajaxSetup({
			dataType: "text",
			cache: false	
		});
		
		$.get(sURL, function(data) {
			  $(dest_element).append(data.rawHTML);
			}, "json");
	}

}


function saveAndPreview() {
	
	//var theText = $('.ckeditor_text').ckeditorGet();
	for(var ed in CKEDITOR.instances) {
		if($('#'+ed).length > 0) // check if the description is still in the document
		{
		$('#'+ed).val(CKEDITOR.instances[ed].getData());
		//alert(CKEDITOR.instances[ed].getData());
		}
	}

	var key = $('#object_mandatoryInformation_key').val();
	var contributorPage = $("#contributor_page").val();

	if(contributorPage!='')
	{
		$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/view/group/?ds_key='+$('#object_mandatoryInformation_dataSource').val()+'&group=' + $('#object_mandatoryInformation_key').val() +'&groupName='+contributorPage);
	}else {
		$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/preview?ds='+$('#object_mandatoryInformation_dataSource').val()+'&key=' + $('#object_mandatoryInformation_key').val());
	}

	$("#errors_preview").hide();	

	/* don't understand why this is duplicated here?? 
	if(contributorPage!='')
	{
		$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/view/group/?ds_key='+$('#object_mandatoryInformation_dataSource').val()+'&group=' + $('#object_mandatoryInformation_key').val() +'&groupName='+contributorPage);
	}else{
		$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/preview?ds='+$('#object_mandatoryInformation_dataSource').val()+'&key=' + $('#object_mandatoryInformation_key').val());
	}
	*/
	
	if (userMode != 'readOnly')
	{
		$('#save_notification').show();
		$('#errors_preview').show();
		var ds = $('#object_mandatoryInformation_dataSource').val();
		$("#save_notification").html("<div>This draft has been saved successfully.</div>");
		
		$("#rda_preview_container").html("<a style='margin-left:10px;float:right;' id='print_preview' href='#'>Print Record</a>&nbsp;&nbsp;<a style='margin-left:10px;float:right;' id='rda_preview_xml' href='#'>View RIF-CS </a>" +
				"<a style='float:right;' id='rda_preview' class='rda_preview' href='#' target='_blank'>" +
				"<img style='padding: 0px 3px;float: left;' src='"+rootAppPath+"orca/_images/globe.png' /> Preview in Research Data Australia</a>" +
						"<div id='rifcs_plain' class='hide'><img src='"+rootAppPath+"orca/_images/delete_16.png' class='closeBlockUI' style='float:right;'/>" +
								"<textarea id='rifcs_plain_content'></textarea>" +
								"</div>");
			

		if(contributorPage!='')
		{
			$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/view/group/?ds_key='+$('#object_mandatoryInformation_dataSource').val()+'&group=' + $('#object_mandatoryInformation_key').val() +'&groupName='+contributorPage);
		}else{
			$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/preview?ds='+$('#object_mandatoryInformation_dataSource').val()+'&key=' + $('#object_mandatoryInformation_key').val());
		}

	//	$(".rda_preview").attr("href",$("#baseURL").val() + 'rda/preview?ds='+$('#object_mandatoryInformation_dataSource').val()+'&key=' + $('#object_mandatoryInformation_key').val());
		$("#print_preview").click(function()
		{
			var win = window.open('process_registry_object.php?task=preview&data_source='+$('#object_mandatoryInformation_dataSource').val()+'&key=' + $('#object_mandatoryInformation_key').val());		
		});

		$('#rda_preview_xml').click(function(){
			var key = $('#object_mandatoryInformation_key').val();
			var ds = $('#object_mandatoryInformation_dataSource').val();
			$.get(rootAppPath + 'orca/services/getRegistryObject.php?key='+encodeURIComponent(key)+'&ds='+encodeURIComponent(ds)+'&type=plain&stripped=true',
		       function(data) {
				$('#rifcs_plain_content').val(data);
		        $.blockUI({
		            message: $('#rifcs_plain'),
		            css: {
		                width: '600px',
		                top:'20%',
		                left:'20%',
		                textAlign: 'left',
		                padding: '10px'
		                },
		                overlayCSS: { backgroundColor: '#000', opacity:   0.6}
	            	});
	            $('.blockOverlay').attr('title','Click to unblock').click($.unblockUI);
	            $('.closeBlockUI').click($.unblockUI);
		       }
		   );
			$('#rifcs_popup').hide();
		});
	
	}else{
		if($('#rda_preview_container').length > 0){
			$('#rda_preview_container').remove();	
		}
		$("#errors_preview").hide();
		
		if(!$("#infos_preview").length){
			//if it's not there, create it so that we can append the preview
			$("#errors_preview").after('<div class="info_notification" id="infos_preview"></div>');
		}
		$("#save_notification").after(
				"<div style='border:none;' id='rda_preview_container'>" +
				"<a style='margin-left:10px;float:right;' id='rda_preview_xml' href='#'>View RIF-CS </a>" +
				"<a style='float:right;' id='rda_preview' class='rda_preview' href='#' target='_blank'>" +
				"<img style='padding: 0px 3px;float: left;' src='"+rootAppPath+"orca/_images/globe.png' /> Preview in Research Data Australia</a></div>" +
						"<div id='rifcs_plain' class='hide'><img src='"+rootAppPath+"orca/_images/delete_16.png' class='closeBlockUI' style='float:right;'/>" +
								"<textarea id='rifcs_plain_content'></textarea>" +
								"</div>");


		//copy and paste from above, need refactor
		
		if(contributorPage!='')
		{
			$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/view/group/?ds_key='+$('#object_mandatoryInformation_dataSource').val()+'&group=' + $('#object_mandatoryInformation_key').val() +'&groupName='+contributorPage);
		}else{
			$(".rda_preview").attr("href",$("#baseRDAURL").val() + '/preview?ds='+$('#object_mandatoryInformation_dataSource').val()+'&key=' + $('#object_mandatoryInformation_key').val());
		}
		$('#rda_preview_xml').click(function(){
			var key = $('#object_mandatoryInformation_key').val();
			var ds = $('#object_mandatoryInformation_dataSource').val();
			$.get(rootAppPath + 'orca/services/getRegistryObject.php?key='+encodeURIComponent(key)+'&ds='+encodeURIComponent(ds)+'&type=plain&stripped=true',
		       function(data) {
				$('#rifcs_plain_content').val(data);
		        $.blockUI({
		            message: $('#rifcs_plain'),
		            css: {
		                width: '600px',
		                top:'20%',
		                left:'20%',
		                textAlign: 'left',
		                padding: '10px'
		                },
		                overlayCSS: { backgroundColor: '#000', opacity:   0.6}
	            	});
	            $('.blockOverlay').attr('title','Click to unblock').click($.unblockUI);
	            $('.closeBlockUI').click($.unblockUI);
		       }
		   );
			$('#rifcs_popup').hide();
		});
		
	} 

	/* alert(document.forms[0].length + " is the length of the form");
	for(i=0;i<document.forms[0].length;i++)
		{
			alert(document.forms[0].elements[i].name + " : "  +document.forms[0].elements[i].value);
		} */
		// Run a filter to trim spaces as appropriate
		$('.input_filter_trim_spaces').each(function(){ $('#' + this.id).val($.trim($('#' + this.id).val())) });
	
	$.post(
		'process_registry_object.php?task=save&userMode=' + userMode + '&data_source='+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+'&key=' + $.urlParam('key'),
		JSON.stringify(form2object('registry_object_add')),
		function(data){ 
			$("#rmd_preview").html(data);
			
			var key = $('#object_mandatoryInformation_key').val();
			if (!key) { return; }
////console.log("dataSource: " + $('#object_mandatoryInformation_dataSource').val());
			$.get(
				'process_registry_object.php?task=validate&data_source='+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+'&key=' + encodeURIComponent($('#object_mandatoryInformation_key').val()),
				function(data) { 
					
					// Reset the quagmire trigger status
					quagmire_reset();
					
					// reset all errors
					$(".error_notification").html("");
					$(".error_notification").hide();
					$(".warning_notification").html("");
					$(".warning_notification").hide();
					$(".info_notification").html("");
					$(".info_notification").hide();
					
					if ($("#infos_preview").length) {
						$("#infos_preview").html("");
						$("#infos_preview").hide();
					}
					
					$("#errors_preview").removeClass('error_notification');
					$("#errors_preview").removeClass('warning_notification');	
					$("#errors_preview").addClass('success_notification');
					$(".fieldError").html("");
					$(".fieldError").hide();
					$("ul.tabs li.error").removeClass("error");
					$("ul.tabs li.warning").removeClass("warning"); 
					$("ul.tabs li.rec").removeClass("rec"); 					
					
					// execute the error script from server
					$("#rmd_scripts").html( data );
					if (!$("#errors_preview").hasClass("warning_notification") && !$("#errors_preview").hasClass("error_notification"))
					{
////console.log("qaRequired: " + qaRequired);						
							$("#errors_preview").html((userMode != 'readOnly' ?
										'<br/><span style="font-size:10px;"><b>Note:</b> This record has been saved in <u>DRAFT state</u>. ' +
										(qaRequired == false ? 
											'<div style="display:block; padding-left:10px;">' +
											'<a onclick="javascript:saveRegistryObject();" title="Add this record to registry" style="cursor:pointer;"><img src="'+orcaImageRoot+'/addtoregistry_button.png" alt="Add to Registry" style="float:left; display:block;" /></a>' +
											'</div>'
										:	
											'<br/><br/>'+
											'The data source you have entered this record under is currently flagged as requiring assessment and approval by ANDS staff.<br/>' +
											'You may either:<br/>' +
											'<input type="radio" name="save_action" value="draft" checked="checked" /> Leave the record as Draft and return to Manage My Records<br/>' +
											'<input type="radio" name="save_action" value="flag" /> Leave the record as Draft but flag the record as being ready for assessment<br/>' +
											'<input type="radio" name="save_action" value="submit_for_review" /> Submit the record for assessment<br/>' +
											'<input type="button" value="Continue" id="draft_continue_where" />'	
										) : '') 			
								+ '<br/><br/>').addClass("success_notification");	
					}

					var qualityLevelText = new Array();
					var roStatus = $('#elementCategory').val();
					qualityLevelText[1] = 'This record meets some of the Metadata Content Requirements  satisfying  minimal requirements for discovery, but does not comply with the Minimum Metadata Content Requirements.';
					qualityLevelText[2] = 'Congratulations! This record satisfies the minimum Metadata Content Requirements.';
					qualityLevelText[3] = 'Congratulations! This record meets and exceeds the minimum Metadata Content Requirements.';
				
					var ql_result = '<div id="ql_result"><div class="ql_num ql'+qualityLevel+'">Level '+qualityLevel+' Record</div><div class="ql_explain"><p>'+qualityLevelText[qualityLevel]+'</p></div><div class="clearfix"></div></div>';
					$('#qa_level_notification').html(ql_result);
						
					
					// Validate DateTime strings
					$('.dateTimeField').each(function(){

						if ($(this).val().length > 0) {
							checkDTF(this.id);
						}
					});
					

					
					// show preview page
					$("#rmd_saving").hide();
					$("#rmd_preview").fadeIn('slow');
					
					// Resolve related objects
					$('#rmd_preview .resolvable_key').each(function(){
						$.getJSON(
							'process_registry_object.php?task=related_object_preview&as_json=true&key=' + encodeURIComponent($(this).html()),
							function(data) { 
									$('#rmd_preview .resolvable_key').each(function(){
										if ($(this).html() == data['key'])
										{
											if (data['status'] == 'NOTFOUND')
											{
												resolved_string = "Cannot resolve key (record may not exist in registry)";
												$(this).parent().after('<tr><td class="attribute">Resolved value:</td><td class="valueAttribute">'+resolved_string+'</td></tr>');
											}
											else
											{
												if (data['status'] == 'PUBLISHED' || data['status'] == 'APPROVED')
												{
													$(this).html('<a href="'+rootAppPath+'orca/view.php?key='+encodeURIComponent(data['key'])+'" target="_blank">'+data['key']+'</a>');													
												}
												else
												{
													$(this).html('<a href="'+rootAppPath+'orca/manage/add_'+data['class'].toLowerCase()+'_registry_object.php?readOnly=true&data_source='+encodeURIComponent(data['data_source'])+'&key='+encodeURIComponent(data['key'])+'" target="_blank">'+data['key']+'</a>');		
												}
												resolved_string = "<b>" + data['title'] + "</b> (" + data['class'] + ") " + data['status_span'];
												$(this).parent().after('<tr><td class="attribute">Resolved value:</td><td class="valueAttribute">'+resolved_string+'</td></tr>');
											}
										}
									});
							});
					});
					
					
					
					$('.ckeditor_text').each(function(){
						testAddressPart(this.id)
					});
					$('.validUri').each(function(){
						testAnyURI(this.id);
					});
					
					// check related Objects 
					$('.relatedObjectKey').each(function(){
						testRelatedKey(this.id);
					});
					//alert($('#object_mandatoryInformation_dataSource').val());
					$.get(rootAppPath + "orca/manage/get_view.php?view=tipQA&key=&status="+ encodeURIComponent($('#status_span').val()) +"&ds="+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+"&key="+ encodeURIComponent($('#object_mandatoryInformation_key').val()),
							function(data) {
									$("#qa_preview").html(data);		
									initQADisplay();				
								},
							'html');
					if(userMode!='readOnly'){
						$('#save_notification').show();
						$('#errors_preview').show();
					}else{
						$('#save_notification').hide();
						$('#errors_preview').hide();
					}
					
					//displayQuagmireSummary();
////console.log("qaRequired2: " + qaRequired);
					
				}
			);
			
			 
		//	$('#rmb_formNotes').html('<pre>'+data+'</pre>'); 
		}
	);

	

}

function initQADisplay(){
	//stupid import registry objects
	$('.qa_ok').addClass('aro_qa_ok');
	$('.aro_qa_ok').removeClass('qa_ok');
	$('.qa_error').addClass('aro_qa_error');
	$('.aro_qa_error').removeClass('qa_error');
	
	var allReqCount = $('*[level=2]').length;
	var okReqArray = $('.aro_qa_ok[level=2]').length;
	var allRecCount = $('*[level=3]').length;
	var okRecArray = $('.aro_qa_ok[level=3]').length;	
	
	//wrap around the current QL with a div
	var qa = $('#qa_level_results');
	for(var i=0;i<=4;i++){
		$('*[level='+i+']', qa).wrapAll('<div class="aro_qa_container" qld="'+i+'"></div>');
	}
	//add the toggle header
	$('.aro_qa_container', qa).prepend('<div class="toggleQAtip"></div>');
	$('.toggleQAtip', qa).each(function(){
		if($(this).parent().attr('qld') == 1)
		   $(this).text('Quality Level 1 - Required RIF-CS Schema Elements');
		if($(this).parent().attr('qld') == 2)
		{
			if ( okReqArray == allReqCount ) {
				$(this).html('Quality Level 2 - You have met all of the ' + allReqCount +' required Metadata Content Requirements.' );
			} 
			else
			{
				$(this).html('Quality Level 2 - You have met '+ okReqArray + ' of the ' + allReqCount +' required Metadata Content Requirements. Refer to the tabs above as indicated by the <img src=\''+rootAppPath+'orca/_images/required_small.png\' /> Warning Icon' );
			}
		}
		if($(this).parent().attr('qld') == 3)
		{
			if ( okRecArray == allRecCount )	
			{
				 $(this).html('Quality Level 3 - You have met all of the ' + allRecCount +' recommended Metadata Content Requirements.' );				
			}
			else
			{
			 	$(this).html('Quality Level 3 - You have met '+ okRecArray + ' of the ' + allRecCount +' recommended Metadata Content Requirements. Refer to the tabs above as indicated by the <img src=\''+rootAppPath+'orca/_images/message_small.png\' /> Warning Icon' );	
			}
		}
	});
	//hide all qa
	$('.aro_qa_container', qa).each(function(){
		$(this).children('.aro_qa_ok, .aro_qa_error').hide();
	});
	//show the first qa that has error
	var showThisQA = $('.aro_qa_error:first', qa).parent();
	$(showThisQA).children().show();
	//coloring the qa that has error, the one that doesn't have error will be the default one
	$('.aro_qa_container', qa).each(function(){
		if($(this).children('.aro_qa_error').length>0){//has an error
			$(this).addClass('aro_error');
		}else{
			$(this).addClass('aro_success');
		}
	});
	//bind the toggle header to open all the qa inside
	$('.toggleQAtip', qa).click(function(){
		$(this).parent().children('.aro_qa_ok, .aro_qa_error').slideToggle('fast', function(){
			
		});
	});
	$('.aro_qa_ok').addClass('aro_success');
	$('.aro_qa_error').addClass('aro_error');
}

function saveRegistryObject(){
	$("#save_notification").hide();
	$("#info_notification").hide();
	$("#errors_preview").html('Saving...');
	$.get('process_registry_object.php?task=add&data_source='+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+'&key=' + $('#object_mandatoryInformation_key').val(),
		function(data) { 
			if (data.match(/schemaValidate/))
			{
				$("#errors_preview").removeClass('success_notification');
				$("#errors_preview").removeClass('warning_notification');
				$("#errors_preview").addClass('error_notification');		
				$("#errors_preview").html(	'A <b>critical error</b> has occured - the record could not be added. A copy of your draft has been saved automatically. Please consult the error below or contact <a href="mailto:'+adminEmail+'">'+adminEmail+'</a>.<br/>' +
											'<span style="font-size:10px;">' +
											data +
											'</span>');
			}
			else if (data.match(/ERROR/))
			{
				$("#errors_preview").removeClass('success_notification');
				$("#errors_preview").removeClass('warning_notification');
				$("#errors_preview").addClass('error_notification');		
				$("#errors_preview").html(	'An error has occured. A copy of your draft has been saved automatically. Please consult the error below or contact <a href="mailto:'+adminEmail+'">'+adminEmail+'</a>.<br/>' +
											'<br/>------------------------</br>' +
											'<span style="font-size:12px;">' +
											data +
											'</span>');
			}
			else 
			{
				$("#rmd_scripts").html(data);
			}
		}
	);
}

function SetErrors(field, text) {

		$("[name="+field+"]").show();
		//alert("[name="+field+"]");
		$("#" + (field.split("_"))[1] + "_tab").removeClass("warning");
		$("#" + (field.split("_"))[1] + "_tab").removeClass("rec");
		$("#" + (field.split("_"))[1] + "_tab").addClass("error");

		var current = $("[name="+field+"]").text();
		
		//console.log(current);
		//console.log(current.indexOf(text));
		
		if(current.indexOf(text)==-1){//if the current value does NOT contains the text, then add it
			$("[name="+field+"]").append(text + "<br/>");
		}


		
		
		$("#errors_preview").removeClass('success_notification');
		$("#errors_preview").removeClass('warning_notification');
		$("#errors_preview").addClass('error_notification');	
		
		$("#errors_preview").html(	'This draft contains errors which prevent it from being added to the ANDS Registry. Refer to the tabs above as indicated by the '+
									'<img src="'+orcaImageRoot+'/error_icon.png" alt="Error Icon" /> Error Icon.  <br/>' 
									);	
}

function SetWarnings(field, text, qCheck) {
	// Check if a Quagmire Check was triggered
	if (qCheck != null) 
	{
		quagmire_trigger(qCheck, text);
	}
	// Create the warning DIV if appropriate
	if (!$("[name="+field.replace(/errors/,"warnings")+"]").length) {
		$("[name="+field+"]").after('<div class="warning_notification'+(field.split('_').length != 2 ? ' fieldWarning' : '')+'" name="' + field.replace(/errors/,"warnings") + '"></div>');

	}
	$("[name="+field.replace(/errors/,"warnings")+"]").show();
	
	// Hide metadata guidance for tab-level warnings to minimize redundancy
	if (metaGuideEnabled && field.split('_').length == 2) {
		metaGuideEnabled = false;
		setStatusCookie("meta", "false");
		showMetaGuide();
	}
	
	// Change the TAB status
	if (!$("#" + (field.split("_"))[1] + "_tab").hasClass("error")) {
		$("#" + (field.split("_"))[1] + "_tab").removeClass("rec"); 	
		$("#" + (field.split("_"))[1] + "_tab").addClass("warning");

	}
	
	// Add the warning text
	$("[name="+field.replace(/errors/,"warnings")+"]").append('<img src="'+ orcaImageRoot +'/required_small.png"/> ' + text + "<br/>");

	if (!$('#errors_preview').hasClass('error_notification'))
	{
////console.log("qaRequiredWWW: " + qaRequired);	
		$("#errors_preview").removeClass('success_notification');
		$("#errors_preview").addClass('warning_notification');
		$("#errors_preview").html(	'<div><div id="quagmire_list"></div>' +		
				(userMode != 'readOnly' ?
				'<br/><span style="font-size:10px;"><b>Note:</b> This record has been saved in <u>DRAFT state</u>. ' +
				(qaRequired == false ? 
					'<div id="warning_risks_accept" style="font-size:10px;"><input type="checkbox" onclick="$(\'#warning_addanyway_button\').css(\'display\',\'block\'); $(\'#warning_risks_accept\').hide();" /> I understand that this record does not meet the Metadata Content Requirements, but I want to add it anyway</div>' +
					'<br/>' +
					'<div id="warning_addanyway_button" style="display:none;">' +
					'<a onclick="javascript:saveRegistryObject();" title="Add this record to registry" style="cursor:pointer;"><img src="'+orcaImageRoot+'/addtoregistry_blue_button.png" alt="Add to Registry" /></a>'
				:	
					'<br/><br/>'+
					'The data source you have entered this record under is currently flagged as requiring assessment and approval by ANDS staff.<br/>' +
					'You may either:<br/>' +
					'<input type="radio" name="save_action" value="draft" checked="checked" /> Leave the record as Draft and return to Manage My Records<br/>' +
					'<input type="radio" name="save_action" value="flag" /> Leave the record as Draft but flag the record as being ready for assessment<br/>' +
					'<input type="radio" name="save_action" value="submit_for_review" /> Submit the record for assessment<br/>' +
					'<input type="button" value="Continue" id="draft_continue_where" />'	
				) : '') 
				+ 
				'</div>');
		
	}
}

function SetInfos(field, text, qCheck) {
	
	// Check if a Quagmire Check was triggered
	if (qCheck != null) 
	{
		quagmire_trigger(qCheck, text);
	}
	
	// Hide metadata guidance for tab-level warnings to minimize redundancy
	if (metaGuideEnabled && field.split('_').length == 2) {
		metaGuideEnabled = false;
		setStatusCookie("meta", "false");
		showMetaGuide();
	}
	
	// Create the warning DIV if appropriate
	if (!$("[name="+field.replace(/errors/,"infos")+"]").length) {
		
		$("[name="+field+"]").after('<div class="info_notification'+(field.split('_').length != 2 ? ' fieldInfo' : '')+'" name="' + field.replace(/errors/,"infos") + '"></div>');
	}
	$("[name="+field.replace(/errors/,"infos")+"]").show();
	// Change the TAB status
	if (!$("#" + (field.split("_"))[1] + "_tab").hasClass("error")&&!$("#" + (field.split("_"))[1] + "_tab").hasClass("warning")) {
		$("#" + (field.split("_"))[1] + "_tab").addClass("rec");
	}	
	// Add the warning text
	$("[name="+field.replace(/errors/,"infos")+"]").append('<img src="'+ orcaImageRoot +'/message_small.png"/> ' + text + "<br/>");
	
	/*if (!$('#errors_preview').hasClass('error_notification'))
	{	
		$("#errors_preview").removeClass('success_notification');
		$("#errors_preview").addClass('warning_notification');
		$("#errors_preview").html(	'<div style="float:left;display:inline-block;">' +
				'This draft does not meet the ANDS Metadata Content Requirements. Refer to the tabs above as indicated by the '+
					'<img src="'+orcaImageRoot+'/warning_icon.png" alt="Warning Icon" /> Warning Icon. '+
				'<br/><span style="font-size:10px;"><b>Note:</b> This record has been saved in <u>DRAFT state</u>. </span>' +
				//'Assuming you understand the risks of ignoring the Metadata Content Requirements, you can continue to publish the record to the ANDS Registry by acknowledging below.' +
				'<div id="warning_risks_accept" style="font-size:10px;"><input type="checkbox" onclick="$(\'#warning_addanyway_button\').css(\'display\',\'inline-block\'); $(\'#warning_risks_accept\').hide();" /> I understand that this record does not meet the Metadata Content Requirements, but I want to add it anyway</div>' +
				'</div>' +
				'<div id="warning_addanyway_button" style="float:left;display:none;">' +
				'<a onclick="javascript:saveRegistryObject();" title="Add this record to registry" style="cursor:pointer;"><img src="'+orcaImageRoot+'/addtoregistry_blue_button.png" alt="Add to Registry" /></a>' +
				'</div>');
		
	}*/
}


// Display the AJAX loading graphic
function showLoading(load_text) 
{
	dest_element = $("#rmd_loading");
	if (load_text == null) { load_text = "Loading Page..."; }
	var loadingText = 	'<br/><br/><div class="loadingPlaceholder">' +
							'<img src="'+cosiImageRoot+'/_icons/ajax_loading.gif" alt="Loading Image" style="padding-left:30px;" />' +
							'<br/><br/>' + load_text +
						'</div>';
	
	if (!dest_element.html) {
		dest_element = $(dest_element);
	}
	
	dest_element.html(loadingText);

}


function incValByName (name) {
	
	document.getElementsByName(name)[0].value = parseInt(document.getElementsByName(name)[0].value) + 1;
	
}

function incValByID (id) {
	
	$('#' + id).val(parseInt($('#' + id).val()) + 1);
	
}

function getElementByName (name) {
	
	return document.getElementsByName(name)[0];
	
}

function getValByName (name) {
	
	return document.getElementsByName(name)[0].value;
	
}

// Fetch HelpText from a remote source with a loading image
// whilst the content is being called
function getHelpText(helpSection)
{
	
	// no helptext for now
	return true;
	
	if (helpEnabled && (helpSection == null || helpSection!= activeHelpSection)) {
		
		var sURL = $("#elementSourceURL").attr("value");
		var tag = "";
		sURL += "?context=add_registry_object_help&";
		if (helpSection == null) {
			tag += $("#elementCategory").attr("value") + "_";
			tag += activeTab.substring(1);
		} else {
			tag += helpSection;
		}
		
		sURL += "tag=" + tag + "&";
	
		var help_button = '<img style=\"float:right; cursor:pointer;\" src=\"' + orcaImageRoot + '/help_button.png\" />';

		
		$.get(sURL, function(data) {
			  $("[name='helpButton']").fadeOut("fast");
			  $("#helpText").html(help_button + data);
			  activeHelpSection = helpSection;
			});
		
		activeHelpSection = helpSection;

	}
}


// Toggle the mandatory class (which prefixes the label with a *)
function toggleMandatoryField(field_name) {

	var label = $('label[for="'+field_name+'"]');
	
	if (label.hasClass("mandatory")) {
		label.removeClass("mandatory");
	} else {
		label.addClass("mandatory");
	}
	
	if ($.browser.msie && parseInt($.browser.version, 10) < 8) 
	{
		$('label.mandatory').each( 
				function(index) { 
					if($(this).html().indexOf('*') == -1) 
					{ 
						$(this).html('<span style="font-style: normal;font-family: Georgia;color: blue;font-weight:bold;font-size:16px;">*</span> ' + $(this).html());
					}
				}
		);
	}
	
	
}


function addVocabComplete(field, type) {
	field = "#" + field;
	var button = field.replace(/object/,"button");
	$(button).click(function(event){
		  event.stopPropagation();
	});
	$( field ).autocomplete({
		minLength: 0,
		source: function(request, response){
			$.getJSON( "process_registry_object.php?task=getvocab", {vocab:type, term:request.term}, response );
		},
		open: function ( event, ui ) {

			$( button ).attr("src",$( button ).attr("src").replace(/_in/,"_out"));

			return false;
		},
		close: function ( event, ui ) {
			if (field == "#object_mandatoryInformation_type") {
				checkMandatoryFields(null);
			}
			if (field.indexOf("electronic") > 0 && field.indexOf("type") > 0)
			{
				testAnyURI(field.replace(/type/, "value_1_value").substr(1));				
			}
			if (field.indexOf("addressPart") > 0 && field.indexOf("type") > 0)
			{
				testAddressPart(field.replace(/_type/, "_value").substr(1));				
			}
			$( button ).attr("src",$( button ).attr("src").replace(/out/,"in"));
			return false;
		},
		focus: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		},
		select: function( event, ui ) {
			$( field ).val( ui.item.value );
			if(ui.item.value=="Unknown/Other"){
				var errorDiv = field.replace("#object","errors");
				var theDiv = document.getElementById(errorDiv);
				theDiv.style.display='inline';
				document.getElementById(errorDiv).innerHTML = 'You have selected the licence type of "Unknown/Other", if you would like to have an additional licence type included within this service, please contact <a href="mailto:services@ands.org.au">services@ands.org.au</a>';
			}
			if(ui.item.value!="Unknown/Other"){
				var errorDiv = field.replace("#object","errors");
				var theDiv = document.getElementById(errorDiv);
				if(theDiv!=null) theDiv.style.display='none';				
			}
			return false;
		}
	})
	.data( "autocomplete" )._renderItem = function( ul, item ) {

		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a><b>" + item.label + "</b> " + (item.value != item.label ? "(" + item.value + ")" : "") + "<br><i>" + item.desc + "</i></a>" )
			.appendTo( ul );
	};
	$(field).css('z-index', '100');
}


function addRelatedObjectSearch(field){
	searchField = '#'+field+'_search';
	result = '#'+field+'_result';
	button = '#'+field+'_button';
	
	$(button).click(function(){
		$(result).html('Loading...');
		doRelatedObjectSearch(field);
	});
	$(searchField).keypress(function(e){
		if(e.which==13){//press enter
			$(result).html('Loading...');
			doRelatedObjectSearch(field);
		}
	}).keyup(function(){//on typing
		//doRelatedObjectSearch(field);
	});
	
	$('#select_'+field+'_class, #select_'+field+'_dataSource').change(function(){
		$(result).html('Loading...');
		doRelatedObjectSearch(field);
	});
	
	$('.selectRelatedObjectValue').live('click', function(){
		where = $(this).attr('name');
		$('#'+where+'_value').val($(this).attr('id'));
		closeSearchModal(where);
		var target = $('#'+where+'_value').parents().nextAll().find('.ro_preview').first();
		getRelatedObjectPreview($(this).attr('id'), target);
	});
}

function doRelatedObjectSearch(field){
	term = $('#'+field+'_search').val();
	//if(term=="")term='*:*';
	roClass = $('#select_'+field+'_class').val(); 
	roDS = $('#select_'+field+'_dataSource').val(); 
	result = '#'+field+'_result';
	//process_registry_object.php?task=searchRelated&sText=dr berry&oClass=Party&dSourceKey=monash-test
	
	if (term.length == 1)
	{
		$(result).html('Search term must contain two or more characters.');
		return;
	}
	
	$.get("process_registry_object.php?task=searchRelated&sText="+encodeURIComponent(term)+"&oClass="+encodeURIComponent(roClass)+"&dSourceKey="+encodeURIComponent(roDS),
	   function(data){
		   //console.log(data);
			$(result).html('');
			if(data){
				$(result).append('<ul></ul>');
				$.each(data, function(){
					var thisRecord = '';
					this.desc = this.desc.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
					thisRecord +='<li><a href="javascript:void(0);" class="selectRelatedObjectValue" name="'+field+'" id="'+this.value+'" title="'+this.value+'">'+this.desc+'</a></li>';
					$(result+' > ul').append(thisRecord);
				});

			}else{
				$(result).html('No result');
			}
	   }, "json");
}



function toggleDropdown(button) {
	button = "#" + button;
	if (/in/.test($(button).attr("src"))) {
		// menu closed, open it
		$("*").autocomplete( "close" );
		$( button.replace(/button/,"object") ).autocomplete( "search", "*" ); 
	} else {
		// menu open, close it
		$( button.replace(/button/,"object") ).autocomplete( "close" ); 
	}
}

function showSearchModal(id)
{
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();

		//Set heigth and width to mask to fill up the whole screen
		$('#mask_'+id).css({'width':maskWidth,'height':maskHeight});
		
		//transition effect		
		//$('#mask_'+id).fadeIn(1000);	
		$('#mask_'+id).fadeTo("fast",0.4);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
		//Set the popup window to center
		$("#searchDialog_"+id).css('position', 'fixed');
		$("#searchDialog_"+id).css('top',  winH/2-$("#searchDialog_"+id).height()/2);
		$("#searchDialog_"+id).css('left', winW/2-$("#searchDialog_"+id).width()/2);
		$("#searchDialog_"+id).css('height', '360px');

	

		//transition effect
		$("#searchDialog_"+id).fadeIn(200); 
		$( "#" + id + "_name").val($("#" + id + "_value").val());
		$( "#" + id + "_name").autocomplete("search", $( "#" + id + "_value").val());

}


function closeSearchModal(id)
{
		$('#mask_' + id).hide();
		$("#searchDialog_" + id).hide();
}

function setRelatedId(id)
{	
	if($("#object_mandatoryInformation_key").val() != $("#" + id + "_name").val())
	{
		$( "#" + id + "_value").val($("#" + id + "_name").val());
		var warningField  = id.replace(/object/,"warnings");
		getRelatedObjectClass(id + "_value");
		$('[name=\"'+ warningField  + '_value\"]').html('');
		$('[name=\"'+ warningField + '_value\"]').hide();
		$('#mask_' + id).hide();
		$("#searchDialog_" + id).hide();
	}
	else
	{
		alert("An object cannot be related to itself");
	}
}

function testRelatedKey(id)
{
	var warningField  = id.replace(/object/,"warnings");
	if($("#object_mandatoryInformation_key").val() == $("#" + id).val() && $("#" + id).val() != '')
	{
		$('[name=\"'+ warningField  + '\"]').html('');		
		SetWarnings(id.replace(/object/,"errors") , "An object cannot be related to itself.");
	}
	else
		{
		$('[name=\"'+ warningField  + '\"]').html('');
		$('[name=\"'+ warningField + '\"]').hide();
		}
}
//process_registry_object.php?task=searchRelated&sText=dr berry&oClass=Party&dSourceKey=monash-test

function addRelatedObjectAutocomplete(field) {
	field = "#" + field;
	var cSelect = field.replace(/object/,"select");
	cSelect = cSelect.replace(/name/,"class");
	var dsSelect = cSelect.replace(/class/,"dataSource");
	//$( field ).autocomplete({ disabled: false});
	alert("in autocomplete");
	$( field ).autocomplete({
		minLength: 2,
		source: function(request, response){
			$.getJSON( "process_registry_object.php?task=searchRelated", {sText:$.trim(request.term), oClass:$( cSelect ).val() ,dSourceKey:$( dsSelect ).val()}, response );
		},
		focus: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		},
		select: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		}
	})
	.data( "autocomplete" )._renderItem = function( ul, item ) {

		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a><b>" + item.label + "</b> " + (item.value != item.label ? "(" + item.value + ")" : "") + "<br><i>" + item.desc + "</i></a>" )
			.appendTo( ul );
	};
}

function addGroupAutocomplete(field) {
	$( field ).autocomplete({
		source: function(request, response){
			$.getJSON( "process_registry_object.php?task=getGroups", {term:$.trim(request.term)}, response );
		},
		focus: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		},
		close: function ( event, ui ) {
			if (field == "#object_mandatoryInformation_group") {
				checkMandatoryFields(null);
			}
			return false;
		},
		select: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		}
	}).data( "autocomplete" )._renderItem = function( ul, item ) {

		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a><b>" + item.value + "</b>" + (item.desc ? "<br><i>" + item.desc + "</i>" : "") + "</a>" )
			.appendTo( ul );
	};
	$(field).css('z-index', '100');
}

function addSubjectVocabComplete(field) {
	field = "#" + field;
	$( field ).autocomplete({
		source: function(request, response){
			//if ($(field.replace(/value/,"type")).val() == "local") return false;
			if (/^\d{6}$/.test($(field).val())) {
				var params = {vocab:$(field.replace(/value/,"type")).val().toUpperCase(), term:$(field).val().replace(/\d{2}$/,"")};
			} else {
				var params = {vocab:$(field.replace(/value/,"type")).val().toUpperCase(), term:$(field).val()};
			}
			$.getJSON( "process_registry_object.php?task=getSubjectVocab", params, response );
		},
		focus: function( event, ui ) {
			return false;
		},
		select: function( event, ui ) {
			if ($( field ).val() ==  ui.item.value) { return false; }
			
			$( field ).val( ui.item.value );
			
			if (/^\d{2}$|^\d{4}$/.test($( field ).val())) {
				$( field ).autocomplete( "search" ); 
			}
			
			return false;
		}
	}).data( "autocomplete" )._renderItem = function( ul, item ) {

		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a>" + (item.value != "" ? "<b>" + item.name + "</b> (" + item.value + ")<br>" : "") + "<i>" + item.desc + "</i></a>" )
			.appendTo( ul );
	};
	$(field).css('z-index', '100');
}



function checkMandatoryFields(fieldId)
{
	
	canContinue = true;

	if (fieldId != null && fieldId == "object_mandatoryInformation_key") 
	{		
		if(decodeURIComponent($.urlParam('key').replace(/\+/g,"%20")) != $("#"+ fieldId).val()) 
		{
			$.get(rootAppPath + "orca/manage/process_registry_object.php?task=checkKey&data_source="+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+"&key="+ $("#"+ fieldId).val(),
					function(data) {						
						$("#rmd_scripts").html(data);						
					}			
			, 'html');		
		} 
		else 
		{			
			$('[name=\"errors_mandatoryInformation_key\"]').html('');
			$('[name=\"errors_mandatoryInformation_key\"]').hide();
			$('#mandatoryInformation_tab').removeClass('error');
		}			
	} 	
	else if(fieldId != null)
	{
		mandatoryFields[fieldId] = $.trim($("#"+ fieldId).val());
	}

	$.each(mandatoryFields, function(index,value){
		if($.trim($("#"+ index).val()) == '')
		{
		canContinue = false;
		}
	});
	
	if (canContinue && isUniqueKey && !hasFlashed)
	{
		hasFlashed = true;
		$("#preview_tab").pulse({
		    backgroundColor: ['#A8D078', '#E0E0E0']
		}, 550, 3, 'linear');
	}
	setTabs();
	//alert("canContinue " + canContinue +  " isUniqueKey " + isUniqueKey);
}


function setRelatedObjectClasses()
{
   
	if($('[name$="roclass"]').length > 0)
	{
		$.rmd.roclassQueries = [];
		
		$('[name$="roclass"]').each(function(index) {		
			$('[name$="roclass"]').each(function(index) { 
				
				var thisField = $(this).attr("id").replace(/_roclass/,"");
				
				if($("#"+ thisField + "_value").val() != '')
				{
					// key value, field name
					$.rmd.roclassQueries.push([$("#"+ thisField + "_value").val(), thisField]);
				}
				
			});
			//getRelatedObjectClassAsync(($(this).attr("id").replace(/roclass/,"value")));			
		});
		
		getRelatedObjectClassAsync($.rmd.roclassQueries);
	}
   else
   {
		
		readyToAdvance = true;
		advanceLoadingStatus();
   }
}


function getRelatedObjectClassAsync(classQueries)
{
	readyToAdvance = false;
	requestsRemaining += 1;
	$.post(rootAppPath + "orca/manage/process_registry_object.php?task=getRelatedClass&data_source="+encodeURIComponent($('#object_mandatoryInformation_dataSource').val()),
		{ 'relations': classQueries },
		function(data) {
				$("#rmd_scripts").html(data);
				readyToAdvance = true;
				requestsRemaining -= 1;
				advanceLoadingStatus();						
			},
		'html');

}


function getRelatedObjectClass(fieldId)
{
	// prevent getting called on first population
	if (requestsRemaining == 0)
	{
		var classField = fieldId.replace(/value/,"roclass");
		if($("#"+ fieldId).val() != '')
		{
			$.get(rootAppPath + "orca/manage/process_registry_object.php?task=getRelatedClass&data_source="+encodeURIComponent($('#object_mandatoryInformation_dataSource').val())+"&key="+ encodeURIComponent($("#"+ fieldId).val()) + "&fieldId=" + classField,
				function(data) {
						$("#rmd_scripts").html(data);						
					},
				'html');
		}
		else
		{
		$("#"+classField).val('');		
		}
	}
}

function setTabs()
{
	
	if(canContinue && isUniqueKey)
	{
		$("#nextButton").removeAttr("disabled");
		$("#finishButton").removeAttr("disabled");
		for (var tab in tabs)
		{
			if(tab != 'mandatoryInformation')
			$(tab + "_tab").removeClass('disabledTab');

		}
	}
	else
	{
		$("#nextButton").attr("disabled", "true");	
		$("#finishButton").attr("disabled", "true");
		for (var tab in tabs)
		{
			if(tab != '#mandatoryInformation')
			$(tab + "_tab").addClass('disabledTab');

		}
	}	
}

function limitCheckNL(field_id, limit_count) 
{
	if ($("[name='"+field_id+"']").val().split('\n').length > limit_count) 
	{
		$("[name='"+field_id+"']").val($("[name='"+field_id+"']").val().split('\n').slice(0,limit_count).join('\n'));
	}
	return true;
}

//=============================================================================
// HELPER FUNCTIONS
//-----------------------------------------------------------------------------

// Get URL params using jQuery - i.e. $.urlParam('someparam'); // value
$.urlParam = function(name){
	var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if (results) {
		try {
			return results[1];
		}
		catch (err) {
			return "";
		}
	} else {
		return "";
	}
};

// Update buttons based on whether we are at the first/last 
// tab in the tab list (i.e. no further to go "next"/"back" to
function updateButtonStatus() {

	if (activeTab == "#preview")
	{
		$("#finishButton").attr("disabled", "true");
	} else {
		$("#finishButton").removeAttr("disabled");
	}
	
	if ($(activeTab + '_tab').next("ul.tabs li").length == 0) {
		$("#nextButton").attr("disabled", "true");
	} else {
		$("#nextButton").removeAttr("disabled");
	}
	
	if ($(activeTab + '_tab').prev("ul.tabs li").length == 0) {
		$("#backButton").attr("disabled", "true");
	} else {
		$("#backButton").removeAttr("disabled");
	}

}

function checkDTF (field_id)
{
	var field = $('#' + field_id);
	
	if (field.val() != '')
	{
		if (!matchDTF(field.val()))
		{
			SetErrors(field_id.replace(/object/,"errors"), "The format of this value must match xs:dateTime (ISO 8601).");
		} else {
			$('[name="' + field_id.replace(/object/,"errors")+'"]').html("").hide();
			checkDateDiff(field_id);
		}
	} 
}

function checkDateDiff(field_id)
{
	if(field_id.indexOf("_location_") > 0)
	{		
		if(field_id.indexOf("_dateFrom") > 0)
		{
			var dateTo = field_id.replace(/dateFrom/,"dateTo");
			var toDate = new Date($('#' + dateTo ).val());
			var fromDate = new Date($('#' + field_id).val());
			if(fromDate > toDate)
			{
				$('[name=\"'+ field_id.replace(/object/,"errors") + '\"]').html('');
				SetErrors(field_id.replace(/object/,"errors"), "The Date From entered is later than the Date To.");		
			}
			else
			{
				$('[name=\"'+ field_id.replace(/object/,"errors") + '\"]').html('');
				$('[name=\"'+ dateTo.replace(/object/,"errors") + '\"]').html('');
				$('[name=\"'+ field_id.replace(/object/,"errors") + '\"]').hide();
				$('[name=\"'+ dateTo.replace(/object/,"errors") + '\"]').hide();
			}
		}
		else if(field_id.indexOf("_dateTo") > 0)
		{
			var dateFrom = field_id.replace(/dateTo/,"dateFrom");	
			var toDate = new Date($('#' + field_id).val());
			var fromDate = new Date($('#' + dateFrom).val());
			if(fromDate > toDate)
			{
				$('[name=\"'+ field_id.replace(/object/,"errors") + '\"]').html('');
				SetErrors(field_id.replace(/object/,"errors"), "The Date To entered is earlier than the Date From.");		
			}	
			else
			{
			$('[name=\"'+ dateFrom.replace(/object/,"errors") + '\"]').html('');
			$('[name=\"'+ field_id.replace(/object/,"errors") + '\"]').html('');
			$('[name=\"'+ dateFrom.replace(/object/,"errors") + '\"]').hide();
			$('[name=\"'+ field_id.replace(/object/,"errors") + '\"]').hide();
			}
		}
		
		var visibleErrorFields = 0;
		$('div[name^="errors_'+ (field_id.split("_"))[1] + '"]').each(
				function(index, element){
					if($(element).css("display") == "block")	
					{					
					visibleErrorFields++;
					}
				}
				);
		if(visibleErrorFields == 0)
		{
			$("#" + (field_id.split("_"))[1] + "_tab").removeClass('error');
		}
		
		
	}
}


function matchDTF(str) {
	var matches = str.match(/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T|\ ]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/);
	if (matches && matches[0] == str) {
		return true;
	}
	
	return false;
}


$('body').click(function() {
	$("*").autocomplete( "close" );
	//$("#dctInnerContainer").remove(); date time control 
});


function showClearAlert()
{
		var userChoice = confirm("**WARNING**\nAny changes since your last save will be lost.\nAre you sure?");
		if(userChoice == true)
		{
			$(window.location).attr('href','my_records.php');
		}
}

// Quagmire Checks for checks between recommended and required items, as well as summary of triggered checks
var TRIGGERED = 1;
var UNTRIGGERED = 0;
var REQUIRED = 1; // setWarnings();
var RECOMMENDED = 0; // setInfos();

var quagmireChecks = {};

function quagmire_init() 
{
    quagmireChecks = {};
}

function quagmire_append (name, type, messageIn) 
{
	quagmireChecks[name] = 
    {
        status: UNTRIGGERED,
        type: type,
        message: messageIn
    };
}

function quagmire_getNumTriggered (target_type)
{
    var trig_count = 0;
    for (i in quagmireChecks)
    {
        var check = quagmireChecks[i];
        if (check.status==TRIGGERED && check.type == target_type) { trig_count++; }
    }
    return trig_count;
}

function quagmire_getTriggered (target_type)
{
    var trig_array = [];
    for (i in quagmireChecks)
    {
        var check = quagmireChecks[i];

        if (check.status==TRIGGERED && check.type == target_type) { trig_array[trig_array.length] = check.message; }
        
    }
    return trig_array;
}
function quagmire_getNotTriggered (target_type)
{
    var not_trig_array = [];
    for (i in quagmireChecks)
    {
        var check = quagmireChecks[i];


        if (check.status!=TRIGGERED && check.type == target_type) 
        { 
        	
        	var elementName = i.split("_");
        	var secondVar = '';
        	if(elementName.length>2) secondVar = elementName[2].toLowerCase();
        	var theType = check.type;
         	not_trig_array[not_trig_array.length] = check.message; 
        }
        
    }
    return not_trig_array;
}
function quagmire_getNumTotal (target_type)
{
    var trig_count = 0;
    for (i in quagmireChecks)
    {
        var check = quagmireChecks[i];
        if (check.type == target_type) { trig_count++; }
    }
    return trig_count;
}

function quagmire_trigger (name, msg)
{
   quagmireChecks[name].status = TRIGGERED;
   quagmireChecks[name].message = msg;
}

function displayQuagmireSummary()
{	
	
	
	var reqCount = quagmire_getNumTriggered(REQUIRED);
    var recCount = quagmire_getNumTriggered(RECOMMENDED);

    
	var req_message = "";
	if(reqCount > 0)
	{
		var allReqCount = quagmire_getNumTotal(REQUIRED);
		var reqArray = quagmire_getTriggered(REQUIRED);
		var okReqArray = quagmire_getNotTriggered(REQUIRED);

		req_message = "<br/><a onclick=\"toggleList('reqWarningList')\" style=\"cursor:pointer\">You have met " + (allReqCount - reqCount) + " of the " + allReqCount + " required Metadata Content Requirements. Required elements are indicated on the above tabs by the <img src=\"" + orcaImageRoot + "/required_icon.png\"/> warning icon.</a><br/><br/><input type=\"button\" id=\"reqWarningList_btn\" class=\"buttonSmall\" value=\"Show Details\" onclick=\"toggleList('reqWarningList')\">";
		req_message += "<div id=\"reqWarningList\" style=\"display:none;\">";
		if(okReqArray.length>0)
		{
			req_message += "<ul class=\"not_triggered\">";
			for (i in okReqArray)
			{
				req_message += "<li>" + okReqArray[i] + "</li>";		
			}	
		    req_message += "</ul>";	
			
		}
		req_message += "<ul class=\"triggered\">";
		
		for (i in reqArray)
	    {
	    	req_message += "<li>" + reqArray[i] +"</li>";	
	    }
		req_message += "</ul>";
		req_message += "</div>";
		
	}
	
	var rec_message = "";
	if(recCount > 0)
	{
		var allRecCount = quagmire_getNumTotal(RECOMMENDED);
		var recArray = quagmire_getTriggered(RECOMMENDED);
		var okRecArray = quagmire_getNotTriggered(RECOMMENDED);		
		
		rec_message += "<br/><a onclick=\"toggleList('recWarningList')\" style=\"cursor:pointer\">You have met " + (allRecCount - recCount) + " of the " + allRecCount + " recommended Metadata Content Requirements. Reccomended elements are indicated on the above tabs by the <img src=\"" + orcaImageRoot + "/message_icon.png\"/> warning icon.</a><br/><br/><input type=\"button\" id=\"recWarningList_btn\" class=\"buttonSmall\" value=\"Show Details\" onclick=\"toggleList('recWarningList')\">";
		rec_message += "<div id=\"recWarningList\" style=\"display:none;\">";
		if(okRecArray.length)
		{
			rec_message += "<ul class=\"not_triggered\">";
			for (i in okRecArray)
			{
				rec_message += "<li>" + okRecArray[i] + "</li>";		
			}	
			rec_message += "</ul>";	
			
		}
		rec_message += "<ul class=\"triggered\">";		
		

	    for (i in recArray)
	    {
	    	rec_message += "<li>" + recArray[i] + "</li>";	
	    }
	    rec_message += "</ul>";
	    rec_message += "</div>";
	}
		
	if(req_message != "")
	{		
		$("#quagmire_list").html(req_message).show();
		
	}
	
	if(rec_message != "" && !$("#errors_preview").hasClass('error_notification'))
	{
		// Create the infos DIV if appropriate
		if (!$("#infos_preview").length) {
			$("#quagmire_list").after('<div class="save_info_notification" id="infos_preview"></div>');
		}
		
		$("#infos_preview").html(rec_message).show();
		
		//if ($.browser.msie) 
		//{
			//$("#recWarningList").css("display","block");
			
		//}
	}
	

}

function testAddressPart(field_id)
{	
	//console.log(field_id);
	var typeField = field_id.replace(/_value/,"_type");

	if(($("#"+typeField).val() == 'telephoneNumber'  || $("#"+typeField).val() == 'text' || $("#"+typeField).val() == 'faxNumber'))
	{
		if(CKEDITOR.instances[field_id] != null)
		{
			$('#'+field_id).val(CKEDITOR.instances[field_id].getData().replace( /<[^<|>]+?>/gi,'').trim());
			CKEDITOR.instances[field_id].destroy(true);	
		}
		testAnyURI(field_id);
	}
	else if($("#"+typeField).val() == 'addressLine' && CKEDITOR.instances[field_id] == null) // check if the description is still in the document
	{
		CKEDITOR.replace(field_id,{ toolbar: 'Basic'});
	}
}


function testAnyURI(field_id)
{
	var fieldValue  = $.trim($("#"+ field_id).val());
	$("#"+ field_id).val(fieldValue);
	var regex =  new RegExp(/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i);
	var emailRegex = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

	var phoneRegex = new RegExp(/^[^a-zA-Z]*$/i);

	var type = 'URI';
	if(fieldValue != '')
	{
		var errorField = field_id.replace(/object/,"errors");
		var warningField = field_id.replace(/object/,"warnings");
		
		if(field_id.indexOf("electronic") > 0 || field_id.indexOf("addressPart") > 0)
		{
			if($("#" + field_id.replace(/value_1_value/,"type")).val() == 'email')
			{
				regex = emailRegex;
				type = "email";
			}

			else if($("#" + field_id.replace(/_value/,"_type")).val() == 'telephoneNumber' || $("#" + field_id.replace(/_value/,"_type")).val() == 'faxNumber')
			{
				regex = phoneRegex;
				if($("#" + field_id.replace(/_value/,"_type")).val() == 'telephoneNumber')
				{
					type = "phone number <br/><span>E.g. '1800-123-456' (should not contain alphabetic characters)</span>";
				}
				else
				{
					type = "fax number <br/><span>E.g. '1800-123-456' (should not contain alphabetic characters)</span>";
				}

			}
			else if($("#" + field_id.replace(/value_1_value/,"type")).val() != 'url' && $("#" + field_id.replace(/value_1_value/,"type")).val() != 'wsdl')
			{
				$('[name=\"'+ errorField + '\"]').html('');
				$('[name=\"'+ errorField + '\"]').hide();
				$('[name=\"'+ warningField  + '\"]').html('');
				$('[name=\"'+ warningField + '\"]').hide();
				var visibleErrorFields = 0;
				var visibleWarningFields = 0;
				$('div[name^="errors_'+ (field_id.split("_"))[1] + '"]').each(
						function(index, element){
							if($(element).css("display") == "block")	
							{					
							visibleErrorFields++;
							}
						}
						);
				$('div[name^="warnings_'+ (field_id.split("_"))[1] + '"]').each(
						function(index, element){
							if($(element).css("display") == "block")	
							{					
								visibleWarningFields++;
							}
						}
						);

				if(visibleErrorFields == 0)
				{
					$("#" + (field_id.split("_"))[1] + "_tab").removeClass('error');
				}
				if(visibleWarningFields == 0)
				{
					$("#" + (field_id.split("_"))[1] + "_tab").removeClass('warning');
				}
				return;
			}

		}
		
		
		if(regex.test(fieldValue))		
		{
			$('[name=\"'+ errorField + '\"]').html('');
			$('[name=\"'+ errorField + '\"]').hide();
			$('[name=\"'+ warningField  + '\"]').html('');
			$('[name=\"'+ warningField + '\"]').hide();
			var visibleErrorFields = 0;
			var visibleWarningFields = 0;
			$('div[name^="errors_'+ (field_id.split("_"))[1] + '"]').each(
					function(index, element){
						if($(element).css("display") == "block")	
						{					
						visibleErrorFields++;
						}
					}
					);
			$('div[name^="warnings_'+ (field_id.split("_"))[1] + '"]').each(
					function(index, element){
						if($(element).css("display") == "block")	
						{					
							visibleWarningFields++;
						}
					}
					);

			if(visibleErrorFields == 0)
			{
				$("#" + (field_id.split("_"))[1] + "_tab").removeClass('error');
			}
			if(visibleWarningFields == 0)
			{
				$("#" + (field_id.split("_"))[1] + "_tab").removeClass('warning');
			}

		}	
		else
		{			
			if(field_id.indexOf("relatedObject") > 0 || field_id.indexOf("accessPolicy") > 0 || field_id.indexOf("rightsUri") > 0)
			{
				$('[name=\"'+ errorField + '\"]').html('');
				if (type == 'URI')
				{
					SetErrors(errorField,"The value must be a valid URI. <br/><span>E.g. 'http://www.example.com/'</span>");
				}
				else
				{
					SetErrors(errorField,"The value must be a valid " + type);
				}
			}
			else 
			{			
				$('[name=\"'+ warningField  + '\"]').html('');
				if (type == 'URI')
				{
					SetWarnings(errorField,"The value must be a valid URI. <br/><span>E.g. 'http://www.example.com/'</span>");	
				}
				else
				{
					SetWarnings(errorField,"The value should be a valid " + type);	
				}
			}
		}
	}
}

function toggleList(list_id)
{

	 if($('#' + list_id).css("display") == "block")
	 {
		if ($.browser.msie) 
		{
			$("#" + list_id).css("display","none")
		}	else {	 
				$('#' + list_id).slideUp();
		}
		$('#' + list_id + '_img').attr("src" , orcaImageRoot + "/expand_icon.png");
		$('#' + list_id + '_btn').attr("value" , "Show Details");
	 }
	 else
	 {
		if ($.browser.msie) 
		{
			$("#" + list_id).css("display","block")
		}	else {	 		 
				$('#' + list_id).slideDown(); 
		}
		$('#' + list_id + '_img').attr("src" , orcaImageRoot + "/colapse_icon.png");
		$('#' + list_id + '_btn').attr("value" , "Hide  Details");
	 }
}

function disableEditing()
{
	$('input[name^="object."], select[name^="object."], input[name^="object."], textarea[name^="object."], .buttonSmall, .button:not(.mmr_action)').each(
			function(){
				$(this).attr("disabled","disabled");
			});	
	$('.cursorimg, .dctIcon, .rmdButtonContainer, img[name="relatedImg"], .mct_toolbar').each(
			function(){
				//$(this).css("display","none");
				$(this).hide();
			});		
	$('input[name^="btn_"]').each(
			function(){
				$(this).attr("disabled","disabled");
			});	
	$('#enableBtn').show();
}

$('#enableBtn').live('click', function(){
	if($(this).attr('value')=='Enable Editing'){
		if (enableEditing())
		{
			userMode = 'edit';
			activeTab = '#mandatoryInformation';
			activateTab(activeTab);
			$('#preview_tab > a').html("<img id=\"saveButton\" src=\"" +orcaImageRoot+ "/save.png\" style=\"padding-top:4px;\" alt=\"Save and Preview this Draft\" /> Save Draft");
			$(this).parent().hide();
		}
	}
});

function enableEditing()
{
	if ($.urlParam('harvested') != "")
	{
		if (!confirm('The record you have selected to edit may have been entered into the ANDS Registry via a harvest. Editing this record will only change the record in the ANDS registry and not in the original harvested source. Do you still want to continue?'))
			return false;
	}
	
	$('input[name^="object."], select[name^="object."], input[name^="object."], textarea[name^="object."], .buttonSmall, .button').each(
			function(){
				$(this).removeAttr("disabled");
			});	
	$('.cursorimg, .dctIcon, .rmdButtonContainer, #preview_tab, img[name="relatedImg"], .mct_toolbar').each(
			function(){
				//$(this).css("display","block");
				$(this).show();
			});	

	$('input[name^="btn_"]').each(
			function(){
				$(this).removeAttr("disabled");
			});	
	
	$('#heading_action').html('Edit ');
	
	return true;
}



// Duplicated from mmr_dhtml.js

$("#button_bar > input").live("click", function(e) {
	e.preventDefault();
	var targetKeys = new Array();
	var action = $(this).attr("name");
	var isPreApproval = true;
	var dataSourceKey = $('#object_mandatoryInformation_dataSource').val();
	targetKeys.push($('#object_mandatoryInformation_key').val());
	

	$('#mmr_datasource_alert_msg').html("Sending message to server. Please wait...<br/><br/>" +
			"<div style='text-align:center'>" +
				"<img src='../../_images/_icons/ajax_loading.gif' />" +
			"</div>"); 
	$.blockUI({ message: $('#mmr_datasource_alert') });
	$.post(
		$("#baseURL").val() + "manage/process_registry_object.php?task=manage_my_records&action=" + $(this).attr("name"),
		{ 	
			'keys[]' : targetKeys, 
			'preapproval' : isPreApproval,
			'dataSourceKey' : dataSourceKey
		},
		function(data)
		{
			if (data['responsecode'] == 0)
			{
				// Error occured
				alert("Error Occured: Access Denied.");
				$.unblockUI(); 
			}
			else if (data['responsecode'] == "MT008")
			{
				$('#mmr_datasource_alert_msg').html('Your records have been sent to ANDS for assessment and approval. You should contact your ANDS Client Liaison Officer to notify them that these records have been submitted.');
			}
			else if (data['responsecode'] == "MT014")
			{
				$('#mmr_datasource_alert_msg').html('An ANDS Quality Assessor has been notified of your submitted record(s)');
			}
			else
			{
				if (data['alert'])
				{
					$('#mmr_datasource_alert_msg').html(data['alert']);
					$.unblockUI(); 
				}
				
				window.location.href = $("#baseURL").val() + "manage/my_records.php?data_source=" + encodeURIComponent($('#object_mandatoryInformation_dataSource').val());
			}
		},
		'json'
	);
	
	
});


function checkTypeValue(theSelect){
	
	theDiv = document.getElementById(theSelect);
	
	if(theDiv.value==''){
		var errorDiv = theSelect.replace("object","errors");
		var theDiv = document.getElementById(errorDiv);
		theDiv.style.display='inline';
		document.getElementById(errorDiv).innerHTML = 'You have not selected a licence type. Please select licence type of "Unknown/Other" if your licence type is not listed. '
		//alert("we have a prob");
	};
}
