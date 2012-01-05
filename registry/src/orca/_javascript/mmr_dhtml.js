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

var STATUS_COOKIE_NAME = 'ORCA_MMR_STATUS';
var STATUS_COOKIE_TTL_DAYS = 365*5;

var MMR_datasource_info_visible = true;

$(document).ready(function() {
	
	// Help lower resolution screens by expanding the outermost page containers to the fixed-width size of the MMR table
	$('body > div').css('min-width','1235px');
	
	$.blockUI.defaults.css.width = '503px';
	
	MMR_initStatusCookie();
	
	if(MMR_datasource_info_visible == "false")
	{
		$('#mmr_datasource_information').hide();
		$('#mmr_information_show').show();
	}
	else
	{
		$('#mmr_datasource_information').show();
		$('#mmr_information_show').hide();
	}

	
	$('#mmr_information_hide').live('click', function(e){
		e.preventDefault();
		MMR_toggleInfoVisible();
	});
	
	$("#mmr_information_show").live("click", function (e) {
		e.preventDefault();
		MMR_toggleInfoVisible();
	});
	
	
	$(".mmr_expandable_table > tbody").each(function() {
		$("tr:gt(7)", this).hide(); 
	   	$("tr:nth-child(9)", this).after("<tr class='mmr_more_records_link'><td colspan='10'></td><td><a href='#'>Show more..</a></td></tr>");
	});
	$(".mmr_more_records_link").live("click", function() {
		var tr = $(this).parent();
	   	tr.children(".record_row").show();
	   	$(this).remove();
	   	$("tr:last", tr).after("<tr class='mmr_less_records_link'><td colspan='10'></td><td><a href='#'>Show less..</a></td></tr>");
	   	return false;
	});
	$(".mmr_less_records_link").live("click", function() {
		var tr = $(this).parent();
	   	$(this).remove();
	   	$("tr:gt(7)", tr).hide(); 
	   	$("tr:nth-child(9)",tr).after("<tr class='mmr_more_records_link'><td colspan='10'></td><td><a href='#'>Show more..</a></td></tr>");
	   	tr.animate({ scrollTop: 0 }, 'slow');
	   	return false;
	});
	
	$("tr.record_row > td:not(.rowSelector):not(.mmr_flag)").live("click", function() {
		
		var select = $(".mmr_select_box", $(this).parent());

		
		if (select.attr("checked") == false)
		{		
			//select.attr("checked", true);
			select.click();
			select.parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
		}
		else
		{
			//select.attr("checked",false);
			select.click();
			select.parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
		}

	});
	

	$(".mmr_flag > .not_flagged").live("click", function() {
		
		if ($(this).parent().hasClass("is_draft"))
		{
			$(this).parent().children().removeClass("hide");
			MMR_setDraftFlag($(this).parent().parent().attr("name"), "true");
			$(this).addClass("hide");
		}
		else
		{
			$(this).parent().children().removeClass("hide");
			MMR_setRegObjFlag($(this).parent().parent().attr("name"), "true");
			$(this).addClass("hide");
		}
	});

	$(".mmr_flag > .flagged").live("click", function(e) {
		if ($(this).parent().hasClass("is_draft"))
		{
			$(this).parent().children().removeClass("hide");
			MMR_setDraftFlag($(this).parent().parent().attr("name"), "false");
			$(this).addClass("hide");
		}
		else
		{
			$(this).parent().children().removeClass("hide");
			MMR_setRegObjFlag($(this).parent().parent().attr("name"), "false");
			$(this).addClass("hide");
		}
		
	});
	
	$(".mmr_select_all_button").live("click", function() {
		var tbl = $(this).parent().parent().parent();
		if ($(this).val()=="deselect all")
		{
			$(".mmr_select_banner", tbl).hide();
			var selectedRows = $(".mmr_select_box", tbl).filter(":checked").click().parent().parent();
			selectedRows.children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
			$(".mmr_button_row > input:enabled", $(this).parents("table.mmr_expandable_table")).attr("disabled","disabled");
			$(this).val("select all");
		}
		else
		{
			var flaggedRecordsExist = ($("tr", tbl).has("td.mmr_flag > .flagged:not(.hide)").length > 0);
			var unshownRecords = $("tr.record_row:not(:visible)", tbl).length;
			if (unshownRecords > 0)
			{
				$(".mmr_select_banner", tbl).children("td").html("There are " + unshownRecords + " more record(s) in this status category that are not visible. Do you want to <a class='mmr_select_all_link'>select these records too</a>?" + (flaggedRecordsExist ? " Alternatively, you could <a class='mmr_select_flagged_link'>only select flagged records</a>?" : ""));
				$(".mmr_select_banner", tbl).show();
			}
			else if (flaggedRecordsExist)
			{
				$(".mmr_select_banner", tbl).children("td").html("Do you want to <a class='mmr_select_flagged_link'>only select flagged records</a>?");
				$(".mmr_select_banner", tbl).show();
			}
			$(".mmr_select_box", $("tr:visible", tbl)).filter(":not(:checked)").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
			$(this).val("deselect all");
		}
		
	});
	
	
	
	$(".mmr_button_row > input").live("click", function() {
		
		if ($(this).attr('disabled')){return;}
		var targetKeys = new Array();
		var action = $(this).attr("name");
		var isPreApproval = $(this).parent().hasClass("preapproval");
		var dataSourceKey = $('#dataSourceKey').val();

		var canContinue = true;
		
		if (action == "DELETE_RECORD" || action == "DELETE_DRAFT")
		{
			$(".mmr_select_box:checked", $(this).parents("table.mmr_expandable_table")).parents(".record_row").each(function() {
				targetKeys.push($(this).attr("name"));
			});
			
			if (!confirm("You are about to delete " + targetKeys.length + " record(s). "+(action == "DELETE_DRAFT" ? "These records will be permanently deleted and cannot be restored. " : "") + "Do you want to continue?"))
			{
				return;
			}
		}
		else 
		{
		
			$(".mmr_select_box:checked", $(this).parents("table.mmr_expandable_table")).parents(".record_row").each(function() {
				targetKeys.push($(this).attr("name"));
	
				if ($(this).hasClass("erroneous"))
				{
					canContinue = false;
				}
			});
			
		}
		
		if (!canContinue && (action != 'BACK_TO_DRAFT'))
		{
			$('#mmr_datasource_alert_msg').html("One or more of the records selected contain errors. <br/><br/>Please correct these errors before continuing.");
			$.blockUI({ message: $('#mmr_datasource_alert') });
			return;
		}
		
		blockLoading();
		$.post(
			$("#elementSourceURL").val() + "task=manage_my_records&action=" + $(this).attr("name"),
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
					}
					location.reload(); 
				}
			},
			'json'
		);
		
		
	});
	
	$(".mmr_select_all_link").live("click", function() {
		var tbl = $(this).parents("table.mmr_expandable_table");
		var checkBoxes = $(".mmr_select_box", $("tr", tbl));
		$(".mmr_select_banner", tbl).children("td").html("All " + checkBoxes.length + " records selected...").parent().delay(1000).fadeOut();
		checkBoxes.filter(":not(:checked)").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
	});
	
	$(".mmr_select_flagged_link").live("click", function() {
		var tbl = $(this).parents("table.mmr_expandable_table");
		
		// Clear all checkboxes
		var checkBoxes = $(".mmr_select_box", $("tr", tbl));
		checkBoxes.filter(":checked").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
		
		$(".mmr_more_records_link", tbl).click();
		
		var flaggedRows = $("tr", tbl).has("td.mmr_flag > .flagged:not(.hide)").has("td.rowSelector > input");
		$("td.rowSelector > input", flaggedRows).filter(":not(:checked)").click().parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
		$(".mmr_select_banner", tbl).children("td").html(flaggedRows.length + " flagged record(s) selected...").parent().delay(1000).fadeOut();
		if (flaggedRows.length == 0)
		{
			$(".mmr_select_all_button", tbl).val("select all");
		}
	});
	
	
	$(".mmr_select_box").live("click", function(e) {

		if (e.originalEvent != undefined)
		{
			if ($(this).val() == "checked")
			{
				$(this).parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#F0EDEA");
			}
			else
			{
				$(this).parent().parent().children("td:not(.mmr_nohighlight)").css("background-color","#e2e2e2");
			}
		}
		
		if ($(this).val() == "checked")
		{
			$(this).val("unchecked");
		}
		else
		{
			$(this).val("checked");
		}
		
		if ($(".mmr_select_box[value=checked]", $(this).parents("table.mmr_expandable_table")).length > 0)
		{
			$(".mmr_button_row > input:disabled", $(this).parents("table.mmr_expandable_table")).removeAttr("disabled");
		}
		else
		{
			$(".mmr_button_row > input:enabled", $(this).parents("table.mmr_expandable_table")).attr("disabled","disabled");
		}
	});
	
	
	
});

function MMR_setDraftFlag(key, flag)
{
	$.get($("#elementSourceURL").val() + "task=flag_draft&data_source=" + encodeURIComponent($("#dataSourceKey").val()) + "&key=" + encodeURIComponent(key) + "&flag=" + flag);
}

function MMR_setRegObjFlag(key, flag)
{
	$.get($("#elementSourceURL").val() + "task=flag_regobj&key=" + encodeURIComponent(key) + "&flag=" + flag);
}



function MMR_toggleInfoVisible()
{
	if (MMR_datasource_info_visible == "true")
	{
		$('#mmr_datasource_information').hide();
		$('#mmr_information_show').show();
		MMR_setStatusCookie('info','false');
	}
	else
	{
		$('#mmr_datasource_information').show();
		$('#mmr_information_show').hide();
		MMR_setStatusCookie('info','true');
	}
}

function MMR_initStatusCookie()
{
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{
		// Status Cookie format:
		// bool||bool||bool
		//   ^     ^     ^
		// info    |     |
		//       unused  |
		//             unused
		
		setCookie(STATUS_COOKIE_NAME, "true||false||false", STATUS_COOKIE_TTL_DAYS);
		MMR_initStatusCookie();
	} else {
		MMR_datasource_info_visible = currentState.split("||")[0];
	}		
}

function MMR_setStatusCookie(field, value) {
	
	// Check cookie exists and get its current state
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{	
		RMD_initStatusCookie();
		currentState = getCookie(STATUS_COOKIE_NAME);
	} 
		
	// Update the appropriate value
	curVals = currentState.split("||");
		
	if (field == "info") {
		curVals[0] = value;
	} 
	
	MMR_datasource_info_visible = curVals[0];
		
	// Reset the cookie
	setCookie(	STATUS_COOKIE_NAME, 
				curVals.join("||"), 
				STATUS_COOKIE_TTL_DAYS);
	
}


function MMR_getFromStatusCookie(field) {
	
	// Check cookie exists and get its current state
	var currentState = '';
	if( (currentState = getCookie(STATUS_COOKIE_NAME)) == '' )
	{	
		RMD_initStatusCookie();
		currentState = getCookie(STATUS_COOKIE_NAME);
	} 
		
	// Update the appropriate value
	curVals = currentState.split("||");
		
	if (field == "info") {
		return curVals[0];
	} 
	
}


function blockLoading()
{
	// change this is regmydata too
	$('#mmr_datasource_alert_msg').html("Sending message to server. Please wait...<br/><br/>" +
										"<div style='text-align:center'>" +
											"<img src='../../_images/_icons/ajax_loading.gif' />" +
										"</div>"); 
	$.blockUI({ message: $('#mmr_datasource_alert') });
}

function blockWithMessage(msg)
{
	$('#mmr_datasource_alert_msg').html(msg);
	$.blockUI({ message: $('#mmr_datasource_alert') });
}