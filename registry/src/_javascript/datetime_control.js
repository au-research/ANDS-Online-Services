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
// DateTime Control Globals
// -----------------------------------------------------------------------------
// Format mask constants.
// These strings will be treated in a case-sensitive way.
// The code assumes that all masks requiring a date will include 'YYYY', 'MM', and 'DD'.
// The code also assumes that all masks requiring a time will include 'hh' and 'mm'.
// There is no support for time resolution greater than minutes (ie. no seconds or fractions of seconds).
// There is no support for years before year 0.
var DCT_FORMAT_ISO8601_DATE          = 'YYYY-MM-DD';
var DCT_FORMAT_ISO8601_DATE_TIME     = 'YYYY-MM-DDThh:mm';
var DCT_FORMAT_ISO8601_DATE_TIME_UTC = 'YYYY-MM-DDThh:mmZ';
var DCT_FORMAT_ISO8601_TIME          = 'hh:mm';
var DCT_FORMAT_ISO8601_TIME_UTC      = 'hh:mmZ';
var DCT_FORMAT_ISO8601_DATETIME      = 'YYYY-MM-DDThh:mm';
var DCT_FORMAT_ISO8601_DATETIME_UTC  = 'YYYY-MM-DDThh:mmZ';
var DCT_FORMAT_AU_DATE               = 'DD/MM/YYYY';
var DCT_FORMAT_AU_DATETIME           = 'DD/MM/YYYY hh:mm AM';
var DCT_FORMAT_US_DATE               = 'MM/DD/YYYY';
var DCT_FORMAT_US_DATETIME           = 'MM/DD/YYYY hh:mm AM';
var DCT_FORMAT_TIME                  = 'hh:mm AM';

// Icon image file constants.
var DCT_ICON_ID_SUFFIX = '_dctIcon';
var DCT_ICON_INACTIVE = 'dct_icon_inactive.gif';
var DCT_ICON_ACTIVE = 'dct_icon_active.gif';
var DCT_IMAGE_RETURN = 'dct_return.gif';
var DCT_IMAGE_NEXT = 'dct_next.gif';
var DCT_IMAGE_PREV = 'dct_prev.gif';
var DCT_IMAGE_CLOSE= 'dct_close.gif';
var DCT_IMAGE_CLOCK = 'dct_clock.png';

// Control state globals.
var inputField_Id = null;
var dctControl = null;
var dctInputField = null;
var dctControlDate = null;   // The date/time tracked by the control.
var dctCalendarMonth = null; // The month currently displayed by the calendar.
var dctCalendarYear = null;  // The year currently displayed by the calendar.
var dctImagesRootPath = '';  // The path to the root of control images & icons/
                             // This can be set in script just before the dateTimeControl div
							 // with a call to dctSetImagePath(path)
var dctFormat = '';
var dctStartDayOfWeek = 0;


// DateTime Control Functions
// -----------------------------------------------------------------------------
function dctInit(imagePath)
{
	dctImagesRootPath = imagePath;
	document.write('<div id="dateTimeControl" style="display: none; position: absolute; z-index: 100;"></div>');
}

function dctGetDateTimeControl(inputFieldId, format)
{

	$(function() {
		$( "#" + inputFieldId ).datetimepicker({
			changeMonth: true,
			changeYear: true,			
		    showOtherMonths: true,
		    useTimeSelects: true,
		    selectOtherMonths: true,
		    showButtonPanel: true,
		    showTimezone: false,
			showSecond: false,
		    timeFormat: "hh:mm:ssZ",		    
		    yearRange: "c-100:c+100",
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: "../_images/dct_icon_inactive.gif",
			buttonImageOnly: true
		});
	});
	var inputField = getObject(inputFieldId);
	inputField.size = format.length;
	inputField.maxLength = 30;
}
function dctGetDateTimeControlSpec(inputFieldId, format, destElement)
{
	
	$(function() {
		$( "#" + inputFieldId ).datetimepicker({
			changeMonth: true,
			changeYear: true,			
		    showOtherMonths: true,
		    useTimeSelects: true,
		    selectOtherMonths: true,
		    showButtonPanel: true,
		    showTimezone: false,
			showSecond: false,
		    timeFormat: "hh:mm:ssZ",		    
		    yearRange: "c-100:c+100",
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: "../_images/dct_icon_inactive.gif",
			buttonImageOnly: true,
			onSelect: function(dateText, inst) {populateFormatExistence(inputFieldId);}
		});
		
		function populateFormatExistence(inputFieldId){
			var value = inputFieldId.replace("_value","_dateFormat");
			if($('#'+value)){
				formatField = getObject(value);
				formatField.value = "W3CDTF";
			}
		}		
	});
	var inputField = getObject(inputFieldId);
	inputField.size = format.length;
	inputField.maxLength = format.length;
}
function dctGetDateTimeControlSpec2(inputFieldId, format, destElement)
{
	// Setup the control icon.
	inputField_Id = inputFieldId;
	var title = "";
	format = format.replace("X","");
	if( format.indexOf('YYYY') > -1 )
	{
		title += 'Calendar';
	}
	if( format.indexOf('hh') > -1 )
	{
		if( title.length > 0 )
		{
			title += ' and ';
		}
		title += 'Time';
	}
	var onclick = "dctDisplayDateTimeControl('" + inputFieldId + "', '" +  format + "')";
	document.getElementById(destElement).innerHTML = '<img class="dctIcon" src="' + dctImagesRootPath + DCT_ICON_INACTIVE + '" id="' + inputFieldId + DCT_ICON_ID_SUFFIX + '" onclick="' + onclick + '" alt="" title="' + title + '" style="vertical-align: middle;" />';
	// Set the input field size and maxlength.
	var inputField = getObject(inputFieldId);
	inputField.size = format.length;
	inputField.maxLength = format.length;
}

function dctCloseDateTimeControl()
{	
	var title = "";
	if( dctFormat.indexOf('YYYY') > -1 )
	{
		title += 'Calendar';
	}
	if( dctFormat.indexOf('hh') > -1 )
	{
		if( title.length > 0 )
		{
			title += ' and ';
		}
		title += 'Time';
	}
	var dateTimeControl = getObject('dateTimeControl');
	displayObject(dateTimeControl, 'none');
	dateTimeControl.innerHTML = '';
	updateImage(dctInputField.id + DCT_ICON_ID_SUFFIX, dctImagesRootPath + DCT_ICON_INACTIVE, title);
	dctUpdateInputFieldValue();
}

function dctDisplayDateTimeControl(inputFieldId, format)
{
	var dateTimeControl = getObject('dateTimeControl');
	var inputField = getObject(inputFieldId);
	var iconId = inputFieldId + '_dctIcon';
	inputField_Id = inputFieldId;
	//  alert("#" + inputFieldId);
	//$("#" + inputFieldId + DCT_ICON_ID_SUFFIX).click(function(event){
	//	event.stopImmediatePropagation();
    //
	//});
	
	
	if( inputField && dateTimeControl )
	{
		if( !isObjectDisplayed(dateTimeControl) )
		{ // The control is not displayed.
			// Initialise and display the control.
			dctInitDateTimeControl(dateTimeControl, inputField, format);	
		}
		else
		{ // The control is displayed, so hide it.
			dctCloseDateTimeControl();
		}
		
		if( inputField.id != dctInputField.id )
		{ // The control is displayed at a different input field.
			// Initialise and display the control.
			dctInitDateTimeControl(dateTimeControl, inputField, format);
		}
	}
}

function dctInitDateTimeControl(dateTimeControl, inputField, format)
{
	// Set the state globals.
	// ----------------------------------------
	// The control object.
	dctControl = dateTimeControl;
	// The calling input field.
	dctInputField = inputField;
	// Set the control date/time format.
	dctFormat2 = format;
	dctFormat = format.replace("X","");	
	// Set the initial control date and time to the current local time.
	dctControlDate = new Date();
	// Reset hours, minutes, seconds, etc to 0 -- no need for this granularity
	dctControlDate.setUTCHours(0, 0, 0, 0);
	
	
	
	// If the format specifies UTC date/time (via the string 'Z' for zulu--zero longitude)
	// then set the current date/time to the UTC date/time.
	if( dctFormat.indexOf('Z') > -1 )
	{
		dctControlDate.setFullYear(dctControlDate.getUTCFullYear());
		dctControlDate.setMonth(dctControlDate.getUTCMonth());
		dctControlDate.setDate(dctControlDate.getUTCDate());
		dctControlDate.setHours(dctControlDate.getUTCHours());
		dctControlDate.setMinutes(dctControlDate.getUTCMinutes());		
	}
	
	// Setup the calendar according to the settings passed from dctGetDateTimeControl
	// ie any value in the calling field, and the date/time format.
	var currentValue = dctInputField.value;
	var isValid = true;

	if( currentValue != '' )
	{
		if( isValid = dctIsValidDateTime(currentValue, dctFormat) )
		{
			// Set the date/time with the values from the calling field.
			var dateYear = dctGetValueForFormat(currentValue, dctFormat, 'YYYY');
			var dateMonth = dctGetValueForFormat(currentValue, dctFormat, 'MM');
			var dateDate = dctGetValueForFormat(currentValue, dctFormat, 'DD');
			if( dateMonth != null )
			{
				dctControlDate.setFullYear(dateYear, dateMonth-1, dateDate);
			}
			var timeAM = dctGetStringForFormat(currentValue, dctFormat, 'AM');
			var timeHours = dctGetValueForFormat(currentValue, dctFormat, 'hh');
			var timeMinutes = dctGetValueForFormat(currentValue, dctFormat, 'mm');
			if( timeHours != null )
			{
				if( timeAM != null )
				{
					timeHours = timeHours%12;
				}
				if( timeAM == 'PM' )
				{
					timeHours += 12;
				}
				dctControlDate.setHours(timeHours, timeMinutes, 0, 0);
			}
		}
		// If we can't read a valid datetime form the calling field
		// then the control will use the current date and time.
	}
	
	if( isValid )
	{
		// Set the state globals for the calendar display.
		dctCalendarMonth = dctControlDate.getMonth();
		dctCalendarYear = dctControlDate.getFullYear();

		// Draw the contents of the control.
		dctDrawDateTimeControl();
	
		// Display the control and update the icon.
		displayObjectNear(dateTimeControl, getObject(inputField.id + DCT_ICON_ID_SUFFIX), RELATIVE_POSITION_ABOVE_RIGHT);
		updateImage(inputField.id + DCT_ICON_ID_SUFFIX, dctImagesRootPath + DCT_ICON_ACTIVE, 'Close');
		// Set the field value.
		dctUpdateInputFieldValue();
	}
	else
	{
		// Select the field with invalid data.
		inputField.select();
		// Alert the user that the field has invalid data.
		alert('Date/time is not valid for required format:\n' + dctFormat);
	}
}

function dctRedrawDateTimeControl()
{
	// Control has already been initialised, just some values changed so...
	dctDrawDateTimeControl();
}

function dctResetStartDayOfWeek(day)
{
	dctStartDayOfWeek = day;
	dctRedrawDateTimeControl();
}

function dctResetToThisMonth()
{
	var date = new Date();
	dctCalendarMonth = date.getMonth();
	dctCalendarYear = date.getFullYear();
	dctRedrawDateTimeControl();
}

function dctDrawDateTimeControl()
{
	var text = '';

	// Inner Container
	text += '<div id="dctInnerContainer">';
	
	// Close Bar
	text += '<div id="dctCloseBar" onmousedown="startMove(event, dctControl)"><img src="' + dctImagesRootPath + DCT_IMAGE_CLOSE + '" alt="" title="Close" id="dctClose" onclick="dctCloseDateTimeControl()" /></div>';
	
	// Date Controls
	// -------------------------------------------------------------------------
	if( dctFormat.indexOf('YYYY') > -1 )
	{
		var SECOND = 1000;
		var MINUTE = parseInt(SECOND * 60, 10);
		var HOUR = parseInt(MINUTE * 60, 10);
		var DAY = parseInt(HOUR * 24, 10);
		
		var onclick = '';
		var i = 0;
		var thisDate = null;
		var dateToday = new Date();
		dateToday.setHours(0);
		dateToday.setMinutes(1);
		dateToday.setSeconds(0);
		
		text += '<div id="dctDateControls">';		
		// Return to the current month.
		onclick = 'dctResetToThisMonth()';
		text += '<img id="dctReturn" onclick="' + onclick + '"';
		text += ' src="' + dctImagesRootPath + DCT_IMAGE_RETURN + '" alt="" title="Go to ';
		text += dctGetMonthName(dateToday.getMonth(), false) + ' ' + dateToday.getFullYear();
		text += '" />&nbsp;';		
		text += dctGetMonthSelect(dctCalendarMonth);
		text += dctGetYearSelect(dctCalendarYear);
		text += '</div>';
	
		// Calendar Title
		//text += '<div id="dctCalendarTitle">';
		text += '<table class="dctCalendarTitleTable" cellspacing="0" cellpadding="0"><tr>';
	
		// Previous Month
		var prevNextYear = dctCalendarYear;
		var prevNextMonth = dctGetNextPrevMonth(dctCalendarMonth, -1);
		onclick = "dctUpdateCalendarDate('MM', " +  prevNextMonth + "); dctRedrawDateTimeControl();";
		if( dctCalendarMonth == 0 )
		{ // It's January, so the previous month is in the previous year.
			prevNextYear--;
			onclick = "dctUpdateCalendarDate('YYYY'," + prevNextYear + "); " + onclick;
		}
		text += '<td width="25%" class="dctNextPrev" onclick="' + onclick + '" title="' + dctGetMonthName(prevNextMonth, false) + ' ' + prevNextYear + '">';
		text += dctGetMonthName(prevNextMonth, true);
		text += '<img src="' + dctImagesRootPath + DCT_IMAGE_PREV + '" alt="" />';
		text += "</td>";
	
		// Title
		text += '<td width="50%" id="dctCalendarTitle">';
		text += dctGetMonthName(dctCalendarMonth, false);
		text += '&nbsp;' + dctCalendarYear;
		text += '</td>';
	
		// Next Month
		prevNextMonth = dctGetNextPrevMonth(dctCalendarMonth, +1);
		onclick = "dctUpdateCalendarDate('MM', " +  prevNextMonth + "); dctRedrawDateTimeControl();";
		if( dctCalendarMonth == 11 )
		{ // It's December, so the next month is in the next year.
			prevNextYear++;
			onclick = "dctUpdateCalendarDate('YYYY'," + prevNextYear + "); " + onclick;
		}
		text += '<td width="25%" class="dctNextPrev" onclick="' + onclick + '" title="' + dctGetMonthName(prevNextMonth, false) + ' ' + prevNextYear + '">';
		text += '<img src="' + dctImagesRootPath + DCT_IMAGE_NEXT + '" alt="" />';
		text += dctGetMonthName(prevNextMonth, true);
		text += "</td>";
		text += '</tr></table>';
		//text += '</div>';
	
		// The Calendar
		text += '<table class="dctCalendarTable" cellpadding="0" cellspacing="0">';
		
		// Days of the week.
		// ---------------------------------
		var dayHeads = new Array(7);
		dayHeads[0] = "Su";
		dayHeads[1] = "Mo";
		dayHeads[2] = "Tu";
		dayHeads[3] = "We";
		dayHeads[4] = "Th";
		dayHeads[5] = "Fr";
		dayHeads[6] = "Sa";
	
		var c = dctStartDayOfWeek;
		text += '<tr>';
		for( i=0; i < 7; i++ )
		{
			onclick = 'dctResetStartDayOfWeek(' +  c + ')';
			if( c == 0 || c == 6 )
			{
				text += '<td width="15%" class="dctWeekendTitle" onclick="' + onclick + '">' + dayHeads[c] + '</td>';
			} else {
				text += '<td width="14%" class="dctWeekdayTitle" onclick="' + onclick + '">' + dayHeads[c] + '</td>';
			}
			c = (c + 1) % 7;
		}
		text += '</tr>';
		
		// Dates in the month.
		// ---------------------------------
		// Calculate how many days before the first of this month we need to go back
		// to start the 7 x 6 table with the correct date.
		var daysBeforeFirst = 0;
		// Set thisDate to the first of the calendar month.
		thisDate = new Date();
		thisDate.setMinutes(1);
		thisDate.setHours(0);
		thisDate.setDate(1);
		thisDate.setMonth(dctCalendarMonth);
		thisDate.setFullYear(dctCalendarYear);
		for( i=0; i < 7; i++ )
		{
			if( thisDate.getDay() == i )
			{
				daysBeforeFirst = ((i - dctStartDayOfWeek) + 7) % 7;
			}
		}
		
		// Set thisDate to the correct date for the start of the table using 
		// the result of the above calculation.
		thisDate.setTime(thisDate.getTime() - (daysBeforeFirst * DAY) + (12 * HOUR));		
		
		// Fill each row in the calendar table.
		var cellClass = "";
		var cellWidth = "";
		var titleNote = "";
		var titleSelected = "";
		for( var k=1; k <= 6; k++ )
		{
			text += '<tr>';
			// Fill each cell in this row.
			for( i=0; i < 7; i++ )
			{
				// Set the cell class.
				cellWidth="14%";
				if( thisDate.getMonth() == dctCalendarMonth )
				{
					cellClass = "dctThisMonth"; // It's this month.
				}
				else
				{
					cellClass = "dctOtherMonth"; // It's another month.
				}
				if( thisDate.getDay() == 0 || thisDate.getDay() == 6 )
				{
					cellClass += "Weekend"; // It's the weekend.
					cellWidth="15%";
				}
				else
				{
					cellClass += "Weekday"; // It's a weekday.
				}
				
				if( thisDate.getDate() == dctControlDate.getDate() && thisDate.getMonth() == dctControlDate.getMonth() && thisDate.getFullYear() == dctControlDate.getFullYear() )
				{
					cellClass = "dctDateSelected"; // It's the selected date.
					titleSelected = " (selected)";
				}
				
				if( thisDate.getDate() == dateToday.getDate() && thisDate.getMonth() == dateToday.getMonth() && thisDate.getFullYear() == dateToday.getFullYear() )
				{
					cellClass += "Today"; // It's today.
					titleNote = " (today)";
				}
	
				// Write this day.
				onclick = "dctUpdateControlDate('YYYY', " + thisDate.getFullYear() + ");dctUpdateControlDate('MM', " + thisDate.getMonth() + ");dctUpdateControlDate('DD', " + thisDate.getDate() + ");dctCloseDateTimeControl();";
				titleNote = thisDate.getDate() + ' ' + dctGetMonthName(thisDate.getMonth(), false) + ' ' + thisDate.getFullYear() + titleNote + titleSelected;
				text += '<td width="' + cellWidth + '" class="' + cellClass + '" onclick="' + onclick + '" title="' + titleNote + '">';
				text += '&nbsp;' + thisDate.getDate() + '&nbsp;';
				text += '</td>';
				
				// Move forward one day for the next cell.
				thisDate.setTime(thisDate.getTime() + DAY);
				cellClass="";
				width="";
				titleNote = "";
				titleSelected = "";
			}				
			text += '</tr>';
			// End row.
		}
		
		// Close the calendar table.
		text += '</table>';
	}
	// -------------------------------------------------------------------------
	// End Date Controls
	

	// Time Controls
	// -------------------------------------------------------------------------
	if( dctFormat.indexOf('hh') > -1 )
	{
		// Check format for hh to show, and AM for 12/24 hour time.
		var bln12Hour = false;
		if( dctFormat.indexOf('AM') > -1 )
		{
			bln12Hour = true;
		}
		text += '<div id="dctTimeControls">';
		text += '<img class="dctIcon" src="' + dctImagesRootPath + DCT_IMAGE_CLOCK + '" /> ';
		text += dctGetHoursSelect(dctControlDate.getHours(), bln12Hour);
		text += dctGetMinutesSelect(dctControlDate.getMinutes());
		if( bln12Hour )
		{
			text += dctGetAMSelect(dctControlDate.getHours());
		}
		text += '</div>';
	}
	
	// Time Zone Controls
	// -------------------------------------------------------------------------
	if( dctFormat2.indexOf('X') > -1 )
	{

		text += '<div id="dctTimeZoneControls">';

		text += dctGetTimeZoneSelect();
	
		text += '</div>';
	}	
	
	// End Inner Container
	text += '</div>';
	dctControl.innerHTML = text;
	text = '';
}

function dctGetDateTimeFormatString(dateObj, format)
{
	var datestring = format;
	
	var year = dateObj.getFullYear() + '';
	if( year.length < 4 )
	{
		while( year.length < 4 )
		{
			year = '0' + year;
		}
	}
	datestring = datestring.replace("YYYY", year);
	
	var months = (dateObj.getMonth()+1) + '';
	if( months.length < 2 ){ months = '0' + months; }
	datestring = datestring.replace("MM", months);
	
	var date = (dateObj.getDate()) + '';
	if( date.length < 2 ){ date = '0' + date; }
	datestring = datestring.replace("DD", date);
	
	var minutes = dateObj.getMinutes() + '';
	if( minutes.length < 2 ){ minutes = '0' + minutes; }
	datestring = datestring.replace("mm", minutes);
	
	var timeHours = dateObj.getHours();
	var timeAM = 'AM';
	if( format.indexOf('AM') > -1 )
	{
		if( timeHours >= 12 ){ 
			timeAM = 'PM';
			timeHours -= 12;
		}
		if( timeHours == 0 )
		{
			timeHours = 12;
		}
	}
	var hours = timeHours + '';
	if( hours.length < 2 ){ hours = '0' + hours; }
	datestring = datestring.replace("hh", hours);
	datestring = datestring.replace("AM", timeAM);
	
	return datestring;
}

function dctIsValidDateTime(datetime, format)
{
	isValid = true;
	// Check that datetime is in the form specified by the format mask.
	isValid = dctDatetimeMatchesFormat(datetime, format);
	
	// If the format is OK, then check the values not covered by the regex.
	if( isValid )
	{	
		// - No check required for year since it passed the numeric regex test already.
		// - Check days and months
		var dateMonth = dctGetValueForFormat(datetime, format, 'MM');
		var dateDate = dctGetValueForFormat(datetime, format, 'DD');
		if( dateMonth != null )
		{ // Then days and months are required for this format.
			// Max days in each month.
			var daysArry = new Array(0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

			if( dateMonth < 1 || dateMonth > 12 )
			{
				isValid = false; // Month is out of range 1-12.
			}
			else
			{
				if( dateDate < 1 || dateDate > daysArry[dateMonth] ){
					isValid = false; // Date is out of range for this month.				
				}
			}
		}
		// - Check hours and minutes (and 24 or 12 hour time)
		var timeAM = dctGetStringForFormat(datetime, format, 'AM');
		var timeHours = dctGetValueForFormat(datetime, format, 'hh');
		var timeMinutes = dctGetValueForFormat(datetime, format, 'mm');
		if( timeHours != null )
		{ // Then hours and minutes are required for this format.
			// Assume 24 hour time.
			var maxHours = 23;
			var minHours = 0;
			if( timeAM != null ){ // Then it's 12 hour time.
				maxHours = 12;
				minHours = 1;
			}
			if( timeHours > maxHours || timeHours < minHours )
			{
				isValid = false;
			}
			if( timeMinutes > 59 || timeMinutes < 0 )
			{
				isValid = false;
			}
		}
	}
	return isValid;
}

function dctDatetimeMatchesFormat(datetime, format)
{
	// Turn the format mask into a regular expression 
	// and check datetime for a match.
	var match = format;
	match = match.replace("YYYY", "[0-9]{4}");
	match = match.replace("MM", "[0-9]{2}");
	match = match.replace("DD", "[0-9]{2}");
	match = match.replace("hh", "[0-9]{2}");
	match = match.replace("mm", "[0-9]{2}");
	match = match.replace("AM", "[A|P][M]");
	match = match.replace("/", "\/");
	match = '^' + match + '$';
	var regex = new RegExp(match);
	return regex.test(datetime);
}

function dctGetValueForFormat(string, format, match)
{
	// Return null if this format doesn't require this match.
	var value = null;
	var searchstring = dctGetStringForFormat(string, format, match);
	if( searchstring != null )
	{
		value = parseInt(searchstring, 10);
		if( isNaN(value) )
		{
			// Return -1 if this value is expected but is not a number.
			value = -1;
		}
	}
	return value;
}

function dctGetStringForFormat(string, format, match)
{
	// Return null if this format doesn't require this match.
	var searchstring = null;
	
	var startIndex = format.indexOf(match);
	if( startIndex > -1 )
	{
		if( startIndex + match.length <= string.length )
		{
			searchstring = string.substring(startIndex, startIndex + match.length);
		}
		else
		{
			// Return an empty string as this substring is expected but couldn't be found.
			searchstring = '';
		}
	}
	return searchstring;
}

function dctUpdateInputFieldValue()
{
	dctInputField.value = dctGetDateTimeFormatString(dctControlDate, dctFormat);
	checkDateDiff(inputField_Id);
}


function dctUpdateCalendarDate(field, value, resetDate)
{
	switch( field )
	{
		case 'YYYY':
			dctCalendarYear = parseInt(value, 10);
			dctUpdateControlDate(field, value);
			break;
		
		case 'MM':
			dctCalendarMonth = parseInt(value, 10);
			dctUpdateControlDate(field, value);
			break;
	}
	if (resetDate) 
	{
		dctUpdateControlDate('DD',parseInt('1', 10));
		dctControlDate.setDate(1);
		dctUpdateInputFieldValue();
	}
}

function dctUpdateControlDate(field, value)
{
	switch( field )
	{
		case 'hh':
			dctControlDate.setHours(parseInt(value, 10));
			dctUpdateInputFieldValue();
			break;
			
		case 'AM':
			var timeHours = dctControlDate.getHours();
			if( value == 'AM' )
			{
				// Changed from PM to AM.
				timeHours -= 12;
			}
			else // PM
			{
				// Changed from AM to PM.
				timeHours += 12;
			}
			dctControlDate.setHours(timeHours);
			dctUpdateInputFieldValue();
			break;
			
		case 'mm':
			dctControlDate.setMinutes(parseInt(value, 10));
			dctUpdateInputFieldValue();
			break;
			
		case 'YYYY':
			dctControlDate.setFullYear(parseInt(value, 10));
			break;
			
		case 'MM':
			dctControlDate.setMonth(parseInt(value, 10));
			break;
			
		case 'DD':
			dctControlDate.setDate(parseInt(value, 10));
			break;
		case 'Z':
			var newNum = parseFloat(value, 10);
			if(newNum>0)
			{
				var theString = '&nbsp;&nbsp;&nbsp;(GMT +' + newNum.toFixed(2) + ')'
			}else{
				var theString = '&nbsp;&nbsp;&nbsp;(GMT ' + newNum.toFixed(2) + ')'				
			}
			document.getElementById('gmtZone').innerHTML =  theString;
			document.getElementById('theZone').value = newNum;
			break;			
	}
}

function dctGetYearSelect(dateYear)
{
	var start = dateYear - 100;
	var end = dateYear + 100;
	var temp = "";
	// We only update the calendar date with a change here.
	var selectStr = '<select id="dctYearSelect" onchange="dctUpdateCalendarDate(\'YYYY\', this.value, true); dctRedrawDateTimeControl();" class="dctSelectList" style="z-index: 100">';
	for( var i=start; i < end; i++ )
	{
		temp = i + "";
		if( temp.length < 4 )
		{
			while( temp.length < 4 )
			{
				temp = '0' + temp;
			}
		}
		selectStr += '<option value="' + temp + '"';
		if( dateYear == i ){
			selectStr += " selected=\"selected\"";
		}
		selectStr += '>' + temp + '</option>';
	}
	selectStr += "</select>";
	
	return selectStr;
}

function dctGetMonthSelect(dateMonth)
{
	// We only update the calendar date with a change here.
	var selectStr = '<select id="dctMonthSelect" onchange="dctUpdateCalendarDate(\'MM\', this.value, true); dctRedrawDateTimeControl();" class="dctSelectList" style="z-index: 100">';
	for( var i=0; i < 12; i++ )
	{
		selectStr += '<option value="' + i + '"';
		if( dateMonth == i )
		{
			selectStr += " selected=\"selected\"";
		}
		selectStr += '>' + dctGetMonthName(i, true) + '</option>';
	}
	selectStr += "</select>";
	
	return selectStr;
}

function dctGetAMSelect(timeHours)
{
	var selectStr = '<select id="dctAMSelectList" onchange="dctUpdateControlDate(\'AM\', this.value)" class="dctSelectList" style="z-index: 100">';
	var selected = "";
	if( timeHours < 12 ){ selected = " selected=\"selected\""; }
	selectStr += '<option value="AM"' + selected + '>AM</option>';
	selected = "";
	if( timeHours >= 12 ){ selected = " selected=\"selected\""; }
	selectStr += '<option value="PM"' + selected + '>PM</option>';
	selectStr += "</select>";
	
	return selectStr;
}

function dctGetHoursSelect(timeHours, bln12Hour)
{
	// Assume 24 hour time.
	var selectedHours = timeHours;
	var maxHours = 23;
	var minHours = 0;
	if( bln12Hour ){ // Then it's 12 hour time.
		maxHours = 12;
		minHours = 1;
		if( selectedHours >= 12 )
		{
			selectedHours -= 12;
		}
		if( selectedHours == 0 )
		{
			selectedHours = 12;
		}
	}
	var temp = "";

	var selectStr = '<select id="dctHoursSelectList" onchange="dctUpdateControlDate(\'hh\', this.value);" class="dctSelectList" style="z-index: 100">';
	for( var i=minHours; i <= maxHours; i++ )
	{
		temp = i + "";
		if( i < 10 )
		{
			temp = "0" + i;
		}
		selectStr += '<option value="' + temp + '"';
		if( selectedHours == i )
		{
			selectStr += " selected=\"selected\"";
		}
		selectStr += '>' + temp + '</option>';
	}
	selectStr += "</select>";
	
	return selectStr;	
}
function dctGetTimeZoneSelect()
{
	var selectStr = '<select id="dctTimeZoneSelectList" onchange="dctUpdateControlDate(\'Z\', this.value);" class="dctSelectList" style="z-index: 100;width:170px">';
	selectStr += '<option value="11.0">(GMT +11:00) EST Australia</option>';
	selectStr += '<option value="-12.0">(GMT -12:00) Eniwetok, Kwajalein</option>';
	selectStr += '<option value="-11.0">(GMT -11:00) Midway Island, Samoa</option>';
	selectStr += '<option value="-10.0">(GMT -10:00) Hawaii</option>';
	selectStr += '<option value="-9.0">(GMT -9:00) Alaska</option>';
	selectStr += '<option value="-8.0">(GMT -8:00) Pacific Time (US &amp; Canada)</option>';
	selectStr += '<option value="-7.0">(GMT -7:00) Mountain Time (US &amp; Canada)</option>';
	selectStr += '<option value="-6.0">(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>';
	selectStr += '<option value="-5.0">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>';
	selectStr += '<option value="-4.0">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>';
	selectStr += '<option value="-3.5">(GMT -3:30) Newfoundland</option>';
	selectStr += '<option value="-3.0">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>';
	selectStr += '<option value="-2.0">(GMT -2:00) Mid-Atlantic</option>';
	selectStr += '<option value="-1.0">(GMT -1:00 hour) Azores, Cape Verde Islands</option>';
	selectStr += '<option value="0.0">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>';
	selectStr += '<option value="1.0">(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>';
	selectStr += '<option value="2.0">(GMT +2:00) Kaliningrad, South Africa</option>';
	selectStr += '<option value="3.0">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>';
	selectStr += '<option value="3.5">(GMT +3:30) Tehran</option>';
	selectStr += '<option value="4.0">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>';
	selectStr += '<option value="4.5">(GMT +4:30) Kabul</option>';
	selectStr += '<option value="5.0">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>';
	selectStr += '<option value="5.5">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>';
	selectStr += '<option value="5.75">(GMT +5:45) Kathmandu</option>';
	selectStr += '<option value="6.0">(GMT +6:00) Almaty, Dhaka, Colombo</option>';
	selectStr += '<option value="7.0">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>';
	selectStr += '<option value="8.0">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>';
	selectStr += '<option value="9.0">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>';
	selectStr += '<option value="9.5">(GMT +9:30) Adelaide, Darwin</option>';
	selectStr += '<option value="10.0">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>';
	selectStr += '<option value="11.0">(GMT +11:00) EST Australia, Magadan, Solomon Islands, New Caledonia</option>';
	selectStr += '<option value="12.0">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>';
	selectStr += "</select>";
	
	return selectStr;	
}
function dctGetMinutesSelect(timeMinutes)
{
	var temp = "";

	var selectStr = '<select id="dctMinutesSelectList" onchange="dctUpdateControlDate(\'mm\', this.value)" class="dctSelectList" style="z-index: 100">';
	for( var i=0; i <= 59; i++ )
	{
		temp = i + "";
		if( i < 10 )
		{
			temp = "0" + i;
		}
		selectStr += '<option value="' + temp + '"';
		if( timeMinutes == i )
		{
			selectStr += " selected=\"selected\"";
		}
		selectStr += '>' + temp + '</option>';
	}
	selectStr += "</select>";

	return selectStr;	
}

function dctGetMonthName(month, blnShort)
{
	var months = new Array(12);
	months[0] = "January";
	months[1] = "February";
	months[2] = "March";
	months[3] = "April";
	months[4] = "May";
	months[5] = "June";
	months[6] = "July";
	months[7] = "August";
	months[8] = "September";
	months[9] = "October";
	months[10] = "November";
	months[11] = "December";
	
	var aMonths = new Array(12);
	aMonths[0] = "Jan";
	aMonths[1] = "Feb";
	aMonths[2] = "Mar";
	aMonths[3] = "Apr";
	aMonths[4] = "May";
	aMonths[5] = "Jun";
	aMonths[6] = "Jul";
	aMonths[7] = "Aug";
	aMonths[8] = "Sep";
	aMonths[9] = "Oct";
	aMonths[10] = "Nov";
	aMonths[11] = "Dec";
	
	if( !blnShort )
	{
		return months[month];
	}
	else
	{
		return aMonths[month];
	}
}

function dctGetNextPrevMonth(month, offset)
{
	var newMonth = (month+offset)%12;
	if( newMonth < 0 )
	{
		newMonth += 12;
	}
	return newMonth;
}
