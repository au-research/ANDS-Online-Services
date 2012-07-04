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
//if (!IN_ORCA) die('No direct access to this file is permitted.');

error_reporting(E_ERROR | E_WARNING | E_PARSE);
if ($keyValue && $draft = getDraftRegistryObject($keyValue, $dataSourceValue))
{
	$thisDataSource = getDataSources($dataSourceValue, null);
	$preview = createPreview($draft[0]['rifcs'], $draft[0]['class'], $draft[0]['registry_object_data_source'],$draft[0]['date_created']);
	
	printHeaderContent();
	echo $preview;
	printFooter();

		
}


function printHeaderContent()
{
header("Content-Type: text/html; charset=UTF-8", true);
// Write the XML declaration.
print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<!--
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
-->
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<title><?php $keyValue ?></title>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/jquery.js"></script>
	<script type="text/javascript" src="<?php print eAPP_ROOT ?>_javascript/jquery-ui-1.8.17.custom.min.js"></script>
    <style type="text/css">body{font-family:Courier; font-size:0.7em;} table{border-collapse:collapse !important;width:100%;} .attribute{width:auto;} td{border:1px solid #ccc; padding:7px; width:100%;} </style>
<script type='text/javascript'>
	 function loadRelatedTitles()
	 {
		 $('.resolvable_key').each(function(){
			 //console.log ("hello" + encodeURIComponent($(this).html()));
						$.getJSON(
							'process_registry_object.php?task=related_object_preview&as_json=true&key=' + encodeURIComponent($(this).html()),
							function(data) { 
								//console.log (data);
									$('.resolvable_key').each(function(){
										if ($(this).html() == data.key)
										{
											if (data.status != 'NOTFOUND')
											{
												var resolved_string = "<b>" + data.title + "</b> (" + data.class + ") " + data.status_span;
												//console.log("resolved string" + resolved_string);
												$(this).parent().after('<tr><td class="attribute">Resolved value:</td><td class="valueAttribute">'+resolved_string+'</td></tr>');
											}
										}
									});
							});
					});
	 }
</script>

</head>
<body onload="loadRelatedTitles(); window.print()">
<img style="float:right;width:135px;margin-bottom:5px" src="<?php print eAPP_ROOT ?>_images/_logos/ands_logo_white_200px.jpg"/>
<div>
<?php		
}




function printFooter()
{
	?>
	
</div>
</body>
</html>	
<?php 
}
