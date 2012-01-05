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
// HTML Table Globals
// -----------------------------------------------------------------------------
define("gXSL_HEADER_PATH", eAPP_ROOT."_xsl/csv_tableheader.xsl");

// for indenting generated HTML code
define("i1", "   ");
define("i2", i1.i1);
define("i3", i2.i1);
define("i4", i3.i1);
define("i5", i4.i1);
define("i6", i5.i1);
define("i7", i6.i1);
define("i8", i7.i1);
define("i9", i8.i1);

$sortOrder = "a";
$sortCol   = 0;

$curPage = 1;
if ( isset($_GET['page']) )
{
	if ( (int)$_GET['page'].""==$_GET['page']."" ) { $curPage = intval($_GET['page']); }
	if ( $curPage < 1 ) { $curPage = 1; }
}


//******************************************************************************
function setSortOrder($sValue)
{
	global $sortOrder;

	$sort = strtolower($sValue);
	if ( substr($sort, 0, 1) == "a" or substr($sort, 0, 1) == "d" )
	{
		$sortOrder = substr($sort, 0, 1);
	}
}


//******************************************************************************
function setSortCol($sValue, $tableCols)
{
	global $sortCol;

	$sort = strtolower($sValue);
	if ( substr($sort, 0, 1) == "a" or substr($sort, 0, 1) == "d" )
	{
		if ( (int)substr($sort, 1).""==substr($sort, 1)."" )
		{
			$sortCol = intval(substr($sort, 1));
			if ( $sortCol < 0 ) { $sortCol = 0; }
		}
	}
	if ( $sortCol > $tableCols ) { $sortCol = 0; }
}


//******************************************************************************
function sortMDArray($array, $by, $order, $type)
{
	$sortby   = "sort$by";
	$firstval = current($array);
	$vals     = array_keys($firstval);
  
	foreach ($vals as $init)
	{
		$keyname  = "sort$init";
		$$keyname = array();
	}

	foreach ($array as $key => $row)
	{
		foreach ($vals as $names)
		{
			$keyname    = "sort$names";
			$test       = array();
			$test[$key] = $row[$names];
			$$keyname   = array_merge($$keyname,$test);
		}
	}

	if ( $order == "d" )
	{
		if ( $type == "num" )
		{
			array_multisort($$sortby, SORT_DESC, SORT_NUMERIC, $array);
		}
		else
		{
			array_multisort($$sortby, SORT_DESC, SORT_STRING, $array);
		}
	}
	else
	{
		if ( $type == "num" )
		{
			array_multisort($$sortby, SORT_ASC, SORT_NUMERIC, $array);
		}
		else
		{
			array_multisort($$sortby, SORT_ASC, SORT_STRING, $array);
		}
	}
	return $array;
}


//******************************************************************************
function doCSV_to_Array($csvFile)
{
	if ( strpos($csvFile, "@@") == false )
	{
		return False;
	}
	else
	{
		$i = 0;
		$aRows = explode("@@", $csvFile, -1);
		foreach ($aRows as $value)
		{
			$j = 0;
			$aCols = explode("||", $value, -1);
			foreach ($aCols as $value1)
			{
				$aArray[$i][$j] = $value1;
				$j++;
			}
			$i++;
		}
		return $aArray;
	}
}


//******************************************************************************
function doCSV_to_KeyArray($csvFile)
{
	if ( strpos($csvFile, "@@") == false )
	{
		return False;
	}
	else
	{
		$i = 0;
		$aRows = explode("@@", $csvFile, -1);
		foreach ($aRows as $value)
		{
			$j = 0;
			$aCols = explode("||", $value, -1);
			foreach ($aCols as $value1)
			{
				$keyPos = strpos($value1, "==");
				if ( $keyPos > 0 )
				{
					$key = substr($value1, 0, $keyPos);
					$value1 = substr($value1, $keyPos+2);
				}
				else
				{
					$key = "key".$j;
				}
				$aArray[$i][$key] = $value1;
				$j++;
			}
			$i++;
		}
		return $aArray;
	}
}


//******************************************************************************
function drawArray_to_Table($xmlTableLayout, $aData, $pageId)
{
	global $curPage;
	global $sortOrder;
	global $sortCol;
	global $eDateTimeFormat;
  
	if ( !is_array($aData) ) { printSafe($aData); }
  
	$numRows       = 0;
	$sort          = "";
	$sortKey       = "";
	$search        = "";
	$csvHeader     = xsltTransform($xmlTableLayout, gSOURCE_URI, gXSL_HEADER_PATH, gSOURCE_URI);   // get table layout definition as csv string
	$tableDef      = explode("@@", $csvHeader, -1);
	$tableHead     = explode("||", $tableDef[0]);
	$tableTitle    = trim($tableHead[0]);               // table title
	$tableCols     = trim($tableHead[1]);               // number of columns
	$tableNumbered = trim(strtolower($tableHead[2]));   // display row numbers
	$tableShow     = trim($tableHead[3]);               // max number of rows to show on each page
	$tableMax      = trim($tableHead[4]);               // max number of rows returned from search
  
	if ( $sortCol > $tableCols ) { $sortCol = 0; }
	if ( $tableShow == "" )      { $tableShow = 0; }
	if ( $tableMax == "" )       { $tableMax = 0; }
	if ( isset($_GET['sort']) )
	{
		$sort = "&sort=".urlencode($_GET['sort']);
	}
	if ( isset($_GET['search']) )
	{
		$search = "&search=".urlencode($_GET['search']);
	}
  
	$sepChar = "?";
	if ( strpos($pageId, "?") > 0 )
	{
		$sepChar = "&";
	}
  
	if ( is_array($aData) )
	{
		$numRows = count($aData);

		// table title
		print("<table summary='".$tableTitle."'");
		if ( $tableNumbered == "true" ) { print(" class='rowNumbers'"); }
		print(">\n".i1."<thead>\n".i2."<tr>\n");
		if ( $tableNumbered == "true" ) { print(i3."<td style='border-bottom: 0px;'></td>\n"); }
		print(i3."<td colspan='".$tableCols."'>".$tableTitle."</td>\n".i2."</tr>\n");

		// page pagination
		if ( $numRows > $tableShow or $search <> "" )
		{
			// Pagination settings.
			$itemsPerPage = $tableShow;
			$pagesPerPage = 20;
			
			// Pagination calculations.
			$pageNumber = $curPage;
			$numItems = $numRows;
			$numPages = getNumPages($numItems, $itemsPerPage, $pageNumber);
		
			$curPage = $pageNumber;
		
			$startIndex = getStartIndex($pageNumber, $itemsPerPage);
			$endIndex = getEndIndex($numItems, $startIndex, $itemsPerPage);
		
			$startPage = getStartPage($pageNumber, $pagesPerPage);
			$endPage = getEndPage($numPages, $startPage, $pagesPerPage);
			 
			print(i2."<tr>\n");
			if ( $tableNumbered == "true" ) { print(i3."<td style='border: 0px; background: transparent;'></td>\n"); }
			print(i3."<td class='resultListHeader' style='border-right: 1px solid #dddddd;' colspan='".$tableCols."'>\n");
			
			// Results information.
			$additionalText = "";
			if ( $tableMax > 0 && $tableMax <= $numRows )
			{
				$additionalText .= " <span style=\"color: #ff0000;\">(from top $tableMax)</span>";
			}

			if ( isset($_GET['search']) )
			{
				if ( $_GET['search'] <> "" ) { $additionalText .= " for '".esc($_GET['search'])."'"; }
			}
			print("<span style=\"\">");
			drawResultsInfo($startIndex, $endIndex, $numRows, $additionalText);
			print("</span>");
			
			// Pagination
			print("&nbsp;&nbsp;");
			$uri = $pageId.$sepChar.$sort.$search;
			drawPagination($numPages, $pageNumber, $startPage, $endPage, $uri);
			
			print(i3."</td>\n".i2."</tr>\n");
		}

		// table header
		$i = 0;
		print(i1."</thead>\n".i1."<tbody>\n".i2."<tr>\n");
		if ( $tableNumbered == "true" ) { print(i3."<th style='border-left: 0px;'></th>\n"); }

		$sortSet = false;
		foreach ($tableDef as $value)
		{
			$colHead = explode("||", $value);
			$order   = trim(strtolower($colHead[3]));  // sort order 'a' (ascending) or 'd' (descending)
			if ( $order == "a" or $order == "d" ) { $sortSet = true; }
		}
		print(" ");

		$typeSet = "string";
		foreach ($tableDef as $value)
		{
			if ( $i > 0 )
			{
				$colHead = explode("||", $value);
				$id      = trim($colHead[0]);              // column heading id
				$display = trim($colHead[1]);              // column heading text
				$align   = trim($colHead[2]);              // column alignment 'left', 'center' or 'right'
				$order   = trim(strtolower($colHead[3]));  // sort order 'a' (ascending) or 'd' (descending)
				$type    = trim(strtolower($colHead[4]));  // sort type 'num' (numeric), 'string', 'link' or 'hidden'
				$sort    = false;

				if ( $type <> "hidden" )
				{
					if ( isset($_GET['sort']) )
					{
						setSortOrder($_GET['sort']);
						setSortCol($_GET['sort'], $tableCols);
						if ( $i == $sortCol+1 )
						{
							$sort    = true;
							$sortKey = $id;
						}
					}
					else
					{
						if ( $order == "a" or $order == "d" )
						{
							$sortOrder = strtolower($order);
							$sortCol   = $i-1;
							$sortKey   = $id;
							$sort      = true;
						}
					}
					if ( !$sortSet && !isset($_GET['sort']) && $i == 1 ) { $sort = true; }
					if ( $sort ) { $typeSet = $type; }

					if ( $sortOrder == "a" )
					{
						$flipSort = "d";
					}
					else
					{
						$flipSort = "a";
					}

					if ($type == "link")
					{
						print(i3."<th align='".$align."'>".$display);
					}
					else 
					{
						if ( $sort )
						{
							$intTemp = $i - 1;
							print(i3."<th align='".$align."' class='currentSortColHeader' title='Reverse the sort order on this column' onclick=\"window.location='".esc($pageId.$sepChar."page=".$curPage."&sort=".$flipSort.$intTemp.$search)."'\">");
							print("<a href='".esc($pageId.$sepChar."page=".$curPage."&sort=".$flipSort.$intTemp.$search)."'>".$display."</a>");
						}
						else
						{
							$intTemp = $i - 1;
							print(i3."<th align='".$align."' class='sortableColHeader'  title='Sort on this column' onclick=\"window.location='".esc($pageId.$sepChar."page=".$curPage."&sort=a".$intTemp.$search)."'\">");
							print("<a href='".esc($pageId.$sepChar."page=".$curPage."&sort=a".$intTemp.$search)."'>".$display."</a>");
						}

						if ( $sort )
						{
							print("&nbsp;&nbsp;<a href='".esc($pageId.$sepChar."page=".$curPage."&sort=".$flipSort.$sortCol.$search)."'>");
							if ( $sortOrder == "a" )
							{
								print("<img src='".gPAG_CONTROL_PATH."arrow_up.gif' alt='' width='11' height='9' />");
							}
							else
							{
								print("<img src='".gPAG_CONTROL_PATH."arrow_down.gif' alt='' width='11' height='9' />");
							}
							print("</a>");
						}
					}
					print("</th>\n");
				}
			}
			$i++;
		}
		print(i2."</tr>\n");

		// sort data
		if ( $sortKey == "" )
		{
			$aData = sortMDArray($aData, $sortCol, $sortOrder, $typeSet);
		}
		else
		{
			$aData = sortMDArray($aData, $sortKey, $sortOrder, $typeSet);
		}

		// table data rows
		$k = 1;
		$j = 1;
		foreach ($aData as $value)
		{
			$minShow = $curPage * $tableShow - $tableShow + 1;
			if ( $tableShow == 0 or ($j <= $tableShow and $k >= $minShow) )
			{
				print(i2."<tr id='row$k' valign='top'>\n");


				$fieldActions = ' onmouseover="recordOver(\'row'.$k.'\', false)" onmouseout="recordOut(\'row'.$k.'\', false)"';
				if ( $tableNumbered == "true" ) {
					print(i3."<td$fieldActions>".$k."</td>\n");
				}
				$i = 1;
				foreach ($value as $value1)
				{
					$colHead  = explode("||", $tableDef[$i]);
					$align    = $colHead[2];               // column alignment 'left', 'center' or 'right'
					$type     = strtolower($colHead[4]);   // sort type 'num' (numeric), 'string' or 'link'
					$linkRef  = $colHead[5];               // link ref (where appropriate)
					$linkName = $colHead[6];               // link ref name (where appropriate)
					$linkType = $colHead[7];               // link ref type 'text' or 'button' (where appropriate)
					$linkKey  = $colHead[8];               // link key - use column specified as key value (where appropriate)

					if ( $type <> "hidden" )
					{
						if ( strpos($value1, "]]") > 0 )
						{
							$bg = substr($value1, strpos($value1, "[[")+2, strpos($value1, "]]")-strpos($value1, "[[")-2);
							$prefix = i3."<td align='".$align."' style='padding-right: 5px; background-color: $bg;'";
							$value1 = substr($value1, 0, strpos($value1, "[["));
						}
						else
						{
							$prefix = i3."<td align='".$align."' style='padding-right: 5px;'";
						}

						$prefix .= $fieldActions;
													
						switch ($type)
						{
							case "date":
								$value1 = formatDateTime($value1, gDATE);
								break;
							case "time":
								$value1 = formatDateTime($value1, gTIME);
								break;
							case "datetime":
								$value1 = formatDateTime($value1, gDATETIME);
								break;
						}

						if ( $linkRef <> "" )
						{
							if ( $linkName == "" )
							{
								$linktemp = $value1;
							}
							else
							{
								$linktemp = $linkName;
							}
							if ( $linkKey <> "" )
							{
								if( is_numeric($linkKey) )
								{
									$keyvalue = $linkKey - 1;
									if ( $keyvalue < 0 or $keyvalue > $tableCols ) { $keyvalue = 0; }
								}
								else
								{
									$keyvalue = $linkKey;
								}
								//$key = trim($value[$keyvalue]);
								$key = $value[$keyvalue];
							}
							else
							{
								//$key = trim($value1);
								$key = $value1;
							}
							
							$key = urlencode($key);
								
							if ( $key == "" )
							{
								// Print the PREFIX
								print($prefix.">\n");
								printSafe($linktemp);
								print("&nbsp;");
							}
							else
							{
								$prefix .= " title=\"View this record\" onclick=\"window.location='".esc($linkRef.$key, true)."'\" class=\"recordLink\"";
								// Print the PREFIX
								print($prefix.">\n");
								if ( $linkType == "button" )
								{
									print("<input type=\"button\" class=\"buttonSmall\" name=\"link$i\" value=\"".esc($linktemp)."\" onclick=\"window.location='".esc($linkRef.$key, true)."'\" />");
								}
								else
								{
									print("<a href=\"".esc($linkRef.$key)."\">".esc($linktemp)."</a>");
								}
							}
						}
						else
						{
							// Print the PREFIX
							print($prefix.">\n");
							printSafe($value1);
						}

						// Print the SUFFIX
						print("\n".i3."</td>\n");
					}
					$i++;
				}
				print(i2."</tr>\n");
				$j++;
			}
			$k++;
		}
		print(i1."</tbody>\n</table>\n");
	}
}

function getArrayFromArray($arrayOfColumnKeysInOrder, $keyedArray)
{
	$newArray = array();
	foreach( $arrayOfColumnKeysInOrder as $column )
	{
		for( $i=0; $i < count($keyedArray); $i++ )
		{
			if( isset($keyedArray[$i][$column]) )
			{
				$newArray[$i][$column] = $keyedArray[$i][$column];
			}
			else
			{
				$newArray[$i][$column] = null;
			}
		}
	}	
	
	return $newArray;
}
?>