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

// Constants used by the HTML presentation functions.
define('gORCA_HTML_LIST_NO_TYPES', 0);
define('gORCA_HTML_LIST', 1);
define('gORCA_HTML_TABLE', 2);

function print_pre($variable, $ret = false)
{
	if (!$ret)
	{
		echo "<pre>"; var_dump($variable); echo "</pre><br/>";
	}
	else
	{
		return var_export($variable, true);
	}
}

function getRegistryObjectStatusColor($status)
{
	$defaultColor = "#990000"; // maroon color, unknown state
	global $gORCA_STATUS_INFO;

	if (isset($gORCA_STATUS_INFO[$status]['colour']))
	{
		return $gORCA_STATUS_INFO[$status]['colour'];
	}
	else
	{
		return $defaultColor;
	}
}

function getRegistryObjectStatusInfo($status)
{
	global $gORCA_STATUS_INFO;
	$defaultStatus = array("span"=>$status, "display"=>"Unknown Status: $status");

	if (isset($gORCA_STATUS_INFO[$status]))
	{
		return $gORCA_STATUS_INFO[$status];
	}
	else
	{
		return $defaultStatus;
	}
}


function getRegistryObjectStatusSpan($status, $short=false)
{

	$status = trim($status); // get rid of stupid fixed length char field padding

	$statusColour = getRegistryObjectStatusColor($status);
	$statusText = getRegistryObjectStatusInfo($status);
	if ($short)
	{
		$statusText = $statusText['short_span'];
	}
	else
	{
		$statusText = $statusText['span'];
	}
	$statusSpan = '<span style="color: #ffffff; background-color: '.$statusColour.'; border: 1px solid #888888; padding-left: 2px; padding-right: 2px;">'.esc($statusText).'</span>';

	return $statusSpan;
}



/* Complicated way to get a table which
 * shows the logo in an intelligent way
 * +-------+------+
 * |       |      |
 * +-------| logo |
 * |       |      |
 * +-------+------+
 * etc.
 */
function getColspan()
{
	global $rowsPrinted, $logo;
	if (!isset($rowsPrinted) || !isset($logo) || $logo === false)
	{
		return "1";
	}

	$rowsPrinted++;
	if ($rowsPrinted > 4)
	{
		return "2";
	}
	else
	{
		return "1";
	}
}

function drawRecordField($safeLabelText, $safeValueText)
{
	$html  = "		<tr>\n";
	$html .= "			<td>$safeLabelText</td>\n";
	$html .= "			<td style='width:100%;position:relative;'  colspan='".getColspan()."'>$safeValueText</td>\n";
	$html .= "		</tr>\n";
	print($html);
}

function getDateRangeHTML($dateFrom, $dateTo, $dateFormat)
{
	$from = null;formatDateTime($dateFrom, $dateFormat);
	$to = null;formatDateTime($dateTo, $dateFormat);
	$dates = "";
	if( $from )
	{
		$dates = "from $from";
	}
	if( $to )
	{
		if( $dates )
		{
			$dates .= " ";
		}
		$dates .= "to $to";
	}
	if( $dateFrom || $dateTo )
	{
		$dates = " <span class=\"dates\" title=\"date range\">$dates</span> ";
	}
	return $dates;
}

function getNameHTML($registryObjectKey, $queryText='')
{
	$html = '';
	$names = getNames($registryObjectKey);

	$rdaName = '';
	$altRdaName = '';
	if( $ComplexNames = getComplexNames($registryObjectKey))
	{
			$hasPrimary = false;
			$alsoKnownAs = array();

			foreach ( $ComplexNames as $row )
			{
				if (strtolower($row['type']) == "primary")
				{
					if($rdaName == '')
					{
						$rdaName = rdaGetNameParts2($row['complex_name_id']);
					}
				}
			    else
			    {
			    	//if($altRdaName == '')
					//{
						$altRdaName = " ".rdaGetNameParts2($row['complex_name_id']);
					//}

				} // end type check

			} // end loop
			//$rdaName = $altRdaName;
			//$rdaName = trim($rdaName,' '.gCHAR_MIDDOT.' ');

		if($rdaName != '')
		{
			$altRdaName = $rdaName;
		}
	}


	if( $queryText )
	{
		$html .= highlightQuery($altRdaName, $queryText);
	}
	else
	{
		$html .= esc($altRdaName);
	}

	return $html;
}

function getComplexNamesHTML($registryObjectKey)
{
	$searchBaseURI = 'search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';
	$html = '';
	$complexNames = getComplexNames($registryObjectKey);
	if( $complexNames )
	{
		$html = '<table class="subtable">'."\n";
		for( $i = 0; $i < count($complexNames); $i++ )
		{
			$dates = getDateRangeHTML($complexNames[$i]['date_from'], $complexNames[$i]['date_to'], gDATE);
			if( $dates || $complexNames[$i]['type'] )
			{
				$html .= '<tr><td colspan="2">';
				if( $complexNames[$i]['type'] )
				{
					$html .= '<span class="majorAttribute" title="complex name type">'.esc($complexNames[$i]['type']).':</span> ';
				}
				$html .= $dates."</td></tr>\n";
			}

			$nameParts = getNameParts($complexNames[$i]['complex_name_id']);
			if( $nameParts )
			{
				for( $j = 0; $j < count($nameParts); $j++ )
				{
					$style = '';
					if( $i < count($complexNames)-1 && $j == count($nameParts)-1 )
					{
						$style = ' style="border-bottom: 1px solid #dddddd; padding-bottom: 2px;"';
					}
					$html .= '<tr>';
					$html .= '<td'.$style.' class="attribute" title="name part type">';
					if( $nameParts[$j]['type'] )
					{
						$html .= esc($nameParts[$j]['type']).':';
					}
					$html .= "</td>";
					$html .= '<td'.$style.'><a class="search" title="Search for this name" href="'.$searchBaseURI.esc(urlencode($nameParts[$j]['value'])).'">'.escWithBreaks($nameParts[$j]['value']).'</a></td>';
					$html .= "</tr>\n";
				}
			}
		}
		$html .= '</table>'."\n";
	}
	return $html;
}

function getIdentifiersHTML($registryObjectKey, $HTMLtype, $queryText='')
{
	$searchBaseURI = 'search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';

	$table = false;
	if( $HTMLtype == gORCA_HTML_TABLE )
	{
		$table = true;
	}
	$html = '';
	$identifiers = getIdentifiers($registryObjectKey);
	if( $identifiers )
	{
		if( $table )
		{
			$html = '<table class="subtable">'."\n";
		}
		for( $i = 0; $i < count($identifiers); $i++ )
		{
			if( $table )
			{
				$html .= '<tr>';
				$html .= '<td class="attribute" title="identifier type">'.esc($identifiers[$i]['type']).":</td>";
				$html .= '<td><a class="search" title="Search for this identifier" href="'.$searchBaseURI.esc(urlencode($identifiers[$i]['value'])).'">'.escWithBreaks($identifiers[$i]['value']).'</a></td>';
				$html .= "</tr>\n";
			}
			else
			{
				if( $i != 0 )
				{
					$html .= ' '.gCHAR_MIDDOT.' ';
				}
				$html .= '<span class="attribute" title="identifier type">'.esc($identifiers[$i]['type']).":</span> ";
				if( $queryText )
				{
					$html .= '<a class="search" title="Search for this identifier" href="'.$searchBaseURI.esc(urlencode($identifiers[$i]['value'])).'">'.highlightQuery($identifiers[$i]['value'], $queryText).'</a>';
				}
				else
				{
					$html .= '<a class="search" title="Search for this identifier" href="'.$searchBaseURI.esc(urlencode($identifiers[$i]['value'])).'">'.esc($identifiers[$i]['value']).'</a>';
				}
			}
		}
		if( $table )
		{
			$html .= '</table>'."\n";
		}
	}
	return $html;
}

function getRelationsHTML($registryObjectKey, $HTMLtype)
{
	$table = false;
	$maxRelationsToShow = 20;
	$relatedObjects = getRelatedObjects($registryObjectKey);
	if( $HTMLtype == gORCA_HTML_TABLE )
	{
		$table = true;
		$maxRelationsToShow = count($relatedObjects);
	}
	$html = '';
	if( $relatedObjects )
	{
		if( $table )
		{
			$html = '<table class="subtable">'."\n";
		}
		for( $i = 0; $i < count($relatedObjects) && $i < $maxRelationsToShow; $i++ )
		{
			$relatedRegistryObjectKey = $relatedObjects[$i]['related_registry_object_key'];
			$relationDescriptions = getRelationDescriptions($relatedObjects[$i]['relation_id']);
			foreach( $relationDescriptions as $relationDescription )
			{
				$relation = '';
				if( $relationDescription['description'] )
				{
					$relation = esc($relationDescription['description'])." ";
				}

				if( $relatedObject=getRegistryObject($relatedRegistryObjectKey) )
				{
					if( trim($relatedObject[0]['status']) == PUBLISHED || userIsORCA_ADMIN() )
					{
						// The related object exists in the registry.
						$relationName = getNameHTML($relatedRegistryObjectKey);
						if( $relationName == '' )
						{
							$relationName .= esc($relatedRegistryObjectKey);
						}
						$relation .= '<a href="view.php?'.esc("key=".urlencode($relatedRegistryObjectKey)).'" title="View this record">';
						$relation .= $relationName;
						$relation .= "</a>";
					}
					else
					{
						$relation .= $relatedRegistryObjectKey;
					}
				}
				else
				{
					$relation .= $relatedRegistryObjectKey;
				}


				if( $table )
				{
					$url = '';
					if( $relationDescription['url'] )
					{
						$url = '&nbsp;&nbsp;<span class="attribute">url:</span>&nbsp;';

						// Fix relative URLs.
						$href = $relationDescription['url'];
						if( !preg_match('/^[a-zA-Z]{0,5}:\/\/.*/', $href) )
						{
							$href = 'http://'.$href;
						}

						$url .= '<a href="'.esc($href).'" class="external" title="'.esc($href).'">'.esc($href).'<img class="external" src="'.gORCA_IMAGE_ROOT.'external_link.gif" alt="" /></a>';
					}
					$html .= '<tr>';
					$html .= '<td class="attribute" title="relation type">'.esc($relationDescription['type']).":</td>";
					$html .= '<td>'.$relation.$url.'</td>';
					$html .= "</tr>\n";
				}
				else
				{
					$html .= $relation.";&nbsp;";
				}
			}
		}
		if( $table )
		{
			$html .= '</table>'."\n";
		}
		elseif( count($relatedObjects) > $maxRelationsToShow )
		{
			$html .= " ...";
		}
	}
	return $html;
}

function getSubjectsHTML($registryObjectKey, $HTMLtype, $queryText='')
{
	$searchBaseURI = 'search.php?collections=collection&amp;services=service&amp;parties=party&amp;activities=activity&amp;action=Search&amp;search=';

	$table = false;
	if( $HTMLtype == gORCA_HTML_TABLE )
	{
		$table = true;
	}
	$html = '';
	$subjects = getSubjects($registryObjectKey);
	if( $subjects )
	{
		if( $table )
		{
			$html = '<table class="subtable">'."\n";
		}
		for( $i = 0; $i < count($subjects); $i++ )
		{
			if( $table )
			{
				$html .= '<tr>';
				$html .= '<td class="attribute" title="subject type">'.esc($subjects[$i]['type']).":</td>";
				$html .= '<td><a class="search" title="Search for this subject" href="'.$searchBaseURI.esc(urlencode($subjects[$i]['value'])).'">'.escWithBreaks($subjects[$i]['value']).'</a></td>';
				$html .= "</tr>\n";
			}
			else
			{
				if( $i != 0 )
				{
					$html .= ' '.gCHAR_MIDDOT.' ';
				}
				$html .= '<span class="attribute" title="subject type">'.esc($subjects[$i]['type']).":</span> ";
				if( $queryText )
				{
					$html .= '<a class="search" title="Search for this subject" href="'.$searchBaseURI.esc(urlencode($subjects[$i]['value'])).'">'.highlightQuery($subjects[$i]['value'], $queryText).'</a>';
				}
				else
				{
					$html .= '<a class="search" title="Search for this subject" href="'.$searchBaseURI.esc(urlencode($subjects[$i]['value'])).'">'.esc($subjects[$i]['value']).'</a>';
				}
			}
		}
		if( $table )
		{
			$html .= '</table>'."\n";
		}
	}
	return $html;
}

function getDescriptionsHTML($registryObjectKey, $HTMLtype, $queryText='', $suppressLogos=true)
{
	$table = false;
	if( $HTMLtype == gORCA_HTML_TABLE )
	{
		$table = true;
	}
	$html = '';
	$descriptions = getDescriptions($registryObjectKey);
	if( $descriptions )
	{
		if( $table )
		{
			$html = '<table class="subtable">'."\n";
		}
		for( $i = 0; $i < count($descriptions); $i++ )
		{
			if ($suppressLogos && strtolower($descriptions[$i]['type']) == 'logo')
			{
				continue;
			}

			if( $table )
			{
				$html .= '<tr>';
				$html .= '<td class="attribute" title="description type">'.esc($descriptions[$i]['type']).":</td>";
				$html .= '<td>'.escWithBreaks($descriptions[$i]['value']).'</td>';
				$html .= "</tr>\n";
			}
			else
			{
				$descr = $descriptions[$i]['value'];
				$html .= '<span class="attribute" title="description type">'.esc($descriptions[$i]['type']).":</span> ";
				if( $queryText )
				{
					$html .= highlightQuery($descr, $queryText);
				}
				else
				{
					$html .= esc($descr);
				}
				if( count($descriptions) > 1 && $i < count($descriptions)-1 )
				{
					$html .= "<br />\n";
				}
			}
		}
		if( $table )
		{
			$html .= '</table>'."\n";
		}
	}
	return $html;
}

define("gORCA_HIGHLIGHT_STARTS_WITH", 0);
define("gORCA_HIGHLIGHT_ENDS_WITH", 1);
define("gORCA_HIGHLIGHT_ALL", 2);

function highlightSearchTerm($safeText, $safeTerm, $location)
{
	$pos = 0;
	$offset = 0;
	$len = strlen($safeTerm);
	$text = '';
	$html = $safeText;
	$reTerm = $safeTerm;
	$reTerm = str_replace("\\", "\\\\", $reTerm);
	$reTerm = str_replace("/", "\/", $reTerm);

	$prefix = '<span style="background-color: #ffe84a; border: 1px solid #888888; padding-left: 2px; padding-right: 2px;">';
	$suffix = '</span>';

	if( $safeText && $safeTerm )
	{
		switch( $location )
		{
			case gORCA_HIGHLIGHT_STARTS_WITH:
				if( ($pos = strpos(strtoupper($html), strtoupper($safeTerm), 0)) === 0 )
				{
					$before = substr($html, 0, $pos);
					$text = substr($html, $pos, $len);
					$after = substr($html, $pos+$len, strlen($html)-($pos+$len));
					$html = "$before$prefix$text$suffix$after";
				}
				break;

			case gORCA_HIGHLIGHT_ENDS_WITH:
				if( ($pos = strpos(strtoupper($html), strtoupper($safeTerm), 0)) === strlen($safeText)-$len )
				{
					$before = substr($html, 0, $pos);
					$text = substr($html, $pos, $len);
					$after = substr($html, $pos+$len, strlen($html)-($pos+$len));
					$html = "$before$prefix$text$suffix$after";
				}
				break;

			case gORCA_HIGHLIGHT_ALL:
				while( ($pos = strpos(strtoupper($html), strtoupper($safeTerm), $offset)) !== false  )
				{
					$before = substr($html, 0, $pos);
					$text = substr($html, $pos, $len);
					$after = substr($html, $pos+$len, strlen($html)-($pos+$len));
					$offset = $pos+strlen($text);

					if( !preg_match("/^$reTerm"."[^<>]*>/is", substr($html, $pos), $matches) )
					{
						$before = $before.$prefix;
						$after = $suffix.$after;
						$offset = $pos+strlen($prefix.$text.$suffix);
					}
					$html = $before.$text.$after;
				}
				break;
		}
	}

	return $html;
}

function highlightQuery($text, $queryText)
{
	$html = '';
	$prefix = '<span style="background-color: #ffe84a; border: 1px solid #888888; padding-left: 2px; padding-right: 2px;">';
	$suffix = '</span>';

	$markedText = getHighlightedQueryText($text, $queryText);

	$html = esc($markedText);
	$html = str_replace("@@@@", $prefix, $html);
	$html = str_replace("$$$$", $suffix, $html);

	return $html;
}

function drawVocabControl($fieldId, $vocabId, $termId=null)
{
	$controlId = $fieldId.'_'.$vocabId;
	$onclick = "vcDisplayVocabControl('".esc($fieldId)."','".esc($controlId)."')";
	print('<img class="vcIcon" id="'.esc($controlId).'_vcIcon" alt="" title="Suggested vocabulary" src="'.esc(gORCA_IMAGE_ROOT).'_controls/_vocab_control/vc_icon_inactive.gif" onclick="'.$onclick.'"/>'."\n");
	print('<div id="'.esc($controlId).'" style="display: none; position: absolute; z-index: 100;" class="vocabControl">'."\n");
	// Inner Container
	print('<div class="vcInnerContainer">'."\n");
	// Close Bar
	print('<div class="vcCloseBar" onmousedown="startMove(event, getObject(\''.esc($controlId).'\'))"><img src="'.esc(gORCA_IMAGE_ROOT).'_controls/_vocab_control/vc_close.gif" alt="" title="Close" class="vcClose" onclick="vcCloseVocabControl(\''.esc($controlId).'\')" /></div>'."\n");
	print('<div class="vcScrollPane">'."\n");
	print('<div class="vcContent">'."\n");
	$vocabHTML = getVocabularyHTML($vocabId, $termId);
	$vocabHTML = str_replace("@@FIELDID@@", esc($fieldId, true), $vocabHTML);
	$vocabHTML = str_replace("@@CONTROLID@@", esc($controlId, true), $vocabHTML);
	print($vocabHTML);
	print("</div>\n");
	print("</div>\n");
	print("</div>\n");
	print("</div>\n");
}

// Global to hold retrieved vocabs as a cache.
$gORCA_VOCABS_OPTION_HTML = Array();

function getVocabularyHTML($vocabId, $termId=null)
{
	global $gORCA_VOCABS_OPTION_HTML;

	$vocabHTML = '';
	// Check to see if we've already retrieved this vocab.
	if( isset($gORCA_VOCABS_OPTION_HTML[$vocabId]) )
	{
		$vocabHTML = $gORCA_VOCABS_OPTION_HTML[$vocabId];
	}
	else
	{
		// Default to the local vocab.
		$url = 'http://'.eHOST.'/'.eROOT_DIR.'/'.'orca/vocabularies/vocabs.xml';
		if( gORCA_VOCABS_BASE_URI )
		{
			// Hit the configured service for the vocab.
			$url = gORCA_VOCABS_BASE_URI."?id=".urlencode($vocabId);
			if( $termId )
			{
				$url .= "&termid=".urlencode($termId);
			}
		}
		// Load and parse the vocab.
		$vocabulary = new DOMDocument();
		$result = $vocabulary->load($url);
		$errors = error_get_last();
		if( !$errors )
		{
			// Parse the xml and turn it into an array.
			// Get an xpath object to use for parsing the XML.
			$XPath = new DOMXpath($vocabulary);
			// Get the default namespace of the registryObjects object.
			$defaultNamespace = $XPath->evaluate('/*')->item(0)->namespaceURI;
			// Register a prefix for the default namespace so that we can actually use the xpath object.
			$XPath->registerNamespace('vc', $defaultNamespace);

			if( $XPath->evaluate("//vc:vocabulary[vc:identifier=\"$vocabId\"]")->item(0) )
			{
				$vocabNode = $XPath->evaluate("//vc:vocabulary[vc:identifier=\"$vocabId\"]")->item(0);
				if( $termId )
				{
					if( $XPath->evaluate(".//vc:term[vc:identifier[@type=\"local\"]=\"$termId\"]", $vocabNode)->item(0) )
					{
						$vocabNode = $XPath->evaluate(".//vc:term[vc:identifier[@type=\"local\"]=\"$termId\"]", $vocabNode)->item(0);
					}
					else
					{
						$vocabNode = null;
					}
				}

				if( $vocabNode )
				{
					$name = $XPath->evaluate("vc:name", $vocabNode)->item(0)->nodeValue;
					$vocabHTML .= '<b>'.esc($name)."</b><br />\n";
					$vocabHTML .= '<div class="vcTermGroup">'."\n";
					getTermsHTML($XPath, $vocabNode, &$vocabHTML);
					$vocabHTML .= "</div>\n";
				}
				else
				{
					$vocabHTML .= '<div class="vcTermGroup">'."\n";
					$vocabHTML .= "</div>\n";
				}

				// Put the vocab into the cache so we can reuse it.
				$gORCA_VOCABS_OPTION_HTML[$vocabId] = $vocabHTML;
			}

		}
	}
	return $vocabHTML;
}

function getTermsHTML($XPath, $termNode, $vocabHTML)
{
	if( $XPath->evaluate("vc:term", $termNode) )
	{
		$terms = $XPath->evaluate("vc:term", $termNode);
		foreach( $terms as $term )
		{
			$type = $term->getAttribute("type");
			$name = $XPath->evaluate("vc:name", $term)->item(0)->nodeValue;
			$identifier = $XPath->evaluate("vc:identifier", $term)->item(0)->nodeValue;

			if( $type == 'nl' )
			{
				$vocabHTML .= '<b>'.esc($name)."</b><br />\n";
				$vocabHTML .= '<div class="vcTermGroup">'."\n";
				getTermsHTML($XPath, $term, &$vocabHTML);
				$vocabHTML .= "</div>\n";
			}
			else
			{
				$onclick = "vcUpdateInputFieldValue('@@FIELDID@@','@@CONTROLID@@', '".esc($identifier, true)."')";
				$title = esc($identifier);
				$vocabHTML .= '  <div title="'.$title.'" class="vcTerm" onclick="'.$onclick.'">'.esc($name)."</div>\n";
			}
		}
	}
}


function rdaGetNameParts2($namePartId)
{
	$name = '';
	$names = getNameParts($namePartId);

	$initial = '';
	$given = '';
	$family = '';
	$suffix = '';
	$fullname = '';
	$title = '';

	if( $names )
	{
		$name = '';
		for( $i = 0; $i < count($names); $i++ )
		{
			switch($names[$i]['type'])
				{
					case "initial":
						$initial .= $names[$i]['value'].' '.gCHAR_MIDDOT.' ';
					break;
					case "family":
						$family .= $names[$i]['value'].' '.gCHAR_MIDDOT.' ';
					break;
					case "given":
						$given .= $names[$i]['value'].' '.gCHAR_MIDDOT.' ';
					break;
					case "suffix":
						$suffix .= ' '.gCHAR_MIDDOT.' '.$names[$i]['value'];
					break;
					case "title":
						$title .= $names[$i]['value'];
					break;
					default:
						$fullname .= $names[$i]['value'];
					break;

				}

				if($fullname)
				{
					$name = $fullname;
				}
					else
				{
					$name = $family.$given.$initial.$title.$suffix;
				}
		}
	}
	return $name;
}
function drawStatTable($typeStats=null){

	$dateFrom = date("Ym",mktime($hour=null,$minute=null,$second=null,$month=getQueryValue('monthFrom'),$day="2",$year=getQueryValue('yearFrom')));
	$periodDisplayFrom = date("M Y",mktime($hour=null,$minute=null,$second=null,$month=getQueryValue('monthFrom'),$day="1",$year=getQueryValue('yearFrom')));
	$dateFromMonth = date("Y-m-d",mktime($hour=null,$minute=null,$second=null,$month=getQueryValue('monthFrom'),$day="2",$year=getQueryValue('yearFrom')));
	$dateTo = date("Ym",mktime($hour=null,$minute=null,$second=null,$month=getQueryValue('monthTo'),$day="2",$year=getQueryValue('yearTo')));
	$periodDisplayTo = date("M Y",mktime($hour=null,$minute=null,$second=null,$month=getQueryValue('monthTo'),$day="1",$year=getQueryValue('yearTo')));
	$dateToMonth = date("Y-m-d",mktime($hour=null,$minute=null,$second=null,$month=getQueryValue('monthTo'),$day="1",$year=getQueryValue('yearTo')));

	if($periodDisplayFrom==$periodDisplayTo)
	{
		$periodDisplay = $periodDisplayTo;
	}
	else
	{
		$periodDisplay = $periodDisplayFrom." - ".$periodDisplayTo;
	}

	$months = get_months($dateFromMonth,$dateToMonth);
	$colspan= count($months["M"]) + 1;

	?>

	<table border="1" bordercolor="#CCCCCC">
		<tr><td colspan="<?php echo $colspan?>" style="font-size:1.2em;color:#003366">ANDS Management Report</td></tr>
		<tr><td colspan="<?php echo $colspan;?>">&nbsp;</td></tr>
		<tr><td width="200" style="background-color:#CCCC99;font-size:1.2em">PUBLIC SYSTEM:</td><td colspan="<?php echo ($colspan-1);?>" align="center" bgcolor="black" style="color:#FFFFFF;"><?php echo $periodDisplay;?></td></tr>
		<tr><td bgcolor="#99FFFF">Handle Service:</td><?php printMonths($months);?></tr>
		<tr><td>Handles Minted</td><?php printStatistics($months, "handles");?></tr>
		<tr><td>Trusted SW Agreement</td><?php printStatistics($months,"M2M");?></tr>
		<tr><td bgcolor="#99FFFF">DOI Service:</td><?php printHeader($months);?></tr>
		<tr><td>DOIs Minted</td><?php printStatistics($months, "dois");?></tr>
		<tr><td>Registered Publisher Agents</td><?php printStatistics($months,"doiClient");?></tr>
		<tr><td>DOI Minting failures</td><?php printStatistics($months,"doiMintFail");?></tr>
		<tr><td bgcolor="#99FFFF">Registry:</td><?php printHeader($months);?></tr>
		<tr><td>Organisations</td><?php printStatistics($months, "Organisations");?></tr>
		<tr><td>Users</td><?php printStatistics($months, "Users");?></tr>
		<tr><td>Data Source Admins</td><?php printStatistics($months, "Data");?></tr>
		<tr><td>Provider Org</td><?php printStatistics($months, "Provider");?></tr>
		<tr><td>Publish my Data</td><?php printStatistics($months, "Publish");?></tr>
		<tr><td>DIRECT</td><?php printStatistics($months, "DIRECT");?></tr>
		<tr><td>Harvestor DIRECT</td><?php printStatistics($months, "GET");?></tr>
		<tr><td>Harvestor OAI-PMH</td><?php printStatistics($months, "RIF");?></tr>
		<tr><td>Total records</td><?php printStatistics($months, "Total");?></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Collections</td><?php printStatistics($months, "Collections");?></tr>
		<?php if($typeStats) printSubStats($months,'Collection')?>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Parties</td><?php printStatistics($months, "Parties");?></tr>
		<?php if($typeStats) printSubStats($months,'Party')?>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Services</td><?php printStatistics($months, "Services");?></tr>
		<?php if($typeStats) printSubStats($months,'Service')?>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Activities</td><?php printStatistics($months, "Activities");?></tr>
		<?php if($typeStats) printSubStats($months,'Activity')?>
		<?php if($typeStats) { ?>
			<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Descriptions</td><td>&nbsp;</td></tr>
		<?php 	printDescriptionStats($months);
		}?>
		<?php if($typeStats) { ?>
			<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Related_info</td><td>&nbsp;</td></tr>
		<?php 	printRelatedInfoStats($months);
		} ?>
		<tr><td bgcolor="#99FFFF">Research Data Australia:</td><?php printHeader($months);?></tr>
		<tr><td>Page Views</td><?php printStatistics($months, "Page");?></tr>
		<tr><td>Site Visits</td><?php printStatistics($months, "Visit");?></tr>
	</table>

<?php
}

function printStatistics($months,$statType){

	foreach($months["m"] as $theMonth)
	{
		$theMonth = date("Y-m-d",(strtotime($theMonth.'+1 month')));
		switch ($statType)
		{
			case "handles":
				?>
			<td><?php echo getPidsCount(strtotime($theMonth));?></td><?php
			break
			;
			case "M2M":
				?>
			<td>
			<?php
			if($theMonth<date("Y-m-d"))
			{
					echo getStoredStatCount($theMonth,'trusted_sw_agreements_count');
			}
			else
			{
				echo getM2MCount(strtotime($theMonth));
			}?></td><?php
			break
			;
			case "dois":
				?>
			<td><?php echo getDoisCount($theMonth);?></td><?php
			break
			;
			case "doiClient":
				?>
			<td><?php echo getDoiClientCount($theMonth);?></td><?php
			break
			;
			case "doiMintFail":
				?>
			<td><?php echo getDoiMintFailCount($theMonth);?></td><?php
			break
			;
			case "Provider":
				?>
			<td><?php echo getDataSourceCount($theMonth);?></td><?php
			break
			;
			case "Organisations":
				?>
			<td><?php echo getOrganisationCount($theMonth);?></td><?php
			break
			;
			case "Users":
				?>
			<td><?php echo getUserCount($theMonth);?></td><?php
			break
			;
			case "Publish":
				?>
			<td><?php echo getPublishMyDataCount($theMonth);?></td><?php
			break
			;
			case "DIRECT":
				?>
			<td><?php echo getHarvestMethodCount($theMonth, 'DIRECT');?></td><?php
			break
			;
			case "GET":
				?>
			<td><?php echo getHarvestMethodCount($theMonth, 'GET');?></td><?php
			break
			;
			case "RIF":
				?>
			<td><?php getHarvestMethodCount($theMonth, 'RIF');?></td><?php
			break
			;
			case "Total":

				?>
			<td>
			<?php
			if($theMonth<date("Y-m-d"))
			{
				echo getStoredStatCount($theMonth,'registry_object_count');

			}
			else
			{
				echo getRegistryObjectStatCount($theMonth,$registryObjectClass=null);
			}
			?>
			</td><?php
			break
			;
			case "Collections":
				?>
			<td>
			<?php
			if($theMonth<date("Y-m-d"))
			{
				echo getStoredStatCount($theMonth,'collection_object_count');
			}
			else
			{
				echo getRegistryObjectStatCount($theMonth,$registryObjectClass='Collection');
			}
			?>
			</td><?php
			break
			;
			case "Parties":
				?>
			<td>
			<?php
			if($theMonth<date("Y-m-d"))
			{
				echo getStoredStatCount($theMonth,'party_object_count');
			}
			else
			{
				echo getRegistryObjectStatCount($theMonth,$registryObjectClass='Party');
			}?></td><?php
			break
			;
			case "Activities":
				?>
			<td>
			<?php
			if($theMonth<date("Y-m-d"))
			{
				echo getStoredStatCount($theMonth,'activity_object_count');
			}
			else
			{
				echo getRegistryObjectStatCount($theMonth,$registryObjectClass='Activity');
			}?></td><?php
			break
			;
			case "Services":
				?>
			<td>
			<?php
			if($theMonth<date("Y-m-d"))
			{
				echo getStoredStatCount($theMonth,'service_object_count');
			}
			else
			{
				echo getRegistryObjectStatCount($theMonth,$registryObjectClass='Service');
			}?></td><?php
			break
			;
			case "Data":
				?>
			<td><?php echo getDataSorceAdminCount($theMonth,$registryObjectClass=null);?></td><?php
			break
			;
			case "Page":
				?>
			<td valign="top"><?php echo getPageAccessCounts($theMonth); ?></td><?php
			break
			;
			case "Visit":
				?>
			<td valign="top"><?php echo getVisitorCounts($theMonth); ?></td><?php
			break
			;
			default:
				$thetype=explode("::",$statType);
				$theStats = getRegistryObjectTypeCount($theMonth,$thetype[0],$thetype[1]);
				?>
			<td style="color:#666666"><?php
				if($theStats)
				{
					$the_count = explode(",",trim(trim($theStats[0]["count"],")"),"("));
					$count = $the_count[1];
				}
				else
				{
					$count = 0;
				}
				print($count);
			?></td>
			<?php
		}

	}

}
function printSubStats($months,$theClass)
{
	$theStats = getRegistryObjectTypeCount($theMonth=null,$theClass,$theType=null);
	if($theStats)
	{

		foreach($theStats as $theStat) {
			$the_type = explode(",",trim(trim($theStat["count"],")"),"("));

?>
				<tr><td style="color:#666666">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $the_type[0];?></td><?php printStatistics($months, $theClass."::".$the_type[0]);?></tr>
		<?php }
	}
}
function printDescriptionStats($months)
{
	$theStats = getDescriptionTypeCount($theMonth=null);
	if($theStats)
	{

		foreach($theStats as $theStat) {
			$the_type = explode(",",trim(trim($theStat["count"],")"),"("));

?>
				<tr><td style="color:#666666">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $the_type[0]?></td><td style="color:#666666"><?php echo $the_type[1];?></td></tr>
		<?php }
	}
}
function printRelatedInfoStats($months)
{
	$theStats = getRelatedInfoTypeCount($theMonth=null);
	if($theStats)
	{

		foreach($theStats as $theStat) {
			$the_type = explode(",",trim(trim($theStat["count"],")"),"("));

?>
				<tr><td style="color:#666666">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $the_type[0]?></td><td style="color:#666666"><?php echo $the_type[1];?></td></tr>
		<?php }
	}
}
function printMonths($months)
{
	foreach($months["M"] as $theMonth)
	{
		?>
		<td bgcolor="#CCCCCC"><?php echo $theMonth;?></td>
		<?php
	}
}
function printHeader($months)
{
	foreach($months["M"] as $theMonth)
	{
		?>
		<td bgcolor="#CCCCCC"></td>
		<?php
	}
}

function convert_isosql_date_to_xsdatetime($date_string)
{
	$date_string = preg_replace("/^([-\\d]*) (.*)/", "\${1}T\${2}", $date_string);
	return $date_string;
}


function generateSlug($phrase, $maxLength = 255)
{
    $result = strtolower($phrase);

    $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
    $result = trim(preg_replace("/[\s-]+/", " ", $result));
    $result = trim(substr($result, 0, $maxLength));
    $result = preg_replace("/\s/", "-", $result);

    return $result;
}


function generateUniqueSlug($display_title, $key)
{
	// Get an initial slug based on title alone
	$slug = generateSlug($display_title);

	// if no name/title, then slug the key and hope for the best
	// these are pretty dumb records anyway...
	if ($slug == NO_NAME_OR_TITLE_SLUG)
	{
		$slug = generateSlug($key);
	}

	// see if our first attempt is unique
	// if the existing mapping is to our own key, we can consider it unique
	$existing_mappings = countOtherSLUGMappings($slug, $key);

	// if not, lets try adding the key to the end
	if ($existing_mappings > 0)
	{
		$key_slug = generateSlug($key);
		if ($slug != $key_slug)
		{
			$slug .= "-" . $key_slug;
		}
	}

	// keys aren't entirely unique once they're slugified, so add some dashes if still conflicting
	// (this is really unlikely!)
	$existing_mappings = countOtherSLUGMappings($slug, $key);

	while ($existing_mappings > 0)
	{
		$slug .= "-";
		$existing_mappings = countOtherSLUGMappings($slug, $key);
	}


	return $slug;
}


function stripExtendedRIFCS($payload)
{
	return preg_replace("/[ \\n+]*<extRif:(.*)( [^>]*|)>.*<\/extRif:\\1>([ ]*\\n+)?|[ ]*<extRif:.*\/>([ ]*\\n+)?| extRif:.*=\"[^\"]*\"|[ ]*xmlns:extRif=\".*\"[ ]*\\n+/imsU","", $payload);
	//|[ ]*<extRif:.*\/>([ ]*\\n+)?| extRif:.*=\"[^\"]*\"
}

function stripRegistryObjectsWrapper($payload)
{
	// strip the wrapper elements of a cached registry object
	return preg_replace("/[ ]*<registryObjects(.*)>[ ]*\\n*|[ ]*<\/registryObjects>[ ]*\\n*|\\n*[ ]*<\?xml(.*)?>[ ]*\\n*/imsU","", $payload);
}

function stripFormData($payload)
{
	// note: this function is untested -- might not work! also, doesn't remove @roclass !
	return preg_replace("/[ ]*field_id=\".*?\"[ ]*\\n+tab_id=\".*?\"/i","", $payload);
}

?>