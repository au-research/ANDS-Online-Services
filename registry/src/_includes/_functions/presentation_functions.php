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

// Global Presentation Variables
// -----------------------------------------------------------------------------
// Theme variables and their default settings.
$gAppLogoImagePath = eIMAGE_ROOT.'_logos/logo_EMPTY.gif';
$gAppMarginClass = 'marginLeftGrey';
$gAppTitleTextColour = '#999999';

// Form and output variables and constants
$gChosen = "";
define("gITEM_CHECK", ' checked="checked"');
define("gITEM_SELECT", ' selected="selected"');

define("gDATETIME", 0);
define("gDATE", 1);
define("gTIME", 2);

define("gERROR_CLASS", ' class="errorText"');

define("gPAG_CONTROL_PATH", eIMAGE_ROOT."_controls/_pagination_control/");

// Application stylesheets.
$gApplicationStylesheets = array();

// Presentation Functions
// -----------------------------------------------------------------------------
function setTheme($theme)
{
	global $eThemes;
	
	global $gAppLogoImagePath;
	global $gAppMarginClass;
	global $gAppTitleTextColour;

	$gAppLogoImagePath = $eThemes[$theme][0];
	$gAppMarginClass = $eThemes[$theme][1];
	$gAppTitleTextColour = $eThemes[$theme][2];
}

function importApplicationStylesheet($uri)
{
	global $gApplicationStylesheets;
	$gApplicationStylesheets[count($gApplicationStylesheets)] = $uri;
}

function printApplicationStylesheets()
{
		global $gApplicationStylesheets;
		
		foreach($gApplicationStylesheets as $uri)
		{
			print("@import url(\"$uri\");\n");
		}
}

// Form and output functions
// -----------------------------------------------------------------------------
function drawDateTimeInput($fieldNameAndID, $unsafeValue, $format)
{
	print '<input type="text" size="20" maxlength="20" name="'.$fieldNameAndID.'" id="'.$fieldNameAndID.'" value="'.esc($unsafeValue).'" />'."\n";
	print '<script type="text/javascript">dctGetDateTimeControl(\''.$fieldNameAndID.'\', \''.$format.'\')</script>';
	print '&nbsp;<span class="inputFormat">'.$format.'</span>';
}

function drawDateTimeZoneInput($fieldNameAndID, $unsafeValue, $format)
{
	print '<input type="text" size="20" maxlength="30" name="'.$fieldNameAndID.'" id="'.$fieldNameAndID.'" value="'.esc($unsafeValue).'" readonly="readonly"/>'."\n";
	print '<script type="text/javascript">dctGetDateTimeControl(\''.$fieldNameAndID.'\', \''.$format.'\')</script>';
	print '&nbsp;<span class="inputFormat">'.str_replace("X","",$format.'</span>');
}
function drawMonthYearInput($field1NameAndID, $unsafeValue1,$field2NameAndID, $unsafeValue2)
{
	if(!$unsafeValue1) $unsafeValue1=date("m");
	if(!$unsafeValue2) $unsafeValue2=date("Y");
		
	print '<select name="'.$field1NameAndID.'" id="'.$field1NameAndID.'"  />';
	 for($i=1;$i<13;$i++)
	{ 
		print('<option value="'.$i.'"');
		if($i==intval($unsafeValue1)) print(" selected"); 
		print('>'.date("M",mktime($hour=null,$minute=null,$second=null,$month=$i,$day=date("d"),$year=date("Y"))).'</option>');
	
	}
	print'</select>'."\n";
	
	print '<select name="'.$field2NameAndID.'" id="'.$field2NameAndID.'"  />';
	for($i=(date("Y")-100);$i<(date("Y")+100);$i++)
	{ 
		print('<option value="'.$i.'"');
		if($i==$unsafeValue2) print(" selected");
		print('>'.$i.'</option>');
	
	} 
	
	print'</select>'."\n";	
	print '&nbsp;<span class="inputFormat">MM -YYYY</span>';
}
function getFormattedDatetimeWithMask($datetime, $mask)
{
	$formatDate = null;
	if( strtotime($datetime) )
	{
		$formatDate = formatDateTimeWithMask($datetime, $mask);
	}
	return $formatDate;
}

function formatDateTimeWithMask($datetime, $mask)
{

	$formatDate = "";
	
	if( $datetime != "" && $datetime != null )
	{
		date_create($datetime);
		if( error_get_last() )
		{
			$formatDate = $datetime;
		}
		else
		{
			$maskFragments = array("YYYY", "MM", "DD", "hh", "mm", "ss", "OOOO", "AM");
			
			// Default to 24 hour time.
			$hoursFormat = 'H';
			
			if( strpos($mask, "AM") > 0 ){
				// Use 12 hour time.
				$hoursFormat = 'h';
			}

			// Get the local timezone as set in application_env.php.
			$timezone = new DateTimeZone(date_default_timezone_get());
			
			// Parse the string into a date.
			// If no timezone offset is supplied in the datetime string
			// then the local timezone will be used by the function (as set in the previous step).
			// Otherwise, if the datetime string includes a timezone offset, then the local timezone will be ignored and overridden.
			$objDate = new DateTime($datetime, $timezone);
			
			// If the mask has a "Z" in it, then the output will be representing a UTC/GMT date, 
			// and we need to convert the date to UTC. 
			// The conversion will be done by setting the timezone so...
			if( strpos($mask, "Z") > 0 )
			{
				$timezone = new DateTimeZone('UTC');
			}
			// Setting the timezone will convert the date.
			// So, we now set the timezone to convert the date to local time,
			// or to UTC, as has been determined in the previous steps.
			$objDate->setTimezone($timezone);
			
			// Get the values for each component of the date.
			$fragmentValues = array($objDate->format("Y"), $objDate->format("m"), $objDate->format("d"), $objDate->format($hoursFormat), $objDate->format("i"), $objDate->format("s"), $objDate->format("O"), $objDate->format("A"));
			
			// Replace all of the fragments in the mask with the values for each fragment that we calculated in the last step.
			$formatDate = str_replace($maskFragments, $fragmentValues, $mask);
		}
	}
	return $formatDate;
}

function formatDateTime($datetime, $type=gDATETIME)
{
	global $eDateFormat;
	global $eTimeFormat;
	global $eDateTimeFormat;

	// Fix for temporal dates which only include a year/year month
	// (e.g. "2005" should remain "2005" not "2005-01-01")
	if (preg_match("/^[0-9]{4}$/",$datetime))
	{
		return $datetime;
	} 
	elseif (preg_match("/^[0-9]{4}[\/\-]{1}[0-9]{2}$/",$datetime))
	{
		return $datetime;
	}
	
	$formatDate = "";
	if( $datetime != "" && $datetime != null )
	{
		
		try {
			$formatDate = new DateTime($datetime);
		}
		catch (Exception $e) 
		{
			// Return the plain text representation
			return $datetime;
		}
		
		switch( $type )
		{
			case gDATE:
				$mask = $eDateFormat;
				break;
			case gTIME:
				$mask = $eTimeFormat;
				break;
			default:
				$mask = $eDateTimeFormat;
				break;
		}
		
		
		$formatDate = formatDateTimeWithMask($datetime, $mask);
	}
	
	return $formatDate;
}

function htmlNumericCharRefs($unsafeString)
{
	$safeString = str_replace("&", "&#38;", $unsafeString);
	$safeString = str_replace('"', "&#34;", $safeString);
	$safeString = str_replace("'", "&#39;", $safeString);
	$safeString = str_replace("<", "&#60;", $safeString);
	$safeString = str_replace(">", "&#62;", $safeString);
	return $safeString;
}

function esc($unsafeString, $forJavascript=false)
{
	$safeString = $unsafeString;
	if( $forJavascript )
	{
		$safeString = str_replace('\\', '\\\\', $safeString);
		$safeString = str_replace("'", "\\'", $safeString);
	}
	$safeString = htmlNumericCharRefs($safeString);
	$safeString = str_replace("\r", "", $safeString);
	$safeString = str_replace("\n", "&#xA;", $safeString);
	return $safeString;
}

function escWithBreaks($unsafeString)
{
	$safeString = esc($unsafeString);
	$safeString =  str_replace("\n", "<br />", $safeString);
	$safeString =  str_replace("&#xA;", "<br />", $safeString);
	$safeString =  str_replace("\t", "&#xA0;&#xA0;&#xA0;&#xA0;", $safeString);
	$safeString =  str_replace("  ", " ", $safeString);
	return $safeString;
}

function printSafe($unsafeString)
{
	$safeString = esc($unsafeString);
	print $safeString;
}

function printSafeWithBreaks($unsafeString)
{
	$safeString = escWithBreaks($unsafeString);
	print $safeString;
}

function setChosen($name, $value, $itemType)
{
	global $gChosen;
	$gChosen = "";
	if( isChosen($name, $value) )
	{
		$gChosen = $itemType;
	}
}

function setChosenFromQuery($name, $value, $itemType)
{
	global $gChosen;
	$gChosen = "";
	if( getQueryValue($name) == $value )
	{
		$gChosen = $itemType;
	}
}

function setChosenFromValue($actualValue, $requiredValue, $itemType)
{
	global $gChosen;
	$gChosen = "";
	if( $actualValue == $requiredValue )
	{
		$gChosen = $itemType;
	}
}

function isChosen($name, $value)
{
	$chosen = false;
	if( isset($_POST[$name]) )
	{
		if( is_array(getPostedValue($name)) )
		{
			if( in_array($value, getPostedValue($name)) )
			{
				$chosen = true;
			}
		} 
		else
		{
			if( getPostedValue($name) == $value )
			{
				$chosen = true;
			}
		}
	}
	
	if( isset($_GET[$name]) )
	{
		if( is_array(getQueryValue($name)) )
		{
			if( in_array($value, getQueryValue($name)) )
			{
				$chosen = true;
			}
		} 
		else
		{
			if( getQueryValue($name) == $value )
			{
				$chosen = true;
			}
		}
	}
	return $chosen;
}

function getNumPages($numItems, $itemsPerPage, &$pageNumber=0)
{
	$numPages =  (int)(($numItems+$itemsPerPage-1)/$itemsPerPage);
	if( $pageNumber > $numPages )
	{ 
		$pageNumber = $numPages;
	}
	return $numPages;
}

function getStartIndex($pageNumber, $itemsPerPage)
{
	return ($pageNumber-1)*$itemsPerPage;
}

function getEndIndex($numItems, $startIndex, $itemsPerPage)
{
	return min($numItems-1, $startIndex+$itemsPerPage-1);
}

function getStartPage($pageNumber, $pagesPerPage)
{
	$pageSet = (int)(($pageNumber-1)/$pagesPerPage);
	return ($pageSet*$pagesPerPage)+1;
}

function getEndPage($numPages, $startPage, $pagesPerPage)
{
	return min($numPages, $startPage+$pagesPerPage-1);
}

function drawResultsHeader($startIndex, $endIndex, $numItems, $additionalText)
{
	print("<p class=\"resultListHeader\">");
	drawResultsInfo($startIndex, $endIndex, $numItems, $additionalText);
	print("</p>\n");
}

function drawResultsInfo($startIndex, $endIndex, $numItems, $additionalText)
{
	print("Results ".($startIndex+1)." to ".($endIndex+1)." of ".$numItems.$additionalText.".");
}

function drawResultsFooter($numPages, $pageNumber, $startPage, $endPage, $uri)
{
	if( $numPages > 1 )
	{
		print("<p class=\"resultListFooter\">");
		drawPagination($numPages, $pageNumber, $startPage, $endPage, $uri);
		print("</p>\n");			
	}		
}

function drawPagination($numPages, $pageNumber, $startPage, $endPage, $uri)
{
	if( $numPages > 1 )
	{
		print("&nbsp;");
		if( $pageNumber > 1 )
		{
			print("<a href=\"".esc("$uri&page=1")."\" title=\"First page (1)\" onclick=\"wcPleaseWait(true, 'Retrieving...')\"><img src=\"".gPAG_CONTROL_PATH."first.gif\" alt=\"First page\" width=\"14\" height=\"12\" /></a>\n");
			print("<a href=\"".esc("$uri&page=".($pageNumber-1))."\" title=\"Previous page (".($pageNumber-1).")\" onclick=\"wcPleaseWait(true, 'Retrieving...')\"><img src=\"".gPAG_CONTROL_PATH."prev.gif\" alt=\"Previous page\" width=\"12\" height=\"12\" /></a>&nbsp;\n");
		}
		else
		{
			print("<span style=\"color: #cccccc;\"><img src=\"".gPAG_CONTROL_PATH."first_disabled.gif\" alt=\"First page\" width=\"14\" height=\"12\" /></span>\n");
			print("<span style=\"color: #cccccc;\"><img src=\"".gPAG_CONTROL_PATH."prev_disabled.gif\" alt=\"Previous page\" width=\"12\" height=\"12\" /></span>&nbsp;\n");
		}
		for( $i=$startPage; $i <= $endPage; $i++ )
		{
			if( $i == $pageNumber )
			{
				print("<span class=\"currentPage\">$pageNumber</span>\n");
			}
			else
			{
				print("<a class=\"pageLink\" href=\"".esc("$uri&page=".$i)."\" title=\"Page $i\" onclick=\"wcPleaseWait(true, 'Retrieving...')\">$i</a>\n");
			}
		}
		if( $pageNumber < $numPages )
		{
			print("&nbsp;<a href=\"".esc("$uri&page=".($pageNumber+1))."\" title=\"Next page (".($pageNumber+1).")\" onclick=\"wcPleaseWait(true, 'Retrieving...')\"><img src=\"".gPAG_CONTROL_PATH."next.gif\" alt=\"Next page\" width=\"12\" height=\"12\" /></a>\n");
			print("<a href=\"".esc("$uri&page=".($numPages))."\" title=\"Last page ($numPages)\" onclick=\"wcPleaseWait(true, 'Retrieving...')\"><img src=\"".gPAG_CONTROL_PATH."last.gif\" alt=\"Last page\" width=\"14\" height=\"12\" /></a>\n");
		}
		else
		{
			print("&nbsp;<span style=\"color: #cccccc;\"><img src=\"".gPAG_CONTROL_PATH."next_disabled.gif\" alt=\"Next page\" width=\"12\" height=\"12\" /></span>\n");
			print("<span style=\"color: #cccccc;\"><img src=\"".gPAG_CONTROL_PATH."last_disabled.gif\" alt=\"Last page\" width=\"14\" height=\"12\" /></span>\n");
		}
		print("&nbsp;");
	}	
}


// Display code for Google Analytics' callback function
// Only display if we tracking ID is specified
function getAnalyticsTrackingCode($trackingID)
{
	
	if ($trackingID != "") {
		
		// Asynchronous tracking snippet
		// from http://code.google.com/apis/analytics/docs/tracking/asyncTracking.html
			
		echo "
		
		 	<!-- Begin Google Analytics tracking code -->
			<script type=\"text/javascript\">
	
			  var _gaq = _gaq || [];
			  _gaq.push(['_setAccount', '".$trackingID."']);
			  _gaq.push(['_trackPageview']);
			
			  (function() {
			    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			  })();
			
			</script>
			<!-- End Google Analytics tracking code -->
			
			";
	
	}
	
}





?>
