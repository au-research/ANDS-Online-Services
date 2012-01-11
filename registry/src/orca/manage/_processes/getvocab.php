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
$vocabs = getTermsForVocab(getQueryValue("vocab"), getQueryValue("term"));
if (count($vocabs) == 0) 
{ 
	$vocabs = getChildTerms(getQueryValue("vocab"), getQueryValue("term"));
}

if (isset($vocabs) && $vocabs) { 
	foreach ($vocabs AS $term) 
	{
		$terms[] = array (	"value" => $term['identifier'],
							"desc" => str_replace(">>"," &raquo; ",$term['vocabpath'])
						 );
	} 
} else {
	$terms[] = array (	"value" => "", "desc" => "No suggestions matching '".getQueryValue("term")."'");
}
print json_encode($terms);