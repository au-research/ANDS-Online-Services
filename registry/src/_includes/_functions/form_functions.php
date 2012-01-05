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
// HTML Form Globals
// -----------------------------------------------------------------------------
define("gXSL_VALIDATION_PATH", eAPP_ROOT."_xsl/csv_validate.xsl");
define("gXSL_HTMLFORM_PATH", eAPP_ROOT."_xsl/form_pres.xsl");
define("gXSL_ERROR_PATH", eAPP_ROOT."_xsl/error_format.xsl");
define("gSOURCE_STRING", "STRING");
define("gSOURCE_URI", "URI");

$gDataSource         = gSOURCE_URI;
$gFormSource         = gSOURCE_URI;
$pageCancel          = "";
$min                 = "";
$max                 = "";
$intValidationErrors = 0;


//******************************************************************************
function doProcessForm(&$xmlForm, &$xmlData, $butSubmit="submit", $butCancel="cancel")
{
	global $pageCancel;
	global $gDataSource;
	global $gFormSource;
	global $intValidationErrors;
  
	$retValue = False;
	$Action = "";
	if ( isset($_POST['action']) ) { $Action = strtolower(getPostedValue('action')); }
	$butValue = strtolower($butCancel);
	if ( $Action == $butValue )
	{
		if ( $pageCancel <> "" ) { responseRedirect($pageCancel); }
	}
	else
	{
		$butValue = strtolower($butSubmit);
		if ( $Action == $butValue )
		{
			$xmlForm     = Validate($xmlForm);   // validate posted data
			$gFormSource = gSOURCE_STRING;
			if ( $intValidationErrors == 0 ) { $retValue = True; }
		}
	}

	if ( $xmlData <> "" )
	{
		if ( $gDataSource == gSOURCE_URI )
		{
			$xmlForm = insertXML($xmlForm, "", file_get_contents($xmlData));
		}
		else
		{
			$xmlForm = insertXML($xmlForm, "", $xmlData);
		}
		$gFormSource = gSOURCE_STRING;
	}

	return $retValue;
}


//******************************************************************************
function formTransform($xmlForm, $pageID, $addErrors="")
{
	global $gFormSource;
  
	if ( $addErrors != "" )
	{
		$xmlForm = insertXML($xmlForm, $addErrors, "");
		$gFormSource = gSOURCE_STRING;
	}
	return xsltTransform($xmlForm, $gFormSource, gXSL_HTMLFORM_PATH, gSOURCE_URI, $pageID);
}


//******************************************************************************
function formTransData($xmlPage, $xslPage, $keyId, $keyValue)
{
	global $gDataSource;
  
	$temp = xsltTransform($xmlPage, gSOURCE_URI, $xslPage, gSOURCE_URI, $keyValue);
	if ( $temp == "" )
	{
		$xmlData = "ERROR: data not found";
	}
	else
	{
		$xmlData = formSetDataElement($keyId, $temp);
		$gDataSource = gSOURCE_STRING;
	}
	return $xmlData;
}


//******************************************************************************
function formSetDataElement($eID, $eValue)
{
	$xmlData = "<cfd:cosiformdata xmlns:cfd='http://apsr.edu.au/namespaces/cfd'>";
	$xmlData = $xmlData."<cfd:element cfd:id='".$eID."' cfd:value='".$eValue."' />";
	$xmlData = $xmlData."</cfd:cosiformdata>";

	return $xmlData;
}


//******************************************************************************
function Validate($xmlIn)
{
	global $gFormSource;

	$xslErrors = "";
	$xslParam  = "<cfd:cosiformdata xmlns:cfd=\"http://apsr.edu.au/namespaces/cfd\">";

	Post_to_XML($xslParam);                                                                 // convert posted data to XML namespace format
	$csvValidate = xsltTransform($xmlIn, $gFormSource, gXSL_VALIDATION_PATH, gSOURCE_URI);   // get validation rules as csv string
	isMandatory($csvValidate, $xslParam, $xslErrors);                                       // validate mandatory fields
	isNumber($csvValidate, $xslParam, $xslErrors);                                          // validate numeric fields
	isInteger($csvValidate, $xslParam, $xslErrors);                                         // validate integer fields
	isDate($csvValidate, $xslParam, $xslErrors);                                            // validate date fields
	isTime($csvValidate, $xslParam, $xslErrors);                                            // validate time fields
	isDateTime($csvValidate, $xslParam, $xslErrors);                                        // validate datetime fields

	$xslParam = $xslParam."</cfd:cosiformdata>";
	$xmlOut = insertXML($xmlIn, $xslErrors, $xslParam);

	return $xmlOut;
}


//******************************************************************************
function insertXML($In, $Errors, $Param)
{
	global $gFormSource;
  
	if ( $gFormSource == gSOURCE_URI )
	{
		$xmlTemp = strtolower(file_get_contents($In));
	}
	else
	{
		$xmlTemp = strtolower($In);
	}
	$posForm1 = strpos($xmlTemp, "<form");
	$posForm2 = strpos($xmlTemp, ">", $posForm1);
	if ( $gFormSource == gSOURCE_URI )
	{
		$xmlTemp = file_get_contents($In);
	}
	else
	{
		$xmlTemp = $In;
	}
	$xmlString = substr($xmlTemp, 0, $posForm2+1).$Errors.$Param.substr($xmlTemp, $posForm2+1);
	return $xmlString;
}


//******************************************************************************
function Post_to_XML(&$xslParam)
{
	foreach( $_POST as $key => $value )
	{
		$value = getPostedValue($key);
		if( is_array($value) )
		{
			foreach( $value as $subKey => $subValue )
			{
				$xslParam = $xslParam."<cfd:element cfd:id=\"".esc($subKey)."\" cfd:value=\"".esc($subValue)."\" />";
			}
		}
		else
		{
			$xslParam = $xslParam."<cfd:element cfd:id=\"".esc($key)."\" cfd:value=\"".esc($value)."\" />";
		}
	}
}


//******************************************************************************
function isMandatory($csvIn, &$xslParam, &$xslErrors)
{
	global $intValidationErrors;

	$i = "";
	$aValidate = explode("@@",substr($csvIn,0,-2));
	$i == 0;
	foreach ($aValidate as $key => $value)
	{
		if ( strpos($value, "||mandatory||" ) > 0)
		{
			$tempKey = substr($value, 0, strpos($value, "||"));
			$tempValue = substr($value, strpos($value, "||")+13);
			$aMandatory[$tempKey] = $tempValue;
			$i++;
		}
	}
	if ( $i > 0 )
	{
		foreach ($aMandatory as $key => $value)
		{
			$err = 0;
			if ( isset($_POST[$key]) )
			{
				if ( $_POST[$key] == "" ) { $err = 1; }
			}
			else
			{
				$err = 1;
			}
			if ( $err == 1 )
			{
				$xslParam  = $xslParam."<cfd:error cfd:id=\"".$key."\" cfd:value=\"mandatory\" />";
				$xslErrors = $xslErrors."<error id=\"".$key."\" value=\"".$value." is a mandatory field.\" />";
				$intValidationErrors++;
			}
		}
	}
}


//******************************************************************************
function isNumber($csvIn, &$xslParam, &$xslErrors)
{
	global $intValidationErrors;
  
	$i = "";
	$aValidate = explode("@@",substr($csvIn,0,-2));
	$i == 0;
	foreach ($aValidate as $key => $value)
	{
		if ( strpos($value, "||number||" ) > 0)
		{
			$tempKey = substr($value, 0, strpos($value, "||"));
			$tempValue = substr($value, strpos($value, "||")+10);
			$aNumber[$tempKey] = $tempValue;
			$i++;
		}
	}
	if ( $i > 0 )
	{
		foreach ($aNumber as $key => $value)
		{
			$err = 0;
			$tempLabel = substr($value, 0, strpos($value, "||"));
			if ( isset($_POST[$key]) )
			{
				$tempValue = getPostedValue($key);
			}
			else
			{
				$tempValue = substr($value, strpos($value, "||")+2);
			}
			if ( !is_numeric($tempValue) && $tempValue <> "" ) { $err = 1; }
			if ( $err == 1 )
			{
				$xslParam  = $xslParam."<cfd:error cfd:id=\"".$key."\" cfd:value=\"number\" />";
				$xslErrors = $xslErrors."<error id=\"".$key."\" value=\"".$tempLabel." must be numeric.\" />";
				$intValidationErrors++;
		 }
		 else
		 {
		 	if ( $tempValue <> "" ) { validateRange("number", $tempValue, $tempLabel, $key, $value, &$xslParam, &$xslErrors); }
		 }
		}
	}
}


//******************************************************************************
function isInteger($csvIn, &$xslParam, &$xslErrors)
{
	global $intValidationErrors;
  
	$i = "";
	$aValidate = explode("@@",substr($csvIn,0,-2));
	$i == 0;
	foreach ($aValidate as $key => $value)
	{
		if ( strpos($value, "||integer||" ) > 0)
		{
			$tempKey = substr($value, 0, strpos($value, "||"));
			$tempValue = substr($value, strpos($value, "||")+11);
			$aInteger[$tempKey] = $tempValue;
			$i++;
		}
	}
	if ( $i > 0 )
	{
		foreach ($aInteger as $key => $value)
		{
			$err = 0;
			$tempLabel = substr($value, 0, strpos($value, "||"));
			if ( isset($_POST[$key]) )
			{
				$tempValue = getPostedValue($key);
			}
			else
			{
				$tempValue = substr($value, strpos($value, "||")+2);
			}
			if ( $tempValue <> "" )
			{
				if ( !is_numeric($tempValue) )
				{
					if ( !is_int($tempValue) ) { $err = 1; }
				}
				else
				{
					if ( !((int)$tempValue.""==$tempValue."") ) { $err = 1; }
				}
			}
			 
			if ( $err == 1)
			{
				$xslParam  = $xslParam."<cfd:error cfd:id=\"".$key."\" cfd:value=\"integer\" />";
				$xslErrors = $xslErrors."<error id=\"".$key."\" value=\"".$tempLabel." must be an integer.\" />";
				$intValidationErrors++;
			}
			else
			{
				if ( $tempValue <> "" ) { validateRange("integer", $tempValue, $tempLabel, $key, $value, &$xslParam, &$xslErrors); }
			}
		}
	}
}


//******************************************************************************
function isDate($csvIn, &$xslParam, &$xslErrors)
{
	global $intValidationErrors;
	global $eDateFormat;
  
	$i = "";
	$aValidate = explode("@@",substr($csvIn,0,-2));
	$i == 0;
	foreach ($aValidate as $key => $value)
	{
		if ( strpos($value, "||date||" ) > 0)
		{
			$tempKey = substr($value, 0, strpos($value, "||"));
			$tempValue = substr($value, strpos($value, "||")+8);
			$aDate[$tempKey] = $tempValue;
			$i++;
		}
	}
	if ( $i > 0 )
	{
		foreach ($aDate as $key => $value)
		{
			$err = 0;
			$tempLabel = substr($value, 0, strpos($value, "||"));
			if ( isset($_POST[$key]) )
			{
				$tempValue = getPostedValue($key);
			}
			else
			{
				$tempValue = substr($value, strpos($value, "||")+2);
			}
			if ( !chkDateTime($tempValue,"D") && $tempValue <> "" ) { $err = 1; }
			if ( $err == 1 )
			{
				$xslParam  = $xslParam."<cfd:error cfd:id=\"".$key."\" cfd:value=\"date\" />";
				$xslErrors = $xslErrors."<error id=\"".$key."\" value=\"".$tempLabel." must be a valid date in the format ".$eDateFormat.".\" />";
				$intValidationErrors++;
			}
			else
			{
				if ( $tempValue <> "" ) { validateRange("date", $tempValue, $tempLabel, $key, $value, &$xslParam, &$xslErrors); }
			}
		}
	}
}


//******************************************************************************
function isTime($csvIn, &$xslParam, &$xslErrors)
{
	global $intValidationErrors;
	global $eTimeFormat;
  
	$i = "";
	$aValidate = explode("@@",substr($csvIn,0,-2));
	$i == 0;
	foreach ($aValidate as $key => $value)
	{
		if ( strpos($value, "||time||") > 0 )
		{
			$tempKey = substr($value, 0, strpos($value, "||"));
			$tempValue = substr($value, strpos($value, "||")+8);
			$aDate[$tempKey] = $tempValue;
			$i++;
		}
	}
	if ( $i > 0 )
	{
		foreach ($aDate as $key => $value)
		{
			$err = 0;
			$tempLabel = substr($value, 0, strpos($value, "||"));
			if ( isset($_POST[$key]) )
			{
				$tempValue = getPostedValue($key);
			}
			else
			{
				$tempValue = substr($value, strpos($value, "||")+2);
			}
			if ( !chkDateTime($tempValue,"T") && $tempValue <> "" ) { $err = 1; }
			if ($err == 1)
			{
				$xslParam  = $xslParam."<cfd:error cfd:id=\"".$key."\" cfd:value=\"time\" />";
				$xslErrors = $xslErrors."<error id=\"".$key."\" value=\"".$tempLabel." must be a valid time in the 24 hour format ".$eTimeFormat.".\" />";
				$intValidationErrors++;
			}
			else
			{
				if ( $tempValue <> "" ) { validateRange("time", $tempValue, $tempLabel, $key, $value, &$xslParam, &$xslErrors); }
			}
		}
	}
}


//******************************************************************************
function isDateTime($csvIn, &$xslParam, &$xslErrors)
{
	global $intValidationErrors;
	global $eDateTimeFormat;
  
	$i = "";
	$aValidate = explode("@@",substr($csvIn,0,-2));
	$i == 0;
	foreach ($aValidate as $key => $value)
	{
		if ( strpos($value, "||datetime||" ) > 0)
		{
			$tempKey = substr($value, 0, strpos($value, "||"));
			$tempValue = substr($value, strpos($value, "||")+12);
			$aDate[$tempKey] = $tempValue;
			$i++;
		}
	}
	if ( $i > 0 )
	{
		foreach ($aDate as $key => $value)
		{
			$err = 0;
			$tempLabel = substr($value, 0, strpos($value, "||"));
			if ( isset($_POST[$key]) )
			{
				$tempValue = getPostedValue($key);
			}
			else
			{
				$tempValue = substr($value, strpos($value, "||")+2);
			}
			if ( !chkDateTime($tempValue,"DT") && $tempValue <> "" ) { $err = 1; }
			if ( $err == 1 )
			{
				$xslParam  = $xslParam."<cfd:error cfd:id=\"".$key."\" cfd:value=\"datetime\" />";
				$xslErrors = $xslErrors."<error id=\"".$key."\" value=\"".$tempLabel." must be a valid date and 24 hour time in format ".$eDateTimeFormat.".\" />";
				$intValidationErrors++;
			}
			{
				if ( $tempValue <> "" ) { validateRange("datetime", $tempValue, $tempLabel, $key, $value, &$xslParam, &$xslErrors); }
			}
		}
	}
}


//******************************************************************************
function chkDateTime($dateIn, $dateType="D")
{
	global $eDateFormat;
	global $eTimeFormat;
	global $eDateTimeFormat;
  
	$validDate = False;
	switch ($dateType)
	{
		case "T":
			$validDate = dateReformat($dateIn, $eTimeFormat);
			break;

		case "DT":
			$validDate = dateReformat($dateIn, $eDateTimeFormat);
			break;

		default:
			$validDate = dateReformat($dateIn, $eDateFormat);
			break;
	}
	return $validDate;
}


//******************************************************************************
function dateReformat($dateIn, $formatIn)
{
	$validDate = False;
	$year  = substr($dateIn, strpos($formatIn, "YYYY"), 4);
	$month = substr($dateIn, strpos($formatIn, "MM"), 2);
	$day   = substr($dateIn, strpos($formatIn, "DD"), 2);
	$hour  = substr($dateIn, strpos($formatIn, "hh"), 2);
	$min   = substr($dateIn, strpos($formatIn, "mm"), 2);

	$isDate = true;
	$isTime = true;
	if ( strpos($formatIn, "YYYY") === false ) { $isDate = false; }
	if ( strpos($formatIn, "hh") === false )   { $isTime = false; }
	 
	if ( $isDate && $isTime )
	{
		if ( date("Y-m-d H:i", strtotime($year."-".$month."-".$day." ".$hour.":".$min)) == $year."-".$month."-".$day." ".$hour.":".$min ) { $validDate = True; }
	}
	else
	{
		if ( $isTime )
		{
			if ( date("H:i", strtotime($hour.":".$min)) == $hour.":".$min ) { $validDate = True; }
		}
		else
		{
			if ( date("Y-m-d", strtotime($year."-".$month."-".$day)) == $year."-".$month."-".$day ) { $validDate = True; }
		}
	}
	return $validDate;
}


//******************************************************************************
function validateRange($type, $valueIn, $labelIn, $keyIn, $stringIn, &$xslParam, &$xslErrors)
{
	global $min;
	global $max;
	global $intValidationErrors;
  
	if ( strpos($stringIn, "||min=" ) === false)
	{
		$min = "none";
	}
	else
	{
		if ( strpos(substr($stringIn, strpos($stringIn, "||min")+6), "||" ) === false)
		{
			$min = substr($stringIn, strpos($stringIn, "||min")+6);
		}
		else
		{
			$min = substr($stringIn, strpos($stringIn, "||min")+6, strpos(substr($stringIn, strpos($stringIn, "||min")+6), "||"));
		}
	}

	if ( strpos($stringIn, "||max=" ) === false)
	{
		$max = "none";
	}
	else
	{
		if ( strpos(substr($stringIn, strpos($stringIn, "||max")+6), "||" ) === false)
		{
			$max = substr($stringIn, strpos($stringIn, "||max")+6);
		}
		else
		{
			$max = substr($stringIn, strpos($stringIn, "||max")+6, strpos(substr($stringIn, strpos($stringIn, "||max")+6), "||"));
		}
	}

	if ( $min <> "none" )
	{
		if ( !validateType($type, "min", $valueIn) )
		{
		 if ( $max <> "none" )
		 {
		 	switch ($type)
		 	{
		 		case "number":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"number\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a number between ".$min." and ".$max." inclusive.\" />";
		 			break;
		 		case "integer":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"integer\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be an integer between ".$min." and ".$max." inclusive.\" />";
		 			break;
		 		case "date":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"date\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date between ".date("j F Y", strtotime($min))." and ".date("j F Y", strtotime($max))." inclusive.\" />";
		 			break;
		 		case "time":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"time\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a 24 hour time between ".date("H:i", strtotime($min))." and ".date("H:i", strtotime($max))." inclusive.\" />";
		 			break;
		 		case "datetime":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"datetime\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date and 24 hour time between ".date("j F Y H:i", strtotime($min))." and ".date("j F Y H:i", strtotime($max))." inclusive.\" />";
		 			break;
		 	}
		 }
		 else
		 {
		 	switch ($type)
		 	{
		 		case "number":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"number\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a number greater than or equal to ".$min.".\" />";
		 			break;
		 		case "integer":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"integer\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be an integer greater than or equal to ".$min.".\" />";
		 			break;
		 		case "date":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"date\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date greater than or equal to ".date("j F Y", strtotime($min)).".\" />";
		 			break;
		 		case "time":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"time\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a 24 hour time greater than or equal to ".date("H:i", strtotime($min)).".\" />";
		 			break;
		 		case "datetime":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"datetime\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date and 24 hour time greater than or equal to ".date("j F Y H:i", strtotime($min)).".\" />";
		 			break;
		 	}
		 }
		 $intValidationErrors++;
		}
	}

	if ( $max <> "none" )
	{
		if ( !validateType($type, "max", $valueIn) )
		{
		 if ( $min <> "none" )
		 {
		 	switch ($type)
		 	{
		 		case "number":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"number\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a number between ".$min." and ".$max." inclusive.\" />";
		 			break;
		 		case "integer":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"integer\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be an integer between ".$min." and ".$max." inclusive.\" />";
		 			break;
		 		case "date":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"date\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date between ".date("j F Y", strtotime($min))." and ".date("j F Y", strtotime($max))." inclusive.\" />";
		 			break;
		 		case "time":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"time\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a 24 hour time between ".date("H:i", strtotime($min))." and ".date("H:i", strtotime($max))." inclusive.\" />";
		 			break;
		 		case "datetime":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"datetime\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date and 24 hour time between ".date("j F Y H:i", strtotime($min))." and ".date("j F Y H:i", strtotime($max))." inclusive.\" />";
		 			break;
		 	}
		 }
		 else
		 {
		 	switch ($type)
		 	{
		 		case "number":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"number\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a number less than or equal to ".$max.".\" />";
		 			break;
		 		case "integer":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"integer\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be an integer less than or equal to ".$max.".\" />";
		 			break;
		 		case "date":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"date\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date less than or equal to ".date("j F Y", strtotime($max)).".\" />";
		 			break;
		 		case "time":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"time\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a 24 hour time less than or equal to ".date("H:i", strtotime($max)).".\" />";
		 			break;
		 		case "datetime":
		 			$xslParam  = $xslParam."<cfd:error cfd:id=\"".$keyIn."\" cfd:value=\"datetime\" />";
		 			$xslErrors = $xslErrors."<error id=\"".$keyIn."\" value=\"".$labelIn." must be a valid date and 24 hour time less than or equal to ".date("j F Y H:i", strtotime($max)).".\" />";
		 			break;
		 	}
		 }
		 $intValidationErrors++;
		}
	}
}


//******************************************************************************
function validateType($type, $minmax, $valueIn)
{
	global $min;
	global $max;
  
	$valid = true;
	switch ($type)
	{
		case "number":
			if ( strtolower($minmax) == "min" )
			{
				if ( floatval($valueIn) < floatval($min) ) { $valid = false; }
			}
			else
			{
				if ( floatval($valueIn) > floatval($max) ) { $valid = false; }
			}
			break;

		case "integer":
			if ( strtolower($minmax) == "min" )
			{
				if ( intval($valueIn) < intval($min) ) { $valid = false; }
			}
			else
			{
				if ( intval($valueIn) > intval($max) ) { $valid = false; }
			}
			break;

		default:
			if ( strtolower($minmax) == "min" )
			{
				if ( strtotime($valueIn) < strtotime($min) ) { $valid = false; }
			}
			else
			{
				if ( strtotime($valueIn) > strtotime($max) ) { $valid = false; }
			}
			break;
	}
	return $valid;
}


//******************************************************************************
function dataFormatDateTime($inXML, $inDateField)
{
	$convString = "";
  
	$strFind1 = "cfd:id=\"".$inDateField."\"";
	$strFind2 = "cfd:value=\"";
	$posFind1 = strpos($inXML, $strFind1);
	$posFind2 = strpos($inXML, $strFind2, $posFind1);
	$posFind3 = strpos($inXML, "\"", $posFind2+11);
  
	$convString = substr($inXML, 0, $posFind2+11).formatDateTime(substr($inXML, $posFind2+11, $posFind3-$posFind2-11)).substr($inXML, $posFind3);
	return $convString;
}


//******************************************************************************
function getError($error)
{
	if ( substr($error, 0, 7) == "Problem" )
	{
		$temp = "<error id=\"\" value=\"".$error."\" />";
	}
	else
	{
		$temp = "<error id=\"\" value=\"".xsltTransform($error, gSOURCE_STRING, gXSL_ERROR_PATH, gSOURCE_URI)."\" />";
	}
	return $temp;
}


//******************************************************************************
function getErrorXML($fieldId, $errorText)
{
	return '<error id="'.esc($fieldId).'" value="'.esc($errorText).'" />';
}


//******************************************************************************
function do_post_request($url, $data, $optional_headers=null)
{
	$php_errormsg = "";
	$params = array('http' => array('method' => 'POST', 'content' => $data));
	if ( $optional_headers!== null ) { $params['http']['header'] = $optional_headers; }
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if ( !$fp ) { throw new Exception("Problem with $url, $php_errormsg"); }
	$response = @stream_get_contents($fp);
	if ( $response === false ) { throw new Exception("Problem reading data from $url, $php_errormsg"); }
	return $response;
}

//******************************************************************************
function arrayToDataXML($dataArray)
{
	$xmlData = "";
	if( $dataArray )
	{
		$rowKeys = array_keys($dataArray[0]);

		$xmlData = '<cfd:cosiformdata xmlns:cfd="http://apsr.edu.au/namespaces/cfd">';
		foreach( $rowKeys as $column )
		{
			$xmlData .= '<cfd:element cfd:id="'.esc($column).'" cfd:value="'.esc($dataArray[0][$column]).'" />';
		}
		$xmlData .= '</cfd:cosiformdata>';
	}
	return $xmlData;
}


//******************************************************************************
function stripLeadZero($numIn)
{
	if ( substr($numIn,0,1) == "0" )
	{
		$numOut = substr($numIn,1);
	}
	else
	{
		$numOut = $numIn;
	}
	return $numOut;
}


//******************************************************************************
function dateISO_to_Cron($dateIn, $runIn)
{
	$cron = "";
	$now = false;
	if ( $dateIn == "" ) { $now = true; }
	if ( $runIn == "N" ) { $now = true; }
  
	if ( !$now )
	{
		switch ($runIn)
		{
			case "D":
				$cron = "0 ".stripLeadZero(date("i", strtotime($dateIn)))." ".date("G", strtotime($dateIn))." ? * *";
				break;
			case "W":
				$cron = "0 ".stripLeadZero(date("i", strtotime($dateIn)))." ".date("G", strtotime($dateIn))." ? * ".strtoupper(date("D", strtotime($dateIn)));
				break;
			case "M":
				$cron = "0 ".stripLeadZero(date("i", strtotime($dateIn)))." ".date("G", strtotime($dateIn))." ".date("j", strtotime($dateIn))." * ?";
				break;
			default:
				$cron = "0 ".stripLeadZero(date("i", strtotime($dateIn)))." ".date("G", strtotime($dateIn))." ".date("j", strtotime($dateIn))." ".date("n", strtotime($dateIn))." ? ".date("Y", strtotime($dateIn));
				break;
		}
	}
	return $cron;
}
?>