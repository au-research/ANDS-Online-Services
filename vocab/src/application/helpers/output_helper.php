<?php
/*
Copyright 2011 The Australian National University
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

function arrayItemXmlResponse($array, $wrapper)
{
	$string = "";
	
	foreach ($array AS $array_item)
		$string .= "<$wrapper>".htmlentities($array_item)."</$wrapper>";
		
	return $string;
}

function authXmlResponse($result, $attributes, array $escaped = array())
{
	$wrapper_elt = "resultset";
	$string = "";
	
	$string .= "<$wrapper_elt>";
	$string .= "<result>$result</result>";
	foreach ($attributes AS $attr_name => $attr_value)
		$string .= "<$attr_name>" . (!in_array($attr_name, $escaped) ? htmlentities($attr_value) : $attr_value) . "</$attr_name>"; // just for minh! :D
	$string .= "</$wrapper_elt>";
	
	return $string;
}


function menuXmlResponse(array $menuItems = array())
{
	$wrapper_elt = "menuitems";
	$string = "";
	
	$string .= "<$wrapper_elt>";
	
	$string .= menuItemXmlResponse($menuItems);
	
	$string .= "</$wrapper_elt>";
	
	return $string;
}

function menuItemXmlResponse(array $menuItems)
{
	$string = "";
	$item_elt = "menuitem";

	foreach ($menuItems AS $item_key => $item_attributes)
	{
		if (count($item_attributes['child_items']) > 0)
		{
			$string .= "<$item_elt name='" . $item_attributes['name'] ."' link='" . $item_attributes['link'] . "'>" . menuItemXmlResponse($item_attributes['child_items']) . "</$item_elt>"; // just for minh! :D
		}
		else 
		{
			$string .= "<$item_elt name='" . $item_attributes['name'] ."' link='" . $item_attributes['link'] . "'></$item_elt>";
		}
	}
	
	return $string; 
}

function json_output($variable)
{
	echo(json_encode($variable));
}

function print_pre($variable)
{
	echo "<pre>".print_r($variable, true)."</pre>";
}