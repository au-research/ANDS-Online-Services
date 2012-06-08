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
var rootAppPath = '';
function setRootPath(path)
{
	rootAppPath = path;
}


function recordOver(rowId, excludeFirstField)
{
	var objTableRow = document.getElementById(rowId);
	var fields = objTableRow.getElementsByTagName('td');
	var startIndex = 0;
	if( excludeFirstField ){ startIndex = 1; }
	for( var i=startIndex; i < fields.length; i++ )
	{
		if( fields[i].style.backgroundColor == '' )
		{
			fields[i].style.backgroundColor = '#ffffff';
		}
	}
	return;
}

function recordOut(rowId, excludeFirstField)
{
	var objTableRow = document.getElementById(rowId);
	var fields = objTableRow.getElementsByTagName('td');
	var startIndex = 0;
	if( excludeFirstField ){ startIndex = 1; }
	for( var i=startIndex; i < fields.length; i++ )
	{
		if( fields[i].style.backgroundColor == '#ffffff' || fields[i].style.backgroundColor == 'rgb(255, 255, 255)' )
		{
			fields[i].style.backgroundColor = '';
		}
	}
	return;
}



function nlaPushCheck()
{
	if(document.getElementById('push_to_nla').value == 1)
	{
		var answer = confirm("By marking the checkbox 'Party records to NLA ?'  you have agreed\n" +
				" \xb7 that you want to have NLA party identifiers assigned to the each of the persons" +
				" and groups you have described using RIF-CS party records in your data source\n" +
				" \xb7 that you will need to make arrangements for manual matching of records using the Trove Identities Manager\n\n" +
				" See http://ands.org.au/guides/ardc-party-infrastructure-awareness.html.")
		if(!answer){
			return false;
		}
		
	}
	// This submit check will now also check if the contributor pages have been reset from non to either auto or manual
	var theOld = document.getElementById('currentPage').innerHTML;
	var theNew = '5';
	for (i=0;i<document.forms[0].institution_pages.length;i++) {
		if (document.forms[0].institution_pages[i].checked) {
			theNew = document.forms[0].institution_pages[i].value;
		}
	}

	if(theOld  == '0' && (theNew == '1' || theNew == '2'))
	{
		var answer = confirm("The Contributor home page will be a public web document representing your organisation.\nANDS advises that you should use only approved text and consult apprpriate authorities within your organisation.")
		if(!answer){
			return false;
		}
		
	}

	return true;
}

function toggle_checkbox(id)
{
	var inputFieldId = id.replace(/_image/,"");
	if(document.getElementById(inputFieldId).value == 1)
		{
		if(inputFieldId=='push_to_nla')
			{
			document.getElementById('isil_value_row').style.display="none";						
			}
		if(inputFieldId=='create_primary_relationships')
			{
			document.getElementById('key_value_row_1').style.display="none";
			
			}		
		document.getElementById(inputFieldId).value = 0;
		document.getElementById(id).src="../_images/gray_unchecked.png";
		}
	else
		{
		if(inputFieldId=='push_to_nla')
			{
			document.getElementById('isil_value_row').style.display="";						
			}
		if(inputFieldId=='create_primary_relationships')
			{
			document.getElementById('key_value_row_1').style.display="";
				
			}
		document.getElementById(inputFieldId).value = 1;
		document.getElementById(id).src="../_images/gray_checked.png";
		}
}
/*
 * 
 * 
		<input type="hidden" id="numRegistryObjectsApproved" name="numRegistryObjectsApproved" value="0"/>					
		<input type="hidden" id="MORE_WORK_REQUIRED" name="MORE_WORK_REQUIRED" value="0"/>
		<input type="hidden" id="DRAFT" name="DRAFT" value="90"/>
		<input type="hidden" id="SUBMITTED_FOR_ASSESSMENT" name="SUBMITTED_FOR_ASSESSMENT" value="0"/>

		<input type="hidden" id="ASSESSMENT_IN_PROGRESS" name="ASSESSMENT_IN_PROGRESS" value="0"/>
 * 
 */
function show_info(id)
{
	var inputFieldId = id.replace(/_image/,"");
	var oldValueId = id.replace(/_image/,"_old");
	if($('#'+ inputFieldId).val() == $('#'+ oldValueId).val() )
	{
		if(inputFieldId == 'auto_publish')
		{
			if($('#'+ inputFieldId).val() == 1)
			{
				var userChoice = confirm("Unchecking the ‘Manually Publish Records’ checkbox will cause " + $('#numRegistryObjectsApproved').val() + " record(s) to be automatically published.\n\n" +
										"It will also cause any future approved records to be published automatically.\n" +
										"This means your records will be publically visible in \nResearch Data Australia immediately after being approved.");
				if(userChoice == true)
				{
				toggle_checkbox(id);
				}	

			}
			else
			{
				alert("Checking the ‘Manually Publish Records’ checkbox \nwill require you to manually publish your approved records\n via the Manage My Records screen.");
				toggle_checkbox(id);
			}

		}
		if(inputFieldId == 'qa_flag')
		{
			if($('#'+ inputFieldId).val() == 1)
			{
				var futureStatus = ' Published ';
				if($('#auto_publish').val() == 1)
				{
					futureStatus = ' Approved ';
				}
					var userChoice = confirm("Unchecking the ‘Quality Assessment Required’ checkbox will cause\n" +
											"" + $('#SUBMITTED_FOR_ASSESSMENT').val() + " SUBMITTED_FOR_ASSESSMENT\n"+
											"" + $('#ASSESSMENT_IN_PROGRESS').val() + " ASSESSMENT_IN_PROGRESS\n"+
											"record(s) to be automatically " + futureStatus + ".\n" + 
											"It will also prevent any future records from being sent through the Quality Assessment workflow.\n"+
											"Do you want to continue?");
					if(userChoice == true)
					{
					toggle_checkbox(id);
					}
				

			}
			else
			{
				alert("Checking the ‘Quality Assessment Required’ checkbox \nwill send any records entered into the ANDS registry \nfrom this data source through the Quality Assessment workflow.");
				toggle_checkbox(id);
			}		
		}
	}
	else
	{
		toggle_checkbox(id);
	}
	
}