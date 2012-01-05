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
if (!IN_ORCA) die('No direct access to this file is permitted.');


$terms = array();

$vocabs = getChildTerms(getQueryValue("vocab"), getQueryValue("term"));

if ((!$vocabs || count($vocabs) == 0) && (strlen(getQueryValue("term")) > 3 || getQueryValue("vocab") == "ANZSRC-TOA"))
{ 
	$vocabs = getTermsForVocab(getQueryValue("vocab"));
}

if (isset($vocabs) && $vocabs) { 
	foreach ($vocabs AS $term) 
	{
		$terms[] = array (	"value" => preg_replace("/0{2,4}$/","",$term['identifier']),
							"name" => $term['name'],
							"desc" => str_replace(">>"," &raquo; ",$term['vocabpath'])
						 );
	} 
} else {
	if (getQueryValue("vocab") == "") {
		$terms[] = array (	"value" => "", "name"=>"No Vocabularies found!", "desc" => "Please select a Subject Type");
	}
	else if (getQueryValue("vocab") == "LOCAL") {
		$terms[] = array (	"value" => "", "name"=>"No Vocabularies found!", "desc" => "Any local values can be used with this Subject Type");
	}
	else
	{
		$terms[] = array (	"value" => "", "name"=>"No Vocabularies found!", "desc" => "Perhaps you need to be more specific?");
	}
}

if (count($terms) > 10) { 
	$terms = array_slice($terms,0,9); 
	$terms[] = array (	"value" => "", "name"=>"", "desc" => "More vocabularies found, try being more specific...");
}
print json_encode($terms);