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

// Characters used in presentation.
define("gCHAR_MIDDOT", "·"); // mid dot, ISO-10646 character 0x00B7.
define("gCHAR_EMDASH", "—"); // em dash, ISO-10646 character 0x2014.


function getTitlePath($menuId, &$pageTitle)
{
	global $gMenus;
	
	if( $menuId && $menuId != gROOT_MENU_ID )
	{
		$titleMenu = getObject($gMenus, $menuId);
		$pageTitle = $titleMenu->title.gCHAR_EMDASH.$pageTitle;
		getTitlePath($titleMenu->parent_id, $pageTitle);
	}
}

function checkSSL($activity_id)
{
	global $gActivities;
	
	$activity = getObject($gActivities, $activity_id);
	if( !$activity->no_check_ssl )
	{
		// If the web root specifies https, but the request wasn't made via https
		// then redirect the request to come back via https.
		$reqIsHTTPS = true;
		
		if( isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) === 'ON' )
		{
			$reqIsHTTPS = true;
		}
		
		if( strtoupper(ePROTOCOL) == 'HTTPS' && !$reqIsHTTPS )
		{
			responseRedirect('https://'.eHOST.getCurrentPath());
		}
	}
}

function getActivityLink($activity_id)
{
	global $gActivities;
	
	$activityLink = '';

	if( hasActivity($activity_id) )
	{
		$activity = getObject($gActivities, $activity_id);
		$activityLink = '<a href="'.eAPP_ROOT.$activity->path.'">'.$activity->title.'</a>';
	}
	return $activityLink;
}

function getObject($array, $key)
{
	$object = false;
	if( isset($array[$key]) )
	{
		$object = $array[$key];
	}
	return $object;
}

function getActivityHelpContentURI($activity_id)
{
	global $gActivities;
	
	$help_content_uri = '';
	$activity = getObject($gActivities, $activity_id);
	if( $activity->help_content_uri )
	{
		$help_content_uri = $activity->help_content_uri;
	}
	return $help_content_uri;
}

function getActivityHelpContentFragmentId($activity_id)
{
	global $gActivities;
	
	$help_content_fragment_id = '';
	$activity = getObject($gActivities, $activity_id);
	if( $activity->help_content_fragment_id )
	{
		$help_content_fragment_id = $activity->help_content_fragment_id;
	}
	return $help_content_fragment_id;
}

function getCurrentPath()
{
	if (isset($_SERVER['REDIRECT_URL']))
	{
		$path = $_SERVER['REDIRECT_URL'];
	} 
	else
	{
		$path = $_SERVER['PHP_SELF'];
	}
	
	$path = str_replace('\\','\/', $path);
	return $path;
}

function responseRedirect($locationURI)
{
	closeDatabaseConnections();
	header('Location: '.$locationURI);
	exit;
}

function fixMagicQuotesGPC()
{
	// Prevent PHP 'magic quotes' data corruption.
	if( get_magic_quotes_gpc() )
	{
		if( $_POST )
		{
			foreach( $_POST as &$value )
			{
				if( is_array($value) )
				{
					foreach( $value as &$subValue )
					{
						$subValue = stripslashes($subValue);
					}
				}
				else
				{
					$value = stripslashes($value);
				}
			}
		}
		
		if( $_GET )
		{
			foreach( $_GET as &$value )
			{
				if( is_array($value) )
				{
					foreach( $value as &$subValue )
					{
						$subValue = stripslashes($subValue);
					}
				}
				else
				{
					$value = stripslashes($value);
				}
			}			
		}
		
		if( $_COOKIE )
		{
			foreach( $_COOKIE as &$value )
			{
				if( is_array($value) )
				{
					foreach( $value as &$subValue )
					{
						$subValue = stripslashes($subValue);
					}
				}
				else
				{
					$value = stripslashes($value);
				}
			}			
		}
		
	}
}

function getPostedValue($formFieldID)
{
	$value = '';
	if( isset($_POST[$formFieldID]) )
	{
		$value = $_POST[$formFieldID];
	}

	return $value;
}

function getQueryValue($queryStringIdentifer)
{
	$value = '';
	if( isset($_GET[$queryStringIdentifer]) )
	{
		$value = $_GET[$queryStringIdentifer];
	}

	return $value;
}

function getPageNumber()
{
	$pageNumber = (int)getQueryValue('page');
	if( $pageNumber < 1 ){ $pageNumber = 1; }
	return $pageNumber;
}

// Debug Functions
// -----------------------------------------------------------------------------
function printData($title, $internal_array)
{
	if( $internal_array )
	{
		print '<b>'.esc($title)."</b><br />\n";
		foreach($internal_array as $key => $value)
		{
			printSafe("$key=");	
			if( is_array($value) )
			{
				foreach( $value as $subvalue )
				{
					printSafe("$subvalue, ");
				}
			}
			else
			{
				printSafe($value);
			}
			print "<br />\n";			
		}
	}
}


// XSLT Functions
// -----------------------------------------------------------------------------
function xsltTransform($xmlIn, $xmlSource, $xslIn, $xslSource, $submitIn="")
{
   global $eDateFormat;
   global $eTimeFormat;
   global $eDateTimeFormat;
   
   $xml = new DomDocument();
   if ( strtolower($xmlSource) == "string" )
   {
      if ( $xml->loadxml($xmlIn) )
      {
         $xmlLoad = true;
      }
      else
      {
         $xmlLoad = false;
      }
   }
   else
   {
      if ( $xml->load($xmlIn) )
      {
         $xmlLoad = true;
      }
      else
      {
         $xmlLoad = false;
      }
   }

   $xsl = new DomDocument();
   if ( strtolower($xslSource) == "string" )
   {
      if ( $xsl->loadxml($xslIn) )
      {
         $xslLoad = true;
      }
      else
      {
         $xslLoad = false;
      }
   }
   else
   {
      if ( $xsl->load($xslIn) )
      {
         $xslLoad = true;
      }
      else
      {
         $xslLoad = false;
      }
   }

   if ( $xmlLoad && $xslLoad )
   {
      $proc = new XSLTProcessor();
      $proc->importStyleSheet($xsl);
      $proc->setParameter(null, "submitId", $submitIn);
      $proc->setParameter(null, "dateFormat", $eDateFormat);
      $proc->setParameter(null, "timeFormat", $eTimeFormat);
      $proc->setParameter(null, "datetimeFormat", $eDateTimeFormat);
      $transformResult = $proc->transformToXML($xml);
   }
   else
   {
      $transformResult = "Error";
   }
   
   return $transformResult;
}

//Stats display functions
//-----------------------------------------
function get_months($date1, $date2) {

   $time1  = strtotime($date1);
   $time2  = strtotime($date2);
   $my     = date('mY', $time2);

   $months["M"] = array(date('M', $time1));
   $months["m"] = array(date('Y-m', $time1));
   
   while($time1 < $time2) {
      $time1 = strtotime(date('Y-m', $time1).' +1 month');
      if(date('mY', $time1) != $my && ($time1 < $time2))
      {
      	$months["M"][] = date('M', $time1);   
        $months["m"][] = date('Y-m', $time1);
      }
   }
   
	if($date1<$date2)
	{
   		$months["M"][]= date('M', $time2);
   		$months["m"][]= date('Y-m', $time2);
	}
   
   return $months;
} 

// Loosely compares two XML fragments for equality (ignoring element and attribute ordering/structure)
// Firstly checks for equal string lengths and then checks that they contain the same characters.
// By default, strips whitespace between XML elements
function compareLooseXMLEquivalent ($xmlFragment1, $xmlFragment2, $stripWhitespace = TRUE)
{
	
	// Replace whitespace between XML elements
	if ($stripWhitespace)
	{
		
		$xmlFragment1 = trim(preg_replace("/\>\s+\</","><",$xmlFragment1));
		$xmlFragment2 = trim(preg_replace("/\>\s+\</","><",$xmlFragment2));
		
	}
	
	// Check fragments are of equal length
	if (strlen($xmlFragment1) != strlen($xmlFragment2))
	{
		return false;
	}
	else
	{
		// Sort the characters in the fragments (QuickSort)
		$xmlSorted1 = str_split($xmlFragment1);
		$xmlSorted2 = str_split($xmlFragment2);
		sort($xmlSorted1);
		sort($xmlSorted2);
		
		// Check for equality (do the fragments contain the same letters)
		if ($xmlSorted1 != $xmlSorted2)
		{
			return false;
		}
		
	}
	
	// Loosely assume fragments are equivalent
	return true;
}


$BENCHMARK_TIME = array(0,0,0,0,0,0,0,0,0,0);
function bench($idx = 0)
{
	global $BENCHMARK_TIME;

	if ($BENCHMARK_TIME[$idx] == 0) 
	{
		$BENCHMARK_TIME[$idx] = microtime(true);
	}
	else
	{
		$diff = sprintf ("%.3f", (float) (microtime(true) - $BENCHMARK_TIME[$idx]));
		$BENCHMARK_TIME[$idx] = 0;
		return $diff;
	}
}
?>